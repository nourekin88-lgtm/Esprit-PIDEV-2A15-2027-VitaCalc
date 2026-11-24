<header class="page-heading catalogue-intro">
    <div>
        <h1><?= e($title) ?></h1>
        <p><?= e($meta['intro']) ?></p>
        <a class="button button-secondary" href="<?= e(route('inscription')) ?>#mon-objectif">Mon objectif et mon menu</a>
    </div>
    <span class="visual-symbol catalogue-symbol <?= e($collection) ?>"><?= icon($meta['icon'], 74) ?></span>
</header>
<div class="catalog-toolbar">
    <?php require __DIR__ . '/../partials/search.php'; ?>
    <span class="result-count"><?= count($records) ?> fiche<?= count($records) > 1 ? 's' : '' ?></span>
</div>
<?php if ($records === []): ?>
    <div class="empty">
        <?= icon('search', 27) ?>
        <h2>Aucune fiche trouvée</h2>
        <p><?= $query === '' ? 'Les fiches apparaîtront ici dès leur publication.' : 'Essayez une recherche avec d’autres mots.' ?></p>
    </div>
<?php else: ?>
    <div class="catalog-grid">
        <?php foreach ($records as $record): ?>
            <?php
            $goal = $catalogueMap[$record['id']] ?? '';
            $destination = route($collection . '-fiche', ['id' => $record['id']]);
            $previewId = 'apercu-fiche-' . $record['id'];
            $readLabel = 'Lire la fiche ' . $record['nom'];
            ?>
            <article class="flip-card" data-flip-card aria-label="<?= e($record['nom']) ?>">
                <div class="flip-inner">
                    <div class="catalog-card flip-face" data-flip-front>
                        <div class="collection-top">
                            <span class="tile-icon <?= e($collection) ?>"><?= icon(NutritionPlan::GOALS[$goal]['icon'] ?? $meta['icon'], 23) ?></span>
                            <button class="icon-button" data-flip-open type="button" aria-controls="<?= e($previewId) ?>"
                                aria-expanded="false" title="Voir l’aperçu" aria-label="<?= e('Voir l’aperçu de la fiche ' . $record['nom']) ?>" hidden><?= icon('rotate', 17) ?></button>
                        </div>
                        <h2><?= e($record['nom']) ?></h2>
                        <p><?= e(excerpt($record['description'], 155)) ?></p>
                        <a class="text-link" href="<?= e($destination) ?>" aria-label="<?= e($readLabel) ?>">Lire la fiche</a>
                    </div>
                    <div class="catalog-card flip-face flip-back" data-flip-back id="<?= e($previewId) ?>" hidden>
                        <div class="collection-top">
                            <h2 class="flip-title"><?= e($record['nom']) ?></h2>
                            <button class="icon-button" data-flip-close type="button" title="Revenir au recto"
                                aria-label="<?= e('Revenir au recto de la fiche ' . $record['nom']) ?>"><?= icon('rotate', 17) ?></button>
                        </div>
                        <p><?= e(excerpt($record['description'], 280)) ?></p>
                        <a class="text-link" href="<?= e($destination) ?>" aria-label="<?= e($readLabel) ?>">Lire la fiche <?= icon('arrow', 16) ?></a>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
