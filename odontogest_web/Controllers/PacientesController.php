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
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'pacientes?error=csrf'); exit;
        }
        $data = [
            'nombre'                     => trim($_POST['nombre']                     ?? ''),
            'apellidos'                  => trim($_POST['apellidos']                  ?? ''),
            'dni'                        => trim($_POST['dni']                        ?? '') ?: null,
            'rtn'                        => trim($_POST['rtn']                        ?? '') ?: null,
            'fecha_nacimiento'           => ($_POST['fecha_nacimiento'] ?? '') ?: null,
            'sexo'                       => $_POST['sexo']                            ?? null,
            'estado_civil'               => $_POST['estado_civil']                    ?? null,
            'ocupacion'                  => trim($_POST['ocupacion']                  ?? '') ?: null,
            'telefono'                   => trim($_POST['telefono']                   ?? '') ?: null,
            'correo'                     => trim($_POST['correo']                     ?? '') ?: null,
            'direccion'                  => trim($_POST['direccion']                  ?? '') ?: null,
            'telefono_emergencia'        => trim($_POST['telefono_emergencia']        ?? '') ?: null,
            'nombre_contacto_emergencia' => trim($_POST['nombre_contacto_emergencia'] ?? '') ?: null,
            'responsable_pago'           => trim($_POST['responsable_pago']           ?? '') ?: null,
        ];

        $id = PacientesModel::insertar($data);
        AuditoriaModel::registrar('pacientes', 'crear', "Paciente #{$id}");
        header('Location: ' . APP_URL . 'pacientes?ok=creado');
        exit;
    }

    public function actualizar(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'pacientes'); exit; }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'pacientes?error=csrf'); exit;
        }
        $id   = (int)($_POST['id_paciente'] ?? 0);
        $data = [
            'nombre'                     => trim($_POST['nombre']                     ?? ''),
            'apellidos'                  => trim($_POST['apellidos']                  ?? ''),
            'dni'                        => trim($_POST['dni']                        ?? '') ?: null,
            'rtn'                        => trim($_POST['rtn']                        ?? '') ?: null,
            'fecha_nacimiento'           => ($_POST['fecha_nacimiento'] ?? '') ?: null,
            'sexo'                       => $_POST['sexo']                            ?? null,
            'estado_civil'               => $_POST['estado_civil']                    ?? null,
            'ocupacion'                  => trim($_POST['ocupacion']                  ?? '') ?: null,
            'telefono'                   => trim($_POST['telefono']                   ?? '') ?: null,
            'correo'                     => trim($_POST['correo']                     ?? '') ?: null,
            'direccion'                  => trim($_POST['direccion']                  ?? '') ?: null,
            'telefono_emergencia'        => trim($_POST['telefono_emergencia']        ?? '') ?: null,
            'nombre_contacto_emergencia' => trim($_POST['nombre_contacto_emergencia'] ?? '') ?: null,
            'responsable_pago'           => trim($_POST['responsable_pago']           ?? '') ?: null,
            'estado'                     => $_POST['estado']                          ?? 'activo',
        ];

        PacientesModel::actualizar($id, $data);
        AuditoriaModel::registrar('pacientes', 'editar', "Paciente #{$id}");
        header('Location: ' . APP_URL . 'pacientes?ok=actualizado');
        exit;
    }

    /** GET pacientes/buscar?q=texto → JSON para autocomplete */
    public function buscar(): void {
        Auth::requireLogin();
        $q = trim($_GET['q'] ?? '');
        header('Content-Type: application/json');
        if (mb_strlen($q) < 2) { echo json_encode(['pacientes' => []]); exit; }
        echo json_encode(['pacientes' => PacientesModel::buscar($q)]);
        exit;
    }

    public function eliminar(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'pacientes'); exit; }
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'pacientes?error=csrf'); exit;
        }
        $id = (int)($_POST['id_paciente'] ?? 0);
        PacientesModel::cambiarEstado($id, 'inactivo');
        AuditoriaModel::registrar('pacientes', 'eliminar', "Paciente #{$id}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
