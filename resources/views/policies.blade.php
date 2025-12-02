<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Policies · CRM</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">

    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/policies.css') }}">

    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>

<div id="main-container">
    @include('menu')

    <section id="dash">
        <div id="lower-table-clients">

            <div id="profile-wrapper">

                {{-- MENU LATERAL --}}
                <aside class="profile-side-menu">
                    <div class="profile-side-header">
                        <i class='bx bx-user-circle'></i>
                        <div class="profile-side-title">
                            <span>Customer</span>
                            <strong>{{ $customer->Name }}</strong>
                        </div>
                    </div>

                    <nav class="profile-side-nav">
                        <button type="button"
                                class="profile-menu-item"
                                onclick="window.location.href='{{ route('customers.profile', $customer->ID) }}'">
                            <i class='bx bx-id-card'></i>
                            <span>Profile</span>
                        </button>

                        <button type="button" class="profile-menu-item active">
                            <i class='bx bx-shield-quarter'></i>
                            <span>Policies</span>
                        </button>
                    </nav>
                </aside>

                {{-- CONTENIDO PRINCIPAL --}}
                <div class="profile-main">

                    <div class="policies-header">
                        <h2>Policies</h2>

                        <button id="new-policy-btn" class="btn policies-new-btn">
                            <i class='bx bx-plus'></i> New Policy
                        </button>
                    </div>

                    {{-- CONFIG PARA JS --}}
                    <div id="policy-config"
                         data-store-url="{{ route('policies.store', $customer->ID) }}"
                         data-csrf="{{ csrf_token() }}">
                    </div>

                    {{-- TABLA --}}
                    <table class="table policies-table">
                        <thead>
                            <tr>
                                <th>Carrier</th>
                                <th>Number</th>
                                <th>Expiration</th>
                                <th>Vehicle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($policies as $p)
                            <tr>
                                <td>{{ $p->pol_carrier }}</td>
                                <td>{{ $p->pol_number }}</td>
                                <td>{{ $p->pol_expiration }}</td>
                                <td>{{ $p->year }} {{ $p->make }} {{ $p->model }}</td>
                                <td>
                                    <button class="btn delete-btn policy-delete-btn"
                                            data-url="{{ route('policies.destroy', $p->id) }}">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;opacity:0.6;">
                                    No policies yet.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </section>
</div>

{{-- OVERLAY NUEVA POLICY --}}
<div id="policy-overlay">
    <div class="policy-overlay-box">
        <h3>New Policy</h3>

        <div class="policy-overlay-body">
            <label>Pol Carrier</label>
            <input type="text" id="pol_carrier">

            <label>Pol Number</label>
            <input type="text" id="pol_number">

            <label>Pol URL (company website)</label>
            <input type="text" id="pol_url">

            <label>Pol Expiration</label>
            <input type="date" id="pol_expiration">

            <label>Pol Eff Date</label>
            <input type="date" id="pol_eff_date">

            <label>Pol Added Date</label>
            <input type="date" id="pol_added_date">

            <label>Pol Due Day</label>
            <input type="text" id="pol_due_day">

            <label>Pol Status</label>
            <input type="text" id="pol_status">

            <label>Pol Agent Record</label>
            <input type="text" id="pol_agent_record">

            <hr>

            <h4>Policy Vehicle per policie row</h4>

            <label>VIN</label>
            <input type="text" id="vin">

            <label>Year</label>
            <input type="number" id="year">

            <label>Make</label>
            <input type="text" id="make">

            <label>Model</label>
            <input type="text" id="model">
        </div>

        <div class="policy-overlay-actions">
            <button id="policy-cancel-btn" class="btn secondary">Cancel</button>
            <button id="policy-save-btn" class="btn policy-save-btn">Save</button>
        </div>
    </div>
</div>

<script src="{{ asset('js/policies.js') }}"></script>
</body>
</html>
