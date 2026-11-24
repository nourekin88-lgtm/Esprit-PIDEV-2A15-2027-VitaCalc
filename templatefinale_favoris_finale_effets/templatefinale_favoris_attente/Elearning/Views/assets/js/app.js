(() => {
    'use strict';

    const $ = (selector) => document.querySelector(selector);
    let events = [];
    let registrations = [];
    let favorites = new Set();   // ids des événements favoris de l'utilisateur
    let waitCounts = {};         // { idEvenement: nombre de personnes en attente }

    const escapeHTML = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[char]);

    const formatDate = (date) => new Date(`${date}T12:00:00`).toLocaleDateString('fr-FR', {
        weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
    });

    async function request(resource, options = {}) {
        const response = await fetch(`api.php?resource=${resource}`, {
            headers: { 'Content-Type': 'application/json' },
            ...options
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.error || 'Une erreur est survenue.');
        return data;
    }

    // ── Favoris (métier simple) ──────────────────────────────────────────────
    const waitingFor = (id) => Number(waitCounts[String(id)] || 0);

    async function loadExtras() {
        const [fav, wait] = await Promise.all([request('favorites'), request('waitlist&view=counts')]);
        favorites  = new Set((fav.ids || []).map(Number));
        waitCounts = wait.counts || {};
    }

    async function toggleFavorite(id) {
        const isFav = favorites.has(Number(id));
        const data = isFav
            ? await fetch(`api.php?resource=favorites&id=${encodeURIComponent(id)}`, { method: 'DELETE' }).then((r) => r.json())
            : await request('favorites', { method: 'POST', body: JSON.stringify({ id_evenement: Number(id) }) });
        if (data.error) throw new Error(data.error);
        favorites = new Set((data.ids || []).map(Number));
        return !isFav;
    }

    function toast(message) {
        let el = $('#siteToast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'siteToast';
            el.className = 'site-toast';
            el.setAttribute('role', 'status');
            document.body.appendChild(el);
        }
        el.textContent = message;
        el.classList.add('show');
        clearTimeout(toast.t);
        toast.t = setTimeout(() => el.classList.remove('show'), 2600);
    }

    /** Bouton « Ajouter aux favoris » de la page détail d'un événement. */
    function initDetailFavorite() {
        const btn = $('.fav-detail');
        if (!btn) return;
        const paint = (on) => {
            btn.classList.toggle('active', on);
            btn.setAttribute('aria-pressed', String(on));
            btn.querySelector('.fav-heart').textContent = on ? '♥' : '♡';
            btn.querySelector('.fav-label').textContent = on ? 'Dans mes favoris' : 'Ajouter aux favoris';
        };
        paint(favorites.has(Number(btn.dataset.favId)));
        btn.addEventListener('click', async () => {
            btn.disabled = true;
            try {
                const on = await toggleFavorite(btn.dataset.favId);
                paint(on);
                toast(on ? '♥ Ajouté à vos favoris' : 'Retiré de vos favoris');
            } catch (err) { toast(err.message); }
            finally { btn.disabled = false; }
        });
    }

    // ── Navigation commune ───────────────────────────────────────────────────
    function initCommonUI() {
        const menu = $('#eventMenu');
        const dropdown = $('#eventDropdown');
        if (menu && dropdown) {
            menu.addEventListener('click', (e) => {
                e.stopPropagation();
                dropdown.classList.toggle('open');
                menu.setAttribute('aria-expanded', dropdown.classList.contains('open'));
            });
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.nav-event')) dropdown.classList.remove('open');
            });
        }
        // Flèche « retour » : revient à la page précédente (sinon lien de secours)
        const back = $('[data-back]');
        if (back) {
            back.addEventListener('click', (e) => {
                let sameSite = false;
                try { sameSite = document.referrer && new URL(document.referrer).origin === location.origin; } catch (_) {}
                if (sameSite && history.length > 1) {
                    e.preventDefault();
                    history.back();
                }
            });
        }
        const loader = $('#siteLoader');
        if (loader) window.addEventListener('load', () => loader.classList.add('loaded'));
    }

    // ── Validation formulaire inscription ────────────────────────────────────

    // Lettres, accents, tiret, apostrophe, espace — au moins 2 caractères
    const NAME_RE  = /^[a-zA-ZÀ-ÖØ-öø-ÿ'\- ]{2,}$/;
    // Chiffres uniquement, optionnellement précédés de + (indicatif), entre 8 et 15 chiffres
    const PHONE_RE = /^\+?[0-9]{8,15}$/;

    /**
     * Affiche ou efface le message d'erreur d'un champ.
     * @param {string} fieldId   – id du conteneur .field
     * @param {string} errorId   – id du <span class="field-error">
     * @param {string} message   – texte d'erreur (vide = pas d'erreur)
     */
    function setFieldError(fieldId, errorId, message) {
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

    function clearAllErrors(form) {
        form.querySelectorAll('.field.error, .consent.error').forEach(el => el.classList.remove('error'));
        form.querySelectorAll('.field-error').forEach(el => el.textContent = '');
        const banner = $('#signupErrorBanner');
        if (banner) { banner.textContent = ''; banner.classList.remove('show'); }
    }

    function showBanner(message) {
        const banner = $('#signupErrorBanner');
        if (!banner) return;
        banner.textContent = message;
        banner.classList.add('show');
        banner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    /**
     * Valide tous les champs du formulaire d'inscription.
     * Retourne true si tout est valide, false sinon.
     */
    function validateSignupForm() {
        let valid = true;

        // Prénom
        const prenom = $('#firstName').value.trim();
        if (!prenom) {
            setFieldError('field-prenom', 'err-prenom', 'Le prénom est obligatoire.');
            valid = false;
        } else if (prenom.length < 2) {
            setFieldError('field-prenom', 'err-prenom', 'Le prénom doit contenir au moins 2 lettres.');
            valid = false;
        } else if (!NAME_RE.test(prenom)) {
            setFieldError('field-prenom', 'err-prenom', 'Le prénom ne doit contenir que des lettres.');
            valid = false;
        } else {
            setFieldError('field-prenom', 'err-prenom', '');
        }

        // Nom
        const nom = $('#lastName').value.trim();
        if (!nom) {
            setFieldError('field-nom', 'err-nom', 'Le nom est obligatoire.');
            valid = false;
        } else if (nom.length < 2) {
            setFieldError('field-nom', 'err-nom', 'Le nom doit contenir au moins 2 lettres.');
            valid = false;
        } else if (!NAME_RE.test(nom)) {
            setFieldError('field-nom', 'err-nom', 'Le nom ne doit contenir que des lettres.');
            valid = false;
        } else {
            setFieldError('field-nom', 'err-nom', '');
        }

        // Email
        const email = $('#email').value.trim();
        const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
        if (!email) {
            setFieldError('field-email', 'err-email', "L'adresse e-mail est obligatoire.");
            valid = false;
        } else if (!emailRe.test(email)) {
            setFieldError('field-email', 'err-email', "L'adresse e-mail n'est pas valide.");
            valid = false;
        } else {
            setFieldError('field-email', 'err-email', '');
        }

        // Téléphone
        const tel = $('#phone').value.trim();
        if (!tel) {
            setFieldError('field-telephone', 'err-telephone', 'Le numéro de téléphone est obligatoire.');
            valid = false;
        } else if (!/^[0-9+]/.test(tel)) {
            setFieldError('field-telephone', 'err-telephone', 'Le téléphone ne doit contenir que des chiffres.');
            valid = false;
        } else if (!PHONE_RE.test(tel.replace(/\s/g, ''))) {
            setFieldError('field-telephone', 'err-telephone', 'Numéro invalide : 8 à 15 chiffres (ex : 20123456 ou +21620123456).');
            valid = false;
        } else {
            setFieldError('field-telephone', 'err-telephone', '');
        }

        // Événement
        const eventId = $('#eventSelect').value;
        if (!eventId) {
            setFieldError('field-evenement', 'err-evenement', 'Veuillez sélectionner un événement.');
            valid = false;
        } else {
            setFieldError('field-evenement', 'err-evenement', '');
        }

        // Consentement
        const consent = $('#consentCheck');
        if (consent && !consent.checked) {
            document.getElementById('field-consent')?.classList.add('error');
            valid = false;
            if (valid === false && !$('#err-prenom').textContent && !$('#err-nom').textContent &&
                !$('#err-email').textContent && !$('#err-telephone').textContent && !$('#err-evenement').textContent) {
                showBanner('Veuillez accepter les conditions pour confirmer votre inscription.');
            }
        } else {
            document.getElementById('field-consent')?.classList.remove('error');
        }

        return valid;
    }

    // ── Validation en temps réel (efface l'erreur dès correction) ────────────
    function attachLiveValidation() {
        const fields = [
            { input: '#firstName', field: 'field-prenom',    err: 'err-prenom',    check: v => NAME_RE.test(v) },
            { input: '#lastName',  field: 'field-nom',       err: 'err-nom',       check: v => NAME_RE.test(v) },
            { input: '#email',     field: 'field-email',     err: 'err-email',     check: v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) },
            { input: '#phone',     field: 'field-telephone', err: 'err-telephone', check: v => PHONE_RE.test(v.replace(/\s/g, '')) },
            { input: '#eventSelect', field: 'field-evenement', err: 'err-evenement', check: v => v !== '' },
        ];
        fields.forEach(({ input, field, err, check }) => {
            const el = $(input);
            if (!el) return;
            el.addEventListener('input', () => {
                if (check(el.value.trim())) setFieldError(field, err, '');
            });
            el.addEventListener('change', () => {
                if (check(el.value.trim())) setFieldError(field, err, '');
            });
        });
        const consent = $('#consentCheck');
        if (consent) {
            consent.addEventListener('change', () => {
                if (consent.checked) document.getElementById('field-consent')?.classList.remove('error');
            });
        }
    }

    // ── Page Events ──────────────────────────────────────────────────────────
    function renderEvents() {
        const grid = $('#eventGrid');
        if (!grid) return;

        const categories = ['Tous', 'Bien-être', 'Nutrition', 'Sport', 'Cuisine', 'Dégustation'];
        const FAV = '__favoris';
        let activeCategory = 'Tous';
        const pills = $('#categoryPills');
        const today = new Date().toISOString().slice(0, 10);

        function draw() {
            let list = events.filter((ev) => activeCategory === 'Tous'
                || (activeCategory === FAV ? favorites.has(Number(ev.id)) : ev.categorie === activeCategory));
            drawFavPill();
            const query = ($('#eventSearch').value || '').trim().toLocaleLowerCase('fr');
            if (query) list = list.filter((ev) =>
                `${ev.titre} ${ev.description} ${ev.lieu} ${ev.categorie}`.toLocaleLowerCase('fr').includes(query));

            const format       = $('#modeFilter').value;
            const availability = $('#availabilityFilter').value;
            const price        = $('#priceFilter').value;
            const sort         = $('#sortFilter').value;

            if (format !== 'all') list = list.filter((ev) => format === 'online' ? ev.format === 'En ligne' : ev.format !== 'En ligne');
            if (availability === 'available') list = list.filter((ev) => ev.placesReservees < ev.capacite);
            if (availability === 'full')      list = list.filter((ev) => ev.placesReservees >= ev.capacite);
            if (price === 'free') list = list.filter((ev) => Number(ev.prix) === 0);
            if (price === 'paid') list = list.filter((ev) => Number(ev.prix) > 0);
            if (sort === 'price') list.sort((a, b) => Number(a.prix) - Number(b.prix));
            if (sort === 'title') list.sort((a, b) => a.titre.localeCompare(b.titre, 'fr'));
            if (sort === 'date')  list.sort((a, b) => a.date.localeCompare(b.date));

            $('#resultCount').textContent = `${list.length} événement${list.length === 1 ? '' : 's'} proposé${list.length === 1 ? '' : 's'}`;

            const upcoming  = events.filter((ev) => ev.date >= today).length;
            const available = events.reduce((t, ev) => t + Math.max(0, ev.capacite - ev.placesReservees), 0);
            $('#eventStats').innerHTML = [
                [events.length, 'Événements proposés'], [upcoming, 'À venir'],
                [available, 'Places disponibles'],      [registrations.length, 'Inscriptions reçues']
            ].map(([n, l]) => `<div class="stat"><b>${n}</b><span>${l}</span></div>`).join('');

            grid.innerHTML = list.length ? list.map((ev, i) => {
                const isFav     = favorites.has(Number(ev.id));
                const waiting   = waitingFor(ev.id);
                const d         = new Date(`${ev.date}T12:00:00`);
                const month     = d.toLocaleDateString('fr-FR', { month: 'short' }).replace('.', '').toUpperCase();
                const full      = Number(ev.placesReservees) >= Number(ev.capacite);
                const remaining = Math.max(0, Number(ev.capacite) - Number(ev.placesReservees));
                const detailUrl = `index.php?page=event-detail&id=${encodeURIComponent(String(ev.id))}`;
                return `<article id="event-${escapeHTML(String(ev.id))}" class="eventcard reveal" style="animation-delay:${i * 50}ms">
                    <a class="eventphoto" href="${detailUrl}" aria-label="Voir la page de ${escapeHTML(ev.titre)}" style="background-image:url('${escapeHTML(ev.image)}')">
                        <div class="datebadge"><small>${month}</small>${String(d.getDate()).padStart(2, '0')}</div>
                        <span class="categorytag">${escapeHTML(ev.categorie)}${ev.format === 'En ligne' ? ' · En ligne' : ''}</span>
                    </a>
                    <button type="button" class="fav-btn ${isFav ? 'active' : ''}" data-fav="${escapeHTML(String(ev.id))}"
                        aria-pressed="${isFav}" aria-label="${isFav ? 'Retirer des favoris' : 'Ajouter aux favoris'}" title="${isFav ? 'Retirer des favoris' : 'Ajouter aux favoris'}">${isFav ? '♥' : '♡'}</button>
                    <div class="eventbody">
                        <h3><a class="event-title-link" href="${detailUrl}">${escapeHTML(ev.titre)}</a></h3>
                        <p>${escapeHTML(ev.description)}</p>
                        <div class="meta">◷ &nbsp;${formatDate(ev.date)} · ${escapeHTML(ev.heure)}</div>
                        <div class="meta">⌖ &nbsp;${escapeHTML(ev.lieu)}</div>
                        ${/^https?:\/\//i.test(ev.lien || '') ? `<a class="event-official-link" href="${escapeHTML(ev.lien)}" target="_blank" rel="noopener noreferrer">🔗 Page officielle de l’événement ↗</a>` : ''}
                        <div class="eventfoot">
                            <div>
                                <div class="price">${Number(ev.prix) ? `${Number(ev.prix)} DT` : 'Gratuit'}</div>
                                <div class="seats ${full ? 'full' : ''}">${full ? (waiting ? `Complet · ${waiting} en attente` : 'Complet') : `${remaining} places restantes`}</div>
                            </div>
                            ${full
                                ? `<a class="btn btn-waitlist" href="index.php?page=signup&event=${encodeURIComponent(String(ev.id))}">⏳ Liste d’attente</a>`
                                : `<a class="btn" href="${detailUrl}">Voir détails →</a>`}
                        </div>
                    </div>
                </article>`;
            }).join('') : `<div class="empty">${activeCategory === FAV ? 'Vous n’avez pas encore de favoris. Cliquez sur ♡ pour en ajouter.' : 'Aucun événement ne correspond à votre recherche.'}</div>`;

            const hashId = decodeURIComponent(location.hash.slice(1));
            if (hashId.startsWith('event-'))
                window.requestAnimationFrame(() => document.getElementById(hashId)?.scrollIntoView({ behavior: 'smooth', block: 'center' }));
        }

        pills.innerHTML = categories.map((cat) =>
            `<button class="pill ${cat === 'Tous' ? 'active' : ''}" data-category="${escapeHTML(cat)}">${escapeHTML(cat)}</button>`).join('')
            + `<button class="pill pill-fav" id="favPill" data-category="${FAV}"></button>`;
        function drawFavPill() {
            const pill = $('#favPill');
            if (!pill) return;
            const n = events.filter((ev) => favorites.has(Number(ev.id))).length;
            pill.innerHTML = `♥ Mes favoris <span class="pill-count">${n}</span>`;
            pill.classList.toggle('active', activeCategory === FAV);
        }
        grid.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-fav]');
            if (!btn) return;
            btn.disabled = true;
            try {
                const on = await toggleFavorite(btn.dataset.fav);
                toast(on ? '♥ Ajouté à vos favoris' : 'Retiré de vos favoris');
                draw();
            } catch (err) { toast(err.message); btn.disabled = false; }
        });
        pills.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-category]');
            if (!btn) return;
            activeCategory = btn.dataset.category;
            pills.querySelectorAll('.pill').forEach((p) => p.classList.toggle('active', p === btn));
            draw();
        });
        ['eventSearch', 'modeFilter', 'availabilityFilter', 'priceFilter', 'sortFilter'].forEach((id) => {
            $(`#${id}`)?.addEventListener(id === 'eventSearch' ? 'input' : 'change', draw);
        });
        $('#filtersButton')?.addEventListener('click', () => $('#filterPanel').classList.toggle('show'));
        draw();
    }

    // ── Page Inscription ─────────────────────────────────────────────────────
    function renderSignup() {
        const form = $('#signupForm');
        if (!form) return;

        const select = $('#eventSelect');

        function fillEvents() {
            const today = new Date().toISOString().slice(0, 10);
            const upcoming = events.filter((ev) => ev.date >= today);
            const open = upcoming.filter((ev) => Number(ev.placesReservees) < Number(ev.capacite));
            const full = upcoming.filter((ev) => Number(ev.placesReservees) >= Number(ev.capacite));
            const keep = select.value;
            select.innerHTML = '<option value="">Sélectionnez un événement</option>' +
                open.map((ev) => `<option value="${escapeHTML(String(ev.id))}">${escapeHTML(ev.titre)} — ${formatDate(ev.date)} (${ev.placesReservees}/${ev.capacite} places)</option>`).join('') +
                (full.length
                    ? `<optgroup label="Complets — rejoindre la liste d’attente">${full.map((ev) =>
                        `<option value="${escapeHTML(String(ev.id))}">${escapeHTML(ev.titre)} — ${formatDate(ev.date)} (complet · ${waitingFor(ev.id)} en attente)</option>`).join('')}</optgroup>`
                    : '');
            const chosen = keep || new URLSearchParams(location.search).get('event');
            if (chosen) select.value = chosen;
            updateWaitlistMode();
        }

        const isFullEvent = (id) => {
            const ev = events.find((item) => String(item.id) === String(id));
            return !!ev && Number(ev.placesReservees) >= Number(ev.capacite);
        };

        // Bascule l'interface en « liste d'attente » quand l'événement choisi est complet
        function updateWaitlistMode() {
            const full = select.value !== '' && isFullEvent(select.value);
            const notice = $('#waitlistNotice');
            if (notice) {
                notice.hidden = !full;
                if (full) {
                    const n = waitingFor(select.value);
                    $('#waitlistNoticeText').textContent = n
                        ? `${n} personne${n > 1 ? 's sont' : ' est'} déjà en attente. Vous serez n°${n + 1} : si une place se libère, vous serez inscrit(e) automatiquement, dans l’ordre d’arrivée.`
                        : 'Vous serez le premier sur la liste : si une place se libère, vous serez inscrit(e) automatiquement.';
                }
            }
            const submit = $('#signupSubmit');
            submit.classList.toggle('btn-waitlist', full);
            submit.innerHTML = full ? 'Rejoindre la liste d’attente&nbsp; &rarr;' : 'Confirmer mon inscription&nbsp; &rarr;';
        }
        select.addEventListener('change', updateWaitlistMode);

        fillEvents();
        attachLiveValidation();

        // Bloquer les chiffres dans prénom/nom et les lettres dans téléphone
        ['#firstName', '#lastName'].forEach((sel) => {
            $(sel)?.addEventListener('keypress', (e) => {
                // Autorise : lettres (y compris accentuées), espace, tiret, apostrophe
                if (!/[a-zA-ZÀ-ÖØ-öø-ÿ'\- ]/.test(e.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key)) {
                    e.preventDefault();
                }
            });
            // Nettoyage au paste : supprime les chiffres et caractères spéciaux
            $(sel)?.addEventListener('paste', (e) => {
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text');
                const clean = text.replace(/[^a-zA-ZÀ-ÖØ-öø-ÿ'\- ]/g, '');
                document.execCommand('insertText', false, clean);
            });
        });

        $('#phone')?.addEventListener('keypress', (e) => {
            // Autorise : chiffres et + en début
            const val = $('#phone').value;
            const isPlus = e.key === '+' && val.length === 0;
            if (!/[0-9]/.test(e.key) && !isPlus && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key)) {
                e.preventDefault();
            }
        });
        $('#phone')?.addEventListener('paste', (e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text');
            const clean = text.replace(/[^0-9+]/g, '').replace(/(?!^\+)\+/g, '');
            document.execCommand('insertText', false, clean);
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            clearAllErrors(form);

            if (!validateSignupForm()) {
                showBanner('Veuillez corriger les erreurs ci-dessous avant de soumettre.');
                return;
            }

            const submit = $('#signupSubmit');
            submit.disabled = true;
            submit.textContent = 'Envoi en cours…';

            try {
                const payload = {
                    prenom:       $('#firstName').value.trim(),
                    nom:          $('#lastName').value.trim(),
                    email:        $('#email').value.trim(),
                    telephone:    $('#phone').value.trim(),
                    id_evenement: select.value,
                };
                // Événement complet → liste d'attente ; sinon inscription classique
                const toWaitlist = isFullEvent(select.value);
                const result = await request(toWaitlist ? 'waitlist' : 'registrations', {
                    method: 'POST',
                    body: JSON.stringify(payload)
                });

                [events, registrations] = await Promise.all([request('events'), request('registrations')]);
                await loadExtras();

                const success = $('#signupSuccess');
                const firstName = $('#firstName').value.trim();
                success.textContent = toWaitlist
                    ? `⏳ Merci ${firstName} ! Vous êtes n°${result.position} sur la liste d’attente. Vous serez inscrit(e) automatiquement dès qu’une place se libère.`
                    : `✓ Merci ${firstName} ! Votre inscription est bien enregistrée.`;
                success.classList.add('show');

                form.reset();
                clearAllErrors(form);
                fillEvents();
            } catch (err) {
                showBanner(err.message);
            } finally {
                submit.disabled = false;
                updateWaitlistMode();
            }
        });
    }

    // ── Démarrage ────────────────────────────────────────────────────────────
    async function start() {
        initCommonUI();
        try {
            [events, registrations] = await Promise.all([request('events'), request('registrations')]);
            await loadExtras();
            renderEvents();
            renderSignup();
            initDetailFavorite();
        } catch (err) {
            const grid = $('#eventGrid');
            if (grid) grid.innerHTML = `<div class="empty">${escapeHTML(err.message)}</div>`;
        }
    }

    start();
})();
