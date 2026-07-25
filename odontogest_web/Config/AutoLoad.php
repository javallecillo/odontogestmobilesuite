<?php
class AutoLoad {
    public static function run(): void {
        spl_autoload_register(function (string $class): void {
            $dirs = [
                ROOT_PATH . 'Config/Core/',
                ROOT_PATH . 'Config/',
                ROOT_PATH . 'Controllers/',
                ROOT_PATH . 'Models/',
                ROOT_PATH . 'Entity/',
            ];
            foreach ($dirs as $dir) {
                $file = $dir . $class . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        });
    }
}
