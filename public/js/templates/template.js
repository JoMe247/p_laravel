document.getElementById("upload").addEventListener("change", handleFileSelect);

let pdfDoc = null;
let pdfBytes = null;
let canvas = document.getElementById("pdfCanvas");
let ctx = canvas.getContext("2d");
let currentPageNumber = 1;
let totalPages = 1;
let overlayTexts = [];
let hasChanges = false;
const undoStack = [];
const redoStack = [];
const scaleFactor = 0.85;
const DOCDTIME_TEXT = "{{DocDTime@}}";
const DOCDTIME_ID_PREFIX = "__DOCDTIME__P";
function docDTimeIdForPage(pageNum) {
    return `${DOCDTIME_ID_PREFIX}${pageNum}`;
}
let docDTimeCorner = "BR";
let renderQueue = Promise.resolve();

function scheduleRender(pageNumber) {
    renderQueue = renderQueue
        .then(() => renderPage(pageNumber))
        .catch(() => renderPage(pageNumber)); // por si una cancelación rechazó
}

let currentRenderTask = null;

// Disable the Save button initially
document.getElementById("saveButton").disabled = true;

async function handleFileSelect(event) {
    let file = event.target.files[0];
    if (!file || file.type !== "application/pdf") {
        alert("Please select a PDF file.");
        return;
    }

    // Reset the canvas and overlayTexts when a new file is uploaded
    resetCanvas();

    let fileReader = new FileReader();
    fileReader.onload = async function () {
        pdfBytes = new Uint8Array(this.result);
        pdfDoc = await PDFLib.PDFDocument.load(pdfBytes);

        const pdfjsLib = window["pdfjs-dist/build/pdf"];
        const pdf = await pdfjsLib.getDocument({ data: pdfBytes }).promise;
        totalPages = pdf.numPages;

        initDocDTimeUI();
        ensureDocDTimeOverlay(); // primero insertamos overlay
        scheduleRender(currentPageNumber); // y luego render 1 sola vez
        updatePageInfo(); // vuelve a renderizar para que se vea el DocDTime@
    };
    fileReader.readAsArrayBuffer(file);
}

async function renderPage(pageNumber) {
    // Cancel any ongoing render task
    if (currentRenderTask) {
        currentRenderTask.cancel();
    }

    const pdfjsLib = window["pdfjs-dist/build/pdf"];
    const pdf = await pdfjsLib.getDocument({ data: pdfBytes }).promise;
    const page = await pdf.getPage(pageNumber);
    const viewport = page.getViewport({ scale: scaleFactor });

    canvas.width = viewport.width;
    canvas.height = viewport.height;

    const renderContext = {
        canvasContext: ctx,
        viewport: viewport,
    };

    currentRenderTask = page.render(renderContext);

    try {
        await currentRenderTask.promise;
    } catch (err) {
        // Si se canceló el render, pdf.js lanza error: hay que ignorarlo
        if (err?.name !== "RenderingCancelledException") {
            console.error(err);
        }
        return;
    } finally {
        currentRenderTask = null;
    }

    // Redraw overlay texts for current page
    overlayTexts.forEach((overlay) => {
        if (overlay.page === currentPageNumber) {
            ctx.font = "20px Arial";
            ctx.fillStyle = "#cc1133";
            ctx.fillText(
                overlay.text,
                overlay.x * scaleFactor,
                overlay.y * scaleFactor + 15,
            );
        }
    });

    setTimeout(function () {
        canvas.style.display = "";
        document.getElementById("draggable-area").style.display = "";
        document.getElementById("draggable-area").style.width =
            canvas.width + "px";
        document.getElementById("draggable-area").style.height =
            canvas.height + "px";
    }, 10);
}

function next() {
    if (currentPageNumber >= totalPages) return;
    currentPageNumber++;
    scheduleRender(currentPageNumber);
    updatePageInfo();
}

function back() {
    if (currentPageNumber <= 1) return;
    currentPageNumber--;
    scheduleRender(currentPageNumber);
    updatePageInfo();
}

document
    .getElementById("addTextButton")
    .addEventListener("click", addTextOverlay);

function addTextOverlay() {
    let text = "{{" + document.getElementById("dragged-value").innerText + "}}";
    let x = parseFloat(document.getElementById("xCoordinate").value);
    let y = parseFloat(document.getElementById("yCoordinate").value);

    if (text === "") {
        alert("Please enter some text.");
        return;
    }
    if (isNaN(x) || isNaN(y)) {
        alert("Please drag the Text box over the area.");
        return;
    }

    undoStack.push(overlayTexts.map((o) => ({ ...o })));
    redoStack.length = 0;

    overlayTexts.push({ text, x, y, page: currentPageNumber });

    ctx.font = "20px Arial";
    ctx.fillStyle = "#cc1133";
    ctx.fillText(text, x * scaleFactor, y * scaleFactor + 15);

    hasChanges = true;
    document.getElementById("saveButton").disabled = false;

    document.getElementById("overlayText").value = "";

    $(".box").animate({
        top: "0px",
        left: "0px",
    });

    document.getElementById("dragged-value").innerText = "Add Text";
}

