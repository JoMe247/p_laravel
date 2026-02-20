let pdfDoc = null;
let pdfBytes = null;

const canvas = document.getElementById('pdfCanvas');
const ctx = canvas.getContext('2d');
const inputOverlay = document.getElementById('inputOverlay');

const scaleFactor = 0.85;
let currentPageNumber = 1;
let totalPages = 0;
let overlayData = [];
let templateDataGlobal = null;

let selectedCustomer = null; // {ID, Name, Phone, Phone2, Email1, Email2}
let selectedPolicyNumber = '';

const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// UI
const templateSelect = document.getElementById('templateSelect');
const customerBtn = document.getElementById('customerBtn');
const policiesBtn = document.getElementById('policiesBtn');
const saveDocBtn = document.getElementById('saveDocBtn');

const customerPanel = document.getElementById('customerPanel');
const policiesPanel = document.getElementById('policiesPanel');

const custName = document.getElementById('custName');
const custPhone = document.getElementById('custPhone');
const custEmail = document.getElementById('custEmail');

const nameSuggest = document.getElementById('nameSuggest');
const phoneSuggest = document.getElementById('phoneSuggest');
const emailSuggest = document.getElementById('emailSuggest');

const selectedCustomerInfo = document.getElementById('selectedCustomerInfo');
const policySelect = document.getElementById('policySelect');

const viewerControls = document.getElementById('viewerControls');
const prevPageBtn = document.getElementById('prevPage');
const nextPageBtn = document.getElementById('nextPage');
const currentPageEl = document.getElementById('currentPage');
const totalPagesEl = document.getElementById('totalPages');

// ---------------------------
// Init
// ---------------------------
document.addEventListener('DOMContentLoaded', async () => {
    await loadTemplateOptions();

    templateSelect.addEventListener('change', async () => {
        const id = templateSelect.value;
        resetCustomerAndPolicies();

        if (!id) {
            clearViewer();
            customerBtn.disabled = true;
            saveDocBtn.disabled = true;
            return;
        }

        customerBtn.disabled = false;

        const data = await fetchTemplateData(id);
        if (!data) return;

        templateDataGlobal = data;
        await loadPDF(data);

        // Si template cargó, ya puedes guardar después (pero exigimos customer para guardar)
        saveDocBtn.disabled = true;
    });

    customerBtn.addEventListener('click', () => {
        if (customerBtn.disabled) return;
        togglePanel(customerPanel);
        hidePanel(policiesPanel);
    });

    policiesBtn.addEventListener('click', () => {
        if (policiesBtn.disabled) return;
        togglePanel(policiesPanel);
        hidePanel(customerPanel);
    });

    policySelect.addEventListener('change', () => {
        selectedPolicyNumber = policySelect.value || '';
    });

    saveDocBtn.addEventListener('click', savePDFToServer);

    // Search suggestions (simple debounce)
    attachSuggest(custName, nameSuggest, 'name');
    attachSuggest(custPhone, phoneSuggest, 'phone');
    attachSuggest(custEmail, emailSuggest, 'email');

    // Pagination
    prevPageBtn.addEventListener('click', () => {
        if (currentPageNumber > 1) {
            saveInputChangesForCurrentPage();
            currentPageNumber--;
            renderPage(currentPageNumber, overlayData);
        }
    });

    nextPageBtn.addEventListener('click', () => {
        if (currentPageNumber < totalPages) {
            saveInputChangesForCurrentPage();
            currentPageNumber++;
            renderPage(currentPageNumber, overlayData);
        }
    });
});

// ---------------------------
// Templates
// ---------------------------
async function loadTemplateOptions() {
    try {
        const res = await fetch(window.ROUTES.templatesOptions, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.ok) return;

        json.templates.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.template_name;
            templateSelect.appendChild(opt);
        });
    } catch (e) {
        console.error(e);
        alert('Failed to load templates.');
    }
}

