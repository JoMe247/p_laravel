<!doctype html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Customer Profile</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="customer-id" content="{{ $customer->ID }}">


    <!-- Archivos CSS -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sms-inbox.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- JQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div id="main-container">
        @include('menu')

        <section id="dash">

            <div id="lower-table-clients" type="fullscreen">

                {{-- CONTENEDOR GENERAL DEL PROFILE --}}
                <div id="profile-wrapper" data-id="{{ $customer->ID }}">

                    <div class="left-column">

                        {{-- MENU LATERAL --}}
                        <aside class="profile-side-menu">
                            <nav class="profile-side-nav">
                                <button type="button" class="profile-menu-item active"
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

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-file'></i>
                                    <span>Documents</span>
                                </button>

                            </nav>
                        </aside>

                        {{-- ⭐ NOTES – FUERA DEL MENÚ, STICKY ⭐ --}}
                        <div class="profile-notes sticky-notes">

                            <div class="notes-header">
                                <h3>Notes</h3>
                                <button id="add-note-btn" class="btn small">+ Add Note</button>
                            </div>

                            <div class="notes-scroll">
                                <div id="notes-list"></div>
                            </div>

                        </div>

                    </div> <!-- /.left-column -->


                    {{-- ⭐ OVERLAY PARA NUEVA NOTA ⭐ --}}
                    <div id="note-overlay">
                        <div class="note-window">
                            <h2 style="margin-bottom:15px;">Add Note</h2>

                            <label>Policy</label>
                            <select id="note-policy">
                                <option value="">— Select policy —</option>
                            </select>


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
                    {{-- /.left-column --}}



                    {{-- CONTENIDO PRINCIPAL --}}
                    <div class="profile-main">

                        <div class="profile-card-container">

                            <div id="profile-alert-container">
                                @if (!$customer->Alert)
                                    <button id="add-alert-btn" class="button" style="margin-bottom: 15px;">
                                        <i class='bx bx-error-circle'></i> Add Alert
                                    </button>
                                @else
                                    <div id="customer-alert-box" class="alert-box">
                                        <i class='bx bx-x alert-delete'></i>
                                        <i class='bx bx-error bx-tada'></i>
                                        <span>{{ $customer->Alert }}</span>
                                    </div>
                                @endif
                            </div>


                            <div class="profile-photo-section">
                                <div class="profile-photo-frame">
                                    <img id="customer-photo"
                                        src="{{ $customer->Picture ? asset($customer->Picture) : asset('img/default-profile.png') }}"
                                        alt="Profile Photo">
                                </div>

                                <button id="upload-photo-btn" class="btn upload-photo-btn">
                                    Upload Photo
                                </button>

                                <form id="photo-upload-form" enctype="multipart/form-data" style="display:none;">
                                    @csrf
                                    <input type="file" name="photo" id="photo-input" accept="image/*">
                                </form>
                            </div>

                            <h2 class="profile-name" id="customer-name-edit" contenteditable="true"
                                spellcheck="false">
                                {{ $customer->Name }}
                            </h2>

                            {{-- *** FORMULARIO ABIERTO AQUÍ *** --}}
                            <form id="profile-form" method="POST"
                                action="{{ route('customers.update', $customer->ID) }}">
                                @csrf
                                <input type="hidden" name="Name" id="customer-name-input" value="{{ $customer->Name }}">
                                @method('PUT')

                                @php
                                    function calculateAge($dob)
                                    {
                                        if (!$dob) {
                                            return null;
                                        }
                                        return \Carbon\Carbon::parse($dob)->age;
                                    }
                                    $age = calculateAge($customer->DOB);
                                @endphp

                                <div class="profile-info-grid editable-top">

                                    <div class="info-row">
                                        <label>DOB</label>
                                        <input type="date" name="DOB" value="{{ $customer->DOB }}">
                                    </div>

                                    <div class="info-row">
                                        <label>Age</label>
                                        <span class="value age-box">{{ $age !== null ? $age : '—' }}</span>
                                    </div>

                                    <div class="info-row">
                                        <label>Gender</label>
                                        <input type="text" name="Gender" value="{{ $customer->Gender }}">
                                    </div>

                                    <div class="info-row">
                                        <label>Marital</label>
                                        <input type="text" name="Marital" value="{{ $customer->Marital }}">
                                    </div>

                                </div>

                                {{-- CONTACT INFO --}}
                                <div class="profile-info-grid profile-contact-grid">

                                    <div class="info-row">
                                        <label>Phone 1</label>
                                        <input type="text" name="Phone" value="{{ $customer->Phone }}">
                                    </div>

                                    <div class="info-row">
                                        <label>Phone 2</label>
                                        <input type="text" name="Phone2" value="{{ $customer->Phone2 }}">
                                    </div>

                                    <div class="info-row">
                                        <label>Email 1</label>
                                        <input type="email" name="Email1" value="{{ $customer->Email1 }}">
                                    </div>

                                    <div class="info-row">
                                        <label>Email 2</label>
                                        <input type="email" name="Email2" value="{{ $customer->Email2 }}">
                                    </div>

                                </div>

                                {{-- DETAILS --}}
                                <div class="profile-section-box">
                                    <h3>Details</h3>

                                    <div class="details-grid">

                                        <div class="info-row">
                                            <label>Address</label>
                                            <input type="text" name="Address" value="{{ $customer->Address }}">
                                        </div>

                                        <div class="info-row">
                                            <label>City</label>
                                            <input type="text" name="City" value="{{ $customer->City }}">
                                        </div>

                                        <div class="info-row">
                                            <label>State</label>
                                            <input type="text" name="State" value="{{ $customer->State }}">
                                        </div>

                                        <div class="info-row">
                                            <label>Zip Code</label>
                                            <input type="text" name="ZIP_Code" value="{{ $customer->ZIP_Code }}">
                                        </div>

                                        <div class="info-row">
                                            <label>Drivers License</label>
                                            <input type="text" name="Drivers_License"
                                                value="{{ $customer->Drivers_License }}">
                                        </div>

                                        <div class="info-row">
                                            <label>DL State</label>
                                            <input type="text" name="DL_State" value="{{ $customer->DL_State }}">
                                        </div>

                                    </div>
                                </div>

                                {{-- OFFICE INFO --}}
                                <div class="profile-section-box">
                                    <h3>Office Information</h3>

                                    <div class="details-grid">

                                        <div class="info-row">
                                            <label>Office</label>
                                            <input type="text" name="Office" value="{{ $customer->Office }}">
                                        </div>

                                        <div class="info-row">
                                            <label>CID</label>
                                            <input type="text" name="CID" value="{{ $customer->CID }}">
                                        </div>

                                        <div class="info-row">
                                            <label>Agent of Record</label>
                                            <span class="added-display">
                                                {{ $customer->Agent_of_Record }}
                                            </span>
                                        </div>

                                        <div class="info-row" style="display:block;">

                                            <label>Agency</label>
                                            <span class="added-display">
                                                {{ $customer->Agency }}
                                            </span>
                                        </div>

                                        <div class="info-row">
                                            <label>Source</label>
                                            <input type="text" name="Source" value="{{ $customer->Source }}">
                                        </div>

                                        <div class="info-row">
                                            <label>Added</label>
                                            <span class="added-display">
                                                {{ $customer->Added ? \Carbon\Carbon::parse($customer->Added)->format('Y-m-d') : '—' }}
                                            </span>
                                        </div>

                                    </div>
                                </div>

                                <div class="profile-actions">
                                    <button type="submit" class="btn profile-btn-save">Save</button>
                                    <a href="{{ route('customers.index') }}" class="btn secondary">Back</a>
                                    <button type="button" id="delete-customer-btn"
                                        class="btn delete-btn">Delete</button>
                                </div>
                            </form>

                        </div>

                    </div>

                </div> {{-- /#profile-wrapper --}}

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


    <!-- Archivos JS -->
    <script src="{{ asset('js/image.js') }}"></script>
    <script src="{{ asset('js/weather.js') }}"></script>
    <script src="{{ asset('js/dropdown.js') }}"></script>
    <script src="{{ asset('js/menu.js') }}"></script>
    <script src="{{ asset('js/table.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/operations.js') }}"></script>
    <script src="{{ asset('js/help.js') }}"></script>
    <script src="{{ asset('js/profile.js') }}"></script>

</body>

</html>
