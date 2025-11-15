<?php
header("Content-Type: application/json");
require_once("../config/db.php");

$nombre = $_POST["nombre_completo"] ?? "";
$telefono = $_POST["telefono"] ?? "";
$email = $_POST["email"] ?? "";

if (trim($nombre) === "") {
    echo json_encode(["success" => false, "message" => "Nombre requerido"]);
    exit;
}

$sql = "INSERT INTO huespedes (nombre_completo, telefono, email)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nombre, $telefono, $email);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "huesped" => [
            "id" => $stmt->insert_id,
            "nombre_completo" => $nombre
        ]
    ]);

    exit;
}

echo json_encode(["success" => false, "message" => "Error al guardar"]);
