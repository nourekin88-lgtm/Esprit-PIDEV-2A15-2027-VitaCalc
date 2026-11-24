<?php
$isEdit = $mode === 'edit';
$lineErrors = $errors['lignes'] ?? [];
if (empty($rows)) {
    $rows = [['nom' => '', 'quantite' => '', 'unite' => '']];
}
/** Une ligne d'ingrédient (aussi utilisée pour le modèle JavaScript). */
$ingRow = function (array $row) use ($unites): string {
    $opts = '<option value="">Unité</option>';
    foreach ($unites as $u) {
        $opts .= '<option value="' . e($u) . '"' . (($row['unite'] ?? '') === $u ? ' selected' : '') . '>' . e($u) . '</option>';
    }
    return '<div class="ing-row">'
        . '<div class="col-name"><input type="text" name="ing_nom[]" class="form-control" list="catalogue-list" maxlength="100" placeholder="Ex : Quinoa" value="' . e($row['nom'] ?? '') . '" aria-label="Nom de l\'ingrédient"></div>'
        . '<input type="text" name="ing_quantite[]" inputmode="decimal" class="form-control" placeholder="Quantité" value="' . e($row['quantite'] ?? '') . '" aria-label="Quantité">'
        . '<select name="ing_unite[]" class="form-select" aria-label="Unité">' . $opts . '</select>'
        . '<button type="button" class="btn btn-outline-danger btn-remove" title="Retirer cet ingrédient" aria-label="Retirer cet ingrédient"><i class="bi bi-x-lg"></i></button>'
        . '</div>';
};
?>
<div class="page-head">
    <div>
        <h2><?= $isEdit ? 'Modifier la recette' : 'Nouvelle recette' ?></h2>
        <p class="sub"><a href="<?= url('admin-recettes') ?>" class="text-primary"><i class="bi bi-arrow-left me-1"></i>Retour aux recettes</a></p>
    </div>
</div>

<?php if (!$isEdit): ?>
    <div class="hint mb-4"><i class="bi bi-lightbulb me-1"></i>Une recette contient <strong>au moins un ingrédient</strong> : composez-la directement ci-dessous.</div>
