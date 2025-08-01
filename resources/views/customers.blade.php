<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" href="img/favicon.png">

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