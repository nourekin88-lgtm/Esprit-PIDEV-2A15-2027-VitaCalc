<header class="pagehero">
 <div class="shell">
  <div class="crumb">
   <a href="index.php?page=home">
    Accueil
   </a>
   / &Eacute;v&eacute;nement / Events
  </div>
  <div class="pagehead">
   <div class="headtitle">
    <span class="headicon">
     ✿
    </span>
    <span>
     <h1>
      Events
     </h1>
     <p>
      D&eacute;couvrez les activit&eacute;s propos&eacute;es par VitaCalc et r&eacute;servez votre place.
     </p>
    </span>
   </div>
   <a class="btn" href="index.php?page=signup">
    ＋ &nbsp;S&rsquo;inscrire &agrave; un &eacute;v&eacute;nement
   </a>
  </div>
  <div class="stats" id="eventStats">
  </div>
 </div>
</header>
<main class="shell">
 <section class="map-section" aria-labelledby="mapHeading">
  <div class="map-intro">
   <span class="eyebrow">ÉVÉNEMENTS PRÈS DE CHEZ VOUS</span>
   <h2 id="mapHeading">Explorez Tunis sur la carte</h2>
   <p>Choisissez une zone pour voir les événements disponibles et leurs informations.</p>
  </div>
  <div class="map-zone-filters" id="mapZoneFilters" aria-label="Filtrer par zone"></div>
  <div class="map-layout">
   <div id="eventMap" class="event-map" role="application" aria-label="Carte interactive des événements à Tunis"></div>
   <aside class="map-sidepanel">
    <h3>Zones avec événements</h3>
    <p id="mapHelp">Cliquez sur un repère ou une zone pour afficher les activités.</p>
    <div id="mapZoneList" class="map-zone-list"></div>
    <div class="map-note">La carte regroupe les événements par zone. Les événements en ligne sont listés séparément.</div>
   </aside>
  </div>
  <div class="map-attribution-note">Carte © OpenStreetMap contributors</div>
 </section>
 <section class="toolbar">
  <div class="toolrow">
   <div class="pills" id="categoryPills">
   </div>
   <div class="toolrow">
    <label class="search">
     <input id="eventSearch" placeholder="Rechercher un &eacute;v&eacute;nement&hellip;" type="search"/>
    </label>
    <button class="btn-outline" id="filtersButton">
     ☷ &nbsp;Filtres
    </button>
   </div>
  </div>
  <div class="filterpanel" id="filterPanel">
   <div class="field">
    <label>
     FORMAT
    </label>
    <select id="modeFilter">
     <option value="all">
      Tous
     </option>
     <option value="presentiel">
      Pr&eacute;sentiel
     </option>
     <option value="online">
      En ligne
     </option>
    </select>
   </div>
   <div class="field">
    <label>
     PLACES
    </label>
    <select id="availabilityFilter">
     <option value="all">
      Toutes
     </option>
     <option value="available">
      Places disponibles
     </option>
     <option value="full">
      Complets
     </option>
    </select>
   </div>
   <div class="field">
    <label>
     PRIX
    </label>
    <select id="priceFilter">
     <option value="all">
      Tous les tarifs
     </option>
     <option value="free">
      Gratuit
     </option>
     <option value="paid">
      Payant
     </option>
    </select>
   </div>
   <div class="field">
    <label>
     TRIER
    </label>
    <select id="sortFilter">
     <option value="date">
      Date prochaine
     </option>
     <option value="price">
      Prix croissant
     </option>
     <option value="title">
      Titre A-Z
     </option>
    </select>
   </div>
  </div>
 </section>
 <div class="resultcount" id="resultCount">
 </div>
 <section class="eventgrid" id="eventGrid">
 </section>
</main>
