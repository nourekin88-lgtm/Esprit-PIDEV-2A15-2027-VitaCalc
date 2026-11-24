<div class="page-head">
    <div>
        <h2>Ingrédients</h2>
        <p class="sub">Catalogue des ingrédients (<?= count($ingredients) ?>). Les quantités se définissent dans chaque recette.</p>
    </div>
    <a href="<?= url('admin-ingredient-create') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Nouvel ingrédient</a>
</div>

<div class="row g-2 mb-3">
    <div class="col-md-6 col-lg-4">
        <div class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control border-start-0" placeholder="Filtrer les ingrédients…" aria-label="Filtrer"
                   data-filter="#ingTable" data-filter-empty="#ingNone">
        </div>
    </div>
</div>

<div class="card-box">
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="ingTable">
            <thead><tr><th>Ingrédient</th><th>Utilisé dans</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($ingredients as $i): ?>
                <tr data-name="<?= e(mb_strtolower($i['nom'])) ?>">
                    <td><div class="cell-main"><?= ingredientThumb($i, 'md') ?><strong><?= e($i['nom']) ?></strong></div></td>
                    <td>
                        <?php if ($i['nbRecettes'] > 0): ?>
                            <span class="badge-soft"><?= (int) $i['nbRecettes'] ?> recette<?= $i['nbRecettes'] > 1 ? 's' : '' ?></span>
                        <?php else: ?>
                            <span class="badge-soft gray">Pas encore utilisé</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <div class="act">
                            <a href="<?= url('admin-ingredient-edit', ['id' => $i['id']]) ?>" class="btn btn-outline-primary btn-icon" title="Modifier" aria-label="Modifier"><i class="bi bi-pencil"></i></a>
                            <form method="post" action="<?= url('admin-ingredient-delete') ?>" data-confirm="Supprimer l'ingrédient « <?= e($i['nom']) ?> » ?">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= (int) $i['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-icon" title="Supprimer" aria-label="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="ingNone" class="text-center text-muted py-5" <?= empty($ingredients) ? '' : 'hidden' ?>>Aucun ingrédient trouvé 🧂</div>
</div>
