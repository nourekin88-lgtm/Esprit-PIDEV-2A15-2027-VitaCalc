<?php
// Routeur pour le serveur PHP intégré : php -S localhost:8000 router.php
// Sert les fichiers statiques (css, js, img, lib) directement,
// et route index.php / api.php pour les requêtes dynamiques.

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Fichiers statiques existants → servis directement
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// Compatibilité : anciens chemins img/... -> Views/assets/img/...
if (str_starts_with($uri, '/img/') && is_file(__DIR__ . '/Views/assets' . $uri)) {
    header('Content-Type: ' . (mime_content_type(__DIR__ . '/Views/assets' . $uri) ?: 'application/octet-stream'));
    readfile(__DIR__ . '/Views/assets' . $uri);
    return true;
}

// api.php
if (str_starts_with($uri, '/api.php') || str_starts_with($uri, '/api')) {
    $_SERVER['SCRIPT_NAME'] = '/api.php';
    require __DIR__ . '/api.php';
    return true;
}

// Tout le reste → index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
return true;
