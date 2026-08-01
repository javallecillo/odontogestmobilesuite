<?php
// ── Helper de respuestas JSON ──────────────────────────────────
/**
 * Sends CORS headers from PHP so API behavior does not rely on Apache
 * per-directory directives or preflight rewrite rules.
 */
function corsHeaders(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Max-Age: 86400');
    header('Vary: Origin, Access-Control-Request-Method, Access-Control-Request-Headers');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

corsHeaders();

function jsonResponse(int $code, array $data): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function ok(array $data): void {
    jsonResponse(200, array_merge(['success' => true], $data));
}

function error(int $code, string $mensaje): void {
    jsonResponse($code, ['success' => false, 'mensaje' => $mensaje]);
}
