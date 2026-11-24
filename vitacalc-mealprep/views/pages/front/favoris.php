<section class="page-hero">
    <span class="blob blob-a"></span><span class="plus plus-1">+</span><span class="plus plus-2">+</span>
    <div class="container position-relative text-center">
        <span class="eyebrow-pill"><i class="bi bi-heart-fill me-1"></i>Mon espace</span>
        <h1>Mes recettes favorites</h1>
        <p class="mb-0 text-muted-2">Retrouvez en un clic les recettes que vous aimez.</p>
    </div>
</section>

<section class="section pt-4">
    <div class="container">
        <?php if (empty($recettes)): ?>
            <div class="panel text-center py-5">
                <div class="empty-ico"><i class="bi bi-heart"></i></div>
                <h5 class="mt-3">Vous n'avez pas encore de favoris</h5>
                <p class="text-muted">Cliquez sur le cœur d'une recette pour la retrouver ici.</p>
                <a href="<?= url('recettes') ?>" class="btn btn-primary">Découvrir les recettes</a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($recettes as $r): ?>
                    <div class="col-lg-4 col-md-6"><?php include BASE_PATH . '/views/includes/recipe_card.php'; ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
