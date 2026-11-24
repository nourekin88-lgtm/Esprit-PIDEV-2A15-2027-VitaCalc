<?php
/**
 * Point d'entrée unique (front controller).
 *   Visiteur / client : index.php?page=home
 *   Administrateur    : index.php?page=admin
 */
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/helpers.php';

// Autoloader : App\Controllers\X => controllers/X.php  |  App\Models\X => models/X.php
spl_autoload_register(function (string $class): void {
    $map = ['App\\Controllers\\' => 'controllers/', 'App\\Models\\' => 'models/'];
    foreach ($map as $prefix => $dir) {
        if (str_starts_with($class, $prefix)) {
            $file = BASE_PATH . '/' . $dir . substr($class, strlen($prefix)) . '.php';
            if (is_file($file)) {
                require_once $file;
            }
            return;
        }
    }
});

$routes = require __DIR__ . '/config/routes.php';
$page   = $_GET['page'] ?? 'home';
$page   = is_string($page) ? $page : 'home';

if (!isset($routes[$page])) {
    (new App\Controllers\FrontController())->notFound();
}

// Protection CSRF : tout formulaire POST doit contenir le jeton de session
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrfValid()) {
    setFlash('danger', 'Votre session a expiré. Veuillez réessayer.');
    header('Location: ' . url('home'));
    exit;
}

[$class, $method] = $routes[$page];

try {
    (new $class())->$method();
} catch (Throwable $ex) {
    http_response_code(500);
    echo '<h1>Erreur</h1><pre>' . e($ex->getMessage()) . '</pre>';
    echo '<p>Vérifiez que le dossier « data » (et assets/uploads) est accessible en écriture.</p>';
}
