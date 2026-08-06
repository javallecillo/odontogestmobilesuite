<?php
$urlActual = strtolower(trim($_GET['url'] ?? '', '/'));

$menu = [
    ['id'=>1,  'nombre'=>'Dashboard',    'url'=>APP_URL.'Dashboard/index',  'icono'=>'fas fa-chart-line',          'permiso'=>''],
    ['id'=>2,  'nombre'=>'Agenda',       'url'=>APP_URL.'Agenda/index',     'icono'=>'fas fa-calendar-alt',        'permiso'=>'agenda.ver'],
    ['id'=>3,  'nombre'=>'Pacientes',    'url'=>APP_URL.'Pacientes/index',  'icono'=>'fas fa-users',               'permiso'=>'pacientes.ver'],
    ['id'=>4,  'nombre'=>'Expedientes',  'url'=>APP_URL.'Expedientes/index','icono'=>'fas fa-folder-open',         'permiso'=>'expedientes.ver'],
    ['id'=>5,  'nombre'=>'Facturación',  'url'=>APP_URL.'Facturacion/index','icono'=>'fas fa-file-invoice-dollar', 'permiso'=>'facturacion.ver'],
    ['id'=>6,  'nombre'=>'Inventario',   'url'=>APP_URL.'Inventario/index', 'icono'=>'fas fa-boxes-stacked',       'permiso'=>'inventario.ver'],
    ['id'=>10, 'nombre'=>'Administración','url'=>'#',                        'icono'=>'fas fa-cogs', 'permiso'=>'', 'children'=>[
        ['id'=>101,'nombre'=>'Usuarios',     'url'=>APP_URL.'Usuarios/index',     'icono'=>'fas fa-user-cog',    'permiso'=>'usuarios.ver'],
        ['id'=>102,'nombre'=>'Roles',        'url'=>APP_URL.'Roles/index',        'icono'=>'fas fa-user-shield', 'permiso'=>'roles.ver'],
        ['id'=>107,'nombre'=>'Odontólogos',  'url'=>APP_URL.'Odontologos/index',  'icono'=>'fas fa-user-doctor', 'permiso'=>''],
        ['id'=>103,'nombre'=>'Servicios',    'url'=>APP_URL.'Servicios/index',    'icono'=>'fas fa-tooth',       'permiso'=>'servicios.ver'],
        ['id'=>104,'nombre'=>'Reportes',  'url'=>APP_URL.'Reportes/index',  'icono'=>'fas fa-chart-bar',  'permiso'=>'reportes.ver'],
        ['id'=>105,'nombre'=>'Auditoría',      'url'=>APP_URL.'Auditoria/index',     'icono'=>'fas fa-history',  'permiso'=>''],
        ['id'=>106,'nombre'=>'Configuración',  'url'=>APP_URL.'Configuracion/index', 'icono'=>'fas fa-gear',     'permiso'=>''],
    ]],
];

