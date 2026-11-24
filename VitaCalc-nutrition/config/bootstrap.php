<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('session.use_strict_mode', '1');
session_name('vitacalc_nutrition');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_cache_limiter('');
session_start();
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header("Content-Security-Policy: default-src 'self'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../model/Model.php';
require_once __DIR__ . '/../model/ModelNutrition.php';
require_once __DIR__ . '/../model/ModelProfil.php';
require_once __DIR__ . '/../model/NutritionPlan.php';
require_once __DIR__ . '/../model/ModelPlanNutrition.php';
require_once __DIR__ . '/../model/MenuPlanner.php';
require_once __DIR__ . '/../controller/Controller.php';
require_once __DIR__ . '/../controller/controllerNutrition.php';
require_once __DIR__ . '/../controller/controllerAdmin.php';
require_once __DIR__ . '/../controller/controllerConnexion.php';
require_once __DIR__ . '/../controller/controllerProfil.php';
