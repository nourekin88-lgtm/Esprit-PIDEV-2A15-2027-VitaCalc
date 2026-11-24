<?php

namespace App\Models;

/**
 * Notes et avis : un client donne 1 à 5 étoiles (et un commentaire facultatif) à une recette.
 * Association Utilisateur 0..* — 0..* Recette, avec les attributs note, commentaire et date.
 * Règle : un seul avis par client et par recette (le renvoyer le modifie).
 */
class Avis extends Model
{
    public const COMMENT_MAX = 300;

    /** Avis d'une recette (les plus récents d'abord), avec le nom affiché de l'auteur (« Sara B. »). */
    public static function forRecette(int $idRecette): array
    {
        $store = self::load();
        $users = [];
        foreach ($store['utilisateurs'] as $u) {
            $users[$u['id']] = $u;
        }
        $out = [];
        foreach ($store['avis'] ?? [] as $a) {
            if ($a['idRecette'] !== $idRecette) {
                continue;
            }
            $u = $users[$a['idUtilisateur']] ?? null;
            $a['auteur'] = $u ? trim(($u['prenom'] ?? '') . ' ' . mb_strtoupper(mb_substr($u['nom'] ?? '', 0, 1)) . '.') : 'Ancien membre';
            $out[] = $a;
        }
        usort($out, fn($x, $y) => strcmp($y['date'] ?? '', $x['date'] ?? ''));
        return $out;
    }

    /** L'avis d'un client sur une recette (null s'il n'a pas encore noté). */
    public static function ofUser(int $idUtilisateur, int $idRecette): ?array
    {
        foreach (self::load()['avis'] ?? [] as $a) {
            if ($a['idUtilisateur'] === $idUtilisateur && $a['idRecette'] === $idRecette) {
                return $a;
            }
        }
        return null;
    }

    /** Ajoute l'avis, ou remplace celui que le client avait déjà donné pour cette recette. */
    public static function donner(int $idUtilisateur, int $idRecette, int $note, string $commentaire): void
    {
        $store = self::load();
        $avis  = ['idUtilisateur' => $idUtilisateur, 'idRecette' => $idRecette, 'note' => $note,
                  'commentaire' => trim($commentaire), 'date' => date('Y-m-d')];
        $found = false;
        foreach ($store['avis'] as $k => $a) {
            if ($a['idUtilisateur'] === $idUtilisateur && $a['idRecette'] === $idRecette) {
                $store['avis'][$k] = $avis;
                $found = true;
            }
        }
        if (!$found) {
            $store['avis'][] = $avis;
        }
        self::save($store);
    }

    public static function retirer(int $idUtilisateur, int $idRecette): void
    {
        $store         = self::load();
        $store['avis'] = array_values(array_filter(
            $store['avis'] ?? [],
            fn($a) => !($a['idUtilisateur'] === $idUtilisateur && $a['idRecette'] === $idRecette)
        ));
        self::save($store);
    }

    public static function count(): int
    {
        return count(self::load()['avis'] ?? []);
    }

    /** Valide la note (1 à 5) et le commentaire. */
    public static function validate(array $d): array
    {
        $errors = [];
        $note   = (string) ($d['note'] ?? '');
        if (!ctype_digit($note) || (int) $note < 1 || (int) $note > 5) {
            $errors['note'] = 'Choisissez une note de 1 à 5 étoiles.';
        }
        if (mb_strlen(trim((string) ($d['commentaire'] ?? ''))) > self::COMMENT_MAX) {
            $errors['commentaire'] = 'Le commentaire ne doit pas dépasser ' . self::COMMENT_MAX . ' caractères.';
        }
        return $errors;
    }
}
