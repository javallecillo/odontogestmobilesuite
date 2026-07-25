<?php
class Auth {
    public static function isLoggedIn(): bool {
        return !empty($_SESSION['user']['id_usuario']);
    }

    public static function id(): ?int {
        return isset($_SESSION['user']['id_usuario'])
            ? (int)$_SESSION['user']['id_usuario']
            : null;
    }

    public static function get(string $key): mixed {
        return $_SESSION['user'][$key] ?? null;
    }

    public static function rol(): string {
        return $_SESSION['user']['rol'] ?? '';
    }

    public static function can(string $permiso): bool {
        // Administrador tiene todo
        if (self::rol() === 'Administrador') return true;
        $permisos = $_SESSION['user']['permisos'] ?? [];
        return in_array($permiso, $permisos, true);
    }

    public static function login(array $userData): void {
        session_regenerate_id(true);
        $_SESSION['user'] = $userData;
    }

    public static function logout(): void {
        session_unset();
        session_destroy();
    }

    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_URL . 'Auth/index');
            exit;
        }
    }

    public static function requireRol(string ...$roles): void {
        self::requireLogin();
        if (!in_array(self::rol(), $roles, true)) {
            http_response_code(403);
            exit('Acceso denegado');
        }
    }
}
