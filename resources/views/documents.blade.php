<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Documents</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">

    <!-- CSS base del proyecto -->
    <!-- Estilos globales -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">

    <!-- CSS de Documents -->
    <link rel="stylesheet" href="{{ asset('css/documents.css') }}">

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body>

    <div id="main-container">
        @include('menu')

        <section id="dash">

            {{-- Si ya tienes sidebar/header global por layout, puedes integrarlo a tu layout.
         Aquí lo dejo standalone para que lo pegues fácil. --}}

            <main class="documents-page">

                <div class="documents-topbar">
                    <div class="documents-actions">
                        <!-- Botón Nuevo Documento (sin funcionamiento por ahora) -->
                        <a href="{{ route('documents.create_document') }}" class="btn btn-primary" id="newDocumentBtn">
                            New Document
                        </a>

                        <a href="{{ route('templates.create') }}" class="btn-template" id="btn-new-template">
                            <i class='bx bx-plus'></i>
                            New Template
                        </a>


                        <!-- Botón Imprimir (sin funcionamiento por ahora) -->
                        <button type="button" class="btn-secondary" id="btn-print-documents">
                            <i class='bx bx-printer'></i>
                            Print
                        </button>
                    </div>
                </div>

                <section class="documents-card">
                    <div class="documents-card-header">
                        <div class="documents-title">
                            <h2>Documents</h2>
                            <p class="documents-count">
                                Total documents: <span id="documents-total">{{ $totalDocuments }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="documents-table-wrap">
                        <table class="documents-table">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">ID</th>
                                    <th style="min-width: 220px;">CUSTOMER'S NAME</th>
                                    <th style="min-width: 140px;">PHONE</th>
                                    <th style="min-width: 120px;">TYPE</th>
                                    <th style="min-width: 120px;">POLICY #</th>
                                    <th style="min-width: 140px;">DATE</th>
                                    <th style="min-width: 220px;">URL</th>
                                    <th style="min-width: 120px;">STATUS</th>
                                    <th style="width: 160px; text-align:center;">TOOLS</th>
                                </tr>
                            </thead>

                            <tbody>
                                {{-- Por ahora vacío --}}
                                @if (isset($documents) && $documents->count() > 0)
                                    @foreach ($documents as $doc)
                                        <tr>
                                            <td>{{ $doc->id }}</td>
                                            <td>{{ $doc->customer_name }}</td>
                                            <td>{{ $doc->phone }}</td>
                                            <td>{{ $doc->type }}</td>
                                            <td>{{ $doc->policy_number }}</td>
                                            <td>{{ $doc->date }}</td>
                                            <td class="td-url">
                                                <a href="#" class="url-link">Open URL</a>
                                            </td>
                                            <td>
                                                <span class="badge-status badge-success">Active</span>
                                            </td>
                                            <td class="td-tools">
                                                <button type="button" class="tool-btn" title="View">
                                                    <i class='bx bx-show'></i>
                                                </button>
                                                <button type="button" class="tool-btn" title="Resend to phone">
                                                    <i class='bx bx-message-rounded-dots'></i>
                                                </button>
                                                <button type="button" class="tool-btn" title="Resend by email">
                                                    <i class='bx bx-envelope'></i>
                                                </button>
                                                <button type="button" class="tool-btn tool-danger" title="Delete">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="empty-row">
                                        <td colspan="9">
                                            <div class="empty-state">
                                                <i class='bx bx-folder-open'></i>
                                                <p>No documents yet.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </section>

            </main>

        </section>
    </div>

    <!-- UI Elements -->
    <div class="window-confirm">
        <div class="confirm-window-container">
            <div class="confirm-window-content">
                <div class="confirm-window-header">
                    <!-- <div class="confirm-window-icon"></div> -->
                    <!-- <div class="confirm-window-close-btn">
                    <button>
                        <i class='bx bx-x'></i>
                    </button>
                </div> -->
                </div>
                <div class="confirm-window-text-content">
                    <div class="confirm-window-title"></div>
                    <div class="confirm-window-description"></div>
                </div>
            </div>
            <div class="confirm-window-buttons">
                <button class="confirm-window-confirm-btn">Confirm</button>
                <button class="confirm-window-cancel-btn" onclick="confirmBoxOff()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="settings-menu">
        <div id="table-border">
            <i class='bx bx-x' id="close-settings" onclick="closeSettings();"></i>
            <h2>Settings</h2>

            <div class="settings-sub-title">Language</div>

            <div id="language-settings">
                <p>
                    <input type="radio" id="test1" name="radio-group" checked>
                    <label for="test1">English</label>
                </p>
                <p>
                    <input type="radio" id="test2" name="radio-group">
                    <label for="test2">Spanish</label>
                </p>
            </div>

            <!-- <div class='settings-sub-title'>Theme</div>
            
            <div id="dark-mode">
                <span class="switch">
                    <input id="switch-rounded" type="checkbox" />
                    <label for="switch-rounded"></label>
                </span>
                <p>Dark Mode</p>
            </div> -->

            <div class='settings-sub-title'>Action Color</div>

            <div class="color-pick-container" id="action-color-container">
                <div class="color-pick" color="default" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="red" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="reddish" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="orange" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="yellow" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="green" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="aquamarine" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="blue" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="royal" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="purple" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="pink" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="gray" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="black" onclick="selectActionColor(this)"></div>
                <div class="color-pick" color="white" onclick="selectActionColor(this)"></div>
            </div>

            <div class="settings-sub-title" style="margin-top:50px;">Side Panel Background</div>

            <div id="background-side-settings">
                <div id="background-color-option-container">

                    <div class='settings-sub-title'>Select Color</div>

                    <div class="color-pick-container">
                        <div class="color-pick" color="default" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="red" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="reddish" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="orange" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="yellow" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="green" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="aquamarine" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="dodgerblue" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="royal" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="purple" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="pink" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="gray" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="black" onclick="selectColor(this)"></div>
                        <div class="color-pick" color="white" onclick="selectColor(this)"></div>
                    </div>
                </div>

                <div id="background-image-option-container">

                    <div id="images-container">
                        <!-- <img id="settings-img-option" src="img/menu/1.jpg" alt=""> -->
                        <div class='settings-sub-title'>Select Image</div>
                        <label class="thumb-options" onclick="selectImage(1)"><img src="img/menu/thumbs/1.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(2)"><img src="img/menu/thumbs/2.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(3)"><img src="img/menu/thumbs/3.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(4)"><img src="img/menu/thumbs/4.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(5)"><img src="img/menu/thumbs/5.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(6)"><img src="img/menu/thumbs/6.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(7)"><img src="img/menu/thumbs/7.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(8)"><img src="img/menu/thumbs/8.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(9)"><img src="img/menu/thumbs/9.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(10)"><img src="img/menu/thumbs/10.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(11)"><img src="img/menu/thumbs/11.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(12)"><img src="img/menu/thumbs/12.jpg"
                                alt=""></label>


                    </div>
                </div>

                <div id="sideBlur-slider">
                    <div class="slider-wrap" id="side-image-slider">
                        <label for="frac" style="display:block;margin-bottom:8px;">Side Image Blur</label>
                        <div class="row">
                            <input id="frac" type="range" min="0" max="1" step="0.01"
                                value="0.00" />
                            <div class="value">
                                <span id="val-pct">0%</span>
                            </div>
                        </div>
                    </div>

                    <div class="slider-wrap" id="home-image-slider">
                        <label for="frac2" style="display:block;margin-bottom:8px;">Home Image Blur</label>
                        <div class="row">
                            <input id="frac2" type="range" min="0" max="1" step="0.01"
                                value="0.00" />
                            <div class="value">
                                <span id="val-pct2">0%</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div id="dim-screen"></div>


    <script src=" {{ asset('js/dropdown.js') }}"></script>
    <script src=" {{ asset('js/menu.js') }}"></script>
    <script src=" {{ asset('js/table.js') }}"></script>
    <script src=" {{ asset('js/settings.js') }}"></script>
    <script src=" {{ asset('js/operations.js') }}"></script>

    <script src="{{ asset('js/documents.js') }}"></script>
</body>

</html>
