<?php /** Formulaire d'ajout / modification. Variables : $categorie, $erreurs, $csrf. */ ?>
<div class="page-heading">
    <h1><?= $categorie->id ? 'Modifier la catégorie' : 'Ajouter une catégorie' ?></h1>
</div>

<form class="form-card" method="post" action="admin.php?page=categorie-save">
    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) $categorie->id ?>">

    <label for="nom">Nom</label>
    <input id="nom" name="nom" maxlength="100" value="<?= h($categorie->nom) ?>" required>
    <?php if (isset($erreurs['nom'])): ?><p class="field-error"><?= h($erreurs['nom']) ?></p><?php endif; ?>

    <label for="description">Description</label>
    <textarea id="description" name="description"><?= h($categorie->description) ?></textarea>

    <div class="actions">
        <button class="btn btn-primary">Enregistrer</button>
        <a class="btn btn-light" href="admin.php?page=categories">Annuler</a>
    </div>
</form>
