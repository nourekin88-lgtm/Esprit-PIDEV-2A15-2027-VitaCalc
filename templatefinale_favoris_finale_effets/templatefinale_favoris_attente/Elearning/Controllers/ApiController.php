<?php
declare(strict_types=1);

class ApiController
{
    private Event $events;
    private Registration $registrations;
    private ListeAttente $waitlist;
    private Favorite $favorites;

    public function __construct()
    {
        $this->events        = new Event();
        $this->registrations = new Registration();
        $this->waitlist      = new ListeAttente();
        $this->favorites     = new Favorite();
    }

    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $resource = (string) ($_GET['resource'] ?? '');
        $method   = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $id       = isset($_GET['id']) && $_GET['id'] !== '' ? (int) $_GET['id'] : null;
        $body     = json_decode(file_get_contents('php://input') ?: '{}', true);
        $body     = is_array($body) ? $body : [];

        try {
            if ($resource === 'events') {
                $this->handleEvents($method, $id, $body);
            } elseif ($resource === 'registrations') {
                $this->handleRegistrations($method, $id, $body);
            } elseif ($resource === 'favorites') {
                $this->handleFavorites($method, $id, $body);
            } elseif ($resource === 'waitlist') {
                $this->handleWaitlist($method, $id, $body);
            } elseif ($resource === 'images') {
                (new ImageController())->search();
            } elseif ($resource === 'upload') {
                (new UploadController())->handle();
            } else {
                $this->respond(['error' => 'Ressource inconnue.'], 404);
            }
        } catch (Throwable $e) {
            $this->respond(['error' => $e->getMessage()], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Événements
    // -------------------------------------------------------------------------

    private function handleEvents(string $method, ?int $id, array $body): void
    {
        if ($method === 'GET') {
            if ($id !== null) {
                $event = $this->events->find($id);
                $event
                    ? $this->respond($event)
                    : $this->respond(['error' => 'Événement introuvable.'], 404);
            }
            $this->respond($this->events->all());
        }

        if ($method === 'POST') {
            $data  = $this->validateEvent($body);
            $event = $this->events->create($data);
            $this->respond($event, 201);
        }

        if ($method === 'PUT') {
            if ($id === null) $this->respond(['error' => 'Identifiant manquant.'], 400);
            $data    = $this->validateEvent($body);
            $updated = $this->events->update($id, $data);
            if ($updated) {
                // Capacité augmentée : les personnes en attente prennent les places libérées.
                $promoted = $this->waitlist->fillFreePlaces($id);
                if ($promoted) {
                    $updated = $this->events->find($id);
                    $updated['promues'] = count($promoted);
                }
            }
            $updated
                ? $this->respond($updated)
                : $this->respond(['error' => 'Événement introuvable.'], 404);
        }

        if ($method === 'DELETE') {
            if ($id === null) $this->respond(['error' => 'Identifiant manquant.'], 400);
            $this->events->delete($id)
                ? $this->respond(['ok' => true])
                : $this->respond(['error' => 'Événement introuvable.'], 404);
        }

        $this->respond(['error' => 'Méthode non autorisée.'], 405);
    }

    // -------------------------------------------------------------------------
    // Inscriptions
    // -------------------------------------------------------------------------

    private function handleRegistrations(string $method, ?int $id, array $body): void
    {
        if ($method === 'GET') {
            if ($id !== null) {
                $reg = $this->registrations->find($id);
                $reg
                    ? $this->respond($reg)
                    : $this->respond(['error' => 'Inscription introuvable.'], 404);
            }
            $this->respond($this->registrations->all());
        }

        if ($method === 'POST') {
            $this->validateRegistration($body);

            $event = $this->events->find((int) $body['id_evenement']);
            if (!$event) {
                $this->respond(['error' => 'Événement introuvable.'], 404);
            }

            // Vérification capacité avant insertion
            if ((int) $event['placesReservees'] >= (int) $event['capacite']) {
                $this->respond(['error' => 'Cet événement est complet. Vous pouvez rejoindre la liste d’attente.', 'waitlist_available' => true], 409);
            }

            $reg = $this->registrations->create([
                'id_evenement' => $event['id'],
                'prenom'       => trim((string) $body['prenom']),
                'nom'          => trim((string) $body['nom']),
                'email'        => trim((string) $body['email']),
                'telephone'    => trim((string) $body['telephone']),
            ]);

            $this->respond($reg, 201);
        }

        if ($method === 'PUT') {
            if ($id === null) $this->respond(['error' => 'Identifiant manquant.'], 400);
            $this->validateRegistration($body, partial: true);
            $updated = $this->registrations->update($id, $body);
            $updated
                ? $this->respond($updated)
                : $this->respond(['error' => 'Inscription introuvable.'], 404);
        }

        if ($method === 'DELETE') {
            if ($id === null) $this->respond(['error' => 'Identifiant manquant.'], 400);
            $reg = $this->registrations->find($id);
            if (!$reg || !$this->registrations->delete($id)) {
                $this->respond(['error' => 'Inscription introuvable.'], 404);
            }
            // Une place se libère : la première personne en attente est inscrite.
            $promoted = $this->waitlist->fillFreePlaces((int) $reg['id_evenement']);
            $this->respond(['ok' => true, 'promoted' => $promoted]);
        }

        $this->respond(['error' => 'Méthode non autorisée.'], 405);
    }

    // -------------------------------------------------------------------------
    // Favoris (métier simple)
    // -------------------------------------------------------------------------

    private function handleFavorites(string $method, ?int $id, array $body): void
    {
        if ($method === 'GET') {
            $this->respond($this->favoritesPayload());
        }

        if ($method === 'POST') {
            $eventId = (int) ($body['id_evenement'] ?? 0);
            if ($eventId < 1 || !$this->events->find($eventId)) {
                $this->respond(['error' => 'Événement introuvable.'], 404);
            }
            $this->favorites->add($eventId);
            $this->respond($this->favoritesPayload(), 201);
        }

        if ($method === 'DELETE') {
            if ($id === null) $this->respond(['error' => 'Identifiant manquant.'], 400);
            $this->favorites->remove($id);
            $this->respond($this->favoritesPayload());
        }

        $this->respond(['error' => 'Méthode non autorisée.'], 405);
    }

    private function favoritesPayload(): array
    {
        return ['ids' => $this->favorites->ids(), 'counts' => $this->favorites->counts()];
    }

    // -------------------------------------------------------------------------
    // Liste d'attente (métier avancé)
    // -------------------------------------------------------------------------

    private function handleWaitlist(string $method, ?int $id, array $body): void
    {
        if ($method === 'GET') {
            // Vue publique : seulement le nombre de personnes en attente par événement.
            if (($_GET['view'] ?? '') === 'counts') {
                $this->respond(['counts' => $this->waitlist->counts()]);
            }
            $eventId = isset($_GET['event']) && $_GET['event'] !== '' ? (int) $_GET['event'] : null;
            $this->respond($this->waitlist->all($eventId));
        }

        if ($method === 'POST') {
            // Action administrateur : promouvoir une personne en attente.
            if (($body['action'] ?? '') === 'promote') {
                $waitId = (int) ($body['id'] ?? 0);
                $entry  = $this->waitlist->find($waitId);
                if (!$entry) {
                    $this->respond(['error' => 'Demande introuvable.'], 404);
                }
                if ($entry['statut'] !== ListeAttente::EN_ATTENTE) {
                    $this->respond(['error' => 'Cette personne a déjà été inscrite.'], 409);
                }
                $reg = $this->waitlist->promote($waitId);
                if ($reg === null) {
                    $this->respond(['error' => 'Aucune place disponible : libérez une place ou augmentez la capacité.'], 409);
                }
                $this->respond(['ok' => true, 'registration' => $reg]);
            }

            // Inscription sur la liste d'attente.
            $this->validateRegistration($body);
            $event = $this->events->find((int) $body['id_evenement']);
            if (!$event) {
                $this->respond(['error' => 'Événement introuvable.'], 404);
            }
            if (new DateTimeImmutable($event['date']) < new DateTimeImmutable('today')) {
                $this->respond(['error' => 'Cet événement est terminé.'], 409);
            }
            if ((int) $event['placesReservees'] < (int) $event['capacite']) {
                $this->respond(['error' => 'Il reste des places : inscrivez-vous directement.'], 409);
            }
            $email = trim((string) $body['email']);
            if ($this->waitlist->emailAlreadyUsed((int) $event['id'], $email)) {
                $this->respond(['error' => 'Cette adresse e-mail est déjà inscrite ou en liste d’attente pour cet événement.'], 409);
            }

            $entry = $this->waitlist->join([
                'id_evenement' => $event['id'],
                'prenom'       => $body['prenom'],
                'nom'          => $body['nom'],
                'email'        => $email,
                'telephone'    => $body['telephone'],
            ]);
            $this->respond($entry, 201);
        }

        if ($method === 'DELETE') {
            if ($id === null) $this->respond(['error' => 'Identifiant manquant.'], 400);
            $this->waitlist->delete($id)
                ? $this->respond(['ok' => true])
                : $this->respond(['error' => 'Demande introuvable.'], 404);
        }

        $this->respond(['error' => 'Méthode non autorisée.'], 405);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    private function validateEvent(array $body): array
    {
        foreach (['titre', 'date', 'heure', 'lieu', 'categorie'] as $key) {
            if (trim((string) ($body[$key] ?? '')) === '') {
                $this->respond(['error' => 'Champ obligatoire manquant : ' . $key], 422);
            }
        }

        $capacite = filter_var($body['capacite'] ?? null, FILTER_VALIDATE_INT);
        if ($capacite === false || $capacite < 1) {
            $this->respond(['error' => 'La capacité doit être un entier supérieur à zéro.'], 422);
        }

        $format       = (string) ($body['format'] ?? 'Présentiel');
        $allowedZones = ['Tunis', 'Ariana', 'La Soukra', 'La Marsa', 'Ben Arous'];
        $zone         = $format === 'En ligne' ? 'En ligne' : (string) ($body['zone'] ?? 'Tunis');
        if ($format !== 'En ligne' && !in_array($zone, $allowedZones, true)) {
            $zone = 'Tunis';
        }

        $lien = trim((string) ($body['lien'] ?? ''));
        if ($lien !== '' && (!preg_match('#^https?://#i', $lien) || !filter_var($lien, FILTER_VALIDATE_URL))) {
            $this->respond(['error' => 'Le lien doit être une adresse web valide (https://…).'], 422);
        }

        return [
            'titre'       => trim((string) $body['titre']),
            'description' => trim((string) ($body['description'] ?? '')),
            'date'        => (string) $body['date'],
            'heure'       => (string) $body['heure'],
            'lieu'        => trim((string) $body['lieu']),
            'zone'        => $zone,
            'categorie'   => (string) $body['categorie'],
            'capacite'    => $capacite,
            'prix'        => max(0.0, (float) ($body['prix'] ?? 0)),
            'format'      => $format,
            'image'       => (string) ($body['image'] ?? 'Views/assets/img/events/yoga-event.jpg'),
            'lien'        => $lien,
        ];
    }

    private function validateRegistration(array $body, bool $partial = false): void
    {
        if (!$partial) {
            foreach (['prenom', 'nom', 'email', 'telephone', 'id_evenement'] as $key) {
                if (trim((string) ($body[$key] ?? '')) === '') {
                    $this->respond(['error' => 'Champ obligatoire manquant : ' . $key], 422);
                }
            }

            // Prénom et nom : lettres uniquement (accents compris), min 2 caractères
            foreach (['prenom', 'nom'] as $key) {
                $val = trim((string) ($body[$key] ?? ''));
                if (!preg_match('/^[\p{L}\'\- ]{2,}$/u', $val)) {
                    $this->respond(['error' => ucfirst($key) . ' invalide : lettres uniquement, 2 caractères minimum.'], 422);
                }
            }

            // Téléphone : chiffres uniquement, optionnellement + en début, 8-15 chiffres
            $tel = trim((string) ($body['telephone'] ?? ''));
            if (!preg_match('/^\+?[0-9]{8,15}$/', str_replace(' ', '', $tel))) {
                $this->respond(['error' => 'Numéro de téléphone invalide : chiffres uniquement (8 à 15 chiffres).'], 422);
            }
        }
        if (!empty($body['email']) && !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            $this->respond(['error' => 'Adresse e-mail invalide.'], 422);
        }
    }

    // -------------------------------------------------------------------------
    // Helper réponse
    // -------------------------------------------------------------------------

    private function respond(array $data, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
