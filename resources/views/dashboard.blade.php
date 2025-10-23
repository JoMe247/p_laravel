<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="base-url" content="{{ url('/') }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
                        <div id="welcome-user">Welcome, &nbsp;<b id="username-welcome">Place Holder Name</b></div>
                        <div id="welcome-date"></div>
                    </div>

                    <div id="weather-info">
                        <img src="" alt="" id="weather-img">
                        <span id="temperature"></span>
                        <span id="weather"></span>
                        
                        <span id="city"></span>
                    </div>

                    <label id="reset-picture" title="Change Home Picture" onclick="resetImage()"><i class='bx bx-reset'></i></label>
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

                <div class="quick-item">
                    <p>Announcements</p>
                    <i class='bx bxs-right-arrow' type='arrow-color'></i>
                    <i class='bx bxs-megaphone'></i><text>5</text>
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
                                    <td class="customer-id">0001</td>
                                    <td class="table-name" id="customer-name">Diego Garay</td>
                                    <td class="customer-policy">1000000</td>
                                    <td class="customer-address">17490 Meandering Way</td>
                                    <td class="customer-phone">469-473-9488</td>
                                    <td class="customer-dob">08/31/1999</td>
                                    <td class="customer-drop"><i class='bx bx-dots-horizontal-rounded' ></i>
                                        <label class="table-panel-options">
                                            <p><i class='bx bx-id-card'></i><a href="">Open</a></p>
                                            <p><i class='bx bx-edit'></i><a href="">Edit</a></p>
                                            <p><i class='bx bx-trash'></i><a href="">Delete</a></p>
                                            <p><i class='bx bxs-message'></i><a href="">SMS</a></p>
                                            <p><i class='bx bx-file'></i><a href="">Invoice</a></p>
                                        </label>
                                    </td>
                                </tr>

                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0002</td>
                                    <td class="table-name" id="customer-name">Emily Johnson</td> <!-- Updated name -->
                                    <td class="customer-policy">2000000</td>
                                    <td class="customer-address">456 Oak Street</td>
                                    <td class="customer-phone">555-123-4567</td>
                                    <td class="customer-dob">05/15/1985</td>
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

                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0003</td>
                                    <td class="table-name" id="customer-name">Michael Rodriguez</td>
                                    <td class="customer-policy">3000000</td>
                                    <td class="customer-address">789 Elm Avenue</td>
                                    <td class="customer-phone">555-456-7890</td>
                                    <td class="customer-dob">10/22/1990</td>
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

                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0005</td>
                                    <td class="table-name" id="customer-name">Alexandra Davis</td>
                                    <td class="customer-policy">5000000</td>
                                    <td class="customer-address">222 Maple Lane</td>
                                    <td class="customer-phone">555-555-1234</td>
                                    <td class="customer-dob">07/08/1975</td>
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

                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0006</td>
                                    <td class="table-name" id="customer-name">Christopher Evans</td>
                                    <td class="customer-policy">6000000</td>
                                    <td class="customer-address">333 Cedar Road</td>
                                    <td class="customer-phone">555-789-0123</td>
                                    <td class="customer-dob">12/05/1988</td>
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
                                
                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0007</td>
                                    <td class="table-name" id="customer-name">Alicia Martinez</td>
                                    <td class="customer-policy">7000000</td>
                                    <td class="customer-address">456 Pine Avenue</td>
                                    <td class="customer-phone">555-234-5678</td>
                                    <td class="customer-dob">09/18/1995</td>
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
                                
                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0008</td>
                                    <td class="table-name" id="customer-name">Daniel Kim</td>
                                    <td class="customer-policy">8000000</td>
                                    <td class="customer-address">789 Birch Street</td>
                                    <td class="customer-phone">555-345-6789</td>
                                    <td class="customer-dob">04/30/1980</td>
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
                                
                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0009</td>
                                    <td class="table-name" id="customer-name">Elena Rodriguez</td>
                                    <td class="customer-policy">9000000</td>
                                    <td class="customer-address">101 Oak Lane</td>
                                    <td class="customer-phone">555-456-7890</td>
                                    <td class="customer-dob">11/15/1983</td>
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
                                
                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0010</td>
                                    <td class="table-name" id="customer-name">Carlos Hernandez</td>
                                    <td class="customer-policy">10000000</td>
                                    <td class="customer-address">222 Maple Road</td>
                                    <td class="customer-phone">555-567-8901</td>
                                    <td class="customer-dob">06/25/1978</td>
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

                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0011</td>
                                    <td class="table-name" id="customer-name">Jessica Lee</td>
                                    <td class="customer-policy">11000000</td>
                                    <td class="customer-address">777 Pine Street</td>
                                    <td class="customer-phone">555-123-4567</td>
                                    <td class="customer-dob">09/08/1987</td>
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
                                
                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0012</td>
                                    <td class="table-name" id="customer-name">Ryan Mitchell</td>
                                    <td class="customer-policy">12000000</td>
                                    <td class="customer-address">888 Oak Avenue</td>
                                    <td class="customer-phone">555-234-5678</td>
                                    <td class="customer-dob">03/24/1992</td>
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
                                
                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0013</td>
                                    <td class="table-name" id="customer-name">Olivia Turner</td>
                                    <td class="customer-policy">13000000</td>
                                    <td class="customer-address">999 Elm Lane</td>
                                    <td class="customer-phone">555-345-6789</td>
                                    <td class="customer-dob">07/12/1980</td>
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
                                
                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0014</td>
                                    <td class="table-name" id="customer-name">Jordan Smith</td>
                                    <td class="customer-policy">14000000</td>
                                    <td class="customer-address">111 Maple Road</td>
                                    <td class="customer-phone">555-456-7890</td>
                                    <td class="customer-dob">11/05/1985</td>
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
                                
                                <tr>
                                    <td><input type="checkbox" name="customer_select" id="" onchange="checkboxActive()"></td>
                                    <td class="customer-id">0015</td>
                                    <td class="table-name" id="customer-name">Gabriel Rivera</td>
                                    <td class="customer-policy">15000000</td>
                                    <td class="customer-address">222 Cedar Lane</td>
                                    <td class="customer-phone">555-567-8901</td>
                                    <td class="customer-dob">06/18/1979</td>
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

                <div id="lower-table-container">

                    <div id="recent-1">
                        <h3 class="sub-title">Recent Documents</h3>

                        <div id="recent-documents">

                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Jose Perez</a><p>Exclusion</p></div>
                                <div class="recent-pdf-date">12/22/2023</div>
                            </div>

                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Juan Rodriguez</a><p>Cancellation</p></div>
                                <div class="recent-pdf-date">01/10/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Maria Garcia</a><p>Exclusion</p></div>
                                <div class="recent-pdf-date">02/15/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Carlos Sanchez</a><p>Endorsement</p></div>
                                <div class="recent-pdf-date">03/05/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Laura Gomez</a><p>Commercial</p></div>
                                <div class="recent-pdf-date">04/20/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Pedro Martinez</a><p>Cancellation</p></div>
                                <div class="recent-pdf-date">05/03/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Ana Rodriguez</a><p>Exclusion</p></div>
                                <div class="recent-pdf-date">06/12/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Jorge Lopez</a><p>Endorsement</p></div>
                                <div class="recent-pdf-date">07/08/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Isabel Gonzalez</a><p>Commercial</p></div>
                                <div class="recent-pdf-date">08/25/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Miguel Ramos</a><p>Cancellation</p></div>
                                <div class="recent-pdf-date">09/14/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Sofia Diaz</a><p>Exclusion</p></div>
                                <div class="recent-pdf-date">10/30/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Ricardo Herrera</a><p>Endorsement</p></div>
                                <div class="recent-pdf-date">11/18/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Gabriela Castro</a><p>Commercial</p></div>
                                <div class="recent-pdf-date">12/05/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Hector Morales</a><p>Cancellation</p></div>
                                <div class="recent-pdf-date">12/19/2023</div>
                            </div>
                            
                            <div class="recent-pdf">
                                <img src="img/pdf-icon.png" alt="">
                                <div class="recent-pdf-title"><a>Carmen Fernandez</a><p>Exclusion</p></div>
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
                        <label class="thumb-options" onclick="selectImage(1)"><img src="img/menu/thumbs/1.jpg" alt="" ></label>
                        <label class="thumb-options" onclick="selectImage(2)"><img src="img/menu/thumbs/2.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(3)"><img src="img/menu/thumbs/3.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(4)"><img src="img/menu/thumbs/4.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(5)"><img src="img/menu/thumbs/5.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(6)"><img src="img/menu/thumbs/6.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(7)"><img src="img/menu/thumbs/7.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(8)"><img src="img/menu/thumbs/8.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(9)"><img src="img/menu/thumbs/9.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(10)"><img src="img/menu/thumbs/10.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(11)"><img src="img/menu/thumbs/11.jpg" alt=""></label>
                        <label class="thumb-options" onclick="selectImage(12)"><img src="img/menu/thumbs/12.jpg" alt=""></label>
                        
                      
                    </div>
                </div>

                <div id="sideBlur-slider">
                    <div class="slider-wrap" id="side-image-slider">
                        <label for="frac" style="display:block;margin-bottom:8px;">Side Image Blur</label>
                        <div class="row">
                        <input id="frac" type="range" min="0" max="1" step="0.01" value="0.00" />
                        <div class="value">
                            <span id="val-pct">0%</span>
                        </div>
                        </div>
                    </div>

                    <div class="slider-wrap" id="home-image-slider">
                        <label for="frac2" style="display:block;margin-bottom:8px;">Home Image Blur</label>
                        <div class="row">
                        <input id="frac2" type="range" min="0" max="1" step="0.01" value="0.00" />
                        <div class="value">
                            <span id="val-pct2">0%</span>
                        </div>
                        </div>
                    </div>
                </div>
                
            </div>
    
        </div>
    </div>

    <div id="dim-screen-1"></div>
    

    <!-- <script src="js/main.js"></script> -->
    <script src="js/image.js"></script>
    <script src="js/weather.js"></script>
    <script src="js/dropdown.js"></script>
    <script src="js/menu.js"></script>
    <script src="js/table.js"></script>
    <script src="js/settings.js"></script>
    <script src="js/operations.js"></script>

</body>
</html>