(() => {
    'use strict';

    const mapElement = document.getElementById('eventMap');
    if (!mapElement) return;

    const escapeHTML = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[character]);

    const dateLabel = (value) => new Date(`${value}T12:00:00`).toLocaleDateString('fr-FR', {
        weekday: 'short', day: '2-digit', month: 'short', year: 'numeric'
    });

    const zoneOrder = ['Tunis', 'Ariana', 'La Soukra', 'La Marsa', 'Ben Arous'];
    const zoneButtons = document.getElementById('mapZoneFilters');
    const zoneList = document.getElementById('mapZoneList');
    const mapHelp = document.getElementById('mapHelp');

    if (!window.L) {
        mapHelp.textContent = 'La carte nécessite une connexion Internet pour charger OpenStreetMap.';
        mapElement.innerHTML = '<div class="map-fallback">La carte interactive n’a pas pu être chargée. Vérifiez votre connexion Internet.</div>';
        return;
    }

    const map = L.map(mapElement, { scrollWheelZoom: false }).setView([36.818, 10.235], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
    }).addTo(map);

    const markers = new Map();

    function popupContent(zone, zoneEvents) {
        const cards = zoneEvents.slice().sort((a, b) => `${a.date} ${a.heure}`.localeCompare(`${b.date} ${b.heure}`)).map((event) => {
            const full = Number(event.placesReservees) >= Number(event.capacite);
            const remaining = Math.max(0, Number(event.capacite) - Number(event.placesReservees));
            const detailLink = `index.php?page=event-detail&id=${encodeURIComponent(event.id)}`;
            const signupLink = `index.php?page=signup&event=${encodeURIComponent(event.id)}`;
            return `<article class="map-popup-event">
                <div class="map-popup-category">${escapeHTML(event.categorie)}</div>
                <h4>${escapeHTML(event.titre)}</h4>
                <div class="map-popup-meta">◷ ${dateLabel(event.date)} · ${escapeHTML(event.heure)}</div>
                <div class="map-popup-meta">⌖ ${escapeHTML(event.lieu)}</div>
                <div class="map-popup-bottom"><span>${Number(event.prix) ? `${Number(event.prix)} DT` : 'Gratuit'} · ${full ? 'Complet' : `${remaining} place(s)`}</span>
                    <div class="map-popup-links"><a href="${detailLink}">Voir la fiche</a>${full ? '' : `<a class="map-popup-book" href="${signupLink}">Réserver</a>`}</div>
                </div>
            </article>`;
        }).join('');

        return `<div class="map-popup"><div class="map-popup-heading"><span class="map-popup-pin">⌖</span><div><b>${escapeHTML(zone)}</b><small>${zoneEvents.length} événement${zoneEvents.length === 1 ? '' : 's'} disponible${zoneEvents.length === 1 ? '' : 's'}</small></div></div>${cards}</div>`;
    }

    function focusZone(zone) {
        const marker = markers.get(zone);
        if (!marker) return;
        map.flyTo(marker.getLatLng(), Math.max(map.getZoom(), 12), { duration: 0.6 });
        marker.openPopup();
        document.querySelectorAll('[data-map-zone]').forEach((button) => button.classList.toggle('active', button.dataset.mapZone === zone));
    }

    function makeZoneButton(zone, count, className = '') {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `map-zone-button ${className}`.trim();
        button.dataset.mapZone = zone;
        button.innerHTML = `<span>${escapeHTML(zone)}</span><b>${count}</b>`;
        button.addEventListener('click', () => focusZone(zone));
        return button;
    }

    async function loadMapEvents() {
        try {
            const response = await fetch('api.php?resource=events', { cache: 'no-store' });
            if (!response.ok) throw new Error('Impossible de charger les événements.');
            const events = await response.json();
            markers.forEach((marker) => map.removeLayer(marker));
            markers.clear();
            const today = new Date().toISOString().slice(0, 10);
            const physicalEvents = events.filter((event) => event.date >= today
                && event.format !== 'En ligne'
                && event.zone !== 'En ligne'
                && Number.isFinite(Number(event.latitude))
                && Number.isFinite(Number(event.longitude)));
            const grouped = new Map();
            physicalEvents.forEach((event) => {
                const zone = event.zone || 'Tunis';
                if (!grouped.has(zone)) grouped.set(zone, []);
                grouped.get(zone).push(event);
            });

            zoneButtons.innerHTML = '';
            zoneList.innerHTML = '';
            if (!grouped.size) {
                mapHelp.textContent = 'Aucun événement en présentiel n’est programmé sur la carte pour le moment.';
                zoneButtons.append(makeZoneButton('Toutes les zones', 0, 'active'));
                zoneList.innerHTML = '<div class="map-empty">Aucune zone à afficher.</div>';
                return;
            }

            const visibleZones = [...grouped.keys()].sort((a, b) => {
                const aIndex = zoneOrder.indexOf(a);
                const bIndex = zoneOrder.indexOf(b);
                return (aIndex < 0 ? 99 : aIndex) - (bIndex < 0 ? 99 : bIndex);
            });
            zoneButtons.append(makeZoneButton('Toutes les zones', physicalEvents.length, 'active'));
            zoneButtons.lastElementChild.addEventListener('click', () => {
                document.querySelectorAll('[data-map-zone]').forEach((button) => button.classList.toggle('active', button.dataset.mapZone === 'Toutes les zones'));
                const allMarkers = [...markers.values()];
                if (allMarkers.length) map.fitBounds(L.featureGroup(allMarkers).getBounds().pad(0.2), { maxZoom: 11 });
                allMarkers.forEach((marker) => marker.closePopup());
            });

            visibleZones.forEach((zone) => {
                const zoneEvents = grouped.get(zone);
                const latitude = Number(zoneEvents[0].latitude);
                const longitude = Number(zoneEvents[0].longitude);
                const icon = L.divIcon({
                    className: 'vitacalc-map-marker-wrap',
                    html: `<span class="vitacalc-map-marker">${zoneEvents.length}</span>`,
                    iconSize: [38, 38],
                    iconAnchor: [19, 19]
                });
                const marker = L.marker([latitude, longitude], { icon }).addTo(map);
                marker.bindPopup(popupContent(zone, zoneEvents), { maxWidth: 360, minWidth: 290, closeButton: true });
                markers.set(zone, marker);

                const chip = makeZoneButton(zone, zoneEvents.length);
                zoneButtons.append(chip);
                zoneList.append(makeZoneButton(zone, zoneEvents.length));
            });

            mapHelp.textContent = `${physicalEvents.length} activité${physicalEvents.length === 1 ? '' : 's'} en présentiel dans ${visibleZones.length} zone${visibleZones.length === 1 ? '' : 's'}. Cliquez sur un repère pour voir les détails.`;
            if (markers.size > 1) {
                map.fitBounds(L.featureGroup([...markers.values()]).getBounds().pad(0.2), { maxZoom: 11 });
            } else {
                map.setView([...markers.values()][0].getLatLng(), 12);
            }
            window.setTimeout(() => map.invalidateSize(), 100);
        } catch (error) {
            mapHelp.textContent = error.message;
            zoneList.innerHTML = '<div class="map-empty">Les données de la carte sont temporairement indisponibles.</div>';
        }
    }

    loadMapEvents();
    window.addEventListener('focus', loadMapEvents);
})();
