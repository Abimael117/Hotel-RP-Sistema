<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");
require_once("../models/Habitacion.php");

// Instancias de modelos
$movModel = new Movimiento($conn);
$habModel = new Habitacion($conn);

// ===========================================================
// SI EL FORMULARIO AÚN NO SE ENVÍA → SOLO MOSTRAR FORMULARIO
// ===========================================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    // Habitaciones
    $habitaciones = $habModel->obtenerTodas();

    // Huespedes
    $sql = "SELECT * FROM huespedes ORDER BY nombre_completo ASC";
    $res = $conn->query($sql);
    $huespedes = $res->fetch_all(MYSQLI_ASSOC);

    // Tipos de precios (para ocupación)
    $precios = $conn->query("SELECT * FROM tipos_precios")->fetch_all(MYSQLI_ASSOC);

    include("../public/form_estancia.php");
    exit;
}

// ===========================================================
// PROCESAR FORMULARIO ENVIADO
// ===========================================================

$habitacionId   = intval($_POST["habitacion_id"]);
$huespedId      = intval($_POST["huesped_id"]);
$fechaCheckIn   = $_POST["fecha_check_in"];
$fechaCheckOut  = $_POST["fecha_check_out"];
$numHuespedes   = intval($_POST["numero_huespedes"]);
$personasExtra  = intval($_POST["personas_extra"]);
$total          = floatval($_POST["total"]);
$metodoPago     = $_POST["metodo_pago"];
$descuento      = floatval($_POST["descuento_aplicado"] ?? 0);

$errores = [];

if (!$habitacionId) $errores[] = "Debes elegir una habitación.";
if (!$huespedId) $errores[] = "Debes elegir un huésped.";
if (!$fechaCheckOut) $errores[] = "Debes seleccionar una fecha de salida.";
if ($fechaCheckOut <= $fechaCheckIn) $errores[] = "La fecha de salida debe ser mayor al día de hoy.";

if (!empty($errores)) {
    $habitaciones = $habModel->obtenerTodas();

    $sql = "SELECT * FROM huespedes ORDER BY nombre_completo ASC";
    $huespedes = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

    $precios = $conn->query("SELECT * FROM tipos_precios")->fetch_all(MYSQLI_ASSOC);

    include("../public/form_estancia.php");
    exit;
}

// Datos para modelo
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
$estanciaId = $movModel->crearEstancia($data, $error);

if (!$estanciaId) {

    $errores[] = $error;

    $habitaciones = $habModel->obtenerTodas();
    $huespedes = $conn->query("SELECT * FROM huespedes ORDER BY nombre_completo ASC")->fetch_all(MYSQLI_ASSOC);
    $precios = $conn->query("SELECT * FROM tipos_precios")->fetch_all(MYSQLI_ASSOC);

    include("../public/form_estancia.php");
    exit;
}

// Todo bien → redirigir
header("Location: movimientos.php?msg=estancia_creada");
exit;

