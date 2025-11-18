<?php
// models/Movimiento.php

// Este modelo maneja TODOS los movimientos de ocupación del hotel:
// - Estancias (renta inmediata)
// - Reservas futuras
// Usando la tabla `reservas` como tabla de movimientos.

// Estructura mínima esperada de la tabla `reservas`:
//
//  id                INT PK AI
//  huesped_id        INT
//  habitacion_id     INT
//  fecha_check_in    DATE
//  fecha_check_out   DATE
//  numero_huespedes  INT
//  personas_extra    INT
//  estado            VARCHAR(20)  -- Activa | Reservada | Finalizada | Cancelada
//  total             DECIMAL(10,2)
//  metodo_pago       VARCHAR(50)
//  tipo_ocupacion    VARCHAR(20)  -- Estancia | Reserva
//  fecha_creacion    DATETIME
//  descuento_aplicado DECIMAL(10,2)
//
// NOTA: `estado` lo usamos como "estado_movimiento"
//       `tipo_ocupacion` lo usamos como "tipo_movimiento"

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
    //
    // Revisa si hay algún movimiento (Estancia o Reserva) para
    // la misma habitación y con fechas traslapadas.
    //
    // Ignora movimientos en estado: Finalizada o Cancelada
    //
    // $ignorarId se usa cuando actualizas un movimiento existente,
    // para no chocarte contigo mismo.
    //
    public function hayTraslape($habitacionId, $fechaInicio, $fechaFin, $ignorarId = null)
    {
        $sql = "
            SELECT COUNT(*) AS total
            FROM {$this->table}
            WHERE habitacion_id = ?
              AND estado IN ('Activa', 'Reservada')
              AND (
                    (fecha_check_in < ? AND fecha_check_out > ?)  -- traslape interno
                 OR (fecha_check_in >= ? AND fecha_check_in < ?)   -- empieza dentro del rango
                 OR (fecha_check_out > ? AND fecha_check_out <= ?) -- termina dentro del rango
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
    // 2) CREAR ESTANCIA (Renta inmediata - hoy)
    // ============================================================
    //
    // Reglas:
    // - tipo_ocupacion = 'Estancia'
    // - estado = 'Activa'
    // - Sí cambia automáticamente la habitación a 'ocupado'
    //
    public function crearEstancia($data, &$error = null)
    {
        $habitacionId   = $data['habitacion_id'];
        $huespedId      = $data['huesped_id'];
        $fechaCheckIn   = $data['fecha_check_in'];   // normalmente HOY
        $fechaCheckOut  = $data['fecha_check_out'];
        $numHuespedes   = $data['numero_huespedes'] ?? 1;
        $personasExtra  = $data['personas_extra'] ?? 0;
        $total          = $data['total'] ?? 0;
        $metodoPago     = $data['metodo_pago'] ?? null;
        $descuento      = $data['descuento_aplicado'] ?? 0;

        // Validar traslapes
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

        // Acción automática #1: poner habitación en 'ocupado'
        $this->cambiarEstadoHabitacion($habitacionId, 'ocupado');

        return $nuevoId;
    }

    // ============================================================
    // 3) CREAR RESERVA FUTURA
    // ============================================================
    //
    // Reglas:
    // - tipo_ocupacion = 'Reserva'
    // - estado = 'Reservada'
    // - NO toca el estado de la habitación
    //
    public function crearReserva($data, &$error = null)
    {
        $habitacionId   = $data['habitacion_id'];
        $huespedId      = $data['huesped_id'];
        $fechaCheckIn   = $data['fecha_check_in'];   // futura
        $fechaCheckOut  = $data['fecha_check_out'];
        $numHuespedes   = $data['numero_huespedes'] ?? 1;
        $personasExtra  = $data['personas_extra'] ?? 0;
        $total          = $data['total'] ?? 0;
        $metodoPago     = $data['metodo_pago'] ?? null;
        $descuento      = $data['descuento_aplicado'] ?? 0;

        // Validar traslapes
        if ($this->hayTraslape($habitacionId, $fechaCheckIn, $fechaCheckOut)) {
            $error = "La habitación ya está reservada/ocupada en ese rango de fechas.";
            return false;
        }

        $sql = "
            INSERT INTO {$this->table}
            (huesped_id, habitacion_id, fecha_check_in, fecha_check_out,
             numero_huespedes, personas_extra, estado, total, metodo_pago,
             tipo_ocupacion, fecha_creacion, descuento_aplicado)
            VALUES (?, ?, ?, ?, ?, ?, 'Reservada', ?, ?, 'Reserva', NOW(), ?)
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
            $error = "Error al crear la reserva: " . $stmt->error;
            $stmt->close();
            return false;
        }

        $nuevoId = $stmt->insert_id;
        $stmt->close();

        // NO se toca la habitación
        return $nuevoId;
    }

    // ============================================================
    // 4) ACTIVAR RESERVA (cuando el huésped llega)
    // ============================================================
    //
    // Reglas:
    // - Solo aplica si tipo_ocupacion = 'Reserva'
    // - estado pasa de 'Reservada' a 'Activa'
    // - Acción automática #2: habitación → 'ocupado'
    //
    public function activarReserva($id, &$error = null)
    {
        // Obtener movimiento
        $mov = $this->obtenerPorId($id);
        if (!$mov) {
            $error = "Movimiento no encontrado.";
            return false;
        }

        if ($mov['tipo_ocupacion'] !== 'Reserva') {
            $error = "Solo se pueden activar registros de tipo Reserva.";
            return false;
        }

        $sql = "UPDATE {$this->table} SET estado = 'Activa' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            $error = "Error al activar la reserva: " . $stmt->error;
            $stmt->close();
            return false;
        }
        $stmt->close();

        // Acción automática #2: poner habitación en 'ocupado'
        $this->cambiarEstadoHabitacion($mov['habitacion_id'], 'ocupado');

        return true;
    }

    // ============================================================
    // 5) FINALIZAR ESTANCIA (cuando el huésped se va)
    // ============================================================
    //
    // Reglas:
    // - estado pasa a 'Finalizada'
    // - NO se toca la habitación (el recepcionista la pasa a Limpieza manualmente)
    //
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

        if (!$stmt->execute()) {
            $error = "Error al finalizar la estancia: " . $stmt->error;
            $stmt->close();
            return false;
        }

        $stmt->close();
        return true;
    }

    // ============================================================
    // 6) OBTENER MOVIMIENTO POR ID
    // ============================================================
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        return $row;
    }

    // ============================================================
    // 7) LISTAR MOVIMIENTOS POR MES/AÑO
    // ============================================================
    //
    // Para la vista principal del módulo.
    //
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
        $res = $stmt->get_result();
        $movimientos = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $movimientos;
    }

    // ============================================================
    // 8) Cambiar estado de una habitación (uso interno)
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
