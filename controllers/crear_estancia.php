<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");
require_once("../models/Habitacion.php");

// Instancias de modelos
$movModel = new Movimiento($conn);
$habModel = new Habitacion($conn);

// ===========================================================
// SI EL FORMULARIO AÚN NO SE ENVÍA → SOLO MOSTRAR VISTA
// ===========================================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    // Obtener habitaciones disponibles (no ocupadas)
    $habitaciones = $habModel->obtenerTodas();

    // Obtener huéspedes
    $sql = "SELECT * FROM huespedes ORDER BY nombre_completo ASC";
    $res = $conn->query($sql);
    $huespedes = $res->fetch_all(MYSQLI_ASSOC);

    include("../public/form_estancia.php");
    exit;
}

// ===========================================================
// SI SE ENVÍA EL FORMULARIO (POST) → PROCESAR CREACIÓN
// ===========================================================

$habitacionId   = intval($_POST["habitacion_id"]);
$huespedId      = intval($_POST["huesped_id"]);
$fechaCheckIn   = $_POST["fecha_check_in"];   // HOY
$fechaCheckOut  = $_POST["fecha_check_out"];
$numHuespedes   = intval($_POST["numero_huespedes"]);
$personasExtra  = intval($_POST["personas_extra"]);
$total          = floatval($_POST["total"]);
$metodoPago     = $_POST["metodo_pago"];
$descuento      = floatval($_POST["descuento_aplicado"] ?? 0);

// Validaciones básicas
$errores = [];

if (!$habitacionId) $errores[] = "Debes elegir una habitación.";
if (!$huespedId) $errores[] = "Debes elegir un huésped.";
if (!$fechaCheckOut) $errores[] = "Debes seleccionar una fecha de salida.";
if ($fechaCheckOut <= $fechaCheckIn) $errores[] = "La fecha de salida debe ser mayor a hoy.";

if (!empty($errores)) {
    $habitaciones = $habModel->obtenerHabitacionesDisponibles();

    $sql = "SELECT * FROM huespedes ORDER BY nombre_completo ASC";
    $res = $conn->query($sql);
    $huespedes = $res->fetch_all(MYSQLI_ASSOC);

    include("../public/form_estancia.php");
    exit;
}

// Datos estructurados
$data = [
    "habitacion_id"     => $habitacionId,
    "huesped_id"        => $huespedId,
    "fecha_check_in"    => $fechaCheckIn,
    "fecha_check_out"   => $fechaCheckOut,
    "numero_huespedes"  => $numHuespedes,
    "personas_extra"    => $personasExtra,
    "total"             => $total,
    "metodo_pago"       => $metodoPago,
    "descuento_aplicado"=> $descuento
];

$error = null;

// Intentar crear estancia
$estanciaId = $movModel->crearEstancia($data, $error);

if (!$estanciaId) {

    $errores[] = $error;

    $habitaciones = $habModel->obtenerHabitacionesDisponibles();

    $sql = "SELECT * FROM huespedes ORDER BY nombre_completo ASC";
    $res = $conn->query($sql);
    $huespedes = $res->fetch_all(MYSQLI_ASSOC);

    include("../public/form_estancia.php");
    exit;
}

// Todo bien → redirigir
header("Location: movimientos.php?msg=estancia_creada");
exit;
