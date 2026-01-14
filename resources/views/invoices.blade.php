<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Invoices</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="customer-id" content="{{ $customer->ID }}">


    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">

    <link rel="stylesheet" href="{{ asset('css/invoices.css') }}">

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



            <div class="invoices-page">

                <!-- HEADER -->
                <div class="logo-box">
                    <img class="agency-logo"
                        src="{{ $agencyInfo->agency_logo ? asset('storage/' . $agencyInfo->agency_logo) : asset('img/default-logo.png') }}"
                        alt="Logo Agencia">
                </div>


                <!-- Agency info (arriba derecha) -->
                <!-- Agency info (arriba derecha) + Customer info (debajo) -->
                <div class="right-info">

                    <!-- AGENCY TOP RIGHT -->
                    <div class="agency-top">
                        <div class="agency-title">{{ $agencyInfo->agency_name ?? '' }}</div>
                        <div class="agency-line">{{ $agencyInfo->office_phone ?? '' }}</div>
                        <div class="agency-line">{{ $agencyInfo->agency_address ?? '' }}</div>
                    </div>

                    <!-- CUSTOMER BELOW -->
                    <div class="customer-box">
                        <div class="customer-title">{{ $customer->Name ?? '' }}</div>
                        <div class="customer-line">{{ $customer->Phone ?? '' }}</div>
                        <div class="customer-line">{{ $customer->Email1 ?? '' }}</div>
                        <div class="customer-line">{{ $customer->Address ?? '' }}</div>
                        <div class="customer-line">
                            {{ $customer->City ?? '' }}{{ $customer->State ?? '' ? ', ' . $customer->State : '' }}
                        </div>

                        <div class="policy-select-wrap">
                            <label class="policy-label">Policy</label>

                            <select id="policySelect" class="policy-select">
                                @if (($policyNumbers ?? collect())->count() === 0)
                                    <option value="">No policies</option>
                                @else
                                    @foreach ($policyNumbers as $p)
                                        <option value="{{ $p }}">{{ $p }}</option>
                                    @endforeach
                                @endif
                            </select>

                            <div class="policy-small">
                                Total Policies: <span>{{ $policiesCount }}</span>
                            </div>
                        </div>

                    </div>

                </div>

            </div>



            <!-- TABLE AREA -->
            <div class="table-card">

                <div class="charges-box"
                    data-save-url="{{ route('invoices.charges.save', ['customerId' => $customerId]) }}">

                    <!-- FEE -->
                    <div class="charge-section">
                        <label class="charge-label">Fee</label>
                        <input id="feeInput" class="charge-input" type="text" value="{{ $fee ?? '' }}"
                            placeholder="Fee">

                        <label class="check-row">
                            <input id="feeSplitCheck" type="checkbox" {{ !empty($feeSplit) ? 'checked' : '' }}>
                            <span>Fee Split Payment</span>
                        </label>

                        <div id="feeSplitFields" class="split-fields"
                            style="{{ !empty($feeSplit) ? '' : 'display:none;' }}">

                            <div class="split-block">
                                <label class="charge-label">Payment 1 Method</label>
                                <select id="feeP1Method" class="charge-select">
                                    <option value="">Select</option>
                                    <option value="Cash" {{ ($feeP1Method ?? '') === 'Cash' ? 'selected' : '' }}>Cash
                                    </option>
                                    <option value="Credit/Debit Card"
                                        {{ ($feeP1Method ?? '') === 'Credit/Debit Card' ? 'selected' : '' }}>Credit/Debit
                                        Card</option>
                                    <option value="EFT" {{ ($feeP1Method ?? '') === 'EFT' ? 'selected' : '' }}>EFT
                                    </option>
                                </select>

                                <input id="feeP1Value" class="charge-input" type="text"
                                    value="{{ $feeP1Value ?? '' }}" placeholder="Payment 1 Amount">
                            </div>

                            <div class="split-block">
                                <label class="charge-label">Payment 2 Method</label>
                                <select id="feeP2Method" class="charge-select">
                                    <option value="">Select</option>
                                    <option value="Cash" {{ ($feeP2Method ?? '') === 'Cash' ? 'selected' : '' }}>Cash
                                    </option>
                                    <option value="Credit/Debit Card"
                                        {{ ($feeP2Method ?? '') === 'Credit/Debit Card' ? 'selected' : '' }}>Credit/Debit
                                        Card</option>
                                    <option value="EFT" {{ ($feeP2Method ?? '') === 'EFT' ? 'selected' : '' }}>EFT
                                    </option>
                                </select>

                                <input id="feeP2Value" class="charge-input" type="text"
                                    value="{{ $feeP2Value ?? '' }}" placeholder="Payment 2 Amount">
                            </div>

                        </div>
                    </div>

                    <!-- PREMIUM -->
                    <div class="charge-section">
                        <label class="charge-label">Premium</label>
                        <input id="premiumInput" class="charge-input" type="text" value="{{ $premium ?? '' }}"
                            placeholder="Premium">

                        <label class="check-row">
                            <input id="premiumSplitCheck" type="checkbox" {{ !empty($premiumSplit) ? 'checked' : '' }}>
                            <span>Premium Split Payment</span>
                        </label>

                        <div id="premiumSplitFields" class="split-fields"
                            style="{{ !empty($premiumSplit) ? '' : 'display:none;' }}">

                            <div class="split-block">
                                <label class="charge-label">Payment 1 Method</label>
                                <select id="premiumP1Method" class="charge-select">
                                    <option value="">Select</option>
                                    <option value="Cash" {{ ($premiumP1Method ?? '') === 'Cash' ? 'selected' : '' }}>
                                        Cash</option>
                                    <option value="Credit/Debit Card"
                                        {{ ($premiumP1Method ?? '') === 'Credit/Debit Card' ? 'selected' : '' }}>
                                        Credit/Debit Card</option>
                                    <option value="EFT" {{ ($premiumP1Method ?? '') === 'EFT' ? 'selected' : '' }}>
                                        EFT</option>
                                </select>

                                <input id="premiumP1Value" class="charge-input" type="text"
                                    value="{{ $premiumP1Value ?? '' }}" placeholder="Payment 1 Amount">
                            </div>

                            <div class="split-block">
                                <label class="charge-label">Payment 2 Method</label>
                                <select id="premiumP2Method" class="charge-select">
                                    <option value="">Select</option>
                                    <option value="Cash" {{ ($premiumP2Method ?? '') === 'Cash' ? 'selected' : '' }}>
                                        Cash</option>
                                    <option value="Credit/Debit Card"
                                        {{ ($premiumP2Method ?? '') === 'Credit/Debit Card' ? 'selected' : '' }}>
                                        Credit/Debit Card</option>
                                    <option value="EFT" {{ ($premiumP2Method ?? '') === 'EFT' ? 'selected' : '' }}>
                                        EFT</option>
                                </select>

                                <input id="premiumP2Value" class="charge-input" type="text"
                                    value="{{ $premiumP2Value ?? '' }}" placeholder="Payment 2 Amount">
                            </div>

                        </div>
                    </div>

                </div>


                <div class="invoice-dates"
                    data-save-url="{{ route('invoices.dates.save', ['customerId' => $customerId]) }}">

                    <!-- CREATION DATE -->
                    <div class="date-row">
                        <label class="date-label">Creation Date</label>

                        <div class="date-input-wrap">
                            <input type="date" id="creationDateInput" class="date-input"
                                value="{{ $creationDate ?: now()->format('Y-m-d') }}">
                            <span class="date-icon"></span>
                        </div>
                    </div>

                    <!-- PAYMENT DATE -->
                    <div class="date-row">
                        <label class="date-label">Payment Date</label>

                        <div class="date-input-wrap">
                            <input type="date" id="paymentDateInput" class="date-input"
                                value="{{ $paymentDate ?: now()->format('Y-m-d') }}">
                            <span class="date-icon"></span>
                        </div>
                    </div>

                </div>



                <div class="table-topbar">
                    <button id="btnAddRow" class="btn-add-row">Add Row</button>

                    <div class="grand-total">
                        Total: <span id="grandTotal">$0.00</span>
                    </div>
                </div>

                <div class="table-scroll">


                    <datalist id="invoiceItemOptions">
                        <option value="Add Coverage - Comp & Collision Or Umbi/umpd"></option>
                        <option value="Add Driver"></option>
                        <option value="Add Vehicle W/comp & Collision Or Umbi/umpd"></option>
                        <option value="Add Vehicle W/liability"></option>
                        <option value="Address Change"></option>
                        <option value="Certificate Of Insurance"></option>
                        <option value="Credit Card Fee"></option>
                        <option value="Delete Vehicle"></option>
                        <option value="Exclude Driver"></option>
                        <option value="Installment"></option>
                        <option value="Installment Fee"></option>
                        <option value="Late Fee"></option>
                        <option value="New Business Commercial"></option>
                        <option value="New Business Comp & Collision Or Umbi/umpd W/dl"></option>
                        <option value="New Business Comp & Collision Or Umbi/umpd W/out Dl"></option>
                        <option value="New Business General Liability"></option>
                        <option value="New Business Homeowners"></option>
                        <option value="New Business Liability W/dl"></option>
                        <option value="New Business Liability W/out Dl"></option>
                        <option value="New Business Motorcycle"></option>
                        <option value="New Business Renters W/lemonade"></option>
                        <option value="New Business Renters W/progressive"></option>
                        <option value="New Business Sr22 Suspended Dl"></option>
                        <option value="Notary"></option>
                        <option value="Nsf Fee"></option>
                        <option value="Other"></option>
                        <option value="Reinstate"></option>
                        <option value="Renewal Fee - 12 Months"></option>
                        <option value="Renewal Fee - 2 Months"></option>
                        <option value="Renewal Fee - 3 Months"></option>
                        <option value="Renewal Fee - 6 Months"></option>
                        <option value="Rewrite Comp & Collision Or Umbi/umpd"></option>
                        <option value="Rewrite Liability"></option>
                        <option value="Sr-22"></option>
                        <option value="Swap Drivers"></option>
                        <option value="Swap Vehicle"></option>
                    </datalist>

                    <table class="invoice-table" id="invoiceTable">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Amount</th>
                                <th>Price ($)</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody id="invoiceTbody">
                            @foreach ($rows as $r)
                                <tr class="row-item" data-row-id="{{ $r->id }}">
                                    <td>
                                        <div class="item-wrap">
                                            <input class="cell-input item-input" type="text"
                                                list="invoiceItemOptions" value="{{ $r->item }}">
                                            <span class="item-arrow"></span>
                                        </div>
                                    </td>


                                    <td>
                                        <input class="cell-input qty-input" type="text"
                                            value="{{ $r->amount }}">
                                    </td>

                                    <td>
                                        <input class="cell-input price-input" type="text"
                                            value="{{ $r->price }}">
                                    </td>

                                    <td class="row-total">$0.00</td>

                                    <td class="row-actions">
                                        <button type="button" class="btn-trash" title="Delete row">🗑</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="hint">
                    * El total se calcula automático (Amount) y se suma al Total general.
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
    <script src="{{ asset('js/profile.js') }}"></script>
    <script src="{{ asset('js/policies.js') }}"></script>
    <script src="{{ asset('js/invoices.js') }}"></script>
</body>

</html>
