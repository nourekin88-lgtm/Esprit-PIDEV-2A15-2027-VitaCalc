<?php

namespace App\Models;

/** Association Utilisateur 0..* — 0..* Recette : « enregistre (favoris) ». */
class Favori extends Model
{
    public static function has(int $idUtilisateur, int $idRecette): bool
    {
        foreach (self::load()['favoris'] as $f) {
            if ($f['idUtilisateur'] === $idUtilisateur && $f['idRecette'] === $idRecette) {
                return true;
            }
        }
        return false;
    }

    /** Ajoute ou retire le favori. Retourne true si la recette est maintenant en favori. */
    public static function toggle(int $idUtilisateur, int $idRecette): bool
    {
        $store = self::load();
        if (self::has($idUtilisateur, $idRecette)) {
            $store['favoris'] = array_values(array_filter(
                $store['favoris'],
                fn($f) => !($f['idUtilisateur'] === $idUtilisateur && $f['idRecette'] === $idRecette)
            ));
            self::save($store);
            return false;
        }
        $store['favoris'][] = ['idUtilisateur' => $idUtilisateur, 'idRecette' => $idRecette];
        self::save($store);
        return true;
    }

    /** Liste des id de recettes favorites d'un utilisateur. */
    public static function idsForUser(int $idUtilisateur): array
    {
        $ids = [];
        foreach (self::load()['favoris'] as $f) {
            if ($f['idUtilisateur'] === $idUtilisateur) {
                $ids[] = $f['idRecette'];
            }
        }
        return $ids;
    }

    public static function countForUser(int $idUtilisateur): int
    {
        return count(self::idsForUser($idUtilisateur));
    }

    public static function count(): int
    {
        return count(self::load()['favoris']);
    }
}
