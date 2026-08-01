-- ══════════════════════════════════════════════════════════════
-- Migración v3.1 — OdontoGest
-- Agrega: tabla recetas + tabla expediente_fotos
-- Ejecutar en HeidiSQL sobre la BD odonto_gest
-- ══════════════════════════════════════════════════════════════

USE odonto_gest;

-- ── Recetas ────────────────────────────────────────────────────
-- Una receta = prescripción médica emitida durante una consulta.
-- Vinculada al expediente del paciente y al odontólogo que la emite.
CREATE TABLE IF NOT EXISTS recetas (
    id_receta       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_expediente   INT UNSIGNED    NOT NULL COMMENT 'FK → expedientes',
    id_odontologo   INT UNSIGNED    NOT NULL COMMENT 'FK → odontologos (quien la emitió)',
    medicamento     VARCHAR(200)    NOT NULL COMMENT 'Nombre del medicamento',
    dosis           VARCHAR(100)    NOT NULL COMMENT 'Ej: 500mg, 1 comprimido',
    frecuencia      VARCHAR(100)    NOT NULL COMMENT 'Ej: cada 8 horas, 3 veces al día',
    duracion        VARCHAR(100)    NOT NULL COMMENT 'Ej: 7 días, hasta terminar',
    notas           TEXT                     COMMENT 'Indicaciones adicionales',
    fecha_emision   DATE            NOT NULL DEFAULT (CURDATE()),
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_receta),
    KEY idx_receta_expediente (id_expediente),
    CONSTRAINT fk_rec_expediente  FOREIGN KEY (id_expediente) REFERENCES expedientes (id_expediente) ON DELETE CASCADE,
    CONSTRAINT fk_rec_odontologo  FOREIGN KEY (id_odontologo) REFERENCES odontologos (id_odontologo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Recetas/prescripciones emitidas al paciente';

-- ── Expediente Fotos ───────────────────────────────────────────
-- Vincula imágenes (fotos dentales, radiografías, documentos) a un expediente.
-- Las imágenes físicas se guardan en el servidor y se referencian por URL en tabla imagenes.
CREATE TABLE IF NOT EXISTS expediente_fotos (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_expediente   INT UNSIGNED    NOT NULL COMMENT 'FK → expedientes',
    id_imagen       INT UNSIGNED    NOT NULL COMMENT 'FK → imagenes (URL del archivo)',
    descripcion     VARCHAR(300)             COMMENT 'Ej: Radiografía panorámica, Foto frontal',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ef_expediente (id_expediente),
    CONSTRAINT fk_ef_expediente FOREIGN KEY (id_expediente) REFERENCES expedientes  (id_expediente) ON DELETE CASCADE,
    CONSTRAINT fk_ef_imagen     FOREIGN KEY (id_imagen)     REFERENCES imagenes      (id_imagen)     ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Fotos y documentos del expediente clínico';

-- ── kv_img: agregar clave para fotos de expediente ────────────
INSERT IGNORE INTO kv_img (kv_key, kv_value) VALUES ('expedientes', 'fotos');

SELECT 'Migración v3.1 aplicada correctamente ✓' AS resultado;
