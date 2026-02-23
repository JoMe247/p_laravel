document.getElementById('upload').addEventListener('change', handleFileSelect);

let pdfDoc = null;
let pdfBytes = null;
let canvas = document.getElementById('pdfCanvas');
let ctx = canvas.getContext('2d');
let currentPageNumber = 1;
let totalPages = 1;
let overlayTexts = [];
let hasChanges = false;
const undoStack = [];
const redoStack = [];
const scaleFactor = 0.85;

let currentRenderTask = null;

// Disable the Save button initially
document.getElementById('saveButton').disabled = true;

async function handleFileSelect(event) {
    let file = event.target.files[0];
    if (!file || file.type !== 'application/pdf') {
        alert('Please select a PDF file.');
        return;
    }

    // Reset the canvas and overlayTexts when a new file is uploaded
    resetCanvas();

    let fileReader = new FileReader();
    fileReader.onload = async function () {
        pdfBytes = new Uint8Array(this.result);
        pdfDoc = await PDFLib.PDFDocument.load(pdfBytes);

        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        const pdf = await pdfjsLib.getDocument({ data: pdfBytes }).promise;
        totalPages = pdf.numPages;

        renderPage(currentPageNumber);
        updatePageInfo();
    };
    fileReader.readAsArrayBuffer(file);
}

async function renderPage(pageNumber) {
    // Cancel any ongoing render task
    if (currentRenderTask) {
        currentRenderTask.cancel();
    }

    const pdfjsLib = window['pdfjs-dist/build/pdf'];
    const pdf = await pdfjsLib.getDocument({ data: pdfBytes }).promise;
    const page = await pdf.getPage(pageNumber);
    const viewport = page.getViewport({ scale: scaleFactor });

    canvas.width = viewport.width;
    canvas.height = viewport.height;

    const renderContext = {
        canvasContext: ctx,
        viewport: viewport
    };

    currentRenderTask = page.render(renderContext);
    await currentRenderTask.promise;
    currentRenderTask = null;

    // Redraw overlay texts for current page
    overlayTexts.forEach(overlay => {
        if (overlay.page === currentPageNumber) {
            ctx.font = '20px Arial';
            ctx.fillStyle = '#cc1133';
            ctx.fillText(overlay.text, overlay.x * scaleFactor, (overlay.y * scaleFactor) + 15);
        }
    });

    setTimeout(function () {
        canvas.style.display = "";
        document.getElementById("draggable-area").style.display = "";
        document.getElementById("draggable-area").style.width = canvas.width + "px";
        document.getElementById("draggable-area").style.height = canvas.height + "px";
    }, 10);
}

function next() {
    if (currentPageNumber >= totalPages) return;
    currentPageNumber++;
    renderPage(currentPageNumber);
    updatePageInfo();
}

function back() {
    if (currentPageNumber <= 1) return;
    currentPageNumber--;
    renderPage(currentPageNumber);
    updatePageInfo();
}

document.getElementById('addTextButton').addEventListener('click', addTextOverlay);

function addTextOverlay() {
    let text = "{{" + document.getElementById('dragged-value').innerText + "}}";
    let x = parseFloat(document.getElementById('xCoordinate').value);
    let y = parseFloat(document.getElementById('yCoordinate').value);

    if (text === '') {
        alert('Please enter some text.');
        return;
    }
    if (isNaN(x) || isNaN(y)) {
        alert('Please drag the Text box over the area.');
        return;
    }

    undoStack.push([...overlayTexts]);
    redoStack.length = 0;

    overlayTexts.push({ text, x, y, page: currentPageNumber });

    ctx.font = '20px Arial';
    ctx.fillStyle = '#cc1133';
    ctx.fillText(text, x * scaleFactor, (y * scaleFactor) + 15);

    hasChanges = true;
    document.getElementById('saveButton').disabled = false;

    document.getElementById('overlayText').value = '';

    $(".box").animate({
        top: "0px",
        left: "0px"
    });

    document.getElementById('dragged-value').innerText = "Add Text";
}

document.getElementById('saveButton').addEventListener('click', savePDF);

