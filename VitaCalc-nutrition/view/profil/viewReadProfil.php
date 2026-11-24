<?php
$format = static fn (float $value, int $digits = 0): string => number_format($value, $digits, ',', ' ');
$measure = static fn (string $value): string => str_replace('.', ',', preg_replace('/\.0\z/', '', $value));
?>
<div class="nutrition-dashboard">
    <section class="plan-banner" aria-labelledby="profile-title">
        <div class="plan-banner-title">
            <span class="signup-emblem"><?= icon('profile', 23) ?></span>
            <h1 id="profile-title">Mon profil nutritionnel</h1>
            <a class="button button-light" href="<?= e(route('inscription', ['modifier' => 1])) ?>">Modifier mon profil</a>
        </div>
        <dl class="plan-profile">
            <div><dt>Genre</dt><dd><?= e($genders[$profile['genre']]) ?></dd></div>
            <div><dt>Âge</dt><dd><?= e($profile['age']) ?> ans</dd></div>
            <div><dt>Poids</dt><dd><?= e($measure((string) $profile['poids'])) ?> kg</dd></div>
            <div><dt>Taille</dt><dd><?= e($measure((string) $profile['taille'])) ?> cm</dd></div>
        </dl>
    </section>

    <?php if ($menuError !== null): ?><p class="notice error" role="alert"><?= e($menuError) ?></p><?php endif; ?>

    <?php require __DIR__ . '/../partials/nutrition-report.php'; ?>
    <?php require __DIR__ . '/../partials/nutrition-goals.php'; ?>
    <?php require __DIR__ . '/../partials/nutrition-menu.php'; ?>
</div>
