<?php
// ── GET /inventario/listar.php ────────────────────────────────
// ?q=&estado=activo|inactivo|agotado&stock_bajo=1&page=1

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/RateLimit.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');
checkRateLimit();
getAuthUser();

$q         = trim($_GET['q']        ?? '');
$estado    = $_GET['estado']        ?? 'activo';
$stockBajo = isset($_GET['stock_bajo']) && $_GET['stock_bajo'] === '1';
$page      = max(1, (int)($_GET['page']  ?? 1));
$limit     = min(50, max(10, (int)($_GET['limit'] ?? 20)));
$offset    = ($page - 1) * $limit;

try {
    $db     = getDB();
    $where  = [];
    $params = [];

    if (in_array($estado, ['activo','inactivo','agotado'])) {
        $where[]          = 'p.estado = :estado';
        $params[':estado']= $estado;
    }

    if ($q !== '') {
        $where[]      = 'p.nombre LIKE :q';
        $params[':q'] = '%' . $q . '%';
    }

    if ($stockBajo) {
        $where[] = 'p.stock <= p.stock_minimo';
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    // Resumen global
    $res = $db->query("
        SELECT
            COUNT(*) AS total_productos,
            SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END) AS agotados,
            SUM(CASE WHEN stock <= stock_minimo AND stock > 0 THEN 1 ELSE 0 END) AS stock_bajo
        FROM producto WHERE estado != 'inactivo'
    ")->fetch();

    // Total paginado
    $cnt = $db->prepare("SELECT COUNT(*) FROM producto p $whereSQL");
    $cnt->execute($params);
    $total = (int)$cnt->fetchColumn();

    // Productos
    $stmt = $db->prepare("
        SELECT
            p.id_producto,
            p.nombre,
            p.descripcion,
            p.stock,
            p.stock_minimo,
            p.precio_costo,
            p.precio_venta,
            p.tasa_impuesto,
            p.unidad_medida,
            p.estado,
            pr.proveedor AS proveedor,
            CASE
                WHEN p.stock = 0          THEN 'agotado'
                WHEN p.stock <= p.stock_minimo THEN 'bajo'
                ELSE 'ok'
            END AS nivel_stock
        FROM producto p
        LEFT JOIN proveedores pr ON pr.id_proveedor = p.id_proveedor
        -- proveedores.proveedor es el nombre (no .nombre)
        $whereSQL
        ORDER BY nivel_stock ASC, p.nombre ASC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute($params);

    ok([
        'productos' => $stmt->fetchAll(),
        'resumen'   => $res,
        'total'     => $total,
        'page'      => $page,
        'pages'     => (int)ceil($total / $limit),
    ]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
