<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user"])) {
    echo json_encode(["success" => false, "message" => "No autenticado"]);
    exit;
}

if ($_SESSION["user"]["rol"] !== "admin") {
    echo json_encode(["success" => false, "message" => "Sin permisos"]);
    exit;
}

require_once("../config/db.php");

$nombre   = trim($_POST["nombre"] ?? "");
$email    = trim($_POST["email"] ?? "");
$rol      = trim($_POST["rol"] ?? "personal");
$password = $_POST["password"] ?? "";

if ($nombre === "" || $email === "" || $password === "") {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Email inválido"]);
    exit;
}

// Verificar que no exista email
$sqlCheck = "SELECT id FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(["success" => false, "message" => "El email ya está registrado"]);
    exit;
}
$stmt->close();

$hash = password_hash($password, PASSWORD_BCRYPT);

$sqlInsert = "INSERT INTO usuarios (nombre, email, password_hash, rol)
              VALUES (?, ?, ?, ?)";

$stmt2 = $conn->prepare($sqlInsert);
$stmt2->bind_param("ssss", $nombre, $email, $hash, $rol);

if ($stmt2->execute()) {
    $id = $stmt2->insert_id;
    $stmt2->close();

    echo json_encode([
        "success" => true,
        "usuario" => [
            "id"     => $id,
            "nombre" => $nombre,
            "email"  => $email,
            "rol"    => $rol
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Error al guardar"]);
}
