<?php
// ── GET /expediente/tratamientos/catalogo.php ─────────────────
// Devuelve el catálogo de tipos de tratamiento para el selector en Flutter.

require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/Auth.php';
require_once __DIR__ . '/../../core/Response.php';

corsHeaders();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') error(405, 'Método no permitido');

getAuthUser();

try {
    $db   = getDB();
    $rows = $db->query("
        SELECT id_tratamiento,
               descripcion,
               COALESCE(precio_base, 0) AS precio_base
        FROM   tratamientos
        WHERE  estado = 'activo'
        ORDER  BY descripcion
    ")->fetchAll();

    ok(['tratamientos' => $rows]);

} catch (PDOException $e) {
    error(500, $e->getMessage());
}
