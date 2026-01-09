(function () {
  const tbody = document.getElementById("invoiceTbody");
  const btnAddRow = document.getElementById("btnAddRow");
  const grandTotalEl = document.getElementById("grandTotal");

  function moneyToNumber(v) {
    if (!v) return 0;
    // acepta: "$1,200.50" "1200.50" "1200"
    const cleaned = String(v).replace(/[^0-9.]/g, "");
    const n = parseFloat(cleaned);
    return isNaN(n) ? 0 : n;
  }

  function formatMoney(n) {
    const fixed = (Math.round(n * 100) / 100).toFixed(2);
    return "$" + fixed.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  }

  function recalc() {
    let grand = 0;

    const rows = tbody.querySelectorAll("tr.row-item");
    rows.forEach((tr) => {
      const amountInput = tr.querySelector(".amount-input");
      const rowTotalEl = tr.querySelector(".row-total");

      const val = moneyToNumber(amountInput ? amountInput.value : "");
      grand += val;

      if (rowTotalEl) rowTotalEl.textContent = formatMoney(val);
    });

    grandTotalEl.textContent = formatMoney(grand);
  }

  function bindRow(tr) {
    const amountInput = tr.querySelector(".amount-input");
    if (amountInput) {
      amountInput.addEventListener("input", recalc);
      amountInput.addEventListener("blur", () => {
        // normaliza a formato dinero al salir
        const n = moneyToNumber(amountInput.value);
        amountInput.value = n ? formatMoney(n) : "";
        recalc();
      });
    }
  }

  function createRow() {
    const tr = document.createElement("tr");
    tr.className = "row-item";

    tr.innerHTML = `
      <td><input class="cell-input" type="text" value=""></td>
      <td><input class="cell-input" type="text" value=""></td>
      <td><input class="cell-input amount-input" type="text" value=""></td>
      <td class="row-total">$0.00</td>
    `;

    bindRow(tr);
    return tr;
  }

  // Add Row -> se agrega ARRIBA y se desplaza hacia arriba
  btnAddRow.addEventListener("click", function () {
    const tr = createRow();
    tbody.prepend(tr);

    // asegura que el scroll se vaya arriba (para ver la fila nueva)
    const scroller = document.querySelector(".table-scroll");
    if (scroller) scroller.scrollTop = 0;

    recalc();
  });

  // Bind existentes
  tbody.querySelectorAll("tr.row-item").forEach(bindRow);

  // Calcula al inicio
  recalc();
})();
