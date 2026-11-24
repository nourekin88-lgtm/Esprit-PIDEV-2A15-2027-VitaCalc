<?php
declare(strict_types=1);

final class NutritionPlan
{
    public const ACTIVITIES = [
        'faible' => ['label' => 'Peu active', 'factor' => 1.4],
        'moderee' => ['label' => 'Modérément active', 'factor' => 1.6],
        'active' => ['label' => 'Active', 'factor' => 1.8],
    ];

    public const DEFAULT_ACTIVITY = 'moderee';

    public const GOALS = [
        'prise' => [
            'label' => 'Prendre du poids',
            'icon' => 'trend-up',
            'title' => 'Alimentation enrichie progressivement',
            'advice' => 'Gardez des repas équilibrés et augmentez les apports progressivement. Une collation nourrissante ou un ajout de noix, d’huile d’olive ou de produit laitier peut compléter les repas.',
            'meals' => [
                'matin' => ['Matin', 'Porridge au yaourt, à la banane et à la purée de cacahuètes.'],
                'midi' => ['Midi', 'Riz complet, pois chiches, légumes rôtis et huile d’olive.'],
                'collation' => ['Collation', 'Pain complet, fromage frais et quelques noix.'],
                'soir' => ['Soir', 'Omelette, pommes de terre et légumes, avec du fromage si vous en mangez.'],
            ],
        ],
        'perte' => [
            'label' => 'Perdre du poids',
            'icon' => 'trend-down',
            'title' => 'Alimentation équilibrée pour une perte progressive',
            'advice' => 'Gardez des repas réguliers. Faites une large place aux légumes, ajoutez une source de protéines et gardez une portion de féculents complets adaptée à votre faim et à votre activité.',
            'meals' => [
                'matin' => ['Matin', 'Yaourt nature ou au soja, pomme et flocons d’avoine.'],
                'midi' => ['Midi', 'Légumes grillés, poulet ou tofu et boulgour complet.'],
                'collation' => ['Collation si faim', 'Un fruit et un yaourt nature.'],
                'soir' => ['Soir', 'Soupe de légumes aux lentilles, pain complet et fruit.'],
            ],
        ],
        'muscle' => [
            'label' => 'Gagner du muscle',
            'icon' => 'dumbbell',
            'title' => 'Alimentation pour accompagner le renforcement musculaire',
            'advice' => 'Répartissez les sources de protéines entre vos repas et incluez des féculents pour soutenir l’entraînement. Associez cette alimentation à un renforcement progressif et à des temps de récupération.',
            'meals' => [
                'matin' => ['Matin', 'Flocons d’avoine, fromage blanc ou yaourt de soja et fruits.'],
                'midi' => ['Midi', 'Poulet ou tofu, riz complet et brocoli.'],
                'collation' => ['Collation après l’entraînement', 'Yaourt ou boisson au soja et banane.'],
                'soir' => ['Soir', 'Saumon ou lentilles, pommes de terre et légumes verts.'],
            ],
        ],
        'maintien' => [
            'label' => 'Conserver son poids',
            'icon' => 'scale',
            'title' => 'Alimentation variée pour le maintien du poids',
            'advice' => 'Variez les légumes, les féculents complets et les sources de protéines. Ajustez vos portions au quotidien selon votre faim et votre niveau d’activité.',
            'meals' => [
                'matin' => ['Matin', 'Pain complet, œuf ou yaourt de soja et fruit de saison.'],
                'midi' => ['Midi', 'Salade de lentilles, légumes croquants et huile d’olive.'],
                'collation' => ['Collation si faim', 'Fruit frais et quelques amandes.'],
                'soir' => ['Soir', 'Poisson ou tofu, quinoa et légumes rôtis.'],
            ],
        ],
    ];

    public static function estimate(array $profile, string $activity): array
    {
        $age = (int) $profile['age'];
        $weight = (float) $profile['poids'];
        $height = (float) $profile['taille'];
        $bmi = $age >= 2 ? $weight / (($height / 100) ** 2) : null;
        $adult = $age >= 18;
        $estimate = [
            'bmi' => $bmi, 'adult' => $adult, 'energy' => null, 'carbs' => null,
            'protein' => null, 'fat' => null, 'fibre' => null, 'water' => null,
        ];

        // Mifflin–St Jeor was developed in adults aged 19–78. The range keeps both reference
        // coefficients without assuming physiological sex from the gender field.
        if ($age < 19 || $age > 78 || $bmi < 18.5 || $bmi >= 40 || $height < 120 || $height > 230) {
            return $estimate;
        }

        $restingBase = 10 * $weight + 6.25 * $height - 5 * $age;
        $factor = self::ACTIVITIES[$activity]['factor'];
        $low = ($restingBase - 161) * $factor;
        $high = ($restingBase + 5) * $factor;

        $estimate['energy'] = [(int) round($low), (int) round($high)];
        $estimate['carbs'] = [(int) round($low * 0.45 / 4), (int) round($high * 0.60 / 4)];
        $estimate['protein'] = (int) round($weight * 0.83);
        $estimate['fat'] = [(int) round($low * 0.20 / 9), (int) round($high * 0.35 / 9)];
        $estimate['fibre'] = 25;
        $estimate['water'] = [$low / 1000, $high / 1000];
        return $estimate;
    }

    public static function goalNotice(string $goal, float $bmi): ?string
    {
        if ($goal === 'perte' && ($bmi < 18.5 || $bmi >= 40)) {
            return 'Pour un objectif de perte de poids dans cette situation, demandez un avis professionnel. Voici des idées de repas équilibrés.';
        }
        if ($goal === 'prise' && ($bmi < 17 || $bmi >= 30)) {
            return 'Pour un objectif de prise de poids dans cette situation, demandez un avis professionnel. Voici des idées de repas équilibrés.';
        }
        return null;
    }
}
