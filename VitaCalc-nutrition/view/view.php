<!doctype html>
<html lang="fr">
<head>
    <?php require_once __DIR__ . '/head.php'; ?>
</head>
<body class="<?= $adminArea ? 'admin-area' : 'public-area' ?>">
    <a class="skip" href="#contenu">Aller au contenu</a>
    <?php require_once __DIR__ . '/partials/sidebar.php'; ?>
    <button class="backdrop" type="button" aria-label="Fermer le menu" data-menu-close hidden></button>
    <div class="workspace<?= $blankPage ? ' blank-workspace' : '' ?>">
        <?php if (!$blankPage): ?>
            <canvas class="ambient-dots" aria-hidden="true"></canvas>
        <?php endif; ?>
        <?php require_once __DIR__ . '/header.php'; ?>
        <main class="content" id="contenu">
            <?php if ($message): ?>
                <div class="notice success" role="status"><?= icon('check', 18) ?> <?= e($message) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </main>
        <?php if (!$blankPage) require_once __DIR__ . '/footer.php'; ?>
    </div>
    <?php if (!$adminArea): ?>
        <div class="reminder-alerts" data-reminder-alerts
            data-reminder-options="<?= e(json_encode(dailyReminders(), JSON_THROW_ON_ERROR)) ?>"></div>
    <?php endif; ?>
    <?php require_once __DIR__ . '/scripts.php'; ?>
</body>
</html>
