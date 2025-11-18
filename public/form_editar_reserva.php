<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");
include("../views/layout/header.php");

// Validar ID
if (!isset($_GET['id'])) {
    die("ID inválido.");
}

$id = intval($_GET['id']);
$movimiento = Movimiento::obtenerPorId($conn, $id);

if (!$movimiento || $movimiento['tipo_ocupacion'] !== "Reserva") {
    die("La reserva no existe o no es una reserva válida.");
}
?>

<link rel="stylesheet" href="../public/assets/css/style.css">
<link rel="stylesheet" href="../public/assets/css/movimientos.css">

<div class="container">

    <h1 class="titulo-modulo">Editar Reserva</h1>
    <p class="descripcion-modulo">Modifica las fechas y datos de la reserva.</p>

    <form method="POST" action="../controllers/editar_reserva.php" class="formulario">

        <input type="hidden" name="id" value="<?= $movimiento['id'] ?>">

        <div class="campo">
            <label>Huésped:</label>
            <input type="text" value="<?= htmlspecialchars($movimiento['nombre_huesped']) ?>" disabled>
        </div>

        <div class="campo">
            <label>Habitación:</label>
            <input type="text" value="Habitación <?= htmlspecialchars($movimiento['numero_habitacion']) ?>" disabled>
        </div>

        <div class="campo">
            <label for="fecha_check_in">Fecha Check-In</label>
            <input type="date"
                   name="fecha_check_in"
                   id="fecha_check_in"
                   required
                   value="<?= $movimiento['fecha_check_in'] ?>">
        </div>

        <div class="campo">
            <label for="fecha_check_out">Fecha Check-Out</label>
            <input type="date"
                   name="fecha_check_out"
                   id="fecha_check_out"
                   required
                   value="<?= $movimiento['fecha_check_out'] ?>">
        </div>

        <div class="campo">
            <label for="notas">Notas (opcional)</label>
            <textarea name="notas" id="notas"><?= htmlspecialchars($movimiento['notas'] ?? "") ?></textarea>
        </div>

        <button type="submit" class="btn-primario">Guardar Cambios</button>
        <a href="../public/movimientos.php" class="btn-secundario">Cancelar</a>

    </form>
</div>

<?php include("../views/layout/footer.php"); ?>
