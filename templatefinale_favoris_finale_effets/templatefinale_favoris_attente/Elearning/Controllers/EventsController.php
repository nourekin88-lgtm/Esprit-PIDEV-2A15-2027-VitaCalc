<?php
declare(strict_types=1);

class EventsController extends Controller
{
    public function index(): void
    {
        $this->render('events', 'Events | VitaCalc', 'events');
    }

    public function detail(): void
    {
        $id    = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $model = new Event();
        $event = $id > 0 ? $model->find($id) : null;

        if ($event === null) {
            http_response_code(404);
            $this->render('not-found', 'Événement introuvable | VitaCalc', 'events');
            return;
        }

        // Calcul du statut
        $today        = new DateTimeImmutable('today');
        $dateEvent    = new DateTimeImmutable($event['date']);
        $reserved     = (int) $event['placesReservees'];
        $capacity     = (int) $event['capacite'];
        $remaining    = max(0, $capacity - $reserved);
        $fillRate     = $capacity > 0 ? round($reserved / $capacity * 100) : 0;
        $isPast       = $dateEvent < $today;
        $isFull       = $remaining === 0;
        $isAlmostFull = !$isFull && $fillRate >= 80;

        if ($isPast) {
            $status      = 'Terminé';
            $statusClass = 'status-past';
        } elseif ($isFull) {
            $status      = 'Complet';
            $statusClass = 'status-full';
        } elseif ($isAlmostFull) {
            $status      = 'Presque complet';
            $statusClass = 'status-almost';
        } else {
            $status      = 'Inscriptions ouvertes';
            $statusClass = 'status-open';
        }

        // Passe les variables à la vue
        $this->render('event-detail', e($event['titre']) . ' | VitaCalc', 'events', 'front', [
            'event'       => $event,
            'reserved'    => $reserved,
            'capacity'    => $capacity,
            'remaining'   => $remaining,
            'fillRate'    => $fillRate,
            'isPast'      => $isPast,
            'isFull'      => $isFull,
            'status'      => $status,
            'statusClass' => $statusClass,
            'isFavorite'  => (new Favorite())->isFavorite((int) $event['id']),
            'waitingCount' => (new ListeAttente())->countWaiting((int) $event['id']),
        ]);
    }
}
