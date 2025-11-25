$(document).ready(function () {

    // ============================
    // OPEN/CLOSE OVERLAY
    // ============================

    $("#newTicketBtn").on("click", function () {
        $("#ticket-overlay").css("display", "flex");
    });

    $("#closeTicketOverlay").on("click", function () {
        $("#ticket-overlay").hide();
    });

    // ====================================================
    // FUNCTION: UPDATE STATUS COUNTERS AUTOMATICALLY
    // ====================================================
    function updateCounters() {
        let counts = {
            "Open": 0,
            "In Progress": 0,
            "Answered": 0,
            "On Hold": 0,
            "Closed": 0,
        };

        // Count rows dynamically
        $("#ticket-table-body tr").each(function () {
            let status = $(this).find(".edit-status").val();
            if (counts[status] !== undefined) {
                counts[status]++;
            }
        });

        // Update display
        $(".st-open").text(counts["Open"] + " Open");
        $(".st-progress").text(counts["In Progress"] + " In Progress");
        $(".st-answered").text(counts["Answered"] + " Answered");
        $(".st-hold").text(counts["On Hold"] + " On Hold");
        $(".st-closed").text(counts["Closed"] + " Closed");
    }

    // Run on load
    updateCounters();


    // ============================
    // EDIT STATUS (AJAX)
    // ============================

    $(".edit-status").on("change", function () {

        let id = $(this).data("id");
        let value = $(this).val();

        $.post("/help/update-status", {
            _token: $('meta[name="csrf-token"]').attr("content"),
            id: id,
            status: value
        }).done(function () {
            updateCounters(); // auto update counters
        });
    });

    // ============================
    // SHOW ALL TICKETS
    // ============================
    $(document).on("click", ".st-all", function () {

        // Show all rows
        $("#ticket-table-body tr").show();

        // Recalculate counters
        updateCounters();
    });



    // ============================
    // EDIT PRIORITY (AJAX)
    // ============================

    $(".edit-priority").on("change", function () {

        let id = $(this).data("id");
        let value = $(this).val();

        $.post("/help/update-priority", {
            _token: $('meta[name="csrf-token"]').attr("content"),
            id: id,
            priority: value
        });
    });


    // ============================
    // FILTER BY STATUS (BUTTONS)
    // ============================

    $(document).on("click", ".st", function () {

        let raw = $(this).text().trim();   // ejemplo: "3 In Progress"

        // EXTRAER SOLO EL TEXTO DEL STATUS
        let status = raw.replace(/[0-9]/g, '').trim();   // resultado: "In Progress"

        // BOTÓN ALL → mostrar todo
        if (status === "All") {
            $("#ticket-table-body tr").show();
            updateCounters();
            return;
        }

        // FILTRAR FILAS
        $("#ticket-table-body tr").each(function () {

            let rowStatus = $(this).find(".edit-status").val().trim();

            if (rowStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        updateCounters();
    });

    // ============================
    // SEARCH BAR
    // ============================

    $("#ticket-search-input").on("keyup", function () {

        let value = $(this).val().toLowerCase();

        $("#ticket-table-body tr").filter(function () {

            $(this).toggle(
                $(this).text().toLowerCase().indexOf(value) > -1
            );

        });

        updateCounters(); // recalc for visible rows
    });

});

// ============================
// SHOW DESCRIPTION OVERLAY
// ============================
$(document).on("click", ".action-info", function () {

    let desc = $(this).data("description");

    $("#description-text").text(desc);
    $("#description-overlay").css("display", "flex");
});

$(document).on("click", "#closeDescription", function () {
    $("#description-overlay").hide();
});

// ============================
// DELETE TICKET
// ============================
$(document).on("click", ".action-delete", function () {

    let id = $(this).data("id");

    Swal.fire({
        title: "Delete Ticket?",
        text: "This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Delete"
    }).then((result) => {

        if (result.isConfirmed) {

            $.post("/help/delete", {
                _token: $('meta[name="csrf-token"]').attr("content"),
                id: id
            })
                .done(function (resp) {
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: "The ticket has been deleted.",
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // Remove row from table instantly
                    $('i.action-delete[data-id="' + id + '"]').closest("tr").remove();

                    // Update counters after deletion
                    updateCounters();
                })
                .fail(function () {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Could not delete the ticket."
                    });
                });

        }

    });

});

