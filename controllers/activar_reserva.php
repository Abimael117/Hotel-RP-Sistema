<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");
require_once("../models/Actividad.php");

// Instancias
$movModel = new Movimiento($conn);
$actModel = new Actividad($conn);

$usuarioId = $_SESSION["usuario_id"];

// =============================
// Validar ID
// =============================
if (!isset($_GET['id'])) {
    die("ID de reserva no proporcionado.");
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
// Activar reserva
// =============================
$exito = $movModel->activarReserva($id, $error);

if (!$exito) {
    echo "<h2>Error al activar reserva</h2>";
    echo "<p>$error</p>";
    echo "<a href='movimientos.php'>Volver</a>";
    exit;
}

// =============================
// Registrar actividad
// =============================
$descripcion = "Se activó una reserva para la habitación $habitacionNumero";
$actModel->registrar($usuarioId, $descripcion, "reserva", $id);

// =============================
// Final
// =============================
header("Location: movimientos.php?msg=reserva_activada");
exit;
