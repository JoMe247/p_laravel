const DELETE_VEHICLE_ITEMS = ["delete vehicle"];

function normalizeInvoiceItemValue(value) {
    return String(value || "").trim().toLowerCase();
}

function isDeleteVehicleItem(value) {
    return normalizeInvoiceItemValue(value) === "delete vehicle";
}

function hasDeleteVehicleRow() {
    return Array.from(
        document.querySelectorAll("#invoiceTbody .item-input"),
    ).some((input) => isDeleteVehicleItem(input.value));
}

function populateDeleteVehicleSelect(preserveValue = true) {
    const policySelect = document.getElementById("policySelect");
    const deleteVehicleSelect = document.getElementById("deleteVehicleSelect");

    if (!policySelect || !deleteVehicleSelect) return;

    const policyNumber = policySelect.value || "";
    const map = window.policyVehiclesMap || {};
    const vehicles = Array.isArray(map[policyNumber]) ? map[policyNumber] : [];

    const currentValue = preserveValue
        ? deleteVehicleSelect.value || window.savedDeleteVehicleKey || ""
        : "";

    deleteVehicleSelect.innerHTML = "";

    if (!vehicles.length) {
        deleteVehicleSelect.disabled = true;
        deleteVehicleSelect.innerHTML =
            '<option value="">No vehicles available</option>';
        return;
    }

    deleteVehicleSelect.disabled = false;
    deleteVehicleSelect.innerHTML =
        '<option value="">Select vehicle</option>';

    vehicles.forEach((vehicle) => {
        const option = document.createElement("option");
        option.value = vehicle.key || "";
        option.textContent = vehicle.label || "Vehicle";
        deleteVehicleSelect.appendChild(option);
    });

    if (
        currentValue &&
        Array.from(deleteVehicleSelect.options).some(
            (option) => option.value === currentValue,
        )
    ) {
        deleteVehicleSelect.value = currentValue;
    }
}

