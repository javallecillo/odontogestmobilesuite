<?php $csrf = Csrf::token(); ?>
<div><div style="padding:24px 28px;">

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
<?php foreach([
    ['label'=>'Total Productos','val'=>$kpis['total_productos']??0,'icon'=>'fa-boxes-stacked','color'=>'blue'],
    ['label'=>'Activos',        'val'=>$kpis['activos']??0,        'icon'=>'fa-check-circle', 'color'=>'green'],
    ['label'=>'Stock Crítico',  'val'=>$kpis['bajo_minimo']??0,    'icon'=>'fa-exclamation',  'color'=>'amber'],
    ['label'=>'Agotados',       'val'=>$kpis['agotados']??0,       'icon'=>'fa-ban',          'color'=>'red'],
] as $k): ?>
<div class="kpi-card">
    <div style="display:flex;align-items:center;gap:14px;">
        <div class="kpi-icon <?= $k['color'] ?>"><i class="fas <?= $k['icon'] ?>"></i></div>
        <div><div class="kpi-value"><?= $k['val'] ?></div><div class="kpi-label"><?= $k['label'] ?></div></div>
    </div>
</div>
<?php endforeach; ?>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;">
<!-- Tabla productos -->
<div>
    <div class="kpi-card" style="margin-bottom:16px;">
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div style="flex:2;min-width:180px;"><label class="form-label">Buscar</label><input type="text" name="buscar" class="form-control" placeholder="Nombre del producto..." value="<?= htmlspecialchars($filtros['buscar']) ?>"></div>
            <div style="flex:1;min-width:130px;"><label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach(['activo','inactivo','agotado'] as $e): ?><option value="<?= $e ?>" <?= $filtros['estado']===$e?'selected':'' ?>><?= ucfirst($e) ?></option><?php endforeach; ?>
                </select></div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn-og-primary"><i class="fas fa-search me-1"></i>Filtrar</button>
                <a href="<?= APP_URL ?>inventario" class="btn-og-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="kpi-card" style="padding:0;overflow:hidden;">
        <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
            <span style="font-weight:600;color:var(--body-text);">Productos <span style="font-size:12px;color:#9CA3AF;">(<?= $total ?>)</span></span>
            <button class="btn-og-primary" onclick="document.getElementById('modalProducto').style.display='flex'"><i class="fas fa-plus me-1"></i>Nuevo Producto</button>
        </div>
        <div style="overflow-x:auto;">
        <table class="tabla-og">
            <thead><tr><th>Producto</th><th>Stock</th><th>Mín.</th><th>Nivel</th><th>Precio Venta</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
            <?php if(empty($productos)): ?>
            <tr><td colspan="7" style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-boxes-stacked fa-2x d-block mb-2" style="opacity:.3;"></i>Sin productos</td></tr>
            <?php else: foreach($productos as $p):
                $niv=$p['nivel_stock'];
                $nivCol=['ok'=>'green','bajo'=>'amber','critico'=>'red','agotado'=>'red'][$niv]??'gray';
            ?>
            <tr>
                <td>
                    <div style="font-weight:600;color:var(--body-text);"><?= htmlspecialchars($p['nombre']) ?></div>
                    <div style="font-size:11px;color:#9CA3AF;"><?= htmlspecialchars($p['unidad_medida']??'') ?><?= $p['proveedor']?' · '.$p['proveedor']:'' ?></div>
                </td>
                <td style="font-weight:700;font-size:16px;<?= $p['stock']<=$p['stock_minimo']?'color:#DC2626;':'' ?>"><?= $p['stock'] ?></td>
                <td><?= $p['stock_minimo'] ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:6px;">
                        <div style="height:6px;border-radius:3px;background:#E5E7EB;flex:1;min-width:50px;">
                            <?php $pct=min(100,round(($p['stock']/max($p['stock_minimo'],1))*100)); ?>
                            <div style="height:100%;border-radius:3px;width:<?= $pct ?>%;background:<?= $pct>=100?'#16A34A':($pct>=50?'#F59E0B':'#DC2626') ?>;"></div>
                        </div>
                        <span class="badge badge-<?= $nivCol ?>" style="font-size:10px;"><?= ucfirst($niv) ?></span>
                    </div>
                </td>
                <td>L. <?= number_format($p['precio_venta'],2) ?></td>
                <td><span class="badge badge-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
                <td>
                    <div style="display:flex;gap:6px;">
                        <button class="btn-og-icon" title="Ajustar stock" onclick="ajustarStock(<?= $p['id_producto'] ?>, '<?= htmlspecialchars($p['nombre']) ?>')"><i class="fas fa-boxes-stacked"></i></button>
                        <button class="btn-og-icon" title="Editar" onclick="editarProducto(<?= htmlspecialchars(json_encode($p)) ?>)"><i class="fas fa-edit"></i></button>
                        <button class="btn-og-icon btn-danger-icon" title="Desactivar" onclick="desactivarProducto(<?= $p['id_producto'] ?>)"><i class="fas fa-trash"></i></button>
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
</div>

