<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");

// Validación
if (!isset($_POST['id'], $_POST['fecha_check_in'], $_POST['fecha_check_out'])) {
    die("Faltan datos para editar la reserva.");
}

$id = intval($_POST['id']);
$checkIn = $_POST['fecha_check_in'];
$checkOut = $_POST['fecha_check_out'];
$notas = $_POST['notas'] ?? null;

// Validar orden de fechas
if ($checkIn > $checkOut) {
    die("La fecha de check-in no puede ser posterior al check-out.");
}

// Llamar al modelo
$resultado = Movimiento::editarReserva($conn, $id, $checkIn, $checkOut, $notas);

if ($resultado) {
    header("Location: ../public/movimientos.php?msg=ReservaActualizada");
    exit;
} else {
    die("Error al actualizar la reserva.");
}
