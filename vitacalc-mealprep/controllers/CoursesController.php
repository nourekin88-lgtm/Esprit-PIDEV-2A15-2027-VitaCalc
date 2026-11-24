<?php

namespace App\Controllers;

use App\Models\Favori;
use App\Models\Recette;
use App\Models\RecetteIngredient;

/**
 * Liste de courses intelligente : le client choisit des recettes et des portions,
 * la page additionne automatiquement les ingrédients identiques.
 */
class CoursesController extends Controller
{
    protected string $layout = 'front';

    public function index(): void
    {
        $this->requireLogin('Connectez-vous pour préparer votre liste de courses.');

        $this->page  = 'courses';
        $this->title = 'Ma liste de courses | VitaCalc';

        $recettes = Recette::all();
        $coursesData = [];
        foreach ($recettes as $r) {
            $lignes = [];
            foreach (RecetteIngredient::byRecette($r['id']) as $l) {
                $lignes[] = ['ing' => $l['id'], 'nom' => $l['nom'], 'q' => (float) $l['quantite'], 'u' => $l['unite'], 'img' => imageAvailable($l['image'] ?? null) ? $l['image'] : '', 'emo' => ingredientEmoji($l['nom'])];
            }
            $coursesData[] = ['id' => $r['id'], 'portions' => $r['nombrePortions'], 'lignes' => $lignes];
        }
        $preselect = (int) ($_GET['add'] ?? 0);
        $mesFavoris = Favori::idsForUser((int) currentUser()['id']);

        $this->render('front/courses', compact('recettes', 'coursesData', 'preselect', 'mesFavoris'), 'front/courses_scripts');
    }
}
