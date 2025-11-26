$(document).ready(function () {

    // ============================================================
    // OPEN / CLOSE CREATE TASK OVERLAY
    // ============================================================

    $(document).on("click", "#btn-new-task", function () {
        $("#task-overlay").addClass("show");
    });

    $(document).on("click", "#cancel-task", function () {
        $("#task-overlay").removeClass("show");
    });


    // ============================================================
    // OPEN / CLOSE DESCRIPTION OVERLAY
    // ============================================================

    $(document).on("click", ".info-btn", function () {
        $("#desc-text").text($(this).data("desc"));
        $("#desc-overlay").addClass("show");
    });

    $(document).on("click", "#close-desc", function () {
        $("#desc-overlay").removeClass("show");
    });


    // ============================================================
    // STATUS COUNTERS
    // ============================================================

    function updateCounters() {
        let counters = {
            "Open": 0,
            "In Progress": 0,
            "Closed": 0
        };

        $("#task-body tr").each(function () {
            let status = $(this).find(".edit-status").val();
            if (counters[status] !== undefined) {
                counters[status]++;
            }
        });

        // Update buttons
        $(".filter-btn[data-filter='all'] .count")
            .text("(" + (counters["Open"] + counters["In Progress"] + counters["Closed"]) + ")");

        $(".filter-btn[data-filter='Open'] .count").text("(" + counters["Open"] + ")");
        $(".filter-btn[data-filter='In Progress'] .count").text("(" + counters["In Progress"] + ")");
        $(".filter-btn[data-filter='Closed'] .count").text("(" + counters["Closed"] + ")");
    }

    updateCounters();


    // ============================================================
    // UPDATE STATUS (AJAX)
    // ============================================================

    $(document).on("change", ".edit-status", function () {
        let id = $(this).data("id");
        let status = $(this).val();

        $.post("/tasks/update-status", {
            _token: $('meta[name="csrf-token"]').attr("content"),
            id: id,
            status: status
        }).always(function () {
            updateCounters();
        });
    });


    // ============================================================
    // UPDATE PRIORITY (AJAX)
    // ============================================================

    $(document).on("change", ".edit-priority", function () {
        let id = $(this).data("id");
        let priority = $(this).val();

        $.post("/tasks/update-priority", {
            _token: $('meta[name="csrf-token"]').attr("content"),
            id: id,
            priority: priority
        });
    });


    // ============================================================
    // DELETE TASK
    // ============================================================

    $(document).on("click", ".delete-btn", function () {

        let id = $(this).data("id");

        Swal.fire({
            title: "Delete Task?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Delete",
            confirmButtonColor: "#d33"
        }).then(result => {

            if (result.isConfirmed) {

                $.post("/tasks/delete", {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    id: id
                }).done(() => {

                    $('i.delete-btn[data-id="' + id + '"]').closest("tr").remove();
                    updateCounters();

                });
            }
        });
    });


    // ============================================================
    // FILTER BUTTONS
    // ============================================================

    $(document).on("click", ".filter-btn", function () {

        $(".filter-btn").removeClass("active");
        $(this).addClass("active");

        let filter = $(this).data("filter");

        if (filter === "all") {
            $("#task-body tr").show();
        } else {
            $("#task-body tr").each(function () {
                let status = $(this).find(".edit-status").val();
                $(this).toggle(status === filter);
            });
        }

        updateCounters();
    });


    // ============================================================
    // SEARCH BAR
    // ============================================================

    $("#task-search-input").on("keyup", function () {

        let text = $(this).val().toLowerCase();

        $("#task-body tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(text) > -1);
        });

        updateCounters();
    });

});
