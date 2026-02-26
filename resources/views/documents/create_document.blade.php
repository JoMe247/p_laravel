<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Document</title>

    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/create_document.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/upload.css') }}">

    <!-- PDF.js + pdf-lib -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js"></script>
    <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>
</head>

<body>

    <div class="page-wrapper">

        <div class="top-bar">
            <a class="btn-back" href="{{ route('documents.index') }}">← Documents</a>
            <h1 class="page-title">Create Document</h1>
        </div>

        <div class="doc-controls">
            <!-- Template -->
            <select id="templateSelect" class="control">
                <option value="">Select template...</option>
            </select>

            <!-- Customer Search (reemplaza botón) -->
            <div class="customer-search-wrap">
                <input id="customerSearch" class="control" type="text"
                    placeholder="Search customer (name or phone)..." disabled>
                <div id="customerSuggest" class="suggest hidden"></div>
            </div>

            <!-- Policies (sin botón ni segundo menú) -->
            <select id="policySelect" class="control" disabled>
                <option value="">Policies...</option>
            </select>

            <!-- Save -->
            <button id="saveDocBtn" class="control btn primary" disabled>Save</button>
        </div>

        <div class="selected-line">
            <span id="selectedCustomerInfo" class="muted">No customer selected.</span>
        </div>

        <!-- PDF Preview -->
        <div class="viewer-wrap">
            <div id="pdfViewer">
                <canvas id="pdfCanvas"></canvas>
                <div id="inputOverlay"></div>
            </div>

            <!-- Controles debajo del PDF -->
            <div id="viewerControls" class="viewer-controls hidden">
                <button id="prevPage" class="btn small">Back P.</button>
                <span class="muted"><span id="currentPage">1</span>/<span id="totalPages">1</span></span>
                <button id="nextPage" class="btn small">Next P.</button>
            </div>
        </div>

    </div>

    <script>
        window.ROUTES = {
            templatesOptions: "{{ route('documents.templates.options') }}",
            templateDataBase: "{{ url('/documents/templates') }}",
            customersSearch: "{{ route('documents.customers.search') }}",
            customerPoliciesBase: "{{ url('/documents/customers') }}",
            saveGenerated: "{{ route('documents.save_generated') }}",
        };
        window.BASE_URL = "{{ url('/') }}";
    </script>

    <script src="{{ asset('js/create_document.js') }}"></script>
</body>

</html>
