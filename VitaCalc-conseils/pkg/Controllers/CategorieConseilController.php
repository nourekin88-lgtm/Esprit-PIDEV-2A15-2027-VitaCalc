<?php
require_once __DIR__ . '/AdminController.php';

class CategorieConseilController extends AdminController
{
    protected string $page  = 'categories';
    protected string $title = 'Gestion des catégories';

    public function liste(): void
    {
        $this->render('categories-liste', ['categories' => CategorieConseil::avecNombre()]);
    }

    public function formulaire(): void
    {
        $id  = (int) ($_GET['id'] ?? 0);
        $cat = $id ? CategorieConseil::find($id) : new CategorieConseil();
        if (!$cat) {
            $this->redirect('categories', 'Cette catégorie n’existe pas.', 'error');
        }
        $this->afficher($cat);
    }

    public function enregistrer(): void
    {
        $this->exigerPost();
        $id  = (int) ($_POST['id'] ?? 0);
        $cat = new CategorieConseil($id ?: null, trim($_POST['nom'] ?? ''), trim($_POST['description'] ?? '') ?: null);

        $erreurs = [];
        if ($cat->nom === '' || self::longueur($cat->nom) > 100) { $erreurs['nom'] = 'Le nom est obligatoire (100 caractères maximum).'; }
        if (!$erreurs) {
            try {
                $cat->save();
            } catch (PDOException $e) {
                if ((int) ($e->errorInfo[1] ?? 0) !== 1062) { throw $e; }
                $erreurs['nom'] = 'Une catégorie porte déjà ce nom.';
            }
        }
        if ($erreurs) {
            $this->afficher($cat, $erreurs);
            return;
        }
        $this->redirect('categories', $id ? 'Catégorie modifiée.' : 'Catégorie ajoutée.');
    }

    public function supprimer(): void
    {
        $this->exigerPost();
        $id = (int) ($_POST['id'] ?? 0);
        $nb = CategorieConseil::nombreConseils($id);
        if ($nb > 0) {
            $this->redirect('categories', "Suppression impossible : cette catégorie contient $nb conseil(s). Supprimez-les ou changez leur catégorie d’abord.", 'error');
        }
        CategorieConseil::delete($id);
        $this->redirect('categories', 'Catégorie supprimée.');
    }

    private function afficher(CategorieConseil $cat, array $erreurs = []): void
    {
        $this->title = $cat->id ? 'Modifier une catégorie' : 'Ajouter une catégorie';
        $this->render('categorie-form', ['categorie' => $cat, 'erreurs' => $erreurs]);
    }
}
