-- ════════════════════════════════════════════════════════════════════════
--  OdontoGest — Stored Procedures
--  Base de datos: odonto_gest
--  Versión: 1.0 | Fecha: 2025
--
--  Módulos cubiertos:
--    1. Dashboard
--    2. Agenda / Citas
--    3. Pacientes
--    4. Expedientes Clínicos
--    5. Facturación
--    6. Inventario / Productos
--    7. Usuarios y Roles
--    8. Auditoría / Notificaciones
-- ════════════════════════════════════════════════════════════════════════

USE odonto_gest;
DELIMITER $$

-- ════════════════════════════════════════════════════════════════════════
--  1. DASHBOARD
-- ════════════════════════════════════════════════════════════════════════

-- ── sp_dashboard_metricas ────────────────────────────────────────────────
-- Devuelve todos los KPIs del dashboard en un solo SELECT
DROP PROCEDURE IF EXISTS sp_dashboard_metricas $$
CREATE PROCEDURE sp_dashboard_metricas()
BEGIN
    -- Citas de hoy (contadores por estado)
    SELECT
        COUNT(*)                                                       AS citas_hoy,
        SUM(estado IN ('pendiente','confirmada','en_curso'))            AS citas_pendientes,
        SUM(estado = 'atendida')                                       AS citas_atendidas,
        SUM(estado = 'cancelada')                                      AS citas_canceladas,
        (SELECT COUNT(*) FROM pacientes   WHERE estado = 'activo')     AS pacientes_activos,
        (SELECT COUNT(*) FROM factura     WHERE estado = 'emitida'
            AND DATE(fecha_emision) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS facturas_pendientes,
        (SELECT COALESCE(SUM(total),0) FROM factura WHERE estado = 'emitida'
            AND DATE(fecha_emision) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)) AS monto_pendiente,
        (SELECT COUNT(*) FROM producto WHERE stock <= stock_minimo AND estado = 'activo') AS stock_bajo
    FROM citas
    WHERE DATE(fecha_cita) = CURDATE();
END$$

-- ── sp_dashboard_proximas_citas ──────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_dashboard_proximas_citas $$
CREATE PROCEDURE sp_dashboard_proximas_citas(
    IN p_limite INT
)
BEGIN
    SELECT
        c.id_cita,
        DATE(c.fecha_cita)                          AS fecha,
        TIME(c.fecha_cita)                          AS hora,
        c.estado,
        CONCAT(p.nombre,' ',p.apellidos)            AS paciente,
        CONCAT(o.nombre,' ',o.apellidos)            AS odontologo,
        s.nombre                                    AS servicio
    FROM citas c
    JOIN pacientes   p ON p.id_paciente   = c.id_paciente
    JOIN odontologos o ON o.id_odontologo = c.id_odontologo
    LEFT JOIN servicios s ON s.id_servicio = c.id_servicio
    WHERE DATE(c.fecha_cita) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
      AND c.estado NOT IN ('cancelada','no_asistio')
    ORDER BY c.fecha_cita
    LIMIT p_limite;
END$$

-- ── sp_dashboard_ultimas_facturas ────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_dashboard_ultimas_facturas $$
CREATE PROCEDURE sp_dashboard_ultimas_facturas(
    IN p_limite INT
)
BEGIN
    SELECT
        f.id_factura,
        f.numero_factura,
        f.estado,
        f.total,
        DATE(f.fecha_emision)                       AS fecha,
        CONCAT(p.nombre,' ',p.apellidos)            AS paciente
    FROM factura f
    JOIN pacientes p ON p.id_paciente = f.id_paciente
    ORDER BY f.id_factura DESC
    LIMIT p_limite;
END$$

-- ── sp_dashboard_alertas_inventario ─────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_dashboard_alertas_inventario $$
CREATE PROCEDURE sp_dashboard_alertas_inventario(
    IN p_limite INT
)
BEGIN
    SELECT
        p.id_producto,
        p.nombre,
        p.stock,
        p.stock_minimo,
        ROUND((p.stock / GREATEST(p.stock_minimo,1)) * 100, 0) AS porcentaje
    FROM producto p
    WHERE p.stock <= p.stock_minimo AND p.estado = 'activo'
    ORDER BY porcentaje ASC
    LIMIT p_limite;
END$$


-- ════════════════════════════════════════════════════════════════════════
--  2. AGENDA / CITAS
-- ════════════════════════════════════════════════════════════════════════

-- ── sp_citas_listar ──────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_citas_listar $$
CREATE PROCEDURE sp_citas_listar(
    IN  p_fecha    DATE,          -- NULL = todas las fechas
    IN  p_estado   VARCHAR(20),   -- '' = todos los estados
    IN  p_buscar   VARCHAR(100),  -- '' = sin filtro nombre
    IN  p_offset   INT,
    IN  p_limite   INT
)
BEGIN
    SELECT
        c.id_cita,
        c.fecha_cita,
        DATE(c.fecha_cita)                      AS fecha,
        TIME(c.fecha_cita)                      AS hora,
        c.estado,
        c.notas,
        CONCAT(p.nombre,' ',p.apellidos)        AS paciente,
        p.telefono                              AS telefono_paciente,
        CONCAT(o.nombre,' ',o.apellidos)        AS odontologo,
        s.nombre                                AS servicio
    FROM citas c
    JOIN pacientes   p ON p.id_paciente   = c.id_paciente
    JOIN odontologos o ON o.id_odontologo = c.id_odontologo
    LEFT JOIN servicios s ON s.id_servicio = c.id_servicio
    WHERE (p_fecha  IS NULL OR DATE(c.fecha_cita) = p_fecha)
      AND (p_estado = '' OR c.estado = p_estado)
      AND (p_buscar = ''
           OR CONCAT(p.nombre,' ',p.apellidos) LIKE CONCAT('%',p_buscar,'%')
           OR CONCAT(o.nombre,' ',o.apellidos) LIKE CONCAT('%',p_buscar,'%'))
    ORDER BY c.fecha_cita DESC
    LIMIT p_limite OFFSET p_offset;
