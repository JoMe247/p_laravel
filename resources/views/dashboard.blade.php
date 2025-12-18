<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="icon" href="img/favicon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Styles -->
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/dash.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/dropdown.css">
    <link rel="stylesheet" href="css/graph.css">
    <link rel="stylesheet" href="css/editCustomer.css">
    <link rel="stylesheet" href="css/ui_elements.css">


    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div id="main-container">

        <!-- Menu Include-->
        @include('menu')

        <section id="dash">

            <!-- Land Image Home -->
            <div id="home-image">
                <div id="home-image-content">

                    <div id="welcome">
                        <div id="welcome-user">
                            Welcome, &nbsp;<b id="username-welcome">{{ $username ?? 'Guest' }}</b>
                        </div>

                        <div id="welcome-date"></div>
                    </div>

                    <div id="weather-info">
                        <img src="" alt="" id="weather-img">
                        <span id="temperature"></span>
                        <span id="weather"></span>

                        <span id="city"></span>
                    </div>

                    <label id="reset-picture" title="Change Home Picture" onclick="resetImage()"><i
                            class='bx bx-reset'></i></label>
                    <label id="view-full-picture" title="View Full Picture"><i class='bx bx-image'></i></label>
                </div>
            </div>

            <!-- Quick Access Buttons -->
            <div id="quick-access">

                <div class="quick-item">
                    <p>Total Customers</p>
                    <i class='bx bxs-right-arrow' type='arrow-color'></i>
                    <i class='bx bxs-user-badge'></i><text>1234</text>
                </div>

                <div class="quick-item">
                    <p>Commercial Insurance</p>
                    <i class='bx bxs-right-arrow' type='arrow-color'></i>
                    <i class='bx bx-buildings'></i><text>12</text>
                </div>

                <div class="quick-item">
                    <p>Personal Insurance</p>
                    <i class='bx bxs-right-arrow' type='arrow-color'></i>
                    <i class='bx bx-building-house'></i><text>38</text>
                </div>

                <div class="quick-item">
                    <p>To Do List</p>
                    <i class='bx bxs-right-arrow' type='arrow-color'></i>
                    <i class='bx bx-list-check'></i><text>8</text>
                </div>

                <div class="quick-item">
                    <p>Today Messages</p>
                    <i class='bx bxs-right-arrow' type='arrow-color'></i>
                    <i class='bx bx-mail-send'></i><text>12</text>
                </div>

                <div class="quick-item" id="open-reminders">
                    <p>Reminders</p>
                    <i class='bx bxs-right-arrow' type='arrow-color'></i>
                    <i class='bx bxs-megaphone'></i><text>{{ $remindersCount }}</text>
                </div>

                <div class="quick-item" data="last-quick-item">
                    <p>Comments</p>
                    <i class='bx bxs-right-arrow' type='arrow-color'></i>
                    <i class='bx bx-message-rounded-dots'></i><text style="font-size: 1.6em">20 New</text>
                </div>

            </div>

            <div id="bottom-container">

                <div id="lower-table-clients">

                    <h3 class="sub-title">Recent Clients</h3>

                    <div id="table-menu">
                        <!-- Remove 'active' class, this is just to show in Codepen thumbnail -->
                        <div class="select-menu" status="pending-drop">
                            <div class="select-btn" id="action-drop-button">
                                <span class="sBtn-text">Quick Action</span>
                                <i class="bx bx-chevron-down"></i>
                            </div>

                            <ul id="table-drop" class="options" style="display: none;">
                                <li class="option">
                                    <i class='bx bxs-message' style="color: rgb(80, 80, 80);"></i>
                                    <span class="option-text" style="color: rgb(80, 80, 80);">SMS</span>
                                </li>
                                <li class="option">
                                    <i class='bx bx-envelope' style="color: rgb(80, 80, 80);"></i>
                                    <span class="option-text" style="color: rgb(80, 80, 80);">Email</span>
                                </li>
                                <li class="option">
                                    <i class='bx bx-table' style="color: rgb(80, 80, 80);"></i>
                                    <span class="option-text" style="color: rgb(80, 80, 80);">Export CSV</span>
                                </li>
                                <li class="option">
                                    <i class='bx bx-edit' style="color: rgb(80, 80, 80);"></i>
                                    <span class="option-text" style="color: rgb(80, 80, 80);">Edit</span>
                                </li>
                                <li class="option">
                                    <i class='bx bxs-trash' style="color: rgb(179, 57, 57);"></i>
                                    <span class="option-text" style="color: rgb(179, 57, 57);">Delete</span>
                                </li>
                                <li class="option">
                                    <i class='bx bx-x-circle' style="color: #c2c2c2;"></i>
                                    <span class="option-text" style="color: #c2c2c2;">Close</span>
                                </li>
                            </ul>
                        </div>

                        <div class="button" color="dodgerblue" size="xsmall" position="absolute"
                            id="allAction-btn" status="pending"><i class='bx bx-right-arrow-alt'></i></div>
                    </div>

                    <div id="search-container">
                        <label for="tableSearch"><i class='bx bx-search'></i></label>
                        <input id="tableSearch" type="text" placeholder="Search">
                    </div>

                    <div id="table-border">

                        <table class="base-table" id="customers-table">
                            <tr id="table-header">
                                <th><input id="selectAll-chk" type="checkbox" onchange="checkAll(this)"></th>
                                <th>ID</th>
                                <th>NAME</th>
                                <th>POLICY</th>
                                <th>ADDRESS</th>
                                <th>PHONE</th>
                                <th>DOB</th>
                                <th></th>
                            </tr>

                            <tbody id="clients-list">
                                @foreach ($customers as $c)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="customer_select"
                                                onchange="checkboxActive()">
                                        </td>

                                        <td class="customer-id">{{ $c->ID }}</td>
                                        <td class="table-name">{{ $c->Name }}</td>
                                        <td class="customer-policy">{{ $c->Policy ?? '—' }}</td>
                                        <td class="customer-address">{{ $c->Address }}</td>
                                        <td class="customer-phone">{{ $c->Phone }}</td>
                                        <td class="customer-dob">{{ $c->DOB }}</td>

                                        <td class="customer-drop">
                                            <i class='bx bx-dots-horizontal-rounded'></i>

                                            <label class="table-panel-options">
                                                <p><i class='bx bx-id-card'></i>
                                                    <a href="{{ url('profile/' . $c->ID) }}">Open</a>
                                                </p>

                                                <p><i class='bx bx-edit'></i>
                                                    <a href="{{ url('profile/' . $c->ID) }}">Edit</a>
                                                </p>

                                                <p><i class='bx bx-trash'></i>
                                                    <a href="{{ url('delete-customer/' . $c->ID) }}">Delete</a>
                                                </p>

                                                <p><i class='bx bxs-message'></i>
                                                    <a href="{{ url('sms/' . $c->Phone) }}">SMS</a>
                                                </p>

                                                <p><i class='bx bx-file'></i>
                                                    <a href="#">Invoice</a>
                                                </p>
                                            </label>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>


                        </table>

                    </div>

                </div>

                <div id="lower-table-container">

                    <div id="recent-1">
                        <h3 class="sub-title">Recent Documents</h3>

                        <div id="recent-documents">

                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Jose Perez</a>
                                    <p>Exclusion</p>
                                </div>
                                <div class="recent-pdf-date">12/22/2023</div>
                            </div>

                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Juan Rodriguez</a>
                                    <p>Cancellation</p>
                                </div>
                                <div class="recent-pdf-date">01/10/2023</div>
                            </div>

                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Maria Garcia</a>
                                    <p>Exclusion</p>
                                </div>
                                <div class="recent-pdf-date">02/15/2023</div>
                            </div>

                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Carlos Sanchez</a>
                                    <p>Endorsement</p>
                                </div>
                                <div class="recent-pdf-date">03/05/2023</div>
                            </div>

                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Hector Morales</a>
                                    <p>Cancellation</p>
                                </div>
                                <div class="recent-pdf-date">12/19/2023</div>
                            </div>

                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Carmen Fernandez</a>
                                    <p>Exclusion</p>
                                </div>
                                <div class="recent-pdf-date">12/31/2023</div>
                            </div>

                        </div>
                    </div>

                    <div id="recent-2">

                        <h3 class="sub-title">Weekly Income</h3>

                        <div class="graph-container">
                            <div class="graph-levels-container">
                                <p class="graph-level">100%</p>
                                <p class="graph-level">75%</p>
                                <p class="graph-level">50%</p>
                                <p class="graph-level">25%</p>
                                <p class="graph-level">0%</p>
                            </div>

                            <div class="graph-bars-containers">

                                <label class="graph-bar-height" style="height: 100%;">
                                    <p class="graph-bar-text">Monday</p>
                                    <e class="graph-amount">$500</e>
                                </label>

                                <label class="graph-bar-height" style="height: 25%;">
                                    <p class="graph-bar-text">Thursday</p>
                                    <e class="graph-amount">$500</e>
                                </label>

                                <label class="graph-bar-height" style="height: 50%;">
                                    <p class="graph-bar-text">Wednesday</p>
                                    <e class="graph-amount">$500</e>
                                </label>

                                <label class="graph-bar-height" style="height: 90%;">
                                    <p class="graph-bar-text">Tuesday</p>
                                    <e class="graph-amount">$500</e>
                                </label>

                                <label class="graph-bar-height" style="height: 10%;">
                                    <p class="graph-bar-text">Friday</p>
                                    <e class="graph-amount">$500</e>
                                </label>

                                <label class="graph-bar-height" style="height: 62%;">
                                    <p class="graph-bar-text">Saturday</p>
                                    <e class="graph-amount">$500</e>
                                </label>

                                <label class="graph-bar-height" style="height: 100%;">
                                    <p class="graph-bar-text">Sunday</p>
                                    <e class="graph-amount">$500</e>
                                </label>


                            </div>
                        </div>

                    </div>

                </div>
            </div>

        </section>

    </div>

    <div id="edit-customer">
        <div id="outer-box">

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


    <!-- <script src="js/main.js"></script> -->
    <script src="{{ asset('js/image.js') }}"></script>
    <script src="{{ asset('js/weather.js') }}"></script>
    <script src="{{ asset('js/dropdown.js') }}"></script>
    <script src="{{ asset('js/menu.js') }}"></script>
    <script src="{{ asset('js/table.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/operations.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>


    {{-- 🔔 REMINDERS OVERLAY --}}
    <div id="reminders-overlay" class="overlay">
        <div class="overlay-box" style="max-width:520px;">
            <div class="overlay-head">
                <h2>My Reminders</h2>
                <button class="overlay-x" id="close-reminders-overlay">×</button>
            </div>

            <div class="overlay-body">
                @if ($reminders->isEmpty())
                    <p style="opacity:.8;">You have no reminders.</p>
                @else
                    <ul class="reminder-list">
                        @foreach ($reminders as $r)
                            <li class="reminder-item">
                                <div class="reminder-date">
                                    {{ $r->remind_at->format('Y-m-d H:i') }}
                                </div>
                                <div class="reminder-desc">
                                    {{ $r->description }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>


</body>

</html>