<!-- Panel alertas -->
<div>
    <div class="kpi-card" style="position:sticky;top:80px;">
        <div style="font-weight:700;color:var(--body-text);margin-bottom:14px;font-size:14px;"><i class="fas fa-exclamation-triangle me-2" style="color:#F59E0B;"></i>Alertas de Stock</div>
        <?php if(empty($alertasStock)): ?>
        <div style="text-align:center;padding:20px;color:#9CA3AF;font-size:13px;"><i class="fas fa-check-circle fa-2x d-block mb-2" style="color:#16A34A;opacity:.5;"></i>Todo en orden</div>
        <?php else: foreach($alertasStock as $a): $col=$a['nivel']==='agotado'?'#DC2626':'#F59E0B'; ?>
        <div style="margin-bottom:12px;padding:10px;border-radius:8px;border:1px solid <?= $col ?>22;background:<?= $col ?>0A;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                <span style="font-weight:600;font-size:13px;color:var(--body-text);"><?= htmlspecialchars($a['nombre']) ?></span>
                <span class="badge" style="background:<?= $col ?>22;color:<?= $col ?>;font-size:10px;"><?= ucfirst($a['nivel']) ?></span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#9CA3AF;">
                <span>Stock: <strong style="color:<?= $col ?>;"><?= $a['stock'] ?></strong></span>
                <span>Mínimo: <?= $a['stock_minimo'] ?></span>
            </div>
            <div style="height:4px;background:#E5E7EB;border-radius:2px;margin-top:6px;">
                <div style="height:100%;border-radius:2px;width:<?= $a['porcentaje_stock'] ?>%;background:<?= $col ?>;"></div>
            </div>
        </div>
        <?php endforeach; endif; ?>
        <div style="font-size:11px;color:#9CA3AF;margin-top:8px;text-align:right;">Valor inventario: <strong style="color:var(--body-text);">L. <?= number_format($kpis['valor_inventario']??0,2) ?></strong></div>
    </div>
</div>
</div><!-- /grid -->
</div></div>

