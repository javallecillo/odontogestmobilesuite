<?php $csrf = Csrf::token(); ?>
<div><div style="padding:24px 28px;">
<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
<?php foreach([
    ['label'=>'Emitidas',     'val'=>$kpis['emitidas']??0,       'icon'=>'fa-file-invoice-dollar','color'=>'amber'],
    ['label'=>'Pagadas',      'val'=>$kpis['pagadas']??0,        'icon'=>'fa-check-circle',       'color'=>'green'],
    ['label'=>'Ingresos Mes', 'val'=>'L. '.number_format($kpis['ingresos_mes']??0,2),'icon'=>'fa-coins','color'=>'blue'],
    ['label'=>'Pendiente',    'val'=>'L. '.number_format($kpis['monto_pendiente']??0,2),'icon'=>'fa-clock','color'=>'red'],
] as $k): ?>
<div class="kpi-card">
    <div style="display:flex;align-items:center;gap:14px;">
        <div class="kpi-icon <?= $k['color'] ?>"><i class="fas <?= $k['icon'] ?>"></i></div>
        <div><div class="kpi-value" style="font-size:<?= strlen($k['val'])>6?'16px':'22px' ?>;"><?= $k['val'] ?></div><div class="kpi-label"><?= $k['label'] ?></div></div>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- Filtros -->
<div class="kpi-card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
        <div style="flex:1;min-width:130px;"><label class="form-label">Estado</label>
            <select name="estado" class="form-select"><option value="">Todos</option>
                <?php foreach(['emitida','pagada','anulada'] as $e): ?><option value="<?= $e ?>" <?= $filtros['estado']===$e?'selected':'' ?>><?= ucfirst($e) ?></option><?php endforeach; ?>
            </select></div>
        <div style="flex:1;min-width:140px;"><label class="form-label">Desde</label><input type="date" name="fecha_ini" class="form-control" value="<?= htmlspecialchars($filtros['fecha_ini']) ?>"></div>
        <div style="flex:1;min-width:140px;"><label class="form-label">Hasta</label><input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($filtros['fecha_fin']) ?>"></div>
        <div style="flex:2;min-width:180px;"><label class="form-label">Buscar</label><input type="text" name="buscar" class="form-control" placeholder="Paciente / N° factura..." value="<?= htmlspecialchars($filtros['buscar']) ?>"></div>
        <div style="display:flex;gap:8px;"><button type="submit" class="btn-og-primary"><i class="fas fa-search me-1"></i>Filtrar</button><a href="<?= APP_URL ?>facturacion" class="btn-og-secondary">Limpiar</a></div>
    </form>
</div>

