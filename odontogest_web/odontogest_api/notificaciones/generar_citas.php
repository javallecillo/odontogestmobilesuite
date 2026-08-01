<?php
// ── POST /notificaciones/generar_citas.php ───────────────────
// Genera alertas de citas próximas (llamar 1x/día desde cron o al cargar dashboard).
// Crea notificación si la cita es mañana y aún no existe una para ese id_cita.
// Solo genera para odontólogos dueños de la cita o administradores.

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/RateLimit.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') error(405, 'Método no permitido');
checkRateLimit();
$user = getAuthUser();

try {
    $db     = getDB();
    $manana = date('Y-m-d', strtotime('+1 day'));
    $hoy    = date('Y-m-d');

    // Citas de hoy y mañana que el usuario puede ver
    $rolSql = in_array($user['rol'], ['Administrador','Recepcionista'])
        ? ''
        : "AND o.id_usuario = {$user['id_usuario']}";

    $stmt = $db->query("
        SELECT
            c.id_cita,
            o.id_usuario AS id_odontologo_usuario,
            CONCAT(p.nombre,' ',p.apellidos) AS paciente,
            DATE_FORMAT(c.fecha_cita,'%H:%i') AS hora,
            DATE(c.fecha_cita) AS fecha_cita,
            s.nombre AS servicio
        FROM citas c
        JOIN pacientes   p ON p.id_paciente   = c.id_paciente
        LEFT JOIN servicios   s ON s.id_servicio   = c.id_servicio
        JOIN odontologos o ON o.id_odontologo = c.id_odontologo
        WHERE DATE(c.fecha_cita) IN ('$hoy','$manana')
          AND c.estado IN ('pendiente','confirmada')
          $rolSql
        ORDER BY c.fecha_cita ASC
    ");

    $citas   = $stmt->fetchAll();
    $creadas = 0;

    foreach ($citas as $cita) {
        $idUsuDest = (int)$cita['id_odontologo_usuario'];
        $fechaCita = $cita['fecha_cita'];
        $esMañana  = $fechaCita === $manana;
        $titulo    = $esMañana
            ? "Cita mañana — {$cita['hora']}"
            : "Cita hoy — {$cita['hora']}";
        $mensaje   = "{$cita['paciente']} · {$cita['servicio']}";
        $marcador  = "cita_{$cita['id_cita']}_" . ($esMañana ? 'manana' : 'hoy');

        // Evitar duplicados usando el título como clave de idempotencia
        $existe = $db->prepare(
            "SELECT COUNT(*) FROM notificaciones
             WHERE id_usuario = :uid AND titulo = :titulo AND DATE(fecha) = :hoy"
        );
        $existe->execute([':uid' => $idUsuDest, ':titulo' => $titulo, ':hoy' => $hoy]);
        if ((int)$existe->fetchColumn() > 0) continue;

        $ins = $db->prepare(
            "INSERT INTO notificaciones (id_usuario, titulo, mensaje)
             VALUES (:uid, :titulo, :msg)"
        );
        $ins->execute([':uid' => $idUsuDest, ':titulo' => $titulo, ':msg' => $mensaje]);
        $creadas++;

        // Si es admin también notificar al mismo admin
        if ($user['rol'] === 'Administrador' && $idUsuDest !== $user['id_usuario']) {
            $existeAdmin = $db->prepare(
                "SELECT COUNT(*) FROM notificaciones
                 WHERE id_usuario = :uid AND titulo = :titulo AND DATE(fecha) = :hoy"
            );
            $existeAdmin->execute([':uid' => $user['id_usuario'], ':titulo' => $titulo, ':hoy' => $hoy]);
            if ((int)$existeAdmin->fetchColumn() === 0) {
                $ins->execute([':uid' => $user['id_usuario'], ':titulo' => $titulo, ':msg' => $mensaje]);
                $creadas++;
            }
        }
    }

    ok(['mensaje' => "Notificaciones generadas: $creadas", 'creadas' => $creadas]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
