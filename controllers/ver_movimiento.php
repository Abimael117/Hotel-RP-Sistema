<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");

// Modelo
$movModel = new Movimiento($conn);

// ==================================
// Validar ID
// ==================================
if (!isset($_GET['id'])) {
    die("ID no proporcionado.");
}

$id = intval($_GET['id']);

// Obtener datos del movimiento
$mov = $movModel->obtenerPorId($id);

if (!$mov) {
    echo "<h2>Movimiento no encontrado</h2>";
    echo "<a href='movimientos.php'>Volver</a>";
    exit;
}

// Obtener datos del huésped
$sql = "SELECT * FROM huespedes WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mov['huesped_id']);
$stmt->execute();
$res = $stmt->get_result();
$huesped = $res->fetch_assoc();
$stmt->close();

// Obtener datos de habitación
$sql = "
    SELECT h.*, t.nombre AS tipo_nombre
    FROM habitaciones h
    LEFT JOIN tipos_habitacion t ON h.tipo_id = t.id
    WHERE h.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mov['habitacion_id']);
$stmt->execute();
$res = $stmt->get_result();
$habitacion = $res->fetch_assoc();
$stmt->close();

// Cargar vista
include("../public/ver_movimiento.php");
