<?php
$csrf        = Csrf::token();
$expData     = ExpedientesModel::obtenerPorPaciente($paciente['id_paciente']);
$idExp       = $expData['id_expediente'] ?? 0;
$tiposSangre = ExpedientesModel::tiposSangre();
$catAlergias = ExpedientesModel::alergias();
$catEnferm   = ExpedientesModel::enfermedades();
$catMeds     = ExpedientesModel::medicamentos();
$alergiasPac = $idExp ? ExpedientesModel::alergiasExpediente($idExp)     : [];
$enfermPac   = $idExp ? ExpedientesModel::enfermedadesExpediente($idExp) : [];
$medsPac     = $idExp ? ExpedientesModel::medicamentosExpediente($idExp) : [];
$idsAlerg    = array_column($alergiasPac,'id_alergia');
$idsEnferm   = array_column($enfermPac,'id_enfermedad');
$idsMeds     = array_column($medsPac,'id_medicamento');
$tabActual   = $tab ?? 'historial';
$edad = $paciente['fecha_nacimiento'] ? (int)date_diff(date_create($paciente['fecha_nacimiento']),date_create())->y : null;
?>
<div><div style="padding:24px 28px;">

<!-- Header paciente -->
<div class="kpi-card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#1A56AB,#0C1F46);color:#fff;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;flex-shrink:0;">
            <?= strtoupper(substr($paciente['nombre'],0,1).substr($paciente['apellidos'],0,1)) ?>
        </div>
        <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px;">
                <h4 style="margin:0;font-size:18px;font-weight:700;color:var(--body-text);"><?= htmlspecialchars($paciente['nombre'].' '.$paciente['apellidos']) ?></h4>
                <span class="badge badge-<?= $paciente['estado_paciente']??$paciente['estado'] ?>"><?= ucfirst($paciente['estado_paciente']??$paciente['estado']??'') ?></span>
            </div>
            <div style="display:flex;gap:18px;flex-wrap:wrap;font-size:13px;color:#6B7280;">
                <?php if($edad): ?><span><i class="fas fa-birthday-cake me-1"></i><?= $edad ?> años</span><?php endif; ?>
                <?php if(!empty($paciente['sexo'])): ?><span><i class="fas fa-venus-mars me-1"></i><?= $paciente['sexo']==='M'?'Masculino':($paciente['sexo']==='F'?'Femenino':'Otro') ?></span><?php endif; ?>
                <?php if(!empty($paciente['telefono'])): ?><span><i class="fas fa-phone me-1"></i><?= htmlspecialchars($paciente['telefono']) ?></span><?php endif; ?>
                <?php if(!empty($paciente['correo'])): ?><span><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($paciente['correo']) ?></span><?php endif; ?>
                <?php if(!empty($expData['grupo_sangre'])): ?><span style="background:#FEE2E2;color:#DC2626;padding:2px 8px;border-radius:4px;font-weight:700;"><i class="fas fa-tint me-1"></i><?= htmlspecialchars($expData['grupo_sangre']) ?></span><?php endif; ?>
            </div>
        </div>
        <a href="<?= APP_URL ?>expedientes" class="btn-og-secondary"><i class="fas fa-arrow-left me-1"></i>Volver</a>
    </div>
</div>

