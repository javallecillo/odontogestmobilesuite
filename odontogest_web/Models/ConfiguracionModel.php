<?php
/**
 * ConfiguracionModel — Sistema, sucursal, catálogos clínicos, proveedores
 */
class ConfiguracionModel {

    /* ── Configuración general (key-value) ─────────────────── */
    public static function getAll(): array {
        $rows = Conexion::getInstance()->query(
            "SELECT nombre, valor, descripcion FROM configuracion ORDER BY nombre"
        )->fetchAll(PDO::FETCH_ASSOC);
        $map = [];
        foreach ($rows as $r) $map[$r['nombre']] = $r;
        return $map;
    }

    public static function get(string $key, string $default = ''): string {
        $st = Conexion::getInstance()->prepare("SELECT valor FROM configuracion WHERE nombre=:k");
        $st->execute([':k' => $key]);
        return $st->fetchColumn() ?: $default;
    }

    public static function set(string $key, string $valor): void {
        Conexion::getInstance()->prepare(
            "INSERT INTO configuracion (nombre,valor) VALUES(:k,:v)
             ON DUPLICATE KEY UPDATE valor=:v2, updated_at=NOW()"
        )->execute([':k'=>$key, ':v'=>$valor, ':v2'=>$valor]);
    }

    public static function saveMultiple(array $data): void {
        foreach ($data as $key => $valor) {
            self::set($key, (string)$valor);
        }
    }

    /* ── Sucursal ───────────────────────────────────────────── */
    public static function getSucursal(): array|false {
        return Conexion::getInstance()
            ->query("SELECT * FROM sucursal WHERE id_sucursal=1 LIMIT 1")
            ->fetch(PDO::FETCH_ASSOC);
    }

