<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hotel RP</title>

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="/Hotel-RP/public/assets/css/style.css">

    <!-- CSS según la vista cargada -->
    <?php 
        $archivoActual = basename($_SERVER['PHP_SELF']);

        if ($archivoActual === 'habitaciones.php') {
            echo '<link rel="stylesheet" href="/Hotel-RP/public/assets/css/habitaciones.css">';
        }

        if ($archivoActual === 'usuarios.php') {
            echo '<link rel="stylesheet" href="/Hotel-RP/public/assets/css/usuarios.css">';
        }

        if ($archivoActual === 'movimientos.php') {
            echo '<link rel="stylesheet" href="/Hotel-RP/public/assets/css/movimientos.css">';
        }
    ?>
</head>

<body>

<div class="main-wrapper">
    <div class="sidebar">
        <h2>Hotel RP</h2>

        <ul>
            <!-- TODAS LAS RUTAS YA SON ABSOLUTAS -->
            <li><a href="/Hotel-RP/public/dashboard.php">Dashboard</a></li>
            <li><a href="/Hotel-RP/controllers/habitaciones.php">Habitaciones</a></li>
            <li><a href="/Hotel-RP/controllers/movimientos.php">Estancias/Reservas</a></li>
            <li><a href="/Hotel-RP/public/calendario.php">Calendario</a></li>

            <?php if ($user && $user['rol'] === 'admin'): ?>
                <li><a href="/Hotel-RP/controllers/usuarios.php">Usuarios</a></li>
                <!-- <li><a href="/Hotel-RP/controllers/reportes.php">Reportes</a></li> -->
            <?php endif; ?>

            <li><a href="/Hotel-RP/controllers/logout.php">Cerrar sesión</a></li>
        </ul>
    </div>

    <div class="main-content">
