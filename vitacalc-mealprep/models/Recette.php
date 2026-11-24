<?php

namespace App\Models;

/** Classe Recette (Module Meal Prep) : id, nom, description, instructions, tempsPreparation, nombrePortions. */
class Recette extends Model
{
    /** Filtres de durée de la liste : clé => [libellé, min, max] */
    public const DUREES = [
        'rapide' => ['≤ 15 min', 0, 15],
        'moyen'  => ['16 – 30 min', 16, 30],
        'long'   => ['Plus de 30 min', 31, 9999],
    ];

    /** Moments de la journée : clé => [libellé, icône Bootstrap, emoji, sous-titre] */
    public const MOMENTS = [
        'matin'    => ['Petit-déjeuner', 'bi-sunrise',    '🌅', 'Pour bien démarrer la journée'],
        'dejeuner' => ['Déjeuner',       'bi-sun',        '☀️', 'Le repas du midi, facile à emporter'],
        'diner'    => ['Dîner',          'bi-moon-stars', '🌙', 'Un repas du soir léger et réconfortant'],
    ];

    /** Devine le moment d'une recette d'après son nom (anciennes recettes / valeur par défaut). */
    public static function guessMoment(string $nom): string
    {
        return matchKeyword($nom, [
            'smoothie' => 'matin', 'overnight' => 'matin', 'oats' => 'matin', 'avoine' => 'matin',
            'porridge' => 'matin', 'chia' => 'matin', 'omelette' => 'matin', 'granola' => 'matin',
            'crepe' => 'matin', 'pancake' => 'matin', 'tartine' => 'matin', 'petit-dej' => 'matin',
            'soupe' => 'diner', 'saumon' => 'diner', 'curry' => 'diner', 'roti' => 'diner',
            'gratin' => 'diner', 'lasagne' => 'diner', 'veloute' => 'diner',
        ], 'dejeuner') ?? 'dejeuner';
    }

    /** Toutes les recettes (plus récentes d'abord), avec ingrédients, favoris et note moyenne. */
    public static function all(string $search = '', string $duree = '', string $moment = ''): array
    {
        $out = [];
        foreach (self::enriched() as $r) {
            if (isset(self::MOMENTS[$moment]) && $r['moment'] !== $moment) {
                continue;
            }
            if ($search !== '') {
                $hay = normalizeText($r['nom'] . ' ' . $r['description'] . ' ' . implode(' ', $r['ingredientsNoms']));
                if (!str_contains($hay, normalizeText($search))) {
                    continue;
                }
            }
            if (isset(self::DUREES[$duree])) {
                [, $min, $max] = self::DUREES[$duree];
                if ($r['tempsPreparation'] < $min || $r['tempsPreparation'] > $max) {
                    continue;
                }
            }
            $out[] = $r;
        }
        return array_reverse($out);
    }

    public static function find(int $id): ?array
    {
        foreach (self::enriched() as $r) {
            if ($r['id'] === $id) {
                return $r;
            }
        }
        return null;
    }

    public static function count(): int
    {
        return count(self::load()['recettes']);
    }

    public static function recent(int $n): array
    {
        return array_slice(self::all(), 0, $n);
    }

    /** Recettes triées par nombre de favoris (les plus aimées d'abord). */
    public static function topFavoris(int $n): array
    {
        $list = self::all();
        usort($list, fn($a, $b) => [$b['nbFavoris'], $a['id']] <=> [$a['nbFavoris'], $b['id']]);
        return array_slice($list, 0, $n);
    }

    /** Recettes les mieux notées (au moins un avis), meilleure moyenne d'abord. */
    public static function topNotees(int $n): array
    {
        $list = array_values(array_filter(self::all(), fn($r) => $r['nbAvis'] > 0));
        usort($list, fn($a, $b) => [$b['noteMoyenne'], $b['nbAvis'], $a['id']] <=> [$a['noteMoyenne'], $a['nbAvis'], $b['id']]);
        return array_slice($list, 0, $n);
    }

    /** Nombre de recettes par moment (pour les filtres), selon les autres filtres actifs. */
    public static function countByMoment(string $search = '', string $duree = ''): array
    {
        $counts = array_fill_keys(array_keys(self::MOMENTS), 0);
        foreach (self::all($search, $duree) as $r) {
            $counts[$r['moment']]++;
        }
        return $counts;
    }

