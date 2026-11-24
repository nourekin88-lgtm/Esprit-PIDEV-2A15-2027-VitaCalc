<section class="empty error-page">
    <span class="tile-icon regimes"><?= icon('alert', 25) ?></span>
    <h1><?= e($title) ?></h1>
    <p><?= e($detail) ?></p>
    <a class="button button-primary" href="<?= e(route($adminArea ? 'admin' : 'accueil')) ?>">Retour à l’accueil <?= icon('arrow', 17) ?></a>
</section>
