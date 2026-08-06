<?php
// $kpis, $odontologos, $pacientes, $servicios ya vienen del Controller
$estados = ['pendiente','confirmada','en_curso','atendida','cancelada','no_asistio'];
$csrf    = Csrf::token();
?>
<div>
<div style="padding:24px 28px;">

<?php if (!empty($_GET['ok'])): ?>
<div style="background:#D1FAE5;color:#065F46;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.875rem;">
    <i class="fas fa-check-circle me-2"></i>Cita registrada correctamente.
</div>
<?php elseif (!empty($_GET['error'])): ?>
<div style="background:#FEE2E2;color:#991B1B;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:.875rem;word-break:break-all;">
    <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
</div>
<?php endif; ?>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
<?php
$kpiItems=[
    ['label'=>'Citas Hoy',  'val'=>$kpis['total_hoy'] ??0,'icon'=>'fa-calendar-day','color'=>'blue'],
    ['label'=>'Pendientes', 'val'=>$kpis['pendientes']??0,'icon'=>'fa-clock',        'color'=>'amber'],
    ['label'=>'Atendidas',  'val'=>$kpis['atendidas'] ??0,'icon'=>'fa-check-circle', 'color'=>'green'],
    ['label'=>'Canceladas', 'val'=>$kpis['canceladas']??0,'icon'=>'fa-times-circle', 'color'=>'red'],
];
foreach($kpiItems as $k): ?>
<div class="kpi-card">
    <div style="display:flex;align-items:center;gap:14px;">
        <div class="kpi-icon <?= $k['color'] ?>"><i class="fas <?= $k['icon'] ?>"></i></div>
        <div><div class="kpi-value"><?= $k['val'] ?></div><div class="kpi-label"><?= $k['label'] ?></div></div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Filtros -->
<div class="kpi-card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
        <div style="flex:1;min-width:160px;"><label class="form-label">Fecha</label>
            <input type="date" name="fecha" class="form-control" value="<?= htmlspecialchars($filtros['fecha']) ?>"></div>
        <div style="flex:1;min-width:140px;"><label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="">Todos</option>
                <?php foreach($estados as $e): ?>
                <option value="<?= $e ?>" <?= $filtros['estado']===$e?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$e)) ?></option>
                <?php endforeach; ?>
            </select></div>
        <div style="flex:2;min-width:200px;"><label class="form-label">Buscar</label>
            <input type="text" name="buscar" class="form-control" placeholder="Paciente / odontólogo..." value="<?= htmlspecialchars($filtros['buscar']) ?>"></div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-og-primary"><i class="fas fa-search me-1"></i>Filtrar</button>
            <a href="<?= APP_URL ?>agenda" class="btn-og-secondary">Limpiar</a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;color:var(--body-text);">Citas — <?= date('d/m/Y',strtotime($filtros['fecha'])) ?></span>
        <button class="btn-og-primary" onclick="document.getElementById('modalCita').style.display='flex'"><i class="fas fa-plus me-1"></i>Nueva Cita</button>
    </div>
    <div style="overflow-x:auto;">
    <table class="tabla-og">
        <thead><tr><th>#</th><th>Hora</th><th>Paciente</th><th>Odontólogo</th><th>Servicio</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if(empty($citas)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-calendar-times fa-2x d-block mb-2" style="opacity:.3;"></i>Sin citas para este filtro</td></tr>
        <?php else: foreach($citas as $c): ?>
        <tr>
            <td style="font-weight:600;color:#1A56AB;">#<?= $c['id_cita'] ?></td>
            <td style="font-weight:600;"><?= substr($c['hora'],0,5) ?></td>
            <td>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="width:32px;height:32px;border-radius:50%;background:#1A3057;color:#B2DAFF;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;"><?= strtoupper(substr($c['paciente'],0,1)) ?></div>
                    <?= htmlspecialchars($c['paciente']) ?>
                </div>
            </td>
            <td><?= htmlspecialchars($c['odontologo']) ?></td>
            <td><?= htmlspecialchars($c['servicio']??'—') ?></td>
            <td><span class="badge badge-<?= $c['estado'] ?>"><?= ucfirst(str_replace('_',' ',$c['estado'])) ?></span></td>
            <td style="display:flex;gap:6px;">
                <button class="btn-og-icon" title="Cambiar estado" onclick="cambiarEstado(<?= $c['id_cita'] ?>)"><i class="fas fa-exchange-alt"></i></button>
                <button class="btn-og-icon btn-danger-icon" title="Eliminar" onclick="eliminarCita(<?= $c['id_cita'] ?>)"><i class="fas fa-trash"></i></button>
            </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
    <?php if($totalPags>1): ?>
    <div style="padding:12px 20px;display:flex;gap:6px;justify-content:center;border-top:1px solid var(--card-border);">
        <?php for($i=1;$i<=$totalPags;$i++): $act=$i===$filtros['pagina']; ?>
        <a href="?<?= http_build_query(array_merge($filtros,['pagina'=>$i])) ?>" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;font-size:13px;text-decoration:none;<?= $act?'background:#1A56AB;color:#fff;':'background:#F5F7FB;color:#374151;border:1px solid #DDE4EF;' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
</div></div>

