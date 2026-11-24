<?php
declare(strict_types=1);

$defaults = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'name' => 'vitacalc_nutrition',
    'user' => 'root',
    'password' => '',
];

$local = __DIR__ . '/db.local.php';
return is_file($local) ? array_replace($defaults, require $local) : $defaults;
