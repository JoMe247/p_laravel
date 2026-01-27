<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <link rel="icon" href="img/favicon.png">

    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/account.css') }}">

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    {{-- Menú lateral --}}
    <div id="main-container">
        @include('menu')

        <section id="dash">

            <div id="dash-content">

                <div id="account-content-inner">

                    <h1 style="color:var(--red1);">Agency {{ $agency->agency_code }}</h1>
                    <h2>Plan actual: {{ $plan->account_type }}</h2>

                    {{-- ======================================================
                        BLOQUE ACTUAL (USO REAL DEL PLAN ACTIVO)
                    ======================================================= --}}
                    <div class="account-grid">

                        {{-- SMS --}}
                        <div class="account-card">
                            <h3>Mensajes SMS</h3>
                            <p style="font-size:1em;padding-top:0px;"><b>Twilio Number:</b> {{ $twilioNumber }}</p>
                            <p style="font-size:1em;"><b>Enviados HOY:</b> {{ $dailySmsCount }}</p>
                            <p style="font-size:1em;padding-bottom:10px;"><b>Enviados este mes:</b> {{ $monthlySmsCount }} / {{ $smsLimit }}</p>

                            @if ($isSmsOverLimit)
                                <div class="account-alert">
                                    ⚠ Has excedido tu límite mensual de mensajes.
                                </div>
                            @endif
                        </div>

                        {{-- DOCUMENTOS --}}
                        <div class="account-card">
                            <h3>e-Sign Docs</h3>
                            <p style="padding-top:15px"><b style="font-size:1.2em"> {{ $monthlyDocCount }} / {{ $docLimit }}</b> </p>

                            @if ($isDocsOverLimit)
                                <div class="account-alert">
                                    ⚠ Has excedido el límite mensual de documentos.
                                </div>
                            @endif
                        </div>

                        {{-- USUARIOS --}}
                        <div class="account-card">
                            <h3>Usuarios</h3>
                            <p><b>Usuarios creados:</b> {{ $totalUsers }} / {{ $userLimit }}</p>

                            @if ($isUserOverLimit)
                                <div class="account-alert">
                                    ⚠ Límite de usuarios alcanzado.
                                </div>
                            @endif
                        </div>

                    </div>


                    {{-- ======================================================
                        PLANES DISPONIBLES (3 COLUMNAS / 3 MINI CARDS)
                    ======================================================= --}}
                    <h2 style="margin-top:20px;color:var(--red1);font-size:2em">Planes disponibles</h2>

                    <div class="plans-row">
                        @foreach($allPlans as $p)
                            @php $isCurrent = ($p->account_type === $currentAccountType); @endphp

                            <div class="plan-col {{ $isCurrent ? 'is-current' : '' }}">

                                <div class="plan-col-header">
                                    <div class="plan-title">{{ $p->account_type }}</div>

                                    @if($isCurrent)
                                        <div class="plan-badge">PLAN ACTUAL</div>
                                    @endif
                                </div>

                                <div class="plan-mini-stack">

                                    <div class="account-card mini-card">
                                        <h4><i class='bx bxs-message' ></i> Mensajes SMS</h4>
                                        <p>Límite mensual: <e style="color:var(--red1);font-weight:bold;">{{ (int)$p->msg_limit }}</e></p>
                                    </div>

                                    <div class="account-card mini-card">
                                        <h4><i class='bx bx-pencil' ></i> e-Sign Docs</h4>
                                        <p>Límite mensual: <e style="color:var(--red1);font-weight:bold;">{{ (int)$p->doc_limit }}</e></p>
                                    </div>

                                    <div class="account-card mini-card">
                                        <h4><i class='bx bxs-user'></i> Usuarios</h4>
                                        <p>Límite total: <e style="color:var(--red1);font-weight:bold;">{{ (int)$p->user_limit }}</e></p>
                                    </div>

                                    @if(str_contains($p->account_type, 'Pro'))
                                        <div class="account-card mini-card">
                                            <h4><i class='bx bx-globe'></i> Website Setup</h4>
                                        </div>
                                    @endif

                                    <div class="account-card mini-card">
                                        <h4><i class='bx bx-support' ></i> Customer Support</h4>
                                    </div>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div id="upgrade-button">Cambiar Plan</div>

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

</body>

</html>
