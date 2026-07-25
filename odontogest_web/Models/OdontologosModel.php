<?php
class OdontologosModel {

    public static function listar(array $f): array {
        $db     = Conexion::getInstance();
        $offset = ($f['pagina'] - 1) * 15;
        $where  = ['1=1']; $p = [];
        if (!empty($f['buscar'])) {
            $where[] = "(CONCAT(o.nombre,' ',o.apellidos) LIKE :q OR o.numero_licencia LIKE :q OR o.correo LIKE :q)";
            $p[':q'] = '%' . $f['buscar'] . '%';
        }
        if (!empty($f['estado'])) { $where[] = 'o.estado=:est'; $p[':est'] = $f['estado']; }
        $w  = implode(' AND ', $where);
        $st = $db->prepare("
            SELECT o.*, e.nombre AS especialidad, c.nombre AS cargo,
                   u.usuario AS usuario_login
            FROM odontologos o
            LEFT JOIN especialidades e ON e.id_especialidad = o.id_especialidad
            LEFT JOIN cargo          c ON c.id_cargo        = o.id_cargo
            LEFT JOIN usuarios       u ON u.id_usuario      = o.id_usuario
            WHERE $w ORDER BY o.apellidos, o.nombre LIMIT 15 OFFSET :off
        ");
        foreach ($p as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function total(array $f): int {
        $db    = Conexion::getInstance();
        $where = ['1=1']; $p = [];
        if (!empty($f['buscar'])) { $where[] = "(CONCAT(o.nombre,' ',o.apellidos) LIKE :q OR o.numero_licencia LIKE :q)"; $p[':q'] = '%' . $f['buscar'] . '%'; }
        if (!empty($f['estado'])) { $where[] = 'o.estado=:est'; $p[':est'] = $f['estado']; }
        $w  = implode(' AND ', $where);
        $st = $db->prepare("SELECT COUNT(*) FROM odontologos o WHERE $w");
        $st->execute($p);
        return (int)$st->fetchColumn();
    }

    public static function insertar(array $d): int {
        $db = Conexion::getInstance();
        $db->prepare("
            INSERT INTO odontologos
                (id_usuario,id_cargo,id_especialidad,nombre,apellidos,numero_licencia,rtn,dni,telefono,correo,fecha_nacimiento,estado)
            VALUES
                (:usr,:car,:esp,:nom,:ape,:lic,:rtn,:dni,:tel,:cor,:fnac,:est)
        ")->execute([
            ':usr'  => (int)($d['id_usuario']      ?? 0),
            ':car'  => (int)($d['id_cargo']         ?? 0),
            ':esp'  => (int)($d['id_especialidad']  ?? 0),
            ':nom'  => trim($d['nombre']            ?? ''),
            ':ape'  => trim($d['apellidos']         ?? ''),
            ':lic'  => trim($d['numero_licencia']   ?? ''),
            ':rtn'  => trim($d['rtn']               ?? '') ?: null,
            ':dni'  => trim($d['dni']               ?? '') ?: null,
            ':tel'  => trim($d['telefono']          ?? '') ?: null,
            ':cor'  => trim($d['correo']            ?? '') ?: null,
            ':fnac' => $d['fecha_nacimiento']        ?? null ?: null,
            ':est'  => $d['estado']                  ?? 'activo',
        ]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, array $d): void {
        Conexion::getInstance()->prepare("
            UPDATE odontologos SET
                id_cargo=:car, id_especialidad=:esp,
                nombre=:nom, apellidos=:ape, numero_licencia=:lic,
                rtn=:rtn, dni=:dni, telefono=:tel, correo=:cor,
                fecha_nacimiento=:fnac, estado=:est
            WHERE id_odontologo=:id
        ")->execute([
            ':car'  => (int)($d['id_cargo']        ?? 0),
            ':esp'  => (int)($d['id_especialidad'] ?? 0),
            ':nom'  => trim($d['nombre']           ?? ''),
            ':ape'  => trim($d['apellidos']        ?? ''),
            ':lic'  => trim($d['numero_licencia']  ?? ''),
            ':rtn'  => trim($d['rtn']              ?? '') ?: null,
            ':dni'  => trim($d['dni']              ?? '') ?: null,
            ':tel'  => trim($d['telefono']         ?? '') ?: null,
            ':cor'  => trim($d['correo']           ?? '') ?: null,
            ':fnac' => $d['fecha_nacimiento']       ?? null ?: null,
            ':est'  => $d['estado']                 ?? 'activo',
            ':id'   => $id,
        ]);
    }

    public static function toggleEstado(int $id): void {
        Conexion::getInstance()->prepare("
            UPDATE odontologos
            SET estado = IF(estado='activo','inactivo','activo')
            WHERE id_odontologo=:id
        ")->execute([':id' => $id]);
    }

    /** Usuarios que aún no tienen registro en odontologos */
    public static function usuariosSinOdontologo(): array {
        return Conexion::getInstance()->query("
            SELECT u.id_usuario, u.usuario, u.nombre_completo
            FROM usuarios u
            WHERE u.estado='activo'
              AND u.id_usuario NOT IN (SELECT id_usuario FROM odontologos)
            ORDER BY u.nombre_completo
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function cargos(): array {
        return Conexion::getInstance()->query("SELECT id_cargo,nombre FROM cargo WHERE estado='activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function especialidades(): array {
        return Conexion::getInstance()->query("SELECT id_especialidad,nombre FROM especialidades ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    }
}
