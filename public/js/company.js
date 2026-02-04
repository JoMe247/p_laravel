// public/js/company.js

document.addEventListener("DOMContentLoaded", () => {

    const btnAdd = document.getElementById("btn-add-company");
    const overlay = document.getElementById("company-overlay");
    const form = document.getElementById("company-form");

    const baseUrl = document.querySelector("meta[name=base-url]").content;
    const token = document.querySelector("meta[name=csrf-token]").content;


    // ---------------------------
    // Phone input: only digits + max 10
    // ---------------------------
    const phoneInput = form?.querySelector("input[name='phone_number']");
    if (phoneInput) {
        phoneInput.addEventListener("input", () => {
            phoneInput.value = (phoneInput.value || "").replace(/\D/g, "").slice(0, 10);
        });
    }

    // ---------------------------
    // Search + Type filter combined
    // ---------------------------
    const searchInput = document.getElementById("company-search");
    const filterButtons = document.querySelectorAll(".filter-btn");
    const cards = document.querySelectorAll(".company-grid .company-card");

    let activeTypeFilter = "all";
    let activeSearch = "";

    function applyFilters() {
        const q = (activeSearch || "").trim().toLowerCase();

        cards.forEach(card => {
            const type = (card.getAttribute("data-type") || "").toLowerCase();

            const name = (card.querySelector(".company-title")?.textContent || "").toLowerCase();
            const phone = (card.querySelector(".field-phone span")?.textContent || "").toLowerCase();

            const matchType = (activeTypeFilter === "all") || (type === activeTypeFilter.toLowerCase());
            const matchSearch = !q || name.includes(q) || phone.includes(q);

            card.style.display = (matchType && matchSearch) ? "flex" : "none";
        });
    }

    // Search listener
    if (searchInput) {
        searchInput.addEventListener("input", () => {
            activeSearch = searchInput.value;
            applyFilters();
        });
    }

    // Type filter listeners (reutiliza tu UI active)
    if (filterButtons.length) {
        filterButtons.forEach(btn => {
            btn.addEventListener("click", () => {
                filterButtons.forEach(b => b.classList.remove("active"));
                btn.classList.add("active");

                activeTypeFilter = btn.getAttribute("data-filter") || "all";
                applyFilters();
            });
        });

        // default active "All"
        const allBtn = document.querySelector('.filter-btn[data-filter="all"]');
        if (allBtn) allBtn.classList.add("active");
    }

    // -------------------------------------------------------------
    // Abrir overlay para crear
    // -------------------------------------------------------------
    if (btnAdd) {
        btnAdd.addEventListener("click", () => {
            form.reset();
            form.removeAttribute("data-id");
            overlay.style.display = "flex";
        });
    }

    // -------------------------------------------------------------
    // Cerrar overlay
    // -------------------------------------------------------------
    window.closeCompanyModal = function () {
        overlay.style.display = "none";
        form.reset();
        form.removeAttribute("data-id");
    };

    // -------------------------------------------------------------
    // Guardar (CREATE / UPDATE)
    // -------------------------------------------------------------
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const id = form.getAttribute("data-id"); // null => create
            const url = id
                ? `${baseUrl}/company/update/${id}`
                : `${baseUrl}/company/store`;

            // ✅ Primero crear FormData
            const data = new FormData(form);

            // ✅ Validación phone 10 dígitos (antes de enviar)
            const rawPhone = (data.get("phone_number") || "").toString();
            const phone = rawPhone.replace(/\D/g, "").slice(0, 10);

            if (phone.length !== 10) {
                Swal.fire("Phone Number", "Phone number must be exactly 10 digits.", "warning");
                return;
            }

            // ✅ Normaliza lo que se manda al backend
            data.set("phone_number", phone);

            try {
                let req = await fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": token
                    },
                    body: data
                });

                let res = await req.json();

                if (!res.ok) {
                    Swal.fire("Error", res.msg || "Could not save company.", "error");
                    return;
                }

                // UPDATE
                if (id) {
                    updateCompanyCard(id, res.data);
                    Swal.fire("Saved!", "Company updated.", "success");
                    closeCompanyModal();
                }
                // CREATE
                else {
                    Swal.fire("Saved!", "Company created.", "success")
                        .then(() => location.reload());
                }

            } catch (err) {
                Swal.fire("Error", "Unexpected error while saving.", "error");
                console.error(err);
            }
        });
    }

});

