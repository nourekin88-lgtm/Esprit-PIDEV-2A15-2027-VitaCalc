<?php

namespace App\Models;

/**
 * Modèle de base SANS base de données.
 * Les données sont stockées dans data/vitacalc.json (créé automatiquement avec des exemples).
 *
 * Tables du fichier (correspondent au diagramme de classes) :
 *   utilisateurs, ingredients, recettes, recette_ingredients (classe d'association), favoris, avis
 */
abstract class Model
{
    private static ?array $store = null;

    protected static function load(): array
    {
        if (self::$store !== null) {
            return self::$store;
        }
        if (!is_file(DATA_FILE)) {
            $dir = dirname(DATA_FILE);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            // Le dossier data contient les mots de passe hachés : on interdit l'accès direct (Apache)
            file_put_contents($dir . '/.htaccess',
                "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n");
            self::save(self::seed());
            return self::$store;
        }
        $data = json_decode((string) file_get_contents(DATA_FILE), true);
        if (!is_array($data)) {
            $data = self::seed();
            self::save($data);
        } elseif (self::migrate($data)) {
            self::save($data); // ancien fichier mis à niveau (moments des repas, avis)
        }
        self::$store = $data;
        return self::$store;
    }

    /**
     * Met à niveau un ancien data/vitacalc.json : ajoute la table « avis »
     * et le « moment » (matin / déjeuner / dîner) des recettes qui n'en ont pas encore.
     * @return bool true si le fichier a été modifié
     */
    private static function migrate(array &$data): bool
    {
        $changed = false;
        if (!isset($data['avis']) || !is_array($data['avis'])) {
            $data['avis'] = [];
            $changed = true;
        }
        foreach ($data['recettes'] ?? [] as $k => $r) {
            if (!isset(Recette::MOMENTS[$r['moment'] ?? ''])) {
                $data['recettes'][$k]['moment'] = Recette::guessMoment((string) ($r['nom'] ?? ''));
                $changed = true;
            }
        }
        return $changed;
    }

