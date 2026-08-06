<?php
/**
 * Template PDF — Reporte de Ingresos
 */
$fi   = htmlspecialchars($fi ?? date('Y-m-01'));
$ff   = htmlspecialchars($ff ?? date('Y-m-d'));
$ahora = date('d/m/Y H:i:s');
$tF = $tSub = $tIsv = $tTot = 0;
foreach ($datos as $d) {
    $tF   += $d['facturas'];
    $tSub += ($d['total'] - $d['isv']);
    $tIsv += $d['isv'];
    $tTot += $d['total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reporte de Ingresos — OdontoGest</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{font-size:13px;}
body{font-family:'Segoe UI',Arial,sans-serif;color:#1A2940;background:#fff;}

.toolbar{display:flex;align-items:center;gap:10px;padding:12px 24px;background:#16a34a;color:#fff;position:sticky;top:0;z-index:100;}
.toolbar h2{font-size:14px;font-weight:600;flex:1;}
.btn-print{background:#fff;color:#16a34a;border:none;padding:7px 18px;border-radius:6px;font-weight:700;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:6px;}
.btn-print:hover{background:#DCFCE7;}
.btn-close{background:rgba(255,255,255,.15);color:#fff;border:none;padding:7px 14px;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px;}
.btn-close:hover{background:rgba(255,255,255,.25);}

.page{width:210mm;margin:10mm auto;padding:14mm 16mm 16mm;background:#fff;}

.doc-header{display:flex;align-items:flex-start;justify-content:space-between;padding-bottom:12px;border-bottom:3px solid #16a34a;margin-bottom:14px;}
.doc-logo{width:48px;height:48px;border-radius:10px;background:#16a34a;display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;color:#fff;flex-shrink:0;}
.doc-brand{margin-left:12px;}
.doc-brand .name{font-size:18px;font-weight:800;color:#16a34a;line-height:1.2;}
.doc-brand .sub{font-size:11px;color:#6B7280;margin-top:2px;}
.doc-meta{text-align:right;font-size:11px;color:#6B7280;line-height:1.7;}
.doc-meta strong{color:#1A2940;font-weight:600;}

.report-title{background:#F0FDF4;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;gap:14px;}
.report-title .icon{font-size:20px;}
.report-title h1{font-size:15px;font-weight:700;color:#1A2940;}
.report-title .period{font-size:11px;color:#6B7280;margin-top:2px;}

.kpis{display:flex;gap:10px;margin-bottom:14px;}
.kpi{flex:1;background:#F5F7FB;border-radius:8px;border:1px solid #DDE4EF;padding:10px 12px;text-align:center;}
.kpi .val{font-size:18px;font-weight:800;color:#16a34a;line-height:1;}
.kpi .lbl{font-size:10px;color:#6B7280;margin-top:3px;text-transform:uppercase;letter-spacing:.5px;}

/* ISV nota legal */
.isv-note{background:#FFF7ED;border:1px solid #FED7AA;border-radius:6px;padding:7px 12px;font-size:11px;color:#92400E;margin-bottom:12px;}

table{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:14px;}
thead tr{background:#16a34a;color:#fff;}
thead th{padding:9px 12px;text-align:left;font-size:10.5px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;}
thead th:not(:first-child){text-align:right;}
tbody tr{border-bottom:1px solid #DDE4EF;}
tbody tr:nth-child(even){background:#F9FAFB;}
tbody td{padding:8px 12px;vertical-align:middle;}
tbody td:not(:first-child){text-align:right;font-variant-numeric:tabular-nums;}
tfoot tr{background:#DCFCE7;font-weight:700;border-top:2px solid #16a34a;}
tfoot td{padding:8px 12px;}
tfoot td:not(:first-child){text-align:right;}

.doc-footer{margin-top:20px;padding-top:12px;border-top:1px solid #DDE4EF;display:flex;justify-content:space-between;align-items:flex-end;font-size:10px;color:#9CA3AF;}
.signature-line{width:160px;border-top:1px solid #6B7280;padding-top:4px;text-align:center;font-size:10px;color:#6B7280;}

@media print {
    .toolbar{display:none!important;}
    html{font-size:12px;}
    .page{width:100%;margin:0;padding:10mm 12mm;box-shadow:none;}
    @page{size:A4 portrait;margin:0;}
    tbody tr{page-break-inside:avoid;}
}
</style>
</head>
<body>

<div class="toolbar">
    <h2>&#x1F4B0; Reporte de Ingresos — Vista Previa</h2>
    <button class="btn-print" onclick="window.print()">&#x1F5A8; Imprimir / Guardar PDF</button>
    <button class="btn-close" onclick="window.close()">&#x2715; Cerrar</button>
</div>

<div class="page">
    <div class="doc-header">
        <div style="display:flex;align-items:flex-start;">
            <div class="doc-logo">O</div>
            <div class="doc-brand">
                <div class="name">OdontoGest</div>
                <div class="sub">Sistema de Gestión Odontológica<br>Clínica Dental Ortonova &mdash; Honduras</div>
            </div>
        </div>
        <div class="doc-meta">
            <strong>Reporte de Ingresos</strong><br>
            Período: <?= $fi ?> al <?= $ff ?><br>
            Emitido: <?= $ahora ?><br>
            Documento N°: <?= strtoupper(substr(md5($ahora), 0, 8)) ?>
        </div>
    </div>

    <div class="report-title">
        
        <div>
            <h1>Reporte de Ingresos por Período</h1>
            <div class="period">Moneda: Lempiras (L.) &mdash; ISV incluido &mdash; Período: <?= $fi ?> al <?= $ff ?></div>
        </div>
    </div>

    <div class="kpis">
        <div class="kpi"><div class="val"><?= $tF ?></div><div class="lbl">Facturas</div></div>
        <div class="kpi"><div class="val">L. <?= number_format($tSub, 2) ?></div><div class="lbl">Subtotal</div></div>
        <div class="kpi"><div class="val">L. <?= number_format($tIsv, 2) ?></div><div class="lbl">ISV Total</div></div>
        <div class="kpi"><div class="val">L. <?= number_format($tTot, 2) ?></div><div class="lbl">Ingreso Total</div></div>
    </div>

    <div class="isv-note">
        &#x26A0; Nota Legal: Los montos de ISV se calculan conforme a la Ley del Impuesto Sobre Ventas (Decreto 24-98) de Honduras.
        Las facturas anuladas no se incluyen en este reporte.
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Facturas</th>
                <th>Subtotal (L.)</th>
                <th>ISV (L.)</th>
                <th>Total (L.)</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($datos)): ?>
            <tr><td colspan="5" style="text-align:center;padding:20px;color:#9CA3AF;">Sin datos para el rango seleccionado.</td></tr>
        <?php else: foreach ($datos as $d): $sub = $d['total'] - $d['isv']; ?>
            <tr>
                <td><?= htmlspecialchars($d['fecha']) ?></td>
                <td><?= $d['facturas'] ?></td>
                <td><?= number_format($sub, 2) ?></td>
                <td><?= number_format($d['isv'], 2) ?></td>
                <td style="font-weight:700;color:#16a34a;"><?= number_format($d['total'], 2) ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td>TOTALES DEL PERÍODO</td>
                <td><?= $tF ?></td>
                <td>L. <?= number_format($tSub, 2) ?></td>
                <td>L. <?= number_format($tIsv, 2) ?></td>
                <td style="color:#16a34a;">L. <?= number_format($tTot, 2) ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="doc-footer">
        <div>
            <div>Documento generado por OdontoGest v<?= APP_VERSION ?>. Información confidencial y de uso interno.</div>
            <div>&copy; <?= date('Y') ?> Clínica Dental Ortonova — Todos los derechos reservados.</div>
        </div>
        <div style="text-align:center;">
            <div class="signature-line">Responsable Financiero</div>
        </div>
    </div>
</div>

<script>window.addEventListener('load',function(){setTimeout(function(){window.print();},600);});</script>
</body>
</html>
