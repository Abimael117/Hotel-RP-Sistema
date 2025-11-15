<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: ../public/index.php");
    exit;
}

// Solo admin puede ver usuarios
if ($_SESSION["user"]["rol"] !== "admin") {
    header("Location: ../public/dashboard.php");
    exit;
}

require_once("../config/db.php");

// Obtener todos los usuarios
$sql = "SELECT id, nombre, email, rol, avatar_base64, fecha_creacion
        FROM usuarios
        ORDER BY fecha_creacion DESC";

$usuarios = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Vista
include("../public/usuarios.php");
