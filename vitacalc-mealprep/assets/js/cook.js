/* VitaCalc - MODE CUISINE : plein écran, une grande étape à la fois, minuteurs intégrés */
(function () {
    'use strict';

    var root = document.getElementById('cook');
    if (!root) { return; }

    var slides   = Array.prototype.slice.call(root.querySelectorAll('.cook-slide'));
    var last     = slides.length - 1;
    var nbSteps  = parseInt(root.dataset.steps, 10) || 0;
    var pos      = 0;

    var bar      = document.getElementById('cookBar');
    var posLabel = document.getElementById('cookPos');
    var prevBtn  = document.getElementById('cookPrev');
    var nextBtn  = document.getElementById('cookNext');
    var pills    = document.getElementById('cookTimers');
    var sheet    = document.getElementById('cookSheet');
    var sheetBody = document.getElementById('cookSheetBody');
    var alarm    = document.getElementById('cookAlarm');
    var alarmTxt = document.getElementById('cookAlarmText');
    var fsBtn    = document.getElementById('cookFsBtn');

    var timers = {};          // index de diapositive -> { total, remaining, endAt, running, done, ack }
    var tickId = null;
    var alarmId = null;
    var audioCtx = null;
    var wakeLock = null;

    /* ---------------------------------------------------------------- */
    /*  Utilitaires                                                      */
    /* ---------------------------------------------------------------- */

    function fmt(sec) {
        sec = Math.max(0, sec);
        var h = Math.floor(sec / 3600), m = Math.floor((sec % 3600) / 60), s = sec % 60;
        var mm = (h ? String(m).padStart(2, '0') : String(m).padStart(2, '0')), ss = String(s).padStart(2, '0');
        return h ? h + ':' + mm + ':' + ss : mm + ':' + ss;
    }

    function safeStorage() { try { return window.sessionStorage; } catch (e) { return null; } }

    /* ---------------------------------------------------------------- */
    /*  Navigation                                                       */
    /* ---------------------------------------------------------------- */

    function label(i) {
        if (i === 0) { return 'Ingrédients'; }
        if (i === last) { return 'Terminé 🎉'; }
        return 'Étape ' + i + ' / ' + nbSteps;
    }

    function show(i, silent) {
        i = Math.max(0, Math.min(last, i));
        slides.forEach(function (s, k) {
            s.classList.toggle('is-active', k === i);
            s.classList.toggle('is-before', k < i);
            s.setAttribute('aria-hidden', k === i ? 'false' : 'true');
        });
        pos = i;
        bar.style.width = (last ? (i / last) * 100 : 100) + '%';
        posLabel.textContent = label(i);
        prevBtn.disabled = i === 0;
        nextBtn.disabled = i === last;
        if (!silent) {
            slides[i].focus({ preventScroll: true });
            var st = safeStorage();
            if (st) { try { st.setItem('vc_cook_' + location.search, String(i)); } catch (e) { /* ignoré */ } }
        }
        renderPills();
    }

    function next() { show(pos + 1); }
    function prev() { show(pos - 1); }

    prevBtn.addEventListener('click', prev);
    nextBtn.addEventListener('click', next);

    // Clavier : flèches, espace ; T = minuteur de l'étape
    document.addEventListener('keydown', function (e) {
        if (e.altKey || e.ctrlKey || e.metaKey) { return; }
        if (e.key === 'Escape') { closeSheet(); return; }
        if (!sheet.hidden || !alarm.hidden) { return; }
        var tag = (e.target && e.target.tagName) || '';
        if (e.key === 'ArrowRight' || (e.key === ' ' && tag !== 'BUTTON' && tag !== 'A' && tag !== 'LI')) {
            e.preventDefault(); next();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault(); prev();
        } else if (e.key === 't' || e.key === 'T') {
            if (timers[pos]) { toggleTimer(pos); }
        }
    });

    // Balayage tactile (téléphone / tablette dans la cuisine)
    var tx = null, ty = null;
    root.addEventListener('touchstart', function (e) {
        if (e.touches.length !== 1) { tx = null; return; }
        tx = e.touches[0].clientX; ty = e.touches[0].clientY;
    }, { passive: true });
    root.addEventListener('touchend', function (e) {
        if (tx === null || !e.changedTouches.length) { return; }
        var dx = e.changedTouches[0].clientX - tx, dy = e.changedTouches[0].clientY - ty;
        tx = null;
        if (Math.abs(dx) > 70 && Math.abs(dx) > Math.abs(dy) * 1.5 && sheet.hidden && alarm.hidden) {
            if (dx < 0) { next(); } else { prev(); }
        }
    }, { passive: true });

    /* ---------------------------------------------------------------- */
    /*  Portions : les quantités (liste + étapes) suivent le nombre choisi */
    /* ---------------------------------------------------------------- */

    var baseServings = parseInt(root.dataset.base, 10) || 1;
    var servings = baseServings;
    var servLabel = document.getElementById('cookPortions');

    function fmtQty(n) { return String(parseFloat(n.toFixed(2))).replace('.', ','); }
    function human(q, u) {
        if (u === 'g' && q >= 1000) { return fmtQty(q / 1000) + ' kg'; }
        if (u === 'ml' && q >= 1000) { return fmtQty(q / 1000) + ' l'; }
        return fmtQty(q) + ' ' + u;
    }
    function applyServings() {
        var f = servings / baseServings;
        Array.prototype.forEach.call(root.querySelectorAll('[data-q]'), function (el) {
            el.textContent = human(parseFloat(el.getAttribute('data-q')) * f, el.getAttribute('data-u'));
        });
        if (servLabel) { servLabel.textContent = servings; }
    }

    /* ---------------------------------------------------------------- */
    /*  Plein écran + écran toujours allumé                              */
    /* ---------------------------------------------------------------- */

    function isFullscreen() { return !!(document.fullscreenElement || document.webkitFullscreenElement); }

    function enterFullscreen() {
        var el = document.documentElement;
        var rq = el.requestFullscreen || el.webkitRequestFullscreen;
        if (rq && !isFullscreen()) {
            try { var p = rq.call(el); if (p && p.catch) { p.catch(function () {}); } } catch (e) { /* ignoré */ }
        }
    }

    function toggleFullscreen() {
        if (isFullscreen()) {
            var ex = document.exitFullscreen || document.webkitExitFullscreen;
            if (ex) { ex.call(document); }
        } else {
            enterFullscreen();
        }
    }

    function syncFsIcon() {
        var on = isFullscreen();
        fsBtn.innerHTML = '<i class="bi ' + (on ? 'bi-fullscreen-exit' : 'bi-fullscreen') + '"></i>';
        fsBtn.setAttribute('aria-label', on ? 'Quitter le plein écran' : 'Plein écran');
        fsBtn.title = on ? 'Quitter le plein écran' : 'Plein écran';
    }
    fsBtn.addEventListener('click', toggleFullscreen);
    document.addEventListener('fullscreenchange', syncFsIcon);
    document.addEventListener('webkitfullscreenchange', syncFsIcon);
    if (!(document.documentElement.requestFullscreen || document.documentElement.webkitRequestFullscreen)) {
        fsBtn.hidden = true; // ex. iPhone : pas d'API plein écran, la page remplit déjà l'écran
    }

    // Garde l'écran allumé pendant la cuisson (si le navigateur le permet)
    function keepAwake() {
        if (!('wakeLock' in navigator)) { return; }
        navigator.wakeLock.request('screen').then(function (l) { wakeLock = l; }).catch(function () {});
    }
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') { keepAwake(); tick(); }
    });
    keepAwake();

    /* ---------------------------------------------------------------- */
    /*  Ingrédients : liste cochable + fenêtre                           */
    /* ---------------------------------------------------------------- */

    function bindIngredientList(ul) {
        if (!ul) { return; }
        function toggle(li) {
            var on = li.classList.toggle('done');
            li.setAttribute('aria-checked', on ? 'true' : 'false');
        }
        ul.addEventListener('click', function (e) {
            var li = e.target.closest('li'); if (li) { toggle(li); }
        });
        ul.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                var li = e.target.closest('li');
                if (li) { e.preventDefault(); e.stopPropagation(); toggle(li); }
            }
        });
    }
    var mainList = document.getElementById('cookIngList');
    bindIngredientList(mainList);

    function openSheet() {
        sheetBody.innerHTML = '';
        if (mainList) {
            var clone = mainList.cloneNode(true);
            clone.removeAttribute('id');
            sheetBody.appendChild(clone);
            bindIngredientList(clone);
        } else {
            sheetBody.innerHTML = '<p class="cook-empty">Aucun ingrédient renseigné.</p>';
        }
        sheet.hidden = false;
        document.getElementById('cookSheetClose').focus();
    }
    function closeSheet() { if (!sheet.hidden) { sheet.hidden = true; } }
    document.getElementById('cookIngBtn').addEventListener('click', openSheet);
    document.getElementById('cookSheetClose').addEventListener('click', closeSheet);
    sheet.addEventListener('click', function (e) { if (e.target === sheet) { closeSheet(); } });

    /* ---------------------------------------------------------------- */
    /*  Minuteurs (un par étape ; plusieurs peuvent tourner en même temps) */
    /* ---------------------------------------------------------------- */

    slides.forEach(function (s, i) {
        var m = parseInt(s.dataset.minutes, 10);
        if (m > 0) { timers[i] = { total: m * 60, remaining: m * 60, endAt: null, running: false, done: false, ack: false }; }
    });

    function timerBox(i) { return slides[i].querySelector('.cook-timer'); }

    function renderTimer(i) {
        var t = timers[i], box = timerBox(i);
        if (!t || !box) { return; }
        box.querySelector('[data-role="display"]').textContent = fmt(t.remaining);
        box.classList.toggle('is-running', t.running);
        box.classList.toggle('is-done', t.done);
        var b = box.querySelector('[data-act="toggle"]');
        b.querySelector('i').className = 'bi ' + (t.running ? 'bi-pause-fill' : (t.done ? 'bi-arrow-counterclockwise' : 'bi-play-fill'));
        b.querySelector('span').textContent = t.running ? 'Pause' : (t.done ? 'Relancer' : (t.remaining < t.total ? 'Reprendre' : 'Démarrer'));
    }

    function renderPills() {
        pills.innerHTML = '';
        Object.keys(timers).forEach(function (k) {
            var i = parseInt(k, 10), t = timers[i];
            if (!(t.running || (t.done && !t.ack))) { return; }
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'cook-pill' + (t.done ? ' is-done' : '') + (i === pos ? ' is-here' : '');
            b.setAttribute('aria-label', 'Aller à l\'étape ' + i);
            b.innerHTML = '<i class="bi ' + (t.done ? 'bi-bell-fill' : 'bi-stopwatch') + '"></i> Étape ' + i + ' · <b>' + fmt(t.remaining) + '</b>';
            b.addEventListener('click', function () { show(i); });
            pills.appendChild(b);
        });
    }

    function unlockAudio() {
        try {
            var AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) { return; }
            if (!audioCtx) { audioCtx = new AC(); }
            if (audioCtx.state === 'suspended') { audioCtx.resume(); }
        } catch (e) { /* ignoré */ }
    }

    function beep() {
        if (navigator.vibrate) { try { navigator.vibrate([250, 120, 250, 120, 250]); } catch (e) { /* ignoré */ } }
        if (!audioCtx) { return; }
        var now = audioCtx.currentTime;
        [0, 0.28, 0.56].forEach(function (d) {
            var o = audioCtx.createOscillator(), g = audioCtx.createGain();
            o.type = 'sine'; o.frequency.value = 880;
            g.gain.setValueAtTime(0.0001, now + d);
            g.gain.exponentialRampToValueAtTime(0.35, now + d + 0.02);
            g.gain.exponentialRampToValueAtTime(0.0001, now + d + 0.22);
            o.connect(g); g.connect(audioCtx.destination);
            o.start(now + d); o.stop(now + d + 0.24);
        });
    }

    function ring() {
        var names = Object.keys(timers).filter(function (k) { return timers[k].done && !timers[k].ack; })
            .map(function (k) { return 'étape ' + k; });
        if (!names.length) { return; }
        alarmTxt.textContent = 'Minuteur terminé : ' + names.join(', ') + ' !';
        alarm.hidden = false;
        document.getElementById('cookAlarmOk').focus();
        beep();
        if (alarmId) { clearInterval(alarmId); }
        var n = 0;
        alarmId = setInterval(function () { if (++n > 15) { stopAlarm(false); } else { beep(); } }, 2200);
    }

    function stopAlarm(ack) {
        if (alarmId) { clearInterval(alarmId); alarmId = null; }
        alarm.hidden = true;
        if (ack) { Object.keys(timers).forEach(function (k) { if (timers[k].done) { timers[k].ack = true; } }); }
        renderPills();
    }
    document.getElementById('cookAlarmOk').addEventListener('click', function () { stopAlarm(true); });

    function tick() {
        var now = Date.now(), any = false, finished = false;
        Object.keys(timers).forEach(function (k) {
            var t = timers[k];
            if (!t.running) { return; }
            t.remaining = Math.max(0, Math.ceil((t.endAt - now) / 1000));
            if (t.remaining === 0) {
                t.running = false; t.done = true; t.ack = false; finished = true;
            } else { any = true; }
            renderTimer(parseInt(k, 10));
        });
        renderPills();
        if (finished) { ring(); }
        if (!any && tickId) { clearInterval(tickId); tickId = null; }
    }

    function ensureTick() { if (!tickId) { tickId = setInterval(tick, 250); } }

    function toggleTimer(i) {
        var t = timers[i]; if (!t) { return; }
        unlockAudio();
        if (t.running) {
            t.remaining = Math.max(0, Math.ceil((t.endAt - Date.now()) / 1000));
            t.running = false;
        } else {
            if (t.done || t.remaining <= 0) { t.remaining = t.total; t.done = false; t.ack = true; }
            t.endAt = Date.now() + t.remaining * 1000;
            t.running = true; t.done = false;
            ensureTick();
        }
        renderTimer(i); renderPills();
    }

    function adjustTimer(i, deltaSec) {
        var t = timers[i]; if (!t) { return; }
        if (t.done) { t.done = false; t.ack = true; t.remaining = 0; }
        if (t.running) {
            var rem = Math.max(0, Math.ceil((t.endAt - Date.now()) / 1000)) + deltaSec;
            if (rem < 5) { rem = 5; }
            t.endAt = Date.now() + rem * 1000;
            t.remaining = rem;
            t.total = Math.max(t.total, rem);
        } else {
            t.total = Math.max(60, t.total + deltaSec);
            t.remaining = t.total;
        }
        renderTimer(i); renderPills();
    }

    function resetTimer(i) {
        var t = timers[i]; if (!t) { return; }
        t.running = false; t.done = false; t.ack = true; t.remaining = t.total;
        renderTimer(i); renderPills();
        if (!Object.keys(timers).some(function (k) { return timers[k].done && !timers[k].ack; })) { stopAlarm(false); }
    }

    function addTimer(i) {
        if (!timers[i]) { timers[i] = { total: 300, remaining: 300, endAt: null, running: false, done: false, ack: false }; }
        var box = timerBox(i); box.hidden = false;
        var add = slides[i].querySelector('[data-act="add"]'); if (add) { add.hidden = true; }
        renderTimer(i);
    }

    // Boutons de la diapositive (délégation)
    root.addEventListener('click', function (e) {
        var b = e.target.closest('[data-act]');
        if (!b) { return; }
        var act = b.getAttribute('data-act');
        var slide = b.closest('.cook-slide');
        var i = slide ? slides.indexOf(slide) : pos;
        if (act === 'start') { enterFullscreen(); unlockAudio(); show(1); }
        else if (act === 'restart') { show(0); }
        else if (act === 'pplus') { if (servings < 40) { servings++; applyServings(); } }
        else if (act === 'pminus') { if (servings > 1) { servings--; applyServings(); } }
        else if (act === 'toggle') { toggleTimer(i); }
        else if (act === 'plus') { adjustTimer(i, 60); }
        else if (act === 'minus') { adjustTimer(i, -60); }
        else if (act === 'reset') { resetTimer(i); }
        else if (act === 'add') { addTimer(i); }
    });

    Object.keys(timers).forEach(function (k) { renderTimer(parseInt(k, 10)); });


    /* ---------------------------------------------------------------- */
    /*  Commande par la main : la caméra détecte un geste de balayage     */
    /*  (main de droite à gauche = suivant, de gauche à droite = précédent). */
    /*  Aucune bibliothèque : on compare les images successives (mouvement). */
    /*  La vidéo reste dans le navigateur, rien n'est envoyé ni enregistré.  */
    /* ---------------------------------------------------------------- */

    var camBtn   = document.getElementById('cookCamBtn');
    var camBox   = document.getElementById('cookCam');
    var camVideo = document.getElementById('cookCamVideo');
    var camMsg   = document.getElementById('cookCamMsg');
    var camFlash = document.getElementById('cookCamFlash');
    var camLevel = document.getElementById('cookCamLevel');
    var camStream = null, camTimer = null, camBusy = false;
    var CAM_KEY = 'vc_cook_cam';
    var W = 80, H = 60;
    var camCanvas = document.createElement('canvas');
    camCanvas.width = W; camCanvas.height = H;
    var camCtx = camCanvas.getContext('2d', { willReadFrequently: true });
    var prevGray = null, track = [], lastTrigger = 0;

    function camSupported() { return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && camCtx); }

    function camSetMsg(t) { camMsg.textContent = t; }

    function camFlashShow(icon) {
        camFlash.innerHTML = '<i class="bi ' + icon + '"></i>';
        camFlash.classList.remove('go'); void camFlash.offsetWidth; camFlash.classList.add('go');
    }

    function camStep() {
        if (!camStream || camVideo.readyState < 2) { return; }
        // image miroir (ce que l'on voit à l'écran) en 80x60, niveaux de gris
        camCtx.save(); camCtx.scale(-1, 1); camCtx.drawImage(camVideo, -W, 0, W, H); camCtx.restore();
        var d = camCtx.getImageData(0, 0, W, H).data, n = W * H, gray = new Uint8Array(n), i;
        for (i = 0; i < n; i++) { gray[i] = (d[i * 4] * 77 + d[i * 4 + 1] * 150 + d[i * 4 + 2] * 29) >> 8; }
        if (!prevGray) { prevGray = gray; return; }

        var mass = 0, sx = 0, x, y;
        for (y = 0; y < H; y++) {
            for (x = 0; x < W; x++) {
                i = y * W + x;
                if (Math.abs(gray[i] - prevGray[i]) > 28) { mass++; sx += x; }
            }
        }
        prevGray = gray;
        var ratio = mass / n, now = Date.now();
        camLevel.style.width = Math.min(100, ratio * 600) + '%';

        // trop de pixels qui bougent d'un coup = changement de lumière, pas une main
        if (ratio < 0.02 || ratio > 0.6) {
            if (track.length && now - track[track.length - 1].t > 250) { track = []; }
            return;
        }
        track.push({ t: now, x: sx / mass });
        track = track.filter(function (p) { return now - p.t < 800; });

        if (now - lastTrigger < 1400 || track.length < 4) { return; }

        var dx = track[track.length - 1].x - track[0].x, steps = 0, same = 0, k;
        for (k = 1; k < track.length; k++) {
            var s = track[k].x - track[k - 1].x;
            if (Math.abs(s) > 0.5) { steps++; if ((s > 0) === (dx > 0)) { same++; } }
        }
        if (Math.abs(dx) >= W * 0.28 && steps >= 3 && same / steps >= 0.7) {
            lastTrigger = now; track = [];
            if (!sheet.hidden || !alarm.hidden) { return; }
            if (dx < 0) { camFlashShow('bi-chevron-double-right'); next(); }
            else { camFlashShow('bi-chevron-double-left'); prev(); }
        }
    }

    function camStop(message) {
        if (camTimer) { clearInterval(camTimer); camTimer = null; }
        if (camStream) { camStream.getTracks().forEach(function (t) { t.stop(); }); camStream = null; }
        camVideo.srcObject = null;
        prevGray = null; track = [];
        camBox.hidden = true; root.classList.remove('cam-on');
        camBtn.classList.remove('is-on');
        camBtn.setAttribute('aria-pressed', 'false');
        camBtn.innerHTML = '<i class="bi bi-camera-video-off"></i>';
        if (message) { camBtn.title = message; }
    }

    function camStart() {
        if (camStream || camBusy) { return; }
        if (!camSupported()) { camBtn.hidden = true; return; }
        camBusy = true;
        camBox.hidden = false; root.classList.add('cam-on');
        camSetMsg('Autorisez la caméra pour changer d\'étape avec la main…');
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 320 }, height: { ideal: 240 } }, audio: false })
            .then(function (stream) {
                camBusy = false;
                camStream = stream;
                camVideo.srcObject = stream;
                var pl = camVideo.play(); if (pl && pl.catch) { pl.catch(function () {}); }
                camBtn.classList.add('is-on');
                camBtn.setAttribute('aria-pressed', 'true');
                camBtn.innerHTML = '<i class="bi bi-camera-video"></i>';
                camBtn.title = 'Désactiver la commande par la main';
                camSetMsg('✋ Main de droite à gauche = suivant · de gauche à droite = précédent');
                camTimer = setInterval(camStep, 80);
                try { window.localStorage.removeItem(CAM_KEY); } catch (e) { /* ignoré */ }
            })
            .catch(function (err) {
                camBusy = false;
                camStop('Caméra indisponible');
                var denied = err && (err.name === 'NotAllowedError' || err.name === 'SecurityError');
                camBtn.title = denied ? 'Caméra refusée : autorisez-la dans le navigateur puis touchez ici' : 'Aucune caméra trouvée';
            });
    }

    camBtn.addEventListener('click', function () {
        if (camStream) {
            camStop('Activer la commande par la main');
            try { window.localStorage.setItem(CAM_KEY, 'off'); } catch (e) { /* ignoré */ }
        } else { camStart(); }
    });
    window.addEventListener('pagehide', function () { camStop(); });

    // Ouvre la caméra dès l'arrivée en mode cuisine (sauf si on l'a désactivée la dernière fois)
    var camOff = false;
    try { camOff = window.localStorage.getItem(CAM_KEY) === 'off'; } catch (e) { camOff = false; }
    if (!camSupported()) { camBtn.hidden = true; }
    else if (camOff) { camBtn.innerHTML = '<i class="bi bi-camera-video-off"></i>'; }
    else { camStart(); }

    /* ---------------------------------------------------------------- */
    /*  Démarrage : reprend à l'étape où l'on était (rechargement)       */
    /* ---------------------------------------------------------------- */

    var startAt = 0, st = safeStorage();
    if (st) { try { startAt = parseInt(st.getItem('vc_cook_' + location.search), 10) || 0; } catch (e) { startAt = 0; } }
    show(startAt, true);
})();
