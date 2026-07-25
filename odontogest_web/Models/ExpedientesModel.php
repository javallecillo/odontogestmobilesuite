<?php
/**
 * ExpedientesModel — Expediente clínico completo
 * Incluye: expedientes, expediente_alergias, expediente_enfermedades,
 *          expediente_medicamentos, odontograma, tratamientos_historial
 */
class ExpedientesModel {

    /* ── Catálogos clínicos ─────────────────────────────────── */
    public static function tiposSangre(): array {
        return Conexion::getInstance()->query("SELECT id_sangre, descripcion FROM sangres ORDER BY id_sangre")->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function alergias(): array {
        return Conexion::getInstance()->query("SELECT id_alergia, descripcion FROM alergias WHERE estado='activa' ORDER BY descripcion")->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function enfermedades(): array {
        return Conexion::getInstance()->query("SELECT id_enfermedad, descripcion FROM enfermedades WHERE estado='activa' ORDER BY descripcion")->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function medicamentos(): array {
        return Conexion::getInstance()->query("SELECT id_medicamento, descripcion FROM medicamentos WHERE estado='activo' ORDER BY descripcion")->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Obtener expediente completo ────────────────────────── */
    public static function obtenerPorPaciente(int $idPaciente): array|false {
        $db = Conexion::getInstance();
        $st = $db->prepare("
            SELECT e.*,
                   s.descripcion AS grupo_sangre,
                   p.nombre, p.apellidos, p.dni, p.fecha_nacimiento, p.sexo,
                   p.telefono, p.correo, p.estado AS estado_paciente
            FROM expedientes e
            JOIN pacientes p ON p.id_paciente = e.id_paciente
            LEFT JOIN sangres s ON s.id_sangre = e.id_sangre
            WHERE e.id_paciente = :id LIMIT 1
        ");
        $st->execute([':id'=>$idPaciente]);
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    /* ── Alergias del paciente ──────────────────────────────── */
    public static function alergiasExpediente(int $idExpediente): array {
        $db = Conexion::getInstance();
        $st = $db->prepare("
            SELECT a.id_alergia, a.descripcion, ea.observacion
            FROM expediente_alergias ea
            JOIN alergias a ON a.id_alergia = ea.id_alergia
            WHERE ea.id_expediente = :id ORDER BY a.descripcion
        ");
        $st->execute([':id'=>$idExpediente]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Enfermedades sistémicas del paciente ───────────────── */
    public static function enfermedadesExpediente(int $idExpediente): array {
        $db = Conexion::getInstance();
        $st = $db->prepare("
            SELECT en.id_enfermedad, en.descripcion, ee.observacion
            FROM expediente_enfermedades ee
            JOIN enfermedades en ON en.id_enfermedad = ee.id_enfermedad
            WHERE ee.id_expediente = :id ORDER BY en.descripcion
        ");
        $st->execute([':id'=>$idExpediente]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Medicamentos actuales del paciente ─────────────────── */
    public static function medicamentosExpediente(int $idExpediente): array {
        $db = Conexion::getInstance();
        $st = $db->prepare("
            SELECT m.id_medicamento, m.descripcion, em.dosis, em.observacion
            FROM expediente_medicamentos em
            JOIN medicamentos m ON m.id_medicamento = em.id_medicamento
            WHERE em.id_expediente = :id ORDER BY m.descripcion
        ");
        $st->execute([':id'=>$idExpediente]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Historial de citas ─────────────────────────────────── */
    public static function historialCitas(int $idPaciente): array {
        $db = Conexion::getInstance();
        $st = $db->prepare("
            SELECT c.id_cita, c.fecha_cita, c.estado, c.notas,
                   CONCAT(o.nombre,' ',o.apellidos) AS odontologo,
                   s.nombre AS servicio
            FROM citas c
            JOIN odontologos o ON o.id_odontologo = c.id_odontologo
            LEFT JOIN servicios s ON s.id_servicio = c.id_servicio
            WHERE c.id_paciente = :id ORDER BY c.fecha_cita DESC
        ");
        $st->execute([':id'=>$idPaciente]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Odontograma ────────────────────────────────────────── */
    public static function odontograma(int $idPaciente): array {
        $db = Conexion::getInstance();
        $st = $db->prepare("
            SELECT od.pieza_dental, od.cara, od.condicion, od.color_codigo,
                   od.descripcion, od.fecha_registro,
                   CONCAT(o.nombre,' ',o.apellidos) AS odontologo
            FROM odontograma od
            JOIN expedientes e ON e.id_expediente = od.id_expediente
            JOIN odontologos o ON o.id_odontologo = od.id_odontologo
            WHERE e.id_paciente = :id ORDER BY od.pieza_dental, od.fecha_registro DESC
        ");
        $st->execute([':id'=>$idPaciente]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Facturas del paciente ──────────────────────────────── */
    public static function facturasPaciente(int $idPaciente): array {
        $db = Conexion::getInstance();
        $st = $db->prepare("
            SELECT id_factura, numero_factura, estado, subtotal, impuesto, total,
                   metodo_pago, DATE(fecha_emision) AS fecha
            FROM factura WHERE id_paciente = :id ORDER BY fecha_emision DESC
        ");
        $st->execute([':id'=>$idPaciente]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ── Documentos adjuntos ────────────────────────────────── */
    public static function documentos(int $idPaciente): array {
        return []; // Implementar cuando se integre el módulo de imágenes/archivos
    }

    /* ── Guardar/actualizar expediente base ──────────────────── */
    public static function guardarExpediente(int $idPaciente, array $d): int {
        $db = Conexion::getInstance();
        $existe = $db->prepare("SELECT id_expediente FROM expedientes WHERE id_paciente=:id");
        $existe->execute([':id'=>$idPaciente]);
        $row = $existe->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $db->prepare("UPDATE expedientes SET id_sangre=:sg, antecedentes=:ant, observaciones=:obs WHERE id_expediente=:id")
               ->execute([':sg'=>$d['id_sangre']??null,':ant'=>$d['antecedentes']??null,':obs'=>$d['observaciones']??null,':id'=>$row['id_expediente']]);
            return $row['id_expediente'];
        } else {
            $db->prepare("INSERT INTO expedientes (id_paciente,id_sangre,antecedentes,observaciones) VALUES (:pac,:sg,:ant,:obs)")
               ->execute([':pac'=>$idPaciente,':sg'=>$d['id_sangre']??null,':ant'=>$d['antecedentes']??null,':obs'=>$d['observaciones']??null]);
            return (int)$db->lastInsertId();
        }
    }

    /* ── Sincronizar alergias (replace) ─────────────────────── */
    public static function sincronizarAlergias(int $idExpediente, array $ids): void {
        $db = Conexion::getInstance();
        $db->prepare("DELETE FROM expediente_alergias WHERE id_expediente=:id")->execute([':id'=>$idExpediente]);
        foreach ($ids as $idA) {
            $db->prepare("INSERT INTO expediente_alergias (id_expediente,id_alergia) VALUES (:exp,:al)")
               ->execute([':exp'=>$idExpediente,':al'=>(int)$idA]);
        }
    }

    /* ── Sincronizar enfermedades ────────────────────────────── */
    public static function sincronizarEnfermedades(int $idExpediente, array $ids): void {
        $db = Conexion::getInstance();
        $db->prepare("DELETE FROM expediente_enfermedades WHERE id_expediente=:id")->execute([':id'=>$idExpediente]);
        foreach ($ids as $idE) {
            $db->prepare("INSERT INTO expediente_enfermedades (id_expediente,id_enfermedad) VALUES (:exp,:en)")
               ->execute([':exp'=>$idExpediente,':en'=>(int)$idE]);
        }
    }

    /* ── Sincronizar medicamentos ────────────────────────────── */
    public static function sincronizarMedicamentos(int $idExpediente, array $items): void {
        $db = Conexion::getInstance();
        $db->prepare("DELETE FROM expediente_medicamentos WHERE id_expediente=:id")->execute([':id'=>$idExpediente]);
        foreach ($items as $item) {
            $db->prepare("INSERT INTO expediente_medicamentos (id_expediente,id_medicamento,dosis,observacion) VALUES (:exp,:med,:dos,:obs)")
               ->execute([':exp'=>$idExpediente,':med'=>(int)$item['id'],':dos'=>$item['dosis']??null,':obs'=>$item['obs']??null]);
        }
    }

    /* ── Guardar pieza odontograma ───────────────────────────── */
    public static function guardarOdontograma(int $idPaciente, array $dientes): void {
        $db = Conexion::getInstance();
        $row = $db->prepare("SELECT id_expediente FROM expedientes WHERE id_paciente=:id");
        $row->execute([':id'=>$idPaciente]);
        $exp = $row->fetchColumn();
        if (!$exp) return;

        // Resolver id_odontologo desde la tabla odontologos (FK, no id_usuario)
        $odRow = $db->prepare("SELECT id_odontologo FROM odontologos WHERE id_usuario=:uid LIMIT 1");
        $odRow->execute([':uid' => Auth::id()]);
        $idOd = $odRow->fetchColumn();
        if (!$idOd) {
            // El usuario logueado no es odontólogo (p.ej. Administrador):
            // usar el primer odontólogo registrado en el sistema
            $idOd = $db->query("SELECT id_odontologo FROM odontologos ORDER BY id_odontologo LIMIT 1")->fetchColumn();
        }
        if (!$idOd) {
            throw new \RuntimeException('No existe ningún odontólogo registrado. Crea uno antes de guardar el odontograma.');
        }

        foreach ($dientes as $pieza => $info) {
            // UPDATE si ya existe esa pieza en este expediente, INSERT si no
            $exists = $db->prepare("SELECT id_odontograma FROM odontograma WHERE id_expediente=:exp AND pieza_dental=:pz LIMIT 1");
            $exists->execute([':exp'=>$exp,':pz'=>(int)$pieza]);
            $idExisting = $exists->fetchColumn();

            if ($idExisting) {
                $db->prepare("UPDATE odontograma SET condicion=:cond,color_codigo=:col,descripcion=:desc,cara=:ca WHERE id_odontograma=:id")
                   ->execute([':cond'=>$info['condicion']??'sano',':col'=>$info['color']??'#E5E7EB',':desc'=>$info['descripcion']??null,':ca'=>$info['cara']??'ninguna',':id'=>$idExisting]);
            } else {
                $db->prepare("INSERT INTO odontograma (id_expediente,id_odontologo,pieza_dental,cara,condicion,color_codigo,descripcion) VALUES (:exp,:od,:pz,:ca,:cond,:col,:desc)")
                   ->execute([':exp'=>$exp,':od'=>$idOd,':pz'=>(int)$pieza,':ca'=>$info['cara']??'ninguna',':cond'=>$info['condicion']??'sano',':col'=>$info['color']??'#E5E7EB',':desc'=>$info['descripcion']??null]);
            }
        }
    }

    /* ── Agregar nota/observación ────────────────────────────── */
    public static function agregarNota(int $idPaciente, ?int $idCita, string $nota, string $tipo): void {
        // Agrega nota como registro en tratamientos_historial o como update del expediente
        $db = Conexion::getInstance();
        $db->prepare("UPDATE expedientes SET observaciones=CONCAT(COALESCE(observaciones,''),:nota) WHERE id_paciente=:id")
           ->execute([':nota'=>"\n[".date('d/m/Y H:i')."] ".$nota,':id'=>$idPaciente]);
    }
}