function toggleDeleteVehicleSelect() {
    const wrap = document.getElementById("deleteVehicleWrap");
    const select = document.getElementById("deleteVehicleSelect");

    if (!wrap || !select) return;

    const shouldShow = hasDeleteVehicleRow();

    wrap.style.display = shouldShow ? "block" : "none";
    select.required = shouldShow;

    if (shouldShow) {
        populateDeleteVehicleSelect(true);
    } else {
        select.required = false;
        select.disabled = false;
        select.value = "";
        select.innerHTML = '<option value="">Select vehicle</option>';
    }

    if (typeof validateInvoiceForm === "function") {
        validateInvoiceForm();
    }
}

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
            if (qtyInput && !String(qtyInput.value || "").trim()) {
                qtyInput.value = "1";
            }
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
                tr.remove(); // elimina de la tabla visual
                recalc(); // recalcula total general
                toggleDeleteVehicleSelect(); // muestra/oculta select de vehículo a eliminar
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
            toggleDeleteVehicleSelect();

            // al escribir o seleccionar
            itemInput.addEventListener("input", () => {
                toggleArrow();
                toggleDeleteVehicleSelect();
            });

            itemInput.addEventListener("change", () => {
                toggleArrow();
                toggleDeleteVehicleSelect();
            });
        }

        // ===== DATALIST: mostrar TODAS las opciones al enfocar =====
        let prevItemValue = "";

        itemInput.addEventListener("focus", () => {
            prevItemValue = itemInput.value; // guardamos lo que tenía

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

      <td><input class="cell-input qty-input" type="text" value="1"></td>
      <td><input class="cell-input price-input" type="text" value=""></td>
      <td class="row-total">$0.00</td>
      <td class="row-actions"><button type="button" class="btn-trash" title="Delete row"><i class="bx bx-trash"></i></button></td>
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

    const policySelectGlobal = document.getElementById("policySelect");
    const deleteVehicleSelectGlobal = document.getElementById("deleteVehicleSelect");

    if (policySelectGlobal) {
        policySelectGlobal.addEventListener("change", () => {
            populateDeleteVehicleSelect(false);
            toggleDeleteVehicleSelect();
        });
    }

    if (deleteVehicleSelectGlobal) {
        deleteVehicleSelectGlobal.addEventListener("change", validateInvoiceForm);
    }

    toggleDeleteVehicleSelect();

    recalc();

    // ====== FEE / PREMIUM UI + SAVE ======
    const chargesBox = document.querySelector(".charges-box");
    if (chargesBox) {
        const saveUrl = chargesBox.getAttribute("data-save-url");
        const csrf = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");
        let isInitCharges = true;

        const paymentMethod = document.getElementById("paymentMethod");

        const feeInput = document.getElementById("feeInput");
        const feeSplitCheck = document.getElementById("feeSplitCheck");
        const feeSplitFields = document.getElementById("feeSplitFields");
        const feeP1Method = document.getElementById("feeP1Method");
        const feeP1Value = document.getElementById("feeP1Value");
        const feeP2Method = document.getElementById("feeP2Method");
        const feeP2Value = document.getElementById("feeP2Value");

        const premiumInput = document.getElementById("premiumInput");
        const premiumSplitCheck = document.getElementById("premiumSplitCheck");
        const premiumSplitFields =
            document.getElementById("premiumSplitFields");
        const premiumP1Method = document.getElementById("premiumP1Method");
        const premiumP1Value = document.getElementById("premiumP1Value");
        const premiumP2Method = document.getElementById("premiumP2Method");
        const premiumP2Value = document.getElementById("premiumP2Value");

        function toggleFee() {
            if (!feeSplitFields) return;

            const active = feeSplitCheck && feeSplitCheck.checked;
            feeSplitFields.style.display = active ? "block" : "none";

            // REQUIRED dinámico
            if (feeP1Method) feeP1Method.required = active;
            if (feeP1Value) feeP1Value.required = active;
            if (feeP2Method) feeP2Method.required = active;
            if (feeP2Value) feeP2Value.required = active;

            // ✅ NO autosave durante inicialización
            if (!isInitCharges) saveCharges();
        }

        function togglePremium() {
            if (!premiumSplitFields) return;

            const active = premiumSplitCheck && premiumSplitCheck.checked;
            premiumSplitFields.style.display = active ? "block" : "none";

            // REQUIRED dinámico
            if (premiumP1Method) premiumP1Method.required = active;
            if (premiumP1Value) premiumP1Value.required = active;
            if (premiumP2Method) premiumP2Method.required = active;
            if (premiumP2Value) premiumP2Value.required = active;

            // ✅ NO autosave durante inicialización
            if (!isInitCharges) saveCharges();
        }

        let t = null;
        function debounceSave() {
            clearTimeout(t);
            t = setTimeout(saveCharges, 250);
        }

        function saveCharges() {
            if (!saveUrl) return;

            const currentInvoiceId = getCurrentInvoiceId();
            if (!currentInvoiceId) return;

            fetch(saveUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    invoice_id: currentInvoiceId,
                    payment_method: paymentMethod ? paymentMethod.value : "",

                    fee: feeInput ? feeInput.value : "",
                    fee_split:
                        feeSplitCheck && feeSplitCheck.checked ? "1" : "0",
                    fee_payment1_method: feeP1Method ? feeP1Method.value : "",
                    fee_payment1_value: feeP1Value ? feeP1Value.value : "",
                    fee_payment2_method: feeP2Method ? feeP2Method.value : "",
                    fee_payment2_value: feeP2Value ? feeP2Value.value : "",

                    premium: premiumInput ? premiumInput.value : "",
                    premium_split:
                        premiumSplitCheck && premiumSplitCheck.checked
                            ? "1"
                            : "0",
                    premium_payment1_method: premiumP1Method
                        ? premiumP1Method.value
                        : "",
                    premium_payment1_value: premiumP1Value
                        ? premiumP1Value.value
                        : "",
                    premium_payment2_method: premiumP2Method
                        ? premiumP2Method.value
                        : "",
                    premium_payment2_value: premiumP2Value
                        ? premiumP2Value.value
                        : "",
                }),
            }).catch(() => { });
        }

        // toggles
        if (feeSplitCheck) feeSplitCheck.addEventListener("change", toggleFee);
        if (premiumSplitCheck)
            premiumSplitCheck.addEventListener("change", togglePremium);

        // inputs autosave
        [
            paymentMethod,
            feeInput,
            feeP1Method,
            feeP1Value,
            feeP2Method,
            feeP2Value,
            premiumInput,
            premiumP1Method,
            premiumP1Value,
            premiumP2Method,
            premiumP2Value,
        ].forEach((el) => {
            if (el) el.addEventListener("input", debounceSave);
            if (el) el.addEventListener("change", debounceSave);
        });

        // Inicializar required al cargar
        if (feeSplitCheck) toggleFee();
        if (premiumSplitCheck) togglePremium();
        isInitCharges = false;
        if (typeof validateInvoiceForm === "function") validateInvoiceForm();
    }
})();

