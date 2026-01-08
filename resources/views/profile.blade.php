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

                <div id="profile-submenu">
                    
                </div>

                <div class="inbox-card" style="padding:30px 40px;max-width:800px;margin:auto;">
                    <h2 style="margin-bottom:20px;">Customer Profile</h2>

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

                        <div style="margin-top:20px;display:flex;gap:12px;">
                            <button type="submit" class="btn"
                                style="background:#2ecc71;border:none;color:#fff;padding:8px 14px;border-radius:6px;">Save</button>
                            <a href="{{ route('customers.index') }}" class="btn secondary"
                                style="padding:8px 14px;border-radius:6px;">Back</a>
                            <button type="button" id="delete-customer-btn" class="btn delete-btn"
                                data-id="{{ $customer->ID }}">Delete</button>

                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Selecciona todos los elementos con atributo data="option"
            document.querySelectorAll('[data="option"]').forEach(option => {
                option.addEventListener('click', function(e) {
                    const onclickAttr = this.getAttribute('onclick');
                    if (onclickAttr && onclickAttr.includes("window.location='./")) {
                        e.preventDefault();
                        // Extrae el destino del onclick actual
                        const dest = onclickAttr.match(/'\.\/(.*?)'/);
                        if (dest && dest[1]) {
                            // Redirige de forma absoluta
                            window.location = '/' + dest[1];
                        }
                    }
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
                                        // Redirige a la lista de clientes
                                        window.location.href =
                                            `${baseUrl}/customers`;
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
                        <label class="thumb-options" onclick="selectImage(1)">
                            <img src="{{ asset('img/menu/thumbs/1.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(2)">
                            <img src="{{ asset('img/menu/thumbs/2.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(3)">
                            <img src="{{ asset('img/menu/thumbs/3.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(4)">
                            <img src="{{ asset('img/menu/thumbs/4.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(5)">
                            <img src="{{ asset('img/menu/thumbs/5.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(6)">
                            <img src="{{ asset('img/menu/thumbs/6.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(7)">
                            <img src="{{ asset('img/menu/thumbs/7.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(8)">
                            <img src="{{ asset('img/menu/thumbs/8.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(9)">
                            <img src="{{ asset('img/menu/thumbs/9.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(10)">
                            <img src="{{ asset('img/menu/thumbs/10.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(11)">
                            <img src="{{ asset('img/menu/thumbs/11.jpg') }}" alt="">
                        </label>
                        <label class="thumb-options" onclick="selectImage(12)">
                            <img src="{{ asset('img/menu/thumbs/12.jpg') }}" alt="">
                        </label>



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


    <!-- <script src="js/main.js"></script> -->
    <script src="{{ asset('js/image.js') }}"></script>
    <script src="{{ asset('js/weather.js') }}"></script>
    <script src="{{ asset('js/dropdown.js') }}"></script>
    <script src="{{ asset('js/menu.js') }}"></script>
    <script src="{{ asset('js/table.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/operations.js') }}"></script>



</body>

</html>
