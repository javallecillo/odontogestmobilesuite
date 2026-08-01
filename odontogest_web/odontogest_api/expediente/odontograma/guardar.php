<?php
// ── POST /expediente/odontograma/guardar.php ──────────────────
// Guarda el estado actual del odontograma completo para un expediente.
// Estrategia: DELETE condiciones existentes del expediente → INSERT nuevas.
// Body JSON:
// {
//   "id_expediente": 1,
//   "dientes": [
//     { "pieza_dental": 11, "condiciones": ["Caries","Bracket"] },
//     { "pieza_dental": 16, "condiciones": ["Corona"] }
//   ]
// }

require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error(405, 'Método no permitido');

$auth = getAuthUser();

$body    = json_decode(file_get_contents('php://input'), true);
$idExp   = (int)($body['id_expediente'] ?? 0);
$dientes = $body['dientes'] ?? [];

if (!$idExp) error(400, 'id_expediente requerido');

// Mapa de colores por condición
const COLORES = [
    'Sano'       => '#4CAF50',
    'Caries'     => '#E53935',
    'Extracción' => '#212121',
    'Corona'     => '#FFD600',
    'Obturación' => '#1565C0',
    'Ausente'    => '#9E9E9E',
    'Implante'   => '#7B1FA2',
    'Fractura'   => '#FF6F00',
    'Bracket'    => '#00BCD4',
];

try {
    $db = getDB();

    // Obtener id_odontologo del usuario autenticado
    $od = $db->prepare('SELECT id_odontologo FROM odontologos WHERE id_usuario = :u LIMIT 1');
    $od->execute([':u' => $auth['id_usuario']]);
    $odontologo = $od->fetch();

    // Si no es odontólogo, buscar admin fallback (id_odontologo = 1)
    $idOdont = $odontologo ? (int)$odontologo['id_odontologo'] : 1;

    $db->beginTransaction();

    // Limpiar registros previos del expediente
    $del = $db->prepare('DELETE FROM odontograma WHERE id_expediente = :eid');
    $del->execute([':eid' => $idExp]);

    // Insertar condiciones actuales
    $ins = $db->prepare("
        INSERT INTO odontograma
            (id_expediente, id_odontologo, pieza_dental, condicion, color_codigo, fecha_registro)
        VALUES
            (:eid, :oid, :pieza, :condicion, :color, NOW())
    ");

    foreach ($dientes as $diente) {
        $pieza      = (int)($diente['pieza_dental'] ?? 0);
        $condiciones = $diente['condiciones'] ?? [];
        if (!$pieza || empty($condiciones)) continue;

        foreach ($condiciones as $cond) {
            $color = COLORES[$cond] ?? '#FF0000';
            $ins->execute([
                ':eid'      => $idExp,
                ':oid'      => $idOdont,
                ':pieza'    => $pieza,
                ':condicion'=> $cond,
                ':color'    => $color,
            ]);
        }
    }

    $db->commit();
    ok(['mensaje' => 'Odontograma guardado correctamente', 'id_expediente' => $idExp]);

} catch (PDOException $e) {
    if ($db->inTransaction()) $db->rollBack();
    error(500, $e->getMessage());
}
