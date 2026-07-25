<?php
class ConfiguracionController {

    public function index(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        $pageTitle     = 'Configuración';
        $tab           = $_GET['tab'] ?? 'sistema';
        $config        = ConfiguracionModel::getAll();
        $sucursal      = ConfiguracionModel::getSucursal();
        $alergias      = ConfiguracionModel::listarAlergias();
        $enfermedades  = ConfiguracionModel::listarEnfermedades();
        $medicamentos  = ConfiguracionModel::listarMedicamentos();
        $tratamientos  = ConfiguracionModel::listarTratamientos();
        $especialidades= ConfiguracionModel::listarEspecialidades();
        $cargos        = ConfiguracionModel::listarCargos();
        $proveedores   = ConfiguracionModel::listarProveedores();
        $sangres       = ConfiguracionModel::listarSangres();
        require_once VIEW_PATH . 'Configuracion/index.php';
    }

    /* POST: guardar configuración general + sucursal */
    public function guardar(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: '.APP_URL.'configuracion'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $tipo = $_POST['tipo'] ?? 'sistema';

        if ($tipo === 'sistema') {
            ConfiguracionModel::saveMultiple([
                'clinica_nombre'   => trim($_POST['clinica_nombre']   ?? ''),
                'clinica_rtn'      => trim($_POST['clinica_rtn']      ?? ''),
                'moneda_simbolo'   => trim($_POST['moneda_simbolo']   ?? 'L'),
                'tasa_isv_reducida'=> trim($_POST['tasa_isv_reducida']?? '15'),
                'tasa_isv_general' => trim($_POST['tasa_isv_general'] ?? '18'),
            ]);
        } elseif ($tipo === 'sucursal') {
            ConfiguracionModel::guardarSucursal([
                'nombre'    => trim($_POST['sucursal_nombre']   ?? ''),
                'ubicacion' => trim($_POST['sucursal_ubicacion']?? ''),
                'contacto'  => trim($_POST['sucursal_contacto'] ?? ''),
                'telefono'  => trim($_POST['sucursal_telefono'] ?? ''),
                'rtn'       => trim($_POST['sucursal_rtn']      ?? ''),
                'cai'       => trim($_POST['sucursal_cai']      ?? ''),
            ]);
        }

        AuditoriaModel::registrar('configuracion','editar',"Config guardada: {$tipo}");
        header('Location: '.APP_URL.'configuracion?tab='.$tipo.'&ok=1'); exit;
    }

    /* POST JSON: guardar item de catálogo (AJAX) */
    public function guardarCatalogo(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $catalogo = $_POST['catalogo'] ?? '';
        $data     = $_POST;

        $id = match($catalogo) {
            'alergia'      => ConfiguracionModel::guardarAlergia($data),
            'enfermedad'   => ConfiguracionModel::guardarEnfermedad($data),
            'medicamento'  => ConfiguracionModel::guardarMedicamento($data),
            'tratamiento'  => ConfiguracionModel::guardarTratamiento($data),
            'especialidad' => ConfiguracionModel::guardarEspecialidad($data),
            'cargo'        => ConfiguracionModel::guardarCargo($data),
            'proveedor'    => ConfiguracionModel::guardarProveedor($data),
            default        => 0,
        };

        AuditoriaModel::registrar('configuracion','editar',"Catálogo {$catalogo} #{$id}");
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $id]);
        exit;
    }

    /* POST JSON: toggle estado de catálogo */
    public function toggleCatalogo(): void {
        Auth::requireLogin();
        Auth::requireRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $catalogo = $_POST['catalogo'] ?? '';
        $id       = (int)($_POST['id'] ?? 0);

        match($catalogo) {
            'alergia'    => ConfiguracionModel::toggleAlergia($id),
            'enfermedad' => ConfiguracionModel::toggleEnfermedad($id),
            'medicamento'=> ConfiguracionModel::toggleMedicamento($id),
            'tratamiento'=> ConfiguracionModel::toggleTratamiento($id),
            'cargo'      => ConfiguracionModel::toggleCargo($id),
            'proveedor'  => ConfiguracionModel::toggleProveedor($id),
            default      => null,
        };

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
