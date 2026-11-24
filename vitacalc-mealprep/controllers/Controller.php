<?php

namespace App\Controllers;

/**
 * Contrôleur de base : capture la vue de contenu ($content) puis l'injecte dans le layout
 * (front | back | auth) qui assemble les includes / partials.
 */
abstract class Controller
{
    /** Clé de la page courante (état actif de la navbar / sidebar) */
    protected string $page = 'home';

    /** Titre affiché dans <title> */
    protected string $title = 'VitaCalc';

    /** Layout utilisé : 'front', 'back' ou 'auth' */
    protected string $layout = 'front';

    /**
     * Rend une vue de contenu (views/pages/{$view}.php) dans le layout.
     *
     * @param string      $view         nom de la vue (sans .php)
     * @param array       $data         variables disponibles dans la vue
     * @param string|null $scriptsView  vue de scripts spécifique à la page (optionnel)
     */
    protected function render(string $view, array $data = [], ?string $scriptsView = null): void
    {
        $content      = $this->capture(BASE_PATH . '/views/pages/' . $view . '.php', $data);
        $extraScripts = $scriptsView ? $this->capture(BASE_PATH . '/views/pages/' . $scriptsView . '.php', $data) : '';

        $title       = $this->title;
        $currentPage = $this->page;

        require BASE_PATH . '/views/layouts/' . $this->layout . '.php';
    }

    /**
     * Les noms de paramètres commencent par « __ » pour ne jamais entrer en collision avec une variable de vue
     * (ex. une vue qui reçoit une variable $data : avec EXTR_SKIP elle aurait été ignorée silencieusement).
     */
    private function capture(string $__file, array $__vars): string
    {
        extract($__vars, EXTR_SKIP);
        ob_start();
        require $__file;
        return (string) ob_get_clean();
    }

    /* ------------------------------------------------------------------ */
    /*  Accès : visiteur / client / administrateur                         */
    /* ------------------------------------------------------------------ */

    /** Réserve la page aux utilisateurs connectés. Un visiteur est envoyé vers $to (inscription ou connexion). */
    protected function requireLogin(string $message, string $to = 'register'): void
    {
        if (isLoggedIn()) {
            return;
        }
        if (!$this->isPost()) {
            $_SESSION['intended'] = currentUrl(); // on y revient après l'inscription / connexion
        }
        setFlash('info', $message);
        $this->redirect($to);
    }

    /** Réserve la page à l'administrateur (un client reçoit une page 403). */
    protected function requireAdmin(): void
    {
        $this->requireLogin('Connectez-vous avec un compte administrateur.', 'login');
        if (!isAdmin()) {
            $this->forbidden();
        }
    }

    /* ------------------------------------------------------------------ */

    protected function redirect(string $page, array $params = []): void
    {
        header('Location: ' . url($page, $params));
        exit;
    }

    protected function redirectTo(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /** Enregistre une photo envoyée depuis un formulaire. @return array ['path' => ?string, 'error' => ?string] */
    protected function saveUpload(string $field): array
    {
        $f = $_FILES[$field] ?? null;
        if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null];
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return ['path' => null, 'error' => "L'envoi de la photo a échoué."];
        }
        if ($f['size'] > UPLOAD_MAX_BYTES) {
            return ['path' => null, 'error' => 'La photo ne doit pas dépasser 3 Mo.'];
        }
        $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $mime  = function_exists('finfo_open') ? finfo_file(finfo_open(FILEINFO_MIME_TYPE), $f['tmp_name']) : ($f['type'] ?? '');
        if (!isset($types[$mime])) {
            return ['path' => null, 'error' => 'Format non accepté (JPG, PNG, WEBP ou GIF).'];
        }
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }
        $name = 'img_' . bin2hex(random_bytes(8)) . '.' . $types[$mime];
        if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . '/' . $name)) {
            return ['path' => null, 'error' => "Impossible d'enregistrer la photo (droits du dossier assets/uploads)."];
        }
        return ['path' => 'assets/uploads/' . $name, 'error' => null];
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->layout = 'front';
        $this->page   = '404';
        $this->title  = 'Page introuvable | VitaCalc';
        $this->render('404');
        exit;
    }

    protected function forbidden(): void
    {
        http_response_code(403);
        $this->layout = 'front';
        $this->page   = '403';
        $this->title  = 'Accès refusé | VitaCalc';
        $this->render('403');
        exit;
    }
}
