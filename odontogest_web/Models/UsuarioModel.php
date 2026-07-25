<?php
/**
 * UsuarioModel — Acceso a datos de la tabla `usuarios`
 * Usado por: AuthController, UsuariosController
 */
class UsuarioModel {

    // ── Auth ─────────────────────────────────────────────────────

    public static function getByUsuario(string $usuario): array|false {
        $db  = Conexion::getInstance();
        $sql = 'SELECT u.id_usuario, u.usuario, u.contrasena,
                       u.nombre_completo, u.correo, u.telefono,
                       u.estado, u.id_rol, r.nombre AS rol
                FROM usuarios u
                JOIN roles r ON r.id_rol = u.id_rol
                WHERE u.usuario = :usuario
                LIMIT 1';
        $s = $db->prepare($sql);
        $s->execute([':usuario' => $usuario]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }

    public static function getPermisos(int $idUsuario): array {
        $db  = Conexion::getInstance();
        $sql = 'SELECT p.nombre
                FROM permisos p
                JOIN permisos_roles pr ON pr.id_permiso = p.id_permiso
                JOIN usuarios u ON u.id_rol = pr.id_rol
                WHERE u.id_usuario = :id';
        $s = $db->prepare($sql);
        $s->execute([':id' => $idUsuario]);
        return $s->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function actualizarUltimoLogin(int $id): void {
        $db = Conexion::getInstance();
        $db->prepare('UPDATE usuarios SET ultimo_login = NOW() WHERE id_usuario = :id')
           ->execute([':id' => $id]);
    }

    // ── CRUD ─────────────────────────────────────────────────────

    public static function listar(string $q = '', string $estado = '', string $rol = '', int $page = 1, int $perPage = 15): array {
        $db     = Conexion::getInstance();
        $offset = ($page - 1) * $perPage;
        $where  = ['1=1'];
        $params = [];

        if ($q !== '') {
            $where[]           = '(u.nombre_completo LIKE :q OR u.usuario LIKE :q OR u.correo LIKE :q)';
            $params[':q']      = '%' . $q . '%';
        }
        if ($estado !== '') {
            $where[]           = 'u.estado = :estado';
            $params[':estado'] = $estado;
        }
        if ($rol !== '') {
            $where[]           = 'r.nombre = :rol';
            $params[':rol']    = $rol;
        }

        $filtro = implode(' AND ', $where);

        $sCount = $db->prepare("SELECT COUNT(*) FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol WHERE $filtro");
        $sCount->execute($params);
        $total = (int)$sCount->fetchColumn();

        $sData = $db->prepare(
            "SELECT u.id_usuario, u.nombre_completo, u.usuario, u.correo,
                    u.telefono, u.estado, u.ultimo_login, u.created_at,
                    r.nombre AS rol, r.id_rol
             FROM usuarios u
             JOIN roles r ON r.id_rol = u.id_rol
             WHERE $filtro
             ORDER BY u.id_usuario DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $k => $v) $sData->bindValue($k, $v);
        $sData->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $sData->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $sData->execute();
        $rows = $sData->fetchAll(PDO::FETCH_ASSOC);

        return [
            'usuarios' => $rows,
            'total'    => $total,
            'pages'    => (int)ceil($total / $perPage),
        ];
    }

    public static function getById(int $id): array|false {
        $db = Conexion::getInstance();
        $s  = $db->prepare(
            'SELECT u.id_usuario, u.nombre_completo, u.usuario, u.correo,
                    u.telefono, u.estado, u.id_rol, r.nombre AS rol
             FROM usuarios u JOIN roles r ON r.id_rol = u.id_rol
             WHERE u.id_usuario = :id LIMIT 1'
        );
        $s->execute([':id' => $id]);
        return $s->fetch(PDO::FETCH_ASSOC);
    }

    public static function crear(array $d): int {
        $db   = Conexion::getInstance();
        $hash = password_hash($d['contrasena'], PASSWORD_BCRYPT, ['cost' => 12]);
        $s    = $db->prepare(
            'INSERT INTO usuarios (id_rol, usuario, contrasena, nombre_completo, correo, telefono, estado)
             VALUES (:id_rol, :usuario, :hash, :nombre, :correo, :tel, :estado)'
        );
        // Correo/teléfono vacíos → NULL (evita UNIQUE constraint con '')
        $correo  = trim($d['correo']   ?? '');
        $telefono= trim($d['telefono'] ?? '');
        $s->execute([
            ':id_rol'  => (int)$d['id_rol'],
            ':usuario' => trim($d['usuario']),
            ':hash'    => $hash,
            ':nombre'  => trim($d['nombre_completo']),
            ':correo'  => $correo   !== '' ? $correo   : null,
            ':tel'     => $telefono !== '' ? $telefono : null,
            ':estado'  => $d['estado'] ?? 'activo',
        ]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, array $d): bool {
        $db = Conexion::getInstance();
        $s  = $db->prepare(
            'UPDATE usuarios SET id_rol=:id_rol, nombre_completo=:nombre,
             correo=:correo, telefono=:tel, estado=:estado
             WHERE id_usuario=:id'
        );
        return $s->execute([
            ':id_rol'  => $d['id_rol'],
            ':nombre'  => trim($d['nombre_completo']),
            ':correo'  => trim($d['correo'] ?? ''),
            ':tel'     => trim($d['telefono'] ?? ''),
            ':estado'  => $d['estado'] ?? 'activo',
            ':id'      => $id,
        ]);
    }

    public static function toggleEstado(int $id): string {
        $db      = Conexion::getInstance();
        $current = $db->prepare('SELECT estado FROM usuarios WHERE id_usuario = :id');
        $current->execute([':id' => $id]);
        $estado  = $current->fetchColumn();
        $nuevo   = ($estado === 'activo') ? 'inactivo' : 'activo';
        $db->prepare('UPDATE usuarios SET estado=:e WHERE id_usuario=:id')
           ->execute([':e' => $nuevo, ':id' => $id]);
        return $nuevo;
    }

    public static function resetPassword(int $id, string $nuevaPass): bool {
        $db   = Conexion::getInstance();
        $hash = password_hash($nuevaPass, PASSWORD_BCRYPT, ['cost' => 12]);
        return $db->prepare('UPDATE usuarios SET contrasena=:h WHERE id_usuario=:id')
                  ->execute([':h' => $hash, ':id' => $id]);
    }

    public static function usuarioExiste(string $usuario, int $excluirId = 0): bool {
        $db = Conexion::getInstance();
        $s  = $db->prepare('SELECT COUNT(*) FROM usuarios WHERE usuario=:u AND id_usuario != :id');
        $s->execute([':u' => $usuario, ':id' => $excluirId]);
        return (int)$s->fetchColumn() > 0;
    }

    public static function getRoles(): array {
        $db = Conexion::getInstance();
        return $db->query('SELECT id_rol, nombre FROM roles ORDER BY id_rol')->fetchAll(PDO::FETCH_ASSOC);
    }
}
