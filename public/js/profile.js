$('#upload-photo-btn').on('click', function () {
    $('#photo-input').click();
});

try {

    $('#photo-input').on('change', function () {

        const customerId = $('#profile-wrapper').data('id'); // ← AHORA SÍ OBTIENE 53
        let formData = new FormData($('#photo-upload-form')[0]);

        $.ajax({
            url: `/customers/${customerId}/upload-photo`,
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    // Actualizar la foto en pantalla
                    $('#customer-photo').attr('src', res.path + '?v=' + Date.now());
                }
            },
            error: function (xhr) {
                console.error("UPLOAD ERROR:", xhr.responseText);
            }
        });
    });
    
} catch (error) {
    
}




/**
 * PROFILE – CUSTOMER ALERTS
 * -------------------------------------
 * Funciones:
 *  - Abrir SweetAlert para agregar una alerta
 *  - Guardar alerta vía AJAX
 *  - Mostrar alerta en pantalla
 *  - Eliminar alerta con confirmación
 */

// ===============================
// FUNCION: activar botón borrar alerta
// ===============================
function enableAlertDelete() {
    const deleteBtn = document.querySelector(".alert-delete");
    if (!deleteBtn) return;

    deleteBtn.addEventListener("click", function () {

        Swal.fire({
            title: "Delete Alert",
            text: "Are you sure you want to remove this alert?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (!result.isConfirmed) return;

            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const id = document.querySelector("meta[name='customer-id']").content;

            fetch(`/customers/${id}/alert/remove`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    // Borrar alerta visualmente
                    document.getElementById("customer-alert-box").remove();

                    // Volver a mostrar botón Add Alert
                    const container = document.getElementById("profile-alert-container");
                    const btn = document.createElement("button");
                    btn.id = "add-alert-btn";
                    btn.classList.add("button");
                    btn.innerHTML = `<i class='bx bx-error-circle'></i> Add Alert`;
                    container.appendChild(btn);

                    // Reactivar la función de agregar alerta
                    activateAddAlert();
                });
        });
    });
}

// ===============================
// FUNCION: activar botón ADD ALERT
// ===============================
function activateAddAlert() {
    const addAlertBtn = document.getElementById("add-alert-btn");
    if (!addAlertBtn) return;

    addAlertBtn.addEventListener("click", function () {

        Swal.fire({
            title: "Add Alert",
            input: "textarea",
            inputPlaceholder: "Type here...",
            inputAttributes: { "aria-label": "Alert text" },
            showCancelButton: true,
            confirmButtonText: "Save",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (!result.isConfirmed || !result.value.trim()) return;

            const alertText = result.value.trim();
            const csrf = document.querySelector('meta[name="csrf-token"]').content;
            const id = document.querySelector("meta[name='customer-id']").content;

            fetch(`/customers/${id}/alert`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf
                },
                body: JSON.stringify({ Alert: alertText })
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) return;

                    // Ocultar botón add alert
                    addAlertBtn.style.display = "none";

                    // Crear el recuadro de alerta
                    const alertBox = document.createElement("div");
                    alertBox.id = "customer-alert-box";
                    alertBox.classList.add("alert-box");
                    alertBox.innerHTML = `
                    <i class='bx bx-x alert-delete'></i>
                    <i class='bx bx-error bx-tada'></i>
                    <span>${data.alert}</span>
                `;

                    // Insertar recuadro
                    document.getElementById("profile-alert-container").appendChild(alertBox);

                    // Activar botón borrar alerta
                    enableAlertDelete();

                    Swal.fire("Alert Saved!", "", "success");
                });
        });
    });
}

// ===============================
// INICIALIZAR TODO
// ===============================
document.addEventListener("DOMContentLoaded", function () {

    // Si ya existe una alerta → activar borrado
    if (document.getElementById("customer-alert-box")) {
        enableAlertDelete();
    }

    // Activar botón Add Alert
    activateAddAlert();
});

/**
 * PROFILE – CUSTOMER NOTES
 * -------------------------------------
 * Funciones:
 *  - Abrir overlay para agregar una nota
 *  - Guardar nota vía AJAX
 *  - Mostrar nota en pantalla
 *  - Eliminar nota con confirmación
 */

$(document).ready(function () {

    const customerId = $("meta[name='customer-id']").attr("content");
    loadNotes();

    $("#add-note-btn").on("click", function () {
        $("#note-overlay").css("display", "flex");
        loadCustomerPoliciesIntoSelect();
    });

    $("#note-cancel").on("click", function () {
        $("#note-overlay").hide();
    });

    $("#note-save").on("click", function () {

        $.post(`/customers/${customerId}/notes`, {
            _token: $("meta[name='csrf-token']").attr("content"),
            policy: $("#note-policy").val(),
            subject: $("#note-subject").val(),
            note: $("#note-text").val(),
        })
            .done(function () {
                $("#note-overlay").hide();
                $("#note-policy").val("");
                $("#note-subject").val("");
                $("#note-text").val("");
                loadNotes();
            });
    });

});


