<?php
// ── GET /agenda/slots_disponibles.php ────────────────────────
// ?id_odontologo=X&fecha=YYYY-MM-DD
// Devuelve todas las horas del día con bandera disponible/ocupado.

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

getAuthUser();

$idOd  = (int)($_GET['id_odontologo'] ?? 0);
$fecha = trim($_GET['fecha'] ?? date('Y-m-d'));

if (!$idOd) error(400, 'id_odontologo requerido');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}

// Horario laboral — ajusta según la clínica
$horasTodas = [
    '07:00','08:00','09:00','10:00','11:00',
    '13:00','14:00','15:00','16:00','17:00',
];

try {
    $db = getDB();

    // Citas activas (no canceladas, no no_asistio) para ese odontólogo en esa fecha
    $st = $db->prepare("
        SELECT TIME_FORMAT(TIME(fecha_cita), '%H:%i') AS hora
        FROM   citas
        WHERE  id_odontologo = :od
          AND  DATE(fecha_cita) = :f
          AND  estado NOT IN ('cancelada', 'no_asistio')
    ");
    $st->execute([':od' => $idOd, ':f' => $fecha]);
    $ocupadas = array_column($st->fetchAll(), 'hora');

    $slots = array_map(fn($h) => [
        'hora'        => $h,
        'disponible'  => !in_array($h, $ocupadas),
    ], $horasTodas);

    ok(['slots' => $slots, 'ocupadas' => $ocupadas]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
