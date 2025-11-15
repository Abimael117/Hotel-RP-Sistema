<?php
$host = "localhost";  
$user = "root";        // cámbialo si tu usuario MySQL no es root
$pass = "";            // cámbialo si tienes contraseña
$dbname = "bd_hotel";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");