/* Silicon Ribbons — app.js */

'use strict';

// ─── Konfigurace kategorií (vložena PHP z config.php na stránce admin.php,
//     nebo načítána z API na index.php) ────────────────────────────────────

const LEVEL_NAMES = { 1:'Nováček', 2:'Pokročilý', 3:'Profík', 4:'Mistr', 5:'Mentor' };

const CATEGORIES_META = {
    DEV: { title:'Software & Web Development', base:'#1E3A8A', light:'#3B82F6' },
    SEC: { title:'Cyber Security',             base:'#7F1D1D', light:'#EF4444' },
    NET: { title:'Networking & Infrastructure', base:'#14532D', light:'#22C55E' },
    HW:  { title:'Hardware & IoT',             base:'#1C1C24', light:'#94A3B8' },
    AID: { title:'AI & Data',                  base:'#BE185D', light:'#EC4899' },
    OPS: { title:'DevOps & Cloud',             base:'#0F766E', light:'#14B8A6' },
    DSN: { title:'UI/UX & Design',             base:'#581C87', light:'#A855F7' },
    AWD: { title:'Special Awards',             base:'#B45309', light:'#F59E0B' },
};

// ─── SVG stužka ──────────────────────────────────────────────────────────────

function ribbonSVG(catId, level, opts = {}) {
    const cat   = CATEGORIES_META[catId] || { base:'#333', light:'#999' };
    const W     = opts.w || 140;
    const H     = opts.h || 48;
    const uid   = `r${Math.random().toString(36).slice(2,7)}`;
    const divX  = Math.round(W * 0.371);   // ~52/140
    const cx    = Math.round(W * 0.714);   // ~100/140
    const cy    = H / 2;
    const scale = W / 140;

    let devices = '';
    if (level === 2) {
        devices = `<circle cx="${cx}" cy="${cy}" r="${5*scale}" fill="#FFF" opacity=".92"/>`;
    } else if (level === 3) {
        devices = `
            <circle cx="${cx - 10*scale}" cy="${cy}" r="${5*scale}" fill="#FFF" opacity=".92"/>
            <circle cx="${cx + 10*scale}" cy="${cy}" r="${5*scale}" fill="#FFF" opacity=".92"/>`;
    } else if (level === 4) {
        devices = `
            <circle cx="${cx - 20*scale}" cy="${cy}" r="${5*scale}" fill="#FFF" opacity=".92"/>
            <circle cx="${cx}"            cy="${cy}" r="${5*scale}" fill="#FFF" opacity=".92"/>
            <circle cx="${cx + 20*scale}" cy="${cy}" r="${5*scale}" fill="#FFF" opacity=".92"/>`;
    } else if (level === 5) {
        const s = cx, sy = cy;
        const k = scale;
        devices = `<polygon points="
            ${s},${sy-11*k} ${s+3.5*k},${sy-4*k} ${s+11*k},${sy-4*k}
            ${s+5*k},${sy+2*k} ${s+7*k},${sy+9*k} ${s},${sy+5*k}
            ${s-7*k},${sy+9*k} ${s-5*k},${sy+2*k} ${s-11*k},${sy-4*k}
            ${s-3.5*k},${sy-4*k}"
            fill="#FFF" opacity=".95"/>`;
    }

    const fs = catId.length <= 2 ? Math.round(14*scale) : Math.round(11*scale);
    const ls = catId.length <= 2 ? Math.round(2*scale)  : Math.round(1*scale);

    return `<svg viewBox="0 0 ${W} ${H}" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="${uid}_g" x1="0%" y1="0%" x2="0%" y2="100%">
      <stop offset="0%"   stop-color="${cat.light}"/>
      <stop offset="100%" stop-color="${cat.base}"/>
    </linearGradient>
    <clipPath id="${uid}_c"><rect width="${W}" height="${H}" rx="3"/></clipPath>
  </defs>
  <rect width="${W}" height="${H}" rx="3" fill="url(#${uid}_g)"/>
  <rect width="${divX}" height="${H}" fill="black" opacity=".22" clip-path="url(#${uid}_c)"/>
  <text x="${divX/2}" y="${H/2+1}" text-anchor="middle" dominant-baseline="middle"
    font-family="'Raleway',sans-serif" font-size="${fs}" font-weight="700"
    fill="#FFF" opacity=".95" letter-spacing="${ls}">${catId}</text>
  ${devices}
  <rect width="${W}" height="2" rx="1" fill="white" opacity=".08"/>
</svg>`;
}

// ─── API helper ───────────────────────────────────────────────────────────────

async function apiFetch(params, method = 'GET', body = null) {
    const url = 'api.php?' + new URLSearchParams(params).toString();
    const opts = { method };
    if (body) {
        opts.headers = { 'Content-Type': 'application/json' };
        opts.body = JSON.stringify(body);
    }
    const res = await fetch(url, opts);
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Chyba serveru');
    return data;
}

// ─── Toast ────────────────────────────────────────────────────────────────────

