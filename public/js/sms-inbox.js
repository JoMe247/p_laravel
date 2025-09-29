const twilioFrom = window.twilioFrom;
const token = window.csrfToken;

function el(q) { return document.querySelector(q); }
function els(q) { return Array.from(document.querySelectorAll(q)); }

// ------------------ Selección múltiple ------------------
const btnDeleteSelected = el('#btnDeleteSelected');
const checkAll = el('#checkAll');
const btnDeleteConversation = el('#btnDeleteConversation'); // Nuevo botón

function updateDeleteButton() {
    const checked = els('.contact-check:checked');
    btnDeleteSelected.disabled = checked.length === 0;
    checkAll.checked = checked.length === els('.contact-check').length && checked.length > 0;
}

// Evento para cada checkbox
el('#contacts')?.addEventListener('change', function (e) {
    if (e.target.classList.contains('contact-check')) {
        updateDeleteButton();
    }
});

// Seleccionar todo
checkAll?.addEventListener('change', function () {
    const checked = this.checked;
    els('.contact-check').forEach(c => c.checked = checked);
    updateDeleteButton();
});

// ------------------ Eliminar conversaciones ------------------

// Eliminar seleccionadas
btnDeleteSelected?.addEventListener('click', async function () {
    const selected = els('.contact-check:checked').map(c => c.value);
    if (selected.length === 0) return;
    if (!confirm(`Eliminar ${selected.length} conversaciones?`)) return;

    try {
        const res = await fetch(window.routes.deleteMany, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ contacts: selected })
        });

        const data = await res.json();
        alert(data.message || 'Conversaciones eliminadas');

        // Remover del DOM sin recargar
        selected.forEach(c => {
            const row = el(`.sms-contact[data-contact="${c}"]`);
            if (row) row.remove();
        });
        updateDeleteButton();
    } catch (err) {
        alert('Error al eliminar conversaciones');
    }
});

// Eliminar una sola desde la lista
el('#contacts')?.addEventListener('click', async function (e) {
    const row = e.target.closest('.sms-contact');
    if (!row) return;

    if (e.target.classList.contains('btnDeleteOne')) {
        const contact = row.getAttribute('data-contact');
        if (!confirm(`¿Eliminar la conversación con ${contact}?`)) return;

        try {
            const res = await fetch(window.routes.deleteOne.replace(':contact', encodeURIComponent(contact)), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            });

            const data = await res.json();
            alert(data.message || 'Conversación eliminada');

            // Remover del DOM sin recargar
            row.remove();
            updateDeleteButton();
        } catch (err) {
            alert('Error al eliminar conversación');
        }
    } else if (!e.target.classList.contains('contact-check')) {
        // Cargar conversación
        const contact = row.getAttribute('data-contact');
        loadConversation(contact);
    }
});

// ------------------ Eliminar conversación actual ------------------
btnDeleteConversation?.addEventListener('click', async function () {
    const contact = el('#currentContact')?.innerText.trim();
    if (!contact) return;

    if (!confirm(`¿Eliminar la conversación con ${contact}?`)) return;

    try {
        const res = await fetch(
            window.routes.deleteOne.replace(':contact', encodeURIComponent(contact)),
            {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            }
        );

        const data = await res.json();
        alert(data.message || 'Conversación eliminada');

        // Limpiar vista de conversación
        el('#messagesPane').innerHTML = '<div class="empty">No hay conversación.</div>';
        el('#currentContact').innerText = '';
        el('#composer').style.display = 'none';

        // También quitarla de la lista de contactos
        const row = el(`.sms-contact[data-contact="${contact}"]`);
        if (row) row.remove();

        // Deshabilitar el botón nuevamente
        btnDeleteConversation.disabled = true;
    } catch (err) {
        alert('Error eliminando conversación');
    }
});

// ------------------ Funciones de búsqueda ------------------
el('#search')?.addEventListener('input', function (e) {
    const q = e.target.value.toLowerCase();
    els('.sms-contact').forEach(c => {
        c.style.display = c.innerText.toLowerCase().includes(q) ? '' : 'none';
    });
});

