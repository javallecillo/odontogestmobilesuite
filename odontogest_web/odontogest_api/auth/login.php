<?php
// ── POST /odontogest_api/auth/login ───────────────────────────
// Body JSON: { "usuario": "JhairRios", "contrasena": "JhairRios10" }
// Respuesta OK: { "success": true, "token": "...", "rol": "...", "nombre": "..." }
// Respuesta error: { "success": false, "mensaje": "..." }

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Response.php';

// Registro sencillo para debugging: escribir método, URI y body entrante.
// Archivo: odontogest_web/odontogest_api/debug.log (asegúrate de permisos de escritura)
try {
    $debugPath = __DIR__ . '/../debug.log';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $headers = function_exists('getallheaders') ? json_encode(getallheaders()) : '';
    $bodyRaw = file_get_contents('php://input');
    @file_put_contents($debugPath, date('c') . " | $method $uri | headers: $headers | body: " . substr($bodyRaw, 0, 2000) . "\n", FILE_APPEND);
} catch (Exception $e) {
    // no bloquear si falla el logging
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error(405, 'Método no permitido');
}

// ── Leer body JSON ────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true);

if (!isset($body['usuario'], $body['contrasena'])) {
    error(400, 'Campos requeridos: usuario, contrasena');
}

$usuario   = trim($body['usuario']);
$contrasena = $body['contrasena'];

if ($usuario === '' || $contrasena === '') {
    error(400, 'Usuario y contraseña no pueden estar vacíos');
}

// ── Buscar usuario en la BD ───────────────────────────────────
try {
    $db  = getDB();
    $sql = "SELECT u.id_usuario, u.usuario, u.contrasena,
                   u.nombre_completo, u.correo,
                   COALESCE(u.telefono, '') AS telefono,
                   u.estado,
                   r.nombre AS rol
            FROM usuarios u
            JOIN roles r ON r.id_rol = u.id_rol
            WHERE u.usuario = :usuario
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([':usuario' => $usuario]);
    $user = $stmt->fetch();

} catch (PDOException $e) {
    error(500, 'Error de base de datos');
}

// ── Validaciones ──────────────────────────────────────────────
if (!$user) {
    error(401, 'Credenciales incorrectas');
}

if ($user['estado'] !== 'activo') {
    error(403, 'Cuenta inactiva o bloqueada. Contacta al administrador.');
}

if (!password_verify($contrasena, $user['contrasena'])) {
    error(401, 'Credenciales incorrectas');
}

// ── Generar token Bearer ──────────────────────────────────────
// Token simple para pruebas: base64(id_usuario|rol|timestamp|random)
// En producción usar JWT con firma HMAC-SHA256
$payload = $user['id_usuario'] . '|' . $user['rol'] . '|' . time();
$token   = base64_encode($payload . '|' . bin2hex(random_bytes(16)));

// ── Actualizar último login ────────────────────────────────────
try {
    $db->prepare('UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = :id')
       ->execute([':id' => $user['id_usuario']]);
} catch (PDOException $e) {
    // No crítico — continuar
}

// ── Respuesta exitosa ─────────────────────────────────────────
ok([
    'token'      => $token,
    'rol'        => $user['rol'],
    'nombre'     => $user['nombre_completo'],
    'id_usuario' => $user['id_usuario'],
    'usuario'    => $user['usuario'],
    'correo'     => $user['correo']   ?? '',
    'telefono'   => $user['telefono'] ?? '',
]);
