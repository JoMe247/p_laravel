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
const OVERLAY_FONT_SIZE = 20;
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
        resetDocDTimeState();

        initDocDTimeUI();
        ensureDocDTimeOverlay(); // primero insertamos overlay
        scheduleRender(currentPageNumber); // y luego render 1 sola vez
        updatePageInfo(); // vuelve a renderizar para que se vea el DocDTime@
        updateUndoRedoButtons();
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
            ctx.save();
            ctx.font = `${OVERLAY_FONT_SIZE}px Arial`;
            ctx.fillStyle = "#cc1133";
            ctx.textBaseline = "top";
            ctx.fillText(
                overlay.text,
                overlay.x * scaleFactor,
                overlay.y * scaleFactor,
            );
            ctx.restore();
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
    ctx.save();
    ctx.font = `${OVERLAY_FONT_SIZE}px Arial`;
    ctx.fillStyle = "#cc1133";
    ctx.textBaseline = "top";
    ctx.fillText(text, x * scaleFactor, y * scaleFactor);
    ctx.restore();

    hasChanges = true;
    document.getElementById("saveButton").disabled = false;

    document.getElementById("overlayText").value = "";

    $(".box").animate({
        top: "0px",
        left: "0px",
    });

    document.getElementById("dragged-value").innerText = "Add Text";
    updateUndoRedoButtons();
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
            size: OVERLAY_FONT_SIZE,
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

function hasUserTextOverlays() {
    // "inputs de texto" = overlays que NO son locked (DocDTime@ es locked)
    return overlayTexts.some(o => !o.locked);
}

function updateUndoRedoButtons() {
    const undoBtn = document.getElementById("undoButton");
    const redoBtn = document.getElementById("redoButton");
    if (!undoBtn || !redoBtn) return;

    // Si no hay textos del usuario, ambos deshabilitados
    if (!hasUserTextOverlays()) {
        undoBtn.disabled = true;
        redoBtn.disabled = true;
        return;
    }

    // Si sí hay textos, habilitar según stacks
    undoBtn.disabled = undoStack.length === 0;
    redoBtn.disabled = redoStack.length === 0;
}

function resetCanvas() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    overlayTexts = [];
    undoStack.length = 0;
    redoStack.length = 0;
    currentPageNumber = 1;

    hasChanges = false;
    document.getElementById("saveButton").disabled = true;
    document.getElementById("undoButton").disabled = true;
    document.getElementById("redoButton").disabled = true;

    document.getElementById("xCoordinate").value = "";
    document.getElementById("yCoordinate").value = "";
    document.getElementById("dragged-value").innerText = "Add Text";
}

function undo() {
    if (undoStack.length > 0) {
        redoStack.push(overlayTexts.map((o) => ({ ...o })));
        overlayTexts = undoStack.pop();
        scheduleRender(currentPageNumber);
        ensureDocDTimeOverlay();

        hasChanges = overlayTexts.length > 0;
        document.getElementById("saveButton").disabled = !hasChanges;
        updateUndoRedoButtons();
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
        updateUndoRedoButtons();
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
    const padX = 10;
    const padTop = 18;
    const padBottom = 18; // ✅ súbelo del borde inferior (ajusta 25–40 si quieres)

    ctx.save();
    ctx.font = `${OVERLAY_FONT_SIZE}px Arial`;
    const textWidthPx = ctx.measureText(DOCDTIME_TEXT).width;
    ctx.restore();

    const textWidth = textWidthPx / scaleFactor;
    const textHeight = OVERLAY_FONT_SIZE / scaleFactor;

    let x = padX;
    let y = padTop;

    switch (corner) {
        case "TL":
            x = padX;
            y = padTop;
            break;

        case "TR":
            x = Math.max(padX, pageW - textWidth - padX);
            y = padTop;
            break;

        case "BL":
            x = padX;
            y = Math.max(padTop, pageH - textHeight - padBottom);
            break;

        case "BR":
        default:
            x = Math.max(padX, pageW - textWidth - padX);
            y = Math.max(padTop, pageH - textHeight - padBottom);
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

    hasChanges = hasUserTextOverlays();
    document.getElementById("saveButton").disabled = !hasChanges;
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
    // fuerza que el click no deje el mismo checkbox apagado
    const idMap = { TL: "dtimeTL", TR: "dtimeTR", BL: "dtimeBL", BR: "dtimeBR" };
    const clicked = document.getElementById(idMap[corner]);
    if (clicked) clicked.checked = true;
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

function resetDocDTimeState() {
    // Default esquina
    docDTimeCorner = "BR";

    // Limpia DocDTime overlays previos (de cualquier PDF anterior)
    overlayTexts = overlayTexts.filter(o => !(o.id && o.id.startsWith(DOCDTIME_ID_PREFIX)));

    // Resetea UI (solo BR marcado)
    const tl = document.getElementById("dtimeTL");
    const tr = document.getElementById("dtimeTR");
    const bl = document.getElementById("dtimeBL");
    const br = document.getElementById("dtimeBR");

    if (tl) tl.checked = false;
    if (tr) tr.checked = false;
    if (bl) bl.checked = false;
    if (br) br.checked = true;
}