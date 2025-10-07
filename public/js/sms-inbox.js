
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

// Eliminar una sola desde la lista (O detectar Click en Cada Elemento)
el('#contacts')?.addEventListener('click', async function (e) {
    
    $(".sms-contact").removeAttr("selected");
    
    const row = e.target.closest('.sms-contact');
    
    row.setAttribute("selected", "true");
 
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
            // console.log("una sola eliminada correctamente");

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

        const url = new URL(window.location.href);
        url.searchParams.set('contact', contact.replace("+",""));
        // url.searchParams.delete('param2');
        window.history.replaceState(null, null, url); // or pushState
    }
});

// ------------------ Eliminar conversación actual ------------------
btnDeleteConversation?.addEventListener('click', async function () {
    const unformatUS = s => '+1' + s.replace(/\D+/g, '').replace(/^1?/, '').slice(0,10);
    const formatUS = s => (m => m ? `+1 (${m[1]}) ${m[2]}-${m[3]}` : s)(s.replace(/\D+/g,'').match(/^1?(\d{3})(\d{3})(\d{4})$/));
    const contact = unformatUS(el('#currentContact')?.innerText.trim());
    if (!contact) return;

    // if (!confirm(`¿Eliminar la conversación con ${contact}?`)) return;

    Swal.fire({
        title: "Are you sure?",
        html: `You won't be able to recover chat with: <br><br><b>${formatUS(contact)}</b>`,
        icon: "warning",
        iconColor: 'var(--red1)',
        width:"420px",
        showCancelButton: true,
        confirmButtonColor: "var(--red1)",
        cancelButtonColor: "#ddd",
        confirmButtonText: "Delete"
    }).then(async (result) => {            // <-- async aquí
    if (!result.isConfirmed) return;

    try {
        btnDeleteConversation.disabled = true;

        const res = await fetch(
        window.routes.deleteOne.replace(':contact', encodeURIComponent(contact)),
        { method: 'DELETE', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } }
        );
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        // alert(data.message || 'Conversación eliminada');

        Swal.fire({
            title: "Conversación eliminada",
            icon: "success",
            iconColor: 'var(--red1)'
        });

        // Limpia la UI
        el('#messagesPane').innerHTML = '<div class="empty">Selecciona un contacto a la izquierda para ver su chat</div>';
        el('#currentContact').innerText = '';
        el('#composer').style.display = 'none';

        // Quita de la lista
        const row = el(`.sms-contact[data-contact="${CSS.escape(contact)}"]`);
        if (row) row.remove();

        // Limpia el query param
        const url = new URL(window.location.href);
        if (url.searchParams.has('contact')) {
        url.searchParams.delete('contact');
        window.history.replaceState(null, '', url);
        }

        btnDeleteConversation.disabled = true; // queda deshabilitado tras borrar
    } catch (err) {
        console.error(err);
        alert('Error eliminando conversación');
        btnDeleteConversation.disabled = false;
    }
    });


    
});

// ------------------ BÚSQUEDA (REEMPLAZAR BLOQUE) ------------------
let searchTimeout = null;

