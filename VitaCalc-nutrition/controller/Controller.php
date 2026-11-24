<?php
declare(strict_types=1);

class Controller
{
    protected function render(?string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $title ??= 'Nutrition';
        $currentPage ??= '';
        $adminArea ??= false;
        $blankPage = $view === null;
        $message = $blankPage ? null : takeFlash();
        $content = '';
        if (!$blankPage) {
            ob_start();
            require __DIR__ . '/../view/' . $view . '.php';
            $content = ob_get_clean();
        }
        require __DIR__ . '/../view/view.php';
    }

    public function error(int $status, string $title, string $detail, bool $adminArea = false): void
    {
        http_response_code($status);
        $this->render('erreur/viewErreur', compact('status', 'title', 'detail', 'adminArea'));
    }

    protected function notFound(bool $adminArea = false): void
    {
        $this->error(404, 'Fiche introuvable', 'Cette fiche a peut-être été supprimée.', $adminArea);
    }

    protected function loadRecord(NutritionRepository $repository, bool $adminArea = false): ?array
    {
        $id = recordId();
        $record = $id === null ? null : $repository->find($id);
        if ($record === null) {
            $this->notFound($adminArea);
        }
        return $record;
    }
}
