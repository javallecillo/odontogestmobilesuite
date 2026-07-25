<?php
// ── GET /expediente/fotos/listar.php?id_expediente=X ──────────

require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

getAuthUser();

$idExp = (int)($_GET['id_expediente'] ?? 0);
if (!$idExp) error(400, 'id_expediente requerido');

try {
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT
            ef.id,
            ef.descripcion,
            ef.created_at,
            img.url,
            img.nombre_archivo,
            img.mime_type
        FROM expediente_fotos ef
        JOIN imagenes img ON img.id_imagen = ef.id_imagen
        WHERE ef.id_expediente = :eid
        ORDER BY ef.created_at DESC
    ");
    $stmt->execute([':eid' => $idExp]);
    ok(['fotos' => $stmt->fetchAll()]);
} catch (PDOException $e) {
    error(500, $e->getMessage());
}
