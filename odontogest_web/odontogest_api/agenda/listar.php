<?php
// ── GET /agenda/listar.php ────────────────────────────────────
// ?fecha=YYYY-MM-DD  (default: hoy)
// ?estado=all|pendiente|confirmada|en_curso|atendida|cancelada
// Odontólogo ve sus citas; Admin/Recepcionista ve todas.

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/RateLimit.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');
checkRateLimit();

$auth   = getAuthUser();
$fecha  = $_GET['fecha']  ?? date('Y-m-d');
$estado = $_GET['estado'] ?? 'all';

// Validar fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}

try {
    $db = getDB();

    // Determinar si filtrar por odontólogo
    $odRow = null;
    if (!in_array($auth['rol'], ['Administrador', 'Recepcionista'])) {
        $od = $db->prepare('SELECT id_odontologo FROM odontologos WHERE id_usuario = :u LIMIT 1');
        $od->execute([':u' => $auth['id_usuario']]);
        $odRow = $od->fetch();
    }

    $where  = ['DATE(c.fecha_cita) = :fecha'];
    $params = [':fecha' => $fecha];

    if ($odRow) {
        $where[]             = 'c.id_odontologo = :oid';
        $params[':oid']      = (int)$odRow['id_odontologo'];
    }

    $estadosValidos = ['pendiente','confirmada','en_curso','atendida','cancelada','no_asistio'];
    if ($estado !== 'all' && in_array($estado, $estadosValidos)) {
        $where[]          = 'c.estado = :estado';
        $params[':estado']= $estado;
    }

    $sql = "
        SELECT
            c.id_cita,
            TIME_FORMAT(c.fecha_cita, '%H:%i')           AS hora,
            CONCAT(p.nombre, ' ', p.apellidos)            AS paciente,
            u.nombre_completo                              AS odontologo,
            COALESCE(s.nombre, t.descripcion, 'General')  AS servicio,
            c.estado,
            c.asistencia,
            c.notas,
            c.id_paciente,
            e.id_expediente
        FROM citas c
        JOIN pacientes   p  ON p.id_paciente  = c.id_paciente
        JOIN odontologos od ON od.id_odontologo = c.id_odontologo
        JOIN usuarios    u  ON u.id_usuario   = od.id_usuario
        LEFT JOIN servicios    s  ON s.id_servicio    = c.id_servicio
        LEFT JOIN tratamientos t  ON t.id_tratamiento = c.id_tratamiento
        LEFT JOIN expedientes  e  ON e.id_paciente    = c.id_paciente
        WHERE " . implode(' AND ', $where) . "
        ORDER BY c.fecha_cita ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $citas = $stmt->fetchAll();

    ok(['citas' => $citas, 'total' => count($citas), 'fecha' => $fecha]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
