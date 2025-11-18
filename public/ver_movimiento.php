<?php include("../views/layout/header.php"); ?>

<link rel="stylesheet" href="../public/assets/css/style.css">
<link rel="stylesheet" href="../public/assets/css/movimientos.css">

<div class="container">

    <h1 class="titulo-modulo">Detalles del Movimiento</h1>

    <div class="card-detalles">

        <!-- Tipo y estado -->
        <div class="detalle-row titulo">
            <h2><?= htmlspecialchars($mov['tipo_ocupacion']) ?></h2>
            <span class="tag estado-<?= strtolower($mov['estado']) ?>">
                <?= htmlspecialchars($mov['estado']) ?>
            </span>
        </div>

        <!-- Información del huésped -->
        <div class="detalle-section">
            <h3>Huésped</h3>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($huesped['nombre_completo']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($huesped['telefono']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($huesped['email']) ?></p>
        </div>

        <!-- Información de habitación -->
        <div class="detalle-section">
            <h3>Habitación</h3>
            <p><strong>Número:</strong> <?= htmlspecialchars($habitacion['numero']) ?></p>
            <p><strong>Tipo:</strong> <?= htmlspecialchars($habitacion['tipo_nombre']) ?></p>
            <p><strong>Estado actual:</strong>
                <span class="tag estado-<?= strtolower($habitacion['estado']) ?>">
                    <?= htmlspecialchars($habitacion['estado']) ?>
                </span>
            </p>
        </div>

        <!-- Fechas -->
        <div class="detalle-section">
            <h3>Fechas</h3>
            <p><strong>Check-in:</strong> <?= htmlspecialchars($mov['fecha_check_in']) ?></p>
            <p><strong>Check-out:</strong> <?= htmlspecialchars($mov['fecha_check_out']) ?></p>
            <p><strong>Fecha de creación:</strong> <?= htmlspecialchars($mov['fecha_creacion']) ?></p>
        </div>

        <!-- Información del movimiento -->
        <div class="detalle-section">
            <h3>Ocupación</h3>
            <p><strong>Número de huéspedes:</strong> <?= htmlspecialchars($mov['numero_huespedes']) ?></p>
            <p><strong>Personas extra:</strong> <?= htmlspecialchars($mov['personas_extra']) ?></p>
        </div>

        <!-- Pagos -->
        <div class="detalle-section">
            <h3>Pago</h3>
            <p><strong>Total:</strong> $<?= number_format($mov['total'], 2) ?></p>
            <p><strong>Método de pago:</strong> <?= htmlspecialchars($mov['metodo_pago']) ?></p>
            <p><strong>Descuento aplicado:</strong> $<?= number_format($mov['descuento_aplicado'], 2) ?></p>
        </div>

    </div>

    <!-- Acciones -->
    <div class="acciones-form">
        <a href="movimientos.php" class="btn-secundario">Volver</a>

        <?php if ($mov['tipo_ocupacion'] === "Reserva" && $mov['estado'] === "Reservada"): ?>
            <a href="activar_reserva.php?id=<?= $mov['id'] ?>" class="btn-primario">Activar Reserva</a>
        <?php endif; ?>

        <?php if ($mov['tipo_ocupacion'] === "Estancia" && $mov['estado'] === "Activa"): ?>
            <a href="finalizar_estancia.php?id=<?= $mov['id'] ?>" class="btn-primario">Finalizar Estancia</a>
        <?php endif; ?>
    </div>

</div>

<?php include("../views/layout/footer.php"); ?>
