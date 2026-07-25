<?php
/**
 * RolesController — ABM de roles / permisos
 */
class RolesController {

    public function index(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        $pageTitle = 'Roles y Permisos';
        $roles = RolesModel::listarTodos();
        require_once VIEW_PATH . 'Roles/index.php';
    }

    public function permisos(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');

        $id_rol = (int)($_GET['id'] ?? 0);
        if (!$id_rol) { header('Location: ' . APP_URL . 'roles'); exit; }

        $pageTitle = 'Permisos del Rol';
        $roles     = RolesModel::listarTodos();
        $rol       = null;
        foreach ($roles as $r) { if ((int)$r['id_rol'] === $id_rol) { $rol = $r; break; } }
        if (!$rol) { header('Location: ' . APP_URL . 'roles'); exit; }

        $permisos    = RolesModel::permisos($id_rol);
        // Agrupar por módulo
        $porModulo   = [];
        foreach ($permisos as $p) {
            $porModulo[$p['modulo']][] = $p;
        }

        require_once VIEW_PATH . 'Roles/permisos.php';
    }

    public function crear(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'roles'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $permisos    = $_POST['permisos'] ?? [];

        $id = RolesModel::insertar($nombre, $descripcion, $permisos);
        AuditoriaModel::registrar('seguridad', 'crear', "Rol #{$id} — {$nombre}");
        header('Location: ' . APP_URL . 'roles?ok=creado');
        exit;
    }

    public function actualizar(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'roles'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id          = (int)($_POST['id_rol'] ?? 0);
        $nombre      = trim($_POST['nombre']      ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $permisos    = $_POST['permisos'] ?? [];

        RolesModel::actualizar($id, $nombre, $descripcion, $permisos);
        AuditoriaModel::registrar('seguridad', 'editar', "Rol #{$id}");
        header('Location: ' . APP_URL . 'roles?ok=actualizado');
        exit;
    }

    public function eliminar(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'roles'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id = (int)($_POST['id_rol'] ?? 0);
        RolesModel::eliminar($id);
        AuditoriaModel::registrar('seguridad', 'eliminar', "Rol #{$id}");

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
