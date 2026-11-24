<?php
declare(strict_types=1);

/**
 * Métier simple — « Ajouter aux favoris » (classes Utilisateur / Favori).
 *
 * Il n'y a pas de compte à créer : chaque visiteur est un Utilisateur anonyme
 * identifié par un jeton aléatoire conservé dans le cookie « vc_user ».
 * Un favori relie un Utilisateur à un Evenement (unique par couple).
 */
class Favorite
{
    private const COOKIE = 'vc_user';

    private PDO $db;
    private ?int $userId = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retourne l'identifiant de l'utilisateur courant.
     * Si $create est vrai et que le visiteur est inconnu, il est créé.
     */
    private function userId(bool $create): ?int
    {
        if ($this->userId !== null) {
            return $this->userId;
        }

        $token = (string) ($_COOKIE[self::COOKIE] ?? '');
        if (preg_match('/^[a-f0-9]{32}$/', $token)) {
            $stmt = $this->db->prepare('SELECT id FROM Utilisateur WHERE token = ?');
            $stmt->execute([$token]);
            $id = $stmt->fetchColumn();
            if ($id !== false) {
                return $this->userId = (int) $id;
            }
        }

        if (!$create) {
            return null;
        }

        $token = bin2hex(random_bytes(16));
        $this->db->prepare('INSERT INTO Utilisateur (token) VALUES (?)')->execute([$token]);
        $this->userId = (int) $this->db->lastInsertId();

        if (!headers_sent()) {
            setcookie(self::COOKIE, $token, [
                'expires'  => time() + 60 * 60 * 24 * 365,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE[self::COOKIE] = $token;

        return $this->userId;
    }

    /** Identifiants des événements favoris de l'utilisateur courant. */
    public function ids(): array
    {
        $uid = $this->userId(false);
        if ($uid === null) {
            return [];
        }
        $stmt = $this->db->prepare('SELECT idEvenement FROM Favori WHERE idUtilisateur = ? ORDER BY dateAjout DESC');
        $stmt->execute([$uid]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function isFavorite(int $eventId): bool
    {
        return in_array($eventId, $this->ids(), true);
    }

    /** Ajoute un favori (sans doublon). */
    public function add(int $eventId): void
    {
        $uid = $this->userId(true);
        $this->db->prepare('INSERT IGNORE INTO Favori (idUtilisateur, idEvenement) VALUES (?, ?)')
                 ->execute([$uid, $eventId]);
    }

    /** Retire un favori. */
    public function remove(int $eventId): void
    {
        $uid = $this->userId(false);
        if ($uid === null) {
            return;
        }
        $this->db->prepare('DELETE FROM Favori WHERE idUtilisateur = ? AND idEvenement = ?')
                 ->execute([$uid, $eventId]);
    }

    /** Nombre de favoris par événement : [idEvenement => nombre]. */
    public function counts(): array
    {
        $rows = $this->db->query('SELECT idEvenement, COUNT(*) AS n FROM Favori GROUP BY idEvenement')->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[(string) (int) $r['idEvenement']] = (int) $r['n'];
        }
        return $out;
    }
}
