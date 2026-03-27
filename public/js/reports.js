document.addEventListener("DOMContentLoaded", () => {
    const getMetaContent = (name) =>
        document.querySelector(`meta[name="${name}"]`)?.getAttribute("content") || "";

    const reportUrls = {
        invoices: getMetaContent("reports-invoices-url"),
        items: getMetaContent("reports-items-url"),
        estimates: getMetaContent("reports-estimates-url"),
        customers: getMetaContent("reports-customers-url"),
    };

    const reportConfigs = {
        invoices: {
            title: "Invoices Report",
            url: reportUrls.invoices,
            usePeriodFilter: true,
            useAgentFilter: true,
            includeTotals: true,
            filePrefix: "reports_invoices",
            columns: [
                { key: "payment_number", label: "Payment #", type: "number" },
                { key: "date", label: "Date", type: "text" },
                { key: "invoice_number", label: "Invoice #", type: "text" },
                { key: "customer", label: "Customer", type: "text" },
                { key: "payment_mode", label: "Payment Mode", type: "text" },
                { key: "fee", label: "Fee", type: "money" },
                { key: "premium", label: "Premium", type: "money" },
                { key: "policy_number", label: "Policy #", type: "text" },
                { key: "description", label: "Description / Item", type: "text" },
                { key: "amount", label: "Amount", type: "money" },
                { key: "sale_agent", label: "Sale Agent", type: "text" },
            ],
        },
        items: {
            title: "Items Report",
            url: reportUrls.items,
            usePeriodFilter: true,
            useAgentFilter: true,
            includeTotals: false,
            filePrefix: "reports_items",
            columns: [
                { key: "item_name", label: "Items", type: "text" },
                { key: "item_count", label: "Amount", type: "number" },
                { key: "item_total_amount", label: "Total Amount", type: "money" },
                { key: "item_average", label: "Promedio", type: "money" },
            ],
        },
        estimates: {
            title: "Estimates Report",
            url: reportUrls.estimates,
            usePeriodFilter: true,
            useAgentFilter: true,
            includeTotals: false,
            filePrefix: "reports_estimates",
            columns: [
                { key: "payment_number", label: "Estimate #", type: "number" },
                { key: "date", label: "Date", type: "text" },
                { key: "invoice_number", label: "Estimate #", type: "text" },
                { key: "customer", label: "Customer", type: "text" },
                { key: "payment_mode", label: "Payment Mode", type: "text" },
                { key: "fee", label: "Fee", type: "money" },
                { key: "premium", label: "Premium", type: "money" },
                { key: "policy_number", label: "Policy #", type: "text" },
                { key: "description", label: "Description / Item", type: "text" },
                { key: "amount", label: "Amount", type: "money" },
                { key: "sale_agent", label: "Sale Agent", type: "text" },
            ],
        },
        customers: {
            title: "Customers Report",
            url: reportUrls.customers,
            usePeriodFilter: true,
            useAgentFilter: false,
            includeTotals: false,
            filePrefix: "reports_customers",
            columns: [],
        },
    };

    const reportTabs = document.querySelectorAll(".report-tab");
    const reportTitle = document.getElementById("reportTitle");

    const periodFilter = document.getElementById("periodFilter");
    const customRange = document.getElementById("customRange");
    const fromDate = document.getElementById("fromDate");
    const toDate = document.getElementById("toDate");
    const applyCustomRange = document.getElementById("applyCustomRange");

    const pageSizeSelect = document.getElementById("pageSizeSelect");
    const exportCsvBtn = document.getElementById("exportCsvBtn");
    const exportPdfBtn = document.getElementById("exportPdfBtn");
    const agentFilter = document.getElementById("agentFilter");
    const tableSearch = document.getElementById("tableSearch");

    const reportTableControls = document.getElementById("reportTableControls");
    const reportsLoading = document.getElementById("reportsLoading");
    const reportPlaceholder = document.getElementById("reportPlaceholder");
    const reportTableWrap = document.getElementById("reportTableWrap");
    const reportsTableHead = document.getElementById("reportsTableHead");
    const reportsTableBody = document.getElementById("reportsTableBody");

    const agentFilterBlock = agentFilter ? agentFilter.closest(".report-filter-block") : null;

    let activeReport = "invoices";
    let allRows = [];
    let activeColumns = [...(reportConfigs.invoices.columns || [])];

    function getActiveConfig() {
        return reportConfigs[activeReport] || null;
    }

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

    function formatColumnValue(column, value, exportMode = false) {
        if (column.type === "money") {
            return exportMode ? Number(value || 0).toFixed(2) : formatMoney(value);
        }

        if (column.type === "number") {
            return exportMode ? String(Number(value || 0)) : String(value ?? 0);
        }

        return String(value ?? "");
    }

    function setControlsEnabled(enabled) {
        if (pageSizeSelect) pageSizeSelect.disabled = !enabled;
        if (exportCsvBtn) exportCsvBtn.disabled = !enabled;
        if (exportPdfBtn) exportPdfBtn.disabled = !enabled;
        if (tableSearch) tableSearch.disabled = !enabled;
    }

    function setEmptyState(message) {
        const colspan = Math.max(activeColumns.length, 1);
        reportsTableBody.innerHTML = `
            <tr>
                <td colspan="${colspan}" class="empty-row">${escapeHtml(message)}</td>
            </tr>
        `;
    }

    function showLoading() {
        reportsLoading.style.display = "block";
        setEmptyState("Loading...");
    }

    function hideLoading() {
        reportsLoading.style.display = "none";
    }

    function buildTableHead() {
        if (!reportsTableHead) return;

        if (!activeColumns.length) {
            reportsTableHead.innerHTML = "";
            return;
        }

        reportsTableHead.innerHTML = `
            <tr>
                ${activeColumns.map(column => `<th>${escapeHtml(column.label)}</th>`).join("")}
            </tr>
        `;
    }

    function getSearchableText(row) {
        return activeColumns
            .map(column => row[column.key] ?? "")
            .join(" ")
            .toLowerCase();
    }

    function getFilteredRows() {
        const query = (tableSearch.value || "").trim().toLowerCase();

        if (!query) {
            return [...allRows];
        }

        return allRows.filter(row => getSearchableText(row).includes(query));
    }

    function getVisibleRows(rows) {
        const size = pageSizeSelect.value;

        if (size === "all") {
            return rows;
        }

        return rows.slice(0, parseInt(size, 10));
    }

    function getCurrentVisibleRows() {
        const filteredRows = getFilteredRows();
        return getVisibleRows(filteredRows);
    }

    function getTotals(rows) {
        return rows.reduce((acc, row) => {
            acc.fee += Number(row.fee || 0);
            acc.premium += Number(row.premium || 0);
            acc.amount += Number(row.amount || 0);
            return acc;
        }, { fee: 0, premium: 0, amount: 0 });
    }

    function isCashMode(mode) {
        const value = String(mode || "").trim().toLowerCase();
        return value === "cash";
    }

    function isCardMode(mode) {
        const value = String(mode || "").trim().toLowerCase();
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

        rows.forEach(row => {
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

    function buildNormalRows(rows) {
        return rows.map(row => `
            <tr>
                ${activeColumns.map(column => {
                    const value = formatColumnValue(column, row[column.key], false);
                    const moneyClass = column.type === "money" ? "money-cell" : "";
                    return `<td class="${moneyClass}">${escapeHtml(value)}</td>`;
                }).join("")}
            </tr>
        `).join("");
    }

    function buildTotalsRow(totals) {
        const feeIndex = activeColumns.findIndex(col => col.key === "fee");
        const premiumIndex = activeColumns.findIndex(col => col.key === "premium");
        const amountIndex = activeColumns.findIndex(col => col.key === "amount");

        const cells = activeColumns.map(() => "");
        if (cells.length > 0) cells[0] = "Total (Per Page)";
        if (feeIndex > -1) cells[feeIndex] = formatMoney(totals.fee);
        if (premiumIndex > -1) cells[premiumIndex] = formatMoney(totals.premium);
        if (amountIndex > -1) cells[amountIndex] = formatMoney(totals.amount);

        return `
            <tr class="totals-row">
                ${cells.map((cell, index) => {
                    const isMoney = [feeIndex, premiumIndex, amountIndex].includes(index);
                    const classes = [
                        isMoney ? "money-cell" : "",
                        index === 0 ? "summary-title-cell" : ""
                    ].join(" ").trim();

                    return `<td class="${classes}">${escapeHtml(cell)}</td>`;
                }).join("")}
            </tr>
        `;
    }

    function buildBreakdownRows(breakdown) {
        const labelIndex = activeColumns.findIndex(col => col.key === "payment_mode");
        const valueIndex = activeColumns.findIndex(col => col.key === "fee");

        if (labelIndex === -1 || valueIndex === -1) {
            return "";
        }

        const items = [
            ["Fee Cash", breakdown.feeCash],
            ["Premium Cash", breakdown.premiumCash],
            ["Total Cash", breakdown.totalCash],
            ["Fee Credit/Debit Card", breakdown.feeCard],
            ["Premium Credit/Debit Card", breakdown.premiumCard],
            ["Total Credit/Debit Card", breakdown.totalCard],
        ];

        return items.map(([label, value]) => {
            const cells = activeColumns.map(() => "");
            cells[labelIndex] = label;
            cells[valueIndex] = formatMoney(value);

            return `
                <tr class="summary-breakdown-row">
                    ${cells.map((cell, index) => {
                        const classes = [
                            index === labelIndex ? "breakdown-label" : "",
                            index === valueIndex ? "money-cell breakdown-value" : "",
                            cell === "" ? "summary-spacer" : ""
                        ].join(" ").trim();

                        return `<td class="${classes}">${escapeHtml(cell)}</td>`;
                    }).join("")}
                </tr>
            `;
        }).join("");
    }

    function renderRows() {
        const filteredRows = getFilteredRows();
        const visibleRows = getVisibleRows(filteredRows);

        if (!visibleRows.length) {
            setEmptyState("No records found.");
            return;
        }

        let html = buildNormalRows(visibleRows);

        if (activeReport === "invoices") {
            const totals = getTotals(visibleRows);
            const breakdown = getMethodBreakdown(visibleRows);

            html += buildTotalsRow(totals);
            html += buildBreakdownRows(breakdown);
        }

        reportsTableBody.innerHTML = html;
    }

    function loadActiveReportData() {
        const config = getActiveConfig();
        if (!config || !config.url) return;

        if (config.usePeriodFilter && periodFilter.value === "custom") {
            if (!fromDate.value || !toDate.value) {
                setEmptyState("Select both dates and press Apply.");
                hideLoading();
                return;
            }
        }

        showLoading();
        buildTableHead();

        const params = new URLSearchParams();

        if (config.usePeriodFilter) {
            params.append("period", periodFilter.value || "all");

            if (periodFilter.value === "custom") {
                params.append("from", fromDate.value);
                params.append("to", toDate.value);
            }
        }

        if (config.useAgentFilter) {
            params.append("agent", agentFilter.value || "");
        }

        fetch(`${config.url}?${params.toString()}`, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
            .then(response => response.json())
            .then(data => {
                activeColumns = Array.isArray(data.columns) && data.columns.length
                    ? data.columns
                    : [...(config.columns || [])];

                buildTableHead();

                allRows = Array.isArray(data.rows) ? data.rows : [];
                renderRows();
                hideLoading();
            })
            .catch(error => {
                console.error(error);
                allRows = [];
                setEmptyState("Error loading report.");
                hideLoading();
            });
    }

    function csvEscape(value) {
        if (value === null || value === undefined) return '""';
        const text = String(value).replace(/"/g, '""');
        return `"${text}"`;
    }

    function downloadCsv(filename, content) {
        const blob = new Blob(["\uFEFF" + content], { type: "text/csv;charset=utf-8;" });
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
        const config = getActiveConfig();
        if (!config) return;

        const rows = getCurrentVisibleRows();
        // const rows = getFilteredRows();

        if (!rows.length) {
            alert("No data to export.");
            return;
        }

        const csvRows = [];

        csvRows.push(
            activeColumns.map(column => csvEscape(column.label)).join(",")
        );

        rows.forEach(row => {
            csvRows.push(
                activeColumns
                    .map(column => csvEscape(formatColumnValue(column, row[column.key], true)))
                    .join(",")
            );
        });

        if (activeReport === "invoices") {
            const totals = getTotals(rows);
            const breakdown = getMethodBreakdown(rows);

            const feeIndex = activeColumns.findIndex(col => col.key === "fee");
            const premiumIndex = activeColumns.findIndex(col => col.key === "premium");
            const amountIndex = activeColumns.findIndex(col => col.key === "amount");
            const labelIndex = activeColumns.findIndex(col => col.key === "payment_mode");
            const valueIndex = activeColumns.findIndex(col => col.key === "fee");

            const totalRow = activeColumns.map(() => "");
            if (totalRow.length > 0) totalRow[0] = "Total (Per Page)";
            if (feeIndex > -1) totalRow[feeIndex] = Number(totals.fee).toFixed(2);
            if (premiumIndex > -1) totalRow[premiumIndex] = Number(totals.premium).toFixed(2);
            if (amountIndex > -1) totalRow[amountIndex] = Number(totals.amount).toFixed(2);

            csvRows.push("");
            csvRows.push(totalRow.map(csvEscape).join(","));

            [
                ["Fee Cash", breakdown.feeCash],
                ["Premium Cash", breakdown.premiumCash],
                ["Total Cash", breakdown.totalCash],
                ["Fee Credit/Debit Card", breakdown.feeCard],
                ["Premium Credit/Debit Card", breakdown.premiumCard],
                ["Total Credit/Debit Card", breakdown.totalCard],
            ].forEach(([label, value]) => {
                const row = activeColumns.map(() => "");
                if (labelIndex > -1) row[labelIndex] = label;
                if (valueIndex > -1) row[valueIndex] = Number(value).toFixed(2);
                csvRows.push(row.map(csvEscape).join(","));
            });
        }

        const now = new Date();
        const filename = `${config.filePrefix}_${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}_${String(now.getHours()).padStart(2, "0")}-${String(now.getMinutes()).padStart(2, "0")}-${String(now.getSeconds()).padStart(2, "0")}.csv`;

        downloadCsv(filename, csvRows.join("\n"));
    }

    function exportCurrentTableToPdf() {
        const config = getActiveConfig();
        if (!config) return;

        const { jsPDF } = window.jspdf || {};
        if (!jsPDF) {
            alert("PDF library not loaded.");
            return;
        }

        const rows = getCurrentVisibleRows();
        // const rows = getFilteredRows();

        if (!rows.length) {
            alert("No data to export.");
            return;
        }

        const doc = new jsPDF({
            orientation: "landscape",
            unit: "pt",
            format: "a4"
        });

        const tableHead = [activeColumns.map(column => column.label)];
        const tableBody = rows.map(row =>
            activeColumns.map(column => formatColumnValue(column, row[column.key], true))
        );

        if (activeReport === "invoices") {
            const totals = getTotals(rows);
            const breakdown = getMethodBreakdown(rows);

            const feeIndex = activeColumns.findIndex(col => col.key === "fee");
            const premiumIndex = activeColumns.findIndex(col => col.key === "premium");
            const amountIndex = activeColumns.findIndex(col => col.key === "amount");
            const labelIndex = activeColumns.findIndex(col => col.key === "payment_mode");
            const valueIndex = activeColumns.findIndex(col => col.key === "fee");

            const totalRow = activeColumns.map(() => "");
            if (totalRow.length > 0) totalRow[0] = "Total (Per Page)";
            if (feeIndex > -1) totalRow[feeIndex] = Number(totals.fee).toFixed(2);
            if (premiumIndex > -1) totalRow[premiumIndex] = Number(totals.premium).toFixed(2);
            if (amountIndex > -1) totalRow[amountIndex] = Number(totals.amount).toFixed(2);
            tableBody.push(totalRow);

            [
                ["Fee Cash", breakdown.feeCash],
                ["Premium Cash", breakdown.premiumCash],
                ["Total Cash", breakdown.totalCash],
                ["Fee Credit/Debit Card", breakdown.feeCard],
                ["Premium Credit/Debit Card", breakdown.premiumCard],
                ["Total Credit/Debit Card", breakdown.totalCard],
            ].forEach(([label, value]) => {
                const row = activeColumns.map(() => "");
                if (labelIndex > -1) row[labelIndex] = label;
                if (valueIndex > -1) row[valueIndex] = Number(value).toFixed(2);
                tableBody.push(row);
            });
        }

        doc.setFontSize(16);
        doc.text(config.title, 40, 35);

        doc.setFontSize(10);
        doc.text(`Period: ${periodFilter.value || "all"}`, 40, 55);
        doc.text(`Sale Agent: ${agentFilter.value || "All Agents"}`, 220, 55);
        doc.text(`Search: ${tableSearch.value || "None"}`, 420, 55);

        doc.autoTable({
            head: tableHead,
            body: tableBody,
            startY: 70,
            theme: "grid",
            styles: {
                fontSize: 8,
                cellPadding: 4,
                overflow: "linebreak",
                valign: "middle"
            },
            headStyles: {
                fillColor: [31, 41, 55],
                textColor: 255,
                fontStyle: "bold"
            },
            bodyStyles: {
                textColor: [55, 65, 81]
            },
            alternateRowStyles: {
                fillColor: [249, 250, 251]
            },
            margin: { top: 70, left: 20, right: 20, bottom: 20 }
        });

        const now = new Date();
        const filename = `${config.filePrefix}_${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}_${String(now.getHours()).padStart(2, "0")}-${String(now.getMinutes()).padStart(2, "0")}-${String(now.getSeconds()).padStart(2, "0")}.pdf`;

        doc.save(filename);
    }

    function activateTab(report) {
        activeReport = report;

        reportTabs.forEach(tab => {
            tab.classList.toggle("active", tab.dataset.report === report);
        });

        const config = getActiveConfig();

        if (config && config.url) {
            reportTitle.textContent = config.title;
            setControlsEnabled(true);

            periodFilter.disabled = !config.usePeriodFilter;

            if (agentFilterBlock) {
                agentFilterBlock.style.display = config.useAgentFilter ? "" : "none";
            }

            if (agentFilter) {
                agentFilter.disabled = !config.useAgentFilter;
            }

            if (!config.usePeriodFilter) {
                customRange.classList.remove("show");
            } else if (periodFilter.value === "custom") {
                customRange.classList.add("show");
            } else {
                customRange.classList.remove("show");
            }

            activeColumns = [...(config.columns || [])];
            buildTableHead();

            reportPlaceholder.style.display = "none";
            reportTableWrap.style.display = "block";
            reportTableControls.style.display = "flex";

            loadActiveReportData();
            return;
        }

        reportTitle.textContent = "Generated Report";
        setControlsEnabled(false);
        periodFilter.disabled = true;
        customRange.classList.remove("show");

        if (agentFilterBlock) {
            agentFilterBlock.style.display = "";
        }

        reportsLoading.style.display = "none";
        reportTableWrap.style.display = "none";
        reportPlaceholder.style.display = "flex";

        reportPlaceholder.innerHTML = `
            <div class="placeholder-box">
                <strong>${report.toUpperCase()}</strong><br>
                This section will be enabled later.
            </div>
        `;
    }

    reportTabs.forEach(tab => {
        tab.addEventListener("click", () => {
            activateTab(tab.dataset.report);
        });
    });

    periodFilter.addEventListener("change", () => {
        const config = getActiveConfig();
        if (!config || !config.url) return;

        if (periodFilter.value === "custom") {
            customRange.classList.add("show");
            setEmptyState("Select both dates and press Apply.");
            return;
        }

        customRange.classList.remove("show");
        loadActiveReportData();
    });

    applyCustomRange.addEventListener("click", () => {
        const config = getActiveConfig();
        if (!config || !config.url) return;
        loadActiveReportData();
    });

    if (agentFilter) {
        agentFilter.addEventListener("change", () => {
            const config = getActiveConfig();
            if (!config || !config.url || !config.useAgentFilter) return;
            loadActiveReportData();
        });
    }

    pageSizeSelect.addEventListener("change", () => {
        const config = getActiveConfig();
        if (!config || !config.url) return;
        renderRows();
    });

    tableSearch.addEventListener("input", () => {
        const config = getActiveConfig();
        if (!config || !config.url) return;
        renderRows();
    });

    exportCsvBtn.addEventListener("click", () => {
        const config = getActiveConfig();
        if (!config || !config.url) return;
        exportCurrentTableToCsv();
    });

    exportPdfBtn.addEventListener("click", () => {
        const config = getActiveConfig();
        if (!config || !config.url) return;
        exportCurrentTableToPdf();
    });

    activateTab("invoices");
});