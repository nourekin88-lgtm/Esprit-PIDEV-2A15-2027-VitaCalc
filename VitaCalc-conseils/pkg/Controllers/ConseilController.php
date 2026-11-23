<?php
require_once __DIR__ . '/../Models/CategorieConseil.php';
require_once __DIR__ . '/../Models/Conseil.php';

class ConseilController
{
    // Présentation du front office (image et phrase de fin), indexée par nom de catégorie.
    // Ce ne sont pas des attributs de la base : une catégorie ajoutée plus tard reçoit les valeurs par défaut.
    private const PRESENTATION = [
        'nutrition'            => ['nutrition.jpg',   "Une bonne alimentation est la clé d’une vie en bonne santé !"],
        'sport'                => ['sport.jpg',       "Le mouvement d’aujourd’hui construit la forme de demain !"],
        'sommeil'              => ['sommeil.jpg',     "Un bon sommeil recharge le corps et l’esprit !"],
        'hydratation'          => ['hydratation.jpg', "Boire assez d’eau, c’est prendre soin de tout son corps !"],
        'gestion-du-stress'    => ['stress.jpg',      "Respirer, c’est déjà commencer à se détendre !"],
        'sante-mentale'        => ['mental.jpg',      "Prendre soin de son esprit est aussi important que de son corps !"],
        'posture-et-ergonomie' => ['posture.jpg',     "Un dos bien installé, c’est une journée plus légère !"],
        'bien-etre-numerique'  => ['numerique.jpg',   "Les écrans sont des outils : à vous de décider du temps qu’ils prennent !"],
        'recuperation'         => ['recuperation.jpg', "Se reposer fait partie de l’entraînement !"],
        'hygiene-de-vie'       => ['hygiene.jpg',     "Les petites habitudes d’aujourd’hui font la santé de demain !"],
    ];
    private const MESSAGE_PAR_DEFAUT = "Un petit geste chaque jour, et le bien-être suit !";

    /**
     * @param array|null $profil Profil de l'utilisateur connecté, fourni par le module Utilisateur :
     *                           ['age' => int, 'sexe' => string, 'poids' => float, 'taille' => float]
     *                           Sans profil (visiteur), tous les conseils sont affichés de façon générale.
     */
    public function index(?array $profil = null)
    {
        $categories = [];
        $erreur     = null;

        try {
            $slugParId = [];
            foreach (CategorieConseil::all() as $cat) {
                $slug = self::slug($cat->nom);
                $slugParId[$cat->id] = $slug;
                $p = self::PRESENTATION[$slug] ?? [null, self::MESSAGE_PAR_DEFAUT];
                $categories[$slug] = [
                    'label'       => $cat->nom,
                    'description' => $cat->description,
                    'image'       => $p[0],
                    'message'     => $p[1],
                    'conseils'    => [],
                ];
            }
            foreach (Conseil::all() as $c) {
                if (isset($slugParId[$c->idCategorie])) {
                    $categories[$slugParId[$c->idCategorie]]['conseils'][] = [
                        'titre'   => $c->titre,
                        'contenu' => $c->contenu,
                    ];
                }
            }
        } catch (PDOException $e) {
            $erreur = "Les conseils sont momentanément indisponibles. Vérifiez que MySQL est démarré et que la base « vitacalc » est importée.";
        }

        // Conseils personnalisés : les catégories recommandées passent en premier.
        $recommandes = self::recommandations($profil, array_keys($categories));
        if ($recommandes) {
            $ordre = array_merge($recommandes, array_diff(array_keys($categories), $recommandes));
            $tri   = [];
            foreach ($ordre as $slug) {
                $tri[$slug] = $categories[$slug];
            }
            $categories = $tri;
        }

        $documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
            ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']))
            : '';
        $projectRoot = str_replace('\\', '/', realpath(__DIR__ . '/../..'));

        if ($documentRoot !== '' && str_starts_with($projectRoot, $documentRoot)) {
            $appUrl = substr($projectRoot, strlen($documentRoot));
            $appUrl = '/' . trim(str_replace('\\', '/', $appUrl), '/');
            if ($appUrl === '/') {
                $appUrl = '/';
            }
        } else {
            $appUrl = '/';
        }

        $baseUrl = rtrim($appUrl, '/') . '/pkg/Views/';

        require_once __DIR__ . '/../Views/FrontOffice/conseil.php';
    }

    /**
     * Choisit jusqu'à 3 catégories à mettre en avant d'après l'âge, le poids et la taille.
     * Ces valeurs ne sont ni affichées ni enregistrées : elles servent uniquement à ordonner les onglets.
     * Le sexe n'est volontairement pas utilisé : les conseils proposés valent pour tout le monde.
     */
    private static function recommandations(?array $profil, array $existantes): array
    {
        if (!$profil) {
            return [];
        }
        $reco = [];

        $age = isset($profil['age']) ? (int) $profil['age'] : 0;
        if ($age > 0 && $age < 25) {
            array_push($reco, 'sommeil', 'bien-etre-numerique');
        } elseif ($age >= 25 && $age < 50) {
            array_push($reco, 'gestion-du-stress', 'posture-et-ergonomie');
        } elseif ($age >= 50) {
            array_push($reco, 'recuperation', 'sport');
        }

        $poids  = (float) ($profil['poids'] ?? 0);
        $taille = (float) ($profil['taille'] ?? 0);
        if ($taille > 3) {          // taille en centimètres -> mètres
            $taille /= 100;
        }
        if ($poids > 0 && $taille > 0.5) {
            $imc = $poids / ($taille * $taille);
            if ($imc < 18.5) {
                $reco[] = 'nutrition';
            } elseif ($imc >= 25) {
                array_push($reco, 'sport', 'nutrition');
            } else {
                $reco[] = 'hydratation';
            }
        }

        return array_slice(array_values(array_unique(array_intersect($reco, $existantes))), 0, 3);
    }

    private static function slug(string $nom): string
    {
        $nom = strtr($nom, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'À' => 'a', 'Â' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'É' => 'e', 'È' => 'e', 'Ê' => 'e',
            'î' => 'i', 'ï' => 'i', 'Î' => 'i', 'ô' => 'o', 'ö' => 'o', 'Ô' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'Ù' => 'u', 'ç' => 'c', 'Ç' => 'c',
        ]);
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($nom)), '-');
    }
}

// Permet d'ouvrir directement le controleur depuis le navigateur.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    (new ConseilController())->index();
}
