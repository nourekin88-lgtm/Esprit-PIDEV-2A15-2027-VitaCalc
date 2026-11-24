<?php $u = currentUser(); ?>

<!-- ===== HERO ===== -->
<section class="hero">
    <span class="blob blob-a"></span><span class="blob blob-b"></span>
    <span class="plus plus-1">+</span><span class="plus plus-2">+</span><span class="plus plus-3">+</span>
    <div class="container position-relative">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <?php if ($u): ?>
                    <span class="eyebrow-pill"><i class="bi bi-stars me-1"></i>Content de vous revoir</span>
                    <h1 class="hero-title">Bonjour <span class="hl"><?= e($u['prenom']) ?></span> 👋<br>Que cuisine-t-on cette semaine ?</h1>
                    <p class="hero-lead">Choisissez vos recettes, ajustez les portions et laissez VitaCalc préparer votre liste de courses.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= url('recettes') ?>" class="btn btn-primary btn-lg">Parcourir les recettes <i class="bi bi-arrow-right ms-1"></i></a>
                        <a href="<?= url('courses') ?>" class="btn btn-outline-primary btn-lg bg-white"><i class="bi bi-cart-check me-1"></i>Ma liste de courses</a>
                    </div>
                <?php else: ?>
                    <span class="eyebrow-pill"><i class="bi bi-heart-pulse me-1"></i>Your Health, Your Balance.</span>
                    <h1 class="hero-title">Mangez sain toute la semaine, <span class="hl">sans stress</span>.</h1>
                    <p class="hero-lead">Le <strong>meal prep</strong>, c'est préparer ses repas à l'avance. Découvrez des recettes healthy, leurs ingrédients et le temps nécessaire pour cuisiner une seule fois et bien manger tous les jours.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= url('register') ?>" class="btn btn-primary btn-lg">Créer mon compte gratuit <i class="bi bi-arrow-right ms-1"></i></a>
                        <a href="<?= url('recettes') ?>" class="btn btn-outline-primary btn-lg bg-white">Découvrir les recettes</a>
                    </div>
                <?php endif; ?>

                <div class="hero-stats">
                    <div><div class="num"><?= (int) $nbRecettes ?></div><div class="lbl">recettes healthy</div></div>
                    <div><div class="num"><?= (int) $nbIngredients ?></div><div class="lbl">ingrédients</div></div>
                    <div><div class="num">100%</div><div class="lbl">fait maison</div></div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-photo">
                    <img src="assets/images/recettes/bowl-poulet-quinoa.jpg" alt="Bowl poulet et quinoa">
                    <span class="float-chip c1"><i class="bi bi-clock"></i> 35 min</span>
                    <span class="float-chip c2"><i class="bi bi-people"></i> 4 portions</span>
                    <span class="float-chip c3"><i class="bi bi-lightning-charge"></i> Riche en protéines</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===== COMMENT ÇA MARCHE ===== -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow-pill">Simple &amp; efficace</span>
            <h2>Le meal prep en 3 étapes</h2>
            <p>Une ou deux heures de préparation et vos repas de la semaine sont prêts.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4"><div class="step-card"><span class="step-no">1</span>
                <div class="step-ico"><i class="bi bi-journal-richtext"></i></div><h5>Choisissez vos recettes</h5>
                <p>Parcourez les recettes : temps de préparation, portions et ingrédients d'un coup d'œil.</p></div></div>
            <div class="col-md-4"><div class="step-card"><span class="step-no">2</span>
                <div class="step-ico"><i class="bi bi-cart-check"></i></div><h5>Faites vos courses</h5>
                <p>Ajustez les portions : les quantités se recalculent et la liste de courses se génère seule.</p></div></div>
            <div class="col-md-4"><div class="step-card"><span class="step-no">3</span>
                <div class="step-ico"><i class="bi bi-box-seam"></i></div><h5>Cuisinez &amp; conservez</h5>
                <p>Suivez les étapes, rangez vos repas dans des boîtes et profitez toute la semaine.</p></div></div>
        </div>
    </div>
</section>

<!-- ===== PAR MOMENT DE LA JOURNÉE ===== -->
<section class="section section-tint">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow-pill"><i class="bi bi-calendar-day me-1"></i>Toute la journée</span>
            <h2>Des recettes pour chaque repas</h2>
            <p>Petit-déjeuner, déjeuner ou dîner : choisissez le moment, on s'occupe du reste.</p>
        </div>
        <div class="row g-4">
            <?php foreach (\App\Models\Recette::MOMENTS as $key => [$mLabel, $mIcon, $mEmoji, $mSub]): ?>
                <div class="col-md-4">
                    <a href="<?= url('recettes', ['moment' => $key]) ?>" class="moment-tile moment-<?= e($key) ?>">
                        <span class="moment-ico"><i class="bi <?= e($mIcon) ?>"></i></span>
                        <h5><?= e($mLabel) ?> <span class="moment-emoji"><?= $mEmoji ?></span></h5>
                        <p><?= e($mSub) ?></p>
                        <span class="moment-count"><?php if ($u): ?><?= (int) ($parMoment[$key] ?? 0) ?> recette<?= ($parMoment[$key] ?? 0) > 1 ? 's' : '' ?><?php else: ?>Découvrir<?php endif; ?> <i class="bi bi-arrow-right ms-1"></i></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($u): ?>
