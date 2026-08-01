<?php $csrf = Csrf::token(); ?>
<div><div style="padding:24px 28px;">

<div style="margin-bottom:20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <a href="<?= APP_URL ?>pacientes" class="btn-og-secondary"><i class="fas fa-arrow-left me-1"></i>Pacientes</a>
    <a href="<?= APP_URL ?>expedientes/ver?id=<?= $paciente['id_paciente'] ?>" class="btn-og-primary">
        <i class="fas fa-folder-open me-1"></i>Ver Expediente Clínico
    </a>
</div>

<!-- Encabezado del paciente -->
<div class="kpi-card" style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--og-primary);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:#fff;flex-shrink:0;">
            <?= strtoupper(mb_substr($paciente['nombre'],0,1) . mb_substr($paciente['apellidos'],0,1)) ?>
        </div>
        <div style="flex:1;">
            <h2 style="font-size:22px;font-weight:700;color:var(--body-text);margin:0 0 4px;">
                <?= htmlspecialchars($paciente['nombre_completo']) ?>
            </h2>
            <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:13px;color:#6B7280;">
                <?php if($paciente['dni']): ?><span><i class="fas fa-id-card me-1"></i><?= htmlspecialchars($paciente['dni']) ?></span><?php endif; ?>
                <?php if($paciente['telefono']): ?><span><i class="fas fa-phone me-1"></i><?= htmlspecialchars($paciente['telefono']) ?></span><?php endif; ?>
                <?php if($paciente['correo']): ?><span><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($paciente['correo']) ?></span><?php endif; ?>
                <?php if($paciente['grupo_sangre']): ?><span><i class="fas fa-tint me-1 text-danger"></i><?= htmlspecialchars($paciente['grupo_sangre']) ?></span><?php endif; ?>
            </div>
        </div>
        <span class="badge-og badge-<?= $paciente['estado'] ?>"><?= ucfirst($paciente['estado']) ?></span>
    </div>
</div>

<!-- Datos personales -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
<div class="kpi-card">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);font-weight:600;color:var(--body-text);">
        <i class="fas fa-user me-2" style="color:var(--og-primary);"></i>Datos Personales
    </div>
    <div style="padding:16px 20px;">
        <?php
        $campos = [
            'Nombre completo' => $paciente['nombre_completo'],
            'DNI'             => $paciente['dni'],
            'RTN'             => $paciente['rtn'],
            'Fecha nacimiento'=> $paciente['fecha_nacimiento'] ? date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) : null,
            'Sexo'            => $paciente['sexo'] ? ucfirst($paciente['sexo']) : null,
            'Estado civil'    => $paciente['estado_civil'] ? ucfirst($paciente['estado_civil']) : null,
            'Ocupación'       => $paciente['ocupacion'],
            'Total citas'     => $paciente['total_citas'] ?? '—',
        ];
        foreach ($campos as $label => $valor): if(!$valor) continue; ?>
        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--card-border);font-size:14px;">
            <span style="color:#6B7280;"><?= $label ?></span>
            <span style="font-weight:500;color:var(--body-text);"><?= htmlspecialchars((string)$valor) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="kpi-card">
    <div style="padding:14px 20px;border-bottom:1px solid var(--card-border);font-weight:600;color:var(--body-text);">
        <i class="fas fa-address-book me-2" style="color:var(--og-primary);"></i>Contacto
    </div>
    <div style="padding:16px 20px;">
        <?php
        $contacto = [
            'Teléfono'           => $paciente['telefono'],
            'Correo'             => $paciente['correo'],
            'Dirección'          => $paciente['direccion'],
            'Emergencia (tel.)'  => $paciente['telefono_emergencia'],
            'Contacto emergencia'=> $paciente['nombre_contacto_emergencia'],
            'Responsable de pago'=> $paciente['responsable_pago'],
        ];
        foreach ($contacto as $label => $valor): if(!$valor) continue; ?>
        <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--card-border);font-size:14px;">
            <span style="color:#6B7280;"><?= $label ?></span>
            <span style="font-weight:500;color:var(--body-text);"><?= htmlspecialchars((string)$valor) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</div>

</div></div>
