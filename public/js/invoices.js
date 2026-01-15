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


    const itemInput = tr.querySelector(".item-input");
    const itemWrap = tr.querySelector(".item-wrap");

    if (itemInput && itemWrap) {
      const toggleArrow = () => {
        if (itemInput.value.trim() !== "") {
          itemWrap.classList.add("has-value");
        } else {
          itemWrap.classList.remove("has-value");
        }
      };

      // al cargar (por si ya trae valor desde BD)
      toggleArrow();

      // al escribir o seleccionar
      itemInput.addEventListener("input", toggleArrow);
      itemInput.addEventListener("change", toggleArrow);
    }


    // ===== DATALIST: mostrar TODAS las opciones al enfocar =====
    let prevItemValue = "";

    itemInput.addEventListener("focus", () => {
      prevItemValue = itemInput.value;   // guardamos lo que tenía

      // Limpia temporalmente para que el datalist muestre todo
      itemInput.value = "";

      // Dispara input para que el navegador refresque sugerencias
      itemInput.dispatchEvent(new Event("input", { bubbles: true }));
    });

    itemInput.addEventListener("click", () => {
      // mismo comportamiento que focus
      if (itemInput.value.trim() !== "") {
        prevItemValue = itemInput.value;
        itemInput.value = "";
        itemInput.dispatchEvent(new Event("input", { bubbles: true }));
      }
    });

    // ====== ITEM: abrir lista completa con 1 click ======
    function openItemOptions() {
      if (!itemInput) return;

      // Truco: tocar el valor para que el navegador muestre todas las opciones
      const current = itemInput.value;

      // fuerza focus
      itemInput.focus();

      // cambia y regresa (no visible, pero dispara sugerencias)
      itemInput.value = " ";
      itemInput.dispatchEvent(new Event("input", { bubbles: true }));

      setTimeout(() => {
        itemInput.value = current;
        itemInput.dispatchEvent(new Event("input", { bubbles: true }));

        // Para que el desplegable aparezca (especialmente Chrome/Edge)
        itemInput.click();
      }, 0);
    }

    // click en input -> abre lista completa (sin doble click)
    itemInput.addEventListener("mousedown", (e) => {
      // evita que el click normal se "coma" el refresco
      e.preventDefault();
      openItemOptions();
    });

    // click en la flecha -> abre lista completa
    const arrow = tr.querySelector(".item-arrow");
    if (arrow) {
      arrow.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        openItemOptions();
      });
    }



  }

  function createRow() {
    const tr = document.createElement("tr");
    tr.className = "row-item";

    tr.innerHTML = `
      <td>
  <div class="item-wrap">
    <input class="cell-input item-input" type="text" list="invoiceItemOptions" value="">
    <span class="item-arrow"></span>
  </div>
</td>

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

  // ====== FEE / PREMIUM UI + SAVE ======
  const chargesBox = document.querySelector(".charges-box");
  if (chargesBox) {
    const saveUrl = chargesBox.getAttribute("data-save-url");
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    const feeInput = document.getElementById("feeInput");
    const feeSplitCheck = document.getElementById("feeSplitCheck");
    const feeSplitFields = document.getElementById("feeSplitFields");
    const feeP1Method = document.getElementById("feeP1Method");
    const feeP1Value = document.getElementById("feeP1Value");
    const feeP2Method = document.getElementById("feeP2Method");
    const feeP2Value = document.getElementById("feeP2Value");

    const premiumInput = document.getElementById("premiumInput");
    const premiumSplitCheck = document.getElementById("premiumSplitCheck");
    const premiumSplitFields = document.getElementById("premiumSplitFields");
    const premiumP1Method = document.getElementById("premiumP1Method");
    const premiumP1Value = document.getElementById("premiumP1Value");
    const premiumP2Method = document.getElementById("premiumP2Method");
    const premiumP2Value = document.getElementById("premiumP2Value");

    function toggleFee() {
      if (!feeSplitFields) return;
      feeSplitFields.style.display = feeSplitCheck && feeSplitCheck.checked ? "block" : "none";
      saveCharges();
    }

    function togglePremium() {
      if (!premiumSplitFields) return;
      premiumSplitFields.style.display = premiumSplitCheck && premiumSplitCheck.checked ? "block" : "none";
      saveCharges();
    }

    let t = null;
    function debounceSave() {
      clearTimeout(t);
      t = setTimeout(saveCharges, 250);
    }

    function saveCharges() {
      if (!saveUrl) return;

      fetch(saveUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN": csrf,
          "Accept": "application/json",
        },
        body: JSON.stringify({
          fee: feeInput ? feeInput.value : "",
          fee_split: feeSplitCheck && feeSplitCheck.checked ? "1" : "0",
          fee_payment1_method: feeP1Method ? feeP1Method.value : "",
          fee_payment1_value: feeP1Value ? feeP1Value.value : "",
          fee_payment2_method: feeP2Method ? feeP2Method.value : "",
          fee_payment2_value: feeP2Value ? feeP2Value.value : "",

          premium: premiumInput ? premiumInput.value : "",
          premium_split: premiumSplitCheck && premiumSplitCheck.checked ? "1" : "0",
          premium_payment1_method: premiumP1Method ? premiumP1Method.value : "",
          premium_payment1_value: premiumP1Value ? premiumP1Value.value : "",
          premium_payment2_method: premiumP2Method ? premiumP2Method.value : "",
          premium_payment2_value: premiumP2Value ? premiumP2Value.value : "",
        }),
      }).catch(() => { });
    }

    // toggles
    if (feeSplitCheck) feeSplitCheck.addEventListener("change", toggleFee);
    if (premiumSplitCheck) premiumSplitCheck.addEventListener("change", togglePremium);

    // inputs autosave
    [
      feeInput, feeP1Method, feeP1Value, feeP2Method, feeP2Value,
      premiumInput, premiumP1Method, premiumP1Value, premiumP2Method, premiumP2Value
    ].forEach((el) => {
      if (el) el.addEventListener("input", debounceSave);
      if (el) el.addEventListener("change", debounceSave);
    });
  }



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


function cleanMoney(v) {
  if (!v) return "";
  return String(v).replace(/[^0-9.]/g, "");
}

// ====== SAVE TABLE JSON ======
const btnSaveTable = document.getElementById("btnSaveTable");
const tableCard = document.querySelector(".table-card");
const tbody = document.getElementById("invoiceTbody");
const grandTotalEl = document.getElementById("grandTotal");


function saveTableJson() {
  const url = tableCard ? tableCard.getAttribute("data-save-url") : "";
  if (!url) {
    console.error("No data-save-url found on .table-card");
    return;
  }

  const rows = [];
  tbody.querySelectorAll("tr.row-item").forEach((tr) => {
    const item = (tr.querySelector(".item-input")?.value || "").trim();
    const amount = (tr.querySelector(".qty-input")?.value || "").trim();
    const price = (tr.querySelector(".price-input")?.value || "").trim();
    const total = (tr.querySelector(".row-total")?.textContent || "").trim();

    rows.push({
      item,
      amount,
      price: cleanMoney(price),
      total: cleanMoney(total),
    });
  });

  const grandTotal = cleanMoney(grandTotalEl.textContent);
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
  const policySelect = document.getElementById("policySelect");
  const policyNumber = policySelect ? policySelect.value : "";

  const invoiceIdMeta = document.querySelector('meta[name="invoice-id"]');
  const invoiceId = invoiceIdMeta ? invoiceIdMeta.getAttribute("content") : "";

  const invoiceBox = document.getElementById("invoiceNumberBox");


  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": csrf || "",
      "Accept": "application/json",
    },
    body: JSON.stringify({ rows, grand_total: grandTotal, policy_number: policyNumber, invoice_id: invoiceId }),
  })
    .then((res) => {
      if (!res.ok) throw new Error("Save failed");
      return res.json();
    })
    .then((data) => {
      if (data && data.invoice_number && invoiceBox) {
        invoiceBox.value = data.invoice_number;
      }
      if (data && data.invoice_id && invoiceIdMeta) {
        invoiceIdMeta.setAttribute("content", data.invoice_id);
      }

      Swal.fire({
        icon: "success",
        title: "Saved",
        text: "Invoice information saved successfully",
        timer: 1600,
        showConfirmButton: false,
      });
    })

    .catch((err) => {
      console.error(err);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Could not save invoice information",
      });
    });

}
if (btnSaveTable) btnSaveTable.addEventListener("click", saveTableJson);
