<?php
class JRouter {
    public static function run(JRequest $req): void {
        $controllerClass = $req->controller;
        $method          = $req->method;
        $params          = $req->params;

        $file = ROOT_PATH . 'Controllers/' . $controllerClass . '.php';

        if (!file_exists($file)) {
            http_response_code(404);
            echo '<h1>404 — Página no encontrada</h1>';
            return;
        }

        require_once $file;

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            http_response_code(404);
            echo '<h1>404 — Método no encontrado</h1>';
            return;
        }

        $controller->$method(...$params);
    }
}