function getCurrentInvoiceId() {
    return (
        document
            .querySelector('meta[name="invoice-id"]')
            ?.getAttribute("content") || ""
    );
}

// ====== SAVE DATES (DATE INPUTS) ======
const datesWrap = document.querySelector(".invoice-dates");
const nextPaymentInput = document.getElementById("nextPaymentDateInput");
const creationInput = document.getElementById("creationDateInput");
const paymentInput = document.getElementById("paymentDateInput");

function saveDates() {
    if (!datesWrap) return;

    const currentInvoiceId = getCurrentInvoiceId();
    if (!currentInvoiceId) return;

    const url = datesWrap.getAttribute("data-save-url");
    if (!url) return;

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            Accept: "application/json",
        },
        body: JSON.stringify({
            invoice_id: currentInvoiceId,
            next_py_date: nextPaymentInput ? nextPaymentInput.value : "",
            creation_date: creationInput ? creationInput.value : "",
            payment_date: paymentInput ? paymentInput.value : "",
        }),
    }).catch(() => { });
}

if (nextPaymentInput) nextPaymentInput.addEventListener("change", saveDates);
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
        const total = (
            tr.querySelector(".row-total")?.textContent || ""
        ).trim();

        rows.push({
            item,
            amount,
            price: cleanMoney(price),
            total: cleanMoney(total),
        });
    });

    const grandTotal = cleanMoney(grandTotalEl.textContent);
    const csrf = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    const policySelect = document.getElementById("policySelect");
    const policyNumber = policySelect ? policySelect.value : "";

    const deleteVehicleSelect = document.getElementById("deleteVehicleSelect");
    const selectedDeleteVehicleKey =
        hasDeleteVehicleRow() && deleteVehicleSelect
            ? deleteVehicleSelect.value
            : "";

    const invoiceIdMeta = document.querySelector('meta[name="invoice-id"]');
    const currentInvoiceId = getCurrentInvoiceId();

    const invoiceBox = document.getElementById("invoiceNumberBox");

    const paymentMethod = document.getElementById("paymentMethod");
    const feeInput = document.getElementById("feeInput");
    const feeSplitCheck = document.getElementById("feeSplitCheck");
    const feeP1Method = document.getElementById("feeP1Method");
    const feeP1Value = document.getElementById("feeP1Value");
    const feeP2Method = document.getElementById("feeP2Method");
    const feeP2Value = document.getElementById("feeP2Value");

    const premiumInput = document.getElementById("premiumInput");
    const premiumSplitCheck = document.getElementById("premiumSplitCheck");
    const premiumP1Method = document.getElementById("premiumP1Method");
    const premiumP1Value = document.getElementById("premiumP1Value");
    const premiumP2Method = document.getElementById("premiumP2Method");
    const premiumP2Value = document.getElementById("premiumP2Value");

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf || "",
            Accept: "application/json",
        },
        body: JSON.stringify({
            invoice_id: currentInvoiceId,
            rows,
            grand_total: grandTotal,
            policy_number: policyNumber,
            selected_delete_vehicle_key: selectedDeleteVehicleKey,

            next_py_date: nextPaymentInput ? nextPaymentInput.value : "",
            creation_date: creationInput ? creationInput.value : "",
            payment_date: paymentInput ? paymentInput.value : "",

            payment_method: paymentMethod ? paymentMethod.value : "",

            fee: feeInput ? feeInput.value : "",
            fee_split: feeSplitCheck && feeSplitCheck.checked ? "1" : "0",
            fee_payment1_method: feeP1Method ? feeP1Method.value : "",
            fee_payment1_value: feeP1Value ? feeP1Value.value : "",
            fee_payment2_method: feeP2Method ? feeP2Method.value : "",
            fee_payment2_value: feeP2Value ? feeP2Value.value : "",

            premium: premiumInput ? premiumInput.value : "",
            premium_split:
                premiumSplitCheck && premiumSplitCheck.checked ? "1" : "0",
            premium_payment1_method: premiumP1Method
                ? premiumP1Method.value
                : "",
            premium_payment1_value: premiumP1Value ? premiumP1Value.value : "",
            premium_payment2_method: premiumP2Method
                ? premiumP2Method.value
                : "",
            premium_payment2_value: premiumP2Value ? premiumP2Value.value : "",
        }),
    })
        .then(async (res) => {
            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                throw data;
            }

            return data;
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
                timer: 900,
                showConfirmButton: false,
            });

            const paymentsUrl = tableCard
                ? tableCard.getAttribute("data-payments-url")
                : "";
            if (paymentsUrl) {
                setTimeout(() => {
                    window.location.href = paymentsUrl;
                }, 900);
            }
        })
        .catch((err) => {
            console.error(err);

            let message = "Could not save invoice information";

            if (err?.error === "missing_delete_vehicle") {
                message = "Select the vehicle you want to delete.";
            } else if (err?.error === "vehicle_not_found") {
                message = "The selected vehicle was not found in the selected policy.";
            }

            Swal.fire({
                icon: "error",
                title: "Error",
                text: message,
            });
        });
}
if (btnSaveTable) btnSaveTable.addEventListener("click", saveTableJson);

