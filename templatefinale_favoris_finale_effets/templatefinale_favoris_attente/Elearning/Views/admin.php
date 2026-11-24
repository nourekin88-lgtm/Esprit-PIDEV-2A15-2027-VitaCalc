<section class="admin-content">
 <div class="view active" id="view-dashboard">
  <div class="welcome">
   <div>
    <div class="overline">
     MARDI &middot; ESPACE VITACALC
    </div>
    <h1>
     Bonjour, Admin
     <span>
      👋
     </span>
    </h1>
    <p>
     Voici le r&eacute;sum&eacute; de votre activit&eacute; &eacute;v&eacute;nementielle.
    </p>
   </div>
   <button class="primary" data-new-event="">
    ＋ Cr&eacute;er un &eacute;v&eacute;nement
   </button>
  </div>
  <div class="metric-grid" id="metrics">
  </div>
  <div class="dash-grid">
   <section class="panel">
    <div class="panel-heading">
     <div>
      <h2>
       &Eacute;v&eacute;nements &agrave; venir
      </h2>
      <p>
       Vos prochains rendez-vous programm&eacute;s
      </p>
     </div>
     <button class="link-button" data-goto="events">
      Voir tout &rarr;
     </button>
    </div>
    <div id="upcomingList">
    </div>
   </section>
   <section class="panel quick-panel">
    <div class="panel-heading">
     <div>
      <h2>
       Actions rapides
      </h2>
      <p>
       G&eacute;rez votre espace en un clic
      </p>
     </div>
    </div>
    <button class="quick-action" data-new-event="">
     <span class="quick-icon">
      ＋
     </span>
     <span>
      <b>
       Cr&eacute;er un &eacute;v&eacute;nement
      </b>
      <small>
       Ajouter une activit&eacute; au programme
      </small>
     </span>
     <strong>
      &rarr;
     </strong>
    </button>
    <button class="quick-action" data-goto="registrations">
     <span class="quick-icon blue">
      ♧
     </span>
     <span>
      <b>
       Voir les inscriptions
      </b>
      <small>
       Consulter la liste des participants
      </small>
     </span>
     <strong>
      &rarr;
     </strong>
    </button>
    <div class="tip">
     <b>
      ✦ Astuce
     </b>
     <p>
      Les &eacute;v&eacute;nements complets ne sont plus propos&eacute;s dans le formulaire d'inscription.
     </p>
    </div>
   </section>
  </div>
  <section class="panel recent-panel">
   <div class="panel-heading">
    <div>
     <h2>
      Derni&egrave;res inscriptions
     </h2>
     <p>
      Les participants les plus r&eacute;cents
     </p>
    </div>
    <button class="link-button" data-goto="registrations">
     Toutes les inscriptions &rarr;
    </button>
   </div>
   <div id="recentRegs">
   </div>
  </section>
 </div>
 <div class="view" id="view-events">
  <div class="welcome">
   <div>
    <div class="overline">
     GESTION DU PROGRAMME
    </div>
    <h1>
     &Eacute;v&eacute;nements
    </h1>
    <p>
     Cr&eacute;ez, modifiez et g&eacute;rez les &eacute;v&eacute;nements VitaCalc.
    </p>
   </div>
   <button class="primary" data-new-event="">
    ＋ Nouvel &eacute;v&eacute;nement
   </button>
  </div>
  <div class="table-tools">
   <label class="admin-search">
    &telrec;
    <input id="eventQuery" placeholder="Rechercher un &eacute;v&eacute;nement..."/>
   </label>
   <select id="eventCategory">
    <option value="all">
     Toutes les cat&eacute;gories
    </option>
    <option>
     Bien-&ecirc;tre
    </option>
    <option>
     Nutrition
    </option>
    <option>
     Sport
    </option>
    <option>
     Cuisine
    </option>
    <option>
     D&eacute;gustation
    </option>
   </select>
  </div>
  <section class="panel table-panel">
   <div class="table-wrap">
    <table>
     <thead>
      <tr>
       <th>
        &Eacute;V&Eacute;NEMENT
       </th>
       <th>
        DATE &amp; HEURE
       </th>
       <th>
        CAT&Eacute;GORIE
       </th>
       <th>
        INSCRITS
       </th>
       <th>
        STATUT
       </th>
       <th>
       </th>
      </tr>
     </thead>
     <tbody id="eventRows">
     </tbody>
    </table>
   </div>
  </section>
 </div>
 <div class="view" id="view-registrations">
  <div class="welcome">
   <div>
    <div class="overline">
     PARTICIPANTS
    </div>
    <h1>
     Inscriptions
    </h1>
    <p>
     Consultez les demandes re&ccedil;ues pour vos &eacute;v&eacute;nements.
    </p>
   </div>
   <button class="export" id="exportCsv">
    ⇩ Exporter CSV
   </button>
  </div>
  <div class="table-tools">
   <label class="admin-search">
    &telrec;
    <input id="regQuery" placeholder="Nom, e-mail ou &eacute;v&eacute;nement..."/>
   </label>
   <select id="regEvent">
    <option value="all">
     Tous les &eacute;v&eacute;nements
    </option>
   </select>
  </div>
  <section class="panel table-panel">
   <div class="table-wrap">
    <table>
     <thead>
      <tr>
       <th>
        PARTICIPANT
       </th>
       <th>
        &Eacute;V&Eacute;NEMENT
       </th>
       <th>
        DATE D'INSCRIPTION
       </th>
       <th>
        CONTACT
       </th>
       <th>
        STATUT
       </th>
       <th>
        ACTIONS
       </th>
      </tr>
     </thead>
     <tbody id="regRows">
     </tbody>
    </table>
   </div>
  </section>
 </div>
 <div class="view" id="view-waitlist">
  <div class="welcome">
   <div>
    <div class="overline">
     &Eacute;V&Eacute;NEMENTS COMPLETS
    </div>
    <h1>
     Liste d'attente
    </h1>
    <p>
     Les personnes sont inscrites automatiquement, dans l'ordre d'arriv&eacute;e, d&egrave;s qu'une place se lib&egrave;re.
    </p>
   </div>
  </div>
  <div class="table-tools">
   <label class="admin-search">
    &telrec;
    <input id="waitQuery" placeholder="Nom, e-mail ou &eacute;v&eacute;nement..."/>
   </label>
   <select id="waitEvent">
    <option value="all">
     Tous les &eacute;v&eacute;nements
    </option>
   </select>
   <select id="waitStatus">
    <option value="en_attente">
     En attente
    </option>
    <option value="promue">
     D&eacute;j&agrave; inscrits
    </option>
    <option value="all">
     Tous les statuts
    </option>
   </select>
  </div>
  <section class="panel table-panel">
   <div class="table-wrap">
    <table>
     <thead>
      <tr>
       <th>
        RANG
       </th>
       <th>
        PARTICIPANT
       </th>
       <th>
        &Eacute;V&Eacute;NEMENT
       </th>
       <th>
        DEMAND&Eacute; LE
       </th>
       <th>
        CONTACT
       </th>
       <th>
        STATUT
       </th>
       <th>
        ACTIONS
       </th>
      </tr>
     </thead>
     <tbody id="waitRows">
     </tbody>
    </table>
   </div>
  </section>
 </div>
