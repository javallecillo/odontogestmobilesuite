<?php
// ── GET /notificaciones/listar.php ───────────────────────────
// Devuelve las notificaciones del usuario autenticado.
// ?solo_no_leidas=1   → filtra leida=0
// ?limit=30           → máximo registros (default 30)

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/RateLimit.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');
checkRateLimit();
$user = getAuthUser();

$soloNoLeidas = isset($_GET['solo_no_leidas']) && $_GET['solo_no_leidas'] === '1';
$limit        = min(50, max(5, (int)($_GET['limit'] ?? 30)));

try {
    $db = getDB();

    // Total no leídas (para el badge)
    $stmtBadge = $db->prepare(
        "SELECT COUNT(*) FROM notificaciones
         WHERE id_usuario = :uid AND leida = 0 AND estado = 'activa'"
    );
    $stmtBadge->execute([':uid' => $user['id_usuario']]);
    $totalNoLeidas = (int)$stmtBadge->fetchColumn();

    $where = "WHERE id_usuario = :uid AND estado = 'activa'";
    if ($soloNoLeidas) $where .= ' AND leida = 0';

    $stmt = $db->prepare(
        "SELECT id_notificacion, titulo, mensaje, leida, fecha
         FROM notificaciones
         $where
         ORDER BY leida ASC, fecha DESC
         LIMIT $limit"
    );
    $stmt->execute([':uid' => $user['id_usuario']]);

    ok([
        'notificaciones'  => $stmt->fetchAll(),
        'total_no_leidas' => $totalNoLeidas,
    ]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
