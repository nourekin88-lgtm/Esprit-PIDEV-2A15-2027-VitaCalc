<?php
/** Fonctions utilitaires globales (utilisables dans les vues et les contrôleurs). */

/* ------------------------------------------------------------------ */
/*  Affichage / URL                                                    */
/* ------------------------------------------------------------------ */

/** Échappe une valeur pour l'affichage HTML (protection XSS). */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Construit une URL du routeur : url('recette', ['id' => 3]) */
function url(string $page, array $params = []): string
{
    return 'index.php?' . http_build_query(array_merge(['page' => $page], $params));
}

/** URL de la page courante (relative). */
function currentUrl(): string
{
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    return 'index.php' . ($qs !== '' ? '?' . $qs : '');
}

/** N'accepte que des URL internes du site (empêche les redirections vers l'extérieur). */
function safeRedirect(?string $target, string $fallback): string
{
    if ($target && preg_match('/^index\.php(\?[A-Za-z0-9_=&%.+\-]*)?$/', $target)) {
        return $target;
    }
    return $fallback;
}

/** Retourne 'active' si la page courante fait partie des clés données (navbar / sidebar). */
function isActive($keys, string $currentPage): string
{
    return in_array($currentPage, (array) $keys, true) ? 'active' : '';
}

/** Valeur ancienne d'un champ de formulaire. */
function old(array $data, string $key, $default = '')
{
    return $data[$key] ?? $default;
}

/* ------------------------------------------------------------------ */
/*  Messages flash                                                     */
/* ------------------------------------------------------------------ */

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/* ------------------------------------------------------------------ */
/*  Authentification / rôles                                           */
/* ------------------------------------------------------------------ */

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user']);
}

function isAdmin(): bool
{
    return (($_SESSION['user']['role'] ?? '') === ROLE_ADMIN);
}

/** Initiales de l'utilisateur connecté (avatar). */
function initials(?array $user): string
{
    if (!$user) {
        return '?';
    }
    return mb_strtoupper(mb_substr($user['prenom'] ?? '', 0, 1) . mb_substr($user['nom'] ?? '', 0, 1));
}

/* ------------------------------------------------------------------ */
/*  Sécurité CSRF (tous les formulaires POST)                          */
/* ------------------------------------------------------------------ */

function csrfToken(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrfToken()) . '">';
}

