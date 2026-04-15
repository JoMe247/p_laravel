$(document).ready(function () {
    // =========================================================================
    //   CONFIG GENERAL
    // =========================================================================
    const csrf = $('meta[name="csrf-token"]').attr("content");

    const $overlayNew = $("#policy-overlay");
    const $newBtn = $("#new-policy-btn");
    const $cancelNew = $("#policy-cancel-btn");
    const $saveNew = $("#policy-save-btn");

    const config = $("#policy-config");
    const storeUrl = config.data("store-url");

    // =========================================================================
    //   POLICY LOG
    // =========================================================================
    const $policyLogBtn = $("#policy-log-btn");
    const $policyLogOverlay = $("#policy-log-overlay");
    const $policyLogClose = $("#policy-log-close");

    $policyLogBtn.on("click", function () {
        $policyLogOverlay.css("display", "flex").hide().fadeIn(150);
    });

    $policyLogClose.on("click", function () {
        $policyLogOverlay.fadeOut(150);
    });

    $policyLogOverlay.on("click", function (e) {
        if (e.target === this) {
            $policyLogOverlay.fadeOut(150);
        }
    });

    // =========================================================================
    //   OVERLAY NUEVA POLICY
    // =========================================================================
    const polStatusInput = document.getElementById("pol_status");

    function resetNewPolicyFormStatus() {
        if (polStatusInput) {
            polStatusInput.value = "Active";
            polStatusInput.setAttribute("readonly", "readonly");
        }
    }

    $newBtn.on("click", function () {
        resetNewPolicyFormStatus();
        $overlayNew.css("display", "flex").hide().fadeIn(150);
    });

    $cancelNew.on("click", function () {
        $overlayNew.fadeOut(150);
    });

    // =========================================================================
    //   GUARDAR NUEVA POLICY (CREAR)
    // =========================================================================
    $saveNew.on("click", function () {
        let vehicules = [];

        $(".vehicle-card").each(function () {
            const $card = $(this);

            const vin = ($card.find(".vin-input").val() || "").trim();

            let year = $card.find(".year-select").val();
            if (!year || year === "other") {
                year = ($card.find(".year-other").val() || "").trim();
            }

            let make = $card.find(".make-select").val();
            if (!make || make === "other") {
                make = ($card.find(".make-other").val() || "").trim();
            }

            let model = $card.find(".model-select").val();
            if (!model || model === "other") {
                model = ($card.find(".model-other").val() || "").trim();
            }

            if (!vin && !year && !make && !model) return;

            vehicules.push({ vin, year, make, model });
        });

        let formData = {
            _token: csrf,
            pol_carrier: $("#pol_carrier").val(),
            pol_number: $("#pol_number").val(),
            pol_url: $("#pol_url").val(),
            pol_expiration: $("#pol_expiration").val(),
            pol_eff_date: $("#pol_eff_date").val(),
            pol_added_date: $("#pol_added_date").val(),
            pol_due_day: $("#pol_due_day").val(),
            pol_status: $("#pol_status").val() || "Active",
            pol_agent_record: $("#pol_agent_record").val(),
            vehicules: JSON.stringify(vehicules),
        };

        $.ajax({
            url: storeUrl,
            type: "POST",
            data: formData,
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert("Error saving policy");
                }
            },
            error: function (xhr) {
                console.error("Error saving policy:", xhr.responseText);
                alert("Error saving policy");
            },
        });
    });

    // =========================================================================
    //   ELIMINAR POLICY
    // =========================================================================
    $(".policy-delete-btn").on("click", function () {
        const url = $(this).data("url");

        if (!confirm("Delete this policy?")) return;

        $.ajax({
            url: url,
            type: "POST",
            data: {
                _token: csrf,
                _method: "DELETE",
            },
            success: function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert("Error deleting policy");
                }
            },
            error: function (xhr) {
                console.error("Error deleting policy:", xhr.responseText);
                alert("Error deleting policy");
            },
        });
    });

    // =========================================================================
    //   SISTEMA DE VEHÍCULOS (CREACIÓN)
    // =========================================================================
    const MAX_VEHICLES = 6;

    $("#add-vehicle-btn").on("click", function () {
        const count = $(".vehicle-card").length;
        if (count >= MAX_VEHICLES) {
            alert("Máximo " + MAX_VEHICLES + " vehículos permitidos.");
            return;
        }

        const id = Date.now();

        $("#vehicle-container").append(`
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
        const $yearSel = $card.find(".year-select");
        const currentYear = new Date().getFullYear();

        $yearSel.empty().append('<option value="">Seleccione</option>');
        for (let y = currentYear; y >= 1980; y--) {
            $yearSel.append(`<option value="${y}">${y}</option>`);
        }
        $yearSel.append('<option value="other">Other</option>');
    }

    // Eliminar vehículo SOLO en creación
    $(document).on("click", ".vehicle-card .vehicle-delete-btn", function () {
        $(this).closest(".vehicle-card").remove();
    });

    // --- VIN Autocompletar ---
    $(document).on("blur", ".vin-input", function () {
        const vin = $(this).val();
        const $card = $(this).closest(".vehicle-card");

        if (!vin || vin.length < 5) return;

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${vin}?format=json`,
            function (res) {
                if (!res?.Results?.[0]) return;
                const v = res.Results[0];

                const year = v.ModelYear;
                const make = v.Make;
                const model = v.Model;

                const $yearSel = $card.find(".year-select");
                const $makeSel = $card.find(".make-select");
                const $modelSel = $card.find(".model-select");

                if (year) {
                    if ($yearSel.find(`option[value="${year}"]`).length === 0) {
                        $yearSel.append(
                            `<option value="${year}">${year}</option>`,
                        );
                    }
                    $yearSel.val(year);
                }

                if (make) {
                    $makeSel
                        .empty()
                        .append(`<option value="${make}">${make}</option>`);
                }

                if (model) {
                    $modelSel
                        .empty()
                        .append(`<option value="${model}">${model}</option>`);
                }

                updateImageForCard($card, make, model, year);
            },
        );
    });

    const COMMON_MAKES = [
        "Toyota",
        "Honda",
        "Ford",
        "Chevrolet",
        "Nissan",
        "Hyundai",
        "Kia",
        "Jeep",
        "RAM",
        "GMC",
        "Subaru",
        "Mazda",
        "Volkswagen",
        "BMW",
        "Mercedes-Benz",
        "Audi",
        "Lexus",
        "Tesla",
        "Dodge",
        "Chrysler",
        "Buick",
        "Cadillac",
        "Volvo",
        "Mitsubishi",
    ];

    function fillMakeSelect($makeSel) {
        $makeSel.empty().append('<option value="">Seleccione marca</option>');
        COMMON_MAKES.forEach((make) => {
            $makeSel.append(`<option value="${make}">${make}</option>`);
        });
        $makeSel.append('<option value="other">Other</option>');
    }

    $(document).on("change", ".year-select", function () {
        const $card = $(this).closest(".vehicle-card");
        const year = $(this).val();
        const $makeSel = $card.find(".make-select");
        const $modelSel = $card.find(".model-select");

        $modelSel.empty().append('<option value="">Seleccione modelo</option>');

        if (year === "other") {
            $(this).hide();
            $card.find(".year-other").show();
            return;
        }

        fillMakeSelect($makeSel);
    });

    $(document).on("change", ".make-select", function () {
        const make = $(this).val();
        const $card = $(this).closest(".vehicle-card");
        const $modelSel = $card.find(".model-select");

        $modelSel
            .empty()
            .append('<option value="">Cargando modelos...</option>');

        if (make === "other") {
            $(this).hide();
            $card.find(".make-other").show();
            return;
        }

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMake/${encodeURIComponent(make)}?format=json`,
            function (res) {
                $modelSel
                    .empty()
                    .append('<option value="">Seleccione modelo</option>');

                if (!res.Results?.length) {
                    $modelSel.append(
                        '<option value="">No hay modelos</option>',
                    );
                    return;
                }

                const models = [
                    ...new Set(
                        res.Results.map((x) => x.Model_Name).filter(Boolean),
                    ),
                ].sort();

                models.forEach((model) => {
                    $modelSel.append(
                        `<option value="${model}">${model}</option>`,
                    );
                });

                $modelSel.append('<option value="other">Other</option>');
            },
        );
    });

    $(document).on("change", ".model-select", function () {
        const $card = $(this).closest(".vehicle-card");
        const year = $card.find(".year-select").val();
        const make = $card.find(".make-select").val();
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

        const id = $card.data("id");
        $(`#thumb_${id}`).css("background-image", `url('${url}')`);
    }

    // =========================================================================
    //   OVERLAY VER / EDITAR POLICY
    // =========================================================================
    const $overlayEdit = $("#policy-edit-overlay");
    const $overlayContent = $("#policy-edit-content");
    const $overlaySave = $("#policy-edit-save");
    const $overlayCancel = $("#policy-edit-cancel");

    $(document).on("click", ".policy-info-btn", function () {
        const showUrl = $(this).data("url");
        const updateUrl = $(this).data("update-url");

        $.get(showUrl, function (res) {
            if (!res || !res.success) {
                alert("Error loading policy data");
                return;
            }

            const p = res.policy;
            let veh = p.vehicules;

            if (typeof veh === "string") {
                try {
                    veh = JSON.parse(veh);
                } catch (e) {
                    veh = [];
                }
            }

            if (!Array.isArray(veh)) veh = [];

            initialPolicySnapshot = {
                pol_carrier: p.pol_carrier ?? "",
                pol_number: p.pol_number ?? "",
                pol_url: p.pol_url ?? "",
                pol_expiration: p.pol_expiration ?? "",
                pol_eff_date: p.pol_eff_date ?? "",
                pol_added_date: p.pol_added_date ?? "",
                pol_due_day: p.pol_due_day ?? "",
                pol_status: p.pol_status ?? "",
                pol_agent_record: p.pol_agent_record ?? "",
                vehicules: JSON.stringify(
                    veh.map((v) => ({
                        vin: v.vin ?? "",
                        year: v.year ?? "",
                        make: v.make ?? "",
                        model: v.model ?? "",
                    })),
                ),
            };

            let html = `
<div class="edit-grid">   
    <div class="edit-left">
        <label>Carrier</label>
        <input type="text" id="edit_pol_carrier" value="${p.pol_carrier ?? ""}">

        <label>Number</label>
        <input type="text" id="edit_pol_number" value="${p.pol_number ?? ""}">

        <label>URL</label>
        <input type="text" id="edit_pol_url" value="${p.pol_url ?? ""}">

        <label>Expiration</label>
        <input type="date" id="edit_pol_expiration" value="${p.pol_expiration ?? ""}">

        <label>Eff Date</label>
        <input type="date" id="edit_pol_eff_date" value="${p.pol_eff_date ?? ""}">

        <label>Added Date</label>
        <input type="date" id="edit_pol_added_date" value="${p.pol_added_date ?? ""}">

        <label>Due Day</label>
        <input type="text" id="edit_pol_due_day" value="${p.pol_due_day ?? ""}">

       <label>Status</label>
<input type="text" id="edit_pol_status" value="${p.pol_status ?? ""}" readonly>

<label>Agent Record</label>
<input type="text" id="edit_pol_agent_record" value="${p.pol_agent_record ?? ""}">
    </div>

    <div class="edit-right">
    <div style="display:flex; flex-wrap:wrap; gap:10px; margin:0 0 14px 0;">
        <button type="button" class="btn add-vehicle-btn edit-status-btn" data-status="Expired" style="margin:0;">
            Expire Policy
        </button>

        <button type="button" class="btn add-vehicle-btn edit-status-btn" data-status="Renewed" style="margin:0;">
            Renew Policy
        </button>

        <button type="button" class="btn add-vehicle-btn edit-status-btn" data-status="Canceled" style="margin:0;">
            Cancel Policy
        </button>
    </div>

    <h4>Vehicles</h4>
    <div class="edit-vehicles-grid">
`;

            veh.forEach((v, index) => {
                const make = v.make || "";
                const model = v.model || "";
                const year = v.year || "";

                let imgUrl = "";
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
                     style="background-image:url('${imgUrl}');"></div>

                <label>VIN</label>
                <input type="text" class="edit_vin" value="${v.vin || ""}">

                <label>Year</label>
                <input type="text" class="edit_year" value="${year}">

                <label>Make</label>
                <input type="text" class="edit_make" value="${make}">

                <label>Model</label>
                <input type="text" class="edit_model" value="${model}">
                <div class="vehicle-delete-btn">Eliminar Vehículo</div>
            </div>
        `;
            });

            html += `
        </div>
    </div>
</div>
`;

            $overlayContent.html(html);
            $overlayEdit.fadeIn(150);

            $overlaySave.off().on("click", function () {
                let updatedVeh = [];

                $(".vehicle-edit-card").each(function () {
                    updatedVeh.push({
                        vin: ($(this).find(".edit_vin").val() || "").trim(),
                        year: ($(this).find(".edit_year").val() || "").trim(),
                        make: ($(this).find(".edit_make").val() || "").trim(),
                        model: ($(this).find(".edit_model").val() || "").trim(),
                    });
                });

                const currentPolicySnapshot = {
                    pol_carrier: ($("#edit_pol_carrier").val() || "").trim(),
                    pol_number: ($("#edit_pol_number").val() || "").trim(),
                    pol_url: ($("#edit_pol_url").val() || "").trim(),
                    pol_expiration: (
                        $("#edit_pol_expiration").val() || ""
                    ).trim(),
                    pol_eff_date: ($("#edit_pol_eff_date").val() || "").trim(),
                    pol_added_date: (
                        $("#edit_pol_added_date").val() || ""
                    ).trim(),
                    pol_due_day: ($("#edit_pol_due_day").val() || "").trim(),
                    pol_status: ($("#edit_pol_status").val() || "").trim(),
                    pol_agent_record: (
                        $("#edit_pol_agent_record").val() || ""
                    ).trim(),
                    vehicules: JSON.stringify(updatedVeh),
                };

                if (
                    initialPolicySnapshot &&
                    JSON.stringify(currentPolicySnapshot) ===
                        JSON.stringify(initialPolicySnapshot)
                ) {
                    alert("No changes to save.");
                    return;
                }

                $.ajax({
                    url: updateUrl,
                    type: "POST",
                    data: {
                        _token: csrf,
                        pol_carrier: currentPolicySnapshot.pol_carrier,
                        pol_number: currentPolicySnapshot.pol_number,
                        pol_url: currentPolicySnapshot.pol_url,
                        pol_expiration: currentPolicySnapshot.pol_expiration,
                        pol_eff_date: currentPolicySnapshot.pol_eff_date,
                        pol_added_date: currentPolicySnapshot.pol_added_date,
                        pol_due_day: currentPolicySnapshot.pol_due_day,
                        pol_status: currentPolicySnapshot.pol_status,
                        pol_agent_record:
                            currentPolicySnapshot.pol_agent_record,
                        vehicules: currentPolicySnapshot.vehicules,
                    },
                    success: function (r) {
                        if (r.success) {
                            location.reload();
                        } else {
                            alert("Error updating policy");
                        }
                    },
                    error: function (xhr) {
                        console.error(
                            "Error updating policy:",
                            xhr.responseText,
                        );
                        alert("Error updating policy");
                    },
                });
            });
        });
    });

    $overlayCancel.on("click", function () {
        $overlayEdit.fadeOut(150);
    });

    $(document).on("click", ".edit-status-btn", function () {
        const newStatus = $(this).data("status");

        $("#edit_pol_status").val(newStatus);

        // Guarda inmediatamente usando el mismo botón Save Changes
        // $("#policy-edit-save").trigger("click");
    });

    // Eliminar vehículo SOLO en edición
    $(document).on(
        "click",
        ".vehicle-edit-card .vehicle-delete-btn",
        function () {
            $(this).closest(".vehicle-edit-card").remove();
        },
    );

    function updateEditImage(index) {
        const $card = $(`.vehicle-edit-card[data-index="${index}"]`);
        const year = $card.find(".edit_year").val();
        const make = $card.find(".edit_make").val();
        const model = $card.find(".edit_model").val();

        if (!year || !make || !model) return;

        const imgUrl =
            `https://cdn.imagin.studio/getImage?customer=img&make=${encodeURIComponent(make)}` +
            `&modelFamily=${encodeURIComponent(model)}&modelYear=${year}` +
            `&paintdescription=white&angle=28&zoomtype=fullscreen`;

        $(`#vehicle_edit_thumb_${index}`).css(
            "background-image",
            `url('${imgUrl}')`,
        );
    }

    $(document).on("blur", ".edit_vin", function () {
        const vin = $(this).val();
        const index = $(this).closest(".vehicle-edit-card").data("index");

        if (!vin) return;

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${vin}?format=json`,
            function (res) {
                if (!res?.Results?.[0]) return;

                const v = res.Results[0];
                const $card = $(`.vehicle-edit-card[data-index="${index}"]`);

                if (v.ModelYear) $card.find(".edit_year").val(v.ModelYear);
                if (v.Make) $card.find(".edit_make").val(v.Make);
                if (v.Model) $card.find(".edit_model").val(v.Model);

                updateEditImage(index);
            },
        );
    });

    $(document).on(
        "change keyup",
        ".edit_year, .edit_make, .edit_model",
        function () {
            const index = $(this).closest(".vehicle-edit-card").data("index");
            updateEditImage(index);
        },
    );

    $(document).on("click", "#add-vehicle-btn-edit", function () {
        const $grid = $("#policy-edit-content").find(".edit-vehicles-grid");
        if (!$grid.length) return;

        const count = $grid.find(".vehicle-edit-card").length;
        if (count >= MAX_VEHICLES) {
            alert("Máximo " + MAX_VEHICLES + " vehículos permitidos.");
            return;
        }

        const index = Date.now();

        $grid.append(`
            <div class="vehicle-edit-card" data-index="${index}">
                <div class="vehicle-edit-thumb"
                     id="vehicle_edit_thumb_${index}"
                     style="background-image:url('');"></div>

                <label>VIN</label>
                <input type="text" class="edit_vin" value="">

                <label>Year</label>
                <input type="text" class="edit_year" value="">

                <label>Make</label>
                <input type="text" class="edit_make" value="">

                <label>Model</label>
                <input type="text" class="edit_model" value="">

                <div class="vehicle-delete-btn">Eliminar Vehículo</div>
            </div>
        `);
    });
});
