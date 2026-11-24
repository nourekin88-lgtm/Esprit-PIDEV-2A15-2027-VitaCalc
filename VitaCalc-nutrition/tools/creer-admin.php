<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function prompt(string $label): string
{
    fwrite(STDOUT, $label);
    return rtrim((string) fgets(STDIN), "\r\n");
}

$user = trim(prompt('Identifiant administrateur : '));
if (!preg_match('/\A[a-zA-Z0-9_.-]{3,60}\z/', $user)) {
    fwrite(STDERR, "Utilisez 3 à 60 lettres ASCII, chiffres, points, tirets ou caractères soulignés.\n");
    exit(1);
}

$password = prompt('Mot de passe (saisie visible dans ce terminal) : ');
if (strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
    fwrite(STDERR, "Le mot de passe doit contenir de 12 à 72 octets.\n");
    exit(1);
}
if (!hash_equals($password, prompt('Confirmez le mot de passe : '))) {
    fwrite(STDERR, "Les mots de passe diffèrent.\n");
    exit(1);
}

$target = __DIR__ . '/../config/admin.local.php';
if (is_file($target) && strtolower(trim(prompt('Remplacer le compte existant ? (oui/non) '))) !== 'oui') {
    exit("Compte existant conservé.\n");
}

$config = ['user' => $user, 'hash' => password_hash($password, PASSWORD_BCRYPT)];
$temporary = tempnam(dirname($target), 'vitacalc-');
if ($temporary === false) {
    fwrite(STDERR, "Impossible de créer le fichier de configuration.\n");
    exit(1);
}
$source = "<?php\nreturn " . var_export($config, true) . ";\n";
if (file_put_contents($temporary, $source) !== strlen($source) || !rename($temporary, $target)) {
    unlink($temporary);
    fwrite(STDERR, "Impossible d’enregistrer le compte dans config/admin.local.php.\n");
    exit(1);
}

fwrite(STDOUT, "Compte administrateur enregistré.\n");
