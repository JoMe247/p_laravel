{{-- resources/views/office.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Office · Sub Users</title>
    <link rel="icon" href="img/favicon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/office.css') }}">

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body class="dark">
    <div id="main-container">
        @include('menu')

        <div class="office-logo-box">
            <form action="{{ route('office.uploadLogo') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="logo-preview">
                    <img src="{{ $agencyData->agency_logo ? asset('storage/' . $agencyData->agency_logo) : asset('img/default-logo.png') }}"
                        alt="Logo Agencia">
                </div>

                <label class="btn secondary upload-btn">
                    Subir Logo
                    <input type="file" name="agency_logo" accept="image/*" onchange="this.form.submit()">
                </label>
            </form>
        </div>


        {{-- FORMULARIO DE AGENCIA (arriba a la derecha) --}}
        <div class="agency-info-box">

            <h3>Datos de Agencia</h3>

            {{-- Código de Agencia heredado (solo lectura) --}}
            <div class="agency-row">
                <label>Código de Agencia:</label>
                <input type="text" value="{{ $agency }}" disabled>
            </div>

            {{-- Número Twilio heredado (solo lectura) --}}
            <div class="agency-row">
                <label>Número Twilio:</label>
                <input type="text" value="{{ $twilioNumber }}" disabled>
            </div>

            <form action="{{ route('agency.save') }}" method="POST">
                @csrf

                {{-- Código de agencia que se usará para guardar --}}
                <input type="hidden" name="agency_code" value="{{ $agency }}">

                <div class="agency-row">
                    <label>Teléfono Oficina</label>
                    <input type="text" name="office_phone" value="{{ $agencyData->office_phone ?? '' }}">
                </div>

                <div class="agency-row">
                    <label>Dirección Agencia</label>
                    <input type="text" name="agency_address" value="{{ $agencyData->agency_address ?? '' }}">
                </div>

                <div class="agency-row">
                    <label>Nombre Agencia</label>
                    <input type="text" name="agency_name" value="{{ $agencyData->agency_name ?? '' }}" required>
                </div>

                <div class="agency-row">
                    <label>Correo Agencia</label>
                    <input type="email" name="agency_email" value="{{ $agencyData->agency_email ?? '' }}" required>
                </div>


                <button class="btn primary" type="submit">Guardar</button>
            </form>
        </div>

        <div class="container">

            <div class="office-topbar">
                <button id="btn-open-overlay" class="btn primary"
                    @if (auth('sub')->check()) disabled title="Los sub users no pueden agregar sub users"
                    @elseif ($isUserLimitReached)
        disabled title="Límite de usuarios alcanzado para este plan" @endif>
                    <i class='bx bx-user-plus'></i> Agregar Sub-User
                </button>

            </div>

            <div class="office-table-wrapper">
                <table class="office-table">
                    <thead>
                        <tr>
                            <th>Nombre de Usuario</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Tipo</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td>{{ $member->username }}</td>
                                <td>{{ $member->name }}</td>
                                <td>{{ $member->email }}</td>
                                <td>{{ $member->tipo }}</td>
                                <td>
                                    @if ($member->tipo === 'Usuario')
                                        @if (auth('web')->check())
                                            <div style="display:flex; gap:10px; align-items:center;">
                                                <!-- EDIT -->
                                                <button type="button" class="btn-edit-subuser" title="Editar"
                                                    data-id="{{ $member->id }}"
                                                    data-username="{{ $member->username }}"
                                                    data-name="{{ $member->name }}" data-email="{{ $member->email }}">
                                                    <i class='bx bxs-edit'></i>
                                                </button>

                                                <!-- DELETE -->
                                                <form method="POST"
                                                    action="{{ route('office.delete', ['id' => $member->id]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn-delete" type="submit">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <div style="display:flex; gap:10px; align-items:center;">
                                                <button class="btn-edit-subuser" disabled
                                                    title="Los sub users no pueden editar usuarios.">
                                                    <i class='bx bx-edit-alt'></i>
                                                </button>

                                                <button class="btn-delete" disabled
                                                    title="Los sub users no pueden eliminar usuarios.">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            </div>
                                        @endif
                                    @else
                                        <span class="admin-lock"><i class='bx bx-lock'></i></span>
                                    @endif


                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Overlay para registrar Sub-User -->
            <div id="overlay-subuser">
                <div class="overlay-content">
                    <i class='bx bx-x overlay-close' id="close-overlay"></i>
                    <h2 id="overlay-title">Registrar Sub-User</h2>


                    <form id="subuser-form" method="POST" action="{{ route('office.store') }}" class="card">
                        @csrf

                        <div class="form-row" id="row-username">
                            <label>Username</label>
                            <input id="subuser-username" type="text" name="username" required>
                        </div>

                        <div class="form-row">
                            <label>Nombre</label>
                            <input id="subuser-name" type="text" name="name" required>
                        </div>

                        <div class="form-row">
                            <label>Email</label>
                            <input id="subuser-email" type="email" name="email" required>
                        </div>

                        <div class="form-row" id="row-password">
                            <label>Password</label>
                            <input id="subuser-password" type="password" name="password" required>
                        </div>


                        @isset($agency)
                            <div class="form-row">
                                <label>Agency</label>
                                <input type="text" value="{{ $agency }}" disabled>
                            </div>
                        @endisset

                        @isset($twilioNumber)
                            <div class="form-row">
                                <label>Twilio From</label>
                                <input type="text" value="{{ $twilioNumber }}" disabled />
                            </div>
                        @endisset

                        <div class="overlay-actions">
                            <button id="subuser-submit-btn" type="submit" class="btn primary">Registrar</button>

                        </div>
                    </form>
                </div>
            </div>


        </div>
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


    <script src="js/image.js"></script>
    <script src="js/dropdown.js"></script>
    <script src="js/menu.js"></script>
    <script src="js/table.js"></script>
    <script src="js/settings.js"></script>
    <script src="js/operations.js"></script>




    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnOpen = document.getElementById('btn-open-overlay');
            const btnClose = document.getElementById('close-overlay');
            const overlay = document.getElementById('overlay-subuser');

            if (!overlay) return;

            const baseUrlMeta = document.querySelector('meta[name="base-url"]');
            const baseUrl = baseUrlMeta ? baseUrlMeta.getAttribute('content') : '';

            const form = document.getElementById('subuser-form');
            const title = document.getElementById('overlay-title');
            const submitBtn = document.getElementById('subuser-submit-btn');

            const rowUsername = document.getElementById('row-username');
            const rowPassword = document.getElementById('row-password');

            const inputUsername = document.getElementById('subuser-username');
            const inputName = document.getElementById('subuser-name');
            const inputEmail = document.getElementById('subuser-email');
            const inputPassword = document.getElementById('subuser-password');

            function openOverlay() {
                overlay.classList.add('show');
            }

            function closeOverlay() {
                overlay.classList.remove('show');
            }

            function removeMethodSpoof() {
                if (!form) return;
                const m = form.querySelector('input[name="_method"]');
                if (m) m.remove();
            }

            function setCreateMode() {
                if (title) title.textContent = 'Registrar Sub-User';
                if (submitBtn) submitBtn.textContent = 'Registrar';

                if (form) {
                    form.action = "{{ route('office.store') }}";
                    removeMethodSpoof();
                }

                // Username visible y editable
                if (rowUsername) rowUsername.style.display = '';
                if (inputUsername) {
                    inputUsername.disabled = false;
                    inputUsername.readOnly = false;
                    inputUsername.required = true;
                    inputUsername.value = '';
                }

                // Password requerido en create
                if (rowPassword) rowPassword.style.display = '';
                if (inputPassword) {
                    inputPassword.required = true;
                    inputPassword.value = '';
                }

                if (inputName) inputName.value = '';
                if (inputEmail) inputEmail.value = '';
            }

            function setEditMode(data) {
                if (title) title.textContent = 'Editar Sub-User';
                if (submitBtn) submitBtn.textContent = 'Guardar cambios';

                if (form) {
                    form.action = baseUrl ? `${baseUrl}/office/${data.id}` : `/office/${data.id}`;
                    removeMethodSpoof();

                    const method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'PUT';
                    form.appendChild(method);
                }

                // Username visible SOLO LECTURA
                if (rowUsername) rowUsername.style.display = '';
                if (inputUsername) {
                    inputUsername.required = false;
                    inputUsername.disabled = true; // no se envía
                    inputUsername.readOnly = true; // visual
                    inputUsername.value = data.username || '';
                }

                // Password opcional en edit
                if (rowPassword) rowPassword.style.display = '';
                if (inputPassword) {
                    inputPassword.required = false;
                    inputPassword.value = '';
                }

                if (inputName) inputName.value = data.name || '';
                if (inputEmail) inputEmail.value = data.email || '';
            }

            // Abrir overlay (crear)
            if (btnOpen) {
                btnOpen.addEventListener('click', () => {
                    setCreateMode();
                    openOverlay();
                });
            }

            // Cerrar overlay
            if (btnClose) btnClose.addEventListener('click', closeOverlay);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeOverlay();
            });

            // Abrir overlay (editar)
            document.addEventListener('click', function(e) {
                const btn = e.target.closest('.btn-edit-subuser');
                if (!btn || btn.disabled) return;

                const data = {
                    id: btn.getAttribute('data-id'),
                    username: btn.getAttribute('data-username'),
                    name: btn.getAttribute('data-name'),
                    email: btn.getAttribute('data-email'),
                };

                setEditMode(data);
                openOverlay();
            });
        });
    </script>



</body>

</html>
