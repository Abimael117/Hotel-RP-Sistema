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

$id      = intval($_POST["id"] ?? 0);
$nombre  = trim($_POST["nombre"] ?? "");
$email   = trim($_POST["email"] ?? "");
$rol     = trim($_POST["rol"] ?? "personal");
$passNew = $_POST["password"] ?? "";

if ($id <= 0 || $nombre === "" || $email === "") {
    echo json_encode(["success" => false, "message" => "Datos inválidos"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Email inválido"]);
    exit;
}

// Verificar que no se repita email en otro usuario
$sqlCheck = "SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1";
$stmt = $conn->prepare($sqlCheck);
$stmt->bind_param("si", $email, $id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    echo json_encode(["success" => false, "message" => "El email ya está en uso por otro usuario"]);
    exit;
}
$stmt->close();

// Armar UPDATE dinámico
if ($passNew !== "") {
    $hash = password_hash($passNew, PASSWORD_BCRYPT);
    $sql = "UPDATE usuarios
            SET nombre = ?, email = ?, rol = ?, password_hash = ?
            WHERE id = ?";
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("ssssi", $nombre, $email, $rol, $hash, $id);
} else {
    $sql = "UPDATE usuarios
            SET nombre = ?, email = ?, rol = ?
            WHERE id = ?";
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("sssi", $nombre, $email, $rol, $id);
}

if ($stmt2->execute()) {
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
    echo json_encode(["success" => false, "message" => "Error al actualizar"]);
}
