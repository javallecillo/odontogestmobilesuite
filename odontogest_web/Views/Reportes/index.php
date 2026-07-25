<div><div style="padding:24px 28px;">
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;">
<?php $cards=[
    ['titulo'=>'Reporte de Citas',     'desc'=>'Análisis de citas por rango de fechas, estado y odontólogo.',   'icon'=>'fa-calendar-check', 'color'=>'blue',  'url'=>'reportes/citas'],
    ['titulo'=>'Reporte de Ingresos',  'desc'=>'Ingresos por período, método de pago e ISV desglosado.',          'icon'=>'fa-coins',          'color'=>'green', 'url'=>'reportes/ingresos'],
    ['titulo'=>'Reporte de Inventario','desc'=>'Estado actual del inventario, productos críticos y valor total.',  'icon'=>'fa-boxes-stacked',  'color'=>'amber', 'url'=>'reportes/inventario'],
];
foreach($cards as $c): ?>
<div class="kpi-card" style="display:flex;flex-direction:column;gap:16px;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div class="kpi-icon <?= $c['color'] ?>" style="width:48px;height:48px;border-radius:12px;"><i class="fas <?= $c['icon'] ?>"></i></div>
        <div style="font-weight:700;font-size:15px;color:var(--body-text);"><?= $c['titulo'] ?></div>
    </div>
    <p style="font-size:13px;color:#6B7280;margin:0;line-height:1.6;"><?= $c['desc'] ?></p>
    <a href="<?= APP_URL.$c['url'] ?>" class="btn-og-primary" style="text-align:center;"><i class="fas fa-chart-bar me-1"></i>Generar Reporte</a>
</div>
<?php endforeach; ?>
</div>
</div></div>
