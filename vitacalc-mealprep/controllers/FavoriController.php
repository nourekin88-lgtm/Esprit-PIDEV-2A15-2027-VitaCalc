<?php

namespace App\Controllers;

use App\Models\Favori;
use App\Models\Recette;

/** Favoris : « enregistre (favoris) » entre Utilisateur et Recette. */
class FavoriController extends Controller
{
    protected string $layout = 'front';

    public function index(): void
    {
        $this->requireLogin('Connectez-vous pour retrouver vos recettes favorites.');

        $this->page  = 'favoris';
        $this->title = 'Mes favoris | VitaCalc';
        $recettes    = Recette::favorisOf((int) currentUser()['id']);
        $mesFavoris  = array_column($recettes, 'id');

        $this->render('front/favoris', compact('recettes', 'mesFavoris'));
    }

    /** Ajoute / retire une recette des favoris (POST), puis revient sur la page d'origine. */
    public function toggle(): void
    {
        $this->requireLogin('Connectez-vous pour enregistrer des favoris.');

        $id = (int) ($_POST['id'] ?? 0);
        if ($this->isPost() && ($recette = Recette::find($id))) {
            $added = Favori::toggle((int) currentUser()['id'], $id);
            setFlash('success', $added
                ? '❤️ « ' . $recette['nom'] . ' » est dans vos favoris.'
                : '« ' . $recette['nom'] . ' » a été retirée de vos favoris.');
        }
        $this->redirectTo(safeRedirect($_POST['redirect'] ?? null, url('recettes')));
    }
}
