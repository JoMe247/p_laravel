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

document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.querySelector('input[name="remind_at"]');
    if (!dateInput) return;

    const now = new Date();

    // Ajuste a formato YYYY-MM-DDTHH:MM
    const pad = n => String(n).padStart(2, '0');
    const minDate =
        now.getFullYear() + '-' +
        pad(now.getMonth() + 1) + '-' +
        pad(now.getDate()) + 'T' +
        pad(now.getHours()) + ':' +
        pad(now.getMinutes());

    dateInput.min = minDate;
});

document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-delete-reminder');
    if (!btn) return;

    const reminderId = btn.dataset.id;
    const customerId = document
        .querySelector('meta[name="customer-id"]')
        .getAttribute('content');

    Swal.fire({
        title: 'Delete reminder?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#666',
        confirmButtonText: 'Yes, delete it'
    }).then((result) => {
        if (!result.isConfirmed) return;

        fetch(`/reminders/${customerId}/${reminderId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                btn.closest('tr').remove();
            }
        });
    });
});
