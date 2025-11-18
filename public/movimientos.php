<?php
include("../views/layout/header.php");
?>

<link rel="stylesheet" href="../public/assets/css/style.css">
<link rel="stylesheet" href="../public/assets/css/movimientos.css">

<div class="container">

    <h1 class="titulo-modulo">Movimientos</h1>
    <p class="descripcion-modulo">Gestiona estancias, reservas y su estado en tiempo real.</p>

    <!-- ============================
         FILTRO DE MES / AÑO
    ============================ -->
    <form class="filtros-form" method="GET" action="movimientos.php">

        <div class="filtros-grid">

            <!-- Select de Mes -->
            <div>
                <label for="mes">Mes</label>
                <select name="mes" id="mes" class="select-input">
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $selected = ($m == $mes) ? "selected" : "";
                        echo "<option value='$m' $selected>" . date("F", mktime(0, 0, 0, $m, 1)) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Select de Año -->
            <div>
                <label for="anio">Año</label>
                <select name="anio" id="anio" class="select-input">
                    <?php
                    $anioActual = date("Y");
                    for ($a = $anioActual - 3; $a <= $anioActual + 1; $a++) {
                        $selected = ($a == $anio) ? "selected" : "";
                        echo "<option value='$a' $selected>$a</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Botón Filtrar -->
            <div class="btn-filtrar-container">
                <button type="submit" class="btn-filtrar">Filtrar</button>
            </div>

        </div>
    </form>

    <!-- ============================
         BOTONES DE ACCIÓN GENERAL
    ============================ -->
    <div class="acciones-generales">
        <a href="crear_estancia.php" class="btn-primario">Nueva Estancia</a>
        <a href="crear_reserva.php" class="btn-secundario">Nueva Reserva</a>
    </div>

    <!-- ============================
         TABLA DE MOVIMIENTOS
    ============================ -->
    <div class="tabla-container">

        <table class="tabla-movimientos">
            <thead>
                <tr>
                    <th>Huésped</th>
                    <th>Habitación</th>
                    <th>Fechas</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($movimientos)) : ?>
                    <tr>
                        <td colspan="7" class="tabla-vacio">
                            No hay movimientos registrados en este mes.
                        </td>
                    </tr>
                <?php else : ?>

                    <?php foreach ($movimientos as $mov) : ?>

                        <tr>

                            <td><?= htmlspecialchars($mov['nombre_huesped']) ?></td>

                            <td>
                                Habitación <?= htmlspecialchars($mov['numero_habitacion']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($mov['fecha_check_in']) ?>
                                —
                                <?= htmlspecialchars($mov['fecha_check_out']) ?>
                            </td>

                            <td>
                                <span class="tag tipo-<?= strtolower($mov['tipo_ocupacion']) ?>">
                                    <?= htmlspecialchars($mov['tipo_ocupacion']) ?>
                                </span>
                            </td>

                            <td>
                                <span class="tag estado-<?= strtolower($mov['estado']) ?>">
                                    <?= htmlspecialchars($mov['estado']) ?>
                                </span>
                            </td>

                            <td>
                                $<?= number_format($mov['total'], 2) ?>
                            </td>

                            <td class="td-acciones">

                                <?php if ($mov['tipo_ocupacion'] === "Reserva" && $mov['estado'] === "Reservada") : ?>
                                    <a href="activar_reserva.php?id=<?= $mov['id'] ?>" class="accion-btn accion-activar">
                                        Activar
                                    </a>
                                    <a href="editar_reserva.php?id=<?= $mov['id'] ?>" class="accion-btn accion-editar">
                                        Editar
                                    </a>
                                    <a href="cancelar_reserva.php?id=<?= $mov['id'] ?>" class="accion-btn accion-eliminar">
                                        Eliminar
                                    </a>
                                <?php endif; ?>

                                <?php if ($mov['estado'] === "Activa") : ?>
                                    <a href="finalizar_estancia.php?id=<?= $mov['id'] ?>" class="accion-btn accion-finalizar">
                                        Finalizar
                                    </a>
                                <?php endif; ?>


                                <a href="ver_movimiento.php?id=<?= $mov['id'] ?>" class="accion-btn accion-ver">
                                    Ver
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>
            </tbody>

        </table>

    </div>

</div>

<?php include("../views/layout/footer.php"); ?>
