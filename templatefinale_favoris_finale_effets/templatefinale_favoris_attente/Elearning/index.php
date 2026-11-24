<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$routes = require BASE_PATH . '/routes.php';
$page = (string) ($_GET['page'] ?? 'home');

if (!isset($routes[$page])) {
    http_response_code(404);
    $page = 'home';
}

[$controllerName, $action] = $routes[$page];
$controller = new $controllerName();
$controller->{$action}();