END$$

-- ── sp_citas_total ───────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_citas_total $$
CREATE PROCEDURE sp_citas_total(
    IN p_fecha   DATE,
    IN p_estado  VARCHAR(20),
    IN p_buscar  VARCHAR(100)
)
BEGIN
    SELECT COUNT(*) AS total
    FROM citas c
    JOIN pacientes   p ON p.id_paciente   = c.id_paciente
    JOIN odontologos o ON o.id_odontologo = c.id_odontologo
    WHERE (p_fecha  IS NULL OR DATE(c.fecha_cita) = p_fecha)
      AND (p_estado = '' OR c.estado = p_estado)
      AND (p_buscar = ''
           OR CONCAT(p.nombre,' ',p.apellidos) LIKE CONCAT('%',p_buscar,'%')
           OR CONCAT(o.nombre,' ',o.apellidos) LIKE CONCAT('%',p_buscar,'%'));
END$$

-- ── sp_citas_insertar ────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_citas_insertar $$
CREATE PROCEDURE sp_citas_insertar(
    IN  p_id_paciente   INT UNSIGNED,
    IN  p_id_odontologo INT UNSIGNED,
    IN  p_id_horario    INT UNSIGNED,
    IN  p_id_servicio   INT UNSIGNED,
    IN  p_fecha_cita    DATETIME,
    IN  p_motivo        TEXT,
    IN  p_notas         TEXT,
    OUT p_id_cita       INT UNSIGNED
)
BEGIN
    INSERT INTO citas
        (id_paciente, id_odontologo, id_horario, id_servicio, fecha_cita, notas, estado)
    VALUES
        (p_id_paciente, p_id_odontologo, p_id_horario, NULLIF(p_id_servicio,0),
         p_fecha_cita, CONCAT_WS(' | ', NULLIF(p_motivo,''), NULLIF(p_notas,'')), 'pendiente');
    SET p_id_cita = LAST_INSERT_ID();
END$$

-- ── sp_citas_actualizar_estado ───────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_citas_actualizar_estado $$
CREATE PROCEDURE sp_citas_actualizar_estado(
    IN p_id_cita INT UNSIGNED,
    IN p_estado  VARCHAR(20)
)
BEGIN
    UPDATE citas SET estado = p_estado WHERE id_cita = p_id_cita;
END$$

-- ── sp_citas_eliminar ────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_citas_eliminar $$
CREATE PROCEDURE sp_citas_eliminar(
    IN p_id_cita INT UNSIGNED
)
BEGIN
    DELETE FROM citas WHERE id_cita = p_id_cita;
END$$

-- ── sp_citas_verificar_disponibilidad ───────────────────────────────────
-- Devuelve 1 si el odontólogo ya tiene cita activa en esa franja, 0 si libre
DROP PROCEDURE IF EXISTS sp_citas_verificar_disponibilidad $$
CREATE PROCEDURE sp_citas_verificar_disponibilidad(
    IN  p_id_odontologo INT UNSIGNED,
    IN  p_fecha_cita    DATETIME,
    IN  p_minutos       INT,          -- duración estimada del servicio
    IN  p_excluir_id    INT UNSIGNED, -- 0 = nueva cita
    OUT p_ocupado       TINYINT
)
BEGIN
    DECLARE v_fin DATETIME;
    SET v_fin = DATE_ADD(p_fecha_cita, INTERVAL p_minutos MINUTE);

    SELECT COUNT(*) INTO p_ocupado
    FROM citas
    WHERE id_odontologo = p_id_odontologo
      AND estado NOT IN ('cancelada','no_asistio')
      AND id_cita <> COALESCE(NULLIF(p_excluir_id,0), 0)
      AND fecha_cita < v_fin
      AND DATE_ADD(fecha_cita, INTERVAL 30 MINUTE) > p_fecha_cita;
END$$


-- ════════════════════════════════════════════════════════════════════════
--  3. PACIENTES
-- ════════════════════════════════════════════════════════════════════════

-- ── sp_pacientes_listar ──────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_pacientes_listar $$
CREATE PROCEDURE sp_pacientes_listar(
    IN p_buscar  VARCHAR(100),
    IN p_estado  VARCHAR(20),
    IN p_offset  INT,
    IN p_limite  INT
)
BEGIN
    SELECT
        p.id_paciente,
        p.nombre,
        p.apellidos,
        CONCAT(p.nombre,' ',p.apellidos)    AS nombre_completo,
        p.telefono,
        p.correo,
        p.estado,
        p.created_at,
        (SELECT COUNT(*) FROM citas c WHERE c.id_paciente = p.id_paciente) AS total_citas
    FROM pacientes p
    WHERE (p_buscar = ''
           OR CONCAT(p.nombre,' ',p.apellidos) LIKE CONCAT('%',p_buscar,'%')
           OR p.telefono LIKE CONCAT('%',p_buscar,'%')
           OR p.correo   LIKE CONCAT('%',p_buscar,'%'))
      AND (p_estado = '' OR p.estado = p_estado)
    ORDER BY p.apellidos, p.nombre
    LIMIT p_limite OFFSET p_offset;
END$$

