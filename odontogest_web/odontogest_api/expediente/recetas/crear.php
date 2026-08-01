<?php
// ── POST /expediente/recetas/crear.php ────────────────────────
// Body: { id_expediente, medicamento, dosis, frecuencia, duracion, notas? }

require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error(405, 'Método no permitido');

$auth = getAuthUser();
$body = json_decode(file_get_contents('php://input'), true);

$idExp      = (int)($body['id_expediente'] ?? 0);
$medicamento= trim($body['medicamento']   ?? '');
$dosis      = trim($body['dosis']         ?? '');
$frecuencia = trim($body['frecuencia']    ?? '');
$duracion   = trim($body['duracion']      ?? '');
$notas      = trim($body['notas']         ?? '');

if (!$idExp || !$medicamento || !$dosis || !$frecuencia || !$duracion) {
    error(400, 'Faltan campos requeridos: id_expediente, medicamento, dosis, frecuencia, duracion');
}

try {
    $db = getDB();

    $od = $db->prepare('SELECT id_odontologo FROM odontologos WHERE id_usuario = :u LIMIT 1');
    $od->execute([':u' => $auth['id_usuario']]);
    $odontologo = $od->fetch();
    $idOdont    = $odontologo ? (int)$odontologo['id_odontologo'] : 1;

    $ins = $db->prepare("
        INSERT INTO recetas (id_expediente, id_odontologo, medicamento, dosis, frecuencia, duracion, notas, fecha_emision)
        VALUES (:eid, :oid, :med, :dos, :frec, :dur, :notas, CURDATE())
    ");
    $ins->execute([
        ':eid'  => $idExp,
        ':oid'  => $idOdont,
        ':med'  => $medicamento,
        ':dos'  => $dosis,
        ':frec' => $frecuencia,
        ':dur'  => $duracion,
        ':notas'=> $notas ?: null,
    ]);

    ok(['id_receta' => (int)$db->lastInsertId(), 'mensaje' => 'Receta creada correctamente']);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
