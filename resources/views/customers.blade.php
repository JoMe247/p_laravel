<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" href="img/favicon.png">

    <!-- Styles -->
<link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    

    <!-- Icons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Tailwind CSS -->
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->

</head>
<body>
    <div id="main-container">
        
        <!-- Menu Include-->
        @include('menu')

        <section id="dash">

            <div id="lower-table-clients" type="fullscreen">

                <h3>Recent Clients</h3>

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

                    <div class="button" color="dodgerblue" size="xsmall" position="absolute" id="allAction-btn" status="pending" ><i class='bx bx-right-arrow-alt' ></i></div>
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


                            <tr>
                                <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                <td class="customer-id">0004</td>
                                <td class="table-name" id="customer-name">Sarah Thompson</td>
                                <td class="customer-policy">4000000</td>
                                <td class="customer-address">101 Pine Street</td>
                                <td class="customer-phone">555-987-6543</td>
                                <td class="customer-dob">03/12/1982</td>
                                <td class="customer-drop"><i class='bx bx-dots-horizontal-rounded'></i>
                                    <label class="table-panel-options">
                                        <p><i class='bx bx-id-card'></i><a href="">Open</a></p>
                                        <p><i class='bx bx-edit'></i><a href="">Edit</a></p>
                                        <p><i class='bx bx-trash'></i><a href="">Delete</a></p>
                                        <p><i class='bx bxs-message'></i><a href="">SMS</a></p>
                                        <p><i class='bx bx-file'></i><a href="">Invoice</a></p>
                                    </label>
                                </td>
                            </tr>

                           
                            
                        </tbody>
                        
                    </table>

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
        
        <div class='settings-sub-title'>Theme</div>
        
        <div id="dark-mode">
            <span class="switch">
                <input id="switch-rounded" type="checkbox" />
                <label for="switch-rounded"></label>
            </span>
            <p>Dark Mode</p>
        </div>

        <div class='settings-sub-title'>Action Color</div>

        <div class="color-pick-container">
            <div class="color-pick" color="red"></div>
            <div class="color-pick" color="reddish"></div>
            <div class="color-pick" color="orange"></div>
            <div class="color-pick" color="yellow"></div>
            <div class="color-pick" color="green"></div>
            <div class="color-pick" color="aquamarine"></div>
            <div class="color-pick" color="blue"></div>
            <div class="color-pick" color="royal"></div>
            <div class="color-pick" color="purple"></div>
            <div class="color-pick" color="pink"></div>
            <div class="color-pick" color="gray"></div>
            <div class="color-pick" color="black"></div>
            <div class="color-pick" color="white"></div>
        </div>

        <div class="settings-sub-title">Side Panel Settings</div>
        
        <div id="background-side-settings">
            <p style="width:100%;">
                <input type="radio" id="background1" name="background-settings" checked>
                <label for="background1">Background Color</label>
            </p>

            <div class='settings-sub-title'>Select Color</div>

            <div class="color-pick-container">
                <div class="color-pick" color="red"></div>
                <div class="color-pick" color="reddish"></div>
                <div class="color-pick" color="orange"></div>
                <div class="color-pick" color="yellow"></div>
                <div class="color-pick" color="green"></div>
                <div class="color-pick" color="aquamarine"></div>
                <div class="color-pick" color="blue"></div>
                <div class="color-pick" color="royal"></div>
                <div class="color-pick" color="purple"></div>
                <div class="color-pick" color="pink"></div>
                <div class="color-pick" color="gray"></div>
                <div class="color-pick" color="black"></div>
                <div class="color-pick" color="white"></div>
            </div>

            <p>
                <input type="radio" id="background2" name="background-settings">
                <label for="background2">Background Image</label>
            </p>
        </div>

    </div>

    <div id="dim-screen"></div>
    

    <!-- <script src="js/main.js"></script> -->
    <!-- <script src="js/image.js"></script> -->
    <!-- <script src="js/weather.js"></script> -->
    <script src="js/dropdown.js"></script>
    <script src="js/menu.js"></script>
    <script src="js/table.js"></script>
    <script src="js/settings.js"></script>
    <script src="js/operations.js"></script>
    
</body>
</html>