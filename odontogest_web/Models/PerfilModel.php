<?php
class PerfilModel {
    public static function obtener(int $id): array|false {
        $db=Conexion::getInstance();
        $st=$db->prepare("SELECT u.*,r.nombre AS rol_nombre FROM usuarios u JOIN roles r ON r.id_rol=u.id_rol WHERE u.id_usuario=:id");
        $st->execute([':id'=>$id]); return $st->fetch(PDO::FETCH_ASSOC);
    }
    public static function actualizarDatos(int $id, array $d): void {
        Conexion::getInstance()->prepare("UPDATE usuarios SET nombre_completo=:n,correo=:c,telefono=:t WHERE id_usuario=:id")
           ->execute([':n'=>$d['nombre_completo'],':c'=>$d['correo']??null,':t'=>$d['telefono']??null,':id'=>$id]);
    }
    public static function cambiarPassword(int $id, string $hashNuevo): void {
        Conexion::getInstance()->prepare("UPDATE usuarios SET contrasena=:h WHERE id_usuario=:id")
           ->execute([':h'=>$hashNuevo,':id'=>$id]);
    }
    public static function passwordActual(int $id): string {
        $st=Conexion::getInstance()->prepare("SELECT contrasena FROM usuarios WHERE id_usuario=:id");
        $st->execute([':id'=>$id]); return $st->fetchColumn()?:'';
    }
}
