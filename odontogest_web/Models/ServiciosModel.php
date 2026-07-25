<?php
class ServiciosModel {
    public static function listar(array $f=[]): array {
        $db=Conexion::getInstance();
        $where=['1=1']; $p=[];
        if(!empty($f['buscar'])){$where[]='nombre LIKE :q';$p[':q']='%'.$f['buscar'].'%';}
        if(!empty($f['estado'])){$where[]='estado=:est';$p[':est']=$f['estado'];}
        $w=implode(' AND ',$where);
        $st=$db->prepare("SELECT * FROM servicios WHERE $w ORDER BY nombre LIMIT 50 OFFSET ".((($f['pagina']??1)-1)*50));
        foreach($p as $k=>$v) $st->bindValue($k,$v);
        $st->execute(); return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function total(array $f=[]): int {
        $db=Conexion::getInstance(); $where=['1=1']; $p=[];
        if(!empty($f['buscar'])){$where[]='nombre LIKE :q';$p[':q']='%'.$f['buscar'].'%';}
        if(!empty($f['estado'])){$where[]='estado=:est';$p[':est']=$f['estado'];}
        $st=$db->prepare("SELECT COUNT(*) FROM servicios WHERE ".implode(' AND ',$where));
        $st->execute($p); return (int)$st->fetchColumn();
    }
    public static function todos(): array {
        return Conexion::getInstance()->query("SELECT id_servicio,nombre,precio_base,duracion_min FROM servicios WHERE estado='activo' ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function insertar(array $d): int {
        $db=Conexion::getInstance();
        $db->prepare("INSERT INTO servicios (nombre,descripcion,precio_base,tasa_impuesto,duracion_min,estado) VALUES (:n,:desc,:p,:t,:dur,'activo')")
           ->execute([':n'=>$d['nombre'],':desc'=>$d['descripcion']??null,':p'=>$d['precio_base'],':t'=>$d['tasa_impuesto']??'15',':dur'=>$d['duracion_min']??30]);
        return (int)$db->lastInsertId();
    }
    public static function actualizar(int $id, array $d): void {
        Conexion::getInstance()->prepare("UPDATE servicios SET nombre=:n,descripcion=:desc,precio_base=:p,tasa_impuesto=:t,duracion_min=:dur,estado=:est WHERE id_servicio=:id")
           ->execute([':n'=>$d['nombre'],':desc'=>$d['descripcion']??null,':p'=>$d['precio_base'],':t'=>$d['tasa_impuesto']??'15',':dur'=>$d['duracion_min']??30,':est'=>$d['estado']??'activo',':id'=>$id]);
    }
    public static function eliminar(int $id): void {
        Conexion::getInstance()->prepare("UPDATE servicios SET estado='inactivo' WHERE id_servicio=:id")->execute([':id'=>$id]);
    }
}