    /** Recettes favorites d'un utilisateur. */
    public static function favorisOf(int $idUtilisateur): array
    {
        $ids = Favori::idsForUser($idUtilisateur);
        return array_values(array_filter(self::all(), fn($r) => in_array($r['id'], $ids, true)));
    }

    /** Crée la recette et ses lignes d'ingrédients (catalogue complété si besoin). */
    public static function create(array $d, array $lignes): int
    {
        $store = self::load();
        $id    = $store['next']['recette']++;
        $store['recettes'][] = ['id' => $id] + self::clean($d);
        self::save($store);
        RecetteIngredient::replace($id, self::resolveLines($lignes));
        return $id;
    }

    public static function update(int $id, array $d, array $lignes): void
    {
        $store = self::load();
        foreach ($store['recettes'] as &$r) {
            if ($r['id'] === $id) {
                $r = ['id' => $id] + self::clean($d);
            }
        }
        unset($r);
        self::save($store);
        RecetteIngredient::replace($id, self::resolveLines($lignes));
    }

    /** Supprime la recette, ses lignes d'ingrédients, ses favoris et ses avis. */
    public static function delete(int $id): void
    {
        $store = self::load();
        foreach ($store['recettes'] as $r) {
            if ($r['id'] === $id && str_starts_with($r['image'] ?? '', 'assets/uploads/')) {
                @unlink(BASE_PATH . '/' . $r['image']);
            }
        }
        $store['recettes']            = array_values(array_filter($store['recettes'], fn($r) => $r['id'] !== $id));
        $store['recette_ingredients'] = array_values(array_filter($store['recette_ingredients'], fn($l) => $l['idRecette'] !== $id));
        $store['favoris']             = array_values(array_filter($store['favoris'], fn($f) => $f['idRecette'] !== $id));
        $store['avis']                = array_values(array_filter($store['avis'] ?? [], fn($a) => $a['idRecette'] !== $id));
        self::save($store);
    }

    /** Valide les champs de la recette (hors lignes d'ingrédients). */
    public static function validate(array $d): array
    {
        $errors = [];
        if (trim($d['nom'] ?? '') === '') {
            $errors['nom'] = 'Le nom de la recette est obligatoire.';
        } elseif (mb_strlen($d['nom']) > 150) {
            $errors['nom'] = 'Le nom ne doit pas dépasser 150 caractères.';
        }
        if (trim($d['description'] ?? '') === '') {
            $errors['description'] = 'La description est obligatoire.';
        }
        if (trim($d['instructions'] ?? '') === '') {
            $errors['instructions'] = 'Les étapes de préparation sont obligatoires.';
        }
        if (!ctype_digit((string) ($d['tempsPreparation'] ?? '')) || (int) $d['tempsPreparation'] < 1) {
            $errors['tempsPreparation'] = 'Le temps de préparation doit être un entier positif (minutes).';
        }
        if (!ctype_digit((string) ($d['nombrePortions'] ?? '')) || (int) $d['nombrePortions'] < 1) {
            $errors['nombrePortions'] = 'Le nombre de portions doit être un entier positif.';
        }
        if (!isset(self::MOMENTS[$d['moment'] ?? ''])) {
            $errors['moment'] = 'Choisissez le moment du repas (petit-déjeuner, déjeuner ou dîner).';
        }
        $image = trim($d['image'] ?? '');
        if ($image !== '' && !preg_match('#^(https?://|assets/)#i', $image)) {
            $errors['image'] = 'Le lien de la photo doit commencer par http:// ou https://';
        }
        return $errors;
    }

