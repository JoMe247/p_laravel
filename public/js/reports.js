document.addEventListener("DOMContentLoaded", () => {
    const invoicesUrl = document
        .querySelector('meta[name="reports-invoices-url"]')
        .getAttribute("content");

    const reportTabs = document.querySelectorAll(".report-tab");
    const reportTitle = document.getElementById("reportTitle");

    const periodFilter = document.getElementById("periodFilter");
    const customRange = document.getElementById("customRange");
    const fromDate = document.getElementById("fromDate");
    const toDate = document.getElementById("toDate");
    const applyCustomRange = document.getElementById("applyCustomRange");

    const pageSizeSelect = document.getElementById("pageSizeSelect");
    const exportCsvBtn = document.getElementById("exportCsvBtn");
    const agentFilter = document.getElementById("agentFilter");
    const tableSearch = document.getElementById("tableSearch");

    const reportTableControls = document.getElementById("reportTableControls");
    const reportsLoading = document.getElementById("reportsLoading");
    const reportPlaceholder = document.getElementById("reportPlaceholder");
    const reportTableWrap = document.getElementById("reportTableWrap");
    const reportsTableBody = document.getElementById("reportsTableBody");

    let activeReport = "invoices";
    let allRows = [];

    function escapeHtml(value) {
        if (value === null || value === undefined) return "";
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatMoney(value) {
        const num = Number(value || 0);
        return `$${num.toFixed(2)}`;
    }

    function showLoading() {
        reportsLoading.style.display = "block";
        reportsTableBody.innerHTML = `
            <tr>
                <td colspan="11" class="empty-row">Loading...</td>
            </tr>
        `;
    }

    function hideLoading() {
        reportsLoading.style.display = "none";
    }

    function setControlsEnabled(enabled) {
        if (pageSizeSelect) pageSizeSelect.disabled = !enabled;
        if (exportCsvBtn) exportCsvBtn.disabled = !enabled;
        if (agentFilter) agentFilter.disabled = !enabled;
        if (tableSearch) tableSearch.disabled = !enabled;
    }

    function getSearchableText(row) {
        return [
            row.payment_number,
            row.date,
            row.invoice_number,
            row.customer,
            row.payment_mode,
            row.fee,
            row.premium,
            row.policy_number,
            row.description,
            row.amount,
            row.sale_agent,
        ]
            .join(" ")
            .toLowerCase();
    }

    function getFilteredRows() {
        const query = (tableSearch.value || "").trim().toLowerCase();

        if (!query) {
            return [...allRows];
        }

        return allRows.filter((row) => getSearchableText(row).includes(query));
    }

    function getVisibleRows(rows) {
        const size = pageSizeSelect.value;

        if (size === "all") {
            return rows;
        }

        return rows.slice(0, parseInt(size, 10));
    }

    function getTotals(rows) {
        return rows.reduce(
            (acc, row) => {
                acc.fee += Number(row.fee || 0);
                acc.premium += Number(row.premium || 0);
                acc.amount += Number(row.amount || 0);
                return acc;
            },
            {
                fee: 0,
                premium: 0,
                amount: 0,
            },
        );
    }

    function isCashMode(mode) {
        const value = String(mode || "")
            .trim()
            .toLowerCase();
        return value === "cash";
    }

    function isCardMode(mode) {
        const value = String(mode || "")
            .trim()
            .toLowerCase();
        return (
            value.includes("credit/debit card") ||
            value.includes("credit debit card") ||
            value.includes("credit card") ||
            value.includes("debit card") ||
            value === "card"
        );
    }

    function getMethodBreakdown(rows) {
        let feeCash = 0;
        let premiumCash = 0;
        let feeCard = 0;
        let premiumCard = 0;

        rows.forEach((row) => {
            const fee = Number(row.fee || 0);
            const premium = Number(row.premium || 0);
            const mode = row.payment_mode || "";

            if (isCashMode(mode)) {
                feeCash += fee;
                premiumCash += premium;
            }

            if (isCardMode(mode)) {
                feeCard += fee;
                premiumCard += premium;
            }
        });

        return {
            feeCash,
            premiumCash,
            totalCash: feeCash + premiumCash,
            feeCard,
            premiumCard,
            totalCard: feeCard + premiumCard,
        };
    }

    function csvEscape(value) {
        if (value === null || value === undefined) return '""';
        const text = String(value).replace(/"/g, '""');
        return `"${text}"`;
    }

    function getCurrentVisibleRows() {
        const filteredRows = getFilteredRows();
        return getVisibleRows(filteredRows);
    }

    function downloadCsv(filename, content) {
        const blob = new Blob(["\uFEFF" + content], {
            type: "text/csv;charset=utf-8;",
        });
        const url = URL.createObjectURL(blob);

        const link = document.createElement("a");
        link.href = url;
        link.setAttribute("download", filename);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        URL.revokeObjectURL(url);
    }

    function exportCurrentTableToCsv() {
        // esta linea sirve para filtrar por pagina 
        // const rows = getCurrentVisibleRows();
        // esta linea sirve para filtrar todo en la tabla de invoices
        const rows = getFilteredRows();

        if (!rows.length) {
            alert("No data to export.");
            return;
        }

        const totals = getTotals(rows);
        const breakdown = getMethodBreakdown(rows);

        const csvRows = [];

        csvRows.push(
            [
                "Payment #",
                "Date",
                "Invoice #",
                "Customer",
                "Payment Mode",
                "Fee",
                "Premium",
                "Policy #",
                "Description / Item",
                "Amount",
                "Sale Agent",
            ]
                .map(csvEscape)
                .join(","),
        );

        rows.forEach((row) => {
            csvRows.push(
                [
                    row.payment_number ?? "",
                    row.date ?? "",
                    row.invoice_number ?? "",
                    row.customer ?? "",
                    row.payment_mode ?? "",
                    Number(row.fee || 0).toFixed(2),
                    Number(row.premium || 0).toFixed(2),
                    row.policy_number ?? "",
                    row.description ?? "",
                    Number(row.amount || 0).toFixed(2),
                    row.sale_agent ?? "",
                ]
                    .map(csvEscape)
                    .join(","),
            );
        });

        csvRows.push("");

        csvRows.push(
            [
                csvEscape("Total (Per Page)"),
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape(Number(totals.fee).toFixed(2)),
                csvEscape(Number(totals.premium).toFixed(2)),
                csvEscape(""),
                csvEscape(""),
                csvEscape(Number(totals.amount).toFixed(2)),
                csvEscape(""),
            ].join(","),
        );

        csvRows.push(
            [
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape("Fee Cash"),
                csvEscape(Number(breakdown.feeCash).toFixed(2)),
            ].join(","),
        );
        csvRows.push(
            [
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape("Premium Cash"),
                csvEscape(Number(breakdown.premiumCash).toFixed(2)),
            ].join(","),
        );
        csvRows.push(
            [
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape("Total Cash"),
                csvEscape(Number(breakdown.totalCash).toFixed(2)),
            ].join(","),
        );
        csvRows.push(
            [
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape("Fee Credit/Debit Card"),
                csvEscape(Number(breakdown.feeCard).toFixed(2)),
            ].join(","),
        );
        csvRows.push(
            [
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape("Premium Credit/Debit Card"),
                csvEscape(Number(breakdown.premiumCard).toFixed(2)),
            ].join(","),
        );
        csvRows.push(
            [
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape(""),
                csvEscape("Total Credit/Debit Card"),
                csvEscape(Number(breakdown.totalCard).toFixed(2)),
            ].join(","),
        );

        const now = new Date();
        const filename = `reports_invoices_${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}_${String(now.getHours()).padStart(2, "0")}-${String(now.getMinutes()).padStart(2, "0")}-${String(now.getSeconds()).padStart(2, "0")}.csv`;

        downloadCsv(filename, csvRows.join("\n"));
    }

    function buildNormalRows(rows) {
        return rows
            .map(
                (row) => `
            <tr>
                <td>${escapeHtml(row.payment_number)}</td>
                <td>${escapeHtml(row.date)}</td>
                <td>${escapeHtml(row.invoice_number)}</td>
                <td>${escapeHtml(row.customer)}</td>
                <td>${escapeHtml(row.payment_mode)}</td>
                <td class="money-cell">${formatMoney(row.fee)}</td>
                <td class="money-cell">${formatMoney(row.premium)}</td>
                <td>${escapeHtml(row.policy_number)}</td>
                <td>${escapeHtml(row.description)}</td>
                <td class="money-cell">${formatMoney(row.amount)}</td>
                <td>${escapeHtml(row.sale_agent)}</td>
            </tr>
        `,
            )
            .join("");
    }

    function buildTotalsRow(totals) {
        return `
            <tr class="totals-row">
                <td colspan="5" class="summary-title-cell">Total (Per Page)</td>
                <td class="money-cell">${formatMoney(totals.fee)}</td>
                <td class="money-cell">${formatMoney(totals.premium)}</td>
                <td></td>
                <td></td>
                <td class="money-cell">${formatMoney(totals.amount)}</td>
                <td></td>
            </tr>
        `;
    }

    function buildBreakdownRows(breakdown) {
        const items = [
            ["Fee Cash", breakdown.feeCash],
            ["Premium Cash", breakdown.premiumCash],
            ["Total Cash", breakdown.totalCash],
            ["Fee Credit/Debit Card", breakdown.feeCard],
            ["Premium Credit/Debit Card", breakdown.premiumCard],
            ["Total Credit/Debit Card", breakdown.totalCard],
        ];

        return items
            .map(
                (item) => `
            <tr class="summary-breakdown-row">
                <td colspan="4" class="summary-spacer"></td>
                <td class="breakdown-label">${escapeHtml(item[0])}</td>
                <td class="money-cell breakdown-value">${formatMoney(item[1])}</td>
                <td colspan="5" class="summary-spacer"></td>
            </tr>
        `,
            )
            .join("");
    }

    function renderRows() {
        const filteredRows = getFilteredRows();
        const visibleRows = getVisibleRows(filteredRows);

        if (!visibleRows.length) {
            reportsTableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="empty-row">No records found.</td>
                </tr>
            `;
            return;
        }

        const totals = getTotals(visibleRows);
        const breakdown = getMethodBreakdown(visibleRows);

        let html = "";
        html += buildNormalRows(visibleRows);
        html += buildTotalsRow(totals);
        html += buildBreakdownRows(breakdown);

        reportsTableBody.innerHTML = html;
    }

    function loadInvoices() {
        if (activeReport !== "invoices") return;

        const period = periodFilter.value;

        if (period === "custom") {
            if (!fromDate.value || !toDate.value) {
                reportsTableBody.innerHTML = `
                    <tr>
                        <td colspan="11" class="empty-row">Select both dates and press Apply.</td>
                    </tr>
                `;
                hideLoading();
                return;
            }
        }

        showLoading();

        const params = new URLSearchParams();
        params.append("period", period);
        params.append("agent", agentFilter.value || "");

        if (period === "custom") {
            params.append("from", fromDate.value);
            params.append("to", toDate.value);
        }

        fetch(`${invoicesUrl}?${params.toString()}`, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                allRows = Array.isArray(data.rows) ? data.rows : [];
                renderRows();
                hideLoading();
            })
            .catch((error) => {
                console.error(error);
                allRows = [];
                reportsTableBody.innerHTML = `
                    <tr>
                        <td colspan="11" class="empty-row">Error loading report.</td>
                    </tr>
                `;
                hideLoading();
            });
    }

    function activateTab(report) {
        activeReport = report;

        reportTabs.forEach((tab) => {
            tab.classList.toggle("active", tab.dataset.report === report);
        });

        if (report === "invoices") {
            reportTitle.textContent = "Generated Report";
            periodFilter.disabled = false;
            setControlsEnabled(true);

            reportPlaceholder.style.display = "none";
            reportTableWrap.style.display = "block";
            reportTableControls.style.display = "flex";

            if (periodFilter.value === "custom") {
                customRange.classList.add("show");
            } else {
                customRange.classList.remove("show");
            }

            loadInvoices();
            return;
        }

        reportTitle.textContent = "Generated Report";
        periodFilter.disabled = true;
        setControlsEnabled(false);

        customRange.classList.remove("show");
        reportsLoading.style.display = "none";
        reportTableWrap.style.display = "none";
        reportPlaceholder.style.display = "flex";

        reportPlaceholder.innerHTML = `
            <div class="placeholder-box">
                <strong>${report.toUpperCase()}</strong><br>
                For now, only the INVOICES report is enabled.
            </div>
        `;
    }

    reportTabs.forEach((tab) => {
        tab.addEventListener("click", () => {
            activateTab(tab.dataset.report);
        });
    });

    periodFilter.addEventListener("change", () => {
        if (activeReport !== "invoices") return;

        if (periodFilter.value === "custom") {
            customRange.classList.add("show");
            reportsTableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="empty-row">Select both dates and press Apply.</td>
                </tr>
            `;
            return;
        }

        customRange.classList.remove("show");
        loadInvoices();
    });

    applyCustomRange.addEventListener("click", () => {
        loadInvoices();
    });

    agentFilter.addEventListener("change", () => {
        if (activeReport !== "invoices") return;
        loadInvoices();
    });

    pageSizeSelect.addEventListener("change", () => {
        if (activeReport !== "invoices") return;
        renderRows();
    });

    exportCsvBtn.addEventListener("click", () => {
        if (activeReport !== "invoices") return;
        exportCurrentTableToCsv();
    });

    tableSearch.addEventListener("input", () => {
        if (activeReport !== "invoices") return;
        renderRows();
    });

    activateTab("invoices");
});
