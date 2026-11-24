<?php
/**
 * Layout FRONT OFFICE : assemble les includes dans l'ordre (voir workshop).
 * Variables : $title, $currentPage, $content, $extraScripts
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php require_once BASE_PATH . '/views/includes/head.php'; ?>
</head>
<body>
    <?php
    require_once BASE_PATH . '/views/includes/spinner.php';
    require_once BASE_PATH . '/views/includes/navbar.php';
    ?>

    <main>
        <?php require BASE_PATH . '/views/includes/flash.php'; ?>
        <?= $content ?>
    </main>

    <?php
    require_once BASE_PATH . '/views/includes/footer.php';
    require_once BASE_PATH . '/views/includes/scripts.php';
    ?>
</body>
</html>