-- ── sp_pacientes_total ───────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_pacientes_total $$
CREATE PROCEDURE sp_pacientes_total(
    IN p_buscar VARCHAR(100),
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT COUNT(*) AS total
    FROM pacientes p
    WHERE (p_buscar = ''
           OR CONCAT(p.nombre,' ',p.apellidos) LIKE CONCAT('%',p_buscar,'%')
           OR p.telefono LIKE CONCAT('%',p_buscar,'%')
           OR p.correo   LIKE CONCAT('%',p_buscar,'%'))
      AND (p_estado = '' OR p.estado = p_estado);
END$$

-- ── sp_pacientes_kpis ────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_pacientes_kpis $$
CREATE PROCEDURE sp_pacientes_kpis()
BEGIN
    SELECT
        COUNT(*)                                   AS total_pacientes,
        SUM(estado = 'activo')                     AS activos,
        SUM(estado = 'inactivo')                   AS inactivos,
        SUM(MONTH(created_at) = MONTH(CURDATE())
            AND YEAR(created_at) = YEAR(CURDATE())) AS nuevos_mes
    FROM pacientes;
END$$

-- ── sp_pacientes_obtener_por_id ──────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_pacientes_obtener_por_id $$
CREATE PROCEDURE sp_pacientes_obtener_por_id(
    IN p_id_paciente INT UNSIGNED
)
BEGIN
    SELECT
        p.*,
        CONCAT(p.nombre,' ',p.apellidos) AS nombre_completo,
        s.tipo                           AS grupo_sangre
    FROM pacientes p
    LEFT JOIN sangres s ON s.id_sangre = (
        SELECT id_sangre FROM expedientes e WHERE e.id_paciente = p.id_paciente LIMIT 1
    )
    WHERE p.id_paciente = p_id_paciente;
END$$

-- ── sp_pacientes_insertar ────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_pacientes_insertar $$
CREATE PROCEDURE sp_pacientes_insertar(
    IN  p_nombre       VARCHAR(100),
    IN  p_apellidos    VARCHAR(100),
    IN  p_fecha_nac    DATE,
    IN  p_sexo         ENUM('M','F','Otro'),
    IN  p_telefono     VARCHAR(20),
    IN  p_correo       VARCHAR(150),
    IN  p_direccion    TEXT,
    IN  p_rtn          VARCHAR(20),
    OUT p_id_paciente  INT UNSIGNED
)
BEGIN
    INSERT INTO pacientes (nombre, apellidos, fecha_nac, sexo, telefono, correo, direccion, rtn, estado)
    VALUES (p_nombre, p_apellidos, NULLIF(p_fecha_nac,''), p_sexo,
            NULLIF(p_telefono,''), NULLIF(p_correo,''),
            NULLIF(p_direccion,''), NULLIF(p_rtn,''), 'activo');
    SET p_id_paciente = LAST_INSERT_ID();
END$$

-- ── sp_pacientes_actualizar ──────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_pacientes_actualizar $$
CREATE PROCEDURE sp_pacientes_actualizar(
    IN p_id_paciente INT UNSIGNED,
    IN p_nombre      VARCHAR(100),
    IN p_apellidos   VARCHAR(100),
    IN p_fecha_nac   DATE,
    IN p_sexo        ENUM('M','F','Otro'),
    IN p_telefono    VARCHAR(20),
    IN p_correo      VARCHAR(150),
    IN p_direccion   TEXT,
    IN p_rtn         VARCHAR(20),
    IN p_estado      VARCHAR(20)
)
BEGIN
    UPDATE pacientes SET
        nombre    = p_nombre,
        apellidos = p_apellidos,
        fecha_nac = NULLIF(p_fecha_nac,''),
        sexo      = p_sexo,
        telefono  = NULLIF(p_telefono,''),
        correo    = NULLIF(p_correo,''),
        direccion = NULLIF(p_direccion,''),
        rtn       = NULLIF(p_rtn,''),
        estado    = p_estado
    WHERE id_paciente = p_id_paciente;
END$$


-- ════════════════════════════════════════════════════════════════════════
--  4. EXPEDIENTES CLÍNICOS
-- ════════════════════════════════════════════════════════════════════════

-- ── sp_expediente_obtener ────────────────────────────────────────────────
-- Datos del expediente + info del paciente
DROP PROCEDURE IF EXISTS sp_expediente_obtener $$
CREATE PROCEDURE sp_expediente_obtener(
    IN p_id_paciente INT UNSIGNED
)
BEGIN
    SELECT
        e.*,
        p.nombre, p.apellidos,
        CONCAT(p.nombre,' ',p.apellidos)    AS nombre_completo,
        p.fecha_nac, p.sexo, p.telefono, p.correo,
        p.estado                            AS estado_paciente,
        s.tipo                              AS grupo_sangre
    FROM expedientes e
    JOIN pacientes p ON p.id_paciente = e.id_paciente
    LEFT JOIN sangres s ON s.id_sangre = e.id_sangre
    WHERE e.id_paciente = p_id_paciente;
END$$

-- ── sp_expediente_historial_citas ────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_expediente_historial_citas $$
CREATE PROCEDURE sp_expediente_historial_citas(
    IN p_id_paciente INT UNSIGNED
)
BEGIN
    SELECT
        c.id_cita,
        c.fecha_cita,
        c.estado,
        c.notas,
        CONCAT(o.nombre,' ',o.apellidos)    AS odontologo,
        s.nombre                            AS servicio
    FROM citas c
    JOIN odontologos o ON o.id_odontologo = c.id_odontologo
    LEFT JOIN servicios s ON s.id_servicio = c.id_servicio
    WHERE c.id_paciente = p_id_paciente
    ORDER BY c.fecha_cita DESC;
END$$

-- ── sp_expediente_odontograma ────────────────────────────────────────────
-- Piezas dentales del odontograma del paciente
DROP PROCEDURE IF EXISTS sp_expediente_odontograma $$
CREATE PROCEDURE sp_expediente_odontograma(
    IN p_id_paciente INT UNSIGNED
)
BEGIN
    SELECT
        od.id_odontograma,
        od.pieza_dental,
        od.cara,
        od.condicion,
        od.color_codigo,
        od.descripcion,
        od.fecha_registro,
        CONCAT(o.nombre,' ',o.apellidos)    AS odontologo
    FROM odontograma od
    JOIN expedientes e  ON e.id_expediente  = od.id_expediente
    JOIN odontologos o  ON o.id_odontologo  = od.id_odontologo
    WHERE e.id_paciente = p_id_paciente
    ORDER BY od.pieza_dental, od.fecha_registro DESC;
END$$

-- ── sp_expediente_guardar_pieza ──────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_expediente_guardar_pieza $$
CREATE PROCEDURE sp_expediente_guardar_pieza(
    IN  p_id_paciente   INT UNSIGNED,
    IN  p_id_odontologo INT UNSIGNED,
    IN  p_pieza         TINYINT UNSIGNED,
    IN  p_cara          VARCHAR(20),
    IN  p_condicion     VARCHAR(100),
    IN  p_color         VARCHAR(10),
    IN  p_descripcion   TEXT
)
BEGIN
    DECLARE v_id_exp INT UNSIGNED;
    SELECT id_expediente INTO v_id_exp
    FROM expedientes WHERE id_paciente = p_id_paciente LIMIT 1;

    IF v_id_exp IS NOT NULL THEN
        INSERT INTO odontograma
            (id_expediente, id_odontologo, pieza_dental, cara, condicion, color_codigo, descripcion)
        VALUES
            (v_id_exp, p_id_odontologo, p_pieza, p_cara, p_condicion,
             COALESCE(NULLIF(p_color,''), '#FF0000'), NULLIF(p_descripcion,''));
    END IF;
END$$

-- ── sp_expediente_facturas_paciente ─────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_expediente_facturas_paciente $$
CREATE PROCEDURE sp_expediente_facturas_paciente(
    IN p_id_paciente INT UNSIGNED
)
BEGIN
    SELECT
        f.id_factura,
        f.numero_factura,
        f.estado,
        f.subtotal,
        f.impuesto,
        f.total,
        f.metodo_pago,
        DATE(f.fecha_emision)               AS fecha
    FROM factura f
    WHERE f.id_paciente = p_id_paciente
    ORDER BY f.fecha_emision DESC;
END$$


-- ════════════════════════════════════════════════════════════════════════
--  5. FACTURACIÓN
-- ════════════════════════════════════════════════════════════════════════

-- ── sp_factura_listar ────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_factura_listar $$
CREATE PROCEDURE sp_factura_listar(
    IN p_estado    VARCHAR(20),   -- '' = todos
    IN p_fecha_ini DATE,
    IN p_fecha_fin DATE,
    IN p_buscar    VARCHAR(100),
    IN p_offset    INT,
    IN p_limite    INT
)
BEGIN
    SELECT
        f.id_factura,
        f.numero_factura,
        f.estado,
        f.subtotal,
        f.descuento,
        f.impuesto,
        f.total,
        f.tasa_impuesto,
        f.metodo_pago,
        DATE(f.fecha_emision)               AS fecha,
        CONCAT(p.nombre,' ',p.apellidos)    AS paciente,
        p.rtn                               AS rtn_paciente
    FROM factura f
    JOIN pacientes p ON p.id_paciente = f.id_paciente
    WHERE (p_estado = '' OR f.estado = p_estado)
      AND (p_fecha_ini IS NULL OR DATE(f.fecha_emision) >= p_fecha_ini)
      AND (p_fecha_fin IS NULL OR DATE(f.fecha_emision) <= p_fecha_fin)
      AND (p_buscar = ''
           OR CONCAT(p.nombre,' ',p.apellidos) LIKE CONCAT('%',p_buscar,'%')
           OR f.numero_factura LIKE CONCAT('%',p_buscar,'%'))
    ORDER BY f.id_factura DESC
    LIMIT p_limite OFFSET p_offset;
END$$

-- ── sp_factura_total ─────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_factura_total $$
CREATE PROCEDURE sp_factura_total(
    IN p_estado    VARCHAR(20),
    IN p_fecha_ini DATE,
    IN p_fecha_fin DATE,
    IN p_buscar    VARCHAR(100)
)
BEGIN
    SELECT COUNT(*) AS total
    FROM factura f
    JOIN pacientes p ON p.id_paciente = f.id_paciente
    WHERE (p_estado = '' OR f.estado = p_estado)
      AND (p_fecha_ini IS NULL OR DATE(f.fecha_emision) >= p_fecha_ini)
      AND (p_fecha_fin IS NULL OR DATE(f.fecha_emision) <= p_fecha_fin)
      AND (p_buscar = ''
           OR CONCAT(p.nombre,' ',p.apellidos) LIKE CONCAT('%',p_buscar,'%')
           OR f.numero_factura LIKE CONCAT('%',p_buscar,'%'));
END$$

-- ── sp_factura_kpis ──────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_factura_kpis $$
CREATE PROCEDURE sp_factura_kpis()
BEGIN
    SELECT
        COUNT(*)                                        AS total_facturas,
        SUM(estado = 'emitida')                         AS emitidas,
        SUM(estado = 'pagada')                          AS pagadas,
        SUM(estado = 'anulada')                         AS anuladas,
        COALESCE(SUM(CASE WHEN estado='pagada' AND MONTH(fecha_emision)=MONTH(CURDATE())
                          AND YEAR(fecha_emision)=YEAR(CURDATE())
                     THEN total ELSE 0 END), 0)         AS ingresos_mes,
        COALESCE(SUM(CASE WHEN estado='emitida' THEN total ELSE 0 END), 0) AS monto_pendiente
    FROM factura;
END$$

-- ── sp_factura_obtener_por_id ────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_factura_obtener_por_id $$
CREATE PROCEDURE sp_factura_obtener_por_id(
    IN p_id_factura INT UNSIGNED
)
BEGIN
    SELECT
        f.*,
        DATE(f.fecha_emision)               AS fecha_emision_fmt,
        CONCAT(p.nombre,' ',p.apellidos)    AS paciente,
        p.telefono, p.correo, p.rtn,
        CONCAT(u.nombre,' ',u.apellidos)    AS usuario_emisor
    FROM factura f
    JOIN pacientes p ON p.id_paciente = f.id_paciente
    JOIN usuarios  u ON u.id_usuario  = f.id_usuario
    WHERE f.id_factura = p_id_factura;

    -- Items del detalle
    SELECT
        d.*,
        s.nombre AS nombre_servicio
    FROM detalle_factura d
    LEFT JOIN servicios s ON s.id_servicio = d.id_servicio
    WHERE d.id_factura = p_id_factura
    ORDER BY d.id_detalle;
END$$

-- ── sp_factura_insertar ──────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_factura_insertar $$
CREATE PROCEDURE sp_factura_insertar(
    IN  p_id_sucursal   INT UNSIGNED,
    IN  p_id_paciente   INT UNSIGNED,
    IN  p_id_cita       INT UNSIGNED,
    IN  p_id_usuario    INT UNSIGNED,
    IN  p_subtotal      DECIMAL(10,2),
    IN  p_descuento     DECIMAL(10,2),
    IN  p_impuesto      DECIMAL(10,2),
    IN  p_total         DECIMAL(10,2),
    IN  p_tasa          ENUM('0','15','18'),
    IN  p_metodo_pago   VARCHAR(20),
    IN  p_notas         TEXT,
    OUT p_id_factura    INT UNSIGNED,
    OUT p_numero        VARCHAR(30)
)
BEGIN
    DECLARE v_consecutivo INT;

    -- Genera número SAR correlativo: F-YYYYMM-XXXXX
    SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(numero_factura,'-',-1) AS UNSIGNED)),0)+1
      INTO v_consecutivo FROM factura;

    SET p_numero = CONCAT('F-', DATE_FORMAT(NOW(),'%Y%m'), '-', LPAD(v_consecutivo,5,'0'));

    INSERT INTO factura
        (id_sucursal, id_paciente, id_cita, id_usuario, numero_factura,
         subtotal, descuento, impuesto, total, tasa_impuesto, metodo_pago, notas, estado, fecha_emision)
    VALUES
        (p_id_sucursal, p_id_paciente, NULLIF(p_id_cita,0), p_id_usuario, p_numero,
         p_subtotal, p_descuento, p_impuesto, p_total, p_tasa,
         p_metodo_pago, NULLIF(p_notas,''), 'emitida', NOW());

    SET p_id_factura = LAST_INSERT_ID();
