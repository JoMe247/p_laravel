document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('reminders-overlay');
    const closeBtn = document.getElementById('close-reminders-overlay');

    if (overlay) {
        function openOverlay() {
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeOverlay() {
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (e) {
            const openRemindersBtn = e.target.closest('#open-reminders');
            if (openRemindersBtn) {
                openOverlay();
                return;
            }

            const closeRemindersBtn = e.target.closest('#close-reminders-overlay');
            if (closeRemindersBtn) {
                closeOverlay();
                return;
            }

            if (e.target === overlay) {
                closeOverlay();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD RECENT CLIENTS - QUICK ACTIONS
    |--------------------------------------------------------------------------
    */

    let selectedDashboardAction = null;

    const actionButton = document.getElementById('allAction-btn');
    const actionText = document.querySelector('#action-drop-button .sBtn-text');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const exportUrl = document.querySelector('meta[name="dashboard-export-customers-url"]')?.getAttribute('content') || '';
    const deleteUrl = document.querySelector('meta[name="dashboard-delete-customers-url"]')?.getAttribute('content') || '';

    function getSelectedCustomerIds() {
        return Array.from(document.querySelectorAll('input[name="customer_select"]:checked'))
            .map(input => input.value)
            .filter(Boolean);
    }

    document.addEventListener('click', function (e) {
        const option = e.target.closest('#table-drop .option');

        if (!option) return;

        selectedDashboardAction = option.getAttribute('data-action');

        const optionLabel = option.querySelector('.option-text')?.textContent?.trim();

        if (actionText && optionLabel) {
            actionText.textContent = optionLabel;
        }
    });

    async function exportSelectedCustomers(ids) {
        const response = await fetch(exportUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'text/csv, application/json'
            },
            body: JSON.stringify({
                ids: ids
            })
        });

        if (!response.ok) {
            let message = 'No se pudo exportar el CSV.';

            try {
                const errorData = await response.json();

                if (errorData.error === 'no_customers_selected') {
                    message = 'Selecciona al menos un customer para exportar.';
                }

                if (errorData.error === 'customers_not_found') {
                    message = 'No se encontraron customers válidos para exportar.';
                }
            } catch (error) {}

            throw new Error(message);
        }

        const blob = await response.blob();

        const disposition = response.headers.get('Content-Disposition');
        let fileName = 'customers_selected.csv';

        if (disposition && disposition.includes('filename=')) {
            fileName = disposition
                .split('filename=')[1]
                .replaceAll('"', '')
                .trim();
        }

        const downloadUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');

        a.href = downloadUrl;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();

        a.remove();
        window.URL.revokeObjectURL(downloadUrl);
    }

    async function deleteSelectedCustomers(ids) {
        const response = await fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ids: ids
            })
        });

        const data = await response.json();

        if (!response.ok || !data.ok) {
            throw new Error('No se pudieron eliminar los customers seleccionados.');
        }

        ids.forEach(id => {
            const checkbox = document.querySelector(`input[name="customer_select"][value="${id}"]`);

            if (checkbox) {
                const row = checkbox.closest('tr');

                if (row) {
                    row.remove();
                }
            }
        });

        const selectAll = document.getElementById('selectAll-chk');

        if (selectAll) {
            selectAll.checked = false;
        }

        if (typeof checkboxActive === 'function') {
            checkboxActive();
        }

        return data.deleted || ids.length;
    }

    if (actionButton) {
        actionButton.addEventListener('click', function () {
            const ids = getSelectedCustomerIds();

            if (!selectedDashboardAction) {
                Swal.fire({
                    title: 'Selecciona una acción',
                    text: 'Primero elige Export CSV o Delete.',
                    icon: 'info',
                    confirmButtonText: 'Aceptar'
                });

                return;
            }

            if (ids.length === 0) {
                Swal.fire({
                    title: 'Selecciona un customer',
                    text: 'Marca al menos un customer de la tabla.',
                    icon: 'warning',
                    confirmButtonText: 'Aceptar'
                });

                return;
            }

            if (selectedDashboardAction === 'export_csv') {
                exportSelectedCustomers(ids).catch(error => {
                    Swal.fire({
                        title: 'Error',
                        text: error.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                });

                return;
            }

            if (selectedDashboardAction === 'delete') {
                Swal.fire({
                    title: '¿Eliminar customers?',
                    text: `Se eliminarán ${ids.length} customer(s). Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                }).then(result => {
                    if (!result.isConfirmed) return;

                    deleteSelectedCustomers(ids)
                        .then(deleted => {
                            Swal.fire({
                                title: 'Eliminado',
                                text: `${deleted} customer(s) eliminado(s) correctamente.`,
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                title: 'Error',
                                text: error.message,
                                icon: 'error',
                                confirmButtonText: 'Aceptar'
                            });
                        });
                });
            }
        });
    }
});