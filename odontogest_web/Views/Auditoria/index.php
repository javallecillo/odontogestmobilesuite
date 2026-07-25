<?php
// Variables: $data (registros, total, pages), $modulos, $acciones, $usuario, $modulo,
//            $accion, $ip, $desde, $hasta, $page

$moduloColor = [
    'seguridad'     => ['bg' => 'rgba(0,92,62,.10)',     'txt' => '#005C3E',  'icon' => 'fa-shield-alt'],
    'agenda'        => ['bg' => 'rgba(2,132,199,.10)',   'txt' => '#0284c7',  'icon' => 'fa-calendar-alt'],
    'expedientes'   => ['bg' => 'rgba(147,51,234,.10)',  'txt' => '#9333ea',  'icon' => 'fa-folder-open'],
    'facturacion'   => ['bg' => 'rgba(217,119,6,.10)',   'txt' => '#d97706',  'icon' => 'fa-file-invoice-dollar'],
    'inventario'    => ['bg' => 'rgba(22,163,74,.10)',   'txt' => '#16a34a',  'icon' => 'fa-boxes-stacked'],
    'configuracion' => ['bg' => 'rgba(107,114,128,.10)', 'txt' => '#6b7280',  'icon' => 'fa-cog'],
    'reportes'      => ['bg' => 'rgba(59,130,246,.10)',  'txt' => '#3b82f6',  'icon' => 'fa-chart-bar'],
    'sistema'       => ['bg' => 'rgba(15,23,42,.08)',    'txt' => '#0f172a',  'icon' => 'fa-server'],
];

$accionColor = [
    'crear'    => ['cls' => 'badge-activo',     'icon' => 'fa-plus-circle'],
    'editar'   => ['cls' => 'badge-odontologo', 'icon' => 'fa-edit'],
    'eliminar' => ['cls' => 'badge-inactivo',   'icon' => 'fa-trash'],
    'ver'      => ['cls' => 'badge-asistente',  'icon' => 'fa-eye'],
    'login'    => ['cls' => 'badge-admin',      'icon' => 'fa-sign-in-alt'],
    'logout'   => ['cls' => 'badge-recep',      'icon' => 'fa-sign-out-alt'],
    'anular'   => ['cls' => 'badge-inactivo',   'icon' => 'fa-ban'],
];

$baseUrl = APP_URL . 'Auditoria/index?' . http_build_query([
    'usuario' => $usuario, 'modulo' => $modulo, 'accion' => $accion,
    'ip' => $ip, 'desde' => $desde, 'hasta' => $hasta, 'page' => '',
]);
$exportUrl = APP_URL . 'Auditoria/exportar?' . http_build_query([
    'usuario' => $usuario, 'modulo' => $modulo, 'accion' => $accion,
    'ip' => $ip, 'desde' => $desde, 'hasta' => $hasta,
]);
?>

