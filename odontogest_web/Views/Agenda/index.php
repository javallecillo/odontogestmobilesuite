<?php
// $kpis, $odontologos, $pacientes, $servicios ya vienen del Controller
$estados = ['pendiente','confirmada','en_curso','atendida','cancelada','no_asistio'];
$csrf    = Csrf::token();
?>
<div>
<div style="padding:24px 28px;">

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
                <button class="btn-og-icon" title="Cambiar estado" onclick="abrirModalEstado(<?= $c['id_cita'] ?>,'<?= $c['estado'] ?>')"><i class="fas fa-exchange-alt"></i></button>
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
        <div style="padding:18px 22px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
            <h5 style="margin:0;font-size:15px;font-weight:700;color:var(--body-text);"><i class="fas fa-calendar-plus me-2" style="color:#1A56AB;"></i>Nueva Cita</h5>
            <button onclick="document.getElementById('modalCita').style.display='none'" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:16px;"><i class="fas fa-times"></i></button>
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

<div id="modalEstadoCita" style="display:none;position:fixed;inset:0;z-index:1070;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);" onclick="cerrarModalEstado()"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:18px;box-shadow:0 24px 70px rgba(0,0,0,.28);width:100%;max-width:520px;margin:16px;overflow:hidden;">
        <div style="padding:18px 22px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;gap:12px;">
            <div>
                <div style="font-size:12px;color:#6B7280;font-weight:600;letter-spacing:.02em;">Actualizar cita</div>
                <h5 style="margin:4px 0 0;font-size:17px;font-weight:800;color:var(--body-text);">Cambiar estado</h5>
            </div>
            <button type="button" onclick="cerrarModalEstado()" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:18px;width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div style="padding:20px 22px 8px;">
            <p style="margin:0 0 14px;color:#6B7280;font-size:13px;line-height:1.5;">Selecciona el nuevo estado de la cita. El cambio se aplicará al confirmar.</p>
            <input type="hidden" id="estadoCitaId">
            <input type="hidden" id="estadoCitaActual">
            <input type="hidden" id="estadoCitaSeleccionado">
            <div id="estadoCitaOpciones" style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;"></div>
        </div>
        <div style="padding:14px 22px 22px;border-top:1px solid var(--card-border);display:flex;justify-content:center;gap:10px;flex-wrap:wrap;">
            <button type="button" class="btn-og-secondary" style="min-width:120px;" onclick="cerrarModalEstado()">Cancelar</button>
            <button type="button" class="btn-og-primary" style="min-width:120px;" onclick="guardarEstadoCita()">Guardar cambio</button>
        </div>
    </div>
</div>

<div id="modalEliminarCita" style="display:none;position:fixed;inset:0;z-index:1075;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);" onclick="cerrarModalEliminar()"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:18px;box-shadow:0 24px 70px rgba(0,0,0,.28);width:100%;max-width:460px;margin:16px;overflow:hidden;">
        <div style="padding:20px 22px 10px;display:flex;align-items:flex-start;gap:14px;">
            <div style="width:50px;height:50px;border-radius:16px;background:rgba(220,38,38,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-trash-alt" style="color:#DC2626;font-size:20px;"></i>
            </div>
            <div style="flex:1;">
                <div style="font-size:12px;color:#6B7280;font-weight:600;letter-spacing:.02em;">Eliminar cita</div>
                <h5 style="margin:4px 0 0;font-size:18px;font-weight:800;color:var(--body-text);">¿Deseas eliminar esta cita?</h5>
                <p style="margin:10px 0 0;color:#6B7280;font-size:13px;line-height:1.5;">Esta acción es permanente y la cita desaparecerá del calendario.</p>
            </div>
        </div>
        <div style="padding:16px 22px 22px;border-top:1px solid var(--card-border);display:flex;justify-content:center;gap:10px;flex-wrap:wrap;">
            <button type="button" class="btn-og-secondary" style="min-width:120px;" onclick="cerrarModalEliminar()">Cancelar</button>
            <button type="button" class="btn-og-danger" style="min-width:120px;" onclick="confirmarEliminarCita()">Eliminar cita</button>
        </div>
    </div>
