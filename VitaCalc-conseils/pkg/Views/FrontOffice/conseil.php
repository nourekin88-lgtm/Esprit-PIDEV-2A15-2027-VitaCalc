<?php
$pageTitle   = 'Conseils bien-être | VitaCalc';
$currentPage = 'conseils';
$categories  = $categories ?? [];
$recommandes = $recommandes ?? [];
$erreur      = $erreur ?? null;

// Personnalisation (fournies par ConseilController)
$selfUrl          = $selfUrl ?? '';
$q                = $q ?? '';
$recherche        = $recherche ?? null;
$cartes           = $cartes ?? [];
$conseilsPerso    = $conseilsPerso ?? [];
$resume           = $resume ?? [];
$resumeProfil     = $resumeProfil ?? [];
$questionnaire    = $questionnaire ?? null;
$objectifProfil   = $objectifProfil ?? null;
$ouvrirFormulaire = $ouvrirFormulaire ?? false;
$erreurFormulaire = $erreurFormulaire ?? false;
$h = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');

// Petites icônes des choix du questionnaire
$icones = [
    'prise-de-masse' => '💪', 'perte-de-poids' => '🔥', 'maintien' => '⚖️', 'endurance' => '🏃', 'aucun' => '🙂',
    'nutrition' => '🥗', 'sport' => '🏋️', 'sommeil' => '🌙', 'hydratation' => '💧', 'gestion-du-stress' => '🧘',
    'bon' => '😊', 'moyen' => '😐', 'mauvais' => '😫', 'faible' => '😌', 'eleve' => '😰',
];

