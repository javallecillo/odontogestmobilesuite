<?php
// ── POST /expediente/tratamientos/crear.php ───────────────────
// Body: { id_paciente, id_tratamiento, descripcion?, fecha_inicio, costo }

require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error(405, 'Método no permitido');

$auth = getAuthUser();
$body = json_decode(file_get_contents('php://input'), true);

$idPac     = (int)($body['id_paciente']    ?? 0);
$idTrat    = (int)($body['id_tratamiento'] ?? 0);
$desc      = trim($body['descripcion']     ?? '');
$fechaIni  = $body['fecha_inicio']         ?? date('Y-m-d');
$costo     = (float)($body['costo']        ?? 0);

if (!$idPac || !$idTrat) error(400, 'id_paciente e id_tratamiento son requeridos');

try {
    $db = getDB();

    $od = $db->prepare('SELECT id_odontologo FROM odontologos WHERE id_usuario = :u LIMIT 1');
    $od->execute([':u' => $auth['id_usuario']]);
    $odontologo = $od->fetch();
    $idOdont    = $odontologo ? (int)$odontologo['id_odontologo'] : 1;

    $ins = $db->prepare("
        INSERT INTO tratamientos_historial
            (id_paciente, id_tratamiento, id_odontologo, descripcion, fecha_inicio, costo, id_usuario_registro)
        VALUES
            (:pid, :tid, :oid, :desc, :fi, :costo, :uid)
    ");
    $ins->execute([
        ':pid'   => $idPac,
        ':tid'   => $idTrat,
        ':oid'   => $idOdont,
        ':desc'  => $desc ?: null,
        ':fi'    => $fechaIni,
        ':costo' => $costo,
        ':uid'   => $auth['id_usuario'],
    ]);

    ok(['id_historial' => (int)$db->lastInsertId(), 'mensaje' => 'Tratamiento registrado correctamente']);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
