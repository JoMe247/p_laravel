(function () {
  const tbody = document.getElementById("invoiceTbody");
  const btnAddRow = document.getElementById("btnAddRow");
  const grandTotalEl = document.getElementById("grandTotal");

  function toNumber(v) {
    if (!v) return 0;
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
      const qtyInput = tr.querySelector(".qty-input");
      const priceInput = tr.querySelector(".price-input");
      const rowTotalEl = tr.querySelector(".row-total");

      const qty = toNumber(qtyInput ? qtyInput.value : "");
      const price = toNumber(priceInput ? priceInput.value : "");
      const rowTotal = qty * price;

      grand += rowTotal;

      if (rowTotalEl) rowTotalEl.textContent = formatMoney(rowTotal);
    });

    grandTotalEl.textContent = formatMoney(grand);
  }

  function bindRow(tr) {
    const qtyInput = tr.querySelector(".qty-input");
    const priceInput = tr.querySelector(".price-input");
    const trashBtn = tr.querySelector(".btn-trash");

    if (qtyInput) qtyInput.addEventListener("input", recalc);

    if (priceInput) {
      priceInput.addEventListener("input", recalc);
      priceInput.addEventListener("blur", () => {
        const n = toNumber(priceInput.value);
        priceInput.value = n ? formatMoney(n) : "";
        recalc();
      });
    }

    if (trashBtn) {
      trashBtn.addEventListener("click", () => {
        tr.remove();       // elimina de la tabla visual
        recalc();          // recalcula total general
      });
    }
  }

  function createRow() {
    const tr = document.createElement("tr");
    tr.className = "row-item";

    tr.innerHTML = `
      <td><input class="cell-input item-input" type="text" list="invoiceItemOptions" value=""></td>
      <td><input class="cell-input qty-input" type="text" value=""></td>
      <td><input class="cell-input price-input" type="text" value=""></td>
      <td class="row-total">$0.00</td>
      <td class="row-actions"><button type="button" class="btn-trash" title="Delete row">🗑</button></td>
    `;

    bindRow(tr);
    return tr;
  }

  btnAddRow.addEventListener("click", function () {
    const tr = createRow();
    tbody.prepend(tr);

    const scroller = document.querySelector(".table-scroll");
    if (scroller) scroller.scrollTop = 0;

    recalc();
  });

  // bind existentes
  tbody.querySelectorAll("tr.row-item").forEach(bindRow);

  recalc();
})();

// ====== SAVE DATES (DATE INPUTS) ======
const datesWrap = document.querySelector(".invoice-dates");
const creationInput = document.getElementById("creationDateInput");
const paymentInput = document.getElementById("paymentDateInput");

function saveDates() {
  if (!datesWrap) return;
  const url = datesWrap.getAttribute("data-save-url");
  if (!url) return;

  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
      "Accept": "application/json",
    },
    body: JSON.stringify({
      creation_date: creationInput ? creationInput.value : "",
      payment_date: paymentInput ? paymentInput.value : "",
    }),
  }).catch(() => { });
}

if (creationInput) creationInput.addEventListener("change", saveDates);
if (paymentInput) paymentInput.addEventListener("change", saveDates);

