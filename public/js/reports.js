document.addEventListener("DOMContentLoaded", () => {
    const invoicesUrl = document
        .querySelector('meta[name="reports-invoices-url"]')
        .getAttribute("content");

    const reportTabs = document.querySelectorAll(".report-tab");
    const reportTitle = document.getElementById("reportTitle");
    const periodFilter = document.getElementById("periodFilter");
    const agentFilter = document.getElementById("agentFilter");
    const customRange = document.getElementById("customRange");
    const fromDate = document.getElementById("fromDate");
    const toDate = document.getElementById("toDate");
    const applyCustomRange = document.getElementById("applyCustomRange");
    const reportsLoading = document.getElementById("reportsLoading");
    const reportPlaceholder = document.getElementById("reportPlaceholder");
    const reportTableWrap = document.getElementById("reportTableWrap");
    const reportsTableBody = document.getElementById("reportsTableBody");

    let activeReport = "invoices";

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
        reportPlaceholder.style.display = "none";
        reportTableWrap.style.display = "block";
    }

    function hideLoading() {
        reportsLoading.style.display = "none";
    }

    function renderRows(rows) {
        if (!rows || !rows.length) {
            reportsTableBody.innerHTML = `
                <tr>
                    <td colspan="11" class="empty-row">No records found.</td>
                </tr>
            `;
            return;
        }

        reportsTableBody.innerHTML = rows
            .map(
                (row) => `
            <tr>
                <td>${escapeHtml(row.payment_number)}</td>
                <td>${escapeHtml(row.date)}</td>
                <td>${escapeHtml(row.invoice_number)}</td>
                <td>${escapeHtml(row.customer)}</td>
                <td>${escapeHtml(row.payment_mode)}</td>
                <td>${formatMoney(row.fee)}</td>
                <td>${formatMoney(row.premium)}</td>
                <td>${escapeHtml(row.policy_number)}</td>
                <td>${escapeHtml(row.description)}</td>
                <td>${formatMoney(row.amount)}</td>
                <td>${escapeHtml(row.sale_agent)}</td>
            </tr>
        `,
            )
            .join("");
    }

    function loadInvoices() {
        if (activeReport !== "invoices") return;

        const period = periodFilter.value;

        if (period === "custom") {
            if (!fromDate.value || !toDate.value) {
                reportsTableBody.innerHTML = `
                    <tr>
                        <td colspan="11" class="empty-row">Select both dates to filter the report.</td>
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
                renderRows(data.rows || []);
                hideLoading();
            })
            .catch((error) => {
                console.error(error);
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
            agentFilter.disabled = false;
            reportPlaceholder.style.display = "none";
            reportTableWrap.style.display = "block";

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
        agentFilter.disabled = true;
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

        agentFilter.addEventListener("change", () => {
            if (activeReport !== "invoices") return;
            loadInvoices();
        });

        customRange.classList.remove("show");
        loadInvoices();
    });

    applyCustomRange.addEventListener("click", () => {
        loadInvoices();
    });

    activateTab("invoices");
});
