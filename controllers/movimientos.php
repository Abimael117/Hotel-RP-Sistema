<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");
require_once("../models/Movimiento.php");

$movModel = new Movimiento($conn);

// ===============================
// Obtener mes y año desde GET
// ===============================
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : date("Y");
$mes  = isset($_GET['mes'])  ? intval($_GET['mes'])  : date("m");

// ===============================
// Obtener movimientos del mes
// ===============================
$movimientos = $movModel->listarPorMes($anio, $mes);

// ===============================
// Incluir la vista
// ===============================
include("../public/movimientos.php");
