document.addEventListener("DOMContentLoaded", () => {
    const csrf =
        window.documentsCsrf ||
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

    function buildRoute(template, id) {
        return template.replace(":id", id);
    }

    async function postAction(url, button) {
        const oldHtml = button.innerHTML;
        button.disabled = true;
        button.style.opacity = "0.6";

        try {
            const res = await fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({}),
            });

            const data = await res.json();

            if (!res.ok || !data.ok) {
                throw new Error(
                    data.message || data.error || "Unexpected error",
                );
            }

            await Swal.fire({
                icon: "success",
                title: "Done",
                text: data.message || "Action completed successfully.",
            });
        } catch (err) {
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: err.message || "Action could not be completed.",
            });
        } finally {
            button.disabled = false;
            button.style.opacity = "1";
            button.innerHTML = oldHtml;
        }
    }

    async function deleteAction(url, button, row) {
        const confirm = await Swal.fire({
            icon: "warning",
            title: "Delete document?",
            text: "This will delete the document, its URL record, signing record and stored PDF.",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            cancelButtonText: "Cancel",
        });

        if (!confirm.isConfirmed) return;

        const oldHtml = button.innerHTML;
        button.disabled = true;
        button.style.opacity = "0.6";

        try {
            const res = await fetch(url, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                },
            });

            const data = await res.json();

            if (!res.ok || !data.ok) {
                throw new Error(
                    data.message || data.error || "Unexpected error",
                );
            }

            if (row) {
                row.remove();
            }

            const totalEl = document.getElementById("documents-total");
            if (totalEl) {
                const current = parseInt(totalEl.textContent || "0", 10);
                totalEl.textContent = Math.max(0, current - 1);
            }

            await Swal.fire({
                icon: "success",
                title: "Deleted",
                text: data.message || "Document deleted successfully.",
            });
        } catch (err) {
            await Swal.fire({
                icon: "error",
                title: "Error",
                text: err.message || "Document could not be deleted.",
            });
        } finally {
            button.disabled = false;
            button.style.opacity = "1";
            button.innerHTML = oldHtml;
        }
    }

    document.querySelectorAll(".btn-resend-phone").forEach((button) => {
        button.addEventListener("click", async () => {
            const docId = button.dataset.docId;
            if (!docId) return;

            const url = buildRoute(window.documentsRoutes.resendPhone, docId);
            await postAction(url, button);
        });
    });

    document.querySelectorAll(".btn-resend-email").forEach((button) => {
        button.addEventListener("click", async () => {
            const docId = button.dataset.docId;
            if (!docId) return;

            const url = buildRoute(window.documentsRoutes.resendEmail, docId);
            await postAction(url, button);
        });
    });

    document.querySelectorAll(".btn-delete-document").forEach((button) => {
        button.addEventListener("click", async () => {
            const docId = button.dataset.docId;
            if (!docId) return;

            const row = button.closest("tr");
            const url = buildRoute(window.documentsRoutes.destroy, docId);
            await deleteAction(url, button, row);
        });
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const printBtn = document.getElementById("btn-print-documents");
    const printMenu = document.getElementById("print-documents-menu");

    if (!printBtn || !printMenu) return;

    let templatesLoaded = false;

    printBtn.addEventListener("click", async (e) => {
        e.stopPropagation();

        if (!templatesLoaded) {
            await loadPrintTemplatesMenu(printMenu);
            templatesLoaded = true;
        }

        printMenu.classList.toggle("hidden");
    });

    printMenu.addEventListener("click", (e) => {
        e.stopPropagation();
    });

    document.addEventListener("click", () => {
        printMenu.classList.add("hidden");
    });
});

async function loadPrintTemplatesMenu(menuEl) {
    try {
        const res = await fetch(window.documentsRoutes.templatesOptions, {
            headers: { Accept: "application/json" },
        });

        const json = await res.json();

        menuEl.innerHTML = "";

        if (
            !json.ok ||
            !Array.isArray(json.templates) ||
            !json.templates.length
        ) {
            const empty = document.createElement("div");
            empty.className = "print-documents-item";
            empty.style.cursor = "default";
            empty.textContent = "No templates available";
            menuEl.appendChild(empty);
            return;
        }

        json.templates.forEach((tpl) => {
            const item = document.createElement("button");
            item.type = "button";
            item.className = "print-documents-item";
            item.innerHTML = `
                <i class='bx bxs-file-pdf'></i>
                <span>${escapeHtml(tpl.template_name || "Untitled")}</span>
            `;

            item.addEventListener("click", () => {
                const pdfUrl =
                    window.documentsRoutes.templateFile.replace(":id", tpl.id) +
                    "?v=" +
                    Date.now();

                openPdfViewer(pdfUrl);
                menuEl.classList.add("hidden");
            });

            menuEl.appendChild(item);
        });
    } catch (error) {
        console.error(error);
        menuEl.innerHTML = `
            <div class="print-documents-item" style="cursor:default;">
                Error loading templates
            </div>
        `;
    }
}

function openPdfViewer(pdfUrl) {
    const newWindow = window.open(pdfUrl, "_blank", "noopener,noreferrer");
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, function (m) {
        return {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;",
        }[m];
    });
}