</div>

<script>
const AGENDA_BASE = '<?= APP_URL ?>agenda';
const AGENDA_CSRF = '<?= $csrf ?>';
const AGENDA_ESTADOS = {
    pendiente: 'Pendiente',
    confirmada: 'Confirmada',
    en_curso: 'En curso',
    atendida: 'Atendida',
    cancelada: 'Cancelada',
    no_asistio: 'No asistió',
};

const AGENDA_ESTADO_TONE = {
    pendiente: { label: 'Pendiente', icon: 'fa-clock', className: 'state-pending' },
    confirmada: { label: 'Confirmada', icon: 'fa-check-circle', className: 'state-confirmada' },
    en_curso: { label: 'En curso', icon: 'fa-spinner', className: 'state-en-curso' },
    atendida: { label: 'Atendida', icon: 'fa-circle-check', className: 'state-atendida' },
    cancelada: { label: 'Cancelada', icon: 'fa-ban', className: 'state-cancelada' },
    no_asistio: { label: 'No asistió', icon: 'fa-user-slash', className: 'state-no-asistio' },
};

function abrirModalEstado(id, estadoActual) {
    document.getElementById('estadoCitaId').value = id;
    document.getElementById('estadoCitaActual').value = estadoActual;
    document.getElementById('estadoCitaSeleccionado').value = estadoActual;

    const contenedor = document.getElementById('estadoCitaOpciones');
    contenedor.innerHTML = '';

    Object.entries(AGENDA_ESTADOS).forEach(([valor, etiqueta]) => {
        const tone = AGENDA_ESTADO_TONE[valor] || { label: etiqueta, icon: 'fa-circle', className: 'state-default' };
        const activo = valor === estadoActual;
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.estado = valor;
        button.onclick = () => seleccionarEstadoCita(valor);
        button.className = `agenda-state-option ${tone.className} ${activo ? 'is-active' : ''}`;
        button.innerHTML = `
            <i class="fas ${tone.icon}" style="font-size:18px;"></i>
            <span style="font-size:13px;font-weight:700;line-height:1.2;text-align:center;">${tone.label}</span>
        `;
        contenedor.appendChild(button);
    });

    document.getElementById('modalEstadoCita').style.display = 'flex';
}

function cerrarModalEstado() {
    document.getElementById('modalEstadoCita').style.display = 'none';
}

function seleccionarEstadoCita(valor) {
    document.getElementById('estadoCitaSeleccionado').value = valor;

    const botones = document.querySelectorAll('#estadoCitaOpciones button[data-estado]');
    botones.forEach((btn) => {
        const activo = btn.dataset.estado === valor;
        btn.classList.toggle('is-active', activo);
    });
}

async function guardarEstadoCita() {
    const id = document.getElementById('estadoCitaId').value;
    const estado = document.getElementById('estadoCitaSeleccionado').value;

    if (!id || !estado) {
        agendaError('Selecciona un estado válido');
        return;
    }

    const fd = new FormData();
    fd.append('csrf_token', AGENDA_CSRF);
    fd.append('id_cita', id);
    fd.append('estado', estado);

    const response = await fetch(`${AGENDA_BASE}/actualizar`, {
        method: 'POST',
        body: fd,
    });

    if (response.ok) {
        location.reload();
        return;
    }

    agendaError('No se pudo cambiar el estado');
}

function agendaError(message) {
    if (window.Swal && typeof Swal.fire === 'function') {
        Swal.fire({icon: 'error', title: 'Error', text: message});
        return;
    }
    alert(message);
}

async function agendaConfirm({ title, text }) {
    if (window.Swal && typeof Swal.fire === 'function') {
        return Swal.fire({
            title,
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#6B7280',
        });
    }

    return { isConfirmed: window.confirm(`${title}\n\n${text}`) };
}

let citaEliminarPendiente = null;

function abrirModalEliminar(id) {
    citaEliminarPendiente = id;
    document.getElementById('modalEliminarCita').style.display = 'flex';
}

