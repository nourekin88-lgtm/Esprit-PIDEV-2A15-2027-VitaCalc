<section class="page-hero">
    <span class="blob blob-a"></span><span class="plus plus-1">+</span><span class="plus plus-2">+</span>
    <div class="container position-relative text-center">
        <span class="eyebrow-pill"><i class="bi bi-cart-check me-1"></i>Meal Prep</span>
        <h1>Ma liste de courses</h1>
        <p class="mb-0 text-muted-2">Choisissez vos recettes de la semaine : VitaCalc additionne les ingrédients pour vous.</p>
    </div>
</section>

<section class="section pt-4">
    <div class="container">
        <div id="courses-app" data-preselect="<?= (int) $preselect ?>" data-fridge-key="vc_fridge_u<?= (int) currentUser()['id'] ?>" data-use-fridge="<?= !empty($_GET['frigo']) ? '1' : '0' ?>">
            <div class="row g-4">
                <!-- 1. Choix des recettes -->
                <div class="col-lg-6">
                    <div class="panel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0"><span class="num-dot">1</span> Mes recettes</h4>
                            <?php if ($mesFavoris): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnFavoris"><i class="bi bi-heart me-1"></i>Mes favoris</button>
                            <?php endif; ?>
                        </div>
                        <div class="pick-list">
                            <?php foreach ($recettes as $r): ?>
                                <div class="pick-row" data-id="<?= (int) $r['id'] ?>" data-base="<?= (int) $r['nombrePortions'] ?>" data-fav="<?= in_array($r['id'], $mesFavoris, true) ? '1' : '0' ?>">
                                    <label class="pick-check">
                                        <input type="checkbox" class="pick-box" aria-label="Choisir <?= e($r['nom']) ?>">
                                        <span class="box"><i class="bi bi-check-lg"></i></span>
                                    </label>
                                    <?= recipeCover($r, 'thumb') ?>
                                    <div class="pick-info">
                                        <strong><?= e($r['nom']) ?></strong>
                                        <small class="text-muted"><i class="bi bi-clock me-1"></i><?= e(formatDuration((int) $r['tempsPreparation'])) ?>
                                            <?php if (in_array($r['id'], $mesFavoris, true)): ?><i class="bi bi-heart-fill text-primary ms-2"></i><?php endif; ?></small>
                                    </div>
                                    <div class="portion-ctrl sm">
                                        <button type="button" class="p-minus" aria-label="Moins">−</button>
                                        <span class="val"><span class="p-val"><?= (int) $r['nombrePortions'] ?></span> p.</span>
                                        <button type="button" class="p-plus" aria-label="Plus">+</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 2. Liste -->
                <div class="col-lg-6">
                    <div class="panel sticky-panel list-panel" id="listPanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0"><span class="num-dot">2</span> À acheter</h4>
                            <span class="chip" id="countChip">0 article</span>
                        </div>
                        <label class="fridge-toggle no-print" id="fridgeToggle" hidden>
                            <input type="checkbox" id="useFridge">
                            <span class="sw"></span>
                            <span class="tx"><i class="bi bi-snow2 me-1"></i>Retirer ce que j'ai déjà dans mon frigo <b id="fridgeToggleN"></b></span>
                        </label>
                        <div id="emptyState" class="text-center text-muted py-4">
                            <div class="empty-ico"><i class="bi bi-cart"></i></div>
                            <p class="mt-3 mb-0">Cochez une ou plusieurs recettes<br>pour générer votre liste.</p>
                        </div>
                        <ul class="shop-list" id="shopList"></ul>
                        <div class="d-flex flex-wrap gap-2 mt-3 no-print" id="listActions" hidden>
                            <button type="button" class="btn btn-primary" id="btnCopy"><i class="bi bi-clipboard me-1"></i>Copier</button>
                            <button type="button" class="btn btn-outline-primary" id="btnPrint"><i class="bi bi-printer me-1"></i>Imprimer</button>
                            <button type="button" class="btn btn-outline-secondary ms-auto" id="btnReset"><i class="bi bi-arrow-counterclockwise me-1"></i>Vider</button>
                        </div>
                        <div class="small text-muted mt-3 no-print" id="copyMsg" aria-live="polite"></div>
                    </div>
                </div>
            </div>
        </div>
        <script type="application/json" id="courses-data"><?= json_encode($coursesData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
    </div>
</section>
