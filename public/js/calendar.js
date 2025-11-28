document.addEventListener('DOMContentLoaded', function () {

    let selectedEvent = null;
    let selectedColor = '#3B82F6';

    let calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        height: 'auto',

        dateClick: function(info) {
            resetOverlay();
            selectedEvent = null;
            document.getElementById("overlay-title").innerText = "Add new event";
            document.getElementById("delete-event").style.display = "none"; 

            document.getElementById("event-start").value = info.dateStr + "T00:00";
            document.getElementById("event-overlay").style.display = "flex";
        },

        eventClick: function(info) {
            let ev = info.event;

            selectedEvent = ev;

            document.getElementById("overlay-title").innerText = "Edit event";
            document.getElementById("delete-event").style.display = "inline-block";

            document.getElementById('event-title').value = ev.title;
            document.getElementById('event-description').value = ev.extendedProps.description ?? '';
            document.getElementById('event-start').value = ev.startStr.replace("Z", "");
            document.getElementById('event-end').value = ev.endStr ? ev.endStr.replace("Z", "") : '';
            document.getElementById('notif-value').value = ev.extendedProps.notification_value ?? 0;
            document.getElementById('notif-unit').value = ev.extendedProps.notification_unit ?? 'minutes';
            document.getElementById('is-public').checked = ev.extendedProps.is_public == 1;

            // seleccionar color
            document.querySelectorAll('.color-box').forEach(b => b.classList.remove('selected'));
            document.querySelector(`.color-box[data-color="${ev.backgroundColor}"]`)?.classList.add('selected');
            selectedColor = ev.backgroundColor;

            document.getElementById("event-overlay").style.display = "flex";
        }
    });

    calendar.render();


    // Load events from backend
    fetch('/calendar/events')
        .then(res => res.json())
        .then(events => {
            events.forEach(e => calendar.addEvent(e));
        });


    // color selection
    document.querySelectorAll('.color-box').forEach(box => {
        box.addEventListener('click', () => {
            document.querySelectorAll('.color-box').forEach(b => b.classList.remove('selected'));
            box.classList.add('selected');
            selectedColor = box.dataset.color;
        });
    });

    function resetOverlay(){
        document.getElementById('event-title').value = "";
        document.getElementById('event-description').value = "";
        document.getElementById('event-end').value = "";
        document.getElementById('notif-value').value = 30;
        document.getElementById('is-public').checked = false;
        document.getElementById("delete-event").style.display = "none";
    }

    // close
    document.getElementById("close-overlay").addEventListener('click', () => {
        document.getElementById("event-overlay").style.display = "none";
    });


    // save/update
    document.getElementById("save-event").addEventListener('click', () => {

        let payload = {
            id: selectedEvent ? selectedEvent.id : null,
            title: document.getElementById('event-title').value,
            description: document.getElementById('event-description').value,
            start_date: document.getElementById('event-start').value,
            end_date: document.getElementById('event-end').value,
            notification_value: document.getElementById('notif-value').value,
            notification_unit: document.getElementById('notif-unit').value,
            color: selectedColor,
            is_public: document.getElementById('is-public').checked ? 1 : 0,
        };

        let url = selectedEvent ? '/calendar/update' : '/calendar/save';

        fetch(url, {
            method: "POST",
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector("meta[name='csrf-token']").content
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(() => location.reload());
    });


    // delete event
    document.getElementById("delete-event").addEventListener('click', () => {

        if (!selectedEvent) return;

        fetch('/calendar/delete/' + selectedEvent.id, {
            method: "DELETE",
            headers: {
                'X-CSRF-TOKEN': document.querySelector("meta[name='csrf-token']").content
            }
        })
        .then(res => res.json())
        .then(() => location.reload());
    });

});
