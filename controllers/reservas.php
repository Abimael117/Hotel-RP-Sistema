<?php 
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: ../public/index.php");
    exit;
}

require_once("../config/db.php");
require_once("../models/Reserva.php");

// Modelo de reservas
$reservaModel = new Reserva($conn);
$reservas = $reservaModel->obtenerTodas();

// Lista de habitaciones
$sqlHab = "
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

$habitaciones = $conn->query($sqlHab)->fetch_all(MYSQLI_ASSOC);

// Lista de huéspedes
$sqlHues = "SELECT id, nombre_completo FROM huespedes ORDER BY nombre_completo ASC";
$huespedes = $conn->query($sqlHues)->fetch_all(MYSQLI_ASSOC);

// =============================
// Obtener precios por tipo/ocupación
// =============================
$sqlPre = "SELECT tipo_id, ocupacion, precio FROM tipos_precios";
$resPre = $conn->query($sqlPre);

$preciosGlobal = [];

while ($row = $resPre->fetch_assoc()) {
    $tipo_id = $row["tipo_id"];
    $ocup    = $row["ocupacion"];
    $precio  = floatval($row["precio"]);

    if (!isset($preciosGlobal[$tipo_id])) {
        $preciosGlobal[$tipo_id] = [];
    }
    $preciosGlobal[$tipo_id][$ocup] = $precio;
}

// Cargar vista
include("../public/reservas.php");