function escapeHTML(s) {
    return (s || '').toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
function escapeRegex(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
function highlightHTML(text, q) {
    if (!q) return text;
    const re = new RegExp('(' + escapeRegex(q) + ')', 'gi');
    return text.replace(re, '<span class="highlighted">$1</span>');
}

el('#search')?.addEventListener('input', function (e) {
    const q = e.target.value.trim();

    // Debounce
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        // If empty, restore contacts list and remove results panel
        if (!q) {
            els('.sms-contact').forEach(c => c.style.display = '');
            const prev = el('#searchResults');
            if (prev) prev.remove();
            return;
        }

        // pick search route (fallback if window.routes.search undefined)
        const route = (window.routes && window.routes.search) ? window.routes.search : '/sms/search';

        try {
            const res = await fetch(route + '?q=' + encodeURIComponent(q));
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();

            // Normalize response to an array of message-like items
            let items = [];
            if (Array.isArray(data)) {
                items = data;
            } else if (Array.isArray(data.conversations)) {
                // supports a possible controller format returning conversations
                items = data.conversations.map(c => ({
                    contact: c.contact,
                    body: c.first_match_excerpt ?? c.last_body ?? '',
                    date: c.last_at ?? ''
                }));
            } else if (Array.isArray(data.results)) {
                items = data.results;
            } else if (data && typeof data === 'object') {
                // Single-object fallback
                items = [data];
            }

            // Remove previous panel
            const old = el('#searchResults');
            if (old) old.remove();

            // Build results panel
            const panel = document.createElement('div');
            panel.id = 'searchResults';
            panel.className = 'search-results';

            if (!items.length) {
                panel.innerHTML = '<div class="empty">Sin resultados</div>';
            } else {
                items.forEach(m => {
                    const contact = m.contact ?? (m.from === twilioFrom ? m.to : m.from);
                    const rawBody = m.body ?? m.last_body ?? '';
                    const bodyPreview = escapeHTML(rawBody).slice(0, 300); // limit preview length
                    const highlightedPreview = highlightHTML(bodyPreview, q);
                    const when = m.date_sent ?? m.date_created ?? m.created_at ?? m.date ?? '';

                    const item = document.createElement('div');
                    item.className = 'search-item';
                    item.innerHTML = `
                        <div class="search-contact">${escapeHTML(contact)}</div>
                        <div class="search-body">${highlightedPreview}</div>
                        <div class="search-date">${escapeHTML(when ? new Date(when).toLocaleString() : '')}</div>
                    `;

                    item.addEventListener('click', async () => {
                        // load conversation and then scroll/highlight first match
                        await loadConversation(contact);

                        // small delay to allow messages to be rendered
                        setTimeout(() => {
                            const msgs = els('#messagesPane .message-box');
                            for (const msgEl of msgs) {
                                if (msgEl.innerText.toLowerCase().includes(q.toLowerCase())) {
                                    // highlight occurrences in message-box HTML
                                    // operate on innerHTML (safe-ish since we escaped above), but escape q in regex
                                    try {
                                        const re = new RegExp('(' + escapeRegex(q) + ')', 'gi');
                                        msgEl.innerHTML = msgEl.innerHTML.replace(re, '<span class="highlighted">$1</span>');
                                    } catch (err) { /* ignore regex errors */ }
                                    msgEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    // briefly add a class for bg highlight (if CSS provided)
                                    msgEl.classList.add('highlight');
                                    setTimeout(() => msgEl.classList.remove('highlight'), 3000);
                                    break;
                                }
                            }
                        }, 350);
                    });

                    panel.appendChild(item);
                });
            }

            // Insert panel above contacts list
            const contactsContainer = el('#contacts');
            if (contactsContainer && contactsContainer.parentNode) {
                contactsContainer.parentNode.insertBefore(panel, contactsContainer);
            } else {
                document.body.appendChild(panel);
            }
        } catch (err) {
            console.error('Error en búsqueda:', err);
        }
    }, 300);
});



