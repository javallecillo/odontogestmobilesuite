<?php
class ServiciosController {
    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Servicios';
        $filtros = ['buscar'=>$_GET['buscar']??'','estado'=>$_GET['estado']??'','pagina'=>max(1,(int)($_GET['pagina']??1))];
        $servicios = ServiciosModel::listar($filtros);
        $total     = ServiciosModel::total($filtros);
        $totalPags = max(1,ceil($total/50));
        require_once VIEW_PATH.'Servicios/index.php';
    }
    public function crear(): void {
        Auth::requireLogin(); Auth::requireRol('Administrador');
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'servicios');exit;}
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'servicios?error=csrf'); exit;
        }
        $id=ServiciosModel::insertar($_POST);
        AuditoriaModel::registrar('configuracion','crear',"Servicio #{$id}");
        header('Location:'.APP_URL.'servicios?ok=creado'); exit;
    }
    public function actualizar(): void {
        Auth::requireLogin(); Auth::requireRol('Administrador');
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'servicios');exit;}
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'servicios?error=csrf'); exit;
        }
        $id=(int)($_POST['id_servicio']??0);
        ServiciosModel::actualizar($id,$_POST);
        AuditoriaModel::registrar('configuracion','editar',"Servicio #{$id}");
        header('Location:'.APP_URL.'servicios?ok=editado'); exit;
    }
    public function eliminar(): void {
        Auth::requireLogin(); Auth::requireRol('Administrador');
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'servicios');exit;}
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'servicios?error=csrf'); exit;
        }
        $id=(int)($_POST['id_servicio']??0);
        ServiciosModel::eliminar($id);
        AuditoriaModel::registrar('configuracion','eliminar',"Servicio #{$id}");
        header('Content-Type:application/json'); echo json_encode(['success'=>true]); exit;
    }

    public function toggleEstado(): void {
        Auth::requireLogin(); Auth::requireRol('Administrador');
        if($_SERVER['REQUEST_METHOD']!=='POST'){header('Location:'.APP_URL.'servicios');exit;}
        if (!Csrf::verify($_POST['csrf_token'] ?? '')) {
            header('Location: ' . APP_URL . 'servicios?error=csrf'); exit;
        }
        $id     = (int)($_POST['id_servicio'] ?? 0);
        $estado = in_array($_POST['estado']??'', ['activo','inactivo']) ? $_POST['estado'] : 'activo';
        Conexion::getInstance()->prepare("UPDATE servicios SET estado=:e WHERE id_servicio=:id")
            ->execute([':e' => $estado, ':id' => $id]);
        AuditoriaModel::registrar('configuracion', 'editar', "Servicio #{$id} → {$estado}");
        header('Content-Type:application/json'); echo json_encode(['success'=>true]); exit;
    }
}
