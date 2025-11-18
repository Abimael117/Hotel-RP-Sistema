<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");

// Modelo
$movModel = new Movimiento($conn);

// =============================
// Validar ID
// =============================
if (!isset($_GET['id'])) {
    die("ID de reserva no proporcionado.");
}

$id = intval($_GET['id']);

$error = null;

// Intentar activar reserva
$exito = $movModel->activarReserva($id, $error);

if (!$exito) {
    // Si hubo error, mostrarlo en pantalla de forma sencilla
    echo "<h2>Error al activar reserva</h2>";
    echo "<p>$error</p>";
    echo "<a href='movimientos.php'>Volver</a>";
    exit;
}

// Todo salió bien
header("Location: movimientos.php?msg=reserva_activada");
exit;
