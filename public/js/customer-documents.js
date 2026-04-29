document.addEventListener('DOMContentLoaded', () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    document.addEventListener('click', async (e) => {
        const deleteBtn = e.target.closest('.single-delete-doc-btn');
        if (!deleteBtn) return;

        const deleteUrl = deleteBtn.getAttribute('data-url');
        const documentId = deleteBtn.getAttribute('data-id');
        const card = document.querySelector(`.customer-document-item[data-id="${documentId}"]`);

        const result = await Swal.fire({
            title: 'Delete document?',
            text: 'This document will be deleted permanently.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.ok) {
                throw new Error(data.message || 'Error deleting document');
            }

            if (card) {
                card.remove();
            }

            await Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: data.message || 'Document deleted successfully.'
            });

            const remainingCards = document.querySelectorAll('.customer-document-item');
            if (!remainingCards.length) {
                location.reload();
            }

        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Could not delete document.'
            });
        }
    });
});