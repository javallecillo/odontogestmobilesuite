<?php
/**
 * AgendaModel — Tabla: citas
 * Columnas clave: fecha_cita DATETIME, estado ENUM('pendiente','confirmada','en_curso','atendida','cancelada','no_asistio')
 */
class AgendaModel {

    public static function listar(array $f): array {
        $db     = Conexion::getInstance();
        $offset = ($f['pagina'] - 1) * 15;
        $where  = ['1=1'];
        $p      = [];

        if (!empty($f['fecha'])) {
            $where[] = 'DATE(c.fecha_cita) = :fecha';
            $p[':fecha'] = $f['fecha'];
        }
        if (!empty($f['estado'])) {
            $where[] = 'c.estado = :estado';
            $p[':estado'] = $f['estado'];
        }
        if (!empty($f['buscar'])) {
            $where[] = "(CONCAT(p.nombre,' ',p.apellidos) LIKE :q OR CONCAT(o.nombre,' ',o.apellidos) LIKE :q)";
            $p[':q'] = '%'.$f['buscar'].'%';
        }

        $w  = implode(' AND ', $where);
        $st = $db->prepare("
            SELECT c.id_cita, c.fecha_cita,
                   DATE(c.fecha_cita) AS fecha, TIME(c.fecha_cita) AS hora,
                   c.estado, c.notas,
                   CONCAT(p.nombre,' ',p.apellidos) AS paciente,
                   p.telefono AS telefono_paciente,
                   CONCAT(o.nombre,' ',o.apellidos) AS odontologo,
                   s.nombre AS servicio
            FROM citas c
            JOIN pacientes   p ON p.id_paciente   = c.id_paciente
            JOIN odontologos o ON o.id_odontologo = c.id_odontologo
            LEFT JOIN servicios s ON s.id_servicio = c.id_servicio
            WHERE $w
            ORDER BY c.fecha_cita DESC
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

        if (!empty($f['fecha']))  { $where[] = 'DATE(c.fecha_cita) = :fecha'; $p[':fecha'] = $f['fecha']; }
        if (!empty($f['estado'])) { $where[] = 'c.estado = :estado';          $p[':estado'] = $f['estado']; }
        if (!empty($f['buscar'])) {
            $where[] = "(CONCAT(p.nombre,' ',p.apellidos) LIKE :q OR CONCAT(o.nombre,' ',o.apellidos) LIKE :q)";
            $p[':q'] = '%'.$f['buscar'].'%';
        }
        $w  = implode(' AND ', $where);
        $st = $db->prepare("SELECT COUNT(*) FROM citas c JOIN pacientes p ON p.id_paciente=c.id_paciente JOIN odontologos o ON o.id_odontologo=c.id_odontologo WHERE $w");
        $st->execute($p);
        return (int)$st->fetchColumn();
    }

    public static function kpis(): array {
        $db = Conexion::getInstance();
        $r = $db->query("
            SELECT
                COUNT(*) AS total_hoy,
                SUM(estado IN ('pendiente','confirmada','en_curso')) AS pendientes,
                SUM(estado = 'atendida') AS atendidas,
                SUM(estado = 'cancelada') AS canceladas
            FROM citas WHERE DATE(fecha_cita) = CURDATE()
        ")->fetch(PDO::FETCH_ASSOC);
        return $r ?: ['total_hoy'=>0,'pendientes'=>0,'atendidas'=>0,'canceladas'=>0];
    }

    public static function insertar(array $d): int {
        $db        = Conexion::getInstance();
        $fechaCita = $d['fecha_cita']; // 'YYYY-MM-DD HH:MM'
        $fecha     = substr($fechaCita, 0, 10);
        $hora      = substr($fechaCita, 11, 5);
        $mapDia    = ['Monday'=>'lunes','Tuesday'=>'martes','Wednesday'=>'miercoles',
                      'Thursday'=>'jueves','Friday'=>'viernes','Saturday'=>'sabado','Sunday'=>'domingo'];
        $dia       = $mapDia[date('l', strtotime($fecha))] ?? 'lunes';

        // Buscar o crear slot de horario para la fecha+hora exacta
        $st = $db->prepare("SELECT id_horario FROM horarios WHERE fecha=:f AND hora=:h LIMIT 1");
        $st->execute([':f'=>$fecha, ':h'=>$hora.':00']);
        $idHorario = $st->fetchColumn();
        if (!$idHorario) {
            $db->prepare("INSERT INTO horarios (dia,hora,duracion_min,fecha,disponible) VALUES (:d,:h,30,:f,1)")
               ->execute([':d'=>$dia,':h'=>$hora.':00',':f'=>$fecha]);
            $idHorario = (int)$db->lastInsertId();
        }

        $db->prepare("
            INSERT INTO citas (id_paciente,id_odontologo,id_horario,id_servicio,fecha_cita,notas,estado)
            VALUES (:pac,:od,:hor,:srv,:fc,:notas,'pendiente')
        ")->execute([
            ':pac'   => $d['id_paciente'],
            ':od'    => $d['id_odontologo'],
            ':hor'   => $idHorario,
            ':srv'   => $d['id_servicio'] ?: null,
            ':fc'    => $fechaCita,
            ':notas' => $d['notas'] ?: null,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function actualizarEstado(int $id, string $estado): void {
        $db = Conexion::getInstance();
        $db->prepare("UPDATE citas SET estado=:e WHERE id_cita=:id")
           ->execute([':e'=>$estado,':id'=>$id]);
    }

    public static function eliminar(int $id): void {
        $db = Conexion::getInstance();
        $db->prepare("DELETE FROM citas WHERE id_cita=:id")->execute([':id'=>$id]);
    }

    public static function listarOdontologos(): array {
        return Conexion::getInstance()->query(
            "SELECT id_odontologo, CONCAT(nombre,' ',apellidos) AS nombre_completo FROM odontologos WHERE estado='activo' ORDER BY nombre"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listarPacientesActivos(): array {
        return Conexion::getInstance()->query(
            "SELECT id_paciente, CONCAT(nombre,' ',apellidos) AS nombre_completo FROM pacientes WHERE estado='activo' ORDER BY nombre LIMIT 500"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
