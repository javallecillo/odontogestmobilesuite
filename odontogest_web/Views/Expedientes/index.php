<?php $csrf = Csrf::token(); ?>
<div><div style="padding:24px 28px;">
<div class="kpi-card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;">
        <div style="flex:1;"><label class="form-label">Buscar paciente</label>
            <input type="text" name="buscar" class="form-control" placeholder="Nombre, DNI, teléfono..." value="<?= htmlspecialchars($filtros['buscar']) ?>"></div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-og-primary"><i class="fas fa-search me-1"></i>Buscar</button>
            <a href="<?= APP_URL ?>expedientes" class="btn-og-secondary">Limpiar</a>
        </div>
    </form>
</div>
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--card-border);">
        <span style="font-weight:600;color:var(--body-text);">Expedientes Clínicos <span style="font-size:12px;color:#9CA3AF;">(<?= $total ?> pacientes)</span></span>
    </div>
    <div style="overflow-x:auto;">
    <table class="tabla-og">
        <thead><tr><th>Paciente</th><th>DNI</th><th>Teléfono</th><th>Total Citas</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if(empty($pacientes)): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-folder-open fa-2x d-block mb-2" style="opacity:.3;"></i>Sin resultados</td></tr>
        <?php else: foreach($pacientes as $p): ?>
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1A56AB,#0C1F46);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">
                        <?= strtoupper(substr($p['nombre'],0,1).substr($p['apellidos'],0,1)) ?>
                    </div>
                    <div><div style="font-weight:600;color:var(--body-text);"><?= htmlspecialchars($p['nombre'].' '.$p['apellidos']) ?></div>
                        <div style="font-size:11px;color:#9CA3AF;"><?= $p['sexo']??'' ?></div></div>
                </div>
            </td>
            <td><?= htmlspecialchars($p['dni']??'—') ?></td>
            <td><?= htmlspecialchars($p['telefono']??'—') ?></td>
            <td style="text-align:center;font-weight:600;"><?= $p['total_citas'] ?></td>
            <td><span class="badge badge-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
            <td><a href="<?= APP_URL ?>expedientes/ver?id=<?= $p['id_paciente'] ?>" class="btn-og-primary" style="font-size:12px;padding:5px 12px;"><i class="fas fa-folder-open me-1"></i>Ver Expediente</a></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>
</div></div>
<style>.btn-og-icon{width:30px;height:30px;border-radius:6px;border:1px solid #DDE4EF;background:#F5F7FB;color:#374151;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:.15s;text-decoration:none;}.btn-og-icon:hover{background:#1A56AB;border-color:#1A56AB;color:#fff;}[data-theme="dark"] .btn-og-icon{background:#253349;border-color:#334155;color:#CBD5E1;}</style>
