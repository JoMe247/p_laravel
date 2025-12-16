(function () {
    const overlay = document.getElementById('reminderOverlay');
    const openBtn = document.getElementById('openReminderOverlay');
    const closeBtn = document.getElementById('closeReminderOverlay');
    const cancelBtn = document.getElementById('cancelReminderBtn');
    const form = document.getElementById('reminderForm');
    const perPageSelect = document.getElementById('perPageSelect');

    function openOverlay() {
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeOverlay() {
        overlay.classList.remove('show');
        document.body.style.overflow = '';
        if (form) form.reset();
    }

    if (openBtn) openBtn.addEventListener('click', openOverlay);
    if (closeBtn) closeBtn.addEventListener('click', closeOverlay);
    if (cancelBtn) cancelBtn.addEventListener('click', closeOverlay);

    // Cerrar al hacer click fuera del cuadro
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeOverlay();
        });
    }

    // ESC para cerrar
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay && overlay.classList.contains('show')) {
            closeOverlay();
        }
    });

    // Cambiar perPage manteniendo q
    if (perPageSelect && window.__REMINDERS__) {
        perPageSelect.addEventListener('change', () => {
            const url = new URL(window.__REMINDERS__.indexUrl, window.location.origin);
            const q = window.__REMINDERS__.q || '';
            if (q) url.searchParams.set('q', q);
            url.searchParams.set('perPage', perPageSelect.value);
            window.location.href = url.toString();
        });
    }
})();
