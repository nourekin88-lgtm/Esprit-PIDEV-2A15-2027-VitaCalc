<section class="page-hero">
    <span class="blob blob-a"></span><span class="plus plus-1">+</span><span class="plus plus-2">+</span>
    <div class="container position-relative text-center">
        <span class="eyebrow-pill">Catalogue</span>
        <h1>Nos ingrédients</h1>
        <p class="mb-0 text-muted-2">Tout ce qu'il faut pour cuisiner vos recettes de meal prep.</p>
    </div>
</section>

<section class="section pt-4">
    <div class="container">
        <?php if (empty($ingredients)): ?>
            <div class="panel text-center py-5">
                <div class="empty-ico"><i class="bi bi-basket2"></i></div>
                <h5 class="mt-3">Aucun ingrédient pour le moment</h5>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($ingredients as $ing): ?>
                    <div class="col-xl-3 col-lg-4 col-6">
                        <div class="ing-card">
                            <?= ingredientThumb($ing, 'xl') ?>
                            <h6><?= e($ing['nom']) ?></h6>
                            <small class="text-muted">
                                <?= $ing['nbRecettes'] > 0 ? 'Dans ' . (int) $ing['nbRecettes'] . ' recette' . ($ing['nbRecettes'] > 1 ? 's' : '') : 'Pas encore utilisé' ?>
                            </small>
                            <div class="mt-2 d-flex flex-wrap justify-content-center gap-1">
                                <?php foreach (array_slice($ing['recettes'], 0, 2, true) as $rid => $rnom): ?>
                                    <a class="chip" href="<?= url('recette', ['id' => $rid]) ?>"><?= e(mb_strimwidth($rnom, 0, 22, '…')) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
