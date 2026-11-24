<?php
declare(strict_types=1);

function e(string|int $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function route(string $page, array $params = []): string
{
    return 'index.php?' . http_build_query(['page' => $page] + $params, '', '&', PHP_QUERY_RFC3986);
}

function redirect(string $page, array $params = []): never
{
    header('Location: ' . route($page, $params), true, 303);
    exit;
}

function input(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) ? $value : '';
}

function recordId(): ?int
{
    $id = filter_var(input($_GET, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function isAdmin(): bool
{
    return ($_SESSION['nutrition_admin'] ?? false) === true;
}

function nutritionProfileId(): ?int
{
    return $_SESSION['nutrition_profile_id'] ?? null;
}

function csrfToken(): string
{
    return $_SESSION['nutrition_csrf'] ??= bin2hex(random_bytes(32));
}

function validCsrf(): bool
{
    return hash_equals(csrfToken(), input($_POST, 'csrf'));
}

function flash(string $message): void
{
    $_SESSION['nutrition_flash'] = $message;
}

function takeFlash(): ?string
{
    $message = $_SESSION['nutrition_flash'] ?? null;
    unset($_SESSION['nutrition_flash']);
    return $message;
}

function excerpt(string $text, int $limit = 128): string
{
    $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';
    return mb_strimwidth($text, 0, $limit, '…', 'UTF-8');
}

function photo(string $name): string
{
    return 'assets/imges/photos/' . rawurlencode($name) . '.webp';
}

function dailyReminders(): array
{
    return [
        'matin' => ['Petit-déjeuner', 'sun', '08:00', 'C’est l’heure du petit-déjeuner.'],
        'midi' => ['Déjeuner', 'bowl', '12:30', 'C’est l’heure du déjeuner.'],
        'collation' => ['Collation', 'apple', '16:00', 'C’est l’heure de la collation.'],
        'soir' => ['Dîner', 'moon', '19:30', 'C’est l’heure du dîner.'],
        'eau' => ['Boire de l’eau', 'drop', '10:00', 'Pensez à boire un verre d’eau.'],
    ];
}

function icon(string $name, int $size = 20): string
{
    $paths = [
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/>',
        'leaf' => '<path d="M20 4c-6 0-12 1-14 5-2 3-1 7 2 9 3 2 8 1 10-3 2-3 2-8 2-11ZM4 21 15 10"/>',
        'utensils' => '<path d="M4 3v6a3 3 0 0 0 6 0V3M7 3v18M20 21V3c-2 1-4 4-4 7v3h4"/>',
        'bowl' => '<path d="M3 12h18a9 9 0 0 1-18 0Zm5 9h8M8 9c0-1 1-1.5 1-2.5S8 5 8 4m5 5c0-1 1-1.5 1-2.5S13 5 13 4"/>',
        'bell' => '<path d="M18 8a6 6 0 1 0-12 0c0 6-3 6-3 9h18c0-3-3-3-3-9m-8 12a2 2 0 0 0 4 0"/>',
        'sun' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M5 5l1.5 1.5m11 11L19 19M5 19l1.5-1.5m11-11L19 5"/>',
        'moon' => '<path d="M20.8 15.4A9 9 0 0 1 8.6 3.2 9 9 0 1 0 20.8 15.4Z"/>',
        'apple' => '<path d="M12 8c-2-2-6-2-8 1-2 4 1 12 5 12 1 0 2-1 3-1s2 1 3 1c4 0 7-8 5-12-2-3-6-3-8-1Zm0 0c0-3 1.5-5 4.5-5"/>',
        'shield' => '<path d="M12 22s7-3.5 7-10V6l-7-3-7 3v6c0 6.5 7 10 7 10Z"/><path d="m9 12 2 2 4-4"/>',
        'trend-up' => '<path d="m3 17 6-6 4 4 7-8m-5 0h5v5"/>',
        'trend-down' => '<path d="m3 7 6 6 4-4 7 8m-5 0h5v-5"/>',
        'dumbbell' => '<path d="M8 12h8M3 10v4m18-4v4M5 8h3v8H5zm11 0h3v8h-3z"/>',
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'arrow' => '<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'back' => '<path d="M19 12H5m6 6-6-6 6-6"/>',
        'chevron' => '<path d="m9 18 6-6-6-6"/>',
        'up-right' => '<path d="M7 17 17 7M7 7h10v10"/>',
        'lock' => '<rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4 4"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'edit' => '<path d="m14 5 5 5m2-2-12 12-5 1 1-5L17 4a2.8 2.8 0 0 1 4 4Z"/>',
        'trash' => '<path d="M3 6h18M9 6V3h6v3m4 0-1 15H6L5 6m5 4v7m4-7v7"/>',
        'logout' => '<path d="M11 20H5V4h6m-1 8h11m-4-4 4 4-4 4"/>',
        'up' => '<path d="m6 12 6-6 6 6m-6-6v13"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'alert' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v6m0 3v1"/>',
        'eye' => '<path d="M2 12s4-6 10-6 10 6 10 6-4 6-10 6-10-6-10-6Z"/><circle cx="12" cy="12" r="2.5"/>',
        'rotate' => '<path d="M21 3v6h-6M3 21v-6h6M20 9a8 8 0 0 0-14-3L3 9m18 6-3 3a8 8 0 0 1-14-3"/>',
        'pause' => '<path d="M9 5v14m6-14v14"/>',
        'play' => '<path d="m8 5 12 7-12 7z"/>',
        'profile' => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a5 5 0 0 1 5-5h6a5 5 0 0 1 5 5v2"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4m10-4v4M3 10h18m-12 5h2m3 0h2"/>',
        'scale' => '<rect x="3" y="4" width="18" height="16" rx="3"/><path d="M7 11a5 5 0 0 1 10 0m-5 1 2-3"/>',
        'ruler' => '<rect x="4" y="7" width="16" height="10" rx="2"/><path d="M8 7v4m4-4v6m4-6v4"/>',
        'drop' => '<path d="M12 3c2.4 3.4 6.5 7.4 6.5 11.5a6.5 6.5 0 0 1-13 0C5.5 10.4 9.6 6.4 12 3Z"/><path d="M9 15a3 3 0 0 0 3 3"/>',
        'energy' => '<path d="m13 2-8 12h6l-1 8 9-12h-6l1-8Z"/>',
        'grain' => '<path d="M5 21 18 8m-7 7c-4 0-6-2-6-5 3 0 5 1 6 5Zm3-3c0-4-2-6-3-7-1 3 0 5 3 7Zm0 0c4 0 6-2 7-4-3-1-6 1-7 4Zm4-4c-1-4 0-6 2-7 1 3 0 5-2 7Z"/>',
        'egg' => '<path d="M19 14c0 4-3 7-7 7s-7-3-7-7c0-3.6 3.8-11 7-11s7 7.4 7 11Z"/><path d="M9 15.5c.4 1 1.2 1.7 2.3 1.9"/>',
        'fibre' => '<path d="M12 22v-9m0 1C5 14 3 10 4 6c4 1 8 3 8 8Zm0 0c0-6 3-9 8-10 1 5-2 10-8 10Z"/>',
    ];

    return '<svg aria-hidden="true" width="' . $size . '" height="' . $size
        . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">'
        . $paths[$name] . '</svg>';
}
