<?php
require_once __DIR__ . '/AdminController.php';

class ConseilAdminController extends AdminController
{
    protected string $page  = 'conseils';
    protected string $title = 'Gestion des conseils';

    public function liste(): void
    {
        $filtre = (int) ($_GET['categorie'] ?? 0) ?: null;
        $this->render('conseils-liste', [
            'conseils'   => Conseil::listeAvecCategorie($filtre),
            'categories' => CategorieConseil::all(),
            'filtre'     => $filtre,
        ]);
    }

    public function formulaire(): void
    {
        $id      = (int) ($_GET['id'] ?? 0);
        $conseil = $id ? Conseil::find($id) : new Conseil(null, '', '', date('Y-m-d'), 0);
        if (!$conseil) {
            $this->redirect('conseils', 'Ce conseil n’existe pas.', 'error');
        }
        $this->afficher($conseil);
    }

    public function enregistrer(): void
    {
        $this->exigerPost();
        $id = (int) ($_POST['id'] ?? 0);
        $c  = new Conseil($id ?: null, trim($_POST['titre'] ?? ''), trim($_POST['contenu'] ?? ''),
                          trim($_POST['date_publication'] ?? ''), (int) ($_POST['id_categorie'] ?? 0));

        $erreurs = [];
        if ($c->titre === '' || self::longueur($c->titre) > 150) { $erreurs['titre'] = 'Le titre est obligatoire (150 caractères maximum).'; }
        if ($c->contenu === '')                                { $erreurs['contenu'] = 'Le contenu est obligatoire.'; }
        if (!CategorieConseil::find($c->idCategorie))          { $erreurs['id_categorie'] = 'Choisissez une catégorie.'; }
        $d = DateTime::createFromFormat('Y-m-d', $c->datePublication);
        if (!$d || $d->format('Y-m-d') !== $c->datePublication) { $erreurs['date_publication'] = 'Date invalide.'; }

        if ($erreurs) {
            $this->afficher($c, $erreurs);
            return;
        }
        $c->save();
        $this->redirect('conseils', $id ? 'Conseil modifié.' : 'Conseil ajouté.');
    }

    public function supprimer(): void
    {
        $this->exigerPost();
        Conseil::delete((int) ($_POST['id'] ?? 0));
        $this->redirect('conseils', 'Conseil supprimé.');
    }

    private function afficher(Conseil $conseil, array $erreurs = []): void
    {
        $this->title = $conseil->id ? 'Modifier un conseil' : 'Ajouter un conseil';
        $this->render('conseil-form', ['conseil' => $conseil, 'categories' => CategorieConseil::all(), 'erreurs' => $erreurs]);
    }
}