// Les étapes du questionnaire : titre + questions [champ, intitulé, choix]
$etapes = [];
if (!$objectifProfil) {   // l'objectif n'est pas dans le profil : on le demande
    $etapes[] = [['objectif', 'Quel est votre objectif physique ?', MoteurConseils::OBJECTIFS]];
}
$etapes[] = [['aspect', 'Quel aspect souhaitez-vous améliorer en priorité ?', MoteurConseils::ASPECTS]];
$etapes[] = [
    ['sommeil', 'Comment évaluez-vous actuellement votre sommeil ?', MoteurConseils::NIVEAUX_SOMMEIL],
    ['stress',  'Comment évaluez-vous votre niveau de stress ?',      MoteurConseils::NIVEAUX_STRESS],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<?php require_once __DIR__ . '/../includes/head.php'; ?>
</head>
<body>
  <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

  <main class="conseils">
    <svg class="leaf-deco" viewBox="0 0 120 160" aria-hidden="true"><path d="M60 150V60M60 100C30 95 14 70 12 40c30 2 46 20 48 60Zm0-30C84 70 100 50 104 20 76 24 62 44 60 90Z" fill="none" stroke="#12a07e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>

    <header class="hero">
      <svg class="hero-leaf" viewBox="0 0 48 48" aria-hidden="true"><path d="M8 40C6 20 18 8 42 6c1 24-10 36-30 36" fill="#12a07e"/><path d="M8 42C16 30 24 22 34 14" stroke="#fff" stroke-width="2.5" fill="none" stroke-linecap="round"/></svg>
      <div>
        <h1>Nos Conseils Bien-être</h1>
        <p>Adoptez de bonnes habitudes pour une vie plus saine</p>
      </div>
    </header>

    <!-- Recherche -->
    <form class="search" role="search" method="get" action="<?= $h($selfUrl) ?>">
      <label class="sr-only" for="q">Rechercher un conseil</label>
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="#12a07e" stroke-width="2.2"/><path d="m15.5 15.5 5 5" stroke="#12a07e" stroke-width="2.2" stroke-linecap="round"/></svg>
      <input type="search" id="q" name="q" value="<?= $h($q) ?>" maxlength="60" autocomplete="off"
             placeholder="Rechercher un conseil : sommeil, stress, hydratation…">
      <button type="submit" class="btn">Rechercher</button>
    </form>
    <p class="search-hints">
      Essayez :
      <?php foreach (['sommeil', 'alimentation', 'stress', 'hydratation', 'activité physique'] as $mot): ?>
        <a href="<?= $h($selfUrl) ?>?q=<?= urlencode($mot) ?>"><?= $h($mot) ?></a>
      <?php endforeach; ?>
    </p>

    <?php if ($erreur): ?>
      <p class="notice" role="alert"><?= htmlspecialchars($erreur) ?></p>
    <?php elseif (!$categories): ?>
      <p class="notice">Aucun conseil n’est publié pour le moment.</p>
    <?php else: ?>

      <?php if ($recherche): ?>
        <!-- Résultats de recherche -->
        <section class="results" aria-live="polite">
          <?php $n = count($recherche['resultats']); ?>
          <h2><?= $n ?> résultat<?= $n > 1 ? 's' : '' ?> pour « <?= $h($recherche['q']) ?> »</h2>
          <p><a class="back-link" href="<?= $h($selfUrl) ?>">← Voir toutes les catégories</a></p>

          <?php if (!$n): ?>
            <p class="notice">Aucun conseil ne correspond à votre recherche. Essayez un mot plus simple (« sommeil », « eau », « stress »…).</p>
          <?php else: ?>
            <ul class="result-list">
              <?php foreach ($recherche['resultats'] as $r): ?>
                <li class="result">
                  <span class="chip-cat"><?= $h($r['categorie']) ?></span>
                  <strong><?= $h($r['titre']) ?></strong>
                  <span><?= $h($r['contenu']) ?></span>
                  <a href="<?= $h($selfUrl) ?>#<?= $h($r['slug']) ?>">Voir la catégorie →</a>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </section>

      <?php else: ?>

        <?php if (!$questionnaire || $ouvrirFormulaire || $erreurFormulaire): ?>
        <!-- Questionnaire de personnalisation -->
        <section class="perso" id="personnaliser">
          <details class="perso-card"<?= ($ouvrirFormulaire || $erreurFormulaire) ? ' open' : '' ?>>
            <summary>
              <span class="perso-ico" aria-hidden="true">📋</span>
              <span class="perso-text">
                <span class="perso-title">Découvrez vos conseils personnalisés</span>
                <span class="perso-sub">Quelques questions pour vous proposer des conseils adaptés à vos besoins.</span>
              </span>
              <span class="btn">Personnaliser mes conseils</span>
            </summary>

            <form class="quiz" method="post" action="<?= $h($selfUrl) ?>">
              <input type="hidden" name="action" value="questionnaire">

              <div class="quiz-head">
                <span class="quiz-step">Étape <b class="js-current">1</b> sur <b class="js-total"><?= count($etapes) ?></b></span>
                <span class="progress" aria-hidden="true"><span class="progress-bar"></span></span>
              </div>

              <?php if ($erreurFormulaire): ?>
                <p class="form-error" role="alert">Merci de répondre à toutes les questions.</p>
              <?php endif; ?>

              <?php foreach ($etapes as $i => $questions): ?>
                <div class="step">
                  <?php if ($i === 0 && $objectifProfil): ?>
                    <p class="profil-note">Votre objectif physique est déjà dans votre profil : <strong><?= $h(MoteurConseils::OBJECTIFS[$objectifProfil]) ?></strong>.</p>
                  <?php endif; ?>
                  <?php foreach ($questions as [$champ, $intitule, $choix]): ?>
                    <fieldset class="q">
                      <legend><?= $h($intitule) ?></legend>
                      <div class="opts">
                        <?php foreach ($choix as $valeur => $libelle): ?>
                          <label class="opt">
                            <input type="radio" name="<?= $champ ?>" value="<?= $h($valeur) ?>" required
                                   <?= ($questionnaire[$champ] ?? null) === $valeur ? 'checked' : '' ?>>
                            <span class="opt-card">
                              <span class="opt-ico" aria-hidden="true"><?= $icones[$valeur] ?? '•' ?></span>
                              <span class="opt-txt"><?= $h($libelle) ?></span>
                            </span>
                          </label>
                        <?php endforeach; ?>
                      </div>
                    </fieldset>
                  <?php endforeach; ?>
                </div>
              <?php endforeach; ?>

              <p class="step-error" role="alert" hidden>Choisissez une réponse pour continuer.</p>

              <div class="quiz-nav">
                <button type="button" class="btn btn-outline btn-prev">← Retour</button>
                <button type="button" class="btn btn-next">Suivant →</button>
                <button type="submit" class="btn btn-submit">Voir mes conseils</button>
              </div>
              <p class="quiz-note">Seules ces questions vous sont posées : votre profil (âge, poids, taille…) est déjà connu. Vos réponses ne sont gardées que le temps de votre visite.</p>
            </form>
          </details>
        </section>
        <?php endif; ?>

        <?php if ($cartes): ?>
          <!-- Conseils personnalisés -->
          <section class="mes-conseils" id="recommandations" aria-labelledby="mc-titre">
            <div class="mc-head">
              <svg class="mc-check" viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="22" fill="#12a07e"/><path d="M14 25l7 7 13-15" fill="none" stroke="#fff" stroke-width="4.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
              <div>
                <h2 id="mc-titre">Vos conseils personnalisés</h2>
                <?php if ($resume || $resumeProfil): ?>
                  <div class="mc-chips">
                    <?php foreach ($resume as $c): ?><span class="mc-chip"><?= $h($c) ?></span><?php endforeach; ?>
                    <?php foreach ($resumeProfil as $c): ?><span class="mc-chip mc-chip-profil"><?= $h($c) ?></span><?php endforeach; ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <p class="mc-intro"><span aria-hidden="true">⭐</span> Voici nos recommandations spécialement pour vous ! Des conseils adaptés à vos réponses.</p>

            <div class="adv-grid">
              <?php foreach ($conseilsPerso as $c): ?>
                <article class="adv-card">
                  <div class="adv-img">
                    <?php if ($c['image']): ?>
                      <img src="<?= $baseUrl ?>assets/img/<?= $h($c['image']) ?>" alt="" loading="lazy" onerror="this.remove()">
                    <?php endif; ?>
                  </div>
                  <div class="adv-body">
                    <span class="adv-chip"><?= $h($c['label']) ?></span>
                    <h3><?= $h($c['titre']) ?></h3>
                    <p><?= $h($c['contenu']) ?></p>
                    <a class="adv-link" href="#<?= $h($c['slug']) ?>" data-goto="<?= $h($c['slug']) ?>">
                      Voir les <?= (int) $c['nb'] ?> conseils →
                    </a>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>

            <div class="mc-actions">
              <?php if ($questionnaire): ?>
                <a class="btn btn-outline" href="<?= $h($selfUrl) ?>?modifier=1#personnaliser">Modifier mes réponses</a>
                <form method="post" action="<?= $h($selfUrl) ?>">
                  <input type="hidden" name="action" value="reinitialiser">
                  <button type="submit" class="btn btn-ghost">Effacer mes réponses</button>
                </form>
              <?php endif; ?>
            </div>
            <p class="mc-note">Ce ne sont pas des prescriptions médicales : ce sont des recommandations générales, choisies selon les informations indiquées.</p>
          </section>

          <h2 class="section-title">Explorer toutes les catégories</h2>
        <?php endif; ?>

      <div class="tabs" role="tablist" aria-label="Catégories de conseils">
        <?php $first = true; foreach ($categories as $key => $cat): ?>
          <button class="tab<?= $first ? ' is-active' : '' ?>" role="tab" id="tab-<?= $key ?>"
                  aria-controls="panel-<?= $key ?>" aria-selected="<?= $first ? 'true' : 'false' ?>"
                  data-target="<?= $key ?>" title="<?= htmlspecialchars($cat['description'] ?? '') ?>"><?= htmlspecialchars($cat['label']) ?><?php if (in_array($key, $recommandes, true)): ?><span class="dot" title="Recommandé pour vous"></span><?php endif; ?></button>
        <?php $first = false; endforeach; ?>
      </div>

      <?php $first = true; foreach ($categories as $key => $cat): ?>
        <section class="panel" id="panel-<?= $key ?>" role="tabpanel" aria-labelledby="tab-<?= $key ?>"<?= $first ? '' : ' hidden' ?>>
          <div class="panel-body">
            <div class="photo">
              <?php if ($cat['image']): ?>
                <img src="<?= $baseUrl ?>assets/img/<?= htmlspecialchars($cat['image']) ?>" alt="Conseils : <?= htmlspecialchars($cat['label']) ?>" onerror="this.remove()">
              <?php endif; ?>
            </div>
            <ul class="checks">
              <?php foreach ($cat['conseils'] as $c): ?>
                <li>
                  <span class="tick"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M5 10.5l3.2 3.2L15 6.8" fill="none" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                  <span class="advice"><strong><?= htmlspecialchars($c['titre']) ?></strong><span><?= htmlspecialchars($c['contenu']) ?></span></span>
                </li>
              <?php endforeach; ?>
              <?php if (!$cat['conseils']): ?>
                <li><span class="advice"><span>Pas encore de conseil dans cette catégorie.</span></span></li>
              <?php endif; ?>
            </ul>
          </div>
          <p class="banner">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-8-5.2-8-11a4.6 4.6 0 0 1 8-3 4.6 4.6 0 0 1 8 3c0 5.8-8 11-8 11Z" fill="#12a07e"/><path d="M7 11.5h3l1.3-2.5 2 4.5 1.2-2h2.5" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?= htmlspecialchars($cat['message']) ?>
          </p>
        </section>
      <?php $first = false; endforeach; ?>
      <?php endif; /* fin : pas de recherche */ ?>
    <?php endif; ?>

    <p class="disclaimer">Ces conseils sont généraux et ne remplacent pas l’avis d’un professionnel de santé.</p>
  </main>

 <?php require_once __DIR__ . '/../includes/footer.php'; ?>
<?php require_once __DIR__ . '/../includes/scripts.php'; ?>
</body>
</html>
