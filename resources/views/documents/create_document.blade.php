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
            <!-- 1) Template -->
            <select id="templateSelect" class="control">
                <option value="">Select template...</option>
            </select>

            <!-- 2) Customer (disabled hasta template) -->
            <button id="customerBtn" class="control btn" disabled>Customer</button>

            <!-- 3) Policies (disabled hasta customer) -->
            <button id="policiesBtn" class="control btn" disabled>Policies</button>

            <!-- Save -->
            <button id="saveDocBtn" class="control btn primary" disabled>Save</button>
        </div>

        <!-- Customer panel -->
        <div id="customerPanel" class="panel hidden">
            <div class="panel-row">
                <label>Name</label>
                <input id="custName" type="text" placeholder="Type name...">
                <div id="nameSuggest" class="suggest hidden"></div>
            </div>

            <div class="panel-row">
                <label>Phone</label>
                <input id="custPhone" type="text" placeholder="Type phone...">
                <div id="phoneSuggest" class="suggest hidden"></div>
            </div>

            <div class="panel-row">
                <label>Email</label>
                <input id="custEmail" type="text" placeholder="Type email...">
                <div id="emailSuggest" class="suggest hidden"></div>
            </div>

            <div class="panel-row">
                <small id="selectedCustomerInfo" class="muted">No customer selected.</small>
            </div>
        </div>

        <!-- Policies panel -->
        <div id="policiesPanel" class="panel hidden">
            <div class="panel-row">
                <label>Policy</label>
                <select id="policySelect">
                    <option value="">Select policy...</option>
                </select>
            </div>
        </div>

        <!-- PDF Preview -->
        <div class="viewer-wrap">
            <div id="pdfViewer">
                <canvas id="pdfCanvas"></canvas>
                <div id="inputOverlay"></div>
            </div>

            <div id="viewerControls" class="viewer-controls hidden">
                <button id="prevPage" class="btn small">Prev</button>
                <span class="muted"><span id="currentPage">1</span>/<span id="totalPages">1</span></span>
                <button id="nextPage" class="btn small">Next</button>
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