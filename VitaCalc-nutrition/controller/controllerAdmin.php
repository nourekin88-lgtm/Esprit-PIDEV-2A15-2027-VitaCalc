<?php
declare(strict_types=1);

final class AdminController extends Controller
{
    public function form(string $collection, bool $editing, string $method): void
    {
        $adminArea = true;
        $currentPage = 'admin-' . $collection;
        $meta = NutritionRepository::collections()[$collection];
        $title = ($editing ? 'Modifier un ' : 'Ajouter un ') . $meta['singular'];
        $repository = new NutritionRepository(database(), $collection);
        $record = $editing ? $this->loadRecord($repository, true) : ['id' => null, 'nom' => '', 'description' => ''];
        if ($record === null) {
            return;
        }

        $errors = [];
        if ($method === 'POST') {
            $record['nom'] = trim(input($_POST, 'nom'));
            $record['description'] = trim(input($_POST, 'description'));
            if ($record['nom'] === '' || !mb_check_encoding($record['nom'], 'UTF-8') || mb_strlen($record['nom'], 'UTF-8') > 120) {
                $errors['nom'] = 'Saisissez un nom entre 1 et 120 caractères.';
            }
            if ($record['description'] === '' || !mb_check_encoding($record['description'], 'UTF-8') || mb_strlen($record['description'], 'UTF-8') > 4000) {
                $errors['description'] = 'Saisissez une description entre 1 et 4 000 caractères.';
            }
            if ($errors === []) {
                if ($editing) {
                    if (!$repository->update($record['id'], $record['nom'], $record['description'])) {
                        $this->notFound(true);
                        return;
                    }
                } else {
                    $repository->create($record['nom'], $record['description']);
                }
                flash($editing ? 'La fiche a été mise à jour.' : 'La fiche a été ajoutée.');
                redirect($currentPage);
            }
            http_response_code(422);
        }

        $this->render('nutrition/viewFormNutrition', compact('title', 'currentPage', 'adminArea', 'collection', 'meta', 'record', 'errors', 'editing'));
    }

    public function delete(string $collection, string $method): void
    {
        $repository = new NutritionRepository(database(), $collection);
        $record = $this->loadRecord($repository, true);
        if ($record === null) {
            return;
        }

        $currentPage = 'admin-' . $collection;
        if ($method === 'POST') {
            if (!$repository->delete($record['id'])) {
                $this->notFound(true);
                return;
            }
            flash('La fiche a été supprimée.');
            redirect($currentPage);
        }

        $title = 'Supprimer la fiche';
        $adminArea = true;
        $this->render('nutrition/viewDeleteNutrition', compact('title', 'currentPage', 'adminArea', 'collection', 'record'));
    }
}
