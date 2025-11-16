<?php
session_start();

// =====================================
// 1. VALIDAR SESIÓN
// =====================================
if (!isset($_SESSION["user"])) {
    header("Location: ../public/index.php");
    exit;
}

// =====================================
// 2. CARGAR DEPENDENCIAS (ANTES DE USARLAS)
// =====================================
require_once("../config/db.php");
require_once("../models/Habitacion.php");

// Crear instancia
$HabitacionModel = new Habitacion($conn);

// =====================================
// 3. MANEJO DE ACCIONES (CAMBIAR ESTADO / ELIMINAR)
// =====================================

// ------ Cambiar estado ------
if (isset($_GET["accion"]) && $_GET["accion"] === "estado") {
    $id = intval($_GET["id"]);
    $nuevo = strtolower($_GET["valor"]);

    $HabitacionModel->cambiarEstado($id, $nuevo);

    header("Location: ../controllers/habitaciones.php");
    exit;
}

// ------ Eliminar habitación ------
if (isset($_GET["accion"]) && $_GET["accion"] === "eliminar") {
    $id = intval($_GET["id"]);

    $HabitacionModel->eliminar($id);

    header("Location: ../controllers/habitaciones.php");
    exit;
}

// =====================================
// 4. OBTENER HABITACIONES
// =====================================
$habitaciones = $HabitacionModel->obtenerTodas();

// =====================================
// 5. OBTENER TIPOS DE HABITACIÓN
// =====================================
$sqlTipos = "SELECT * FROM tipos_habitacion";
$resultTipos = $conn->query($sqlTipos);
$tipos = $resultTipos->fetch_all(MYSQLI_ASSOC);

// =====================================
// 6. MOSTRAR VISTA
// =====================================
include("../public/habitaciones.php");
