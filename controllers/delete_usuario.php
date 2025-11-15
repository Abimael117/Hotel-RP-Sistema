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

$id = intval($_POST["id"] ?? 0);

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "ID inválido"]);
    exit;
}

// No permitir eliminarse a sí mismo
if ($id === $_SESSION["user"]["id"]) {
    echo json_encode(["success" => false, "message" => "No puedes eliminar tu propio usuario"]);
    exit;
}

// (Opcional) evitar borrar el último admin
$sqlAdmins = "SELECT COUNT(*) AS total FROM usuarios WHERE rol = 'admin' AND id <> ?";
$stmt = $conn->prepare($sqlAdmins);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($res["total"] == 0) {
    echo json_encode(["success" => false, "message" => "No puedes eliminar al último admin"]);
    exit;
}

$sqlDel = "DELETE FROM usuarios WHERE id = ?";
$stmt2 = $conn->prepare($sqlDel);
$stmt2->bind_param("i", $id);

if ($stmt2->execute()) {
    $stmt2->close();
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error al eliminar"]);
}
