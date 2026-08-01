<?php
/**
 * AgendaController — Gestión de citas
 */
class AgendaController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Agenda';

        $filtros = [
            'fecha'   => $_GET['fecha']   ?? '',   // vacío = sin filtro de fecha
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
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'agenda?error=csrf'); exit;
        }
        if (empty($_POST['id_paciente']) || empty($_POST['id_odontologo']) || empty($_POST['fecha_cita'])) {
            header('Location: ' . APP_URL . 'agenda?error=campos_requeridos'); exit;
        }

        $data = [
            'id_paciente'   => (int)$_POST['id_paciente'],
            'id_odontologo' => (int)$_POST['id_odontologo'],
            'id_servicio'   => !empty($_POST['id_servicio']) ? (int)$_POST['id_servicio'] : null,
            'fecha_cita'    => $_POST['fecha_cita'],
            'notas'         => trim($_POST['notas'] ?? ''),
        ];

        try {
            $id    = AgendaModel::insertar($data);
            $fecha = substr(str_replace('T', ' ', $data['fecha_cita']), 0, 10);
            AuditoriaModel::registrar('agenda', 'crear', "Cita #{$id}");
            header('Location: ' . APP_URL . 'agenda?ok=creada&fecha=' . $fecha); exit;
        } catch (PDOException $e) {
            header('Location: ' . APP_URL . 'agenda?error=' . urlencode($e->getMessage())); exit;
        }
    }

    public function actualizar(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'agenda'); exit; }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'agenda?error=csrf'); exit;
        }
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
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'agenda?error=csrf'); exit;
        }
        $id = (int)($_POST['id_cita'] ?? 0);
        AgendaModel::eliminar($id);
        AuditoriaModel::registrar('agenda', 'eliminar', "Cita #{$id}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
