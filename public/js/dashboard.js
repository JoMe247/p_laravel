document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('reminders-overlay');
    const closeBtn = document.getElementById('close-reminders-overlay');

    if (!overlay) return;

    function openOverlay() {
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeOverlay() {
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    }

    document.addEventListener('click', function (e) {
        const openRemindersBtn = e.target.closest('#open-reminders');
        if (openRemindersBtn) {
            openOverlay();
            return;
        }

        const closeRemindersBtn = e.target.closest('#close-reminders-overlay');
        if (closeRemindersBtn) {
            closeOverlay();
            return;
        }

        if (e.target === overlay) {
            closeOverlay();
        }
    });
});