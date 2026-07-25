<?php
// ── GET /expediente/resumen.php?id_paciente=X ─────────────────
// Devuelve datos del paciente + resumen del expediente.

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

getAuthUser();

$id = (int)($_GET['id_paciente'] ?? 0);
if (!$id) error(400, 'id_paciente requerido');

try {
    $db = getDB();

    // Datos del paciente + expediente
    $stmt = $db->prepare("
        SELECT
            p.id_paciente,
            CONCAT(p.nombre, ' ', p.apellidos)  AS nombre_completo,
            p.fecha_nacimiento,
            p.sexo,
            p.telefono,
            p.correo,
            p.direccion,
            p.estado,
            e.id_expediente,
            e.numero_expediente,
            e.observaciones,
            e.updated_at AS expediente_actualizado
        FROM pacientes p
        LEFT JOIN expedientes e ON e.id_paciente = p.id_paciente
        WHERE p.id_paciente = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    $paciente = $stmt->fetch();

    if (!$paciente) error(404, 'Paciente no encontrado');

    // Alergias
    $al = $db->prepare("
        SELECT a.nombre FROM alergias a
        JOIN expediente_alergias ea ON ea.id_alergia = a.id_alergia
        WHERE ea.id_expediente = :eid
    ");
    $al->execute([':eid' => $paciente['id_expediente']]);
    $paciente['alergias'] = array_column($al->fetchAll(), 'nombre');

    // Enfermedades
    $en = $db->prepare("
        SELECT en.nombre FROM enfermedades en
        JOIN expediente_enfermedades ee ON ee.id_enfermedad = en.id_enfermedad
        WHERE ee.id_expediente = :eid
    ");
    $en->execute([':eid' => $paciente['id_expediente']]);
    $paciente['enfermedades'] = array_column($en->fetchAll(), 'nombre');

    // Conteos resumen
    $cnt = $db->prepare("
        SELECT
            (SELECT COUNT(*) FROM odontograma        WHERE id_expediente = :eid) AS dientes_registrados,
            (SELECT COUNT(*) FROM recetas            WHERE id_expediente = :eid) AS total_recetas,
            (SELECT COUNT(*) FROM expediente_fotos   WHERE id_expediente = :eid) AS total_fotos,
            (SELECT COUNT(*) FROM tratamientos_historial WHERE id_paciente = :pid) AS total_tratamientos
    ");
    $cnt->execute([':eid' => $paciente['id_expediente'], ':pid' => $id]);
    $paciente['resumen'] = $cnt->fetch();

    ok($paciente);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
