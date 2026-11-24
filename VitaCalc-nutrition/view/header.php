<?php
$profileLabel = nutritionProfileId() === null ? 'S’inscrire' : 'Mon profil';
$sectionLabel = $adminArea ? 'Nutrition' : 'Espace nutrition';
if ($currentPage === 'inscription') {
    $sectionLabel = nutritionProfileId() === null ? 'Inscription' : 'Mon profil';
}
?>
<header class="topbar" id="haut">
    <div class="topbar-inner">
        <button class="icon-button menu-toggle" type="button" aria-controls="navigation" aria-expanded="false" aria-label="Ouvrir le menu" data-menu-toggle>
            <?= icon('menu') ?>
        </button>
        <div class="breadcrumb">
            <a href="<?= e(route($adminArea ? 'admin' : 'accueil')) ?>"><?= $adminArea ? 'Administration' : 'VitaCalc' ?></a>
            <?= icon('chevron', 14) ?>
            <span><?= e($sectionLabel) ?></span>
        </div>
        <?php if ($adminArea && isAdmin()): ?>
            <span class="top-label"><span></span> Administrateur</span>
        <?php else: ?>
            <div class="top-actions">
                <a class="button button-primary signup-entry" href="<?= e(route('inscription')) ?>"
                    <?= $currentPage === 'inscription' ? 'aria-current="page"' : '' ?>>
                    <?= icon('profile', 17) ?> <?= e($profileLabel) ?>
                </a>
                <a class="top-label" href="<?= e(route(isAdmin() ? 'admin' : 'connexion')) ?>"><?= icon('shield', 15) ?> <span>Administration</span></a>
            </div>
        <?php endif; ?>
    </div>
</header>
