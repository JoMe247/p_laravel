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
    <div class="document-information" style="padding:0; overflow:hidden;">
        <iframe src="{{ route('sign.pdf', ['short' => $short, 'docId' => $docId]) }}"
            style="width:100%; height:650px; border:0; display:block;"></iframe>
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

    <script>
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const wrapper = document.getElementById("signature-pad");
        const canvas = wrapper.querySelector("canvas");


        const signHere = document.getElementById("sign-here");

        function hideSignHere() {
            if (signHere) signHere.style.display = "none";
        }

        function showSignHere() {
            if (signHere) signHere.style.display = "block";
        }

        // Ajuste canvas a tamaño real
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
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
            document.getElementById("sign-here").style.display = "block";
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

            document.getElementById('ok-loading').style.display = 'flex';

            const imgBase64 = signaturePad.toDataURL('image/png');

            try {
                const res = await fetch(@json(route('sign.signature', ['short' => $short, 'docId' => $docId])), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        imgBase64
                    })
                });

                const data = await res.json();

                document.getElementById('ok-loading').style.display = 'none';

                if (!data.ok) {
                    alert(data.error || 'Error guardando firma');
                    return;
                }

                // Por ahora solo confirmamos guardado
                alert('Firma guardada (temporal): ' + data.path);

            } catch (e) {
                document.getElementById('ok-loading').style.display = 'none';
                alert('Error de red guardando firma');
            }
        });
    </script>

</body>

</html>
