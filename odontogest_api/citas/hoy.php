<?php
// ── GET /citas/hoy.php ────────────────────────────────────────
// Headers: Authorization: Bearer <token>
// Respuesta: [ { id_cita, hora, paciente, servicio, estado, asistencia } ]

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

$user = getAuthUser();

try {
    $db = getDB();

    $sql = "
        SELECT
            c.id_cita,
            TIME_FORMAT(c.fecha_cita, '%H:%i')          AS hora,
            CONCAT(p.nombre, ' ', p.apellidos)           AS paciente,
            COALESCE(s.nombre, t.descripcion, 'Sin servicio') AS servicio,
            c.estado,
            c.asistencia
        FROM citas c
        JOIN pacientes p    ON p.id_paciente  = c.id_paciente
        LEFT JOIN servicios s    ON s.id_servicio  = c.id_servicio
        LEFT JOIN tratamientos t ON t.id_tratamiento = c.id_tratamiento
        WHERE DATE(c.fecha_cita) = CURDATE()
          AND c.id_odontologo = (
              SELECT id_odontologo FROM odontologos WHERE id_usuario = :id LIMIT 1
          )
        ORDER BY c.fecha_cita ASC
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $user['id_usuario']]);
    $citas = $stmt->fetchAll();

    // Si el usuario es Admin/Recepcionista (no es odontologo) devolver todas
    if (empty($citas)) {
        $stmt2 = $db->prepare("
            SELECT
                c.id_cita,
                TIME_FORMAT(c.fecha_cita, '%H:%i')          AS hora,
                CONCAT(p.nombre, ' ', p.apellidos)           AS paciente,
                COALESCE(s.nombre, t.descripcion, 'Sin servicio') AS servicio,
                c.estado,
                c.asistencia
            FROM citas c
            JOIN pacientes p    ON p.id_paciente  = c.id_paciente
            LEFT JOIN servicios s    ON s.id_servicio  = c.id_servicio
            LEFT JOIN tratamientos t ON t.id_tratamiento = c.id_tratamiento
            WHERE DATE(c.fecha_cita) = CURDATE()
            ORDER BY c.fecha_cita ASC
            LIMIT 20
        ");
        $stmt2->execute();
        $citas = $stmt2->fetchAll();
    }

    ok(['citas' => $citas, 'total' => count($citas)]);

} catch (PDOException $e) {
    error(500, 'Error de base de datos: ' . $e->getMessage());
}
