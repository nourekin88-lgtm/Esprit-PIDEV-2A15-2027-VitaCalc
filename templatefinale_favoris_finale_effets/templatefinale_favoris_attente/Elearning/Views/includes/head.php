<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#073c2a">
<meta name="description" content="VitaCalc — événements bien-être, ateliers et inscriptions à Tunis.">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="Views/assets/css/app.css?v=<?= filemtime(ASSETS_PATH . '/css/app.css') ?>">
<?php if (($layout ?? 'front') === 'admin'): ?>
<link rel="stylesheet" href="Views/assets/css/admin.css?v=<?= filemtime(ASSETS_PATH . '/css/admin.css') ?>">
<?php endif; ?>
<link rel="stylesheet" href="Views/assets/css/theme-emerald.css">
<?php if ($currentPage === 'events'): ?>
<link rel="stylesheet" href="Views/assets/lib/leaflet/leaflet.css">
<link rel="stylesheet" href="Views/assets/css/events-map.css">
<?php endif; ?>
<link rel="stylesheet" href="Views/assets/css/effects.css?v=<?= filemtime(ASSETS_PATH . '/css/effects.css') ?>">