<!-- Tabla -->
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;color:var(--body-text);">Facturas <span style="font-size:12px;color:#9CA3AF;">(<?= $total ?>)</span></span>
        <button class="btn-og-primary" onclick="document.getElementById('modalFactura').style.display='flex'"><i class="fas fa-plus me-1"></i>Nueva Factura</button>
    </div>
    <div style="overflow-x:auto;">
    <table class="tabla-og">
        <thead><tr><th>N° Factura</th><th>Fecha</th><th>Paciente</th><th>Subtotal</th><th>ISV</th><th>Total</th><th>Método</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
        <?php if(empty($facturas)): ?>
        <tr><td colspan="9" style="text-align:center;padding:40px;color:#9CA3AF;"><i class="fas fa-file-invoice fa-2x d-block mb-2" style="opacity:.3;"></i>Sin facturas</td></tr>
        <?php else: foreach($facturas as $f): ?>
        <tr>
            <td style="font-weight:600;color:#1A56AB;"><?= htmlspecialchars($f['numero_factura']) ?></td>
            <td><?= $f['fecha'] ?></td>
            <td><?= htmlspecialchars($f['paciente']) ?></td>
            <td>L. <?= number_format($f['subtotal'],2) ?></td>
            <td>L. <?= number_format($f['impuesto'],2) ?></td>
            <td style="font-weight:700;">L. <?= number_format($f['total'],2) ?></td>
            <td><?= ucfirst($f['metodo_pago']) ?></td>
            <td><span class="badge badge-<?= $f['estado'] ?>"><?= ucfirst($f['estado']) ?></span></td>
            <td>
                <div style="display:flex;gap:6px;">
                    <?php if($f['estado']==='emitida'): ?>
                    <button class="btn-og-icon" title="Marcar pagada" onclick="marcarPagada(<?= $f['id_factura'] ?>)" style="color:#16A34A;"><i class="fas fa-check"></i></button>
                    <button class="btn-og-icon btn-danger-icon" title="Anular" onclick="anularFactura(<?= $f['id_factura'] ?>)"><i class="fas fa-ban"></i></button>
                    <?php endif; ?>
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
<style>.btn-og-icon{width:30px;height:30px;border-radius:6px;border:1px solid #DDE4EF;background:#F5F7FB;color:#374151;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:.15s;}.btn-og-icon:hover{background:#1A56AB;border-color:#1A56AB;color:#fff;}.btn-danger-icon:hover{background:#DC2626;border-color:#DC2626;color:#fff;}[data-theme="dark"] .btn-og-icon{background:#253349;border-color:#334155;color:#CBD5E1;}</style>

<!-- ── Modal Nueva Factura ──────────────────────────────────────── -->
<div id="modalFactura" style="display:none;position:fixed;inset:0;z-index:1050;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="document.getElementById('modalFactura').style.display='none'"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:16px;padding:28px 32px;width:min(640px,95vw);max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="margin:0;font-size:16px;font-weight:700;color:var(--body-text);">Nueva Factura</h3>
            <button onclick="document.getElementById('modalFactura').style.display='none'" style="background:none;border:none;cursor:pointer;color:#9CA3AF;font-size:18px;"><i class="fas fa-times"></i></button>
        </div>
        <form id="formFactura" method="POST" action="<?= APP_URL ?>facturacion/crear" onsubmit="prepararFactura()">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="subtotal" id="fac_sub">
            <input type="hidden" name="isv"      id="fac_isv">
            <input type="hidden" name="total"    id="fac_tot">
            <input type="hidden" name="items"    id="fac_items">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:span 2;">
                    <label class="form-label">Paciente *</label>
                    <input type="text" id="fac_buscar_pac" class="form-control" placeholder="Escriba el nombre del paciente…" oninput="buscarPacFac(this.value)" autocomplete="off">
                    <input type="hidden" name="id_paciente" id="fac_pac_id" required>
                    <div id="fac_pac_res" style="border:1px solid #DDE4EF;border-radius:8px;background:var(--card-bg);max-height:160px;overflow-y:auto;display:none;position:absolute;z-index:10;width:100%;"></div>
                </div>
                <div><label class="form-label">Método de Pago</label>
                    <select name="metodo_pago" class="form-select">
                        <option value="efectivo">Efectivo</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="transferencia">Transferencia</option>
                    </select></div>
                <div><label class="form-label">ISV</label>
                    <select id="fac_tasa" class="form-select" onchange="recalcular()">
                        <option value="0">0%</option>
                        <option value="15" selected>15%</option>
                        <option value="18">18%</option>
                    </select></div>
                <div style="grid-column:span 2;"><label class="form-label">Notas</label><textarea name="notas" class="form-control" rows="2"></textarea></div>
            </div>

            <!-- Items -->
            <div style="margin-top:18px;">
                <div style="font-weight:600;font-size:13px;color:var(--body-text);margin-bottom:10px;">Ítems de la factura</div>
                <div id="fac_items_lista"></div>
                <div style="display:grid;grid-template-columns:3fr 1fr 1fr auto;gap:8px;margin-top:8px;">
                    <input type="text" id="ni_desc" class="form-control" placeholder="Descripción del servicio">
                    <input type="number" id="ni_qty"  class="form-control" value="1" min="1" placeholder="Cant.">
                    <input type="number" id="ni_precio" class="form-control" step="0.01" placeholder="Precio L.">
                    <button type="button" class="btn-og-primary" onclick="agregarItem()"><i class="fas fa-plus"></i></button>
                </div>
            </div>

            <!-- Totales -->
            <div style="margin-top:16px;padding:14px;background:var(--sidebar-bg);border-radius:10px;text-align:right;font-size:14px;">
                <div>Subtotal: <strong id="show_sub">L. 0.00</strong></div>
                <div>ISV: <strong id="show_isv">L. 0.00</strong></div>
                <div style="font-size:17px;margin-top:6px;color:#1A56AB;">Total: <strong id="show_tot">L. 0.00</strong></div>
            </div>

            <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
                <button type="button" class="btn-og-secondary" onclick="document.getElementById('modalFactura').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-og-primary"><i class="fas fa-save me-1"></i>Emitir Factura</button>
            </div>
        </form>
    </div>
</div>

<script>
function marcarPagada(id){Swal.fire({title:'¿Marcar como pagada?',icon:'question',showCancelButton:true,confirmButtonColor:'#16A34A',confirmButtonText:'Confirmar',cancelButtonText:'Cancelar'}).then(r=>{if(!r.isConfirmed)return;const fd=new FormData();fd.append('csrf_token','<?= $csrf ?>');fd.append('id_factura',id);fetch('<?= APP_URL ?>facturacion/marcarPagada',{method:'POST',body:fd}).then(()=>location.reload());});}
function anularFactura(id){Swal.fire({title:'Anular factura',input:'text',inputPlaceholder:'Motivo de anulación...',showCancelButton:true,confirmButtonColor:'#DC2626',confirmButtonText:'Anular',cancelButtonText:'Cancelar',inputValidator:v=>!v&&'Ingrese el motivo'}).then(r=>{if(!r.isConfirmed)return;const fd=new FormData();fd.append('csrf_token','<?= $csrf ?>');fd.append('id_factura',id);fd.append('motivo',r.value);fetch('<?= APP_URL ?>facturacion/anular',{method:'POST',body:fd}).then(()=>location.reload());});}

// ── Nueva Factura ────────────────────────────────────
let facItems=[];
function agregarItem(){
    const desc=document.getElementById('ni_desc').value.trim();
    const qty=parseFloat(document.getElementById('ni_qty').value)||1;
    const precio=parseFloat(document.getElementById('ni_precio').value)||0;
    if(!desc||!precio)return Swal.fire('Completa descripción y precio','','warning');
    facItems.push({descripcion:desc,cantidad:qty,precio:precio,subtotal:qty*precio,total:qty*precio});
    document.getElementById('ni_desc').value='';document.getElementById('ni_qty').value=1;document.getElementById('ni_precio').value='';
    renderItems();recalcular();
}
function renderItems(){
    const el=document.getElementById('fac_items_lista');
    if(!facItems.length){el.innerHTML='<p style="color:#9CA3AF;font-size:13px;text-align:center;">Sin ítems aún</p>';return;}
    el.innerHTML=facItems.map((it,i)=>`<div style="display:flex;justify-content:space-between;align-items:center;padding:6px 10px;background:#F5F7FB;border-radius:6px;margin-bottom:6px;font-size:13px;"><span>${it.descripcion} × ${it.cantidad}</span><span>L. ${(it.total).toFixed(2)} <button type="button" onclick="remItem(${i})" style="background:none;border:none;color:#DC2626;cursor:pointer;margin-left:8px;"><i class="fas fa-times"></i></button></span></div>`).join('');
}
function remItem(i){facItems.splice(i,1);renderItems();recalcular();}
function recalcular(){
    const sub=facItems.reduce((a,it)=>a+it.total,0);
    const tasa=parseFloat(document.getElementById('fac_tasa').value)||0;
    const isv=sub*tasa/100;
    const tot=sub+isv;
    document.getElementById('show_sub').textContent='L. '+sub.toFixed(2);
    document.getElementById('show_isv').textContent='L. '+isv.toFixed(2);
    document.getElementById('show_tot').textContent='L. '+tot.toFixed(2);
}
function prepararFactura(){
    const sub=facItems.reduce((a,it)=>a+it.total,0);
    const tasa=parseFloat(document.getElementById('fac_tasa').value)||0;
    const isv=sub*tasa/100;
    document.getElementById('fac_sub').value=sub.toFixed(2);
    document.getElementById('fac_isv').value=isv.toFixed(2);
    document.getElementById('fac_tot').value=(sub+isv).toFixed(2);
    document.getElementById('fac_items').value=JSON.stringify(facItems);
}
let pacTimer;
function buscarPacFac(q){
    clearTimeout(pacTimer);
    if(q.length<2){document.getElementById('fac_pac_res').style.display='none';return;}
    pacTimer=setTimeout(()=>{
        fetch('<?= APP_URL ?>pacientes/buscar?q='+encodeURIComponent(q))
        .then(r=>r.json()).then(data=>{
            const res=document.getElementById('fac_pac_res');
            if(!data.pacientes||!data.pacientes.length){res.style.display='none';return;}
            res.innerHTML=data.pacientes.map(p=>`<div onclick="selPac(${p.id_paciente},'${p.nombre_completo.replace(/'/g,"\\'")}');document.getElementById('fac_pac_res').style.display='none'" style="padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #EEE;">${p.nombre_completo}</div>`).join('');
            res.style.display='block';
        }).catch(()=>{});
    },300);
}
function selPac(id,nom){document.getElementById('fac_pac_id').value=id;document.getElementById('fac_buscar_pac').value=nom;}
// reset modal al abrir
document.getElementById('modalFactura').addEventListener('show',()=>{facItems=[];renderItems();recalcular();});
</script>
