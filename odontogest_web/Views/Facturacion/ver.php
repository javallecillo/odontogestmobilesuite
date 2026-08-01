<?php $csrf = Csrf::token(); ?>
<div><div style="padding:24px 28px;">

<div style="margin-bottom:20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <a href="<?= APP_URL ?>facturacion" class="btn-og-secondary"><i class="fas fa-arrow-left me-1"></i>Facturación</a>
    <button onclick="window.print()" class="btn-og-primary"><i class="fas fa-print me-1"></i>Imprimir</button>
</div>

<!-- Cabecera de la factura -->
<div class="kpi-card" style="margin-bottom:20px;padding:0;overflow:hidden;">
    <div style="background:var(--og-primary);color:#fff;padding:18px 24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <div style="font-size:20px;font-weight:700;">Factura #<?= htmlspecialchars($factura['numero_factura']) ?></div>
            <div style="font-size:13px;opacity:.85;margin-top:4px;">Emitida el <?= date('d/m/Y', strtotime($factura['fecha_emision'])) ?></div>
        </div>
        <?php
        $estadoColor = ['emitida'=>'#F59E0B','pagada'=>'#16A34A','anulada'=>'#DC2626'];
        $color = $estadoColor[$factura['estado']] ?? '#6B7280';
        ?>
        <div style="background:<?= $color ?>;color:#fff;padding:6px 18px;border-radius:20px;font-weight:700;font-size:13px;">
            <?= strtoupper($factura['estado']) ?>
        </div>
    </div>
    <div style="padding:20px 24px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div>
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#6B7280;margin-bottom:6px;">Paciente</div>
            <div style="font-weight:600;color:var(--body-text);"><?= htmlspecialchars($factura['paciente']) ?></div>
            <?php if($factura['rtn']): ?><div style="font-size:13px;color:#6B7280;">RTN: <?= htmlspecialchars($factura['rtn']) ?></div><?php endif; ?>
            <?php if($factura['telefono']): ?><div style="font-size:13px;color:#6B7280;"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($factura['telefono']) ?></div><?php endif; ?>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:#6B7280;margin-bottom:6px;">Pago</div>
            <div style="font-weight:600;color:var(--body-text);"><?= ucfirst($factura['metodo_pago'] ?? '—') ?></div>
            <div style="font-size:13px;color:#6B7280;">Tasa ISV: <?= $factura['tasa_impuesto'] ?? 15 ?>%</div>
        </div>
    </div>
</div>

<!-- Detalle de ítems -->
<div class="kpi-card" style="padding:0;overflow:hidden;margin-bottom:16px;">
    <div style="padding:12px 20px;border-bottom:1px solid var(--card-border);font-weight:600;color:var(--body-text);">
        Detalle de Servicios
    </div>
    <div style="overflow-x:auto;">
    <table class="tabla-og">
        <thead>
            <tr><th>#</th><th>Descripción</th><th style="text-align:right;">Cant.</th><th style="text-align:right;">P. Unitario</th><th style="text-align:right;">Subtotal</th></tr>
        </thead>
        <tbody>
        <?php if (empty($items)): ?>
            <tr><td colspan="5" style="text-align:center;color:#9CA3AF;padding:20px;">Sin ítems registrados.</td></tr>
        <?php else: foreach ($items as $i => $item): ?>
            <tr>
                <td style="color:#9CA3AF;"><?= $i+1 ?></td>
                <td><?= htmlspecialchars($item['descripcion'] ?? $item['nombre_servicio'] ?? '—') ?></td>
                <td style="text-align:right;"><?= $item['cantidad'] ?? 1 ?></td>
                <td style="text-align:right;font-variant-numeric:tabular-nums;">L. <?= number_format($item['precio_unitario'] ?? 0, 2) ?></td>
                <td style="text-align:right;font-weight:600;font-variant-numeric:tabular-nums;">L. <?= number_format($item['total_linea'] ?? $item['subtotal'] ?? 0, 2) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Totales -->
<div style="display:flex;justify-content:flex-end;">
<div class="kpi-card" style="min-width:280px;">
    <?php
    $rows_tot = [
        ['Subtotal',  'L. ' . number_format($factura['subtotal'] ?? 0, 2), false],
        ['Descuento', 'L. ' . number_format($factura['descuento'] ?? 0, 2), false],
        ['ISV (' . ($factura['tasa_impuesto'] ?? 15) . '%)', 'L. ' . number_format($factura['impuesto'] ?? 0, 2), false],
        ['TOTAL', 'L. ' . number_format($factura['total'] ?? 0, 2), true],
    ];
    ?>
    <?php foreach($rows_tot as [$label, $valor, $bold]): ?>
    <div style="display:flex;justify-content:space-between;padding:10px 20px;border-bottom:1px solid var(--card-border);
                font-weight:<?= $bold ? '700' : '400' ?>;
                font-size:<?= $bold ? '16px' : '14px' ?>;
                color:<?= $bold ? 'var(--og-primary)' : 'var(--body-text)' ?>;">
        <span><?= $label ?></span><span style="font-variant-numeric:tabular-nums;"><?= $valor ?></span>
    </div>
    <?php endforeach; ?>
</div>
</div>

<?php if ($factura['estado'] === 'emitida'): ?>
<div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
    <form method="POST" action="<?= APP_URL ?>facturacion/marcarPagada" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="id_factura" value="<?= $factura['id_factura'] ?>">
        <button type="button" class="btn-og-success" onclick="confirmarPagada(this.closest('form'))">
            <i class="fas fa-check me-1"></i>Marcar como Pagada
        </button>
    </form>
<script>
async function confirmarPagada(form) {
    const ok = await OgSwal.confirm({
        title: '¿Marcar como pagada?',
        text: 'Se registrará el pago de esta factura.',
        confirmText: 'Confirmar pago',
        icon: 'question',
    });
    if (ok) form.submit();
}
</script>
    <?php if (Auth::can('anular_factura') || Auth::rol() === 'Administrador'): ?>
    <button class="btn-og-danger" onclick="document.getElementById('frm-anular').style.display='block'">
        <i class="fas fa-ban me-1"></i>Anular Factura
    </button>
    <form id="frm-anular" method="POST" action="<?= APP_URL ?>facturacion/anular" style="display:none;margin-top:12px;width:100%;">
        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
        <input type="hidden" name="id_factura" value="<?= $factura['id_factura'] ?>">
        <div style="display:flex;gap:8px;max-width:500px;">
            <input type="text" name="motivo" class="form-control" placeholder="Motivo de anulación (requerido)" required>
            <button type="submit" class="btn-og-danger">Confirmar Anulación</button>
        </div>
    </form>
    <?php endif; ?>
</div>
<?php endif; ?>

</div></div>
