<?php
/**
 * Carte de recette réutilisable (accueil, liste, favoris).
 * Variables : $r (recette), $mesFavoris (ids des favoris du client, optionnel)
 * Visiteur : bouton « Voir plus » (🔒) qui mène à l'inscription.  Client : bouton cœur + « Voir la recette ».
 */
$logged = isLoggedIn();
$isFav  = $logged && in_array($r['id'], $mesFavoris ?? [], true);
$more   = max(0, $r['nbIngredients'] - count($r['apercu']));
?>
<article class="recipe-card">
    <a class="cover-link" href="<?= url('recette', ['id' => $r['id']]) ?>" tabindex="-1" aria-hidden="true"><?= recipeCover($r) ?></a>

    <?php if ($logged): ?>
        <form method="post" action="<?= url('favori-toggle') ?>" class="fav-form">
            <?= csrfField() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <input type="hidden" name="redirect" value="<?= e(currentUrl()) ?>">
            <button type="submit" class="fav-btn <?= $isFav ? 'is-fav' : '' ?>"
                    title="<?= $isFav ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>"
                    aria-label="<?= $isFav ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>">
                <i class="bi <?= $isFav ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
            </button>
        </form>
    <?php endif; ?>

    <div class="recipe-body">
        <h3 class="recipe-title"><a href="<?= url('recette', ['id' => $r['id']]) ?>"><?= e($r['nom']) ?></a></h3>
        <div class="recipe-rate"><?= ratingBadge($r) ?></div>
        <p class="recipe-desc"><?= e(mb_strimwidth($r['description'], 0, 95, '…')) ?></p>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <?= momentChip($r['moment']) ?>
            <span class="chip"><i class="bi bi-clock"></i><?= e(formatDuration((int) $r['tempsPreparation'])) ?></span>
            <span class="chip"><i class="bi bi-people"></i><?= (int) $r['nombrePortions'] ?> portions</span>
            <span class="chip"><i class="bi bi-basket2"></i><?= (int) $r['nbIngredients'] ?> ingrédients</span>
        </div>
        <p class="recipe-apercu">
            <?= e(implode(' · ', $r['apercu'])) ?><?= $more ? ' <strong>+' . $more . '</strong>' : '' ?>
        </p>
        <?php if ($logged): ?>
            <a href="<?= url('recette', ['id' => $r['id']]) ?>" class="btn btn-primary w-100 mt-auto">Voir la recette <i class="bi bi-arrow-right ms-1"></i></a>
        <?php else: ?>
            <a href="<?= url('recette', ['id' => $r['id']]) ?>" class="btn btn-outline-primary w-100 mt-auto">Voir plus <i class="bi bi-lock-fill ms-1"></i></a>
        <?php endif; ?>
    </div>
</article>
