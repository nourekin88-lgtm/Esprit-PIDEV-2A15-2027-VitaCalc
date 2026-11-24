<?php
/** Layout du Back Office. Variables : $title, $currentPage, $content, $flash, $baseUrl. */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($title) ?> | VitaCalc Back Office</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/backoffice.css">
</head>
<body>
<div class="dashboard">
    <?php require __DIR__ . '/partials/sidebar.php'; ?>
    <main class="main-content">
        <?php require __DIR__ . '/partials/topbar.php'; ?>
        <div class="content">
            <?php if ($flash): ?>
                <div class="flash <?= h($flash['type']) ?>" role="status"><?= h($flash['msg']) ?></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>
</div>
</body>
</html>
