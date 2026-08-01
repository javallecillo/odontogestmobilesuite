<?php
// ── POST /notificaciones/marcar_leida.php ───────────────────
// Body: { id_notificacion: int }  → marca una como leída
// Body: { todas: true }           → marca todas como leídas

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/RateLimit.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error(405, 'Método no permitido');
checkRateLimit();
$user = getAuthUser();

$body = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $db = getDB();

    if (!empty($body['todas'])) {
        // Marcar todas como leídas
        $stmt = $db->prepare(
            "UPDATE notificaciones SET leida = 1
             WHERE id_usuario = :uid AND leida = 0"
        );
        $stmt->execute([':uid' => $user['id_usuario']]);
        ok(['mensaje' => 'Todas las notificaciones marcadas como leídas']);
    } else {
        $id = (int)($body['id_notificacion'] ?? 0);
        if ($id <= 0) error(400, 'id_notificacion requerido');

        $stmt = $db->prepare(
            "UPDATE notificaciones SET leida = 1
             WHERE id_notificacion = :id AND id_usuario = :uid"
        );
        $stmt->execute([':id' => $id, ':uid' => $user['id_usuario']]);

        if ($stmt->rowCount() === 0) error(404, 'Notificación no encontrada');
        ok(['mensaje' => 'Notificación marcada como leída']);
    }

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
