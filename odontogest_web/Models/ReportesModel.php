<?php
class ReportesModel {
    public static function citas(string $fi, string $ff): array {
        $db=Conexion::getInstance();
        $st=$db->prepare("SELECT DATE(fecha_cita) AS fecha,COUNT(*) AS total,SUM(estado='atendida') AS atendidas,SUM(estado='cancelada') AS canceladas FROM citas WHERE DATE(fecha_cita) BETWEEN :fi AND :ff GROUP BY DATE(fecha_cita) ORDER BY fecha");
        $st->execute([':fi'=>$fi,':ff'=>$ff]); return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function ingresos(string $fi, string $ff): array {
        $db=Conexion::getInstance();
        $st=$db->prepare("SELECT DATE(fecha_emision) AS fecha,COUNT(*) AS facturas,SUM(total) AS total,SUM(impuesto) AS isv FROM factura WHERE DATE(fecha_emision) BETWEEN :fi AND :ff AND estado!='anulada' GROUP BY DATE(fecha_emision) ORDER BY fecha");
        $st->execute([':fi'=>$fi,':ff'=>$ff]); return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function inventario(): array {
        return Conexion::getInstance()->query("SELECT nombre,stock,stock_minimo,precio_costo,precio_venta,estado FROM producto ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    }
}
