<?php
/**
 * FacturacionModel — Tabla: factura + detalle_factura
 * estado ENUM('emitida','pagada','anulada') | columna: fecha_emision
 */
class FacturacionModel {

    public static function listar(array $f): array {
        $db     = Conexion::getInstance();
        $offset = ($f['pagina'] - 1) * 15;
        $where  = ['1=1'];
        $p      = [];

        if (!empty($f['estado']))    { $where[] = 'f.estado=:est';          $p[':est']=$f['estado']; }
        if (!empty($f['fecha_ini'])) { $where[] = 'DATE(f.fecha_emision)>=:fi'; $p[':fi']=$f['fecha_ini']; }
        if (!empty($f['fecha_fin'])) { $where[] = 'DATE(f.fecha_emision)<=:ff'; $p[':ff']=$f['fecha_fin']; }
        if (!empty($f['buscar'])) {
            $where[] = "(CONCAT(p.nombre,' ',p.apellidos) LIKE :q OR f.numero_factura LIKE :q)";
            $p[':q'] = '%'.$f['buscar'].'%';
        }
        $w  = implode(' AND ', $where);
        $st = $db->prepare("
            SELECT f.id_factura, f.numero_factura, f.estado,
                   f.subtotal, f.descuento, f.impuesto, f.total,
                   f.tasa_impuesto, f.metodo_pago,
                   DATE(f.fecha_emision) AS fecha,
                   CONCAT(p.nombre,' ',p.apellidos) AS paciente
            FROM factura f JOIN pacientes p ON p.id_paciente=f.id_paciente
            WHERE $w ORDER BY f.id_factura DESC
            LIMIT 15 OFFSET :off
        ");
        foreach ($p as $k => $v) $st->bindValue($k, $v);
        $st->bindValue(':off', $offset, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function total(array $f): int {
        $db    = Conexion::getInstance();
        $where = ['1=1']; $p = [];
        if (!empty($f['estado']))    { $where[]='f.estado=:est'; $p[':est']=$f['estado']; }
        if (!empty($f['fecha_ini'])) { $where[]='DATE(f.fecha_emision)>=:fi'; $p[':fi']=$f['fecha_ini']; }
        if (!empty($f['fecha_fin'])) { $where[]='DATE(f.fecha_emision)<=:ff'; $p[':ff']=$f['fecha_fin']; }
        if (!empty($f['buscar']))    {
            $where[]="(CONCAT(p.nombre,' ',p.apellidos) LIKE :q OR f.numero_factura LIKE :q)";
            $p[':q']='%'.$f['buscar'].'%';
        }
        $w  = implode(' AND ', $where);
        $st = $db->prepare("SELECT COUNT(*) FROM factura f JOIN pacientes p ON p.id_paciente=f.id_paciente WHERE $w");
        $st->execute($p);
        return (int)$st->fetchColumn();
    }

    public static function kpis(): array {
        $db = Conexion::getInstance();
        return $db->query("
            SELECT COUNT(*) AS total_facturas,
                   SUM(estado='emitida') AS emitidas,
                   SUM(estado='pagada') AS pagadas,
                   SUM(estado='anulada') AS anuladas,
                   COALESCE(SUM(CASE WHEN estado='pagada' AND MONTH(fecha_emision)=MONTH(CURDATE()) AND YEAR(fecha_emision)=YEAR(CURDATE()) THEN total ELSE 0 END),0) AS ingresos_mes,
                   COALESCE(SUM(CASE WHEN estado='emitida' THEN total ELSE 0 END),0) AS monto_pendiente
            FROM factura
        ")->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public static function obtenerPorId(int $id): array|false {
        $db = Conexion::getInstance();
        $st = $db->prepare("
            SELECT f.*, DATE(f.fecha_emision) AS fecha_fmt,
                   CONCAT(p.nombre,' ',p.apellidos) AS paciente,
                   p.telefono, p.correo, p.rtn
            FROM factura f JOIN pacientes p ON p.id_paciente=f.id_paciente
            WHERE f.id_factura=:id
        ");
        $st->execute([':id'=>$id]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public static function itemsFactura(int $id): array {
        $db = Conexion::getInstance();
        $st = $db->prepare("SELECT d.*, s.nombre AS nombre_servicio FROM detalle_factura d LEFT JOIN servicios s ON s.id_servicio=d.id_servicio WHERE d.id_factura=:id ORDER BY d.id_detalle");
        $st->execute([':id'=>$id]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function insertar(array $d, array $items): int {
        $db = Conexion::getInstance();
        // Número correlativo
        $cons = (int)$db->query("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(numero_factura,'-',-1) AS UNSIGNED)),0)+1 FROM factura")->fetchColumn();
        $num  = 'F-'.date('Ym').'-'.str_pad($cons,5,'0',STR_PAD_LEFT);

        $db->prepare("
            INSERT INTO factura (id_sucursal,id_paciente,id_cita,id_usuario,numero_factura,subtotal,descuento,impuesto,total,tasa_impuesto,metodo_pago,notas,estado,fecha_emision)
            VALUES (1,:pac,:cit,:usr,:num,:sub,0,:imp,:tot,'15',:mp,:notas,'emitida',NOW())
        ")->execute([
            ':pac'=>$d['id_paciente'], ':cit'=>$d['id_cita']??null,
            ':usr'=>Auth::id(),        ':num'=>$num,
            ':sub'=>$d['subtotal'],    ':imp'=>$d['isv'],
            ':tot'=>$d['total'],       ':mp'=>$d['metodo_pago'],
            ':notas'=>$d['notas']??null,
        ]);
        $id = (int)$db->lastInsertId();

        foreach ($items as $item) {
            $db->prepare("INSERT INTO detalle_factura (id_factura,descripcion,cantidad,precio_unitario,subtotal,total_linea) VALUES (:id,:desc,:qty,:pu,:sub,:tot)")
               ->execute([':id'=>$id,':desc'=>$item['descripcion'],':qty'=>$item['cantidad']??1,':pu'=>$item['precio'],':sub'=>$item['subtotal']??$item['precio'],':tot'=>$item['total']??$item['precio']]);
        }
        return $id;
    }

    public static function cambiarEstado(int $id, string $estado): void {
        Conexion::getInstance()->prepare("UPDATE factura SET estado=:e WHERE id_factura=:id")
            ->execute([':e'=>$estado,':id'=>$id]);
    }
}
