<?php
require_once __DIR__ . '/../_bootstrap.php';

try {
    $input = api_input();

    $idPaciente = (int)($input['id_paciente'] ?? 0);
    $nombre = trim((string)($input['nombre'] ?? ''));
    $fechaNacimiento = trim((string)($input['fecha_nacimiento'] ?? ''));
    $telefono = trim((string)($input['telefono'] ?? ''));

    if ($nombre === '' || $fechaNacimiento === '' || $telefono === '') {
        api_json([
            'success' => false,
            'message' => 'Nombre, fecha de nacimiento y teléfono son obligatorios.',
        ], 422);
    }

    $fecha = DateTime::createFromFormat('Y-m-d', $fechaNacimiento);
    if (!$fecha || $fecha->format('Y-m-d') !== $fechaNacimiento) {
        api_json([
            'success' => false,
            'message' => 'La fecha de nacimiento debe tener formato YYYY-MM-DD.',
        ], 422);
    }

    $db = Conexion::get();

    if ($idPaciente > 0) {
        $st = $db->prepare(
            'UPDATE pacientes SET
                nombre = :nombre,
                apellidos = :apellidos,
                fecha_nacimiento = :fecha_nacimiento,
                telefono = :telefono,
                estado = :estado
             WHERE id_paciente = :id_paciente'
        );
        $st->execute([
            ':nombre' => $nombre,
            ':apellidos' => '',
            ':fecha_nacimiento' => $fechaNacimiento,
            ':telefono' => $telefono,
            ':estado' => 'activo',
            ':id_paciente' => $idPaciente,
        ]);
    } else {
        $st = $db->prepare(
            'INSERT INTO pacientes (
                nombre, apellidos, fecha_nacimiento, telefono, estado
             ) VALUES (
                :nombre, :apellidos, :fecha_nacimiento, :telefono, :estado
             )'
        );
        $st->execute([
            ':nombre' => $nombre,
            ':apellidos' => '',
            ':fecha_nacimiento' => $fechaNacimiento,
            ':telefono' => $telefono,
            ':estado' => 'activo',
        ]);
        $idPaciente = (int) $db->lastInsertId();
    }

    $row = $db->prepare(
        'SELECT id_paciente, nombre, fecha_nacimiento, telefono, estado
         FROM pacientes
         WHERE id_paciente = :id_paciente'
    );
    $row->execute([':id_paciente' => $idPaciente]);

    api_json([
        'success' => true,
        'paciente' => $row->fetch(PDO::FETCH_ASSOC),
    ]);
} catch (Throwable $e) {
    api_json([
        'success' => false,
        'message' => 'No fue posible guardar el paciente.',
    ], 500);
}