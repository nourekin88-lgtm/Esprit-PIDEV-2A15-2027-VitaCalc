<form class="search-form" method="get" action="index.php" role="search">
    <input type="hidden" name="page" value="<?= e($currentPage) ?>">
    <label class="search-field">
        <?= icon('search', 19) ?>
        <span class="visually-hidden">Rechercher une fiche</span>
        <input type="search" name="q" placeholder="Rechercher une fiche…" maxlength="120" value="<?= e($query) ?>">
    </label>
    <button class="button button-secondary" type="submit">Rechercher</button>
    <?php if ($query !== ''): ?>
        <a class="text-link" href="<?= e(route($currentPage)) ?>">Effacer</a>
    <?php endif; ?>
</form>
