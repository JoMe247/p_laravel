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
    const $policyLogPrint = $("#policy-log-print");

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

    $policyLogPrint.on("click", function () {
        const logHtml = ($("#policy-log-text").html() || "").trim();

        const printWindow = window.open("", "_blank", "width=900,height=700");
        if (!printWindow) {
            alert(
                "Pop-up blocked. Please allow pop-ups to print the Policy Log.",
            );
            return;
        }

        printWindow.document.write(`
        <html>
        <head>
            <title>Policy Log</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 30px;
                    color: #222;
                }

                h1 {
                    margin: 0 0 20px 0;
                    font-size: 24px;
                }

                .print-log-wrapper {
                    width: 100%;
                }

                .policy-log-entry {
    border: 1px solid #ccc;
    border-radius: 8px;
    padding: 14px 16px;
    margin-bottom: 12px;
    background: #fff;
    font-size: 13px;
    line-height: 1.7;
    text-align: left;
    word-break: break-word;
    position: relative;
}

.policy-log-entry div {
    margin-bottom: 4px;
}

                .policy-log-entry strong {
                    font-weight: 700;
                }
            </style>
        </head>
        <body>
            <h1>Policy Log</h1>
            <div class="print-log-wrapper">
                ${logHtml || '<div class="policy-log-entry">No policy logs found.</div>'}
            </div>
        </body>
        </html>
    `);

        printWindow.document.close();
        printWindow.focus();

        setTimeout(() => {
            printWindow.print();
        }, 250);
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
    let initialPolicySnapshot = null;

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

        <button type="button" id="add-vehicle-btn-edit" class="btn add-vehicle-btn" style="margin:0;">
            <i class='bx bx-car' style="font-size:1.4em"></i>&nbsp; Añadir Vehículo
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

        <div class="vehicle-field">
            <label>VIN (opcional)</label>
            <input type="text" class="edit_vin" value="${v.vin || ""}">
        </div>

        <div class="vehicle-field">
            <label>Año</label>
            <select class="edit_year_select">
                <option value="">Seleccione</option>
            </select>
            <input type="text" class="edit_year_other" style="display:none;" placeholder="Otro año">
        </div>

        <div class="vehicle-field">
            <label>Make</label>
            <select class="edit_make_select">
                <option value="">Seleccione</option>
            </select>
            <input type="text" class="edit_make_other" style="display:none;" placeholder="Otra marca">
        </div>

        <div class="vehicle-field">
            <label>Model</label>
            <select class="edit_model_select">
                <option value="">Seleccione</option>
            </select>
            <input type="text" class="edit_model_other" style="display:none;" placeholder="Otro modelo">
        </div>

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

            $("#policy-edit-content .vehicle-edit-card").each(function (i) {
                initEditVehicleCard($(this), veh[i] || {});
            });

            $overlaySave.off().on("click", function () {
                let updatedVeh = [];

                $(".vehicle-edit-card").each(function () {
                    const $card = $(this);

                    const vin = ($card.find(".edit_vin").val() || "").trim();

                    let year = $card.find(".edit_year_select").val();
                    if (!year || year === "other") {
                        year = (
                            $card.find(".edit_year_other").val() || ""
                        ).trim();
                    }

                    let make = $card.find(".edit_make_select").val();
                    if (!make || make === "other") {
                        make = (
                            $card.find(".edit_make_other").val() || ""
                        ).trim();
                    }

                    let model = $card.find(".edit_model_select").val();
                    if (!model || model === "other") {
                        model = (
                            $card.find(".edit_model_other").val() || ""
                        ).trim();
                    }

                    if (!vin && !year && !make && !model) return;

                    updatedVeh.push({ vin, year, make, model });
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

    function initYearsForEditCard($card, selectedYear = "") {
        const $yearSel = $card.find(".edit_year_select");
        const $yearOther = $card.find(".edit_year_other");
        const currentYear = new Date().getFullYear();

        $yearSel.empty().append('<option value="">Seleccione</option>');
        for (let y = currentYear; y >= 1980; y--) {
            $yearSel.append(`<option value="${y}">${y}</option>`);
        }
        $yearSel.append('<option value="other">Other</option>');

        if (selectedYear) {
            const exists =
                $yearSel.find(`option[value="${selectedYear}"]`).length > 0;
            if (exists) {
                $yearSel.val(String(selectedYear));
                $yearOther.hide().val("");
                $yearSel.show();
            } else {
                $yearSel.val("other");
                $yearOther.val(selectedYear).show();
            }
        }
    }

    function fillEditMakeSelect($makeSel, selectedMake = "") {
        $makeSel.empty().append('<option value="">Seleccione marca</option>');
        COMMON_MAKES.forEach((make) => {
            $makeSel.append(`<option value="${make}">${make}</option>`);
        });
        $makeSel.append('<option value="other">Other</option>');

        if (selectedMake) {
            const exists =
                $makeSel.find(`option[value="${selectedMake}"]`).length > 0;
            $makeSel.val(exists ? selectedMake : "other");
        }
    }

    function loadEditModelsForCard($card, make, selectedModel = "") {
        const $modelSel = $card.find(".edit_model_select");
        const $modelOther = $card.find(".edit_model_other");

        $modelSel
            .empty()
            .append('<option value="">Cargando modelos...</option>');

        if (!make || make === "other") {
            $modelSel
                .empty()
                .append('<option value="">Seleccione modelo</option>');
            if (selectedModel) {
                $modelSel.val("other");
                $modelOther.val(selectedModel).show();
            }
            updateEditImageByCard($card);
            return;
        }

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/GetModelsForMake/${encodeURIComponent(make)}?format=json`,
            function (res) {
                $modelSel
                    .empty()
                    .append('<option value="">Seleccione modelo</option>');

                if (!res.Results?.length) {
                    $modelSel.append('<option value="other">Other</option>');
                    if (selectedModel) {
                        $modelSel.val("other");
                        $modelOther.val(selectedModel).show();
                    }
                    updateEditImageByCard($card);
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

                if (selectedModel) {
                    const exists =
                        $modelSel.find(`option[value="${selectedModel}"]`)
                            .length > 0;
                    if (exists) {
                        $modelSel.val(selectedModel);
                        $modelOther.hide().val("");
                    } else {
                        $modelSel.val("other");
                        $modelOther.val(selectedModel).show();
                    }
                }

                updateEditImageByCard($card);
            },
        ).fail(function () {
            updateEditImageByCard($card);
        });
    }

    function updateEditImageByCard($card) {
        let year = $card.find(".edit_year_select").val();
        if (!year || year === "other") {
            year = ($card.find(".edit_year_other").val() || "").trim();
        }

        let make = $card.find(".edit_make_select").val();
        if (!make || make === "other") {
            make = ($card.find(".edit_make_other").val() || "").trim();
        }

        let model = $card.find(".edit_model_select").val();
        if (!model || model === "other") {
            model = ($card.find(".edit_model_other").val() || "").trim();
        }

        if (!make || !model || !year) return;

        const index = $card.data("index");
        const imgUrl =
            `https://cdn.imagin.studio/getImage?customer=img&make=${encodeURIComponent(make)}` +
            `&modelFamily=${encodeURIComponent(model)}&modelYear=${year}` +
            `&paintdescription=white&angle=28&zoomtype=fullscreen`;

        $(`#vehicle_edit_thumb_${index}`).css(
            "background-image",
            `url('${imgUrl}')`,
        );
    }

    function initEditVehicleCard($card, vehicle = {}) {
        const vin = vehicle.vin || "";
        const year = vehicle.year || "";
        const make = vehicle.make || "";
        const model = vehicle.model || "";

        $card.find(".edit_vin").val(vin);

        initYearsForEditCard($card, year);

        const $makeSel = $card.find(".edit_make_select");
        const $makeOther = $card.find(".edit_make_other");
        fillEditMakeSelect($makeSel, make);

        if (make) {
            const exists = $makeSel.find(`option[value="${make}"]`).length > 0;
            if (exists) {
                $makeSel.val(make).show();
                $makeOther.hide().val("");
            } else {
                $makeSel.val("other").show();
                $makeOther.val(make).show();
            }
        }

        if (make && make !== "other") {
            loadEditModelsForCard($card, make, model);
        } else if (model) {
            $card.find(".edit_model_select").val("other");
            $card.find(".edit_model_other").val(model).show();
            updateEditImageByCard($card);
        } else {
            updateEditImageByCard($card);
        }
    }

    $(document).on("blur", ".vehicle-edit-card .edit_vin", function () {
        const vin = ($(this).val() || "").trim();
        const $card = $(this).closest(".vehicle-edit-card");

        if (!vin || vin.length < 5) return;

        $.get(
            `https://vpic.nhtsa.dot.gov/api/vehicles/decodevinvalues/${vin}?format=json`,
            function (res) {
                if (!res?.Results?.[0]) return;

                const v = res.Results[0];
                const year = v.ModelYear || "";
                const make = v.Make || "";
                const model = v.Model || "";

                if (year) {
                    initYearsForEditCard($card, year);
                }

                if (make) {
                    const $makeSel = $card.find(".edit_make_select");
                    const $makeOther = $card.find(".edit_make_other");
                    fillEditMakeSelect($makeSel, make);

                    const exists =
                        $makeSel.find(`option[value="${make}"]`).length > 0;
                    if (exists) {
                        $makeSel.val(make).show();
                        $makeOther.hide().val("");
                    } else {
                        $makeSel.val("other").show();
                        $makeOther.val(make).show();
                    }

                    loadEditModelsForCard($card, make, model);
                }

                setTimeout(() => {
                    updateEditImageByCard($card);
                }, 150);
            },
        );
    });

    $(document).on(
        "change",
        ".vehicle-edit-card .edit_year_select",
        function () {
            const $card = $(this).closest(".vehicle-edit-card");

            if ($(this).val() === "other") {
                $(this).hide();
                $card.find(".edit_year_other").show().focus();
                return;
            }

            $card.find(".edit_year_other").hide().val("");
            updateEditImageByCard($card);
        },
    );

    $(document).on(
        "change",
        ".vehicle-edit-card .edit_make_select",
        function () {
            const $card = $(this).closest(".vehicle-edit-card");
            const make = $(this).val();
            const $modelSel = $card.find(".edit_model_select");
            const $modelOther = $card.find(".edit_model_other");

            $modelSel
                .empty()
                .append('<option value="">Seleccione modelo</option>');
            $modelOther.hide().val("");

            if (make === "other") {
                $(this).hide();
                $card.find(".edit_make_other").show().focus();
                return;
            }

            $card.find(".edit_make_other").hide().val("");
            loadEditModelsForCard($card, make, "");
        },
    );

    $(document).on(
        "change",
        ".vehicle-edit-card .edit_model_select",
        function () {
            const $card = $(this).closest(".vehicle-edit-card");

            if ($(this).val() === "other") {
                $(this).hide();
                $card.find(".edit_model_other").show().focus();
                return;
            }

            $card.find(".edit_model_other").hide().val("");
            updateEditImageByCard($card);
        },
    );

    $(document).on(
        "keyup change",
        ".vehicle-edit-card .edit_year_other, .vehicle-edit-card .edit_make_other, .vehicle-edit-card .edit_model_other",
        function () {
            const $card = $(this).closest(".vehicle-edit-card");
            updateEditImageByCard($card);
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
            <div class="vehicle-edit-thumb" id="vehicle_edit_thumb_${index}"></div>

            <div class="vehicle-field">
                <label>VIN (opcional)</label>
                <input type="text" class="edit_vin" value="">
            </div>

            <div class="vehicle-field">
                <label>Año</label>
                <select class="edit_year_select">
                    <option value="">Seleccione</option>
                </select>
                <input type="text" class="edit_year_other" style="display:none;" placeholder="Otro año">
            </div>

            <div class="vehicle-field">
                <label>Make</label>
                <select class="edit_make_select">
                    <option value="">Seleccione</option>
                </select>
                <input type="text" class="edit_make_other" style="display:none;" placeholder="Otra marca">
            </div>

            <div class="vehicle-field">
                <label>Model</label>
                <select class="edit_model_select">
                    <option value="">Seleccione</option>
                </select>
                <input type="text" class="edit_model_other" style="display:none;" placeholder="Otro modelo">
            </div>

            <div class="vehicle-delete-btn">Eliminar Vehículo</div>
        </div>
    `);

        initEditVehicleCard(
            $grid.find(`.vehicle-edit-card[data-index="${index}"]`),
            {},
        );
    });
});
