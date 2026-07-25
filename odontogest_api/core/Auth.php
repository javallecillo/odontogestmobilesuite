<?php
// ── Validación de Bearer Token ────────────────────────────────
// Formato: base64(id_usuario|rol|timestamp|random_hex)
// TTL: 24 horas. En producción reemplazar por JWT con firma HMAC-SHA256.

require_once __DIR__ . '/Response.php';

const TOKEN_TTL = 86400; // 24 horas en segundos

function getAuthUser(): array {
    // XAMPP/Apache bloquea Authorization por defecto.
    // El .htaccess lo re-expone como HTTP_AUTHORIZATION o REDIRECT_HTTP_AUTHORIZATION.
    $header = '';
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $ah = apache_request_headers();
        if (is_array($ah) && !empty($ah['Authorization'])) {
            $header = $ah['Authorization'];
        }
    }

    if (!str_starts_with($header, 'Bearer ')) {
        error(401, 'Token requerido');
    }

    $token   = substr($header, 7);
    $decoded = base64_decode($token, true);

    if (!$decoded) {
        error(401, 'Token inválido');
    }

    $parts = explode('|', $decoded, 4);
    if (count($parts) < 3) {
        error(401, 'Token malformado');
    }

    // Validar que id_usuario sea numérico positivo
    if (!ctype_digit((string)$parts[0]) || (int)$parts[0] <= 0) {
        error(401, 'Token inválido');
    }

    // Validar expiración de 24 horas
    $timestamp = (int)$parts[2];
    if ((time() - $timestamp) > TOKEN_TTL) {
        error(401, 'Sesión expirada. Inicia sesión nuevamente.');
    }

    return [
        'id_usuario' => (int)$parts[0],
        'rol'        => htmlspecialchars(strip_tags($parts[1]), ENT_QUOTES, 'UTF-8'),
        'timestamp'  => $timestamp,
    ];
}

// Aplica CORS y sale si es preflight OPTIONS
function corsHeaders(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
