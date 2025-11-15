<?php

class Reserva {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Obtener todas las reservas con joins bonitos
    public function obtenerTodas() {
        $sql = "SELECT r.*,
                       h.numero AS habitacion_numero,
                       hu.nombre_completo AS huesped_nombre
                FROM reservas r
                JOIN habitaciones h ON r.habitacion_id = h.id
                JOIN huespedes hu ON r.huesped_id = hu.id
                ORDER BY r.fecha_creacion DESC";

        $result = $this->conn->query($sql);

        $reservas = [];
        while ($fila = $result->fetch_assoc()) {
            $reservas[] = $fila;
        }

        return $reservas;
    }
}