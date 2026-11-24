<?php
declare(strict_types=1);

/**
 * Métier avancé — « Gérer une liste d'attente ».
 *
 * Quand un événement est complet, les personnes s'inscrivent sur la liste
 * d'attente (ordre d'arrivée = position). Dès qu'une place se libère
 * (annulation, capacité augmentée, promotion manuelle), la première personne
 * en attente devient une Inscription et passe au statut « promue ».
 *
 * Colonnes : id, idEvenement, prenom, nom, email, telephone,
 *            dateDemande, statut (en_attente | promue), datePromotion
 */
class ListeAttente
{
    public const EN_ATTENTE = 'en_attente';
    public const PROMUE     = 'promue';

    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private function normalize(array $row, ?int $position = null): array
    {
        return [
            'id'             => (int) $row['id'],
            'id_evenement'   => (int) $row['idEvenement'],
            'prenom'         => $row['prenom'],
            'nom'            => $row['nom'],
            'email'          => $row['email'],
            'telephone'      => $row['telephone'],
            'date_demande'   => $row['dateDemande'],
            'statut'         => $row['statut'],
            'date_promotion' => $row['datePromotion'],
            'position'       => $row['statut'] === self::EN_ATTENTE ? $position : null,
        ];
    }

    /** Toute la liste (ou celle d'un événement), avec la position de chacun. */
    public function all(?int $eventId = null): array
    {
        $sql = 'SELECT id, idEvenement, prenom, nom, email, telephone, dateDemande, statut, datePromotion
                FROM ListeAttente';
        $params = [];
        if ($eventId !== null) {
            $sql .= ' WHERE idEvenement = ?';
            $params[] = $eventId;
        }
        $stmt = $this->db->prepare($sql . ' ORDER BY idEvenement ASC, id ASC');
        $stmt->execute($params);

        $counters = [];
        $out = [];
        foreach ($stmt->fetchAll() as $row) {
            $ev = (int) $row['idEvenement'];
            $pos = null;
            if ($row['statut'] === self::EN_ATTENTE) {
                $counters[$ev] = ($counters[$ev] ?? 0) + 1;
                $pos = $counters[$ev];
            }
            $out[] = $this->normalize($row, $pos);
        }
        return $out;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, idEvenement, prenom, nom, email, telephone, dateDemande, statut, datePromotion
             FROM ListeAttente WHERE id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        return $this->normalize($row, $this->position($id, (int) $row['idEvenement']));
    }

    /** Position (1 = prochain servi) d'une demande en attente. */
    private function position(int $id, int $eventId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) FROM ListeAttente WHERE idEvenement = ? AND statut = ? AND id <= ?'
        );
        $stmt->execute([$eventId, self::EN_ATTENTE, $id]);
        return (int) $stmt->fetchColumn();
    }

    /** Nombre de personnes en attente pour un événement. */
    public function countWaiting(int $eventId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM ListeAttente WHERE idEvenement = ? AND statut = ?');
        $stmt->execute([$eventId, self::EN_ATTENTE]);
        return (int) $stmt->fetchColumn();
    }

    /** [idEvenement => nombre en attente] pour tous les événements. */
    public function counts(): array
    {
        $stmt = $this->db->prepare(
            'SELECT idEvenement, COUNT(*) AS n FROM ListeAttente WHERE statut = ? GROUP BY idEvenement'
        );
        $stmt->execute([self::EN_ATTENTE]);
        $out = [];
        foreach ($stmt->fetchAll() as $r) {
            $out[(string) (int) $r['idEvenement']] = (int) $r['n'];
        }
        return $out;
    }

    /** Cette adresse e-mail est-elle déjà inscrite ou en attente pour l'événement ? */
    public function emailAlreadyUsed(int $eventId, string $email): bool
    {
        $stmt = $this->db->prepare(
            'SELECT (SELECT COUNT(*) FROM Inscription WHERE idEvenement = :e1 AND LOWER(email) = LOWER(:m1))
                  + (SELECT COUNT(*) FROM ListeAttente WHERE idEvenement = :e2 AND statut = :s AND LOWER(email) = LOWER(:m2))'
        );
        $stmt->execute([':e1' => $eventId, ':m1' => $email, ':e2' => $eventId, ':s' => self::EN_ATTENTE, ':m2' => $email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /** Ajoute une personne à la fin de la liste d'attente. */
    public function join(array $data): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ListeAttente (idEvenement, prenom, nom, email, telephone)
             VALUES (:ev, :prenom, :nom, :email, :tel)'
        );
        $stmt->execute([
            ':ev'     => (int) $data['id_evenement'],
            ':prenom' => trim((string) $data['prenom']),
            ':nom'    => trim((string) $data['nom']),
            ':email'  => trim((string) $data['email']),
            ':tel'    => trim((string) $data['telephone']),
        ]);
        return $this->find((int) $this->db->lastInsertId());
    }

    /** Retire une demande de la liste. */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ListeAttente WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Transforme une demande en inscription confirmée (si une place est libre).
     * Retourne l'inscription créée ou null s'il n'y a plus de place.
     */
    public function promote(int $id): ?array
    {
        $entry = $this->find($id);
        if ($entry === null || $entry['statut'] !== self::EN_ATTENTE) {
            return null;
        }

        $this->db->beginTransaction();
        try {
            // Registration::create vérifie et incrémente la place de façon atomique.
            $registration = (new Registration())->create([
                'id_evenement' => $entry['id_evenement'],
                'prenom'       => $entry['prenom'],
                'nom'          => $entry['nom'],
                'email'        => $entry['email'],
                'telephone'    => $entry['telephone'],
            ]);
            $this->db->prepare(
                'UPDATE ListeAttente SET statut = ?, datePromotion = NOW() WHERE id = ?'
            )->execute([self::PROMUE, $id]);
            $this->db->commit();
            return $registration;
        } catch (PDOException $e) {
            // Erreur SQL réelle : on annule et on remonte l'erreur.
            $this->db->rollBack();
            throw $e;
        } catch (RuntimeException $e) {
            // Événement complet : rien n'est modifié.
            $this->db->rollBack();
            return null;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Remplit automatiquement les places libérées d'un événement en
     * promouvant les personnes en attente, dans l'ordre d'arrivée.
     * Retourne la liste des personnes promues.
     */
    public function fillFreePlaces(int $eventId): array
    {
        $promoted = [];
        $stmt = $this->db->prepare(
            'SELECT id FROM ListeAttente WHERE idEvenement = ? AND statut = ? ORDER BY id ASC'
        );
        $stmt->execute([$eventId, self::EN_ATTENTE]);

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $waitId) {
            $entry = $this->find((int) $waitId);
            if ($this->promote((int) $waitId) === null) {
                break; // plus de place
            }
            $promoted[] = $entry;
        }
        return $promoted;
    }
}
