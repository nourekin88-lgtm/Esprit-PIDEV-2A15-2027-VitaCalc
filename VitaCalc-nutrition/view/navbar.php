<?php
$links = [
    'accueil' => ['Vue d’ensemble', 'home'],
    'regimes' => ['Régimes alimentaires', 'bowl'],
    'objectifs' => ['Objectifs nutritionnels', 'target'],
    'inscription' => [nutritionProfileId() === null ? 'S’inscrire' : 'Mon profil', 'profile'],
    'notifications' => ['Notifications', 'bell'],
] + NutritionController::EXTRA_SECTIONS;
?>
<nav class="side-nav" aria-label="Navigation nutrition">
    <p class="nav-label">Nutrition</p>
    <?php foreach ($links as $key => [$label, $symbol]): ?>
        <a class="nav-link<?= $currentPage === $key ? ' active' : '' ?>" href="<?= e(route($key)) ?>"
            <?= $currentPage === $key ? 'aria-current="page"' : '' ?>>
            <span class="nav-symbol"><?= icon($symbol, 18) ?></span>
            <span><?= e($label) ?></span>
        </a>
    <?php endforeach; ?>
</nav>
