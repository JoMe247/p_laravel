(function () {
    function openOverlay() {
        const overlay = document.getElementById("upload-overlay");
        if (!overlay) return;
        overlay.style.display = "flex";
        overlay.classList.add("active");
    }

    function closeOverlay() {
        const overlay = document.getElementById("upload-overlay");
        if (!overlay) return;
        overlay.style.display = "none";
        overlay.classList.remove("active");
    }

    // Delegación: funciona aunque el DOM cambie
    document.addEventListener("click", function (e) {
        const openBtn = e.target.closest("#open-upload");
        const closeBtn = e.target.closest("#close-upload");

        if (openBtn) {
            e.preventDefault();
            openOverlay();
            return;
        }

        if (closeBtn) {
            e.preventDefault();
            closeOverlay();
            return;
        }

        // Click fuera del modal (sobre el overlay)
        const overlay = document.getElementById("upload-overlay");
        if (overlay && e.target === overlay) {
            closeOverlay();
        }
    });

    // ESC para cerrar
    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeOverlay();
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('.files-table tbody tr');
    const typeButtons = document.querySelectorAll('.file-type-btn');

    if (!rows.length || !typeButtons.length) return;

    typeButtons.forEach(btn => {
        btn.addEventListener('click', () => {

            // Estado activo
            typeButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const type = btn.dataset.type;

            rows.forEach(row => {
                const rowType = row.dataset.type;

                if (type === 'all') {
                    row.style.display = '';
                } else if (type === 'image') {
                    row.style.display = ['png', 'jpg', 'jpeg', 'gif', 'webp'].includes(rowType)
                        ? ''
                        : 'none';
                } else if (type === 'doc') {
                    row.style.display = ['doc', 'docx', 'xls', 'xlsx'].includes(rowType)
                        ? ''
                        : 'none';
                } else if (type === 'zip') {
                    row.style.display = ['zip', 'rar'].includes(rowType)
                        ? ''
                        : 'none';
                } else {
                    row.style.display = (rowType === type) ? '' : 'none';
                }


            });
        });
    });
});

