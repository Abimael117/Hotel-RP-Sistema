<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: index.php");
    exit;
}

$nombre = $_SESSION["user"]["nombre"];
$rol = $_SESSION["user"]["rol"];

include("../views/layout/header.php");
?>

<h1>Bienvenido, <?php echo htmlspecialchars($nombre); ?> 👋</h1>

<p>Tu rol es: <strong><?php echo $rol; ?></strong></p>

<?php
include("../views/layout/footer.php");
?>