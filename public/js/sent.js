// ✅ Búsqueda global en inbox y sent
document.getElementById('searchInput')?.addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();

    // Detectamos qué tabla está activa (inbox o sent)
    let table = document.getElementById('inboxTable') || document.getElementById('sentTable');
    if (!table) return;

    let rows = table.querySelectorAll("tbody tr");

    rows.forEach(row => {
        // Para inbox (col 1 = fecha, col 2 = de, col 5 = mensaje)
        // Para sent  (col 0 = fecha, col 1 = para, col 3 = mensaje)
        let fecha = row.cells[0]?.textContent.toLowerCase() || "";
        let numero = row.cells[1]?.textContent.toLowerCase() || "";
        let mensaje = row.cells[3]?.textContent.toLowerCase() || row.cells[5]?.textContent.toLowerCase() || "";

        if (fecha.includes(filter) || numero.includes(filter) || mensaje.includes(filter)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
