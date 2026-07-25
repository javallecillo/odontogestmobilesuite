<?php
$csrf = Csrf::token();
$iconModulo = [
    'agenda'        => 'fa-calendar-alt',
    'expedientes'   => 'fa-folder-open',
    'facturacion'   => 'fa-file-invoice-dollar',
    'inventario'    => 'fa-boxes-stacked',
    'configuracion' => 'fa-cogs',
    'seguridad'     => 'fa-shield-halved',
    'reportes'      => 'fa-chart-bar',
    'sistema'       => 'fa-server',
];
$colorModulo = [
    'agenda'        => '#1A56AB',
    'expedientes'   => '#7C3AED',
    'facturacion'   => '#059669',
    'inventario'    => '#D97706',
    'configuracion' => '#0891B2',
    'seguridad'     => '#DC2626',
    'reportes'      => '#9333EA',
    'sistema'       => '#6B7280',
];
$totalPermisos   = count($permisos);
$totalAsignados  = count(array_filter($permisos, fn($p) => $p['asignado']));
$pct = $totalPermisos > 0 ? round($totalAsignados / $totalPermisos * 100) : 0;
?>
<div><div style="padding:24px 28px;">

<!-- Header -->
<div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
    <a href="<?= APP_URL ?>roles" class="btn-og-secondary"><i class="fas fa-arrow-left me-1"></i>Roles</a>
    <div>
        <h4 style="margin:0;font-size:18px;font-weight:700;color:var(--body-text);">
            <i class="fas fa-key me-2" style="color:#1A56AB;"></i>
            Permisos — <?= htmlspecialchars($rol['nombre']) ?>
        </h4>
        <?php if(!empty($rol['descripcion'])): ?>
        <div style="font-size:13px;color:#9CA3AF;margin-top:2px;"><?= htmlspecialchars($rol['descripcion']) ?></div>
        <?php endif; ?>
    </div>
    <div style="margin-left:auto;text-align:right;">
        <div style="font-size:13px;color:#9CA3AF;">Permisos asignados</div>
        <div style="font-size:20px;font-weight:700;color:var(--body-text);"><?= $totalAsignados ?> / <?= $totalPermisos ?></div>
    </div>
</div>

<!-- Barra progreso -->
<div class="kpi-card" style="margin-bottom:20px;padding:14px 20px;">
    <div style="display:flex;justify-content:space-between;font-size:13px;color:#6B7280;margin-bottom:8px;">
        <span>Cobertura de permisos</span><span style="font-weight:600;color:var(--body-text);"><?= $pct ?>%</span>
    </div>
    <div style="height:8px;border-radius:4px;background:var(--card-border);">
        <div style="height:100%;border-radius:4px;width:<?= $pct ?>%;background:<?= $pct>=80?'#16A34A':($pct>=50?'#F59E0B':'#DC2626') ?>;transition:width .4s;"></div>
    </div>
</div>

