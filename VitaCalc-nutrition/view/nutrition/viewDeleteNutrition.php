<section class="form-panel confirmation">
    <span class="tile-icon warn"><?= icon('trash', 24) ?></span>
    <h1><?= e($title) ?></h1>
    <p>La fiche <strong>« <?= e($record['nom']) ?> »</strong> sera retirée du catalogue. Cette action est définitive.</p>
    <form class="form-actions" method="post" action="<?= e(route($collection . '-supprimer', ['id' => $record['id']])) ?>">
        <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
        <button class="button button-danger" type="submit">Supprimer la fiche</button>
        <a class="button button-secondary" href="<?= e(route($currentPage)) ?>">Annuler</a>
    </form>
</section>
