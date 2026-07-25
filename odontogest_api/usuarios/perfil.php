<?php
// ── GET  /usuarios/perfil.php  → obtener perfil del usuario autenticado
// ── PUT  /usuarios/perfil.php  → actualizar nombre, usuario, correo, telefono, contraseña

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
$auth = getAuthUser();   // valida Bearer token y retorna id_usuario, rol
$idUsuario = (int)$auth['id_usuario'];

// ── GET: devolver datos del perfil ──────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $db   = getDB();
        $stmt = $db->prepare("
            SELECT u.id_usuario, u.nombre_completo, u.usuario, u.correo,
                   COALESCE(u.telefono, u.telefono_celular, '') AS telefono,
                   u.foto_perfil,
                   r.nombre AS rol
            FROM usuarios u
            JOIN roles r ON r.id_rol = u.id_rol
            WHERE u.id_usuario = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $idUsuario]);
        $user = $stmt->fetch();

        if (!$user) error(404, 'Usuario no encontrado');

        ok([
            'id_usuario'    => $user['id_usuario'],
            'nombre'        => $user['nombre_completo'],
            'usuario'       => $user['usuario'],
            'correo'        => $user['correo']      ?? '',
            'telefono'      => $user['telefono']    ?? '',
            'foto_perfil'   => $user['foto_perfil'] ?? null,
            'rol'           => $user['rol'],
        ]);
    } catch (PDOException $e) {
        error(500, 'Error de base de datos');
    }
}

// ── PUT: actualizar perfil ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body = json_decode(file_get_contents('php://input'), true) ?? [];
    $db   = getDB();

    $sets   = [];
    $params = [':id' => $idUsuario];

    if (!empty($body['nombre'])) {
        $sets[]              = 'nombre_completo = :nombre';
        $params[':nombre']   = trim($body['nombre']);
    }

    if (!empty($body['correo'])) {
        if (!filter_var($body['correo'], FILTER_VALIDATE_EMAIL)) {
            error(400, 'Correo inválido');
        }
        // Verificar que no lo use otro usuario
        $dup = $db->prepare('SELECT id_usuario FROM usuarios WHERE correo = :c AND id_usuario != :id LIMIT 1');
        $dup->execute([':c' => $body['correo'], ':id' => $idUsuario]);
        if ($dup->fetch()) error(409, 'El correo ya está en uso por otro usuario');
        $sets[]            = 'correo = :correo';
        $params[':correo'] = trim($body['correo']);
    }

    if (!empty($body['telefono'])) {
        // Guardar en la columna que exista (telefono o telefono_celular)
        $cols = $db->query("SHOW COLUMNS FROM usuarios LIKE 'telefono'")->fetchAll();
        $colName = count($cols) ? 'telefono' : 'telefono_celular';
        $sets[]               = "$colName = :telefono";
        $params[':telefono']  = trim($body['telefono']);
    }

    if (!empty($body['usuario'])) {
        // Verificar unicidad del username
        $dupU = $db->prepare('SELECT id_usuario FROM usuarios WHERE usuario = :u AND id_usuario != :id LIMIT 1');
        $dupU->execute([':u' => $body['usuario'], ':id' => $idUsuario]);
        if ($dupU->fetch()) error(409, 'El nombre de usuario ya está en uso');
        $sets[]             = 'usuario = :usuario';
        $params[':usuario'] = trim($body['usuario']);
    }

    // Cambio de contraseña — requiere contraseña actual
    if (!empty($body['nueva_contrasena'])) {
        if (strlen($body['nueva_contrasena']) < 8) {
            error(400, 'La nueva contraseña debe tener al menos 8 caracteres');
        }
        // Verificar contraseña actual
        try {
            $chk  = $db->prepare('SELECT contrasena FROM usuarios WHERE id_usuario = :id LIMIT 1');
            $chk->execute([':id' => $idUsuario]);
            $row  = $chk->fetch();
            if (!$row || !password_verify($body['contrasena_actual'] ?? '', $row['contrasena'])) {
                error(401, 'Contraseña actual incorrecta');
            }
        } catch (PDOException $e) {
            error(500, 'Error verificando contraseña');
        }
        $sets[]               = 'contrasena = :pass';
        $params[':pass']      = password_hash($body['nueva_contrasena'], PASSWORD_BCRYPT, ['cost' => 12]);
    }

    if (empty($sets)) {
        error(400, 'No hay campos para actualizar');
    }

    try {
        $sql = 'UPDATE usuarios SET ' . implode(', ', $sets) . ' WHERE id_usuario = :id';
        $db->prepare($sql)->execute($params);
        ok(['mensaje' => 'Perfil actualizado correctamente']);
    } catch (PDOException $e) {
        error(500, 'Error al actualizar: ' . $e->getMessage());
    }
}

error(405, 'Método no permitido');
