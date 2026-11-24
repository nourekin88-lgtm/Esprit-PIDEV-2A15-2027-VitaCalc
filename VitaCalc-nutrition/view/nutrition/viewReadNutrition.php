<a class="back-link" href="<?= e(route($collection)) ?>"><?= icon('back', 17) ?> <?= e($meta['title']) ?></a>
<article class="detail-panel">
    <span class="tile-icon <?= e($collection) ?>"><?= icon(NutritionPlan::GOALS[$goalCode ?? '']['icon'] ?? $meta['icon'], 24) ?></span>
    <h1><?= e($record['nom']) ?></h1>
    <p class="description"><?= e($record['description']) ?></p>
    <p class="detail-profile-link">
        <a class="button button-primary" href="<?= e(route('inscription', $goalCode ? ['objectif' => $goalCode] : [])) ?>#mon-objectif">
            <?= $goalCode ? 'Choisir cet objectif dans mon profil' : 'Voir mon menu personnalisé' ?>
        </a>
    </p>
    <?php if (isAdmin()): ?>
        <a class="button button-secondary" href="<?= e(route($collection . '-modifier', ['id' => $record['id']])) ?>"><?= icon('edit', 16) ?> Modifier la fiche</a>
    <?php endif; ?>
</article>