document.getElementById("saveButton").addEventListener("click", savePDF);

async function savePDF() {
    if (!hasChanges) {
        alert("No changes to save!");
        return;
    }

    const documentName = prompt(
        "Please enter a name for the template:",
        "MyTemplate",
    );

    if (!documentName) {
        alert("You must enter a name for the template to save it.");
        return;
    }

    const originalPdfBytes = pdfBytes;
    const pdfDocWithText = await PDFLib.PDFDocument.load(originalPdfBytes);

    overlayTexts.forEach((overlay) => {
        const pages = pdfDocWithText.getPages();
        const page = pages[overlay.page - 1];
        const { height } = page.getSize();

        page.drawText(overlay.text, {
            x: overlay.x,
            y: height - overlay.y - 20,
            size: 20,
            color: PDFLib.rgb(1, 0, 0),
        });
    });

    const modifiedPdfBytes = await pdfDocWithText.save();
    const overlayData = JSON.stringify(overlayTexts);

    const formData = new FormData();
    formData.append(
        "pdf",
        new Blob([originalPdfBytes], { type: "application/pdf" }),
        "original.pdf",
    );
    formData.append(
        "pdfModified",
        new Blob([modifiedPdfBytes], { type: "application/pdf" }),
        "modified.pdf",
    );
    formData.append("overlayData", overlayData);
    formData.append("templateName", documentName);

    // ✅ Laravel endpoint
    fetch(window.TEMPLATE_SAVE_URL, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": window.CSRF_TOKEN,
        },
        body: formData,
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.ok) {
                // Redirige automáticamente a Documents
                window.location.href = window.DOCUMENTS_URL;
            } else {
                alert("Error saving template.");
            }
        })

        .catch((err) => {
            console.error(err);
            alert("Failed to save PDF and coordinates on the server.");
        });
}

function updatePageInfo() {
    document.getElementById("counter").textContent = currentPageNumber;
    document.getElementById("total-pages").textContent = totalPages;

    if (totalPages >= 2) {
        document.getElementById("backPage").disabled = false;
        document.getElementById("nextPage").disabled = false;
    } else {
        document.getElementById("backPage").disabled = true;
        document.getElementById("nextPage").disabled = true;
    }
}

function resetCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    overlayTexts = [];
    undoStack.length = 0;
    redoStack.length = 0;
    currentPageNumber = 1;

    hasChanges = false;
    document.getElementById("saveButton").disabled = true;

    document.getElementById("xCoordinate").value = "";
    document.getElementById("yCoordinate").value = "";
    document.getElementById("dragged-value").innerText = "Add Text";
}

function undo() {
    if (undoStack.length > 0) {
        rundoStack.push(overlayTexts.map((o) => ({ ...o })));
        overlayTexts = undoStack.pop();
        scheduleRender(currentPageNumber);
        ensureDocDTimeOverlay();

        hasChanges = overlayTexts.length > 0;
        document.getElementById("saveButton").disabled = !hasChanges;
    }
}

function redo() {
    if (redoStack.length > 0) {
        undoStack.push(overlayTexts.map((o) => ({ ...o })));
        overlayTexts = redoStack.pop();
        scheduleRender(currentPageNumber);
        ensureDocDTimeOverlay();

        hasChanges = overlayTexts.length > 0;
        document.getElementById("saveButton").disabled = !hasChanges;
    }
}

$(function () {
    $(".box").draggable({
        containment: ".container-drag",
        cursor: "crosshair",
        drag: function (e, ui) {
            $("#yCoordinate").val(ui.position.top / scaleFactor);
            $("#xCoordinate").val(ui.position.left / scaleFactor);
        },
    });
});

function docNameActive() {
    document
        .getElementById("dragged-value")
        .setAttribute("data-icon", "docname");
    document.getElementById("dragged-value").innerHTML = "DocName@";
    document
        .getElementById("dragged-value")
        .setAttribute("contenteditable", "false");
}

function textActive() {
    document.getElementById("dragged-value").setAttribute("data-icon", "text");
    document.getElementById("dragged-value").innerHTML = "Add Text";
    document
        .getElementById("dragged-value")
        .setAttribute("contenteditable", "true");
    window
        .getSelection()
        .selectAllChildren(document.getElementById("dragged-value"));
}

function penActive() {
    document
        .getElementById("dragged-value")
        .setAttribute("data-icon", "signature");
    document.getElementById("dragged-value").innerHTML = "DocSign@";
    document
        .getElementById("dragged-value")
        .setAttribute("contenteditable", "false");
}

