<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#ffde17">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <title>{{ $customerName }} - Sign</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ✅ Copia tus CSS a public/css --}}
    <link rel="stylesheet" href="{{ asset('css/signature-pad.css') }}">
    <link rel="stylesheet" href="{{ asset('css/firmar.css') }}">
</head>

<body onselectstart="return false">

    {{-- ✅ PREVIEW PDF (reemplaza la sección document-information) --}}
    <div class="pdf-preview-section">
        <div class="pdf-preview-wrap">
            <canvas id="pdfCanvas"></canvas>
        </div>

        <div class="pdf-meta-bar">
            <p>Total Pages: <span id="totalPages">0</span></p>
            <p>Current Page: <span id="currentPage">1</span></p>
        </div>

        <div class="pdf-nav-buttons">
            <button type="button" id="prevPage">Back P.</button>
            <button type="button" id="nextPage">Next P.</button>
        </div>
    </div>

    {{-- ✅ FIRMA --}}
    <div id="signature-pad" class="signature-pad">
        <div class="signature-pad--body">
            <canvas></canvas>
        </div>

        <div class="signature-pad--footer">
            <div class="signature-pad--actions">
                <div>
                    <button type="button" class="button clear" id="clear">
                        <p class="icon-trash">Clear</p>
                    </button>
                    <button type="button" class="button" id="undo">
                        <p class="icon-ccw">Undo</p>
                    </button>
                </div>

                <div style="display:block;margin-left:auto;margin-right:auto;">
                    <button type="button" class="button save" id="firmar">
                        <p class="icon-pencil">Sign</p>
                    </button>
                    <p id="sign-here" class="icon-pencil">SIGN HERE</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container-loading" id="ok-loading">
        <div class="spinner-box">
            <div class="circle-border2">
                <div class="circle-core2"></div>
            </div>
        </div>
    </div>

    {{-- ✅ Copia signature_pad.umd.js a public/js --}}
    <script src="{{ asset('js/signature_pad.umd.js') }}"></script>
    <script src="{{ asset('js/pdfjs/pdf.min.js') }}"></script>
    <script src="{{ asset('js/vendor/pdf-lib.min.js') }}"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdfjs/pdf.worker.min.js') }}";
    </script>

    <script>
        const pdfUrlBase = @json(route('sign.pdf', ['short' => $short, 'docId' => $docId]));
        const docsignOverlay = @json($docsignOverlay);

        function buildPdfUrl() {
            return pdfUrlBase + '?t=' + Date.now();
        }
        let pdfDoc = null;
        let currentPageNumber = 1;
        let totalPages = 0;
        let isRendering = false;
        let pendingPage = null;

        const pdfCanvas = document.getElementById("pdfCanvas");
        const pdfCtx = pdfCanvas.getContext("2d");

        const totalPagesEl = document.getElementById("totalPages");
        const currentPageEl = document.getElementById("currentPage");
        const prevPageBtn = document.getElementById("prevPage");
        const nextPageBtn = document.getElementById("nextPage");
        const pdfWrap = document.querySelector(".pdf-preview-wrap");
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const wrapper = document.getElementById("signature-pad");
        const canvas = wrapper.querySelector("canvas");


        const signHere = document.getElementById("sign-here");

        prevPageBtn.disabled = true;
        nextPageBtn.disabled = true;

        function updatePdfButtons() {
            prevPageBtn.disabled = currentPageNumber <= 1;
            nextPageBtn.disabled = currentPageNumber >= totalPages;
            currentPageEl.textContent = currentPageNumber;
            totalPagesEl.textContent = totalPages;
        }

        async function renderPage(pageNumber) {
            if (!pdfDoc) return;

            isRendering = true;

            const page = await pdfDoc.getPage(pageNumber);

            const viewportBase = page.getViewport({
                scale: 1
            });
            const availableWidth = Math.max(300, pdfWrap.clientWidth - 36);
            const scale = availableWidth / viewportBase.width;
            const viewport = page.getViewport({
                scale
            });

            const ratio = window.devicePixelRatio || 1;

            pdfCanvas.width = Math.floor(viewport.width * ratio);
            pdfCanvas.height = Math.floor(viewport.height * ratio);
            pdfCanvas.style.width = `${viewport.width}px`;
            pdfCanvas.style.height = `${viewport.height}px`;

            pdfCtx.setTransform(ratio, 0, 0, ratio, 0, 0);
            pdfCtx.clearRect(0, 0, viewport.width, viewport.height);

            await page.render({
                canvasContext: pdfCtx,
                viewport
            }).promise;

            isRendering = false;
            updatePdfButtons();

            if (pendingPage !== null) {
                const next = pendingPage;
                pendingPage = null;
                renderPage(next);
            }
        }

        function queueRenderPage(pageNumber) {
            if (isRendering) {
                pendingPage = pageNumber;
            } else {
                renderPage(pageNumber);
            }
        }

        async function loadPdfPreview() {
            try {
                const freshPdfUrl = buildPdfUrl();
                console.log("pdfUrl:", freshPdfUrl);

                const loadingTask = pdfjsLib.getDocument({
                    url: freshPdfUrl
                });

                pdfDoc = await loadingTask.promise;

                console.log("PDF cargado correctamente:", pdfDoc);

                totalPages = pdfDoc.numPages;
                currentPageNumber = 1;

                updatePdfButtons();
                await renderPage(currentPageNumber);

            } catch (error) {
                console.error("Error loading PDF preview:", error);
                alert("No se pudo cargar el preview del PDF. Revisa la consola.");
            }
        }

        prevPageBtn.addEventListener("click", () => {
            if (currentPageNumber <= 1) return;
            currentPageNumber--;
            queueRenderPage(currentPageNumber);
        });

        nextPageBtn.addEventListener("click", () => {
            if (currentPageNumber >= totalPages) return;
            currentPageNumber++;
            queueRenderPage(currentPageNumber);
        });

        window.addEventListener("resize", () => {
            if (pdfDoc) {
                queueRenderPage(currentPageNumber);
            }
        });

        function hideSignHere() {
            if (signHere) signHere.style.display = "none";
        }

        function showSignHere() {
            if (signHere) signHere.style.display = "block";
        }

        // Ajuste canvas a tamaño real
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const data = signaturePad ? signaturePad.toData() : [];

            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);

            if (data.length) {
                signaturePad.fromData(data);
                hideSignHere();
            } else {
                signaturePad.clear();
                showSignHere();
            }
        }

        // ✅ ocultar "SIGN HERE" al primer toque/click en el canvas (100% confiable)
        canvas.addEventListener('pointerdown', hideSignHere, {
            passive: true
        });
        canvas.addEventListener('mousedown', hideSignHere, {
            passive: true
        });
        canvas.addEventListener('touchstart', hideSignHere, {
            passive: true
        });

        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255,255,255,0)',
            onBegin: hideSignHere
        });

        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        document.getElementById('clear').addEventListener('click', () => {
            signaturePad.clear();
            showSignHere();
        });

        document.getElementById('undo').addEventListener('click', () => {
            const data = signaturePad.toData();
            if (data && data.length) {
                data.pop();
                signaturePad.fromData(data);
            }
            if (signaturePad.isEmpty()) showSignHere();
        });

        document.getElementById('firmar').addEventListener('click', async () => {
            if (signaturePad.isEmpty()) {
                alert('Primero dibuja tu firma.');
                return;
            }

            if (!docsignOverlay || !docsignOverlay.page) {
                alert('No se encontró la posición de DocSign@.');
                return;
            }

            document.getElementById('ok-loading').style.display = 'flex';

            try {
                // 1) Obtener el PDF original
                const existingPdfBytes = await fetch(buildPdfUrl()).then(res => res.arrayBuffer());

                // 2) Cargar PDF con pdf-lib
                const pdfDocLib = await PDFLib.PDFDocument.load(existingPdfBytes);

                // 3) Cargar firma PNG desde el canvas
                const imgBase64 = signaturePad.toDataURL('image/png');
                const pngImage = await pdfDocLib.embedPng(imgBase64);

                // 4) Página objetivo
                const targetPageIndex = Math.max(0, (docsignOverlay.page || 1) - 1);
                const pages = pdfDocLib.getPages();
                const page = pages[targetPageIndex];

                if (!page) {
                    throw new Error('La página del overlay no existe en el PDF.');
                }

                const pageHeight = page.getHeight();

                const x = Number(docsignOverlay.x || 0);
                const y = Number(docsignOverlay.y || 0);
                const width = Number(docsignOverlay.width || 160);
                const height = Number(docsignOverlay.height || 55);

                // Convertir Y desde sistema tipo canvas (origen arriba-izquierda)
                // a sistema PDF (origen abajo-izquierda)
                const pdfY = pageHeight - y - height;

                // ✅ 5) Tapar el texto DocSign@ con un rectángulo blanco
                const clearPadX = 12;
                const clearPadY = 8;

                page.drawRectangle({
                    x: x - clearPadX,
                    y: pdfY - clearPadY,
                    width: width + (clearPadX * 2),
                    height: height + (clearPadY * 2),
                    color: PDFLib.rgb(1, 1, 1),
                    borderWidth: 0,
                });

                // ✅ 6) Dibujar firma en el PDF
                page.drawImage(pngImage, {
                    x,
                    y: pdfY,
                    width,
                    height
                });

                // 6) Generar PDF final
                const signedPdfBytes = await pdfDocLib.save();

                // 7) Enviar PDF final al backend
                const formData = new FormData();
                const blob = new Blob([signedPdfBytes], {
                    type: 'application/pdf'
                });
                formData.append('pdf', blob, 'signed-document.pdf');

                const res = await fetch(@json(route('sign.signature', ['short' => $short, 'docId' => $docId])), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf
                    },
                    body: formData
                });

                const data = await res.json();

                document.getElementById('ok-loading').style.display = 'none';

                if (!data.ok) {
                    alert(data.error || 'Error guardando PDF firmado');
                    return;
                }

                alert('PDF firmado guardado correctamente.');

                // refrescar preview
                pdfDoc = null;
                await loadPdfPreview();

            } catch (e) {
                document.getElementById('ok-loading').style.display = 'none';
                console.error(e);
                alert('Error al insertar la firma en el PDF.');
            }
        });

        loadPdfPreview();
    </script>

</body>

</html>
