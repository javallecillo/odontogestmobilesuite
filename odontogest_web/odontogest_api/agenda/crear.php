<?php
// ── POST /agenda/crear.php ────────────────────────────────────
// Body JSON: { id_paciente, id_odontologo, fecha_cita "YYYY-MM-DD HH:MM",
//              id_servicio? (int|null), notas? }
// Regla: 1 cita por odontólogo por hora (no canceladas).

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error(405, 'Método no permitido');

getAuthUser();

$body   = json_decode(file_get_contents('php://input'), true);
$idPac  = (int)($body['id_paciente']   ?? 0);
$idOd   = (int)($body['id_odontologo'] ?? 0);
$fecha  = trim($body['fecha_cita']     ?? '');
$idServ = !empty($body['id_servicio']) ? (int)$body['id_servicio'] : null;
$notas  = trim($body['notas']          ?? '');

if (!$idPac || !$idOd || !$fecha) {
    error(400, 'id_paciente, id_odontologo y fecha_cita son requeridos');
}
if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', $fecha)) {
    error(400, 'Formato fecha_cita inválido — usa YYYY-MM-DD HH:MM');
}

try {
    $db = getDB();

    // Verificar colisión de horario (mismo odontologo, misma hora, no cancelada)
    $chk = $db->prepare("
        SELECT COUNT(*) FROM citas
        WHERE id_odontologo = ?
          AND fecha_cita = ?
          AND estado NOT IN ('cancelada','no_asistio')
    ");
    $chk->execute([$idOd, $fecha]);
    if ((int)$chk->fetchColumn() > 0) {
        error(409, 'El odontólogo ya tiene una cita en ese horario');
    }

    // Buscar o crear horario
    $hora      = substr($fecha, 11, 5);
    $fechaSola = substr($fecha,  0, 10);
    $mapDia    = [
        'Monday'    => 'lunes',   'Tuesday'  => 'martes',
        'Wednesday' => 'miercoles','Thursday' => 'jueves',
        'Friday'    => 'viernes', 'Saturday' => 'sabado',
        'Sunday'    => 'domingo',
    ];
    $dia = $mapDia[date('l', strtotime($fechaSola))] ?? 'lunes';

    $hRow = $db->prepare('SELECT id_horario FROM horarios WHERE fecha = ? AND hora = ? LIMIT 1');
    $hRow->execute([$fechaSola, $hora . ':00']);
    $idHorario = $hRow->fetchColumn();

    if (!$idHorario) {
        $db->prepare('INSERT INTO horarios (dia, hora, duracion_min, fecha, disponible) VALUES (?, ?, 30, ?, 1)')
            ->execute([$dia, $hora . ':00', $fechaSola]);
        $idHorario = (int)$db->lastInsertId();
    }

    // Insertar cita
    $ins = $db->prepare("
        INSERT INTO citas
            (id_paciente, id_odontologo, id_horario, id_servicio, fecha_cita, notas, estado, asistencia)
        VALUES
            (?, ?, ?, ?, ?, ?, 'pendiente', 'pendiente')
    ");
    $ins->execute([
        $idPac,
        $idOd,
        $idHorario,
        $idServ,
        $fecha,
        $notas ?: null,
    ]);

    ok([
        'id_cita' => (int)$db->lastInsertId(),
        'mensaje' => 'Cita registrada correctamente',
    ]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