<div style="padding:24px 28px;">

    <!-- ── Cabecera ──── -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h4 style="margin:0;font-weight:800;color:#005C3E;">
                <i class="fas fa-history me-2"></i>Auditoría del Sistema
            </h4>
            <small style="color:#6b7280;"><?= number_format($data['total']) ?> registro<?= $data['total'] !== 1 ? 's' : '' ?></small>
        </div>
        <a href="<?= htmlspecialchars($exportUrl) ?>"
           class="btn btn-outline-success" style="border-radius:8px;padding:8px 16px;font-size:.88rem;border-color:#005C3E;color:#005C3E;">
            <i class="fas fa-file-csv me-2"></i>Exportar CSV
        </a>
    </div>

    <!-- ── Filtros ──── -->
    <form method="GET" action="<?= APP_URL ?>Auditoria/index"
          style="background:#fff;border-radius:12px;border:1px solid rgba(0,92,62,.10);padding:16px 20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.05);">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;align-items:flex-end;">

            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Usuario</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="usuario"
                           value="<?= htmlspecialchars($usuario) ?>" placeholder="Nombre o usuario">
                </div>
            </div>

            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Módulo</label>
                <select class="form-select" name="modulo">
                    <option value="">Todos</option>
                    <?php foreach ($modulos as $m): ?>
                    <option value="<?= $m ?>" <?= $modulo === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Acción</label>
                <select class="form-select" name="accion">
                    <option value="">Todas</option>
                    <?php foreach ($acciones as $a): ?>
                    <option value="<?= $a ?>" <?= $accion === $a ? 'selected' : '' ?>><?= ucfirst($a) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">IP</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                    <input type="text" class="form-control" name="ip"
                           value="<?= htmlspecialchars($ip) ?>" placeholder="192.168.x.x">
                </div>
            </div>

            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Desde</label>
                <input type="date" class="form-control" name="desde" value="<?= htmlspecialchars($desde) ?>">
            </div>

            <div>
                <label style="font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Hasta</label>
                <input type="date" class="form-control" name="hasta" value="<?= htmlspecialchars($hasta) ?>">
            </div>

            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-og-primary" style="padding:9px 18px;border-radius:8px;font-size:.88rem;white-space:nowrap;">
                    <i class="fas fa-filter me-1"></i>Filtrar
                </button>
                <a href="<?= APP_URL ?>Auditoria/index" class="btn btn-outline-secondary" style="border-radius:8px;padding:9px 14px;font-size:.88rem;">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </div>
    </form>

    <!-- ── Tabla ──── -->
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,92,62,.10);overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);">
        <?php if (empty($data['registros'])): ?>
        <div style="padding:48px;text-align:center;color:#6b7280;">
            <i class="fas fa-history fa-2x mb-3 d-block" style="opacity:.2;"></i>
            No hay registros con los filtros aplicados.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="tabla-og">
            <thead>
                <tr>
                    <th style="width:150px;">Fecha</th>
                    <th>Usuario</th>
                    <th style="width:120px;">Módulo</th>
                    <th style="width:100px;">Acción</th>
                    <th>Descripción</th>
                    <th style="width:130px;">IP</th>
                    <th style="width:40px;text-align:center;" title="Detalles">
                        <i class="fas fa-info-circle"></i>
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data['registros'] as $r):
                $mc = $moduloColor[$r['modulo']] ?? ['bg'=>'rgba(0,0,0,.06)','txt'=>'#374151','icon'=>'fa-circle'];
                $ac = $accionColor[$r['accion']]  ?? ['cls'=>'badge-inactivo','icon'=>'fa-circle'];
                $esHoy = date('Y-m-d') === date('Y-m-d', strtotime($r['fecha']));
            ?>
            <tr>
                <!-- Fecha -->
                <td style="font-size:.82rem;white-space:nowrap;">
                    <div style="font-weight:<?= $esHoy ? '700' : '400' ?>;color:<?= $esHoy ? '#005C3E' : '#374151' ?>;">
                        <?= date('d/m/Y', strtotime($r['fecha'])) ?>
                    </div>
                    <div style="color:#6b7280;font-size:.75rem;">
                        <?= date('H:i:s', strtotime($r['fecha'])) ?>
                    </div>
                </td>

                <!-- Usuario -->
                <td>
                    <div style="font-weight:600;font-size:.88rem;">
                        <?= htmlspecialchars($r['nombre_completo']) ?>
                    </div>
                    <div style="font-size:.76rem;color:#6b7280;">
                        @<?= htmlspecialchars($r['usuario']) ?>
                        <span style="margin-left:4px;font-size:.7rem;background:rgba(0,92,62,.08);color:#005C3E;
                                     padding:1px 6px;border-radius:10px;">
                            <?= htmlspecialchars($r['rol']) ?>
                        </span>
                    </div>
                </td>

                <!-- Módulo -->
                <td>
                    <span style="display:inline-flex;align-items:center;gap:5px;
                                 background:<?= $mc['bg'] ?>;color:<?= $mc['txt'] ?>;
                                 padding:4px 10px;border-radius:20px;font-size:.78rem;font-weight:600;">
                        <i class="fas <?= $mc['icon'] ?>" style="font-size:.7rem;"></i>
                        <?= ucfirst(htmlspecialchars($r['modulo'])) ?>
                    </span>
                </td>

                <!-- Acción -->
                <td>
                    <span class="badge-og <?= $ac['cls'] ?>" style="display:inline-flex;align-items:center;gap:5px;">
                        <i class="fas <?= $ac['icon'] ?>" style="font-size:.7rem;"></i>
                        <?= ucfirst(htmlspecialchars($r['accion'])) ?>
                    </span>
                </td>

                <!-- Descripción -->
                <td style="font-size:.86rem;color:#374151;max-width:300px;">
                    <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:280px;"
                         title="<?= htmlspecialchars($r['descripcion']) ?>">
                        <?= htmlspecialchars($r['descripcion']) ?>
                    </div>
                </td>

                <!-- IP -->
                <td style="font-size:.82rem;font-family:monospace;color:#374151;">
                    <?= htmlspecialchars($r['ip']) ?>
                </td>

                <!-- Detalles -->
                <td style="text-align:center;">
                    <button type="button" class="btn btn-sm btn-ver-detalle"
                            style="background:rgba(0,92,62,.08);color:#005C3E;border-radius:6px;padding:4px 8px;"
                            data-id="<?= $r['id_auditoria'] ?>"
                            data-fecha="<?= htmlspecialchars($r['fecha']) ?>"
                            data-usuario="<?= htmlspecialchars($r['nombre_completo'] . ' (@' . $r['usuario'] . ')') ?>"
                            data-rol="<?= htmlspecialchars($r['rol']) ?>"
                            data-modulo="<?= htmlspecialchars(ucfirst($r['modulo'])) ?>"
                            data-accion="<?= htmlspecialchars(ucfirst($r['accion'])) ?>"
                            data-desc="<?= htmlspecialchars($r['descripcion'], ENT_QUOTES) ?>"
                            data-ip="<?= htmlspecialchars($r['ip']) ?>"
                            data-ua="<?= htmlspecialchars($r['user_agent'], ENT_QUOTES) ?>"
                            title="Ver detalle completo">
                        <i class="fas fa-search"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- ── Paginación ──── -->
        <?php if ($data['pages'] > 1): ?>
        <div style="padding:12px 20px;border-top:1px solid rgba(0,92,62,.08);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <small style="color:#6b7280;">
                Página <?= $page ?> de <?= $data['pages'] ?>
                &mdash; <?= number_format($data['total']) ?> registros totales
            </small>
            <div style="display:flex;gap:5px;flex-wrap:wrap;">
                <?php
                // Mostrar máx 10 páginas alrededor de la actual
                $rango  = 4;
                $pinicio = max(1, $page - $rango);
                $pfin    = min($data['pages'], $page + $rango);

                if ($pinicio > 1):
                ?>
                <a href="<?= $baseUrl . 1 ?>" style="<?= paginaStyle(false) ?>">1</a>
                <?php if ($pinicio > 2): ?><span style="padding:5px 4px;color:#6b7280;">…</span><?php endif; ?>
                <?php endif; ?>

                <?php for ($p = $pinicio; $p <= $pfin; $p++): ?>
                <a href="<?= $baseUrl . $p ?>" style="<?= paginaStyle($p === $page) ?>">
                    <?= $p ?>
                </a>
                <?php endfor; ?>

                <?php if ($pfin < $data['pages']):
                    if ($pfin < $data['pages'] - 1): ?><span style="padding:5px 4px;color:#6b7280;">…</span><?php endif; ?>
                <a href="<?= $baseUrl . $data['pages'] ?>" style="<?= paginaStyle(false) ?>"><?= $data['pages'] ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
