<?php
/**
 * Layout BACK OFFICE (administrateur) : assemble les includes dans l'ordre (voir workshop, partie 2).
 * Variables : $title, $currentPage, $content, $extraScripts
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once BASE_PATH . '/views/includes/back/head.php'; ?>
</head>
<body class="admin-body">
    <!-- ======== Preloader ======== -->
    <div id="preloader"><div class="spinner"></div></div>

    <?php require_once BASE_PATH . '/views/includes/back/sidebar.php'; ?>
    <div class="overlay"></div>

    <main class="main-wrapper">
        <?php require_once BASE_PATH . '/views/includes/back/header.php'; ?>

        <div class="content-area">
            <?php require BASE_PATH . '/views/includes/flash.php'; ?>
            <?= $content ?>
        </div>

        <?php require_once BASE_PATH . '/views/includes/back/footer.php'; ?>
    </main>

    <?php require_once BASE_PATH . '/views/includes/back/scripts.php'; ?>
</body>
</html>
