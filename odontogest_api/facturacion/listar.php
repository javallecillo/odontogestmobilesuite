<?php
// ── GET /facturacion/listar.php ───────────────────────────────
// ?estado=all|emitida|pagada|anulada&page=1&limit=20

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/RateLimit.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');
checkRateLimit();
getAuthUser();

$estado = $_GET['estado'] ?? 'all';
$page   = max(1, (int)($_GET['page']  ?? 1));
$limit  = min(50, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

try {
    $db     = getDB();
    $where  = [];
    $params = [];

    $estadosValidos = ['emitida','pagada','anulada'];
    if (in_array($estado, $estadosValidos)) {
        $where[]          = 'f.estado = :estado';
        $params[':estado']= $estado;
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Totales globales para el resumen
    $totales = $db->prepare("
        SELECT
            COUNT(*) AS total_facturas,
            SUM(CASE WHEN f.estado = 'pagada'  THEN f.total ELSE 0 END) AS total_cobrado,
            SUM(CASE WHEN f.estado = 'emitida' THEN f.total ELSE 0 END) AS total_pendiente
        FROM factura f
    ");
    $totales->execute();
    $resumen = $totales->fetch();

    // Total paginado
    $cnt = $db->prepare("SELECT COUNT(*) FROM factura f $whereSQL");
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();

    // Facturas
    $stmt = $db->prepare("
        SELECT
            f.id_factura,
            f.numero_factura,
            CONCAT(p.nombre, ' ', p.apellidos)  AS paciente,
            f.subtotal,
            f.impuesto,
            f.descuento,
            f.total,
            f.tasa_impuesto,
            f.metodo_pago,
            f.estado,
            f.fecha_emision,
            f.fecha_pago
        FROM factura f
        JOIN pacientes p ON p.id_paciente = f.id_paciente
        $whereSQL
        ORDER BY f.fecha_emision DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);

    ok([
        'facturas'  => $stmt->fetchAll(),
        'resumen'   => $resumen,
        'total'     => $total,
        'page'      => $page,
        'pages'     => (int)ceil($total / $limit),
    ]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
