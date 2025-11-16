<?php

class Habitacion {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // ==============================================================
    //  OBTENER TODAS LAS HABITACIONES
    // ==============================================================
    public function obtenerTodas() {
        $sql = "
            SELECT 
                h.id,
                h.numero,
                h.tipo_id,
                h.capacidad,
                h.precio_noche,
                h.estado,
                t.nombre AS tipo_nombre
            FROM habitaciones h
            JOIN tipos_habitacion t ON t.id = h.tipo_id
            ORDER BY h.numero ASC
        ";

        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // ==============================================================
    //  CAMBIAR ESTADO (disponible / ocupado / reservado / limpieza / mantenimiento)
    // ==============================================================
    public function cambiarEstado($id, $nuevoEstado) {

        $id = intval($id);
        $nuevoEstado = strtolower($this->conn->real_escape_string($nuevoEstado));

        $permitidos = [
            "disponible",
            "ocupado",
            "reservado",
            "limpieza",
            "mantenimiento"
        ];

        if (!in_array($nuevoEstado, $permitidos)) {
            return false;
        }

        $sql = "UPDATE habitaciones 
                SET estado = '$nuevoEstado' 
                WHERE id = $id 
                LIMIT 1";

        return $this->conn->query($sql);
    }

    // ==============================================================
    //  ELIMINAR HABITACIÓN
    // ==============================================================
    public function eliminar($id) {

        $id = intval($id);

        // Podríamos validar que no tenga reservas activas, pero tú dices si lo agregamos

        $sql = "DELETE FROM habitaciones 
                WHERE id = $id 
                LIMIT 1";

        return $this->conn->query($sql);
    }
}
