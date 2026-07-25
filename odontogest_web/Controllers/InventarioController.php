<?php
/**
 * InventarioController — Control de productos / stock
 * Tabla: producto | stock, stock_minimo, estado('activo','inactivo','agotado')
 */
class InventarioController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Inventario';

        $filtros = [
            'buscar'  => $_GET['buscar'] ?? '',
            'estado'  => $_GET['estado'] ?? 'activo',
            'pagina'  => max(1, (int)($_GET['pagina'] ?? 1)),
        ];

        $productos    = InventarioModel::listar($filtros);
        $total        = InventarioModel::total($filtros);
        $kpis         = InventarioModel::kpis();
        $alertasStock = InventarioModel::alertasStock();
        $proveedores  = InventarioModel::listarProveedores();
        $porPagina    = 15;
        $totalPags    = max(1, ceil($total / $porPagina));

        require_once VIEW_PATH . 'Inventario/index.php';
    }

    public function crear(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'inventario'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $data = [
            'id_proveedor'  => (int)($_POST['id_proveedor']  ?? 0) ?: null,
            'nombre'        => trim($_POST['nombre']          ?? ''),
            'descripcion'   => trim($_POST['descripcion']     ?? '') ?: null,
            'unidad_medida' => trim($_POST['unidad_medida']   ?? ''),
            'stock'         => (int)($_POST['stock']          ?? 0),
            'stock_minimo'  => (int)($_POST['stock_minimo']   ?? 0),
            'precio_costo'  => (float)($_POST['precio_costo'] ?? 0),
            'precio_venta'  => (float)($_POST['precio_venta'] ?? 0),
            'tasa_impuesto' => (float)($_POST['tasa_impuesto']?? 0),
            'estado'        => 'activo',
        ];

        $id = InventarioModel::insertar($data);
        AuditoriaModel::registrar('inventario', 'crear', "Producto #{$id}");
        header('Location: ' . APP_URL . 'inventario?ok=creado');
        exit;
    }

    public function actualizar(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'inventario'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id   = (int)($_POST['id_producto'] ?? 0);
        $data = [
            'id_proveedor'  => (int)($_POST['id_proveedor']  ?? 0) ?: null,
            'nombre'        => trim($_POST['nombre']          ?? ''),
            'descripcion'   => trim($_POST['descripcion']     ?? '') ?: null,
            'unidad_medida' => trim($_POST['unidad_medida']   ?? ''),
            'stock'         => (int)($_POST['stock']          ?? 0),
            'stock_minimo'  => (int)($_POST['stock_minimo']   ?? 0),
            'precio_costo'  => (float)($_POST['precio_costo'] ?? 0),
            'precio_venta'  => (float)($_POST['precio_venta'] ?? 0),
            'tasa_impuesto' => (float)($_POST['tasa_impuesto']?? 0),
            'estado'        => $_POST['estado'] ?? 'activo',
        ];

        InventarioModel::actualizar($id, $data);
        AuditoriaModel::registrar('inventario', 'editar', "Producto #{$id}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function ajustarStock(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'inventario'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id       = (int)($_POST['id_producto'] ?? 0);
        $cantidad = (int)($_POST['cantidad']     ?? 0);
        $tipo     = $_POST['tipo'] ?? 'entrada'; // 'entrada' | 'salida' | 'ajuste'
        $motivo   = trim($_POST['motivo'] ?? '');

        InventarioModel::ajustarStock($id, $cantidad, $tipo, $motivo);
        AuditoriaModel::registrar('inventario', 'editar', "Stock ajustado producto #{$id} {$tipo} {$cantidad} — {$motivo}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function eliminar(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'inventario'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id = (int)($_POST['id_producto'] ?? 0);
        InventarioModel::cambiarEstado($id, 'inactivo');
        AuditoriaModel::registrar('inventario', 'eliminar', "Producto #{$id}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
