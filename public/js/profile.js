$('#upload-photo-btn').on('click', function () {
    $('#photo-input').click();
});

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
        error: function(xhr) {
            console.error("UPLOAD ERROR:", xhr.responseText);
        }
    });
});


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
            inputLabel: "Write an alert or note for this customer",
            inputPlaceholder: "Type the alert here...",
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

    const customerId = $("#profile-wrapper").data("id");

    function loadNotes() {
        $.get(`/customers/${customerId}/notes`, function (notes) {

            $("#notes-list").html("");

            notes.forEach(n => {
                $("#notes-list").append(`
                    <div class="note-item">
                        <div class="note-meta">
                            <b>Policy:</b> ${n.policy ?? ''} |
                            <b>Subject:</b> ${n.subject ?? ''}<br>
                            <b>By:</b> ${n.created_by} |
                            <b>Date:</b> ${n.created_at}
                        </div>

                        <p>${n.note}</p>

                        <div class="note-delete" data-id="${n.id}">🗑</div>
                    </div>
                `);
            });
        });
    }

    loadNotes();

    $("#add-note-btn").click(() => {
        $("#note-overlay").fadeIn();
    });

    $("#note-cancel").click(() => {
        $("#note-overlay").fadeOut();
    });

    $("#note-save").click(() => {

        $.post(`/customers/${customerId}/notes`, {
            policy: $("#note-policy").val(),
            subject: $("#note-subject").val(),
            note: $("#note-text").val(),
            _token: $('meta[name="csrf-token"]').attr("content")
        }, function () {
            $("#note-overlay").fadeOut();
            $("#note-policy").val("");
            $("#note-subject").val("");
            $("#note-text").val("");
            loadNotes();
        });

    });

    $(document).on("click", ".note-delete", function () {
        let id = $(this).data("id");

        $.ajax({
            url: `/notes/${id}`,
            type: "DELETE",
            data: { _token: $('meta[name="csrf-token"]').attr("content") },
            success: function () {
                loadNotes();
            }
        });
    });

});
