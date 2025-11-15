<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: ../public/index.php");
    exit;
}

require_once("../config/db.php");
require_once("../models/Habitacion.php");

// Modelo de habitaciones
$habitacionModel = new Habitacion($conn);
$habitaciones = $habitacionModel->obtenerTodas();

// ===============================
// OBTENER LOS TIPOS DE HABITACIÓN
// ===============================
$sqlTipos = "SELECT * FROM tipos_habitacion";
$resultTipos = $conn->query($sqlTipos);
$tipos = $resultTipos->fetch_all(MYSQLI_ASSOC);

// Enviar todo a la vista
include("../public/habitaciones.php");
