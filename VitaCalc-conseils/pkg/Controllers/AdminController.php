<?php
require_once __DIR__ . '/../Models/CategorieConseil.php';
require_once __DIR__ . '/../Models/Conseil.php';

if (!function_exists('h')) {
    function h($valeur): string { return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8'); }
}

/** Base des contrôleurs du Back Office : affichage dans le layout, messages, sécurité des formulaires. */
class AdminController
{
    protected string $page  = 'dashboard';
    protected string $title = 'Tableau de bord';

    public function dashboard(): void
    {
        $categories = CategorieConseil::avecNombre();
        $this->render('index', [
            'stats' => [
                'conseils'   => Conseil::count(),
                'categories' => count($categories),
                'ce_mois'    => Conseil::countCeMois(),
                'vides'      => count(array_filter($categories, fn($c) => (int) $c['nb'] === 0)),
            ],
            'derniers'   => Conseil::listeAvecCategorie(null, 5),
            'categories' => $categories,
        ]);
    }

    protected function render(string $view, array $data = []): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        $baseUrl     = self::baseUrl();
        $title       = $this->title;
        $currentPage = $this->page;
        $flash       = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        $csrf = $_SESSION['csrf'] ??= bin2hex(random_bytes(16));

        extract($data);
        ob_start();
        require __DIR__ . '/../Views/BackOffice/' . $view . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/BackOffice/layout.php';
    }

    protected function redirect(string $page, string $message = '', string $type = 'success'): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        if ($message !== '') { $_SESSION['flash'] = ['type' => $type, 'msg' => $message]; }
        header('Location: admin.php?page=' . $page);
        exit;
    }

    /** Refuse tout ce qui n'est pas un POST venant d'un de nos formulaires. */
    protected function exigerPost(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
            http_response_code(400);
            exit('Requête invalide.');
        }
    }

    protected static function longueur(string $texte): int
    {
        return function_exists('mb_strlen') ? mb_strlen($texte) : strlen($texte);
    }

    protected static function baseUrl(): string
    {
        $doc  = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'])) : '';
        $root = str_replace('\\', '/', (string) realpath(__DIR__ . '/../..'));
        $app  = ($doc !== '' && str_starts_with($root, $doc)) ? '/' . trim(substr($root, strlen($doc)), '/') : '/';
        return rtrim($app, '/') . '/pkg/Views/';
    }
}
