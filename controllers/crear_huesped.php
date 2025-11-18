<?php
require_once("../config/auth_middleware.php");
require_once("../config/db.php");

$errores = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre_completo"]);
    $email  = trim($_POST["email"]);
    $tel    = trim($_POST["telefono"]);

    // Validación básica
    if (!$nombre) $errores[] = "El nombre es obligatorio.";

    if (empty($errores)) {

        $sql = "INSERT INTO huespedes (nombre_completo, email, telefono, fecha_registro)
                VALUES (?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $nombre, $email, $tel);

        if ($stmt->execute()) {

            echo "<script>
                    alert('Huésped creado correctamente');

                    // Recarga la ventana padre (Nueva Estancia o Nueva Reserva)
                    if (window.opener) {
                        window.opener.location.reload();
                    }

                    // Cierra este popup
                    window.close();
                  </script>";
            exit;
        } else {
            $errores[] = "Error al guardar: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Cargar la vista (formulario con estilo)
include("../public/form_huesped.php");
?>