END$$

-- ── sp_factura_cambiar_estado ────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_factura_cambiar_estado $$
CREATE PROCEDURE sp_factura_cambiar_estado(
    IN p_id_factura           INT UNSIGNED,
    IN p_estado               VARCHAR(10),
    IN p_responsable_anulado  INT UNSIGNED,  -- 0 si no aplica
    IN p_motivo               VARCHAR(400)   -- '' si no aplica
)
BEGIN
    UPDATE factura SET
        estado                = p_estado,
        responsable_anulado   = NULLIF(p_responsable_anulado, 0),
        motivo_anulacion      = NULLIF(p_motivo,'')
    WHERE id_factura = p_id_factura;
END$$


-- ════════════════════════════════════════════════════════════════════════
--  6. INVENTARIO / PRODUCTOS
-- ════════════════════════════════════════════════════════════════════════

-- ── sp_inventario_listar ─────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_inventario_listar $$
CREATE PROCEDURE sp_inventario_listar(
    IN p_buscar VARCHAR(100),
    IN p_estado VARCHAR(20),
    IN p_offset INT,
    IN p_limite INT
)
BEGIN
    SELECT
        p.id_producto,
        p.nombre,
        p.descripcion,
        p.unidad_medida,
        p.stock,
        p.stock_minimo,
        p.precio_costo,
        p.precio_venta,
        p.tasa_impuesto,
        p.estado,
        p.created_at,
        CASE
            WHEN p.stock = 0           THEN 'agotado'
            WHEN p.stock <= p.stock_minimo THEN 'critico'
            WHEN p.stock <= p.stock_minimo * 1.5 THEN 'bajo'
            ELSE 'ok'
        END AS nivel_stock,
        pr.nombre AS proveedor
    FROM producto p
    LEFT JOIN proveedores pr ON pr.id_proveedor = p.id_proveedor
    WHERE (p_buscar = '' OR p.nombre LIKE CONCAT('%',p_buscar,'%'))
      AND (p_estado = '' OR p.estado = p_estado)
    ORDER BY p.nombre
    LIMIT p_limite OFFSET p_offset;
