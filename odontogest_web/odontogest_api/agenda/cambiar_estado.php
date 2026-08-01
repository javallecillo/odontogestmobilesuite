<?php
// ── POST /agenda/cambiar_estado.php ──────────────────────────
// Body: { id_cita, estado, asistencia? }

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error(405, 'Método no permitido');

$auth = getAuthUser();
$body = json_decode(file_get_contents('php://input'), true);

$idCita    = (int)($body['id_cita'] ?? 0);
$estado    = $body['estado']    ?? '';
$asistencia= $body['asistencia'] ?? null;

$estadosValidos = ['pendiente','confirmada','en_curso','atendida','cancelada','no_asistio'];
if (!$idCita || !in_array($estado, $estadosValidos)) {
    error(400, 'id_cita y estado válido son requeridos');
}

try {
    $db = getDB();

    $sets    = ['estado = :estado'];
    $params  = [':estado' => $estado, ':id' => $idCita];

    if ($asistencia && in_array($asistencia, ['pendiente','asistio','no_asistio'])) {
        $sets[]               = 'asistencia = :asistencia';
        $params[':asistencia']= $asistencia;
    }

    $sql = 'UPDATE citas SET ' . implode(', ', $sets) . ' WHERE id_cita = :id';
    $db->prepare($sql)->execute($params);

    ok(['mensaje' => 'Estado actualizado correctamente']);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
