<?php
// ── Generador de hash bcrypt para contraseñas ─────────────────
// USO: http://localhost/odontogest_api/tools/generar_hash.php?pass=TuContrasenia
// SOLO para desarrollo/pruebas — eliminar en producción

if (!isset($_GET['pass']) || trim($_GET['pass']) === '') {
    echo '<h3>Uso:</h3>';
    echo '<code>http://localhost/odontogest_api/tools/generar_hash.php?pass=TuContrasenia</code>';
    exit;
}

$password = $_GET['pass'];
$hash     = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

header('Content-Type: text/plain');
echo "Contraseña:  $password\n";
echo "Hash bcrypt: $hash\n\n";
echo "-- Copia este UPDATE en HeidiSQL:\n";
echo "UPDATE odonto_gest.usuarios SET contrasena = '$hash' WHERE usuario = 'TU_USUARIO';";
