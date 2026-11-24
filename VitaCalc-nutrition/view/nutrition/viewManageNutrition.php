<header class="page-heading split-heading">
    <div>
        <h1><?= e($title) ?></h1>
        <p>Les fiches enregistrées sont visibles dans le catalogue public.</p>
    </div>
    <a class="button button-primary" href="<?= e(route($collection . '-ajouter')) ?>"><?= icon('plus', 18) ?> Ajouter une fiche</a>
</header>
<div class="catalog-toolbar">
    <?php require __DIR__ . '/../partials/search.php'; ?>
    <span class="result-count"><?= count($records) ?> fiche<?= count($records) > 1 ? 's' : '' ?></span>
</div>
<?php if ($records === []): ?>
    <div class="empty">
        <?= icon($meta['icon'], 27) ?>
        <h2>Aucune fiche trouvée</h2>
        <p><?= $query === '' ? 'Ajoutez une première fiche pour la publier.' : 'Essayez une recherche avec d’autres mots.' ?></p>
    </div>
<?php else: ?>
    <div class="table-panel">
        <table>
            <caption class="visually-hidden"><?= e($title) ?></caption>
            <thead><tr><th scope="col">Nom</th><th scope="col">Description</th><th scope="col">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <tr>
                        <th scope="row"><a href="<?= e(route($collection . '-fiche', ['id' => $record['id']])) ?>"><?= e($record['nom']) ?></a></th>
                        <td><?= e(excerpt($record['description'], 105)) ?></td>
                        <td>
                            <div class="row-actions">
                                <a class="icon-button" href="<?= e(route($collection . '-fiche', ['id' => $record['id']])) ?>" title="Consulter" aria-label="<?= e('Consulter ' . $record['nom']) ?>"><?= icon('eye', 17) ?></a>
                                <a class="icon-button" href="<?= e(route($collection . '-modifier', ['id' => $record['id']])) ?>" title="Modifier" aria-label="<?= e('Modifier ' . $record['nom']) ?>"><?= icon('edit', 17) ?></a>
                                <a class="icon-button danger-icon" href="<?= e(route($collection . '-supprimer', ['id' => $record['id']])) ?>" title="Supprimer" aria-label="<?= e('Supprimer ' . $record['nom']) ?>"><?= icon('trash', 17) ?></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
