<?php
// Siempre aseguramos sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay usuario logueado, mandamos al login
if (!isset($_SESSION["user"])) {
    header("Location: ../public/index.php");
    exit;
}

// ------------------------------
//  PROTECCIÓN POR ROLES (OPCIÓN 2)
// ------------------------------

// Archivos que SOLO un admin puede ver
$soloAdmin = [
    "usuarios.php",
    "reportes.php",
    "add_usuario.php",
    "update_usuario.php",
    "delete_usuario.php"
];

// Detectamos cuál archivo se está ejecutando
$archivoActual = basename($_SERVER["PHP_SELF"]);

// Si el archivo requiere admin y NO es admin → fuera
if (in_array($archivoActual, $soloAdmin)) {
    if ($_SESSION["user"]["rol"] !== "admin") {
        header("Location: ../public/dashboard.php?error=permiso");
        exit;
    }
}
