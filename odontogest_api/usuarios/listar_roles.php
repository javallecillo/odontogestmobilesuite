<?php
// ── GET /usuarios/listar_roles.php ───────────────────────────
// Devuelve los roles disponibles para el formulario de crear usuario

require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

getAuthUser(); // solo autenticado

try {
    $db   = getDB();
    $stmt = $db->query('SELECT id_rol, nombre FROM roles ORDER BY id_rol');
    ok(['roles' => $stmt->fetchAll()]);
} catch (PDOException $e) {
    error(500, $e->getMessage());
}