END$$

-- ── sp_inventario_total ──────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_inventario_total $$
CREATE PROCEDURE sp_inventario_total(
    IN p_buscar VARCHAR(100),
    IN p_estado VARCHAR(20)
)
BEGIN
    SELECT COUNT(*) AS total
    FROM producto p
    WHERE (p_buscar = '' OR p.nombre LIKE CONCAT('%',p_buscar,'%'))
      AND (p_estado = '' OR p.estado = p_estado);
END$$

-- ── sp_inventario_kpis ───────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_inventario_kpis $$
CREATE PROCEDURE sp_inventario_kpis()
BEGIN
    SELECT
        COUNT(*)                            AS total_productos,
        SUM(estado = 'activo')              AS activos,
        SUM(stock = 0)                      AS agotados,
        SUM(stock <= stock_minimo AND estado = 'activo') AS bajo_minimo,
        COALESCE(SUM(stock * precio_costo), 0) AS valor_inventario
    FROM producto;
END$$

-- ── sp_inventario_alertas_stock ──────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_inventario_alertas_stock $$
CREATE PROCEDURE sp_inventario_alertas_stock(
    IN p_limite INT
)
BEGIN
    SELECT
        p.id_producto,
        p.nombre,
        p.stock,
        p.stock_minimo,
        p.unidad_medida,
        ROUND((p.stock / GREATEST(p.stock_minimo,1)) * 100, 0) AS porcentaje_stock,
        CASE
            WHEN p.stock = 0               THEN 'agotado'
            WHEN p.stock <= p.stock_minimo THEN 'critico'
            ELSE 'bajo'
        END AS nivel
    FROM producto p
    WHERE p.stock <= p.stock_minimo AND p.estado = 'activo'
    ORDER BY porcentaje_stock ASC
    LIMIT p_limite;
