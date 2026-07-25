<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Csrf::token() ?>">
    <title>Iniciar Sesión | <?= APP_NAME ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: #E8EDF5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        /* ── Wrapper principal ─────────────────────────── */
        .login-wrapper {
            width: 100%;
            max-width: 900px;
            min-height: 540px;
            display: flex;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(10,30,80,.22);
        }

        /* ── Panel izquierdo ────────────────────────────── */
        .login-left {
            flex: 0 0 42%;
            background: linear-gradient(145deg, #1A56AB 0%, #0C1F46 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding: 40px 36px;
            overflow: hidden;
        }

        /* Círculos decorativos — bien separados */
        .login-left::before {
            content: '';
            position: absolute;
            top: -100px;
            right: -100px;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
        }
        .login-left::after {
            content: '';
            position: absolute;
            bottom: -70px;
            left: -70px;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
        }

        .deco-circle {
            position: absolute;
            border-radius: 50%;
        }
        /* Círculo grande centrado, zona media-alta */
        .deco-circle-1 {
            width: 180px; height: 180px;
            top: 18%; left: 50%;
            transform: translateX(-50%);
            background: rgba(255,255,255,.10);
        }
        /* Círculo mediano, desplazado a la izquierda y abajo del grande */
        .deco-circle-2 {
            width: 110px; height: 110px;
            top: 36%; left: 20%;
            background: rgba(255,255,255,.14);
        }
        /* Círculo pequeño, esquina superior izquierda */
        .deco-circle-3 {
            width: 60px; height: 60px;
            top: 8%; left: 12%;
            background: rgba(255,255,255,.09);
        }

        /* Contenido marca */
        .brand-block {
            position: relative;
            z-index: 2;
            text-align: center;
            color: #fff;
        }
        .brand-name {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -.3px;
            margin-bottom: 4px;
        }
        .brand-sub {
            font-size: 13px;
            font-weight: 400;
            opacity: .75;
            margin-bottom: 3px;
        }
        .brand-clinic {
            font-size: 12px;
            font-weight: 400;
            opacity: .55;
            margin-bottom: 24px;
        }

        /* Lista de features */
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }
        .feature-list li {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255,255,255,.80);
            font-size: 12.5px;
            padding: 5px 0;
        }
        .feature-list li span.dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: rgba(178,218,255,.7);
            flex-shrink: 0;
        }

        /* ── Panel derecho ──────────────────────────────── */
        .login-right {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 52px 52px 36px;
        }

        .form-box { width: 100%; max-width: 360px; }

        .form-title {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            margin-bottom: 6px;
        }
        .form-sub {
            font-size: 13px;
            color: #6B7280;
            margin-bottom: 32px;
        }

        .field-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #1A56AB;
            margin-bottom: 6px;
        }

        .field-wrap { position: relative; margin-bottom: 18px; }

        .field-wrap input {
            width: 100%;
            height: 44px;
            border: 1.5px solid #DDE4EF;
            border-radius: 9px;
            padding: 0 40px 0 14px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #1A2940;
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .field-wrap input::placeholder { color: #9CA3AF; font-size: 13px; }
        .field-wrap input:focus {
            border-color: #1A56AB;
            box-shadow: 0 0 0 3px rgba(26,86,171,.14);
        }

        .btn-eye {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9CA3AF;
            font-size: 14px;
            padding: 0;
            display: flex;
            align-items: center;
        }
        .btn-eye:hover { color: #1A56AB; }

        .btn-login {
            width: 100%;
            height: 44px;
            background: #1A56AB;
            color: #fff;
            border: none;
            border-radius: 9px;
            font-size: 14.5px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background .2s, box-shadow .2s;
            margin-top: 4px;
        }
        .btn-login:hover { background: #154a96; box-shadow: 0 4px 14px rgba(26,86,171,.35); }
        .btn-login:active { background: #103d80; }
        .btn-login:disabled { opacity: .65; cursor: not-allowed; }

        .error-box {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #DC2626;
            margin-bottom: 16px;
            display: none;
        }
        .error-box.visible { display: block; }

        .login-footer {
            margin-top: auto;
            padding-top: 32px;
            font-size: 11.5px;
            color: #9CA3AF;
            text-align: center;
        }

        /* ── Responsive ─────────────────────────────────── */
        @media (max-width: 640px) {
            .login-left { display: none; }
            .login-right { padding: 40px 28px 32px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- Panel izquierdo -->
    <div class="login-left">
        <span class="deco-circle deco-circle-1"></span>
        <span class="deco-circle deco-circle-2"></span>
        <span class="deco-circle deco-circle-3"></span>

        <div class="brand-block">
            <div class="brand-name">OdontoGest</div>
            <div class="brand-sub">Sistema de Gestión Odontológica</div>
            <div class="brand-clinic">Clínica Dental Paz &mdash; Honduras</div>

            <ul class="feature-list">
                <li><span class="dot"></span> Gestión de citas y agenda</li>
                <li><span class="dot"></span> Expedientes y odontograma</li>
                <li><span class="dot"></span> Facturación con ISV Honduras</li>
                <li><span class="dot"></span> Control de inventario</li>
            </ul>
        </div>
    </div>

    <!-- Panel derecho -->
    <div class="login-right">
        <div class="form-box">
            <h1 class="form-title">Bienvenido</h1>
            <p class="form-sub">Ingrese sus credenciales para acceder al sistema</p>

            <div id="errorBox" class="error-box"></div>

            <form id="formLogin" novalidate>
                <!-- Usuario -->
                <label class="field-label" for="username">Usuario</label>
                <div class="field-wrap">
                    <input type="text" id="username" name="username"
                           placeholder="Ingrese su usuario"
                           autocomplete="username" required>
                </div>

                <!-- Contraseña -->
                <label class="field-label" for="password">Contraseña</label>
                <div class="field-wrap">
                    <input type="password" id="password" name="password"
                           placeholder="••••••••••"
                           autocomplete="current-password" required>
                    <button type="button" class="btn-eye" id="btnEye"
                            tabindex="-1" aria-label="Mostrar/ocultar contraseña">
                        <i class="fa fa-eye" id="eyeIcon"></i>
                    </button>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    Iniciar Sesión
                </button>
            </form>
        </div>

        <div class="login-footer">
            &copy; 2025 OdontoGest &middot; Clínica Dental Paz
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script>
/* Toggle contraseña */
document.getElementById('btnEye').addEventListener('click', function () {
    const inp  = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        inp.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
});

/* Submit */
document.getElementById('formLogin').addEventListener('submit', async function (e) {
    e.preventDefault();

    const usuario    = document.getElementById('username').value.trim();
    const contrasena = document.getElementById('password').value;
    const btnLogin   = document.getElementById('btnLogin');
    const errorBox   = document.getElementById('errorBox');

    if (!usuario || !contrasena) {
        showError('Por favor complete todos los campos.');
        return;
    }

    btnLogin.disabled = true;
    btnLogin.textContent = 'Verificando...';
    errorBox.classList.remove('visible');

    // CSRF token guardado en meta tag
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    try {
        const res  = await fetch('<?= APP_URL ?>auth/procesarLogin', {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ usuario, contrasena, _csrf: csrf }),
        });
        const data = await res.json();

        if (data.success) {
            window.location.href = data.redirect ?? '<?= APP_URL ?>dashboard/index';
        } else {
            showError(data.error ?? 'Credenciales incorrectas.');
        }
    } catch (err) {
        showError('Error de conexión. Intente nuevamente.');
    } finally {
        btnLogin.disabled = false;
        btnLogin.textContent = 'Iniciar Sesión';
    }
});

function showError(msg) {
    const box = document.getElementById('errorBox');
    box.textContent = msg;
    box.classList.add('visible');
}
</script>
</body>
</html>