async function fetchTemplateData(id) {
    try {
        const res = await fetch(`${window.ROUTES.templateDataBase}/${id}`, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        if (!json.ok) {
            alert(json.error || 'Template not found.');
            return null;
        }
        return json;
    } catch (e) {
        console.error(e);
        alert('Failed to load template data.');
        return null;
    }
}

// ---------------------------
// PDF Viewer + Inputs overlay
// ---------------------------
async function loadPDF(templateData) {
    const pdfjsLib = window['pdfjs-dist/build/pdf'];

    overlayData = Array.isArray(templateData.overlay_data) ? templateData.overlay_data : [];

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
        if (!pdfResponse.ok) throw new Error('Failed to fetch PDF.');

        pdfBytes = await pdfResponse.arrayBuffer();
        pdfDoc = await pdfjsLib.getDocument(pdfBytes).promise;

        totalPages = pdfDoc.numPages;
        totalPagesEl.textContent = totalPages;
        currentPageNumber = 1;

        viewerControls.classList.toggle('hidden', totalPages <= 1);

        await renderPage(currentPageNumber, overlayData);
    } catch (err) {
        console.error(err);
        alert('Failed to load PDF preview. Revisa el path original_file_path/original_original.');
        clearViewer();
    }
}

function saveInputChangesForCurrentPage() {
    const inputFields = inputOverlay.querySelectorAll('input');
    inputFields.forEach(inputField => {
        const placeholder = inputField.dataset.placeholder;
        const overlay = overlayData.find(o => o.text === placeholder && o.page === currentPageNumber);
        if (overlay) {
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

    inputOverlay.innerHTML = '';

    overlayDataParam.forEach(overlay => {
        if (overlay.page === pageNumber && typeof overlay.text === 'string' && overlay.text.includes('{{') && overlay.text.includes('}}')) {
            const inputField = document.createElement('input');
            inputField.type = 'text';
            inputField.value = overlay.text.replace(/{{|}}/g, '');

            inputField.style.position = 'absolute';
            inputField.style.left = `${(overlay.x * scaleFactor) + 10}px`;
            inputField.style.top = `${(overlay.y * scaleFactor) + 2}px`;
            inputField.dataset.placeholder = overlay.text;

            inputOverlay.appendChild(inputField);
        }
    });
}

function clearViewer() {
    pdfDoc = null;
    pdfBytes = null;
    overlayData = [];
    templateDataGlobal = null;
    canvas.width = 1;
    canvas.height = 1;
    inputOverlay.innerHTML = '';
    viewerControls.classList.add('hidden');
}

// ---------------------------
// Customer suggestions + selection
// ---------------------------
function attachSuggest(inputEl, suggestEl, mode) {
    let t = null;

    inputEl.addEventListener('input', () => {
        const val = inputEl.value.trim();
        clearTimeout(t);

        if (val.length < 2) {
            hideSuggest(suggestEl);
            return;
        }

        t = setTimeout(async () => {
            const customers = await fetchCustomers(val);
            renderSuggest(customers, suggestEl, mode);
        }, 250);
    });

    document.addEventListener('click', (e) => {
        if (!suggestEl.contains(e.target) && e.target !== inputEl) hideSuggest(suggestEl);
    });
}

async function fetchCustomers(q) {
    try {
        const url = `${window.ROUTES.customersSearch}?q=${encodeURIComponent(q)}`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();
        return json.ok ? (json.customers || []) : [];
    } catch (e) {
        console.error(e);
        return [];
    }
}

function renderSuggest(customers, suggestEl, mode) {
    suggestEl.innerHTML = '';
    if (!customers.length) {
        hideSuggest(suggestEl);
        return;
    }

    customers.forEach(c => {
        const item = document.createElement('div');
        item.className = 'suggest-item';

        const phones = [c.Phone, c.Phone2].filter(Boolean).join(' / ');
        const emails = [c.Email1, c.Email2].filter(Boolean).join(' / ');

        item.innerHTML = `
      <div class="si-title">${escapeHtml(c.Name || '')}</div>
      <div class="si-sub">${escapeHtml(phones)} ${emails ? ' • ' + escapeHtml(emails) : ''}</div>
    `;

        item.addEventListener('click', async () => {
            selectCustomer(c);
            hideSuggest(suggestEl);

            // Autocompleta campos base
            custName.value = c.Name || '';
            custPhone.value = c.Phone || c.Phone2 || '';
            custEmail.value = c.Email1 || c.Email2 || '';

            // Cargar policies
            await loadPoliciesForCustomer(c.ID);
        });

        suggestEl.appendChild(item);
    });

    suggestEl.classList.remove('hidden');
}

function selectCustomer(c) {
    selectedCustomer = c;
    selectedCustomerInfo.textContent = `Selected: ${c.ID} • ${c.Name || ''}`;
    policiesBtn.disabled = false;
    saveDocBtn.disabled = false; // ya hay template y customer
}

async function loadPoliciesForCustomer(customerId) {
    try {
        const url = `${window.ROUTES.customerPoliciesBase}/${customerId}/policies`;
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
        const json = await res.json();

        policySelect.innerHTML = `<option value="">Select policy...</option>`;

        if (json.ok && Array.isArray(json.policies)) {
            json.policies.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.pol_number;
                opt.textContent = p.pol_number;
                policySelect.appendChild(opt);
            });
        }

        // Abre panel automáticamente
        showPanel(policiesPanel);
        hidePanel(customerPanel);
    } catch (e) {
        console.error(e);
        alert('Failed to load policies.');
    }
}

function hideSuggest(el) { el.classList.add('hidden'); el.innerHTML = ''; }

// ---------------------------
// Save PDF (pdf-lib) + upload Laravel
// ---------------------------
async function savePDFToServer() {
    if (!pdfBytes || !templateDataGlobal) {
        alert('PDF is not loaded.');
        return;
    }
    if (!selectedCustomer) {
        alert('Select a customer first.');
        return;
    }

    // guardar cambios de inputs antes de construir
    saveInputChangesForCurrentPage();

    const pdfDocWithText = await PDFLib.PDFDocument.load(pdfBytes);
    const pages = pdfDocWithText.getPages();

    overlayData.forEach(overlay => {
        const page = pages[overlay.page - 1];
        if (!page) return;

        const { height } = page.getSize();
        const inputValue = String(overlay.text || '').replace(/{{|}}/g, '');

        page.drawText(inputValue, {
            x: overlay.x,
            y: height - overlay.y - 20,
            size: 20,
            color: PDFLib.rgb(0, 0, 0)
        });
    });

    const modifiedPdfBytes = await pdfDocWithText.save();
    const blob = new Blob([modifiedPdfBytes], { type: 'application/pdf' });

    const formData = new FormData();
    formData.append('template_id', templateSelect.value);
    formData.append('customer_id', selectedCustomer.ID);
    formData.append('customer_name', selectedCustomer.Name || '');
    formData.append('policy_number', selectedPolicyNumber || '');
    formData.append('pdf', blob, `document_${Date.now()}.pdf`);
    formData.append('customer_phone', custPhone.value || '');
    formData.append('customer_email', custEmail.value || '');

    // type: define un número fijo o un select si quieres
    // por ahora lo dejo como 1 (ajústalo según tu lógica)
    formData.append('doc_type', 1);

    try {
        const res = await fetch(window.ROUTES.saveGenerated, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            body: formData
        });

        const json = await res.json();
        if (!json.ok) {
            alert('Failed to save PDF.');
            return;
        }

        alert('PDF saved!');
        // si quieres redirigir a documents:
        window.location.href = `${window.BASE_URL}/documents`;
    } catch (e) {
        console.error(e);
        alert('Failed to upload PDF.');
    }
}

// ---------------------------
// Panels + reset
// ---------------------------
function togglePanel(panel) {
    panel.classList.toggle('hidden');
}
function showPanel(panel) { panel.classList.remove('hidden'); }
function hidePanel(panel) { panel.classList.add('hidden'); }

function resetCustomerAndPolicies() {
    selectedCustomer = null;
    selectedPolicyNumber = '';
    custName.value = '';
    custPhone.value = '';
    custEmail.value = '';
    selectedCustomerInfo.textContent = 'No customer selected.';

    policiesBtn.disabled = true;
    saveDocBtn.disabled = true;

    policySelect.innerHTML = `<option value="">Select policy...</option>`;
    hidePanel(customerPanel);
    hidePanel(policiesPanel);
}

function escapeHtml(str) {
    return String(str).replace(/[&<>"']/g, (m) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[m]));
}