<!-- Alertas médicas rápidas -->
<?php if(!empty($alergiasPac) || !empty($enfermPac)): ?>
<div style="background:#FEF9EC;border:1px solid #FCD34D;border-radius:10px;padding:12px 16px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:16px;">
    <div style="display:flex;align-items:center;gap:6px;font-size:13px;font-weight:600;color:#92400E;"><i class="fas fa-exclamation-triangle"></i>Alertas médicas:</div>
    <?php foreach($alergiasPac as $a): ?>
    <span style="background:#FEE2E2;color:#991B1B;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;"><i class="fas fa-allergies me-1"></i><?= htmlspecialchars($a['descripcion']) ?></span>
    <?php endforeach; ?>
    <?php foreach($enfermPac as $e): ?>
    <span style="background:#FEF3C7;color:#92400E;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;"><i class="fas fa-heartbeat me-1"></i><?= htmlspecialchars($e['descripcion']) ?></span>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Tabs -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:2px solid var(--card-border);">
    <?php foreach(['historial'=>['fa-history','Historial'],'expediente'=>['fa-notes-medical','Datos Clínicos'],'odontograma'=>['fa-tooth','Odontograma'],'facturas'=>['fa-file-invoice-dollar','Facturas']] as $k=>[$ic,$lbl]): ?>
    <a href="?id=<?= $paciente['id_paciente'] ?>&tab=<?= $k ?>"
       style="padding:10px 18px;font-size:13px;font-weight:600;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;
              <?= $tabActual===$k?'color:#1A56AB;border-bottom-color:#1A56AB;':'color:#6B7280;' ?>">
        <i class="fas <?= $ic ?> me-1"></i><?= $lbl ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── TAB: HISTORIAL ─────────────────────────────────────── -->
<?php if($tabActual==='historial'): ?>
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);font-weight:600;color:var(--body-text);">Historial de Citas</div>
    <table class="tabla-og">
        <thead><tr><th>Fecha</th><th>Odontólogo</th><th>Servicio</th><th>Estado</th><th>Notas</th></tr></thead>
        <tbody>
        <?php if(empty($historial)): ?>
        <tr><td colspan="5" style="text-align:center;padding:30px;color:#9CA3AF;">Sin historial de citas</td></tr>
        <?php else: foreach($historial as $h): ?>
        <tr>
            <td><?= date('d/m/Y H:i',strtotime($h['fecha_cita'])) ?></td>
            <td><?= htmlspecialchars($h['odontologo']) ?></td>
            <td><?= htmlspecialchars($h['servicio']??'—') ?></td>
            <td><span class="badge badge-<?= $h['estado'] ?>"><?= ucfirst(str_replace('_',' ',$h['estado'])) ?></span></td>
            <td style="font-size:12px;color:#6B7280;max-width:200px;"><?= nl2br(htmlspecialchars($h['notas']??'—')) ?></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- ── TAB: DATOS CLÍNICOS ──────────────────────────────── -->
<?php elseif($tabActual==='expediente'): ?>
<form method="POST" action="<?= APP_URL ?>expedientes/guardarExpediente">
<input type="hidden" name="csrf_token" value="<?= $csrf ?>">
<input type="hidden" name="id_paciente" value="<?= $paciente['id_paciente'] ?>">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

<!-- Columna izquierda: datos generales + sangre -->
<div style="display:flex;flex-direction:column;gap:16px;">
    <div class="kpi-card">
        <div style="font-weight:700;color:var(--body-text);margin-bottom:14px;font-size:14px;"><i class="fas fa-tint me-2" style="color:#DC2626;"></i>Tipo de Sangre</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;">
            <?php foreach($tiposSangre as $ts): $sel=($expData['id_sangre']??null)==$ts['id_sangre']; ?>
            <label style="cursor:pointer;">
                <input type="radio" name="id_sangre" value="<?= $ts['id_sangre'] ?>" <?= $sel?'checked':'' ?> style="display:none;" class="radio-sangre">
                <span class="chip-sangre <?= $sel?'chip-selected':'' ?>"><?= htmlspecialchars($ts['descripcion']) ?></span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="kpi-card">
        <div style="font-weight:700;color:var(--body-text);margin-bottom:14px;font-size:14px;"><i class="fas fa-notes-medical me-2" style="color:#1A56AB;"></i>Antecedentes</div>
        <textarea name="antecedentes" class="form-control" rows="4" placeholder="Antecedentes médicos relevantes..."><?= htmlspecialchars($expData['antecedentes']??'') ?></textarea>
    </div>

    <div class="kpi-card">
        <div style="font-weight:700;color:var(--body-text);margin-bottom:14px;font-size:14px;"><i class="fas fa-clipboard me-2" style="color:#1A56AB;"></i>Observaciones Generales</div>
        <textarea name="observaciones" class="form-control" rows="4" placeholder="Observaciones del expediente..."><?= htmlspecialchars($expData['observaciones']??'') ?></textarea>
    </div>