<!-- ===== FRIGO (client) ===== -->
<section class="section pb-0">
    <div class="container">
        <a href="<?= url('frigo') ?>" class="fridge-banner">
            <span class="fb-ico"><i class="bi bi-snow2"></i></span>
            <span class="fb-txt"><strong>Qu'est-ce que j'ai dans mon frigo ?</strong><small>Cochez vos ingrédients : on vous propose les recettes les plus proches, avec le % d'ingrédients que vous avez déjà.</small></span>
            <i class="bi bi-arrow-right fb-arrow"></i>
        </a>
    </div>
</section>
<?php endif; ?>

<?php if ($u && $favoris): ?>
<!-- ===== MES FAVORIS (client) ===== -->
<section class="section section-tint">
    <div class="container">
        <div class="section-head left">
            <span class="eyebrow-pill"><i class="bi bi-heart-fill me-1"></i>Pour vous</span>
            <h2>Vos recettes favorites</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($favoris as $r): ?>
                <div class="col-lg-4 col-md-6"><?php include BASE_PATH . '/views/includes/recipe_card.php'; ?></div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4"><a href="<?= url('favoris') ?>" class="btn btn-outline-primary bg-white">Tous mes favoris</a></div>
    </div>
</section>
<?php endif; ?>

<!-- ===== RECETTES RÉCENTES ===== -->
<section class="section <?= ($u && !$favoris) ? 'section-tint' : '' ?>">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow-pill">Nos recettes</span>
            <h2>Les dernières recettes ajoutées</h2>
            <p>Des idées saines, rapides et faciles à préparer à l'avance.</p>
        </div>
        <?php if (empty($recettes)): ?>
            <div class="panel text-center text-muted">Aucune recette pour le moment. Revenez bientôt ! 🍽️</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($recettes as $r): ?>
                    <div class="col-lg-4 col-md-6"><?php include BASE_PATH . '/views/includes/recipe_card.php'; ?></div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5"><a href="<?= url('recettes') ?>" class="btn btn-primary btn-lg px-5">Voir toutes les recettes</a></div>
        <?php endif; ?>
    </div>
</section>

<?php if ($u && $rapides): ?>
<!-- ===== RAPIDES (client) ===== -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow-pill"><i class="bi bi-lightning-charge-fill me-1"></i>Pressé ?</span>
            <h2>Prêtes en 15 minutes ou moins</h2>
        </div>
        <div class="row g-4">
            <?php foreach ($rapides as $r): ?>
                <div class="col-lg-4 col-md-6"><?php include BASE_PATH . '/views/includes/recipe_card.php'; ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!$u): ?>
<!-- ===== DÉBLOQUER (visiteur) ===== -->
<section class="section">
    <div class="container">
        <div class="unlock">
            <span class="blob blob-a"></span>
            <div class="row align-items-center g-4 position-relative">
                <div class="col-lg-7">
                    <span class="eyebrow-pill on-dark"><i class="bi bi-unlock me-1"></i>Gratuit</span>
                    <h2>Créez votre compte et débloquez tout VitaCalc</h2>
                    <ul class="unlock-list">
                        <li><i class="bi bi-check-circle-fill"></i>Les recettes complètes : ingrédients, quantités et étapes</li>
                        <li><i class="bi bi-check-circle-fill"></i>Le calcul automatique des portions</li>
                        <li><i class="bi bi-check-circle-fill"></i>Vos favoris et votre liste de courses de la semaine</li>
                    </ul>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <a href="<?= url('register') ?>" class="btn btn-light btn-lg px-5 text-primary fw-bold">Je m'inscris</a>
                    <p class="mt-3 mb-0 small">Déjà inscrit ? <a href="<?= url('login') ?>" class="text-white fw-semibold">Se connecter</a></p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ===== INGRÉDIENTS ===== -->
<?php if (!empty($populaires)): ?>
<section class="section <?= $u ? 'section-tint' : '' ?>">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow-pill">Au menu</span>
            <h2>Les ingrédients de nos recettes</h2>
            <p>Des produits simples et frais, faciles à trouver.</p>
        </div>
        <div class="ing-strip">
            <?php foreach ($populaires as $ing): ?>
                <div class="ing-pill">
                    <?= ingredientThumb($ing, 'lg') ?>
                    <span><?= e($ing['nom']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if ($u): ?>
            <div class="text-center mt-4"><a href="<?= url('ingredients') ?>" class="btn btn-outline-primary bg-white">Voir tous les ingrédients</a></div>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<!-- ===== AVANTAGES ===== -->
<section class="section">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow-pill">Les avantages</span>
            <h2>Pourquoi faire du meal prep ?</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4"><div class="benefit"><div class="ico"><i class="bi bi-hourglass-split"></i></div><div><h6>Gain de temps</h6><p>Vous cuisinez une fois, vous mangez toute la semaine.</p></div></div></div>
            <div class="col-md-4"><div class="benefit"><div class="ico"><i class="bi bi-heart-pulse"></i></div><div><h6>Repas équilibrés</h6><p>Vous contrôlez les ingrédients et les quantités de vos repas.</p></div></div></div>
            <div class="col-md-4"><div class="benefit"><div class="ico"><i class="bi bi-recycle"></i></div><div><h6>Moins de gaspillage</h6><p>Des courses planifiées : vous achetez uniquement ce qu'il faut.</p></div></div></div>
        </div>
    </div>
</section>
