<?php
include("../views/layout/header.php");
?>

<link rel="stylesheet" href="assets/css/reportes.css">

<h1>Reportes</h1>

<div class="reportes-grid">

    <!-- INGRESOS POR MES -->
    <div class="card-grafica">
        <h2>Ingresos por Mes</h2>
        <canvas id="grafIngresos"></canvas>
    </div>

    <!-- TENDENCIA DE OCUPACIÓN -->
    <div class="card-grafica">
        <h2>Tendencia de Ocupación</h2>
        <canvas id="grafOcupacion"></canvas>
    </div>

</div>

<script>
    const datosIngresos = <?= json_encode($ingresosMes); ?>;
    const dias = <?= json_encode($dias); ?>;
    const ocupacion = <?= json_encode($ocupacion); ?>;
    const totalHabitaciones = <?= $totalHabitaciones; ?>;
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/reportes.js"></script>

<?php include("../views/layout/footer.php"); ?>
