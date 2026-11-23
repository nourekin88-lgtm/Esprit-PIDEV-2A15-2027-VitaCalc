<?php /** Liste des catégories. Variables : $categories (avec 'nb'), $csrf. */ ?>
<div class="page-heading">
    <h1>Gestion des catégories</h1>
    <p>Une catégorie ne peut être supprimée que si elle ne contient plus aucun conseil.</p>
</div>

<section class="panel">
    <div class="toolbar">
        <span></span>
        <a class="btn btn-primary" href="admin.php?page=categorie-form">+ Ajouter une catégorie</a>
    </div>
    <table class="table-dashboard">
        <thead><tr><th>Catégorie</th><th>Description</th><th>Conseils</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($categories as $k): ?>
            <tr>
                <td><span class="category-badge"><?= h($k['nom']) ?></span></td>
                <td><?= h($k['description']) ?></td>
                <td><?= (int) $k['nb'] ?></td>
                <td class="actions">
                    <a class="btn btn-light" href="admin.php?page=categorie-form&amp;id=<?= (int) $k['id'] ?>">Modifier</a>
                    <form method="post" action="admin.php?page=categorie-delete" onsubmit="return confirm('Supprimer cette catégorie ?');">
                        <input type="hidden" name="csrf" value="<?= h($csrf) ?>">
                        <input type="hidden" name="id" value="<?= (int) $k['id'] ?>">
                        <button class="btn btn-danger">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
