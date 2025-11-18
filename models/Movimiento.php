<?php
// models/Movimiento.php

class Movimiento
{
    private $conn;
    private $table = "reservas";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // ============================================================
    // 1) VALIDAR DISPONIBILIDAD (EVITAR DOBLE RESERVA)
    // ============================================================
    public function hayTraslape($habitacionId, $fechaInicio, $fechaFin, $ignorarId = null)
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE habitacion_id = ?
              AND estado IN ('Activa', 'Reservada')
              AND (
                    (fecha_check_in < ? AND fecha_check_out > ?)
                 OR (fecha_check_in >= ? AND fecha_check_in < ?)
                 OR (fecha_check_out > ? AND fecha_check_out <= ?)
              )
        ";

        if ($ignorarId !== null) {
            $sql .= " AND id <> ? ";
        }

        $stmt = $this->conn->prepare($sql);

        if ($ignorarId !== null) {
            $stmt->bind_param(
                "isssssii",
                $habitacionId,
                $fechaFin,
                $fechaInicio,
                $fechaInicio,
                $fechaFin,
                $fechaInicio,
                $fechaFin,
                $ignorarId
            );
        } else {
            $stmt->bind_param(
                "issssss",
                $habitacionId,
                $fechaFin,
                $fechaInicio,
                $fechaInicio,
                $fechaFin,
                $fechaInicio,
                $fechaFin
            );
        }

        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $result['total'] > 0;
    }

    // ============================================================
    // 2) CREAR ESTANCIA (INMEDIATA)
    // ============================================================
    public function crearEstancia($data, &$error = null)
    {
        $habitacionId   = $data['habitacion_id'];
        $huespedId      = $data['huesped_id'];
        $fechaCheckIn   = $data['fecha_check_in'];
        $fechaCheckOut  = $data['fecha_check_out'];
        $numHuespedes   = $data['numero_huespedes'] ?? 1;
        $personasExtra  = $data['personas_extra'] ?? 0;
        $total          = $data['total'] ?? 0;
        $metodoPago     = $data['metodo_pago'] ?? null;
        $descuento      = $data['descuento_aplicado'] ?? 0;

        if ($this->hayTraslape($habitacionId, $fechaCheckIn, $fechaCheckOut)) {
            $error = "La habitación ya está reservada/ocupada en ese rango de fechas.";
            return false;
        }

        $sql = "
            INSERT INTO {$this->table}
            (huesped_id, habitacion_id, fecha_check_in, fecha_check_out,
             numero_huespedes, personas_extra, estado, total, metodo_pago,
             tipo_ocupacion, fecha_creacion, descuento_aplicado)
            VALUES (?, ?, ?, ?, ?, ?, 'Activa', ?, ?, 'Estancia', NOW(), ?)
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param(
            "iissiidss",
            $huespedId,
            $habitacionId,
            $fechaCheckIn,
            $fechaCheckOut,
            $numHuespedes,
            $personasExtra,
            $total,
            $metodoPago,
            $descuento
        );

        if (!$stmt->execute()) {
            $error = "Error al crear la estancia: " . $stmt->error;
            $stmt->close();
            return false;
        }

        $nuevoId = $stmt->insert_id;
        $stmt->close();

        // Acción automática: habitación ocupada
        $this->cambiarEstadoHabitacion($habitacionId, 'ocupado');

        return $nuevoId;
    }

    // ============================================================
    // 3) CREAR RESERVA FUTURA (CORRECTO Y ALINEADO)
    // ============================================================
    public function crearReserva($data, &$error = null)
    {
        $sql = "
            INSERT INTO {$this->table}
            (
                huesped_id,
                habitacion_id,
                fecha_check_in,
                fecha_check_out,
                numero_huespedes,
                personas_extra,
                estado,
                total,
                metodo_pago,
                tipo_ocupacion,
                fecha_creacion,
                descuento_aplicado
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)
        ";

        $stmt = $this->conn->prepare($sql);

        $estado = "Reservada";
        $tipo   = "Reserva";

        // ORDEN EXACTO DE CAMPOS en tu tabla → 11 variables
        $stmt->bind_param(
            "iissiisdssd",
            $data['huesped_id'],
            $data['habitacion_id'],
            $data['fecha_check_in'],
            $data['fecha_check_out'],
            $data['numero_huespedes'],
            $data['personas_extra'],
            $estado,
            $data['total'],
            $data['metodo_pago'],
            $tipo,
            $data['descuento_aplicado']
        );

        if (!$stmt->execute()) {
            $error = "Error al crear la reserva: " . $stmt->error;
            $stmt->close();
            return false;
        }

        $id = $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    // ============================================================
    // 4) ACTIVAR RESERVA
    // ============================================================
    public function activarReserva($id, &$error = null)
    {
        $mov = $this->obtenerPorId($id);
        if (!$mov) {
            $error = "Movimiento no encontrado.";
            return false;
        }

        if ($mov['tipo_ocupacion'] !== 'Reserva') {
            $error = "Solo se pueden activar reservas.";
            return false;
        }

        $sql = "UPDATE {$this->table} SET estado = 'Activa' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $this->cambiarEstadoHabitacion($mov['habitacion_id'], 'ocupado');

        return true;
    }

    // ============================================================
    // 5) FINALIZAR ESTANCIA
    // ============================================================
    public function finalizarEstancia($id, &$error = null)
    {
        $mov = $this->obtenerPorId($id);
        if (!$mov) {
            $error = "Movimiento no encontrado.";
            return false;
        }

        $sql = "UPDATE {$this->table} SET estado = 'Finalizada' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        return true;
    }

    // ============================================================
    // 6) OBTENER POR ID
    // ============================================================
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $res;
    }

    // ============================================================
    // 7) LISTAR MOVIMIENTOS POR MES/AÑO
    // ============================================================
    public function listarPorMes($anio, $mes)
    {
        $sql = "
            SELECT r.*, h.numero AS numero_habitacion, hu.nombre_completo AS nombre_huesped
            FROM {$this->table} r
            INNER JOIN habitaciones h ON r.habitacion_id = h.id
            INNER JOIN huespedes hu ON r.huesped_id = hu.id
            WHERE YEAR(r.fecha_check_in) = ?
              AND MONTH(r.fecha_check_in) = ?
            ORDER BY r.fecha_check_in ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $anio, $mes);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $res;
    }

    // ============================================================
    // 8) CAMBIAR ESTADO HABITACIÓN
    // ============================================================
    private function cambiarEstadoHabitacion($habitacionId, $nuevoEstado)
    {
        $sql = "UPDATE habitaciones SET estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $nuevoEstado, $habitacionId);
        $stmt->execute();
        $stmt->close();
    }
}
