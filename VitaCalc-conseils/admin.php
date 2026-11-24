<?php
// Point d'entrée du Back Office : admin.php?page=dashboard | conseils | categories ...
session_start();
require_once __DIR__ . '/pkg/Controllers/AdminController.php';
require_once __DIR__ . '/pkg/Controllers/ConseilAdminController.php';
require_once __DIR__ . '/pkg/Controllers/CategorieConseilController.php';

$routes = [
    'dashboard'        => [AdminController::class,            'dashboard'],
    'conseils'         => [ConseilAdminController::class,     'liste'],
    'conseil-form'     => [ConseilAdminController::class,     'formulaire'],
    'conseil-save'     => [ConseilAdminController::class,     'enregistrer'],
    'conseil-delete'   => [ConseilAdminController::class,     'supprimer'],
    'categories'       => [CategorieConseilController::class, 'liste'],
    'categorie-form'   => [CategorieConseilController::class, 'formulaire'],
    'categorie-save'   => [CategorieConseilController::class, 'enregistrer'],
    'categorie-delete' => [CategorieConseilController::class, 'supprimer'],
];

$page = $_GET['page'] ?? 'dashboard';
if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'dashboard';
}

try {
    [$classe, $methode] = $routes[$page];
    (new $classe())->$methode();
} catch (PDOException $e) {
    http_response_code(500);
    echo '<p style="font-family:sans-serif;padding:2rem">Base de données indisponible : démarrez MySQL et importez <code>database/vitacalc_conseils.sql</code>.</p>';
}
