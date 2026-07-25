<?php $csrf = Csrf::token(); ?>
<div><div style="padding:24px 28px;">
<div class="kpi-card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div style="flex:2;min-width:200px;"><label class="form-label">Buscar</label><input type="text" name="buscar" class="form-control" placeholder="Nombre del servicio..." value="<?= htmlspecialchars($filtros['buscar']) ?>"></div>
        <div style="flex:1;min-width:120px;"><label class="form-label">Estado</label>
            <select name="estado" class="form-select"><option value="">Todos</option><option value="activo" <?= $filtros['estado']==='activo'?'selected':'' ?>>Activo</option><option value="inactivo" <?= $filtros['estado']==='inactivo'?'selected':'' ?>>Inactivo</option></select></div>
        <div style="display:flex;gap:8px;"><button type="submit" class="btn-og-primary"><i class="fas fa-search me-1"></i>Filtrar</button><a href="<?= APP_URL ?>servicios" class="btn-og-secondary">Limpiar</a></div>
    </form>
</div>
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;color:var(--body-text);">Catálogo de Servicios <span style="font-size:12px;color:#9CA3AF;">(<?= $total ?>)</span></span>
        <button class="btn-og-primary" onclick="document.getElementById('modalServicio').style.display='flex'"><i class="fas fa-plus me-1"></i>Nuevo Servicio</button>
    </div>
    <div style="overflow-x:auto;">
    <table class="tabla-og">
        <thead><tr><th>Servicio</th><th>Precio Base</th><th>ISV</th><th>Duración</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if(empty($servicios)): ?>
        <tr><td colspan="6" style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-tooth fa-2x d-block mb-2" style="opacity:.3;"></i>Sin servicios</td></tr>
        <?php else: foreach($servicios as $s): ?>
        <tr>
            <td><div style="font-weight:600;color:var(--body-text);"><?= htmlspecialchars($s['nombre']) ?></div><div style="font-size:11px;color:#9CA3AF;"><?= htmlspecialchars(mb_strimwidth($s['descripcion']??'',0,60,'...')) ?></div></td>
            <td style="font-weight:700;">L. <?= number_format($s['precio_base'],2) ?></td>
            <td><?= $s['tasa_impuesto'] ?>%</td>
            <td><?= $s['duracion_min'] ?> min</td>
            <td><span class="badge badge-<?= $s['estado'] ?>"><?= ucfirst($s['estado']) ?></span></td>
            <td><div style="display:flex;gap:6px;">
                <button class="btn-og-icon" onclick="editarServicio(<?= htmlspecialchars(json_encode($s)) ?>)"><i class="fas fa-edit"></i></button>
                <button class="btn-og-icon btn-danger-icon" onclick="eliminarServicio(<?= $s['id_servicio'] ?>)"><i class="fas fa-trash"></i></button>
            </div></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>
</div></div>
<!-- Modal -->
<div id="modalServicio" style="display:none;position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="document.getElementById('modalServicio').style.display='none'"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:500px;margin:16px;">
        <div style="padding:16px 22px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
            <h5 style="margin:0;font-size:15px;font-weight:700;color:var(--body-text);"><span id="modalSrvTitulo">Nuevo Servicio</span></h5>
            <button onclick="document.getElementById('modalServicio').style.display='none'" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:16px;"><i class="fas fa-times"></i></button>
        </div>
        <form id="formServicio" method="POST" action="<?= APP_URL ?>servicios/crear">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="id_servicio" id="fs_id">
            <div style="padding:20px 22px;display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:span 2;"><label class="form-label">Nombre *</label><input type="text" name="nombre" id="fs_nom" class="form-control" required></div>
                <div><label class="form-label">Precio Base (L.) *</label><input type="number" name="precio_base" id="fs_precio" class="form-control" step="0.01" min="0" required></div>
                <div><label class="form-label">ISV</label><select name="tasa_impuesto" id="fs_isv" class="form-select"><option value="0">0%</option><option value="15" selected>15%</option><option value="18">18%</option></select></div>
                <div><label class="form-label">Duración (min)</label><input type="number" name="duracion_min" id="fs_dur" class="form-control" value="30" min="5"></div>
                <div><label class="form-label">Estado</label><select name="estado" id="fs_est" class="form-select"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
                <div style="grid-column:span 2;"><label class="form-label">Descripción</label><textarea name="descripcion" id="fs_desc" class="form-control" rows="2"></textarea></div>
            </div>
            <div style="padding:14px 22px;border-top:1px solid var(--card-border);display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-og-secondary" onclick="document.getElementById('modalServicio').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-og-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
<style>.btn-og-icon{width:30px;height:30px;border-radius:6px;border:1px solid #DDE4EF;background:#F5F7FB;color:#374151;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:.15s;}.btn-og-icon:hover{background:#1A56AB;border-color:#1A56AB;color:#fff;}.btn-danger-icon:hover{background:#DC2626;border-color:#DC2626;color:#fff;}[data-theme="dark"] .btn-og-icon{background:#253349;border-color:#334155;color:#CBD5E1;}</style>
<script>
function editarServicio(s){document.getElementById('modalSrvTitulo').textContent='Editar Servicio';document.getElementById('formServicio').action='<?= APP_URL ?>servicios/actualizar';document.getElementById('fs_id').value=s.id_servicio;document.getElementById('fs_nom').value=s.nombre;document.getElementById('fs_precio').value=s.precio_base;document.getElementById('fs_isv').value=s.tasa_impuesto;document.getElementById('fs_dur').value=s.duracion_min;document.getElementById('fs_est').value=s.estado;document.getElementById('fs_desc').value=s.descripcion||'';document.getElementById('modalServicio').style.display='flex';}
function eliminarServicio(id){Swal.fire({title:'¿Desactivar servicio?',icon:'warning',showCancelButton:true,confirmButtonColor:'#DC2626',confirmButtonText:'Desactivar',cancelButtonText:'Cancelar'}).then(r=>{if(!r.isConfirmed)return;const fd=new FormData();fd.append('csrf_token','<?= $csrf ?>');fd.append('id_servicio',id);fetch('<?= APP_URL ?>servicios/eliminar',{method:'POST',body:fd}).then(()=>location.reload());});}
</script>
