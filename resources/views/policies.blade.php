<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Policies</title>
        <link rel="icon" href="{{ asset('img/favicon.png') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="base-url" content="{{ url('/') }}">
        <meta name="customer-id" content="{{ $customer->ID }}">


        <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
        <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
        <link rel="stylesheet" href="{{ asset('css/main.css') }}">
        <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
        <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
        <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
        <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
        <!-- <link rel="stylesheet" href="{{ asset('css/sms-inbox.css') }}"> -->
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

                                <button type="button" class="profile-menu-item active"
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

                                <button type="button" class="profile-menu-item"
                                    onclick="window.location.href='{{ route('profile.documents', $customer->ID) }}'">
                                    <i class='bx bx-file'></i>
                                    <span>Documents</span>
                                </button>

                            </nav>
                        </aside>

                        {{-- ⭐ NOTES – FUERA DEL MENÚ, STICKY ⭐ --}}
                        <div class="profile-notes sticky-notes">

                            <div class="notes-header">
                                <h3>Notes</h3>
                                <button id="add-note-btn" class="btn small"><i class='bx bx-message-alt-add'></i>
                                    &nbsp;Add
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

                    {{-- CONTENIDO PRINCIPAL --}}
                    <div class="profile-main">

                        <div class="policies-header">
                            <div style="display:flex; gap:10px; align-items:center;">
                                <button id="new-policy-btn" class="btn policies-new-btn" type="button">
                                    <i class='bx bx-file' style="font-size:1.2em;margin-right:5px;"></i> New Policy
                                </button>

                                <button id="policy-log-btn" class="btn policies-new-btn" type="button">
                                    <i class='bx bx-notepad' style="font-size:1.2em;margin-right:5px;"></i> Policy Log
                                </button>
                            </div>
                        </div>

                        {{-- CONFIG PARA JS --}}
                        <div id="policy-config" data-store-url="{{ route('policies.store', $customer->ID) }}"
                            data-csrf="{{ csrf_token() }}">
                        </div>

                        {{-- TABLA --}}
                        <table class="table policies-table">
                            <thead style="border-top-right-radius: 12px;border-top-left-radius: 12px;">
                                <tr>
                                    <th>Carrier</th>
                                    <th>Number</th>
                                    <th>Expiration</th>
                                    <th>Status</th>
                                    <th>Vehicles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($policies as $p)
                                    @php
                                        // Convertir JSON a arreglo
                                        $veh = $p->vehicules;

                                        if (is_string($veh)) {
                                            $veh = json_decode($veh, true);
                                        }

                                        $vehicleCount = is_array($veh) ? count($veh) : 0;
                                    @endphp

                                    <tr>
                                        <td>{{ $p->pol_carrier }}</td>
                                        <td>{{ $p->pol_number }}</td>
                                        <td>{{ $p->pol_expiration }}</td>

                                        {{--  Pol Status --}}
                                        <td>{{ $p->pol_status ?? '-' }}</td>

                                        {{--  Número de vehículos --}}
                                        <td>
                                            @if ($vehicleCount === 0)
                                                0
                                            @elseif($vehicleCount === 1)
                                                1
                                            @else
                                                {{ $vehicleCount }}
                                            @endif
                                        </td>

                                        <td style="display:flex;justify-content:center">

                                            {{-- Botón INFO (i en un círculo) --}}
                                            <button class="btn policy-info-btn" title="View / Edit"
                                                id="edit-policy-btn" data-id="{{ $p->id }}"
                                                data-url="{{ route('policies.show', $p->id) }}"
                                                data-update-url="{{ route('policies.update', $p->id) }}">
                                                <i class='bx bx-info-circle'></i>
                                            </button>


                                            <button class="btn delete-btn policy-delete-btn" id="delete-policy-btn"
                                                data-url="{{ route('policies.destroy', $p->id) }}">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </td>
                                    </tr>

                                @empty
                                    <tr>
                                        <td colspan="6" style="text-align:center;opacity:0.6;">
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


                        <div class="policy-columns">

                            {{-- LEFT PANEL (POLICY FIELDS) --}}
                            <div class="policy-left">

                                <label>Carrier</label>
                                <input type="text" id="pol_carrier">

                                <label>Number</label>
                                <input type="text" id="pol_number">

                                <label>URL (Company Website)</label>
                                <input type="text" id="pol_url">

                                <label>Expiration Date</label>
                                <input type="date" id="pol_expiration">

                                <label>Effective Date</label>
                                <input type="date" id="pol_eff_date">

                                <label>Added Date</label>
                                <input type="date" id="pol_added_date">

                                <label>Payment Due Day</label>
                                <input type="text" id="pol_due_day">

                                <label>Status</label>
                                <input type="text" id="pol_status" value="Active" readonly>

                                <label>Agent Record</label>
                                <input type="text" id="pol_agent_record">

                                <div class="policy-overlay-actions">
                                    <button id="policy-save-btn" class="btn policy-save-btn">Save</button>
                                    <button id="policy-cancel-btn" class="btn secondary">Cancel</button>
                                </div>

                            </div>

                            {{-- RIGHT PANEL (VEHICLES) --}}
                            <div class="policy-right">

                                <h3>New Policy</h3>

                                <div
                                    style="display:flex; flex-wrap:wrap; gap:10px; margin-left:10px; margin-bottom:10px;">
                                    <button id="add-vehicle-btn" class="btn add-vehicle-btn" type="button"
                                        style="margin:0;">
                                        <i class='bx bx-car' style="font-size:1.4em"></i>&nbsp; Añadir Vehículo
                                    </button>
                                </div>

                                <div id="vehicle-container" class="vehicle-container">
                                    {{-- Vehicle cards generated by JS --}}
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div id="policy-edit-overlay" class="policy-edit-overlay" style="display:none;">
                    <div class="policy-edit-box">

                        <div id="policy-edit-content">
                            <h3>Policy</h3>
                            <!-- Aquí JS insertará todos los campos -->
                        </div>

                        <div class="policy-edit-actions">
                            <button id="policy-edit-save" class="btn">Save Changes</button>
                            <button id="policy-edit-cancel" class="btn secondary">Close</button>
                        </div>
                    </div>
                </div>

                <div id="policy-log-overlay" class="policy-log-overlay" style="display:none;">
                    <div class="policy-log-box">
                        <div class="policy-log-header">
                            <h2>Policy Log</h2>
                            <button type="button" id="policy-log-close" class="btn secondary">Close</button>
                        </div>

                        <textarea id="policy-log-text" readonly>
