<?php
/**
 * Moteur de règles du module « Conseils bien-être » (sans IA, sans nouvelle table).
 *  - rechercher()          : recherche texte dans les conseils et les catégories ;
 *  - validerReponses()     : contrôle les réponses du questionnaire ;
 *  - objectifDepuisProfil(): lit l'objectif physique du profil utilisateur ;
 *  - recommander()         : choisit jusqu'à 4 catégories à mettre en avant.
 *
 * Les clés des catégories sont les « slugs » calculés par ConseilController (ex. « gestion-du-stress »).
 * Ces recommandations sont générales : ce ne sont pas des prescriptions médicales.
 */
class MoteurConseils
{
    // Réponses possibles du questionnaire (clé => libellé affiché).
    public const ASPECTS = [
        'nutrition'         => 'Nutrition',
        'sport'             => 'Sport',
        'sommeil'           => 'Sommeil',
        'hydratation'       => 'Hydratation',
        'gestion-du-stress' => 'Gestion du stress',
    ];
    public const NIVEAUX_SOMMEIL = ['bon' => 'Bon', 'moyen' => 'Moyen', 'mauvais' => 'Mauvais'];
    public const NIVEAUX_STRESS  = ['faible' => 'Faible', 'moyen' => 'Moyen', 'eleve' => 'Élevé'];
    public const OBJECTIFS = [
        'prise-de-masse' => 'Prise de masse',
        'perte-de-poids' => 'Perte de poids',
        'maintien'       => 'Maintien / forme générale',
        'endurance'      => 'Endurance / performance',
        'aucun'          => 'Pas d’objectif particulier',
    ];

    // Points ajoutés à chaque catégorie selon l'objectif physique (critère principal).
    private const POINTS_OBJECTIF = [
        'prise-de-masse' => ['nutrition' => 3, 'sport' => 3, 'recuperation' => 3, 'sommeil' => 1],
        'perte-de-poids' => ['nutrition' => 3, 'sport' => 3, 'hydratation' => 2, 'sommeil' => 1],
        'maintien'       => ['hygiene-de-vie' => 2, 'sport' => 2, 'nutrition' => 2],
        'endurance'      => ['sport' => 3, 'hydratation' => 3, 'recuperation' => 3, 'nutrition' => 1],
    ];

    // Points ajoutés selon la qualité du sommeil et le niveau de stress déclarés.
    private const POINTS_SOMMEIL = [
        'mauvais' => ['sommeil' => 4, 'bien-etre-numerique' => 1, 'hygiene-de-vie' => 1],
        'moyen'   => ['sommeil' => 2],
    ];
    private const POINTS_STRESS = [
        'eleve' => ['gestion-du-stress' => 4, 'sante-mentale' => 2, 'recuperation' => 1],
        'moyen' => ['gestion-du-stress' => 2],
    ];

    // Mots que l'utilisateur peut taper pour retrouver une catégorie (recherche).
    private const SYNONYMES = [
        'nutrition'            => 'alimentation manger repas aliment nourriture regime diete',
        'sport'                => 'activite physique exercice marche course musculation fitness bouger entrainement',
        'sommeil'              => 'dormir dodo coucher insomnie nuit reveil',
        'hydratation'          => 'eau boire boisson soif gourde',
        'gestion-du-stress'    => 'stress anxiete detente relaxation respiration calme pression',
        'sante-mentale'        => 'moral emotion emotions psychologue mental bien-etre',
        'posture-et-ergonomie' => 'dos ergonomie assis bureau chaise articulations',
        'bien-etre-numerique'  => 'ecran ecrans telephone smartphone notifications numerique internet',
        'recuperation'         => 'repos etirement etirements fatigue recuperer courbature',
        'hygiene-de-vie'       => 'habitudes hygiene mains medecin bilan dehors',
    ];

