<?php
/**
 * ExpedientesController — Expediente clínico por paciente
 */
class ExpedientesController {

    public function index(): void {
        Auth::requireLogin();
        $pageTitle = 'Expedientes';

        $filtros = [
            'buscar' => $_GET['buscar'] ?? '',
            'pagina' => max(1, (int)($_GET['pagina'] ?? 1)),
        ];

        $pacientes = PacientesModel::listar($filtros);
        $total     = PacientesModel::total($filtros);
        $porPagina = 15;
        $totalPags = max(1, ceil($total / $porPagina));

        require_once VIEW_PATH . 'Expedientes/index.php';
    }

    public function ver(): void {
        Auth::requireLogin();
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { header('Location: ' . APP_URL . 'expedientes'); exit; }

        $pageTitle  = 'Expediente Clínico';
        $tab        = $_GET['tab'] ?? 'historial';
        $paciente   = PacientesModel::obtenerPorId($id);
        if (!$paciente) { header('Location: ' . APP_URL . 'expedientes'); exit; }

        $historial  = ExpedientesModel::historialCitas($id);
        $facturas   = ExpedientesModel::facturasPaciente($id);
        $odontograma= ExpedientesModel::odontograma($id);
        $documentos = ExpedientesModel::documentos($id);

        require_once VIEW_PATH . 'Expedientes/ver.php';
    }

    public function agregarNota(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'expedientes'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id_paciente = (int)($_POST['id_paciente'] ?? 0);
        $id_cita     = (int)($_POST['id_cita']     ?? 0) ?: null;
        $nota        = trim($_POST['nota']          ?? '');
        $tipo        = $_POST['tipo']               ?? 'general';

        ExpedientesModel::agregarNota($id_paciente, $id_cita, $nota, $tipo);
        AuditoriaModel::registrar('expedientes', 'crear', "Nota expediente paciente #{$id_paciente}");
        header('Location: ' . APP_URL . 'expedientes/ver?id=' . $id_paciente . '&tab=historial&ok=1');
        exit;
    }

    public function guardarOdontograma(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'expedientes'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id_paciente = (int)($_POST['id_paciente'] ?? 0);
        // Guardado pieza individual desde el formulario del odontograma
        $dientes = [
            $_POST['pieza'] ?? 0 => [
                'condicion'   => $_POST['condicion']   ?? 'sano',
                'color'       => $_POST['color']       ?? '#E5E7EB',
                'descripcion' => $_POST['descripcion'] ?? '',
                'cara'        => $_POST['cara']        ?? 'ninguna',
            ]
        ];

        ExpedientesModel::guardarOdontograma($id_paciente, $dientes);
        AuditoriaModel::registrar('expedientes', 'editar', "Odontograma paciente #{$id_paciente}");
        header('Location: ' . APP_URL . 'expedientes/ver?id=' . $id_paciente . '&tab=odontograma&ok=1');
        exit;
    }

    public function guardarExpediente(): void {
        Auth::requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ' . APP_URL . 'expedientes'); exit; }
        Csrf::verify($_POST['csrf_token'] ?? '');

        $id_paciente = (int)($_POST['id_paciente'] ?? 0);

        // 1. Guardar datos base del expediente (sangre, antecedentes, observaciones)
        $idExp = ExpedientesModel::guardarExpediente($id_paciente, [
            'id_sangre'      => $_POST['id_sangre']    ?? null,
            'antecedentes'   => trim($_POST['antecedentes']  ?? ''),
            'observaciones'  => trim($_POST['observaciones'] ?? ''),
        ]);

        // 2. Sincronizar alergias
        $alergias = array_map('intval', $_POST['alergias'] ?? []);
        ExpedientesModel::sincronizarAlergias($idExp, $alergias);

        // 3. Sincronizar enfermedades
        $enfermedades = array_map('intval', $_POST['enfermedades'] ?? []);
        ExpedientesModel::sincronizarEnfermedades($idExp, $enfermedades);

        // 4. Sincronizar medicamentos con dosis
        $medIds   = array_map('intval', $_POST['medicamentos_ids'] ?? []);
        $medDosis = $_POST['medicamentos_dosis'] ?? [];
        $medItems = array_map(fn($id) => [
            'id'   => $id,
            'dosis'=> trim($medDosis[$id] ?? ''),
        ], $medIds);
        ExpedientesModel::sincronizarMedicamentos($idExp, $medItems);

        AuditoriaModel::registrar('expedientes', 'editar', "Datos clínicos paciente #{$id_paciente}");
        header('Location: ' . APP_URL . 'expedientes/ver?id=' . $id_paciente . '&tab=expediente&ok=1');
        exit;
    }
}
