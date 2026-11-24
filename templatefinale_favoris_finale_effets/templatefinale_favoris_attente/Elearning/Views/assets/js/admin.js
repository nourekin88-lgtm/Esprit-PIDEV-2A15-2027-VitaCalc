(() => {
    'use strict';

    const $ = (selector) => document.querySelector(selector);
    let events = [];
    let registrations = [];
    let waitlist = [];
    let favCounts = {};

    const escapeHTML = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[char]);

    const formatDate = (value) => {
        if (!value) return '\u2014';
        const date = new Date(`${String(value).slice(0, 10)}T12:00:00`);
        return Number.isNaN(date.getTime()) ? '\u2014' : date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric' });
    };

    async function request(resource, options = {}, id = '') {
        const idPart = id !== '' && id != null ? `&id=${encodeURIComponent(id)}` : '';
        const response = await fetch(`api.php?resource=${resource}${idPart}`, {
            headers: { 'Content-Type': 'application/json' },
            ...options
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Une erreur est survenue.');
        return data;
    }

    async function refresh() {
        [events, registrations] = await Promise.all([request('events'), request('registrations')]);
        await loadExtras();
        render();
    }

    async function loadExtras() {
        const [wait, fav] = await Promise.all([request('waitlist'), request('favorites')]);
        waitlist  = wait;
        favCounts = fav.counts || {};
    }

    const waitingCount = (id) => waitlist.filter((w) => String(w.id_evenement) === String(id) && w.statut === 'en_attente').length;

    function showToast(message) {
        const notice = $('#adminNotice');
        notice.textContent = message;
        notice.classList.add('show');
        window.setTimeout(() => notice.classList.remove('show'), 2800);
    }

    function showView(name) {
        document.querySelectorAll('.view').forEach((v) => v.classList.remove('active'));
        document.getElementById(`view-${name}`).classList.add('active');
        document.querySelectorAll('.side-link').forEach((b) => b.classList.toggle('active', b.dataset.view === name));
        $('#crumbTitle').textContent = name === 'dashboard' ? "Vue d'ensemble" : name === 'events' ? '\u00c9v\u00e9nements' : name === 'waitlist' ? "Liste d'attente" : 'Inscriptions';
        $('#sidebar').classList.remove('open');
        render();
    }

    document.querySelectorAll('.side-link').forEach((b) => b.addEventListener('click', () => showView(b.dataset.view)));
    document.querySelectorAll('[data-goto]').forEach((b) => b.addEventListener('click', () => showView(b.dataset.goto)));
    document.querySelectorAll('[data-new-event]').forEach((b) => b.addEventListener('click', () => openForm()));
    $('#hamb').addEventListener('click', () => $('#sidebar').classList.toggle('open'));
    $('#todayLabel').textContent = new Date().toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });

    // ── Rendu Dashboard ───────────────────────────────────────────────────────
    function renderDashboard() {
        const today = new Date().toISOString().slice(0, 10);
        const upcoming = events.filter((ev) => ev.date >= today);
        const free = events.reduce((s, ev) => s + Math.max(0, Number(ev.capacite) - Number(ev.placesReservees)), 0);
        const fillRate = events.length
            ? Math.round(events.reduce((s, ev) => s + Number(ev.placesReservees) / Math.max(1, Number(ev.capacite)), 0) / events.length * 100)
            : 0;

        $('#metrics').classList.add('five');
        const waitingTotal = waitlist.filter((w) => w.statut === 'en_attente').length;
        $('#metrics').innerHTML = [
            ['\u2726', events.length,       '\u00c9v\u00e9nements au total',              ''],
            ['\u25f7', upcoming.length,     '\u00c9v\u00e9nements \u00e0 venir',          'blue'],
            ['\u2667', registrations.length,'Inscriptions re\u00e7ues',                   'amber'],
            ['\u2197', free,                `Places disponibles \u00b7 ${fillRate}% remplis`, 'pink'],
            ['\u23f3', waitingTotal,        'En liste d\u2019attente',                    'amber']
        ].map(([ic, v, l, c]) => `<div class="metric"><span class="metric-ico ${c}">${ic}</span><span><b>${v}</b><small>${l}</small></span></div>`).join('');

        $('#upcomingList').innerHTML = upcoming.length
            ? upcoming.slice().sort((a, b) => a.date.localeCompare(b.date)).slice(0, 4).map((ev) => {
                const full = Number(ev.placesReservees) >= Number(ev.capacite);
                return `<div class="upcoming-item">
                    <span class="event-thumb" style="background-image:url('${escapeHTML(ev.image)}')"></span>
                    <span class="upcoming-info"><b>${escapeHTML(ev.titre)}</b><small>${formatDate(ev.date)} \u00b7 ${escapeHTML(ev.heure)} \u00b7 ${escapeHTML(ev.lieu)}</small></span>
                    <span class="pill-status ${full ? 'full' : ''}">${full ? 'Complet' : '\u00c0 venir'}</span>
                </div>`;
            }).join('')
            : '<div class="empty-row">Aucun \u00e9v\u00e9nement programm\u00e9.</div>';

        const recent = registrations.slice().sort((a, b) => String(b.date_inscription).localeCompare(String(a.date_inscription))).slice(0, 4);
        $('#recentRegs').innerHTML = recent.length
            ? `<div class="table-wrap"><table><thead><tr><th>PARTICIPANT</th><th>\u00c9V\u00c9NEMENT</th><th>DATE</th><th>CONTACT</th></tr></thead><tbody>
                ${recent.map((row) => {
                    const ev = events.find((item) => String(item.id) === String(row.id_evenement));
                    return `<tr><td><b>${escapeHTML(row.prenom)} ${escapeHTML(row.nom)}</b></td><td>${escapeHTML(ev?.titre || '\u00c9v\u00e9nement supprim\u00e9')}</td><td>${formatDate(row.date_inscription)}</td><td>${escapeHTML(row.email)}</td></tr>`;
                }).join('')}
                </tbody></table></div>`
            : "<div class=\"empty-row\">Les inscriptions r\u00e9centes s\u2019afficheront ici.</div>";
    }

    // ── Rendu tableau événements ───────────────────────────────────────────────
    function renderEvents() {
        if (!$('#eventRows')) return;
        const query    = $('#eventQuery').value.toLocaleLowerCase('fr');
        const category = $('#eventCategory').value;
        const list = events.filter((ev) =>
            (!query || `${ev.titre} ${ev.lieu}`.toLocaleLowerCase('fr').includes(query)) &&
            (category === 'all' || ev.categorie === category)
        );
        $('#eventRows').innerHTML = list.length
            ? list.map((ev) => {
                const full = Number(ev.placesReservees) >= Number(ev.capacite);
                return `<tr>
                    <td><div class="table-event"><img src="${escapeHTML(ev.image)}" alt=""><span><b>${escapeHTML(ev.titre)}</b><small>${escapeHTML(ev.lieu)}</small></span></div></td>
                    <td>${formatDate(ev.date)}<br><small>${escapeHTML(ev.heure)}</small></td>
                    <td><span class="badge-cat">${escapeHTML(ev.categorie)}</span></td>
                    <td>${ev.placesReservees} / ${ev.capacite}${favCounts[String(ev.id)] ? `<br><span class="fav-count">\u2665 ${favCounts[String(ev.id)]} favori${favCounts[String(ev.id)] > 1 ? 's' : ''}</span>` : ''}</td>
                    <td><span class="pill-status ${full ? 'full' : ''}">${full ? (waitingCount(ev.id) ? `Complet \u00b7 ${waitingCount(ev.id)} en attente` : 'Complet') : 'Publi\u00e9'}</span></td>
                    <td><div class="row-actions">
                        <button data-edit="${escapeHTML(String(ev.id))}" title="Modifier">\u270e</button>
                        <button class="delete" data-delete="${escapeHTML(String(ev.id))}" title="Supprimer">\u232b</button>
                    </div></td>
                </tr>`;
            }).join('')
            : '<tr><td colspan="6" class="empty-row">Aucun \u00e9v\u00e9nement trouv\u00e9.</td></tr>';

        $('#eventRows').querySelectorAll('[data-edit]').forEach((btn) =>
            btn.addEventListener('click', () => openForm(events.find((ev) => String(ev.id) === String(btn.dataset.edit))))
        );
        $('#eventRows').querySelectorAll('[data-delete]').forEach((btn) =>
            btn.addEventListener('click', () => deleteEvent(btn.dataset.delete))
        );
    }

    // ── Rendu tableau inscriptions ─────────────────────────────────────────────
    function renderRegistrations() {
        if (!$('#regRows')) return;
        const selected = $('#regEvent').value;
        $('#regEvent').innerHTML = '<option value="all">Tous les \u00e9v\u00e9nements</option>' +
            events.map((ev) => `<option value="${escapeHTML(String(ev.id))}">${escapeHTML(ev.titre)}</option>`).join('');
        if (events.some((ev) => String(ev.id) === String(selected))) $('#regEvent').value = selected;

        const query   = $('#regQuery').value.toLocaleLowerCase('fr');
        const eventId = $('#regEvent').value;
        const list = registrations.filter((row) => {
            const ev   = events.find((item) => String(item.id) === String(row.id_evenement));
            const text = `${row.prenom} ${row.nom} ${row.email} ${ev?.titre || ''}`.toLocaleLowerCase('fr');
            return (!query || text.includes(query)) && (eventId === 'all' || String(row.id_evenement) === String(eventId));
        });
        $('#regRows').innerHTML = list.length
            ? list.map((row) => {
                const ev       = events.find((item) => String(item.id) === String(row.id_evenement));
                const initials = `${(row.prenom || '?')[0]}${(row.nom || '?')[0]}`;
                return `<tr>
                    <td><div class="table-event"><span class="admin-avatar">${escapeHTML(initials)}</span><span><b>${escapeHTML(row.prenom)} ${escapeHTML(row.nom)}</b><small>${escapeHTML(row.email)}</small></span></div></td>
                    <td>${escapeHTML(ev?.titre || '\u00c9v\u00e9nement supprim\u00e9')}</td>
                    <td>${formatDate(row.date_inscription)}</td>
                    <td>${escapeHTML(row.telephone || '\u2014')}</td>
                    <td><span class="pill-status">Confirm\u00e9e</span></td>
                    <td><div class="row-actions"><button class="delete" data-cancel-reg="${escapeHTML(String(row.id))}" title="Annuler l\u2019inscription">\u2715 Annuler</button></div></td>
                </tr>`;
            }).join('')
            : '<tr><td colspan="6" class="empty-row">Aucune inscription trouv\u00e9e.</td></tr>';
        $('#regRows').querySelectorAll('[data-cancel-reg]').forEach((btn) =>
            btn.addEventListener('click', () => cancelRegistration(btn.dataset.cancelReg))
        );
    }

    // ── Liste d'attente ───────────────────────────────────────────────────────
    function renderWaitlist() {
        if (!$('#waitRows')) return;
        const selected = $('#waitEvent').value;
        $('#waitEvent').innerHTML = '<option value="all">Tous les \u00e9v\u00e9nements</option>' +
            events.map((ev) => `<option value="${escapeHTML(String(ev.id))}">${escapeHTML(ev.titre)}</option>`).join('');
        if (events.some((ev) => String(ev.id) === String(selected))) $('#waitEvent').value = selected;

        const query   = $('#waitQuery').value.toLocaleLowerCase('fr');
        const eventId = $('#waitEvent').value;
        const status  = $('#waitStatus').value;
        const list = waitlist.filter((row) => {
            const ev   = events.find((item) => String(item.id) === String(row.id_evenement));
            const text = `${row.prenom} ${row.nom} ${row.email} ${ev?.titre || ''}`.toLocaleLowerCase('fr');
            return (!query || text.includes(query))
                && (eventId === 'all' || String(row.id_evenement) === String(eventId))
                && (status === 'all' || row.statut === status);
        });

        $('#waitRows').innerHTML = list.length
            ? list.map((row) => {
                const ev       = events.find((item) => String(item.id) === String(row.id_evenement));
                const initials = `${(row.prenom || '?')[0]}${(row.nom || '?')[0]}`;
                const waiting  = row.statut === 'en_attente';
                const hasRoom  = ev && Number(ev.placesReservees) < Number(ev.capacite);
                return `<tr>
                    <td>${waiting ? `<span class="pos-chip">#${row.position}</span>` : '\u2014'}</td>
                    <td><div class="table-event"><span class="admin-avatar">${escapeHTML(initials)}</span><span><b>${escapeHTML(row.prenom)} ${escapeHTML(row.nom)}</b><small>${escapeHTML(row.email)}</small></span></div></td>
                    <td>${escapeHTML(ev?.titre || '\u00c9v\u00e9nement supprim\u00e9')}</td>
                    <td>${formatDate(row.date_demande)}</td>
                    <td>${escapeHTML(row.telephone || '\u2014')}</td>
                    <td><span class="pill-status ${waiting ? 'waiting' : 'promoted'}">${waiting ? 'En attente' : 'Inscrit(e)'}</span></td>
                    <td><div class="row-actions">
                        ${waiting ? `<button class="promote" data-promote="${row.id}" title="${hasRoom ? 'Inscrire maintenant' : 'Aucune place disponible'}" ${hasRoom ? '' : 'disabled'}>\u2713 Inscrire</button>` : ''}
                        <button class="delete" data-wait-delete="${row.id}" title="Retirer de la liste">\u232b</button>
                    </div></td>
                </tr>`;
            }).join('')
            : '<tr><td colspan="7" class="empty-row">Aucune personne en liste d\u2019attente.</td></tr>';

        $('#waitRows').querySelectorAll('[data-promote]').forEach((btn) =>
            btn.addEventListener('click', () => promoteWaiting(btn.dataset.promote))
        );
        $('#waitRows').querySelectorAll('[data-wait-delete]').forEach((btn) =>
            btn.addEventListener('click', () => removeWaiting(btn.dataset.waitDelete))
        );
    }

    async function promoteWaiting(id) {
        try {
            await request('waitlist', { method: 'POST', body: JSON.stringify({ action: 'promote', id: Number(id) }) });
            await refresh();
            showToast('Personne inscrite \u00e0 l\u2019\u00e9v\u00e9nement.');
        } catch (err) {
            showToast(err.message);
        }
    }

    async function removeWaiting(id) {
        if (!window.confirm('Retirer cette personne de la liste d\u2019attente ?')) return;
        try {
            await request('waitlist', { method: 'DELETE' }, id);
            await refresh();
            showToast('Demande retir\u00e9e de la liste d\u2019attente.');
        } catch (err) {
            showToast(err.message);
        }
    }

    async function cancelRegistration(id) {
        const row = registrations.find((r) => String(r.id) === String(id));
        if (!row || !window.confirm(`Annuler l\u2019inscription de ${row.prenom} ${row.nom} ? La premi\u00e8re personne en liste d\u2019attente sera inscrite automatiquement.`)) return;
        try {
            const res = await request('registrations', { method: 'DELETE' }, id);
            await refresh();
            const n = (res.promoted || []).length;
            showToast(n
                ? `Inscription annul\u00e9e \u2014 ${res.promoted[0].prenom} ${res.promoted[0].nom} a \u00e9t\u00e9 inscrit(e) depuis la liste d\u2019attente.`
                : 'Inscription annul\u00e9e.');
        } catch (err) {
            showToast(err.message);
        }
    }

    function render() {
        $('#sideEventCount').textContent = events.length;
        $('#sideRegCount').textContent   = registrations.length;
        $('#sideWaitCount').textContent  = waitlist.filter((w) => w.statut === 'en_attente').length;
        if ($('#view-dashboard').classList.contains('active'))     renderDashboard();
        if ($('#view-events').classList.contains('active'))        renderEvents();
        if ($('#view-registrations').classList.contains('active')) renderRegistrations();
        if ($('#view-waitlist').classList.contains('active'))      renderWaitlist();
    }

    // ── Validation formulaire événement ───────────────────────────────────────

    function setAdminFieldError(fieldId, errorId, message) {
        const field = document.getElementById(fieldId);
        const span  = document.getElementById(errorId);
        if (!field || !span) return;
        if (message) {
            field.classList.add('error');
            span.textContent = message;
        } else {
            field.classList.remove('error');
            span.textContent = '';
        }
    }

    function clearAdminFormErrors() {
        document.querySelectorAll('.admin-field.error').forEach((el) => el.classList.remove('error'));
        ['err-titre','err-date','err-heure','err-lieu','err-capacite','err-prix','err-desc'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.textContent = '';
        });
        const banner = $('#modalErrorBanner');
        if (banner) { banner.textContent = ''; banner.classList.remove('show'); }
    }

    function showModalError(message) {
        const banner = $('#modalErrorBanner');
        if (!banner) return;
        banner.textContent = message;
        banner.classList.add('show');
        banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function validateEventForm() {
        let valid = true;
        const today  = new Date().toISOString().slice(0, 10);
        const maxDate = new Date(); maxDate.setFullYear(maxDate.getFullYear() + 2);
        const maxDateStr = maxDate.toISOString().slice(0, 10);

        // Titre
        const titre = $('#aTitle').value.trim();
        if (!titre) {
            setAdminFieldError('aTitle', 'err-titre', 'Le titre est obligatoire.');
            valid = false;
        } else if (titre.length < 3) {
            setAdminFieldError('aTitle', 'err-titre', 'Le titre doit contenir au moins 3 caract\u00e8res.');
            valid = false;
        } else if (titre.length > 150) {
            setAdminFieldError('aTitle', 'err-titre', 'Le titre ne peut pas d\u00e9passer 150 caract\u00e8res.');
            valid = false;
        } else if (/[0-9]/.test(titre)) {
            setAdminFieldError('aTitle', 'err-titre', 'Le titre ne peut pas contenir de chiffres.');
            valid = false;
        } else {
            setAdminFieldError('aTitle', 'err-titre', '');
        }

        // Date
        const date = $('#aDate').value;
        if (!date) {
            setAdminFieldError('aDate', 'err-date', 'La date est obligatoire.');
            valid = false;
        } else if (date < today) {
            setAdminFieldError('aDate', 'err-date', 'La date ne peut pas \u00eatre dans le pass\u00e9.');
            valid = false;
        } else if (date > maxDateStr) {
            setAdminFieldError('aDate', 'err-date', 'La date ne peut pas d\u00e9passer 2 ans dans le futur.');
            valid = false;
        } else {
            setAdminFieldError('aDate', 'err-date', '');
        }

        // Heure
        const heure = $('#aTime').value;
        if (!heure) {
            setAdminFieldError('aTime', 'err-heure', "L'heure est obligatoire.");
            valid = false;
        } else if (Number(heure.split(':')[0]) < 6) {
            setAdminFieldError('aTime', 'err-heure', "L'heure doit \u00eatre entre 06:00 et 23:59.");
            valid = false;
        } else {
            setAdminFieldError('aTime', 'err-heure', '');
        }

        // Lieu
        const lieu = $('#aPlace').value.trim();
        if (!lieu) {
            setAdminFieldError('aPlace', 'err-lieu', 'Le lieu est obligatoire.');
            valid = false;
        } else if (lieu.length < 3) {
            setAdminFieldError('aPlace', 'err-lieu', 'Le lieu doit contenir au moins 3 caract\u00e8res.');
            valid = false;
        } else {
            setAdminFieldError('aPlace', 'err-lieu', '');
        }

        // Capacité
        const cap = Number($('#aCapacity').value);
        if (!$('#aCapacity').value || !Number.isInteger(cap) || cap < 1) {
            setAdminFieldError('aCapacity', 'err-capacite', 'La capacit\u00e9 doit \u00eatre un entier sup\u00e9rieur \u00e0 z\u00e9ro.');
            valid = false;
        } else if (cap > 10000) {
            setAdminFieldError('aCapacity', 'err-capacite', 'La capacit\u00e9 ne peut pas d\u00e9passer 10\u202f000.');
            valid = false;
        } else {
            setAdminFieldError('aCapacity', 'err-capacite', '');
        }

        // Prix
        const prixVal = $('#aPrice').value;
        const prix    = parseFloat(prixVal);
        if (prixVal === '' || isNaN(prix)) {
            setAdminFieldError('aPrice', 'err-prix', 'Le tarif est obligatoire (mettez 0 pour gratuit).');
            valid = false;
        } else if (prix < 0) {
            setAdminFieldError('aPrice', 'err-prix', 'Le tarif ne peut pas \u00eatre n\u00e9gatif.');
            valid = false;
        } else {
            setAdminFieldError('aPrice', 'err-prix', '');
        }

        // Description
        const desc = $('#aDesc').value.trim();
        if (desc.length > 1000) {
            setAdminFieldError('aDesc', 'err-desc', `La description d\u00e9passe 1\u202f000 caract\u00e8res (${desc.length}).`);
            valid = false;
        } else {
            setAdminFieldError('aDesc', 'err-desc', '');
        }

        return valid;
    }

    function attachAdminLiveValidation() {
        const today = new Date().toISOString().slice(0, 10);

        const rules = [
            { id: 'aTitle',    errId: 'err-titre',    check: v => v.length >= 3 && v.length <= 150 && !/[0-9]/.test(v) },
            { id: 'aDate',     errId: 'err-date',     check: v => v !== '' && v >= today },
            { id: 'aTime',     errId: 'err-heure',    check: v => v !== '' && Number(v.split(':')[0]) >= 6 },
            { id: 'aPlace',    errId: 'err-lieu',     check: v => v.length >= 3 },
            { id: 'aCapacity', errId: 'err-capacite', check: v => Number.isInteger(Number(v)) && Number(v) >= 1 },
            { id: 'aPrice',    errId: 'err-prix',     check: v => v !== '' && !isNaN(parseFloat(v)) && parseFloat(v) >= 0 },
            { id: 'aDesc',     errId: 'err-desc',     check: v => v.length <= 1000 },
        ];

        rules.forEach(({ id, errId, check }) => {
            document.getElementById(id)?.addEventListener('input', () => {
                const val = document.getElementById(id).value.trim();
                if (check(val)) setAdminFieldError(id, errId, '');
            });
        });

        // Compteur description
        $('#aDesc')?.addEventListener('input', () => {
            const len     = $('#aDesc').value.trim().length;
            const counter = $('#aDescCounter');
            if (counter) {
                counter.textContent = `${len}/1000`;
                counter.style.color = len > 900 ? '#c73a3a' : '#9aaa9f';
            }
        });

        // Bloquer chiffres dans le titre
        document.getElementById('aTitle')?.addEventListener('keypress', (e) => {
            if (/[0-9]/.test(e.key)) e.preventDefault();
        });
        document.getElementById('aTitle')?.addEventListener('paste', (e) => {
            e.preventDefault();
            const text  = (e.clipboardData || window.clipboardData).getData('text');
            const clean = text.replace(/[0-9]/g, '');
            document.execCommand('insertText', false, clean);
        });

        // Bloquer lettres dans Capacité
        document.getElementById('aCapacity')?.addEventListener('keypress', (e) => {
            if (!/[0-9]/.test(e.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key))
                e.preventDefault();
        });

        // Bloquer lettres dans Prix
        document.getElementById('aPrice')?.addEventListener('keypress', (e) => {
            if (!/[0-9.,]/.test(e.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key))
                e.preventDefault();
        });
    }

    // ── Upload image local ────────────────────────────────────────────────────

    function setUploadStatus(message, type = '') {
        const el = $('#aImageStatus');
        el.textContent = message;
        el.className   = 'img-upload-status' + (type ? ` show ${type}` : '');
    }

    function showImagePreview(src, name) {
        $('#aImagePreviewImg').src      = src;
        $('#aImagePreviewName').textContent = name;
        $('#aImagePreview').classList.add('show');
        $('#aImageDropzone').style.display = 'none';
        setUploadStatus('');
    }

    function resetImagePicker() {
        $('#aImage').value                 = '';
        $('#aImagePreviewImg').src         = '';
        $('#aImagePreviewName').textContent = '';
        $('#aImagePreview').classList.remove('show');
        $('#aImageDropzone').style.display = '';
        $('#aImageFile').value             = '';
        setUploadStatus('');
    }

    async function uploadFile(file) {
        // Validation côté client
        const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!allowed.includes(file.type)) {
            setUploadStatus('\u274c Format non autoris\u00e9. Utilisez JPG, PNG, WEBP ou GIF.', 'error');
            return;
        }
        if (file.size > 5 * 1024 * 1024) {
            setUploadStatus('\u274c Le fichier d\u00e9passe 5\u00a0Mo.', 'error');
            return;
        }

        // Prévisualisation immédiate
        const reader = new FileReader();
        reader.onload = (e) => showImagePreview(e.target.result, file.name);
        reader.readAsDataURL(file);

        setUploadStatus('\u23f3 Envoi en cours\u2026', 'loading');

        const form = new FormData();
        form.append('image', file);

        try {
            const res  = await fetch('api.php?resource=upload', { method: 'POST', body: form });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Erreur upload.');
            $('#aImage').value = data.path;
            setUploadStatus('\u2713 Image enregistr\u00e9e : ' + data.filename, 'success');
        } catch (err) {
            setUploadStatus('\u274c ' + err.message, 'error');
            $('#aImage').value = '';
        }
    }

    function initImageUploader() {
        const dropzone  = $('#aImageDropzone');
        const fileInput = $('#aImageFile');

        // Clic sur la zone → ouvre le sélecteur de fichier
        dropzone.addEventListener('click', (e) => {
            if (e.target !== fileInput) fileInput.click();
        });

        // Sélection via le sélecteur
        fileInput.addEventListener('change', () => {
            if (fileInput.files[0]) uploadFile(fileInput.files[0]);
        });

        // Drag & drop
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.classList.add('drag-over');
        });
        dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('drag-over');
            const file = e.dataTransfer?.files[0];
            if (file) uploadFile(file);
        });

        // Bouton Changer
        $('#aImageClear').addEventListener('click', resetImagePicker);
    }

    initImageUploader();

    // ── Formulaire événement (modal) ──────────────────────────────────────────

    function openForm(ev = null) {
        clearAdminFormErrors();
        const form = $('#adminEventForm');
        form.reset();
        $('#aId').value       = ev?.id          || '';
        $('#modalHeading').textContent = ev ? 'Modifier l\u2019\u00e9v\u00e9nement' : 'Cr\u00e9er un \u00e9v\u00e9nement';
        $('#aTitle').value    = ev?.titre        || '';
        $('#aCategory').value = ev?.categorie    || 'Bien-\u00eatre';
        $('#aDate').value     = ev?.date         || '';
        $('#aTime').value     = ev?.heure?.slice(0, 5) || '10:00';
        $('#aPlace').value    = ev?.lieu         || '';
        $('#aZone').value     = ev?.zone         || 'Tunis';
        $('#aCapacity').value = ev?.capacite     || 20;
        $('#aPrice').value    = ev?.prix         ?? 0;
        $('#aFormat').value   = ev?.format       || 'Pr\u00e9sentiel';
        $('#aImage').value    = ev?.image        || '';
        $('#aLink').value     = ev?.lien         || '';
        $('#aDesc').value     = ev?.description  || '';

        // Compteur description
        const counter = document.getElementById('aDescCounter');
        if (counter) counter.textContent = `${($('#aDesc').value.trim().length)}/1000`;

        // Image : réinitialiser l'uploader
        resetImagePicker();
        if (ev?.image) {
            $('#aImage').value = ev.image;
            $('#aImagePreviewImg').src       = ev.image;
            $('#aImagePreviewName').textContent = ev.image.split('/').pop();
            $('#aImagePreview').classList.add('show');
            $('#aImageDropzone').style.display = 'none';
        }

        $('#eventModal').classList.add('show');
        attachAdminLiveValidation();
    }

    async function deleteEvent(id) {
        const ev = events.find((item) => String(item.id) === String(id));
        if (!ev || !window.confirm(`Supprimer \u00ab ${ev.titre} \u00bb ? Les inscriptions associ\u00e9es seront \u00e9galement supprim\u00e9es.`)) return;
        try {
            await request('events', { method: 'DELETE' }, id);
            await refresh();
            showToast('\u00c9v\u00e9nement supprim\u00e9.');
        } catch (err) {
            showToast(err.message);
        }
    }

    // Fermeture modal
    document.querySelectorAll('[data-modal-close]').forEach((btn) =>
        btn.addEventListener('click', () => $('#eventModal').classList.remove('show'))
    );
    $('#eventModal').addEventListener('click', (e) => {
        if (e.target === $('#eventModal')) $('#eventModal').classList.remove('show');
    });

    // Soumission formulaire événement
    $('#adminEventForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        clearAdminFormErrors();

        if (!validateEventForm()) {
            showModalError('Veuillez corriger les erreurs avant d\u2019enregistrer.');
            return;
        }

        const id      = $('#aId').value;
        const payload = {
            titre:       $('#aTitle').value.trim(),
            categorie:   $('#aCategory').value,
            date:        $('#aDate').value,
            heure:       $('#aTime').value,
            lieu:        $('#aPlace').value.trim(),
            zone:        $('#aZone').value,
            capacite:    Number($('#aCapacity').value),
            prix:        Number($('#aPrice').value) || 0,
            format:      $('#aFormat').value,
            image:       $('#aImage').value || 'Views/assets/img/events/yoga-event.jpg',
            lien:        $('#aLink').value.trim(),
            description: $('#aDesc').value.trim(),
        };

        try {
            await request('events', { method: id ? 'PUT' : 'POST', body: JSON.stringify(payload) }, id);
            $('#eventModal').classList.remove('show');
            await refresh();
            showToast(id ? '\u00c9v\u00e9nement modifi\u00e9.' : '\u00c9v\u00e9nement cr\u00e9\u00e9.');
        } catch (err) {
            showModalError(err.message);
        }
    });

    // Filtres
    $('#eventQuery').addEventListener('input', renderEvents);
    $('#eventCategory').addEventListener('change', renderEvents);
    $('#regQuery').addEventListener('input', renderRegistrations);
    $('#regEvent').addEventListener('change', renderRegistrations);
    $('#waitQuery').addEventListener('input', renderWaitlist);
    $('#waitEvent').addEventListener('change', renderWaitlist);
    $('#waitStatus').addEventListener('change', renderWaitlist);

    // Export CSV
    $('#exportCsv').addEventListener('click', () => {
        const rows = [
            ['Pr\u00e9nom', 'Nom', 'E-mail', 'T\u00e9l\u00e9phone', '\u00c9v\u00e9nement', 'Date inscription'],
            ...registrations.map((row) => [
                row.prenom, row.nom, row.email, row.telephone,
                events.find((ev) => String(ev.id) === String(row.id_evenement))?.titre || '',
                row.date_inscription || ''
            ])
        ];
        const csv  = '\ufeff' + rows.map((r) => r.map((c) => `"${String(c ?? '').replaceAll('"', '""')}"`).join(';')).join('\n');
        const link = document.createElement('a');
        link.href  = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        link.download = 'inscriptions-vitacalc.csv';
        link.click();
        URL.revokeObjectURL(link.href);
    });

    // Chargement initial
    request('events').then((items) => {
        events = items;
        return request('registrations');
    }).then((items) => {
        registrations = items;
        return loadExtras();
    }).then(() => {
        render();
    }).catch((err) => showToast(`${err.message} V\u00e9rifiez que PHP est d\u00e9marr\u00e9.`));

    const loader = $('#siteLoader');
    if (loader) window.addEventListener('load', () => loader.classList.add('loaded'));
})();