// ------------------------------------------------------------------
// EDITAR: cargar datos en overlay
// ------------------------------------------------------------------
window.editCompany = async function (id) {


    const baseUrl = document.querySelector("meta[name=base-url]").content;

    try {
        let req = await fetch(`${baseUrl}/company/edit/${id}`);
        let res = await req.json();

        if (!res.ok) {
            Swal.fire("Error", "Could not load company data.", "error");
            return;
        }

        const c = res.data;
        const overlay = document.getElementById("company-overlay");
        const form = document.getElementById("company-form");

        // Mostrar overlay
        overlay.style.display = "flex";

        // Cargar datos al formulario
        form.company_name.value = c.company_name ?? "";
        form.user_name.value = c.user_name ?? "";
        form.phone_number.value = c.phone_number ?? "";
        form.password.value = c.password ?? "";
        form.type.value = c.type ?? "";
        form.description.value = c.description ?? "";
        form.url.value = c.url ?? "";

        // Marcar modo edición
        form.setAttribute("data-id", id);

        // --- Vista previa de imagen ---
        const preview = document.getElementById("preview-current-picture");

        if (c.picture && c.picture.trim() !== "") {
            preview.src = `${baseUrl}/uploads/company/${c.picture}`;
            preview.style.display = "block";
        } else {
            preview.style.display = "none";
        }

    } catch (e) {
        console.error(e);
        Swal.fire("Error", "Unexpected error while loading company.", "error");
    }

};

// ------------------------------------------------------------------
// DELETE: borrar tarjeta y registro
// ------------------------------------------------------------------
window.deleteCompany = function (id) {

    const baseUrl = document.querySelector("meta[name=base-url]").content;
    const token = document.querySelector("meta[name=csrf-token]").content;

    Swal.fire({
        title: "Delete?",
        text: "This company will be permanently deleted.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Delete"
    }).then(async (res) => {
        if (!res.isConfirmed) return;

        try {
            let req = await fetch(`${baseUrl}/company/delete/${id}`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": token
                }
            });

            let data = await req.json();

            if (!data.ok) {
                Swal.fire("Error", data.msg || "Could not delete company.", "error");
                return;
            }

            // Eliminar tarjeta del DOM
            const card = document.querySelector(`.company-card[data-id="${id}"]`);
            if (card) card.remove();

            Swal.fire("Deleted!", "Company removed.", "success");

        } catch (e) {
            Swal.fire("Error", "Unexpected error while deleting.", "error");
            console.error(e);
        }
    });
};

// ------------------------------------------------------------------
// LIVE UPDATE: actualizar tarjeta sin recargar
// ------------------------------------------------------------------
window.updateCompanyCard = function (id, c) {

    const baseUrl = document.querySelector("meta[name=base-url]").content;
    const card = document.querySelector(`.company-card[data-id="${id}"]`);
    if (!card) return;

    // Title
    const title = card.querySelector(".company-title");
    if (title) title.textContent = c.company_name ?? "";

    // User
    const userSpan = card.querySelector(".field-user span");
    if (userSpan) userSpan.textContent = c.user_name ?? "";

    // Phone
    const phoneSpan = card.querySelector(".field-phone span");
    if (phoneSpan) phoneSpan.textContent = c.phone_number ?? "";

    // Password
    const passSpan = card.querySelector(".field-password span");
    if (passSpan) passSpan.textContent = c.password ?? "";

    // Type
    const typeSpan = card.querySelector(".field-type span");
    if (typeSpan) typeSpan.textContent = c.type ?? "";

    // Description
    const descSpan = card.querySelector(".field-desc span");
    if (descSpan) descSpan.textContent = c.description ?? "";

    // URL
    const urlA = card.querySelector(".field-url a");
    if (urlA) {
        urlA.textContent = c.url ?? "";
        urlA.href = c.url ?? "#";
    }

    // Picture
    const img = card.querySelector(".company-img");
    if (img) {
        if (c.picture) img.src = `${baseUrl}/uploads/company/${c.picture}`;
    }
};

// ... todo tu código company.js ...

// ---------------------------------------------------------------------
// Vista previa cuando seleccionas una nueva imagen
// ---------------------------------------------------------------------
document.addEventListener("DOMContentLoaded", () => {
    const inputPicture = document.querySelector("input[name='picture']");

    if (inputPicture) {
        inputPicture.addEventListener("change", function () {
            const preview = document.getElementById("preview-current-picture");
            const file = this.files[0];

            if (file) {
                preview.src = URL.createObjectURL(file);
                preview.style.display = "block";
            }
        });
    }
});

// Cerrar overlay al hacer click afuera
document.addEventListener("click", function (e) {
    const overlay = document.getElementById("company-overlay");
    const modal = document.getElementById("company-modal");

    if (!overlay || !modal) return;

    // Si el overlay está activo y el click NO fue dentro del modal
    if (overlay.style.display === "flex" && !modal.contains(e.target)) {
        if (e.target === overlay) {
            closeCompanyModal();
        }
    }
});

// ----------------------------------------------
// FILTRO DE TARJETAS POR TYPE
// ----------------------------------------------

window.addEventListener("load", () => {

    const filterButtons = document.querySelectorAll(".filter-btn");
    const cards = document.querySelectorAll(".company-grid .company-card");

    filterButtons.forEach(btn => {

        btn.addEventListener("click", () => {

            // quitar active
            filterButtons.forEach(b => b.classList.remove("active"));
            btn.classList.add("active");

            const filter = btn.getAttribute("data-filter");

            cards.forEach(card => {
                const type = card.getAttribute("data-type");

                if (filter === "all") {
                    card.style.display = "flex";
                } else if (type === filter) {
                    card.style.display = "flex";
                } else {
                    card.style.display = "none";
                }
            });
        });
    });

});
