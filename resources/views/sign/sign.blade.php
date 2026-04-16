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
        <script src="{{ asset('js/device.js') }}"></script>
        <script src="{{ asset('js/signature_pad.umd.js') }}"></script>
        <script src="{{ asset('js/pdfjs/pdf.min.js') }}"></script>
        <script src="{{ asset('js/vendor/pdf-lib.min.js') }}"></script>
        <script>
            pdfjsLib.GlobalWorkerOptions.workerSrc = "{{ asset('js/pdfjs/pdf.worker.min.js') }}";
        </script>

        <script>
            window.IPINFO_TOKEN = @json(config('services.ipinfo.token'));
        </script>

        <script>
            const pdfUrlBase = @json(route('sign.pdf', ['short' => $short, 'docId' => $docId]));
            const docsignOverlay = @json($docsignOverlay);
            const docdtimeOverlay = @json($docdtimeOverlay);

            function buildPdfUrl() {
                return pdfUrlBase + '?t=' + Date.now();
            }

            function getSignedDateTimeLabel() {
                const now = new Date();

                const yyyy = now.getFullYear();
                const mm = String(now.getMonth() + 1).padStart(2, "0");
                const dd = String(now.getDate()).padStart(2, "0");

                const hh = String(now.getHours()).padStart(2, "0");
                const mi = String(now.getMinutes()).padStart(2, "0");
                const ss = String(now.getSeconds()).padStart(2, "0");

                const signedDate = `${yyyy}-${mm}-${dd}`;
                const signedTime = `${hh}:${mi}:${ss}`;

                return {
                    signedDate,
                    signedTime,
                    label: `Signature ${signedDate} at ${signedTime}`,
                };
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

            function getBitsLabel() {
                return /WOW64|Win64|x64|amd64/i.test(navigator.userAgent) ?
                    " (64 Bits)" :
                    " (32 Bits)";
            }

            function getClientDeviceInfo() {
                let gpuInfo = "";
                try {
                    gpuInfo = typeof getVideoCardInfo === "function" ?
                        JSON.stringify(getVideoCardInfo()) :
                        "";
                } catch (e) {
                    gpuInfo = "";
                }

                const browserName =
                    typeof browser !== "undefined" ?
                    browser :
                    (window.browserInfo?.browser || "");

                const osName = `${window.browserInfo?.os || ""} ${window.browserInfo?.osVersion || ""}${getBitsLabel()}`.trim();

                return {
                    browser_client: browserName || "",
                    os_client: osName || "",
                    dName_client: `${(window.navigator.userAgent || "").toLowerCase()}${gpuInfo}`,
                    device_client: window.browserInfo?.mobile ? "Mobile Device" : "Desktop Device",
                };
            }

            function getClientCoordinates() {
                return new Promise((resolve) => {
                    if (!navigator.geolocation) {
                        resolve("");
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            resolve(
                                `${position.coords.latitude},${position.coords.longitude}`
                            );
                        },
                        () => resolve(""), {
                            enableHighAccuracy: true,
                            timeout: 8000,
                            maximumAge: 0,
                        }
                    );
                });
            }


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


            function getPublicIpFromL2() {
                return new Promise((resolve) => {
                    const varName = "userip_" + Date.now();

                    const script = document.createElement("script");
                    script.src = `https://l2.io/ip.js?var=${varName}`;

                    script.onload = function() {
                        const ip = window[varName] || "";
                        try {
                            delete window[varName];
                        } catch (e) {}
                        script.remove();
                        resolve(ip);
                    };

                    script.onerror = function() {
                        try {
                            delete window[varName];
                        } catch (e) {}
                        script.remove();
                        resolve("");
                    };

                    document.body.appendChild(script);
                });
            }

            async function getClientIpInfoLegacy() {
                const out = {
                    ip: "",
                    city: "",
                    country: "",
                    region: "",
                    coords: "", // la que usarás en el certificado
                    coords_ipinfo: "", // coordenadas por IP pública
                    coords_browser: "", // coordenadas del navegador
                };

                if (!window.IPINFO_TOKEN) {
                    console.warn("No hay IPINFO_TOKEN configurado.");
                    return out;
                }

                try {
                    const publicIp = await getPublicIpFromL2();
                    out.ip = publicIp || "";

                    if (!publicIp) {
                        return out;
                    }

                    const res = await fetch(
                        `https://ipinfo.io/${publicIp}?token=${encodeURIComponent(window.IPINFO_TOKEN)}`);
                    const json = await res.json();

                    out.ip = json.ip || publicIp || "";
                    out.city = json.city || "";
                    out.country = json.country || "";
                    out.region = json.region || "";
                    out.coords_ipinfo = json.loc || "";
                    out.coords = out.coords_ipinfo; // ← por default usar IP pública

                    console.log("coords ipinfo:", out.coords_ipinfo);
                } catch (e) {
                    console.warn("No se pudo obtener ipinfo client:", e);
                }

                try {
                    if (navigator.geolocation) {
                        const coords = await new Promise((resolve, reject) => {
                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    resolve(`${position.coords.latitude},${position.coords.longitude}`);
                                },
                                reject, {
                                    enableHighAccuracy: true,
                                    timeout: 8000,
                                    maximumAge: 0,
                                }
                            );
                        });

                        if (coords) {
                            console.log("coords navegador:", coords);
                            out.coords_browser = coords;
                            // OJO: ya no reemplazamos out.coords
                        }
                    }
                } catch (e) {
                    console.warn("Ubicación del navegador no disponible:", e);
                }

                return out;
            }

            function getTrimmedSignatureDataUrl() {
                const srcCanvas = canvas;
                const srcCtx = srcCanvas.getContext('2d', {
                    willReadFrequently: true
                });

                const w = srcCanvas.width;
                const h = srcCanvas.height;
                const imageData = srcCtx.getImageData(0, 0, w, h).data;

                let minX = w;
                let minY = h;
                let maxX = -1;
                let maxY = -1;

                for (let y = 0; y < h; y++) {
                    for (let x = 0; x < w; x++) {
                        const alpha = imageData[(y * w + x) * 4 + 3];
                        if (alpha > 0) {
                            if (x < minX) minX = x;
                            if (y < minY) minY = y;
                            if (x > maxX) maxX = x;
                            if (y > maxY) maxY = y;
                        }
                    }
                }

                // respaldo por seguridad
                if (maxX === -1 || maxY === -1) {
                    return signaturePad.toDataURL('image/png');
                }

                const pad = 6; // pequeño margen extra
                minX = Math.max(0, minX - pad);
                minY = Math.max(0, minY - pad);
                maxX = Math.min(w - 1, maxX + pad);
                maxY = Math.min(h - 1, maxY + pad);

                const cropWidth = maxX - minX + 1;
                const cropHeight = maxY - minY + 1;

                const croppedCanvas = document.createElement('canvas');
                croppedCanvas.width = cropWidth;
                croppedCanvas.height = cropHeight;

                const croppedCtx = croppedCanvas.getContext('2d');
                croppedCtx.drawImage(
                    srcCanvas,
                    minX, minY, cropWidth, cropHeight,
                    0, 0, cropWidth, cropHeight
                );

                return croppedCanvas.toDataURL('image/png');
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

                    // 3) Cargar firma PNG recortada (sin tanto espacio transparente)
                    const trimmedImgBase64 = getTrimmedSignatureDataUrl();
                    const pngImage = await pdfDocLib.embedPng(trimmedImgBase64);
                    const clientInfo = getClientDeviceInfo();
                    const clientGeo = await getClientIpInfoLegacy();
                    console.log('clientGeo', clientGeo);
                    // usar coordenadas de IP pública en el certificado
                    const clientCoordinates = clientGeo.coords_ipinfo || clientGeo.coords || '';

                    // 4) Página objetivo
                    const targetPageIndex = Math.max(0, (docsignOverlay.page || 1) - 1);
                    const pages = pdfDocLib.getPages();
                    const page = pages[targetPageIndex];

                    if (!page) {
                        throw new Error('La página del overlay no existe en el PDF.');
                    }

                    const pageHeight = page.getHeight();

                    // Área base de DocSign@
                    // Área base de DocSign@
                    const baseX = Number(docsignOverlay.x || 0);
                    const baseY = Number(docsignOverlay.y || 0);
                    const baseWidth = Number(docsignOverlay.width || 160);
                    const baseHeight = Number(docsignOverlay.height || 55);

                    // Convertir Y desde canvas (arriba-izquierda) a PDF (abajo-izquierda)
                    const basePdfY = pageHeight - baseY - baseHeight;

                    // Tamaño solo de la firma
                    const signatureScale = 1.00;

                    // Caja objetivo centrada en la posición de DocSign@
                    const targetBoxWidth = baseWidth * signatureScale;
                    const targetBoxHeight = baseHeight * signatureScale;

                    const targetBoxX = baseX - ((targetBoxWidth - baseWidth) / 2);
                    const targetBoxY = basePdfY - ((targetBoxHeight - baseHeight) / 2);

                    // Mantener proporción real del PNG
                    const imgAspect = pngImage.width / pngImage.height;

                    let drawWidth = targetBoxWidth;
                    let drawHeight = drawWidth / imgAspect;

                    if (drawHeight > targetBoxHeight) {
                        drawHeight = targetBoxHeight;
                        drawWidth = drawHeight * imgAspect;
                    }

                    // Centrar la firma dentro de la caja
                    const drawX = targetBoxX + ((targetBoxWidth - drawWidth) / 2);
                    const drawY = targetBoxY + ((targetBoxHeight - drawHeight) / 2);

                    // Dibujar solo la firma PNG, sin fondo blanco
                    page.drawImage(pngImage, {
                        x: drawX,
                        y: drawY,
                        width: drawWidth,
                        height: drawHeight,
                    });

                    // Dibujar fecha/hora de firma en la posición de DocDTime@
                    if (docdtimeOverlay && docdtimeOverlay.page) {
                        const signedMeta = getSignedDateTimeLabel();

                        const dtTargetPageIndex = Math.max(0, (Number(docdtimeOverlay.page || 1) - 1));
                        const dtPage = pages[dtTargetPageIndex];

                        if (dtPage) {
                            const dtPageHeight = dtPage.getHeight();
                            const dtX = Number(docdtimeOverlay.x || 0);
                            const dtY = Number(docdtimeOverlay.y || 0);

                            dtPage.drawText(signedMeta.label, {
                                x: dtX,
                                y: dtPageHeight - dtY - 20,
                                size: 12,
                                color: PDFLib.rgb(0, 0, 0),
                            });
                        }
                    }

                    // 6) Generar PDF final
                    const signedPdfBytes = await pdfDocLib.save({
                        useObjectStreams: false
                    });

                    // 7) Enviar PDF final al backend
                    const formData = new FormData();
                    const blob = new Blob([signedPdfBytes], {
                        type: 'application/pdf'
                    });
                    formData.append('pdf', blob, 'signed-document.pdf');

                    formData.append('signature_data', trimmedImgBase64);
                    formData.append('browser_client', clientInfo.browser_client);
                    formData.append('os_client', clientInfo.os_client);
                    formData.append('dName_client', clientInfo.dName_client);
                    formData.append('device_client', clientInfo.device_client);
                    formData.append('coordinates_client', clientCoordinates || '');

                    formData.append('ip_client', clientGeo.ip || '');
                    formData.append('city_client', clientGeo.city || '');
                    formData.append('country_client', clientGeo.country || '');
                    formData.append('client_region', clientGeo.region || '');

                    const res = await fetch(@json(route('sign.signature', ['short' => $short, 'docId' => $docId])), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const rawText = await res.text();

                    let data;
                    try {
                        data = JSON.parse(rawText);
                    } catch (err) {
                        console.error('Respuesta no JSON:', rawText);
                        throw new Error('El servidor devolvió HTML en lugar de JSON.');
                    }

                    document.getElementById('ok-loading').style.display = 'none';

                    if (!res.ok || !data.ok) {
                        alert(data.detail || data.error || 'Error guardando PDF firmado');
                        return;
                    }

                    alert('PDF firmado guardado correctamente.');

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
