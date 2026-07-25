<?php
require_once __DIR__ . '/../_bootstrap.php';

try {
    $input = api_input();
    $idPaciente = (int)($input['id_paciente'] ?? 0);

    if ($idPaciente <= 0) {
        api_json([
            'success' => false,
            'message' => 'Debe indicar un paciente válido.',
        ], 422);
    }

    $db = Conexion::get();
    $st = $db->prepare('UPDATE pacientes SET estado = :estado WHERE id_paciente = :id_paciente');
    $st->execute([
        ':estado' => 'inactivo',
        ':id_paciente' => $idPaciente,
    ]);

    api_json([
        'success' => true,
    ]);
} catch (Throwable $e) {
    api_json([
        'success' => false,
        'message' => 'No fue posible eliminar el paciente.',
    ], 500);
}