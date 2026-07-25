<?php
// $usuario = null (nuevo) o array (editar)
// $roles   = array de roles
// $pageTitle ya seteado en el controller
$esNuevo = empty($usuario);
$titulo  = $esNuevo ? 'Nuevo usuario' : 'Editar usuario';
?>

<div style="padding:24px 28px;">

    <!-- Breadcrumb -->
    <nav style="margin-bottom:16px;font-size:.83rem;color:#6b7280;">
        <a href="<?= APP_URL ?>Usuarios/index" style="color:#005C3E;text-decoration:none;">
            <i class="fas fa-user-cog me-1"></i>Usuarios
        </a>
        <span style="margin:0 8px;">/</span>
        <span><?= $titulo ?></span>
    </nav>

    <div style="max-width:640px;">
        <h4 style="font-weight:800;color:#005C3E;margin-bottom:20px;">
            <i class="fas fa-<?= $esNuevo ? 'user-plus' : 'user-edit' ?> me-2"></i><?= $titulo ?>
        </h4>

        <!-- Alerta global -->
        <div id="alertGlobal" class="d-none mb-3"
             style="border-radius:10px;padding:10px 14px;font-size:.88rem;"></div>

        <form id="formUsuario"
              style="background:#fff;border-radius:12px;border:1px solid rgba(0,92,62,.10);padding:28px;box-shadow:0 2px 8px rgba(0,0,0,.06);">
            <input type="hidden" id="csrfToken" value="<?= Csrf::token() ?>">
            <?php if (!$esNuevo): ?>
            <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

                <!-- Nombre completo -->
                <div class="mb-3" style="grid-column:1/-1;">
                    <label class="form-label">Nombre completo <span style="color:#dc2626;">*</span></label>
                    <input type="text" class="form-control" name="nombre_completo" required
                           value="<?= htmlspecialchars($usuario['nombre_completo'] ?? '') ?>"
                           placeholder="Ej: Juan Carlos Pérez">
                </div>

                <!-- Usuario -->
                <div class="mb-3">
                    <label class="form-label">Nombre de usuario <span style="color:#dc2626;">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">@</span>
                        <input type="text" class="form-control" name="usuario"
                               value="<?= htmlspecialchars($usuario['usuario'] ?? '') ?>"
                               placeholder="usuario123"
                               <?= !$esNuevo ? 'readonly style="background:#f9f9f9;"' : 'required' ?>>
                    </div>
                    <?php if (!$esNuevo): ?>
                    <small style="color:#6b7280;font-size:.76rem;">El usuario no se puede cambiar tras la creación.</small>
                    <?php endif; ?>
                </div>

                <!-- Rol -->
                <div class="mb-3">
                    <label class="form-label">Rol <span style="color:#dc2626;">*</span></label>
                    <select class="form-select" name="id_rol" required>
                        <option value="">-- Selecciona un rol --</option>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?= $r['id_rol'] ?>"
                            <?= (!$esNuevo && $usuario['id_rol'] == $r['id_rol']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Correo -->
                <div class="mb-3">
                    <label class="form-label">Correo electrónico</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" name="correo"
                               value="<?= htmlspecialchars($usuario['correo'] ?? '') ?>"
                               placeholder="correo@ejemplo.com">
                    </div>
                </div>

                <!-- Teléfono -->
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" class="form-control" name="telefono"
                               value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>"
                               placeholder="9999-9999">
                    </div>
                </div>

                <!-- Estado -->
                <div class="mb-3">
                    <label class="form-label">Estado <span style="color:#dc2626;">*</span></label>
                    <select class="form-select" name="estado" required>
                        <option value="activo"   <?= (!$esNuevo && $usuario['estado'] === 'activo')   ? 'selected' : ($esNuevo ? 'selected' : '') ?>>Activo</option>
                        <option value="inactivo" <?= (!$esNuevo && $usuario['estado'] === 'inactivo') ? 'selected' : '' ?>>Inactivo</option>
                        <option value="bloqueado"<?= (!$esNuevo && $usuario['estado'] === 'bloqueado')? 'selected' : '' ?>>Bloqueado</option>
                    </select>
                </div>

                <!-- Contraseña (solo en creación) -->
                <?php if ($esNuevo): ?>
                <div class="mb-3" style="grid-column:1/-1;">
                    <label class="form-label">Contraseña <span style="color:#dc2626;">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" name="contrasena"
                               id="passInput" placeholder="Mínimo 6 caracteres" required>
                        <button type="button" class="btn btn-outline-secondary" id="btnTogglePass" tabindex="-1">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                    <small style="color:#6b7280;font-size:.76rem;">
                        Para cambiar la contraseña de un usuario existente usa el botón <i class="fas fa-key"></i> en la lista.
                    </small>
                </div>
                <?php endif; ?>

            </div>

            <!-- Botones -->
            <div style="display:flex;gap:12px;margin-top:8px;">
                <button type="submit" class="btn-og-primary" id="btnGuardar"
                        style="padding:10px 24px;border-radius:8px;">
                    <span id="btnTxt"><i class="fas fa-save me-2"></i><?= $esNuevo ? 'Crear usuario' : 'Guardar cambios' ?></span>
                    <span id="btnSpin" class="d-none">
                        <span class="spinner-border spinner-border-sm me-2"></span>Guardando...
                    </span>
                </button>
                <a href="<?= APP_URL ?>Usuarios/index"
                   class="btn btn-outline-secondary" style="border-radius:8px;padding:10px 20px;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
<?php if ($esNuevo): ?>
document.getElementById('btnTogglePass')?.addEventListener('click', function () {
    const inp  = document.getElementById('passInput');
    const icon = document.getElementById('eyeIcon');
    inp.type   = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
});
<?php endif; ?>

document.getElementById('formUsuario').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn  = document.getElementById('btnGuardar');
    const txt  = document.getElementById('btnTxt');
    const spin = document.getElementById('btnSpin');
    const alrt = document.getElementById('alertGlobal');

    btn.disabled = true; txt.classList.add('d-none'); spin.classList.remove('d-none'); alrt.classList.add('d-none');

    const fd   = new FormData(this);
    const body = Object.fromEntries(fd.entries());
    body._csrf = document.getElementById('csrfToken').value;

    try {
        const res  = await fetch('<?= APP_URL ?>Usuarios/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (data.success) {
            await Swal.fire({ icon:'success', title:'¡Listo!', text: data.message, confirmButtonColor:'#005C3E', timer:1600, showConfirmButton:false });
            window.location.href = '<?= APP_URL ?>Usuarios/index';
        } else {
            alrt.style.background = '#fef2f2'; alrt.style.color = '#dc2626';
            alrt.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + (data.error || 'Error al guardar');
            alrt.classList.remove('d-none');
            btn.disabled = false; txt.classList.remove('d-none'); spin.classList.add('d-none');
        }
    } catch (_) {
        alrt.style.background = '#fef2f2'; alrt.style.color = '#dc2626';
        alrt.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Error de conexión';
        alrt.classList.remove('d-none');
        btn.disabled = false; txt.classList.remove('d-none'); spin.classList.add('d-none');
    }
});
</script>
