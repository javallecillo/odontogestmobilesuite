<?php $fi=htmlspecialchars($fecha_ini); $ff=htmlspecialchars($fecha_fin); ?>
<div><div style="padding:24px 28px;">
<div class="kpi-card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
        <div><a href="<?= APP_URL ?>reportes" class="btn-og-secondary"><i class="fas fa-arrow-left me-1"></i>Reportes</a></div>
        <div style="flex:1;min-width:140px;"><label class="form-label">Desde</label><input type="date" name="fecha_ini" class="form-control" value="<?= $fi ?>"></div>
        <div style="flex:1;min-width:140px;"><label class="form-label">Hasta</label><input type="date" name="fecha_fin" class="form-control" value="<?= $ff ?>"></div>
        <button type="submit" class="btn-og-primary"><i class="fas fa-chart-bar me-1"></i>Generar</button>
    </form>
</div>
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);font-weight:600;color:var(--body-text);">Ingresos del <?= $fi ?> al <?= $ff ?></div>
    <table class="tabla-og">
        <thead><tr><th>Fecha</th><th>Facturas</th><th>Subtotal</th><th>ISV</th><th>Total</th></tr></thead>
        <tbody>
        <?php if(empty($datos)): ?>
        <tr><td colspan="5" style="text-align:center;padding:30px;color:#9CA3AF;">Sin datos para el rango seleccionado</td></tr>
        <?php else: $tF=0;$tT=0;$tI=0; foreach($datos as $d): $tF+=$d['facturas'];$tT+=$d['total'];$tI+=$d['isv']; ?>
        <tr>
            <td><?= $d['fecha'] ?></td>
            <td><?= $d['facturas'] ?></td>
            <td>L. <?= number_format($d['total']-$d['isv'],2) ?></td>
            <td>L. <?= number_format($d['isv'],2) ?></td>
            <td style="font-weight:700;color:#16A34A;">L. <?= number_format($d['total'],2) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr style="background:rgba(26,86,171,.05);font-weight:700;">
            <td>TOTAL</td><td><?= $tF ?></td><td>L. <?= number_format($tT-$tI,2) ?></td><td>L. <?= number_format($tI,2) ?></td><td style="color:#16A34A;">L. <?= number_format($tT,2) ?></td>
        </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</div></div>
