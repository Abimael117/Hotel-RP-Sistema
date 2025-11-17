<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user"])) {
    echo json_encode([]);
    exit;
}

require_once("../config/db.php");

$fecha_in  = $_GET["fecha_in"] ?? null;
$fecha_out = $_GET["fecha_out"] ?? null;

if (!$fecha_in || !$fecha_out) {
    echo json_encode([]);
    exit;
}

// 1. Filtrar habitaciones que NO estén en mantenimiento/limpieza/ocupado
$sql = "
    SELECT 
        h.id, h.numero, h.tipo_id, h.capacidad, h.precio_noche,
        t.nombre AS tipo_nombre
    FROM habitaciones h
    JOIN tipos_habitacion t ON t.id = h.tipo_id
    WHERE h.estado = 'disponible'
";

$result = $conn->query($sql);
$habitaciones = [];

while ($row = $result->fetch_assoc()) {
    $habitaciones[] = $row;
}

// 2. Filtrar por disponibilidad real (evitar choques con reservas activas)
$habitacionesDisponibles = [];

foreach ($habitaciones as $hab) {

    $sqlCheck = "
        SELECT COUNT(*) AS total
        FROM reservas
        WHERE habitacion_id = ?
        AND estado = 'Activa'
        AND NOT (fecha_check_out <= ? OR fecha_check_in >= ?)
    ";

    $stmt = $conn->prepare($sqlCheck);
    $stmt->bind_param("iss", $hab["id"], $fecha_in, $fecha_out);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res["total"] == 0) {
        $habitacionesDisponibles[] = $hab;
    }
}

echo json_encode($habitacionesDisponibles);
