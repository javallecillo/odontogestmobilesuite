<?php
// ── RateLimit — límite de peticiones por IP ───────────────────
// Máximo 60 peticiones por minuto por IP.
// Usa archivos temporales — en producción reemplazar por Redis.

function checkRateLimit(int $maxPerMinute = 60): void {
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $key     = sys_get_temp_dir() . '/rl_' . md5($ip) . '.json';
    $now     = time();
    $window  = 60; // segundos

    $data = ['count' => 0, 'window_start' => $now];

    if (file_exists($key)) {
        $raw = @file_get_contents($key);
        if ($raw) {
            $saved = json_decode($raw, true);
            if ($saved && ($now - $saved['window_start']) < $window) {
                $data = $saved;
            }
        }
    }

    $data['count']++;
    file_put_contents($key, json_encode($data), LOCK_EX);

    if ($data['count'] > $maxPerMinute) {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        header('Retry-After: ' . ($window - ($now - $data['window_start'])));
        echo json_encode([
            'success' => false,
            'mensaje' => 'Demasiadas peticiones. Intenta en un momento.',
        ]);
        exit;
    }
}
