<?php

namespace App\Controllers;

use App\Models\Ingredient;
use App\Models\Recette;
use App\Models\RecetteIngredient;

/** Back Office : « Gérer les recettes » + « Composer une recette » (ajout des ingrédients). */
class RecetteController extends Controller
{
    protected string $layout = 'back';
    protected string $page   = 'admin-recettes';
    protected string $title  = 'Recettes | VitaCalc Admin';

    public function __construct()
    {
        $this->requireAdmin();
    }

    public function index(): void
    {
        $search   = trim((string) ($_GET['q'] ?? ''));
        $recettes = Recette::all($search);
        $this->render('back/recettes', compact('recettes', 'search'));
    }

    public function create(): void
    {
        $this->title = 'Nouvelle recette | VitaCalc Admin';
        $this->form([], [], [], url('admin-recette-store'), 'create');
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin-recettes');
        }
        $data   = $_POST;
        $parsed = Recette::parseLines($_POST);
        $errors = $this->validateAll($data, $parsed);
        if (!$errors) {
            $this->applyUpload($data, $errors);
        }
        if ($errors) {
            $this->title = 'Nouvelle recette | VitaCalc Admin';
            $this->form($data, $parsed['rows'], $errors, url('admin-recette-store'), 'create');
            return;
        }
        Recette::create($data, $parsed['lignes']);
        setFlash('success', 'Recette ajoutée avec ses ' . count($parsed['lignes']) . ' ingrédient(s) 🎉');
        $this->redirect('admin-recettes');
    }

    public function edit(): void
    {
        $id      = (int) ($_GET['id'] ?? 0);
        $recette = Recette::find($id);
        if (!$recette) {
            $this->notFound();
        }
        $rows = array_map(fn($l) => ['nom' => $l['nom'], 'quantite' => qty($l['quantite']), 'unite' => $l['unite']],
            RecetteIngredient::byRecette($id));
        $this->title = 'Modifier la recette | VitaCalc Admin';
        $this->form($recette, $rows, [], url('admin-recette-update', ['id' => $id]), 'edit');
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$this->isPost() || !Recette::find($id)) {
            $this->redirect('admin-recettes');
        }
        $data   = $_POST;
        $parsed = Recette::parseLines($_POST);
        $errors = $this->validateAll($data, $parsed);
        if (!$errors) {
            $this->applyUpload($data, $errors);
        }
        if ($errors) {
            $this->title = 'Modifier la recette | VitaCalc Admin';
            $this->form($data, $parsed['rows'], $errors, url('admin-recette-update', ['id' => $id]), 'edit');
            return;
        }
        Recette::update($id, $data, $parsed['lignes']);
        setFlash('success', 'Recette modifiée avec succès ✅');
        $this->redirect('admin-recettes');
    }

    public function delete(): void
    {
        if ($this->isPost()) {
            Recette::delete((int) ($_POST['id'] ?? 0));
            setFlash('success', 'Recette supprimée (avec ses lignes d\'ingrédients et ses favoris).');
        }
        $this->redirect('admin-recettes');
    }

    /* ------------------------------------------------------------------ */

    private function validateAll(array $data, array $parsed): array
    {
        $errors = Recette::validate($data);
        if ($parsed['errors']) {
            $errors['lignes'] = $parsed['errors'];
        }
        return $errors;
    }

    /** Si une photo est envoyée, elle remplace le lien. */
    private function applyUpload(array &$data, array &$errors): void
    {
        $up = $this->saveUpload('imageFile');
        if ($up['error']) {
            $errors['imageFile'] = $up['error'];
        } elseif ($up['path']) {
            $data['image'] = $up['path'];
        }
    }

    private function form(array $recette, array $rows, array $errors, string $action, string $mode): void
    {
        $this->render('back/recette_form', [
            'recette'   => $recette,
            'rows'      => $rows,
            'errors'    => $errors,
            'action'    => $action,
            'mode'      => $mode,
            'catalogue' => Ingredient::all(),
            'unites'    => Ingredient::UNITES,
        ], 'back/recette_form_scripts');
    }
}
