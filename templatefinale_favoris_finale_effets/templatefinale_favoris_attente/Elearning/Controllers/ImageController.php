<?php
declare(strict_types=1);

/**
 * Proxy de recherche d'images via l'API Pixabay.
 *
 * Pixabay est GRATUIT, sans CB, clé obtenue en 1 min sur pixabay.com/api/docs/
 * 100 requêtes/min, 20 000/mois.
 *
 * Sans clé → message d'erreur clair invitant à configurer.
 *
 * GET api.php?resource=images&q=yoga&page=1
 */
class ImageController
{
    private string $pixabayKey;
    private const PER_PAGE = 9;

    public function __construct()
    {
        $config           = require BASE_PATH . '/config.php';
        $this->pixabayKey = trim((string) ($config['pixabay_key'] ?? ''));
    }

    public function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        $query = trim((string) ($_GET['q'] ?? ''));
        $page  = max(1, (int) ($_GET['page'] ?? 1));

        if ($query === '') {
            echo json_encode(['results' => [], 'totalPages' => 0, 'page' => 1]);
            exit;
        }

        if ($this->pixabayKey === '') {
            http_response_code(503);
            echo json_encode([
                'error' => 'Clé Pixabay non configurée. Obtenez-en une gratuitement sur pixabay.com/api/docs/ puis ajoutez-la dans config.php (clé "pixabay_key").'
            ]);
            exit;
        }

        $this->searchPixabay($query, $page);
    }

    private function searchPixabay(string $query, int $page): void
    {
        // Traduit les mots-clés français courants vers l'anglais pour de meilleurs résultats
        $translated = $this->translateQuery($query);

        $url = sprintf(
            'https://pixabay.com/api/?key=%s&q=%s&image_type=photo&orientation=horizontal&category=&per_page=%d&page=%d&safesearch=true&lang=fr',
            urlencode($this->pixabayKey),
            urlencode($translated),
            self::PER_PAGE,
            $page
        );

        $ctx = stream_context_create(['http' => [
            'timeout'     => 8,
            'user_agent'  => 'VitaCalc/1.0',
        ]]);

        $raw = @file_get_contents($url, false, $ctx);

        if ($raw === false) {
            http_response_code(502);
            echo json_encode(['error' => 'Impossible de joindre Pixabay. Vérifiez votre connexion internet.']);
            exit;
        }

        $data = json_decode($raw, true);

        if (!isset($data['hits'])) {
            http_response_code(502);
            echo json_encode(['error' => 'Réponse Pixabay inattendue. Vérifiez votre clé API.']);
            exit;
        }

        $results = array_map(fn(array $p): array => [
            'id'     => (string) $p['id'],
            'url'    => $p['webformatURL'],   // 640px — parfait pour l'affichage
            'thumb'  => $p['previewURL'],      // miniature 150px
            'author' => $p['user'] ?? 'Pixabay',
            'source' => 'pixabay',
        ], $data['hits']);

        $total      = (int) ($data['totalHits'] ?? 0);
        $totalPages = min(50, (int) ceil($total / self::PER_PAGE)); // Pixabay limite à 500 résultats

        echo json_encode([
            'results'    => $results,
            'totalPages' => $totalPages,
            'page'       => $page,
            'source'     => 'pixabay',
            'query'      => $translated,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Traduit les termes français fréquents vers l'anglais
     * pour améliorer la pertinence des résultats Pixabay.
     */
    private function translateQuery(string $query): string
    {
        $map = [
            'yoga'           => 'yoga meditation',
            'méditation'     => 'meditation mindfulness',
            'meditation'     => 'meditation mindfulness',
            'nutrition'      => 'healthy food nutrition',
            'cuisine'        => 'cooking food kitchen',
            'bien-être'      => 'wellness wellbeing',
            'bien etre'      => 'wellness spa',
            'sport'          => 'sport fitness exercise',
            'marche'         => 'walking nature trail',
            'pilates'        => 'pilates fitness',
            'étirements'     => 'stretching yoga',
            'etirements'     => 'stretching fitness',
            'respiration'    => 'breathing meditation calm',
            'randonnée'      => 'hiking nature trail',
            'randonnee'      => 'hiking trail outdoor',
            'danse'          => 'dance dancing',
            'natation'       => 'swimming pool',
            'running'        => 'running jogging',
            'course'         => 'running jogging',
            'vélo'           => 'cycling bike outdoor',
            'velo'           => 'cycling bike',
            'basketball'     => 'basketball sport',
            'football'       => 'football soccer',
            'tennis'         => 'tennis sport',
            'atelier'        => 'workshop group activity',
            'communauté'     => 'community people together',
            'communaute'     => 'community group',
            'relaxation'     => 'relaxation spa calm',
            'detente'        => 'relaxation calm nature',
            'détente'        => 'relaxation spa calm',
        ];

        $lower = mb_strtolower($query, 'UTF-8');
        foreach ($map as $fr => $en) {
            if (str_contains($lower, $fr)) {
                return $en;
            }
        }

        // Pas de traduction trouvée : on envoie le mot-clé tel quel
        return $query;
    }
}