// ------------------ Sincronización ------------------
el('#btnSync')?.addEventListener('click', async function () {
  this.disabled = true;
  this.setAttribute("syncAnimation", "true");
  this.innerHTML = `<i class='bx bx-sync'></i></button>`;

  try {
    const res = await fetch(window.routes.sync, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
    });

    // Manejo de error de servidor (por si devuelve HTML por error)
    if (!res.headers.get('content-type')?.includes('application/json')) {
      const text = await res.text();
      console.error('Respuesta no-JSON:', text);
      throw new Error('La respuesta no es JSON');
    }

    const data = await res.json();
    // console.log('Sincronizados:', data.synced);
    // console.log('Inbox (últimos mensajes por contacto):', data.contacts);

    // Ejemplo: solo los cuerpos de los últimos mensajes
    // console.log('Bodies:', data.contacts.map(c => c.last_body));
    renderContacts(data.contacts);
    updateContactsPanel();
    this.disabled = false;
    this.removeAttribute("syncAnimation", "false");

    url = new URL(window.location.href);
    if (url.searchParams.has('contact')) {
        // console.log("existe contact");
        // console.log(url.searchParams.get("contact"));
        let contactURL = url.searchParams.get("contact");
        loadConversation(`+${contactURL}`);
    }else{
        
    }
    // Si quisieras refrescar:
    // location.reload();

  } catch (err) {
    console.error(err);
    this.disabled = false;
    this.removeAttribute("syncAnimation");
    this.innerHTML = `<i class='bx bx-error' ></i>`;
  }
});


// ------------------ Cargar conversación ------------------
async function loadConversation(contact) {

    const formatUS = s => (m => m ? `+1 (${m[1]}) ${m[2]}-${m[3]}` : s)(s.replace(/\D+/g,'').match(/^1?(\d{3})(\d{3})(\d{4})$/));
    el('#currentContact').innerText =  formatUS(contact);
    el('#composer').style.display = '';
    el('#toInput').value = contact;

    btnDeleteConversation.style.display = "flex";

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

    url = new URL(window.location.href);
    if (url.searchParams.has('contact')) {
        // console.log("existe contact");
        // console.log(url.searchParams.get("contact"));
        let contactURL = url.searchParams.get("contact");
        loadConversation(`+${contactURL}`);
        
    }else{
        
    }
}

// Ejecutar después de cargar la lista de contactos
updateContactsPanel();

/* --- NUEVO PANEL PARA ENVIAR MENSAJES --- */
document.addEventListener('DOMContentLoaded', function () {
    const newMsgBtn = document.getElementById('newMessageBtn');
    const newMsgPanel = document.getElementById('newMessagePanel');
    const newMsgClose = document.getElementById('closeNewMessage');
    const newMsgForm = document.getElementById('newMessageForm');

    if (!newMsgBtn || !newMsgPanel || !newMsgForm) return;

    // Mostrar panel
    newMsgBtn.addEventListener('click', () => {
        newMsgPanel.classList.add('show');
        document.getElementById('newTo').focus();
    });

    // Cerrar panel
    newMsgClose.addEventListener('click', () => {
        newMsgPanel.classList.remove('show');
        newMsgForm.reset();
    });

    // Enviar mensaje
    newMsgForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const to = document.getElementById('newTo').value.trim();
        const body = document.getElementById('newBody').value.trim();

        if (!to || !body) {
            alert('Por favor, completa ambos campos.');
            return;
        }

        try {
            const response = await fetch(window.routes.send, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ to, body }),
            });

            if (response.ok) {
                const data = await response.json();
                alert('Mensaje enviado con éxito ✅');
                newMsgPanel.classList.remove('show');
                newMsgForm.reset();

                // (Opcional) Si deseas refrescar el panel actual:
                if (typeof loadConversations === 'function') loadConversations();

            } else {
                const err = await response.text();
                alert('Error al enviar el mensaje ❌: ' + err);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Ocurrió un error al enviar el mensaje.');
        }
    });
});


// --- Helpers ---
const formatUS = s => (m => m ? `+1 (${m[1]}) ${m[2]}-${m[3]}` : s)(
  String(s).replace(/\D+/g,'').match(/^1?(\d{3})(\d{3})(\d{4})$/)
);

const truncate = (str, n = 60) => {
  const s = String(str || '');
  return s.length > n ? s.slice(0, n - 1) + '…' : s;
};

