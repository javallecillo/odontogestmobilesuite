<div><div style="padding:24px 28px;">
<div style="margin-bottom:16px;display:flex;gap:10px;align-items:center;">
    <a href="<?= APP_URL ?>reportes" class="btn-og-secondary"><i class="fas fa-arrow-left me-1"></i>Reportes</a>
    <button onclick="window.print()" class="btn-og-primary"><i class="fas fa-print me-1"></i>Imprimir / PDF</button>
</div>
<div class="kpi-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);display:flex;justify-content:space-between;align-items:center;">
        <span style="font-weight:600;color:var(--body-text);">Estado del Inventario</span>
        <span style="font-size:12px;color:#9CA3AF;"><?= count($datos) ?> productos · <?= date('d/m/Y H:i') ?></span>
    </div>
    <table class="tabla-og">
        <thead><tr><th>Producto</th><th>Stock</th><th>Mínimo</th><th>Precio Costo</th><th>Precio Venta</th><th>Estado</th></tr></thead>
        <tbody>
        <?php if(empty($datos)): ?>
        <tr><td colspan="6" style="text-align:center;padding:30px;color:#9CA3AF;">Sin productos</td></tr>
        <?php else: foreach($datos as $p): ?>
        <tr>
            <td style="font-weight:600;"><?= htmlspecialchars($p['nombre']) ?></td>
            <td style="<?= $p['stock']<=$p['stock_minimo']?'color:#DC2626;font-weight:700;':'' ?>"><?= $p['stock'] ?></td>
            <td><?= $p['stock_minimo'] ?></td>
            <td>L. <?= number_format($p['precio_costo'],2) ?></td>
            <td>L. <?= number_format($p['precio_venta'],2) ?></td>
            <td><span class="badge badge-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
</div></div>
