$(document).ready(function () {

    // =========================================================================
    //   CONFIG GENERAL
    // =========================================================================
    const csrf = $('meta[name="csrf-token"]').attr('content');

    const $overlayNew  = $('#policy-overlay');
    const $newBtn      = $('#new-policy-btn');
    const $cancelNew   = $('#policy-cancel-btn');
    const $saveNew     = $('#policy-save-btn');

    const config   = $('#policy-config');
    const storeUrl = config.data('store-url'); // ruta policies.store


    // =========================================================================
    //   OVERLAY NUEVA POLICY
    // =========================================================================

    // Abrir overlay de nueva policy
    $newBtn.on('click', function () {
        $overlayNew.css('display', 'flex').hide().fadeIn(150);
    });

    // Cerrar overlay nueva policy
    $cancelNew.on('click', function () {
        $overlayNew.fadeOut(150);
    });


    // =========================================================================
    //   GUARDAR NUEVA POLICY (CREAR)
    // =========================================================================
    $saveNew.on('click', function () {

        let vehicules = [];

        $('.vehicle-card').each(function () {
            const $card = $(this);

            const vin = ($card.find('.vin-input').val() || '').trim();

            let year = $card.find('.year-select').val();
            if (!year || year === 'other') {
                year = ($card.find('.year-other').val() || '').trim();
            }

            let make = $card.find('.make-select').val();
            if (!make || make === 'other') {
                make = ($card.find('.make-other').val() || '').trim();
            }

            let model = $card.find('.model-select').val();
            if (!model || model === 'other') {
                model = ($card.find('.model-other').val() || '').trim();
            }

            // Si tarjeta está vacía, no la guardamos
            if (!vin && !year && !make && !model) return;

            vehicules.push({ vin, year, make, model });
        });

        let formData = {
            _token: csrf,
            pol_carrier:      $('#pol_carrier').val(),
            pol_number:       $('#pol_number').val(),
            pol_url:          $('#pol_url').val(),
            pol_expiration:   $('#pol_expiration').val(),
            pol_eff_date:     $('#pol_eff_date').val(),
            pol_added_date:   $('#pol_added_date').val(),
            pol_due_day:      $('#pol_due_day').val(),
            pol_status:       $('#pol_status').val(),
            pol_agent_record: $('#pol_agent_record').val(),
            vehicules: JSON.stringify(vehicules)
        };

        $.ajax({
            url:  storeUrl,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error saving policy');
                }
            },
            error: function (xhr) {
                console.error('Error saving policy:', xhr.responseText);
                alert('Error saving policy');
            }
        });
    });


    // =========================================================================
    //   ELIMINAR POLICY
    // =========================================================================
    $('.policy-delete-btn').on('click', function () {
        const url = $(this).data('url');

        if (!confirm('Delete this policy?')) return;

        $.ajax({
            url: url,
            type: 'POST',
            data: {
                _token: csrf,
                _method: 'DELETE'
            },
            success: function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert('Error deleting policy');
                }
            },
            error: function (xhr) {
                console.error('Error deleting policy:', xhr.responseText);
                alert('Error deleting policy');
            }
        });
    });


    // =========================================================================
    //   SISTEMA DE VEHÍCULOS (CREACIÓN)
    // =========================================================================
    const MAX_VEHICLES = 6; // aquí cambias el límite

    $('#add-vehicle-btn').on('click', function () {

        const count = $('.vehicle-card').length;
        if (count >= MAX_VEHICLES) {
            alert('Máximo ' + MAX_VEHICLES + ' vehículos permitidos.');
            return;
        }

        const id = Date.now();

        $('#vehicle-container').append(`
            <div class="vehicle-card" data-id="${id}">
                <div id="thumb_${id}" class="vehicle-thumb"></div>

                <div class="vehicle-field">
                    <label>VIN (opcional)</label>
                    <input type="text" class="vin-input">
                </div>

                <div class="vehicle-field">
                    <label>Año</label>
                    <select class="year-select">
                        <option value="">Seleccione</option>
                    </select>
                    <input type="text" class="year-other" style="display:none;" placeholder="Otro año">
                </div>

                <div class="vehicle-field">
                    <label>Make</label>
                    <select class="make-select">
                        <option value="">Seleccione</option>
                    </select>
                    <input type="text" class="make-other" style="display:none;" placeholder="Otra marca">
                </div>

                <div class="vehicle-field">
                    <label>Model</label>
                    <select class="model-select">
                        <option value="">Seleccione</option>
                    </select>
                    <input type="text" class="model-other" style="display:none;" placeholder="Otro modelo">
                </div>

                <div class="vehicle-delete-btn">Eliminar Vehículo</div>
            </div>
        `);

        initYearsForCard($(`.vehicle-card[data-id='${id}']`));
    });

    function initYearsForCard($card) {
        const $yearSel = $card.find('.year-select');
        const currentYear = new Date().getFullYear();

        $yearSel.empty().append('<option value="">Seleccione</option>');
        for (let y = currentYear; y >= 1980; y--) {
            $yearSel.append(`<option value="${y}">${y}</option>`);
        }
        $yearSel.append('<option value="other">Other</option>');
    }

    $(document).on('click', '.vehicle-delete-btn', function () {
        $(this).closest('.vehicle-card').remove();
    });


    // --- VIN → autocompletar ---
    $(document).on('blur', '.vin-input', function () {
        const vin = $(this).val();
        const $card = $(this).closest('.vehicle-card');

        if (!vin || vin.length < 5) return;

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${vin}?format=json`,
            function (res) {

                if (!res?.Results?.[0]) return;
                const v = res.Results[0];

                const year  = v.ModelYear;
                const make  = v.Make;
                const model = v.Model;

                const $yearSel  = $card.find('.year-select');
                const $makeSel  = $card.find('.make-select');
                const $modelSel = $card.find('.model-select');

                if (year) {
                    if ($yearSel.find(`option[value="${year}"]`).length === 0) {
                        $yearSel.append(`<option value="${year}">${year}</option>`);
                    }
                    $yearSel.val(year);
                }

                if (make) {
                    $makeSel.empty().append(`<option value="${make}">${make}</option>`);
                }

                if (model) {
                    $modelSel.empty().append(`<option value="${model}">${model}</option>`);
                }

                updateImageForCard($card, make, model, year);
            }
        );
    });

    // --- Año → cargar marcas ---
    $(document).on('change', '.year-select', function () {
        const $card     = $(this).closest('.vehicle-card');
        const year      = $(this).val();
        const $makeSel  = $card.find('.make-select');
        const $modelSel = $card.find('.model-select');

        $makeSel.empty().append('<option value="">Cargando marcas...</option>');
        $modelSel.empty().append('<option value="">Seleccione modelo</option>');

        if (year === 'other') {
            $(this).hide();
            $card.find('.year-other').show();
            return;
        }

        $.get('https://vpic.nhtsa.dot.gov/api/vehicles/getallmakes?format=json',
            function (res) {
                $makeSel.empty().append('<option value="">Seleccione marca</option>');
                res.Results.forEach(m => {
                    if (m.Make_Name) {
                        $makeSel.append(`<option value="${m.Make_Name}">${m.Make_Name}</option>`);
                    }
                });
                $makeSel.append('<option value="other">Other</option>');
            }
        );
    });

    // --- Marca → cargar modelos ---
    $(document).on('change', '.make-select', function () {
        const make      = $(this).val();
        const $card     = $(this).closest('.vehicle-card');
        const $modelSel = $card.find('.model-select');

        $modelSel.empty().append('<option value="">Cargando modelos...</option>');

        if (make === 'other') {
            $(this).hide();
            $card.find('.make-other').show();
            return;
        }

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMake/${encodeURIComponent(make)}?format=json`,
            function (res) {

                $modelSel.empty().append('<option value="">Seleccione modelo</option>');

                if (!res.Results?.length) {
                    $modelSel.append('<option value="">No hay modelos</option>');
                    return;
                }

                res.Results.forEach(m => {
                    if (m.Model_Name) {
                        $modelSel.append(`<option value="${m.Model_Name}">${m.Model_Name}</option>`);
                    }
                });

                $modelSel.append('<option value="other">Other</option>');
            }
        );
    });

    // --- Modelo → actualizar imagen ---
    $(document).on('change', '.model-select', function () {
        const $card = $(this).closest('.vehicle-card');
        const year  = $card.find('.year-select').val();
        const make  = $card.find('.make-select').val();
        const model = $(this).val();

        if (!year || !make || !model) return;

        updateImageForCard($card, make, model, year);
    });

    function updateImageForCard($card, make, model, year) {
        if (!make || !model || !year) return;

        const url =
            `https://cdn.imagin.studio/getImage?customer=img&make=${encodeURIComponent(make)}` +
            `&modelFamily=${encodeURIComponent(model)}&modelYear=${year}` +
            `&paintdescription=white&angle=28&zoomtype=fullscreen`;

        const id = $card.data('id');
        $(`#thumb_${id}`).css('background-image', `url('${url}')`);
    }


    // =========================================================================
    //   OVERLAY VER / EDITAR POLICY (BOTÓN i)
    // =========================================================================

    const $overlayEdit   = $('#policy-edit-overlay');
    const $overlayContent = $('#policy-edit-content');
    const $overlaySave   = $('#policy-edit-save');
    const $overlayCancel = $('#policy-edit-cancel');

    // Abrir overlay al dar click en el botón i
    $(document).on('click', '.policy-info-btn', function () {

        const id         = $(this).data('id');
        const showUrl    = $(this).data('url');
        const updateUrl  = $(this).data('update-url');

        $.get(showUrl, function (res) {

            if (!res || !res.success) {
                alert('Error loading policy data');
                return;
            }

            const p = res.policy;
            let veh = p.vehicules;

            if (typeof veh === 'string') {
                try {
                    veh = JSON.parse(veh);
                } catch (e) {
                    veh = [];
                }
            }
            if (!Array.isArray(veh)) veh = [];

            // Construimos el HTML dentro del overlay
            let html = `
                <div class="edit-section">
                    <label>Carrier</label>
                    <input type="text" id="edit_pol_carrier" value="${p.pol_carrier ?? ''}">

                    <label>Number</label>
                    <input type="text" id="edit_pol_number" value="${p.pol_number ?? ''}">

                    <label>URL</label>
                    <input type="text" id="edit_pol_url" value="${p.pol_url ?? ''}">

                    <label>Expiration</label>
                    <input type="date" id="edit_pol_expiration" value="${p.pol_expiration ?? ''}">

                    <label>Eff Date</label>
                    <input type="date" id="edit_pol_eff_date" value="${p.pol_eff_date ?? ''}">

                    <label>Added Date</label>
                    <input type="date" id="edit_pol_added_date" value="${p.pol_added_date ?? ''}">

                    <label>Due Day</label>
                    <input type="text" id="edit_pol_due_day" value="${p.pol_due_day ?? ''}">

                    <label>Status</label>
                    <input type="text" id="edit_pol_status" value="${p.pol_status ?? ''}">

                    <label>Agent Record</label>
                    <input type="text" id="edit_pol_agent_record" value="${p.pol_agent_record ?? ''}">
                </div>

                <h4>Vehicles</h4>
            `;

            veh.forEach((v, index) => {

                const make  = v.make || '';
                const model = v.model || '';
                const year  = v.year || '';

                let imgUrl = '';
                if (make && model && year) {
                    imgUrl =
                        `https://cdn.imagin.studio/getImage?customer=img&make=${encodeURIComponent(make)}` +
                        `&modelFamily=${encodeURIComponent(model)}&modelYear=${year}` +
                        `&paintdescription=white&angle=28&zoomtype=fullscreen`;
                }

                html += `
                    <div class="vehicle-edit-card" data-index="${index}">
                        <div class="vehicle-edit-thumb"
                             id="vehicle_edit_thumb_${index}"
                             style="background-image:url('${imgUrl}');">
                        </div>

                        <label>VIN</label>
                        <input type="text" class="edit_vin" value="${v.vin || ''}">

                        <label>Year</label>
                        <input type="text" class="edit_year" value="${year}">

                        <label>Make</label>
                        <input type="text" class="edit_make" value="${make}">

                        <label>Model</label>
                        <input type="text" class="edit_model" value="${model}">
                    </div>
                `;
            });

            $overlayContent.html(html);
            $overlayEdit.fadeIn(150);

            // Handler de guardado (lo re-bindeamos cada vez)
            $overlaySave.off().on('click', function () {

                let updatedVeh = [];

                $('.vehicle-edit-card').each(function () {
                    updatedVeh.push({
                        vin:   $(this).find('.edit_vin').val(),
                        year:  $(this).find('.edit_year').val(),
                        make:  $(this).find('.edit_make').val(),
                        model: $(this).find('.edit_model').val()
                    });
                });

                $.ajax({
                    url:  updateUrl,
                    type: 'POST',
                    data: {
                        _token: csrf,

                        pol_carrier:      $('#edit_pol_carrier').val(),
                        pol_number:       $('#edit_pol_number').val(),
                        pol_url:          $('#edit_pol_url').val(),
                        pol_expiration:   $('#edit_pol_expiration').val(),
                        pol_eff_date:     $('#edit_pol_eff_date').val(),
                        pol_added_date:   $('#edit_pol_added_date').val(),
                        pol_due_day:      $('#edit_pol_due_day').val(),
                        pol_status:       $('#edit_pol_status').val(),
                        pol_agent_record: $('#edit_pol_agent_record').val(),
                        vehicules: JSON.stringify(updatedVeh)
                    },
                    success: function (r) {
                        if (r.success) {
                            location.reload();
                        } else {
                            alert('Error updating policy');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error updating policy:', xhr.responseText);
                        alert('Error updating policy');
                    }
                });
            });

        });
    });

    // Cerrar overlay de edición
    $overlayCancel.on('click', function () {
        $overlayEdit.fadeOut(150);
    });


    // =========================================================================
    //   ACTUALIZAR IMAGENES EN MODO EDICIÓN POR CAMBIOS MANUALES
    // =========================================================================
    function updateEditImage(index) {
        const $card = $(`.vehicle-edit-card[data-index="${index}"]`);
        const year  = $card.find('.edit_year').val();
        const make  = $card.find('.edit_make').val();
        const model = $card.find('.edit_model').val();

        if (!year || !make || !model) return;

        const imgUrl =
            `https://cdn.imagin.studio/getImage?customer=img&make=${encodeURIComponent(make)}` +
            `&modelFamily=${encodeURIComponent(model)}&modelYear=${year}` +
            `&paintdescription=white&angle=28&zoomtype=fullscreen`;

        $(`#vehicle_edit_thumb_${index}`).css('background-image', `url('${imgUrl}')`);
    }

    // VIN en modo edición
    $(document).on('blur', '.edit_vin', function () {
        const vin   = $(this).val();
        const index = $(this).closest('.vehicle-edit-card').data('index');

        if (!vin) return;

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${vin}?format=json`,
            function (res) {
                if (!res?.Results?.[0]) return;

                const v = res.Results[0];
                const $card = $(`.vehicle-edit-card[data-index="${index}"]`);

                if (v.ModelYear) $card.find('.edit_year').val(v.ModelYear);
                if (v.Make)      $card.find('.edit_make').val(v.Make);
                if (v.Model)     $card.find('.edit_model').val(v.Model);

                updateEditImage(index);
            }
        );
    });

    // Cambios manuales en year/make/model en edición
    $(document).on('change keyup', '.edit_year, .edit_make, .edit_model', function () {
        const index = $(this).closest('.vehicle-edit-card').data('index');
        updateEditImage(index);
    });

});
