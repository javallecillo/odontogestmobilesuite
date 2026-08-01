<?php $exportBase = APP_URL . 'reportes/exportar?tipo=inventario'; ?>
<div><div style="padding:24px 28px;">

<div style="margin-bottom:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;" class="export-btns">
    <a href="<?= APP_URL ?>reportes" class="btn-og-secondary"><i class="fas fa-arrow-left me-1"></i>Reportes</a>
    <a href="<?= $exportBase ?>&formato=excel" class="btn-og-success" title="Descargar como Excel profesional">
        <i class="fas fa-file-excel me-1"></i>Descargar Excel
    </a>
    <a href="<?= $exportBase ?>&formato=pdf" target="_blank" class="btn-og-secondary" title="Descargar como PDF">
        <i class="fas fa-file-pdf me-1"></i>Descargar PDF
    </a>
</div>

<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <span style="font-weight:600;color:var(--body-text);">Estado del Inventario</span>
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="font-size:12px;color:#9CA3AF;"><?= count($datos) ?> productos &middot; <?= date('d/m/Y H:i') ?></span>
            <a href="<?= $exportBase ?>&formato=excel" class="btn-og-success" style="font-size:.8rem;padding:5px 12px;">
                <i class="fas fa-file-excel"></i> .xlsx
            </a>
            <a href="<?= $exportBase ?>&formato=pdf" target="_blank" class="btn-og-secondary" style="font-size:.8rem;padding:5px 12px;">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>
    <div style="overflow-x:auto;">
    <table class="tabla-og">
        <thead><tr><th>Producto</th><th>Stock</th><th>Mínimo</th><th>Precio Costo</th><th>Precio Venta</th><th>Estado</th></tr></thead>
        <tbody>
        <?php if(empty($datos)): ?>
        <tr><td colspan="6" style="text-align:center;padding:30px;color:#9CA3AF;">Sin productos</td></tr>
        <?php else: foreach($datos as $p): ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($p['nombre']) ?></td>
            <td style="<?= $p['stock']<=$p['stock_minimo']?'color:#DC2626;font-weight:700;':'' ?>"><?= $p['stock'] ?><?= $p['stock']<=$p['stock_minimo'] ? ' <i class="fas fa-exclamation-triangle" style="font-size:10px;"></i>' : '' ?></td>
            <td><?= $p['stock_minimo'] ?></td>
            <td>L. <?= number_format($p['precio_costo'],2) ?></td>
            <td>L. <?= number_format($p['precio_venta'],2) ?></td>
            <td><span class="badge-og badge-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>
</div></div>
