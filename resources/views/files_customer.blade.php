<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Files Customer</title>
    <link rel="icon" href="{{ asset('img/favicon.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="customer-id" content="{{ $customer->ID }}">

    <!-- Archivos CSS -->
    <link rel="stylesheet" href="{{ asset('css/variables.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dash.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dropdown.css') }}">
    <link rel="stylesheet" href="{{ asset('css/graph.css') }}">
    <link rel="stylesheet" href="{{ asset('css/editCustomer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui_elements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sms-inbox.css') }}">
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
    <link rel="stylesheet" href="{{ asset('css/files_customer.css') }}">

    <!-- Icons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- JQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <!-- Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div id="main-container">
        @include('menu')

        <section id="dash">



            <div id="profile-wrapper" data-id="{{ $customer->ID }}">

                <div class="files-layout">


                    <!-- 🔸 COLUMNA IZQUIERDA -->
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

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-credit-card'></i>
                                    <span>Invoices (Payments)</span>
                                </button>

                                <button type="button" class="profile-menu-item"
                                    onclick="window.location.href='{{ route('reminders.index', $customer->ID) }}'">
                                    <i class='bx bx-task'></i>
                                    <span>Reminders</span>
                                </button>

                                <button type="button" class="profile-menu-item active"
                                    onclick="window.location.href='{{ route('files.customer', $customer->ID) }}'">
                                    <i class='bx bx-folder'></i>
                                    <span>Files</span>
                                </button>

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-file'></i>
                                    <span>Documents</span>
                                </button>

                                <button type="button" class="profile-menu-item">
                                    <i class='bx bx-map'></i>
                                    <span>Map</span>
                                </button>
                            </nav>
                        </aside>

                        {{-- ⭐ NOTES – FUERA DEL MENÚ, STICKY ⭐ --}}
                        <div class="profile-notes sticky-notes">

                            <div class="notes-header">
                                <h3>Notes</h3>
                                <button id="add-note-btn" class="btn small"><i class='bx bx-message-alt-add'></i>  &nbsp;Add Note</button>
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




                    <!-- COLUMNA DERECHA (FILES) -->
                    <div class="files-container">

                        <div class="files-header">
                            <h2>
                                {{ $customer->name }}

                                <span class="files-count">
                                    <i class="bx bx-folder"></i>{{ $files->count() }}
                                </span>

                                <!-- 🔹 FILE TYPE FILTER BUTTONS -->
                                <div class="files-type-filters">
                                    <button class="file-type-btn active" data-type="all">All</button>
                                    <button class="file-type-btn pdf" data-type="pdf">PDF</button>
                                    <button class="file-type-btn doc" data-type="doc">DOC</button>
                                    <button class="file-type-btn image" data-type="image">IMG</button>
                                    <button class="file-type-btn zip" data-type="zip">ZIP</button>
                                </div>
                            </h2>


                            <div class="files-header-actions">
                                <button id="open-upload" class="btn-primary">
                                    <i class='bx bx-cloud-upload' ></i> &nbsp;Upload
                                </button>

                                <select id="files-filter" style="display:none">
                                    <option value="all">All files</option>
                                    <option value="name">File name</option>
                                    <option value="date">Date</option>
                                    <option value="user">Uploaded by</option>
                                    <option value="pdf">PDF</option>
                                    <option value="doc">DOC</option>
                                    <option value="docx">DOCX</option>
                                    <option value="png">PNG</option>
                                    <option value="jpg">JPG</option>
                                </select>

                            </div>
                        </div>

                        <div class="files-table-wrapper">
                            <table class="files-table">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>Last Modified</th>
                                        <th>Uploaded By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($files as $file)
                                        @php
                                            $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));

                                            $iconMap = [
                                                'avi'  => 'avi.png',
                                                'css'  => 'css.png',
                                                'csv'  => 'csv.png',
                                                'dll'  => 'dll.png',
                                                'doc'  => 'doc.png',
                                                'docx' => 'docx.png',
                                                'eps'  => 'eps.png',
                                                'exe'  => 'exe.png',
                                                'gif'  => 'gif.png',
                                                'html' => 'html.png',
                                                'htm'  => 'html.png',
                                                'jpg'  => 'jpg.png',
                                                'jpeg' => 'jpg.png',
                                                'js'   => 'js.png',
                                                'mov'  => 'mov.png',
                                                'mp3'  => 'mp3.png',
                                                'pdf'  => 'pdf.png',
                                                'php'  => 'php.png',
                                                'png'  => 'png.png',
                                                'ppt'  => 'ppt.png',
                                                'pptx' => 'ppt.png',
                                                'psd'  => 'psd.png',
                                                'rar'  => 'rar.png',
                                                'svg'  => 'svg.png',
                                                'txt'  => 'txt.png',
                                                'wav'  => 'wav.png',
                                                'xls'  => 'xls.png',
                                                'xlsx' => 'xlsx.png',
                                                'zip'  => 'zip.png',
                                            ];

                                            $iconFile = $iconMap[$ext] ?? 'file.png';
                                            $iconPath = asset('img/file_icons/' . $iconFile);

                                            $isImage = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']);
                                            $uploadedBy = null;

                                            if ($file->uploaded_by_type === 'user') {
                                                $uploadedBy = \App\Models\User::find($file->uploaded_by_id)?->name;
                                            } else {
                                                $uploadedBy = \App\Models\SubUser::find($file->uploaded_by_id)?->name;
                                            }
                                        @endphp

                                        <tr data-type="{{ $ext }}">
                                            <td>
                                                <div class="file-info">
                                                    @if($isImage)
                                                        <a href="{{ route('files.view', $file->id) }}" target="_blank">
                                                            <img class="customer-file-img" src="{{ route('files.view', $file->id) }}" alt="{{ $file->file_name }}">
                                                            <small class="meta-img-info">{{ number_format($file->file_size / 1024, 2) }} KB</small>
                                                        </a>
                                                    @else
                                                        <img src="{{ $iconPath }}" alt="{{ $ext }}" class="file-icon-img">

                                                        <div class="file-meta">
                                                            <a href="{{ route('files.view', $file->id) }}" target="_blank">
                                                                {{ $file->file_name }}
                                                            </a>
                                                            <small>{{ number_format($file->file_size / 1024, 2) }} KB</small>
                                                        </div>
                                                    @endif

                                                    <!-- <img src="{{ $iconPath }}" alt="{{ $ext }}" class="file-icon-img"> -->

                                                    
                                                </div>
                                            </td>

                                            <td>{{ ($file->updated_at ?? $file->created_at)->format('Y-m-d H:i') }}</td>

                                            <td>{{ $uploadedBy ?? 'Unknown' }}</td>

                                            <td class="files-actions">
                                                <a href="{{ route('files.view', $file->id) }}" target="_blank" class="icon-btn show-file">
                                                    <i class='bx bx-show-alt'></i>
                                                </a>

                                                <a href="{{ route('files.download', $file->id) }}" class="icon-btn download-file">
                                                    <i class='bx bxs-download' ></i>
                                                </a>

                                                <form method="POST" action="{{ route('files.delete', $file->id) }}"
                                                    onsubmit="return confirm('Are you sure you want to delete this file?')"
                                                    style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="icon-btn danger">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </section>
    </div>


    <!-- Upload Overlay -->
    <div id="upload-overlay">
        <div class="upload-modal">
            <h3>Upload customer file</h3>

            <form id="upload-form" method="POST" action="{{ route('files.store', $customer->ID) }}" enctype="multipart/form-data">
                @csrf

                <div id="drop-zone" class="drop-zone">
                    <input type="file" name="file" id="upload-file-input" required hidden>

                    <div class="drop-zone-content">
                        <i class='bx bxs-file-image upload-drop-icon'></i>
                        <p id="subtext-drag-file" class="drop-zone-text">Drop your file here or Click to browse</p>
                        <small id="selected-file-name" class="selected-file-name"></small>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="submit" class="btn-primary" id="upload-file-button" style="display:none;">Upload</button>
                    <button type="button" id="close-upload">Cancel</button>
                </div>
            </form>
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
                        <label class="thumb-options" onclick="selectImage(1)"><img src="../../img/menu/thumbs/1.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(2)"><img src="../../img/menu/thumbs/2.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(3)"><img src="../../img/menu/thumbs/3.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(4)"><img src="../../img/menu/thumbs/4.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(5)"><img src="../../img/menu/thumbs/5.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(6)"><img src="../../img/menu/thumbs/6.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(7)"><img src="../../img/menu/thumbs/7.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(8)"><img src="../../img/menu/thumbs/8.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(9)"><img src="../../img/menu/thumbs/9.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(10)"><img src="../../img/menu/thumbs/10.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(11)"><img src="../../img/menu/thumbs/11.jpg"
                                alt=""></label>
                        <label class="thumb-options" onclick="selectImage(12)"><img src="../../img/menu/thumbs/12.jpg"
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


    <!-- Archivos JS -->
    <script src="{{ asset('js/image.js') }}"></script>
    <script src="{{ asset('js/weather.js') }}"></script>
    <script src="{{ asset('js/dropdown.js') }}"></script>
    <script src="{{ asset('js/menu.js') }}"></script>
    <script src="{{ asset('js/table.js') }}"></script>
    <script src="{{ asset('js/settings.js') }}"></script>
    <script src="{{ asset('js/operations.js') }}"></script>
    <script src="{{ asset('js/help.js') }}"></script>
    <script src="{{ asset('js/profile.js') }}"></script>
    <script src="{{ asset('js/files_customer.js') }}"></script>
</body>

</html>