    public static function guardarSucursal(array $d): void {
        $db = Conexion::getInstance();
        $db->prepare("
            INSERT INTO sucursal (id_sucursal,nombre,ubicacion,contacto,telefono,rtn,cai,estado)
            VALUES (1,:nom,:ub,:cont,:tel,:rtn,:cai,'activa')
            ON DUPLICATE KEY UPDATE
                nombre=:nom2, ubicacion=:ub2, contacto=:cont2,
                telefono=:tel2, rtn=:rtn2, cai=:cai2
        ")->execute([
            ':nom'=>$d['nombre'],    ':nom2'=>$d['nombre'],
            ':ub'=>$d['ubicacion'],  ':ub2'=>$d['ubicacion'],
            ':cont'=>$d['contacto'], ':cont2'=>$d['contacto'],
            ':tel'=>$d['telefono'],  ':tel2'=>$d['telefono'],
            ':rtn'=>$d['rtn']??null, ':rtn2'=>$d['rtn']??null,
            ':cai'=>$d['cai']??null, ':cai2'=>$d['cai']??null,
        ]);
    }

    /* ── Alergias ───────────────────────────────────────────── */
    public static function listarAlergias(): array {
        return Conexion::getInstance()->query(
            "SELECT * FROM alergias ORDER BY descripcion"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function guardarAlergia(array $d): int {
        $db = Conexion::getInstance();
        if (!empty($d['id_alergia'])) {
            $db->prepare("UPDATE alergias SET descripcion=:desc,estado=:est WHERE id_alergia=:id")
               ->execute([':desc'=>$d['descripcion'],':est'=>$d['estado']??'activa',':id'=>$d['id_alergia']]);
            return (int)$d['id_alergia'];
        }
        $db->prepare("INSERT INTO alergias (descripcion,estado) VALUES(:desc,'activa')")
           ->execute([':desc'=>$d['descripcion']]);
        return (int)$db->lastInsertId();
    }
    public static function toggleAlergia(int $id): void {
        Conexion::getInstance()->prepare(
            "UPDATE alergias SET estado=IF(estado='activa','inactiva','activa') WHERE id_alergia=:id"
        )->execute([':id'=>$id]);
    }

    /* ── Enfermedades sistémicas ────────────────────────────── */
    public static function listarEnfermedades(): array {
        return Conexion::getInstance()->query(
            "SELECT * FROM enfermedades ORDER BY descripcion"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function guardarEnfermedad(array $d): int {
        $db = Conexion::getInstance();
        if (!empty($d['id_enfermedad'])) {
            $db->prepare("UPDATE enfermedades SET descripcion=:desc,estado=:est WHERE id_enfermedad=:id")
               ->execute([':desc'=>$d['descripcion'],':est'=>$d['estado']??'activa',':id'=>$d['id_enfermedad']]);
            return (int)$d['id_enfermedad'];
        }
        $db->prepare("INSERT INTO enfermedades (descripcion,estado) VALUES(:desc,'activa')")
           ->execute([':desc'=>$d['descripcion']]);
        return (int)$db->lastInsertId();
    }
    public static function toggleEnfermedad(int $id): void {
        Conexion::getInstance()->prepare(
            "UPDATE enfermedades SET estado=IF(estado='activa','inactiva','activa') WHERE id_enfermedad=:id"
        )->execute([':id'=>$id]);
    }

    /* ── Medicamentos ───────────────────────────────────────── */
    public static function listarMedicamentos(): array {
        return Conexion::getInstance()->query(
            "SELECT * FROM medicamentos ORDER BY descripcion"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function guardarMedicamento(array $d): int {
        $db = Conexion::getInstance();
        if (!empty($d['id_medicamento'])) {
            $db->prepare("UPDATE medicamentos SET descripcion=:desc,estado=:est WHERE id_medicamento=:id")
               ->execute([':desc'=>$d['descripcion'],':est'=>$d['estado']??'activo',':id'=>$d['id_medicamento']]);
            return (int)$d['id_medicamento'];
        }
        $db->prepare("INSERT INTO medicamentos (descripcion,estado) VALUES(:desc,'activo')")
           ->execute([':desc'=>$d['descripcion']]);
        return (int)$db->lastInsertId();
    }
    public static function toggleMedicamento(int $id): void {
        Conexion::getInstance()->prepare(
            "UPDATE medicamentos SET estado=IF(estado='activo','inactivo','activo') WHERE id_medicamento=:id"
        )->execute([':id'=>$id]);
    }

    /* ── Tratamientos ───────────────────────────────────────── */
    public static function listarTratamientos(): array {
        return Conexion::getInstance()->query(
            "SELECT * FROM tratamientos ORDER BY descripcion"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function guardarTratamiento(array $d): int {
        $db = Conexion::getInstance();
        if (!empty($d['id_tratamiento'])) {
            $db->prepare("UPDATE tratamientos SET descripcion=:desc,precio_base=:p,tasa_impuesto=:t,estado=:est WHERE id_tratamiento=:id")
               ->execute([':desc'=>$d['descripcion'],':p'=>$d['precio_base'],':t'=>$d['tasa_impuesto']??'15',':est'=>$d['estado']??'activo',':id'=>$d['id_tratamiento']]);
            return (int)$d['id_tratamiento'];
        }
        $db->prepare("INSERT INTO tratamientos (descripcion,precio_base,tasa_impuesto,estado) VALUES(:desc,:p,:t,'activo')")
           ->execute([':desc'=>$d['descripcion'],':p'=>$d['precio_base'],':t'=>$d['tasa_impuesto']??'15']);
        return (int)$db->lastInsertId();
    }
    public static function toggleTratamiento(int $id): void {
        Conexion::getInstance()->prepare(
            "UPDATE tratamientos SET estado=IF(estado='activo','inactivo','activo') WHERE id_tratamiento=:id"
        )->execute([':id'=>$id]);
    }

    /* ── Especialidades ─────────────────────────────────────── */
    public static function listarEspecialidades(): array {
        return Conexion::getInstance()->query(
            "SELECT * FROM especialidades ORDER BY nombre"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function guardarEspecialidad(array $d): int {
        $db = Conexion::getInstance();
        if (!empty($d['id_especialidad'])) {
            $db->prepare("UPDATE especialidades SET nombre=:n,descripcion=:desc WHERE id_especialidad=:id")
               ->execute([':n'=>$d['nombre'],':desc'=>$d['descripcion']??null,':id'=>$d['id_especialidad']]);
            return (int)$d['id_especialidad'];
        }
        $db->prepare("INSERT INTO especialidades (nombre,descripcion) VALUES(:n,:desc)")
           ->execute([':n'=>$d['nombre'],':desc'=>$d['descripcion']??null]);
        return (int)$db->lastInsertId();
    }

    /* ── Cargos ─────────────────────────────────────────────── */
    public static function listarCargos(): array {
        return Conexion::getInstance()->query(
            "SELECT * FROM cargo ORDER BY nombre"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function guardarCargo(array $d): int {
        $db = Conexion::getInstance();
        if (!empty($d['id_cargo'])) {
            $db->prepare("UPDATE cargo SET nombre=:n,descripcion=:desc,estado=:est WHERE id_cargo=:id")
               ->execute([':n'=>$d['nombre'],':desc'=>$d['descripcion']??null,':est'=>$d['estado']??'activo',':id'=>$d['id_cargo']]);
            return (int)$d['id_cargo'];
        }
        $db->prepare("INSERT INTO cargo (nombre,descripcion,estado) VALUES(:n,:desc,'activo')")
           ->execute([':n'=>$d['nombre'],':desc'=>$d['descripcion']??null]);
        return (int)$db->lastInsertId();
    }
    public static function toggleCargo(int $id): void {
        Conexion::getInstance()->prepare(
            "UPDATE cargo SET estado=IF(estado='activo','inactivo','activo') WHERE id_cargo=:id"
        )->execute([':id'=>$id]);
    }

    /* ── Proveedores ────────────────────────────────────────── */
    public static function listarProveedores(): array {
        return Conexion::getInstance()->query(
            "SELECT * FROM proveedores ORDER BY proveedor"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function guardarProveedor(array $d): int {
        $db = Conexion::getInstance();
        if (!empty($d['id_proveedor'])) {
            $db->prepare("UPDATE proveedores SET proveedor=:n,rtn=:rtn,telefono=:tel,correo=:cor,ubicacion=:ub,contacto_nombre=:cont,estado=:est WHERE id_proveedor=:id")
               ->execute([':n'=>$d['proveedor'],':rtn'=>$d['rtn']??null,':tel'=>$d['telefono']??null,':cor'=>$d['correo']??null,':ub'=>$d['ubicacion']??null,':cont'=>$d['contacto_nombre']??null,':est'=>$d['estado']??'activo',':id'=>$d['id_proveedor']]);
            return (int)$d['id_proveedor'];
        }
        $db->prepare("INSERT INTO proveedores (proveedor,rtn,telefono,correo,ubicacion,contacto_nombre,estado) VALUES(:n,:rtn,:tel,:cor,:ub,:cont,'activo')")
           ->execute([':n'=>$d['proveedor'],':rtn'=>$d['rtn']??null,':tel'=>$d['telefono']??null,':cor'=>$d['correo']??null,':ub'=>$d['ubicacion']??null,':cont'=>$d['contacto_nombre']??null]);
        return (int)$db->lastInsertId();
    }
    public static function toggleProveedor(int $id): void {
        Conexion::getInstance()->prepare(
            "UPDATE proveedores SET estado=IF(estado='activo','inactivo','activo') WHERE id_proveedor=:id"
        )->execute([':id'=>$id]);
    }

    /* ── Tipos de sangre (solo lectura + add) ───────────────── */
    public static function listarSangres(): array {
        return Conexion::getInstance()->query(
            "SELECT id_sangre, descripcion FROM sangres ORDER BY id_sangre"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
