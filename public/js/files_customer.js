(function () {
    function openOverlay() {
        const overlay = document.getElementById("upload-overlay");
        if (!overlay) return;
        overlay.style.display = "flex";
        overlay.classList.add("active");
    }

    function closeOverlay() {
        const overlay = document.getElementById("upload-overlay");
        const input = document.getElementById("upload-file-input");
        const fileName = document.getElementById("selected-file-name");
        const dropZone = document.getElementById("drop-zone");

        if (!overlay) return;

        overlay.style.display = "none";
        overlay.classList.remove("active");

        if (input) input.value = "";
        if (fileName) fileName.textContent = "No file selected";
        if (dropZone) dropZone.classList.remove("dragover");
    }

    function updateFileName(file) {
        const fileName = document.getElementById("selected-file-name");
        if (!fileName) return;

        fileName.innerHTML = "<i class='bx bx-check' ></i>" + file ? "<i class='bx bx-check' ></i>" + file.name : "No file selected";
        document.getElementById("subtext-drag-file").style.display = "none";
        document.getElementById("upload-file-button").style.display = "";
    }

    function setSingleFileToInput(fileInput, file) {
        if (!fileInput || !file) return;

        try {
            const dt = new DataTransfer();
            dt.items.add(file);
            fileInput.files = dt.files;
        } catch (err) {
            // fallback por compatibilidad
            fileInput.files = fileInput.files;
        }
    }

    function handleDroppedFile(file) {
        const fileInput = document.getElementById("upload-file-input");
        if (!fileInput || !file) return;

        openOverlay();
        setSingleFileToInput(fileInput, file);
        updateFileName(file);
    }

    function bindUploadArea() {
        const dropZone = document.getElementById("drop-zone");
        const fileInput = document.getElementById("upload-file-input");

        if (!dropZone || !fileInput) return;

        dropZone.addEventListener("click", function () {
            fileInput.click();
        });

        fileInput.addEventListener("change", function () {
            const file = this.files && this.files[0] ? this.files[0] : null;
            updateFileName(file);
        });

        ["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
            dropZone.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ["dragenter", "dragover"].forEach(eventName => {
            dropZone.addEventListener(eventName, function () {
                dropZone.classList.add("dragover");
            });
        });

        ["dragleave", "drop"].forEach(eventName => {
            dropZone.addEventListener(eventName, function () {
                dropZone.classList.remove("dragover");
            });
        });

        dropZone.addEventListener("drop", function (e) {
            const files = e.dataTransfer.files;
            if (!files || !files.length) return;

            handleDroppedFile(files[0]);
        });
    }

    function bindTableDropZone() {
        const filesTable = document.querySelector(".files-table");
        if (!filesTable) return;

        ["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
            filesTable.addEventListener(eventName, function (e) {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        ["dragenter", "dragover"].forEach(eventName => {
            filesTable.addEventListener(eventName, function () {
                filesTable.classList.add("table-dragover");
            });
        });

        ["dragleave", "drop"].forEach(eventName => {
            filesTable.addEventListener(eventName, function (e) {
                const related = e.relatedTarget;
                if (eventName === "dragleave" && related && filesTable.contains(related)) return;
                filesTable.classList.remove("table-dragover");
            });
        });

        filesTable.addEventListener("drop", function (e) {
            filesTable.classList.remove("table-dragover");

            const files = e.dataTransfer.files;
            if (!files || !files.length) return;

            handleDroppedFile(files[0]);
        });
    }

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
            document.getElementById("upload-file-button").style.display = "none";
            closeOverlay();
            return;
        }

        const overlay = document.getElementById("upload-overlay");
        if (overlay && e.target === overlay) {
            document.getElementById("upload-file-button").style.display = "none";
            closeOverlay();
        }
    });

    document.addEventListener("keydown", function (e) {
        if (e.key === "Escape") closeOverlay();
    });

    document.addEventListener("DOMContentLoaded", function () {
        bindUploadArea();
        bindTableDropZone();
    });
})();

document.addEventListener('DOMContentLoaded', () => {
    const rows = document.querySelectorAll('.files-table tbody tr');
    const typeButtons = document.querySelectorAll('.file-type-btn');

    if (!rows.length || !typeButtons.length) return;

    typeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            typeButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const type = btn.dataset.type;

            rows.forEach(row => {
                const rowType = row.dataset.type;

                if (type === 'all') {
                    row.style.display = '';
                } else if (type === 'image') {
                    row.style.display = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'].includes(rowType)
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