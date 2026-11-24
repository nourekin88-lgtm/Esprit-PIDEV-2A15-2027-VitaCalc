<aside class="sidebar" id="navigation">
    <a class="brand" href="<?= e(route($adminArea ? 'admin' : 'accueil')) ?>" aria-label="VitaCalc, accueil">
        <img src="assets/imges/logo-vitacalc.png" alt="VitaCalc">
    </a>
    <?php if ($adminArea): ?>
        <?php
        $links = [
            'admin' => ['Tableau de bord', 'grid'],
            'admin-regimes' => ['Gérer les régimes', 'bowl'],
            'admin-objectifs' => ['Gérer les objectifs', 'target'],
        ];
        ?>
        <nav class="side-nav" aria-label="Administration nutrition">
            <p class="nav-label">Administration</p>
            <?php foreach ($links as $key => [$label, $symbol]): ?>
                <a class="nav-link<?= $currentPage === $key ? ' active' : '' ?>" href="<?= e(route($key)) ?>"
                    <?= $currentPage === $key ? 'aria-current="page"' : '' ?>>
                    <span class="nav-symbol"><?= icon($symbol, 18) ?></span>
                    <span><?= e($label) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php else: ?>
        <?php require_once __DIR__ . '/../navbar.php'; ?>
    <?php endif; ?>

    <div class="sidebar-bottom">
        <?php if ($adminArea && isAdmin()): ?>
            <a class="nav-link" href="<?= e(route('accueil')) ?>"><?= icon('up-right', 18) ?> <span>Voir le site public</span></a>
            <form method="post" action="<?= e(route('deconnexion')) ?>">
                <input type="hidden" name="csrf" value="<?= e(csrfToken()) ?>">
                <button class="nav-link" type="submit"><?= icon('logout', 18) ?> <span>Se déconnecter</span></button>
            </form>
        <?php else: ?>
            <a class="nav-link<?= $currentPage === 'connexion' ? ' active' : '' ?>" href="<?= e(route(isAdmin() ? 'admin' : 'connexion')) ?>"
                <?= $currentPage === 'connexion' ? 'aria-current="page"' : '' ?>>
                <span class="nav-symbol"><?= icon('shield', 18) ?></span>
                <span>Administration</span>
            </a>
        <?php endif; ?>
    </div>
</aside>
