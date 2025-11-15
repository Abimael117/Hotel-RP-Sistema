<?php
session_start();

require_once("../config/db.php");
require_once("../models/Usuario.php");

$usuarioModel = new Usuario($conn);

$email = trim($_POST['email'] ?? "");
$password = trim($_POST['password'] ?? "");

$user = $usuarioModel->login($email, $password);

if ($user) {

    // Guardar datos mínimos en sesión
    $_SESSION["user"] = [
        "id" => $user["id"],
        "nombre" => $user["nombre"],
        "email" => $user["email"],
        "rol" => $user["rol"]
    ];

    header("Location: ../public/dashboard.php");
    exit;

} else {
    header("Location: ../public/index.php?error=1");
    exit;
}