function showToast(msg, type = 'success') {
    let el = document.getElementById('toast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'toast';
        document.body.appendChild(el);
    }
    el.textContent = msg;
    el.className = `toast-${type}`;
    el.classList.add('show');
    clearTimeout(el._t);
    el._t = setTimeout(() => el.classList.remove('show'), 3000);
}

// ─── Modal ────────────────────────────────────────────────────────────────────

const modal = {
    overlay: null,
    content: null,

    init() {
        this.overlay = document.getElementById('modal-overlay');
        this.content = document.getElementById('modal-content');
        this.overlay.querySelector('.modal-close').addEventListener('click', () => this.close());
        this.overlay.addEventListener('click', e => { if (e.target === this.overlay) this.close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') this.close(); });
    },

    open(html) {
        this.content.innerHTML = html;
        this.overlay.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
    },

    close() {
        this.overlay.setAttribute('hidden', '');
        document.body.style.overflow = '';
        this.content.innerHTML = '';
    },
};

// ─── Stránka INDEX ────────────────────────────────────────────────────────────

function initIndexPage() {
    modal.init();

    const grid    = document.getElementById('students-grid');
    const navEl   = document.getElementById('quick-nav');
    const catCont = document.getElementById('categories-container');
    const fName   = document.getElementById('f-name');
    const fCat    = document.getElementById('f-cat');
    const fLvl    = document.getElementById('f-lvl');
    const fYear   = document.getElementById('f-year');
    const fSort   = document.getElementById('f-sort');

    if (!grid) return;

    const PAGE_SIZE = 12;
    let currentOffset = 0;
    let isLoadingMore = false;

    // Naplnit select kategorií a roků
    Object.entries(CATEGORIES_META).forEach(([id, cat]) => {
        fCat.add(new Option(`${id} — ${cat.title}`, id));
    });
    const currentYear = new Date().getFullYear();
    for (let y = currentYear; y >= currentYear - 6; y--) {
        fYear.add(new Option(y, y));
    }

    // Katalog
    renderCatalog(navEl, catCont);

    function buildParams(offset) {
        const params = { action: 'students', limit: PAGE_SIZE, offset };
        const name = fName.value.trim();
        if (name)  params.name = name;
        const cat = fCat.value;
        if (cat)   params.category = cat;
        const lvl = fLvl.value;
        if (lvl && lvl !== '0') params.min_level = lvl;
        const year = fYear.value;
        if (year && year !== '0') params.year = year;
        params.sort = fSort.value;
        return params;
    }

    // Načíst první stránku (reset)
    async function loadStudents() {
        currentOffset = 0;
        grid.innerHTML = '<div class="loading">Načítání…</div>';
        removeLoadMoreBtn();
        try {
            const data = await apiFetch(buildParams(0));
            currentOffset = data.students.length;
            renderStudents(grid, data.students, 0);
            if (data.has_more) appendLoadMoreBtn(data.students.length);
        } catch (e) {
            grid.innerHTML = `<div class="empty-state">Chyba: ${e.message}</div>`;
        }
    }

    // Načíst další stránku (append)
    async function loadMore() {
        if (isLoadingMore) return;
        isLoadingMore = true;
        const btn = document.getElementById('load-more-btn');
        if (btn) { btn.disabled = true; btn.textContent = 'Načítání…'; }
        try {
            const data = await apiFetch(buildParams(currentOffset));
            const existingCount = grid.querySelectorAll('.student-card').length;
            appendStudents(grid, data.students, existingCount);
            currentOffset += data.students.length;
            removeLoadMoreBtn();
            if (data.has_more) appendLoadMoreBtn(currentOffset);
        } catch (e) {
            showToast('Chyba načítání: ' + e.message, 'error');
            if (btn) { btn.disabled = false; btn.textContent = 'Načíst další'; }
        } finally {
            isLoadingMore = false;
        }
    }

    function appendLoadMoreBtn(count) {
        const wrap = document.createElement('div');
        wrap.id = 'load-more-wrap';
        wrap.className = 'load-more-wrap';
        wrap.innerHTML = `<button id="load-more-btn" class="btn btn-ghost">Načíst další</button>`;
        grid.after(wrap);
        document.getElementById('load-more-btn').addEventListener('click', loadMore);
    }

    function removeLoadMoreBtn() {
        document.getElementById('load-more-wrap')?.remove();
    }

    // Debounce pro textové pole
    let debounceTimer;
    fName.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(loadStudents, 300);
    });
    [fCat, fLvl, fYear, fSort].forEach(el => el.addEventListener('change', loadStudents));

    // Klik na kartu
    grid.addEventListener('click', e => {
        const card = e.target.closest('.student-card');
        if (card) openStudentModal(card.dataset.id);
    });

    loadStudents();
}

