<section class="section pt-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('home') ?>">Accueil</a></li>
                <li class="breadcrumb-item"><a href="<?= url('recettes') ?>">Recettes</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= e($recette['nom']) ?></li>
            </ol>
        </nav>

        <?= recipeCover($recette, 'hero') ?>

        <div class="row g-5 mt-0">
            <!-- Présentation + étapes -->
            <div class="col-lg-7">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
                    <h1 class="detail-title mb-0"><?= e($recette['nom']) ?></h1>
                    <form method="post" action="<?= url('favori-toggle') ?>">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int) $recette['id'] ?>">
                        <input type="hidden" name="redirect" value="<?= e(currentUrl()) ?>">
                        <button type="submit" class="btn <?= $estFavori ? 'btn-primary' : 'btn-outline-primary' ?>">
                            <i class="bi <?= $estFavori ? 'bi-heart-fill' : 'bi-heart' ?> me-1"></i><?= $estFavori ? 'Dans mes favoris' : 'Ajouter aux favoris' ?>
                        </button>
                    </form>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <a href="#avis" class="detail-rate"><?= ratingBadge($recette) ?></a>
                    <?= momentChip($recette['moment'], 'chip lg') ?>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-4">
                    <span class="chip lg"><i class="bi bi-clock"></i><?= e(formatDuration((int) $recette['tempsPreparation'])) ?></span>
                    <span class="chip lg"><i class="bi bi-people"></i><?= (int) $recette['nombrePortions'] ?> portions</span>
                    <span class="chip lg"><i class="bi bi-basket2"></i><?= count($lignes) ?> ingrédients</span>
                    <?php if ($recette['nbFavoris'] > 0): ?>
                        <span class="chip lg"><i class="bi bi-heart-fill"></i><?= (int) $recette['nbFavoris'] ?></span>
                    <?php endif; ?>
                </div>

                <p class="lead text-muted-2"><?= nl2br(e($recette['description'])) ?></p>

                <!-- Mode cuisine -->
                <a href="<?= url('cuisine', ['id' => $recette['id']]) ?>" class="cook-launch">
                    <span class="cl-ico"><i class="bi bi-fullscreen"></i></span>
                    <span class="cl-txt"><strong>Mode cuisine</strong><small>Plein écran, une grande étape à la fois, avec minuteur intégré</small></span>
                    <i class="bi bi-arrow-right cl-arrow"></i>
                </a>

                <h3 class="h4 mt-5 mb-4"><i class="bi bi-list-ol text-primary me-2"></i>Préparation pas à pas</h3>
                <?php foreach (steps($recette['instructions']) as $n => $step): ?>
                    <div class="step-item">
                        <div class="no"><?= $n + 1 ?></div>
                        <p><?= e($step) ?><?php if ($mn = stepMinutes($step)): ?> <span class="step-time"><i class="bi bi-stopwatch"></i><?= e(formatDuration($mn)) ?></span><?php endif; ?></p>
                    </div>
                <?php endforeach; ?>

                <!-- Notes et avis -->
                <h3 class="h4 mt-5 mb-3" id="avis"><i class="bi bi-star-fill text-primary me-2"></i>Notes et avis</h3>
                <div class="avis-summary">
                    <?php if ($recette['nbAvis'] > 0): ?>
                        <div class="avis-big"><?= e(number_format((float) $recette['noteMoyenne'], 1, ',', '')) ?><small>/5</small></div>
                        <div>
                            <?= stars((float) $recette['noteMoyenne'], 'lg') ?>
                            <div class="text-muted small mt-1"><?= (int) $recette['nbAvis'] ?> avis de la communauté</div>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">Personne n'a encore noté cette recette. Soyez le premier ! ⭐</div>
                    <?php endif; ?>
                </div>

                <form method="post" action="<?= url('avis-save') ?>" class="avis-form" novalidate>
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= (int) $recette['id'] ?>">
                    <p class="fw-semibold mb-2"><?= $monAvis ? 'Votre note (vous pouvez la modifier)' : 'Donnez votre note' ?></p>
                    <fieldset class="star-input">
                        <legend class="visually-hidden">Note de 1 à 5 étoiles</legend>
                        <?php for ($n = 5; $n >= 1; $n--): ?>
                            <input type="radio" name="note" id="note<?= $n ?>" value="<?= $n ?>" <?= ($monAvis['note'] ?? 0) === $n ? 'checked' : '' ?> required>
                            <label for="note<?= $n ?>" title="<?= $n ?> étoile<?= $n > 1 ? 's' : '' ?>"><i class="bi bi-star-fill"></i><span class="visually-hidden"><?= $n ?> étoile<?= $n > 1 ? 's' : '' ?></span></label>
                        <?php endfor; ?>
                    </fieldset>
                    <label for="commentaire" class="form-label mt-3">Votre avis <span class="text-muted fw-normal">(facultatif)</span></label>
                    <textarea id="commentaire" name="commentaire" rows="3" maxlength="<?= \App\Models\Avis::COMMENT_MAX ?>" class="form-control"
                              placeholder="Qu'en avez-vous pensé ? Une astuce ?"><?= e($monAvis['commentaire'] ?? '') ?></textarea>
                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i><?= $monAvis ? 'Mettre à jour mon avis' : 'Publier mon avis' ?></button>
                    </div>
                </form>
                <?php if ($monAvis): ?>
                    <form method="post" action="<?= url('avis-delete') ?>" class="mt-2" data-confirm="Retirer votre avis sur cette recette ?">
                        <?= csrfField() ?>
                        <input type="hidden" name="id" value="<?= (int) $recette['id'] ?>">
                        <button type="submit" class="btn btn-link text-danger p-0 small"><i class="bi bi-trash me-1"></i>Retirer mon avis</button>
                    </form>
                <?php endif; ?>

                <?php if ($avis): ?>
                    <ul class="avis-list">
                        <?php foreach ($avis as $a): ?>
                            <li>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <strong><?= e($a['auteur']) ?></strong>
                                    <?= stars((float) $a['note']) ?>
                                    <small class="text-muted ms-auto"><?= e(date('d/m/Y', strtotime($a['date'] ?? 'now'))) ?></small>
                                </div>
                                <?php if (trim((string) ($a['commentaire'] ?? '')) !== ''): ?>
                                    <p class="mb-0 mt-1"><?= nl2br(e($a['commentaire'])) ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- Ingrédients -->
            <div class="col-lg-5">
                <div class="panel sticky-panel" id="recipe-panel" data-base-portions="<?= (int) $recette['nombrePortions'] ?>">
                    <h4 class="mb-3"><i class="bi bi-basket2 text-primary me-2"></i>Ingrédients</h4>

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                        <span class="text-muted small">Pour combien de personnes ?</span>
                        <div class="portion-ctrl">
                            <button type="button" id="portMinus" aria-label="Moins de portions">−</button>
                            <span class="val"><span id="portVal"><?= (int) $recette['nombrePortions'] ?></span> portions</span>
                            <button type="button" id="portPlus" aria-label="Plus de portions">+</button>
                        </div>
                    </div>

                    <?php if (empty($lignes)): ?>
                        <p class="text-muted mb-0">Aucun ingrédient renseigné pour cette recette.</p>
                    <?php else: ?>
                        <ul class="ing-list">
                            <?php foreach ($lignes as $l): ?>
                                <li>
                                    <span class="d-flex align-items-center">
                                        <span class="check"><i class="bi bi-check-lg"></i></span>
                                        <?= ingredientThumb($l, 'sm') ?>
                                        <span class="name"><?= e($l['nom']) ?></span>
                                    </span>
                                    <span class="qty-box"><span class="qty" data-base="<?= e((float) $l['quantite']) ?>"><?= e(qty($l['quantite'])) ?></span> <?= e($l['unite']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="text-muted small mt-3 mb-3"><i class="bi bi-lightbulb me-1"></i>Cliquez sur un ingrédient pour le cocher quand il est dans votre panier.</p>
                    <?php endif; ?>

                    <a href="<?= url('courses', ['add' => $recette['id']]) ?>" class="btn btn-primary w-100">
                        <i class="bi bi-cart-plus me-1"></i>Ajouter à ma liste de courses
                    </a>
                </div>
                <a href="<?= url('recettes') ?>" class="btn btn-outline-primary mt-4"><i class="bi bi-arrow-left me-1"></i>Retour aux recettes</a>
            </div>
        </div>
    </div>
</section>
