<?php
/**
 * Template PDF — Reporte de Inventario
 */
$ahora    = date('d/m/Y H:i:s');
$total    = count($datos);
$criticos = array_filter($datos, fn($p) => $p['stock'] <= $p['stock_minimo']);
$valorTotal = array_sum(array_map(fn($p) => $p['precio_costo'] * $p['stock'], $datos));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reporte de Inventario — OdontoGest</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{font-size:13px;}
body{font-family:'Segoe UI',Arial,sans-serif;color:#1A2940;background:#fff;}

.toolbar{display:flex;align-items:center;gap:10px;padding:12px 24px;background:#d97706;color:#fff;position:sticky;top:0;z-index:100;}
.toolbar h2{font-size:14px;font-weight:600;flex:1;}
.btn-print{background:#fff;color:#d97706;border:none;padding:7px 18px;border-radius:6px;font-weight:700;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:6px;}
.btn-print:hover{background:#FEF9C3;}
.btn-close{background:rgba(255,255,255,.15);color:#fff;border:none;padding:7px 14px;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px;}

.page{width:210mm;margin:10mm auto;padding:14mm 16mm 16mm;background:#fff;}

.doc-header{display:flex;align-items:flex-start;justify-content:space-between;padding-bottom:12px;border-bottom:3px solid #d97706;margin-bottom:14px;}
.doc-logo{width:48px;height:48px;border-radius:10px;background:#d97706;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;}
.doc-brand{margin-left:12px;}
.doc-brand .name{font-size:18px;font-weight:800;color:#d97706;line-height:1.2;}
.doc-brand .sub{font-size:11px;color:#6B7280;margin-top:2px;}
.doc-meta{text-align:right;font-size:11px;color:#6B7280;line-height:1.7;}
.doc-meta strong{color:#1A2940;font-weight:600;}

.report-title{background:#FFFBEB;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;gap:14px;}
.report-title .icon{font-size:20px;}
.report-title h1{font-size:15px;font-weight:700;color:#1A2940;}
.report-title .period{font-size:11px;color:#6B7280;margin-top:2px;}

.kpis{display:flex;gap:10px;margin-bottom:14px;}
.kpi{flex:1;background:#F5F7FB;border-radius:8px;border:1px solid #DDE4EF;padding:10px 12px;text-align:center;}
.kpi .val{font-size:20px;font-weight:800;color:#d97706;line-height:1;}
.kpi .lbl{font-size:10px;color:#6B7280;margin-top:3px;text-transform:uppercase;letter-spacing:.5px;}
.kpi.red .val{color:#dc2626;}
.kpi.blue .val{color:#1A56AB;}

.criticos-note{background:#FEF2F2;border:1px solid #FECACA;border-radius:6px;padding:7px 12px;font-size:11px;color:#991B1B;margin-bottom:12px;}

table{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:14px;}
thead tr{background:#d97706;color:#fff;}
thead th{padding:9px 12px;text-align:left;font-size:10.5px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;}
thead th:nth-child(2),thead th:nth-child(3),thead th:nth-child(4),thead th:nth-child(5){text-align:right;}
tbody tr{border-bottom:1px solid #DDE4EF;}
tbody tr:nth-child(even){background:#F9FAFB;}
tbody tr.critico{background:#FFF1F2;}
tbody td{padding:7px 12px;vertical-align:middle;}
tbody td:nth-child(2),tbody td:nth-child(3),tbody td:nth-child(4),tbody td:nth-child(5){text-align:right;font-variant-numeric:tabular-nums;}
tfoot tr{background:#FEF3C7;font-weight:700;border-top:2px solid #d97706;}
tfoot td{padding:8px 12px;}

.badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;}
.badge-activo  {background:rgba(22,163,74,.12);color:#16a34a;}
.badge-inactivo{background:rgba(107,114,128,.12);color:#6b7280;}
.badge-agotado {background:rgba(220,38,38,.12);color:#dc2626;}
.stock-warn    {color:#dc2626;font-weight:700;}

.doc-footer{margin-top:20px;padding-top:12px;border-top:1px solid #DDE4EF;display:flex;justify-content:space-between;align-items:flex-end;font-size:10px;color:#9CA3AF;}
.signature-line{width:160px;border-top:1px solid #6B7280;padding-top:4px;text-align:center;font-size:10px;color:#6B7280;}

@media print {
    .toolbar{display:none!important;}
    html{font-size:11px;}
    .page{width:100%;margin:0;padding:8mm 10mm;box-shadow:none;}
    @page{size:A4 portrait;margin:0;}
    tbody tr{page-break-inside:avoid;}
}
</style>
</head>
<body>

<div class="toolbar">
    <h2>&#x1F4E6; Reporte de Inventario — Vista Previa</h2>
    <button class="btn-print" onclick="window.print()">&#x1F5A8; Imprimir / Guardar PDF</button>
    <button class="btn-close" onclick="window.close()">&#x2715; Cerrar</button>
</div>

<div class="page">
    <div class="doc-header">
        <div style="display:flex;align-items:flex-start;">
            <div class="doc-logo">O</div>
            <div class="doc-brand">
                <div class="name">OdontoGest</div>
                <div class="sub">Sistema de Gestión Odontológica<br>Clínica Dental Paz &mdash; Honduras</div>
            </div>
        </div>
        <div class="doc-meta">
            <strong>Reporte de Inventario</strong><br>
            Estado al: <?= $ahora ?><br>
            Total productos: <?= $total ?><br>
            Documento N°: <?= strtoupper(substr(md5($ahora), 0, 8)) ?>
        </div>
    </div>

    <div class="report-title">
        <span class="icon">&#x1F4E6;</span>
        <div>
            <h1>Estado Actual del Inventario</h1>
            <div class="period">Generado el <?= $ahora ?> &mdash; <?= $total ?> productos registrados</div>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi"><div class="val"><?= $total ?></div><div class="lbl">Total Productos</div></div>
        <div class="kpi red"><div class="val"><?= count($criticos) ?></div><div class="lbl">Stock Crítico</div></div>
        <div class="kpi blue"><div class="val">L. <?= number_format($valorTotal, 2) ?></div><div class="lbl">Valor en Inventario</div></div>
    </div>

    <?php if (count($criticos) > 0): ?>
    <div class="criticos-note">
        &#x26A0; Alerta: <?= count($criticos) ?> producto(s) están en o por debajo del stock mínimo establecido. Se recomienda reabastecimiento inmediato.
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Costo (L.)</th>
                <th>Venta (L.)</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($datos)): ?>
            <tr><td colspan="6" style="text-align:center;padding:20px;color:#9CA3AF;">Sin productos registrados.</td></tr>
        <?php else: foreach ($datos as $p): $esCritico = $p['stock'] <= $p['stock_minimo']; ?>
            <tr class="<?= $esCritico ? 'critico' : '' ?>">
                <td style="font-weight:600;"><?= htmlspecialchars($p['nombre']) ?></td>
                <td class="<?= $esCritico ? 'stock-warn' : '' ?>"><?= $p['stock'] ?><?= $esCritico ? ' &#x26A0;' : '' ?></td>
                <td><?= $p['stock_minimo'] ?></td>
                <td><?= number_format($p['precio_costo'], 2) ?></td>
                <td><?= number_format($p['precio_venta'], 2) ?></td>
                <td><span class="badge badge-<?= $p['estado'] ?>"><?= ucfirst($p['estado']) ?></span></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td>RESUMEN: <?= $total ?> productos</td>
                <td colspan="2">Críticos: <?= count($criticos) ?></td>
                <td colspan="3">Valor total: L. <?= number_format($valorTotal, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="doc-footer">
        <div>
            <div>Documento generado por OdontoGest v<?= APP_VERSION ?>. Información confidencial y de uso interno.</div>
            <div>&copy; <?= date('Y') ?> Clínica Dental Paz — Todos los derechos reservados.</div>
        </div>
        <div style="text-align:center;">
            <div class="signature-line">Responsable de Inventario</div>
        </div>
    </div>
</div>

<script>window.addEventListener('load',function(){setTimeout(function(){window.print();},600);});</script>
</body>
</html>
