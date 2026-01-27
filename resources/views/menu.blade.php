<input type="checkbox" id="menu-chk" style="display: none;">

<div id="bottom-menu-close">
    <!-- Logo superior -->
    <img src="{{ asset('img/logo.png') }}" alt="Logo">

    <label class="menu-icon" for="menu-chk" onclick="overFlowH()">
        <div class="bar-menu-icon"></div>
        <div class="bar-menu-icon"></div>
        <div class="bar-menu-icon"></div>
    </label>
</div>

<section id="lateral">

    <!-- Logo principal -->
    <div class="lateral-row">
        <a id="img-hover" href="/dashboard"></a>
        <img id="main-logo" src="{{ asset('img/logo-short-white.png') }}" alt="DoClient Logo">
    </div>

    <!-- Opciones del Dashboard -->
    <div id="dash-options">

        <!-- Categoría -->
        <div class="lateral-row" data="sub-option">
            Home
        </div>

        <!-- Opciones de navegación -->
        <div class="lateral-row" data="option" onclick="window.location='{{ url('/dashboard') }}'" id="dashboard">
            <i class='bx bxs-dashboard'></i> Dashboard
        </div>

        <div class="lateral-row" data="option" onclick="window.location='{{url('/account')}}'" id="account">
            <i class='bx bx-devices'></i> Account
        </div>

        <div class="lateral-row" data="option" onclick="window.location='{{ url('/office') }}'" id="office">
            <i class='bx bx-sitemap'></i> Office
        </div>

        <!-- Categoría -->
        <div class="lateral-row" data="sub-option">
            Data
        </div>

        <!-- Opciones de Data -->
        <div class="lateral-row" data="option" onclick="window.location='{{ url('/customers') }}'" id="customers">
            <i class='bx bx-user'></i> Customers
        </div>

        <div class="lateral-row" data="option" onclick="window.location='{{ url('/companies') }}'" id="companies">
            <i class='bx bxs-buildings'></i> Companies
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bx-file'></i> Documents
        </div>

        <div class="lateral-row" data="option" onclick="window.location='{{ url('/sms') }}'" id="sms">
            <i class='bx bx-envelope'></i> SMS
        </div>

        <div class="lateral-row" data="option" onclick="window.location='{{ url('/whatsapp') }}'" id="whatsapp">
            <i class='bx bxl-whatsapp'></i> WhatsApp
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bx-receipt'></i> Payments
        </div>

        <div class="lateral-row" data="option">
            <i class='bx bx-bar-chart-alt'></i> Reports
        </div>

        <div class="lateral-row" data="option" onclick="window.location='{{ url('/tasks') }}'" id="tasks">
            <i class='bx bx-check-circle'></i> Tasks
        </div>

        <div class="lateral-row" data="option" onclick="window.location='{{ url('/calendar') }}'" id="calendar">
            <i class='bx bxs-calendar'></i> Calendar
        </div>

        <!-- Categoría -->
        <div class="lateral-row" data="sub-option">
            System
        </div>

        <!-- Opciones de sistema -->
        <div class="lateral-row" data="option">
            <i class='bx bx-category'></i> Tools
        </div>

        <div class="lateral-row" data="option" onclick="window.location='{{ url('/help') }}'" id="help">
            <i class='bx bx-help-circle'></i> Help
        </div>

        <div class="lateral-row" data="option" onclick="openSettings();">
            <i class='bx bx-cog'></i> Settings
        </div>

        <div class="lateral-row" data="option"
            onclick="confirmBoxOn('Please Confirm', 'Are you sure you want to Logout?', 'logOut()')">
            <i class='bx bx-log-out'></i> Log out
        </div>
    </div>

    <div id="lateral-blur"></div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
        @csrf
    </form>

    <script>

        try {
            let tab = window.location.pathname.replace("/", "");
            document.getElementById(tab).setAttribute("tab","active");
        } catch (error) {
            // console.log("No existe tab en el menu lateral");
        }
        
    </script>

</section>
