<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Policies · CRM</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">
    <link rel="stylesheet" href="{{ asset('css/company.css') }}">
    <link rel="stylesheet" href="{{ asset('css/help.css') }}">

    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/policies.css') }}">

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

            <div id="dash-content">

                <div class="main-container">



                    <div id="profile-wrapper">

                        {{-- MENU LATERAL --}}
                        <aside class="profile-side-menu">
                            <div class="profile-side-header">
                                <i class='bx bx-user-circle'></i>
                                <div class="profile-side-title">
                                    <span>Customer</span>
                                    <strong>{{ $customer->Name }}</strong>
                                </div>
                            </div>

                            <nav class="profile-side-nav">
                                <button type="button" class="profile-menu-item"
                                    onclick="window.location.href='{{ route('profile', $customer->ID) }}'">
                                    <i class='bx bx-id-card'></i>
                                    <span>Profile</span>
                                </button>

                                <button type="button" class="profile-menu-item active">
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

                        {{-- CONTENIDO PRINCIPAL --}}
                        <div class="profile-main">

                            <div class="policies-header">
                                <h2>Policies</h2>

                                <button id="new-policy-btn" class="btn policies-new-btn">
                                    <i class='bx bx-plus'></i> New Policy
                                </button>
                            </div>

                            {{-- CONFIG PARA JS --}}
                            <div id="policy-config" data-store-url="{{ route('policies.store', $customer->ID) }}"
                                data-csrf="{{ csrf_token() }}">
                            </div>

                            {{-- TABLA --}}
                            <table class="table policies-table">
                                <thead>
                                    <tr>
                                        <th>Carrier</th>
                                        <th>Number</th>
                                        <th>Expiration</th>
                                        <th>Vehicle</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($policies as $p)
                                        @php
                                            $veh = $p->vehicules;

                                            if (is_string($veh)) {
                                                $veh = json_decode($veh, true);
                                            }

                                            $first = $veh[0] ?? null;
                                        @endphp

                                        <tr>
                                            <td>{{ $p->pol_carrier }}</td>
                                            <td>{{ $p->pol_number }}</td>
                                            <td>{{ $p->pol_expiration }}</td>

                                            <td>
                                                @if ($first)
                                                    {{ $first['year'] ?? '' }}
                                                    {{ $first['make'] ?? '' }}
                                                    {{ $first['model'] ?? '' }}
                                                @else
                                                    -
                                                @endif
                                            </td>

                                            <td>
                                                <button class="btn delete-btn policy-delete-btn"
                                                    data-url="{{ route('policies.destroy', $p->id) }}">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="text-align:center;opacity:0.6;">
                                                No policies yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>

                        </div>
                    </div>





                    <div id="policy-overlay">
                        <div class="policy-overlay-box policy-flex">

                            <h3>New Policy</h3>

                            <div class="policy-columns">

                                {{-- LEFT PANEL (POLICY FIELDS) --}}
                                <div class="policy-left">

                                    <label>Pol Carrier</label>
                                    <input type="text" id="pol_carrier">

                                    <label>Pol Number</label>
                                    <input type="text" id="pol_number">

                                    <label>Pol URL (company website)</label>
                                    <input type="text" id="pol_url">

                                    <label>Pol Expiration</label>
                                    <input type="date" id="pol_expiration">

                                    <label>Pol Eff Date</label>
                                    <input type="date" id="pol_eff_date">

                                    <label>Pol Added Date</label>
                                    <input type="date" id="pol_added_date">

                                    <label>Pol Due Day</label>
                                    <input type="text" id="pol_due_day">

                                    <label>Pol Status</label>
                                    <input type="text" id="pol_status">

                                    <label>Pol Agent Record</label>
                                    <input type="text" id="pol_agent_record">

                                    <div class="policy-overlay-actions">
                                        <button id="policy-save-btn" class="btn policy-save-btn">Save</button>
                                        <button id="policy-cancel-btn" class="btn secondary">Cancel</button>
                                    </div>

                                </div>

                                {{-- RIGHT PANEL (VEHICLES) --}}
                                <div class="policy-right">

                                    <button id="add-vehicle-btn" class="btn add-vehicle-btn">
                                        + Añadir Vehículo
                                    </button>

                                    <div id="vehicle-container" class="vehicle-container">
                                        {{-- Vehicle cards generated by JS --}}
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
    </div>
    </section>
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

    <script src="{{ asset('js/policies.js') }}"></script>
</body>

</html>
