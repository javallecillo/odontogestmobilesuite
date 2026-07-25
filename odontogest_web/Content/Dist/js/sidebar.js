document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent    = document.querySelector('.main-content');
    const topHeader      = document.querySelector('.top-header');
    const menuToggle     = document.getElementById('btnMenuToggle');
    const btnClose       = document.getElementById('btnCloseSidebar');

    if (!sidebar || !mainContent || !topHeader) return;

    const KEY = 'og-sidebar';

    function closeSubmenus() {
        sidebar.querySelectorAll('.collapse.show').forEach(s => s.classList.remove('show'));
        sidebar.querySelectorAll('[aria-expanded="true"]').forEach(l => l.setAttribute('aria-expanded', 'false'));
    }

    function setCollapsed(collapsed) {
        sidebar.classList.toggle('collapsed', collapsed);
        mainContent.classList.toggle('sidebar-collapsed', collapsed);
        topHeader.classList.toggle('sidebar-collapsed', collapsed);
        if (collapsed) closeSubmenus();
        localStorage.setItem(KEY, collapsed ? '1' : '0');
    }

    function openMobile()  {
        sidebar.classList.add('show');
        sidebarOverlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    function closeMobile() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    menuToggle?.addEventListener('click', () => {
        if (window.innerWidth >= 992) {
            setCollapsed(!sidebar.classList.contains('collapsed'));
        } else {
            sidebar.classList.contains('show') ? closeMobile() : openMobile();
        }
    });

    btnClose?.addEventListener('click', closeMobile);
    sidebarOverlay?.addEventListener('click', closeMobile);

    sidebar.addEventListener('mouseleave', () => {
        if (sidebar.classList.contains('collapsed')) closeSubmenus();
    });

    // Cerrar submenú cuando el sidebar está colapsado y se hace hover
    sidebar.addEventListener('mouseenter', () => {
        // Solo re-abre submenus en modo expandido
    });

    sidebar.querySelectorAll('.sidebar-nav .nav-link:not([data-bs-toggle])').forEach(link => {
        link.addEventListener('click', () => { if (window.innerWidth < 992) closeMobile(); });
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 992) {
            sidebarOverlay.classList.remove('show');
            document.body.style.overflow = '';
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('sidebar-collapsed');
            topHeader.classList.remove('sidebar-collapsed');
        }
    });

    // Acordeón — un submenu abierto a la vez
    const sidebarMenu = document.getElementById('sidebarMenu');
    sidebarMenu?.addEventListener('show.bs.collapse', e => {
        sidebarMenu.querySelectorAll('.collapse.show').forEach(open => {
            if (open !== e.target) {
                bootstrap.Collapse.getInstance(open)?.hide();
            }
        });
    });

    // Restaurar estado desde localStorage (solo desktop)
    if (window.innerWidth >= 992) {
        setCollapsed(localStorage.getItem(KEY) === '1');
    }
});