@forelse($policyLog as $log)
@php($snapshot = $log['snapshot'] ?? [])

[{{ $log['created_at'] ?? '-' }}] {{ $log['action_text'] ?? 'Updated' }}
By: {{ $log['changed_by'] ?? 'System' }}
Policy: {{ $snapshot['pol_number'] ?? '-' }}
Status: {{ $snapshot['pol_status'] ?? 'Active' }}
EffDate: {{ $snapshot['pol_eff_date'] ?? '-' }}
ExpDate: {{ $snapshot['pol_expiration'] ?? '-' }}
PayDate: {{ $snapshot['pol_due_day'] ?? '-' }}
Carrier: {{ $snapshot['pol_carrier'] ?? '-' }}
URL: {{ $snapshot['pol_url'] ?? '-' }}
Agent Record: {{ $snapshot['pol_agent_record'] ?? '-' }}
Changed Fields: {{ $log['changed_fields_text'] ?? '-' }}

@empty
No policy logs found.
@endforelse
</textarea>

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

        {{-- Scripts --}}
        <script src="{{ asset('js/image.js') }}"></script>
        <script src="{{ asset('js/weather.js') }}"></script>
        <script src="{{ asset('js/dropdown.js') }}"></script>
        <script src="{{ asset('js/menu.js') }}"></script>
        <script src="{{ asset('js/table.js') }}"></script>
        <script src="{{ asset('js/settings.js') }}"></script>
        <script src="{{ asset('js/operations.js') }}"></script>
        <script src="{{ asset('js/help.js') }}"></script>
        <script src="{{ asset('js/profile.js') }}"></script>
        <script src="{{ asset('js/policies.js') }}"></script>

    </body>

</html>
