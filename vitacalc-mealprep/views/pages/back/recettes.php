<div class="page-head">
    <div>
        <h2>Recettes</h2>
        <p class="sub">Ajoutez, modifiez ou supprimez les recettes du site (<?= count($recettes) ?> au total).</p>
    </div>
    <a href="<?= url('admin-recette-create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvelle recette</a>
</div>

<form method="get" action="index.php" class="row g-2 mb-3">
    <input type="hidden" name="page" value="admin-recettes">
    <div class="col-md-6 col-lg-4">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" name="q" value="<?= e($search) ?>" class="form-control border-start-0" placeholder="Rechercher une recette ou un ingrédient…" aria-label="Rechercher">
        </div>
    </div>
    <div class="col-auto">
        <button class="btn btn-outline-primary" type="submit">Rechercher</button>
        <?php if ($search !== ''): ?><a href="<?= url('admin-recettes') ?>" class="btn btn-outline-secondary">Effacer</a><?php endif; ?>
    </div>
</form>

<div class="card-box">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr><th>Recette</th><th>Repas</th><th>Temps</th><th>Portions</th><th>Ingrédients</th><th>Note</th><th>Favoris</th><th class="text-end">Actions</th></tr>
            </thead>
            <tbody>
            <?php if (empty($recettes)): ?>
                <tr><td colspan="8" class="text-center text-muted py-5">Aucune recette trouvée. Cliquez sur « Nouvelle recette » pour commencer 🍽️</td></tr>
            <?php endif; ?>
            <?php foreach ($recettes as $r): ?>
                <tr>
                    <td>
                        <div class="cell-main">
                            <?= recipeCover($r, 'thumb') ?>
                            <div><strong><?= e($r['nom']) ?></strong><small><?= e(mb_strimwidth($r['description'], 0, 70, '…')) ?></small></div>
                        </div>
                    </td>
                    <td><?= momentChip($r['moment'], 'badge-soft') ?></td>
                    <td><span class="badge-soft"><i class="bi bi-clock"></i><?= e(formatDuration((int) $r['tempsPreparation'])) ?></span></td>
                    <td><?= (int) $r['nombrePortions'] ?></td>
                    <td><span class="badge-soft gray"><?= (int) $r['nbIngredients'] ?> ingr.</span></td>
                    <td><?= ratingBadge($r) ?></td>
                    <td><span class="badge-soft rose"><i class="bi bi-heart-fill"></i><?= (int) $r['nbFavoris'] ?></span></td>
                    <td class="text-end">
                        <div class="act">
                            <a href="<?= url('recette', ['id' => $r['id']]) ?>" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-icon" title="Voir sur le site" aria-label="Voir sur le site"><i class="bi bi-eye"></i></a>
                            <a href="<?= url('admin-recette-edit', ['id' => $r['id']]) ?>" class="btn btn-outline-primary btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= url('admin-recette-delete') ?>" data-confirm="Supprimer « <?= e($r['nom']) ?> » ? Ses ingrédients et ses favoris seront aussi supprimés.">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-icon" title="Supprimer" aria-label="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
