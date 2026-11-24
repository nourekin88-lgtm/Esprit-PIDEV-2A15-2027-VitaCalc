<?php
declare(strict_types=1);

final class NutritionController extends Controller
{
    public const EXTRA_SECTIONS = [
        'preparation-repas' => ['Préparation des repas', 'utensils'],
        'suivi-sportif' => ['Suivi sportif', 'dumbbell'],
        'evenements' => ['Événements', 'calendar'],
        'conseils' => ['Conseils', 'leaf'],
    ];

    public function blankSection(string $section): void
    {
        $this->render(null, [
            'title' => self::EXTRA_SECTIONS[$section][0],
            'currentPage' => $section,
        ]);
    }

    public function reminders(): void
    {
        $this->render('notification/viewReadAllNotification', ['title' => 'Notifications', 'currentPage' => 'notifications']);
    }

    public function home(bool $adminArea = false): void
    {
        $pdo = database();
        $collections = NutritionRepository::collections();
        $counts = [];
        $latest = [];
        foreach ($collections as $key => $meta) {
            $repository = new NutritionRepository($pdo, $key);
            $counts[$key] = $repository->count();
            $latest[$key] = $repository->newest();
        }

        $currentPage = $adminArea ? 'admin' : 'accueil';
        $title = $adminArea ? 'Tableau de bord' : 'Espace nutrition';
        $this->render('home/viewAffichAllHome', compact('title', 'currentPage', 'adminArea', 'collections', 'counts', 'latest'));
    }

    public function catalog(string $collection, bool $adminArea = false): void
    {
        $meta = NutritionRepository::collections()[$collection];
        $query = mb_substr(trim(input($_GET, 'q')), 0, 120, 'UTF-8');
        $pdo = database();
        $records = (new NutritionRepository($pdo, $collection))->all($query);
        $catalogueMap = $adminArea ? [] : (new NutritionPlanRepository($pdo))->catalogueMap($collection);
        $title = $meta['title'];
        $currentPage = $adminArea ? 'admin-' . $collection : $collection;
        $view = $adminArea ? 'nutrition/viewManageNutrition' : 'nutrition/viewReadAllNutrition';
        $this->render($view, compact('title', 'currentPage', 'adminArea', 'collection', 'meta', 'query', 'records', 'catalogueMap'));
    }

    public function detail(string $collection): void
    {
        $pdo = database();
        $record = $this->loadRecord(new NutritionRepository($pdo, $collection));
        if ($record === null) {
            return;
        }

        $meta = NutritionRepository::collections()[$collection];
        $goalCode = (new NutritionPlanRepository($pdo))->catalogueMap($collection)[$record['id']] ?? null;
        $this->render('nutrition/viewReadNutrition', [
            'title' => $record['nom'],
            'currentPage' => $collection,
            'collection' => $collection,
            'meta' => $meta,
            'record' => $record,
            'goalCode' => $goalCode,
        ]);
    }
}
