<?php
// ── GET /agenda/odontologos.php ───────────────────────────────
// Lista odontólogos activos para el selector de nueva cita en Flutter.

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

getAuthUser();

try {
    $db   = getDB();
    $rows = $db->query("
        SELECT od.id_odontologo,
               CONCAT(od.nombre,' ',od.apellidos) AS nombre_completo,
               od.numero_licencia,
               e.nombre AS especialidad
        FROM   odontologos od
        LEFT JOIN especialidades e ON e.id_especialidad = od.id_especialidad
        WHERE  od.estado = 'activo'
        ORDER  BY od.nombre
    ")->fetchAll();

    ok(['odontologos' => $rows]);
} catch (PDOException $e) {
    error(500, $e->getMessage());
}
