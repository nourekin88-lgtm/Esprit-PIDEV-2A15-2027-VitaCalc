<?php

namespace App\Models;

/**
 * Classe d'association RecetteIngredient (quantité, unité).
 * Recette 1 ◆── 1..* RecetteIngredient *──0..* → 1 Ingredient
 */
class RecetteIngredient extends Model
{
    /** Lignes d'une recette, déjà jointes avec le catalogue : id, nom, image, quantite, unite. */
    public static function byRecette(int $idRecette): array
    {
        $store = self::load();
        $cat   = [];
        foreach ($store['ingredients'] as $i) {
            $cat[$i['id']] = $i;
        }
        $out = [];
        foreach ($store['recette_ingredients'] as $l) {
            if ($l['idRecette'] !== $idRecette || !isset($cat[$l['idIngredient']])) {
                continue;
            }
            $i     = $cat[$l['idIngredient']];
            $out[] = [
                'id' => $i['id'], 'nom' => $i['nom'], 'image' => $i['image'] ?? '',
                'quantite' => $l['quantite'], 'unite' => $l['unite'],
            ];
        }
        return $out;
    }

    /** Remplace toutes les lignes d'une recette. $lignes : [idIngredient, quantite, unite]. */
    public static function replace(int $idRecette, array $lignes): void
    {
        $store = self::load();
        $store['recette_ingredients'] = array_values(array_filter(
            $store['recette_ingredients'],
            fn($l) => $l['idRecette'] !== $idRecette
        ));
        foreach ($lignes as $l) {
            $store['recette_ingredients'][] = [
                'idRecette' => $idRecette, 'idIngredient' => (int) $l['idIngredient'],
                'quantite' => (float) $l['quantite'], 'unite' => $l['unite'],
            ];
        }
        self::save($store);
    }
}
