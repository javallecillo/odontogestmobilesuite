<?php
// ── GET /pacientes/buscar.php?q=texto ─────────────────────────
// Busca pacientes por nombre, apellidos o número de expediente.
// Requiere Bearer token.

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

getAuthUser();

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) error(400, 'Ingresa al menos 2 caracteres para buscar');

try {
    $db   = getDB();
    $like = '%' . $q . '%';

    $stmt = $db->prepare("
        SELECT
            p.id_paciente,
            CONCAT(p.nombre, ' ', p.apellidos)  AS nombre_completo,
            p.fecha_nacimiento,
            p.telefono,
            p.correo,
            p.estado,
            e.id_expediente,
            CONCAT('EXP-', LPAD(e.id_expediente, 5, '0')) AS numero_expediente
        FROM pacientes p
        LEFT JOIN expedientes e ON e.id_paciente = p.id_paciente
        WHERE p.estado = 'activo'
          AND (
              p.nombre    LIKE :q
           OR p.apellidos LIKE :q
          )
        ORDER BY p.nombre, p.apellidos
        LIMIT 20
    ");
    $stmt->execute([':q' => $like]);
    $pacientes = $stmt->fetchAll();

    ok(['pacientes' => $pacientes, 'total' => count($pacientes)]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
