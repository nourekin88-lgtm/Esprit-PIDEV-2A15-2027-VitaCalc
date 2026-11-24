<?php
declare(strict_types=1);

final class MenuPlanner
{
    public const MEALS = [
        'matin' => ['Matin', 'repas-matin'],
        'midi' => ['Midi', 'repas-midi'],
        'collation' => ['Collation', 'repas-collation'],
        'soir' => ['Soir', 'repas-soir'],
    ];

    private const BASE = [
        'matin' => ['avoine' => 65, 'yaourt' => 185, 'banane' => 135, 'amandes' => 20],
        'midi' => ['riz' => 250, 'poulet' => 120, 'carotte' => 155, 'olive' => 13],
        'collation' => ['pain' => 70, 'yaourt' => 125, 'pomme' => 165],
        'soir' => ['pates' => 230, 'lentille' => 195, 'brocoli' => 165, 'olive' => 14],
    ];

    private const GOAL_VARIATIONS = [
        'prise' => ['matin' => ['noix' => 32, 'avoine' => 78], 'collation' => ['pain' => 90, 'yaourt' => 160, 'poire' => 170]],
        'perte' => ['matin' => ['pomme' => 175, 'avoine' => 62, 'yaourt' => 190, 'amandes' => 18],
            'soir' => ['pates' => 210, 'lentille' => 200, 'haricot_vert' => 185, 'olive' => 14]],
        'muscle' => ['midi' => ['riz' => 240, 'dinde' => 150, 'brocoli' => 150, 'olive' => 14],
            'collation' => ['pain' => 70, 'yaourt_allege' => 190, 'banane' => 145],
            'soir' => ['pates' => 230, 'thon' => 110, 'haricot' => 110, 'carotte' => 155, 'olive' => 14]],
        'maintien' => [],
    ];

    private array $foods;

    public function __construct()
    {
        $contents = file_get_contents(__DIR__ . '/data/foods.json');
        $this->foods = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    }

    public static function context(array $profile, string $goal, string $activity): string
    {
        return hash('sha256', json_encode([
            'v2', $profile['age'], $profile['poids'], $profile['taille'], $goal, $activity,
        ], JSON_THROW_ON_ERROR));
    }

    public static function legacyContext(array $profile, string $goal, string $activity): ?string
    {
        // Les anciens champs servent uniquement à retrouver les remplacements déjà enregistrés.
        if ($profile['ancienPays'] === null || $profile['ancienBudget'] === null) {
            return null;
        }
        return hash('sha256', json_encode([
            'v1', $profile['ancienPays'], $profile['ancienBudget'], $profile['age'], $profile['poids'],
            $profile['taille'], $goal, $activity,
        ], JSON_THROW_ON_ERROR));
    }

    public function build(string $goal, array $estimate, array $substitutions): array
    {
        $target = array_sum($estimate['energy']) / 2;
        if ($goal === 'prise') {
            $target += 300;
        } elseif ($goal === 'perte' && $estimate['bmi'] >= 25) {
            $target -= 250;
        }

        $standard = array_replace(self::BASE, self::GOAL_VARIATIONS[$goal]);
        if ($goal === 'prise') {
            $standard['matin'] = array_replace(self::BASE['matin'], self::GOAL_VARIATIONS[$goal]['matin']);
            unset($standard['matin']['amandes']);
        }
        $rows = $this->portion($standard, $target);
        foreach ($rows as $slot => $original) {
            $options = $this->alternatives($original);
            $savedFood = $substitutions[$slot]['aliment'] ?? '';
            if (isset($options[$savedFood])) {
                $rows[$slot] = $options[$savedFood];
                unset($options[$savedFood]);
                $options[$original['id']] = $original;
            }
            $rows[$slot]['options'] = $options;
        }
        return ['items' => $rows, 'totals' => $this->totals($rows)];
    }

    private function portion(array $recipe, float $target): array
    {
        $rows = [];
        foreach ($recipe as $meal => $ingredients) {
            foreach ($ingredients as $index => $grams) {
                $rows[$meal . '-' . $index] = $this->item($index, $grams, $meal);
            }
        }
        $factor = $target / $this->totals($rows)['kcal'];
        foreach ($rows as $slot => $item) {
            $rows[$slot] = $this->item($item['id'], max(1, (int) round($item['grams'] * $factor)), $item['meal']);
        }
        return $rows;
    }

    private function item(string $id, int $grams, string $meal): array
    {
        $food = $this->foods[$id];
        $values = array_map(static fn (float $number): float => $number * $grams / 100, $food['nutrients']);
        return ['id' => $id, 'grams' => $grams, 'meal' => $meal, 'values' => $values] + $food;
    }

    private function totals(array $rows): array
    {
        $totals = array_fill_keys(['kcal', 'protein', 'carbs', 'fat', 'fibre'], 0);
        foreach ($rows as $row) {
            foreach ($row['values'] as $key => $value) {
                $totals[$key] += $value;
            }
        }
        return $totals;
    }

    private function alternatives(array $original): array
    {
        $options = [];
        $base = $original['values'];
        foreach ($this->foods as $id => $food) {
            if ($id === $original['id'] || $food['family'] !== $original['family']) {
                continue;
            }
            $grams = (int) round(100 * $base['kcal'] / $food['nutrients']['kcal']);
            if ($grams < 1 || $grams < $original['grams'] * 0.5 || $grams > $original['grams'] * 2.1) {
                continue;
            }
            $option = $this->item($id, $grams, $original['meal']);
            $limits = [
                'kcal' => max(5, $base['kcal'] * 0.08),
                'protein' => max(2.7, $base['protein'] * 0.21),
                'carbs' => max(4.5, $base['carbs'] * 0.18),
                'fat' => max(3, $base['fat'] * 0.25),
                'fibre' => max(2.7, $base['fibre'] * 0.30),
            ];
            $different = array_filter(array_keys($limits), fn (string $key): bool =>
                abs($option['values'][$key] - $base[$key]) > $limits[$key]
            );
            if ($different === []) {
                $options[$id] = $option;
            }
        }
        return $options;
    }
}
