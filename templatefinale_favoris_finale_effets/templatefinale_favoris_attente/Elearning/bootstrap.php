<?php
declare(strict_types=1);

// Chemins globaux
define('BASE_PATH', __DIR__);
define('ASSETS_PATH', BASE_PATH . '/Views/assets');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Autoload léger : Controllers + Models
spl_autoload_register(function (string $className): void {
    $folders = [
        BASE_PATH . '/Controllers/',
        BASE_PATH . '/Models/',
    ];
    foreach ($folders as $folder) {
        $file = $folder . $className . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

// Helper d'échappement HTML
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