function paginaStyle(bool $activo): string {
    return 'padding:5px 12px;border-radius:6px;font-size:.83rem;text-decoration:none;' .
           'font-weight:' . ($activo ? '700' : '400') . ';' .
           'background:' . ($activo ? '#005C3E' : '#f0f4f2') . ';' .
           'color:' . ($activo ? '#fff' : '#374151') . ';';
}
?>

<!-- ── Modal detalle (SweetAlert2) ──── -->
<script>
document.querySelectorAll('.btn-ver-detalle').forEach(btn => {
    btn.addEventListener('click', function () {
        const d = this.dataset;
        Swal.fire({
            title: `<span style="font-size:1rem;font-weight:700;color:#005C3E;">
                        <i class="fas fa-history me-2"></i>Detalle del registro #${d.id}
                    </span>`,
            html: `
                <table style="width:100%;text-align:left;border-collapse:collapse;font-size:.88rem;">
                    ${fila('Fecha y hora', d.fecha)}
                    ${fila('Usuario', d.usuario)}
                    ${fila('Rol', d.rol)}
                    ${fila('Módulo', d.modulo)}
                    ${fila('Acción', d.accion)}
                    ${fila('Descripción', d.desc, true)}
                    ${fila('Dirección IP', `<code style="background:#f0f4f2;padding:2px 6px;border-radius:4px;">${d.ip}</code>`)}
                    ${fila('Navegador / Cliente', `<span style="word-break:break-all;font-size:.78rem;color:#6b7280;">${d.ua}</span>`)}
                </table>`,
            confirmButtonColor: '#005C3E',
            confirmButtonText: 'Cerrar',
            width: '640px',
        });
    });
});

function fila(label, valor, multilinea = false) {
    return `<tr style="border-bottom:1px solid #f0f4f2;">
        <td style="padding:8px 10px 8px 0;font-weight:600;color:#374151;white-space:nowrap;width:140px;">${label}</td>
        <td style="padding:8px 0;color:#1a2e25;${multilinea?'white-space:pre-wrap;':''}>${valor ?? '—'}</td>
    </tr>`;
}
</script>