END$$

-- ── sp_inventario_insertar ───────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_inventario_insertar $$
CREATE PROCEDURE sp_inventario_insertar(
    IN  p_id_proveedor  INT UNSIGNED,
    IN  p_nombre        VARCHAR(200),
    IN  p_descripcion   TEXT,
    IN  p_unidad        VARCHAR(50),
    IN  p_stock         INT UNSIGNED,
    IN  p_stock_minimo  INT UNSIGNED,
    IN  p_precio_costo  DECIMAL(10,2),
    IN  p_precio_venta  DECIMAL(10,2),
    IN  p_tasa          ENUM('0','15','18'),
    OUT p_id_producto   INT UNSIGNED
)
BEGIN
    INSERT INTO producto
        (id_proveedor, nombre, descripcion, unidad_medida,
         stock, stock_minimo, precio_costo, precio_venta, tasa_impuesto, estado)
    VALUES
        (p_id_proveedor, p_nombre, NULLIF(p_descripcion,''),
         NULLIF(p_unidad,''),
         p_stock, p_stock_minimo, p_precio_costo, p_precio_venta, p_tasa, 'activo');
    SET p_id_producto = LAST_INSERT_ID();

    -- Si quedó en 0 actualizar estado a agotado
    IF p_stock = 0 THEN
        UPDATE producto SET estado = 'agotado' WHERE id_producto = p_id_producto;
    END IF;
END$$

-- ── sp_inventario_ajustar_stock ──────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_inventario_ajustar_stock $$
CREATE PROCEDURE sp_inventario_ajustar_stock(
    IN p_id_producto INT UNSIGNED,
    IN p_cantidad    INT,
    IN p_tipo        VARCHAR(10),   -- 'entrada' | 'salida' | 'ajuste'
    IN p_motivo      VARCHAR(200)
)
BEGIN
    DECLARE v_stock_nuevo INT;

    IF p_tipo = 'entrada' THEN
        UPDATE producto SET stock = stock + p_cantidad WHERE id_producto = p_id_producto;
    ELSEIF p_tipo = 'salida' THEN
        UPDATE producto SET stock = GREATEST(0, stock - p_cantidad) WHERE id_producto = p_id_producto;
    ELSEIF p_tipo = 'ajuste' THEN
        UPDATE producto SET stock = GREATEST(0, p_cantidad) WHERE id_producto = p_id_producto;
    END IF;

    -- Actualizar estado automático
    SELECT stock INTO v_stock_nuevo FROM producto WHERE id_producto = p_id_producto;
    IF v_stock_nuevo = 0 THEN
        UPDATE producto SET estado = 'agotado' WHERE id_producto = p_id_producto;
    ELSE
        UPDATE producto SET estado = 'activo'  WHERE id_producto = p_id_producto AND estado = 'agotado';
    END IF;
END$$


-- ════════════════════════════════════════════════════════════════════════
--  7. USUARIOS Y ROLES
-- ════════════════════════════════════════════════════════════════════════

-- ── sp_usuarios_listar ───────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_usuarios_listar $$
CREATE PROCEDURE sp_usuarios_listar(
    IN p_buscar VARCHAR(100),
    IN p_estado VARCHAR(20),
    IN p_offset INT,
    IN p_limite INT
)
BEGIN
    SELECT
        u.id_usuario,
        u.username,
        u.nombre,
        u.apellidos,
        CONCAT(u.nombre,' ',u.apellidos) AS nombre_completo,
        u.correo,
        u.estado,
        u.created_at,
        r.nombre AS rol
    FROM usuarios u
    JOIN roles r ON r.id_rol = u.id_rol
    WHERE (p_buscar = ''
           OR CONCAT(u.nombre,' ',u.apellidos) LIKE CONCAT('%',p_buscar,'%')
           OR u.username LIKE CONCAT('%',p_buscar,'%')
           OR u.correo   LIKE CONCAT('%',p_buscar,'%'))
      AND (p_estado = '' OR u.estado = p_estado)
    ORDER BY u.apellidos, u.nombre
    LIMIT p_limite OFFSET p_offset;
END$$

-- ── sp_usuarios_obtener_por_username ─────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_usuarios_obtener_por_username $$
CREATE PROCEDURE sp_usuarios_obtener_por_username(
    IN p_username VARCHAR(80)
)
BEGIN
    SELECT u.*, r.nombre AS rol_nombre
    FROM usuarios u
    JOIN roles r ON r.id_rol = u.id_rol
    WHERE u.username = p_username AND u.estado = 'activo'
    LIMIT 1;
END$$

-- ── sp_usuarios_insertar ─────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_usuarios_insertar $$
CREATE PROCEDURE sp_usuarios_insertar(
    IN  p_username   VARCHAR(80),
    IN  p_password   VARCHAR(255),   -- ya hasheado (bcrypt)
    IN  p_nombre     VARCHAR(100),
    IN  p_apellidos  VARCHAR(100),
    IN  p_correo     VARCHAR(150),
    IN  p_id_rol     INT UNSIGNED,
    OUT p_id_usuario INT UNSIGNED
)
BEGIN
    INSERT INTO usuarios (username, password, nombre, apellidos, correo, id_rol, estado)
    VALUES (p_username, p_password, p_nombre, p_apellidos,
            NULLIF(p_correo,''), p_id_rol, 'activo');
    SET p_id_usuario = LAST_INSERT_ID();
END$$

