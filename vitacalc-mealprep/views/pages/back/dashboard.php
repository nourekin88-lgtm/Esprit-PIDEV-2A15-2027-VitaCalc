<?php $admin = currentUser(); ?>
<!-- Bandeau de bienvenue -->
<section class="welcome">
    <span class="blob blob-a"></span>
    <div class="position-relative">
        <h2>Bonjour <?= e($admin['prenom']) ?> 👋</h2>
        <p class="mb-3">Voici l'activité du module <strong>Meal Prep</strong>. Tout ce que vous ajoutez ici apparaît automatiquement sur le site.</p>
        <a href="<?= url('admin-recette-create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle recette</a>
        <a href="<?= url('home') ?>" target="_blank" rel="noopener" class="btn btn-outline-light ms-1"><i class="bi bi-box-arrow-up-right me-1"></i>Voir le site</a>
    </div>
</section>

<!-- Statistiques -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="stat-card"><div class="ico"><i class="bi bi-journal-richtext"></i></div>
        <div><div class="num"><?= (int) $nbRecettes ?></div><div class="lbl">Recettes</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card warm"><div class="ico"><i class="bi bi-basket2"></i></div>
        <div><div class="num"><?= (int) $nbIngredients ?></div><div class="lbl">Ingrédients au catalogue</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card dark"><div class="ico"><i class="bi bi-people"></i></div>
        <div><div class="num"><?= (int) $nbClients ?></div><div class="lbl">Clients inscrits</div></div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="stat-card rose"><div class="ico"><i class="bi bi-heart-fill"></i></div>
        <div><div class="num"><?= (int) $nbFavoris ?></div><div class="lbl">Favoris enregistrés</div></div></div></div>
</div>

<div class="row g-4 mb-4">
    <!-- Recettes les mieux notées -->
    <div class="col-12">
        <div class="card-box">
            <div class="card-head"><h5><i class="bi bi-star-fill text-primary me-2"></i>Recettes les mieux notées <span class="text-muted fw-normal fs-6">· <?= (int) $nbAvis ?> avis au total</span></h5></div>
            <div class="card-body-pad">
                <?php if (empty($meilleures)): ?>
                    <p class="text-muted mb-0">Aucun avis pour le moment : les notes des clients apparaîtront ici.</p>
                <?php else: ?>
                    <ul class="bar-list">
                        <?php foreach ($meilleures as $r): ?>
                            <li>
                                <span class="name"><?= e($r['nom']) ?></span>
                                <span class="val"><?= e(number_format((float) $r['noteMoyenne'], 1, ',', '')) ?> <i class="bi bi-star-fill"></i> <small class="text-muted">(<?= (int) $r['nbAvis'] ?>)</small></span>
                                <span class="track"><span class="fill" style="width: <?= (int) round($r['noteMoyenne'] / 5 * 100) ?>%"></span></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Recettes les plus aimées -->
    <div class="col-lg-7">
        <div class="card-box h-100">
            <div class="card-head"><h5><i class="bi bi-bar-chart text-primary me-2"></i>Recettes les plus aimées</h5></div>
            <div class="card-body-pad">
                <?php if (empty($top)): ?>
                    <p class="text-muted mb-0">Aucune recette pour le moment.</p>
                <?php else: ?>
                    <ul class="bar-list">
                        <?php foreach ($top as $r): ?>
                            <li>
                                <span class="name"><?= e($r['nom']) ?></span>
                                <span class="val"><?= (int) $r['nbFavoris'] ?> <i class="bi bi-heart-fill"></i></span>
                                <span class="track"><span class="fill" style="width: <?= (int) round($r['nbFavoris'] / $maxFav * 100) ?>%"></span></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="col-lg-5">
        <div class="card-box h-100">
            <div class="card-head"><h5><i class="bi bi-lightning-charge text-primary me-2"></i>Actions rapides</h5></div>
            <div class="card-body-pad">
                <a class="quick" href="<?= url('admin-recette-create') ?>"><span class="q-ico"><i class="bi bi-journal-plus"></i></span><span><strong>Composer une recette</strong><small>Étapes, photo et ingrédients</small></span></a>
                <a class="quick" href="<?= url('admin-ingredient-create') ?>"><span class="q-ico"><i class="bi bi-plus-circle"></i></span><span><strong>Ajouter un ingrédient</strong><small>Compléter le catalogue</small></span></a>
                <a class="quick" href="<?= url('admin-recettes') ?>"><span class="q-ico"><i class="bi bi-pencil-square"></i></span><span><strong>Gérer les recettes</strong><small>Modifier ou supprimer</small></span></a>
            </div>
        </div>
    </div>
</div>

<!-- Dernières recettes -->
<div class="card-box">
    <div class="card-head">
        <h5><i class="bi bi-clock-history text-primary me-2"></i>Dernières recettes</h5>
        <a href="<?= url('admin-recettes') ?>" class="btn btn-sm btn-outline-primary">Tout voir</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Recette</th><th>Temps</th><th>Portions</th><th>Ingrédients</th><th>Favoris</th></tr></thead>
            <tbody>
            <?php if (empty($dernieres)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Aucune recette pour le moment.</td></tr>
            <?php endif; ?>
            <?php foreach ($dernieres as $r): ?>
                <tr>
                    <td><div class="cell-main"><?= recipeCover($r, 'thumb') ?><strong><?= e($r['nom']) ?></strong></div></td>
                    <td><span class="badge-soft"><i class="bi bi-clock"></i><?= e(formatDuration((int) $r['tempsPreparation'])) ?></span></td>
                    <td><?= (int) $r['nombrePortions'] ?></td>
                    <td><span class="badge-soft gray"><?= (int) $r['nbIngredients'] ?></span></td>
                    <td><span class="badge-soft rose"><i class="bi bi-heart-fill"></i><?= (int) $r['nbFavoris'] ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
