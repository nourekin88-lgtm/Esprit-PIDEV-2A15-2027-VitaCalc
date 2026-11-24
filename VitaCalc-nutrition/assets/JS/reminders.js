(() => {
    const alerts = document.querySelector('[data-reminder-alerts]');
    const options = Object.entries(JSON.parse(alerts.dataset.reminderOptions));
    const baseUrl = new URL('../../', document.currentScript.src);
    const settingsKey = 'vitacalc:rappels:' + baseUrl.pathname;
    const deliveredKey = settingsKey + ':affichés';
    const form = document.querySelector('[data-reminder-form]');
    const rows = form ? [...form.querySelectorAll('[data-reminder]')] : [];
    const supportsNotifications = window.isSecureContext && 'Notification' in window;
    const validTime = /^(?:[01]\d|2[0-3]):[0-5]\d$/;
    const firedHere = new Set();
    let settings = loadSettings();
    let formEdited = false;
    let worker;

    function read(key) {
        try {
            return JSON.parse(localStorage.getItem(key));
        } catch {
            return null;
        }
    }

    function loadSettings() {
        const stored = read(settingsKey);
        return Object.fromEntries(options.map(([id, [, , defaultTime]]) => {
            const value = stored?.[id];
            return [id, {
                enabled: value?.enabled === true,
                time: typeof value?.time === 'string' && validTime.test(value.time) ? value.time : defaultTime,
            }];
        }));
    }

    function permissionGranted() {
        return supportsNotifications && Notification.permission === 'granted';
    }

    function notificationWorker() {
        if (!('serviceWorker' in navigator)) return Promise.resolve(null);
        worker ??= navigator.serviceWorker.register(new URL('rappels-sw.js', baseUrl))
            .then(() => navigator.serviceWorker.ready)
            .catch(() => {
                worker = undefined;
                return null;
            });
        return worker;
    }

    async function showSystemNotification(id, message) {
        const notificationOptions = {
            body: message,
            icon: new URL('assets/imges/logo-vitacalc.png', baseUrl).href,
            tag: settingsKey + ':' + id,
            lang: 'fr',
        };
        const registration = await notificationWorker();
        if (registration) {
            await registration.showNotification('VitaCalc', notificationOptions);
        } else {
            const notification = new Notification('VitaCalc', notificationOptions);
            notification.onclick = () => {
                window.focus();
                notification.close();
            };
        }
    }

    function displayReminder(id, message) {
        const card = document.createElement('div');
        const text = document.createElement('span');
        const close = document.createElement('button');
        card.className = 'notice';
        text.setAttribute('role', 'status');
        close.type = 'button';
        close.className = 'icon-button';
        close.setAttribute('aria-label', 'Fermer ce rappel');
        close.textContent = '×';
        close.addEventListener('click', () => card.remove());
        card.append(text, close);
        alerts.append(card);
        text.textContent = message;

        if (permissionGranted()) {
            showSystemNotification(id, message).catch(() => {
                // The in-app reminder stays available if the browser blocks the system alert.
            });
        }
    }

    function dueReminders(now) {
        const minute = now.getHours() * 60 + now.getMinutes();
        return options.filter(([id]) => {
            const { enabled, time } = settings[id];
            const [hour, minutes] = time.split(':').map(Number);
            const elapsed = minute - (hour * 60 + minutes);
            return enabled && elapsed >= 0 && elapsed <= 1;
        });
    }

    function reminderDay(now) {
        return [now.getFullYear(), now.getMonth() + 1, now.getDate()].join('-');
    }

    function deliverReminders() {
        if (document.hidden && !permissionGranted()) return;
        settings = loadSettings();
        const now = new Date();
        const day = reminderDay(now);
        const previous = read(deliveredKey);
        const delivered = new Set(previous?.day === day && Array.isArray(previous.ids) ? previous.ids : []);
        for (const [id, [, , , message]] of dueReminders(now)) {
            const occurrence = day + ':' + id;
            if (firedHere.has(occurrence)) continue;
            firedHere.add(occurrence);
            if (delivered.has(id)) continue;
            delivered.add(id);
            try {
                localStorage.setItem(deliveredKey, JSON.stringify({ day, ids: [...delivered] }));
            } catch {
                // Without persistent storage the daily marker can only last for this page.
            }
            displayReminder(id, message);
        }
    }

    function checkReminders() {
        if (document.hidden && !permissionGranted()) return;
        const now = new Date();
        const day = reminderDay(now);
        if (!dueReminders(now).some(([id]) => !firedHere.has(day + ':' + id))) return;
        if (navigator.locks) {
            navigator.locks.request(deliveredKey, deliverReminders).catch(deliverReminders);
        } else {
            deliverReminders();
        }
    }

    function loadForm() {
        for (const row of rows) {
            const value = settings[row.dataset.reminder];
            row.querySelector('input[type="time"]').value = value.time;
            row.querySelector('input[type="checkbox"]').checked = value.enabled;
        }
    }

    if (form) {
        const feedback = form.querySelector('[data-reminder-feedback]');
        const permission = form.querySelector('[data-reminder-permission]');
        const permissionStatus = form.querySelector('[data-reminder-permission-status]');

        function updatePermission() {
            const state = supportsNotifications ? Notification.permission : 'unsupported';
            permission.hidden = state !== 'default';
            permissionStatus.textContent = {
                granted: 'Notifications système autorisées.',
                denied: 'Notifications système bloquées. Les rappels s’affichent dans VitaCalc.',
                unsupported: 'Les rappels s’affichent dans VitaCalc sur ce navigateur.',
                default: '',
            }[state];
        }

        form.addEventListener('input', () => {
            formEdited = true;
            feedback.textContent = '';
        });
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const next = Object.fromEntries(rows.map((row) => [row.dataset.reminder, {
                time: row.querySelector('input[type="time"]').value,
                enabled: row.querySelector('input[type="checkbox"]').checked,
            }]));
            try {
                localStorage.setItem(settingsKey, JSON.stringify(next));
                settings = next;
                formEdited = false;
                feedback.classList.remove('is-error');
                feedback.textContent = 'Rappels enregistrés.';
                checkReminders();
            } catch {
                feedback.classList.add('is-error');
                feedback.textContent = 'Le navigateur empêche l’enregistrement des rappels.';
            }
        });
        permission.addEventListener('click', async () => {
            permission.disabled = true;
            try {
                if (await Notification.requestPermission() === 'granted') notificationWorker();
                updatePermission();
            } catch {
                permissionStatus.textContent = 'Autorisation indisponible. Les rappels restent visibles dans VitaCalc.';
            } finally {
                permission.disabled = false;
            }
        });
        window.addEventListener('focus', updatePermission);
        loadForm();
        updatePermission();
        form.hidden = false;
    }

    window.addEventListener('storage', (event) => {
        if (event.key === settingsKey || event.key === null) {
            settings = loadSettings();
            if (!formEdited) loadForm();
            checkReminders();
        }
    });
    document.addEventListener('visibilitychange', checkReminders);
    window.addEventListener('pageshow', () => {
        settings = loadSettings();
        if (!formEdited) loadForm();
        checkReminders();
    });
    setInterval(checkReminders, 5000);
    checkReminders();
})();
