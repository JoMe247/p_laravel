<input type="checkbox" id="menu-chk" style="display: none;">

<div id="bottom-menu-close">

    <img src="img/logo.png" alt="">

    <label class="menu-icon" for="menu-chk" onclick="overFlowH()">
        <div class="bar-menu-icon"></div>
        <div class="bar-menu-icon"></div>
        <div class="bar-menu-icon"></div>
    </label>

    <!-- <h3>Open Menu</h3> -->

</div>

<section id="lateral">

    <!-- Logo Row -->
    <div class="lateral-row" style="backdrop-filter: blur(4px);">
        <img id="main-logo" src="img/logo-short-white.png" alt="DoClient Logo">
    </div>

    <!-- User Row -->
    <!-- <div class="lateral-row">
        <div id="user-row">
            <img id="user-img" src="users/user1.jpg" alt="">
            <p id="user-name">Diego G</p>
        </div>
    </div> -->

    <!-- Dash Options Start-->
    <div id="dash-options">

        <!-- New Category -->
        <div class="lateral-row" data="sub-option">
            Home
        </div>
        <!-- Inside Category Options -->
        <div class="lateral-row" data="option" onclick="window.location='./dashboard'">
            <i class='bx bxs-dashboard'></i> Dashboard
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bx-devices'></i> Account 
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bx-sitemap'></i> Office 
        </div>


        <!-- New Category -->
        <div class="lateral-row" data="sub-option">
            Data
        </div>
        <!-- Inside Category Options -->
        <div class="lateral-row" data="option" onclick="window.location='./customers'">
            <i class='bx bx-user'></i> Customers 
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bxs-buildings'></i> Companies 
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bx-file'></i> Documents 
        </div>

        <div class="lateral-row" data="option" onclick="window.location='./inbox'">
            <i class='bx bx-envelope'></i> SMS 
        </div>

        <div class="lateral-row" data="option" onclick="window.location='./inbox'">
            <i class='bx bxl-whatsapp'></i> WhatsApp 
        </div>
        
        <div class="lateral-row" data="option">
            <i class='bx bx-receipt'></i> Payments 
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bx-bar-chart-alt'></i> Reports 
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bx-file-blank'></i> Invoice 
        </div>

        <!-- New Category -->
        <div class="lateral-row" data="sub-option">
            System
        </div>
        <!-- Inside Category Options -->
        <div class="lateral-row" data="option">
            <i class='bx bx-category'></i> Tools
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bx-help-circle'></i> Help
        </div>

        <div class="lateral-row" data="option" onclick="openSettings();">
            <i class='bx bx-cog'></i> Settings 
        </div>

        <div class="lateral-row" data="option" onclick="confirmBoxOn('Please Confirm', 'Are you sure you want to Logout?', 'logOut()')">
            <i class='bx bx-log-out'></i> Log out 
        </div>

    </div>
    <!-- Dash Options End -->

    <div id="lateral-blur"></div>
</section>

        