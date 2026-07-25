<?php
/**
 * FacturacionController — Gestión de facturas
 * Tabla: factura | estado ENUM('emitida','pagada','anulada')
 */
class FacturacionController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Facturación';

        $filtros = [
            'estado'     => $_GET['estado']      ?? '',
            'fecha_ini'  => $_GET['fecha_ini']   ?? '',
            'fecha_fin'  => $_GET['fecha_fin']   ?? '',
            'buscar'     => $_GET['buscar']      ?? '',
            'pagina'     => max(1, (int)($_GET['pagina'] ?? 1)),
        ];

        $facturas  = FacturacionModel::listar($filtros);
        $total     = FacturacionModel::total($filtros);
        $kpis      = FacturacionModel::kpis();
        $porPagina = 15;
        $totalPags = max(1, ceil($total / $porPagina));

        require_once VIEW_PATH . 'Facturacion/index.php';
    }

    public function ver(): void {
        Auth::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: ' . APP_URL . 'facturacion'); exit; }

        $pageTitle = 'Detalle de Factura';
        $factura   = FacturacionModel::obtenerPorId($id);
        if (!$factura) { header('Location: ' . APP_URL . 'facturacion'); exit; }
        $items = FacturacionModel::itemsFactura($id);

        require_once VIEW_PATH . 'Facturacion/ver.php';
    }

    public function crear(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'facturacion'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $data = [
            'id_paciente' => (int)($_POST['id_paciente'] ?? 0),
            'id_cita'     => (int)($_POST['id_cita']     ?? 0) ?: null,
            'subtotal'    => (float)($_POST['subtotal']  ?? 0),
            'isv'         => (float)($_POST['isv']       ?? 0),
            'total'       => (float)($_POST['total']     ?? 0),
            'metodo_pago' => $_POST['metodo_pago'] ?? 'efectivo',
            'estado'      => 'emitida',
            'notas'       => trim($_POST['notas'] ?? '') ?: null,
        ];
        $items = json_decode($_POST['items'] ?? '[]', true) ?: [];

        $id = FacturacionModel::insertar($data, $items);
        AuditoriaModel::registrar('facturacion', 'crear', "Factura #{$id}");
        header('Location: ' . APP_URL . 'facturacion?ok=creada');
        exit;
    }

    public function marcarPagada(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'facturacion'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id = (int)($_POST['id_factura'] ?? 0);
        FacturacionModel::cambiarEstado($id, 'pagada');
        AuditoriaModel::registrar('facturacion', 'editar', "Factura #{$id}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function anular(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'facturacion'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id     = (int)($_POST['id_factura'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');

        FacturacionModel::cambiarEstado($id, 'anulada');
        AuditoriaModel::registrar('facturacion', 'anular', "Factura #{$id} — {$motivo}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
