<header class="pagehero">
 <div class="shell">
  <div class="crumb">
   <a href="index.php?page=home">
    Accueil
   </a>
   / &Eacute;v&eacute;nement / Inscription
  </div>
  <div class="pagehead">
   <div class="headtitle">
    <span class="headicon">
     ＋
    </span>
    <span>
     <h1>
      Inscription
     </h1>
     <p>
      R&eacute;servez votre place &agrave; l&rsquo;&eacute;v&eacute;nement de votre choix.
     </p>
    </span>
   </div>
  </div>
 </div>
</header>
<main class="shell signup-layout">
 <div class="signup-photo">
  <div class="photo-caption">
   <span class="eyebrow" style="color:#b4f0d1">
    VITACALC &middot; &Agrave; VIVRE ENSEMBLE
   </span>
   <h2>
    Un moment qui fait du bien.
   </h2>
   <p>
    Choisissez votre activit&eacute; et rejoignez notre communaut&eacute;.
   </p>
  </div>
 </div>
 <form class="formcard" id="signupForm">
  <h2>
   R&eacute;server ma place
  </h2>
  <p>
   Remplissez les informations ci-dessous pour finaliser votre inscription.
  </p>
  <div id="signupErrorBanner" class="form-error-banner span2"></div>
  <div class="formgrid">
   <div class="field" id="field-prenom">
    <label for="firstName">PR&Eacute;NOM *</label>
    <input id="firstName" maxlength="60" name="prenom" placeholder="Votre pr&eacute;nom" autocomplete="given-name"/>
    <span class="field-error" id="err-prenom"></span>
   </div>
   <div class="field" id="field-nom">
    <label for="lastName">NOM *</label>
    <input id="lastName" maxlength="60" name="nom" placeholder="Votre nom" autocomplete="family-name"/>
    <span class="field-error" id="err-nom"></span>
   </div>
   <div class="field span2" id="field-email">
    <label for="email">ADRESSE E-MAIL *</label>
    <input id="email" name="email" placeholder="vous@exemple.com" type="email" autocomplete="email"/>
    <span class="field-error" id="err-email"></span>
   </div>
   <div class="field span2" id="field-telephone">
    <label for="phone">T&Eacute;L&Eacute;PHONE *</label>
    <input id="phone" name="telephone" placeholder="+216 &hellip;" type="tel" autocomplete="tel"/>
    <span class="field-error" id="err-telephone"></span>
   </div>
   <div class="field span2" id="field-evenement">
    <label for="eventSelect">&Eacute;V&Eacute;NEMENT CHOISI *</label>
    <select id="eventSelect" name="eventId">
     <option value="">S&eacute;lectionnez un &eacute;v&eacute;nement</option>
    </select>
    <span class="field-error" id="err-evenement"></span>
   </div>
   <div class="waitlist-notice span2" id="waitlistNotice" role="status" hidden>
    <span class="waitlist-notice-ico">⏳</span>
    <span>
     <b>Cet événement est complet.</b>
     <small id="waitlistNoticeText">Inscrivez-vous sur la liste d’attente : si une place se libère, vous serez inscrit(e) automatiquement, dans l’ordre d’arrivée.</small>
    </span>
   </div>
   <label class="consent span2" id="field-consent">
    <input id="consentCheck" type="checkbox"/>
    J&rsquo;accepte d&rsquo;&ecirc;tre contact&eacute;(e) au sujet de cette inscription et confirme l&rsquo;exactitude des informations fournies.
   </label>
   <div class="span2">
    <button class="btn" type="submit" id="signupSubmit">
     Confirmer mon inscription&nbsp; &rarr;
    </button>
   </div>
  </div>
  <div class="success" id="signupSuccess" role="status">
  </div>
 </form>
</main>