const formatDateMX = iso => {
  if (!iso) return '';
  try {
    const dt = new Date(iso);
    // Fuerza zona MX para consistencia con Carbon
    const fmt = new Intl.DateTimeFormat('es-MX', {
      timeZone: 'America/Mexico_City',
      day: '2-digit', month: '2-digit', year: 'numeric',
      hour: '2-digit', minute: '2-digit', hour12: false
    });
    // Intl en es-MX da "dd/mm/aaaa, HH:MM"; quitamos la coma si aparece
    return fmt.format(dt).replace(',', '');
  } catch { return ''; }
};

// --- Render principal ---
function renderContacts(list = []) {
  const container = document.querySelector('#contacts');
  if (!container) return;

  // Guarda checks seleccionados para restaurar
  const prevChecked = new Set(
    Array.from(container.querySelectorAll('.contact-check:checked')).map(i => i.value)
  );

  container.innerHTML = '';

  if (!list.length) {
    container.innerHTML = `
      <div class="empty">No hay conversaciones. Presiona
        <strong>Actualizar</strong> para leer mensajes desde Twilio.
      </div>`;
    return;
  }

  for (const c of list) {
    const wrap = document.createElement('div');
    wrap.className = 'sms-contact';
    wrap.dataset.contact = c.contact || '';
    wrap.dataset.lastAt  = c.last_at  || '';

    const chk = document.createElement('input');
    chk.type = 'checkbox';
    chk.className = 'contact-check';
    chk.value = c.contact || '';
    chk.style.marginRight = '8px';
    if (prevChecked.has(chk.value)) chk.checked = true;

    const meta = document.createElement('div');
    meta.className = 'meta';
    meta.style.flex = '1';

    const nameDiv = document.createElement('div');
    nameDiv.style.fontWeight = '600';
    nameDiv.style.textAlign = 'left';
    nameDiv.textContent = formatUS(c.contact || '');

    const lastDiv = document.createElement('div');
    lastDiv.className = 'last';
    lastDiv.textContent = truncate(c.last_body, 60);

    const dateDiv = document.createElement('div');
    dateDiv.className = 'contact-date';
    dateDiv.textContent = formatDateMX(c.last_at);

    meta.appendChild(nameDiv);
    meta.appendChild(lastDiv);
    wrap.appendChild(chk);
    wrap.appendChild(meta);
    wrap.appendChild(dateDiv);
    container.appendChild(wrap);
  }
}


(() => {
  const SYNC_MS = 60_000; // 60s

  const forceSyncClick = () => {
    const btn = document.querySelector('#btnSync');
    if (btn && !btn.disabled) btn.click();
  };

  // ---- Interval solo cuando la página está visible ----
  let visIntervalId = null;
  const startVisibleInterval = () => {
    if (visIntervalId) return;
    visIntervalId = setInterval(() => {
      if (document.visibilityState === 'visible') forceSyncClick();
    }, SYNC_MS);
  };
  const stopVisibleInterval = () => {
    if (visIntervalId) {
      clearInterval(visIntervalId);
      visIntervalId = null;
    }
  };

  // ---- Inactividad 60s (solo si visible) ----
  let idleTimeoutId = null;
  const clearIdleTimer = () => {
    if (idleTimeoutId) {
      clearTimeout(idleTimeoutId);
      idleTimeoutId = null;
    }
  };
  const resetIdleTimer = () => {
    clearIdleTimer();
    if (document.visibilityState !== 'visible') return;
    idleTimeoutId = setTimeout(() => {
      forceSyncClick();
      resetIdleTimer(); // sigue vigilando
    }, SYNC_MS);
  };

  const activityEvents = ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'];
  activityEvents.forEach(ev =>
    window.addEventListener(ev, resetIdleTimer, { passive: true })
  );

  // ---- Reacciona a cambios de visibilidad ----
  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      startVisibleInterval();
      resetIdleTimer();
    } else {
      stopVisibleInterval();
      clearIdleTimer();
    }
  });

  // ---- Inicio ----
  if (document.visibilityState === 'visible') {
    startVisibleInterval();
    resetIdleTimer();
  }
})();