function validateInvoiceForm() {
    const btnSave = document.getElementById("btnSaveTable");
    if (!btnSave) return;

    let valid = true;

    const policySelect = document.getElementById("policySelect");
    if (!policySelect || !policySelect.value) {
        valid = false;
    }

    const rows = document.querySelectorAll("#invoiceTbody tr.row-item");
    if (!rows.length) {
        valid = false;
    } else {
        let hasValidRow = false;

        rows.forEach((tr) => {
            const item = tr.querySelector(".item-input")?.value.trim();
            const qty = tr.querySelector(".qty-input")?.value.trim();
            const price = tr.querySelector(".price-input")?.value.trim();

            if (item && qty && price) {
                hasValidRow = true;
            }
        });

        if (!hasValidRow) valid = false;
    }

    document
        .querySelectorAll("input[required], select[required]")
        .forEach((el) => {
            if (el.offsetParent !== null && !el.value) {
                valid = false;
            }
        });

    const deleteVehicleSelect = document.getElementById("deleteVehicleSelect");
    if (
        deleteVehicleSelect &&
        deleteVehicleSelect.required &&
        !deleteVehicleSelect.value
    ) {
        valid = false;
    }

    btnSave.disabled = !valid;
}

document.addEventListener("DOMContentLoaded", () => {
    validateInvoiceForm();
    document.addEventListener("input", validateInvoiceForm);
    document.addEventListener("change", validateInvoiceForm);
    document.addEventListener("click", validateInvoiceForm);
});
