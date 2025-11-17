<?php 
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user"])) {
    echo json_encode(["success" => false, "message" => "No autenticado"]);
    exit;
}

require_once("../config/db.php");

// =============================
// RECIBIR DATOS DEL FORM
// =============================
$huesped_id     = intval($_POST["huesped_id"] ?? 0);
$habitacion_id  = intval($_POST["habitacion_id"] ?? 0);
$fecha_in       = $_POST["fecha_check_in"] ?? "";
$fecha_out      = $_POST["fecha_check_out"] ?? "";
$ocupacion      = $_POST["ocupacion"] ?? ""; 
$personas_extra = isset($_POST["personas_extra"]) ? max(0, intval($_POST["personas_extra"])) : 0;
$metodo_pago    = $_POST["metodo_pago"] ?? "";

// NUEVO: Se recibe el descuento
$descuento10    = isset($_POST["descuento10"]) ? 1 : 0; // 1 = aplica, 0 = no aplica

// Validar ocupación
$ocupacionesPermitidas = ["sencilla", "doble"];
if (!in_array($ocupacion, $ocupacionesPermitidas, true)) {
    echo json_encode(["success" => false, "message" => "Ocupación inválida"]);
    exit;
}

// =============================
// VALIDAR FECHAS
// =============================
if (!$fecha_in || !$fecha_out || $fecha_in >= $fecha_out) {
    echo json_encode(["success" => false, "message" => "Rango de fechas inválido"]);
    exit;
}

// =============================
// VERIFICAR DISPONIBILIDAD
// =============================
$sqlCheck = "SELECT COUNT(*) AS total
             FROM reservas
             WHERE habitacion_id = ?
             AND estado = 'Activa'
             AND NOT (fecha_check_out <= ? OR fecha_check_in >= ?)";

$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("iss", $habitacion_id, $fecha_in, $fecha_out);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result()->fetch_assoc();
$stmtCheck->close();

if ($resCheck["total"] > 0) {
    echo json_encode(["success" => false, "message" => "La habitación ya está reservada en esas fechas"]);
    exit;
}

// =============================
// OBTENER TIPO Y PRECIO BASE
// =============================
$sqlPrecio = "SELECT tipo_id, precio_noche FROM habitaciones WHERE id = ?";
$stmtPrecio = $conn->prepare($sqlPrecio);
$stmtPrecio->bind_param("i", $habitacion_id);
$stmtPrecio->execute();
$resPrecio = $stmtPrecio->get_result()->fetch_assoc();
$stmtPrecio->close();

if (!$resPrecio) {
    echo json_encode(["success" => false, "message" => "Habitación inválida"]);
    exit;
}

$tipo_id     = intval($resPrecio["tipo_id"]);
$precio_base = floatval($resPrecio["precio_noche"]);

// =============================
// INTENTAR OBTENER PRECIO POR TIPO/OCUPACIÓN
// =============================
$sqlTP = "SELECT precio FROM tipos_precios WHERE tipo_id = ? AND ocupacion = ?";
$stmtTP = $conn->prepare($sqlTP);
$stmtTP->bind_param("is", $tipo_id, $ocupacion);
$stmtTP->execute();
$resTP = $stmtTP->get_result()->fetch_assoc();
$stmtTP->close();

$precio_noche = $resTP ? floatval($resTP["precio"]) : $precio_base;

// =============================
// CALCULAR TOTAL
// =============================
$d1 = new DateTime($fecha_in);
$d2 = new DateTime($fecha_out);
$interval = $d1->diff($d2);
$noches = $interval->days;

if ($noches <= 0) {
    echo json_encode(["success" => false, "message" => "Rango de fechas inválido"]);
    exit;
}

$incluidas = ($ocupacion === "sencilla") ? 1 : 2;
$numero_huesp = $incluidas + $personas_extra;
$costo_extra = $personas_extra * 100 * $noches;

$subtotal = ($noches * $precio_noche) + $costo_extra;

// =============================
// APLICAR DESCUENTO SI SE PIDIÓ
// =============================
$total = $subtotal;

if ($descuento10 === 1) {
    $total = $subtotal * 0.90; // aplica 10%
}

// =============================
// INSERTAR RESERVA
// =============================
$sqlInsert = "INSERT INTO reservas
              (huesped_id, habitacion_id, fecha_check_in, fecha_check_out,
               numero_huespedes, personas_extra, estado, total, metodo_pago, descuento_aplicado)
              VALUES (?, ?, ?, ?, ?, ?, 'Activa', ?, ?, ?)";

$stmt = $conn->prepare($sqlInsert);
$stmt->bind_param(
    "iissiidsi",
    $huesped_id,
    $habitacion_id,
    $fecha_in,
    $fecha_out,
    $numero_huesp,
    $personas_extra,
    $total,
    $metodo_pago,
    $descuento10
);

if ($stmt->execute()) {
    $idNew = $stmt->insert_id;
    $stmt->close();

    // =============================
    // ACTUALIZAR ESTADO DE HABITACIÓN
    // =============================
    $hoy = date("Y-m-d");

    if ($fecha_in == $hoy) {
        $sqlEstado = "UPDATE habitaciones SET estado='ocupado' WHERE id=?";
    } else {
        $sqlEstado = "UPDATE habitaciones SET estado='reservado' WHERE id=?";
    }

    $stmtEstado = $conn->prepare($sqlEstado);
    $stmtEstado->bind_param("i", $habitacion_id);
    $stmtEstado->execute();
    $stmtEstado->close();

    // =============================
    // OBTENER RESERVA COMPLETA
    // =============================
    $sql = "SELECT r.*,
                   h.numero AS habitacion_numero,
                   hu.nombre_completo AS huesped_nombre
            FROM reservas r
            JOIN habitaciones h ON r.habitacion_id = h.id
            JOIN huespedes hu ON r.huesped_id = hu.id
            WHERE r.id = ?";

    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("i", $idNew);
    $stmt2->execute();
    $res = $stmt2->get_result()->fetch_assoc();
    $stmt2->close();

    echo json_encode([
        "success" => true,
        "reserva" => $res
    ]);

} else {
    echo json_encode(["success" => false, "message" => "Error al guardar"]);
}
?>
