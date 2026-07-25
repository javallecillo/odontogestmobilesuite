<?php
class InventarioModel {
    public static function listar(array $f): array {
        $db = Conexion::getInstance();
        $offset = ($f['pagina']-1)*15;
        $where = ['1=1']; $p = [];
        if (!empty($f['buscar'])) { $where[]='p.nombre LIKE :q'; $p[':q']='%'.$f['buscar'].'%'; }
        if (!empty($f['estado'])) { $where[]='p.estado=:est';    $p[':est']=$f['estado']; }
        $w = implode(' AND ',$where);
        $st = $db->prepare("
            SELECT p.*,
                   CASE WHEN p.stock=0 THEN 'agotado'
                        WHEN p.stock<=p.stock_minimo THEN 'critico'
                        WHEN p.stock<=p.stock_minimo*1.5 THEN 'bajo'
                        ELSE 'ok' END AS nivel_stock,
                   pr.proveedor AS proveedor
            FROM producto p LEFT JOIN proveedores pr ON pr.id_proveedor=p.id_proveedor
            WHERE $w ORDER BY p.nombre LIMIT 15 OFFSET :off
        ");
        foreach ($p as $k=>$v) $st->bindValue($k,$v);
        $st->bindValue(':off',$offset,PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function total(array $f): int {
        $db=$Conexion=Conexion::getInstance(); $where=['1=1']; $p=[];
        if (!empty($f['buscar'])) { $where[]='nombre LIKE :q'; $p[':q']='%'.$f['buscar'].'%'; }
        if (!empty($f['estado'])) { $where[]='estado=:est';    $p[':est']=$f['estado']; }
        $st=$db->prepare("SELECT COUNT(*) FROM producto WHERE ".implode(' AND ',$where));
        $st->execute($p); return (int)$st->fetchColumn();
    }
    public static function kpis(): array {
        return Conexion::getInstance()->query("
            SELECT COUNT(*) AS total_productos, SUM(estado='activo') AS activos,
                   SUM(stock=0) AS agotados,
                   SUM(stock<=stock_minimo AND estado='activo') AS bajo_minimo,
                   COALESCE(SUM(stock*precio_costo),0) AS valor_inventario
            FROM producto
        ")->fetch(PDO::FETCH_ASSOC)?:[];
    }
    public static function alertasStock(int $limite=10): array {
        return Conexion::getInstance()->query("
            SELECT id_producto,nombre,stock,stock_minimo,unidad_medida,
                   ROUND((stock/GREATEST(stock_minimo,1))*100,0) AS porcentaje_stock,
                   CASE WHEN stock=0 THEN 'agotado' WHEN stock<=stock_minimo THEN 'critico' ELSE 'bajo' END AS nivel
            FROM producto WHERE stock<=stock_minimo AND estado='activo'
            ORDER BY porcentaje_stock ASC LIMIT $limite
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function listarProveedores(): array {
        $db = Conexion::getInstance();
        // Garantiza que exista al menos uno
        $count = (int)$db->query("SELECT COUNT(*) FROM proveedores")->fetchColumn();
        if ($count === 0) {
            $db->prepare("INSERT INTO proveedores (proveedor,estado) VALUES ('Proveedor General','activo')")->execute();
        }
        return $db->query("SELECT id_proveedor, proveedor FROM proveedores WHERE estado='activo' ORDER BY proveedor")->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function proveedorDefault(): int {
        $db  = Conexion::getInstance();
        $id  = (int)$db->query("SELECT id_proveedor FROM proveedores LIMIT 1")->fetchColumn();
        if (!$id) {
            $db->prepare("INSERT INTO proveedores (proveedor,estado) VALUES ('Proveedor General','activo')")->execute();
            $id = (int)$db->lastInsertId();
        }
        return $id;
    }

    public static function insertar(array $d): int {
        $db  = Conexion::getInstance();
        // Usar proveedor del form si viene, si no el default
        $idP = !empty($d['id_proveedor']) ? (int)$d['id_proveedor'] : self::proveedorDefault();
        $imp = isset($d['tasa_impuesto']) ? (float)$d['tasa_impuesto'] : 0;
        $db->prepare("
            INSERT INTO producto
                (id_proveedor,nombre,descripcion,unidad_medida,stock,stock_minimo,
                 precio_costo,precio_venta,tasa_impuesto,estado)
            VALUES (:prov,:nom,:desc,:uni,:stk,:stm,:pc,:pv,:imp,'activo')
        ")->execute([
            ':prov' => $idP,
            ':nom'  => $d['nombre'],
            ':desc' => $d['descripcion']  ?? null,
            ':uni'  => $d['unidad_medida']?? null,
            ':stk'  => (int)$d['stock'],
            ':stm'  => (int)$d['stock_minimo'],
            ':pc'   => (float)($d['precio_costo'] ?? 0),
            ':pv'   => (float)($d['precio_venta']  ?? 0),
            ':imp'  => $imp,
        ]);
        return (int)$db->lastInsertId();
    }

    public static function actualizar(int $id, array $d): void {
        $idP = !empty($d['id_proveedor']) ? (int)$d['id_proveedor'] : self::proveedorDefault();
        $imp = isset($d['tasa_impuesto']) ? (float)$d['tasa_impuesto'] : 0;
        Conexion::getInstance()->prepare("
            UPDATE producto SET
                id_proveedor=:prov, nombre=:nom, descripcion=:desc,
                unidad_medida=:uni, stock=:stk, stock_minimo=:stm,
                precio_costo=:pc, precio_venta=:pv,
                tasa_impuesto=:imp, estado=:est
            WHERE id_producto=:id
        ")->execute([
            ':prov' => $idP,
            ':nom'  => $d['nombre'],
            ':desc' => $d['descripcion']  ?? null,
            ':uni'  => $d['unidad_medida']?? null,
            ':stk'  => (int)$d['stock'],
            ':stm'  => (int)$d['stock_minimo'],
            ':pc'   => (float)($d['precio_costo'] ?? 0),
            ':pv'   => (float)($d['precio_venta']  ?? 0),
            ':imp'  => $imp,
            ':est'  => $d['estado'] ?? 'activo',
            ':id'   => $id,
        ]);
    }
    public static function ajustarStock(int $id, int $cantidad, string $tipo, string $motivo=''): void {
        $db=Conexion::getInstance();
        if ($tipo==='entrada')  $db->prepare("UPDATE producto SET stock=stock+:c WHERE id_producto=:id")->execute([':c'=>$cantidad,':id'=>$id]);
        elseif($tipo==='salida') $db->prepare("UPDATE producto SET stock=GREATEST(0,stock-:c) WHERE id_producto=:id")->execute([':c'=>$cantidad,':id'=>$id]);
        else                     $db->prepare("UPDATE producto SET stock=GREATEST(0,:c) WHERE id_producto=:id")->execute([':c'=>$cantidad,':id'=>$id]);
        $stk=(int)$db->query("SELECT stock FROM producto WHERE id_producto=$id")->fetchColumn();
        $est=$stk===0?'agotado':'activo';
        $db->prepare("UPDATE producto SET estado=:e WHERE id_producto=:id")->execute([':e'=>$est,':id'=>$id]);
    }
    public static function cambiarEstado(int $id, string $estado): void {
        Conexion::getInstance()->prepare("UPDATE producto SET estado=:e WHERE id_producto=:id")->execute([':e'=>$estado,':id'=>$id]);
    }
}
