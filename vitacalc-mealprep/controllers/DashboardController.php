<?php

namespace App\Controllers;

use App\Models\Avis;
use App\Models\Favori;
use App\Models\Ingredient;
use App\Models\Recette;
use App\Models\Utilisateur;

/** Back Office (administrateur) : tableau de bord. */
class DashboardController extends Controller
{
    protected string $layout = 'back';
    protected string $page   = 'admin';
    protected string $title  = 'Dashboard | VitaCalc Admin';

    public function __construct()
    {
        $this->requireAdmin();
    }

    public function index(): void
    {
        $nbRecettes     = Recette::count();
        $nbIngredients  = Ingredient::count();
        $nbClients      = Utilisateur::countByRole(ROLE_CLIENT);
        $nbFavoris      = Favori::count();
        $top            = Recette::topFavoris(5);
        $maxFav         = max(1, ...array_column($top ?: [['nbFavoris' => 1]], 'nbFavoris'));
        $dernieres      = Recette::recent(5);
        $nbAvis         = Avis::count();
        $meilleures     = Recette::topNotees(5);

        $this->render('back/dashboard', compact(
            'nbRecettes', 'nbIngredients', 'nbClients', 'nbFavoris', 'top', 'maxFav', 'dernieres', 'nbAvis', 'meilleures'
        ));
    }
}
