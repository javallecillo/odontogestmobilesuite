<?php $csrf = Csrf::token(); ?>
<div><div style="padding:24px 28px;">

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
<?php foreach([
    ['label'=>'Total Pacientes','val'=>$kpis['total']??0,     'icon'=>'fa-users',      'color'=>'blue'],
    ['label'=>'Activos',        'val'=>$kpis['activos']??0,   'icon'=>'fa-user-check', 'color'=>'green'],
    ['label'=>'Inactivos',      'val'=>$kpis['inactivos']??0, 'icon'=>'fa-user-slash', 'color'=>'red'],
    ['label'=>'Nuevos este mes','val'=>$kpis['nuevos_mes']??0,'icon'=>'fa-user-plus',  'color'=>'amber'],
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
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
        <div style="flex:3;min-width:220px;"><label class="form-label">Buscar</label>
            <input type="text" name="buscar" class="form-control" placeholder="Nombre, teléfono, correo, DNI..." value="<?= htmlspecialchars($filtros['buscar']) ?>"></div>
        <div style="flex:1;min-width:130px;"><label class="form-label">Estado</label>
            <select name="estado" class="form-select">
                <option value="">Todos</option>
                <?php foreach(['activo','inactivo','fallecido'] as $e): ?>
                <option value="<?= $e ?>" <?= $filtros['estado']===$e?'selected':'' ?>><?= ucfirst($e) ?></option>
                <?php endforeach; ?>
            </select></div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-og-primary"><i class="fas fa-search me-1"></i>Filtrar</button>
            <a href="<?= APP_URL ?>pacientes" class="btn-og-secondary">Limpiar</a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:16px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;color:var(--body-text);">Pacientes <span style="font-size:12px;color:#9CA3AF;font-weight:400;">(<?= $total ?> total)</span></span>
        <button class="btn-og-primary" onclick="document.getElementById('modalPaciente').style.display='flex'"><i class="fas fa-user-plus me-1"></i>Nuevo Paciente</button>
    </div>
    <div style="overflow-x:auto;">
    <table class="tabla-og">
        <thead><tr><th>Paciente</th><th>DNI</th><th>Teléfono</th><th>Correo</th><th>Citas</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if(empty($pacientes)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-users fa-2x d-block mb-2" style="opacity:.3;"></i>Sin pacientes</td></tr>
        <?php else: foreach($pacientes as $p): ?>
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1A56AB,#0C1F46);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;flex-shrink:0;">
                        <?= strtoupper(substr($p['nombre'],0,1).substr($p['apellidos'],0,1)) ?>
                    </div>
                    <div>
                        <div style="font-weight:600;color:var(--body-text);"><?= htmlspecialchars($p['nombre'].' '.$p['apellidos']) ?></div>
                        <div style="font-size:11px;color:#9CA3AF;"><?= $p['sexo']??'' ?></div>
                    </div>
                </div>
            </td>
            <td><?= htmlspecialchars($p['dni']??'—') ?></td>
            <td><?= htmlspecialchars($p['telefono']??'—') ?></td>
            <td><?= htmlspecialchars($p['correo']??'—') ?></td>
            <td style="text-align:center;font-weight:600;"><?= $p['total_citas'] ?></td>
            <td><span class="badge badge-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <a href="<?= APP_URL ?>expedientes/ver?id=<?= $p['id_paciente'] ?>" class="btn-og-icon" title="Ver expediente"><i class="fas fa-folder-open"></i></a>
                    <button class="btn-og-icon" title="Editar" onclick="editarPaciente(<?= htmlspecialchars(json_encode($p)) ?>)"><i class="fas fa-edit"></i></button>
                    <button class="btn-og-icon btn-danger-icon" title="Desactivar" onclick="desactivarPaciente(<?= $p['id_paciente'] ?>)"><i class="fas fa-user-slash"></i></button>
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

<!-- Modal Nuevo/Editar Paciente -->
<div id="modalPaciente" style="display:none;position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;overflow-y:auto;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="document.getElementById('modalPaciente').style.display='none'"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:680px;margin:24px 16px;">
        <div style="padding:18px 22px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
            <h5 style="margin:0;font-size:15px;font-weight:700;color:var(--body-text);"><i class="fas fa-user-plus me-2" style="color:#1A56AB;"></i><span id="modalPacTitulo">Nuevo Paciente</span></h5>
            <button onclick="document.getElementById('modalPaciente').style.display='none'" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:16px;"><i class="fas fa-times"></i></button>
        </div>
        <form id="formPaciente" method="POST" action="<?= APP_URL ?>pacientes/crear">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="id_paciente" id="fp_id" value="">
            <div style="padding:20px 22px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div><label class="form-label">Nombre *</label><input type="text" name="nombre" id="fp_nombre" class="form-control" required></div>
                    <div><label class="form-label">Apellidos *</label><input type="text" name="apellidos" id="fp_apellidos" class="form-control" required></div>
                    <div><label class="form-label">DNI / Pasaporte</label><input type="text" name="dni" id="fp_dni" class="form-control"></div>
                    <div><label class="form-label">RTN Fiscal</label><input type="text" name="rtn" id="fp_rtn" class="form-control"></div>
                    <div><label class="form-label">Fecha de Nacimiento</label><input type="date" name="fecha_nacimiento" id="fp_fnac" class="form-control"></div>
                    <div><label class="form-label">Sexo</label>
                        <select name="sexo" id="fp_sexo" class="form-select">
                            <option value="">Seleccionar</option>
                            <option value="M">Masculino</option><option value="F">Femenino</option><option value="Otro">Otro</option>
                        </select></div>
                    <div><label class="form-label">Teléfono</label><input type="text" name="telefono" id="fp_tel" class="form-control"></div>
                    <div><label class="form-label">Correo</label><input type="email" name="correo" id="fp_cor" class="form-control"></div>
                    <div style="grid-column:span 2;"><label class="form-label">Dirección</label><input type="text" name="direccion" id="fp_dir" class="form-control"></div>
                    <div><label class="form-label">Estado Civil</label>
                        <select name="estado_civil" id="fp_ecv" class="form-select">
                            <option value="">Seleccionar</option>
                            <option value="soltero">Soltero/a</option>
                            <option value="casado">Casado/a</option>
                            <option value="union_libre">Unión libre</option>
                            <option value="divorciado">Divorciado/a</option>
                            <option value="viudo">Viudo/a</option>
                            <option value="otro">Otro</option>
                        </select></div>
                    <div><label class="form-label">Ocupación</label><input type="text" name="ocupacion" id="fp_ocu" class="form-control" placeholder="Profesión u oficio"></div>
                    <div><label class="form-label">Tel. Emergencia</label><input type="text" name="telefono_emergencia" id="fp_telE" class="form-control"></div>
                    <div><label class="form-label">Contacto Emergencia</label><input type="text" name="nombre_contacto_emergencia" id="fp_conE" class="form-control" placeholder="Nombre completo"></div>
                    <div><label class="form-label">Responsable de Pago</label><input type="text" name="responsable_pago" id="fp_rspP" class="form-control" placeholder="Nombre del responsable"></div>
                    <div><label class="form-label">Estado</label>
                        <select name="estado" id="fp_est" class="form-select">
                            <option value="activo">Activo</option><option value="inactivo">Inactivo</option><option value="fallecido">Fallecido</option>
                        </select></div>
                </div>
            </div>
            <div style="padding:14px 22px;border-top:1px solid var(--card-border);display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-og-secondary" onclick="document.getElementById('modalPaciente').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-og-primary" id="btnGuardarPac">Guardar</button>
            </div>
        </form>
    </div>
</div>

<style>
.btn-og-icon{width:30px;height:30px;border-radius:6px;border:1px solid #DDE4EF;background:#F5F7FB;color:#374151;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:.15s;text-decoration:none;}
.btn-og-icon:hover{background:#1A56AB;border-color:#1A56AB;color:#fff;}
.btn-danger-icon:hover{background:#DC2626;border-color:#DC2626;color:#fff;}
[data-theme="dark"] .btn-og-icon{background:#253349;border-color:#334155;color:#CBD5E1;}
</style>
<script>
function editarPaciente(p){
    document.getElementById('modalPacTitulo').textContent='Editar Paciente';
    document.getElementById('formPaciente').action='<?= APP_URL ?>pacientes/actualizar';
    document.getElementById('fp_id').value=p.id_paciente;
    document.getElementById('fp_nombre').value=p.nombre||'';
    document.getElementById('fp_apellidos').value=p.apellidos||'';
    document.getElementById('fp_dni').value=p.dni||'';
    document.getElementById('fp_rtn').value=p.rtn||'';
    document.getElementById('fp_fnac').value=p.fecha_nacimiento||'';
    document.getElementById('fp_sexo').value=p.sexo||'';
    document.getElementById('fp_tel').value=p.telefono||'';
    document.getElementById('fp_cor').value=p.correo||'';
    document.getElementById('fp_dir').value=p.direccion||'';
    document.getElementById('fp_ecv').value=p.estado_civil||'';
    document.getElementById('fp_ocu').value=p.ocupacion||'';
    document.getElementById('fp_telE').value=p.telefono_emergencia||'';
    document.getElementById('fp_conE').value=p.nombre_contacto_emergencia||'';
    document.getElementById('fp_rspP').value=p.responsable_pago||'';
    document.getElementById('fp_est').value=p.estado||'activo';
    document.getElementById('modalPaciente').style.display='flex';
}
function desactivarPaciente(id){
    Swal.fire({title:'¿Desactivar paciente?',icon:'warning',showCancelButton:true,confirmButtonColor:'#DC2626',confirmButtonText:'Desactivar',cancelButtonText:'Cancelar'})
    .then(r=>{if(!r.isConfirmed)return;const fd=new FormData();fd.append('csrf_token','<?= $csrf ?>');fd.append('id_paciente',id);fetch('<?= APP_URL ?>pacientes/eliminar',{method:'POST',body:fd}).then(()=>location.reload());});
}
</script>
