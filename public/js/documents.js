document.addEventListener('DOMContentLoaded', () => {
    const csrf = window.documentsCsrf || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function buildRoute(template, id) {
        return template.replace(':id', id);
    }

    async function postAction(url, button) {
        const oldHtml = button.innerHTML;
        button.disabled = true;
        button.style.opacity = '0.6';

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });

            const data = await res.json();

            if (!res.ok || !data.ok) {
                throw new Error(data.message || data.error || 'Unexpected error');
            }

            await Swal.fire({
                icon: 'success',
                title: 'Done',
                text: data.message || 'Action completed successfully.'
            });
        } catch (err) {
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Action could not be completed.'
            });
        } finally {
            button.disabled = false;
            button.style.opacity = '1';
            button.innerHTML = oldHtml;
        }
    }

    async function deleteAction(url, button, row) {
        const confirm = await Swal.fire({
            icon: 'warning',
            title: 'Delete document?',
            text: 'This will delete the document, its URL record, signing record and stored PDF.',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        });

        if (!confirm.isConfirmed) return;

        const oldHtml = button.innerHTML;
        button.disabled = true;
        button.style.opacity = '0.6';

        try {
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();

            if (!res.ok || !data.ok) {
                throw new Error(data.message || data.error || 'Unexpected error');
            }

            if (row) {
                row.remove();
            }

            const totalEl = document.getElementById('documents-total');
            if (totalEl) {
                const current = parseInt(totalEl.textContent || '0', 10);
                totalEl.textContent = Math.max(0, current - 1);
            }

            await Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: data.message || 'Document deleted successfully.'
            });
        } catch (err) {
            await Swal.fire({
                icon: 'error',
                title: 'Error',
                text: err.message || 'Document could not be deleted.'
            });
        } finally {
            button.disabled = false;
            button.style.opacity = '1';
            button.innerHTML = oldHtml;
        }
    }

    document.querySelectorAll('.btn-resend-phone').forEach(button => {
        button.addEventListener('click', async () => {
            const docId = button.dataset.docId;
            if (!docId) return;

            const url = buildRoute(window.documentsRoutes.resendPhone, docId);
            await postAction(url, button);
        });
    });

    document.querySelectorAll('.btn-resend-email').forEach(button => {
        button.addEventListener('click', async () => {
            const docId = button.dataset.docId;
            if (!docId) return;

            const url = buildRoute(window.documentsRoutes.resendEmail, docId);
            await postAction(url, button);
        });
    });

    document.querySelectorAll('.btn-delete-document').forEach(button => {
        button.addEventListener('click', async () => {
            const docId = button.dataset.docId;
            if (!docId) return;

            const row = button.closest('tr');
            const url = buildRoute(window.documentsRoutes.destroy, docId);
            await deleteAction(url, button, row);
        });
    });
});