-- ── sp_usuarios_cambiar_estado ───────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_usuarios_cambiar_estado $$
CREATE PROCEDURE sp_usuarios_cambiar_estado(
    IN p_id_usuario INT UNSIGNED,
    IN p_estado     VARCHAR(20)
)
BEGIN
    UPDATE usuarios SET estado = p_estado WHERE id_usuario = p_id_usuario;
END$$

-- ── sp_roles_listar_todos ────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_roles_listar_todos $$
CREATE PROCEDURE sp_roles_listar_todos()
BEGIN
    SELECT r.*, COUNT(u.id_usuario) AS total_usuarios
    FROM roles r
    LEFT JOIN usuarios u ON u.id_rol = r.id_rol
    GROUP BY r.id_rol
    ORDER BY r.nombre;
END$$

-- ── sp_roles_permisos ────────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_roles_permisos $$
CREATE PROCEDURE sp_roles_permisos(
    IN p_id_rol INT UNSIGNED
)
BEGIN
    SELECT p.*, (pr.id_rol IS NOT NULL) AS asignado
    FROM permisos p
    LEFT JOIN permisos_roles pr ON pr.id_permiso = p.id_permiso AND pr.id_rol = p_id_rol
    ORDER BY p.modulo, p.nombre;
END$$


-- ════════════════════════════════════════════════════════════════════════
--  8. AUDITORÍA Y NOTIFICACIONES
-- ════════════════════════════════════════════════════════════════════════

-- ── sp_auditoria_registrar ───────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_auditoria_registrar $$
CREATE PROCEDURE sp_auditoria_registrar(
    IN p_id_usuario INT UNSIGNED,
    IN p_accion     VARCHAR(100),
    IN p_detalle    TEXT,
    IN p_ip         VARCHAR(45)
)
BEGIN
    INSERT INTO auditoria (id_usuario, accion, detalle, ip, created_at)
    VALUES (NULLIF(p_id_usuario,0), p_accion, NULLIF(p_detalle,''),
            NULLIF(p_ip,''), NOW());
END$$

-- ── sp_auditoria_listar ──────────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_auditoria_listar $$
CREATE PROCEDURE sp_auditoria_listar(
    IN p_fecha_ini DATE,
    IN p_fecha_fin DATE,
    IN p_offset    INT,
    IN p_limite    INT
)
BEGIN
    SELECT
        a.id_auditoria,
        a.accion,
        a.detalle,
        a.ip,
        a.created_at,
        CONCAT(u.nombre,' ',u.apellidos) AS usuario
    FROM auditoria a
    LEFT JOIN usuarios u ON u.id_usuario = a.id_usuario
    WHERE (p_fecha_ini IS NULL OR DATE(a.created_at) >= p_fecha_ini)
      AND (p_fecha_fin IS NULL OR DATE(a.created_at) <= p_fecha_fin)
    ORDER BY a.id_auditoria DESC
    LIMIT p_limite OFFSET p_offset;
END$$

-- ── sp_notificaciones_listar ─────────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_notificaciones_listar $$
CREATE PROCEDURE sp_notificaciones_listar(
    IN p_id_usuario INT UNSIGNED,
    IN p_limite     INT
)
BEGIN
    SELECT
        n.id_notificacion,
        n.titulo,
        n.mensaje,
        n.leida,
        n.created_at AS fecha
    FROM notificaciones n
    WHERE n.id_usuario = p_id_usuario
    ORDER BY n.id_notificacion DESC
    LIMIT p_limite;
END$$

-- ── sp_notificaciones_no_leidas ──────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_notificaciones_no_leidas $$
CREATE PROCEDURE sp_notificaciones_no_leidas(
    IN p_id_usuario INT UNSIGNED
)
BEGIN
    SELECT COUNT(*) AS no_leidas
    FROM notificaciones
    WHERE id_usuario = p_id_usuario AND leida = 0;
END$$

-- ── sp_notificaciones_marcar_leida ───────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_notificaciones_marcar_leida $$
CREATE PROCEDURE sp_notificaciones_marcar_leida(
    IN p_id          INT UNSIGNED,
    IN p_id_usuario  INT UNSIGNED  -- valida que la notif pertenezca al usuario
)
BEGIN
    UPDATE notificaciones SET leida = 1
    WHERE id_notificacion = p_id AND id_usuario = p_id_usuario;
END$$

-- ── sp_notificaciones_marcar_todas_leidas ────────────────────────────────
DROP PROCEDURE IF EXISTS sp_notificaciones_marcar_todas_leidas $$
CREATE PROCEDURE sp_notificaciones_marcar_todas_leidas(
    IN p_id_usuario INT UNSIGNED
)
BEGIN
    UPDATE notificaciones SET leida = 1
    WHERE id_usuario = p_id_usuario AND leida = 0;
END$$

-- ── sp_notificaciones_crear ──────────────────────────────────────────────
-- Inserta notificación para un usuario específico o para todos (0 = todos)
DROP PROCEDURE IF EXISTS sp_notificaciones_crear $$
CREATE PROCEDURE sp_notificaciones_crear(
    IN p_id_usuario INT UNSIGNED,   -- 0 = para todos los usuarios activos
    IN p_titulo     VARCHAR(200),
    IN p_mensaje    TEXT
)
BEGIN
    IF p_id_usuario = 0 THEN
        INSERT INTO notificaciones (id_usuario, titulo, mensaje)
        SELECT id_usuario, p_titulo, p_mensaje FROM usuarios WHERE estado = 'activo';
    ELSE
        INSERT INTO notificaciones (id_usuario, titulo, mensaje)
        VALUES (p_id_usuario, p_titulo, p_mensaje);
    END IF;
END$$


-- ════════════════════════════════════════════════════════════════════════
--  REPORTES ESTADÍSTICOS
-- ════════════════════════════════════════════════════════════════════════

