// public/js/add-customer.js
document.addEventListener('DOMContentLoaded', function () {

    const addBtn = document.getElementById('cust-addCustomer-btn');
    const editCustomer = document.getElementById('cust-edit-customer');
    const outerBox = document.getElementById('cust-outer-box');

    if (!addBtn) { console.warn('add-customer.js: #cust-addCustomer-btn no encontrado.'); return; }
    if (!editCustomer || !outerBox) { alert('Contenedor de edición no encontrado.'); return; }

    // Helpers para overlay si no existen
    if (typeof window.custDimOn !== 'function') {
        window.custDimOn = function () {
            const d = document.getElementById('cust-dim-overlay');
            if (d) { d.style.display = 'block'; d.style.opacity = '0.6'; }
        };
    }
    if (typeof window.custDimOff !== 'function') {
        window.custDimOff = function () {
            const d = document.getElementById('cust-dim-overlay');
            if (d) { d.style.display = 'none'; d.style.opacity = ''; }
        };
    }

    function openAddCustomer() {
        custDimOn();
        editCustomer.style.display = 'flex';
        outerBox.innerHTML = `
            <div class="customer-modal-content">
                <div class="customer-modal-header">
                    <h2>Add Customer</h2>
                    <button id="customer-close" class="btn-close" style="display:none"><i class="bx bx-x"></i></button>
                </div>
                <form id="customer-form">
                    <div class="customer-modal-fields">
                        <div class="customer-field">
                            <label>Name</label>
                            <input id="customer_Name" name="Name" type="text" required>
                        </div>
                        <div class="customer-field">
                            <label>Address</label>
                            <input id="customer_Address" name="Address" type="text">
                        </div>
                        <div class="customer-field">
                            <label>Phone</label>
                            <input id="customer_Phone" name="Phone" type="text">
                        </div>
                        <div class="customer-field">
                            <label>DOB</label>
                            <input id="customer_DOB" name="DOB" type="date">
                        </div>
                    </div>
                    <div class="customer-modal-buttons">
                        <button type="submit" id="customer-save" class="btn-save">Registrar</button>
                        <button type="button" id="customer-cancel" class="btn-cancel">Cancelar</button>
                    </div>
                    <div id="customer-error" class="customer-error"></div>
                </form>
            </div>
        `;
        // animación
        setTimeout(() => { outerBox.style.transform = 'translateY(0)'; outerBox.style.opacity = '1'; }, 20);

        // Bind buttons
        document.getElementById('customer-close').addEventListener('click', closeAddCustomer);
        document.getElementById('customer-cancel').addEventListener('click', closeAddCustomer);

        document.getElementById('customer-form').addEventListener('submit', async function (ev) {
            ev.preventDefault();
            showError('');

            const payload = {
                Name: document.getElementById('customer_Name').value.trim(),
                Address: document.getElementById('customer_Address').value.trim(),
                Phone: document.getElementById('customer_Phone').value.trim(),
                DOB: document.getElementById('customer_DOB').value
            };

            if (!payload.Name) { showError('Name es requerido.'); return; }

            const csrf = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const res = await fetch('/customers', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                if (!res.ok) { showError('Error al guardar: ' + res.status); return; }

                const json = await res.json();
                if (json && json.id) { window.location.href = '/profile/' + json.id; }
                else { showError('No se recibió ID del servidor.'); }

            } catch (err) { console.error(err); showError('Error de red al guardar.'); }
        });

        function showError(msg) {
            const el = document.getElementById('customer-error');
            el.textContent = msg;
            el.style.display = msg ? 'block' : 'none';
        }
    }

    function closeAddCustomer() {
        outerBox.style.transform = '';
        outerBox.style.opacity = '0';
        setTimeout(() => {
            outerBox.innerHTML = '';
            editCustomer.style.display = 'none';
            custDimOff();
        }, 250);
    }

    addBtn.addEventListener('click', (e) => { e.preventDefault(); openAddCustomer(); });


    // --- Selección múltiple y eliminación de clientes ---
    $(document).ready(function () {
        const baseUrl = $('meta[name="base-url"]').attr('content');
        const token = $('meta[name="csrf-token"]').attr('content');
        const deleteBtn = $('#cust-deleteSelected-btn');
        const selectAll = $('#select-all-customers');

        // Activar o desactivar el botón según selección
        function toggleDeleteButton() {
            const anySelected = $('.select-customer:checked').length > 0;
            deleteBtn.prop('disabled', !anySelected);
        }

        // Seleccionar/Deseleccionar todos
        selectAll.on('change', function () {
            $('.select-customer').prop('checked', $(this).is(':checked'));
            toggleDeleteButton();
        });

        // Cambios en casillas individuales
        $(document).on('change', '.select-customer', function () {
            if (!$(this).is(':checked')) selectAll.prop('checked', false);
            toggleDeleteButton();
        });

        // Eliminar seleccionados
        deleteBtn.on('click', function () {
            const selectedIds = $('.select-customer:checked').map(function () {
                return $(this).data('id');
            }).get();

            if (selectedIds.length === 0) return;

            if (!confirm(`¿Eliminar ${selectedIds.length} cliente(s)?`)) return;

            $.ajax({
                url: `${baseUrl}/customers/delete-multiple`,
                type: 'POST',
                data: { ids: selectedIds },
                headers: { 'X-CSRF-TOKEN': token },
                success: function (response) {
                    if (response.success) {
                        selectedIds.forEach(id => {
                            $(`.select-customer[data-id="${id}"]`).closest('tr').fadeOut(300, function () {
                                $(this).remove();
                            });
                        });
                        toggleDeleteButton();
                    } else {
                        alert(response.message || 'No se pudieron eliminar los clientes.');
                    }
                },
                error: function () {
                    alert('Error al eliminar los clientes.');
                }
            });
        });
    });


    
});
