<?php
/**
 * Layout MODE CUISINE : plein écran, sans navbar ni footer (seulement la recette).
 * Variables : $title, $content
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= e($title ?? APP_NAME) ?></title>
    <meta name="theme-color" content="#263746">
    <link rel="icon" href="assets/images/logo/favicon.png" type="image/png">
    <link href="assets/lib/bootstrap-icons/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/theme.css" rel="stylesheet">
    <link href="assets/css/cook.css" rel="stylesheet">
</head>
<body class="cook-body">
    <?= $content ?>
    <script src="assets/js/cook.js"></script>
</body>
</html>
