<?php /** Formulaire d'ajout / modification. Variables : $conseil, $categories, $erreurs, $csrf. */ ?>
<div class="page-heading">
    <h1><?= $conseil->id ? 'Modifier le conseil' : 'Ajouter un conseil' ?></h1>
</div>

<form class="form-card" method="post" action="admin.php?page=conseil-save">
    <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
    <input type="hidden" name="id" value="<?= (int) $conseil->id ?>">

    <label for="titre">Titre</label>
    <input id="titre" name="titre" maxlength="150" value="<?= h($conseil->titre) ?>" required>
    <?php if (isset($erreurs['titre'])): ?><p class="field-error"><?= h($erreurs['titre']) ?></p><?php endif; ?>

    <label for="id_categorie">Catégorie</label>
    <select id="id_categorie" name="id_categorie" required>
        <option value="">— Choisir —</option>
        <?php foreach ($categories as $k): ?>
            <option value="<?= $k->id ?>"<?= $conseil->idCategorie === $k->id ? ' selected' : '' ?>><?= h($k->nom) ?></option>
        <?php endforeach; ?>
    </select>
    <?php if (isset($erreurs['id_categorie'])): ?><p class="field-error"><?= h($erreurs['id_categorie']) ?></p><?php endif; ?>

    <label for="contenu">Contenu</label>
    <textarea id="contenu" name="contenu" required><?= h($conseil->contenu) ?></textarea>
    <?php if (isset($erreurs['contenu'])): ?><p class="field-error"><?= h($erreurs['contenu']) ?></p><?php endif; ?>

    <label for="date_publication">Date de publication</label>
    <input id="date_publication" type="date" name="date_publication" value="<?= h($conseil->datePublication) ?>" required>
    <?php if (isset($erreurs['date_publication'])): ?><p class="field-error"><?= h($erreurs['date_publication']) ?></p><?php endif; ?>

    <div class="actions">
        <button class="btn btn-primary">Enregistrer</button>
        <a class="btn btn-light" href="admin.php?page=conseils">Annuler</a>
    </div>
</form>