function calendarActive() {
    document
        .getElementById("dragged-value")
        .setAttribute("data-icon", "calendar");
    document.getElementById("dragged-value").innerHTML = "DocCDate@";
    document
        .getElementById("dragged-value")
        .setAttribute("contenteditable", "false");
}

function getPageSize(pageNumber) {
    if (!pdfDoc) return null;
    const pages = pdfDoc.getPages();
    const page = pages[pageNumber - 1];
    if (!page) return null;
    return page.getSize(); // { width, height }
}

function measurePdfTextWidth(text, fontSize = 20) {
    // pdf-lib usa su propia métrica; Helvetica está disponible por defecto.
    // Si alguna vez embebes otra fuente, aquí habría que ajustar.
    try {
        return PDFLib.StandardFonts.Helvetica ? null : null;
    } catch (e) {
        return null;
    }
}

// Calcula coordenadas en “tu sistema” (y desde arriba)
function getCornerXY(corner, pageW, pageH) {
    // padding real al borde (en coordenadas PDF)
    const pad = 8;

    // Medimos el texto con canvas en pixeles y lo convertimos a "PDF units"
    // Porque en render dibujas en canvas escalado: x*scaleFactor.
    // Medimos con la misma fuente/tamaño que usas al renderizar.
    ctx.save();
    ctx.font = "20px Arial";
    const textWidthPx = ctx.measureText(DOCDTIME_TEXT).width; // px en canvas
    ctx.restore();

    // Convertir a unidades PDF (unidades base antes de scale)
    const textWidth = textWidthPx / scaleFactor;

    // Altura aproximada (20px font + un pequeño ajuste) en unidades PDF
    const textHeight = 20 / scaleFactor;

    // NOTA: tu overlay.y es "desde arriba" (top-based). 0 = arriba.
    // Para TOP: y = pad
    // Para BOTTOM: y = pageH - textHeight - pad
    let x = pad;
    let y = pad;

    switch (corner) {
        case "TL":
            x = pad;
            y = pad;
            break;

        case "TR":
            x = Math.max(pad, pageW - textWidth - pad);
            y = pad;
            break;

        case "BL":
            x = pad;
            y = Math.max(pad, pageH - textHeight - pad);
            break;

        case "BR":
        default:
            x = Math.max(pad, pageW - textWidth - pad);
            y = Math.max(pad, pageH - textHeight - pad);
            break;
    }

    return { x, y };
}

function ensureDocDTimeOverlay() {
    if (!pdfDoc || !pdfBytes) return;
    if (!totalPages || totalPages < 1) return;

    for (let p = 1; p <= totalPages; p++) {
        const id = docDTimeIdForPage(p);

        // si ya existe el de esa página, skip
        let existing = overlayTexts.find((o) => o.id === id);
        if (existing) continue;

        const size = getPageSize(p);
        if (!size) continue;

        const { x, y } = getCornerXY(docDTimeCorner, size.width, size.height);

        overlayTexts.push({
            id,
            text: DOCDTIME_TEXT,
            x,
            y,
            page: p,
            locked: true,
        });
    }

    hasChanges = true;
    document.getElementById("saveButton").disabled = false;
}

function moveDocDTimeToCorner(corner) {
    if (!pdfDoc || !totalPages) return;

    // undo (deep copy)
    undoStack.push(overlayTexts.map((o) => ({ ...o })));
    redoStack.length = 0;

    for (let p = 1; p <= totalPages; p++) {
        const size = getPageSize(p);
        if (!size) continue;

        const { x, y } = getCornerXY(corner, size.width, size.height);
        const id = docDTimeIdForPage(p);

        let existing = overlayTexts.find((o) => o.id === id);

        if (!existing) {
            overlayTexts.push({
                id,
                text: DOCDTIME_TEXT,
                x,
                y,
                page: p,
                locked: true,
            });
        } else {
            existing.x = x;
            existing.y = y;
            existing.page = p;
        }
    }

    hasChanges = true;
    document.getElementById("saveButton").disabled = false;

    scheduleRender(currentPageNumber);
}

function setDocDTimeCorner(corner) {
    docDTimeCorner = corner;

    // “checkbox exclusivo”: apaga los otros
    const map = { TL: "dtimeTL", TR: "dtimeTR", BL: "dtimeBL", BR: "dtimeBR" };
    Object.keys(map).forEach((k) => {
        const el = document.getElementById(map[k]);
        if (el) el.checked = k === corner;
    });

    moveDocDTimeToCorner(corner);
}

function initDocDTimeUI() {
    // por default BR
    const br = document.getElementById("dtimeBR");
    if (br) br.checked = true;
}
