<?php
/**
 * « Qu'est-ce que j'ai dans mon frigo ? »
 * Variables : $ingredients, $shelves (ingrédients rangés par étagère), $recettes, $frigoData, $mesFavoris
 * Un vrai frigo s'ouvre ; on y « prend » les ingrédients, et les recettes se classent en direct (assets/js/frigo.js).
 */
$uid = (int) currentUser()['id'];
?>
<link rel="stylesheet" href="assets/css/frigo.css">

<section class="page-hero">
    <span class="blob blob-a"></span><span class="plus plus-1">+</span><span class="plus plus-2">+</span>
    <div class="container position-relative text-center">
        <span class="eyebrow-pill"><i class="bi bi-snow2 me-1"></i>Anti-gaspi</span>
        <h1>Qu'est-ce que j'ai dans mon frigo ?</h1>
        <p class="mb-0 text-muted-2">Ouvrez le frigo, touchez ce que vous avez : on vous propose les recettes à cuisiner avec.</p>
    </div>
</section>

<section class="section pt-4" id="fridge" data-storage="vc_fridge_u<?= $uid ?>">
    <div class="container">
        <div class="row g-4 g-xl-5">

            <!-- ====== LE FRIGO ====== -->
            <div class="col-lg-6">
                <div class="fridge-tools">
                    <div class="search-box">
                        <i class="bi bi-search"></i>
                        <input type="text" id="fridgeSearch" placeholder="Chercher un ingrédient (ex : tomate, poulet…)" aria-label="Chercher un ingrédient" autocomplete="off">
                    </div>
                </div>

                <?php if (empty($ingredients)): ?>
                    <div class="panel text-center py-5"><p class="text-muted mb-0">Le catalogue d'ingrédients est vide pour le moment.</p></div>
                <?php else: ?>
                <div class="fridge-wrap">
                    <div class="fridge" id="fridgeBox">
                        <div class="fridge-cabinet">

                            <!-- Intérieur -->
                            <div class="fridge-inside" id="fridgeInside" inert>
                                <div class="fi-light" aria-hidden="true"></div>
                                <div class="fi-mist" aria-hidden="true"><i></i><i></i><i></i></div>
                                <div class="fi-scroll" id="fridgeGrid">
                                    <?php foreach (FRIDGE_SHELVES as $key => [$titre, $icone]):
                                        if ($key === 'placard' || empty($shelves[$key])) { continue; } ?>
                                        <section class="shelf shelf-<?= e($key) ?>" data-shelf="<?= e($key) ?>">
                                            <h3 class="shelf-label"><i class="bi <?= e($icone) ?>"></i><?= e($titre) ?></h3>
                                            <div class="shelf-items">
                                                <?php foreach ($shelves[$key] as $i): ?>
                                                    <label class="fi" data-name="<?= e(normalizeText($i['nom'])) ?>">
                                                        <input type="checkbox" value="<?= (int) $i['id'] ?>">
                                                        <?= ingredientThumb($i, 'lg') ?>
                                                        <span class="fi-nm"><?= e($i['nom']) ?></span>
                                                        <span class="fi-ck"><i class="bi bi-check-lg"></i></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </section>
                                    <?php endforeach; ?>
                                    <p class="fi-nomatch d-none" id="fridgeNoMatch"><i class="bi bi-search"></i> Aucun ingrédient ne correspond à votre recherche.</p>
                                </div>
                            </div>

                            <!-- Porte (pivote sur la gauche) -->
                            <div class="fridge-door" id="fridgeDoor">
                                <div class="door-face door-front">
                                    <div class="door-freezer" aria-hidden="true"><span class="hdl hdl-top"></span></div>
                                    <div class="door-main" aria-hidden="true"><span class="hdl hdl-main"></span></div>
                                    <img class="door-logo" src="assets/images/logo/vitacalc-icon.png" alt="" aria-hidden="true">
                                    <div class="door-note" aria-hidden="true">
                                        <b id="doorNoteN">0</b>
                                        <span id="doorNoteT">ingrédient<br>au frigo</span>
                                    </div>
                                    <div class="door-hint" aria-hidden="true"><i class="bi bi-hand-index-thumb"></i> Touchez pour ouvrir</div>
                                    <button type="button" class="door-hit" id="fridgeToggle" aria-expanded="false" aria-controls="fridgeInside" aria-label="Ouvrir le frigo"></button>
                                </div>
                                <div class="door-face door-back" aria-hidden="true">
                                    <span class="rail r1"></span><span class="rail r2"></span><span class="rail r3"></span>
                                </div>
                            </div>
                        </div>
                        <div class="fridge-feet" aria-hidden="true"><i></i><i></i></div>
                        <div class="fridge-floor" aria-hidden="true"></div>
                    </div>
                </div>

                <?php if (!empty($shelves['placard'])): [$pTitre, $pIcone] = FRIDGE_SHELVES['placard']; ?>
                <div class="cupboard">
                    <div class="cupboard-in">
                        <section class="shelf shelf-placard" data-shelf="placard">
                            <h3 class="shelf-label"><i class="bi <?= e($pIcone) ?>"></i><?= e($pTitre) ?></h3>
                            <div class="shelf-items">
                                <?php foreach ($shelves['placard'] as $i): ?>
                                    <label class="fi" data-name="<?= e(normalizeText($i['nom'])) ?>">
                                        <input type="checkbox" value="<?= (int) $i['id'] ?>">
                                        <?= ingredientThumb($i, 'lg') ?>
                                        <span class="fi-nm"><?= e($i['nom']) ?></span>
                                        <span class="fi-ck"><i class="bi bi-check-lg"></i></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                </div>
                <?php endif; ?>

                <div class="fridge-bar mt-3">
                    <span class="fridge-count" id="fridgeCount" aria-live="polite">0 coché</span>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="fridgeClose" hidden><i class="bi bi-door-closed me-1"></i>Fermer le frigo</button>
                    <button type="button" class="btn btn-link text-danger p-0 ms-auto small" id="fridgeClear"><i class="bi bi-x-circle me-1"></i>Tout vider</button>
                </div>
                <?php endif; ?>
            </div>

            <!-- ====== RECETTES ====== -->
            <div class="col-lg-6">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <h4 class="mb-0"><i class="bi bi-stars text-primary me-2"></i>Recettes avec vos ingrédients</h4>
                    <div class="filter-row m-0" role="group" aria-label="Filtrer les suggestions">
                        <button type="button" class="filter-chip on" data-fridge-filter="all">Toutes</button>
                        <button type="button" class="filter-chip" data-fridge-filter="full">100 % prêtes</button>
                    </div>
                </div>
                <p class="fr-summary" id="fridgeSummary" aria-live="polite" hidden></p>

                <div class="panel text-center py-5" id="fridgeEmpty">
                    <div class="empty-ico"><i class="bi bi-snow2"></i></div>
                    <h5 class="mt-3" id="fridgeEmptyTitle">Ouvrez le frigo et choisissez vos ingrédients</h5>
                    <p class="text-muted mb-0" id="fridgeEmptyText">Les recettes que vous pouvez préparer (ou presque) apparaîtront ici, avec ce qu'il vous manque.</p>
                </div>

                <div class="fridge-results" id="fridgeResults">
                    <?php foreach ($recettes as $r): ?>
                        <article class="fridge-card" data-id="<?= (int) $r['id'] ?>" data-time="<?= (int) $r['tempsPreparation'] ?>" hidden>
                            <a href="<?= url('recette', ['id' => $r['id']]) ?>" class="fc-cover"><?= recipeCover($r, 'thumb') ?></a>
                            <div class="fc-body">
                                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                                    <h5 class="fc-title mb-0"><a href="<?= url('recette', ['id' => $r['id']]) ?>"><?= e($r['nom']) ?></a></h5>
                                    <span class="fc-pct" data-role="pct">0 %</span>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 my-2">
                                    <?= momentChip($r['moment']) ?>
                                    <span class="chip"><i class="bi bi-clock"></i><?= e(formatDuration((int) $r['tempsPreparation'])) ?></span>
                                    <?= ratingBadge($r) ?>
                                </div>
                                <div class="fc-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><span data-role="bar"></span></div>
                                <div class="fc-ings" data-role="ings"></div>
                                <p class="fc-have small mb-2" data-role="have"></p>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="<?= url('recette', ['id' => $r['id']]) ?>" class="btn btn-primary btn-sm">Voir la recette</a>
                                    <a href="<?= url('courses', ['add' => $r['id'], 'frigo' => 1]) ?>" class="btn btn-outline-primary btn-sm" data-role="shop"><i class="bi bi-cart-plus me-1"></i><span>Liste de courses</span></a>
                                    <a href="<?= url('cuisine', ['id' => $r['id']]) ?>" class="btn btn-outline-primary btn-sm" data-role="cook" hidden><i class="bi bi-fullscreen me-1"></i>Mode cuisine</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="application/json" id="fridgeData"><?= json_encode(array_values($frigoData), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="assets/js/frigo.js" defer></script>
