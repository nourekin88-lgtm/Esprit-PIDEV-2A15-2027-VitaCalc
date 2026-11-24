<?php
declare(strict_types=1);

$page = array_key_exists('page', $_GET) ? input($_GET, 'page') : 'accueil';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$controller = new Controller();
$nutrition = new NutritionController();
$admin = new AdminController();
$auth = new AuthController();

$routes = [
    'accueil' => ['GET', false, fn () => $nutrition->home()],
    'inscription' => ['GET|POST', false, fn () => (new ProfileController())->register($method)],
    'notifications' => ['GET', false, fn () => $nutrition->reminders()],
    'admin' => ['GET', true, fn () => $nutrition->home(true)],
    'connexion' => ['GET|POST', false, fn () => $auth->login($method)],
    'deconnexion' => ['POST', true, fn () => $auth->logout()],
];

foreach (array_keys(NutritionController::EXTRA_SECTIONS) as $section) {
    $routes[$section] = ['GET', false, fn () => $nutrition->blankSection($section)];
}

foreach (array_keys(NutritionRepository::collections()) as $collection) {
    $routes[$collection] = ['GET', false, fn () => $nutrition->catalog($collection)];
    $routes[$collection . '-fiche'] = ['GET', false, fn () => $nutrition->detail($collection)];
    $routes['admin-' . $collection] = ['GET', true, fn () => $nutrition->catalog($collection, true)];
    $routes[$collection . '-ajouter'] = ['GET|POST', true, fn () => $admin->form($collection, false, $method)];
    $routes[$collection . '-modifier'] = ['GET|POST', true, fn () => $admin->form($collection, true, $method)];
    $routes[$collection . '-supprimer'] = ['GET|POST', true, fn () => $admin->delete($collection, $method)];
}

if (!isset($routes[$page])) {
    $controller->error(404, 'Page introuvable', 'Cette adresse ne correspond à aucune page de VitaCalc.');
    return;
}

[$verbs, $requiresAdmin, $action] = $routes[$page];
$allowed = explode('|', $verbs);
if (!in_array($method, $allowed, true) && !($method === 'HEAD' && in_array('GET', $allowed, true))) {
    header('Allow: ' . str_replace('GET', 'GET, HEAD', implode(', ', $allowed)));
    $controller->error(405, 'Action indisponible', 'Cette méthode ne convient pas à cette page.');
    return;
}

if ($requiresAdmin && !isAdmin()) {
    redirect('connexion');
}
if ($method === 'POST' && !validCsrf()) {
    $controller->error(403, 'Formulaire expiré', 'Revenez au formulaire et réessayez.', $requiresAdmin);
    return;
}

try {
    $action();
} catch (PDOException $exception) {
    error_log('VitaCalc PDO: ' . $exception->getMessage());
    $controller->error(503, 'Base de données indisponible', 'Vérifiez que MySQL est démarré dans XAMPP et que le fichier VitaCalc.sql a été importé.', $requiresAdmin);
}
