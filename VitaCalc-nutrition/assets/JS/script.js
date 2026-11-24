const root = document.documentElement;
const menuToggle = document.querySelector('[data-menu-toggle]');
const menuClose = document.querySelector('[data-menu-close]');
const sidebar = document.querySelector('#navigation');
const mobile = window.matchMedia('(max-width: 850px)');

function setMenu(open, returnFocus = false) {
    root.classList.toggle('menu-open', open);
    menuToggle.setAttribute('aria-expanded', String(open));
    menuToggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    menuClose.hidden = !open;

    if (open) {
        sidebar.querySelector('a').focus({ preventScroll: true });
    } else if (returnFocus) {
        menuToggle.focus();
    }
}

root.classList.add('js');
menuToggle.addEventListener('click', () => setMenu(menuToggle.getAttribute('aria-expanded') !== 'true'));
menuClose.addEventListener('click', () => setMenu(false, true));
mobile.addEventListener('change', () => setMenu(false));
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && root.classList.contains('menu-open')) {
        setMenu(false, true);
    }
});
document.addEventListener('focusin', (event) => {
    if (root.classList.contains('menu-open') && !sidebar.contains(event.target) &&
        event.target !== menuToggle && event.target !== menuClose) {
        setMenu(false);
    }
});

document.querySelectorAll('[data-counter]').forEach((field) => {
    const counter = document.getElementById(field.dataset.counter);
    const number = new Intl.NumberFormat('fr-FR');
    const update = () => {
        const length = field.value.length;
        counter.textContent = number.format(length) + ' / ' + number.format(field.maxLength) + ' caractères';
    };
    field.addEventListener('input', update);
    update();
});

document.querySelectorAll('[data-flip-card]').forEach((card) => {
    const front = card.querySelector('[data-flip-front]');
    const back = card.querySelector('[data-flip-back]');
    const open = card.querySelector('[data-flip-open]');
    const close = card.querySelector('[data-flip-close]');
    let flipped = false;
    let mouseInside = false;
    let pinned = false;

    const fallback = card.querySelector('[data-flip-fallback]');
    if (fallback) fallback.hidden = true;
    open.hidden = false;
    back.hidden = false;
    back.inert = true;

    function turn(showBack, moveFocus = false) {
        if (showBack === flipped) return;
        flipped = showBack;
        card.classList.toggle('is-flipped', flipped);
        open.setAttribute('aria-expanded', String(flipped));
        const incomingFace = flipped ? back : front;
        const outgoingFace = flipped ? front : back;
        incomingFace.inert = false;
        if (moveFocus) (flipped ? close : open).focus({ preventScroll: true });
        outgoingFace.inert = true;
    }

    card.addEventListener('pointerenter', (event) => {
        if (event.pointerType !== 'mouse') return;
        mouseInside = true;
        if (!front.contains(document.activeElement)) turn(true);
    });
    card.addEventListener('pointerleave', (event) => {
        if (event.pointerType !== 'mouse') return;
        mouseInside = false;
        if (!pinned && !back.contains(document.activeElement)) turn(false);
    });
    card.addEventListener('focusout', (event) => {
        if (!mouseInside && !pinned && !card.contains(event.relatedTarget)) turn(false);
    });

    function showFront() {
        pinned = false;
        turn(false, true);
    }

    open.addEventListener('click', () => {
        pinned = true;
        turn(true, true);
    });
    close.addEventListener('click', showFront);
    card.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && flipped) {
            event.preventDefault();
            showFront();
        }
    });
});

const signup = document.querySelector('[data-signup]');
if (signup) {
    const steps = Array.from(signup.querySelectorAll('[data-signup-step]'));
    const progress = signup.querySelector('[data-signup-progress]');
    const back = signup.querySelector('[data-signup-back]');
    const next = signup.querySelector('[data-signup-next]');
    const save = signup.querySelector('[data-signup-save]');
    let current = 0;
    const last = steps.length - 1;

    function showStep(index, focus = true) {
        current = index;
        steps.forEach((step, i) => {
            step.hidden = i !== current;
            progress.children[i].classList.toggle('step-complete', i < current);
            if (i === current) {
                progress.children[i].setAttribute('aria-current', 'step');
            } else {
                progress.children[i].removeAttribute('aria-current');
            }
        });
        back.hidden = current === 0;
        next.hidden = current === last;
        save.hidden = current !== last;
        if (focus) steps[current].querySelector('legend').focus();
    }

    function invalidField(step) {
        for (const field of step.querySelectorAll('input, select')) {
            if (field.dataset.min !== undefined) {
                const value = Number(field.value.replace(',', '.'));
                const { min, max } = field.dataset;
                const outOfRange = field.value !== '' && (value < Number(min) || value > Number(max));
                field.setCustomValidity(outOfRange ? `La valeur doit être comprise entre ${min.replace('.', ',')} et ${max}.` : '');
            }
            if (!field.validity.valid) return field;
        }
        return null;
    }

    function advance() {
        const invalid = invalidField(steps[current]);
        if (invalid) {
            invalid.reportValidity();
            return;
        }
        showStep(current + 1);
    }

    next.addEventListener('click', advance);
    back.addEventListener('click', () => showStep(current - 1));
    signup.addEventListener('submit', (event) => {
        if (current < last) {
            event.preventDefault();
            advance();
            return;
        }
        for (let index = 0; index < steps.length; index++) {
            const invalid = invalidField(steps[index]);
            if (invalid) {
                event.preventDefault();
                showStep(index, false);
                invalid.reportValidity();
                return;
            }
        }
    });

    signup.noValidate = true;
    signup.classList.add('signup-enhanced');
    progress.hidden = false;
    const invalidStep = steps.findIndex((step) => step.querySelector('[aria-invalid="true"]'));
    showStep(Math.max(0, invalidStep), invalidStep >= 0);
}

