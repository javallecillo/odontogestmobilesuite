<?php
/**
 * AgendaController — Gestión de citas
 */
class AgendaController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Agenda';

        $filtros = [
            'fecha'   => $_GET['fecha']   ?? date('Y-m-d'),
            'estado'  => $_GET['estado']  ?? '',
            'buscar'  => $_GET['buscar']  ?? '',
            'pagina'  => max(1, (int)($_GET['pagina'] ?? 1)),
        ];

        $citas        = AgendaModel::listar($filtros);
        $total        = AgendaModel::total($filtros);
        $porPagina    = 15;
        $totalPags    = max(1, ceil($total / $porPagina));
        $kpis         = AgendaModel::kpis();
        $odontologos  = AgendaModel::listarOdontologos();
        $pacientes    = AgendaModel::listarPacientesActivos();
        $servicios    = ServiciosModel::todos();

        require_once VIEW_PATH . 'Agenda/index.php';
    }

    public function crear(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'agenda'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $data = [
            'id_paciente'   => (int)($_POST['id_paciente']   ?? 0),
            'id_odontologo' => (int)($_POST['id_odontologo'] ?? 0),
            'id_servicio'   => (int)($_POST['id_servicio']   ?? 0) ?: null,
            'fecha_cita'    => $_POST['fecha_cita']    ?? '',
            'motivo'        => trim($_POST['motivo']    ?? ''),
            'notas'         => trim($_POST['notas']     ?? ''),
        ];

        $id = AgendaModel::insertar($data);
        AuditoriaModel::registrar('agenda', 'crear', "Cita #{$id}");
        header('Location: ' . APP_URL . 'agenda?ok=creada');
        exit;
    }

    public function actualizar(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'agenda'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id     = (int)($_POST['id_cita'] ?? 0);
        $estado = $_POST['estado'] ?? '';

        AgendaModel::actualizarEstado($id, $estado);
        AuditoriaModel::registrar('agenda', 'editar', "Cita #{$id} → {$estado}");
        header('Location: ' . APP_URL . 'agenda?ok=actualizada');
        exit;
    }

    public function eliminar(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'agenda'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id = (int)($_POST['id_cita'] ?? 0);
        AgendaModel::eliminar($id);
        AuditoriaModel::registrar('cita_eliminada', "Cita #{$id}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
