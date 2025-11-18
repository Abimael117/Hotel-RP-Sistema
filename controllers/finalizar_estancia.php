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
    die("ID de estancia no proporcionado.");
}

$id = intval($_GET['id']);

$error = null;

// Intentar finalizar estancia
$exito = $movModel->finalizarEstancia($id, $error);

if (!$exito) {
    echo "<h2>Error al finalizar estancia</h2>";
    echo "<p>$error</p>";
    echo "<a href='movimientos.php'>Volver</a>";
    exit;
}

header("Location: movimientos.php?msg=estancia_finalizada");
exit;
