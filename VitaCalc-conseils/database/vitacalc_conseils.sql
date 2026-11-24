-- VitaCalc : module 1 « Conseils bien-être »
-- À importer avec phpMyAdmin (onglet Importer) ou : mysql -u root < vitacalc_conseils.sql

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS vitacalc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vitacalc;

DROP TABLE IF EXISTS conseil;
DROP TABLE IF EXISTS categorie_conseil;

CREATE TABLE categorie_conseil (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(100) NOT NULL UNIQUE,
  description TEXT
) ENGINE=InnoDB;

CREATE TABLE conseil (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  titre            VARCHAR(150) NOT NULL,
  contenu          TEXT NOT NULL,
  date_publication DATE NOT NULL,
  id_categorie     INT NOT NULL,
  CONSTRAINT fk_conseil_categorie FOREIGN KEY (id_categorie)
    REFERENCES categorie_conseil (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Relation : une catégorie contient 0..* conseils ; un conseil appartient à 1 catégorie.

INSERT INTO categorie_conseil (id, nom, description) VALUES
 (1, 'Nutrition', 'Une alimentation équilibrée et variée.'),
 (2, 'Sport', 'Bouger régulièrement, à son rythme.'),
 (3, 'Sommeil', 'Retrouver un sommeil réparateur.'),
 (4, 'Hydratation', 'Boire suffisamment au fil de la journée.'),
 (5, 'Gestion du stress', 'Apaiser la pression du quotidien.'),
 (6, 'Santé mentale', 'Prendre soin de son équilibre émotionnel.'),
 (7, 'Posture et ergonomie', 'Protéger son dos et ses articulations au quotidien.'),
 (8, 'Bien-être numérique', 'Garder une relation saine avec les écrans.'),
 (9, 'Récupération', 'Laisser au corps le temps de se régénérer.'),
 (10, 'Hygiène de vie', 'Les petites habitudes qui font la différence.');

INSERT INTO conseil (titre, contenu, date_publication, id_categorie) VALUES
 ('Privilégiez les aliments naturels', 'Fruits, légumes et céréales complètes devraient composer la base de vos repas.', '2026-11-01', 1),
 ('Limitez les aliments trop sucrés et trop gras', 'Gardez-les pour les occasions et préférez les produits peu transformés.', '2026-11-01', 1),
 ('Mangez à heures régulières', 'Des repas réguliers aident à garder de l’énergie tout au long de la journée.', '2026-11-01', 1),

 ('Bougez au moins 30 minutes par jour', 'Marche rapide, vélo ou danse : choisissez ce qui vous plaît.', '2026-11-02', 2),
 ('Échauffez-vous avant l’effort', 'Cinq minutes de mobilité préparent les muscles et limitent les blessures.', '2026-11-02', 2),
 ('Augmentez l’intensité progressivement', 'Ajoutez un peu de durée ou de difficulté chaque semaine, sans vous presser.', '2026-11-02', 2),

 ('Couchez-vous à heures régulières', 'Un rythme stable, même le week-end, améliore la qualité du sommeil.', '2026-11-03', 3),
 ('Évitez les écrans avant de dormir', 'Coupez-les une heure avant le coucher pour aider le cerveau à se calmer.', '2026-11-03', 3),
 ('Soignez votre chambre', 'Fraîche, calme et sombre : les trois conditions d’un bon repos.', '2026-11-03', 3),

 ('Buvez de l’eau tout au long de la journée', 'N’attendez pas d’avoir soif : buvez par petites gorgées régulières.', '2026-11-04', 4),
 ('Gardez une gourde à portée de main', 'La voir sur votre bureau est le meilleur rappel pour boire.', '2026-11-04', 4),
 ('Adaptez-vous à l’effort et à la chaleur', 'Buvez davantage quand vous transpirez ou qu’il fait chaud.', '2026-11-04', 4),

 ('Pratiquez la respiration profonde', 'Inspirez 4 secondes, expirez 6 secondes, pendant trois minutes.', '2026-11-05', 5),
 ('Faites des pauses dans votre journée', 'Quelques minutes loin de votre tâche aident à relâcher la pression.', '2026-11-05', 5),
 ('Organisez vos priorités', 'Notez trois tâches essentielles par jour plutôt qu’une liste interminable.', '2026-11-05', 5),

 ('Parlez de ce que vous ressentez', 'Se confier à un proche de confiance allège souvent ce que l’on porte.', '2026-11-06', 6),
 ('Réservez du temps pour ce qui vous fait du bien', 'Lecture, musique, nature : planifiez-le comme un rendez-vous.', '2026-11-06', 6),
 ('Demandez de l’aide quand c’est difficile', 'Si le mal-être dure, un médecin ou un psychologue peut vous accompagner.', '2026-11-06', 6),

 ('Réglez votre poste de travail', 'L’écran à hauteur des yeux et les pieds à plat au sol soulagent le dos.', '2026-11-07', 7),
 ('Faites des pauses régulières devant l’ordinateur', 'Levez-vous et étirez-vous quelques minutes toutes les heures.', '2026-11-07', 7),
 ('Alternez les positions', 'Passez de la position assise à debout pour éviter les tensions.', '2026-11-07', 7),

 ('Fixez des plages sans écran', 'Repas et soirées sans téléphone favorisent le calme et les échanges.', '2026-11-08', 8),
 ('Réduisez les notifications', 'Gardez seulement celles qui comptent pour limiter les interruptions.', '2026-11-08', 8),
 ('Faites des pauses visuelles', 'Toutes les 20 minutes, regardez au loin pendant 20 secondes.', '2026-11-08', 8),

 ('Prévoyez des jours de repos', 'Le corps progresse pendant la récupération, pas seulement pendant l’effort.', '2026-11-09', 9),
 ('Étirez-vous après l’effort', 'Quelques étirements doux aident à relâcher les muscles sollicités.', '2026-11-09', 9),
 ('Écoutez les signaux de fatigue', 'Une fatigue qui persiste indique qu’il faut ralentir.', '2026-11-09', 9),

 ('Lavez-vous les mains régulièrement', 'C’est un geste simple pour limiter la transmission des microbes.', '2026-11-10', 10),
 ('Passez du temps dehors', 'La lumière du jour et l’air frais soutiennent l’humeur et le sommeil.', '2026-11-10', 10),
 ('Faites un bilan régulier avec votre médecin', 'Un suivi régulier permet de repérer tôt un éventuel problème.', '2026-11-10', 10);

-- À l'intégration (relation « rédige (admin) » du diagramme), la personne qui fait le module
-- Utilisateur pourra ajouter la clé étrangère. Exemple, une fois la table utilisateur créée :
-- ALTER TABLE conseil ADD COLUMN id_utilisateur INT NULL,
--   ADD CONSTRAINT fk_conseil_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateur (id);
