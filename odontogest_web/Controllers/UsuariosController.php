<?php
/**
 * UsuariosController — CRUD de usuarios del sistema
 *
 * index()          GET  → lista paginada
 * nuevo()          GET  → formulario vacío
 * editar($id)      GET  → formulario con datos
 * save()           POST JSON → crear o actualizar
 * toggleEstado()   POST JSON → activar/desactivar
 * resetPassword()  POST JSON → resetear contraseña
 * delete()         POST JSON → eliminación lógica (inactivo)
 */
class UsuariosController {

    public function index(): void {
        Auth::requireRol('Administrador');
        $pageTitle = 'Gestión de Usuarios';

        $q      = trim($_GET['q']      ?? '');
        $estado = trim($_GET['estado'] ?? '');
        $rol    = trim($_GET['rol']    ?? '');
        $page   = max(1, (int)($_GET['page'] ?? 1));

        $data   = UsuarioModel::listar($q, $estado, $rol, $page);
        $roles  = UsuarioModel::getRoles();

        require_once VIEW_PATH . 'Usuarios/index.php';
    }

    public function nuevo(): void {
        Auth::requireRol('Administrador');
        $pageTitle = 'Nuevo Usuario';
        $usuario   = null;
        $roles     = UsuarioModel::getRoles();
        require_once VIEW_PATH . 'Usuarios/form.php';
    }

    public function editar(string $id = ''): void {
        Auth::requireRol('Administrador');
        $id      = (int)$id;
        $usuario = UsuarioModel::getById($id);
        if (!$usuario) { http_response_code(404); echo 'Usuario no encontrado'; exit; }

        $pageTitle = 'Editar Usuario';
        $roles     = UsuarioModel::getRoles();
        require_once VIEW_PATH . 'Usuarios/form.php';
    }

    // ── JSON endpoints ───────────────────────────────────────────

    public function save(): void {
        header('Content-Type: application/json; charset=utf-8');
        Auth::requireRol('Administrador');

        $body = json_decode(file_get_contents('php://input'), true);

        if (!Csrf::verify($body['_csrf'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'CSRF inválido']); exit;
        }

        $id     = (int)($body['id_usuario'] ?? 0);
        $campos = ['nombre_completo', 'id_rol', 'estado'];
        foreach ($campos as $c) {
            if (empty($body[$c])) {
                echo json_encode(['success' => false, 'error' => "Campo requerido: $c"]); exit;
            }
        }

        if ($id === 0) {
            // Crear
            if (empty($body['usuario'])) {
                echo json_encode(['success' => false, 'error' => 'El nombre de usuario es requerido']); exit;
            }
            if (empty($body['contrasena']) || strlen($body['contrasena']) < 6) {
                echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']); exit;
            }
            if (UsuarioModel::usuarioExiste(trim($body['usuario']))) {
                echo json_encode(['success' => false, 'error' => 'El nombre de usuario ya está en uso']); exit;
            }
            $newId = UsuarioModel::crear($body);
            AuditoriaModel::registrar('seguridad', 'crear',
                "Creó usuario @{$body['usuario']} | nombre: {$body['nombre_completo']} | id_rol: {$body['id_rol']}");
            echo json_encode(['success' => true, 'id' => $newId, 'message' => 'Usuario creado correctamente']);
        } else {
            // Actualizar
            $prev = UsuarioModel::getById($id);
            UsuarioModel::actualizar($id, $body);
            AuditoriaModel::registrar('seguridad', 'editar',
                "Actualizó usuario @{$prev['usuario']} (ID:{$id}) | nombre: {$body['nombre_completo']} | estado: {$body['estado']} | id_rol: {$body['id_rol']}");
            echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
        }
        exit;
    }

    public function toggleEstado(): void {
        header('Content-Type: application/json; charset=utf-8');
        Auth::requireRol('Administrador');

        $body  = json_decode(file_get_contents('php://input'), true);
        if (!Csrf::verify($body['_csrf'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'CSRF inválido']); exit;
        }

        $id = (int)($body['id_usuario'] ?? 0);
        if ($id === Auth::id()) {
            echo json_encode(['success' => false, 'error' => 'No puedes desactivar tu propia cuenta']); exit;
        }

        $prev  = UsuarioModel::getById($id);
        $nuevo = UsuarioModel::toggleEstado($id);
        AuditoriaModel::registrar('seguridad', 'editar',
            "Cambió estado de @{$prev['usuario']} (ID:{$id}) → {$nuevo}");
        echo json_encode(['success' => true, 'estado' => $nuevo]);
        exit;
    }

    public function resetPassword(): void {
        header('Content-Type: application/json; charset=utf-8');
        Auth::requireRol('Administrador');

        $body = json_decode(file_get_contents('php://input'), true);
        if (!Csrf::verify($body['_csrf'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'CSRF inválido']); exit;
        }

        $id   = (int)($body['id_usuario'] ?? 0);
        $pass = $body['nueva_contrasena'] ?? '';

        if (strlen($pass) < 6) {
            echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']); exit;
        }

        $prev = UsuarioModel::getById($id);
        UsuarioModel::resetPassword($id, $pass);
        AuditoriaModel::registrar('seguridad', 'editar',
            "Reseteó contraseña de @{$prev['usuario']} (ID:{$id})");
        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada']);
        exit;
    }
}