-- ── sp_reporte_citas_por_rango ───────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_reporte_citas_por_rango $$
CREATE PROCEDURE sp_reporte_citas_por_rango(
    IN p_fecha_ini DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT
        DATE(fecha_cita)     AS fecha,
        COUNT(*)             AS total,
        SUM(estado='atendida')  AS atendidas,
        SUM(estado='cancelada') AS canceladas,
        SUM(estado='no_asistio') AS no_asistio
    FROM citas
    WHERE DATE(fecha_cita) BETWEEN p_fecha_ini AND p_fecha_fin
    GROUP BY DATE(fecha_cita)
    ORDER BY fecha;
END$$

-- ── sp_reporte_ingresos_por_mes ──────────────────────────────────────────
DROP PROCEDURE IF EXISTS sp_reporte_ingresos_por_mes $$
CREATE PROCEDURE sp_reporte_ingresos_por_mes(
    IN p_anio INT
)
BEGIN
    SELECT
        MONTH(fecha_emision)                AS mes,
        MONTHNAME(fecha_emision)            AS nombre_mes,
        COUNT(*)                            AS total_facturas,
        COALESCE(SUM(CASE WHEN estado='pagada' THEN total ELSE 0 END),0) AS ingresos_pagados,
        COALESCE(SUM(CASE WHEN estado='emitida' THEN total ELSE 0 END),0) AS ingresos_pendientes,
        COALESCE(SUM(impuesto),0)           AS total_isv
    FROM factura
    WHERE YEAR(fecha_emision) = p_anio AND estado <> 'anulada'
    GROUP BY MONTH(fecha_emision), MONTHNAME(fecha_emision)
    ORDER BY mes;
END$$

-- ── sp_reporte_odontologo_rendimiento ───────────────────────────────────
DROP PROCEDURE IF EXISTS sp_reporte_odontologo_rendimiento $$
CREATE PROCEDURE sp_reporte_odontologo_rendimiento(
    IN p_fecha_ini DATE,
    IN p_fecha_fin DATE
)
BEGIN
    SELECT
        CONCAT(o.nombre,' ',o.apellidos)    AS odontologo,
        COUNT(c.id_cita)                    AS total_citas,
        SUM(c.estado='atendida')            AS atendidas,
        SUM(c.estado='cancelada')           AS canceladas,
        ROUND(SUM(c.estado='atendida')/GREATEST(COUNT(*),1)*100,1) AS efectividad_pct
    FROM odontologos o
    LEFT JOIN citas c ON c.id_odontologo = o.id_odontologo
        AND DATE(c.fecha_cita) BETWEEN p_fecha_ini AND p_fecha_fin
    GROUP BY o.id_odontologo, o.nombre, o.apellidos
    ORDER BY atendidas DESC;
END$$

DELIMITER ;

-- ════════════════════════════════════════════════════════════════════════
--  FIN — stored_procedures.sql
--  Total SPs: 38
-- ════════════════════════════════════════════════════════════════════════

-- ═══════════════════════════════════════════════════════════════
-- SEED: Permisos del sistema (ejecutar una vez)
-- ═══════════════════════════════════════════════════════════════
INSERT IGNORE INTO permisos (nombre, descripcion, modulo) VALUES
  -- Agenda
  ('agenda.ver',           'Ver agenda y citas',               'agenda'),
  ('agenda.crear',         'Crear nuevas citas',               'agenda'),
  ('agenda.editar',        'Editar / cambiar estado de citas', 'agenda'),
  ('agenda.eliminar',      'Eliminar citas',                   'agenda'),
  -- Pacientes
  ('pacientes.ver',        'Ver listado de pacientes',         'agenda'),
  ('pacientes.crear',      'Registrar nuevos pacientes',       'agenda'),
  ('pacientes.editar',     'Editar datos de pacientes',        'agenda'),
  ('pacientes.eliminar',   'Desactivar pacientes',             'agenda'),
  -- Expedientes
  ('expedientes.ver',      'Ver expedientes clínicos',         'expedientes'),
  ('expedientes.editar',   'Editar expediente y odontograma',  'expedientes'),
  -- Facturación
  ('facturacion.ver',      'Ver facturas',                     'facturacion'),
  ('facturacion.crear',    'Emitir facturas',                  'facturacion'),
  ('facturacion.anular',   'Anular facturas',                  'facturacion'),
  -- Inventario
  ('inventario.ver',       'Ver inventario',                   'inventario'),
  ('inventario.crear',     'Agregar productos',                'inventario'),
  ('inventario.editar',    'Editar productos y ajustar stock', 'inventario'),
  ('inventario.eliminar',  'Desactivar productos',             'inventario'),
  -- Servicios
  ('servicios.ver',        'Ver catálogo de servicios',        'configuracion'),
  ('servicios.crear',      'Agregar servicios',                'configuracion'),
  ('servicios.editar',     'Editar servicios',                 'configuracion'),
  ('servicios.eliminar',   'Desactivar servicios',             'configuracion'),
  -- Usuarios
  ('usuarios.ver',         'Ver usuarios del sistema',         'seguridad'),
  ('usuarios.crear',       'Crear usuarios',                   'seguridad'),
  ('usuarios.editar',      'Editar usuarios',                  'seguridad'),
  ('usuarios.eliminar',    'Desactivar usuarios',              'seguridad'),
  -- Roles
  ('roles.ver',            'Ver roles',                        'seguridad'),
  ('roles.crear',          'Crear roles',                      'seguridad'),
  ('roles.editar',         'Editar permisos de roles',         'seguridad'),
  ('roles.eliminar',       'Eliminar roles',                   'seguridad'),
  -- Reportes / Auditoría
  ('reportes.ver',         'Ver reportes del sistema',         'reportes'),
  ('auditoria.ver',        'Ver log de auditoría',             'seguridad'),
  -- Sistema
  ('configuracion.ver',    'Ver configuración del sistema',    'configuracion');

-- Asignar todos los permisos al rol Administrador (id_rol=1)
INSERT IGNORE INTO permisos_roles (id_rol, id_permiso)
  SELECT 1, id_permiso FROM permisos;
