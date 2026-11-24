<?php /** Liste des conseils. Variables : $conseils, $categories, $filtre, $csrf. */ ?>
<div class="page-heading">
    <h1>Gestion des conseils</h1>
    <p>Ajoutez, modifiez ou supprimez les conseils affichés sur le site.</p>
</div>

<section class="panel">
    <div class="toolbar">
        <form method="get" action="admin.php">
            <input type="hidden" name="page" value="conseils">
            <select name="categorie" onchange="this.form.submit()" aria-label="Filtrer par catégorie">
                <option value="">Toutes les catégories</option>
                <?php foreach ($categories as $k): ?>
                    <option value="<?= $k->id ?>"<?= $filtre === $k->id ? ' selected' : '' ?>><?= h($k->nom) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <a class="btn btn-primary" href="admin.php?page=conseil-form">+ Ajouter un conseil</a>
    </div>

    <?php if (!$conseils): ?>
        <p class="empty">Aucun conseil<?= $filtre ? ' dans cette catégorie' : '' ?>.</p>
    <?php else: ?>
    <table class="table-dashboard">
        <thead><tr><th>Titre</th><th>Catégorie</th><th>Date de publication</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($conseils as $c): ?>
            <tr>
                <td><?= h($c['titre']) ?></td>
                <td><span class="category-badge"><?= h($c['categorie']) ?></span></td>
                <td><?= date('d/m/Y', strtotime($c['date_publication'])) ?></td>
                <td class="actions">
                    <a class="btn btn-light" href="admin.php?page=conseil-form&amp;id=<?= (int) $c['id'] ?>">Modifier</a>
                    <form method="post" action="admin.php?page=conseil-delete" onsubmit="return confirm('Supprimer ce conseil ?');">
                        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                        <button class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>
