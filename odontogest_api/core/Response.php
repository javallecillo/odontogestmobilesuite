<?php
// ── Helper de respuestas JSON ──────────────────────────────────
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