<!-- Modal Nueva Cita -->
<div id="modalCita" style="display:none;position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="document.getElementById('modalCita').style.display='none'"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:520px;margin:16px;">
        <div class="og-modal-header">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="og-modal-icon"><i class="fas fa-calendar-plus"></i></div>
                <h5 style="margin:0;">Nueva Cita</h5>
            </div>
            <button class="og-modal-close" onclick="document.getElementById('modalCita').style.display='none'"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="<?= APP_URL ?>agenda/crear">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div style="padding:20px 22px;">
                <div class="mb-3">
                    <label class="form-label">Paciente *</label>
                    <select name="id_paciente" class="form-select" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach($pacientes as $pa): ?>
                        <option value="<?= $pa['id_paciente'] ?>"><?= htmlspecialchars($pa['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Odontólogo *</label>
                    <select name="id_odontologo" class="form-select" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach($odontologos as $od): ?>
                        <option value="<?= $od['id_odontologo'] ?>"><?= htmlspecialchars($od['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Servicio</label>
                    <select name="id_servicio" class="form-select">
                        <option value="">— Sin servicio —</option>
                        <?php foreach($servicios as $sv): ?>
                        <option value="<?= $sv['id_servicio'] ?>"><?= htmlspecialchars($sv['nombre']) ?> (L. <?= number_format($sv['precio_base'],2) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label">Fecha y Hora *</label><input type="datetime-local" name="fecha_cita" class="form-control" required></div>
                <div class="mb-0"><label class="form-label">Notas</label><textarea name="notas" class="form-control" rows="2"></textarea></div>
            </div>
            <div style="padding:14px 22px;border-top:1px solid var(--card-border);display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-og-secondary" onclick="document.getElementById('modalCita').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-og-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<style>
.btn-og-icon{width:30px;height:30px;border-radius:6px;border:1px solid #DDE4EF;background:#F5F7FB;color:#374151;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:background .15s,color .15s;}
.btn-og-icon:hover{background:#EEF2FF;color:#1A56AB;border-color:#BFCFEF;}
.btn-danger-icon{border-color:#FCA5A5;background:#FEF2F2;color:#dc2626;}
.btn-danger-icon:hover{background:#FEE2E2;border-color:#F87171;}
.badge{padding:3px 10px;border-radius:20px;font-size:.72rem;font-weight:600;letter-spacing:.02em;}
.badge-pendiente  {background:#FEF3C7;color:#92400E;}
.badge-confirmada {background:#D1FAE5;color:#065F46;}
.badge-en_curso   {background:#DBEAFE;color:#1E40AF;}
.badge-atendida   {background:#D1FAE5;color:#065F46;}
.badge-cancelada  {background:#FEE2E2;color:#991B1B;}
.badge-no_asistio {background:#F3F4F6;color:#6B7280;}
</style>

<script>
// ── Cambiar estado de cita ────────────────────────────────────
function cambiarEstado(id) {
    const opts  = ['pendiente','confirmada','en_curso','atendida','cancelada','no_asistio'];
    const etiq  = ['Pendiente','Confirmada','En curso','Atendida','Cancelada','No asistió'];
    const icons = [
        'fas fa-clock text-warning',
        'fas fa-calendar-check text-success',
        'fas fa-spinner text-primary',
        'fas fa-check-circle text-success',
        'fas fa-ban text-danger',
        'fas fa-user-times text-muted',
    ];

    Swal.fire({
        title: 'Cambiar estado de cita',
        html: opts.map((o, i) =>
            `<button type="button" class="og-estado-btn" data-v="${o}"
              style="display:flex;align-items:center;gap:10px;width:100%;padding:10px 14px;
                     margin-bottom:6px;border-radius:8px;border:1px solid #DDE4EF;
                     background:var(--card-bg);cursor:pointer;font-size:14px;font-weight:500;
                     color:var(--body-text);">
              <span style="width:16px;text-align:center;"><i class="${icons[i]}"></i></span>${etiq[i]}
            </button>`
        ).join(''),
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        background: 'var(--card-bg)',
        color: 'var(--body-text)',
        customClass: { popup:'og-swal-popup', cancelButton:'og-swal-cancel' },
        didOpen: () => {
            document.querySelectorAll('.og-estado-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    Swal.close();
                    const fd = new FormData();
                    fd.append('csrf_token', '<?= $csrf ?>');
                    fd.append('id_cita',    id);
                    fd.append('estado',     btn.dataset.v);
                    fetch('<?= APP_URL ?>agenda/actualizar', { method:'POST', body: fd })
                        .then(() => location.reload());
                });
            });
        },
    });
}

// ── Eliminar cita ─────────────────────────────────────────────
async function eliminarCita(id) {
    const ok = await OgSwal.danger({
        title: '¿Eliminar cita?',
        text: 'Esta acción no se puede deshacer.',
        confirmText: 'Eliminar',
    });
    if (!ok) return;
    const fd = new FormData();
    fd.append('csrf_token', '<?= $csrf ?>');
    fd.append('id_cita',    id);
    fetch('<?= APP_URL ?>agenda/eliminar', { method:'POST', body: fd })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); else OgSwal.error('Error al eliminar la cita'); })
        .catch(() => OgSwal.error('Error al eliminar la cita'));
}
</script>
