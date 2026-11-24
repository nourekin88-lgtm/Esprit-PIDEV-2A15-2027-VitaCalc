<?php
declare(strict_types=1);

/**
 * Configuration de la base de données MySQL.
 * Adaptez les valeurs à votre environnement local.
 */
return [
    'db_host'    => 'localhost',
    'db_port'    => '3306',
    'db_name'    => 'vitacalc_events',
    'db_user'    => 'root',
    'db_pass'    => '',
    'db_charset' => 'utf8mb4',

    // Clé Pixabay GRATUITE (sans CB) → https://pixabay.com/api/docs/
    // 1. Créez un compte sur pixabay.com
    // 2. Votre clé apparaît directement sur https://pixabay.com/api/docs/
    // 3. Collez-la ici
    'pixabay_key' => '',
];
