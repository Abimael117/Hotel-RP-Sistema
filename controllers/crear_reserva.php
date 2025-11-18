<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");
require_once("../models/Habitacion.php");
require_once("../models/Actividad.php");

// Instanciar modelos
$movModel = new Movimiento($conn);
$habModel = new Habitacion($conn);
$actModel = new Actividad($conn);

// ID del usuario que hace el movimiento
$usuarioId = $_SESSION["usuario_id"];

// ===========================================================
// SI EL FORMULARIO NO SE ENVÍA → MOSTRAR FORMULARIO
// ===========================================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    // Habitaciones
    $habitaciones = $habModel->obtenerTodas();

    // Huespedes
    $sql = "SELECT * FROM huespedes ORDER BY nombre_completo ASC";
    $res = $conn->query($sql);
    $huespedes = $res->fetch_all(MYSQLI_ASSOC);

    // Tipos de precios
    $precios = $conn->query("SELECT * FROM tipos_precios")->fetch_all(MYSQLI_ASSOC);

    include("../public/form_reserva.php");
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

    include("../public/form_reserva.php");
    exit;
}

// ===========================================================
// GUARDAR RESERVA
// ===========================================================

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
$reservaId = $movModel->crearReserva($data, $error);

if (!$reservaId) {
    $errores[] = $error;

    $habitaciones = $habModel->obtenerTodas();

    $sql = "SELECT * FROM huespedes ORDER BY nombre_completo ASC";
    $res = $conn->query($sql);
    $huespedes = $res->fetch_all(MYSQLI_ASSOC);

    include("../public/form_reserva.php");
    exit;
}

// ===========================================================
// REGISTRAR ACTIVIDAD
// ===========================================================
$descripcion = "Se creó una reserva para la habitación $habitacionId";
$actModel->registrar($usuarioId, $descripcion, "reserva", $reservaId);

// ===========================================================
// TODO BIEN — REDIRIGIR
// ===========================================================
header("Location: movimientos.php?msg=reserva_creada");
exit;
