/**
 * og-ui.js — Helpers de UI para OdontoGest Panel Web
 *
 * Exporta:
 *   OgSwal.confirm(opts)   → confirmación con tema OG
 *   OgSwal.success(msg)    → toast de éxito
 *   OgSwal.error(msg)      → toast de error
 *   OgSwal.input(opts)     → diálogo con campo de texto
 *   ogModal.open(id)       → abrir modal por ID
 *   ogModal.close(id)      → cerrar modal por ID
 */

/* ── Tema base SweetAlert2 ─────────────────────────────────── */
const _swalBase = {
    confirmButtonColor:   '#1A56AB',
    cancelButtonColor:    '#6B7280',
    background:           'var(--card-bg)',
    color:                'var(--body-text)',
    customClass: {
        popup:          'og-swal-popup',
        confirmButton:  'og-swal-confirm',
        cancelButton:   'og-swal-cancel',
        title:          'og-swal-title',
    },
};

/* Inyectar estilos inline para las clases de Swal */
(function injectSwalStyles() {
    if (document.getElementById('og-swal-styles')) return;
    const s = document.createElement('style');
    s.id = 'og-swal-styles';
    s.textContent = `
        .og-swal-popup {
            border-radius: 16px !important;
            box-shadow: 0 24px 64px rgba(0,0,0,.22) !important;
            border: 1px solid var(--card-border) !important;
            font-family: 'Inter','Segoe UI',system-ui,sans-serif !important;
        }
        .og-swal-title {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: var(--body-text) !important;
        }
        .swal2-html-container {
            font-size: 14px !important;
            color: var(--body-text-muted) !important;
        }
        .og-swal-confirm, .og-swal-cancel {
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            padding: 9px 22px !important;
            border: none !important;
        }
        .og-swal-confirm { background: #1A56AB !important; }
        .og-swal-confirm:hover { background: #154a96 !important; }
        .og-swal-cancel  { background: #F0F3F8 !important; color: #374151 !important; }
        .og-swal-cancel:hover  { background: #E2E8F0 !important; }
        /* Modo oscuro */
        [data-theme="dark"] .og-swal-popup  { background: #1E293B !important; border-color: #334155 !important; }
        [data-theme="dark"] .og-swal-title  { color: #E2E8F0 !important; }
        [data-theme="dark"] .swal2-html-container { color: #94A3B8 !important; }
        [data-theme="dark"] .og-swal-cancel { background: #253349 !important; color: #CBD5E1 !important; }
        [data-theme="dark"] .og-swal-cancel:hover { background: #334155 !important; }
    `;
    document.head.appendChild(s);
})();

/* ── OgSwal ────────────────────────────────────────────────── */
const OgSwal = {

    /**
     * Diálogo de confirmación.
     * @param {Object} opts
     * @param {string} opts.title
     * @param {string} [opts.text]
     * @param {string} [opts.confirmText='Confirmar']
     * @param {string} [opts.cancelText='Cancelar']
     * @param {string} [opts.confirmColor]  — por defecto azul OG
     * @param {string} [opts.icon='question']
     * @returns {Promise<boolean>}
     */
    async confirm({ title, text, confirmText = 'Confirmar', cancelText = 'Cancelar',
                    confirmColor, icon = 'question' } = {}) {
        const result = await Swal.fire({
            ..._swalBase,
            title,
            text,
            icon,
            showCancelButton:   true,
            confirmButtonText:  confirmText,
            cancelButtonText:   cancelText,
            confirmButtonColor: confirmColor ?? '#1A56AB',
        });
        return result.isConfirmed;
    },

    /**
     * Confirmación destructiva (roja).
     */
    async danger({ title, text, confirmText = 'Eliminar', cancelText = 'Cancelar', icon = 'warning' } = {}) {
        return this.confirm({ title, text, confirmText, cancelText, icon, confirmColor: '#DC2626' });
    },

    /**
     * Toast de éxito (esquina superior derecha, auto-cierre 2.5s).
     */
    success(msg) {
        Swal.fire({
            ..._swalBase,
            icon:              'success',
            title:             msg,
            toast:             true,
            position:          'top-end',
            showConfirmButton: false,
            timer:             2500,
            timerProgressBar:  true,
        });
    },

    /**
     * Toast de error.
     */
    error(msg) {
        Swal.fire({
            ..._swalBase,
            icon:              'error',
            title:             msg,
            toast:             true,
            position:          'top-end',
            showConfirmButton: false,
            timer:             3500,
            timerProgressBar:  true,
        });
    },

    /**
     * Diálogo con campo de texto.
     * @returns {Promise<string|null>} valor ingresado o null si canceló
     */
    async input({ title, placeholder, confirmText = 'Aceptar', cancelText = 'Cancelar',
                  validator, inputType = 'text' } = {}) {
        const result = await Swal.fire({
            ..._swalBase,
            title,
            input:            inputType,
            inputPlaceholder: placeholder,
            showCancelButton:  true,
            confirmButtonText: confirmText,
            cancelButtonText:  cancelText,
            inputValidator:    validator,
        });
        return result.isConfirmed ? result.value : null;
    },
};

/* ── ogModal ────────────────────────────────────────────────── */
const ogModal = {
    open(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'flex';
    },
    close(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    },
};

/* ── Cerrar modales con tecla Escape ───────────────────────── */
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.querySelectorAll('[id^="modal"]').forEach(m => {
        if (m.style.display === 'flex') m.style.display = 'none';
    });
});

/* ── Exponer globalmente ────────────────────────────────────── */
window.OgSwal  = OgSwal;
window.ogModal = ogModal;
