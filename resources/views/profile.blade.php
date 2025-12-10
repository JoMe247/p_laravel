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
    {{-- NUEVO CSS SOLO PARA PROFILE --}}
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

                            <div class="profile-side-header" style="display:none;"></div>

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

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-credit-card'></i>
                                    <span>Invoices (Payments)</span>
                                </button>

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-task'></i>
                                    <span>Reminders</span>
                                </button>

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-folder'></i>
                                    <span>Files</span>
                                </button>

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-map'></i>
                                    <span>Map</span>
                                </button>
                            </nav>
                        </aside>


                        <!-- ⭐ NOTES fuera del aside, pero en la misma columna izquierda ⭐ -->
                        <div class="notes-container sticky-notes">
                            <div class="notes-header">
                                <h3>Notes</h3>
                                <button id="add-note-btn" class="btn small">Add Note</button>
                            </div>

                            <div class="notes-scroll">
                                <div id="notes-list">
                                    {{-- notas cargadas por JS --}}
                                </div>
                            </div>
                        </div>
                    </div> {{-- /.left-column --}}

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

                                <h2 class="profile-name">{{ $customer->Name }}</h2>

                                {{-- *** FORMULARIO ABIERTO AQUÍ *** --}}
                                <form id="profile-form" method="POST"
                                    action="{{ route('customers.update', $customer->ID) }}">
                                    @csrf
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
                                                <input type="text" name="Address"
                                                    value="{{ $customer->Address }}">
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
                                                <input type="text" name="ZIP_Code"
                                                    value="{{ $customer->ZIP_Code }}">
                                            </div>

                                            <div class="info-row">
                                                <label>Drivers License</label>
                                                <input type="text" name="Drivers_License"
                                                    value="{{ $customer->Drivers_License }}">
                                            </div>

                                            <div class="info-row">
                                                <label>DL State</label>
                                                <input type="text" name="DL_State"
                                                    value="{{ $customer->DL_State }}">
                                            </div>

                                        </div>
                                    </div>

                                    {{-- OFFICE INFO --}}
                                    <div class="profile-section-box">
                                        <h3>Office Information</h3>

                                        <div class="details-grid">

                                            <div class="info-row">
                                                <label>Office</label>
                                                <input type="text" name="Office"
                                                    value="{{ $customer->Office }}">
                                            </div>

                                            <div class="info-row">
                                                <label>CID</label>
                                                <input type="text" name="CID" value="{{ $customer->CID }}">
                                            </div>

                                            <div class="info-row">
                                                <label>Agent of Record</label>
                                                <input type="text" name="Agent_of_Record"
                                                    value="{{ $customer->Agent_of_Record }}">
                                            </div>

                                            <div class="info-row">
                                                <label>Agency</label>
                                                <input type="text" name="Agency"
                                                    value="{{ $customer->Agency }}">
                                            </div>

                                            <div class="info-row">
                                                <label>Source</label>
                                                <input type="text" name="Source"
                                                    value="{{ $customer->Source }}">
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


                    <div id="note-overlay" class="note-overlay">
                        <div class="note-window">
                            <h2>Add Note</h2>

                            <label>Policy</label>
                            <input type="text" id="note-policy">

                            <label>Subject</label>
                            <input type="text" id="note-subject">

                            <label>Note</label>
                            <textarea id="note-text"></textarea>

                            <div class="overlay-actions">
                                <button class="btn secondary" id="note-cancel">Cancel</button>
                                <button class="btn" id="note-save">Save</button>
                            </div>
                        </div>
                    </div>

                </div>
        </section>
    </div>



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
