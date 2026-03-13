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
        if (!overlay || overlay.page !== currentPageNumber) return;

        const placeholder = String(overlay.text || "")
            .replace(/{{|}}/g, "")
            .trim();

        // ✅ No tocar DocCDate@
        if (placeholder === "DocCDate@") return;

        overlay.text = `{{${inputField.value}}}`;
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
            const placeholder = overlay.text.replace(/{{|}}/g, "").trim();

            // Escala visual real del canvas (si cambia por CSS)
            const rect = canvas.getBoundingClientRect();
            const scaleX = rect.width / canvas.width;
            const scaleY = rect.height / canvas.height;

            inputOverlay.style.width = rect.width + "px";
            inputOverlay.style.height = rect.height + "px";

            let leftPx = overlay.x * scaleFactor * scaleX;
            let topPx = overlay.y * scaleFactor * scaleY;

            const OFFSET_Y = 2;
            topPx += OFFSET_Y;

            // Clamp (suave) para no salirse
            leftPx = Math.max(0, Math.min(leftPx, rect.width - 10));
            topPx = Math.max(0, Math.min(topPx, rect.height - 10));

            // ✅ 1) DocCDate@ -> texto fijo (NO input)
            if (placeholder === "DocCDate@") {
                const now = new Date();
                const yyyy = now.getFullYear();
                const mm = String(now.getMonth() + 1).padStart(2, "0");
                const dd = String(now.getDate()).padStart(2, "0");
                const dateText = `${yyyy}-${mm}-${dd}`;

                const fixed = document.createElement("div");
                fixed.textContent = dateText;

                fixed.style.position = "absolute";
                fixed.style.left = `${leftPx}px`;
                fixed.style.top = `${topPx}px`;

                fixed.style.pointerEvents = "none"; // ✅ no editable
                fixed.style.background = "transparent";
                fixed.style.border = "1px solid rgba(255,255,255,.22)";
                fixed.style.borderRadius = "6px";
                fixed.style.padding = "4px 6px";
                fixed.style.fontSize = "14px";
                fixed.style.whiteSpace = "nowrap";

                inputOverlay.appendChild(fixed);
                return; // ✅ importante: no crear input
            }

            // ✅ 2) resto de placeholders -> input normal
            const inputField = document.createElement("input");
            inputField.type = "text";
            inputField.value = placeholder;

            inputField.style.position = "absolute";
            inputField.style.left = `${leftPx}px`;
            inputField.style.top = `${topPx}px`;

            inputField.dataset.overlayIndex = String(index);
            inputOverlay.appendChild(inputField);
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

function getBitsLabel() {
    return /WOW64|Win64|x64|amd64/i.test(navigator.userAgent)
        ? " (64 Bits)"
        : " (32 Bits)";
}

function getAgentDeviceInfo() {
    let gpuInfo = "";
    try {
        gpuInfo =
            typeof getVideoCardInfo === "function"
                ? JSON.stringify(getVideoCardInfo())
                : "";
    } catch (e) {
        gpuInfo = "";
    }

    const browserName =
        typeof browser !== "undefined"
            ? browser
            : window.browserInfo?.browser || "";

    const osName =
        `${window.browserInfo?.os || ""} ${window.browserInfo?.osVersion || ""}${getBitsLabel()}`.trim();

    return {
        browser_agent: browserName || "",
        os_agent: osName || "",
        dName_agent: `${(window.navigator.userAgent || "").toLowerCase()}${gpuInfo}`,
        device_agent: window.browserInfo?.mobile
            ? "Mobile Device"
            : "Desktop Device",
    };
}

function getAgentCoordinates() {
    return new Promise((resolve) => {
        if (!navigator.geolocation) {
            resolve("");
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                resolve(
                    `${position.coords.latitude},${position.coords.longitude}`,
                );
            },
            () => resolve(""),
            {
                enableHighAccuracy: true,
                timeout: 8000,
                maximumAge: 0,
            },
        );
    });
}

async function getIpLocationInfo() {
    const out = {
        ip: "",
        city: "",
        country: "",
        region: "",
        coords: "",
    };

    try {
        const res = await fetch("https://ipinfo.io/json?token=TU_TOKEN_AQUI");
        const json = await res.json();

        out.ip = json.ip || "";
        out.city = json.city || "";
        out.country = json.country || "";
        out.region = json.region || "";
        out.coords = json.loc || "";
    } catch (e) {
        console.warn("No se pudo obtener ipinfo:", e);
    }

    try {
        if (navigator.geolocation) {
            const coords = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve(
                            `${position.coords.latitude},${position.coords.longitude}`,
                        );
                    },
                    reject,
                    {
                        enableHighAccuracy: true,
                        timeout: 8000,
                        maximumAge: 0,
                    },
                );
            });

            if (coords) {
                out.coords = coords;
            }
        }
    } catch (e) {
        console.warn("No se dieron permisos de ubicación:", e);
    }

    return out;
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

        const placeholder = String(overlay.text || "")
            .replace(/{{|}}/g, "")
            .trim();

        // ✅ DocCDate@ -> fecha actual automática
        let textToDraw = placeholder;

        if (placeholder === "DocCDate@") {
            const now = new Date();
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, "0");
            const dd = String(now.getDate()).padStart(2, "0");
            textToDraw = `${yyyy}-${mm}-${dd}`;
        }

        page.drawText(textToDraw, {
            x: overlay.x,
            y: height - overlay.y - 20,
            size: 20,
            color: PDFLib.rgb(0, 0, 0),
        });
    });

    const modifiedPdfBytes = await pdfDocWithText.save({
        useObjectStreams: false,
    });
    const blob = new Blob([modifiedPdfBytes], { type: "application/pdf" });

    const formData = new FormData();
    formData.append("template_id", templateSelect.value);
    formData.append("customer_id", selectedCustomer.ID);
    formData.append("customer_name", selectedCustomer.Name || "");
    formData.append("policy_number", selectedPolicyNumber || "");
    formData.append("pdf", blob, `document_${Date.now()}.pdf`);

    const phone = selectedCustomer?.Phone || selectedCustomer?.Phone2 || "";
    formData.append("customer_phone", phone);

    const email =
        (selectedCustomer?.Email1 && String(selectedCustomer.Email1).trim()) ||
        (selectedCustomer?.Email2 && String(selectedCustomer.Email2).trim()) ||
        "";

    formData.append("customer_email", email);

    const agentInfo = getAgentDeviceInfo();
    const geoInfo = await getIpLocationInfo();
    const agentCoordinates = geoInfo.coords || (await getAgentCoordinates());

    formData.append("browser_agent", agentInfo.browser_agent);
    formData.append("os_agent", agentInfo.os_agent);
    formData.append("dName_agent", agentInfo.dName_agent);
    formData.append("device_agent", agentInfo.device_agent);

    formData.append("ip_agent", geoInfo.ip || "");
    formData.append("city_agent", geoInfo.city || "");
    formData.append("country_agent", geoInfo.country || "");
    formData.append("agent_region", geoInfo.region || "");
    formData.append("coordinates_agent", agentCoordinates || "");

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
