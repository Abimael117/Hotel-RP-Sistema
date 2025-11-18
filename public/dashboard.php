<?php
require_once("../config/auth_middleware.php"); // <-- NUEVO
require_once("../config/db.php");


// ==========================================
// MÉTRICAS PRINCIPALES
// ==========================================

// Total ingresos últimos 30 días
$ingresos = $conn->query("
    SELECT IFNULL(SUM(total),0) AS total 
    FROM reservas 
    WHERE estado='Activa'
    AND fecha_check_in >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['total'];

// Total de reservas
$totalReservas = $conn->query("
    SELECT COUNT(*) AS total
    FROM reservas
    WHERE tipo_ocupacion = 'Reserva'
      AND MONTH(fecha_check_in) = MONTH(CURDATE())
      AND YEAR(fecha_check_in) = YEAR(CURDATE())
")->fetch_assoc()['total'];




// Total habitaciones
$habitacionesTotales = $conn->query("
    SELECT COUNT(*) AS total 
    FROM habitaciones
")->fetch_assoc()['total'];

// Habitaciones disponibles (REAL)
$habDisponibles = $conn->query("
    SELECT COUNT(*) AS total 
    FROM habitaciones 
    WHERE estado = 'disponible'
")->fetch_assoc()['total'];

// Habitaciones ocupadas (REAL)
$habOcupadas = $habitacionesTotales - $habDisponibles;

// Tasa de ocupación REAL
$tasaOcupacion = ($habitacionesTotales > 0)
    ? round(($habOcupadas / $habitacionesTotales) * 100, 1)
    : 0;

// Huéspedes principales
$huespedesTop = $conn->query("
    SELECT hu.nombre_completo, SUM(r.total) AS gasto
    FROM reservas r
    JOIN huespedes hu ON hu.id = r.huesped_id
    WHERE r.estado='Activa'
    GROUP BY hu.id
    ORDER BY gasto DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Actividad reciente (mientras no exista la tabla de actividad REAL)
$actividad = $conn->query("
    SELECT CONCAT('Reserva ', id) AS titulo,
           fecha_creacion
    FROM reservas
    ORDER BY fecha_creacion DESC
    LIMIT 6
")->fetch_all(MYSQLI_ASSOC);


include("../views/layout/header.php");
?>

<link rel="stylesheet" href="/Hotel-RP/public/assets/css/dashboard.css">

<h1>Panel de Control</h1>

<div class="dashboard-grid">

    <div class="card-metric">
        <p class="metric-title">Ingresos Totales</p>
        <h2 class="metric-value">$<?= number_format($ingresos, 2); ?></h2>
    </div>

    <div class="card-metric">
        <p class="metric-title">Reservas Mes Actual</p>
        <h2 class="metric-value"><?= $totalReservas; ?></h2>
    </div>

    <div class="card-metric">
        <p class="metric-title">Tasa de Ocupación</p>
        <h2 class="metric-value"><?= $tasaOcupacion; ?>%</h2>
    </div>

    <div class="card-metric">
        <p class="metric-title">Habitaciones Disponibles</p>
        <h2 class="metric-value"><?= $habDisponibles; ?></h2>
    </div>

</div>

<!-- ===================== -->
<!-- HUÉSPEDES PRINCIPALES -->
<!-- ===================== -->

<div class="section">
    <h2>Huéspedes Principales</h2>

    <table class="table">
        <tr><th>Huésped</th><th>Monto Gastado</th></tr>

        <?php if (empty($huespedesTop)) : ?>
            <tr><td colspan="2">No hay datos de huéspedes principales.</td></tr>
        <?php else: ?>
            <?php foreach ($huespedesTop as $h): ?>
                <tr>
                    <td><?= $h['nombre_completo']; ?></td>
                    <td>$<?= number_format($h['gasto'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>

<!-- ===================== -->
<!-- ACTIVIDAD RECIENTE -->
<!-- ===================== -->

<div class="section">
    <h2>Actividad Reciente</h2>

    <div class="activity-list">
        <?php foreach ($actividad as $a): ?>
        <div class="activity-item">
            <div class="activity-dot"></div>
            <div>
                <p class="activity-text"><?= $a['titulo']; ?></p>
                <span class="activity-time"><?= $a['fecha_creacion']; ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include("../views/layout/footer.php"); ?>
