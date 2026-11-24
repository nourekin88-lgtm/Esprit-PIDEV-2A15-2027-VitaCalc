<?php

namespace App\Models;

/**
 * Classe Ingredient : catalogue d'ingrédients (id, nom) indépendant des recettes.
 * La quantité et l'unité sont portées par la classe d'association RecetteIngredient.
 */
class Ingredient extends Model
{
    public const UNITES = ['g', 'kg', 'ml', 'l', 'cuillère à soupe', 'cuillère à café', 'tasse', 'pièce', 'pincée'];

    /** Catalogue complet avec le nombre de recettes qui utilisent chaque ingrédient (les plus utilisés d'abord). */
    public static function all(): array
    {
        $store = self::load();
        $noms  = array_column($store['recettes'], 'nom', 'id');
        $usage = [];
        foreach ($store['recette_ingredients'] as $l) {
            if (isset($noms[$l['idRecette']])) {
                $usage[$l['idIngredient']][$l['idRecette']] = $noms[$l['idRecette']];
            }
        }
        $out = [];
        foreach ($store['ingredients'] as $i) {
            $i['recettes']   = $usage[$i['id']] ?? [];
            $i['nbRecettes'] = count($i['recettes']);
            $out[] = $i;
        }
        usort($out, fn($a, $b) => [$b['nbRecettes'], normalizeText($a['nom'])] <=> [$a['nbRecettes'], normalizeText($b['nom'])]);
        return $out;
    }

    public static function find(int $id): ?array
    {
        foreach (self::load()['ingredients'] as $i) {
            if ($i['id'] === $id) {
                return $i;
            }
        }
        return null;
    }

    public static function findByName(string $nom): ?array
    {
        $key = normalizeText(trim($nom));
        foreach (self::load()['ingredients'] as $i) {
            if (normalizeText($i['nom']) === $key) {
                return $i;
            }
        }
        return null;
    }

    /** Retourne l'id de l'ingrédient (le crée dans le catalogue s'il n'existe pas encore). */
    public static function findOrCreate(string $nom): int
    {
        if ($existing = self::findByName($nom)) {
            return $existing['id'];
        }
        return self::create(['nom' => $nom, 'image' => '']);
    }

    public static function count(): int
    {
        return count(self::load()['ingredients']);
    }

    public static function create(array $d): int
    {
        $store = self::load();
        $nom   = trim($d['nom']);
        $id    = $store['next']['ingredient']++;
        $store['ingredients'][] = [
            'id'    => $id,
            'nom'   => mb_strtoupper(mb_substr($nom, 0, 1)) . mb_substr($nom, 1),
            'image' => trim($d['image'] ?? ''),
        ];
        self::save($store);
        return $id;
    }

    public static function update(int $id, array $d): void
    {
        $store = self::load();
        foreach ($store['ingredients'] as &$i) {
            if ($i['id'] === $id) {
                $nom       = trim($d['nom']);
                $i['nom']   = mb_strtoupper(mb_substr($nom, 0, 1)) . mb_substr($nom, 1);
                $i['image'] = trim($d['image'] ?? '');
            }
        }
        unset($i);
        self::save($store);
    }

    /** Nombre de recettes qui utilisent cet ingrédient. */
    public static function usageCount(int $id): int
    {
        $recettes = [];
        foreach (self::load()['recette_ingredients'] as $l) {
            if ($l['idIngredient'] === $id) {
                $recettes[$l['idRecette']] = true;
            }
        }
        return count($recettes);
    }

    /** Supprime l'ingrédient seulement s'il n'est utilisé par aucune recette. */
    public static function delete(int $id): bool
    {
        if (self::usageCount($id) > 0) {
            return false;
        }
        $store = self::load();
        $store['ingredients'] = array_values(array_filter($store['ingredients'], fn($i) => $i['id'] !== $id));
        self::save($store);
        return true;
    }

    public static function validate(array $d, ?int $ignoreId = null): array
    {
        $errors = [];
        $nom    = trim($d['nom'] ?? '');
        if ($nom === '') {
            $errors['nom'] = "Le nom de l'ingrédient est obligatoire.";
        } elseif (mb_strlen($nom) > 100) {
            $errors['nom'] = 'Le nom ne doit pas dépasser 100 caractères.';
        } elseif (($doublon = self::findByName($nom)) && $doublon['id'] !== $ignoreId) {
            $errors['nom'] = 'Cet ingrédient existe déjà dans le catalogue.';
        }
        $image = trim($d['image'] ?? '');
        if ($image !== '' && !preg_match('#^(https?://|assets/)#i', $image)) {
            $errors['image'] = 'Le lien de la photo doit commencer par http:// ou https://';
        }
        return $errors;
    }
}