const canvas = document.querySelector('.ambient-dots');
const dots = canvas?.getContext('2d');
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const reel = document.querySelector('[data-reel]');

if (reel) {
    const media = reel.querySelector('.hero-media');
    const video = reel.querySelector('video');
    const toggle = reel.querySelector('.reel-toggle');
    const playIcon = toggle.querySelector('[data-reel-play]');
    const pauseIcon = toggle.querySelector('[data-reel-pause]');
    let pausedByChoice = reduceMotion.matches;
    let inView = false;
    let scrollFrame = null;

    function updateControl() {
        const label = pausedByChoice ? 'Lire la vidéo' : 'Mettre la vidéo en pause';
        toggle.setAttribute('aria-label', label);
        toggle.title = label;
        playIcon.hidden = !pausedByChoice;
        pauseIcon.hidden = pausedByChoice;
    }

    function updatePlayback() {
        if (pausedByChoice || !inView || document.hidden) {
            video.pause();
            return;
        }
        video.play().catch((error) => {
            if (error.name !== 'AbortError') {
                pausedByChoice = true;
                updateControl();
            }
        });
    }

    function positionReel() {
        scrollFrame = null;
        const top = reel.getBoundingClientRect().top;
        const offset = reduceMotion.matches ? 0 : Math.min(30, Math.max(0, -top * 0.17));
        media.style.setProperty('--reel-shift', offset + 'px');
    }

    const observer = new IntersectionObserver(([entry]) => {
        inView = entry.intersectionRatio >= 0.15;
        updatePlayback();
    }, { threshold: 0.15 });
    observer.observe(reel);

    toggle.addEventListener('click', () => {
        pausedByChoice = !pausedByChoice;
        updateControl();
        updatePlayback();
    });
    window.addEventListener('scroll', () => {
        if (scrollFrame === null) scrollFrame = requestAnimationFrame(positionReel);
    }, { passive: true });
    document.addEventListener('visibilitychange', updatePlayback);
    reduceMotion.addEventListener('change', () => {
        pausedByChoice = reduceMotion.matches;
        updateControl();
        updatePlayback();
        positionReel();
    });

    updateControl();
    toggle.hidden = false;
    positionReel();
}

if (dots) {
    let width;
    let height;
    let particles;
    let frame;
    let previousTime;

    function random(min, max) {
        return min + Math.random() * (max - min);
    }

    function wrap(value, size) {
        const margin = 4;
        return value < -margin ? size + margin : value > size + margin ? -margin : value;
    }

    function draw(time) {
        const elapsed = previousTime === undefined ? 0 : Math.min((time - previousTime) / 1000, 0.04);
        previousTime = time;
        dots.clearRect(0, 0, width, height);
        for (const particle of particles) {
            if (Math.random() < elapsed * 0.8) particle.turn = random(-1.3, 1.3);
            particle.angle += particle.turn * elapsed;
            particle.x = wrap(particle.x + Math.cos(particle.angle) * particle.speed * elapsed, width);
            particle.y = wrap(particle.y + Math.sin(particle.angle) * particle.speed * elapsed, height);
            dots.beginPath();
            dots.fillStyle = '#174d33';
            dots.globalAlpha = particle.opacity;
            dots.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
            dots.fill();
        }
        if (!reduceMotion.matches) frame = requestAnimationFrame(draw);
    }

    function refreshDots() {
        cancelAnimationFrame(frame);
        previousTime = undefined;
        width = canvas.clientWidth;
        height = canvas.clientHeight;
        const scale = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(width * scale);
        canvas.height = Math.round(height * scale);
        dots.setTransform(scale, 0, 0, scale, 0, 0);
        const count = Math.min(18, Math.max(4, Math.round(width * height / 60000)));
        particles = Array.from({ length: count }, () => ({
            x: random(0, width), y: random(0, height), angle: random(0, Math.PI * 2),
            radius: random(1.2, 3.1), opacity: random(0.32, 0.62),
            speed: random(12, 30), turn: random(-1, 1),
        }));
        draw(performance.now());
    }

    window.addEventListener('resize', refreshDots);
    reduceMotion.addEventListener('change', refreshDots);
    refreshDots();
}
