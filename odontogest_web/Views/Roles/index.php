<?php $csrf = Csrf::token(); ?>
<div><div style="padding:24px 28px;">
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;color:var(--body-text);"><i class="fas fa-user-shield me-2" style="color:#1A56AB;"></i>Roles del Sistema</span>
        <button class="btn-og-primary" onclick="document.getElementById('modalRol').style.display='flex'"><i class="fas fa-plus me-1"></i>Nuevo Rol</button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;padding:20px;">
    <?php foreach($roles as $r): ?>
    <div class="kpi-card" style="border:1px solid var(--card-border);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
            <div>
                <div style="font-weight:700;font-size:15px;color:var(--body-text);"><?= htmlspecialchars($r['nombre']) ?></div>
                <div style="font-size:12px;color:#9CA3AF;margin-top:2px;"><?= htmlspecialchars($r['descripcion']??'') ?></div>
            </div>
            <span class="badge badge-blue" style="background:rgba(26,86,171,.1);color:#1A56AB;"><?= $r['total_usuarios'] ?> usuarios</span>
        </div>
        <div style="display:flex;gap:8px;margin-top:12px;padding-top:10px;border-top:1px solid var(--card-border);">
            <button class="btn-og-secondary" style="flex:1;font-size:12px;" onclick="verPermisos(<?= $r['id_rol'] ?>,'<?= htmlspecialchars($r['nombre']) ?>')"><i class="fas fa-key me-1"></i>Permisos</button>
            <button class="btn-og-icon btn-danger-icon" title="Eliminar" onclick="eliminarRol(<?= $r['id_rol'] ?>)"><i class="fas fa-trash"></i></button>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
</div></div>
<!-- Modal Nuevo Rol -->
<div id="modalRol" style="display:none;position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="document.getElementById('modalRol').style.display='none'"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:440px;margin:16px;">
        <div style="padding:16px 22px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
            <h5 style="margin:0;font-size:15px;font-weight:700;color:var(--body-text);">Nuevo Rol</h5>
            <button onclick="document.getElementById('modalRol').style.display='none'" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:16px;"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="<?= APP_URL ?>roles/crear">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div style="padding:20px 22px;">
                <div class="mb-3"><label class="form-label">Nombre *</label><input type="text" name="nombre" class="form-control" required></div>
                <div class="mb-0"><label class="form-label">Descripción</label><input type="text" name="descripcion" class="form-control"></div>
            </div>
            <div style="padding:14px 22px;border-top:1px solid var(--card-border);display:flex;justify-content:flex-end;gap:10px;">
                <button type="button" class="btn-og-secondary" onclick="document.getElementById('modalRol').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-og-primary">Crear Rol</button>
            </div>
        </form>
    </div>
</div>
<style>.btn-og-icon{width:30px;height:30px;border-radius:6px;border:1px solid #DDE4EF;background:#F5F7FB;color:#374151;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:.15s;}.btn-og-icon:hover{background:#1A56AB;border-color:#1A56AB;color:#fff;}.btn-danger-icon:hover{background:#DC2626;border-color:#DC2626;color:#fff;}[data-theme="dark"] .btn-og-icon{background:#253349;border-color:#334155;color:#CBD5E1;}</style>
<script>
function verPermisos(id,nombre){window.location.href='<?= APP_URL ?>roles/permisos?id='+id;}
function eliminarRol(id){Swal.fire({title:'¿Eliminar rol?',text:'Los usuarios con este rol perderán acceso.',icon:'warning',showCancelButton:true,confirmButtonColor:'#DC2626',confirmButtonText:'Eliminar',cancelButtonText:'Cancelar'}).then(r=>{if(!r.isConfirmed)return;const fd=new FormData();fd.append('csrf_token','<?= $csrf ?>');fd.append('id_rol',id);fetch('<?= APP_URL ?>roles/eliminar',{method:'POST',body:fd}).then(()=>location.reload());});}
</script>
