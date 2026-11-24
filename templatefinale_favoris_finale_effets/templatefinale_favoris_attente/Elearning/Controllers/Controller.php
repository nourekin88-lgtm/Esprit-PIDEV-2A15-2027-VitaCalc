<?php
declare(strict_types=1);

/** Contrôleur de base : capture la vue puis l'insère dans le layout partagé. */
abstract class Controller
{
    protected function render(
        string $view,
        string $title,
        string $currentPage,
        string $layout = 'front',
        array  $data   = []
    ): void {
        $viewFile = BASE_PATH . '/Views/' . ($view === 'home' ? 'index' : ($view === 'not-found' ? '404' : $view)) . '.php';
        if (!is_file($viewFile)) {
            http_response_code(404);
            $viewFile = BASE_PATH . '/Views/index.php';
        }

        // Extrait les variables de $data dans le scope courant
        extract($data, EXTR_SKIP);

        $pageTitle = $title;
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = BASE_PATH . '/Views/'
            . ($layout === 'admin' ? 'admin-layout.php' : 'layout.php');
        require $layoutFile;
    }
}
