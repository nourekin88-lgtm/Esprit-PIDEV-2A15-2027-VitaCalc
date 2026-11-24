<?php
// Connexion MySQL (XAMPP : utilisateur "root", mot de passe vide par défaut).
// Ne publiez jamais un vrai mot de passe dans un dépôt public.
class Database
{
    private const HOST = 'localhost';
    private const NAME = 'vitacalc';
    private const USER = 'root';
    private const PASS = '';

    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                'mysql:host=' . self::HOST . ';dbname=' . self::NAME . ';charset=utf8mb4',
                self::USER,
                self::PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }
        return self::$pdo;
    }
}
