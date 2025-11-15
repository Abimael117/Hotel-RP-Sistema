<?php
$pwd = "AdMiN";
$hash = password_hash($pwd, PASSWORD_DEFAULT);

echo "<p>Contraseña en texto plano: <strong>{$pwd}</strong></p>";
echo "<p>Hash generado:</p>";
echo "<code>{$hash}</code>";