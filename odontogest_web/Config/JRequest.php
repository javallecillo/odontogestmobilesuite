<?php
class JRequest {
    public string $controller;
    public string $method;
    public array  $params;

    public function __construct() {
        $url    = trim($_GET['url'] ?? '', '/');
        $partes = array_filter(explode('/', $url));
        $partes = array_values($partes);

        $this->controller = ucfirst(strtolower($partes[0] ?? 'dashboard')) . 'Controller';
        $this->method     = strtolower($partes[1] ?? 'index');
        $this->params     = array_slice($partes, 2);
    }
}
