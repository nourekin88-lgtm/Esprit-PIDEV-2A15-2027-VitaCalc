<?php if ($plan !== null): ?>
    <section class="plan-section plan-menu" id="regime-conseille" aria-labelledby="plan-title">
        <div class="plan-menu-head">
            <span class="tile-icon regimes"><?= icon('leaf', 22) ?></span>
            <div>
                <p class="plan-kicker"><?= e($goals[$goal]['label']) ?></p>
                <h2 id="plan-title"><?= e($diet['nom'] ?? $plan['title']) ?></h2>
            </div>
        </div>
        <?php if ($goalNotice !== null): ?><p class="plan-note"><?= e($goalNotice) ?></p><?php endif; ?>
        <p><?= e($plan['advice']) ?></p>
        <?php if ($menu !== null): ?>
            <?php require __DIR__ . '/personalized-menu.php'; ?>
        <?php else: ?>
            <h3>Exemple de journée</h3>
            <div class="plan-meals">
                <?php foreach ($plan['meals'] as $slot => [$meal, $idea]): ?>
                    <article class="meal-card">
                        <div class="meal-heading">
                            <img src="<?= e(photo(MenuPlanner::MEALS[$slot][1])) ?>" width="80" height="80" loading="lazy" decoding="async" alt="">
                            <h4><?= e($meal) ?></h4>
                        </div>
                        <p class="meal-copy"><?= e($idea) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($diet !== null): ?>
            <a class="button button-secondary" href="<?= e(route('regimes-fiche', ['id' => $diet['id']])) ?>">Consulter la fiche du régime</a>
        <?php endif; ?>
    </section>
<?php endif; ?>
