let pdfDoc = null;
let pdfBytes = null;

const canvas = document.getElementById("pdfCanvas");
const ctx = canvas.getContext("2d");
const inputOverlay = document.getElementById("inputOverlay");

let scaleFactor = 0.85;
let currentPageNumber = 1;
let totalPages = 0;
let overlayData = [];
let templateDataGlobal = null;

let selectedCustomer = null; // {ID, Name, Phone, Phone2, Email1, Email2}
let selectedPolicyNumber = "";

const csrf = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

// UI
const templateSelect = document.getElementById("templateSelect");
const customerSearch = document.getElementById("customerSearch");
const customerSuggest = document.getElementById("customerSuggest");

const saveDocBtn = document.getElementById("saveDocBtn");

const selectedCustomerInfo = document.getElementById("selectedCustomerInfo");
const policySelect = document.getElementById("policySelect");

const viewerControls = document.getElementById("viewerControls"); // ahora es el contenedor de botones
const pageInfoBar = document.getElementById("pageInfoBar"); // barra "Total Pages / Current Page"

const prevPageBtn = document.getElementById("backPage");
const nextPageBtn = document.getElementById("nextPage");

const currentPageEl = document.getElementById("counter");
const totalPagesEl = document.getElementById("total-pages");

// ---------------------------
// Init
// ---------------------------
document.addEventListener("DOMContentLoaded", async () => {
    await loadTemplateOptions();

    templateSelect.addEventListener("change", async () => {
        const id = templateSelect.value;
        resetCustomerAndPolicies();

        if (!id) {
            clearViewer();
            customerSearch.disabled = true;
            return;
        }

        customerSearch.disabled = false;

        const data = await fetchTemplateData(id);
        if (!data) return;

        templateDataGlobal = data;

        // Si el template trae meta de escala, úsala
        if (
            data.overlay_meta &&
            typeof data.overlay_meta.scaleFactor === "number"
        ) {
            scaleFactor = data.overlay_meta.scaleFactor;
        } else {
            scaleFactor = 0.85; // fallback
        }
        await loadPDF(data);

        // Hasta que elija customer, no habilitamos policies ni save
        saveDocBtn.disabled = true;
        policySelect.disabled = true;
    });

    // Sugerencias customer
    attachCustomerSuggest();

    policySelect.addEventListener("change", () => {
        selectedPolicyNumber = policySelect.value || "";
    });

    saveDocBtn.addEventListener("click", savePDFToServer);

    // pagination (igual que ya lo tienes)
    prevPageBtn.addEventListener("click", () => {
        if (currentPageNumber > 1) {
            saveInputChangesForCurrentPage();
            currentPageNumber--;
            renderPage(currentPageNumber, overlayData);
            updatePagerButtons();
        }
    });

    nextPageBtn.addEventListener("click", () => {
        if (currentPageNumber < totalPages) {
            saveInputChangesForCurrentPage();
            currentPageNumber++;
            renderPage(currentPageNumber, overlayData);
            updatePagerButtons();
        }
    });
});

// ---------------------------
// Templates
// ---------------------------
async function loadTemplateOptions() {
    try {
        const res = await fetch(window.ROUTES.templatesOptions, {
            headers: { Accept: "application/json" },
        });
        const json = await res.json();
        if (!json.ok) return;

        json.templates.forEach((t) => {
            const opt = document.createElement("option");
            opt.value = t.id;
            opt.textContent = t.template_name;
            templateSelect.appendChild(opt);
        });
    } catch (e) {
        console.error(e);
        alert("Failed to load templates.");
    }
}

async function fetchTemplateData(id) {
    try {
        const res = await fetch(`${window.ROUTES.templateDataBase}/${id}`, {
            headers: { Accept: "application/json" },
        });
        const json = await res.json();
        if (!json.ok) {
            alert(json.error || "Template not found.");
            return null;
        }
        return json;
    } catch (e) {
        console.error(e);
        alert("Failed to load template data.");
        return null;
    }
}

// ---------------------------
// PDF Viewer + Inputs overlay
// ---------------------------
async function loadPDF(templateData) {
    const pdfjsLib = window["pdfjs-dist/build/pdf"];

    overlayData = Array.isArray(templateData.overlay_data)
        ? templateData.overlay_data
        : [];

    try {
        // Ajusta aquí si tu PDF está en otra ruta.
        // Lo más común en Laravel: servirlo desde /storage/... (public disk)
        // Asegúrate de tener: php artisan storage:link
        //
        // templateData.original_file_path debe ser algo como:
        // "pdf_overlays/originals/miarchivo.pdf"  (dentro de storage/app/public)
        //
        const pdfUrl = `${window.BASE_URL}/documents/templates/file/${templateData.id}?v=${Date.now()}`;

        const pdfResponse = await fetch(pdfUrl);
        if (!pdfResponse.ok) throw new Error("Failed to fetch PDF.");

        pdfBytes = await pdfResponse.arrayBuffer();
        pdfDoc = await pdfjsLib.getDocument(pdfBytes).promise;

        totalPages = pdfDoc.numPages;
        totalPagesEl.textContent = totalPages;
        currentPageNumber = 1;

      

        currentPageEl.textContent = currentPageNumber;

        // habilitar/deshabilitar igual que template
        if (totalPages >= 2) {
            prevPageBtn.disabled = false;
            nextPageBtn.disabled = false;
        } else {
            prevPageBtn.disabled = true;
            nextPageBtn.disabled = true;
        }

        pageInfoBar.style.display = "";
        viewerControls.style.display = "";
        updatePagerButtons();

        await renderPage(currentPageNumber, overlayData);
    } catch (err) {
        console.error(err);
        alert(
            "Failed to load PDF preview. Revisa el path original_file_path/original_original.",
        );
        clearViewer();
        if (pageInfoBar) pageInfoBar.style.display = "none";
        if (viewerControls) viewerControls.style.display = "none";
    }
}