// ------------------ Sincronización ------------------
el('#btnSync')?.addEventListener('click', async function () {
    this.disabled = true;
    this.innerText = 'Sincronizando...';

    try {
        const res = await fetch(window.routes.sync, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
        });
        const data = await res.json();
        alert('Mensajes sincronizados: ' + (data.synced ?? 0));
        location.reload();
    } catch (err) {
        alert('Error al sincronizar mensajes');
        this.disabled = false;
        this.innerText = 'Actualizar';
    }
});

// ------------------ Cargar conversación ------------------
async function loadConversation(contact) {
    el('#currentContact').innerText = contact;
    el('#composer').style.display = '';
    el('#toInput').value = contact;

    if (btnDeleteConversation) btnDeleteConversation.disabled = false;

    const pane = el('#messagesPane');
    pane.innerHTML = '<div class="empty">Cargando...</div>';

    try {
        const res = await fetch('/sms/messages/' + encodeURIComponent(contact));
        const msgs = await res.json();

        if (!msgs.length) {
            pane.innerHTML = '<div class="empty">No hay mensajes disponibles.</div>';
            return;
        }

        pane.innerHTML = '';
        msgs.forEach(m => {
            const wrapper = document.createElement('div');
            wrapper.className = 'message-wrapper ' + (m.from === twilioFrom ? 'sent' : 'received');

            const box = document.createElement('div');
            box.className = 'message-box';
            box.innerHTML = m.body ? m.body.replace(/\n/g,'<br>') : '';

            const when = m.date_sent || m.date_created || m.created_at
                ? new Date(m.date_sent || m.date_created || m.created_at).toLocaleString()
                : '';

            const date = document.createElement('div');
            date.className = 'message-date';
            date.innerText = when;

            wrapper.appendChild(box);
            wrapper.appendChild(date);
            pane.appendChild(wrapper);
        });
        pane.scrollTop = pane.scrollHeight;
    } catch (err) {
        pane.innerHTML = '<div class="empty">Error cargando mensajes.</div>';
    }
}

// ------------------ Enviar mensaje ------------------
el('#sendForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const to = el('#toInput').value;
    const body = el('#bodyInput').value.trim();
    if (!body) return;

    const btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = 'Enviando...';

    try {
        const res = await fetch(window.routes.send, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ to, body })
        });

        const data = await res.json();
        btn.disabled = false;
        btn.innerText = 'Enviar';

        if (data.ok) {
            el('#bodyInput').value = '';

            const pane = el('#messagesPane');
            const wrapper = document.createElement('div');
            wrapper.className = 'message-wrapper sent';

            const box = document.createElement('div');
            box.className = 'message-box';
            box.innerHTML = body.replace(/\n/g,'<br>');

            const now = new Date().toLocaleString();
            const date = document.createElement('div');
            date.className = 'message-date';
            date.innerText = now;

            wrapper.appendChild(box);
            wrapper.appendChild(date);
            pane.appendChild(wrapper);
            pane.scrollTop = pane.scrollHeight;
        } else {
            alert('Error al enviar mensaje');
        }
    } catch (err) {
        btn.disabled = false;
        btn.innerText = 'Enviar';
        alert('Error al enviar mensaje');
    }
});


// ------------------ Formatear la fecha del último mensaje en el panel de contactos ------------------
function formatContactDate(ts) {
    if (!ts) return '';
    const d = new Date(ts);
    const now = new Date();
    const diff = now - d;

    if (diff < 24 * 60 * 60 * 1000) {
        // Menos de un día → mostrar hora
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } else {
        // Más de un día → mostrar fecha completa
        return d.toLocaleDateString([], { year: 'numeric', month: '2-digit', day: '2-digit' });
    }
}

// ------------------ Actualizar panel de contactos ------------------
function updateContactsPanel() {
    els('.sms-contact').forEach(c => {
        const ts = c.getAttribute('data-last-at'); // Asegúrate de tener data-last-at en el Blade
        const dateDiv = c.querySelector('.contact-date'); // Selecciona la clase correcta
        if (dateDiv) dateDiv.innerText = formatContactDate(ts);
    });
}

// Ejecutar después de cargar la lista de contactos
updateContactsPanel();
