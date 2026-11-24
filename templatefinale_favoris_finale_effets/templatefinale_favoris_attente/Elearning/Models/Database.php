<?php
declare(strict_types=1);

/**
 * Singleton PDO — connexion MySQL avec initialisation automatique.
 * Si la base ou les tables n'existent pas, elles sont créées au premier appel.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = require BASE_PATH . '/config.php';

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            // Connexion sans dbname pour pouvoir créer la base si elle n'existe pas
            $dsnNoDB = sprintf(
                'mysql:host=%s;port=%s;charset=%s',
                $config['db_host'],
                $config['db_port'],
                $config['db_charset']
            );
            $pdo = new PDO($dsnNoDB, $config['db_user'], $config['db_pass'], $options);

            // Crée la base si absente
            $dbName = $config['db_name'];
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");

            // Crée les tables si absentes
            self::createTables($pdo);
            self::createFeatureTables($pdo);
            self::ensureLienColumn($pdo);

            // Insère les données démo si les tables sont vides
            self::seedIfEmpty($pdo);

            self::$instance = $pdo;

        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Connexion à la base de données impossible : ' . $e->getMessage()
            ]);
            exit;
        }

        return self::$instance;
    }

    // -------------------------------------------------------------------------

    private static function createTables(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS Evenement (
                id              INT UNSIGNED   NOT NULL AUTO_INCREMENT,
                titre           VARCHAR(150)   NOT NULL,
                description     TEXT           NOT NULL,
                dateEvenement   DATE           NOT NULL,
                heure           TIME           NOT NULL,
                lieu            VARCHAR(200)   NOT NULL,
                categorie       VARCHAR(80)    NOT NULL,
                capacite        INT UNSIGNED   NOT NULL,
                prix            DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
                format          VARCHAR(50)    NOT NULL,
                image           VARCHAR(255)   DEFAULT NULL,
                placesReservees INT UNSIGNED   NOT NULL DEFAULT 0,
                PRIMARY KEY (id),
                INDEX idx_evenement_date      (dateEvenement),
                INDEX idx_evenement_categorie (categorie),
                CONSTRAINT chk_evenement_capacite CHECK (capacite > 0),
                CONSTRAINT chk_evenement_places   CHECK (placesReservees <= capacite),
                CONSTRAINT chk_evenement_prix     CHECK (prix >= 0)
            ) ENGINE=InnoDB
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS Inscription (
                id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                dateInscription  DATE          NOT NULL,
                idEvenement      INT UNSIGNED  NOT NULL,
                prenom           VARCHAR(80)   NOT NULL,
                nom              VARCHAR(80)   NOT NULL,
                email            VARCHAR(190)  NOT NULL,
                telephone        VARCHAR(30)   NOT NULL,
                PRIMARY KEY (id),
                INDEX idx_inscription_evenement (idEvenement),
                INDEX idx_inscription_date      (dateInscription),
                CONSTRAINT fk_inscription_evenement
                    FOREIGN KEY (idEvenement)
                    REFERENCES Evenement (id)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");
    }

    // -------------------------------------------------------------------------

    /**
     * Tables des métiers « Favoris » et « Liste d'attente ».
     * Utilisateur : visiteur anonyme identifié par un jeton (cookie vc_user).
     */
    private static function createFeatureTables(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS Utilisateur (
                id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
                token         CHAR(32)     NOT NULL,
                dateCreation  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_utilisateur_token (token)
            ) ENGINE=InnoDB
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS Favori (
                id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
                idUtilisateur INT UNSIGNED NOT NULL,
                idEvenement   INT UNSIGNED NOT NULL,
                dateAjout     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_favori (idUtilisateur, idEvenement),
                INDEX idx_favori_evenement (idEvenement),
                CONSTRAINT fk_favori_utilisateur
                    FOREIGN KEY (idUtilisateur) REFERENCES Utilisateur (id)
                    ON UPDATE CASCADE ON DELETE CASCADE,
                CONSTRAINT fk_favori_evenement
                    FOREIGN KEY (idEvenement) REFERENCES Evenement (id)
                    ON UPDATE CASCADE ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS ListeAttente (
                id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
                idEvenement   INT UNSIGNED NOT NULL,
                prenom        VARCHAR(80)  NOT NULL,
                nom           VARCHAR(80)  NOT NULL,
                email         VARCHAR(190) NOT NULL,
                telephone     VARCHAR(30)  NOT NULL,
                dateDemande   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                statut        VARCHAR(20)  NOT NULL DEFAULT 'en_attente',
                datePromotion DATETIME     DEFAULT NULL,
                PRIMARY KEY (id),
                INDEX idx_attente_evenement (idEvenement, statut),
                CONSTRAINT fk_attente_evenement
                    FOREIGN KEY (idEvenement) REFERENCES Evenement (id)
                    ON UPDATE CASCADE ON DELETE CASCADE
            ) ENGINE=InnoDB
        ");
    }

    /** Ajoute la colonne `lien` (page officielle de l'événement) si elle manque. */
    private static function ensureLienColumn(PDO $pdo): void
    {
        $stmt = $pdo->query(
            "SELECT COUNT(*) FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Evenement' AND COLUMN_NAME = 'lien'"
        );
        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->exec("ALTER TABLE Evenement ADD COLUMN lien VARCHAR(500) DEFAULT NULL AFTER image");
        }
    }

    private static function seedIfEmpty(PDO $pdo): void
    {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM Evenement')->fetchColumn();
        if ($count > 0) {
            return; // Données déjà présentes
        }

        $stmt = $pdo->prepare("
            INSERT INTO Evenement
                (titre, description, dateEvenement, heure, lieu, categorie,
                 capacite, prix, format, image, placesReservees)
            VALUES
                (:titre, :description, :date, :heure, :lieu, :categorie,
                 :capacite, :prix, :format, :image, :places)
        ");

        $today = new DateTimeImmutable('today');

        $events = [
            [
                'titre'       => 'Yoga au lever du soleil',
                'description' => 'Commencez la journée en douceur avec une séance accessible à tous.',
                'days'        => 5,  'heure' => '08:30',
                'lieu'        => 'Jardin japonais, Tunis',
                'categorie'   => 'Bien-être', 'capacite' => 18,
                'prix'        => 25.00, 'format' => 'Présentiel',
                'image'       => 'Views/assets/img/events/yoga-event.jpg', 'places' => 10,
            ],
            [
                'titre'       => 'Méditation & respiration',
                'description' => 'Un atelier guidé pour retrouver calme et énergie.',
                'days'        => 8,  'heure' => '18:00',
                'lieu'        => 'Espace Vita, Ariana',
                'categorie'   => 'Bien-être', 'capacite' => 20,
                'prix'        => 15.00, 'format' => 'Présentiel',
                'image'       => 'Views/assets/img/events/meditation-event.jpg', 'places' => 13,
            ],
            [
                'titre'       => 'Atelier cuisine saine',
                'description' => 'Préparez des recettes simples et gourmandes avec notre nutritionniste.',
                'days'        => 12, 'heure' => '10:00',
                'lieu'        => 'La Maison Verte, La Marsa',
                'categorie'   => 'Nutrition', 'capacite' => 16,
                'prix'        => 35.00, 'format' => 'Présentiel',
                'image'       => 'Views/assets/img/events/community-event.jpg', 'places' => 9,
            ],
            [
                'titre'       => 'Marche & respiration',
                'description' => 'Une balade guidée en plein air pour bouger et se ressourcer.',
                'days'        => 16, 'heure' => '17:30',
                'lieu'        => 'Parc du Belvédère, Tunis',
                'categorie'   => 'Sport', 'capacite' => 24,
                'prix'        => 0.00, 'format' => 'Présentiel',
                'image'       => 'Views/assets/img/events/yoga-event.jpg', 'places' => 12,
            ],
            [
                'titre'       => 'Nutrition & énergie au quotidien',
                'description' => 'Conseils pratiques pour composer des repas équilibrés.',
                'days'        => 20, 'heure' => '18:30',
                'lieu'        => 'Atelier en ligne',
                'categorie'   => 'Nutrition', 'capacite' => 40,
                'prix'        => 10.00, 'format' => 'En ligne',
                'image'       => 'Views/assets/img/events/meditation-event.jpg', 'places' => 17,
            ],
            [
                'titre'       => 'Pilates en plein air',
                'description' => 'Une séance accessible à tous dans une ambiance conviviale.',
                'days'        => 24, 'heure' => '09:00',
                'lieu'        => 'Parc Essaada, Ben Arous',
                'categorie'   => 'Sport', 'capacite' => 14,
                'prix'        => 20.00, 'format' => 'Présentiel',
                'image'       => 'Views/assets/img/events/community-event.jpg', 'places' => 6,
            ],
            [
                'titre'       => 'Étirements & détente',
                'description' => "Une pause active pour relâcher les tensions et retrouver de l'énergie.",
                'days'        => 27, 'heure' => '10:30',
                'lieu'        => 'Centre sportif El Wafa, La Soukra',
                'categorie'   => 'Bien-être', 'capacite' => 16,
                'prix'        => 18.00, 'format' => 'Présentiel',
                'image'       => 'Views/assets/img/events/yoga-event.jpg', 'places' => 4,
            ],
        ];

        foreach ($events as $ev) {
            $stmt->execute([
                ':titre'       => $ev['titre'],
                ':description' => $ev['description'],
                ':date'        => $today->modify('+' . $ev['days'] . ' days')->format('Y-m-d'),
                ':heure'       => $ev['heure'],
                ':lieu'        => $ev['lieu'],
                ':categorie'   => $ev['categorie'],
                ':capacite'    => $ev['capacite'],
                ':prix'        => $ev['prix'],
                ':format'      => $ev['format'],
                ':image'       => $ev['image'],
                ':places'      => $ev['places'],
            ]);
        }
    }
}
