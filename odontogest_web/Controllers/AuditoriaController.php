<?php
/**
 * AuditoriaController
 * index()  → vista con tabla paginada y filtros
 * exportar() → descarga CSV del log filtrado (JSON_METHODS no aplica — se maneja aquí)
 */
class AuditoriaController {

    public function index(): void {
        Auth::requireRol('Administrador');
        $pageTitle = 'Auditoría del Sistema';

        $usuario = trim($_GET['usuario'] ?? '');
        $modulo  = trim($_GET['modulo']  ?? '');
        $accion  = trim($_GET['accion']  ?? '');
        $ip      = trim($_GET['ip']      ?? '');
        $desde   = trim($_GET['desde']   ?? '');
        $hasta   = trim($_GET['hasta']   ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));

        $data    = AuditoriaModel::listar($usuario, $modulo, $accion, $ip, $desde, $hasta, $page);
        $modulos = AuditoriaModel::modulos();
        $acciones= AuditoriaModel::acciones();

        require_once VIEW_PATH . 'Auditoria/index.php';
    }

    public function exportar(): void {
        Auth::requireRol('Administrador');

        $usuario = trim($_GET['usuario'] ?? '');
        $modulo  = trim($_GET['modulo']  ?? '');
        $accion  = trim($_GET['accion']  ?? '');
        $ip      = trim($_GET['ip']      ?? '');
        $desde   = trim($_GET['desde']   ?? '');
        $hasta   = trim($_GET['hasta']   ?? '');

        // Sin paginación para exportar todo (máx 10 000 filas por seguridad)
        $data = AuditoriaModel::listar($usuario, $modulo, $accion, $ip, $desde, $hasta, 1, 10000);

        $fecha = date('Ymd_His');
        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"auditoria_{$fecha}.csv\"");
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        // BOM para Excel UTF-8
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID','Fecha','Usuario','Nombre','Rol','Módulo','Acción','Descripción','IP','User-Agent']);

        foreach ($data['registros'] as $r) {
            fputcsv($out, [
                $r['id_auditoria'],
                $r['fecha'],
                $r['usuario'],
                $r['nombre_completo'],
                $r['rol'],
                $r['modulo'],
                $r['accion'],
                $r['descripcion'],
                $r['ip'],
                $r['user_agent'],
            ]);
        }
        fclose($out);
        exit;
    }
}
