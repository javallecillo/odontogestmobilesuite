<?php
// ── GET /expediente/odontograma/get.php?id_expediente=X ───────
// Devuelve todas las condiciones registradas por pieza dental.

require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

getAuthUser();

$idExp = (int)($_GET['id_expediente'] ?? 0);
if (!$idExp) error(400, 'id_expediente requerido');

try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT
            id_odontograma,
            pieza_dental,
            cara,
            condicion,
            color_codigo,
            descripcion,
            fecha_registro
        FROM odontograma
        WHERE id_expediente = :eid
        ORDER BY pieza_dental, fecha_registro DESC
    ");
    $stmt->execute([':eid' => $idExp]);
    $registros = $stmt->fetchAll();

    // Agrupar por pieza dental para facilitar consumo en Flutter
    $agrupado = [];
    foreach ($registros as $r) {
        $pieza = (int)$r['pieza_dental'];
        if (!isset($agrupado[$pieza])) {
            $agrupado[$pieza] = ['pieza_dental' => $pieza, 'condiciones' => []];
        }
        $agrupado[$pieza]['condiciones'][] = [
            'id'         => (int)$r['id_odontograma'],
            'condicion'  => $r['condicion'],
            'cara'       => $r['cara'],
            'color'      => $r['color_codigo'],
            'descripcion'=> $r['descripcion'],
            'fecha'      => $r['fecha_registro'],
        ];
    }

    ok(['dientes' => array_values($agrupado)]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
