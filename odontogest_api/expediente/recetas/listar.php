<?php
// ── GET /expediente/recetas/listar.php?id_expediente=X ────────

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
            r.id_receta,
            r.medicamento,
            r.dosis,
            r.frecuencia,
            r.duracion,
            r.notas,
            r.fecha_emision,
            CONCAT(u.nombre, ' ', u.apellido) AS odontologo
        FROM recetas r
        JOIN odontologos od ON od.id_odontologo = r.id_odontologo
        JOIN usuarios u     ON u.id_usuario     = od.id_usuario
        WHERE r.id_expediente = :eid
        ORDER BY r.fecha_emision DESC
    ");
    $stmt->execute([':eid' => $idExp]);
    ok(['recetas' => $stmt->fetchAll()]);
} catch (PDOException $e) {
    error(500, $e->getMessage());
}
