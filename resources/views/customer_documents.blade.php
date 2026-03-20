<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">

    <title>{{ $customer->Name }} - Documents</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="customer-id" content="{{ $customer->ID }}">

    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer-documents.css') }}">

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

            <div id="profile-wrapper" data-id="{{ $customer->ID }}">

                <div class="left-column">

                    {{-- MENU LATERAL --}}
                    <aside class="profile-side-menu">
                        <nav class="profile-side-nav">
                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('profile', $customer->ID) }}'">
                                <i class='bx bx-id-card'></i>
                                <span>Profile</span>
                            </button>

                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('policies.index', $customer->ID) }}'">
                                <i class='bx bx-shield-quarter'></i>
                                <span>Policies</span>
                            </button>

                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('payments', ['customerId' => $customer->ID]) }}'">
                                <i class='bx bx-credit-card'></i>
                                <span>Invoices (Payments)</span>
                            </button>

                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('estimates', $customer->ID) }}'">
                                <i class='bx bx-bar-chart-alt'></i>
                                <span>Estimates</span>
                            </button>

                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('reminders.index', $customer->ID) }}'">
                                <i class='bx bx-task'></i>
                                <span>Reminders</span>
                            </button>

                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('files.customer', $customer->ID) }}'">
                                <i class='bx bx-folder'></i>
                                <span>Files</span>
                            </button>

                            <button type="button" class="profile-menu-item active">
                                <i class='bx bx-file'></i>
                                <span>Documents</span>
                            </button>
                        </nav>
                    </aside>

                    <div class="profile-notes sticky-notes">

                        <div class="notes-header">
                            <h3>Notes</h3>
                            <button id="add-note-btn" class="btn small"><i class='bx bx-message-alt-add'></i> &nbsp;Add
                                Note</button>
                        </div>

                        <div class="notes-scroll">
                            <div id="notes-list"></div>
                        </div>

                    </div>



                    {{-- ⭐ OVERLAY PARA NUEVA NOTA ⭐ --}}
                    <div id="note-overlay">
                        <div class="note-window">
                            <h2 style="margin-bottom:15px;">Add Note</h2>

                            <label>Policy</label>
                            <input type="text" id="note-policy">

                            <label>Subject</label>
                            <input type="text" id="note-subject">

                            <label>Note</label>
                            <textarea id="note-text" rows="5"></textarea>

                            <div class="overlay-actions">
                                <button class="btn secondary" id="note-cancel">Cancel</button>
                                <button class="btn" id="note-save">Save</button>
                            </div>
                        </div>
                    </div>



                </div>

                <div class="profile-main">
                    <div class="customer-documents-card">

                        <div class="customer-documents-header">
                            <div>
                                <h2>{{ $customer->Name }} - Documents</h2>
                                <p>{{ $documents->count() }} document(s)</p>
                            </div>

                            <div class="customer-documents-actions">
                                <a href="{{ route('profile', $customer->ID) }}" class="btn secondary">
                                    <i class='bx bx-arrow-back'></i>
                                </a>
                            </div>
                        </div>

                        @if ($documents->count())
                            <div class="customer-documents-grid" id="customer-documents-grid">
                                @foreach ($documents as $doc)
                                    <div class="customer-document-item" data-id="{{ $doc->id }}">
                                        <div class="customer-document-card">

                                            <a href="{{ route('documents.view_pdf', $doc->id) }}" target="_blank"
                                                class="doc-icon-wrap">
                                                <img src="{{ asset('img/pdf-icon.png') }}" alt="PDF">
                                            </a>

                                            <div class="doc-info">
                                                <div class="doc-name" title="{{ $doc->display_name }}">
                                                    {{ $doc->display_name }}
                                                </div>

                                                <div class="doc-date">
                                                    @if (!empty($doc->created_at))
                                                        {{ \Carbon\Carbon::parse($doc->created_at)->format('Y-m-d H:i') }}
                                                    @elseif (!empty($doc->date))
                                                        {{ $doc->date }} {{ $doc->time ?? '' }}
                                                    @else
                                                        —
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="doc-card-actions">

                                                <button type="button"
                                                    class="btn delete-btn small single-delete-doc-btn"
                                                    data-id="{{ $doc->id }}"
                                                    data-url="{{ route('documents.destroy', $doc->id) }}">
                                                    Delete
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="customer-documents-empty">
                                <i class='bx bx-file-blank'></i>
                                <h3>No documents found</h3>
                                <p>This customer does not have generated documents yet.</p>
                            </div>
                        @endif

                    </div>
                </div>

            </div>
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
                        <label class="thumb-options" onclick="selectImage(1)"><img
                                src="{{ asset('../img/menu/thumbs/1.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(2)"><img
                                src="{{ asset('../img/menu/thumbs/2.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(3)"><img
                                src="{{ asset('../img/menu/thumbs/3.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(4)"><img
                                src="{{ asset('../img/menu/thumbs/4.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(5)"><img
                                src="{{ asset('../img/menu/thumbs/5.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(6)"><img
                                src="{{ asset('../img/menu/thumbs/6.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(7)"><img
                                src="{{ asset('../img/menu/thumbs/7.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(8)"><img
                                src="{{ asset('../img/menu/thumbs/8.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(9)"><img
                                src="{{ asset('../img/menu/thumbs/9.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(10)"><img
                                src="{{ asset('../img/menu/thumbs/10.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(11)"><img
                                src="{{ asset('../img/menu/thumbs/11.jpg') }}" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(12)"><img
                                src="{{ asset('../img/menu/thumbs/12.jpg') }}" alt=""></label>


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

    <!-- Archivos JS -->
    <script src="{{ asset('js/image.js') }}"></script>
    <script src="{{ asset('js/dropdown.js') }}"></script>
    <script src="{{ asset('js/menu.js') }}"></script>
    <script src="{{ asset('js/table.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/operations.js') }}"></script>
    <script src="{{ asset('js/help.js') }}"></script>
    <script src="{{ asset('js/profile.js') }}"></script>

    <script src="{{ asset('js/customer-documents.js') }}"></script>

</body>

</html>
