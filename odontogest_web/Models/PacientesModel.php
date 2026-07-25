<?php
/**
 * PacientesModel — Tabla: pacientes
 * Columnas: nombre, apellidos, dni, rtn, fecha_nacimiento, sexo, estado_civil,
 *           ocupacion, telefono, telefono_emergencia, nombre_contacto_emergencia,
 *           responsable_pago, correo, direccion, estado
 */
class PacientesModel {

    public static function listar(array $f): array {
        $db     = Conexion::getInstance();
        $offset = ($f['pagina'] - 1) * 15;
        $where  = ['1=1'];
        $p      = [];

        if (!empty($f['buscar'])) {
            $where[] = "(CONCAT(p.nombre,' ',p.apellidos) LIKE :q OR p.telefono LIKE :q OR p.correo LIKE :q OR p.dni LIKE :q)";
            $p[':q'] = '%'.$f['buscar'].'%';
        }
        if (!empty($f['estado'])) {
            $where[] = 'p.estado = :est';
            $p[':est'] = $f['estado'];
        }

        $w  = implode(' AND ', $where);
        $st = $db->prepare("
            SELECT p.id_paciente, p.nombre, p.apellidos,
                   CONCAT(p.nombre,' ',p.apellidos) AS nombre_completo,
                   p.dni, p.rtn, p.fecha_nacimiento, p.sexo,
                   p.estado_civil, p.ocupacion,
                   p.telefono, p.telefono_emergencia,
                   p.nombre_contacto_emergencia, p.responsable_pago,
                   p.correo, p.direccion, p.estado, p.created_at,
                   (SELECT COUNT(*) FROM citas c WHERE c.id_paciente = p.id_paciente) AS total_citas
            FROM pacientes p
            WHERE $w
            ORDER BY p.apellidos, p.nombre
            LIMIT 15 OFFSET :off
        ");
        foreach ($p as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function total(array $f): int {
        $db    = Conexion::getInstance();
        $where = ['1=1'];
        $p     = [];

        if (!empty($f['buscar'])) {
            $where[] = "(CONCAT(nombre,' ',apellidos) LIKE :q OR telefono LIKE :q OR correo LIKE :q OR dni LIKE :q)";
            $p[':q'] = '%'.$f['buscar'].'%';
        }
        if (!empty($f['estado'])) { $where[] = 'estado = :est'; $p[':est'] = $f['estado']; }

        $w  = implode(' AND ', $where);
        $st = $db->prepare("SELECT COUNT(*) FROM pacientes WHERE $w");
        $st->execute($p);
        return (int)$st->fetchColumn();
    }

    public static function kpis(): array {
        $db = Conexion::getInstance();
        return $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(estado='activo') AS activos,
                SUM(estado='inactivo') AS inactivos,
                SUM(MONTH(created_at)=MONTH(CURDATE()) AND YEAR(created_at)=YEAR(CURDATE())) AS nuevos_mes
            FROM pacientes
        ")->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public static function obtenerPorId(int $id): array|false {
        $db = Conexion::getInstance();
        $st = $db->prepare("
            SELECT p.*, CONCAT(p.nombre,' ',p.apellidos) AS nombre_completo,
                   s.descripcion AS grupo_sangre
            FROM pacientes p
            LEFT JOIN sangres s ON s.id_sangre = (
                SELECT id_sangre FROM expedientes e WHERE e.id_paciente = p.id_paciente LIMIT 1
            )
            WHERE p.id_paciente = :id
        ");
        $st->execute([':id' => $id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public static function insertar(array $d): int {
        $db = Conexion::getInstance();
        $db->prepare("
            INSERT INTO pacientes (
                nombre, apellidos, dni, rtn, fecha_nacimiento, sexo,
                estado_civil, ocupacion, telefono, telefono_emergencia,
                nombre_contacto_emergencia, responsable_pago, correo, direccion, estado
            ) VALUES (
                :nom, :ape, :dni, :rtn, :fnac, :sexo,
                :ecv, :ocu, :tel, :telE,
                :conE, :rspP, :cor, :dir, 'activo'
            )
        ")->execute([
            ':nom'  => $d['nombre'],
            ':ape'  => $d['apellidos'],
            ':dni'  => $d['dni']                        ?? null,
            ':rtn'  => $d['rtn']                        ?? null,
            ':fnac' => $d['fecha_nacimiento']            ?? null,
            ':sexo' => $d['sexo']                        ?? null,
            ':ecv'  => $d['estado_civil']                ?? null,
            ':ocu'  => $d['ocupacion']                   ?? null,
            ':tel'  => $d['telefono']                    ?? null,
            ':telE' => $d['telefono_emergencia']          ?? null,
            ':conE' => $d['nombre_contacto_emergencia']  ?? null,
            ':rspP' => $d['responsable_pago']             ?? null,
            ':cor'  => $d['correo']                      ?? null,
            ':dir'  => $d['direccion']                   ?? null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, array $d): void {
        $db = Conexion::getInstance();
        $db->prepare("
            UPDATE pacientes SET
                nombre=:nom, apellidos=:ape, dni=:dni, rtn=:rtn,
                fecha_nacimiento=:fnac, sexo=:sexo,
                estado_civil=:ecv, ocupacion=:ocu,
                telefono=:tel, telefono_emergencia=:telE,
                nombre_contacto_emergencia=:conE, responsable_pago=:rspP,
                correo=:cor, direccion=:dir, estado=:est
            WHERE id_paciente=:id
        ")->execute([
            ':nom'  => $d['nombre'],
            ':ape'  => $d['apellidos'],
            ':dni'  => $d['dni']                        ?? null,
            ':rtn'  => $d['rtn']                        ?? null,
            ':fnac' => $d['fecha_nacimiento']            ?? null,
            ':sexo' => $d['sexo']                        ?? null,
            ':ecv'  => $d['estado_civil']                ?? null,
            ':ocu'  => $d['ocupacion']                   ?? null,
            ':tel'  => $d['telefono']                    ?? null,
            ':telE' => $d['telefono_emergencia']          ?? null,
            ':conE' => $d['nombre_contacto_emergencia']  ?? null,
            ':rspP' => $d['responsable_pago']             ?? null,
            ':cor'  => $d['correo']                      ?? null,
            ':dir'  => $d['direccion']                   ?? null,
            ':est'  => $d['estado']                      ?? 'activo',
            ':id'   => $id,
        ]);
    }

    public static function cambiarEstado(int $id, string $estado): void {
        Conexion::getInstance()->prepare("UPDATE pacientes SET estado=:e WHERE id_paciente=:id")
            ->execute([':e'=>$estado,':id'=>$id]);
    }
}
