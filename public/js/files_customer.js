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
