<?php

namespace App\Controllers;

use App\Models\Favori;
use App\Models\Utilisateur;

/** Profil de l'utilisateur connecté (informations + mot de passe). */
class ProfilController extends Controller
{
    protected string $layout = 'front';

    public function index(): void
    {
        $this->requireLogin('Connectez-vous pour accéder à votre profil.', 'login');

        $this->page  = 'profil';
        $this->title = 'Mon profil | VitaCalc';
        $id          = (int) currentUser()['id'];
        $errors      = [];
        $form        = Utilisateur::find($id);

        if ($this->isPost()) {
            $form   = $_POST + $form;
            $errors = Utilisateur::validateProfile($id, $_POST);
            if (!$errors) {
                $updated = Utilisateur::updateProfile($id, $_POST);
                $_SESSION['user'] = Utilisateur::sessionData($updated);
                setFlash('success', 'Votre profil a été mis à jour ✅');
                $this->redirect('profil');
            }
        }
        $compte     = Utilisateur::find($id);
        $nbFavoris  = Favori::countForUser($id);

        $this->render('front/profil', compact('form', 'errors', 'compte', 'nbFavoris'));
    }
}
