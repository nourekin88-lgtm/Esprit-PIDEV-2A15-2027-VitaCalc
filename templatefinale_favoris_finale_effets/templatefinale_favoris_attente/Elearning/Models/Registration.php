<?php
declare(strict_types=1);

/**
 * Modèle Inscription — CRUD complet via PDO/MySQL.
 *
 * Colonnes MySQL : id, dateInscription, idEvenement, prenom, nom, email, telephone
 */
class Registration
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // -------------------------------------------------------------------------
    // Helper privé
    // -------------------------------------------------------------------------

    /**
     * Normalise les clés BDD → clés frontend attendues par le JS.
     */
    private function normalize(array $row): array
    {
        return [
            'id'               => (int) $row['id'],
            'id_evenement'     => (int) $row['idEvenement'],
            'date_inscription' => $row['dateInscription'],
            'prenom'           => $row['prenom'],
            'nom'              => $row['nom'],
            'email'            => $row['email'],
            'telephone'        => $row['telephone'],
        ];
    }

    // -------------------------------------------------------------------------
    // CRUD public
    // -------------------------------------------------------------------------

    /**
     * Retourne toutes les inscriptions, triées par date décroissante.
     */
    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT id, dateInscription, idEvenement, prenom, nom, email, telephone
             FROM Inscription
             ORDER BY dateInscription DESC, id DESC'
        );
        return array_map([$this, 'normalize'], $stmt->fetchAll());
    }

    /**
     * Retourne les inscriptions d'un événement précis.
     */
    public function forEvent(int|string $eventId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, dateInscription, idEvenement, prenom, nom, email, telephone
             FROM Inscription
             WHERE idEvenement = ?
             ORDER BY dateInscription DESC'
        );
        $stmt->execute([(int) $eventId]);
        return array_map([$this, 'normalize'], $stmt->fetchAll());
    }

    /**
     * Retourne une inscription par son identifiant ou null.
     */
    public function find(int|string $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, dateInscription, idEvenement, prenom, nom, email, telephone
             FROM Inscription
             WHERE id = ?'
        );
        $stmt->execute([(int) $id]);
        $row = $stmt->fetch();
        return $row !== false ? $this->normalize($row) : null;
    }

    /**
     * Crée une inscription et incrémente placesReservees atomiquement.
     * L'ApiController vérifie la capacité en amont ; on fait ici l'UPDATE + INSERT.
     */
    public function create(array $data): array
    {
        // Incrémente la place de manière atomique
        $upd = $this->db->prepare(
            'UPDATE Evenement
             SET placesReservees = placesReservees + 1
             WHERE id = :id AND placesReservees < capacite'
        );
        $upd->execute([':id' => (int) $data['id_evenement']]);

        if ($upd->rowCount() === 0) {
            throw new RuntimeException('Cet événement est complet ou indisponible.');
        }

        $stmt = $this->db->prepare(
            'INSERT INTO Inscription (dateInscription, idEvenement, prenom, nom, email, telephone)
             VALUES (CURDATE(), :idEvenement, :prenom, :nom, :email, :telephone)'
        );
        $stmt->execute([
            ':idEvenement' => (int) $data['id_evenement'],
            ':prenom'      => trim((string) $data['prenom']),
            ':nom'         => trim((string) $data['nom']),
            ':email'       => trim((string) $data['email']),
            ':telephone'   => trim((string) $data['telephone']),
        ]);

        $newId = (int) $this->db->lastInsertId();
        return $this->find($newId);
    }

    /**
     * Met à jour les coordonnées d'une inscription (prenom, nom, email, telephone).
     * Retourne l'enregistrement mis à jour ou null si introuvable.
     */
    public function update(int|string $id, array $data): ?array
    {
        $existing = $this->find($id);
        if ($existing === null) {
            return null;
        }

        $stmt = $this->db->prepare(
            'UPDATE Inscription
             SET prenom    = :prenom,
                 nom       = :nom,
                 email     = :email,
                 telephone = :telephone
             WHERE id = :id'
        );
        $stmt->execute([
            ':id'        => (int) $id,
            ':prenom'    => $data['prenom']    ?? $existing['prenom'],
            ':nom'       => $data['nom']       ?? $existing['nom'],
            ':email'     => $data['email']     ?? $existing['email'],
            ':telephone' => $data['telephone'] ?? $existing['telephone'],
        ]);

        return $this->find($id);
    }

    /**
     * Supprime une inscription et décrémente placesReservees de l'événement lié.
     * Retourne true si supprimée.
     */
    public function delete(int|string $id): bool
    {
        $registration = $this->find($id);
        if ($registration === null) {
            return false;
        }

        $stmt = $this->db->prepare('DELETE FROM Inscription WHERE id = ?');
        $stmt->execute([(int) $id]);

        if ($stmt->rowCount() > 0) {
            // Décrémente la place libérée
            $this->db->prepare(
                'UPDATE Evenement
                 SET placesReservees = GREATEST(0, placesReservees - 1)
                 WHERE id = ?'
            )->execute([(int) $registration['id_evenement']]);
            return true;
        }
        return false;
    }

    /**
     * Supprime toutes les inscriptions d'un événement.
     * (Utile si la suppression cascade MySQL est désactivée.)
     */
    public function deleteForEvent(int|string $eventId): void
    {
        $this->db->prepare('DELETE FROM Inscription WHERE idEvenement = ?')
                 ->execute([(int) $eventId]);
    }
}