<!-- Modal Nuevo/Editar Producto -->
<div id="modalProducto" style="display:none;position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="document.getElementById('modalProducto').style.display='none'"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:600px;margin:16px;max-height:90vh;overflow-y:auto;">
        <div style="padding:16px 22px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;background:var(--card-bg);z-index:1;">
            <h5 style="margin:0;font-size:15px;font-weight:700;color:var(--body-text);"><i class="fas fa-box me-2" style="color:#1A56AB;"></i><span id="modalProdTitulo">Nuevo Producto</span></h5>
            <button onclick="document.getElementById('modalProducto').style.display='none'" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:16px;"><i class="fas fa-times"></i></button>
        </div>
        <form id="formProducto" method="POST" action="<?= APP_URL ?>inventario/crear">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="id_producto" id="pp_id" value="">
            <div style="padding:20px 22px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div style="grid-column:span 2;"><label class="form-label">Nombre *</label><input type="text" name="nombre" id="pp_nom" class="form-control" required></div>
                    <div><label class="form-label">Proveedor</label>
                        <select name="id_proveedor" id="pp_prov" class="form-select">
                            <option value="">— Seleccionar —</option>
                            <?php foreach($proveedores as $pv): ?>
                            <option value="<?= $pv['id_proveedor'] ?>"><?= htmlspecialchars($pv['proveedor']) ?></option>
                            <?php endforeach; ?>
                        </select></div>
                    <div><label class="form-label">Unidad</label><input type="text" name="unidad_medida" id="pp_uni" class="form-control" placeholder="Caja, unidad, frasco..."></div>
                    <div><label class="form-label">Stock actual *</label><input type="number" name="stock" id="pp_stk" class="form-control" min="0" required></div>
                    <div><label class="form-label">Stock mínimo *</label><input type="number" name="stock_minimo" id="pp_stm" class="form-control" min="0" required></div>
                    <div><label class="form-label">Precio Costo (L.)</label><input type="number" name="precio_costo" id="pp_pc" class="form-control" step="0.01" min="0"></div>
                    <div><label class="form-label">Precio Venta (L.)</label><input type="number" name="precio_venta" id="pp_pv" class="form-control" step="0.01" min="0"></div>
                    <div><label class="form-label">Tasa Impuesto (%)</label>
                        <select name="tasa_impuesto" id="pp_imp" class="form-select">
                            <option value="0">0% — Sin impuesto</option>
                            <option value="15">15% — ISV</option>
                            <option value="18">18% — ISV especial</option>
                        </select></div>
                    <div><label class="form-label">Estado</label>
                        <select name="estado" id="pp_est" class="form-select"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
                    <div style="grid-column:span 2;"><label class="form-label">Descripción</label><textarea name="descripcion" id="pp_desc" class="form-control" rows="2"></textarea></div>
                </div>
            </div>
            <div style="padding:14px 22px;border-top:1px solid var(--card-border);display:flex;justify-content:flex-end;gap:10px;position:sticky;bottom:0;background:var(--card-bg);">
                <button type="button" class="btn-og-secondary" onclick="document.getElementById('modalProducto').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-og-primary">Guardar</button>
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
function editarProducto(p){
    document.getElementById('modalProdTitulo').textContent='Editar Producto';
    document.getElementById('formProducto').action='<?= APP_URL ?>inventario/actualizar';
    const map={id_producto:'id',nombre:'nom',unidad_medida:'uni',stock:'stk',
                stock_minimo:'stm',precio_costo:'pc',precio_venta:'pv',
                descripcion:'desc',estado:'est',
                id_proveedor:'prov',tasa_impuesto:'imp'};
    Object.entries(map).forEach(([k,s])=>{
        const el=document.getElementById('pp_'+s);
        if(el) el.value=p[k]??'';
    });
    document.getElementById('modalProducto').style.display='flex';
}
function ajustarStock(id, nombre){
    Swal.fire({title:'Ajustar stock — '+nombre,html:`<div style="display:flex;flex-direction:column;gap:12px;text-align:left;">
        <div><label style="font-size:13px;font-weight:600;">Tipo</label><br>
        <select id="sTipo" style="width:100%;padding:8px;border-radius:6px;border:1px solid #DDE4EF;font-size:13px;">
            <option value="entrada">Entrada (+)</option><option value="salida">Salida (-)</option><option value="ajuste">Ajuste directo</option>
        </select></div>
        <div><label style="font-size:13px;font-weight:600;">Cantidad</label><br>
        <input type="number" id="sCant" min="0" value="1" style="width:100%;padding:8px;border-radius:6px;border:1px solid #DDE4EF;font-size:13px;"></div>
        <div><label style="font-size:13px;font-weight:600;">Motivo</label><br>
        <input type="text" id="sMotivo" placeholder="Motivo del ajuste..." style="width:100%;padding:8px;border-radius:6px;border:1px solid #DDE4EF;font-size:13px;"></div>
    </div>`,showCancelButton:true,confirmButtonColor:'#1A56AB',confirmButtonText:'Aplicar',cancelButtonText:'Cancelar',
    preConfirm:()=>({tipo:document.getElementById('sTipo').value,cantidad:document.getElementById('sCant').value,motivo:document.getElementById('sMotivo').value})
    }).then(r=>{
        if(!r.isConfirmed)return;
        const fd=new FormData();
        fd.append('csrf_token','<?= $csrf ?>');fd.append('id_producto',id);
        fd.append('tipo',r.value.tipo);fd.append('cantidad',r.value.cantidad);fd.append('motivo',r.value.motivo);
        fetch('<?= APP_URL ?>inventario/ajustarStock',{method:'POST',body:fd}).then(()=>location.reload());
    });
}
function desactivarProducto(id){
    Swal.fire({title:'¿Desactivar producto?',icon:'warning',showCancelButton:true,confirmButtonColor:'#DC2626',confirmButtonText:'Desactivar',cancelButtonText:'Cancelar'})
    .then(r=>{if(!r.isConfirmed)return;const fd=new FormData();fd.append('csrf_token','<?= $csrf ?>');fd.append('id_producto',id);fetch('<?= APP_URL ?>inventario/eliminar',{method:'POST',body:fd}).then(()=>location.reload());});
}
</script>
