<?php

class Habitacion {

    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener todas las habitaciones con su tipo
   public function obtenerTodas() { 
    $sql = "SELECT 
                h.id,
                h.numero,
                h.tipo_id,
                h.capacidad,
                h.precio_noche,
                h.estado,
                t.nombre AS tipo_nombre
            FROM habitaciones h
            JOIN tipos_habitacion t ON t.id = h.tipo_id";

    $result = $this->conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}


}