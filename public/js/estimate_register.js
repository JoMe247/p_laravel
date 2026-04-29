(function () {
    const tbody = document.getElementById("estimateTbody");
    const btnAddRow = document.getElementById("btnAddEstimateRow");
    const grandTotalEl = document.getElementById("estimateGrandTotal");
    const btnSaveTable = document.getElementById("btnSaveEstimateTable");

    const tableCard = document.querySelector(".table-card");
    const estimateIdMeta = document.querySelector('meta[name="estimate-id"]');
    const estimateNumberBox = document.getElementById("estimateNumberBox");

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";

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

    function cleanMoney(v) {
        if (!v) return "";
        return String(v).replace(/[^0-9.]/g, "");
    }

    function recalc() {
        let grand = 0;
        tbody.querySelectorAll("tr.row-item").forEach((tr) => {
            const qtyInput = tr.querySelector(".qty-input");
            const priceInput = tr.querySelector(".price-input");
            const rowTotalEl = tr.querySelector(".row-total");

            const qty = toNumber(qtyInput?.value || "");
            const price = toNumber(priceInput?.value || "");
            const rowTotal = qty * price;

            grand += rowTotal;
            if (rowTotalEl) rowTotalEl.textContent = formatMoney(rowTotal);
        });

        if (grandTotalEl) grandTotalEl.textContent = formatMoney(grand);
    }

    function bindRow(tr) {
        const qtyInput = tr.querySelector(".qty-input");
        const priceInput = tr.querySelector(".price-input");
        const trashBtn = tr.querySelector(".btn-trash");

        const itemInput = tr.querySelector(".item-input");
        const itemWrap = tr.querySelector(".item-wrap");

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
                tr.remove();
                recalc();
                validateEstimateForm();
            });
        }

        // arrow show/hide
        if (itemInput && itemWrap) {
            const toggleArrow = () => {
                if (itemInput.value.trim() !== "") itemWrap.classList.add("has-value");
                else itemWrap.classList.remove("has-value");
            };

            toggleArrow();
            itemInput.addEventListener("input", toggleArrow);
            itemInput.addEventListener("change", toggleArrow);

            // open list
            function openItemOptions() {
                const current = itemInput.value;
                itemInput.focus();
                itemInput.value = " ";
                itemInput.dispatchEvent(new Event("input", { bubbles: true }));
                setTimeout(() => {
                    itemInput.value = current;
                    itemInput.dispatchEvent(new Event("input", { bubbles: true }));
                    itemInput.click();
                }, 0);
            }

            itemInput.addEventListener("mousedown", (e) => {
                e.preventDefault();
                openItemOptions();
            });

            const arrow = tr.querySelector(".item-arrow");
            if (arrow) {
                arrow.addEventListener("click", (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    openItemOptions();
                });
            }
        }
    }

    function createRow() {
        const tr = document.createElement("tr");
        tr.className = "row-item";
        tr.innerHTML = `
      <td>
        <div class="item-wrap">
          <input class="cell-input item-input" type="text" list="estimateItemOptions" value="">
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

    if (btnAddRow) {
        btnAddRow.addEventListener("click", () => {
            const tr = createRow();
            tbody.prepend(tr);

            const scroller = document.querySelector(".table-scroll");
            if (scroller) scroller.scrollTop = 0;

            recalc();
            validateEstimateForm();
        });
    }

    // bind existentes
    tbody.querySelectorAll("tr.row-item").forEach(bindRow);
    recalc();

    // ====== SAVE CHARGES ======
    const chargesBox = document.querySelector(".charges-box");
    if (chargesBox) {
        const saveUrl = chargesBox.getAttribute("data-save-url");
        let isInitCharges = true;

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
                    estimate_id: estimateIdMeta?.getAttribute("content") || "",

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

        let t = null;
        function debounceSave() {
            clearTimeout(t);
            t = setTimeout(saveCharges, 250);
        }

        function toggleFee() {
            if (!feeSplitFields) return;
            const active = feeSplitCheck && feeSplitCheck.checked;
            feeSplitFields.style.display = active ? "block" : "none";

            if (feeP1Method) feeP1Method.required = active;
            if (feeP1Value) feeP1Value.required = active;
            if (feeP2Method) feeP2Method.required = active;
            if (feeP2Value) feeP2Value.required = active;

            if (!isInitCharges) saveCharges();
            validateEstimateForm();
        }

        function togglePremium() {
            if (!premiumSplitFields) return;
            const active = premiumSplitCheck && premiumSplitCheck.checked;
            premiumSplitFields.style.display = active ? "block" : "none";

            if (premiumP1Method) premiumP1Method.required = active;
            if (premiumP1Value) premiumP1Value.required = active;
            if (premiumP2Method) premiumP2Method.required = active;
            if (premiumP2Value) premiumP2Value.required = active;

            if (!isInitCharges) saveCharges();
            validateEstimateForm();
        }

        if (feeSplitCheck) feeSplitCheck.addEventListener("change", toggleFee);
        if (premiumSplitCheck) premiumSplitCheck.addEventListener("change", togglePremium);

        [
            feeInput, feeP1Method, feeP1Value, feeP2Method, feeP2Value,
            premiumInput, premiumP1Method, premiumP1Value, premiumP2Method, premiumP2Value
        ].forEach((el) => {
            if (!el) return;
            el.addEventListener("input", debounceSave);
            el.addEventListener("change", debounceSave);
        });

        if (feeSplitCheck) toggleFee();
        if (premiumSplitCheck) togglePremium();
        isInitCharges = false;
    }

    // ====== SAVE DATES ======
    const datesWrap = document.querySelector(".invoice-dates");
    const nextPaymentInput = document.getElementById("nextPaymentDateInput");
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
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json",
            },
            body: JSON.stringify({
                estimate_id: estimateIdMeta?.getAttribute("content") || "",
                next_py_date: nextPaymentInput ? nextPaymentInput.value : "",
                creation_date: creationInput ? creationInput.value : "",
                payment_date: paymentInput ? paymentInput.value : "",
            }),
        }).catch(() => { });
    }

    saveDates();
    if (creationInput) creationInput.addEventListener("change", saveDates);
    if (paymentInput) paymentInput.addEventListener("change", saveDates);
    if (nextPaymentInput) nextPaymentInput.addEventListener("change", saveDates);

    // ====== SAVE TABLE JSON ======
    function saveTableJson() {
        const url = tableCard?.getAttribute("data-save-url") || "";
        if (!url) return;

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

        const grandTotal = cleanMoney(grandTotalEl?.textContent || "");
        const policyNumber = document.getElementById("policySelect")?.value || "";

        fetch(url, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrf,
                "Accept": "application/json",
            },
            body: JSON.stringify({
                rows,
                grand_total: grandTotal,
                policy_number: policyNumber,
                estimate_id: estimateIdMeta?.getAttribute("content") || "",
            }),
        })
            .then((res) => {
                if (!res.ok) throw new Error("Save failed");
                return res.json();
            })
            .then((data) => {
                // ✅ Poner el número real en la caja (como invoices)
                if (data && data.estimate_number && estimateNumberBox) {
                    estimateNumberBox.value = data.estimate_number;
                }

                // ✅ Guardar estimate_id en el meta (como invoices)
                if (data && data.estimate_id && estimateIdMeta) {
                    estimateIdMeta.setAttribute("content", data.estimate_id);
                }

                Swal.fire({
                    icon: "success",
                    title: "Saved",
                    text: "Estimate saved successfully",
                    timer: 900,
                    showConfirmButton: false,
                });

                const estimatesUrl = tableCard ? tableCard.getAttribute("data-estimates-url") : "";
                if (estimatesUrl) {
                    setTimeout(() => {
                        window.location.href = estimatesUrl;
                    }, 900);
                }
            })
            .catch((err) => {
                console.error(err);
                Swal.fire({ icon: "error", title: "Error", text: "Could not save estimate information" });
            });
    }

    if (btnSaveTable) btnSaveTable.addEventListener("click", saveTableJson);

    // ====== VALIDATION ======
    function validateEstimateForm() {
        const btnSave = document.getElementById("btnSaveEstimateTable");
        if (!btnSave) return;

        let valid = true;

        const policySelect = document.getElementById("policySelect");
        if (!policySelect || !policySelect.value) valid = false;

        const rows = document.querySelectorAll("#estimateTbody tr.row-item");
        if (!rows.length) valid = false;
        else {
            let hasValidRow = false;
            rows.forEach((tr) => {
                const item = tr.querySelector(".item-input")?.value.trim();
                const qty = tr.querySelector(".qty-input")?.value.trim();
                const price = tr.querySelector(".price-input")?.value.trim();
                if (item && qty && price) hasValidRow = true;
            });
            if (!hasValidRow) valid = false;
        }

        document.querySelectorAll("input[required], select[required]").forEach((el) => {
            if (el.offsetParent !== null && !el.value) valid = false;
        });

        btnSave.disabled = !valid;
    }

    document.addEventListener("input", validateEstimateForm);
    document.addEventListener("change", validateEstimateForm);
    document.addEventListener("click", validateEstimateForm);
    document.addEventListener("DOMContentLoaded", validateEstimateForm);

    validateEstimateForm();
})();
