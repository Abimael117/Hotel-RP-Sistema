<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");
require_once("../models/Habitacion.php");

// Instanciar modelos
$movModel = new Movimiento($conn);
$habModel = new Habitacion($conn);

// ===========================================================
// SI NO ES POST → MOSTRAR FORMULARIO
// ===========================================================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    // Habitaciones disponibles (en cualquier estado excepto 'ocupado')
    $habitaciones = $habModel->obtenerTodas(); // Reserva puede ser con habitación disponible, limpieza, mantenimiento, reservada (manual), fuera de servicio

    // Obtener huéspedes
    $sql = "SELECT * FROM huespedes ORDER BY nombre_completo ASC";
    $res = $conn->query($sql);
    $huespedes = $res->fetch_all(MYSQLI_ASSOC);

    include("../public/form_reserva.php");
    exit;
}

// ===========================================================
// SI ES POST → PROCESAR RESERVA
// ===========================================================

$habitacionId   = intval($_POST["habitacion_id"]);
$huespedId      = intval($_POST["huesped_id"]);
$fechaCheckIn   = $_POST["fecha_check_in"];    // FUTURA
$fechaCheckOut  = $_POST["fecha_check_out"];
$numHuespedes   = intval($_POST["numero_huespedes"]);
$personasExtra  = intval($_POST["personas_extra"]);
$total          = floatval($_POST["total"]);
$metodoPago     = $_POST["metodo_pago"];
$descuento      = floatval($_POST["descuento_aplicado"] ?? 0);

// Validaciones simples
$errores = [];

if (!$habitacionId) $errores[] = "Debes elegir una habitación.";
if (!$huespedId) $errores[] = "Debes elegir un huésped.";
if (!$fechaCheckIn || !$fechaCheckOut) $errores[] = "Debes elegir fechas.";
if ($fechaCheckIn <= date('Y-m-d')) $errores[] = "El check-in debe ser una fecha futura.";
if ($fechaCheckOut <= $fechaCheckIn) $errores[] = "El check-out debe ser mayor al check-in.";

if (!empty($errores)) {

    $habitaciones = $habModel->obtenerTodas();

    $sql = "SELECT * FROM huespedes ORDER BY nombre_completo ASC";
    $res = $conn->query($sql);
    $huespedes = $res->fetch_all(MYSQLI_ASSOC);

    include("../public/form_reserva.php");
    exit;
}

// Datos a enviar al modelo
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

// Crear reserva (NO cambia estado de habitación)
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

// Todo bien
header("Location: movimientos.php?msg=reserva_creada");
exit;
