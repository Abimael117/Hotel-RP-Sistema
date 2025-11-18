<?php include("../views/layout/popup_header.php"); ?>

<div class="popup-container">

    <h1>Nuevo Huésped</h1>
    <p>Registrar un nuevo huésped en el sistema.</p>

    <?php if (!empty($errores)) : ?>
        <div class="alerta-error" style="margin-bottom: 15px;">
            <?php foreach ($errores as $e) : ?>
                <p><?= $e ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label>Nombre completo</label>
        <input type="text" name="nombre_completo" required>

        <label>Email</label>
        <input type="email" name="email">

        <label>Teléfono</label>
        <input type="text" name="telefono">

        <button class="popup-btn">Guardar</button>
    </form>

</div>

<?php include("../views/layout/popup_footer.php"); ?>
