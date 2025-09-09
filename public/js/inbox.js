// ✅ Búsqueda global (fecha, número o mensaje)
document.getElementById('searchInput')?.addEventListener('keyup', function() {
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