</section>
<div class="shade" id="eventModal">
 <form class="admin-modal" id="adminEventForm">
  <header>
   <h2 id="modalHeading">
    Cr&eacute;er un &eacute;v&eacute;nement
   </h2>
   <button class="xclose" data-modal-close="" type="button">
    &times;
   </button>
  </header>
  <div class="modal-error-banner" id="modalErrorBanner"></div>
  <div class="modal-fields">
   <input id="aId" type="hidden"/>

   <div class="admin-field wide">
    <label for="aTitle">TITRE DE L'&Eacute;V&Eacute;NEMENT *</label>
    <input id="aTitle" minlength="3" maxlength="150" placeholder="Ex. : Yoga au lever du soleil"/>
    <span class="admin-field-error" id="err-titre"></span>
   </div>

   <div class="admin-field">
    <label for="aCategory">CAT&Eacute;GORIE</label>
    <select id="aCategory">
     <option>Bien-&ecirc;tre</option>
     <option>Nutrition</option>
     <option>Sport</option>
     <option>Cuisine</option>
     <option>D&eacute;gustation</option>
    </select>
   </div>

   <div class="admin-field">
    <label for="aDate">DATE *</label>
    <input id="aDate" type="date"/>
    <span class="admin-field-error" id="err-date"></span>
   </div>

   <div class="admin-field">
    <label for="aTime">HEURE *</label>
    <input id="aTime" type="time" value="10:00"/>
    <span class="admin-field-error" id="err-heure"></span>
   </div>

   <div class="admin-field">
    <label for="aPlace">LIEU *</label>
    <input id="aPlace" placeholder="Adresse / salle exacte" maxlength="200"/>
    <span class="admin-field-error" id="err-lieu"></span>
   </div>

   <div class="admin-field">
    <label for="aZone">ZONE POUR LA CARTE</label>
    <select id="aZone">
     <option>Tunis</option>
     <option>Ariana</option>
     <option>La Soukra</option>
     <option>La Marsa</option>
     <option>Ben Arous</option>
    </select>
   </div>

   <div class="admin-field">
    <label for="aCapacity">CAPACIT&Eacute; *</label>
    <input id="aCapacity" type="number" min="1" max="10000" value="20"/>
    <span class="admin-field-error" id="err-capacite"></span>
   </div>

   <div class="admin-field">
    <label for="aPrice">TARIF (DT)</label>
    <input id="aPrice" type="number" min="0" max="9999.99" step="0.5" value="0"/>
    <span class="admin-field-error" id="err-prix"></span>
   </div>

   <div class="admin-field">
    <label for="aFormat">FORMAT</label>
    <select id="aFormat">
     <option>Pr&eacute;sentiel</option>
     <option>En ligne</option>
    </select>
   </div>

   <div class="admin-field wide">
    <label for="aLink">LIEN DE LA PAGE OFFICIELLE (facultatif)</label>
    <input id="aLink" type="url" maxlength="500" placeholder="https://www.exemple.com/mon-evenement"/>
   </div>

   <div class="admin-field wide" id="image-picker-field">
    <span>IMAGE DE L'&Eacute;V&Eacute;NEMENT</span>
    <div class="img-uploader">
     <div class="img-dropzone" id="aImageDropzone">
      <input id="aImageFile" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="img-file-input"/>
      <div class="img-drop-label">
       <span class="img-drop-icon">&#128247;</span>
       <b>Glissez une image ici</b>
       <span>ou <u>cliquez pour parcourir</u></span>
       <small>JPG, PNG, WEBP &mdash; max&nbsp;5&nbsp;Mo</small>
      </div>
     </div>
     <div class="img-upload-preview" id="aImagePreview">
      <img id="aImagePreviewImg" src="" alt=""/>
      <div class="img-preview-info">
       <span id="aImagePreviewName"></span>
       <button class="img-clear-btn" id="aImageClear" type="button">&times; Changer</button>
      </div>
     </div>
     <div class="img-upload-status" id="aImageStatus"></div>
     <input id="aImage" type="hidden"/>
    </div>
   </div>

   <div class="admin-field wide">
    <label for="aDesc">DESCRIPTION</label>
    <textarea id="aDesc" placeholder="D&eacute;crivez l'activit&eacute;&hellip;" rows="3" maxlength="1000"></textarea>
    <div style="display:flex;justify-content:space-between;margin-top:3px">
     <span class="admin-field-error" id="err-desc"></span>
     <span id="aDescCounter" style="font-size:9px;color:#9aaa9f;margin-left:auto">0/1000</span>
    </div>
   </div>

  </div>
  <footer>
   <button class="export" data-modal-close="" type="button">
    Annuler
   </button>
   <button class="primary" type="submit">
    Enregistrer
   </button>
  </footer>
 </form>
</div>
<div class="notice" id="adminNotice">
</div>
