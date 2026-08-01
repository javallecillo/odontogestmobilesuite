-- ============================================================
--  OdontoGest — Creación completa de base de datos v3
--  Sistema de gestión de clínica dental — UTH Móvil II 2026
--  35 tablas · 8 módulos · FK validadas
--  Autor: Proyecto Final — Jhair Ríos
--  Ejecutar en XAMPP / MariaDB 10.x o MySQL 8.x
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS odonto_gest;
CREATE DATABASE odonto_gest
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE odonto_gest;

-- ============================================================
--  BLOQUE 1 — SISTEMA (sin dependencias externas primero)
-- ============================================================

-- ── KV_IMG ───────────────────────────────────────────────────
-- Catálogo de módulos/carpetas para organizar imágenes (patrón KV)
CREATE TABLE kv_img (
    id_kv_img   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    kv_key      VARCHAR(100)    NOT NULL COMMENT 'Módulo o carpeta: pacientes|usuarios|citas|expedientes',
    kv_value    VARCHAR(500)    NOT NULL COMMENT 'Sub-clave o metadato del grupo',
    PRIMARY KEY (id_kv_img),
    UNIQUE KEY uq_kv_img (kv_key, kv_value(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de grupos de imágenes';

INSERT INTO kv_img (kv_key, kv_value) VALUES
    ('pacientes',   'foto_perfil'),
    ('usuarios',    'foto_perfil'),
    ('citas',       'radiografia'),
    ('expedientes', 'documento');

-- ── Imagenes ──────────────────────────────────────────────────
-- Almacén central de archivos/imágenes referenciados por otras tablas
CREATE TABLE imagenes (
    id_imagen   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_kv_img   INT UNSIGNED    NOT NULL COMMENT 'FK → kv_img (módulo propietario)',
    url         VARCHAR(1000)   NOT NULL COMMENT 'URL pública o ruta relativa del archivo',
    nombre_archivo VARCHAR(300) NOT NULL COMMENT 'Nombre original del archivo',
    mime_type   VARCHAR(100)    NOT NULL DEFAULT 'image/jpeg',
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_imagen),
    CONSTRAINT fk_img_kv FOREIGN KEY (id_kv_img) REFERENCES kv_img (id_kv_img)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Almacén central de imágenes y documentos';

-- ── Configuracion ─────────────────────────────────────────────
CREATE TABLE configuracion (
    id_config   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100)    NOT NULL UNIQUE COMMENT 'Clave de configuración',
    valor       VARCHAR(500)    NOT NULL,
    descripcion VARCHAR(300),
    updated_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_config)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Parámetros globales del sistema';

INSERT INTO configuracion (nombre, valor, descripcion) VALUES
    ('clinica_nombre',      'Clínica Dental OdontoGest', 'Nombre de la clínica'),
    ('clinica_rtn',         '08011985123456',             'RTN fiscal de la clínica'),
    ('moneda_simbolo',      'L',                          'Símbolo de moneda (Lempiras)'),
    ('tasa_isv_reducida',   '15',                         'ISV 15% — Servicios'),
    ('tasa_isv_general',    '18',                         'ISV 18% — Bienes generales'),
    ('version_bd',          '3.0',                        'Versión del esquema de BD');

-- ── Reportes ──────────────────────────────────────────────────
CREATE TABLE reportes (
    id_reporte  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    kv_reporte  VARCHAR(100)    NOT NULL COMMENT 'Tipo/módulo del reporte',
    kv_key      VARCHAR(100)    NOT NULL,
    kv_value    TEXT            NOT NULL,
    fecha       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_reporte),
    KEY idx_rep_tipo (kv_reporte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Resultados y parámetros de reportes generados';

-- ============================================================
--  BLOQUE 2 — SEGURIDAD
-- ============================================================

-- ── Roles ─────────────────────────────────────────────────────
CREATE TABLE roles (
    id_rol      TINYINT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(80)         NOT NULL UNIQUE
                                    COMMENT 'Administrador|Odontologo|Recepcionista|Asistente',
    descripcion VARCHAR(300),
    PRIMARY KEY (id_rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Roles del sistema RBAC';

INSERT INTO roles (nombre, descripcion) VALUES
    ('Administrador',  'Acceso total al sistema'),
    ('Odontologo',     'Gestión clínica: agenda, expedientes, odontograma'),
    ('Recepcionista',  'Gestión de citas, pacientes y facturación'),
    ('Asistente',      'Apoyo clínico con acceso limitado');

-- ── Permisos ──────────────────────────────────────────────────
CREATE TABLE permisos (
    id_permiso  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100)    NOT NULL UNIQUE COMMENT 'Ej: pacientes.ver, citas.crear',
    descripcion VARCHAR(300),
    modulo      ENUM('seguridad','agenda','expedientes',
                     'facturacion','inventario','configuracion',
                     'reportes','sistema')
                                NOT NULL DEFAULT 'sistema',
    PRIMARY KEY (id_permiso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Permisos granulares del sistema';

-- ── Usuarios ──────────────────────────────────────────────────
-- REGLA: el backend determina el rol desde las credenciales.
-- NO hay selector de rol en el login del cliente.
CREATE TABLE usuarios (
    id_usuario      INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    id_rol          TINYINT UNSIGNED    NOT NULL,
    usuario         VARCHAR(80)         NOT NULL UNIQUE COMMENT 'Nombre de usuario para login',
    contrasena      VARCHAR(255)        NOT NULL COMMENT 'Hash bcrypt',
    nombre_completo VARCHAR(200)        NOT NULL,
    correo          VARCHAR(150)        UNIQUE,
    telefono        VARCHAR(20),
    id_img          INT UNSIGNED        COMMENT 'FK → imagenes (foto de perfil)',
    estado          ENUM('activo','inactivo','bloqueado')
                                        NOT NULL DEFAULT 'activo',
    ultimo_login    DATETIME,
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP
                                        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario),
    CONSTRAINT fk_usr_rol   FOREIGN KEY (id_rol)   REFERENCES roles    (id_rol),
    CONSTRAINT fk_usr_img   FOREIGN KEY (id_img)   REFERENCES imagenes (id_imagen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Usuarios del sistema — autenticación y sesión';

-- ── Permisos_Roles ────────────────────────────────────────────
CREATE TABLE permisos_roles (
    id_rol      TINYINT UNSIGNED    NOT NULL,
    id_permiso  INT UNSIGNED        NOT NULL,
    PRIMARY KEY (id_rol, id_permiso),
    CONSTRAINT fk_pr_rol        FOREIGN KEY (id_rol)     REFERENCES roles    (id_rol)    ON DELETE CASCADE,
    CONSTRAINT fk_pr_permiso    FOREIGN KEY (id_permiso) REFERENCES permisos (id_permiso) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tabla pivote Roles ↔ Permisos';

-- ── Auditoria ─────────────────────────────────────────────────
CREATE TABLE auditoria (
    id_auditoria    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_usuario      INT UNSIGNED    NOT NULL,
    modulo          ENUM('seguridad','agenda','expedientes',
                         'facturacion','inventario','configuracion',
                         'reportes','sistema')              NOT NULL,
    accion          ENUM('crear','editar','eliminar','ver',
                         'login','logout','anular')         NOT NULL,
    descripcion     TEXT,
    ip              VARCHAR(45)     COMMENT 'IPv4 o IPv6',
    user_agent      VARCHAR(300),
    fecha           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_auditoria),
    KEY idx_aud_usuario (id_usuario),
    KEY idx_aud_fecha   (fecha),
    CONSTRAINT fk_aud_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro de auditoría de acciones del sistema';

-- ── Notificaciones ────────────────────────────────────────────
CREATE TABLE notificaciones (
    id_notificacion INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_usuario      INT UNSIGNED    NOT NULL,
    titulo          VARCHAR(150)    NOT NULL,
    mensaje         TEXT            NOT NULL,
    leida           TINYINT(1)      NOT NULL DEFAULT 0,
    estado          ENUM('activa','archivada') NOT NULL DEFAULT 'activa',
    fecha           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_notificacion),
    KEY idx_notif_usuario (id_usuario),
    CONSTRAINT fk_notif_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Notificaciones internas del sistema';

-- ============================================================
--  BLOQUE 3 — PERSONAL / CLÍNICA
-- ============================================================

-- ── Cargo ─────────────────────────────────────────────────────
CREATE TABLE cargo (
    id_cargo    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(100)    NOT NULL UNIQUE,
    descripcion VARCHAR(300),
    estado      ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    PRIMARY KEY (id_cargo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Cargos del personal de la clínica';

INSERT INTO cargo (nombre, descripcion) VALUES
    ('Odontólogo General',  'Atención clínica general'),
    ('Especialista',        'Atención en especialidad odontológica'),
    ('Asistente Dental',    'Apoyo en silla durante procedimientos'),
    ('Recepcionista',       'Atención al cliente, agenda y cobros'),
    ('Administrador',       'Gestión administrativa y de sistema');

-- ── Especialidades ────────────────────────────────────────────
CREATE TABLE especialidades (
    id_especialidad INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(120)    NOT NULL UNIQUE,
    descripcion     VARCHAR(300),
    PRIMARY KEY (id_especialidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Especialidades odontológicas';

INSERT INTO especialidades (nombre) VALUES
    ('Odontología General'),('Ortodoncia'),('Endodoncia'),
    ('Periodoncia'),('Cirugía Maxilofacial'),('Odontopediatría'),
    ('Implantología'),('Estética Dental');

-- ── Odontologos ───────────────────────────────────────────────
CREATE TABLE odontologos (
    id_odontologo   INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    id_usuario      INT UNSIGNED        NOT NULL,
    id_cargo        INT UNSIGNED        NOT NULL,
    id_especialidad INT UNSIGNED        NOT NULL,
    nombre          VARCHAR(200)        NOT NULL,
    apellidos       VARCHAR(200)        NOT NULL,
    numero_licencia VARCHAR(50)         NOT NULL UNIQUE COMMENT 'Número de colegiación',
    rtn             VARCHAR(20)         COMMENT 'RTN fiscal del profesional',
    dni             VARCHAR(20)         COMMENT 'DNI/pasaporte',
    telefono        VARCHAR(20),
    correo          VARCHAR(150),
    fecha_nacimiento DATE,
    estado          ENUM('activo','inactivo','vacaciones')
                                        NOT NULL DEFAULT 'activo',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_odontologo),
    CONSTRAINT fk_od_usuario    FOREIGN KEY (id_usuario)      REFERENCES usuarios     (id_usuario),
    CONSTRAINT fk_od_cargo      FOREIGN KEY (id_cargo)        REFERENCES cargo        (id_cargo),
    CONSTRAINT fk_od_especialidad FOREIGN KEY (id_especialidad) REFERENCES especialidades (id_especialidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Odontólogos registrados en la clínica';

-- ── Empleados ─────────────────────────────────────────────────
CREATE TABLE empleados (
    id_empleado     INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    id_usuario      INT UNSIGNED        NOT NULL,
    id_cargo        INT UNSIGNED        NOT NULL,
    nombre          VARCHAR(200)        NOT NULL,
    apellidos       VARCHAR(200)        NOT NULL,
    dni             VARCHAR(20),
    rtn             VARCHAR(20),
    telefono        VARCHAR(20),
    correo          VARCHAR(150),
    fecha_nacimiento DATE,
    fecha_ingreso   DATE                NOT NULL,
    estado          ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    created_at      DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_empleado),
    CONSTRAINT fk_emp_usuario   FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_emp_cargo     FOREIGN KEY (id_cargo)   REFERENCES cargo    (id_cargo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Empleados administrativos y de apoyo';

-- ── HorarioLaboral ────────────────────────────────────────────
CREATE TABLE horario_laboral (
    id_horario_lab  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_usuario      INT UNSIGNED    NOT NULL COMMENT 'Usuario (odontologo o empleado)',
    dia             ENUM('lunes','martes','miercoles','jueves',
                         'viernes','sabado','domingo')  NOT NULL,
    hora_entrada    TIME            NOT NULL,
    hora_salida     TIME            NOT NULL,
    estado          ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    PRIMARY KEY (id_horario_lab),
    UNIQUE KEY uq_horario_dia (id_usuario, dia),
    CONSTRAINT fk_hl_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Horarios laborales semanales del personal';

-- ============================================================
--  BLOQUE 4 — CATÁLOGOS CLÍNICOS
-- ============================================================

-- ── Sangres ───────────────────────────────────────────────────
CREATE TABLE sangres (
    id_sangre   TINYINT UNSIGNED    NOT NULL AUTO_INCREMENT,
    descripcion VARCHAR(10)         NOT NULL UNIQUE COMMENT 'A+, A-, B+, B-, AB+, AB-, O+, O-',
    PRIMARY KEY (id_sangre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tipos de sangre';

INSERT INTO sangres (descripcion) VALUES
    ('A+'),('A-'),('B+'),('B-'),('AB+'),('AB-'),('O+'),('O-');

-- ── Alergias ──────────────────────────────────────────────────
CREATE TABLE alergias (
    id_alergia  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    descripcion VARCHAR(200)    NOT NULL UNIQUE,
    estado      ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
    PRIMARY KEY (id_alergia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de alergias comunes';

INSERT INTO alergias (descripcion) VALUES
    ('Penicilina'),('Amoxicilina'),('Aspirina'),('Ibuprofeno'),
    ('Látex'),('Anestésicos locales'),('Ninguna conocida');

-- ── Enfermedades ──────────────────────────────────────────────
CREATE TABLE enfermedades (
    id_enfermedad   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    descripcion     VARCHAR(200)    NOT NULL UNIQUE,
    estado          ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
    PRIMARY KEY (id_enfermedad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de enfermedades sistémicas relevantes';

INSERT INTO enfermedades (descripcion) VALUES
    ('Diabetes Mellitus tipo 1'),('Diabetes Mellitus tipo 2'),
    ('Hipertensión arterial'),('Cardiopatía'),('Coagulopatía'),
    ('VIH/SIDA'),('Hepatitis B'),('Hepatitis C'),('Ninguna conocida');

-- ── Medicamentos ──────────────────────────────────────────────
CREATE TABLE medicamentos (
    id_medicamento  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    descripcion     VARCHAR(200)    NOT NULL UNIQUE,
    estado          ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    PRIMARY KEY (id_medicamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de medicamentos en uso actual';

-- ── Tratamientos ──────────────────────────────────────────────
CREATE TABLE tratamientos (
    id_tratamiento  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    descripcion     VARCHAR(200)    NOT NULL UNIQUE,
    precio_base     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    tasa_impuesto   ENUM('0','15','18') NOT NULL DEFAULT '15'
                                    COMMENT 'ISV aplicable al tratamiento',
    estado          ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    PRIMARY KEY (id_tratamiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de tratamientos odontológicos';

INSERT INTO tratamientos (descripcion, precio_base, tasa_impuesto) VALUES
    ('Limpieza dental',         500.00,'15'),
    ('Extracción simple',       800.00,'15'),
    ('Extracción quirúrgica',  1500.00,'15'),
    ('Endodoncia (1 conducto)',3500.00,'15'),
    ('Resina compuesta',        750.00,'15'),
    ('Corona de porcelana',    5000.00,'15'),
    ('Blanqueamiento dental',  3000.00,'15'),
    ('Ortodoncia mensualidad', 1500.00,'15'),
    ('Implante dental',       15000.00,'15');

-- ============================================================
--  BLOQUE 5 — PACIENTES
-- ============================================================

-- ── Pacientes ─────────────────────────────────────────────────
CREATE TABLE pacientes (
    id_paciente                 INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre                      VARCHAR(150)    NOT NULL,
    apellidos                   VARCHAR(150)    NOT NULL,
    dni                         VARCHAR(20)     UNIQUE COMMENT 'DNI / Pasaporte',
    rtn                         VARCHAR(20)     COMMENT 'RTN fiscal Honduras',
    fecha_nacimiento            DATE,
    sexo                        ENUM('M','F','Otro'),
    estado_civil                ENUM('soltero','casado','union_libre',
                                     'divorciado','viudo','otro'),
    ocupacion                   VARCHAR(150),
    telefono                    VARCHAR(20),
    telefono_emergencia         VARCHAR(20),
    nombre_contacto_emergencia  VARCHAR(150),
    responsable_pago            VARCHAR(150),
    correo                      VARCHAR(150),
    direccion                   VARCHAR(400),
    id_img                      INT UNSIGNED    COMMENT 'FK → imagenes (foto del paciente)',
    estado                      ENUM('activo','inactivo','fallecido')
                                                NOT NULL DEFAULT 'activo',
    created_at                  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_paciente),
    KEY idx_pac_nombre (nombre, apellidos),
    CONSTRAINT fk_pac_img   FOREIGN KEY (id_img) REFERENCES imagenes (id_imagen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Datos personales de los pacientes';

-- ── Expedientes ───────────────────────────────────────────────
CREATE TABLE expedientes (
    id_expediente       INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    id_paciente         INT UNSIGNED        NOT NULL,
    id_sangre           TINYINT UNSIGNED    COMMENT 'FK → sangres',
    observaciones       TEXT                COMMENT 'Notas generales del expediente',
    antecedentes        TEXT                COMMENT 'Antecedentes médicos relevantes',
    id_img              INT UNSIGNED        COMMENT 'FK → imagenes (documento adjunto)',
    created_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME            NOT NULL DEFAULT CURRENT_TIMESTAMP
                                            ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_expediente),
    UNIQUE KEY uq_exp_paciente (id_paciente),
    CONSTRAINT fk_exp_paciente  FOREIGN KEY (id_paciente) REFERENCES pacientes (id_paciente) ON DELETE CASCADE,
    CONSTRAINT fk_exp_sangre    FOREIGN KEY (id_sangre)   REFERENCES sangres   (id_sangre),
    CONSTRAINT fk_exp_img       FOREIGN KEY (id_img)      REFERENCES imagenes  (id_imagen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Expediente clínico del paciente (1:1 con Pacientes)';

-- ── expediente_alergias (N:M) ─────────────────────────────────
CREATE TABLE expediente_alergias (
    id_expediente   INT UNSIGNED    NOT NULL,
    id_alergia      INT UNSIGNED    NOT NULL,
    observacion     VARCHAR(300),
    PRIMARY KEY (id_expediente, id_alergia),
    CONSTRAINT fk_ea_expediente FOREIGN KEY (id_expediente) REFERENCES expedientes (id_expediente) ON DELETE CASCADE,
    CONSTRAINT fk_ea_alergia    FOREIGN KEY (id_alergia)    REFERENCES alergias    (id_alergia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Alergias asociadas al expediente del paciente';

-- ── expediente_enfermedades (N:M) ────────────────────────────
CREATE TABLE expediente_enfermedades (
    id_expediente   INT UNSIGNED    NOT NULL,
    id_enfermedad   INT UNSIGNED    NOT NULL,
    observacion     VARCHAR(300),
    PRIMARY KEY (id_expediente, id_enfermedad),
    CONSTRAINT fk_ee_expediente  FOREIGN KEY (id_expediente) REFERENCES expedientes  (id_expediente) ON DELETE CASCADE,
    CONSTRAINT fk_ee_enfermedad  FOREIGN KEY (id_enfermedad) REFERENCES enfermedades (id_enfermedad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Enfermedades sistémicas del paciente';

-- ── expediente_medicamentos (N:M) ────────────────────────────
CREATE TABLE expediente_medicamentos (
    id_expediente   INT UNSIGNED    NOT NULL,
    id_medicamento  INT UNSIGNED    NOT NULL,
    dosis           VARCHAR(100),
    observacion     VARCHAR(300),
    PRIMARY KEY (id_expediente, id_medicamento),
    CONSTRAINT fk_em_expediente  FOREIGN KEY (id_expediente) REFERENCES expedientes  (id_expediente) ON DELETE CASCADE,
    CONSTRAINT fk_em_medicamento FOREIGN KEY (id_medicamento) REFERENCES medicamentos (id_medicamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Medicamentos actuales del paciente';

-- ── Odontograma ───────────────────────────────────────────────
-- Notación FDI: piezas 11–18, 21–28, 31–38, 41–48
CREATE TABLE odontograma (
    id_odontograma  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_expediente   INT UNSIGNED    NOT NULL,
    id_odontologo   INT UNSIGNED    NOT NULL,
    pieza_dental    TINYINT UNSIGNED NOT NULL COMMENT 'Notación FDI: 11-48',
    cara            ENUM('vestibular','palatino','mesial',
                         'distal','oclusal','ninguna')  NOT NULL DEFAULT 'ninguna',
    condicion       VARCHAR(100)    NOT NULL COMMENT 'Caries, restauración, ausente, etc.',
    color_codigo    VARCHAR(10)     NOT NULL DEFAULT '#FF0000' COMMENT 'Color HEX para el odontograma visual',
    descripcion     TEXT,
    observaciones   TEXT,
    fecha_registro  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_odontograma),
    KEY idx_odont_expediente (id_expediente),
    CONSTRAINT fk_odont_expediente  FOREIGN KEY (id_expediente) REFERENCES expedientes  (id_expediente) ON DELETE CASCADE,
    CONSTRAINT fk_odont_odontologo  FOREIGN KEY (id_odontologo) REFERENCES odontologos  (id_odontologo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Registro gráfico de condiciones por pieza dental (FDI)';

-- ── Tratamientos_Historial ────────────────────────────────────
CREATE TABLE tratamientos_historial (
    id_historial            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_paciente             INT UNSIGNED    NOT NULL,
    id_tratamiento          INT UNSIGNED    NOT NULL,
    id_odontologo           INT UNSIGNED    NOT NULL,
    descripcion             TEXT,
    fecha_inicio            DATE            NOT NULL,
    fecha_fin               DATE,
    costo                   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    abono                   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    saldo_pendiente         DECIMAL(10,2)   NOT NULL DEFAULT 0.00
                                            COMMENT 'costo - abono (calculado en app)',
    estado                  ENUM('en_proceso','completado',
                                 'suspendido','cancelado') NOT NULL DEFAULT 'en_proceso',
    id_usuario_registro     INT UNSIGNED    NOT NULL COMMENT 'Quién registró',
    created_at              DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_historial),
    KEY idx_th_paciente (id_paciente),
    CONSTRAINT fk_th_paciente       FOREIGN KEY (id_paciente)          REFERENCES pacientes    (id_paciente),
    CONSTRAINT fk_th_tratamiento    FOREIGN KEY (id_tratamiento)       REFERENCES tratamientos (id_tratamiento),
    CONSTRAINT fk_th_odontologo     FOREIGN KEY (id_odontologo)        REFERENCES odontologos  (id_odontologo),
    CONSTRAINT fk_th_usuario_reg    FOREIGN KEY (id_usuario_registro)  REFERENCES usuarios     (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Historial de tratamientos por paciente';

-- ============================================================
--  BLOQUE 6 — AGENDA
-- ============================================================

-- ── Horarios ──────────────────────────────────────────────────
-- Slots de tiempo disponibles para agendar citas
CREATE TABLE horarios (
    id_horario  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    dia         ENUM('lunes','martes','miercoles','jueves',
                     'viernes','sabado','domingo')  NOT NULL,
    hora        TIME            NOT NULL,
    duracion_min SMALLINT UNSIGNED NOT NULL DEFAULT 30 COMMENT 'Duración del slot en minutos',
    fecha       DATE,
    disponible  TINYINT(1)      NOT NULL DEFAULT 1,
    PRIMARY KEY (id_horario),
    KEY idx_hor_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Slots de tiempo para agenda de citas';

-- ── Servicios ─────────────────────────────────────────────────
CREATE TABLE servicios (
    id_servicio     INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(200)    NOT NULL UNIQUE,
    descripcion     TEXT,
    precio_base     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    tasa_impuesto   ENUM('0','15','18') NOT NULL DEFAULT '15'
                                    COMMENT 'ISV: 0%|15%|18%',
    duracion_min    SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    estado          ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    PRIMARY KEY (id_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Catálogo de servicios ofrecidos por la clínica';

-- ── Citas ─────────────────────────────────────────────────────
CREATE TABLE citas (
    id_cita         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_odontologo   INT UNSIGNED    NOT NULL,
    id_paciente     INT UNSIGNED    NOT NULL,
    id_horario      INT UNSIGNED    NOT NULL,
    id_servicio     INT UNSIGNED,
    id_tratamiento  INT UNSIGNED    COMMENT 'Tratamiento principal de la cita',
    id_img          INT UNSIGNED    COMMENT 'FK → imagenes (radiografía, imagen adjunta)',
    fecha_cita      DATETIME        NOT NULL,
    notas           TEXT,
    estado          ENUM('pendiente','confirmada','en_curso',
                         'atendida','cancelada','no_asistio')
                                    NOT NULL DEFAULT 'pendiente',
    asistencia      ENUM('pendiente','asistio','no_asistio')
                                    NOT NULL DEFAULT 'pendiente',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_cita),
    KEY idx_cita_fecha     (fecha_cita),
    KEY idx_cita_odontologo (id_odontologo),
    KEY idx_cita_paciente  (id_paciente),
    CONSTRAINT fk_cita_odontologo   FOREIGN KEY (id_odontologo)  REFERENCES odontologos  (id_odontologo),
    CONSTRAINT fk_cita_paciente     FOREIGN KEY (id_paciente)    REFERENCES pacientes    (id_paciente),
    CONSTRAINT fk_cita_horario      FOREIGN KEY (id_horario)     REFERENCES horarios     (id_horario),
    CONSTRAINT fk_cita_servicio     FOREIGN KEY (id_servicio)    REFERENCES servicios    (id_servicio),
    CONSTRAINT fk_cita_tratamiento  FOREIGN KEY (id_tratamiento) REFERENCES tratamientos (id_tratamiento),
    CONSTRAINT fk_cita_img          FOREIGN KEY (id_img)         REFERENCES imagenes     (id_imagen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Citas médicas agendadas';

-- ============================================================
--  BLOQUE 7 — INVENTARIO
-- ============================================================

-- ── Proveedores ───────────────────────────────────────────────
CREATE TABLE proveedores (
    id_proveedor    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    proveedor       VARCHAR(200)    NOT NULL,
    rtn             VARCHAR(20)     COMMENT 'RTN fiscal del proveedor',
    telefono        VARCHAR(20),
    correo          VARCHAR(150),
    ubicacion       VARCHAR(400),
    contacto_nombre VARCHAR(150)    COMMENT 'Nombre del contacto comercial',
    estado          ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
    PRIMARY KEY (id_proveedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Proveedores de materiales e insumos';

-- ── KV_Producto ───────────────────────────────────────────────
-- Atributos extra de productos (fabricante, presentación, lote…)
CREATE TABLE kv_producto (
    id_kv_producto  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    kv_key          VARCHAR(100)    NOT NULL COMMENT 'Ej: fabricante, presentacion, lote',
    kv_value        VARCHAR(500)    NOT NULL,
    PRIMARY KEY (id_kv_producto),
    UNIQUE KEY uq_kv_producto (kv_key, kv_value(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Atributos clave-valor de productos';

-- ── Producto ──────────────────────────────────────────────────
CREATE TABLE producto (
    id_producto     INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_proveedor    INT UNSIGNED    NOT NULL,
    id_kv_producto  INT UNSIGNED    COMMENT 'Atributos adicionales (KV)',
    nombre          VARCHAR(200)    NOT NULL,
    descripcion     TEXT,
    stock           INT UNSIGNED    NOT NULL DEFAULT 0,
    stock_minimo    INT UNSIGNED    NOT NULL DEFAULT 5 COMMENT 'Alerta de reorden',
    precio_costo    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    precio_venta    DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    tasa_impuesto   ENUM('0','15','18') NOT NULL DEFAULT '0',
    unidad_medida   VARCHAR(50)     COMMENT 'Caja, unidad, frasco, ml, etc.',
    estado          ENUM('activo','inactivo','agotado') NOT NULL DEFAULT 'activo',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_producto),
    KEY idx_prod_nombre (nombre),
    CONSTRAINT fk_prod_proveedor    FOREIGN KEY (id_proveedor)   REFERENCES proveedores (id_proveedor),
    CONSTRAINT fk_prod_kv           FOREIGN KEY (id_kv_producto) REFERENCES kv_producto (id_kv_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Productos e insumos del inventario';

-- ── Reportes_Inventario ───────────────────────────────────────
CREATE TABLE reportes_inventario (
    id_reporte_inv      INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_producto         INT UNSIGNED    NOT NULL,
    id_usuario_mod      INT UNSIGNED    NOT NULL COMMENT 'Quién realizó el movimiento',
    accion              ENUM('entrada','salida','ajuste','baja') NOT NULL,
    cantidad            INT             NOT NULL COMMENT 'Positivo entrada, negativo salida',
    stock_resultante    INT UNSIGNED    NOT NULL,
    descripcion         VARCHAR(400),
    fecha               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_reporte_inv),
    KEY idx_rinv_producto (id_producto),
    CONSTRAINT fk_rinv_producto     FOREIGN KEY (id_producto)    REFERENCES producto  (id_producto),
    CONSTRAINT fk_rinv_usuario_mod  FOREIGN KEY (id_usuario_mod) REFERENCES usuarios  (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Movimientos de inventario (kardex)';

-- ============================================================
--  BLOQUE 8 — FACTURACIÓN
-- ============================================================

-- ── Sucursal ──────────────────────────────────────────────────
CREATE TABLE sucursal (
    id_sucursal INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre      VARCHAR(150)    NOT NULL,
    ubicacion   VARCHAR(400),
    contacto    VARCHAR(150),
    telefono    VARCHAR(20),
    rtn         VARCHAR(20)     COMMENT 'RTN de la sucursal para facturación',
    cai         VARCHAR(50)     COMMENT 'CAI habilitado por el SAR (Honduras)',
    estado      ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
    created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_sucursal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Sucursales de la clínica';

INSERT INTO sucursal (nombre, ubicacion, contacto)
    VALUES ('Sede Principal', 'Tegucigalpa, Honduras', 'Administración');

-- ── Factura ───────────────────────────────────────────────────
CREATE TABLE factura (
    id_factura              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_sucursal             INT UNSIGNED    NOT NULL,
    id_paciente             INT UNSIGNED    NOT NULL,
    id_cita                 INT UNSIGNED    COMMENT 'Cita asociada (opcional)',
    id_usuario              INT UNSIGNED    NOT NULL COMMENT 'Quien emitió la factura',
    numero_factura          VARCHAR(30)     NOT NULL UNIQUE COMMENT 'Correlativo SAR',
    rtn_paciente            VARCHAR(20)     COMMENT 'RTN del paciente para crédito fiscal',
    subtotal                DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    descuento               DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    impuesto                DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    total                   DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    tasa_impuesto           ENUM('0','15','18') NOT NULL DEFAULT '15',
    metodo_pago             ENUM('efectivo','tarjeta',
                                 'transferencia','otro')
                                            NOT NULL DEFAULT 'efectivo',
    notas                   TEXT,
    estado                  ENUM('emitida','pagada','anulada')
                                            NOT NULL DEFAULT 'emitida',
    responsable_anulado     INT UNSIGNED    COMMENT 'FK → usuarios (quien anuló)',
    motivo_anulacion        VARCHAR(400),
    fecha_emision           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_pago              DATETIME,
    PRIMARY KEY (id_factura),
    KEY idx_fac_paciente (id_paciente),
    KEY idx_fac_fecha    (fecha_emision),
    CONSTRAINT fk_fac_sucursal      FOREIGN KEY (id_sucursal)         REFERENCES sucursal (id_sucursal),
    CONSTRAINT fk_fac_paciente      FOREIGN KEY (id_paciente)         REFERENCES pacientes (id_paciente),
    CONSTRAINT fk_fac_cita          FOREIGN KEY (id_cita)             REFERENCES citas     (id_cita),
    CONSTRAINT fk_fac_usuario       FOREIGN KEY (id_usuario)          REFERENCES usuarios  (id_usuario),
    CONSTRAINT fk_fac_anulado       FOREIGN KEY (responsable_anulado) REFERENCES usuarios  (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Facturas emitidas por la clínica';

-- ── Detalle_Factura ───────────────────────────────────────────
CREATE TABLE detalle_factura (
    id_detalle      INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_factura      INT UNSIGNED    NOT NULL,
    id_servicio     INT UNSIGNED    COMMENT 'Servicio facturado (opcional)',
    id_tratamiento  INT UNSIGNED    COMMENT 'Tratamiento facturado (opcional)',
    descripcion     VARCHAR(400)    NOT NULL,
    cantidad        SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2)   NOT NULL,
    descuento_linea DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    tasa_impuesto   ENUM('0','15','18') NOT NULL DEFAULT '15',
    impuesto_monto  DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    subtotal        DECIMAL(10,2)   NOT NULL COMMENT 'cantidad × precio_unitario',
    total_linea     DECIMAL(10,2)   NOT NULL COMMENT 'subtotal - descuento + impuesto',
    PRIMARY KEY (id_detalle),
    CONSTRAINT fk_det_factura       FOREIGN KEY (id_factura)     REFERENCES factura      (id_factura)    ON DELETE CASCADE,
    CONSTRAINT fk_det_servicio      FOREIGN KEY (id_servicio)    REFERENCES servicios    (id_servicio),
    CONSTRAINT fk_det_tratamiento   FOREIGN KEY (id_tratamiento) REFERENCES tratamientos (id_tratamiento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Líneas de detalle por factura';

-- ── Historial_Facturacion ─────────────────────────────────────
CREATE TABLE historial_facturacion (
    id_historial    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_factura      INT UNSIGNED    NOT NULL,
    id_usuario      INT UNSIGNED    NOT NULL COMMENT 'Quién realizó la acción',
    id_producto     INT UNSIGNED    COMMENT 'Producto/insumo consumido (opcional)',
    accion          ENUM('crear','editar','anular','pagar') NOT NULL,
    descripcion     VARCHAR(400),
    fecha           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_historial),
    CONSTRAINT fk_hf_factura    FOREIGN KEY (id_factura) REFERENCES factura  (id_factura) ON DELETE CASCADE,
    CONSTRAINT fk_hf_usuario    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
    CONSTRAINT fk_hf_producto   FOREIGN KEY (id_producto) REFERENCES producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Auditoría de cambios sobre facturas';

-- ============================================================
--  VISTAS ÚTILES
-- ============================================================

CREATE OR REPLACE VIEW v_citas_hoy AS
SELECT
    c.id_cita,
    c.fecha_cita,
    CONCAT(p.nombre,' ',p.apellidos)    AS paciente,
    CONCAT(o.nombre,' ',o.apellidos)    AS odontologo,
    s.nombre                            AS servicio,
    c.estado,
    c.asistencia
FROM citas c
JOIN pacientes p ON p.id_paciente = c.id_paciente
JOIN odontologos o ON o.id_odontologo = c.id_odontologo
LEFT JOIN servicios s ON s.id_servicio = c.id_servicio
WHERE DATE(c.fecha_cita) = CURDATE()
ORDER BY c.fecha_cita;

CREATE OR REPLACE VIEW v_stock_bajo AS
SELECT
    id_producto,
    nombre,
    stock,
    stock_minimo,
    (stock_minimo - stock) AS unidades_faltantes
FROM producto
WHERE stock <= stock_minimo AND estado = 'activo'
ORDER BY unidades_faltantes DESC;

CREATE OR REPLACE VIEW v_pacientes_expediente AS
SELECT
    p.id_paciente,
    CONCAT(p.nombre,' ',p.apellidos)    AS paciente,
    p.dni,
    p.telefono,
    sg.descripcion                      AS tipo_sangre,
    e.id_expediente,
    e.observaciones
FROM pacientes p
LEFT JOIN expedientes e ON e.id_paciente = p.id_paciente
LEFT JOIN sangres sg   ON sg.id_sangre   = e.id_sangre
WHERE p.estado = 'activo';

-- ============================================================
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  USUARIO ADMINISTRADOR INICIAL
-- ============================================================
-- Credenciales de prueba:
--   usuario:    JhairRios
--   contraseña: JhairRios10
--   rol:        Administrador (id_rol = 1)
-- IMPORTANTE: cambiar contraseña antes de producción.
-- ============================================================

INSERT INTO usuarios (id_rol, usuario, contrasena, nombre_completo, correo, estado)
VALUES (
    1,
    'JhairRios',
    '$2b$12$2toBiq1K872qn7YcU1xw0.qQYmC147Ky/qbh4eG.uCF78qMJx2yQ2',
    'Edson Jhair Ríos Coto',
    'jhairrios1098@icloud.com',
    'activo'
);

-- ============================================================
--  FIN — odonto_gest v3
--  Total: 35 tablas + 3 N:M + 2 vistas
--  Orden de creación: sin FK circulares
--  Honduras: ISV ENUM('0','15','18'), RTN, CAI
-- ============================================================

-- ============================================================
--  UTILITARIOS DE CONTRASEÑAS
--  Las contraseñas usan bcrypt (cost 12) generado desde PHP.
--  MySQL no tiene bcrypt nativo — usar el script PHP para
--  generar hashes:
--  http://localhost/odontogest_api/tools/generar_hash.php?pass=TuContrasenia
-- ============================================================

-- ── Procedimiento: registrar usuario con contraseña ya hasheada ──
-- La app llama este SP desde PHP DESPUÉS de hacer password_hash().
-- NUNCA pasar contraseña en texto plano a este SP desde el exterior.
DELIMITER $$

CREATE PROCEDURE sp_crear_usuario (
    IN p_id_rol         TINYINT UNSIGNED,
    IN p_usuario        VARCHAR(80),
    IN p_hash           VARCHAR(255),   -- bcrypt hash generado en PHP
    IN p_nombre         VARCHAR(200),
    IN p_correo         VARCHAR(150)
)
BEGIN
    INSERT INTO usuarios (id_rol, usuario, contrasena, nombre_completo, correo, estado)
    VALUES (p_id_rol, p_usuario, p_hash, p_nombre, p_correo, 'activo');

    SELECT LAST_INSERT_ID() AS id_usuario;
END $$

DELIMITER ;

-- ── Ejemplo: cambiar contraseña desde HeidiSQL ────────────────
-- Paso 1: Genera el hash en:
--   http://localhost/odontogest_api/tools/generar_hash.php?pass=NuevaContrasenia
-- Paso 2: Copia el hash y ejecuta:
--   UPDATE usuarios SET contrasena = '$2y$12$...' WHERE usuario = 'JhairRios';
