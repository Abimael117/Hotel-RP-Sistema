<?php include("../views/layout/header.php"); ?>

<link rel="stylesheet" href="../public/assets/css/style.css">
<link rel="stylesheet" href="../public/assets/css/movimientos.css">

<div class="container">

    <h1 class="titulo-modulo">Nueva Reserva</h1>
    <p class="descripcion-modulo">Registrar una reserva futura.</p>

    <!-- Mostrar errores -->
    <?php if (!empty($errores)): ?>
        <div class="alerta-error">
            <ul>
                <?php foreach ($errores as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="crear_reserva.php" class="formulario-mov">

        <div class="form-grid">

            <!-- HABITACIÓN -->
            <div class="form-group">
                <label>Habitación</label>
                <select name="habitacion_id" required>
                    <option value="">Seleccionar habitación…</option>

                    <?php foreach ($habitaciones as $hab): ?>
                        <option value="<?= $hab['id'] ?>">
                            <?= "Habitación " . $hab['numero'] . " (" . $hab['estado'] . ")" ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- HUESPED -->
            <div class="form-group">
                <label>Huésped</label>
                <select name="huesped_id" required>
                    <option value="">Seleccionar huésped…</option>

                    <?php foreach ($huespedes as $hu): ?>
                        <option value="<?= $hu['id'] ?>">
                            <?= $hu['nombre_completo'] ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- CHECK-IN -->
            <div class="form-group">
                <label>Fecha de Check-in (futuro)</label>
                <input
                    type="date"
                    name="fecha_check_in"
                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                    required
                >
            </div>

            <!-- CHECK-OUT -->
            <div class="form-group">
                <label>Fecha de Check-out</label>
                <input
                    type="date"
                    name="fecha_check_out"
                    required
                >
            </div>

            <!-- NUM HUESPEDES -->
            <div class="form-group">
                <label>Número de huéspedes</label>
                <input type="number" name="numero_huespedes" min="1" value="1" required>
            </div>

            <!-- PERSONAS EXTRA -->
            <div class="form-group">
                <label>Personas extra</label>
                <input type="number" name="personas_extra" min="0" value="0">
            </div>

            <!-- TOTAL -->
            <div class="form-group">
                <label>Total ($)</label>
                <input type="number" step="0.01" name="total" min="0" value="0" required>
            </div>

            <!-- MÉTODO DE PAGO -->
            <div class="form-group">
                <label>Método de pago</label>
                <select name="metodo_pago" required>
                    <option value="">Selecciona método…</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta">Tarjeta</option>
                    <option value="Transferencia">Transferencia</option>
                </select>
            </div>

            <!-- DESCUENTO -->
            <div class="form-group">
                <label>Descuento aplicado</label>
                <input type="number" step="0.01" name="descuento_aplicado" min="0" value="0">
            </div>

        </div>

        <div class="acciones-form">
            <a href="movimientos.php" class="btn-secundario">Cancelar</a>
            <button type="submit" class="btn-primario">Guardar Reserva</button>
        </div>

    </form>
</div>

<?php include("../views/layout/footer.php"); ?>