<!-- Formulario de permisos -->
<form method="POST" action="<?= APP_URL ?>roles/actualizar" id="formPermisos">
    <input type="hidden" name="csrf_token"   value="<?= $csrf ?>">
    <input type="hidden" name="id_rol"       value="<?= $rol['id_rol'] ?>">
    <input type="hidden" name="nombre"       value="<?= htmlspecialchars($rol['nombre']) ?>">
    <input type="hidden" name="descripcion"  value="<?= htmlspecialchars($rol['descripcion']??'') ?>">

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;margin-bottom:20px;">
    <?php foreach($porModulo as $modulo => $perms): ?>
    <?php
        $icon  = $iconModulo[$modulo]  ?? 'fa-circle';
        $color = $colorModulo[$modulo] ?? '#6B7280';
        $asignados = count(array_filter($perms, fn($p) => $p['asignado']));
        $total     = count($perms);
    ?>
    <div class="kpi-card" style="padding:0;overflow:hidden;">
        <!-- Cabecera módulo -->
        <div style="padding:12px 16px;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--card-border);">
            <div style="width:32px;height:32px;border-radius:8px;background:<?= $color ?>1a;display:flex;align-items:center;justify-content:center;">
                <i class="fas <?= $icon ?>" style="color:<?= $color ?>;font-size:14px;"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:13px;color:var(--body-text);text-transform:capitalize;"><?= $modulo ?></div>
                <div style="font-size:11px;color:#9CA3AF;"><?= $asignados ?>/<?= $total ?> permisos</div>
            </div>
            <!-- Toggle todos del módulo -->
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:11px;color:#9CA3AF;" title="Seleccionar todos">
                <input type="checkbox" class="mod-toggle" data-mod="<?= $modulo ?>"
                    <?= $asignados === $total ? 'checked' : '' ?>
                    style="width:14px;height:14px;accent-color:<?= $color ?>;cursor:pointer;">
                Todos
            </label>
        </div>
        <!-- Lista de permisos -->
        <div style="padding:12px 16px;">
        <?php foreach($perms as $p): ?>
        <label style="display:flex;align-items:center;gap:10px;padding:7px 0;cursor:pointer;border-bottom:1px solid var(--card-border);<?= $p === end($perms)?'border:none':''; ?>">
            <input type="checkbox" name="permisos[]"
                   value="<?= $p['id_permiso'] ?>"
                   class="perm-check mod-<?= $modulo ?>"
                   <?= $p['asignado'] ? 'checked' : '' ?>
                   style="width:15px;height:15px;accent-color:<?= $color ?>;cursor:pointer;flex-shrink:0;">
            <div>
                <div style="font-size:13px;font-weight:500;color:var(--body-text);"><?= htmlspecialchars($p['descripcion'] ?? $p['nombre']) ?></div>
                <div style="font-size:11px;color:#9CA3AF;font-family:monospace;"><?= htmlspecialchars($p['nombre']) ?></div>
            </div>
        </label>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

    <!-- Acciones -->
    <div style="display:flex;gap:10px;justify-content:flex-end;">
        <button type="button" class="btn-og-secondary" onclick="toggleTodos(false)"><i class="fas fa-times me-1"></i>Quitar todos</button>
        <button type="button" class="btn-og-secondary" onclick="toggleTodos(true)"><i class="fas fa-check-double me-1"></i>Seleccionar todos</button>
        <button type="submit" class="btn-og-primary"><i class="fas fa-save me-1"></i>Guardar Permisos</button>
    </div>
</form>

<?php if(empty($porModulo)): ?>
<div class="kpi-card" style="text-align:center;padding:40px;">
    <i class="fas fa-lock fa-3x" style="color:#DDE4EF;margin-bottom:14px;display:block;"></i>
    <p style="color:#9CA3AF;margin:0;">No hay permisos definidos en el sistema.<br>
    Ejecuta el seed SQL en <code>BD_OdontoGest/stored_procedures.sql</code> (bloque SEED Permisos).</p>
</div>
<?php endif; ?>

</div></div>

<script>
// Toggle todos los checkboxes de un módulo
document.querySelectorAll('.mod-toggle').forEach(chk => {
    chk.addEventListener('change', function() {
        const mod = this.dataset.mod;
        document.querySelectorAll('.mod-' + mod).forEach(c => c.checked = this.checked);
    });
});

// Actualizar el toggle "Todos" cuando cambia un permiso individual
document.querySelectorAll('.perm-check').forEach(chk => {
    chk.addEventListener('change', function() {
        const classes = [...this.classList];
        const modClass = classes.find(c => c.startsWith('mod-') && c !== 'mod-toggle' && c !== 'perm-check');
        if (!modClass) return;
        const mod  = modClass.replace('mod-', '');
        const all  = document.querySelectorAll('.mod-' + mod + '.perm-check');
        const chks = [...all].filter(c => c.checked);
        const toggle = document.querySelector('.mod-toggle[data-mod="' + mod + '"]');
        if (toggle) toggle.checked = (chks.length === all.length);
    });
});

function toggleTodos(val) {
    document.querySelectorAll('.perm-check').forEach(c => c.checked = val);
    document.querySelectorAll('.mod-toggle').forEach(c => c.checked = val);
}
</script>
