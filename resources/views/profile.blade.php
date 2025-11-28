<!doctype html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Customer Profile</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

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

                {{-- CONTENEDOR GENERAL DEL PROFILE (MENU LATERAL INTERNO + CONTENIDO) --}}
                <div id="profile-wrapper">
                    {{-- MENU LATERAL INTERNO --}}
                    <aside class="profile-side-menu">
                        <div class="profile-side-header">
                            <i class='bx bx-user-circle'></i>
                            <div class="profile-side-title">
                                <span>Customer</span>
                                <strong>{{ $customer->Name ?? 'Profile' }}</strong>
                            </div>
                        </div>

                        <nav class="profile-side-nav">
                            <button type="button" class="profile-menu-item active">
                                <i class='bx bx-id-card'></i>
                                <span>Profile</span>
                            </button>

                            <button type="button" class="profile-menu-item">
                                <i class='bx bx-shield-quarter'></i>
                                <span>Policies</span>
                            </button>

                            <button type="button" class="profile-menu-item">
                                <i class='bx bx-credit-card'></i>
                                <span>Invoices (Payments)</span>
                            </button>

                            <button type="button" class="profile-menu-item">
                                <i class='bx bx-task'></i>
                                <span>Reminders/Tasks</span>
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

                    {{-- CONTENIDO PRINCIPAL DEL PROFILE --}}
                    <div class="profile-main">
                        <div class="inbox-card profile-card">
                            <h2 class="profile-title">Customer Profile</h2>

                            <form method="POST" action="{{ route('customers.update', $customer->ID) }}">
                                @csrf
                                @method('PUT')

                                <label>Name</label>
                                <input type="text" name="Name" value="{{ old('Name', $customer->Name) }}">

                                <label>Phone</label>
                                <input type="text" name="Phone" value="{{ old('Phone', $customer->Phone) }}">

                                <label>Email1</label>
                                <input type="email" name="Email1" value="{{ old('Email1', $customer->Email1) }}">

                                <label>Email2</label>
                                <input type="email" name="Email2" value="{{ old('Email2', $customer->Email2) }}">

                                <label>Address</label>
                                <input type="text" name="Address" value="{{ old('Address', $customer->Address) }}">

                                <label>City</label>
                                <input type="text" name="City" value="{{ old('City', $customer->City) }}">

                                <label>State</label>
                                <input type="text" name="State" value="{{ old('State', $customer->State) }}">

                                <label>ZIP_Code</label>
                                <input type="text" name="ZIP_Code" value="{{ old('ZIP_Code', $customer->ZIP_Code) }}">

                                <label>Drivers_License</label>
                                <input type="text" name="Drivers_License"
                                    value="{{ old('Drivers_License', $customer->Drivers_License) }}">

                                <label>DL_State</label>
                                <input type="text" name="DL_State" value="{{ old('DL_State', $customer->DL_State) }}">

                                <label>DOB</label>
                                <input type="date" name="DOB" value="{{ old('DOB', $customer->DOB) }}">

                                <label>Source</label>
                                <input type="text" name="Source" value="{{ old('Source', $customer->Source) }}">

                                <label>Office</label>
                                <input type="text" name="Office" value="{{ old('Office', $customer->Office) }}">

                                <label>Marital</label>
                                <input type="text" name="Marital" value="{{ old('Marital', $customer->Marital) }}">

                                <label>Gender</label>
                                <input type="text" name="Gender" value="{{ old('Gender', $customer->Gender) }}">

                                <label>CID</label>
                                <input type="text" name="CID" value="{{ old('CID', $customer->CID) }}">

                                <label>Added</label>
                                <input type="text" name="Added" value="{{ old('Added', $customer->Added) }}">

                                <label>Agent_of_Record</label>
                                <input type="text" name="Agent_of_Record"
                                    value="{{ old('Agent_of_Record', $customer->Agent_of_Record) }}">

                                <label>Alert</label>
                                <textarea name="Alert">{{ old('Alert', $customer->Alert) }}</textarea>

                                <label>Picture (URL)</label>
                                <input type="text" name="Picture" value="{{ old('Picture', $customer->Picture) }}">

                                <label>Agency</label>
                                <input type="text" name="Agency" value="{{ old('Agency', $customer->Agency) }}">

                                <div class="profile-actions">
                                    <button type="submit" class="btn profile-btn-save">
                                        Save
                                    </button>
                                    <a href="{{ route('customers.index') }}" class="btn secondary profile-btn-back">
                                        Back
                                    </a>
                                    <button type="button" id="delete-customer-btn"
                                        class="btn delete-btn profile-btn-delete"
                                        data-id="{{ $customer->ID }}">
                                        Delete
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> {{-- /#profile-wrapper --}}

            </div>
        </section>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Mantener la lógica existente para data="option"
            document.querySelectorAll('[data="option"]').forEach(option => {
                option.addEventListener('click', function(e) {
                    const onclickAttr = this.getAttribute('onclick');
                    if (onclickAttr && onclickAttr.includes("window.location='./")) {
                        e.preventDefault();
                        const dest = onclickAttr.match(/'\.\/(.*?)'/);
                        if (dest && dest[1]) {
                            window.location = '/' + dest[1];
                        }
                    }
                });
            });

            // NUEVO: marcar activo el botón del menú lateral interno (sin redirecciones)
            const profileMenuItems = document.querySelectorAll('.profile-menu-item');
            profileMenuItems.forEach(btn => {
                btn.addEventListener('click', () => {
                    profileMenuItems.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                });
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const deleteBtn = document.getElementById('delete-customer-btn');
            const baseUrl = document.querySelector('meta[name="base-url"]').content;

            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const customerId = this.getAttribute('data-id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `${baseUrl}/customers/delete-multiple`,
                                type: 'POST',
                                data: {
                                    ids: [customerId],
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(response) {
                                    Swal.fire(
                                        'Deleted!',
                                        'Customer has been deleted.',
                                        'success'
                                    ).then(() => {
                                        window.location.href = `${baseUrl}/customers`;
                                    });
                                },
                                error: function(xhr, status, error) {
                                    Swal.fire(
                                        'Error!',
                                        'There was a problem deleting this customer.',
                                        'error'
                                    );
                                    console.error(error);
                                }
                            });
                        }
                    });
                });
            }
        });
    </script>

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

    {{-- Scripts --}}
    <script src="{{ asset('js/image.js') }}"></script>
    <script src="{{ asset('js/weather.js') }}"></script>
    <script src="{{ asset('js/dropdown.js') }}"></script>
    <script src="{{ asset('js/menu.js') }}"></script>
    <script src="{{ asset('js/table.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/operations.js') }}"></script>
    <script src="{{ asset('js/help.js') }}"></script>

</body>

</html>