</div>

<!-- Columna derecha: alergias, enfermedades, medicamentos -->
<div style="display:flex;flex-direction:column;gap:16px;">
    <!-- Alergias -->
    <div class="kpi-card">
        <div style="font-weight:700;color:var(--body-text);margin-bottom:14px;font-size:14px;"><i class="fas fa-allergies me-2" style="color:#DC2626;"></i>Alergias</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
            <?php foreach($catAlergias as $al): $chk=in_array($al['id_alergia'],$idsAlerg); ?>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:6px 8px;border-radius:6px;border:1px solid <?= $chk?'#1A56AB':'var(--card-border)' ?>;background:<?= $chk?'rgba(26,86,171,.08)':'transparent' ?>;font-size:13px;transition:.15s;" class="check-item">
                <input type="checkbox" name="alergias[]" value="<?= $al['id_alergia'] ?>" <?= $chk?'checked':'' ?> style="accent-color:#1A56AB;">
                <?= htmlspecialchars($al['descripcion']) ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Enfermedades Sistémicas -->
    <div class="kpi-card">
        <div style="font-weight:700;color:var(--body-text);margin-bottom:14px;font-size:14px;"><i class="fas fa-heartbeat me-2" style="color:#F59E0B;"></i>Enfermedades Sistémicas</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
            <?php foreach($catEnferm as $ef): $chk=in_array($ef['id_enfermedad'],$idsEnferm); ?>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:6px 8px;border-radius:6px;border:1px solid <?= $chk?'#F59E0B':'var(--card-border)' ?>;background:<?= $chk?'rgba(245,158,11,.08)':'transparent' ?>;font-size:13px;transition:.15s;" class="check-item">
                <input type="checkbox" name="enfermedades[]" value="<?= $ef['id_enfermedad'] ?>" <?= $chk?'checked':'' ?> style="accent-color:#F59E0B;">
                <?= htmlspecialchars($ef['descripcion']) ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Medicamentos Actuales -->
    <div class="kpi-card">
        <div style="font-weight:700;color:var(--body-text);margin-bottom:14px;font-size:14px;"><i class="fas fa-pills me-2" style="color:#8B5CF6;"></i>Medicamentos Actuales</div>
        <div style="display:flex;flex-direction:column;gap:6px;">
            <?php foreach($catMeds as $med): $chk=in_array($med['id_medicamento'],$idsMeds); $dosisVal=''; foreach($medsPac as $mp) if($mp['id_medicamento']==$med['id_medicamento']){$dosisVal=$mp['dosis']??'';break;} ?>
            <div style="display:flex;align-items:center;gap:8px;padding:6px 8px;border-radius:6px;border:1px solid <?= $chk?'#8B5CF6':'var(--card-border)' ?>;background:<?= $chk?'rgba(139,92,246,.07)':'transparent' ?>;" class="check-item">
                <input type="checkbox" name="medicamentos_ids[]" value="<?= $med['id_medicamento'] ?>" <?= $chk?'checked':'' ?> style="accent-color:#8B5CF6;flex-shrink:0;">
                <span style="flex:1;font-size:13px;"><?= htmlspecialchars($med['descripcion']) ?></span>
                <input type="text" name="medicamentos_dosis[<?= $med['id_medicamento'] ?>]" value="<?= htmlspecialchars($dosisVal) ?>" placeholder="Dosis" class="form-control" style="width:100px;height:28px;font-size:12px;padding:2px 8px;">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>

