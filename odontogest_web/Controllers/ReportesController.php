<?php
/**
 * ReportesController — Reportes generales
 */
class ReportesController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Reportes';
        require_once VIEW_PATH . 'Reportes/index.php';
    }

    public function citas(): void {
        Auth::requireLogin();
        $pageTitle   = 'Reporte de Citas';
        $fecha_ini   = $_GET['fecha_ini'] ?? date('Y-m-01');
        $fecha_fin   = $_GET['fecha_fin'] ?? date('Y-m-d');
        $datos       = ReportesModel::citas($fecha_ini, $fecha_fin);
        require_once VIEW_PATH . 'Reportes/citas.php';
    }

    public function ingresos(): void {
        Auth::requireLogin();
        $pageTitle = 'Reporte de Ingresos';
        $fecha_ini = $_GET['fecha_ini'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $datos     = ReportesModel::ingresos($fecha_ini, $fecha_fin);
        require_once VIEW_PATH . 'Reportes/ingresos.php';
    }

    public function inventario(): void {
        Auth::requireLogin();
        $pageTitle = 'Reporte de Inventario';
        $datos     = ReportesModel::inventario();
        require_once VIEW_PATH . 'Reportes/inventario.php';
    }
}