    protected static function save(array $store): void
    {
        self::$store = $store;
        file_put_contents(DATA_FILE, json_encode($store, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /** Données de démonstration : 2 comptes, 12 recettes healthy, catalogue d'ingrédients, 2 favoris. */
    private static function seed(): array
    {
        $store = [
            'next' => ['utilisateur' => 1, 'recette' => 1, 'ingredient' => 1],
            'utilisateurs' => [], 'ingredients' => [], 'recettes' => [], 'recette_ingredients' => [], 'favoris' => [], 'avis' => [],
        ];
        $today = date('Y-m-d');

        // Comptes de démonstration (voir README)
        $comptes = [
            ['VitaCalc', 'Admin', 'admin@vitacalc.tn', 'Admin@123', '+216 71 000 000', ROLE_ADMIN],
            ['Ben Salah', 'Sara', 'client@vitacalc.tn', 'Client@123', '+216 20 123 456', ROLE_CLIENT],
        ];
        foreach ($comptes as [$nom, $prenom, $email, $pwd, $tel, $role]) {
            $store['utilisateurs'][] = [
                'id' => $store['next']['utilisateur']++, 'nom' => $nom, 'prenom' => $prenom, 'email' => $email,
                'motDePasseHash' => password_hash($pwd, PASSWORD_DEFAULT), 'telephone' => $tel,
                'role' => $role, 'dateInscription' => $today,
            ];
        }

        // [nom, description, étapes, temps (min), portions, fichier photo, [[ingrédient, quantité, unité], ...]]
        $demo = [
            ['Bowl poulet & quinoa', 'Un bowl protéiné et équilibré, parfait pour le déjeuner de la semaine.',
             "Cuire le quinoa.\nGriller le poulet assaisonné.\nDécouper les légumes.\nAssembler dans des boîtes et conserver au frais.", 35, 4, 'bowl-poulet-quinoa',
             [['Quinoa', 200, 'g'], ['Blanc de poulet', 500, 'g'], ['Brocoli', 1, 'pièce'], ["Huile d'olive", 2, 'cuillère à soupe']]],
            ['Overnight oats banane', 'Petit-déjeuner prêt la veille, riche en fibres et sans cuisson.',
             "Mélanger les flocons d'avoine et le lait.\nAjouter la banane écrasée.\nLaisser reposer une nuit au réfrigérateur.", 10, 2, 'overnight-oats-banane',
             [["Flocons d'avoine", 100, 'g'], ['Lait', 250, 'ml'], ['Banane', 2, 'pièce']]],
            ['Salade quinoa avocat', 'Une salade fraîche et rassasiante, pleine de bons gras.',
             "Cuire le quinoa et le laisser refroidir.\nCouper l'avocat, la tomate et le concombre en dés.\nMélanger avec le quinoa.\nAssaisonner avec l'huile d'olive et le jus de citron.", 20, 3, 'salade-quinoa-avocat',
             [['Quinoa', 150, 'g'], ['Avocat', 2, 'pièce'], ['Tomate', 3, 'pièce'], ['Concombre', 1, 'pièce'], ['Citron', 1, 'pièce'], ["Huile d'olive", 2, 'cuillère à soupe']]],
            ['Saumon grillé & brocoli', 'Riche en oméga-3 : un dîner léger et plein de saveurs.',
             "Cuire le riz.\nMariner le saumon avec le citron et l'huile d'olive.\nGriller le saumon 4 minutes de chaque côté.\nCuire le brocoli à la vapeur et servir ensemble.", 30, 4, 'saumon-grille-brocoli',
             [['Saumon', 600, 'g'], ['Brocoli', 1, 'pièce'], ['Riz', 200, 'g'], ['Citron', 1, 'pièce'], ["Huile d'olive", 1, 'cuillère à soupe']]],
            ['Omelette aux épinards', 'Une omelette moelleuse, riche en protéines et en fer.',
             "Battre les œufs avec une pincée de sel.\nFaire revenir les épinards dans l'huile d'olive.\nVerser les œufs, ajouter le fromage.\nCuire à feu doux puis plier l'omelette.", 15, 2, 'omelette-epinards',
             [['Oeufs', 6, 'pièce'], ['Épinards', 150, 'g'], ['Fromage', 50, 'g'], ["Huile d'olive", 1, 'cuillère à soupe'], ['Sel', 1, 'pincée']]],
            ['Smoothie bowl fraise-banane', 'Un petit-déjeuner coloré, frais et plein de vitamines.',
             "Mixer la banane, les fraises et le yaourt.\nVerser dans un bol.\nAjouter les flocons d'avoine et un filet de miel.", 10, 2, 'smoothie-bowl-fraise-banane',
             [['Banane', 2, 'pièce'], ['Fraise', 200, 'g'], ['Yaourt', 250, 'g'], ['Miel', 1, 'cuillère à soupe'], ["Flocons d'avoine", 30, 'g']]],
            ['Wrap poulet & légumes croquants', 'Un déjeuner à emporter, léger et facile à préparer.',
             "Griller le poulet coupé en lanières.\nCouper la tomate et la laitue.\nTartiner la tortilla de yaourt.\nGarnir, rouler et envelopper.", 25, 4, 'wrap-poulet-legumes',
             [['Blanc de poulet', 400, 'g'], ['Tortilla complète', 4, 'pièce'], ['Tomate', 2, 'pièce'], ['Laitue', 100, 'g'], ['Yaourt', 100, 'g']]],
            ['Soupe de lentilles', 'Réconfortante, économique et riche en fibres. Elle se congèle très bien.',
             "Faire revenir l'oignon et l'ail.\nAjouter les carottes, les tomates et les lentilles.\nCouvrir d'eau et cuire 30 minutes.\nMixer à votre goût.", 40, 5, 'soupe-lentilles',
             [['Lentilles', 250, 'g'], ['Carotte', 2, 'pièce'], ['Oignon', 1, 'pièce'], ['Ail', 2, 'pièce'], ['Tomate', 2, 'pièce']]],
            ['Pâtes complètes aux légumes', "Un plat complet et coloré, prêt en moins d'une demi-heure.",
             "Cuire les pâtes.\nFaire sauter la courgette et les tomates avec l'ail.\nMélanger aux pâtes.\nParsemer de fromage râpé.", 25, 4, 'pates-completes-legumes',
             [['Pâtes complètes', 300, 'g'], ['Courgette', 2, 'pièce'], ['Tomate', 4, 'pièce'], ['Ail', 2, 'pièce'], ['Fromage', 40, 'g'], ["Huile d'olive", 1, 'cuillère à soupe']]],
            ['Curry de pois chiches', 'Un curry végétarien, parfumé et rassasiant.',
             "Cuire le riz.\nFaire revenir l'oignon avec les épices.\nAjouter les tomates et les pois chiches.\nIncorporer les épinards en fin de cuisson.", 35, 4, 'curry-pois-chiches',
             [['Pois chiches', 400, 'g'], ['Tomate', 3, 'pièce'], ['Oignon', 1, 'pièce'], ['Épinards', 100, 'g'], ['Riz', 200, 'g']]],
            ['Pudding de chia aux fraises', 'Un dessert ou petit-déjeuner sain, préparé la veille.',
             "Mélanger les graines de chia, le lait et le miel.\nLaisser gonfler au frigo toute la nuit.\nServir avec des fraises fraîches.", 10, 2, 'pudding-chia-fraises',
             [['Graines de chia', 40, 'g'], ['Lait', 250, 'ml'], ['Miel', 1, 'cuillère à café'], ['Fraise', 100, 'g']]],
            ['Poulet rôti & patate douce', 'Un classique du meal prep : simple, savoureux et complet.',
             "Préchauffer le four à 200°C.\nCouper les patates douces et le brocoli.\nAssaisonner le poulet avec l'ail et l'huile d'olive.\nEnfourner 35 minutes.", 45, 4, 'poulet-roti-patate-douce',
             [['Blanc de poulet', 600, 'g'], ['Patate douce', 2, 'pièce'], ['Brocoli', 1, 'pièce'], ['Ail', 2, 'pièce'], ["Huile d'olive", 2, 'cuillère à soupe']]],
        ];

        $ingIds = [];
        foreach ($demo as [$nom, $desc, $etapes, $temps, $portions, $photo, $liste]) {
            $idR = $store['next']['recette']++;
            $store['recettes'][] = [
                'id' => $idR, 'nom' => $nom, 'description' => $desc, 'instructions' => $etapes,
                'tempsPreparation' => $temps, 'nombrePortions' => $portions,
                'moment' => Recette::guessMoment($nom),
                'image' => 'assets/images/recettes/' . $photo . '.jpg',
            ];
            foreach ($liste as [$n, $q, $u]) {
                $key = normalizeText($n);
                if (!isset($ingIds[$key])) {
                    $ingIds[$key] = $store['next']['ingredient']++;
                    $store['ingredients'][] = ['id' => $ingIds[$key], 'nom' => $n, 'image' => ''];
                }
                $store['recette_ingredients'][] = [
                    'idRecette' => $idR, 'idIngredient' => $ingIds[$key], 'quantite' => (float) $q, 'unite' => $u,
                ];
            }
        }

        // Favoris de démonstration (client : Sara)
        $store['favoris'] = [['idUtilisateur' => 2, 'idRecette' => 1], ['idUtilisateur' => 2, 'idRecette' => 3]];
        return $store;
    }
}
