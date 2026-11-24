<?php
require_once __DIR__ . '/../Models/CategorieConseil.php';
require_once __DIR__ . '/../Models/Conseil.php';
require_once __DIR__ . '/../Services/MoteurConseils.php';

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
     *                           ['age' => int, 'sexe' => string, 'poids' => float, 'taille' => float,
     *                            'objectif_physique' => string]
     *                           Le profil n'est jamais redemandé à l'utilisateur : il sert seulement à l'affichage
     *                           (âge, sexe, poids, taille) et, s'il existe, « objectif_physique » évite la question de l'objectif.
     *                           Sans profil (visiteur), le questionnaire permet quand même de personnaliser.
     */
    public function index(?array $profil = null)
    {
        self::demarrerSession();
        $profil ??= self::profilConnecte();

        $selfUrl = '/' . ltrim((string) strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/');

        // Envoi du questionnaire (ou remise à zéro) : on traite, puis on redirige (évite le renvoi du formulaire).
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->traiterFormulaire($selfUrl, $profil);
        }

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

        // Recherche : ?q=sommeil
        $q = trim(mb_substr((string) ($_GET['q'] ?? ''), 0, 60));
        $recherche = $q === '' ? null : ['q' => $q, 'resultats' => MoteurConseils::rechercher($q, $categories)];

        // Recommandations : objectif du profil + réponses du questionnaire (gardées en session, pas en base).
        $questionnaire  = $_SESSION['questionnaire'] ?? null;
        $objectifProfil = MoteurConseils::objectifDepuisProfil($profil);
        $cartes         = MoteurConseils::recommander($questionnaire, $objectifProfil, array_keys($categories));
        $recommandes    = array_keys($cartes);

        // Conseils mis en avant : un conseil (le premier) pour chacune des catégories recommandées.
        $conseilsPerso = [];
        foreach ($recommandes as $slug) {
            $cat = $categories[$slug];
            if (!$cat['conseils']) {
                continue;
            }
            $conseilsPerso[] = [
                'slug'    => $slug,
                'label'   => $cat['label'],
                'image'   => $cat['image'],
                'titre'   => $cat['conseils'][0]['titre'],
                'contenu' => $cat['conseils'][0]['contenu'],
                'nb'      => count($cat['conseils']),
            ];
        }

        // Résumé affiché au-dessus des conseils : réponses au questionnaire + profil (déjà connu, jamais redemandé).
        $resume = [];
        $objectif = $objectifProfil ?? ($questionnaire['objectif'] ?? null);
        if ($objectif && isset(MoteurConseils::OBJECTIFS[$objectif]) && $objectif !== 'aucun') {
            $resume[] = MoteurConseils::OBJECTIFS[$objectif];
        }
        if ($questionnaire) {
            $resume[] = 'Priorité : ' . MoteurConseils::ASPECTS[$questionnaire['aspect']];
            $resume[] = 'Sommeil : ' . mb_strtolower(MoteurConseils::NIVEAUX_SOMMEIL[$questionnaire['sommeil']], 'UTF-8');
            $resume[] = 'Stress : '  . mb_strtolower(MoteurConseils::NIVEAUX_STRESS[$questionnaire['stress']], 'UTF-8');
        }
        $resumeProfil = self::resumeProfil($profil);

        // Les catégories recommandées passent en premier dans les onglets.
        if ($recommandes) {
            $ordre = array_merge($recommandes, array_diff(array_keys($categories), $recommandes));
            $tri   = [];
            foreach ($ordre as $slug) {
                $tri[$slug] = $categories[$slug];
            }
            $categories = $tri;
        }

        // Etat du formulaire : ouvert après une erreur ou via « Modifier mes réponses ».
        $erreurFormulaire = !empty($_SESSION['questionnaire_erreur']);
        unset($_SESSION['questionnaire_erreur']);
        $ouvrirFormulaire = $erreurFormulaire || isset($_GET['modifier']);

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

    /** Enregistre (ou efface) les réponses du questionnaire en session, puis redirige. */
    private function traiterFormulaire(string $selfUrl, ?array $profil): void
    {
        $action = $_POST['action'] ?? '';

        if ($action === 'questionnaire') {
            $reponses = MoteurConseils::validerReponses($_POST, MoteurConseils::objectifDepuisProfil($profil) !== null);
            if ($reponses === null) {
                $_SESSION['questionnaire_erreur'] = true;
                self::rediriger($selfUrl . '#personnaliser');
            }
            $_SESSION['questionnaire'] = $reponses;
            self::rediriger($selfUrl . '#recommandations');
        }

        if ($action === 'reinitialiser') {
            unset($_SESSION['questionnaire']);
            self::rediriger($selfUrl . '#personnaliser');
        }

        self::rediriger($selfUrl);
    }

    private static function rediriger(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /** Informations du profil à afficher, ex. ['25 ans', 'Homme', '66 kg', '185 cm']. */
    private static function resumeProfil(?array $profil): array
    {
        if (!$profil) {
            return [];
        }
        $r = [];
        $age = (int) ($profil['age'] ?? 0);
        if ($age > 0) {
            $r[] = $age . ' ans';
        }
        $sexe = trim((string) ($profil['sexe'] ?? ''));
        if ($sexe !== '') {
            $r[] = mb_convert_case(mb_strtolower($sexe, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        }
        $poids = (float) ($profil['poids'] ?? 0);
        if ($poids > 0) {
            $r[] = rtrim(rtrim(number_format($poids, 1, ',', ''), '0'), ',') . ' kg';
        }
        $taille = (float) ($profil['taille'] ?? 0);
        if ($taille > 0) {
            $r[] = round($taille <= 3 ? $taille * 100 : $taille) . ' cm';   // mètres ou centimètres
        }
        return $r;
    }

    private static function demarrerSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Profil de l'utilisateur connecté. A adapter à l'intégration avec le module Utilisateur :
     * ici on suppose que ce module le place dans $_SESSION['profil'] (sinon : visiteur, null).
     */
    private static function profilConnecte(): ?array
    {
        $profil = $_SESSION['profil'] ?? null;
        return is_array($profil) ? $profil : null;
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
