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
    <link rel="stylesheet" href="/Hotel-RP/public/assets/css/style.css">
    
    
    <?php if (basename($_SERVER['PHP_SELF']) === 'habitaciones.php'): ?>
    <link rel="stylesheet" href="/Hotel-RP/public/assets/css/habitaciones.css">
    <?php endif; ?>

    <link rel="stylesheet" href="/Hotel-RP/public/assets/css/reservas.css">
    <link rel="stylesheet" href="/Hotel-RP/public/assets/css/usuarios.css">



</head>

<body>
<div class="main-wrapper">
    <div class="sidebar">
        <h2>Hotel RP</h2>

        <ul>
            <li><a href="../public/dashboard.php">Dashboard</a></li>
            <li><a href="../controllers/habitaciones.php">Habitaciones</a></li>
            <li><a href="/Hotel-RP/controllers/reservas.php">Reservas</a></li>
            <li><a href="../public/calendario.php">Calendario</a></li>
            <?php if ($user['rol'] === 'admin'): ?>
                <li><a href="../controllers/usuarios.php">Usuarios</a></li>
                <li><a href="../controllers/reportes.php">Reportes</a></li>
            <?php endif; ?>

            <li><a href="../controllers/logout.php">Cerrar sesión</a></li>
        </ul>
    </div>

    <div class="main-content">
