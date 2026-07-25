<?php
// ── GET /dashboard/metricas.php ───────────────────────────────
// Headers: Authorization: Bearer <token>
// Respuesta: { citas_hoy, atendidas, pendientes, pacientes_total }

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

$user = getAuthUser();

try {
    $db = getDB();

    // Total citas de hoy para el odontólogo
    $s = $db->prepare("
        SELECT
            COUNT(*) AS citas_hoy,
            SUM(estado = 'atendida')   AS atendidas,
            SUM(estado IN ('pendiente','confirmada','en_curso')) AS pendientes
        FROM citas
        WHERE DATE(fecha_cita) = CURDATE()
          AND id_odontologo = (
              SELECT id_odontologo FROM odontologos WHERE id_usuario = :id LIMIT 1
          )
    ");
    $s->execute([':id' => $user['id_usuario']]);
    $stats = $s->fetch();

    // Total pacientes activos
    $p = $db->query("SELECT COUNT(*) AS total FROM pacientes WHERE estado = 'activo'");
    $pacientes = $p->fetch();

    ok([
        'citas_hoy'      => (int)($stats['citas_hoy']   ?? 0),
        'atendidas'      => (int)($stats['atendidas']   ?? 0),
        'pendientes'     => (int)($stats['pendientes']  ?? 0),
        'pacientes_total'=> (int)($pacientes['total']   ?? 0),
    ]);

} catch (PDOException $e) {
    error(500, 'Error de base de datos: ' . $e->getMessage());
}
