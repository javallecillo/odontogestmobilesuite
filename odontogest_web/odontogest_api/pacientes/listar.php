<?php
// ── GET /pacientes/listar.php ─────────────────────────────────
// ?q=texto&estado=activo|inactivo&page=1&limit=20

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/RateLimit.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');
checkRateLimit();
getAuthUser();

$q      = trim($_GET['q']      ?? '');
$estado = $_GET['estado']      ?? 'activo';
$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = min(50, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

try {
    $db     = getDB();
    $where  = [];
    $params = [];

    if (in_array($estado, ['activo','inactivo'])) {
        $where[]          = 'p.estado = :estado';
        $params[':estado']= $estado;
    }

    if ($q !== '') {
        $where[]      = '(p.nombre LIKE :q OR p.apellidos LIKE :q OR p.dni LIKE :q OR p.telefono LIKE :q)';
        $params[':q'] = '%' . $q . '%';
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Total
    $cntStmt = $db->prepare("
        SELECT COUNT(*) FROM pacientes p
        LEFT JOIN expedientes e ON e.id_paciente = p.id_paciente
        $whereSQL
    ");
    $cntStmt->execute($params);
    $total = (int)$cntStmt->fetchColumn();

    // Datos
    $stmt = $db->prepare("
        SELECT
            p.id_paciente,
            CONCAT(p.nombre, ' ', p.apellidos)  AS nombre_completo,
            p.dni,
            p.fecha_nacimiento,
            p.telefono,
            p.correo,
            p.estado,
            p.estado_civil,
            p.sexo,
            e.id_expediente,
            CONCAT('EXP-', LPAD(e.id_expediente, 5, '0')) AS numero_expediente
        FROM pacientes p
        LEFT JOIN expedientes e ON e.id_paciente = p.id_paciente
        $whereSQL
        ORDER BY p.nombre, p.apellidos
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);

    ok([
        'pacientes' => $stmt->fetchAll(),
        'total'     => $total,
        'page'      => $page,
        'pages'     => (int)ceil($total / $limit),
    ]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
