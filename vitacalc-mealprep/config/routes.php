<?php

use App\Controllers\AuthController;
use App\Controllers\AvisController;
use App\Controllers\CoursesController;
use App\Controllers\DashboardController;
use App\Controllers\FavoriController;
use App\Controllers\FrontController;
use App\Controllers\IngredientController;
use App\Controllers\ProfilController;
use App\Controllers\RecetteController;

/**
 * Table de routage : ?page=clé => [Contrôleur, méthode]
 *
 *  Visiteur      : home, recettes, login, register
 *  Client        : + recette (détail), cuisine, frigo, avis, ingredients, favoris, courses, profil
 *  Administrateur: + admin-* (Back Office), hérite des droits du client
 */
return [
    // ---------- VISITEUR ----------
    'home'     => [FrontController::class, 'home'],
    'recettes' => [FrontController::class, 'recettes'],
    'login'    => [AuthController::class, 'login'],
    'register' => [AuthController::class, 'register'],
    'logout'   => [AuthController::class, 'logout'],

    // ---------- CLIENT (connecté) ----------
    'recette'       => [FrontController::class, 'recette'],
    'ingredients'   => [FrontController::class, 'ingredients'],
    'favoris'       => [FavoriController::class, 'index'],
    'favori-toggle' => [FavoriController::class, 'toggle'],
    'courses'       => [CoursesController::class, 'index'],
    'cuisine'       => [FrontController::class, 'cuisine'],        // mode cuisine : plein écran, étapes + minuteur
    'frigo'         => [FrontController::class, 'frigo'],          // « Qu'est-ce que j'ai dans mon frigo ? »
    'avis-save'     => [AvisController::class, 'save'],            // note 1 à 5 étoiles + commentaire
    'avis-delete'   => [AvisController::class, 'delete'],
    'profil'        => [ProfilController::class, 'index'],

    // ---------- ADMINISTRATEUR (Back Office) ----------
    'admin'                   => [DashboardController::class, 'index'],

    'admin-recettes'          => [RecetteController::class, 'index'],
    'admin-recette-create'    => [RecetteController::class, 'create'],
    'admin-recette-store'     => [RecetteController::class, 'store'],
    'admin-recette-edit'      => [RecetteController::class, 'edit'],
    'admin-recette-update'    => [RecetteController::class, 'update'],
    'admin-recette-delete'    => [RecetteController::class, 'delete'],

    'admin-ingredients'       => [IngredientController::class, 'index'],
    'admin-ingredient-create' => [IngredientController::class, 'create'],
    'admin-ingredient-store'  => [IngredientController::class, 'store'],
    'admin-ingredient-edit'   => [IngredientController::class, 'edit'],
    'admin-ingredient-update' => [IngredientController::class, 'update'],
    'admin-ingredient-delete' => [IngredientController::class, 'delete'],
];
