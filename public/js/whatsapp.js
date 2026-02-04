// ✅ Búsqueda global (fecha, número o mensaje)
document.getElementById('searchInput')?.addEventListener('keyup', function () {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#inboxTable tbody tr");

    rows.forEach(row => {
        let fecha = row.cells[1]?.textContent.toLowerCase() || "";
        let numero = row.cells[2]?.textContent.toLowerCase() || "";
        let mensaje = row.cells[5]?.textContent.toLowerCase() || "";

        if (fecha.includes(filter) || numero.includes(filter) || mensaje.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});

// ✅ Select all
document.getElementById('select-all')?.addEventListener('change', function () {
    const checked = this.checked;
    document.querySelectorAll('.row-check').forEach(cb => {
        cb.checked = checked;
    });
});

// ✅ Si desmarcan uno, actualizar el select-all
document.addEventListener('change', function (e) {
    if (!e.target.classList.contains('row-check')) return;

    const all = document.querySelectorAll('.row-check');
    const allChecked = document.querySelectorAll('.row-check:checked');

    const selectAll = document.getElementById('select-all');
    if (selectAll) selectAll.checked = (all.length > 0 && allChecked.length === all.length);
});

// ✅ Eliminar seleccionados (bulk)
function bulkDelete() {
    const checked = Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);

    if (checked.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Sin selección',
            text: 'Selecciona al menos un mensaje para eliminar.',
        });
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar mensajes?',
        text: `Vas a eliminar ${checked.length} mensaje(s).`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (!result.isConfirmed) return;

        const container = document.getElementById('bulk-hidden-inputs');
        container.innerHTML = '';

        checked.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'messages[]';
            input.value = id;
            container.appendChild(input);
        });

        document.getElementById('bulkDeleteForm').submit();
    });
}

// 👇 IMPORTANTE: para que el onclick="bulkDelete()" del Blade funcione
window.bulkDelete = bulkDelete;
