$(document).ready(function () {

    // ===============================
    //   OVERLAY + GUARDADO POLICY
    // ===============================
    const $overlay   = $('#policy-overlay');
    const $cancelBtn = $('#policy-cancel-btn');
    const $saveBtn   = $('#policy-save-btn');
    const $newBtn    = $('#new-policy-btn');

    const config   = $('#policy-config');
    const storeUrl = config.data('store-url');
    const csrf     = config.data('csrf');

    // Abrir overlay
    $newBtn.on('click', function () {
        $overlay.css('display', 'flex').hide().fadeIn(150);
    });

    // Cerrar overlay
    $cancelBtn.on('click', function () {
        $overlay.fadeOut(150);
    });

    // ===============================
    //   GUARDAR POLICY CON JSON
    // ===============================
    $saveBtn.on('click', function () {

        let vehicules = [];

        $('.vehicle-card').each(function () {

            const $card = $(this);

            const vin = ($card.find('.vin-input').val() || '').trim();

            // YEAR
            let year = $card.find('.year-select').val();
            if (!year || year === 'other') {
                year = ($card.find('.year-other').val() || '').trim();
            }

            // MAKE
            let make = $card.find('.make-select').val();
            if (!make || make === 'other') {
                make = ($card.find('.make-other').val() || '').trim();
            }

            // MODEL
            let model = $card.find('.model-select').val();
            if (!model || model === 'other') {
                model = ($card.find('.model-other').val() || '').trim();
            }

            if (!vin && !year && !make && !model) {
                return;
            }

            vehicules.push({
                vin: vin,
                year: year,
                make: make,
                model: model
            });
        });

        let formData = {
            _token: csrf,
            pol_carrier: $('#pol_carrier').val(),
            pol_number: $('#pol_number').val(),
            pol_url: $('#pol_url').val(),
            pol_expiration: $('#pol_expiration').val(),
            pol_eff_date: $('#pol_eff_date').val(),
            pol_added_date: $('#pol_added_date').val(),
            pol_due_day: $('#pol_due_day').val(),
            pol_status: $('#pol_status').val(),
            pol_agent_record: $('#pol_agent_record').val(),
            vehicules: JSON.stringify(vehicules)
        };

        $.ajax({
            url: storeUrl,
            method: 'POST',
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

    // ===============================
    //   ELIMINAR POLICY
    // ===============================
    $('.policy-delete-btn').on('click', function () {
        const url = $(this).data('url');

        if (!confirm('Delete this policy?')) return;

        $.ajax({
            url: url,
            method: 'POST',
            data: { _token: csrf, _method: 'DELETE' },
            success: function (response) {
                if (response.success) location.reload();
                else alert('Error deleting policy');
            },
            error: function (xhr) {
                console.error('Error deleting policy:', xhr.responseText);
            }
        });
    });

    // ===============================
    //   🚗 SISTEMA DE VEHÍCULOS
    // ===============================
    const MAX_VEHICLES = 6;

    $('#add-vehicle-btn').on('click', function () {

        const count = $('.vehicle-card').length;
        if (count >= MAX_VEHICLES) {
            alert("Máximo " + MAX_VEHICLES + " vehículos permitidos.");
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
        const $yearSelect = $card.find('.year-select');
        const currentYear = new Date().getFullYear();

        $yearSelect.empty().append('<option value="">Seleccione</option>');
        for (let y = currentYear; y >= 1980; y--) {
            $yearSelect.append(`<option value="${y}">${y}</option>`);
        }
        $yearSelect.append('<option value="other">Other</option>');
    }

    $(document).on('click', '.vehicle-delete-btn', function () {
        $(this).closest('.vehicle-card').remove();
    });

    // ===============================
    //   VIN → AUTOCOMPLETAR
    // ===============================
    $(document).on('blur', '.vin-input', function () {

        const vin = $(this).val();
        const $card = $(this).closest('.vehicle-card');
        if (!vin || vin.length < 5) return;

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${vin}?format=json`,
            function (res) {

                if (!res || !res.Results || !res.Results[0]) return;

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


    // ===============================
    //   AÑO → LISTA DE MAKES
    // ===============================
    $(document).on("change", ".year-select", function () {
        const $card = $(this).closest('.vehicle-card');
        const year = $(this).val();
        const $makeSel = $card.find('.make-select');
        const $modelSel = $card.find('.model-select');

        $makeSel.empty().append(`<option value="">Cargando marcas...</option>`);
        $modelSel.empty().append(`<option value="">Seleccione modelo</option>`);

        if (year === "other") {
            $(this).hide();
            $card.find('.year-other').show();
            return;
        }

        $.get("https://vpic.nhtsa.dot.gov/api/vehicles/getallmakes?format=json", function (res) {

            $makeSel.empty().append(`<option value="">Seleccione marca</option>`);

            res.Results.forEach(m => {
                if (m.Make_Name) {
                    $makeSel.append(`<option value="${m.Make_Name}">${m.Make_Name}</option>`);
                }
            });

            $makeSel.append(`<option value="other">Other</option>`);
        });
    });

    // ===============================
    //   MARCA → LISTA DE MODELOS
    // ===============================
    $(document).on("change", ".make-select", function () {

        const make = $(this).val();
        const $card = $(this).closest('.vehicle-card');
        const $modelSel = $card.find('.model-select');

        $modelSel.empty().append(`<option value="">Cargando modelos...</option>`);

        if (make === "other") {
            $(this).hide();
            $card.find('.make-other').show();
            return;
        }

        const encodedMake = encodeURIComponent(make);

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMake/${encodedMake}?format=json`,
            function (res) {

                $modelSel.empty().append(`<option value="">Seleccione modelo</option>`);

                if (!res.Results || res.Results.length === 0) {
                    $modelSel.append(`<option value="">No hay modelos</option>`);
                    return;
                }

                res.Results.forEach(m => {
                    if (m.Model_Name) {
                        $modelSel.append(`<option value="${m.Model_Name}">${m.Model_Name}</option>`);
                    }
                });

                $modelSel.append(`<option value="other">Other</option>`);
            }
        );
    });


    // ===============================
    //   MODELO CAMBIADO → IMAGEN
    // ===============================
    $(document).on('change', '.model-select', function () {

        const $card = $(this).closest('.vehicle-card');
        const year = $card.find('.year-select').val();
        const make = $card.find('.make-select').val();
        const model = $(this).val();

        if (!year || !make || !model) return;

        updateImageForCard($card, make, model, year);
    });


    // ===============================
    //   IMAGEN POR TARJETA
    // ===============================
    function updateImageForCard($card, make, model, year) {
        if (!make || !model || !year) return;

        const url =
            `https://cdn.imagin.studio/getImage?customer=img&make=${encodeURIComponent(make)}` +
            `&modelFamily=${encodeURIComponent(model)}` +
            `&modelYear=${year}&paintdescription=white&angle=28&zoomtype=fullscreen`;

        const cardId = $card.data('id');
        $(`#thumb_${cardId}`).css('background-image', `url('${url}')`);
    }

});
