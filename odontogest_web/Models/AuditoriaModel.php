<?php
/**
 * AuditoriaModel — Registro y consulta del log de auditoría
 *
 * Tabla: auditoria
 *   id_auditoria, id_usuario, modulo, accion, descripcion, ip, user_agent, fecha
 *
 * MÓDULOS válidos: seguridad | agenda | expedientes | facturacion
 *                  inventario | configuracion | reportes | sistema
 * ACCIONES válidas: crear | editar | eliminar | ver | login | logout | anular
 */
class AuditoriaModel {

    // ── Captura IP real del cliente (IPv4/IPv6, proxy-aware) ─────
    private static function ip(): string {
        foreach (['HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                // X-Forwarded-For puede tener lista; tomar el primero
                $ip = trim(explode(',', $_SERVER[$k])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }

    // ── Registrar una acción ─────────────────────────────────────
    public static function registrar(
        string $modulo,
        string $accion,
        string $descripcion,
        ?int   $idUsuario = null
    ): void {
        $id = $idUsuario ?? Auth::id();
        if (!$id) return; // sin sesión no hay FK válida

        try {
            $db = Conexion::getInstance();
            $db->prepare(
                'INSERT INTO auditoria (id_usuario, modulo, accion, descripcion, ip, user_agent, fecha)
                 VALUES (:id, :modulo, :accion, :desc, :ip, :ua, NOW())'
            )->execute([
                ':id'     => $id,
                ':modulo' => $modulo,
                ':accion' => $accion,
                ':desc'   => mb_substr($descripcion, 0, 5000),
                ':ip'     => self::ip(),
                ':ua'     => mb_substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 300),
            ]);
        } catch (PDOException) {
            // Auditoría no debe romper el flujo principal
        }
    }

    // ── Listar con filtros paginados ─────────────────────────────
    public static function listar(
        string $usuario  = '',
        string $modulo   = '',
        string $accion   = '',
        string $ip       = '',
        string $desde    = '',
        string $hasta    = '',
        int    $page     = 1,
        int    $perPage  = 25
    ): array {
        $db     = Conexion::getInstance();
        $offset = ($page - 1) * $perPage;
        $where  = ['1=1'];
        $params = [];

        if ($usuario !== '') {
            $where[]           = '(u.usuario LIKE :usr OR u.nombre_completo LIKE :usr)';
            $params[':usr']    = '%' . $usuario . '%';
        }
        if ($modulo !== '') {
            $where[]           = 'a.modulo = :mod';
            $params[':mod']    = $modulo;
        }
        if ($accion !== '') {
            $where[]           = 'a.accion = :acc';
            $params[':acc']    = $accion;
        }
        if ($ip !== '') {
            $where[]           = 'a.ip LIKE :ip';
            $params[':ip']     = '%' . $ip . '%';
        }
        if ($desde !== '') {
            $where[]           = 'DATE(a.fecha) >= :desde';
            $params[':desde']  = $desde;
        }
        if ($hasta !== '') {
            $where[]           = 'DATE(a.fecha) <= :hasta';
            $params[':hasta']  = $hasta;
        }

        $filtro = implode(' AND ', $where);
        $base   = "FROM auditoria a
                   JOIN usuarios u ON u.id_usuario = a.id_usuario
                   JOIN roles    r ON r.id_rol     = u.id_rol
                   WHERE $filtro";

        $sCount = $db->prepare("SELECT COUNT(*) $base");
        $sCount->execute($params);
        $total  = (int)$sCount->fetchColumn();

        // Query única con JOIN a roles
        $sData2 = $db->prepare(
            "SELECT a.id_auditoria, a.modulo, a.accion, a.descripcion,
                    a.ip, a.user_agent, a.fecha,
                    u.usuario, u.nombre_completo,
                    r.nombre AS rol
             FROM auditoria a
             JOIN usuarios u ON u.id_usuario = a.id_usuario
             JOIN roles    r ON r.id_rol     = u.id_rol
             WHERE $filtro
             ORDER BY a.fecha DESC
             LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) $sData2->bindValue($k, $v);
        $sData2->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $sData2->bindValue(':off', $offset,  PDO::PARAM_INT);
        $sData2->execute();
        $rows = $sData2->fetchAll(PDO::FETCH_ASSOC);

        return [
            'registros' => $rows,
            'total'     => $total,
            'pages'     => max(1, (int)ceil($total / $perPage)),
        ];
    }

    // ── Catálogos para los selects de filtro ─────────────────────
    public static function modulos(): array {
        return ['seguridad','agenda','expedientes','facturacion',
                'inventario','configuracion','reportes','sistema'];
    }

    public static function acciones(): array {
        return ['crear','editar','eliminar','ver','login','logout','anular'];
    }
}
