<?php $layout = 'admin'; ?>
<!doctype html>
<html lang="fr">
<head>
    <?php require BASE_PATH . '/Views/includes/head.php'; ?>
</head>
<body data-page="admin">
    <?php require BASE_PATH . '/Views/includes/spinner.php'; ?>
    <div class="admin-shell">
        <?php require BASE_PATH . '/Views/includes/sidebar.php'; ?>
        <main class="admin-main">
            <?php require BASE_PATH . '/Views/includes/header.php'; ?>
            <?= $content ?>
            <?php require BASE_PATH . '/Views/includes/admin-footer.php'; ?>
        </main>
    </div>
    <?php require BASE_PATH . '/Views/includes/scripts.php'; ?>
</body>
</html>
