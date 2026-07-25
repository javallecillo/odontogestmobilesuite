<?php
class RolesModel {
    public static function listarTodos(): array {
        return Conexion::getInstance()->query("SELECT r.*, COUNT(u.id_usuario) AS total_usuarios FROM roles r LEFT JOIN usuarios u ON u.id_rol=r.id_rol GROUP BY r.id_rol ORDER BY r.nombre")->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function permisos(int $idRol): array {
        $db=$Conexion=Conexion::getInstance();
        $st=$db->prepare("SELECT p.*,(pr.id_rol IS NOT NULL) AS asignado FROM permisos p LEFT JOIN permisos_roles pr ON pr.id_permiso=p.id_permiso AND pr.id_rol=:id ORDER BY p.modulo,p.nombre");
        $st->execute([':id'=>$idRol]); return $st->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function insertar(string $nombre, string $desc, array $permisos): int {
        $db=Conexion::getInstance();
        $db->prepare("INSERT INTO roles (nombre,descripcion) VALUES (:n,:d)")->execute([':n'=>$nombre,':d'=>$desc]);
        $id=(int)$db->lastInsertId();
        foreach($permisos as $p) $db->prepare("INSERT INTO permisos_roles(id_rol,id_permiso) VALUES(:r,:p)")->execute([':r'=>$id,':p'=>(int)$p]);
        return $id;
    }
    public static function actualizar(int $id, string $nombre, string $desc, array $permisos): void {
        $db=Conexion::getInstance();
        $db->prepare("UPDATE roles SET nombre=:n,descripcion=:d WHERE id_rol=:id")->execute([':n'=>$nombre,':d'=>$desc,':id'=>$id]);
        $db->prepare("DELETE FROM permisos_roles WHERE id_rol=:id")->execute([':id'=>$id]);
        foreach($permisos as $p) $db->prepare("INSERT INTO permisos_roles(id_rol,id_permiso) VALUES(:r,:p)")->execute([':r'=>$id,':p'=>(int)$p]);
    }
    public static function eliminar(int $id): void {
        Conexion::getInstance()->prepare("DELETE FROM roles WHERE id_rol=:id")->execute([':id'=>$id]);
    }
}
