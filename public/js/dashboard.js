document.addEventListener('DOMContentLoaded', () => {

    const openBtn = document.querySelector('.quick-item');
    const overlay = document.getElementById('reminders-overlay');
    const closeBtn = document.getElementById('close-reminders-overlay');

    if (!openBtn || !overlay) return;

    openBtn.addEventListener('click', () => {
        overlay.classList.add('show');
        document.body.style.overflow = 'hidden';
    });

    closeBtn.addEventListener('click', () => {
        overlay.classList.remove('show');
        document.body.style.overflow = '';
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('click', function (e) {
    const btn = e.target.closest('#open-reminders');
    if (!btn) return;

    const overlay = document.getElementById('reminders-overlay');
    if (!overlay) {
        console.error('Reminders overlay not found');
        return;
    }

    overlay.classList.add('show');
    document.body.style.overflow = 'hidden';
});
