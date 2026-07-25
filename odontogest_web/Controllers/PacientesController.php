<?php
/**
 * PacientesController — Gestión de pacientes
 */
class PacientesController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Pacientes';

        $filtros = [
            'buscar'  => $_GET['buscar'] ?? '',
            'estado'  => $_GET['estado'] ?? '',
            'pagina'  => max(1, (int)($_GET['pagina'] ?? 1)),
        ];

        $pacientes = PacientesModel::listar($filtros);
        $total     = PacientesModel::total($filtros);
        $kpis      = PacientesModel::kpis();
        $porPagina = 15;
        $totalPags = max(1, ceil($total / $porPagina));

        require_once VIEW_PATH . 'Pacientes/index.php';
    }

    public function ver(): void {
        Auth::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: ' . APP_URL . 'pacientes'); exit; }

        $pageTitle = 'Ficha de Paciente';
        $paciente  = PacientesModel::obtenerPorId($id);
        if (!$paciente) { header('Location: ' . APP_URL . 'pacientes'); exit; }

        require_once VIEW_PATH . 'Pacientes/ver.php';
    }

    public function crear(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'pacientes'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $data = [
            'nombre'       => trim($_POST['nombre']       ?? ''),
            'apellidos'    => trim($_POST['apellidos']    ?? ''),
            'fecha_nac'    => $_POST['fecha_nac']    ?? null,
            'sexo'         => $_POST['sexo']         ?? '',
            'telefono'     => trim($_POST['telefono'] ?? ''),
            'correo'       => trim($_POST['correo']   ?? ''),
            'direccion'    => trim($_POST['direccion']?? ''),
            'rtn'          => trim($_POST['rtn']      ?? '') ?: null,
            'alergias'     => trim($_POST['alergias'] ?? '') ?: null,
            'grupo_sangre' => $_POST['grupo_sangre']  ?? null,
            'estado'       => 'activo',
        ];

        $id = PacientesModel::insertar($data);
        AuditoriaModel::registrar('agenda', 'crear', "Paciente #{$id}");
        header('Location: ' . APP_URL . 'pacientes?ok=creado');
        exit;
    }

    public function actualizar(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'pacientes'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id   = (int)($_POST['id_paciente'] ?? 0);
        $data = [
            'nombre'       => trim($_POST['nombre']       ?? ''),
            'apellidos'    => trim($_POST['apellidos']    ?? ''),
            'fecha_nac'    => $_POST['fecha_nac']    ?? null,
            'sexo'         => $_POST['sexo']         ?? '',
            'telefono'     => trim($_POST['telefono'] ?? ''),
            'correo'       => trim($_POST['correo']   ?? ''),
            'direccion'    => trim($_POST['direccion']?? ''),
            'rtn'          => trim($_POST['rtn']      ?? '') ?: null,
            'alergias'     => trim($_POST['alergias'] ?? '') ?: null,
            'grupo_sangre' => $_POST['grupo_sangre']  ?? null,
            'estado'       => $_POST['estado']        ?? 'activo',
        ];

        PacientesModel::actualizar($id, $data);
        AuditoriaModel::registrar('agenda', 'editar', "Paciente #{$id}");
        header('Location: ' . APP_URL . 'pacientes?ok=actualizado');
        exit;
    }

    public function eliminar(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'pacientes'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id = (int)($_POST['id_paciente'] ?? 0);
        PacientesModel::cambiarEstado($id, 'inactivo');
        AuditoriaModel::registrar('agenda', 'eliminar', "Paciente #{$id}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
