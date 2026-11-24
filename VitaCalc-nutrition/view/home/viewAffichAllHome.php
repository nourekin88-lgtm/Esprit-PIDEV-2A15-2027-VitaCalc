<?php if ($adminArea): ?>
    <header class="page-heading">
        <h1>Tableau de bord</h1>
        <p>Gérez le contenu publié dans l’espace nutrition de VitaCalc.</p>
    </header>
<?php else: ?>
    <section class="hero" aria-labelledby="hero-title" data-reel>
        <div class="hero-media" aria-hidden="true">
            <video id="reel-entrainement" class="hero-video" muted loop playsinline preload="none" tabindex="-1">
                <source src="assets/imges/entrainement.mp4" type="video/mp4">
            </video>
        </div>
        <div class="hero-copy">
            <p class="hero-label"><?= icon('leaf', 17) ?> Espace nutrition</p>
            <h1 id="hero-title">Mieux manger,<br><em>à votre rythme.</em></h1>
            <p>Explorez les régimes alimentaires et les objectifs nutritionnels qui s’accordent à votre quotidien.</p>
            <a class="button button-light" href="<?= e(route('regimes')) ?>">Découvrir les régimes <?= icon('arrow', 17) ?></a>
            <a class="button hero-profile" href="<?= e(route('inscription')) ?>"><?= nutritionProfileId() === null ? 'Créer mon profil' : 'Voir mon menu' ?></a>
        </div>
        <button class="reel-toggle" type="button" aria-controls="reel-entrainement" aria-label="Mettre la vidéo en pause" hidden>
            <span data-reel-pause><?= icon('pause', 21) ?></span>
            <span data-reel-play hidden><?= icon('play', 21) ?></span>
        </button>
    </section>
<?php endif; ?>

<section class="section" aria-labelledby="collections">
    <div class="section-heading">
        <h2 id="collections"><?= $adminArea ? 'Collections à gérer' : 'Explorer la nutrition' ?></h2>
    </div>
    <div class="two-grid">
        <?php foreach ($collections as $key => $meta): ?>
            <?php require __DIR__ . '/../partials/collection-card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>

<section class="section recent" aria-labelledby="dernieres-fiches">
    <div class="section-heading"><h2 id="dernieres-fiches">Dernières fiches ajoutées</h2></div>
    <div class="two-grid">
        <?php foreach ($collections as $key => $meta): ?>
            <div class="recent-panel">
                <h3><?= icon($meta['icon'], 17) ?> <?= e($meta['title']) ?></h3>
                <?php if ($latest[$key] === []): ?>
                    <p class="recent-empty">Aucune fiche publiée.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($latest[$key] as $record): ?>
                            <li>
                                <a href="<?= e(route($key . ($adminArea ? '-modifier' : '-fiche'), ['id' => $record['id']])) ?>">
                                    <span><?= e($record['nom']) ?></span> <?= icon('chevron', 16) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</section>
