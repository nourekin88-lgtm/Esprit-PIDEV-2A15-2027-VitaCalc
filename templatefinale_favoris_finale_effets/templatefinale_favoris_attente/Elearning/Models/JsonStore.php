<?php
declare(strict_types=1);

/** Petite couche de persistance JSON pour le prototype local.
 *  Pour une mise en production, remplacer par PDO + MySQL.
 */
class JsonStore
{
    public static function read(string $file, array $fallback = []): array
    {
        if (!is_file($file)) {
            self::write($file, $fallback);
            return $fallback;
        }

        $json = file_get_contents($file);
        $data = json_decode($json ?: '[]', true);
        return is_array($data) ? $data : $fallback;
    }

    public static function write(string $file, array $data): void
    {
        $directory = dirname($file);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $json = json_encode(
            array_values($data),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        file_put_contents($file, $json . PHP_EOL, LOCK_EX);
    }
}
