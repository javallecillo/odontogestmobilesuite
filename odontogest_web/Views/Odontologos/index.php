<?php $csrf = Csrf::token(); ?>
<div><div style="padding:24px 28px;">

<!-- KPI -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
<?php
$activos   = OdontologosModel::total(['buscar'=>'','estado'=>'activo']);
$inactivos = OdontologosModel::total(['buscar'=>'','estado'=>'inactivo']);
foreach([
    ['label'=>'Total Odontólogos','val'=>$total,    'icon'=>'fa-user-doctor','color'=>'blue'],
    ['label'=>'Activos',          'val'=>$activos,  'icon'=>'fa-circle-check','color'=>'green'],
    ['label'=>'Inactivos',        'val'=>$inactivos,'icon'=>'fa-circle-xmark','color'=>'red'],
] as $k): ?>
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
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div style="flex:3;min-width:200px;"><label class="form-label">Buscar</label><input type="text" name="buscar" class="form-control" placeholder="Nombre, licencia, correo…" value="<?= htmlspecialchars($filtros['buscar']) ?>"></div>
        <div style="flex:1;min-width:130px;"><label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="">Todos</option>
                <?php foreach(['activo','inactivo','vacaciones'] as $e): ?>
                <option value="<?= $e ?>" <?= $filtros['estado']===$e?'selected':'' ?>><?= ucfirst($e) ?></option>
                <?php endforeach; ?>
            </select></div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-og-primary"><i class="fas fa-search me-1"></i>Filtrar</button>
            <a href="<?= APP_URL ?>odontologos" class="btn-og-secondary">Limpiar</a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;color:var(--body-text);">Odontólogos <span style="font-size:12px;color:#9CA3AF;">(<?= $total ?>)</span></span>
        <button class="btn-og-primary" onclick="abrirModalOd()"><i class="fas fa-plus me-1"></i>Nuevo Odontólogo</button>
    </div>
    <?php if(isset($_GET['ok'])): ?>
    <div style="padding:10px 20px;background:#DCFCE7;color:#166534;font-size:13px;border-bottom:1px solid #BBF7D0;">
        <i class="fas fa-check me-1"></i>
        <?= $_GET['ok']==='creado'?'Odontólogo registrado correctamente.':'Datos actualizados correctamente.' ?>
    </div>
    <?php endif; ?>
    <div style="overflow-x:auto;">
    <table class="tabla-og">
        <thead><tr><th>Nombre</th><th>Licencia</th><th>Especialidad</th><th>Cargo</th><th>Teléfono</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if(empty($odontologos)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-user-doctor fa-2x d-block mb-2" style="opacity:.3;"></i>Sin odontólogos registrados</td></tr>
        <?php else: foreach($odontologos as $o): ?>
        <tr>
            <td>
                <div style="font-weight:600;color:var(--body-text);"><?= htmlspecialchars($o['nombre'].' '.$o['apellidos']) ?></div>
                <div style="font-size:11px;color:#9CA3AF;"><?= htmlspecialchars($o['correo']??'') ?></div>
            </td>
            <td style="font-family:monospace;"><?= htmlspecialchars($o['numero_licencia']) ?></td>
            <td><?= htmlspecialchars($o['especialidad']??'—') ?></td>
            <td><?= htmlspecialchars($o['cargo']??'—') ?></td>
            <td><?= htmlspecialchars($o['telefono']??'—') ?></td>
            <td><span class="badge badge-<?= $o['estado'] ?>"><?= ucfirst($o['estado']) ?></span></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <button class="btn-og-icon" title="Editar" onclick="editarOd(<?= htmlspecialchars(json_encode($o)) ?>)"><i class="fas fa-edit"></i></button>
                    <button class="btn-og-icon <?= $o['estado']==='activo'?'btn-danger-icon':'' ?>" title="<?= $o['estado']==='activo'?'Desactivar':'Activar' ?>" onclick="toggleOd(<?= $o['id_odontologo'] ?>, '<?= $o['estado'] ?>')"><i class="fas fa-<?= $o['estado']==='activo'?'ban':'circle-check' ?>"></i></button>
                </div>
            </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
    <?php if($totalPags>1): ?>
    <div style="padding:12px 20px;display:flex;gap:6px;justify-content:center;border-top:1px solid var(--card-border);">
        <?php for($i=1;$i<=$totalPags;$i++): ?>
        <a href="?<?= http_build_query(array_merge($filtros,['pagina'=>$i])) ?>" style="width:32px;height:32px;display:flex;align-items:center;justify-content:center;border-radius:6px;font-size:13px;text-decoration:none;<?= $i===$filtros['pagina']?'background:#1A56AB;color:#fff;':'background:#F5F7FB;color:#374151;border:1px solid #DDE4EF;' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
</div></div>

<!-- ── Modal Odontólogo ─────────────────────────────────────────── -->
<div id="modalOd" style="display:none;position:fixed;inset:0;z-index:1050;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="cerrarModalOd()"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:16px;padding:28px 32px;width:min(640px,95vw);max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 id="odTitulo" style="margin:0;font-size:16px;font-weight:700;color:var(--body-text);">Nuevo Odontólogo</h3>
            <button onclick="cerrarModalOd()" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:18px;"><i class="fas fa-times"></i></button>
        </div>
        <form id="formOd" method="POST" action="<?= APP_URL ?>odontologos/crear">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="id_odontologo" id="od_id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div><label class="form-label">Nombre *</label><input type="text" name="nombre" id="od_nom" class="form-control" required></div>
                <div><label class="form-label">Apellidos *</label><input type="text" name="apellidos" id="od_ape" class="form-control" required></div>
                <div><label class="form-label">N° Licencia *</label><input type="text" name="numero_licencia" id="od_lic" class="form-control" required></div>
                <div><label class="form-label">DNI / Pasaporte</label><input type="text" name="dni" id="od_dni" class="form-control"></div>
                <div><label class="form-label">Especialidad *</label>
                    <select name="id_especialidad" id="od_esp" class="form-select" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach($especialidades as $e): ?>
                        <option value="<?= $e['id_especialidad'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div><label class="form-label">Cargo *</label>
                    <select name="id_cargo" id="od_car" class="form-select" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach($cargos as $c): ?>
                        <option value="<?= $c['id_cargo'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div id="od_usuario_wrap"><label class="form-label">Usuario del sistema</label>
                    <select name="id_usuario" id="od_usr" class="form-select">
                        <option value="">— Sin usuario —</option>
                        <?php foreach($usuarios as $u): ?>
                        <option value="<?= $u['id_usuario'] ?>"><?= htmlspecialchars($u['nombre_completo'].' ('.$u['usuario'].')') ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div><label class="form-label">Teléfono</label><input type="text" name="telefono" id="od_tel" class="form-control"></div>
                <div style="grid-column:span 2;"><label class="form-label">Correo</label><input type="email" name="correo" id="od_cor" class="form-control"></div>
                <div><label class="form-label">Fecha Nacimiento</label><input type="date" name="fecha_nacimiento" id="od_fnac" class="form-control"></div>
                <div><label class="form-label">Estado</label>
                    <select name="estado" id="od_est" class="form-select">
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                        <option value="vacaciones">Vacaciones</option>
                    </select></div>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
                <button type="button" class="btn-og-secondary" onclick="cerrarModalOd()">Cancelar</button>
                <button type="submit" class="btn-og-primary"><i class="fas fa-save me-1"></i>Guardar</button>
            </div>
        </form>
    </div>
</div>

<style>
.btn-og-icon{width:30px;height:30px;border-radius:6px;border:1px solid #DDE4EF;background:#F5F7FB;color:#374151;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:.15s;}
.btn-og-icon:hover{background:#1A56AB;border-color:#1A56AB;color:#fff;}
.btn-danger-icon:hover{background:#DC2626;border-color:#DC2626;color:#fff;}
[data-theme="dark"] .btn-og-icon{background:#253349;border-color:#334155;color:#CBD5E1;}
</style>

<script>
function abrirModalOd(){
    document.getElementById('odTitulo').textContent='Nuevo Odontólogo';
    document.getElementById('formOd').action='<?= APP_URL ?>odontologos/crear';
    document.getElementById('formOd').reset();
    document.getElementById('od_id').value='';
    document.getElementById('od_usuario_wrap').style.display='';
    document.getElementById('modalOd').style.display='flex';
}
function cerrarModalOd(){ document.getElementById('modalOd').style.display='none'; }
function editarOd(o){
    document.getElementById('odTitulo').textContent='Editar Odontólogo';
    document.getElementById('formOd').action='<?= APP_URL ?>odontologos/actualizar';
    document.getElementById('od_id').value=o.id_odontologo;
    document.getElementById('od_nom').value=o.nombre;
    document.getElementById('od_ape').value=o.apellidos;
    document.getElementById('od_lic').value=o.numero_licencia;
    document.getElementById('od_dni').value=o.dni||'';
    document.getElementById('od_esp').value=o.id_especialidad;
    document.getElementById('od_car').value=o.id_cargo;
    document.getElementById('od_tel').value=o.telefono||'';
    document.getElementById('od_cor').value=o.correo||'';
    document.getElementById('od_fnac').value=o.fecha_nacimiento||'';
    document.getElementById('od_est').value=o.estado;
    document.getElementById('od_usuario_wrap').style.display='none'; // no cambiar usuario al editar
    document.getElementById('modalOd').style.display='flex';
}
function toggleOd(id, estado){
    const txt=estado==='activo'?'¿Desactivar este odontólogo?':'¿Activar este odontólogo?';
    Swal.fire({title:txt,icon:'warning',showCancelButton:true,confirmButtonColor:estado==='activo'?'#DC2626':'#16A34A',confirmButtonText:'Confirmar',cancelButtonText:'Cancelar'})
    .then(r=>{
        if(!r.isConfirmed)return;
        const fd=new FormData();
        fd.append('csrf_token','<?= $csrf ?>');
        fd.append('id_odontologo',id);
        fetch('<?= APP_URL ?>odontologos/toggleEstado',{method:'POST',body:fd}).then(()=>location.reload());
    });
}
</script>
