<?php
$csrf    = Csrf::token();
$ok      = $_GET['ok']  ?? '';
$err     = $_GET['err'] ?? '';
$iniciales = strtoupper(substr($perfil['nombre_completo']??'?',0,1).
             (strpos($perfil['nombre_completo']??'',' ')!==false
                ? substr(strrchr($perfil['nombre_completo']??'',' '),1,1):''));
?>
<div><div style="padding:24px 28px;">

<?php if($ok==='1'): ?>
<div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:13px;font-weight:600;"><i class="fas fa-check-circle me-2"></i>Datos actualizados correctamente.</div>
<?php elseif($ok==='password'): ?>
<div style="background:#F0FDF4;border:1px solid #86EFAC;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#166534;font-size:13px;font-weight:600;"><i class="fas fa-check-circle me-2"></i>Contraseña cambiada correctamente.</div>
<?php elseif($err==='password_incorrecto'): ?>
<div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#DC2626;font-size:13px;font-weight:600;"><i class="fas fa-exclamation-circle me-2"></i>La contraseña actual es incorrecta.</div>
<?php elseif($err==='password_invalido'): ?>
<div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#DC2626;font-size:13px;font-weight:600;"><i class="fas fa-exclamation-circle me-2"></i>Las contraseñas no coinciden o tienen menos de 8 caracteres.</div>
<?php endif; ?>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">

<!-- Sidebar perfil -->
<div class="kpi-card" style="text-align:center;">
    <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#1A56AB,#0C1F46);color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:700;margin:0 auto 14px;">
        <?= htmlspecialchars($iniciales) ?>
    </div>
    <div style="font-weight:700;font-size:16px;color:var(--body-text);"><?= htmlspecialchars($perfil['nombre_completo']??'') ?></div>
    <div style="font-size:13px;color:#9CA3AF;margin-top:4px;">@<?= htmlspecialchars($perfil['usuario']??'') ?></div>
    <div style="margin-top:10px;"><span class="badge badge-blue" style="background:rgba(26,86,171,.1);color:#1A56AB;font-size:12px;"><?= htmlspecialchars($perfil['rol_nombre']??'') ?></span></div>
    <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--card-border);font-size:12px;color:#9CA3AF;text-align:left;">
        <?php if(!empty($perfil['correo'])): ?>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;"><i class="fas fa-envelope" style="width:14px;color:#6B7280;"></i><?= htmlspecialchars($perfil['correo']) ?></div>
        <?php endif; ?>
        <?php if(!empty($perfil['telefono'])): ?>
        <div style="display:flex;align-items:center;gap:8px;"><i class="fas fa-phone" style="width:14px;color:#6B7280;"></i><?= htmlspecialchars($perfil['telefono']) ?></div>
        <?php endif; ?>
    </div>
</div>

<!-- Formularios -->
<div style="display:flex;flex-direction:column;gap:16px;">

    <!-- Datos personales -->
    <div class="kpi-card">
        <div style="font-weight:700;font-size:14px;color:var(--body-text);margin-bottom:16px;"><i class="fas fa-user me-2" style="color:#1A56AB;"></i>Datos Personales</div>
        <form method="POST" action="<?= APP_URL ?>perfil/actualizar">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:span 2;">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="nombre_completo" class="form-control" value="<?= htmlspecialchars($perfil['nombre_completo']??'') ?>" required>
                </div>
                <div>
                    <label class="form-label">Correo electrónico</label>
                    <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($perfil['correo']??'') ?>">
                </div>
                <div>
                    <label class="form-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($perfil['telefono']??'') ?>">
                </div>
            </div>
            <div style="margin-top:16px;display:flex;justify-content:flex-end;">
                <button type="submit" class="btn-og-primary"><i class="fas fa-save me-1"></i>Guardar Cambios</button>
            </div>
        </form>
    </div>

    <!-- Cambiar contraseña -->
    <div class="kpi-card">
        <div style="font-weight:700;font-size:14px;color:var(--body-text);margin-bottom:16px;"><i class="fas fa-lock me-2" style="color:#8B5CF6;"></i>Cambiar Contraseña</div>
        <form method="POST" action="<?= APP_URL ?>perfil/password">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div style="grid-column:span 2;">
                    <label class="form-label">Contraseña actual *</label>
                    <input type="password" name="password_actual" class="form-control" required autocomplete="current-password">
                </div>
                <div>
                    <label class="form-label">Nueva contraseña *</label>
                    <input type="password" name="password_nuevo" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
                <div>
                    <label class="form-label">Confirmar contraseña *</label>
                    <input type="password" name="password_confirm" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
            </div>
            <div style="margin-top:8px;font-size:12px;color:#9CA3AF;">Mínimo 8 caracteres.</div>
            <div style="margin-top:14px;display:flex;justify-content:flex-end;">
                <button type="submit" class="btn-og-primary" style="background:#8B5CF6;"><i class="fas fa-key me-1"></i>Cambiar Contraseña</button>
            </div>
        </form>
    </div>

</div><!-- /formularios -->
</div><!-- /grid -->
</div></div>
