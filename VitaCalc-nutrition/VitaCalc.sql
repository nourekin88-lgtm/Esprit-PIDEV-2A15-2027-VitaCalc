CREATE DATABASE IF NOT EXISTS vitacalc_nutrition CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vitacalc_nutrition;

CREATE TABLE IF NOT EXISTS RegimeAlimentaire (
    idRegime INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nomRegime VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    PRIMARY KEY (idRegime)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ObjectifNutritionnel (
    idObjectif INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nomObjectif VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    PRIMARY KEY (idObjectif)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ProfilNutritionnel (
    idProfil INT UNSIGNED NOT NULL AUTO_INCREMENT,
    genre VARCHAR(24) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    poidsKg DECIMAL(4,1) NOT NULL,
    tailleCm DECIMAL(4,1) NOT NULL,
    PRIMARY KEY (idProfil),
    CONSTRAINT profil_genre_valide CHECK (genre IN ('femme', 'homme', 'non_binaire', 'non_precise')),
    CONSTRAINT profil_age_valide CHECK (age BETWEEN 1 AND 125),
    CONSTRAINT profil_poids_valide CHECK (poidsKg BETWEEN 0.1 AND 700),
    CONSTRAINT profil_taille_valide CHECK (tailleCm BETWEEN 20 AND 300)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS RegimeParObjectif (
    objectif VARCHAR(12) NOT NULL,
    idRegime INT UNSIGNED NOT NULL,
    PRIMARY KEY (objectif),
    CONSTRAINT regime_objectif_valide CHECK (objectif IN ('prise', 'perte', 'muscle', 'maintien')),
    CONSTRAINT regime_objectif_regime_fk FOREIGN KEY (idRegime) REFERENCES RegimeAlimentaire (idRegime) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ChoixNutritionnel (
    idProfil INT UNSIGNED NOT NULL,
    objectif VARCHAR(12) NOT NULL,
    activite VARCHAR(12) NOT NULL,
    PRIMARY KEY (idProfil),
    CONSTRAINT choix_objectif_valide CHECK (objectif IN ('prise', 'perte', 'muscle', 'maintien')),
    CONSTRAINT choix_activite_valide CHECK (activite IN ('faible', 'moderee', 'active')),
    CONSTRAINT choix_profil_fk FOREIGN KEY (idProfil) REFERENCES ProfilNutritionnel (idProfil) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS BudgetProfilNutritionnel (
    idProfil INT UNSIGNED NOT NULL,
    pays CHAR(3) NOT NULL,
    montant DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (idProfil),
    CONSTRAINT budget_montant_valide CHECK (montant >= 0),
    CONSTRAINT budget_profil_fk FOREIGN KEY (idProfil) REFERENCES ProfilNutritionnel (idProfil) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS SubstitutionNutritionnelle (
    idProfil INT UNSIGNED NOT NULL,
    emplacement VARCHAR(24) NOT NULL,
    contexte CHAR(64) NOT NULL,
    aliment VARCHAR(32) NOT NULL,
    grammes SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (idProfil, emplacement),
    CONSTRAINT substitution_quantite_valide CHECK (grammes > 0),
    CONSTRAINT substitution_profil_fk FOREIGN KEY (idProfil) REFERENCES ProfilNutritionnel (idProfil) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ObjectifCataloguePersonnel (
    objectif VARCHAR(12) NOT NULL,
    idObjectif INT UNSIGNED NOT NULL,
    PRIMARY KEY (objectif),
    UNIQUE (idObjectif),
    CONSTRAINT catalogue_personnel_objectif_valide CHECK (objectif IN ('prise', 'perte', 'muscle', 'maintien')),
    CONSTRAINT catalogue_personnel_objectif_fk FOREIGN KEY (idObjectif) REFERENCES ObjectifNutritionnel (idObjectif) ON DELETE CASCADE
) ENGINE=InnoDB;

USE vitacalc_nutrition;

START TRANSACTION;

INSERT INTO RegimeAlimentaire (nomRegime, description)
SELECT 'Alimentation enrichie progressivement',
       'Pour accompagner une prise de poids progressive, complétez des repas variés par une collation nourrissante. Les noix, l’huile d’olive, le fromage ou le yaourt peuvent enrichir des repas habituels. Par exemple, ajoutez une collation de pain complet, fromage frais et noix.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM RegimeParObjectif WHERE objectif = 'prise');
SET @vitacalc_inserted = ROW_COUNT();
INSERT INTO RegimeParObjectif (objectif, idRegime)
SELECT 'prise', LAST_INSERT_ID() FROM DUAL WHERE @vitacalc_inserted = 1;

INSERT INTO RegimeAlimentaire (nomRegime, description)
SELECT 'Alimentation équilibrée pour une perte progressive',
       'Construisez des repas réguliers autour de légumes, de protéines variées et de féculents complets. Ajustez les portions à votre faim et à votre activité. Un exemple de déjeuner associe légumes grillés, poulet ou tofu et boulgour complet.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM RegimeParObjectif WHERE objectif = 'perte');
SET @vitacalc_inserted = ROW_COUNT();
INSERT INTO RegimeParObjectif (objectif, idRegime)
SELECT 'perte', LAST_INSERT_ID() FROM DUAL WHERE @vitacalc_inserted = 1;

INSERT INTO RegimeAlimentaire (nomRegime, description)
SELECT 'Alimentation pour accompagner le renforcement musculaire',
       'Répartissez les sources de protéines entre les repas et gardez une place pour les féculents pour accompagner l’entraînement. Un exemple de repas réunit du riz complet, du poulet ou du tofu et du brocoli.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM RegimeParObjectif WHERE objectif = 'muscle');
SET @vitacalc_inserted = ROW_COUNT();
INSERT INTO RegimeParObjectif (objectif, idRegime)
SELECT 'muscle', LAST_INSERT_ID() FROM DUAL WHERE @vitacalc_inserted = 1;

INSERT INTO RegimeAlimentaire (nomRegime, description)
SELECT 'Alimentation variée pour le maintien du poids',
       'Alternez légumes, fruits, féculents complets et sources de protéines au fil de la semaine. Adaptez les portions à votre faim et à vos journées. Vous pouvez par exemple préparer une salade de lentilles et de légumes avec un filet d’huile d’olive.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM RegimeParObjectif WHERE objectif = 'maintien');
SET @vitacalc_inserted = ROW_COUNT();
INSERT INTO RegimeParObjectif (objectif, idRegime)
SELECT 'maintien', LAST_INSERT_ID() FROM DUAL WHERE @vitacalc_inserted = 1;

INSERT INTO ObjectifNutritionnel (nomObjectif, description)
SELECT 'Prendre du poids', 'Enrichissez progressivement des repas variés et ajoutez une collation nourrissante selon votre appétit. Depuis votre profil VitaCalc, choisissez cet objectif pour afficher votre menu personnalisé.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ObjectifCataloguePersonnel WHERE objectif = 'prise');
SET @vitacalc_inserted = ROW_COUNT();
INSERT INTO ObjectifCataloguePersonnel (objectif, idObjectif)
SELECT 'prise', LAST_INSERT_ID() FROM DUAL WHERE @vitacalc_inserted = 1;

INSERT INTO ObjectifNutritionnel (nomObjectif, description)
SELECT 'Perdre du poids', 'Maintenez des repas réguliers et équilibrés, avec une place pour les légumes, les protéines et les féculents complets. Consultez depuis votre profil l’exemple de journée proposé.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ObjectifCataloguePersonnel WHERE objectif = 'perte');
SET @vitacalc_inserted = ROW_COUNT();
INSERT INTO ObjectifCataloguePersonnel (objectif, idObjectif)
SELECT 'perte', LAST_INSERT_ID() FROM DUAL WHERE @vitacalc_inserted = 1;

INSERT INTO ObjectifNutritionnel (nomObjectif, description)
SELECT 'Gagner du muscle', 'Associez un renforcement progressif et de la récupération à des repas variés et à des sources de protéines réparties dans la journée.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ObjectifCataloguePersonnel WHERE objectif = 'muscle');
SET @vitacalc_inserted = ROW_COUNT();
INSERT INTO ObjectifCataloguePersonnel (objectif, idObjectif)
SELECT 'muscle', LAST_INSERT_ID() FROM DUAL WHERE @vitacalc_inserted = 1;

INSERT INTO ObjectifNutritionnel (nomObjectif, description)
SELECT 'Conserver son poids', 'Variez vos repas et adaptez les portions à votre faim et à votre niveau d’activité. Le profil VitaCalc propose un exemple de journée avec des portions personnalisées.'
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM ObjectifCataloguePersonnel WHERE objectif = 'maintien');
SET @vitacalc_inserted = ROW_COUNT();
INSERT INTO ObjectifCataloguePersonnel (objectif, idObjectif)
SELECT 'maintien', LAST_INSERT_ID() FROM DUAL WHERE @vitacalc_inserted = 1;

UPDATE ObjectifNutritionnel o
JOIN ObjectifCataloguePersonnel c ON c.idObjectif = o.idObjectif AND c.objectif = 'prise'
SET o.description = 'Enrichissez progressivement des repas variés et ajoutez une collation nourrissante selon votre appétit. Depuis votre profil VitaCalc, choisissez cet objectif pour afficher votre menu personnalisé.'
WHERE BINARY o.description = 'Enrichissez progressivement des repas variés et ajoutez une collation nourrissante selon votre appétit. Depuis votre profil VitaCalc, choisissez cet objectif et affichez le régime associé à votre budget.';

UPDATE ObjectifNutritionnel o
JOIN ObjectifCataloguePersonnel c ON c.idObjectif = o.idObjectif AND c.objectif = 'maintien'
SET o.description = 'Variez vos repas et adaptez les portions à votre faim et à votre niveau d’activité. Le profil VitaCalc propose un exemple de journée avec des portions personnalisées.'
WHERE BINARY o.description = 'Variez vos repas et adaptez les portions à votre faim et à votre niveau d’activité. Le profil VitaCalc propose un exemple de journée estimé avec votre budget.';

COMMIT;
