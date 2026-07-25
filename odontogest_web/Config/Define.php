<?php
// Constantes globales del panel web OdontoGest

define('APP_NAME',    'OdontoGest');
define('APP_VERSION', '1.0.0');

// URL base
// URL dinámica — funciona en cualquier puerto/host sin cambiar código
$_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_path     = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\'') . '/';
define('APP_URL', $_protocol . '://' . $_host . $_path);

// Rutas absolutas
define('ROOT_PATH',     __DIR__ . '/../');
define('TEMPLATE_PATH', ROOT_PATH . 'Template/Default/');
define('VIEW_PATH',     ROOT_PATH . 'Views/');


// Base de datos
define('DB_HOST', '54.87.216.191');
define('DB_NAME', 'odonto_gest');
define('DB_USER', 'jval02');
define('DB_PASS', 'J0rg34r7ur0_v_3');
define('DB_PORT', 3306);

// Sesion
define('SESSION_NAME',     'og_web_sess');
define('SESSION_LIFETIME', 0);
define('SESSION_TIMEOUT',  7200);

// Rutas publicas (sin login)
define('PUBLIC_ROUTES', ['auth']);

// Metodos que devuelven JSON o hacen redirect directo (no cargan template)
define('JSON_METHODS', [
    // Auth
    'procesarLogin', 'logout',
    // Genericos JSON
    'toggle', 'delete', 'save', 'buscar', 'cambiarEstado',
    'resetPassword', 'toggleEstado',
    // Notificaciones
    'obtener', 'marcarLeida', 'marcarTodas',
    // Agenda / Pacientes / Inventario / Servicios / Roles / Facturacion
    'crear', 'actualizar', 'eliminar',
    'marcarPagada', 'anular', 'ajustarStock',
    // Expedientes
    'guardarExpediente', 'guardarOdontograma', 'agregarNota',
    // Perfil
    'password',
    // Configuracion - catalogos AJAX y redirect guardar
    'guardarCatalogo', 'toggleCatalogo', 'guardar',
]);
