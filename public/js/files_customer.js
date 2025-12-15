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

filter.addEventListener('change', () => {
    const value = filter.value;
    const rows = Array.from(tbody.querySelectorAll('tr'));

    // Mostrar todos
    if (value === 'all') {
        rows.forEach(row => row.style.display = '');
        return;
    }

    // 🔹 ORDENAR
    if (value === 'name') {
        rows.sort((a, b) =>
            a.children[0].innerText.localeCompare(b.children[0].innerText)
        );
    }

    if (value === 'date') {
        rows.sort((a, b) =>
            new Date(b.children[1].innerText) - new Date(a.children[1].innerText)
        );
    }

    if (value === 'user') {
        rows.sort((a, b) =>
            a.children[2].innerText.localeCompare(b.children[2].innerText)
        );
    }

    if (['name', 'date', 'user'].includes(value)) {
        tbody.innerHTML = '';
        rows.forEach(r => tbody.appendChild(r));
        return;
    }

    // 🔹 FILTRAR POR TIPO DE ARCHIVO
    rows.forEach(row => {
        row.style.display = (row.dataset.type === value) ? '' : 'none';
    });
});

