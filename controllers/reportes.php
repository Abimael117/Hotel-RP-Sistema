<?php
require_once("../config/auth_middleware.php"); // <-- NUEVO
require_once("../config/db.php");


// ==========================================
// INGRESOS POR MES (últimos 6 meses)
// ==========================================

$sqlIngresos = "
    SELECT 
        DATE_FORMAT(fecha_check_in, '%Y-%m') AS mes,
        SUM(total) AS ingresos
    FROM reservas
    WHERE estado='Activa'
      AND fecha_check_in >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes ASC
";

$resIngresos = $conn->query($sqlIngresos);
$ingresosMes = [];
while ($row = $resIngresos->fetch_assoc()) {
    $ingresosMes[] = $row;
}

// ==========================================
// TENDENCIA DE OCUPACIÓN (últimos 7 días)
// ==========================================

$dias = [];
$ocupacion = [];

for ($i = 6; $i >= 0; $i--) {
    $dia = date("Y-m-d", strtotime("-$i days"));
    $dias[] = $dia;

    $sqlOcup = "
        SELECT COUNT(*) AS ocupadas
        FROM reservas
        WHERE estado='Activa'
        AND '$dia' BETWEEN fecha_check_in AND fecha_check_out
    ";

    $res = $conn->query($sqlOcup);
    $ocup = $res->fetch_assoc()['ocupadas'] ?? 0;

    $ocupacion[] = $ocup;
}

// Total habitaciones
$totalHabitaciones = $conn->query("SELECT COUNT(*) AS total FROM habitaciones")
                          ->fetch_assoc()['total'];

include("../public/reportes.php");
