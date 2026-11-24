<?php
$logged = isLoggedIn();
$MOMENTS = \App\Models\Recette::MOMENTS;
/** Lien d'un filtre : on garde la recherche, la durée et le moment courants, sauf celui qu'on change. */
$linkTo = function (array $change) use ($duree, $search, $moment): string {
    $params = array_filter(['q' => $search, 'duree' => $duree, 'moment' => $moment], fn($v) => $v !== '');
    foreach ($change as $k => $v) {
        if ($v === '') { unset($params[$k]); } else { $params[$k] = $v; }
    }
    return e(url('recettes', $params));
};
$chip = function (string $key, string $label) use ($duree, $linkTo): string {
    return '<a href="' . $linkTo(['duree' => $key]) . '" class="filter-chip ' . ($duree === $key ? 'on' : '') . '">' . e($label) . '</a>';
};
?>
<section class="page-hero">
    <span class="blob blob-a"></span><span class="plus plus-1">+</span><span class="plus plus-2">+</span>
    <div class="container position-relative text-center">
        <span class="eyebrow-pill">Meal Prep</span>
        <h1>Nos recettes healthy</h1>
        <p class="mb-0 text-muted-2">Choisissez une recette, voyez ses ingrédients et lancez-vous.</p>
    </div>
</section>

<section class="section pt-4">
    <div class="container">
        <form method="get" action="index.php" class="search-wrap">
            <input type="hidden" name="page" value="recettes">
            <?php if ($duree !== ''): ?><input type="hidden" name="duree" value="<?= e($duree) ?>"><?php endif; ?>
            <?php if ($moment !== ''): ?><input type="hidden" name="moment" value="<?= e($moment) ?>"><?php endif; ?>
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" name="q" value="<?= e($search) ?>" placeholder="Rechercher une recette ou un ingrédient (ex : poulet, avoine…)" aria-label="Rechercher">
                <button class="btn btn-primary" type="submit">Rechercher</button>
            </div>
        </form>

        <!-- Moment de la journée : petit-déjeuner / déjeuner / dîner -->
        <div class="moment-tabs" role="tablist" aria-label="Moment de la journée">
            <a href="<?= $linkTo(['moment' => '']) ?>" class="moment-tab <?= $moment === '' ? 'on' : '' ?>">
                <i class="bi bi-grid"></i><span>Tous les repas</span><?php if ($logged): ?><em><?= array_sum($parMoment) ?></em><?php endif; ?>
            </a>
            <?php foreach ($MOMENTS as $key => [$mLabel, $mIcon]): ?>
                <a href="<?= $linkTo(['moment' => $key]) ?>" class="moment-tab moment-<?= e($key) ?> <?= $moment === $key ? 'on' : '' ?>">
                    <i class="bi <?= e($mIcon) ?>"></i><span><?= e($mLabel) ?></span><?php if ($logged): ?><em><?= (int) $parMoment[$key] ?></em><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="filter-row">
            <?= $chip('', 'Toutes les durées') ?>
            <?php foreach (\App\Models\Recette::DUREES as $key => [$label]): ?>
                <?= $chip($key, $label) ?>
            <?php endforeach; ?>
        </div>

        <?php if (!$logged): ?>
            <div class="guest-banner">
                <i class="bi bi-lock-fill"></i>
                <div><strong>Aperçu visiteur.</strong> Créez un compte gratuit pour voir les ingrédients avec leurs quantités, les étapes, enregistrer vos favoris et générer votre liste de courses.</div>
                <a href="<?= url('register') ?>" class="btn btn-primary btn-sm ms-lg-auto">S'inscrire</a>
            </div>
        <?php endif; ?>

        <?php if ($search !== '' || $duree !== '' || $moment !== ''): ?>
            <p class="text-muted mb-4"><?= count($recettes) ?> résultat(s)
                <?php if ($search !== ''): ?>pour « <strong><?= e($search) ?></strong> »<?php endif; ?>
                <?php if ($moment !== ''): ?>· <strong><?= e($MOMENTS[$moment][0]) ?></strong><?php endif; ?>
                · <a href="<?= url('recettes') ?>">Effacer les filtres</a></p>
        <?php endif; ?>

        <?php if (empty($recettes)): ?>
            <div class="panel text-center py-5">
                <div class="empty-ico"><i class="bi bi-search"></i></div>
                <h5 class="mt-3">Aucune recette trouvée</h5>
                <p class="text-muted mb-0">Essayez un autre mot-clé ou retirez les filtres.</p>
            </div>
        <?php elseif ($moment !== '' || !$logged): ?>
            <!-- Un seul moment choisi : grille simple -->
            <div class="row g-4">
                <?php foreach ($recettes as $r): ?>
                    <div class="col-lg-4 col-md-6"><?php include BASE_PATH . '/views/includes/recipe_card.php'; ?></div>
                <?php endforeach; ?>
            </div>
            <?php if (!$logged && $apercu): ?>
                <div class="panel text-center mt-5 py-4">
                    <div class="empty-ico"><i class="bi bi-lock"></i></div>
                    <h5 class="mt-3">Il y a plus de recettes qui vous attendent</h5>
                    <p class="text-muted">Créez un compte gratuit pour accéder à toutes nos recettes healthy.</p>
                    <a href="<?= url('register') ?>" class="btn btn-primary">Voir toutes les recettes 🔒</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Tous les repas : une section par moment de la journée -->
            <?php foreach ($MOMENTS as $key => [$mLabel, $mIcon, $mEmoji, $mSub]): ?>
                <?php $groupe = array_values(array_filter($recettes, fn($x) => $x['moment'] === $key)); ?>
                <?php if (!$groupe) { continue; } ?>
                <div class="moment-section moment-<?= e($key) ?>" id="moment-<?= e($key) ?>">
                    <div class="moment-head">
                        <span class="moment-ico"><i class="bi <?= e($mIcon) ?>"></i></span>
                        <div>
                            <h2><?= e($mLabel) ?> <span class="moment-emoji"><?= $mEmoji ?></span></h2>
                            <p><?= e($mSub) ?> · <?= count($groupe) ?> recette<?= count($groupe) > 1 ? 's' : '' ?></p>
                        </div>
                    </div>
                    <div class="row g-4">
                        <?php foreach ($groupe as $r): ?>
                            <div class="col-lg-4 col-md-6"><?php include BASE_PATH . '/views/includes/recipe_card.php'; ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>
