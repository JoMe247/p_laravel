$(document).ready(function () {
    const $overlay = $('#policy-overlay');
    const config   = $('#policy-config');

    const storeUrl = config.data('store-url');
    const csrf     = config.data('csrf');

    // Abrir overlay
    $('#new-policy-btn').on('click', function () {
        $overlay.css('display', 'flex').hide().fadeIn(150);
    });

    // Cerrar overlay
    $('#policy-cancel-btn').on('click', function () {
        $overlay.fadeOut(150);
    });

    // Guardar policy
    $('#policy-save-btn').on('click', function () {
        $.ajax({
            url: storeUrl,
            method: 'POST',
            data: {
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
                vin: $('#vin').val(),
                year: $('#year').val(),
                make: $('#make').val(),
                model: $('#model').val(),
            },
            success: function () {
                location.reload();
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error saving policy');
            }
        });
    });

    // Eliminar policy
    $('.policy-delete-btn').on('click', function () {
        const url = $(this).data('url');

        if (!confirm('Delete this policy?')) return;

        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: csrf
            },
            success: function () {
                location.reload();
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                alert('Error deleting policy');
            }
        });
    });
});
