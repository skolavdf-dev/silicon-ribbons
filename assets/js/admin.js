/* Silicon Ribbons — admin.js */

'use strict';

let allAdminStudents = [];

document.addEventListener('DOMContentLoaded', initAdmin);

function initAdmin() {
    if (typeof IS_ADMIN === 'undefined' || !IS_ADMIN) return;

    modal.init();
    loadAdminStudents();

    document.getElementById('form-add-student')?.addEventListener('submit', handleAddStudent);
    document.getElementById('form-add-merit')?.addEventListener('submit', handleAddMerit);

    const adminSearch = document.getElementById('admin-search');
    if (adminSearch) {
        let t;
        adminSearch.addEventListener('input', () => {
            clearTimeout(t);
            t = setTimeout(() => filterAdminStudents(adminSearch.value.trim()), 200);
        });
    }
}

// ─── Načíst studenty pro admin ────────────────────────────────────────────────

async function loadAdminStudents() {
    const list = document.getElementById('admin-students-list');
    if (!list) return;

    try {
        const students = await apiFetch({ action: 'admin_students' });
        allAdminStudents = students;
        renderAdminList(students);
    } catch (e) {
        list.innerHTML = `<div class="empty-state">Chyba: ${e.message}</div>`;
    }
}

function renderAdminList(students) {
    const list = document.getElementById('admin-students-list');
    if (!students.length) {
        list.innerHTML = '<div class="empty-state">Žádní studenti.</div>';
        return;
    }
    list.innerHTML = students.map(s => `
        <div class="admin-student-row"
             data-id="${s.id}"
             data-lastname="${esc(s.lastname)}"
             data-firstname="${esc(s.firstname)}"
             tabindex="0" role="button"
             aria-label="Editovat ${esc(s.lastname)} ${esc(s.firstname)}">
            <div class="admin-student-name">
                ${esc(s.lastname)} ${esc(s.firstname)}
                ${parseInt(s.is_public) ? '' : `<span class="admin-anon-code" title="Anonymní kód">${esc(s.anon_code)}</span>`}
            </div>
            <div class="admin-student-year">${s.admission_year}</div>
            <div class="admin-student-score">${s.merit_score} bodů</div>
            <button class="btn btn-sm btn-add-merit" data-id="${s.id}"
                    title="Udělit ocenění tomuto studentovi"
                    aria-label="Udělit ocenění: ${esc(s.lastname)} ${esc(s.firstname)}">+</button>
        </div>`).join('');

    list.querySelectorAll('.admin-student-row').forEach(row => {
        // klik na + — výběr studenta do formuláře
        row.querySelector('.btn-add-merit').addEventListener('click', e => {
            e.stopPropagation();
            selectStudentForMerit(row.dataset.id, `${row.dataset.lastname} ${row.dataset.firstname}`);
        });
        // klik na zbytek řádku — edit modal
        row.addEventListener('click', () => openEditModal(parseInt(row.dataset.id), row.dataset));
        row.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openEditModal(parseInt(row.dataset.id), row.dataset); }
        });
    });
}

