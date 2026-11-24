<?php

namespace App\Controllers;

use App\Models\Ingredient;

/** Back Office : « Gérer les ingrédients » (catalogue). */
class IngredientController extends Controller
{
    protected string $layout = 'back';
    protected string $page   = 'admin-ingredients';
    protected string $title  = 'Ingrédients | VitaCalc Admin';

    public function __construct()
    {
        $this->requireAdmin();
    }

    public function index(): void
    {
        $ingredients = Ingredient::all();
        $this->render('back/ingredients', compact('ingredients'));
    }

    public function create(): void
    {
        $this->title = 'Nouvel ingrédient | VitaCalc Admin';
        $this->form([], [], url('admin-ingredient-store'), 'create');
    }

    public function store(): void
    {
        if (!$this->isPost()) {
            $this->redirect('admin-ingredients');
        }
        $data   = $_POST;
        $errors = Ingredient::validate($data);
        $this->applyUpload($data, $errors);
        if ($errors) {
            $this->title = 'Nouvel ingrédient | VitaCalc Admin';
            $this->form($data, $errors, url('admin-ingredient-store'), 'create');
            return;
        }
        Ingredient::create($data);
        setFlash('success', 'Ingrédient ajouté au catalogue 🥕');
        $this->redirect('admin-ingredients');
    }

    public function edit(): void
    {
        $id         = (int) ($_GET['id'] ?? 0);
        $ingredient = Ingredient::find($id);
        if (!$ingredient) {
            $this->notFound();
        }
        $this->title = "Modifier l'ingrédient | VitaCalc Admin";
        $this->form($ingredient, [], url('admin-ingredient-update', ['id' => $id]), 'edit');
    }

    public function update(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$this->isPost() || !Ingredient::find($id)) {
            $this->redirect('admin-ingredients');
        }
        $data   = $_POST;
        $errors = Ingredient::validate($data, $id);
        $this->applyUpload($data, $errors);
        if ($errors) {
            $this->title = "Modifier l'ingrédient | VitaCalc Admin";
            $this->form($data, $errors, url('admin-ingredient-update', ['id' => $id]), 'edit');
            return;
        }
        Ingredient::update($id, $data);
        setFlash('success', 'Ingrédient modifié avec succès ✅');
        $this->redirect('admin-ingredients');
    }

    public function delete(): void
    {
        if ($this->isPost()) {
            $id = (int) ($_POST['id'] ?? 0);
            if (Ingredient::delete($id)) {
                setFlash('success', 'Ingrédient supprimé du catalogue.');
            } else {
                setFlash('danger', 'Impossible : cet ingrédient est utilisé par au moins une recette. Retirez-le d\'abord de ces recettes.');
            }
        }
        $this->redirect('admin-ingredients');
    }

    /* ------------------------------------------------------------------ */

    private function applyUpload(array &$data, array &$errors): void
    {
        if ($errors) {
            return;
        }
        $up = $this->saveUpload('imageFile');
        if ($up['error']) {
            $errors['imageFile'] = $up['error'];
        } elseif ($up['path']) {
            $data['image'] = $up['path'];
        }
    }

    private function form(array $ingredient, array $errors, string $action, string $mode): void
    {
        $this->render('back/ingredient_form', compact('ingredient', 'errors', 'action', 'mode'));
    }
}