function studentCardHTML(s, rank) {
    const ribbonsHtml = Object.entries(s.ribbons || {})
        .map(([cat, lvl]) => `<span class="student-ribbon-chip" title="${cat} L${lvl}">${ribbonSVG(cat, lvl, {w:70,h:24})}</span>`)
        .join('');
    return `
    <div class="student-card" data-id="${s.id}" role="listitem" tabindex="0" aria-label="Profil: ${esc(s.display_name)}">
        <div class="student-rank">#${rank}</div>
        <div class="student-name">${esc(s.display_name)}</div>
        <div class="student-meta">Nastoupil: ${s.admission_year}</div>
        <div class="student-score">Merit Score: ${s.merit_score}</div>
        <div class="student-ribbons">${ribbonsHtml}</div>
    </div>`;
}

function renderStudents(container, students, startRank) {
    if (!students.length) {
        container.innerHTML = '<div class="empty-state">Žádní studenti neodpovídají zadaným filtrům.</div>';
        return;
    }

    container.innerHTML = students.map((s, i) => studentCardHTML(s, startRank + i + 1)).join('');

    // klávesnice Enter
    container.querySelectorAll('.student-card').forEach(card => {
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openStudentModal(card.dataset.id); }
        });
    });
}

function appendStudents(container, students, startRank) {
    const frag = document.createDocumentFragment();
    students.forEach((s, i) => {
        const div = document.createElement('div');
        div.innerHTML = studentCardHTML(s, startRank + i + 1);
        const card = div.firstElementChild;
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openStudentModal(card.dataset.id); }
        });
        frag.appendChild(card);
    });
    container.appendChild(frag);
}

async function openStudentModal(id) {
    modal.open('<div class="loading" style="text-align:center;padding:2rem">Načítání…</div>');
    try {
        const s = await apiFetch({ action: 'student', id });
        const meritsByDate = [...s.merits].sort((a, b) => b.granted_at.localeCompare(a.granted_at));

        const timelineHtml = meritsByDate.map(m => {
            const catMeta = CATEGORIES_META[m.category] || {};
            const lvlName = (LEVEL_NAMES[m.level]) || `L${m.level}`;
            const date    = m.granted_at.substring(0, 10);
            return `
            <div class="timeline-item">
                <div class="timeline-ribbon">${ribbonSVG(m.category, m.level, {w:100,h:34})}</div>
                <div class="timeline-info">
                    <div class="timeline-title">${esc(catMeta.title || m.category)} — ${esc(lvlName)}</div>
                    <div class="timeline-meta">${esc(m.granted_by)} · ${date}</div>
                    ${m.description ? `<div class="timeline-desc">${esc(m.description)}</div>` : ''}
                </div>
            </div>`;
        }).join('');

        modal.open(`
            <div class="modal-student-name">${esc(s.display_name)}</div>
            <div class="modal-student-meta">Nastoupil: ${s.admission_year}</div>
            <span class="modal-score">Merit Score: ${s.merit_score}</span>
            <span class="modal-score" style="color:var(--muted);background:rgba(255,255,255,.06);border-color:var(--border)">Ocenění: ${s.merits.length}</span>
            <p class="section-label" style="margin-top:1.25rem">Časová osa ocenění</p>
            <div class="timeline">${timelineHtml || '<p style="color:var(--muted)">Žádná ocenění.</p>'}</div>
        `);
    } catch (e) {
        modal.open(`<p style="color:#fca5a5;padding:1rem">Chyba: ${e.message}</p>`);
    }
}

// ─── Katalog kategorií ────────────────────────────────────────────────────────

function renderCatalog(navEl, catCont) {
    apiFetch({ action: 'categories' }).then(cats => {
        let navHtml = '';
        let catsHtml = '';

        Object.entries(cats).forEach(([id, cat]) => {
            navHtml += `<a href="#cat-${id}" title="${id} — ${cat.title}">${ribbonSVG(id, 5)}</a>`;

            const levelsHtml = Object.entries(cat.levels).map(([lvl, data]) => `
                <div class="level-item">
                    <div class="level-ribbon">${ribbonSVG(id, parseInt(lvl))}</div>
                    <div class="level-content">
                        <div class="level-title">
                            ${esc(data.name)} <span class="level-lvl">(L${lvl})</span>
                        </div>
                        <div class="level-req">${esc(data.req)}</div>
                    </div>
                </div>`).join('');

            catsHtml += `
            <div id="cat-${id}" class="category-card" style="--cat-color:${cat.light}">
                <div class="category-header">
                    <div class="category-title">
                        <span class="category-abbr">${id}</span>
                        ${esc(cat.title)}
                    </div>
                    <div class="category-desc">${esc(cat.desc)}</div>
                </div>
                <div class="level-list">${levelsHtml}</div>
            </div>`;
        });

        navEl.innerHTML  = navHtml;
        catCont.innerHTML = catsHtml;
    }).catch(e => {
        catCont.innerHTML = `<p class="empty-state">Chyba načítání katalogu: ${e.message}</p>`;
    });
}

// ─── Utility ──────────────────────────────────────────────────────────────────

function esc(str) {
    if (str == null) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

// ─── Bootstrap ────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('students-grid')) {
        initIndexPage();
    }
});
