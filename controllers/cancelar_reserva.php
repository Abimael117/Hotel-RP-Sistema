<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");

// Validar ID
if (!isset($_GET['id'])) {
    die("ID inválido.");
}

$id = intval($_GET['id']);

$resultado = Movimiento::cancelarReserva($conn, $id);

if ($resultado) {
    header("Location: ../public/movimientos.php?msg=ReservaCancelada");
    exit;
} else {
    die("Error al cancelar la reserva.");
}
