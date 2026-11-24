<?php

namespace App\Controllers;

use App\Models\Avis;
use App\Models\Recette;

/** Notes et avis : le client donne (ou modifie / retire) sa note de 1 à 5 étoiles sur une recette. */
class AvisController extends Controller
{
    public function save(): void
    {
        $this->requireLogin('Connectez-vous pour noter une recette ⭐');

        $id      = (int) ($_POST['id'] ?? 0);
        $recette = Recette::find($id);
        if (!$this->isPost() || !$recette) {
            $this->redirect('recettes');
        }
        $errors = Avis::validate($_POST);
        if ($errors) {
            setFlash('danger', implode(' ', $errors));
        } else {
            Avis::donner((int) currentUser()['id'], $id, (int) $_POST['note'], (string) ($_POST['commentaire'] ?? ''));
            setFlash('success', '⭐ Merci ! Votre avis sur « ' . $recette['nom'] . ' » est enregistré.');
        }
        $this->redirectTo(url('recette', ['id' => $id]) . '#avis');
    }

    public function delete(): void
    {
        $this->requireLogin('Connectez-vous pour gérer vos avis.');

        $id = (int) ($_POST['id'] ?? 0);
        if ($this->isPost() && Recette::find($id)) {
            Avis::retirer((int) currentUser()['id'], $id);
            setFlash('success', 'Votre avis a été retiré.');
        }
        $this->redirectTo(url('recette', ['id' => $id]) . '#avis');
    }
}
