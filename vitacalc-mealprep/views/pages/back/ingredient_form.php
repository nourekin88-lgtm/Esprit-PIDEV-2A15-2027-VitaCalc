<?php $isEdit = $mode === 'edit'; ?>
<div class="page-head">
    <div>
        <h2><?= $isEdit ? "Modifier l'ingrédient" : 'Nouvel ingrédient' ?></h2>
        <p class="sub"><a href="<?= url('admin-ingredients') ?>" class="text-primary"><i class="bi bi-arrow-left me-1"></i>Retour aux ingrédients</a></p>
    </div>
</div>

<form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" novalidate>
    <?= csrfField() ?>
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card-box"><div class="card-body-pad">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom de l'ingrédient *</label>
                    <input type="text" id="nom" name="nom" maxlength="100" placeholder="Ex : Brocoli"
                           class="form-control <?= isset($errors['nom']) ? 'is-invalid' : '' ?>" value="<?= e(old($ingredient, 'nom')) ?>" autofocus>
                    <div class="invalid-feedback"><?= e($errors['nom'] ?? '') ?></div>
                </div>
                <div class="hint"><i class="bi bi-info-circle me-1"></i>La <strong>quantité</strong> et l'<strong>unité</strong> se choisissent dans chaque recette (« Composer une recette »).</div>
            </div></div>
        </div>
        <div class="col-lg-5">
            <div class="card-box"><div class="card-body-pad">
                <h5 class="mb-3">Photo <span class="text-muted fw-normal fs-6">(facultatif)</span></h5>
                <div class="photo-preview round mb-3 mx-auto" id="photoPreview">
                    <?php if (!empty($ingredient['image'])): ?>
                        <img src="<?= e($ingredient['image']) ?>" alt="Aperçu" onerror="this.remove()">
                    <?php else: ?><i class="bi bi-image fs-2"></i><?php endif; ?>
                </div>
                <input type="file" name="imageFile" accept="image/*" data-preview="#photoPreview"
                       class="form-control mb-2 <?= isset($errors['imageFile']) ? 'is-invalid' : '' ?>">
                <div class="invalid-feedback"><?= e($errors['imageFile'] ?? '') ?></div>
                <input type="text" name="image" placeholder="ou coller un lien https://…"
                       class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" value="<?= e(old($ingredient, 'image')) ?>">
                <div class="invalid-feedback"><?= e($errors['image'] ?? '') ?></div>
                <div class="form-text">Sans photo, le site en choisit une selon le nom (ex : Brocoli, Lait…).</div>
            </div></div>
        </div>
    </div>
    <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary btn-lg px-4"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
        <a href="<?= url('admin-ingredients') ?>" class="btn btn-outline-secondary btn-lg">Annuler</a>
    </div>
</form>
