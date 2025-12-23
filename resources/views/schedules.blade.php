<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Schedules</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">


    <!-- Archivos CSS -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/schedules.css') }}">

    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- JQuery -->
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

                    {{-- tu header/sidebar si ya lo tienes --}}
                    @includeIf('partials.sidebar')

                    <div class="page-wrap">
                        <div class="schedules-topbar">
                            <div class="sched-title">
                                <h2 id="weekTitle">Week</h2>
                                <div class="week-nav">
                                    <button class="week-btn" id="prevWeek"><i class='bx bx-chevron-left'></i></button>
                                    <button class="week-btn" id="nextWeek"><i class='bx bx-chevron-right'></i></button>
                                    <button class="week-btn ghost" id="goToday">Today</button>
                                </div>
                            </div>

                            <div class="sched-badges">
                                <span class="badge {{ $isOwner ? 'ok' : 'view' }}">
                                    {{ $isOwner ? 'Edit Enabled (User)' : 'View Only (Sub User)' }}
                                </span>
                            </div>
                        </div>

                        <div class="schedules-card">
                            <div class="table-wrap">
                                <table class="sched-table">
                                    <thead>
                                        <tr>
                                            <th class="col-assign">Assign to</th>
                                            <th data-dow="1">Mon</th>
                                            <th data-dow="2">Tue</th>
                                            <th data-dow="3">Wed</th>
                                            <th data-dow="4">Thu</th>
                                            <th data-dow="5">Fri</th>
                                            <th data-dow="6">Sat</th>
                                            <th data-dow="0">Sun</th>
                                        </tr>
                                    </thead>
                                    <tbody id="schedBody">
                                        {{-- JS render --}}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Overlay 1: picker de shifts --}}
                    <div class="overlay" id="shiftPickerOverlay" aria-hidden="true">
                        <div class="overlay-card">
                            <div class="overlay-head">
                                <div class="overlay-title">
                                    <h3 id="pickerTitle">Add a shift</h3>
                                    <p id="pickerSub">Select a shift to assign</p>
                                </div>
                                <button class="icon-btn" id="closePicker"><i class='bx bx-x'></i></button>
                            </div>

                            <div class="overlay-body">
                                <div class="shift-list" id="shiftList"></div>
                            </div>

                            <div class="overlay-foot">
                                <button class="btn ghost" id="removeAssignmentBtn" style="display:none;">
                                    Remove shift
                                </button>

                                <button class="btn primary" id="openCreateShift">
                                    <i class='bx bx-plus'></i> New Shift
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Overlay 2: crear/editar shift --}}
                    <div class="overlay" id="shiftFormOverlay" aria-hidden="true">
                        <div class="overlay-card">
                            <div class="overlay-head">
                                <div class="overlay-title">
                                    <h3 id="formTitle">New Shift</h3>
                                    <p class="muted">Create a reusable shift template</p>
                                </div>
                                <button class="icon-btn" id="closeForm"><i class='bx bx-x'></i></button>
                            </div>

                            <div class="overlay-body">
                                <div class="form-grid">
                                    <div class="field">
                                        <label>Assign to</label>
                                        <select id="assignToSelect"></select>
                                        <small class="hint">Opcional: puedes dejarlo “Any” (plantilla
                                            general).</small>
                                    </div>

                                    <div class="field">
                                        <label>Shift color</label>
                                        <select id="colorSelect">
                                            <option value="">Default (Gray)</option>
                                            <option value="blue">Blue</option>
                                            <option value="green">Green</option>
                                            <option value="orange">Orange</option>
                                            <option value="purple">Purple</option>
                                            <option value="red">Red</option>
                                        </select>
                                    </div>

                                    <div class="field">
                                        <label>Time <span class="req">*</span></label>
                                        <input type="text" id="timeInput" placeholder="e.g. 12:00 pm - 5:00 am"
                                            autocomplete="off">
                                        <div class="suggest-box" id="timeSuggest"></div>
                                    </div>

                                    <div class="field checkline">
                                        <label class="check">
                                            <input type="checkbox" id="timeOffCheck">
                                            <span>Time off</span>
                                        </label>
                                    </div>

                                    <div class="field">
                                        <label>Time off type <span class="req" id="offReq"
                                                style="display:none;">*</span></label>
                                        <select id="timeOffType" disabled>
                                            <option value="">Select...</option>
                                            <option value="Holiday">Holiday</option>
                                            <option value="Personal">Personal</option>
                                            <option value="Sick">Sick</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="overlay-foot">
                                <button class="btn ghost" id="backToPicker">Back</button>
                                <button class="btn danger" id="deleteShiftBtn" style="display:none;">Delete</button>
                                <button class="btn primary" id="saveShiftBtn">Save</button>
                            </div>
                        </div>
                    </div>

                    <script>
                        window.SCHEDULES_BOOT = {
                            weekStart: @json($weekStart),
                            weekEnd: @json($weekEnd),
                            canEdit: @json($isOwner),
                        };
                    </script>
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
    <script src="{{ asset('js/schedules.js') }}"></script>
</body>

</html>
