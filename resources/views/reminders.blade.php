<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reminders</title>
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

    <!-- Estilos de esta vista -->
    <link rel="stylesheet" href="{{ asset('css/reminders.css') }}">

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

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-credit-card'></i>
                                    <span>Invoices (Payments)</span>
                                </button>

                                <button type="button" class="profile-menu-item active"
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

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-map'></i>
                                    <span>Map</span>
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

                    <div class="reminders-page">

                        <div class="reminders-header">
                            <div class="left">
                                <h1 class="title">Reminders</h1>

                                @if (session('success'))
                                    <div class="flash-success">{{ session('success') }}</div>
                                @endif
                            </div>

                            <div class="right">
                                <button id="openReminderOverlay" class="btn-primary">
                                    Set Reminder
                                </button>

                                <div class="perpage-wrap">
                                    <label class="perpage-label" for="perPageSelect">Show</label>
                                    <select id="perPageSelect" class="perpage-select">
                                        <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10
                                        </option>
                                        <option value="20" {{ (int) $perPage === 20 ? 'selected' : '' }}>20
                                        </option>
                                        <option value="40" {{ (int) $perPage === 40 ? 'selected' : '' }}>40
                                        </option>
                                        <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="reminders-card">
                            <div class="table-topbar">
                                <div class="search-wrap">
                                    <form method="GET" action="{{ route('reminders.index', $customer->ID) }}"
                                        class="search-form">
                                        <input type="text" name="q" value="{{ $q }}"
                                            class="search-input" placeholder="Search in reminders..."
                                            autocomplete="off">
                                        <input type="hidden" name="perPage" value="{{ (int) $perPage }}">
                                        <button class="search-btn" type="submit">Search</button>
                                    </form>
                                </div>
                            </div>

                            <div class="table-wrap">
                                <table class="reminders-table">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th class="col-date">Date</th>
                                            <th class="col-remind">Remind</th>
                                            <th class="col-notified">Is notified?</th>
                                            <th class="col-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($reminders as $r)
                                            <tr>
                                                <td class="td-desc">{{ $r->description }}</td>
                                                <td class="td-date">
                                                    {{ optional($r->remind_at)->format('Y-m-d H:i') }}
                                                </td>
                                                <td class="td-remind">{{ $r->remind_name ?? '—' }}</td>
                                                <td class="td-notified">
                                                    <span class="pill {{ $r->send_email ? 'yes' : 'no' }}">
                                                        {{ $r->send_email ? 'Si' : 'No' }}
                                                    </span>
                                                </td>

                                                <td class="td-actions">
                                                    <button class="btn-delete-reminder" data-id="{{ $r->id }}"
                                                        title="Delete reminder">
                                                        <i class='bx bx-trash'></i>
                                                    </button>
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="empty-row">No reminders found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="table-footer">
                                <div class="footer-left">
                                    <div class="footer-meta">
                                        Showing {{ $reminders->firstItem() ?? 0 }} - {{ $reminders->lastItem() ?? 0 }}
                                        of {{ $reminders->total() }} reminders
                                    </div>
                                </div>

                                <div class="footer-right">
                                    <div class="pagination-wrap">
                                        {{-- Mantiene tu estilo "Previous 1,2,3 Next" usando el paginator default --}}
                                        {{ $reminders->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OVERLAY -->
                    <div id="reminderOverlay" class="overlay">
                        <div class="overlay-box">
                            <div class="overlay-head">
                                <h2>Set Reminder</h2>
                                <button type="button" class="overlay-x" id="closeReminderOverlay"
                                    aria-label="Close">×</button>
                            </div>

                            <form method="POST" action="{{ route('reminders.store', $customer->ID) }}"
                                id="reminderForm" class="overlay-body">
                                @csrf

                                <div class="field">
                                    <label>Date to be notified <span class="req">*</span></label>
                                    <input type="datetime-local" name="remind_at" required>
                                    <small class="hint">Select date and time (hh:mm).</small>
                                </div>

                                <div class="field">
                                    <label>Set Reminder to <span class="req">*</span></label>
                                    <select name="remind_to" required>
                                        <option value="" disabled selected>Select a user...</option>

                                        <optgroup label="Users">
                                            @foreach ($users as $u)
                                                <option value="user:{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </optgroup>

                                        <optgroup label="Sub Users">
                                            @foreach ($subs as $s)
                                                <option value="sub:{{ $s->id }}">{{ $s->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="field">
                                    <label>Description <span class="req">*</span></label>
                                    <textarea name="description" rows="5" required placeholder="Write the reminder details..."></textarea>
                                </div>

                                <label class="checkline">
                                    <input type="checkbox" name="send_email">
                                    <span>Send also an email for this reminder</span>
                                </label>

                                <div class="overlay-actions">
                                    <button type="button" class="btn-ghost" id="cancelReminderBtn">Cancel</button>
                                    <button type="submit" class="btn-primary">Save</button>
                                </div>

                            </form>
                        </div>
                    </div>

                    <script>
                        // Para mantener perPage al cambiarlo sin perder q
                        window.__REMINDERS__ = {
                            perPage: {{ (int) $perPage }},
                            q: @json($q),
                            indexUrl: @json(route('reminders.index', $customer->ID)),
                        };
                    </script>
                    <script src="{{ asset('js/reminders.js') }}"></script>
                </div> <!-- /#profile-wrapper -->
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
    <script src="{{ asset('js/reminders.js') }}"></script>
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