<div style="margin-top:16px;display:flex;justify-content:flex-end;gap:10px;">
    <a href="?id=<?= $paciente['id_paciente'] ?>&tab=historial" class="btn-og-secondary">Cancelar</a>
    <button type="submit" class="btn-og-primary"><i class="fas fa-save me-1"></i>Guardar Datos Clínicos</button>
</div>
</form>

<!-- ── TAB: ODONTOGRAMA ──────────────────────────────────── -->
<?php elseif($tabActual==='odontograma'): ?>
<div class="kpi-card">
    <div style="font-weight:700;color:var(--body-text);margin-bottom:18px;font-size:14px;"><i class="fas fa-tooth me-2" style="color:#1A56AB;"></i>Odontograma — Notación FDI</div>
    <?php
    // Construir mapa pieza→condición desde BD
    $mapOdont=[];
    foreach($odontograma as $od) $mapOdont[$od['pieza_dental']]=$od;
    // Piezas FDI
    $filas=[
        ['sup-der',[18,17,16,15,14,13,12,11]],
        ['sup-izq',[21,22,23,24,25,26,27,28]],
        ['inf-izq',[31,32,33,34,35,36,37,38]],
        ['inf-der',[41,42,43,44,45,46,47,48]],
    ];
    $colores=['sano'=>'#E5E7EB','caries'=>'#DC2626','restaurado'=>'#1A56AB','ausente'=>'#374151','corona'=>'#F59E0B','extraccion'=>'#7C3AED'];
    ?>
    <div style="display:flex;flex-direction:column;gap:4px;align-items:center;">
    <?php foreach([['sup-der','sup-izq'],['inf-izq','inf-der']] as $par): ?>
    <div style="display:flex;gap:4px;justify-content:center;flex-wrap:wrap;">
        <?php foreach($par as $cuad): foreach($filas as [$k,$piezas]): if($k!==$cuad) continue; foreach($piezas as $n):
            $od=$mapOdont[$n]??null; $col=$od?$od['color_codigo']:$colores['sano']; ?>
        <div title="Pieza <?= $n ?><?= $od?': '.$od['condicion']:'' ?>"
             style="width:38px;height:38px;border-radius:6px;background:<?= $col ?>;border:2px solid <?= $od?'#1A56AB':'#DDE4EF' ?>;display:flex;flex-direction:column;align-items:center;justify-content:center;cursor:pointer;font-size:10px;font-weight:700;color:<?= $col===$colores['sano']?'#374151':'#fff' ?>;transition:.15s;"
             onclick="abrirPieza(<?= $n ?>,'<?= htmlspecialchars($od['condicion']??'') ?>')">
            <?= $n ?>
        </div>
        <?php endforeach; endforeach; endforeach; ?>
    </div>
    <div style="height:8px;"></div>
    <?php endforeach; ?>
    </div>

    <div style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;">
        <?php foreach($colores as $nombre=>$col): ?>
        <div style="display:flex;align-items:center;gap:5px;font-size:12px;">
            <div style="width:14px;height:14px;border-radius:3px;background:<?= $col ?>;border:1px solid #DDE4EF;"></div>
            <?= ucfirst($nombre) ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal pieza -->
