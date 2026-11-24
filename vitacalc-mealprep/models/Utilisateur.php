<?php

namespace App\Models;

/** Classe Utilisateur (noyau) : rôle CLIENT ou ADMIN, mot de passe haché. */
class Utilisateur extends Model
{
    /** Hash factice : évite de révéler si l'e-mail existe (temps de réponse identique). */
    private const DUMMY_HASH = '$2y$10$FBpOVvYYajeZcrpkEYi2PudQKRyK8QEbZA4SYRbpvQBCexy0SjJnO';

    public static function all(): array
    {
        return self::load()['utilisateurs'];
    }

    public static function find(int $id): ?array
    {
        foreach (self::load()['utilisateurs'] as $u) {
            if ($u['id'] === $id) {
                return $u;
            }
        }
        return null;
    }

    public static function findByEmail(string $email): ?array
    {
        $email = mb_strtolower(trim($email));
        foreach (self::load()['utilisateurs'] as $u) {
            if ($u['email'] === $email) {
                return $u;
            }
        }
        return null;
    }

    public static function countByRole(string $role): int
    {
        return count(array_filter(self::load()['utilisateurs'], fn($u) => $u['role'] === $role));
    }

    /** Retourne l'utilisateur si l'e-mail et le mot de passe sont corrects, sinon null. */
    public static function authenticate(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);
        $ok   = password_verify($password, $user['motDePasseHash'] ?? self::DUMMY_HASH);
        return ($user && $ok) ? $user : null;
    }

    /** Données stockées en session (jamais le hash). */
    public static function sessionData(array $u): array
    {
        return ['id' => $u['id'], 'nom' => $u['nom'], 'prenom' => $u['prenom'], 'email' => $u['email'], 'role' => $u['role']];
    }

    /** Inscription : le rôle est toujours CLIENT (jamais choisi par le formulaire). */
    public static function register(array $d): array
    {
        $store = self::load();
        $user  = [
            'id'              => $store['next']['utilisateur']++,
            'nom'             => trim($d['nom']),
            'prenom'          => trim($d['prenom']),
            'email'           => mb_strtolower(trim($d['email'])),
            'motDePasseHash'  => password_hash($d['motDePasse'], PASSWORD_DEFAULT),
            'telephone'       => trim($d['telephone'] ?? ''),
            'role'            => ROLE_CLIENT,
            'dateInscription' => date('Y-m-d'),
        ];
        $store['utilisateurs'][] = $user;
        self::save($store);
        return $user;
    }

    public static function validateRegistration(array $d): array
    {
        $errors = [];
        self::validateIdentity($d, $errors);

        $email = trim($d['email'] ?? '');
        if ($email === '') {
            $errors['email'] = "L'adresse e-mail est obligatoire.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Cette adresse e-mail n'est pas valide.";
        } elseif (self::findByEmail($email)) {
            $errors['email'] = 'Un compte existe déjà avec cet e-mail. Connectez-vous.';
        }

        $pwd = (string) ($d['motDePasse'] ?? '');
        if (($msg = self::passwordError($pwd)) !== null) {
            $errors['motDePasse'] = $msg;
        }
        if ($pwd !== (string) ($d['confirmation'] ?? '')) {
            $errors['confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
        }
        return $errors;
    }

    public static function validateProfile(int $id, array $d): array
    {
        $errors = [];
        self::validateIdentity($d, $errors);

        $new = (string) ($d['nouveauMotDePasse'] ?? '');
        if ($new !== '') {
            $user = self::find($id);
            if (!$user || !password_verify((string) ($d['motDePasseActuel'] ?? ''), $user['motDePasseHash'])) {
                $errors['motDePasseActuel'] = 'Le mot de passe actuel est incorrect.';
            }
            if (($msg = self::passwordError($new)) !== null) {
                $errors['nouveauMotDePasse'] = $msg;
            }
            if ($new !== (string) ($d['confirmation'] ?? '')) {
                $errors['confirmation'] = 'Les deux mots de passe ne sont pas identiques.';
            }
        }
        return $errors;
    }

    public static function updateProfile(int $id, array $d): ?array
    {
        $store = self::load();
        $found = null;
        foreach ($store['utilisateurs'] as &$u) {
            if ($u['id'] === $id) {
                $u['nom']       = trim($d['nom']);
                $u['prenom']    = trim($d['prenom']);
                $u['telephone'] = trim($d['telephone'] ?? '');
                if (($d['nouveauMotDePasse'] ?? '') !== '') {
                    $u['motDePasseHash'] = password_hash($d['nouveauMotDePasse'], PASSWORD_DEFAULT);
                }
                $found = $u;
            }
        }
        unset($u);
        self::save($store);
        return $found;
    }

    private static function validateIdentity(array $d, array &$errors): void
    {
        foreach (['prenom' => 'Le prénom', 'nom' => 'Le nom'] as $field => $label) {
            $v = trim($d[$field] ?? '');
            if ($v === '') {
                $errors[$field] = $label . ' est obligatoire.';
            } elseif (mb_strlen($v) > 60) {
                $errors[$field] = $label . ' ne doit pas dépasser 60 caractères.';
            }
        }
        $tel = trim($d['telephone'] ?? '');
        if ($tel !== '' && !preg_match('/^[0-9+\s().-]{8,20}$/', $tel)) {
            $errors['telephone'] = 'Numéro invalide (8 chiffres minimum, ex : 20 123 456).';
        }
    }

    private static function passwordError(string $pwd): ?string
    {
        if (mb_strlen($pwd) < 8) {
            return 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if (!preg_match('/[A-Za-z]/', $pwd) || !preg_match('/\d/', $pwd)) {
            return 'Le mot de passe doit contenir des lettres et au moins un chiffre.';
        }
        return null;
    }
}
