<?php
declare(strict_types=1);

final class AuthController extends Controller
{
    public function login(string $method): void
    {
        if (isAdmin()) {
            redirect('admin');
        }

        $title = 'Connexion administrateur';
        $currentPage = 'connexion';
        $config = __DIR__ . '/../config/admin.local.php';
        $account = is_file($config) ? require $config : null;
        $configured = is_array($account) && isset($account['user'], $account['hash']);
        $user = '';
        $error = null;
        if ($method === 'POST' && $configured) {
            $user = trim(input($_POST, 'identifiant'));
            $password = input($_POST, 'mot_de_passe');
            $passwordMatches = strlen($password) <= 72 && !str_contains($password, "\0")
                && password_verify($password, $account['hash']);
            if ($passwordMatches && hash_equals($account['user'], $user)) {
                session_regenerate_id(true);
                $_SESSION['nutrition_admin'] = true;
                unset($_SESSION['nutrition_csrf']);
                redirect('admin');
            }
            http_response_code(422);
            $error = 'Identifiant ou mot de passe incorrect.';
        }

        $this->render('login', compact('title', 'currentPage', 'configured', 'user', 'error'));
    }

    public function logout(): void
    {
        unset($_SESSION['nutrition_admin'], $_SESSION['nutrition_csrf']);
        session_regenerate_id(true);
        flash('Vous êtes déconnecté.');
        redirect('accueil');
    }
}
