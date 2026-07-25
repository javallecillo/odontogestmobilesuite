<?php
declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../odontogest_web/Config/Define.php';
require_once __DIR__ . '/../odontogest_web/Config/Conexion.php';

try {
	$pdo = Conexion::get();
	$stmt = $pdo->query('SELECT 1 AS conexion_ok');
	$resultado = $stmt->fetch();

	echo '<!doctype html>';
	echo '<html lang="es">';
	echo '<head><meta charset="utf-8"><title>Prueba de conexión</title></head>';
	echo '<body style="font-family: Arial, sans-serif; padding: 24px;">';
	echo '<h1>Conexión exitosa</h1>';
	echo '<p>La base de datos respondió correctamente.</p>';
	echo '<pre>' . htmlspecialchars(print_r($resultado, true), ENT_QUOTES, 'UTF-8') . '</pre>';
	echo '</body></html>';
} catch (Throwable $e) {
	http_response_code(500);

	echo '<!doctype html>';
	echo '<html lang="es">';
	echo '<head><meta charset="utf-8"><title>Error de conexión</title></head>';
	echo '<body style="font-family: Arial, sans-serif; padding: 24px;">';
	echo '<h1>Error de conexión</h1>';
	echo '<p>No fue posible conectar con la base de datos.</p>';
	echo '<pre>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
	echo '</body></html>';
}
