<?php
// ── POST /usuarios/crear.php ──────────────────────────────────
// Body JSON: { nombre, apellido, correo, usuario, contrasena, id_rol }
// Requiere Bearer token con rol Administrador o Recepcionista

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error(405, 'Método no permitido');

$auth = getAuthUser();
if (!in_array($auth['rol'], ['Administrador', 'Recepcionista'])) {
    error(403, 'Sin permisos para crear usuarios');
}

$body = json_decode(file_get_contents('php://input'), true);

// Acepta nombre_completo o nombre+apellido
$nombre_completo = trim($body['nombre_completo'] ?? '');
if ($nombre_completo === '') {
    $n = trim($body['nombre']   ?? '');
    $a = trim($body['apellido'] ?? '');
    $nombre_completo = trim("$n $a");
}
$correo    = trim($body['correo']     ?? '');
$usuario   = trim($body['usuario']    ?? '');
$contrasena = $body['contrasena']     ?? '';
$id_rol    = (int)($body['id_rol']    ?? 0);

// Validaciones básicas
if (!$nombre_completo || !$usuario || !$contrasena || !$id_rol) {
    error(400, 'Faltan campos requeridos: nombre_completo, usuario, contrasena, id_rol');
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    error(400, 'Correo inválido');
}
if (strlen($contrasena) < 8) {
    error(400, 'La contraseña debe tener al menos 8 caracteres');
}

try {
    $db = getDB();

    // Verificar duplicados
    $dup = $db->prepare('SELECT id_usuario FROM usuarios WHERE usuario = :u OR correo = :c LIMIT 1');
    $dup->execute([':u' => $usuario, ':c' => $correo]);
    if ($dup->fetch()) {
        error(409, 'Usuario o correo ya registrado');
    }

    $hash = password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => 12]);

    $ins = $db->prepare("
        INSERT INTO usuarios (nombre_completo, correo, usuario, contrasena, id_rol, estado)
        VALUES (:nombre, :correo, :usuario, :contrasena, :id_rol, 'activo')
    ");
    $ins->execute([
        ':nombre'     => $nombre_completo,
        ':correo'     => $correo !== '' ? $correo : null,
        ':usuario'    => $usuario,
        ':contrasena' => $hash,
        ':id_rol'     => $id_rol,
    ]);

    $newId = (int) $db->lastInsertId();
    ok(['id_usuario' => $newId, 'mensaje' => 'Usuario creado correctamente']);

} catch (PDOException $e) {
    error(500, 'Error de base de datos: ' . $e->getMessage());
}
