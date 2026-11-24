-- =============================================================================
-- VitaCalc Events — Script d'initialisation MySQL
-- Exécuter une seule fois pour créer la base, les tables et les données démo.
-- =============================================================================

CREATE DATABASE IF NOT EXISTS vitacalc_events
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vitacalc_events;

-- -----------------------------------------------------------------------------
-- Table Evenement
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Evenement (
    id              INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    titre           VARCHAR(150)      NOT NULL,
    description     TEXT              NOT NULL,
    dateEvenement   DATE              NOT NULL,
    heure           TIME              NOT NULL,
    lieu            VARCHAR(200)      NOT NULL,
    categorie       VARCHAR(80)       NOT NULL,
    capacite        INT UNSIGNED      NOT NULL,
    prix            DECIMAL(10, 2)    NOT NULL DEFAULT 0.00,
    format          VARCHAR(50)       NOT NULL,
    image           VARCHAR(255)      DEFAULT NULL,
    lien            VARCHAR(500)      DEFAULT NULL,
    placesReservees INT UNSIGNED      NOT NULL DEFAULT 0,

    PRIMARY KEY (id),
    INDEX idx_evenement_date      (dateEvenement),
    INDEX idx_evenement_categorie (categorie),
    CONSTRAINT chk_evenement_capacite CHECK (capacite > 0),
    CONSTRAINT chk_evenement_places   CHECK (placesReservees <= capacite),
    CONSTRAINT chk_evenement_prix     CHECK (prix >= 0)
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- Table Inscription
-- -----------------------------------------------------------------------------
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
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- Métier simple : Favoris (Utilisateur / Favori)
-- -----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Utilisateur (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    token         CHAR(32)     NOT NULL,
    dateCreation  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_utilisateur_token (token)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Favori (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    idUtilisateur INT UNSIGNED NOT NULL,
    idEvenement   INT UNSIGNED NOT NULL,
    dateAjout     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_favori (idUtilisateur, idEvenement),
    INDEX idx_favori_evenement (idEvenement),
    CONSTRAINT fk_favori_utilisateur FOREIGN KEY (idUtilisateur)
        REFERENCES Utilisateur (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_favori_evenement FOREIGN KEY (idEvenement)
        REFERENCES Evenement (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- Métier avancé : Liste d'attente (inscription quand l'événement est complet)
-- -----------------------------------------------------------------------------
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
    CONSTRAINT fk_attente_evenement FOREIGN KEY (idEvenement)
        REFERENCES Evenement (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------------------------------
-- Données de démonstration — Événements
-- Les dates sont relatives à aujourd'hui ; adaptez-les si nécessaire.
-- -----------------------------------------------------------------------------
INSERT INTO Evenement (titre, description, dateEvenement, heure, lieu, categorie, capacite, prix, format, image, placesReservees) VALUES
('Yoga au lever du soleil',
 'Commencez la journée en douceur avec une séance accessible à tous.',
 DATE_ADD(CURDATE(), INTERVAL 5 DAY),  '08:30',
 'Jardin japonais, Tunis',  'Bien-être', 18, 25.00, 'Présentiel', 'Views/assets/img/events/yoga-event.jpg', 10),

('Méditation & respiration',
 'Un atelier guidé pour retrouver calme et énergie.',
 DATE_ADD(CURDATE(), INTERVAL 8 DAY),  '18:00',
 'Espace Vita, Ariana',     'Bien-être', 20, 15.00, 'Présentiel', 'Views/assets/img/events/meditation-event.jpg', 13),

('Atelier cuisine saine',
 'Préparez des recettes simples et gourmandes avec notre nutritionniste.',
 DATE_ADD(CURDATE(), INTERVAL 12 DAY), '10:00',
 'La Maison Verte, La Marsa', 'Nutrition', 16, 35.00, 'Présentiel', 'Views/assets/img/events/community-event.jpg', 9),

('Marche & respiration',
 'Une balade guidée en plein air pour bouger et se ressourcer.',
 DATE_ADD(CURDATE(), INTERVAL 16 DAY), '17:30',
 'Parc du Belvédère, Tunis', 'Sport', 24, 0.00, 'Présentiel', 'Views/assets/img/events/yoga-event.jpg', 12),

('Nutrition & énergie au quotidien',
 'Conseils pratiques pour composer des repas équilibrés.',
 DATE_ADD(CURDATE(), INTERVAL 20 DAY), '18:30',
 'Atelier en ligne',        'Nutrition', 40, 10.00, 'En ligne', 'Views/assets/img/events/meditation-event.jpg', 17),

('Pilates en plein air',
 'Une séance accessible à tous dans une ambiance conviviale.',
 DATE_ADD(CURDATE(), INTERVAL 24 DAY), '09:00',
 'Parc Essaada, Ben Arous', 'Sport', 14, 20.00, 'Présentiel', 'Views/assets/img/events/community-event.jpg', 6),

('Étirements & détente',
 'Une pause active pour relâcher les tensions et retrouver de l''énergie.',
 DATE_ADD(CURDATE(), INTERVAL 27 DAY), '10:30',
 'Centre sportif El Wafa, La Soukra', 'Bien-être', 16, 18.00, 'Présentiel', 'Views/assets/img/events/yoga-event.jpg', 4);