function selectStudentForMerit(id, name) {
    document.getElementById('merit-student-id').value = id;

    const display = document.getElementById('merit-student-display');
    display.classList.add('is-selected');
    display.innerHTML = `
        <span class="merit-student-name">${esc(name)}</span>
        <button type="button" class="btn btn-sm btn-ghost merit-student-clear" aria-label="Zrušit výběr studenta">Změnit</button>`;
    display.querySelector('.merit-student-clear').addEventListener('click', clearStudentForMerit);

    // Zvýraznit řádek v seznamu
    document.querySelectorAll('.admin-student-row').forEach(r => r.classList.remove('is-merit-target'));
    document.querySelectorAll(`.admin-student-row[data-id="${id}"]`).forEach(r => r.classList.add('is-merit-target'));

    // Scroll k formuláři (na mobilu je vlevo nahoře)
    document.getElementById('panel-add-merit')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function clearStudentForMerit() {
    document.getElementById('merit-student-id').value = '';
    const display = document.getElementById('merit-student-display');
    display.classList.remove('is-selected');
    display.innerHTML = `<span class="merit-student-placeholder">Vyberte studenta tlačítkem&nbsp;<strong>+</strong>&nbsp;ze seznamu vpravo</span>`;
    document.querySelectorAll('.admin-student-row').forEach(r => r.classList.remove('is-merit-target'));
}

function filterAdminStudents(query) {
    if (!query) { renderAdminList(allAdminStudents); return; }
    // Umožní hledat i bez # (např. "246AF0" najde "#246AF0")
    const q = query.replace(/^#/, '').toLowerCase();
    renderAdminList(allAdminStudents.filter(s =>
        s.lastname.toLowerCase().includes(q) ||
        s.firstname.toLowerCase().includes(q) ||
        (s.anon_code && s.anon_code.replace('#', '').toLowerCase().includes(q))
    ));
}

// ─── Přidat studenta ──────────────────────────────────────────────────────────

async function handleAddStudent(e) {
    e.preventDefault();
    const form = e.target;
    const btn  = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    try {
        const data = {
            lastname:       form.lastname.value.trim(),
            firstname:      form.firstname.value.trim(),
            admission_year: parseInt(form.admission_year.value),
            is_public:      form.is_public.checked ? 1 : 0,
        };
        await apiFetch({ action: 'add_student' }, 'POST', data);
        showToast('Student byl přidán.', 'success');
        form.reset();
        form.admission_year.value = new Date().getFullYear();
        await loadAdminStudents();
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        btn.disabled = false;
    }
}

// ─── Přidat ocenění ───────────────────────────────────────────────────────────

async function handleAddMerit(e) {
    e.preventDefault();
    const form = e.target;
    const studentId = parseInt(document.getElementById('merit-student-id').value);
    if (!studentId) {
        showToast('Nejprve vyberte studenta tlačítkem + ze seznamu.', 'error');
        return;
    }
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    try {
        const data = {
            student_id:  studentId,
            category:    form.category.value,
            level:       parseInt(form.level.value),
            granted_by:  form.granted_by.value.trim(),
            description: form.description.value.trim(),
        };
        await apiFetch({ action: 'add_merit' }, 'POST', data);
        showToast('Ocenění bylo uděleno.', 'success');
        form.reset();
        clearStudentForMerit();
        await loadAdminStudents();
    } catch (err) {
        showToast(err.message, 'error');
    } finally {
        btn.disabled = false;
    }
}

// ─── Modal editace studenta ───────────────────────────────────────────────────

async function openEditModal(studentId, rowData = {}) {
    modal.open('<div class="loading" style="text-align:center;padding:2rem">Načítání…</div>');

    try {
        const s = await apiFetch({ action: 'student', id: studentId });
        const meritRows = s.merits.map(m => {
            const catTitle = (CATEGORIES_DATA.find(c => c.id === m.category) || {}).title || m.category;
            const lvlName  = LEVEL_NAMES[m.level] || `L${m.level}`;
            return `
            <div class="modal-merit-row" data-merit-id="${m.id}">
                <div class="modal-merit-ribbon">${ribbonSVG(m.category, m.level, {w:90,h:30})}</div>
                <div class="modal-merit-info">
                    <strong>${esc(catTitle)} — ${esc(lvlName)}</strong>
                    ${m.granted_by} · ${m.granted_at.substring(0,10)}
                    ${m.description ? `<br>${esc(m.description)}` : ''}
                </div>
                <button class="btn btn-danger btn-sm merit-delete-btn" data-id="${m.id}" title="Odebrat ocenění">&times;</button>
            </div>`;
        }).join('');

        modal.open(`
            <h3 class="modal-student-name" style="margin-bottom:.75rem">Editace: ${esc(s.display_name)}</h3>

            <form id="edit-student-form" style="margin-bottom:1.5rem">
                <div class="form-row">
                    <div class="form-group">
                        <label>Příjmení</label>
                        <input type="text" name="lastname" value="${esc(rowData.lastname || '')}" required placeholder="Příjmení">
                    </div>
                    <div class="form-group">
                        <label>Jméno</label>
                        <input type="text" name="firstname" value="${esc(rowData.firstname || '')}" required placeholder="Jméno">
                    </div>
                </div>
                <div class="form-row" style="margin-bottom:1rem">
                    <div class="form-group">
                        <label>Rok nástupu</label>
                        <input type="number" name="admission_year" value="${s.admission_year}" min="2000" max="2099" required>
                    </div>
                    <div class="form-group form-group--checkbox" style="justify-content:flex-end">
                        <label>
                            <input type="checkbox" name="is_public" ${s.is_public ? 'checked' : ''}>
                            Veřejný profil
                        </label>
                    </div>
                </div>
                <div style="display:flex;gap:.75rem;flex-wrap:wrap">
                    <button type="submit" class="btn btn-primary btn-sm">Uložit změny</button>
                    <button type="button" class="btn btn-danger btn-sm" id="delete-student-btn">Smazat studenta</button>
                </div>
                <input type="hidden" name="id" value="${s.id}">
            </form>

            <p class="section-label" style="margin-bottom:.75rem">Udělená ocenění</p>
            <div class="modal-edit-ribbons" id="merit-rows">
                ${meritRows || '<p style="color:var(--muted);font-size:.88rem">Žádná ocenění.</p>'}
            </div>
        `);

        // Event listenery v modalu
        document.getElementById('edit-student-form')?.addEventListener('submit', async ev => {
            ev.preventDefault();
            const f = ev.target;
            try {
                await apiFetch({ action: 'update_student' }, 'POST', {
                    id:             parseInt(f.id.value),
                    lastname:       f.lastname.value.trim(),
                    firstname:      f.firstname.value.trim(),
                    admission_year: parseInt(f.admission_year.value),
                    is_public:      f.is_public.checked ? 1 : 0,
                });
                showToast('Student uložen.', 'success');
                modal.close();
                await loadAdminStudents();
            } catch (err) { showToast(err.message, 'error'); }
        });

        document.getElementById('delete-student-btn')?.addEventListener('click', async () => {
            if (!confirm(`Opravdu smazat studenta "${s.display_name}"? Budou odstraněna i všechna jeho ocenění.`)) return;
            try {
                await apiFetch({ action: 'delete_student' }, 'POST', { id: s.id });
                showToast('Student smazán.', 'success');
                modal.close();
                await loadAdminStudents();
            } catch (err) { showToast(err.message, 'error'); }
        });

        document.getElementById('merit-rows')?.addEventListener('click', async e => {
            const btn = e.target.closest('.merit-delete-btn');
            if (!btn) return;
            const mId = parseInt(btn.dataset.id);
            if (!confirm('Odebrat toto ocenění?')) return;
            try {
                await apiFetch({ action: 'delete_merit' }, 'POST', { id: mId });
                btn.closest('.modal-merit-row').remove();
                showToast('Ocenění odebráno.', 'success');
                await loadAdminStudents();
            } catch (err) { showToast(err.message, 'error'); }
        });

    } catch (err) {
        modal.open(`<p style="color:#fca5a5;padding:1rem">Chyba: ${err.message}</p>`);
    }
}
