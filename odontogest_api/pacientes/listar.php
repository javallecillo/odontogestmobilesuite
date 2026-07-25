<?php
require_once __DIR__ . '/../_bootstrap.php';

try {
    $db = Conexion::get();

    $estado = trim((string)($_GET['estado'] ?? 'activo'));
    $limit = max(1, min(1000, (int)($_GET['limit'] ?? 500)));

    $where = ['1=1'];
    $params = [];

    if ($estado !== '' && $estado !== 'todos') {
        $where[] = 'estado = :estado';
        $params[':estado'] = $estado;
    }

    $sql = 'SELECT id_paciente, nombre, fecha_nacimiento, telefono, estado
            FROM pacientes
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY nombre, apellidos
            LIMIT ' . (int) $limit;

    $st = $db->prepare($sql);
    foreach ($params as $key => $value) {
        $st->bindValue($key, $value);
    }
    $st->execute();

    api_json([
        'success' => true,
        'pacientes' => $st->fetchAll(PDO::FETCH_ASSOC),
    ]);
} catch (Throwable $e) {
    api_json([
        'success' => false,
        'message' => 'No fue posible listar los pacientes.',
    ], 500);
}