// 📌 Función para cargar notas
function loadNotes() {

    const customerId = $("meta[name='customer-id']").attr("content");

    $.get(`/customers/${customerId}/notes`, function (notes) {

        let html = "";

        notes.forEach(note => {

            let formattedDate = note.created_at
                ? new Date(note.created_at).toLocaleString()
                : "";

            html += `
                <div class="note-item">

                    <small>${formattedDate}</small>

                    <b>Policy:</b> ${note.policy ?? '—'}<br>
                    <b>Subject:</b> ${note.subject}<br>
                    <b>By:</b> ${note.created_by}<br><br>

                    <div style="white-space:pre-line;">
                        ${note.note}
                    </div>

                    <button class="note-delete-btn" onclick="deleteNote(${note.id})">
                        <i class='bx bx-trash'></i>
                    </button>

                </div>`;
        });

        $("#notes-list").html(html);
    });
}


// 📌 Eliminar nota
function deleteNote(noteId) {

    $.ajax({
        url: `/customers/notes/${noteId}`,
        method: "DELETE",
        data: {
            _token: $("meta[name='csrf-token']").attr("content")
        },
        success: function () {
            loadNotes();
        }
    });

}

function loadCustomerPoliciesIntoSelect() {
    const customerId = $("meta[name='customer-id']").attr("content");

    // Limpia el select y deja opción default
    const $sel = $("#note-policy");
    $sel.html(`<option value="">— Select policy —</option>`);

    $.get(`/customers/${customerId}/policies`, function (policies) {

        // policies viene como array: ["POL-0001","POL-0002",...]
        if (!policies || policies.length === 0) {
            $sel.append(`<option value="" disabled>(No policies found)</option>`);
            return;
        }

        policies.forEach(pol => {
            $sel.append(`<option value="${pol}">${pol}</option>`);
        });
    });
}

// ===============================
// PROFILE – EDIT CUSTOMER NAME (H2 editable) ✅ FIX CURSOR JUMP
// ===============================
document.addEventListener("DOMContentLoaded", function () {
    const nameEl = document.getElementById("customer-name-edit");
    const hiddenInput = document.getElementById("customer-name-input");
    if (!nameEl || !hiddenInput) return;

    // Pegar solo texto plano
    nameEl.addEventListener("paste", function (e) {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData("text");
        document.execCommand("insertText", false, text);
    });

    // Mientras escribe: NO reasignar innerText (para no mover el cursor)
    nameEl.addEventListener("input", function () {
        hiddenInput.value = (nameEl.innerText || "").trim();
    });

    // Al salir: ahora sí normaliza espacios y actualiza ambos
    nameEl.addEventListener("blur", function () {
        const cleaned = (nameEl.innerText || "")
            .trim()
            .replace(/\s+/g, " ");

        nameEl.innerText = cleaned || "—";
        hiddenInput.value = cleaned;
    });

    // Enter no mete salto de línea
    nameEl.addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
            nameEl.blur();
        }
    });
});

// ===============================
// PROFILE – CUSTOMER VIEW LOG
// ===============================
document.addEventListener("DOMContentLoaded", function () {
    const openBtn = document.getElementById("view-profile-log-btn");
    const overlay = document.getElementById("customer-views-overlay");
    const closeBtn = document.getElementById("customer-views-close");

    if (openBtn && overlay) {
        openBtn.addEventListener("click", function () {
            overlay.style.display = "flex";
        });
    }

    if (closeBtn && overlay) {
        closeBtn.addEventListener("click", function () {
            overlay.style.display = "none";
        });
    }

    if (overlay) {
        overlay.addEventListener("click", function (e) {
            if (e.target === overlay) {
                overlay.style.display = "none";
            }
        });
    }
});

// ===============================
// PROFILE – REGISTER VIEW ONLY ON ENTRY, NOT ON RELOAD
// ===============================
document.addEventListener("DOMContentLoaded", function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const logUrl = document.querySelector('meta[name="customer-view-log-url"]')?.content;

    if (!csrf || !logUrl) return;

    function getNavigationType() {
        const navEntries = performance.getEntriesByType("navigation");
        if (navEntries && navEntries.length > 0) {
            return navEntries[0].type; // navigate | reload | back_forward
        }

        // fallback navegadores viejos
        if (performance.navigation) {
            if (performance.navigation.type === 1) return "reload";
            if (performance.navigation.type === 2) return "back_forward";
        }

        return "navigate";
    }

    const navType = getNavigationType();

    // No registrar si fue recarga manual
    if (navType === "reload") {
        return;
    }

    fetch(logUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf,
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({})
    })
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.entry) return;

            prependCustomerViewLog(data.entry);
        })
        .catch(err => {
            console.error("Error logging customer view:", err);
        });

    function prependCustomerViewLog(entry) {
        const list = document.getElementById("customer-views-list");
        if (!list) return;

        const empty = document.getElementById("customer-view-empty");
        if (empty) empty.remove();

        const item = document.createElement("div");
        item.className = "customer-view-item";

        item.innerHTML = `
            <div class="customer-view-meta">
                <div><strong>Date:</strong> ${entry.full_date ?? "-"}</div>
                <div><strong>Note Type:</strong> ${entry.type ?? "CUSTOMER VIEW"}</div>
                <div><strong>By:</strong> ${entry.by ?? "-"}</div>
            </div>

            <div class="customer-view-text">
                ${entry.message ?? ""}
            </div>
        `;

        list.prepend(item);
    }
});