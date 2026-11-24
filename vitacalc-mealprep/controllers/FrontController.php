<?php

namespace App\Controllers;

use App\Models\Avis;
use App\Models\Favori;
use App\Models\Ingredient;
use App\Models\Recette;
use App\Models\RecetteIngredient;

/**
 * Front Office.
 *  - Visiteur : voit l'accueil et la liste des recettes ; « Voir plus » mène à l'inscription.
 *  - Client   : voit la recette complète (ingrédients, étapes, portions), les ingrédients, ses favoris.
 */
class FrontController extends Controller
{
    protected string $layout = 'front';

    /** Nombre de recettes visibles par un visiteur non inscrit. */
    private const VISITOR_LIMIT = 3;

    public function home(): void
    {
        $this->page  = 'home';
        $this->title = 'VitaCalc Meal Prep | Mangez sain, simplement';

        $user          = currentUser();
        $recettes      = Recette::recent(3);
        $nbRecettes    = Recette::count();
        $nbIngredients = Ingredient::count();
        $populaires    = array_slice(Ingredient::all(), 0, 6);
        $favoris       = $user ? array_slice(Recette::favorisOf((int) $user['id']), 0, 3) : [];
        $rapides       = $user ? array_slice(Recette::all('', 'rapide'), 0, 3) : [];

        $parMoment     = Recette::countByMoment();

        $this->render('front/home', compact('recettes', 'nbRecettes', 'nbIngredients', 'populaires', 'favoris', 'rapides', 'parMoment'));
    }

    public function recettes(): void
    {
        $this->page  = 'recettes';
        $this->title = 'Nos recettes | VitaCalc';

        $search   = trim((string) ($_GET['q'] ?? ''));
        $duree    = (string) ($_GET['duree'] ?? '');
        $moment   = (string) ($_GET['moment'] ?? '');
        $moment   = isset(Recette::MOMENTS[$moment]) ? $moment : '';
        $recettes = Recette::all($search, $duree, $moment);
        // Visiteur non inscrit : aperçu limité à 3 recettes (le reste est réservé aux comptes)
        $apercu = !isLoggedIn() && count($recettes) > self::VISITOR_LIMIT;
        if (!isLoggedIn()) {
            $recettes = array_slice($recettes, 0, self::VISITOR_LIMIT);
        }
        $parMoment  = Recette::countByMoment($search, $duree);   // pastilles : nombre de recettes par moment
        $mesFavoris = isLoggedIn() ? Favori::idsForUser((int) currentUser()['id']) : [];

        $this->render('front/recettes', compact('recettes', 'search', 'duree', 'moment', 'parMoment', 'mesFavoris', 'apercu'));
    }

    /** Détail d'une recette : réservé aux utilisateurs inscrits. */
    public function recette(): void
    {
        $this->requireLogin('Créez un compte gratuit (ou connectez-vous) pour voir la recette complète 🥗');

        $recette = Recette::find((int) ($_GET['id'] ?? 0));
        if (!$recette) {
            $this->notFound();
        }
        $this->page  = 'recettes';
        $this->title = $recette['nom'] . ' | VitaCalc';

        $lignes     = RecetteIngredient::byRecette($recette['id']);
        $estFavori  = Favori::has((int) currentUser()['id'], $recette['id']);
        $avis       = Avis::forRecette($recette['id']);
        $monAvis    = Avis::ofUser((int) currentUser()['id'], $recette['id']);

        $this->render('front/recette_detail', compact('recette', 'lignes', 'estFavori', 'avis', 'monAvis'));
    }

    /**
     * MODE CUISINE : écran plein, une grande étape à la fois, avec minuteur intégré.
     * Layout « cook » (sans navbar ni footer pour ne garder que la recette à l'écran).
     */
    public function cuisine(): void
    {
        $this->requireLogin('Connectez-vous pour utiliser le mode cuisine 👩‍🍳');

        $recette = Recette::find((int) ($_GET['id'] ?? 0));
        if (!$recette) {
            $this->notFound();
        }
        $this->layout = 'cook';
        $this->page   = 'recettes';
        $this->title  = 'Mode cuisine : ' . $recette['nom'] . ' | VitaCalc';

        $lignes = RecetteIngredient::byRecette($recette['id']);
        $etapes = array_map(fn(string $t) => [
            'texte'   => $t,
            'minutes' => stepMinutes($t),
            'ings'    => stepIngredients($t, $lignes),   // photos réelles des ingrédients cités dans l'étape
            'action'  => stepAction($t),
        ], steps($recette['instructions']));
        $prenom = (string) (currentUser()['prenom'] ?? '');

        $this->render('front/cuisine', compact('recette', 'lignes', 'etapes', 'prenom'));
    }

    /**
     * « Qu'est-ce que j'ai dans mon frigo ? » : le client coche ses ingrédients,
     * la page classe les recettes selon le % d'ingrédients déjà disponibles (calcul en JavaScript, en direct).
     */
    public function frigo(): void
    {
        $this->requireLogin('Connectez-vous pour trouver des recettes avec ce que vous avez dans le frigo 🧊');

        $this->page  = 'frigo';
        $this->title = "Qu'est-ce que j'ai dans mon frigo ? | VitaCalc";

        $ingredients = Ingredient::all();
        usort($ingredients, fn($a, $b) => strcmp(normalizeText($a['nom']), normalizeText($b['nom'])));

        // Chaque ingrédient est rangé sur son étagère (laitiers, viandes, fruits, légumes, placard…)
        $shelves = array_fill_keys(array_keys(FRIDGE_SHELVES), []);
        foreach ($ingredients as $i) {
            $shelves[ingredientShelf($i['nom'])][] = $i;
        }

        $recettes = Recette::all();
        $frigoData = [];
        foreach ($recettes as $r) {
            $lignes = RecetteIngredient::byRecette($r['id']);
            $frigoData[] = [
                'id'   => $r['id'],
                'ings' => array_map(fn($l) => [
                    'i' => $l['id'], 'n' => $l['nom'],
                    'p' => imageAvailable($l['image'] ?? null) ? $l['image'] : '',
                    'e' => ingredientEmoji($l['nom']),
                ], $lignes),
            ];
        }
        $mesFavoris = Favori::idsForUser((int) currentUser()['id']);

        $this->render('front/frigo', compact('ingredients', 'shelves', 'recettes', 'frigoData', 'mesFavoris'));
    }

    /** Catalogue des ingrédients : réservé aux utilisateurs inscrits. */
    public function ingredients(): void
    {
        $this->requireLogin('Connectez-vous pour découvrir tous les ingrédients.');

        $this->page  = 'ingredients';
        $this->title = 'Nos ingrédients | VitaCalc';
        $ingredients = Ingredient::all();

        $this->render('front/ingredients', compact('ingredients'));
    }
}
