<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");
require_once("../models/Actividad.php");

// Modelo
$movModel = new Movimiento($conn);
$actModel = new Actividad($conn);

$usuarioId = $_SESSION["usuario_id"];

// =============================
// Validar ID
// =============================
if (!isset($_GET['id'])) {
    die("ID de estancia no proporcionado.");
}

$id = intval($_GET['id']);
$error = null;

// Obtener movimiento
$mov = $movModel->obtenerPorId($id);
if (!$mov) {
    die("Movimiento no encontrado.");
}

// Obtener número REAL de la habitación
$habitacionNumero = $conn->query("
    SELECT numero FROM habitaciones WHERE id = {$mov['habitacion_id']}
")->fetch_assoc()['numero'];

// =============================
// Finalizar estancia
// =============================
$exito = $movModel->finalizarEstancia($id, $error);

if (!$exito) {
    echo "<h2>Error al finalizar estancia</h2>";
    echo "<p>$error</p>";
    echo "<a href='movimientos.php'>Volver</a>";
    exit;
}

// =============================
// Registrar actividad
// =============================
$descripcion = "Se finalizó la estancia de la habitación $habitacionNumero";
$actModel->registrar($usuarioId, $descripcion, "estancia", $id);

// =============================
// Final
// =============================
header("Location: movimientos.php?msg=estancia_finalizada");
exit;
?>
