<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Tasks · CRM</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <!-- Styles -->
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
    <link rel="stylesheet" href="{{ asset('css/tasks.css') }}">

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

                    <!-- ===========================
                         TOP BAR (MATCH WITH tasks.js)
                    ============================ -->
                    <div class="task-top-bar">

                        <h1 class="title">Tasks</h1>

                        <div class="task-status-counters">
                            <button class="filter-btn active" data-filter="all">
                                All <span class="count">(0)</span>
                            </button>

                            <button class="filter-btn" data-filter="Open">
                                Open <span class="count">(0)</span>
                            </button>

                            <button class="filter-btn" data-filter="In Progress">
                                In Progress <span class="count">(0)</span>
                            </button>

                            <button class="filter-btn" data-filter="Closed">
                                Closed <span class="count">(0)</span>
                            </button>
                        </div>

                        <div class="task-search">
                            <i class='bx bx-search'></i>
                            <input type="text" id="task-search-input" placeholder="Search...">
                        </div>

                        <button id="btn-new-task" class="btn-add">+ New Task</button>
                    </div>



                    <!-- ===========================
                         TASK TABLE (MATCH WITH tasks.js)
                    ============================ -->
                    <div class="task-table-wrapper">

                        <table class="task-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Assignees</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody id="task-body">

                                @forelse ($tasks as $t)
                                    <tr>

                                        <td>{{ $t->id }}</td>

                                        <td>{{ $t->subject }}</td>

                                        <!-- MUST MATCH .edit-status -->
                                        <td>
                                            <select class="edit-status" data-id="{{ $t->id }}">
                                                <option {{ $t->status == 'Open' ? 'selected' : '' }}>Open</option>
                                                <option {{ $t->status == 'In Progress' ? 'selected' : '' }}>In Progress
                                                </option>
                                                <option {{ $t->status == 'Closed' ? 'selected' : '' }}>Closed</option>
                                            </select>
                                        </td>

                                        <!-- MUST MATCH .edit-priority -->
                                        <td>
                                            <select class="edit-priority" data-id="{{ $t->id }}">
                                                <option {{ $t->priority == 'Low' ? 'selected' : '' }}>Low</option>
                                                <option {{ $t->priority == 'Medium' ? 'selected' : '' }}>Medium
                                                </option>
                                                <option {{ $t->priority == 'High' ? 'selected' : '' }}>High</option>
                                                <option {{ $t->priority == 'Urgent' ? 'selected' : '' }}>Urgent
                                                </option>
                                            </select>
                                        </td>

                                        <td>{{ $t->assigned_name }}</td>

                                        <td>{{ \Carbon\Carbon::parse($t->created_at)->format('Y-m-d') }}</td>

                                        <td >
                                            <!-- MUST MATCH .info-btn -->
                                            <i class='bx bx-info-circle info-btn'
                                                data-desc="{{ $t->description }}"></i>

                                            <!-- MUST MATCH .delete-btn -->
                                            <i class='bx bx-trash delete-btn' data-id="{{ $t->id }}"></i>
                                        </td>

                                    </tr>
                                @empty

                                    <tr>
                                        <td colspan="7" class="empty-msg">No entries found</td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>

                    </div>



                    <!-- ===========================
                         DESCRIPTION OVERLAY (MATCH)
                    ============================ -->
                    <div id="desc-overlay">
                        <div class="description-modal">
                            <h3>Description</h3>
                            <p id="desc-text"></p>
                            <button id="close-desc" class="btn-cancel">Close</button>
                        </div>
                    </div>



                    <!-- ===========================
                         CREATE TASK OVERLAY (MATCH)
                    ============================ -->
                    <div id="task-overlay">
                        <div class="overlay-content">

                            <h2>Create New Task</h2>

                            <form id="task-form" method="POST" action="{{ route('tasks.store') }}">
                                @csrf

                                <label>Subject</label>
                                <input type="text" name="subject" required>

                                <label>Start Date</label>
                                <input type="date" name="start_date" required>

                                <label>Due Date</label>
                                <input type="date" name="due_date" required>

                                <label>Priority</label>
                                <select name="priority" required>
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>

                                <label>Assignees</label>
                                <select name="assigned" required>
                                    @foreach ($assignees as $a)
                                        <option value="{{ $a['type'] }}|{{ $a['id'] }}">
                                            {{ ucfirst($a['type']) }} — {{ $a['name'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <label>Description</label>
                                <textarea name="description" rows="4" required></textarea>

                                <button type="submit" class="btn-save">Save Task</button>
                                <button type="button" id="cancel-task" class="btn-cancel">Cancel</button>
                            </form>

                        </div>
                    </div>
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
    <script src="{{ asset('js/tasks.js') }}"></script>

</body>

</html>
