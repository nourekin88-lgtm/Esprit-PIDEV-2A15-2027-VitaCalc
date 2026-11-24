<a class="back-link" href="<?= e(route($currentPage)) ?>"><?= icon('back', 17) ?> <?= e($meta['title']) ?></a>
<header class="page-heading">
    <h1><?= e($title) ?></h1>
    <p>Le nom et la description apparaîtront dans le catalogue.</p>
</header>
<form class="form-panel" method="post" action="<?= e(route($collection . ($editing ? '-modifier' : '-ajouter'), $editing ? ['id' => $record['id']] : [])) ?>">
    <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
    <?php if ($errors !== []): ?>
        <p class="notice error" role="alert"><?= icon('alert', 18) ?> Corrigez les champs signalés.</p>
    <?php endif; ?>
    <div class="form-field">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" required maxlength="120" value="<?= e($record['nom']) ?>"
            <?= isset($errors['nom']) ? 'aria-invalid="true" aria-describedby="nom-erreur"' : '' ?>>
        <?php if (isset($errors['nom'])): ?><p class="field-error" id="nom-erreur"><?= e($errors['nom']) ?></p><?php endif; ?>
    </div>
    <div class="form-field">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="9" required maxlength="4000" data-counter="description-limite"
            <?= isset($errors['description']) ? 'aria-invalid="true"' : '' ?>
            aria-describedby="description-limite<?= isset($errors['description']) ? ' description-erreur' : '' ?>"><?= e($record['description']) ?></textarea>
        <p class="form-hint" id="description-limite">4 000 caractères maximum</p>
        <?php if (isset($errors['description'])): ?><p class="field-error" id="description-erreur"><?= e($errors['description']) ?></p><?php endif; ?>
    </div>
    <div class="form-actions">
        <button class="button button-primary" type="submit"><?= icon('check', 18) ?> <?= $editing ? 'Enregistrer' : 'Publier la fiche' ?></button>
        <a class="button button-secondary" href="<?= e(route($currentPage)) ?>">Annuler</a>
    </div>
</form>
