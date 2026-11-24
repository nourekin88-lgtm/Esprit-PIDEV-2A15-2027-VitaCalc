<?php
$destination = route($adminArea ? 'admin-' . $key : $key);
$count = $counts[$key];
?>
<?php if ($adminArea): ?>
    <a class="collection-card" href="<?= e($destination) ?>">
        <span class="collection-top">
            <span class="tile-icon <?= e($key) ?>"><?= icon($meta['icon'], 23) ?></span>
            <span class="count"><?= e($count) ?> fiche<?= $count > 1 ? 's' : '' ?></span>
        </span>
        <h3><?= e($meta['title']) ?></h3>
        <p><?= e($meta['intro']) ?></p>
        <span class="collection-link">Gérer les fiches <?= icon('arrow', 16) ?></span>
    </a>
<?php else: ?>
    <article class="flip-card" data-flip-card aria-label="<?= e($meta['title']) ?>">
        <div class="flip-inner">
            <div class="collection-card flip-face" data-flip-front>
                <div class="collection-top">
                    <span class="tile-icon <?= e($key) ?>"><?= icon($meta['icon'], 23) ?></span>
                    <span class="count"><?= e($count) ?> fiche<?= $count > 1 ? 's' : '' ?></span>
                </div>
                <h3><?= e($meta['title']) ?></h3>
                <p><?= e($meta['intro']) ?></p>
                <button class="flip-link" data-flip-open aria-controls="apercu-<?= e($key) ?>"
                    aria-label="<?= e('Retourner la carte ' . $meta['title']) ?>" aria-expanded="false" type="button" hidden>
                    <?= icon('rotate', 17) ?>
                </button>
                <a class="collection-link" data-flip-fallback href="<?= e($destination) ?>">Voir les fiches <?= icon('arrow', 16) ?></a>
            </div>
            <div class="collection-card flip-face flip-back" data-flip-back id="apercu-<?= e($key) ?>" hidden>
                <div class="collection-top">
                    <p class="flip-title"><?= e($meta['title']) ?></p>
                    <button class="icon-button" data-flip-close type="button" title="Revenir au recto"
                        aria-label="<?= e('Revenir au recto de la carte ' . $meta['title']) ?>"><?= icon('rotate', 17) ?></button>
                </div>
                <?php if ($latest[$key] === []): ?>
                    <p>Aucune fiche publiée pour le moment.</p>
                <?php else: ?>
                    <ul class="flip-preview">
                        <?php foreach (array_slice($latest[$key], 0, 2) as $record): ?>
                            <li>
                                <a href="<?= e(route($key . '-fiche', ['id' => $record['id']])) ?>">
                                    <span><?= e($record['nom']) ?></span> <?= icon('chevron', 14) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <a class="collection-link" href="<?= e($destination) ?>">Voir toutes les fiches <?= icon('arrow', 16) ?></a>
            </div>
        </div>
    </article>
<?php endif; ?>
