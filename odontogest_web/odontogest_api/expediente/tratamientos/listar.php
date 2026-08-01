<?php
// ── GET /expediente/tratamientos/listar.php?id_paciente=X ─────

require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

getAuthUser();

$idPac = (int)($_GET['id_paciente'] ?? 0);
if (!$idPac) error(400, 'id_paciente requerido');

try {
    $db   = getDB();
    $stmt = $db->prepare("
        SELECT
            th.id_historial,
            t.descripcion       AS tratamiento,
            th.descripcion      AS notas,
            th.fecha_inicio,
            th.fecha_fin,
            th.costo,
            th.abono,
            th.saldo_pendiente,
            th.estado,
            u.nombre_completo AS odontologo
        FROM tratamientos_historial th
        JOIN tratamientos  t  ON t.id_tratamiento  = th.id_tratamiento
        JOIN odontologos   od ON od.id_odontologo  = th.id_odontologo
        JOIN usuarios      u  ON u.id_usuario      = od.id_usuario
        WHERE th.id_paciente = :pid
        ORDER BY th.fecha_inicio DESC
    ");
    $stmt->execute([':pid' => $idPac]);
    ok(['tratamientos' => $stmt->fetchAll()]);
} catch (PDOException $e) {
    error(500, $e->getMessage());
}