function csrfValid(): bool
{
    $token = $_POST['_token'] ?? '';
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ------------------------------------------------------------------ */
/*  Formatage                                                          */
/* ------------------------------------------------------------------ */

/** Formate une quantité : 1.5 => "1,5", 200.00 => "200". */
function qty($value): string
{
    $s = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    return str_replace('.', ',', $s === '' ? '0' : $s);
}

/** 75 => "1 h 15", 35 => "35 min". */
function formatDuration(int $minutes): string
{
    if ($minutes < 60) {
        return $minutes . ' min';
    }
    $h = intdiv($minutes, 60);
    $m = $minutes % 60;
    return $h . ' h' . ($m ? ' ' . str_pad((string) $m, 2, '0', STR_PAD_LEFT) : '');
}

/** Découpe les instructions en étapes (une ligne = une étape, numérotation retirée). */
function steps(string $text): array
{
    $out = [];
    foreach (preg_split('/\r\n|\r|\n/', trim($text)) as $line) {
        $line = trim((string) preg_replace('/^\s*\d+[\.\)\-]\s*/', '', $line));
        if ($line !== '') {
            $out[] = $line;
        }
    }
    return $out;
}

/**
 * Durée (en minutes) citée dans une étape, ou null s'il n'y en a pas.
 * « Griller le saumon 4 minutes de chaque côté » => 4 · « cuire 1 h 30 » => 90 · « 2 à 3 min » => 3 (la plus longue).
 * Sert au minuteur du mode cuisine.
 */
function stepMinutes(string $step): ?int
{
    $t = normalizeText($step);
    $total = 0;
    // heures : « 1 h », « 2 heures », « 1 h 30 »
    if (preg_match('/(\d+)\s*(?:h|heures?)\b(?:\s*(\d{1,2})\b(?!\s*(?:g|kg|ml|l)\b))?/u', $t, $m)) {
        $total += (int) $m[1] * 60 + (isset($m[2]) ? (int) $m[2] : 0);
    }
    // minutes : « 30 minutes », « 2 à 3 min », « 5-6 mn »
    if (preg_match('/(\d+)(?:\s*(?:-|a|à|ou)\s*(\d+))?\s*(?:min(?:ute)?s?|mn)\b/u', $t, $m)) {
        $total += (int) ($m[2] ?? '' ?: $m[1]);
    }
    return $total > 0 ? min($total, 24 * 60) : null;
}

/* ------------------------------------------------------------------ */
/*  Notes et avis : étoiles                                            */
/* ------------------------------------------------------------------ */

/** 4.5 => ★★★★½ (icônes Bootstrap). Lecture seule. */
function stars(float $note, string $class = ''): string
{
    $html = '<span class="stars ' . e($class) . '" role="img" aria-label="' . e(number_format($note, 1, ',', '')) . ' sur 5">';
    for ($i = 1; $i <= 5; $i++) {
        if ($note >= $i - 0.25) {
            $html .= '<i class="bi bi-star-fill"></i>';
        } elseif ($note >= $i - 0.75) {
            $html .= '<i class="bi bi-star-half"></i>';
        } else {
            $html .= '<i class="bi bi-star"></i>';
        }
    }
    return $html . '</span>';
}

/** Note moyenne + étoiles + nombre d'avis (carte de recette, détail, back office). */
function ratingBadge(array $r): string
{
    if (empty($r['nbAvis'])) {
        return '<span class="rating-none"><i class="bi bi-star"></i>Pas encore noté</span>';
    }
    return '<span class="rating-badge">' . stars((float) $r['noteMoyenne'])
        . '<strong>' . e(number_format((float) $r['noteMoyenne'], 1, ',', '')) . '</strong>'
        . '<small>(' . (int) $r['nbAvis'] . ')</small></span>';
}

/** Pastille « Petit-déjeuner / Déjeuner / Dîner » d'une recette. */
function momentChip(string $moment, string $class = 'chip'): string
{
    $m = \App\Models\Recette::MOMENTS[$moment] ?? null;
    if (!$m) {
        return '';
    }
    return '<span class="' . e($class) . ' moment moment-' . e($moment) . '"><i class="bi ' . e($m[1]) . '"></i>' . e($m[0]) . '</span>';
}

/* ------------------------------------------------------------------ */
/*  Images : emoji + photos automatiques (si aucune photo envoyée)     */
/* ------------------------------------------------------------------ */

/** Minuscules sans accents (pour comparer des mots). */
function normalizeText(string $text): string
{
    return strtr(mb_strtolower($text), [
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'œ' => 'oe', '’' => "'",
    ]);
}

/** Première valeur de $map dont le mot-clé apparaît dans $text. $whole = mot entier (pluriel accepté). */
function matchKeyword(string $text, array $map, ?string $default = null, bool $whole = false): ?string
{
    $n = normalizeText($text);
    foreach ($map as $word => $value) {
        $re = '/\b' . preg_quote((string) $word, '/') . ($whole ? '(?:s|x)?\b' : '') . '/u';
        if (preg_match($re, $n)) {
            return $value;
        }
    }
    return $default;
}

function recipeEmoji(string $name): string
{
    return matchKeyword($name, [
        'smoothie' => '🥤', 'wrap' => '🌯', 'curry' => '🍛', 'chia' => '🍮', 'pudding' => '🍮', 'omelette' => '🍳',
        'oeuf' => '🍳', 'soupe' => '🍲', 'saumon' => '🐟', 'thon' => '🐟', 'poisson' => '🐟', 'pate' => '🍝',
        'pasta' => '🍝', 'bowl' => '🥗', 'salade' => '🥗', 'avoine' => '🥣', 'oats' => '🥣', 'porridge' => '🥣',
        'poulet' => '🍗', 'riz' => '🍚', 'gateau' => '🍰', 'cake' => '🍰', 'pizza' => '🍕', 'burger' => '🍔',
        'sandwich' => '🥪', 'banane' => '🍌', 'fruit' => '🍓', 'legume' => '🥦', 'viande' => '🥩', 'boeuf' => '🥩',
    ], '🍱');
}

/** Mot-clé anglais d'un plat (photo automatique d'une recette). */
function dishTag(string $name): string
{
    return matchKeyword($name, [
        'wrap' => 'wrap', 'smoothie' => 'smoothie', 'curry' => 'curry', 'chia' => 'pudding', 'omelette' => 'omelette',
        'oeuf' => 'omelette', 'soupe' => 'soup', 'saumon' => 'salmon', 'pate' => 'pasta', 'bowl' => 'salad',
        'salade' => 'salad', 'avoine' => 'oatmeal', 'oats' => 'oatmeal', 'porridge' => 'oatmeal', 'poulet' => 'chicken',
        'riz' => 'rice', 'gateau' => 'cake', 'cake' => 'cake', 'pizza' => 'pizza', 'burger' => 'burger',
        'sandwich' => 'sandwich', 'banane' => 'banana', 'fruit' => 'fruit', 'legume' => 'vegetables', 'viande' => 'steak',
    ], 'meal');
}

/** Mot-clé anglais d'un ingrédient (photo automatique). Null si inconnu. */
function ingredientTag(string $name): ?string
{
    return matchKeyword($name, [
        'pomme de terre' => 'potato', 'patate douce' => 'sweet-potato', 'pois chiche' => 'chickpeas',
        'poulet' => 'chicken', 'dinde' => 'turkey', 'boeuf' => 'beef', 'viande' => 'meat', 'saumon' => 'salmon',
        'thon' => 'tuna', 'crevette' => 'shrimp', 'quinoa' => 'quinoa', 'brocoli' => 'broccoli', 'olive' => 'olive',
        'avoine' => 'oats', 'lait' => 'milk', 'banane' => 'banana', 'riz' => 'rice', 'oeuf' => 'egg',
        'tomate' => 'tomato', 'carotte' => 'carrot', 'avocat' => 'avocado', 'citron' => 'lemon', 'oignon' => 'onion',
        'ail' => 'garlic', 'pate' => 'pasta', 'fromage' => 'cheese', 'yaourt' => 'yogurt', 'miel' => 'honey',
        'courgette' => 'zucchini', 'poivron' => 'pepper', 'epinard' => 'spinach', 'concombre' => 'cucumber',
        'lentille' => 'lentils', 'sel' => 'salt', 'sucre' => 'sugar', 'farine' => 'flour', 'beurre' => 'butter',
        'pomme' => 'apple', 'fraise' => 'strawberry', 'amande' => 'almond', 'noix' => 'walnut',
        'haricot' => 'beans', 'champignon' => 'mushroom', 'mais' => 'corn', 'laitue' => 'lettuce',
        'roquette' => 'arugula', 'persil' => 'parsley', 'menthe' => 'mint', 'cannelle' => 'cinnamon',
        'pain' => 'bread', 'orange' => 'orange', 'mangue' => 'mango', 'chocolat' => 'chocolate',
        'chia' => 'chia', 'graine' => 'seeds', 'tortilla' => 'tortilla',
    ], null, true);
}

function ingredientEmoji(string $name): string
{
    return matchKeyword($name, [
        'pomme de terre' => '🥔', 'patate douce' => '🍠', 'pois chiche' => '🫘', 'poulet' => '🍗', 'dinde' => '🦃',
        'boeuf' => '🥩', 'viande' => '🥩', 'saumon' => '🐟', 'thon' => '🐟', 'crevette' => '🦐', 'brocoli' => '🥦',
        'olive' => '🫒', 'laitue' => '🥬', 'lait' => '🥛', 'banane' => '🍌', 'riz' => '🍚', 'oeuf' => '🥚',
        'tomate' => '🍅', 'carotte' => '🥕', 'avocat' => '🥑', 'citron' => '🍋', 'oignon' => '🧅', 'ail' => '🧄',
        'pate' => '🍝', 'fromage' => '🧀', 'yaourt' => '🥛', 'miel' => '🍯', 'courgette' => '🥒', 'concombre' => '🥒',
        'poivron' => '🫑', 'epinard' => '🥬', 'roquette' => '🥬', 'pomme' => '🍎', 'fraise' => '🍓', 'noix' => '🥜',
        'amande' => '🥜', 'champignon' => '🍄', 'mais' => '🌽', 'pain' => '🍞', 'orange' => '🍊', 'mangue' => '🥭',
        'beurre' => '🧈', 'sel' => '🧂', 'avoine' => '🌾', 'quinoa' => '🌾', 'farine' => '🌾', 'chocolat' => '🍫',
        'lentille' => '🫘', 'haricot' => '🫘', 'chia' => '🌱', 'graine' => '🌱', 'tortilla' => '🫓',
    ], '🥄', true);
}

/** Photo automatique (réelle) par mot-clé, stable grâce au paramètre lock. */
function autoPhoto(string $tag, int $seed, int $w, int $h): string
{
    return 'https://loremflickr.com/' . $w . '/' . $h . '/' . rawurlencode($tag) . '?lock=' . max(1, $seed);
}

/** Vrai si l'image est une URL, ou un fichier local qui existe vraiment. */
function imageAvailable(?string $src): bool
{
    if (!$src) {
        return false;
    }
    if (preg_match('#^https?://#i', $src)) {
        return true;
    }
    return is_file(BASE_PATH . '/' . $src);
}

/**
 * Image de couverture d'une recette : photo envoyée (ou lien), sinon photo automatique.
 * L'emoji reste derrière : si la photo ne charge pas, il s'affiche.
 * $size : 'card' | 'hero' | 'thumb'
 */
function recipeCover(array $r, string $size = 'card'): string
{
    $id   = (int) ($r['id'] ?? 0);
    $name = $r['nom'] ?? '';
    $dim  = $size === 'thumb' ? [96, 96] : [900, 600];
    $src  = imageAvailable($r['image'] ?? null) ? $r['image'] : autoPhoto(dishTag($name), $id + 10, $dim[0], $dim[1]);
    return '<div class="cover cover-' . e($size) . ' cover-p' . ($id % 6) . '">'
        . '<span class="cover-emoji">' . recipeEmoji($name) . '</span>'
        . '<img src="' . e($src) . '" alt="' . e($name) . '" loading="lazy" onerror="this.remove()">'
        . '</div>';
}

/** Vignette ronde d'un ingrédient. $size : 'sm' | 'md' | 'lg' | 'xl' */
function ingredientThumb(array $i, string $size = 'md'): string
{
    $name = $i['nom'] ?? '';
    $id   = (int) ($i['id'] ?? 1);
    $src  = imageAvailable($i['image'] ?? null) ? $i['image'] : null;
    if (!$src && ($tag = ingredientTag($name))) {
        $src = autoPhoto($tag, 20 + $id, 240, 240);
    }
    $img = $src ? '<img src="' . e($src) . '" alt="' . e($name) . '" loading="lazy" onerror="this.remove()">' : '';
    return '<span class="ing-thumb ing-thumb-' . e($size) . '"><span class="ing-emoji">' . ingredientEmoji($name) . '</span>' . $img . '</span>';
}


/* ------------------------------------------------------------------ */
/*  Frigo : étagère où ranger chaque ingrédient                         */
/* ------------------------------------------------------------------ */

/** Étagères du frigo (ordre d'affichage, de haut en bas) : clé => [titre, icône Bootstrap]. */
const FRIDGE_SHELVES = [
    'laitiers' => ['Laitiers & œufs', 'bi-egg-fried'],
    'viandes'  => ['Viandes & poissons', 'bi-snow'],
    'fruits'   => ['Fruits', 'bi-apple'],
    'porte'    => ['Balconnet : huiles & sauces', 'bi-droplet-half'],
    'legumes'  => ['Bac à légumes', 'bi-flower2'],
    'placard'  => ['Placard (produits secs)', 'bi-basket2'],
];

/** Étagère d'un ingrédient d'après son nom (ce qui est inconnu va au placard). */
function ingredientShelf(string $name): string
{
    $groups = [
        'porte'    => ['huile', 'miel', 'moutarde', 'sauce', 'vinaigre', 'confiture', 'jus', 'harissa', 'ketchup', 'mayonnaise'],
        'viandes'  => ['poulet', 'dinde', 'boeuf', 'viande', 'saumon', 'thon', 'crevette', 'poisson', 'jambon', 'agneau', 'veau', 'sardine'],
        'laitiers' => ['lait', 'yaourt', 'fromage', 'oeuf', 'beurre', 'creme', 'mozzarella', 'feta', 'ricotta', 'skyr', 'lben', 'raib'],
        'legumes'  => ['pomme de terre', 'patate', 'brocoli', 'tomate', 'concombre', 'epinard', 'laitue', 'carotte', 'courgette',
                       'oignon', 'ail', 'poivron', 'champignon', 'persil', 'menthe', 'aubergine', 'chou', 'roquette', 'salade',
                       'betterave', 'celeri', 'radis', 'navet', 'coriandre', 'piment', 'fenouil', 'poireau', 'haricots verts'],
        'fruits'   => ['banane', 'avocat', 'citron', 'fraise', 'pomme', 'orange', 'mangue', 'raisin', 'poire', 'peche', 'framboise',
                       'myrtille', 'ananas', 'kiwi', 'pasteque', 'melon', 'cerise', 'abricot', 'datte', 'figue', 'clementine', 'mandarine'],
    ];
    foreach ($groups as $shelf => $words) {
        if (matchKeyword($name, array_fill_keys($words, true), null, true)) {
            return $shelf;
        }
    }
    return 'placard';
}


/* ------------------------------------------------------------------ */
/*  Mode cuisine : ingrédients et geste de chaque étape                 */
/* ------------------------------------------------------------------ */

/**
 * Ingrédients de la recette cités dans le texte d'une étape (« Griller le poulet » => Blanc de poulet).
 * Un ingrédient est reconnu si l'un de ses mots (pluriel accepté) apparaît dans l'étape.
 * @param array $lignes lignes de RecetteIngredient::byRecette()
 */
function stepIngredients(string $step, array $lignes): array
{
    $t    = normalizeText($step);
    $stop = ['de', 'du', 'des', 'la', 'le', 'les', 'aux', 'au', 'et', 'complete', 'completes', 'complet', 'douce'];
    $out  = [];
    foreach ($lignes as $l) {
        $words = preg_split("/[^a-z0-9]+/", normalizeText($l['nom']), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($words as $w) {
            if (strlen($w) < 3 || in_array($w, $stop, true)) {
                continue;
            }
            $root = preg_replace('/(s|x)$/', '', $w);
            if (preg_match('/\b' . preg_quote($root, '/') . '(?:s|x)?\b/u', $t)) {
                $out[] = $l;
                break;
            }
        }
    }
    return $out;
}

/** Geste principal d'une étape : [icône Bootstrap, libellé] (affiché en pastille dans le mode cuisine). */
function stepAction(string $step): array
{
    $map = [
        'couper' => ['bi-scissors', 'Découpe'], 'decouper' => ['bi-scissors', 'Découpe'], 'emincer' => ['bi-scissors', 'Découpe'],
        'hacher' => ['bi-scissors', 'Découpe'], 'eplucher' => ['bi-scissors', 'Préparation'], 'laver' => ['bi-droplet', 'Lavage'],
        'prechauffer' => ['bi-thermometer-half', 'Four'], 'enfourner' => ['bi-thermometer-half', 'Four'],
        'cuire' => ['bi-fire', 'Cuisson'], 'griller' => ['bi-fire', 'Cuisson'], 'faire revenir' => ['bi-fire', 'Cuisson'],
        'faire sauter' => ['bi-fire', 'Cuisson'], 'mijoter' => ['bi-fire', 'Cuisson'], 'rotir' => ['bi-fire', 'Cuisson'],
        'bouillir' => ['bi-fire', 'Cuisson'], 'vapeur' => ['bi-moisture', 'Vapeur'],
        'melanger' => ['bi-cup-hot', 'Mélange'], 'battre' => ['bi-cup-hot', 'Mélange'], 'mixer' => ['bi-cup-hot', 'Mixage'],
        'incorporer' => ['bi-cup-hot', 'Mélange'], 'ajouter' => ['bi-plus-circle', 'Ajout'], 'verser' => ['bi-droplet', 'Versez'],
        'mariner' => ['bi-droplet-half', 'Marinade'], 'assaisonner' => ['bi-droplet-half', 'Assaisonnement'],
        'laisser' => ['bi-snow2', 'Repos'], 'refroidir' => ['bi-snow2', 'Repos'], 'conserver' => ['bi-snow2', 'Conservation'],
        'assembler' => ['bi-boxes', 'Assemblage'], 'servir' => ['bi-boxes', 'Service'], 'garnir' => ['bi-boxes', 'Garniture'],
        'parsemer' => ['bi-stars', 'Finition'], 'plier' => ['bi-stars', 'Finition'], 'rouler' => ['bi-stars', 'Finition'],
        'tartiner' => ['bi-stars', 'Garniture'],
    ];
    $n = normalizeText($step);
    $best = null; $bestPos = PHP_INT_MAX;
    foreach ($map as $verb => $v) {
        if (preg_match('/\b' . preg_quote($verb, '/') . '/u', $n, $m, PREG_OFFSET_CAPTURE) && $m[0][1] < $bestPos) {
            $best = $v; $bestPos = $m[0][1];
        }
    }
    return $best ?? ['bi-egg-fried', 'À faire'];
}