function saveInputChangesForCurrentPage() {
    const inputFields = inputOverlay.querySelectorAll("input");
    inputFields.forEach((inputField) => {
        const idx = Number(inputField.dataset.overlayIndex);
        const overlay = overlayData[idx];
        if (overlay && overlay.page === currentPageNumber) {
            overlay.text = `{{${inputField.value}}}`;
        }
    });
}

async function renderPage(pageNumber, overlayDataParam) {
    if (!pdfDoc) return;

    const page = await pdfDoc.getPage(pageNumber);
    const viewport = page.getViewport({ scale: scaleFactor });

    canvas.width = viewport.width;
    canvas.height = viewport.height;

    await page.render({ canvasContext: ctx, viewport }).promise;

    currentPageEl.textContent = pageNumber;

    // Importante: guardamos lo actual antes de limpiar
    saveInputChangesForCurrentPage();

    inputOverlay.innerHTML = "";

    overlayDataParam.forEach((overlay, index) => {
        if (
            overlay.page === pageNumber &&
            typeof overlay.text === "string" &&
            overlay.text.includes("{{") &&
            overlay.text.includes("}}")
        ) {
            const inputField = document.createElement("input");
            inputField.type = "text";
            inputField.value = overlay.text.replace(/{{|}}/g, "");

            // Posición absoluta
            inputField.style.position = "absolute";

            // Escala visual real del canvas (si el canvas se ve diferente por CSS)
            const rect = canvas.getBoundingClientRect();
            const scaleX = rect.width / canvas.width;
            const scaleY = rect.height / canvas.height;

            // Asegura overlay igual al canvas visible
            inputOverlay.style.width = rect.width + "px";
            inputOverlay.style.height = rect.height + "px";

            // Coordenadas (guardadas como "sin escala" en template.js)
            let leftPx = overlay.x * scaleFactor * scaleX;
            let topPx = overlay.y * scaleFactor * scaleY;

            // ✅ offset fino (el que preguntabas)
            const OFFSET_Y = 2; // prueba 2 o 3
            topPx += OFFSET_Y;

            // Clamp para que no se salgan
            leftPx = Math.max(0, Math.min(leftPx, rect.width - 10));
            topPx = Math.max(0, Math.min(topPx, rect.height - 10));

            inputField.style.left = `${leftPx}px`;
            inputField.style.top = `${topPx}px`;

            inputField.dataset.placeholder = overlay.text;

            inputOverlay.appendChild(inputField);
            inputField.dataset.overlayIndex = String(index);
        }
    });
}

function updatePagerButtons() {
    if (!prevPageBtn || !nextPageBtn) return;
    prevPageBtn.disabled = currentPageNumber <= 1;
    nextPageBtn.disabled = currentPageNumber >= totalPages;
}

function clearViewer() {
    pdfDoc = null;
    pdfBytes = null;
    overlayData = [];
    templateDataGlobal = null;
    canvas.width = 1;
    canvas.height = 1;
    inputOverlay.innerHTML = "";
    viewerControls.classList.add("hidden");
    if (pageInfoBar) pageInfoBar.style.display = "none";
    if (viewerControls) viewerControls.style.display = "none";
}

// ---------------------------
// Customer suggestions + selection
// ---------------------------
function attachCustomerSuggest() {
    let t = null;

    customerSearch.addEventListener("input", () => {
        const val = customerSearch.value.trim();
        clearTimeout(t);

        if (val.length < 2) {
            hideSuggest(customerSuggest);
            return;
        }

        t = setTimeout(async () => {
            const customers = await fetchCustomers(val);
            renderCustomerSuggest(customers);
        }, 220);
    });

    document.addEventListener("click", (e) => {
        if (
            !customerSuggest.contains(e.target) &&
            e.target !== customerSearch
        ) {
            hideSuggest(customerSuggest);
        }
    });
}