async function savePDF() {
    if (!hasChanges) {
        alert('No changes to save!');
        return;
    }

    const documentName = prompt("Please enter a name for the template:", "MyTemplate");

    if (!documentName) {
        alert("You must enter a name for the template to save it.");
        return;
    }

    const originalPdfBytes = pdfBytes;
    const pdfDocWithText = await PDFLib.PDFDocument.load(originalPdfBytes);

    overlayTexts.forEach(overlay => {
        const pages = pdfDocWithText.getPages();
        const page = pages[overlay.page - 1];
        const { height } = page.getSize();

        page.drawText(overlay.text, {
            x: overlay.x,
            y: height - overlay.y - 20,
            size: 20,
            color: PDFLib.rgb(1, 0, 0)
        });
    });

    const modifiedPdfBytes = await pdfDocWithText.save();
    const overlayData = JSON.stringify(overlayTexts);

    const formData = new FormData();
    formData.append('pdf', new Blob([originalPdfBytes], { type: 'application/pdf' }), 'original.pdf');
    formData.append('pdfModified', new Blob([modifiedPdfBytes], { type: 'application/pdf' }), 'modified.pdf');
    formData.append('overlayData', overlayData);
    formData.append('templateName', documentName);

    // ✅ Laravel endpoint
    fetch(window.TEMPLATE_SAVE_URL, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.CSRF_TOKEN
        },
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.ok) {
                // Redirige automáticamente a Documents
                window.location.href = window.DOCUMENTS_URL;
            } else {
                alert('Error saving template.');
            }
        })

        .catch(err => {
            console.error(err);
            alert('Failed to save PDF and coordinates on the server.');
        });
}

function updatePageInfo() {
    document.getElementById('counter').textContent = currentPageNumber;
    document.getElementById('total-pages').textContent = totalPages;

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
    document.getElementById('saveButton').disabled = true;

    document.getElementById('xCoordinate').value = '';
    document.getElementById('yCoordinate').value = '';
    document.getElementById('dragged-value').innerText = 'Add Text';
}

function undo() {
    if (undoStack.length > 0) {
        redoStack.push([...overlayTexts]);
        overlayTexts = undoStack.pop();
        renderPage(currentPageNumber);

        hasChanges = overlayTexts.length > 0;
        document.getElementById('saveButton').disabled = !hasChanges;
    }
}

function redo() {
    if (redoStack.length > 0) {
        undoStack.push([...overlayTexts]);
        overlayTexts = redoStack.pop();
        renderPage(currentPageNumber);

        hasChanges = overlayTexts.length > 0;
        document.getElementById('saveButton').disabled = !hasChanges;
    }
}

$(function () {
    $(".box").draggable({
        containment: ".container-drag",
        cursor: "crosshair",
        drag: function (e, ui) {
            $('#yCoordinate').val(ui.position.top / scaleFactor);
            $('#xCoordinate').val(ui.position.left / scaleFactor);
        }
    });
});

function docNameActive() {
    document.getElementById("dragged-value").setAttribute("data-icon", "docname");
    document.getElementById("dragged-value").innerHTML = "DocName@";
    document.getElementById("dragged-value").setAttribute("contenteditable", "false");
}

function textActive() {
    document.getElementById("dragged-value").setAttribute("data-icon", "text");
    document.getElementById("dragged-value").innerHTML = "Add Text";
    document.getElementById("dragged-value").setAttribute("contenteditable", "true");
    window.getSelection().selectAllChildren(document.getElementById("dragged-value"));
}

function penActive() {
    document.getElementById("dragged-value").setAttribute("data-icon", "signature");
    document.getElementById("dragged-value").innerHTML = "DocSign@";
    document.getElementById("dragged-value").setAttribute("contenteditable", "false");
}

function watchActive() {
    document.getElementById("dragged-value").setAttribute("data-icon", "watch");
    document.getElementById("dragged-value").innerHTML = "DocDTime@";
    document.getElementById("dragged-value").setAttribute("contenteditable", "false");
}

function calendarActive() {
    document.getElementById("dragged-value").setAttribute("data-icon", "calendar");
    document.getElementById("dragged-value").innerHTML = "DocCDate@";
    document.getElementById("dragged-value").setAttribute("contenteditable", "false");
}
