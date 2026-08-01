<?php
/**
 * ReportesController — Reportes generales + exportación Excel/PDF
 *
 * Ruta de exportación: reportes/exportar?tipo=citas|ingresos|inventario&formato=excel|pdf
 * El método exportar() está en $metodosRaw en index.php → omite template HTML.
 */
class ReportesController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Reportes';
        require_once VIEW_PATH . 'Reportes/index.php';
    }

    public function citas(): void {
        Auth::requireLogin();
        $pageTitle = 'Reporte de Citas';
        $fecha_ini = $_GET['fecha_ini'] ?? date('Y-m-01');
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
        $datos     = ReportesModel::citas($fecha_ini, $fecha_fin);
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

    /* ═══════════════════════════════════════════════════════════
       EXPORTAR — sin template HTML (está en $metodosRaw del index)
       URL: reportes/exportar?tipo=citas&formato=excel&fecha_ini=...
       ═══════════════════════════════════════════════════════════ */
    public function exportar(): void {
        Auth::requireLogin();

        $tipo    = $_GET['tipo']    ?? '';
        $formato = $_GET['formato'] ?? 'excel';

        match ($tipo) {
            'citas'      => $this->_exportCitas($formato),
            'ingresos'   => $this->_exportIngresos($formato),
            'inventario' => $this->_exportInventario($formato),
            default      => $this->_redirigir(),
        };
    }

    // ── Citas ─────────────────────────────────────────────────────
    private function _exportCitas(string $formato): void {
        $fi   = $_GET['fecha_ini'] ?? date('Y-m-01');
        $ff   = $_GET['fecha_fin'] ?? date('Y-m-d');
        $datos = ReportesModel::citas($fi, $ff);

        if ($formato === 'excel') {
            $encabezados = ['Fecha', 'Total Citas', 'Atendidas', 'Canceladas', 'Efectividad'];
            $filas       = [];
            $totCitas = $totAt = $totCan = 0;

            foreach ($datos as $d) {
                $ef = $d['total'] > 0 ? round($d['atendidas'] / $d['total'] * 100) : 0;
                $filas[] = [$d['fecha'], $d['total'], $d['atendidas'], $d['canceladas'], $ef . '%'];
                $totCitas += $d['total'];
                $totAt    += $d['atendidas'];
                $totCan   += $d['canceladas'];
            }
            $efTotal = $totCitas > 0 ? round($totAt / $totCitas * 100) : 0;
            $totales = ['TOTALES', $totCitas, $totAt, $totCan, $efTotal . '%'];

            XlsxExporter::descargar(
                'Reporte de Citas — OdontoGest',
                "Período: {$fi}  al  {$ff}   ·   Generado: " . date('d/m/Y H:i'),
                $encabezados, $filas, $totales,
                "Reporte_Citas_{$fi}_{$ff}"
            );
        } else {
            // PDF: página HTML independiente optimizada para impresión
            require_once VIEW_PATH . 'Reportes/pdf_citas.php';
        }
    }

    // ── Ingresos ──────────────────────────────────────────────────
    private function _exportIngresos(string $formato): void {
        $fi    = $_GET['fecha_ini'] ?? date('Y-m-01');
        $ff    = $_GET['fecha_fin'] ?? date('Y-m-d');
        $datos = ReportesModel::ingresos($fi, $ff);

        if ($formato === 'excel') {
            $encabezados = ['Fecha', 'Facturas', 'Subtotal (L.)', 'ISV (L.)', 'Total (L.)'];
            $filas       = [];
            $tF = $tSub = $tIsv = $tTot = 0;

            foreach ($datos as $d) {
                $sub      = $d['total'] - $d['isv'];
                $filas[]  = [
                    $d['fecha'],
                    $d['facturas'],
                    number_format($sub, 2),
                    number_format($d['isv'], 2),
                    number_format($d['total'], 2),
                ];
                $tF   += $d['facturas'];
                $tSub += $sub;
                $tIsv += $d['isv'];
                $tTot += $d['total'];
            }
            $totales = ['TOTALES', $tF, number_format($tSub, 2), number_format($tIsv, 2), number_format($tTot, 2)];

            XlsxExporter::descargar(
                'Reporte de Ingresos — OdontoGest',
                "Período: {$fi}  al  {$ff}   ·   Generado: " . date('d/m/Y H:i'),
                $encabezados, $filas, $totales,
                "Reporte_Ingresos_{$fi}_{$ff}"
            );
        } else {
            require_once VIEW_PATH . 'Reportes/pdf_ingresos.php';
        }
    }

    // ── Inventario ────────────────────────────────────────────────
    private function _exportInventario(string $formato): void {
        $datos = ReportesModel::inventario();

        if ($formato === 'excel') {
            $encabezados = ['Producto', 'Stock', 'Mínimo', 'Costo (L.)', 'Venta (L.)', 'Estado'];
            $filas       = [];
            foreach ($datos as $p) {
                $filas[] = [
                    $p['nombre'],
                    $p['stock'],
                    $p['stock_minimo'],
                    number_format($p['precio_costo'], 2),
                    number_format($p['precio_venta'], 2),
                    ucfirst($p['estado']),
                ];
            }

            XlsxExporter::descargar(
                'Reporte de Inventario — OdontoGest',
                'Estado actual al ' . date('d/m/Y H:i'),
                $encabezados, $filas, [],
                'Reporte_Inventario_' . date('Y-m-d')
            );
        } else {
            require_once VIEW_PATH . 'Reportes/pdf_inventario.php';
        }
    }

    private function _redirigir(): void {
        header('Location: ' . APP_URL . 'reportes');
        exit;
    }
}
