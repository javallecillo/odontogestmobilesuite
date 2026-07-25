<?php
// Variables disponibles: $data (usuarios, total, pages), $roles, $q, $estado, $rol, $page
$rolBadge = [
    'Administrador' => 'badge-admin',
    'Odontologo'    => 'badge-odontologo',
    'Recepcionista' => 'badge-recep',
    'Asistente'     => 'badge-asistente',
];
$estadoBadge = ['activo' => 'badge-activo', 'inactivo' => 'badge-inactivo', 'bloqueado' => 'badge-inactivo'];
?>

<div style="padding:24px 28px;">

    <!-- ── Cabecera ──── -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <div>
            <h4 style="margin:0;font-weight:800;color:#005C3E;">
                <i class="fas fa-user-cog me-2"></i>Gestión de Usuarios
            </h4>
            <small style="color:#6b7280;"><?= $data['total'] ?> usuario<?= $data['total'] !== 1 ? 's' : '' ?> encontrado<?= $data['total'] !== 1 ? 's' : '' ?></small>
        </div>
        <a href="<?= APP_URL ?>Usuarios/nuevo" class="btn-og-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border-radius:8px;font-size:.88rem;">
            <i class="fas fa-plus"></i> Nuevo usuario
        </a>
    </div>

    <!-- ── Filtros ──── -->
    <form method="GET" action="<?= APP_URL ?>Usuarios/index"
          style="background:#fff;border-radius:12px;border:1px solid rgba(0,92,62,.10);padding:16px 20px;margin-bottom:20px;display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <label style="font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Buscar</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q) ?>"
                       placeholder="Nombre, usuario o correo..." style="border-radius:0 8px 8px 0;">
            </div>
        </div>
        <div style="min-width:140px;">
            <label style="font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Estado</label>
            <select class="form-select" name="estado" style="border-radius:8px;">
                <option value="">Todos</option>
                <option value="activo"   <?= $estado === 'activo'   ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= $estado === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                <option value="bloqueado"<?= $estado === 'bloqueado'? 'selected' : '' ?>>Bloqueado</option>
            </select>
        </div>
        <div style="min-width:160px;">
            <label style="font-size:.78rem;font-weight:600;color:#374151;margin-bottom:4px;display:block;">Rol</label>
            <select class="form-select" name="rol" style="border-radius:8px;">
                <option value="">Todos</option>
                <?php foreach ($roles as $r): ?>
                <option value="<?= htmlspecialchars($r['nombre']) ?>" <?= $rol === $r['nombre'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($r['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-og-primary" style="padding:9px 18px;border-radius:8px;font-size:.88rem;">
                <i class="fas fa-filter me-1"></i>Filtrar
            </button>
            <a href="<?= APP_URL ?>Usuarios/index" class="btn btn-outline-secondary" style="border-radius:8px;padding:9px 14px;font-size:.88rem;">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>

    <!-- ── Tabla ──── -->
    <div style="background:#fff;border-radius:12px;border:1px solid rgba(0,92,62,.10);overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);">
        <?php if (empty($data['usuarios'])): ?>
        <div style="padding:48px;text-align:center;color:#6b7280;">
            <i class="fas fa-user-slash fa-2x mb-3 d-block" style="opacity:.25;"></i>
            No se encontraron usuarios con los filtros aplicados.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="tabla-og">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre / Usuario</th>
                    <th>Correo</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Último acceso</th>
                    <th style="text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data['usuarios'] as $u): ?>
            <tr>
                <td style="color:#6b7280;font-size:.82rem;"><?= $u['id_usuario'] ?></td>
                <td>
                    <div style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars($u['nombre_completo']) ?></div>
                    <div style="font-size:.78rem;color:#6b7280;">@<?= htmlspecialchars($u['usuario']) ?></div>
                </td>
                <td style="font-size:.88rem;color:#374151;"><?= htmlspecialchars($u['correo'] ?? '—') ?></td>
                <td>
                    <span class="badge-og <?= $rolBadge[$u['rol']] ?? 'badge-inactivo' ?>">
                        <?= htmlspecialchars($u['rol']) ?>
                    </span>
                </td>
                <td>
                    <span class="badge-og <?= $estadoBadge[$u['estado']] ?? 'badge-inactivo' ?>">
                        <?= ucfirst($u['estado']) ?>
                    </span>
                </td>
                <td style="font-size:.82rem;color:#6b7280;">
                    <?= $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : '—' ?>
                </td>
                <td style="text-align:center;white-space:nowrap;">
                    <a href="<?= APP_URL ?>Usuarios/editar/<?= $u['id_usuario'] ?>"
                       class="btn btn-sm" style="background:rgba(0,92,62,.08);color:#005C3E;border-radius:6px;margin-right:4px;"
                       title="Editar">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-toggle-estado"
                            style="background:rgba(217,119,6,.08);color:#d97706;border-radius:6px;margin-right:4px;"
                            data-id="<?= $u['id_usuario'] ?>"
                            data-estado="<?= $u['estado'] ?>"
                            title="<?= $u['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>">
                        <i class="fas fa-<?= $u['estado'] === 'activo' ? 'user-slash' : 'user-check' ?>"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-reset-pass"
                            style="background:rgba(147,51,234,.08);color:#9333ea;border-radius:6px;"
                            data-id="<?= $u['id_usuario'] ?>"
                            data-nombre="<?= htmlspecialchars($u['nombre_completo'], ENT_QUOTES) ?>"
                            title="Resetear contraseña">
                        <i class="fas fa-key"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>

        <!-- ── Paginación ──── -->
        <?php if ($data['pages'] > 1): ?>
        <div style="padding:12px 20px;border-top:1px solid rgba(0,92,62,.08);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
            <small style="color:#6b7280;">Página <?= $page ?> de <?= $data['pages'] ?></small>
            <div style="display:flex;gap:6px;">
                <?php
                $base = APP_URL . 'Usuarios/index?' . http_build_query(['q'=>$q,'estado'=>$estado,'rol'=>$rol,'page'=>'']);
                for ($p = 1; $p <= $data['pages']; $p++):
                    $active = ($p === $page);
                ?>
                <a href="<?= $base . $p ?>"
                   style="padding:5px 12px;border-radius:6px;font-size:.83rem;font-weight:<?= $active ? '700' : '400' ?>;
                          background:<?= $active ? '#005C3E' : '#f0f4f2' ?>;
                          color:<?= $active ? '#fff' : '#374151' ?>;text-decoration:none;">
                    <?= $p ?>
                </a>
                <?php endfor; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- ── Scripts ──────────────────────────────────────────────────── -->
<script>
const CSRF  = '<?= Csrf::token() ?>';
const BASE  = '<?= APP_URL ?>';

// Toggle estado
document.querySelectorAll('.btn-toggle-estado').forEach(btn => {
    btn.addEventListener('click', async function () {
        const id     = this.dataset.id;
        const estado = this.dataset.estado;
        const accion = estado === 'activo' ? 'desactivar' : 'activar';

        const { isConfirmed } = await Swal.fire({
            title: `¿${accion.charAt(0).toUpperCase()+accion.slice(1)} usuario?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#005C3E',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar',
        });
        if (!isConfirmed) return;

        const res  = await fetch(BASE + 'Usuarios/toggleEstado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_usuario: id, _csrf: CSRF }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon:'success', title:'Listo', text: `Usuario ${data.estado}`, confirmButtonColor:'#005C3E', timer:1500, showConfirmButton:false });
            setTimeout(() => location.reload(), 1600);
        } else {
            Swal.fire({ icon:'error', title:'Error', text: data.error, confirmButtonColor:'#005C3E' });
        }
    });
});

// Reset password
document.querySelectorAll('.btn-reset-pass').forEach(btn => {
    btn.addEventListener('click', async function () {
        const id     = this.dataset.id;
        const nombre = this.dataset.nombre;

        const { value: pass } = await Swal.fire({
            title: `Resetear contraseña`,
            html: `<p style="margin-bottom:8px;color:#374151;">Usuario: <strong>${nombre}</strong></p>
                   <input type="password" id="swal-pass" class="swal2-input" placeholder="Nueva contraseña (mín. 6 chars)">`,
            confirmButtonText: 'Guardar',
            confirmButtonColor: '#005C3E',
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const v = document.getElementById('swal-pass').value;
                if (!v || v.length < 6) { Swal.showValidationMessage('Mínimo 6 caracteres'); return false; }
                return v;
            },
        });
        if (!pass) return;

        const res  = await fetch(BASE + 'Usuarios/resetPassword', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_usuario: id, nueva_contrasena: pass, _csrf: CSRF }),
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire({ icon:'success', title:'Listo', text:'Contraseña actualizada', confirmButtonColor:'#005C3E', timer:1500, showConfirmButton:false });
        } else {
            Swal.fire({ icon:'error', title:'Error', text: data.error, confirmButtonColor:'#005C3E' });
        }
    });
});
</script>