    /** Minuscules, sans accents ni ponctuation : « Écran, Sommeil ! » => « ecran sommeil ». */
    public static function normaliser(string $texte): string
    {
        $texte = mb_strtolower($texte, 'UTF-8');
        $texte = strtr($texte, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c',
            'œ' => 'oe', 'æ' => 'ae', '’' => ' ',
        ]);
        return trim(preg_replace('/[^a-z0-9]+/', ' ', $texte));
    }

    /**
     * Recherche dans les titres, contenus, noms et descriptions de catégories (+ synonymes).
     * Tous les mots saisis doivent être trouvés (début de mot) ; les titres comptent le plus.
     *
     * @param array $categories slug => ['label', 'description', 'conseils' => [['titre','contenu'], ...], ...]
     * @return array[] [['titre', 'contenu', 'slug', 'categorie'], ...] triés par pertinence
     */
    public static function rechercher(string $requete, array $categories): array
    {
        $mots = [];
        foreach (explode(' ', self::normaliser($requete)) as $mot) {
            if (strlen($mot) > 3 && in_array(substr($mot, -1), ['s', 'x'], true)) {
                $mot = substr($mot, 0, -1);      // « conseils » => « conseil »
            }
            if (strlen($mot) >= 2) {      // on ignore les lettres isolées (trop de résultats sans intérêt)
                $mots[] = $mot;
            }
        }
        $mots = array_slice(array_unique($mots), 0, 6);
        if (!$mots) {
            return [];
        }

        $resultats = [];
        foreach ($categories as $slug => $cat) {
            $texteCat = ' ' . self::normaliser(
                $cat['label'] . ' ' . ($cat['description'] ?? '') . ' ' . (self::SYNONYMES[$slug] ?? '')
            ) . ' ';

            foreach ($cat['conseils'] as $c) {
                $texteTitre   = ' ' . self::normaliser($c['titre']) . ' ';
                $texteContenu = ' ' . self::normaliser($c['contenu']) . ' ';
                $score   = 0;
                $tousLesMotsTrouves = true;

                foreach ($mots as $mot) {
                    $cherche = ' ' . $mot;      // début de mot : « eau » ne trouve pas « beau »
                    $points  = 0;
                    if (str_contains($texteTitre, $cherche))   { $points += 3; }
                    if (str_contains($texteCat, $cherche))     { $points += 2; }
                    if (str_contains($texteContenu, $cherche)) { $points += 1; }
                    if ($points === 0) {
                        $tousLesMotsTrouves = false;   // un mot introuvable : le conseil est écarté
                        break;
                    }
                    $score += $points;
                }

                if ($tousLesMotsTrouves) {
                    $resultats[] = [
                        'titre'     => $c['titre'],
                        'contenu'   => $c['contenu'],
                        'slug'      => $slug,
                        'categorie' => $cat['label'],
                        'score'     => $score,
                    ];
                }
            }
        }

        usort($resultats, fn($a, $b) => $b['score'] <=> $a['score']);
        return $resultats;
    }

    /**
     * Vérifie les réponses du formulaire. Retourne les réponses nettoyées, ou null si une réponse
     * obligatoire manque ou n'est pas dans la liste autorisée.
     */
    public static function validerReponses(array $post, bool $objectifDansProfil = false): ?array
    {
        $aspect  = (string) ($post['aspect']  ?? '');
        $sommeil = (string) ($post['sommeil'] ?? '');
        $stress  = (string) ($post['stress']  ?? '');
        $objectif = (string) ($post['objectif'] ?? '');

        if (!isset(self::ASPECTS[$aspect]) || !isset(self::NIVEAUX_SOMMEIL[$sommeil]) || !isset(self::NIVEAUX_STRESS[$stress])) {
            return null;
        }
        // L'objectif est demandé dans le questionnaire, sauf si le profil le fournit déjà.
        if (!$objectifDansProfil && !isset(self::OBJECTIFS[$objectif])) {
            return null;
        }
        return [
            'aspect'   => $aspect,
            'sommeil'  => $sommeil,
            'stress'   => $stress,
            'objectif' => isset(self::OBJECTIFS[$objectif]) ? $objectif : null,
        ];
    }

    /**
     * Transforme l'objectif physique du profil (texte libre ou clé) en une de nos clés d'objectif.
     * Retourne null si le profil n'a pas d'objectif exploitable.
     * Le profil est fourni par le module Utilisateur : ['objectif_physique' => 'Prise de masse', ...]
     */
    public static function objectifDepuisProfil(?array $profil): ?string
    {
        $brut = $profil['objectif_physique'] ?? $profil['objectif'] ?? '';
        $txt  = self::normaliser((string) $brut);
        if ($txt === '') {
            return null;
        }
        $cle = str_replace(' ', '-', $txt);
        if (isset(self::POINTS_OBJECTIF[$cle])) {
            return $cle;
        }
        $contient = fn(array $mots) => (bool) array_filter($mots, fn($m) => str_contains($txt, $m));

        if ($contient(['masse', 'muscul', 'gain', 'prise'])) { return 'prise-de-masse'; }
        if ($contient(['perte', 'maigr', 'mincir', 'seche', 'poids']))        { return 'perte-de-poids'; }
        if ($contient(['endurance', 'perform', 'cardio']))           { return 'endurance'; }
        if ($contient(['maintien', 'forme', 'sante', 'equilibre']))  { return 'maintien'; }
        return null;
    }

    /**
     * Calcule les catégories à mettre en avant (4 au maximum), de la plus à la moins pertinente.
     *
     * @param array|null  $reponses       réponses validées du questionnaire (ou null)
     * @param string|null $objectifProfil objectif lu dans le profil (prioritaire sur celui du questionnaire)
     * @param string[]    $existantes     slugs des catégories qui existent vraiment en base
     * @return array slug => ['score' => int, 'raisons' => string[]]
     */
    public static function recommander(?array $reponses, ?string $objectifProfil, array $existantes): array
    {
        $scores = [];
        $ajouter = function (array $points, string $raison) use (&$scores, $existantes) {
            foreach ($points as $slug => $pts) {
                if (!in_array($slug, $existantes, true)) {
                    continue;   // catégorie supprimée ou renommée par l'admin
                }
                $scores[$slug]['score'] = ($scores[$slug]['score'] ?? 0) + $pts;
                if (!in_array($raison, $scores[$slug]['raisons'] ?? [], true)) {
                    $scores[$slug]['raisons'][] = $raison;
                }
            }
        };

        // 1. Aspect que l'utilisateur veut améliorer
        if ($reponses && isset(self::ASPECTS[$reponses['aspect']])) {
            $ajouter([$reponses['aspect'] => 5], 'Vous souhaitez améliorer ce point');
        }

        // 2. Objectif physique : le profil d'abord, sinon la réponse du questionnaire
        $objectif = $objectifProfil ?? ($reponses['objectif'] ?? null);
        if ($objectif !== null && isset(self::POINTS_OBJECTIF[$objectif])) {
            $ajouter(
                self::POINTS_OBJECTIF[$objectif],
                'Utile pour votre objectif : ' . mb_strtolower(self::OBJECTIFS[$objectif], 'UTF-8')
            );
        }

        // 3. Sommeil et stress déclarés
        if ($reponses) {
            if (isset(self::POINTS_SOMMEIL[$reponses['sommeil']])) {
                $ajouter(
                    self::POINTS_SOMMEIL[$reponses['sommeil']],
                    $reponses['sommeil'] === 'mauvais' ? 'Votre sommeil est à améliorer' : 'Votre sommeil pourrait être meilleur'
                );
            }
            if (isset(self::POINTS_STRESS[$reponses['stress']])) {
                $ajouter(
                    self::POINTS_STRESS[$reponses['stress']],
                    $reponses['stress'] === 'eleve' ? 'Votre niveau de stress est élevé' : 'Votre niveau de stress est moyen'
                );
            }
        }

        // Tri : score décroissant ; à score égal, on garde l'ordre d'apparition.
        $ordre = array_flip(array_keys($scores));
        uksort($scores, function ($a, $b) use ($scores, $ordre) {
            return [$scores[$b]['score'], $ordre[$a]] <=> [$scores[$a]['score'], $ordre[$b]];
        });
        return array_slice($scores, 0, 4, true);
    }
}
