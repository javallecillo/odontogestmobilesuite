<?php
/**
 * AuthController — Login / Logout
 * - index()         → GET  Auth/index        → muestra formulario (sin template)
 * - procesarLogin() → POST Auth/procesarLogin → JSON (en JSON_METHODS)
 * - logout()        → GET  Auth/logout        → JSON_METHODS pero redirige
 */
class AuthController {

    public function index(): void {
        if (Auth::isLoggedIn()) {
            header('Location: ' . APP_URL . 'Dashboard/index');
            exit;
        }
        $expired = !empty($_GET['expired']);
        require_once VIEW_PATH . 'Auth/index.php';
    }

    public function procesarLogin(): void {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }

        // ── Rate limiting: máx 5 intentos → lockout 5 min ────────
        $intentos = (int)($_SESSION['login_intentos'] ?? 0);
        $lockout  = (int)($_SESSION['login_lockout']  ?? 0);

        if ($lockout > time()) {
            $resta = ceil(($lockout - time()) / 60);
            echo json_encode(['success' => false,
                'error' => "Demasiados intentos fallidos. Espera {$resta} minuto(s)."]);
            exit;
        }

        $body     = json_decode(file_get_contents('php://input'), true);
        $usuario  = trim($body['usuario']   ?? '');
        $password = $body['contrasena']     ?? '';
        $csrf     = $body['_csrf']          ?? '';

        if (!Csrf::verify($csrf)) {
            echo json_encode(['success' => false, 'error' => 'Token CSRF inválido. Recarga la página.']);
            exit;
        }

        if ($usuario === '' || $password === '') {
            echo json_encode(['success' => false, 'error' => 'Completa usuario y contraseña.']);
            exit;
        }

        $user = UsuarioModel::getByUsuario($usuario);

        // Siempre ejecutar password_verify para evitar timing attacks
        $hashDummy = '$2y$12$invalido.invalido.invalido.invalido.invalido.invalido..';
        $hashReal  = $user['contrasena'] ?? $hashDummy;
        $passOk    = password_verify($password, $hashReal);

        if (!$user || !$passOk) {
            $_SESSION['login_intentos'] = $intentos + 1;
            if ($_SESSION['login_intentos'] >= 5) {
                $_SESSION['login_lockout']  = time() + 300; // 5 min
                $_SESSION['login_intentos'] = 0;
            }
            echo json_encode(['success' => false, 'error' => 'Credenciales incorrectas.']);
            exit;
        }

        if ($user['estado'] !== 'activo') {
            echo json_encode(['success' => false, 'error' => 'Cuenta inactiva. Contacta al administrador.']);
            exit;
        }

        // Login exitoso → resetear contadores
        unset($_SESSION['login_intentos'], $_SESSION['login_lockout']);

        $permisos = UsuarioModel::getPermisos((int)$user['id_usuario']);

        Auth::login([
            'id_usuario'     => $user['id_usuario'],
            'nombre'         => $user['nombre_completo'],
            'usuario'        => $user['usuario'],
            'rol'            => $user['rol'],
            'permisos'       => $permisos,
        ]);

        UsuarioModel::actualizarUltimoLogin((int)$user['id_usuario']);

        // Auditoría: login exitoso (el usuario ya está en sesión)
        AuditoriaModel::registrar(
            'seguridad', 'login',
            "Inicio de sesión — usuario: {$user['usuario']} | rol: {$user['rol']}",
            (int)$user['id_usuario']
        );

        echo json_encode(['success' => true, 'redirect' => APP_URL . 'Dashboard/index']);
        exit;
    }

    public function logout(): void {
        // Auditoría ANTES de destruir la sesión
        if (Auth::isLoggedIn()) {
            AuditoriaModel::registrar('seguridad', 'logout',
                'Cierre de sesión — usuario: ' . (Auth::get('usuario') ?? ''));
        }
        Auth::logout();
        header('Location: ' . APP_URL . 'Auth/index');
        exit;
    }
}
