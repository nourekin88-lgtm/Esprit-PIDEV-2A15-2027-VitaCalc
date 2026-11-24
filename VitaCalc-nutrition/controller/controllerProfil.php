<?php
declare(strict_types=1);

final class ProfileController extends Controller
{
    private const GENDERS = [
        'femme' => 'Femme',
        'homme' => 'Homme',
        'non_binaire' => 'Non-binaire',
        'non_precise' => 'Je préfère ne pas répondre',
    ];

    public function register(string $method): void
    {
        $currentPage = 'inscription';
        $genders = self::GENDERS;
        $id = nutritionProfileId();
        $profile = null;
        $repository = null;
        $pdo = null;
        if ($id !== null) {
            $pdo = database();
            $repository = new ProfileRepository($pdo);
            $profile = $repository->find($id);
            if ($profile === null) {
                unset($_SESSION['nutrition_profile_id']);
                $id = null;
            }
        }

        $editing = $profile !== null;
        $action = $method === 'POST' ? input($_POST, 'action') : '';
        $summaryAction = in_array($action, ['objectif', 'remplacement'], true);
        $pendingGoal = $method === 'POST' ? input($_POST, 'objectif_suggere') : input($_GET, 'objectif');
        if (!isset(NutritionPlan::GOALS[$pendingGoal])) {
            $pendingGoal = '';
        }
        if ($summaryAction && !$editing) {
            redirect('inscription');
        }
        if ($editing && ($summaryAction || ($method !== 'POST' && input($_GET, 'modifier') !== '1'))) {
            $this->summary($pdo, $id, $profile, $action, $pendingGoal);
            return;
        }

        $values = ['genre' => '', 'age' => '', 'poids' => '', 'taille' => ''];
        if ($editing) {
            $values = [
                'genre' => $profile['genre'],
                'age' => (string) $profile['age'],
                'poids' => str_replace('.', ',', (string) $profile['poids']),
                'taille' => str_replace('.', ',', (string) $profile['taille']),
            ];
        }

        $errors = [];
        if ($method === 'POST') {
            foreach ($values as $key => $_) {
                $values[$key] = trim(input($_POST, $key));
            }
            if (!isset($genders[$values['genre']])) {
                $errors['genre'] = 'Sélectionnez une option pour continuer.';
            }
            $age = (int) $values['age'];
            if (!preg_match('/\A[0-9]{1,3}\z/', $values['age']) || $age < 1 || $age > 125) {
                $errors['age'] = 'Indiquez un âge entier entre 1 et 125 ans.';
            }
            $weight = $this->decimal($values['poids'], 0.1, 700);
            if ($weight === null) {
                $errors['poids'] = 'Indiquez un poids entre 0,1 et 700 kg, avec une décimale au maximum.';
            }
            $height = $this->decimal($values['taille'], 20, 300);
            if ($height === null) {
                $errors['taille'] = 'Indiquez une taille entre 20 et 300 cm, avec une décimale au maximum.';
            }
            if ($errors === []) {
                $pdo ??= database();
                $repository ??= new ProfileRepository($pdo);
                $profileId = $repository->save([
                    'genre' => $values['genre'], 'age' => $age, 'poids' => $weight, 'taille' => $height,
                ], $id);
                $_SESSION['nutrition_profile_id'] = $profileId;
                flash($editing ? 'Vos informations ont été mises à jour.' : 'Votre profil nutritionnel a été créé.');
                redirect('inscription', $pendingGoal === '' ? [] : ['objectif' => $pendingGoal]);
            }

            http_response_code(422);
        }

        $title = $editing ? 'Modifier mon profil' : 'Inscription';
        $this->render('profil/viewCreateProfil', compact('title', 'currentPage', 'genders', 'values', 'errors', 'editing', 'pendingGoal'));
    }

    private function summary(PDO $pdo, int $id, array $profile, string $action, string $pendingGoal): void
    {
        $title = 'Mon profil nutritionnel';
        $currentPage = 'inscription';
        $genders = self::GENDERS;
        $goals = NutritionPlan::GOALS;
        $activities = NutritionPlan::ACTIVITIES;
        $repository = new NutritionPlanRepository($pdo);
        $choice = $repository->choice($id);
        $goal = $choice['objectif'] ?? '';
        $activity = $choice['activite'] ?? NutritionPlan::DEFAULT_ACTIVITY;
        $choiceError = null;
        $menuError = null;

        if ($action === 'objectif') {
            $requestedGoal = input($_POST, 'objectif');
            $requestedActivity = input($_POST, 'activite');
            if ((int) $profile['age'] < 18 || !isset($goals[$requestedGoal], $activities[$requestedActivity])) {
                http_response_code(422);
                $choiceError = 'Choisissez un objectif et un niveau d’activité valides.';
            } else {
                $repository->saveChoice($id, $requestedGoal, $requestedActivity);
                flash('Votre objectif nutritionnel a été enregistré.');
                header('Location: ' . route('inscription') . '#regime-conseille', true, 303);
                exit;
            }
        }

        $estimate = NutritionPlan::estimate($profile, $activity);
        $selectedGoal = $pendingGoal === '' ? $goal : $pendingGoal;
        $menu = $context = null;
        $plan = $diet = $goalNotice = null;
        if ($estimate['adult'] && isset($goals[$goal])) {
            $goalNotice = NutritionPlan::goalNotice($goal, $estimate['bmi']);
            if ($goalNotice === null) {
                $plan = $goals[$goal];
                $diet = $repository->diet($goal);
            } else {
                $plan = ['title' => 'Idées de repas équilibrés'] + $goals['maintien'];
            }
            if ($estimate['energy'] !== null) {
                $planner = new MenuPlanner();
                $context = MenuPlanner::context($profile, $goal, $activity);
                $substitutions = $repository->substitutions($id, $context);
                $legacyContext = MenuPlanner::legacyContext($profile, $goal, $activity);
                if ($legacyContext !== null) {
                    $substitutions += $repository->substitutions($id, $legacyContext);
                }
                $menu = $planner->build(
                    $goalNotice === null ? $goal : 'maintien', $estimate, $substitutions
                );
            }
        }
        if ($action === 'remplacement') {
            $slot = input($_POST, 'emplacement');
            $food = input($_POST, 'aliment');
            $option = $menu['items'][$slot]['options'][$food] ?? null;
            if ($context !== null && hash_equals($context, input($_POST, 'contexte')) && $option !== null) {
                $repository->saveSubstitution($id, $context, $slot, $food, $option['grams']);
                flash('L’aliment a été remplacé. Les repères du menu ont été recalculés.');
                header('Location: ' . route('inscription') . '#aliment-' . rawurlencode($slot), true, 303);
                exit;
            }
            http_response_code(422);
            $menuError = 'Ce remplacement est indisponible pour le menu actuel.';
        }

        $this->render('profil/viewReadProfil', compact(
            'title', 'currentPage', 'genders', 'profile', 'goals', 'activities', 'goal', 'activity',
            'estimate', 'choiceError', 'goalNotice', 'plan', 'diet', 'selectedGoal',
            'menu', 'context', 'menuError'
        ));
    }

    private function decimal(string $value, float $minimum, float $maximum): ?string
    {
        if (!preg_match('/\A[0-9]{1,3}(?:[,.][0-9])?\z/', $value)) {
            return null;
        }
        $normalized = str_replace(',', '.', $value);
        $number = (float) $normalized;
        return $number >= $minimum && $number <= $maximum ? $normalized : null;
    }
}
