<?php
/**
 * index.php — Front Controller
 * OdontoGest Panel Web — DeskCod
 */

// ── Cabeceras de seguridad ───────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// ── Core ────────────────────────────────────────────────────
require_once __DIR__ . '/Config/Define.php';
require_once __DIR__ . '/Config/AutoLoad.php';
require_once __DIR__ . '/Config/JRequest.php';
require_once __DIR__ . '/Config/JRouter.php';

AutoLoad::run();

// ── Sesión segura ────────────────────────────────────────────
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path'     => '/',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() === PHP_SESSION_NONE) session_start();

// CSRF token inicial
Csrf::token();

// ── Timeout de sesión ────────────────────────────────────────
if (Auth::isLoggedIn()) {
    if (isset($_SESSION['ultima_actividad']) &&
        (time() - $_SESSION['ultima_actividad']) > SESSION_TIMEOUT) {
        Auth::logout();
        header('Location: ' . APP_URL . 'Auth/index?expired=1');
        exit;
    }
    $_SESSION['ultima_actividad'] = time();
}

// ── Request ──────────────────────────────────────────────────
$req      = new JRequest();
$segmento = strtolower(str_replace('controller', '', strtolower($req->controller)));
$metodo   = $req->method;

// ── Control de acceso ────────────────────────────────────────
if (!Auth::isLoggedIn() && !in_array($segmento, PUBLIC_ROUTES, true)) {
    header('Location: ' . APP_URL . 'Auth/index');
    exit;
}

// ── ¿Requiere template? ──────────────────────────────────────
$sinTemplate  = ['auth'];
// Métodos que generan su propia respuesta (CSV, PDF, etc.) — sin template
$metodosRaw   = ['exportar'];
if (in_array($metodo, $metodosRaw, true)) {
    JRouter::run($req);
    exit;
}
$metodosJson  = array_map('strtolower', JSON_METHODS);
$usaTemplate  = !in_array($segmento, $sinTemplate, true)
             && !in_array($metodo, $metodosJson, true);

if ($usaTemplate) {
    // Buffer view output → permite que el controller setee $pageTitle antes del <head>
    ob_start();
    JRouter::run($req);
    $viewContent = ob_get_clean();

    require_once TEMPLATE_PATH . 'header.php';
    require_once TEMPLATE_PATH . 'menu.php';
    echo $viewContent;
    require_once TEMPLATE_PATH . 'footer.php';
} else {
    JRouter::run($req);
}
