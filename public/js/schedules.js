(() => {
  const baseUrl = document.querySelector('meta[name="base-url"]').content;
  const csrf = document.querySelector('meta[name="csrf-token"]').content;

  const boot = window.SCHEDULES_BOOT || {};
  let currentBaseDate = boot.weekStart || new Date().toISOString().slice(0, 10);

  const weekTitle = document.getElementById('weekTitle');
  const schedBody = document.getElementById('schedBody');

  const pickerOverlay = document.getElementById('shiftPickerOverlay');
  const formOverlay = document.getElementById('shiftFormOverlay');

  const pickerTitle = document.getElementById('pickerTitle');
  const pickerSub = document.getElementById('pickerSub');
  const shiftList = document.getElementById('shiftList');



  const assignToInput = document.getElementById('assignToInput');
  const colorSelect = document.getElementById('colorSelect');
  const timeInput = document.getElementById('timeInput');
  const timeSuggest = document.getElementById('timeSuggest');
  const timeOffCheck = document.getElementById('timeOffCheck');
  const timeOffType = document.getElementById('timeOffType');
  const offReq = document.getElementById('offReq');

  const removeAssignmentBtn = document.getElementById('removeAssignmentBtn');

  const formTitle = document.getElementById('formTitle');

  let state = {
    canEdit: !!boot.canEdit,
    week: null,
    people: [],
    assignments: [],
    shifts: [],
    selectedCell: null, // {date,target_type,target_id,target_name}
    editingShiftId: null
  };

  const COLORS = new Set(['blue', 'green', 'orange', 'purple', 'red']);

  function setPdfLink(weekStartStr) {
    const btn = document.getElementById('downloadPdf');
    if (!btn) return;

    // weekStartStr debe ser Monday YYYY-MM-DD
    btn.href = `${baseUrl}/schedules/pdf?start=${encodeURIComponent(weekStartStr)}`;
  }

  function fmtDowHeaders(weekStartStr) {
    // Mon..Sun dates
    const start = new Date(weekStartStr + 'T00:00:00');
    const dates = [];
    for (let i = 0; i < 7; i++) {
      const d = new Date(start);
      d.setDate(start.getDate() + i);
      dates.push(d.toISOString().slice(0, 10));
    }
    return dates;
  }

  function openOverlay(el) { el.classList.add('show'); el.setAttribute('aria-hidden', 'false'); }
  function closeOverlay(el) { el.classList.remove('show'); el.setAttribute('aria-hidden', 'true'); }

  function buildKey(date, type, id) { return `${date}|${type}|${id}`; }

  function getAssignmentFor(date, type, id) {
    return state.assignments.find(a => a.date === date && a.target_type === type && String(a.target_id) === String(id));
  }

  function chipClass(color) {
    if (!color) return 'gray';
    return COLORS.has(color) ? color : 'gray';
  }

  async function fetchWeek(dateStr) {
    const r = await fetch(`${baseUrl}/schedules/week?date=${encodeURIComponent(dateStr)}`, { credentials: 'same-origin' });
    const data = await r.json();
    state.week = data.week;
    state.people = data.people || [];
    state.assignments = data.assignments || [];
    state.canEdit = !!data.canEdit;
    weekTitle.textContent = data.week.title;
    renderTable();
    setPdfLink(state.week.start);
  }

  async function fetchShifts() {
    const r = await fetch(`${baseUrl}/schedules/shifts`, { credentials: 'same-origin' });
    const data = await r.json();
    state.shifts = data.shifts || [];
    state.canEdit = !!data.canEdit;
  }

  function renderTable() {
    const dates = fmtDowHeaders(state.week.start);
    schedBody.innerHTML = '';

    state.people.forEach(p => {
      const tr = document.createElement('tr');

      const tdName = document.createElement('td');
      tdName.innerHTML = `
        <div class="person-cell">
          <div class="person-pill"><i class='bx bx-user'></i></div>
          <div>
            <div>${escapeHtml(p.name)}</div>
            <div class="ribbon-sub">${p.type === 'user' ? 'User' : 'Sub User'}</div>
          </div>
        </div>
      `;
      tr.appendChild(tdName);

      dates.forEach(date => {
        const td = document.createElement('td');
        td.innerHTML = `
          <div class="day-cell"
               data-date="${date}"
               data-target-type="${p.type}"
               data-target-id="${p.id}"
               data-target-name="${escapeHtml(p.name)}">
            <div class="hover-add"><i class='bx bx-plus'></i> Add a shift</div>
          </div>
        `;
        const cell = td.querySelector('.day-cell');

        const ass = getAssignmentFor(date, p.type, p.id);
        if (ass && ass.shift) {
          const txt = ass.shift.is_time_off ? ass.shift.time_off_type : ass.shift.time_text;
          const cls = chipClass(ass.shift.color);
          const chip = document.createElement('div');
          chip.className = `shift-chip ${cls}`;
          chip.textContent = txt;
          cell.appendChild(chip);
          cell.classList.add('has-shift');

        }

        cell.addEventListener('click', () => onCellClick(cell));
        tr.appendChild(td);
      });

      schedBody.appendChild(tr);
    });
  }

  function onCellClick(cell) {
    const date = cell.dataset.date;
    const target_type = cell.dataset.targetType;
    const target_id = cell.dataset.targetId;
    const target_name = cell.dataset.targetName;

    state.selectedCell = { date, target_type, target_id, target_name };
    state.shiftContext = { target_type, target_id, target_name };


    pickerTitle.textContent = `${target_name} · ${prettyDate(date)}`;
    pickerSub.textContent = state.canEdit ? 'Select a shift to assign' : 'View shifts (read only)';

    const existing = getAssignmentFor(date, target_type, target_id);
    removeAssignmentBtn.style.display = (existing && state.canEdit) ? 'inline-flex' : 'none';

    renderShiftList();
    openOverlay(pickerOverlay);
  }

  function renderShiftList() {
    shiftList.innerHTML = '';

    if (!state.shifts.length) {
      const empty = document.createElement('div');
      empty.className = 'ribbon-sub';
      empty.textContent = 'No shifts yet. Create one with “New Shift”.';
      shiftList.appendChild(empty);
      return;
    }

    state.shifts.forEach(s => {
      const ribbon = document.createElement('div');
      ribbon.className = 'shift-ribbon';



      const txt = s.is_time_off ? s.time_off_type : s.time_text;
      const cls = chipClass(s.color);

      ribbon.innerHTML = `
        <div class="left">
          <span class="ribbon-dot" style="background:${dotBg(cls)}"></span>
          <div style="min-width:0">
            <div class="ribbon-text">${escapeHtml(txt)}</div>
            <div class="ribbon-sub">${s.is_time_off ? 'Time off' : 'Work shift'}</div>
          </div>
        </div>
        <div class="ribbon-actions">
  <button class="pencil-btn" title="Edit">
    <i class='bx bx-pencil'></i>
  </button>

  <button class="trash-btn" title="Delete">
    <i class='bx bx-trash'></i>
  </button>
</div>
      `;

      // click en cinta = asignar al día
      ribbon.querySelector('.left').addEventListener('click', async () => {
        if (!state.canEdit) return;
        await assignToCell(s.id);
        closeOverlay(pickerOverlay);
        await refreshAll();
      });

      // lápiz = editar shift (solo user)
      ribbon.querySelector('.pencil-btn').addEventListener('click', () => {
        if (!state.canEdit) return;
        openEditShift(s);
      });

      // editar
      ribbon.querySelector('.pencil-btn').addEventListener('click', () => {
        if (!state.canEdit) return;
        openEditShift(s);
      });

      // 🔥 NUEVO: borrar directamente desde el primer overlay
      ribbon.querySelector('.trash-btn').addEventListener('click', async (e) => {
        e.stopPropagation();
        if (!state.canEdit) return;

        const result = await Swal.fire({
          title: 'Delete shift?',
          text: `This will permanently delete "${s.time_text}"`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#e11d48',
          cancelButtonColor: '#64748b',
          confirmButtonText: 'Yes, delete',
          cancelButtonText: 'Cancel'
        });

        if (!result.isConfirmed) return;

        await deleteShiftDirect(s.id);

        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Shift deleted',
          showConfirmButton: false,
          timer: 2000
        });
      });

      shiftList.appendChild(ribbon);
    });
  }

  function openEditShift(shift) {
    state.editingShiftId = shift.id;
    formTitle.textContent = 'Edit Shift';

    // 👉 asignar nombre automáticamente
    assignToInput.value = state.selectedCell
      ? state.selectedCell.target_name
      : '';


    // form values
    colorSelect.value = shift.color || '';
    timeOffCheck.checked = !!shift.is_time_off;
    timeOffType.value = shift.time_off_type || '';
    timeInput.value = shift.time_text || '';



    toggleTimeOffUI();
    closeOverlay(pickerOverlay);
    openOverlay(formOverlay);
  }

  function openNewShift() {
    state.editingShiftId = null;
    formTitle.textContent = 'New Shift';

    // 👉 asignar nombre automáticamente
    assignToInput.value = state.selectedCell
      ? state.selectedCell.target_name
      : '';


    // reset form
    colorSelect.value = '';
    timeOffCheck.checked = false;
    timeOffType.value = '';
    timeInput.value = '';
    toggleTimeOffUI();

    closeOverlay(pickerOverlay);
    openOverlay(formOverlay);
  }


  function toggleTimeOffUI() {
    const off = timeOffCheck.checked;
    timeInput.disabled = off;
    timeOffType.disabled = !off;
    offReq.style.display = off ? 'inline' : 'none';
    if (off) timeInput.value = '';
    if (!off) timeOffType.value = '';
  }

  async function assignToCell(shiftId) {
    const c = state.selectedCell;
    if (!c) return;

    await fetch(`${baseUrl}/schedules/assign`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify({
        date: c.date,
        target_type: c.target_type,
        target_id: c.target_id,
        shift_id: shiftId
      })
    });
  }

  async function removeAssignment() {
    const c = state.selectedCell;
    if (!c) return;

    await fetch(`${baseUrl}/schedules/assign`, {
      method: 'DELETE',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify({
        date: c.date,
        target_type: c.target_type,
        target_id: c.target_id
      })
    });

    closeOverlay(pickerOverlay);
    await refreshAll();
  }

  async function saveShift() {
    if (!state.canEdit) return;

    const isOff = timeOffCheck.checked;
    const payload = {
      assign_type: 'any',
      assign_id: null,
      color: colorSelect.value || null,
      is_time_off: isOff,
      time_off_type: isOff
        ? `OFF - ${timeOffType.value}`
        : null,
      time_text: !isOff ? (timeInput.value || '').trim() : null
    };

    if (isOff && !timeOffType.value) {
      alert('Time off type is required.');
      return;
    }

    if (!isOff && !payload.time_text) {
      alert('Time is required.');
      return;
    }

    const url = state.editingShiftId
      ? `${baseUrl}/schedules/shifts/${state.editingShiftId}`
      : `${baseUrl}/schedules/shifts`;

    const method = state.editingShiftId ? 'PUT' : 'POST';

    await fetch(url, {
      method,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify(payload)
    });

    closeOverlay(formOverlay);
    await fetchShifts();      // 🔥 refresca shifts
    renderShiftList();        // 🔥 repinta overlay
    openOverlay(pickerOverlay);

  }

  async function deleteShiftDirect(shiftId) {
    await fetch(`${baseUrl}/schedules/shifts/${shiftId}`, {
      method: 'DELETE',
      credentials: 'same-origin',
      headers: { 'X-CSRF-TOKEN': csrf }
    });

    // quitar asignaciones locales
    state.assignments = state.assignments.filter(
      a => a.shift && String(a.shift.id) !== String(shiftId)
    );

    // refrescar UI
    await fetchShifts();
    renderTable();
    renderShiftList();
  }


  async function refreshAll(shiftsOnly = false) {
    if (shiftsOnly) await fetchShifts();
    else {
      await fetchShifts();
      await fetchWeek(state.week ? state.week.start : currentBaseDate);
    }
  }

  // ---------- Time suggestions (cada 30 min) ----------
  function buildTimeOptions(prefix) {
    // genera horas 00:00 .. 23:30 en formato 12h
    const opts = [];
    for (let h = 0; h < 24; h++) {
      for (let m = 0; m < 60; m += 30) {
        const v = to12h(h, m);
        if (!prefix) opts.push(v);
        else if (v.toLowerCase().startsWith(prefix.toLowerCase())) opts.push(v);
      }
    }
    return opts.slice(0, 30);
  }

  function to12h(h, m) {
    const ampm = h >= 12 ? 'pm' : 'am';
    let hh = h % 12; if (hh === 0) hh = 12;
    const mm = String(m).padStart(2, '0');
    return `${hh}:${mm} ${ampm}`;
  }

  function showSuggest(list) {
    timeSuggest.innerHTML = '';
    if (!list.length) { timeSuggest.style.display = 'none'; return; }
    list.forEach(v => {
      const div = document.createElement('div');
      div.className = 'suggest-item';
      div.textContent = v;
      div.addEventListener('click', () => {
        insertTimePart(v);
        timeSuggest.style.display = 'none';
      });
      timeSuggest.appendChild(div);
    });
    timeSuggest.style.display = 'block';
  }

  function insertTimePart(v) {
    const t = timeInput.value.trim();

    // Si ya existe el separador, completa la hora final
    if (t.includes(' - ')) {
      const start = t.split(' - ')[0].trim();
      timeInput.value = `${start} - ${v}`;
      timeInput.focus();
      return;
    }

    // Primera selección → autocompletar con " - "
    timeInput.value = `${v} - `;
    timeInput.focus();
  }




  timeInput?.addEventListener('input', () => {
    const raw = timeInput.value.trim().toLowerCase();

    // detecta qué parte está escribiendo: inicio o final
    let prefix = raw;
    if (raw.includes(' - ')) {
      const parts = raw.split(' - ');
      prefix = (parts[1] || '').trim();
      if (raw.endsWith(' - ')) prefix = '';
    }

    // si escribió "2" sugiere 2:00 pm/am etc, y general por prefijo
    const list = buildTimeOptions(prefix);
    showSuggest(list);
  });

  timeInput?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && timeSuggest.style.display === 'block') {
      const first = timeSuggest.querySelector('.suggest-item');
      if (first) {
        e.preventDefault();
        insertTimePart(first.textContent);
        timeSuggest.style.display = 'none';
      }
    }
  });

  // ---------- Buttons / events ----------

  document.getElementById('backCalendar')?.addEventListener('click', () => {
    window.location.href = `${baseUrl}/calendar`;
  });

  document.getElementById('prevWeek').addEventListener('click', async () => {
    const d = new Date((state.week?.start || currentBaseDate) + 'T00:00:00');
    d.setDate(d.getDate() - 7);
    currentBaseDate = d.toISOString().slice(0, 10);
    await fetchWeek(currentBaseDate);
  });

  document.getElementById('nextWeek').addEventListener('click', async () => {
    const d = new Date((state.week?.start || currentBaseDate) + 'T00:00:00');
    d.setDate(d.getDate() + 7);
    currentBaseDate = d.toISOString().slice(0, 10);
    await fetchWeek(currentBaseDate);
  });

  document.getElementById('goToday').addEventListener('click', async () => {
    currentBaseDate = new Date().toISOString().slice(0, 10);
    await fetchWeek(currentBaseDate);
  });

  document.getElementById('closePicker').addEventListener('click', () => {
    state.selectedCell = null;
    state.shiftContext = null;
    closeOverlay(pickerOverlay);
  });

  document.getElementById('closeForm').addEventListener('click', () => closeOverlay(formOverlay));

  document.getElementById('openCreateShift').addEventListener('click', () => {
    if (!state.canEdit) return;
    openNewShift();
  });

  document.getElementById('backToPicker').addEventListener('click', () => {
    closeOverlay(formOverlay);
    openOverlay(pickerOverlay);
  });

  removeAssignmentBtn.addEventListener('click', () => {
    if (!state.canEdit) return;
    removeAssignment();
  });

  timeOffCheck.addEventListener('change', toggleTimeOffUI);
  document.getElementById('saveShiftBtn').addEventListener('click', saveShift);


  // Utils
  function prettyDate(yyyy_mm_dd) {
    const d = new Date(yyyy_mm_dd + 'T00:00:00');
    return d.toLocaleDateString('en-US', { weekday: 'long', month: 'short', day: 'numeric', year: 'numeric' });
  }
  function dotBg(cls) {
    const map = {
      gray: 'rgba(148,163,184,0.5)',
      blue: 'rgba(59,130,246,0.55)',
      green: 'rgba(34,197,94,0.45)',
      orange: 'rgba(249,115,22,0.45)',
      purple: 'rgba(168,85,247,0.45)',
      red: 'rgba(239,68,68,0.45)'
    };
    return map[cls] || map.gray;
  }
  function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, s => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[s]));
  }

  // init
  (async function init() {
    await fetchShifts();
    await fetchWeek(currentBaseDate);
  })();
})();