function renderCustomerSuggest(customers) {
    customerSuggest.innerHTML = "";

    if (!customers.length) {
        hideSuggest(customerSuggest);
        return;
    }

    customers.forEach((c) => {
        const item = document.createElement("div");
        item.className = "suggest-item";

        const phones = [c.Phone, c.Phone2].filter(Boolean).join(" / ");

        item.innerHTML = `
      <div class="si-title">${escapeHtml(c.Name || "")}</div>
      <div class="si-sub">${escapeHtml(phones)}</div>
    `;

        item.addEventListener("click", async () => {
            selectedCustomer = c;

            customerSearch.value = c.Name || "";
            selectedCustomerInfo.textContent = `Selected: ${c.ID} • ${c.Name || ""}`;

            hideSuggest(customerSuggest);

            await loadPoliciesForCustomer(c.ID);

            policySelect.disabled = false;
            saveDocBtn.disabled = false;
        });

        customerSuggest.appendChild(item);
    });

    customerSuggest.classList.remove("hidden");
}

async function fetchCustomers(q) {
    try {
        const url = `${window.ROUTES.customersSearch}?q=${encodeURIComponent(q)}`;

        console.log("Searching customers:", q);
        console.log("URL:", url);

        const res = await fetch(url, {
            headers: { Accept: "application/json" },
        });
        const json = await res.json();

        console.log("Search response:", json);

        return json.ok ? json.customers || [] : [];
    } catch (e) {
        console.error("fetchCustomers error:", e);
        return [];
    }
}

async function loadPoliciesForCustomer(customerId) {
    try {
        const url = `${window.ROUTES.customerPoliciesBase}/${customerId}/policies`;
        const res = await fetch(url, {
            headers: { Accept: "application/json" },
        });
        const json = await res.json();

        policySelect.innerHTML = `<option value="">Policies...</option>`;
        selectedPolicyNumber = "";

        if (json.ok && Array.isArray(json.policies)) {
            json.policies.forEach((p) => {
                const opt = document.createElement("option");
                opt.value = p.pol_number;
                opt.textContent = p.pol_number;
                policySelect.appendChild(opt);
            });
        }
    } catch (e) {
        console.error(e);
        alert("Failed to load policies.");
    }
}

function hideSuggest(el) {
    el.classList.add("hidden");
    el.innerHTML = "";
}

// ---------------------------
// Save PDF (pdf-lib) + upload Laravel
// ---------------------------
async function savePDFToServer() {
    if (!pdfBytes || !templateDataGlobal) {
        alert("PDF is not loaded.");
        return;
    }
    if (!selectedCustomer) {
        alert("Select a customer first.");
        return;
    }

    // guardar cambios de inputs antes de construir
    saveInputChangesForCurrentPage();

    const pdfDocWithText = await PDFLib.PDFDocument.load(pdfBytes);
    const pages = pdfDocWithText.getPages();

    overlayData.forEach((overlay) => {
        const page = pages[overlay.page - 1];
        if (!page) return;

        const { height } = page.getSize();
        const inputValue = String(overlay.text || "").replace(/{{|}}/g, "");

        page.drawText(inputValue, {
            x: overlay.x,
            y: height - overlay.y - 20,
            size: 20,
            color: PDFLib.rgb(0, 0, 0),
        });
    });

    const modifiedPdfBytes = await pdfDocWithText.save();
    const blob = new Blob([modifiedPdfBytes], { type: "application/pdf" });

    const formData = new FormData();
    formData.append("template_id", templateSelect.value);
    formData.append("customer_id", selectedCustomer.ID);
    formData.append("customer_name", selectedCustomer.Name || "");
    formData.append("policy_number", selectedPolicyNumber || "");
    formData.append("pdf", blob, `document_${Date.now()}.pdf`);
    const phone = selectedCustomer?.Phone || selectedCustomer?.Phone2 || "";
    formData.append("customer_phone", phone);

    // type: define un número fijo o un select si quieres
    // por ahora lo dejo como 1 (ajústalo según tu lógica)
    formData.append("doc_type", 1);

    try {
        const res = await fetch(window.ROUTES.saveGenerated, {
            method: "POST",
            headers: { "X-CSRF-TOKEN": csrf },
            body: formData,
        });

        const json = await res.json();
        if (!json.ok) {
            alert("Failed to save PDF.");
            return;
        }

        alert("PDF saved!");
        // si quieres redirigir a documents:
        window.location.href = `${window.BASE_URL}/documents`;
    } catch (e) {
        console.error(e);
        alert("Failed to upload PDF.");
    }
}

// ---------------------------
// Panels + reset
// ---------------------------
function togglePanel(panel) {
    panel.classList.toggle("hidden");
}
function showPanel(panel) {
    panel.classList.remove("hidden");
}
function hidePanel(panel) {
    panel.classList.add("hidden");
}

function resetCustomerAndPolicies() {
    selectedCustomer = null;
    selectedPolicyNumber = "";

    customerSearch.value = "";
    selectedCustomerInfo.textContent = "No customer selected.";

    policySelect.innerHTML = `<option value="">Policies...</option>`;
    policySelect.disabled = true;

    saveDocBtn.disabled = true;

    hideSuggest(customerSuggest);
}

function escapeHtml(str) {
    return String(str).replace(
        /[&<>"']/g,
        (m) =>
            ({
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#039;",
            })[m],
    );
}
