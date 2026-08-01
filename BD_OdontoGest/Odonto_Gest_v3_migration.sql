-- ============================================================
--  MIGRACIÓN v3 — OdontoGest
--  Agrega tablas y campos faltantes detectados en Excel
--  Ejecutar sobre la BD existente (odonto_gest)
--  Orden: CREATE nuevas tablas → ALTER tablas existentes
-- ============================================================

USE odonto_gest;

-- ============================================================
--  PASO 1: NUEVAS TABLAS CATÁLOGO
--  (deben existir antes de los ALTER que les referencian)
-- ============================================================

-- Tipos de sangre (A+, A-, B+, B-, AB+, AB-, O+, O-)
CREATE TABLE IF NOT EXISTS sangres (
    id          TINYINT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(10)         NOT NULL UNIQUE COMMENT 'Ej: A+, O-, AB+'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO sangres (descripcion) VALUES
    ('A+'),('A-'),('B+'),('B-'),('AB+'),('AB-'),('O+'),('O-');

-- Catálogo de alergias comunes
CREATE TABLE IF NOT EXISTS alergias_catalogo (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(200)    NOT NULL UNIQUE,
    estado      ENUM('activa','inactiva') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catálogo de enfermedades sistémicas relevantes
CREATE TABLE IF NOT EXISTS enfermedades_catalogo (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(200)    NOT NULL UNIQUE,
    estado      ENUM('activa','inactiva') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Catálogo de medicamentos
CREATE TABLE IF NOT EXISTS medicamentos_catalogo (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    descripcion VARCHAR(200)    NOT NULL UNIQUE,
    estado      ENUM('activa','inactiva') NOT NULL DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cargos del personal (Odontólogo, Asistente, Recepcionista, etc.)
CREATE TABLE IF NOT EXISTS cargos (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100)    NOT NULL UNIQUE,
    descripcion VARCHAR(300),
    estado      ENUM('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO cargos (nombre, descripcion) VALUES
    ('Odontólogo General',  'Atención clínica general'),
    ('Especialista',        'Atención en especialidad'),
    ('Asistente Dental',    'Asistencia en silla'),
    ('Recepcionista',       'Atención al cliente y agenda'),
    ('Administrador',       'Gestión administrativa');

-- Sucursales de la clínica
CREATE TABLE IF NOT EXISTS sucursales (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(150)    NOT NULL,
    ubicacion   VARCHAR(400),
    contacto    VARCHAR(100),
    telefono    VARCHAR(20),
    estado      ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO sucursales (nombre, ubicacion, contacto) VALUES
    ('Sede Principal', 'Tegucigalpa, Honduras', 'Administración');

-- Key-Value para atributos extra de productos/insumos
CREATE TABLE IF NOT EXISTS kv_producto (
    id      INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    kv_key  VARCHAR(100)    NOT NULL COMMENT 'Ej: fabricante, presentacion, lote',
    kv_value VARCHAR(500)   NOT NULL,
    UNIQUE KEY uq_kv_producto (kv_key, kv_value(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Almacenamiento de imágenes con patrón KV
-- key = carpeta/módulo (ej: pacientes, citas, expedientes)
-- value = nombre del archivo
CREATE TABLE IF NOT EXISTS imagenes (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    kv_key      VARCHAR(100)    NOT NULL COMMENT 'Módulo/carpeta: pacientes|citas|expedientes|usuarios',
    kv_value    VARCHAR(500)    NOT NULL COMMENT 'Nombre de archivo o ruta relativa',
    url         VARCHAR(1000)   NOT NULL COMMENT 'URL completa accesible',
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Horarios laborales del personal (disponibilidad por día)
CREATE TABLE IF NOT EXISTS horarios_laborales (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    usuario_id      INT UNSIGNED    NOT NULL,
    dia             ENUM('lunes','martes','miercoles','jueves','viernes',
                         'sabado','domingo') NOT NULL,
    hora_entrada    TIME            NOT NULL,
    hora_salida     TIME            NOT NULL,
    estado          ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_horario (usuario_id, dia),
    CONSTRAINT fk_hl_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notificaciones del sistema para usuarios
CREATE TABLE IF NOT EXISTS notificaciones (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED    NOT NULL,
    mensaje     TEXT            NOT NULL,
    leida       TINYINT(1)      NOT NULL DEFAULT 0,
    fecha       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado      ENUM('activa','archivada') NOT NULL DEFAULT 'activa',
    CONSTRAINT fk_notif_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Configuración general del sistema (clave-valor)
CREATE TABLE IF NOT EXISTS configuracion (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100)    NOT NULL UNIQUE COMMENT 'Clave de configuración',
    valor       VARCHAR(500)    NOT NULL,
    descripcion VARCHAR(300),
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO configuracion (nombre, valor, descripcion) VALUES
    ('clinica_nombre',    'Clínica Dental Paz',    'Nombre de la clínica'),
    ('moneda',            'L',                      'Símbolo de moneda (Lempiras)'),
    ('tasa_isv_reducida', '0.15',                   'ISV 15% bienes sujetos tasa reducida'),
    ('tasa_isv_general',  '0.18',                   'ISV 18% servicios y bienes generales'),
    ('version_bd',        '3.0',                    'Versión del esquema de BD');

-- Auditoría de acciones del sistema
CREATE TABLE IF NOT EXISTS auditoria (
    id          INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED    NOT NULL,
    modulo      ENUM('seguridad','agenda','expedientes','facturacion',
                     'inventario','configuracion') NOT NULL,
    accion      ENUM('crear','editar','eliminar','ver','login','logout') NOT NULL,
    descripcion TEXT,
    ip          VARCHAR(45)     COMMENT 'IPv4 o IPv6',
    fecha       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  PASO 2: ALTER TABLE — tablas existentes
-- ============================================================

-- ── usuarios ─────────────────────────────────────────────────
ALTER TABLE usuarios
    ADD COLUMN usuario          VARCHAR(80)     UNIQUE          AFTER id
                                                COMMENT 'Nombre de usuario para login',
    ADD COLUMN img_perfil_id    INT UNSIGNED                    AFTER telefono
                                                COMMENT 'FK → imagenes.id',
    ADD CONSTRAINT fk_usr_img   FOREIGN KEY (img_perfil_id)     REFERENCES imagenes(id);

-- ── pacientes ────────────────────────────────────────────────
ALTER TABLE pacientes
    ADD COLUMN rtn                          VARCHAR(20)     AFTER numero_identidad
                                            COMMENT 'RTN fiscal Honduras',
    ADD COLUMN estado_civil                 ENUM('soltero','casado','union_libre',
                                                 'divorciado','viudo','otro')
                                            AFTER sexo,
    ADD COLUMN ocupacion                    VARCHAR(150)    AFTER estado_civil,
    ADD COLUMN nombre_contacto_emergencia   VARCHAR(150)    AFTER telefono_emergencia,
    ADD COLUMN responsable_pago             VARCHAR(150)    AFTER nombre_contacto_emergencia,
    ADD COLUMN img_paciente_id              INT UNSIGNED    AFTER responsable_pago
                                            COMMENT 'FK → imagenes.id',
    ADD CONSTRAINT fk_pac_img   FOREIGN KEY (img_paciente_id) REFERENCES imagenes(id);

-- ── expedientes ──────────────────────────────────────────────
-- Agrega FK a sangres y FK a imagen de expediente
ALTER TABLE expedientes
    ADD COLUMN sangre_id            TINYINT UNSIGNED    AFTER paciente_id,
    ADD COLUMN img_expediente_id    INT UNSIGNED        AFTER observaciones
                                    COMMENT 'FK → imagenes.id',
    ADD CONSTRAINT fk_exp_sangre    FOREIGN KEY (sangre_id)         REFERENCES sangres(id),
    ADD CONSTRAINT fk_exp_img       FOREIGN KEY (img_expediente_id) REFERENCES imagenes(id);

-- ── odontograma ──────────────────────────────────────────────
ALTER TABLE odontograma
    ADD COLUMN observaciones TEXT AFTER descripcion;

-- ── historial_tratamientos ───────────────────────────────────
ALTER TABLE historial_tratamientos
    ADD COLUMN costo                DECIMAL(10,2)   NOT NULL DEFAULT 0.00  AFTER estado
                                    COMMENT 'Costo cobrado por este tratamiento (L)',
    ADD COLUMN abono                DECIMAL(10,2)   NOT NULL DEFAULT 0.00  AFTER costo
                                    COMMENT 'Monto abonado hasta el momento',
    ADD COLUMN saldo_pendiente      DECIMAL(10,2)   NOT NULL DEFAULT 0.00  AFTER abono
                                    COMMENT 'costo - abono',
    ADD COLUMN usuario_registro_id  INT UNSIGNED                           AFTER saldo_pendiente,
    ADD CONSTRAINT fk_ht_usr_reg    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id);

-- ── citas ────────────────────────────────────────────────────
ALTER TABLE citas
    ADD COLUMN horario_id   INT UNSIGNED        AFTER sillon_id
                            COMMENT 'FK → horarios_laborales.id',
    ADD COLUMN servicio_id  INT UNSIGNED        AFTER horario_id
                            COMMENT 'Servicio principal de la cita',
    ADD COLUMN img_cita_id  INT UNSIGNED        AFTER notas
                            COMMENT 'FK → imagenes.id (radiografía u otra imagen)',
    ADD COLUMN asistencia   ENUM('pendiente','asistio','no_asistio')
                            NOT NULL DEFAULT 'pendiente' AFTER estado,
    ADD CONSTRAINT fk_cita_horario  FOREIGN KEY (horario_id)  REFERENCES horarios_laborales(id),
    ADD CONSTRAINT fk_cita_servicio FOREIGN KEY (servicio_id) REFERENCES servicios_catalogo(id),
    ADD CONSTRAINT fk_cita_img      FOREIGN KEY (img_cita_id) REFERENCES imagenes(id);

-- ── odontologos ──────────────────────────────────────────────
ALTER TABLE odontologos
    ADD COLUMN rtn              VARCHAR(20)     AFTER numero_licencia
                                COMMENT 'RTN fiscal del profesional',
    ADD COLUMN fecha_nacimiento DATE            AFTER rtn,
    ADD COLUMN cargo_id         INT UNSIGNED    AFTER fecha_nacimiento,
    ADD CONSTRAINT fk_od_cargo  FOREIGN KEY (cargo_id) REFERENCES cargos(id);

-- ── proveedores ──────────────────────────────────────────────
ALTER TABLE proveedores
    ADD COLUMN rtn  VARCHAR(20) AFTER email
                    COMMENT 'RTN fiscal del proveedor';

-- ── insumos ──────────────────────────────────────────────────
ALTER TABLE insumos
    ADD COLUMN kv_producto_id   INT UNSIGNED                    AFTER proveedor_id
                                COMMENT 'FK → kv_producto.id (atributos extra)',
    ADD COLUMN tasa_impuesto    ENUM('0','15','18')
                                NOT NULL DEFAULT '0'            AFTER precio_unitario
                                COMMENT 'ISV aplicable: 0% | 15% | 18%',
    ADD CONSTRAINT fk_ins_kv    FOREIGN KEY (kv_producto_id) REFERENCES kv_producto(id);

-- ── facturas ─────────────────────────────────────────────────
ALTER TABLE facturas
    ADD COLUMN tasa_impuesto        ENUM('0','15','18')
                                    NOT NULL DEFAULT '15'       AFTER descuento
                                    COMMENT 'ISV global de la factura',
    ADD COLUMN metodo_pago          ENUM('efectivo','tarjeta',
                                        'transferencia','otro')
                                    NOT NULL DEFAULT 'efectivo' AFTER estado,
    ADD COLUMN rtn_paciente         VARCHAR(20)                 AFTER metodo_pago
                                    COMMENT 'RTN del paciente para crédito fiscal',
    ADD COLUMN responsible_anulado  INT UNSIGNED                AFTER notas
                                    COMMENT 'Usuario que anuló la factura',
    ADD CONSTRAINT fk_fac_anulado   FOREIGN KEY (responsible_anulado) REFERENCES usuarios(id);

-- ── detalle_factura ──────────────────────────────────────────
ALTER TABLE detalle_factura
    ADD COLUMN tasa_impuesto    ENUM('0','15','18')
                                NOT NULL DEFAULT '0'    AFTER subtotal
                                COMMENT 'ISV por línea (hereda del servicio)';

-- ── servicios_catalogo ───────────────────────────────────────
ALTER TABLE servicios_catalogo
    ADD COLUMN tasa_impuesto    ENUM('0','15','18')
                                NOT NULL DEFAULT '15'   AFTER precio_base
                                COMMENT 'ISV por defecto del servicio';

-- ============================================================
--  PASO 3: TABLAS RELACIONALES N:M (expediente ↔ catálogos)
--  En lugar de TEXT libre, ahora se pueden asociar múltiples
--  registros del catálogo al expediente
-- ============================================================

CREATE TABLE IF NOT EXISTS expediente_alergias (
    expediente_id   INT UNSIGNED    NOT NULL,
    alergia_id      INT UNSIGNED    NOT NULL,
    observacion     VARCHAR(300),
    PRIMARY KEY (expediente_id, alergia_id),
    CONSTRAINT fk_ea_exp     FOREIGN KEY (expediente_id) REFERENCES expedientes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ea_alergia FOREIGN KEY (alergia_id)    REFERENCES alergias_catalogo(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expediente_enfermedades (
    expediente_id   INT UNSIGNED    NOT NULL,
    enfermedad_id   INT UNSIGNED    NOT NULL,
    observacion     VARCHAR(300),
    PRIMARY KEY (expediente_id, enfermedad_id),
    CONSTRAINT fk_ee_exp        FOREIGN KEY (expediente_id) REFERENCES expedientes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ee_enfermedad FOREIGN KEY (enfermedad_id) REFERENCES enfermedades_catalogo(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS expediente_medicamentos (
    expediente_id   INT UNSIGNED    NOT NULL,
    medicamento_id  INT UNSIGNED    NOT NULL,
    dosis           VARCHAR(100),
    PRIMARY KEY (expediente_id, medicamento_id),
    CONSTRAINT fk_em_exp         FOREIGN KEY (expediente_id)  REFERENCES expedientes(id) ON DELETE CASCADE,
    CONSTRAINT fk_em_medicamento FOREIGN KEY (medicamento_id) REFERENCES medicamentos_catalogo(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
--  FIN DE MIGRACIÓN v3
--  Total cambios:
--    12 tablas nuevas
--    3 tablas relacionales N:M
--    13 tablas alteradas
-- ============================================================
