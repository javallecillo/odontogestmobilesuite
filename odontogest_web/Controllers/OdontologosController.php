<?php
class OdontologosController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle  = 'Odontólogos';
        $filtros    = [
            'buscar' => $_GET['buscar'] ?? '',
            'estado' => $_GET['estado'] ?? 'activo',
            'pagina' => max(1, (int)($_GET['pagina'] ?? 1)),
        ];
        $odontologos = OdontologosModel::listar($filtros);
        $total       = OdontologosModel::total($filtros);
        $totalPags   = max(1, ceil($total / 15));
        $usuarios    = OdontologosModel::usuariosSinOdontologo();
        $cargos      = OdontologosModel::cargos();
        $especialidades = OdontologosModel::especialidades();
        require_once VIEW_PATH . 'Odontologos/index.php';
    }

    public function crear(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location:' . APP_URL . 'odontologos'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');
        $id = OdontologosModel::insertar($_POST);
        AuditoriaModel::registrar('odontologos', 'crear', "Odontólogo #{$id}");
        header('Location:' . APP_URL . 'odontologos?ok=creado'); exit;
    }

    public function actualizar(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location:' . APP_URL . 'odontologos'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['id_odontologo'] ?? 0);
        OdontologosModel::actualizar($id, $_POST);
        AuditoriaModel::registrar('odontologos', 'editar', "Odontólogo #{$id}");
        header('Location:' . APP_URL . 'odontologos?ok=editado'); exit;
    }

    public function toggleEstado(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location:' . APP_URL . 'odontologos'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');
        $id = (int)($_POST['id_odontologo'] ?? 0);
        OdontologosModel::toggleEstado($id);
        AuditoriaModel::registrar('odontologos', 'toggle', "Odontólogo #{$id}");
        header('Content-Type:application/json');
        echo json_encode(['success' => true]); exit;
    }
}
