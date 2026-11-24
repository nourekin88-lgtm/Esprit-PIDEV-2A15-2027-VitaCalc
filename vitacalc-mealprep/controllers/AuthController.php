<?php

namespace App\Controllers;

use App\Models\Utilisateur;

/**
 * Cas d'utilisation « S'inscrire » et « Se connecter ».
 * Après connexion : l'administrateur va au Back Office, le client revient là où il était (ou à l'accueil).
 */
class AuthController extends Controller
{
    protected string $layout = 'auth';

    public function login(): void
    {
        if (isLoggedIn()) {
            $this->goAfterLogin(currentUser());
        }
        $errors = [];
        $old    = [];
        if ($this->isPost()) {
            $old  = ['email' => trim($_POST['email'] ?? '')];
            $user = Utilisateur::authenticate($old['email'], (string) ($_POST['motDePasse'] ?? ''));
            if ($user) {
                $this->openSession($user);
                setFlash('success', 'Bienvenue ' . $user['prenom'] . ' 👋');
                $this->goAfterLogin($user);
            }
            $errors['global'] = 'E-mail ou mot de passe incorrect.';
        }
        $this->page  = 'login';
        $this->title = 'Connexion | VitaCalc';
        $this->render('auth/login', compact('errors', 'old'));
    }

    public function register(): void
    {
        if (isLoggedIn()) {
            $this->goAfterLogin(currentUser());
        }
        $errors = [];
        $old    = [];
        if ($this->isPost()) {
            $old    = $_POST;
            $errors = Utilisateur::validateRegistration($_POST);
            if (!$errors) {
                $user = Utilisateur::register($_POST);
                $this->openSession($user);
                setFlash('success', 'Votre compte est créé. Bienvenue ' . $user['prenom'] . ' ! 🎉');
                $this->goAfterLogin($user);
            }
        }
        $this->page  = 'register';
        $this->title = 'Inscription | VitaCalc';
        $this->render('auth/register', compact('errors', 'old'));
    }

    public function logout(): void
    {
        if ($this->isPost()) {
            $_SESSION = [];
            session_regenerate_id(true);
            setFlash('success', 'Vous êtes déconnecté. À bientôt !');
        }
        $this->redirect('home');
    }

    /* ------------------------------------------------------------------ */

    private function openSession(array $user): void
    {
        session_regenerate_id(true);
        unset($_SESSION['csrf']);
        $_SESSION['user'] = Utilisateur::sessionData($user);
    }

    /** Redirection selon le rôle. */
    private function goAfterLogin(array $user): void
    {
        $intended = $_SESSION['intended'] ?? null;
        unset($_SESSION['intended']);

        if (($user['role'] ?? '') === ROLE_ADMIN) {
            $to = ($intended && str_starts_with($intended, 'index.php?page=admin')) ? $intended : url('admin');
        } else {
            $to = $intended ?: url('home');
        }
        $this->redirectTo(safeRedirect($to, url('home')));
    }
}
