<?php
/**
 * Configuration générale de VitaCalc (Module Meal Prep).
 * Pas de base de données : les données sont enregistrées dans data/vitacalc.json
 * (créé automatiquement au premier lancement avec des données de démonstration).
 */

define('APP_NAME', 'VitaCalc');
define('BASE_PATH', dirname(__DIR__));

// Stockage
define('DATA_FILE', BASE_PATH . '/data/vitacalc.json');
define('UPLOAD_DIR', BASE_PATH . '/assets/uploads');
define('UPLOAD_MAX_BYTES', 3 * 1024 * 1024); // 3 Mo

// Rôles (voir classe Utilisateur du diagramme de classes)
const ROLE_CLIENT = 'CLIENT';
const ROLE_ADMIN  = 'ADMIN';

// Affiche les comptes de démonstration sur la page de connexion (mettre false avant la mise en ligne)
const SHOW_DEMO_ACCOUNTS = true;
