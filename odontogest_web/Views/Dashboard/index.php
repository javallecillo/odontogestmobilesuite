<?php
// $m = métricas de DashboardModel::metricas()
$fmt    = fn(float $n) => number_format($n, 2, '.', ',');
$fmtInt = fn(int   $n) => number_format($n, 0, '.', ',');

$estadoBadge = [
    'pendiente'  => 'badge-pendiente',
    'confirmada' => 'badge-confirmada',
    'atendida'   => 'badge-atendida',
    'cancelada'  => 'badge-cancelada',
    'en_curso'   => 'badge-en_curso',
];
?>
<div style="padding:24px 28px;">

    <!-- ── Fila KPI ──────────────────────────────────────── -->
    <div class="row g-3 mb-4">

        <!-- Citas hoy -->
        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon blue"><i class="fas fa-calendar-check"></i></div>
                <div class="kpi-value"><?= $fmtInt((int)($m['citas_hoy'] ?? 0)) ?></div>
                <div class="kpi-label">Citas hoy</div>
                <div class="kpi-badge flat"><i class="fas fa-calendar-day me-1"></i><?= date('d/m/Y') ?></div>
            </div>
        </div>

        <!-- Pacientes activos -->
        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon green"><i class="fas fa-users"></i></div>
                <div class="kpi-value"><?= $fmtInt((int)($m['pacientes_activos'] ?? 0)) ?></div>
                <div class="kpi-label">Pacientes activos</div>
                <div class="kpi-badge up"><i class="fas fa-arrow-up me-1"></i>Total registrados</div>
            </div>
        </div>

        <!-- Facturas pendientes -->
        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon amber"><i class="fas fa-file-invoice-dollar"></i></div>
                <div class="kpi-value"><?= $fmtInt((int)($m['facturas_pendientes'] ?? 0)) ?></div>
                <div class="kpi-label">Facturas pendientes</div>
                <div class="kpi-badge down"><i class="fas fa-clock me-1"></i>Por cobrar</div>
            </div>
        </div>

        <!-- Stock bajo -->
        <div class="col-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon red"><i class="fas fa-box-open"></i></div>
                <div class="kpi-value"><?= $fmtInt((int)($m['stock_bajo'] ?? 0)) ?></div>
                <div class="kpi-label">Productos stock bajo</div>
                <div class="kpi-badge <?= (int)($m['stock_bajo'] ?? 0) > 0 ? 'down' : 'flat' ?>">
                    <i class="fas fa-<?= (int)($m['stock_bajo'] ?? 0) > 0 ? 'exclamation-triangle' : 'check' ?> me-1"></i>
                    <?= (int)($m['stock_bajo'] ?? 0) > 0 ? 'Requiere atención' : 'Sin alertas' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Fila principal: Citas del día + Resumen ─────── -->
    <div class="row g-3 mb-4">

        <!-- Citas del día (2/3) -->
        <div class="col-12 col-lg-8">
            <div class="kpi-card" style="padding:0;">
                <div style="padding:16px 20px;border-bottom:1px solid #DDE4EF;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <div style="font-size:.95rem;font-weight:700;color:#1A2940;">Citas del día</div>
                        <div style="font-size:.76rem;color:#6B7280;margin-top:1px;"><?= date('l, d \d\e F Y') ?></div>
                    </div>
                    <a href="<?= APP_URL ?>Agenda/index" class="btn-og-primary" style="font-size:.8rem;padding:7px 14px;">
                        <i class="fas fa-plus"></i> Nueva Cita
                    </a>
                </div>

                <?php if (empty($m['proximas_citas'])): ?>
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-calendar-times fa-2x mb-2 d-block" style="opacity:.25;"></i>
                    No hay citas registradas para hoy
                </div>
                <?php else: ?>
                <div style="overflow-x:auto;">
                    <table class="tabla-og">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Odontólogo</th>
                                <th>Servicio</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($m['proximas_citas'] as $c): ?>
                            <tr>
                                <td>
                                    <span style="font-weight:600;color:#1A56AB;">
                                        <?= htmlspecialchars(substr($c['hora'] ?? '', 0, 5)) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:600;color:#1A2940;">
                                        <?= htmlspecialchars($c['paciente'] ?? '—') ?>
                                    </div>
                                </td>
                                <td style="color:#6B7280;font-size:.84rem;">
                                    <?= htmlspecialchars($c['odontologo'] ?? '—') ?>
                                </td>
                                <td style="font-size:.84rem;color:#6B7280;">
                                    <?= htmlspecialchars($c['servicio'] ?? '—') ?>
                                </td>
                                <td>
                                    <?php $est = strtolower($c['estado'] ?? ''); ?>
                                    <span class="badge-og <?= $estadoBadge[$est] ?? 'badge-inactivo' ?>">
                                        <?= htmlspecialchars(ucfirst($est)) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Resumen de citas (1/3) -->
        <div class="col-12 col-lg-4">
            <div class="kpi-card h-100">
                <div style="font-size:.95rem;font-weight:700;color:#1A2940;margin-bottom:16px;">
                    Resumen de citas
                </div>
                <?php
                $resumen = [
                    ['label'=>'Pendientes',  'val'=>$m['graf_pendiente']  ?? 0, 'color'=>'#d97706', 'bg'=>'rgba(217,119,6,.12)'],
                    ['label'=>'Confirmadas', 'val'=>$m['graf_confirmada'] ?? 0, 'color'=>'#1A56AB', 'bg'=>'rgba(26,86,171,.12)'],
                    ['label'=>'Atendidas',   'val'=>$m['graf_atendida']   ?? 0, 'color'=>'#16a34a', 'bg'=>'rgba(22,163,74,.12)'],
                    ['label'=>'Canceladas',  'val'=>$m['graf_cancelada']  ?? 0, 'color'=>'#dc2626', 'bg'=>'rgba(220,38,38,.12)'],
                ];
                $totalDisplay = array_sum(array_column($resumen, 'val'));
                $totalDiv     = $totalDisplay ?: 1; // divisor para porcentajes, nunca 0
                foreach ($resumen as $r):
                    $pct = round(($r['val'] / $totalDiv) * 100);
                ?>
                <div style="margin-bottom:14px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                        <span style="font-size:.83rem;color:#374151;"><?= $r['label'] ?></span>
                        <span style="font-size:.83rem;font-weight:600;color:#1A2940;"><?= $r['val'] ?></span>
                    </div>
                    <div style="height:7px;background:#F0F3F8;border-radius:4px;overflow:hidden;">
                        <div style="height:100%;width:<?= $pct ?>%;background:<?= $r['color'] ?>;border-radius:4px;transition:width .5s;"></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div style="margin-top:20px;padding-top:14px;border-top:1px solid #DDE4EF;text-align:center;">
                    <div style="font-size:1.5rem;font-weight:800;color:#1A2940;"><?= $fmtInt($totalDisplay) ?></div>
                    <div style="font-size:.76rem;color:#6B7280;">citas totales registradas</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Fila inferior: Facturas + Inventario + Notificaciones ── -->
    <div class="row g-3">

        <!-- Últimas facturas -->
        <div class="col-12 col-lg-4">
            <div class="kpi-card" style="padding:0;">
                <div style="padding:14px 18px;border-bottom:1px solid #DDE4EF;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.9rem;font-weight:700;color:#1A2940;">
                        <i class="fas fa-file-invoice-dollar me-1" style="color:#1A56AB;"></i>
                        Últimas Facturas
                    </span>
                    <a href="<?= APP_URL ?>Facturacion/index" style="font-size:.75rem;color:#1A56AB;text-decoration:none;">Ver todas</a>
                </div>
                <div style="padding:12px 18px;">
                    <?php if (empty($m['ultimas_facturas'])): ?>
                    <p class="text-muted text-center small py-3 mb-0">Sin facturas recientes</p>
                    <?php else: ?>
                    <?php foreach (array_slice($m['ultimas_facturas'] ?? [], 0, 5) as $f): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F0F3F8;">
                        <div>
                            <div style="font-size:.82rem;font-weight:600;color:#1A2940;">
                                <?= htmlspecialchars($f['paciente'] ?? '—') ?>
                            </div>
                            <div style="font-size:.74rem;color:#6B7280;">
                                <?= htmlspecialchars($f['fecha'] ?? '') ?>
                            </div>
                        </div>
                        <div class="text-end">
                            <div style="font-size:.84rem;font-weight:700;color:#1A2940;">
                                L <?= $fmt((float)($f['total'] ?? 0)) ?>
                            </div>
                            <span class="badge-og badge-<?= strtolower($f['estado'] ?? 'pendiente') ?>">
                                <?= ucfirst($f['estado'] ?? '') ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Alertas inventario -->
        <div class="col-12 col-lg-4">
            <div class="kpi-card" style="padding:0;">
                <div style="padding:14px 18px;border-bottom:1px solid #DDE4EF;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.9rem;font-weight:700;color:#1A2940;">
                        <i class="fas fa-box-open me-1" style="color:#dc2626;"></i>
                        Alertas de Inventario
                    </span>
                    <a href="<?= APP_URL ?>Inventario/index" style="font-size:.75rem;color:#1A56AB;text-decoration:none;">Ver todo</a>
                </div>
                <div style="padding:12px 18px;">
                    <?php if (empty($m['alertas_inventario'])): ?>
                    <div class="text-center py-4 text-muted" style="font-size:.84rem;">
                        <i class="fas fa-check-circle fa-2x mb-2 d-block" style="color:#16a34a;opacity:.5;"></i>
                        Inventario en buen estado
                    </div>
                    <?php else: ?>
                    <?php foreach (array_slice($m['alertas_inventario'] ?? [], 0, 5) as $p): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F0F3F8;">
                        <div style="font-size:.82rem;font-weight:600;color:#1A2940;max-width:60%;">
                            <?= htmlspecialchars($p['nombre'] ?? '—') ?>
                        </div>
                        <div class="text-end">
                            <span style="font-size:.82rem;font-weight:700;color:#dc2626;"><?= (int)($p['stock'] ?? 0) ?> uds</span><br>
                            <span class="badge-og badge-critico" style="font-size:.68rem;">Bajo mínimo</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Acceso rápido -->
        <div class="col-12 col-lg-4">
            <div class="kpi-card">
                <div style="font-size:.9rem;font-weight:700;color:#1A2940;margin-bottom:14px;">
                    <i class="fas fa-bolt me-1" style="color:#1A56AB;"></i>Acceso Rápido
                </div>
                <div class="row g-2">
                    <?php $accesos = [
                        ['url'=>'Agenda/index',    'ico'=>'fas fa-calendar-plus',   'lbl'=>'Nueva Cita',     'color'=>'#1A56AB'],
                        ['url'=>'Pacientes/nuevo', 'ico'=>'fas fa-user-plus',        'lbl'=>'Nuevo Paciente', 'color'=>'#16a34a'],
                        ['url'=>'Facturacion/nuevo','ico'=>'fas fa-file-invoice',     'lbl'=>'Nueva Factura',  'color'=>'#d97706'],
                        ['url'=>'Inventario/index', 'ico'=>'fas fa-boxes-stacked',   'lbl'=>'Inventario',     'color'=>'#9333ea'],
                        ['url'=>'Pacientes/index',  'ico'=>'fas fa-users',            'lbl'=>'Pacientes',      'color'=>'#0891b2'],
                        ['url'=>'Reportes/index',   'ico'=>'fas fa-chart-bar',        'lbl'=>'Reportes',       'color'=>'#d97706'],
                    ]; ?>
                    <?php foreach ($accesos as $ac): ?>
                    <div class="col-6">
                        <a href="<?= APP_URL . $ac['url'] ?>"
                           style="display:flex;align-items:center;gap:10px;padding:10px 12px;
                                  background:#F5F7FB;border:1px solid #DDE4EF;border-radius:9px;
                                  text-decoration:none;transition:all .15s;color:#1A2940;"
                           onmouseenter="this.style.borderColor='<?= $ac['color'] ?>';this.style.background='#fff'"
                           onmouseleave="this.style.borderColor='#DDE4EF';this.style.background='#F5F7FB'">
                            <i class="<?= $ac['ico'] ?>" style="color:<?= $ac['color'] ?>;font-size:.95rem;width:20px;text-align:center;"></i>
                            <span style="font-size:.8rem;font-weight:600;"><?= $ac['lbl'] ?></span>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </div><!-- /row inferior -->
</div><!-- /container -->
