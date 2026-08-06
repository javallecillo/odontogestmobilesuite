<?php $fi=htmlspecialchars($fecha_ini); $ff=htmlspecialchars($fecha_fin);
$exportBase = APP_URL . "reportes/exportar?tipo=citas&fecha_ini={$fecha_ini}&fecha_fin={$fecha_fin}"; ?>
<div><div style="padding:24px 28px;">

<!-- Filtros + botones de exportar -->
<div class="kpi-card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div><a href="<?= APP_URL ?>reportes" class="btn-og-secondary"><i class="fas fa-arrow-left me-1"></i>Reportes</a></div>
        <div style="flex:1;min-width:140px;"><label class="form-label">Desde</label><input type="date" name="fecha_ini" class="form-control" value="<?= $fi ?>"></div>
        <div style="flex:1;min-width:140px;"><label class="form-label">Hasta</label><input type="date" name="fecha_fin" class="form-control" value="<?= $ff ?>"></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;" class="export-btns">
            <button type="submit" class="btn-og-primary"><i class="fas fa-chart-bar me-1"></i>Generar</button>
            <a href="<?= $exportBase ?>&formato=excel" class="btn-og-success" title="Descargar Excel profesional">
                <i class="fas fa-file-excel me-1"></i>Excel
            </a>
            <a href="<?= $exportBase ?>&formato=pdf" target="_blank" class="btn-og-secondary" title="Descargar PDF profesional">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <span style="font-weight:600;color:var(--body-text);">Citas del <?= $fi ?> al <?= $ff ?></span>
        <div style="display:flex;gap:8px;">
            <a href="<?= $exportBase ?>&formato=excel" class="btn-og-success" style="font-size:.8rem;padding:5px 12px;" title="Descargar como Excel">
                <i class="fas fa-file-excel"></i> .xlsx
            </a>
            <a href="<?= $exportBase ?>&formato=pdf" target="_blank" class="btn-og-secondary" style="font-size:.8rem;padding:5px 12px;" title="Descargar como PDF">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>
    <div style="overflow-x:auto;">
    <table class="tabla-og">
        <thead><tr><th>Fecha</th><th>Total</th><th>Atendidas</th><th>Canceladas</th><th>Efectividad</th></tr></thead>
        <tbody>
        <?php if(empty($datos)): ?>
        <tr><td colspan="5" style="text-align:center;padding:30px;color:#9CA3AF;">Sin datos para el rango seleccionado</td></tr>
        <?php else: $totCitas=0;$totAt=0;$totCan=0; foreach($datos as $d): $totCitas+=$d['total'];$totAt+=$d['atendidas'];$totCan+=$d['canceladas']; $ef=$d['total']>0?round($d['atendidas']/$d['total']*100):0; ?>
        <tr>
            <td><?= $d['fecha'] ?></td>
            <td style="font-weight:600;"><?= $d['total'] ?></td>
            <td style="color:#16A34A;font-weight:600;"><?= $d['atendidas'] ?></td>
            <td style="color:#DC2626;"><?= $d['canceladas'] ?></td>
            <td>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="height:6px;border-radius:3px;background:#E5E7EB;flex:1;min-width:40px;">
                        <div style="height:100%;border-radius:3px;width:<?= $ef ?>%;background:<?= $ef>=80?'#16A34A':($ef>=50?'#F59E0B':'#DC2626') ?>;"></div>
                    </div>
                    <span style="font-size:12px;font-weight:600;white-space:nowrap;"><?= $ef ?>%</span>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr style="background:rgba(26,86,171,.05);font-weight:700;">
            <td>TOTAL</td><td><?= $totCitas ?></td><td style="color:#16A34A;"><?= $totAt ?></td><td style="color:#DC2626;"><?= $totCan ?></td>
            <td><?= $totCitas>0?round($totAt/$totCitas*100):0 ?>%</td>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

</div></div>
