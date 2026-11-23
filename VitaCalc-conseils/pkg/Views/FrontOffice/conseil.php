<?php
$pageTitle   = 'Conseils bien-être | VitaCalc';
$currentPage = 'conseils';
$categories  = $categories ?? [];
$recommandes = $recommandes ?? [];
$erreur      = $erreur ?? null;
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

    <?php if ($erreur): ?>
      <p class="notice" role="alert"><?= htmlspecialchars($erreur) ?></p>
    <?php elseif (!$categories): ?>
      <p class="notice">Aucun conseil n’est publié pour le moment.</p>
    <?php else: ?>

      <?php if ($recommandes): ?>
        <p class="for-you-note">Sélection pour vous : <?= htmlspecialchars(implode(', ', array_map(fn($k) => $categories[$k]['label'], $recommandes))) ?>.</p>
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
    <?php endif; ?>

    <p class="disclaimer">Ces conseils sont généraux et ne remplacent pas l’avis d’un professionnel de santé.</p>
  </main>

 <?php require_once __DIR__ . '/../includes/footer.php'; ?>
<?php require_once __DIR__ . '/../includes/scripts.php'; ?>
</body>
</html>