<?php endif; ?>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
    <?= csrfField() ?>
    <div class="row g-4">
        <!-- Informations -->
        <div class="col-lg-7">
            <div class="card-box"><div class="card-body-pad">
                <h5 class="mb-3">Informations</h5>
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom de la recette *</label>
                    <input type="text" id="nom" name="nom" maxlength="150" placeholder="Ex : Bowl poulet & quinoa"
                           class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>" value="<?= e(old($recette, 'nom')) ?>">
                    <div class="invalid-feedback"><?= e($errors['nom'] ?? '') ?></div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description courte *</label>
                    <textarea id="description" name="description" rows="2" placeholder="Une phrase qui donne envie…"
                              class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"><?= e(old($recette, 'description')) ?></textarea>
                    <div class="invalid-feedback"><?= e($errors['description'] ?? '') ?></div>
                </div>
                <div class="mb-3">
                    <label for="instructions" class="form-label">Étapes de préparation *</label>
                    <textarea id="instructions" name="instructions" rows="7" placeholder="Écrivez une étape par ligne :&#10;Cuire le riz.&#10;Couper les légumes."
                              class="form-control <?= isset($errors['instructions']) ? 'is-invalid' : '' ?>"><?= e(old($recette, 'instructions')) ?></textarea>
                    <div class="form-text">Une étape par ligne : elles seront numérotées automatiquement sur le site. Écrivez la durée dans l'étape (ex : « Cuire 30 minutes ») : le <strong>mode cuisine</strong> proposera un minuteur automatiquement.</div>
                    <div class="invalid-feedback"><?= e($errors['instructions'] ?? '') ?></div>
                </div>
                <div class="mb-3">
                    <label for="moment" class="form-label">Moment du repas *</label>
                    <select id="moment" name="moment" class="form-select <?= isset($errors['moment']) ? 'is-invalid' : '' ?>">
                        <?php $momentSel = old($recette, 'moment', 'dejeuner'); ?>
                        <?php foreach (\App\Models\Recette::MOMENTS as $mKey => [$mLabel, , $mEmoji]): ?>
                            <option value="<?= e($mKey) ?>" <?= $momentSel === $mKey ? 'selected' : '' ?>><?= $mEmoji ?> <?= e($mLabel) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback"><?= e($errors['moment'] ?? '') ?></div>
                    <div class="form-text">La recette sera classée dans « Petit-déjeuner », « Déjeuner » ou « Dîner » sur le site.</div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label for="tempsPreparation" class="form-label">Temps de préparation (minutes) *</label>
                        <input type="number" min="1" id="tempsPreparation" name="tempsPreparation"
                               class="form-control <?= isset($errors['tempsPreparation']) ? 'is-invalid' : '' ?>" value="<?= e(old($recette, 'tempsPreparation')) ?>">
                        <div class="invalid-feedback"><?= e($errors['tempsPreparation'] ?? '') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label for="nombrePortions" class="form-label">Nombre de portions *</label>
                        <input type="number" min="1" id="nombrePortions" name="nombrePortions"
                               class="form-control <?= isset($errors['nombrePortions']) ? 'is-invalid' : '' ?>" value="<?= e(old($recette, 'nombrePortions')) ?>">
                        <div class="invalid-feedback"><?= e($errors['nombrePortions'] ?? '') ?></div>
                    </div>
                </div>
            </div></div>
        </div>

        <!-- Photo -->
        <div class="col-lg-5">
            <div class="card-box"><div class="card-body-pad">
                <h5 class="mb-3">Photo <span class="text-muted fw-normal fs-6">(facultatif)</span></h5>
                <div class="photo-preview mb-3" id="photoPreview">
                    <?php if (!empty($recette['image'])): ?>
                        <img src="<?= e($recette['image']) ?>" alt="Aperçu" onerror="this.remove()">
                    <?php else: ?><span><i class="bi bi-card-image me-1"></i>Aperçu de la photo</span><?php endif; ?>
                </div>
                <label for="imageFile" class="form-label">Choisir une photo sur votre ordinateur</label>
                <input type="file" name="imageFile" id="imageFile" accept="image/*" data-preview="#photoPreview"
                       class="form-control <?= isset($errors['imageFile']) ? 'is-invalid' : '' ?>">
                <div class="invalid-feedback"><?= e($errors['imageFile'] ?? '') ?></div>
                <div class="form-text mb-3">JPG, PNG ou WEBP · 3 Mo maximum.</div>
                <label for="image" class="form-label">…ou coller un lien</label>
                <input type="text" id="image" name="image" placeholder="https://…"
                       class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" value="<?= e(old($recette, 'image')) ?>">
                <div class="invalid-feedback"><?= e($errors['image'] ?? '') ?></div>
                <div class="form-text">Sans photo, le site en propose une automatiquement.</div>
            </div></div>
        </div>

        <!-- Ingrédients (Composer une recette) -->
        <div class="col-12">
            <div class="card-box"><div class="card-body-pad">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <h5 class="mb-0">Ingrédients de la recette *</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addRow"><i class="bi bi-plus-lg me-1"></i>Ajouter un ingrédient</button>
                </div>

                <?php if ($lineErrors): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3"><?php foreach ($lineErrors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>

                <div class="ing-head"><span>Ingrédient</span><span>Quantité</span><span>Unité</span><span></span></div>
                <div class="ing-rows" id="ingRows">
                    <?php foreach ($rows as $row): ?><?= $ingRow($row) ?><?php endforeach; ?>
                </div>
                <div class="form-text mt-2">Tapez le nom : les ingrédients déjà dans le catalogue sont proposés. Un nouvel ingrédient est créé automatiquement.</div>

                <datalist id="catalogue-list">
                    <?php foreach ($catalogue as $c): ?><option value="<?= e($c['nom']) ?>"><?php endforeach; ?>
                </datalist>
                <template id="ingRowTpl"><?= $ingRow([]) ?></template>
            </div></div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary btn-lg px-4"><i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Enregistrer les modifications' : 'Créer la recette' ?></button>
        <a href="<?= url('admin-recettes') ?>" class="btn btn-outline-secondary btn-lg">Annuler</a>
    </div>
</form>
