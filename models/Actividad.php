<?php
// models/Actividad.php

class Actividad
{
    private $conn;
    private $table = "actividades";

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Registrar actividad
    public function registrar($usuarioId, $descripcion, $entidad, $entidadId)
    {
        $sql = "INSERT INTO {$this->table}
                (usuario_id, descripcion, entidad, entidad_id, fecha)
                VALUES (?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issi", $usuarioId, $descripcion, $entidad, $entidadId);
        $stmt->execute();
        $stmt->close();
    }

    // Listar últimas 10 actividades
    public function recientes($limite = 10)
    {
        $sql = "
            SELECT a.*, u.nombre AS usuario
            FROM {$this->table} a
            LEFT JOIN usuarios u ON a.usuario_id = u.id
            ORDER BY fecha DESC
            LIMIT ?
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();

        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }
}
