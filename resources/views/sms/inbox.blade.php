<!doctype html>
<html lang="es">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SMS Inbox</title>
    <link rel="icon" href="img/favicon.png">

    <!-- Archivos CSS -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/inbox.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sms-inbox.css') }}">

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
            <div id="lower-table-clients" type="fullscreen">
                <!-- <div class="inbox-container mt-10"> -->
                <div class="inbox-card">
                    <!-- <h1>📥 SMS Inbox</h1> -->
                    <div class="top-actions">
                        <button id="btnSync" class="btn secondary"><i class='bx bx-sync'></i></button>
                        <input id="search" placeholder="Buscar..."
                            style="margin-left:10px;padding:8px;border-radius:6px;border:1px solid #ddd" />
                        <button style="margin-left: auto;" id="newMessageBtn" class="btn secondary"><i
                                class='bx bxs-comment-add'></i> &nbsp;NUEVO MENSAJE</button>
                    </div>

                    <div class="sms-app">
                        <!-- Lista de contactos -->
                        <div class="sms-list">
                            <div class="top-actions" style="background-color:#ebeef5;">
                                <label
                                    style="margin-left:12px;margin-right:10px;display:flex;align-items:center;gap:6px;">
                                    <input type="checkbox" id="checkAll">
                                </label>
                                <button id="btnDeleteSelected" class="btn danger" disabled><i
                                        class='bx bxs-trash'></i></button>
                            </div>
                            <div id="contacts">
                                @forelse ($contacts as $c)
                                    <div class="sms-contact" data-contact="{{ $c['contact'] }}"
                                        data-last-at="{{ $c['last_at'] }}">
                                        <input type="checkbox" class="contact-check" value="{{ $c['contact'] }}"
                                            style="margin-right:8px;">
                                        <div class="meta" style="flex:1;">
                                            <div style="font-weight:600;text-align:left;">
                                                {{ preg_replace('/^1?(\d{3})(\d{3})(\d{4})$/', '+1 ($1) $2-$3', preg_replace('/\D+/', '', $c['contact'])) ?: $c['contact'] }}
                                            </div>
                                            <div class="last">{{ Str::limit($c['last_body'], 60) }}</div>
                                        </div>
                                        <div class="contact-date">
                                            {{ $c['last_at'] ? \Carbon\Carbon::parse($c['last_at'])->format('d/m/Y H:i') : '' }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="empty">No hay conversaciones. Presiona
                                        <strong>Actualizar</strong>
                                        para leer mensajes desde Twilio.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Panel de chat -->
                        <div class="sms-chat">
                            <div
                                style="height:33px;padding:12px;border-bottom:1px solid #eee; display:flex;align-items:center; gap:12px;">
                                <div id="currentContact" style="font-weight:400"></div>
                                <div style="margin-left:auto">
                                    <button id="btnDeleteConversation" class="btn btn-danger" disabled><i
                                            class='bx bx-trash-alt'></i> &nbsp;Delete this chat</button>
                                </div>
                            </div>

                            <div class="messages" id="messagesPane">
                                <div class="empty">Selecciona un contacto a la izquierda para ver su chat</div>
                                {{-- 
                                    Ejemplo para cuando cargues los mensajes:
                                    @foreach ($messages as $message)
                                        <div class="message-wrapper {{ $message->from == $twilio ? 'sent' : 'received' }}">
                                            <div class="message-box">
                                                {{ $message->body }}
                                            </div>
                                            <span class="message-date">
                                                {{ \Carbon\Carbon::parse($message->date_sent)->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                    @endforeach
                                    --}}
                            </div>

                            <div class="composer" id="composer" style="display:none;">
                                <form id="sendForm" style="display:flex;width:100%;gap:8px;align-items:center;">
                                    @csrf
                                    <input type="hidden" name="to" id="toInput" />
                                    <textarea name="body" id="bodyInput" placeholder="Escribe un mensaje..."></textarea>
                                    <button type="submit" class="btn"><i class='bx bxs-send'></i></button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Variables JS globales -->
                    <script>
                        window.twilioFrom = "{{ $twilio }}";
                        window.csrfToken = "{{ csrf_token() }}";
                        window.routes = {
                            sync: "{{ route('sms.sync') }}",
                            send: "{{ route('sms.send') }}",
                            deleteOne: "{{ route('sms.deleteOne', ':contact') }}",
                            deleteMany: "{{ route('sms.deleteMany') }}"
                        };
                    </script>
                    <script src="{{ asset('js/sms-inbox.js') }}"></script>

                </div>
                <!-- </div> -->
            </div>
        </section>
    </div>


    <!-- Overlay oscuro -->
    <div id="overlay"></div>

    <!-- Panel lateral para nuevo mensaje -->
    <div id="newMessagePanel">
        <button id="closeNewMessage" title="Cerrar">&times;</button>
        <h3>NEW MESSAGE</h3>

        <form id="newMessageForm">
            @csrf
            <label for="newTo">To (+1):</label>
            <input type="text" id="newTo" name="to" placeholder="+12144696789" required>

            <label for="newBody">Message:</label>
            <textarea id="newBody" name="body" placeholder="Type a message..." required></textarea>

            <button type="submit">SEND</button>
        </form>
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
    <script src="js/weather.js"></script>
    <script src="js/dropdown.js"></script>
    <script src="js/menu.js"></script>
    <script src="js/table.js"></script>
    <script src="js/settings.js"></script>
    <script src="js/operations.js"></script>
    <script src="js/inbox.js"></script>


</body>

</html>
