<?php
$error = isset($_GET['error']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Hotel Real Primaveras - Login</title>

    <!-- Aquí cargamos SOLO el CSS del login -->
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <div class="login-card">

        <h2>Hotel Real Primaveras</h2>
        <p>Ingresa tus credenciales para acceder al panel.</p>

        <?php if ($error): ?>
            <div class="error-msg">Credenciales incorrectas</div>
        <?php endif; ?>

        <form action="../controllers/auth.php" method="POST">

            <label>Usuario:</label>
            <input type="text" name="email" placeholder="admin" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <button type="submit">Iniciar Sesión</button>
        </form>

    </div>

</body>
</html>