function cerrarModalEliminar() {
    citaEliminarPendiente = null;
    document.getElementById('modalEliminarCita').style.display = 'none';
}

async function confirmarEliminarCita() {
    if (!citaEliminarPendiente) return;

    const fd = new FormData();
    fd.append('csrf_token', AGENDA_CSRF);
    fd.append('id_cita', citaEliminarPendiente);

    const response = await fetch(`${AGENDA_BASE}/eliminar`, {
        method: 'POST',
        body: fd,
    });

    if (response.ok) {
        cerrarModalEliminar();
        location.reload();
        return;
    }

    agendaError('No se pudo eliminar la cita');
}

async function eliminarCita(id) {
    abrirModalEliminar(id);
}
</script>

<style>
.agenda-state-option {
    border: 1px solid var(--card-border);
    background: var(--card-bg);
    color: var(--body-text);
    border-radius: 14px;
    padding: 14px 12px;
    min-height: 74px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease, background .15s ease, color .15s ease;
    font: inherit;
}

.agenda-state-option:hover {
    transform: translateY(-1px);
}

.agenda-state-option.is-active {
    box-shadow: 0 8px 22px rgba(26,86,171,.10);
    transform: translateY(-1px);
}

.agenda-state-option.state-pending {
    border-color: #FDBA74;
    color: #B45309;
    background: #FFF7ED;
}

.agenda-state-option.state-confirmada {
    border-color: #93C5FD;
    color: #1D4ED8;
    background: #EFF6FF;
}

.agenda-state-option.state-en-curso {
    border-color: #6EE7B7;
    color: #047857;
    background: #ECFDF5;
}

.agenda-state-option.state-atendida {
    border-color: #86EFAC;
    color: #15803D;
    background: #F0FDF4;
}

.agenda-state-option.state-cancelada {
    border-color: #FCA5A5;
    color: #B91C1C;
    background: #FEF2F2;
}

.agenda-state-option.state-no-asistio {
    border-color: #CBD5E1;
    color: #475569;
    background: #F8FAFC;
}

[data-theme="dark"] .agenda-state-option {
    background: #162032;
    color: #E2E8F0;
    border-color: #334155;
}

[data-theme="dark"] .agenda-state-option.is-active {
    box-shadow: 0 10px 28px rgba(15,23,42,.35);
}

[data-theme="dark"] .agenda-state-option.state-pending {
    border-color: rgba(251,191,36,.55);
    color: #FBBF24;
    background: rgba(251,191,36,.10);
}

[data-theme="dark"] .agenda-state-option.state-confirmada {
    border-color: rgba(96,165,250,.55);
    color: #60A5FA;
    background: rgba(59,130,246,.12);
}

[data-theme="dark"] .agenda-state-option.state-en-curso {
    border-color: rgba(52,211,153,.55);
    color: #34D399;
    background: rgba(16,185,129,.12);
}

[data-theme="dark"] .agenda-state-option.state-atendida {
    border-color: rgba(74,222,128,.55);
    color: #4ADE80;
    background: rgba(34,197,94,.12);
}

[data-theme="dark"] .agenda-state-option.state-cancelada {
    border-color: rgba(248,113,113,.55);
    color: #F87171;
    background: rgba(239,68,68,.12);
}

[data-theme="dark"] .agenda-state-option.state-no-asistio {
    border-color: rgba(148,163,184,.55);
    color: #CBD5E1;
    background: rgba(51,65,85,.38);
}

.btn-og-danger {
    min-height: 44px;
    border: 1px solid #DC2626;
    background: #DC2626;
    color: #fff;
    border-radius: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: .15s ease;
}

.btn-og-danger:hover {
    background: #B91C1C;
    border-color: #B91C1C;
}

[data-theme="dark"] .btn-og-danger {
    background: #EF4444;
    border-color: #EF4444;
}

[data-theme="dark"] .btn-og-danger:hover {
    background: #DC2626;
    border-color: #DC2626;
}

.btn-og-icon{width:30px;height:30px;border-radius:6px;border:1px solid #DDE4EF;background:#F5F7FB;color:#374151;cursor:pointer;display:inline-flex;a