<div id="modalPieza" style="display:none;position:fixed;inset:0;z-index:1060;align-items:center;justify-content:center;">
    <div style="position:absolute;inset:0;background:rgba(0,0,0,.45);" onclick="document.getElementById('modalPieza').style.display='none'"></div>
    <div style="position:relative;background:var(--card-bg);border-radius:14px;box-shadow:0 20px 60px rgba(0,0,0,.25);width:100%;max-width:420px;margin:16px;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--card-border);font-weight:700;color:var(--body-text);">Registrar condición — Pieza <span id="numPieza"></span></div>
        <form method="POST" action="<?= APP_URL ?>expedientes/guardarOdontograma">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="id_paciente" value="<?= $paciente['id_paciente'] ?>">
            <input type="hidden" name="pieza" id="inputPieza">
            <div style="padding:16px 20px;">
                <div class="mb-3"><label class="form-label">Condición</label>
                    <select name="condicion" id="selectCondicion" class="form-select">
                        <?php foreach($colores as $n=>$c): ?><option value="<?= $n ?>"><?= ucfirst($n) ?></option><?php endforeach; ?>
                    </select></div>
                <div class="mb-3"><label class="form-label">Descripción</label><input type="text" name="descripcion" class="form-control" placeholder="Detalles adicionales..."></div>
                <div class="mb-0"><label class="form-label">Color</label><input type="color" name="color" id="inputColor" class="form-control form-control-color" value="#DC2626"></div>
            </div>
            <div style="padding:12px 20px;border-top:1px solid var(--card-border);display:flex;justify-content:flex-end;gap:8px;">
                <button type="button" class="btn-og-secondary" onclick="document.getElementById('modalPieza').style.display='none'">Cancelar</button>
                <button type="submit" class="btn-og-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>
<script>
const colorMap={sano:'#E5E7EB',caries:'#DC2626',restaurado:'#1A56AB',ausente:'#374151',corona:'#F59E0B',extraccion:'#7C3AED'};
document.getElementById('selectCondicion').addEventListener('change',function(){
    document.getElementById('inputColor').value=colorMap[this.value]||'#DC2626';
});
function abrirPieza(n,cond){
    document.getElementById('numPieza').textContent=n;
    document.getElementById('inputPieza').value=n;
    if(cond) document.getElementById('selectCondicion').value=cond;
    document.getElementById('inputColor').value=colorMap[document.getElementById('selectCondicion').value]||'#DC2626';
    document.getElementById('modalPieza').style.display='flex';
}
</script>

<!-- ── TAB: FACTURAS ─────────────────────────────────────── -->
<?php elseif($tabActual==='facturas'): ?>
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);font-weight:600;color:var(--body-text);">Facturas del Paciente</div>
    <table class="tabla-og">
        <thead><tr><th>N° Factura</th><th>Fecha</th><th>Subtotal</th><th>ISV</th><th>Total</th><th>Método</th><th>Estado</th></tr></thead>
        <tbody>
        <?php if(empty($facturas)): ?>
        <tr><td colspan="7" style="text-align:center;padding:30px;color:#9CA3AF;">Sin facturas registradas</td></tr>
        <?php else: foreach($facturas as $f): ?>
        <tr>
            <td style="font-weight:600;color:#1A56AB;"><?= htmlspecialchars($f['numero_factura']) ?></td>
            <td><?= $f['fecha'] ?></td>
            <td>L. <?= number_format($f['subtotal'],2) ?></td>
            <td>L. <?= number_format($f['impuesto'],2) ?></td>
            <td style="font-weight:700;">L. <?= number_format($f['total'],2) ?></td>
            <td><?= ucfirst($f['metodo_pago']) ?></td>
            <td><span class="badge badge-<?= $f['estado'] ?>"><?= ucfirst($f['estado']) ?></span></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

</div></div>

<style>
.chip-sangre{display:inline-block;padding:5px 14px;border-radius:20px;border:1.5px solid #DDE4EF;font-size:13px;font-weight:600;cursor:pointer;transition:.15s;color:var(--body-text);}
.chip-sangre:hover,.chip-selected{background:#DC2626;border-color:#DC2626;color:#fff;}
.radio-sangre:checked+.chip-sangre{background:#DC2626;border-color:#DC2626;color:#fff;}
.check-item:hover{border-color:#1A56AB !important;}
[data-theme="dark"] .chip-sangre{border-color:#334155;color:#CBD5E1;}
</style>
<script>
document.querySelectorAll('.radio-sangre').forEach(r=>{
    r.addEventListener('change',()=>{
        document.querySelectorAll('.chip-sangre').forEach(c=>c.classList.remove('chip-selected'));
        r.nextElementSibling.classList.add('chip-selected');
    });
});
</script>