function ogIsActive(string $itemUrl): bool {
    global $urlActual;
    if ($itemUrl === '#' || empty($itemUrl)) return false;
    $path = strtolower(trim(parse_url($itemUrl, PHP_URL_PATH) ?? '', '/'));
    $base = strtolower(trim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/'));
    if ($base && str_starts_with($path, $base)) $path = ltrim(substr($path, strlen($base)), '/');
    return $path !== '' && str_starts_with($urlActual, $path);
}

function ogRenderMenu(array $menu): void {
    foreach ($menu as $item) {
        if (!empty($item['permiso']) && !Auth::can($item['permiso'])) continue;

        if (!empty($item['children'])) {
            $hijoActivo = false;
            foreach ($item['children'] as $child) {
                if (ogIsActive($child['url'])) { $hijoActivo = true; break; }
            }
            $sid = 'sub-' . $item['id'];
            echo '<li class="nav-item">';
            echo '<a class="nav-link accordion-toggle" href="#" data-bs-toggle="collapse"
                     data-bs-target="#'.$sid.'" aria-expanded="'.($hijoActivo?'true':'false').'">';
            echo '<i class="'.htmlspecialchars($item['icono']).'"></i>';
            echo '<span class="ms-2">'.htmlspecialchars($item['nombre']).'</span>';
            echo '<i class="fas fa-chevron-down chevron-icon"></i></a>';
            echo '<div class="collapse'.($hijoActivo?' show':'').'" id="'.$sid.'"><ul class="nav flex-column">';
            foreach ($item['children'] as $child) {
                if (!empty($child['permiso']) && !Auth::can($child['permiso'])) continue;
                $active = ogIsActive($child['url']) ? ' active' : '';
                echo '<li class="nav-item"><a class="nav-link'.$active.'" href="'.htmlspecialchars($child['url']).'"
                         style="padding-left:42px; font-size:.85rem;">';
                echo '<i class="'.htmlspecialchars($child['icono']).'"></i>';
                echo '<span class="ms-2">'.htmlspecialchars($child['nombre']).'</span></a></li>';
            }
            echo '</ul></div></li>';
        } else {
            $active = ogIsActive($item['url']) ? ' active' : '';
            echo '<li class="nav-item"><a class="nav-link'.$active.'" href="'.htmlspecialchars($item['url']).'">';
            echo '<i class="'.htmlspecialchars($item['icono']).'"></i>';
            echo '<span class="ms-2">'.htmlspecialchars($item['nombre']).'</span></a></li>';
        }
    }
}

// Iniciales del nombre de usuario para el avatar
$nombreUsuario = Auth::get('nombre') ?? 'Usuario';
$rolUsuario    = Auth::rol() ?? '';
$iniciales = '';
foreach (array_slice(explode(' ', $nombreUsuario), 0, 2) as $parte) {
    $iniciales .= strtoupper(mb_substr($parte, 0, 1));
}
?>

<!-- ══════════════════════════════════════════════════════════
     SIDEBAR — OdontoGest
     ══════════════════════════════════════════════════════════ -->
<aside class="sidebar" id="sidebar">

    <!-- Logo / Brand -->
    <div class="sidebar-header">
        <a class="sidebar-brand" href="<?= APP_URL ?>Dashboard/index">
            <div class="sidebar-logo-icon">
                <i class="fas fa-tooth"></i>
            </div>
            <div class="sidebar-logo-texto">
                <div class="brand-name">OdontoGest</div>
                <div class="brand-sub">Panel Admin</div>
            </div>
        </a>
        <button class="btn d-lg-none p-0" id="btnCloseSidebar"
                style="color:rgba(255,255,255,.5);background:none;border:none;font-size:1.1rem;">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Navegación -->
    <nav class="sidebar-nav">
        <ul class="nav flex-column" id="sidebarMenu">
            <?php ogRenderMenu($menu); ?>
        </ul>
    </nav>

    <!-- Usuario logueado -->
    <div class="sidebar-user">
        <div class="sidebar-user-avatar"><?= htmlspecialchars($iniciales) ?></div>
        <div class="sidebar-user-info">
            <div class="user-name"><?= htmlspecialchars($nombreUsuario) ?></div>
            <div class="user-role"><?= htmlspecialchars($rolUsuario) ?></div>
        </div>
    </div>
</aside>

<!-- Overlay móvil -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ══════════════════════════════════════════════════════════
     TOP HEADER
     ══════════════════════════════════════════════════════════ -->
<header class="top-header" id="topHeader">

    <!-- Toggle sidebar -->
    <button class="btn-menu-toggle" id="btnMenuToggle" title="Menú">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Título página -->
    <div class="header-page-info">
        <div class="header-title"><?= htmlspecialchars($pageTitle ?? 'Panel') ?></div>
        <div class="header-sub">Clínica Dental Ortonova — Honduras</div>
    </div>

    <!-- Buscador centrado -->
    <div class="header-search">
        <i class="fas fa-search search-icon"></i>
        <input type="text" placeholder="Buscar pacientes, citas...">
    </div>

    <!-- Controles derecha -->
    <div class="d-flex align-items-center gap-2 ms-auto">

        <!-- Toggle modo oscuro -->
        <button class="btn-notificaciones" id="btnDarkMode" title="Modo oscuro / claro" onclick="ogToggleDark()">
            <i class="fas fa-moon" id="iconDark"></i>
        </button>

        <!-- Notificaciones -->
        <div class="dropdown">
            <button class="btn-notificaciones" id="btnNotif" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-bell"></i>
                <span class="badge-notif d-none" id="badgeNotif">0</span>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-sm" id="dropdownNotif"
                 style="width:320px;max-height:400px;overflow-y:auto;padding:0;border:1px solid #DDE4EF;border-radius:12px;">
                <div style="padding:12px 16px;border-bottom:1px solid #DDE4EF;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:700;font-size:.85rem;color:#1A2940;">
                        <i class="fas fa-bell me-1" style="color:#1A56AB;"></i>Notificaciones
                    </span>
                    <button type="button" class="btn btn-link btn-sm p-0 text-muted" id="btnMarcarTodas"
                            style="font-size:.75rem;text-decoration:none;color:#1A56AB;">
                        Leer todas
                    </button>
                </div>
                <div id="listaNotif">
                    <div class="text-center py-4 text-muted" style="font-size:.85rem;">
                        <i class="fas fa-bell-slash fa-2x mb-2 d-block" style="opacity:.25;"></i>
                        Sin notificaciones
                    </div>
                </div>
                <div style="padding:8px 16px;border-top:1px solid #DDE4EF;text-align:center;">
                    <small style="color:#9CA3AF;" id="txtNoLeidas">0 sin leer</small>
                </div>
            </div>
        </div>

        <!-- Avatar + nombre + dropdown -->
        <div class="dropdown">
            <button class="d-flex align-items-center gap-2 bg-transparent border-0 py-1 px-2"
                    style="cursor:pointer;border-radius:8px;transition:background .15s;"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    onmouseenter="this.style.background='#F0F3F8'"
                    onmouseleave="this.style.background='transparent'">
                <div class="header-avatar"><?= htmlspecialchars($iniciales) ?></div>
                <div class="d-none d-md-block text-start">
                    <div style="font-size:.84rem;font-weight:600;color:#1A2940;line-height:1.2;">
                        <?= htmlspecialchars($nombreUsuario) ?>
                    </div>
                    <div style="font-size:.71rem;color:#6B7280;"><?= htmlspecialchars($rolUsuario) ?></div>
                </div>
                <i class="fas fa-chevron-down d-none d-md-block" style="font-size:.65rem;color:#9CA3AF;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm"
                style="border:1px solid #DDE4EF;border-radius:10px;min-width:190px;padding:6px;">
                <li>
                    <span class="dropdown-item-text" style="font-size:.78rem;color:#9CA3AF;padding:6px 12px;">
                        <i class="fas fa-shield-alt me-1"></i><?= htmlspecialchars($rolUsuario) ?>
                    </span>
                </li>
                <li><hr class="dropdown-divider my-1" style="border-color:#DDE4EF;"></li>
                <li>
                    <a class="dropdown-item" href="<?= APP_URL ?>Perfil/index"
                       style="font-size:.875rem;color:var(--body-text,#1A2940);border-radius:6px;padding:8px 12px;">
                        <i class="fas fa-user-circle me-2" style="color:#1A56AB;"></i>Mi Perfil
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1" style="border-color:#DDE4EF;"></li>
                <li>
                    <a class="dropdown-item" href="<?= APP_URL ?>Auth/logout"
                       style="font-size:.875rem;color:#dc2626;border-radius:6px;padding:8px 12px;">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>

<!-- ══════════════════════════════════════════════════════════
     MAIN CONTENT (abierto aquí, cerrado en footer.php)
     ══════════════════════════════════════════════════════════ -->
<main class="main-content" id="mainContent">

<script>
/* ── Dark mode (carga antes que nada para evitar flash) ── */
(function(){
    const saved = localStorage.getItem('og_theme');
    if (saved === 'dark') document.documentElement.setAttribute('data-theme','dark');
})();

function ogToggleDark(){
    const html = document.documentElement;
    const dark = html.getAttribute('data-theme') === 'dark';
    html.setAttribute('data-theme', dark ? 'light' : 'dark');
    localStorage.setItem('og_theme', dark ? 'light' : 'dark');
    ogUpdateDarkIcon();
}
function ogUpdateDarkIcon(){
    const dark = document.documentElement.getAttribute('data-theme') === 'dark';
    const ico  = document.getElementById('iconDark');
    if (ico) { ico.className = dark ? 'fas fa-sun' : 'fas fa-moon'; }
    const btn  = document.getElementById('btnDarkMode');
    if (btn) { btn.title = dark ? 'Modo claro' : 'Modo oscuro'; }
}
document.addEventListener('DOMContentLoaded', ogUpdateDarkIcon);

(function(){
    /* ── Notificaciones ─────────────────────────────────── */
    const APP_URL   = '<?= APP_URL ?>';
    const badge     = document.getElementById('badgeNotif');
    const lista     = document.getElementById('listaNotif');
    const txtNoLeid = document.getElementById('txtNoLeidas');
    const btnMarcar = document.getElementById('btnMarcarTodas');

    function tiempo(f){
        const d = Math.floor((new Date() - new Date(f.replace(' ','T'))) / 1000);
        if(d<60)  return 'Hace un momento';
        if(d<3600)return `Hace ${Math.floor(d/60)} min`;
        if(d<86400)return `Hace ${Math.floor(d/3600)} h`;
        return `Hace ${Math.floor(d/86400)} d`;
    }
    function render(notifs, noLeidas){
        badge.textContent = noLeidas>99?'99+':noLeidas;
        badge.classList.toggle('d-none', noLeidas===0);
        txtNoLeid.textContent = `${noLeidas} sin leer`;
        if(!notifs||!notifs.length){
            lista.innerHTML='<div class="text-center py-4 text-muted" style="font-size:.85rem;"><i class="fas fa-bell-slash fa-2x mb-2 d-block" style="opacity:.25;"></i>Sin notificaciones</div>';
            return;
        }
        lista.innerHTML=notifs.map(n=>`
            <div class="notif-item p-3" data-id="${n.id_notificacion}" data-leida="${n.leida}"
                 style="border-bottom:1px solid #F0F3F8;cursor:pointer;${parseInt(n.leida)===0?'background:rgba(26,86,171,.04);':''}">
                <div style="font-weight:${parseInt(n.leida)===0?'700':'500'};font-size:.83rem;color:#1A2940;">${n.titulo}</div>
                <div style="font-size:.76rem;color:#6B7280;margin-top:2px;">${n.mensaje}</div>
                <div style="font-size:.7rem;color:#9CA3AF;margin-top:3px;">${tiempo(n.fecha)}</div>
            </div>`).join('');
    }
    function cargar(){
        fetch(APP_URL+'Notificaciones/obtener')
            .then(r=>r.json())
            .then(d=>render(d.notificaciones,d.total_no_leidas))
            .catch(()=>{});
    }
    lista.addEventListener('click',function(e){
        const item=e.target.closest('.notif-item');
        if(!item||item.dataset.leida==='1')return;
        fetch(APP_URL+'Notificaciones/marcarLeida',{
            method:'POST',headers:{'Content-Type':'application/json'},
            body:JSON.stringify({id:item.dataset.id})
        }).then(()=>cargar());
    });
    btnMarcar?.addEventListener('click',()=>{
        fetch(APP_URL+'Notificaciones/marcarTodas',{method:'POST'}).then(()=>cargar());
    });
    document.getElementById('btnNotif')?.addEventListener('click',cargar);
    cargar();
    setInterval(cargar,30000);

    /* ── Sidebar toggle ─────────────────────────────────── */
    const sidebar  = document.getElementById('sidebar');
    const header   = document.getElementById('topHeader');
    const main     = document.getElementById('mainContent');
    const overlay  = document.getElementById('sidebarOverlay');
    const btnToggle= document.getElementById('btnMenuToggle');
    const btnClose = document.getElementById('btnCloseSidebar');
    // En móvil siempre empieza cerrado; en escritorio respeta localStorage
    let collapsed  = window.innerWidth < 992
        ? true
        : localStorage.getItem('og_sidebar_collapsed') === '1';

    // Agregar title para tooltips en modo collapsed
    function syncTooltips(isCollapsed) {
        sidebar.querySelectorAll('.sidebar-nav .nav-link').forEach(link => {
            const span = link.querySelector('span');
            if (span) {
                link.title = isCollapsed ? span.textContent.trim() : '';
            }
        });
    }

    function applySidebar(animate=true){
        if(!animate) sidebar.style.transition = 'none';

        if(window.innerWidth < 992){
            sidebar.classList.toggle('show', !collapsed);
            overlay.classList.toggle('show', !collapsed);
            sidebar.classList.remove('collapsed');
            header.classList.remove('sidebar-collapsed');
            main.classList.remove('sidebar-collapsed');
            syncTooltips(false);
        } else {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            sidebar.classList.toggle('collapsed', collapsed);
            header.classList.toggle('sidebar-collapsed', collapsed);
            main.classList.toggle('sidebar-collapsed', collapsed);
            syncTooltips(collapsed);
            // Cerrar acordeones al colapsar
            if (collapsed) {
                sidebar.querySelectorAll('.collapse.show').forEach(el => {
                    el.classList.remove('show');
                    const toggle = sidebar.querySelector(`[data-bs-target="#${el.id}"]`);
                    if (toggle) toggle.setAttribute('aria-expanded','false');
                });
            }
        }

        if(!animate) requestAnimationFrame(()=> sidebar.style.transition = '');
    }

    btnToggle?.addEventListener('click',()=>{
        collapsed = !collapsed;
        localStorage.setItem('og_sidebar_collapsed', collapsed ? '1' : '0');
        applySidebar();
    });
    btnClose?.addEventListener('click', ()=>{ collapsed=true;  applySidebar(); });
    overlay?.addEventListener('click',  ()=>{ collapsed=true;  applySidebar(); });
    window.addEventListener('resize',   ()=>applySidebar(false));
    applySidebar(false);
})();
</script>
