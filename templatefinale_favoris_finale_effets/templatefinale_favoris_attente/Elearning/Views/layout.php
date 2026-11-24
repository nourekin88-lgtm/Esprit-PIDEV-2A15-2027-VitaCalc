<?php $layout = 'front'; ?>
<!doctype html>
<html lang="fr">
<head>
    <?php require BASE_PATH . '/Views/includes/head.php'; ?>
</head>
<body data-page="<?= e($currentPage) ?>">
    <?php require BASE_PATH . '/Views/includes/spinner.php'; ?>
    <?php require BASE_PATH . '/Views/includes/navbar.php'; ?>

    <?php if ($currentPage !== 'home'): ?>
        <?php
        // Page de secours si l'utilisateur n'a pas d'historique (lien direct)
        $backUrl   = 'index.php?page=home';
        $backLabel = 'Retour à l’accueil';
        if ($currentPage === 'events' && ($_GET['page'] ?? '') === 'event-detail') {
            $backUrl   = 'index.php?page=events';
            $backLabel = 'Retour aux événements';
        } elseif ($currentPage === 'signup') {
            $evId = isset($_GET['event']) ? (int) $_GET['event'] : 0;
            if ($evId > 0) {
                $backUrl   = 'index.php?page=event-detail&id=' . $evId;
                $backLabel = 'Retour à l’événement';
            } else {
                $backUrl   = 'index.php?page=events';
                $backLabel = 'Retour aux événements';
            }
        }
        ?>
        <div class="backbar shell">
            <a class="backarrow" href="<?= e($backUrl) ?>" data-back aria-label="<?= e($backLabel) ?>" title="<?= e($backLabel) ?>">
                <span aria-hidden="true">&larr;</span>
                <span class="backarrow-text">Retour</span>
            </a>
        </div>
    <?php endif; ?>

    <?= $content ?>

    <?php require BASE_PATH . '/Views/includes/footer.php'; ?>
    <?php require BASE_PATH . '/Views/includes/scripts.php'; ?>
</body>
</html>
