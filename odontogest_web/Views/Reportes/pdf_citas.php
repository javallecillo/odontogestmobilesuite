<?php
/**
 * Template PDF — Reporte de Citas
 * Página HTML standalone con print CSS profesional.
 * Se accede vía: reportes/exportar?tipo=citas&formato=pdf
 */
$fi   = htmlspecialchars($fi ?? date('Y-m-01'));
$ff   = htmlspecialchars($ff ?? date('Y-m-d'));
$ahora = date('d/m/Y H:i:s');
$totCitas = $totAt = $totCan = 0;
foreach ($datos as $d) { $totCitas += $d['total']; $totAt += $d['atendidas']; $totCan += $d['canceladas']; }
$efTotal = $totCitas > 0 ? round($totAt / $totCitas * 100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reporte de Citas — OdontoGest</title>
<style>
/* ── Reset ── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
html{font-size:13px;}
body{font-family:'Segoe UI',Arial,sans-serif;color:#1A2940;background:#fff;padding:0;}

/* ── No-print toolbar ── */
.toolbar{
    display:flex;align-items:center;gap:10px;
    padding:12px 24px;background:#1A56AB;color:#fff;
    position:sticky;top:0;z-index:100;
}
.toolbar h2{font-size:14px;font-weight:600;flex:1;}
.btn-print{
    background:#fff;color:#1A56AB;border:none;
    padding:7px 18px;border-radius:6px;font-weight:700;
    cursor:pointer;font-size:13px;display:flex;align-items:center;gap:6px;
}
.btn-print:hover{background:#E0EAFC;}
.btn-close{
    background:rgba(255,255,255,.15);color:#fff;border:none;
    padding:7px 14px;border-radius:6px;font-weight:600;
    cursor:pointer;font-size:13px;
}
.btn-close:hover{background:rgba(255,255,255,.25);}

/* ── Página imprimible ── */
.page{
    width:210mm;margin:10mm auto;padding:14mm 16mm 16mm;
    background:#fff;
}

/* ── Encabezado del documento ── */
.doc-header{
    display:flex;align-items:flex-start;justify-content:space-between;
    padding-bottom:12px;border-bottom:3px solid #1A56AB;margin-bottom:14px;
}
.doc-logo{
    width:48px;height:48px;border-radius:10px;
    background:#1A56AB;display:flex;align-items:center;justify-content:center;
    font-size:22px;font-weight:800;color:#fff;flex-shrink:0;
}
.doc-brand{margin-left:12px;}
.doc-brand .name{font-size:18px;font-weight:800;color:#1A56AB;line-height:1.2;}
.doc-brand .sub{font-size:11px;color:#6B7280;margin-top:2px;}
.doc-meta{text-align:right;font-size:11px;color:#6B7280;line-height:1.7;}
.doc-meta strong{color:#1A2940;font-weight:600;}

/* ── Título del reporte ── */
.report-title{
    background:#EEF3FC;border-radius:8px;
    padding:10px 14px;margin-bottom:14px;
    display:flex;align-items:center;gap:14px;
}
.report-title .icon{font-size:20px;}
.report-title h1{font-size:15px;font-weight:700;color:#1A2940;}
.report-title .period{font-size:11px;color:#6B7280;margin-top:2px;}

/* ── KPIs de resumen ── */
.kpis{display:flex;gap:10px;margin-bottom:14px;}
.kpi{
    flex:1;background:#F5F7FB;border-radius:8px;
    border:1px solid #DDE4EF;padding:10px 12px;text-align:center;
}
.kpi .val{font-size:22px;font-weight:800;color:#1A56AB;line-height:1;}
.kpi .lbl{font-size:10px;color:#6B7280;margin-top:3px;text-transform:uppercase;letter-spacing:.5px;}
.kpi.green .val{color:#16a34a;}
.kpi.red   .val{color:#dc2626;}
.kpi.amber .val{color:#d97706;}

/* ── Tabla ── */
table{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:14px;}
thead tr{background:#1A56AB;color:#fff;}
thead th{
    padding:9px 12px;text-align:left;
    font-size:10.5px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;
}
tbody tr{border-bottom:1px solid #DDE4EF;}
tbody tr:nth-child(even){background:#F9FAFB;}
tbody td{padding:8px 12px;vertical-align:middle;}
tbody tr:last-child{border-bottom:none;}
tfoot tr{background:#E8EDF5;font-weight:700;border-top:2px solid #1A56AB;}
tfoot td{padding:8px 12px;}

/* Barra de efectividad */
.bar-wrap{display:flex;align-items:center;gap:6px;}
.bar{height:6px;border-radius:3px;background:#E5E7EB;flex:1;min-width:40px;}
.bar-fill{height:100%;border-radius:3px;}

/* ── Firma/footer legal ── */
.doc-footer{
    margin-top:20px;padding-top:12px;border-top:1px solid #DDE4EF;
    display:flex;justify-content:space-between;align-items:flex-end;
    font-size:10px;color:#9CA3AF;
}
.signature-line{
    width:160px;border-top:1px solid #6B7280;
    padding-top:4px;text-align:center;font-size:10px;color:#6B7280;
}

/* ══ @media print ══ */
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

<!-- Toolbar (solo en pantalla) -->
<div class="toolbar">
    <h2>&#x1F4CB; Reporte de Citas — Vista Previa</h2>
    <button class="btn-print" onclick="window.print()">&#x1F5A8; Imprimir / Guardar PDF</button>
    <button class="btn-close" onclick="window.close()">&#x2715; Cerrar</button>
</div>

<div class="page">

    <!-- Encabezado del documento -->
    <div class="doc-header">
        <div style="display:flex;align-items:flex-start;">
            <div class="doc-logo">O</div>
            <div class="doc-brand">
                <div class="name">OdontoGest</div>
                <div class="sub">Sistema de Gestión Odontológica<br>Clínica Dental Paz &mdash; Honduras</div>
            </div>
        </div>
        <div class="doc-meta">
            <strong>Reporte de Citas</strong><br>
            Período: <?= $fi ?> al <?= $ff ?><br>
            Emitido: <?= $ahora ?><br>
            Documento N°: <?= strtoupper(substr(md5($ahora), 0, 8)) ?>
        </div>
    </div>

    <!-- Título -->
    <div class="report-title">
        <span class="icon">&#x1F4C5;</span>
        <div>
            <h1>Reporte de Citas por Rango de Fechas</h1>
            <div class="period">Período analizado: <?= $fi ?> al <?= $ff ?></div>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpis">
        <div class="kpi"><div class="val"><?= $totCitas ?></div><div class="lbl">Total Citas</div></div>
        <div class="kpi green"><div class="val"><?= $totAt ?></div><div class="lbl">Atendidas</div></div>
        <div class="kpi red"><div class="val"><?= $totCan ?></div><div class="lbl">Canceladas</div></div>
        <div class="kpi amber"><div class="val"><?= $efTotal ?>%</div><div class="lbl">Efectividad</div></div>
    </div>

    <!-- Tabla de datos -->
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Total</th>
                <th>Atendidas</th>
                <th>Canceladas</th>
                <th style="min-width:120px;">Efectividad</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($datos)): ?>
            <tr><td colspan="5" style="text-align:center;padding:20px;color:#9CA3AF;">Sin datos para el rango seleccionado.</td></tr>
        <?php else: foreach ($datos as $d): $ef = $d['total'] > 0 ? round($d['atendidas'] / $d['total'] * 100) : 0;
                $color = $ef >= 80 ? '#16a34a' : ($ef >= 50 ? '#d97706' : '#dc2626'); ?>
            <tr>
                <td><?= htmlspecialchars($d['fecha']) ?></td>
                <td style="font-weight:600;"><?= $d['total'] ?></td>
                <td style="color:#16a34a;font-weight:600;"><?= $d['atendidas'] ?></td>
                <td style="color:#dc2626;"><?= $d['canceladas'] ?></td>
                <td>
                    <div class="bar-wrap">
                        <div class="bar"><div class="bar-fill" style="width:<?= $ef ?>%;background:<?= $color ?>;"></div></div>
                        <span style="font-weight:700;color:<?= $color ?>;white-space:nowrap;"><?= $ef ?>%</span>
                    </div>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL PERÍODO</td>
                <td><?= $totCitas ?></td>
                <td style="color:#16a34a;"><?= $totAt ?></td>
                <td style="color:#dc2626;"><?= $totCan ?></td>
                <td style="color:<?= $efTotal >= 80 ? '#16a34a' : ($efTotal >= 50 ? '#d97706' : '#dc2626') ?>;"><?= $efTotal ?>%</td>
            </tr>
        </tfoot>
    </table>

    <!-- Notas legales y firma -->
    <div class="doc-footer">
        <div>
            <div>Este documento es de uso interno y confidencial.</div>
            <div>OdontoGest v<?= APP_VERSION ?> &mdash; <?= date('Y') ?> Todos los derechos reservados.</div>
        </div>
        <div style="text-align:center;">
            <div class="signature-line">Responsable del Reporte</div>
        </div>
    </div>

</div><!-- /page -->

<script>
// Auto-print al cargar (comportamiento de "descarga" de PDF)
window.addEventListener('load', function() {
    // Pequeño delay para que el navegador termine de renderizar
    setTimeout(function(){ window.print(); }, 600);
});
</script>
</body>
</html>
