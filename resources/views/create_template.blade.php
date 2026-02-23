<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Template</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="{{ asset('css/templates/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/templates/upload.css') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.worker.min.js"></script>
    <script src="https://unpkg.com/pdf-lib/dist/pdf-lib.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>
        window.TEMPLATE_SAVE_URL = "{{ route('templates.store') }}";
        window.DOCUMENTS_URL = "{{ route('documents.index') }}";
        window.CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>
</head>

<body>

    <div class="container" style="display: none;">
        <h1>Create Template</h1>

        <div id="paginaActual">Current Page: &nbsp;&nbsp;<p id="counter">1</p>
        </div>
        <div id="paginasTotal">Total Pages: &nbsp;&nbsp;<p id="total-pages"></p>
        </div>

        <label for="upload" id="upload-file-button"><i class='bx bx-upload'></i>&nbsp; Upload New File</label>
        <input type="file" id="upload" accept="application/pdf" style="position: fixed;z-index:-1;opacity: 0;">

        <canvas id="pdfCanvas" style="display:none"></canvas>

        <div class="container-drag" id="draggable-area" style="display: none;">
            <div class="box" contenteditable="true" id="dragged-value" spellcheck="false" data-icon="text">Add Text
            </div>
        </div>

        <div class="overlay-controls">
            <button class="button-controls" onclick="undo()">
                <i class='bx bx-undo'></i>
                <p>Undo</p>
            </button>

            <button class="button-controls" onclick="redo()">
                <i class='bx bx-redo'></i>
                <p>Redo</p>
            </button>

            <button class="button-controls" id="backPage" onclick="back()" disabled>
                <i class='bx bx-arrow-from-right'></i>
                <p>Back P.</p>
            </button>

            <button class="button-controls" id="nextPage" onclick="next()" disabled>
                <i class='bx bx-arrow-to-right'></i>
                <p>Next P.</p>
            </button>

            <button id="addTextButton" class="button-controls">
                <i class='bx bxs-send'></i>
                <p>Add Text</p>
            </button>

            <button id="saveButton" class="button-controls">
                <i class='bx bx-save'></i>
                <p>Save</p>
            </button>

            <input type="text" id="overlayText" placeholder="Enter text to overlay" style="display: none;">
            <input type="number" id="xCoordinate" placeholder="X Coordinate" style="display: none;">
            <input type="number" id="yCoordinate" placeholder="Y Coordinate" style="display: none;">
        </div>
    </div>

    <!-- Upload Area -->
    <div id="uploadArea" class="upload-area">
        <div class="upload-area__header">
            <h1 class="upload-area__title">Upload your file</h1>
            <p class="upload-area__paragraph" style="display:none">
                File should be an image
                <strong class="upload-area__tooltip">
                    Like <span class="upload-area__tooltip-data"></span>
                </strong>
            </p>
        </div>

        <div id="dropZoon" class="upload-area__drop-zoon drop-zoon" ondragover="handleDrag(event)"
            ondragleave="resetBox()">
            <span class="drop-zoon__icon"><i class='bx bxs-file-image'></i></span>
            <p class="drop-zoon__paragraph">Drop your file here or Click to browse</p>
            <span id="loadingText" class="drop-zoon__loading-text">Please Wait</span>
            <img src="" alt="Preview Image" id="previewImage" class="drop-zoon__preview-image" draggable="false"
                style="display: none;">
            <input style="display:none;" type="file" id="fileInput" class="drop-zoon__file-input"
                accept="application/pdf">
        </div>

        <div id="fileDetails" class="upload-area__file-details file-details">
            <h3 class="file-details__title">Uploaded File</h3>

            <div id="uploadedFile" class="uploaded-file">
                <div class="uploaded-file__icon-container">
                    <i class='bx bxs-file-blank uploaded-file__icon'></i>
                    <span class="uploaded-file__icon-text"></span>
                </div>

                <div id="uploadedFileInfo" class="uploaded-file__info">
                    <span class="uploaded-file__name">Project</span>
                    <span class="uploaded-file__counter">0%</span>
                </div>
            </div>
        </div>
    </div>
    <!-- End Upload Area -->

    <div id="options-bar" style="display: none;">

        <div class="option-item" onclick="docNameActive()">
            <i class='bx bx-user'></i>
            <p class="option-info">
                This will place the document name dynamically when sending.
            </p>
        </div>
        
        <div class="option-item" onclick="textActive()">
            <i class='bx bx-text' style="padding-left: 3px;"></i>
            <p class="option-info">Add text fields to the document.</p>
        </div>

        <div class="option-item" onclick="penActive()">
            <i class='bx bx-pen'></i>
            <p class="option-info">This will be used to place the signature.</p>
        </div>

        <div class="option-item" onclick="watchActive()">
            <i class='bx bxs-watch'></i>
            <p class="option-info">This will be used to place the date and time the document was signed.</p>
        </div>

        <div class="option-item" onclick="calendarActive()">
            <i class='bx bx-calendar'></i>
            <p class="option-info">Text field used to place the current day each time you send a document.</p>
        </div>
    </div>

    <script src="{{ asset('js/templates/template.js') }}"></script>
    <script src="{{ asset('js/templates/upload.js') }}"></script>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>

</body>

</html>
