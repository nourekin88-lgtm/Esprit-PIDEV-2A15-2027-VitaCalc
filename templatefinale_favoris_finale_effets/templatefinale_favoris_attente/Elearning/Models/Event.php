<?php
declare(strict_types=1);

/**
 * Modèle Evenement — CRUD complet via PDO/MySQL.
 *
 * Colonnes MySQL : id, titre, description, dateEvenement, heure, lieu,
 *                  categorie, capacite, prix, format, image, placesReservees
 *
 * Le modèle ajoute latitude/longitude à la volée (non stockés en BDD)
 * pour rester compatible avec le frontend cartographique Leaflet.
 */
class Event
{
    private PDO $db;

    /** Coordonnées approximatives des zones de Tunis. */
    private const DISTRICTS = [
        'Tunis'     => ['latitude' => 36.8065, 'longitude' => 10.1815],
        'Ariana'    => ['latitude' => 36.8625, 'longitude' => 10.1956],
        'La Soukra' => ['latitude' => 36.8842, 'longitude' => 10.2544],
        'La Marsa'  => ['latitude' => 36.8782, 'longitude' => 10.3247],
        'Ben Arous' => ['latitude' => 36.7536, 'longitude' => 10.2189],
        'En ligne'  => ['latitude' => null,    'longitude' => null],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // -------------------------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------------------------

    /**
     * Déduit la zone (district) depuis le champ lieu si format !== 'En ligne'.
     */
    private function inferDistrict(array $event): string
    {
        if (($event['format'] ?? '') === 'En ligne') {
            return 'En ligne';
        }
        $place = strtolower((string) ($event['lieu'] ?? ''));
        $map = [
            'ben arous' => 'Ben Arous',
            'la soukra' => 'La Soukra',
            'soukra'    => 'La Soukra',
            'ariana'    => 'Ariana',
            'la marsa'  => 'La Marsa',
        ];
        foreach ($map as $needle => $district) {
            if (str_contains($place, $needle)) {
                return $district;
            }
        }
        return 'Tunis';
    }

    /**
     * Ajoute zone, latitude et longitude à un tableau événement.
     * Normalise aussi les clés BDD → frontend (dateEvenement → date).
     */
    private function normalize(array $row): array
    {
        // Renommage dateEvenement → date pour le JS frontend
        if (isset($row['dateEvenement']) && !isset($row['date'])) {
            $row['date'] = $row['dateEvenement'];
            unset($row['dateEvenement']);
        }

        // Conversion types numériques
        $row['id']             = (int) $row['id'];
        $row['capacite']       = (int) $row['capacite'];
        $row['placesReservees'] = (int) $row['placesReservees'];
        $row['prix']           = (float) $row['prix'];

        // Zone et coordonnées
        $zone = $this->inferDistrict($row);
        $row['zone']      = $zone;
        $row['latitude']  = self::DISTRICTS[$zone]['latitude']  ?? null;
        $row['longitude'] = self::DISTRICTS[$zone]['longitude'] ?? null;

        return $row;
    }

    // -------------------------------------------------------------------------
    // CRUD public
    // -------------------------------------------------------------------------

    /**
     * Retourne tous les événements, triés par date.
     */
    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT id, titre, description, dateEvenement, heure, lieu,
                    categorie, capacite, prix, format, image, lien, placesReservees
             FROM Evenement
             ORDER BY dateEvenement ASC, heure ASC'
        );
        return array_map([$this, 'normalize'], $stmt->fetchAll());
    }

    /**
     * Retourne un événement par son identifiant ou null.
     */
    public function find(int|string $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, titre, description, dateEvenement, heure, lieu,
                    categorie, capacite, prix, format, image, lien, placesReservees
             FROM Evenement
             WHERE id = ?'
        );
        $stmt->execute([(int) $id]);
        $row = $stmt->fetch();
        return $row !== false ? $this->normalize($row) : null;
    }

    /**
     * Crée un événement et retourne l'enregistrement inséré.
     */
    public function create(array $data): array
    {
        $stmt = $this->db->prepare(
            'INSERT INTO Evenement
                (titre, description, dateEvenement, heure, lieu, categorie,
                 capacite, prix, format, image, lien, placesReservees)
             VALUES
                (:titre, :description, :dateEvenement, :heure, :lieu, :categorie,
                 :capacite, :prix, :format, :image, :lien, 0)'
        );
        $stmt->execute([
            ':titre'         => $data['titre'],
            ':description'   => $data['description'] ?? '',
            ':dateEvenement' => $data['date'],
            ':heure'         => $data['heure'],
            ':lieu'          => $data['lieu'],
            ':categorie'     => $data['categorie'],
            ':capacite'      => (int) $data['capacite'],
            ':prix'          => (float) ($data['prix'] ?? 0),
            ':format'        => $data['format'] ?? 'Présentiel',
            ':image'         => $data['image'] ?? 'Views/assets/img/events/yoga-event.jpg',
            ':lien'          => ($data['lien'] ?? '') !== '' ? $data['lien'] : null,
        ]);
        $newId = (int) $this->db->lastInsertId();
        return $this->find($newId);
    }

    /**
     * Met à jour un événement existant et retourne le résultat ou null si introuvable.
     */
    public function update(int|string $id, array $data): ?array
    {
        $existing = $this->find($id);
        if ($existing === null) {
            return null;
        }

        // On ne met à jour que les colonnes fournies, avec fallback sur l'existant
        $stmt = $this->db->prepare(
            'UPDATE Evenement
             SET titre          = :titre,
                 description    = :description,
                 dateEvenement  = :dateEvenement,
                 heure          = :heure,
                 lieu           = :lieu,
                 categorie      = :categorie,
                 capacite       = :capacite,
                 prix           = :prix,
                 format         = :format,
                 image          = :image,
                 lien           = :lien,
                 placesReservees = :placesReservees
             WHERE id = :id'
        );
        $stmt->execute([
            ':id'              => (int) $id,
            ':titre'           => $data['titre']           ?? $existing['titre'],
            ':description'     => $data['description']     ?? $existing['description'],
            ':dateEvenement'   => $data['date']            ?? $existing['date'],
            ':heure'           => $data['heure']           ?? $existing['heure'],
            ':lieu'            => $data['lieu']            ?? $existing['lieu'],
            ':categorie'       => $data['categorie']       ?? $existing['categorie'],
            ':capacite'        => (int) ($data['capacite']       ?? $existing['capacite']),
            ':prix'            => (float) ($data['prix']          ?? $existing['prix']),
            ':format'          => $data['format']          ?? $existing['format'],
            ':image'           => $data['image']           ?? $existing['image'],
            ':lien'            => array_key_exists('lien', $data)
                                    ? (($data['lien'] ?? '') !== '' ? $data['lien'] : null)
                                    : ($existing['lien'] ?? null),
            ':placesReservees' => (int) ($data['placesReservees'] ?? $existing['placesReservees']),
        ]);

        return $this->find($id);
    }

    /**
     * Incrémente placesReservees de 1 de manière atomique.
     * Retourne false si l'événement est complet ou introuvable.
     */
    public function incrementPlaces(int|string $id): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE Evenement
             SET placesReservees = placesReservees + 1
             WHERE id = :id AND placesReservees < capacite'
        );
        $stmt->execute([':id' => (int) $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Supprime un événement et retourne true si la suppression a eu lieu.
     * Les inscriptions associées sont supprimées en cascade par la FK MySQL.
     */
    public function delete(int|string $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM Evenement WHERE id = ?');
        $stmt->execute([(int) $id]);
        return $stmt->rowCount() > 0;
    }
}
