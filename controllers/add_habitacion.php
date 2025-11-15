<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user"])) {
    echo json_encode(["success" => false, "message" => "No autenticado"]);
    exit;
}

require_once("../config/db.php");

// Recibir datos
$numero        = $_POST["numero"];
$tipo_id       = $_POST["tipo_id"];
$capacidad     = $_POST["capacidad"];
$precio_noche  = $_POST["precio_noche"];
$estado        = $_POST["estado"];

// Guardar
$sql = "INSERT INTO habitaciones (numero, tipo_id, capacidad, precio_noche, estado)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("siids", $numero, $tipo_id, $capacidad, $precio_noche, $estado);

if ($stmt->execute()) {

    // Obtener tipo nombre
    $tipoRes = $conn->query("SELECT nombre FROM tipos_habitacion WHERE id = $tipo_id");
    $tipoRow = $tipoRes->fetch_assoc();

    echo json_encode([
        "success" => true,
        "habitacion" => [
            "numero" => $numero,
            "tipo_nombre" => $tipoRow["nombre"],
            "capacidad" => $capacidad,
            "precio_noche" => $precio_noche,
            "estado" => $estado
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Error al guardar"]);
}

$stmt->close();
$conn->close();