    /**
     * Lit les lignes d'ingrédients du formulaire « Composer une recette » (ing_nom[], ing_quantite[], ing_unite[]).
     * Règle du diagramme : une recette contient 1..* ingrédients.
     *
     * @return array ['rows' => lignes saisies (pour réafficher), 'lignes' => lignes valides, 'errors' => messages]
     */
    public static function parseLines(array $post): array
    {
        $noms   = (array) ($post['ing_nom'] ?? []);
        $qtes   = (array) ($post['ing_quantite'] ?? []);
        $unites = (array) ($post['ing_unite'] ?? []);
        $rows   = [];
        $lignes = [];
        $errors = [];
        $seen   = [];

        $n = max(count($noms), count($qtes), count($unites));
        for ($i = 0; $i < $n; $i++) {
            $nom = trim((string) ($noms[$i] ?? ''));
            $q   = trim((string) ($qtes[$i] ?? ''));
            $u   = trim((string) ($unites[$i] ?? ''));
            if ($nom === '' && $q === '') {
                continue; // ligne vide ignorée
            }
            $rows[] = ['nom' => $nom, 'quantite' => $q, 'unite' => $u];
            $qn = str_replace(',', '.', $q);
            if ($nom === '') {
                $errors[] = 'Ingrédient n°' . count($rows) . " : le nom est obligatoire.";
            } elseif (mb_strlen($nom) > 100) {
                $errors[] = '« ' . $nom . ' » : nom trop long (100 caractères maximum).';
            } elseif (!is_numeric($qn) || (float) $qn <= 0) {
                $errors[] = '« ' . $nom . ' » : la quantité doit être un nombre supérieur à 0.';
            } elseif (!in_array($u, Ingredient::UNITES, true)) {
                $errors[] = '« ' . $nom . ' » : choisissez une unité.';
            } elseif (isset($seen[normalizeText($nom)])) {
                $errors[] = '« ' . $nom . ' » est déjà dans la recette.';
            } else {
                $seen[normalizeText($nom)] = true;
                $lignes[] = ['nom' => $nom, 'quantite' => (float) $qn, 'unite' => $u];
            }
        }
        if (!$rows) {
            $errors[] = 'Ajoutez au moins un ingrédient à la recette.';
        }
        return ['rows' => $rows, 'lignes' => $lignes, 'errors' => $errors];
    }

    /* ---------------------------------------------------------------- */

    /** Recettes + noms d'ingrédients + aperçu + nombre de favoris. */
    private static function enriched(): array
    {
        $store = self::load();
        $noms  = array_column($store['ingredients'], 'nom', 'id');
        $lines = [];
        foreach ($store['recette_ingredients'] as $l) {
            $lines[$l['idRecette']][] = $noms[$l['idIngredient']] ?? '?';
        }
        $favs = [];
        foreach ($store['favoris'] as $f) {
            $favs[$f['idRecette']] = ($favs[$f['idRecette']] ?? 0) + 1;
        }
        $sum = [];
        $nb  = [];
        foreach ($store['avis'] ?? [] as $a) {
            $sum[$a['idRecette']] = ($sum[$a['idRecette']] ?? 0) + (int) $a['note'];
            $nb[$a['idRecette']]  = ($nb[$a['idRecette']] ?? 0) + 1;
        }
        $out = [];
        foreach ($store['recettes'] as $r) {
            $ings                = $lines[$r['id']] ?? [];
            $r['moment']          = isset(self::MOMENTS[$r['moment'] ?? '']) ? $r['moment'] : self::guessMoment($r['nom']);
            $r['ingredientsNoms'] = $ings;
            $r['nbIngredients']   = count($ings);
            $r['apercu']          = array_slice($ings, 0, 3);
            $r['nbFavoris']       = $favs[$r['id']] ?? 0;
            $r['nbAvis']          = $nb[$r['id']] ?? 0;
            $r['noteMoyenne']     = $r['nbAvis'] > 0 ? round($sum[$r['id']] / $r['nbAvis'], 1) : 0.0;
            $out[] = $r;
        }
        return $out;
    }

    private static function resolveLines(array $lignes): array
    {
        return array_map(fn($l) => [
            'idIngredient' => Ingredient::findOrCreate($l['nom']),
            'quantite'     => $l['quantite'],
            'unite'        => $l['unite'],
        ], $lignes);
    }

    private static function clean(array $d): array
    {
        return [
            'nom'              => trim($d['nom']),
            'description'      => trim($d['description']),
            'instructions'     => trim($d['instructions']),
            'tempsPreparation' => (int) $d['tempsPreparation'],
            'nombrePortions'   => (int) $d['nombrePortions'],
            'moment'           => $d['moment'],
            'image'            => trim($d['image'] ?? ''),
        ];
    }
}
