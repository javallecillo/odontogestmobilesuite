<?php
class PerfilController {
    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Mi Perfil';
        $perfil    = PerfilModel::obtener(Auth::id());
        require_once VIEW_PATH.'Perfil/index.php';
    }
    public function actualizar(): void {
        Auth::requireLogin();
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'perfil');exit;}
        Csrf::verify($_POST['csrf_token']??'');
        PerfilModel::actualizarDatos(Auth::id(),$_POST);
        // Actualizar nombre en sesión
        $_SESSION[SESSION_NAME.'_nombre'] = trim($_POST['nombre_completo']??'');
        header('Location:'.APP_URL.'perfil?ok=1'); exit;
    }
    public function password(): void {
        Auth::requireLogin();
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'perfil');exit;}
        Csrf::verify($_POST['csrf_token']??'');
        $actual  = $_POST['password_actual']  ?? '';
        $nuevo   = $_POST['password_nuevo']   ?? '';
        $confirm = $_POST['password_confirm'] ?? '';
        $hash    = PerfilModel::passwordActual(Auth::id());
        if(!password_verify($actual,$hash)){
            header('Location:'.APP_URL.'perfil?err=password_incorrecto'); exit;
        }
        if($nuevo!==$confirm || strlen($nuevo)<8){
            header('Location:'.APP_URL.'perfil?err=password_invalido'); exit;
        }
        PerfilModel::cambiarPassword(Auth::id(),password_hash($nuevo,PASSWORD_BCRYPT));
        AuditoriaModel::registrar('seguridad','editar','Cambio de contraseña');
        header('Location:'.APP_URL.'perfil?ok=password'); exit;
    }
}
