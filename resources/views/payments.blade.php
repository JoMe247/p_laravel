<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Payments</title>

    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="customer-id" content="{{ $customer->ID }}">



    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">


    <link rel="stylesheet" href="{{ asset('css/payments.css') }}">


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

            <div class="payments-layout">

                <div class="left-column">

                    {{-- MENU LATERAL --}}
                    <aside class="profile-side-menu">
                        <nav class="profile-side-nav">
                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('profile', $customer->ID) }}'">
                                <i class='bx bx-id-card'></i>
                                <span>Profile</span>
                            </button>

                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('policies.index', $customer->ID) }}'">
                                <i class='bx bx-shield-quarter'></i>
                                <span>Policies</span>
                            </button>

                            <button type="button" class="profile-menu-item active"
                                onclick="window.location.href='{{ route('payments', ['customerId' => $customer->ID]) }}'">
                                <i class='bx bx-credit-card'></i>
                                <span>Invoices (Payments)</span>
                            </button>

                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('reminders.index', $customer->ID) }}'">
                                <i class='bx bx-task'></i>
                                <span>Reminders</span>
                            </button>

                            <button type="button" class="profile-menu-item"
                                onclick="window.location.href='{{ route('files.customer', $customer->ID) }}'">
                                <i class='bx bx-folder'></i>
                                <span>Files</span>
                            </button>

                            <button type="button" class="profile-menu-item">
                                <i class='bx bx-file'></i>
                                <span>Documents</span>
                            </button>

                        </nav>
                    </aside>

                    {{-- ⭐ NOTES – FUERA DEL MENÚ, STICKY ⭐ --}}
                    <div class="profile-notes sticky-notes">

                        <div class="notes-header">
                            <h3>Notes</h3>
                            <button id="add-note-btn" class="btn small">+ Add Note</button>
                        </div>

                        <div class="notes-scroll">
                            <div id="notes-list"></div>
                        </div>

                    </div>

                </div> <!-- /.left-column -->


                {{-- ⭐ OVERLAY PARA NUEVA NOTA ⭐ --}}
                <div id="note-overlay">
                    <div class="note-window">
                        <h2 style="margin-bottom:15px;">Add Note</h2>

                        <label>Policy</label>
                        <input type="text" id="note-policy">

                        <label>Subject</label>
                        <input type="text" id="note-subject">

                        <label>Note</label>
                        <textarea id="note-text" rows="5"></textarea>

                        <div class="overlay-actions">
                            <button class="btn secondary" id="note-cancel">Cancel</button>
                            <button class="btn" id="note-save">Save</button>
                        </div>
                    </div>
                </div>
                {{-- /.left-column --}}

                <div class="payments-wrapper">
                    <div class="payments-actions">

                        <a class="btn-invoices"
                            href="{{ route('invoices', ['customerId' => $customerId, 'new' => 1]) }}">
                            Invoices
                        </a>

                        <div class="footer-image-controls">
                            <label class="switch">
                                <input type="checkbox" id="footerImgToggle"
                                    {{ !empty($agencyInfo->invoice_footer_enabled) ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>

                            <button type="button" class="btn-footer-img" id="openFooterOverlay"
                                data-has-image="{{ !empty($agencyInfo->invoice_footer_image) ? '1' : '0' }}"
                                style="{{ !empty($agencyInfo->invoice_footer_enabled) && !empty($agencyInfo->invoice_footer_image) ? '' : 'display:none;' }}">
                                Update Image
                            </button>

                        </div>


                    </div>


                    <div class="payments-card">
                        <h2>Payments</h2>

                        <div class="invoices-table-wrap">
                            <table class="invoices-table">
                                <thead>
                                    <tr>
                                        <th>Invoice#</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Policy#</th>
                                        <th>Amount</th>
                                        <th>Fee</th>
                                        <th>Premium</th>
                                        <th>Item</th>
                                        <th style="width:140px;">Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($invoices as $inv)
                                        <tr>
                                            <td class="td-strong">{{ $inv->invoice_number ?? '' }}</td>
                                            <td>{{ $inv->creation_date ?? '' }}</td>
                                            <td>{{ $inv->payment_date ?? '' }}</td>
                                            <td>{{ $inv->policy_number ?? '' }}</td>

                                            <td class="td-money">
                                                @php
                                                    $a = $inv->amount_calc ?? '';
                                                @endphp
                                                {{ $a !== '' ? '$' . number_format((float) $a, 2) : '' }}
                                            </td>

                                            <td class="td-money">
                                                @php $f = $inv->fee ?? ''; @endphp
                                                {{ $f !== '' ? '$' . number_format((float) preg_replace('/[^0-9.]/', '', $f), 2) : '' }}
                                            </td>

                                            <td class="td-money">
                                                @php $p = $inv->premium ?? ''; @endphp
                                                {{ $p !== '' ? '$' . number_format((float) preg_replace('/[^0-9.]/', '', $p), 2) : '' }}
                                            </td>

                                            <td class="td-item" title="{{ $inv->first_item ?? '' }}">
                                                {{ $inv->first_item ?? '' }}
                                            </td>

                                            <td class="td-actions">
                                                {{-- EDIT: abre invoices en modo edición (mismo invoice) --}}
                                                <a class="icon-btn" title="Edit"
                                                    href="{{ route('invoices', ['customerId' => $customerId, 'invoiceId' => $inv->id]) }}">
                                                    <i class='bx bx-edit-alt'></i>
                                                </a>

                                                {{-- PDF: por ahora placeholder --}}
                                                <button class="icon-btn" type="button" title="Download PDF">
                                                    <a class="icon-btn" title="Download PDF"
                                                        href="{{ route('invoices.pdf', ['invoiceId' => $inv->id]) }}">
                                                        <i class='bx bxs-file-pdf'></i>
                                                    </a>
                                                </button>

                                                {{-- DELETE --}}
                                                <form class="inline-form" method="POST"
                                                    action="{{ route('invoices.destroy', ['invoiceId' => $inv->id]) }}"
                                                    onsubmit="return confirm('Delete this invoice?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="icon-btn danger" type="submit" title="Delete">
                                                        <i class='bx bxs-trash'></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="td-empty">No invoices yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- PAGINACIÓN --}}
                        <div class="payments-pagination">
                            {{ $invoices->links() }}
                        </div>
                    </div>
                </div>
            </div>

            <div id="footer-img-overlay" class="overlay" style="display:none;">
                <div class="overlay-box">
                    <div class="overlay-head">
                        <h3>Invoice PDF Footer Image</h3>
                        <button type="button" class="overlay-close" id="closeFooterOverlay">
                            <i class='bx bx-x'></i>
                        </button>
                    </div>

                    <p class="overlay-muted">
                        Upload an image that will appear at the bottom of every invoice PDF for this agency.
                    </p>

                    <button type="button" class="btn-upload" id="btnAddFooterImage">
                        + Add Image
                    </button>

                    <input type="file" id="footerImageInput" accept="image/*" style="display:none;">

                    <div class="overlay-preview" id="footerPreview" style="display:none;">
                        <img id="footerPreviewImg" alt="Preview">
                    </div>

                    <div class="overlay-actions">
                        <button type="button" class="btn secondary" id="cancelFooterUpload">Cancel</button>
                        <button type="button" class="btn" id="saveFooterUpload">Save</button>
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
    <script src="{{ asset('js/profile.js') }}"></script>
    <script src="{{ asset('js/policies.js') }}"></script>
    <script src="{{ asset('js/invoices.js') }}"></script>
    <script src="{{ asset('js/payments.js') }}"></script>


</body>

</html>
