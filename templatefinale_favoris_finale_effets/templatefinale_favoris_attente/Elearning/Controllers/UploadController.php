<?php
declare(strict_types=1);

/**
 * Upload d'image pour les événements.
 * Sauvegarde dans Views/assets/img/events/ et retourne le chemin relatif.
 *
 * POST api.php?resource=upload  (multipart/form-data, champ : image)
 */
class UploadController
{
    private const MAX_SIZE   = 5 * 1024 * 1024; // 5 Mo
    private const ALLOWED    = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const EXT_MAP    = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    private const UPLOAD_DIR = 'Views/assets/img/events/';

    public function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée.']);
            exit;
        }

        if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            http_response_code(422);
            echo json_encode(['error' => 'Aucun fichier reçu.']);
            exit;
        }

        $file = $_FILES['image'];

        // Erreur PHP upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(422);
            echo json_encode(['error' => 'Erreur lors du transfert du fichier (code ' . $file['error'] . ').']);
            exit;
        }

        // Taille
        if ($file['size'] > self::MAX_SIZE) {
            http_response_code(422);
            echo json_encode(['error' => 'Le fichier dépasse 5 Mo.']);
            exit;
        }

        // Type MIME réel (pas le type déclaré par le navigateur)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, self::ALLOWED, true)) {
            http_response_code(422);
            echo json_encode(['error' => 'Format non autorisé. Utilisez JPG, PNG, WEBP ou GIF.']);
            exit;
        }

        // Dossier de destination
        $uploadDir = BASE_PATH . '/' . self::UPLOAD_DIR;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        // Nom unique
        $ext      = self::EXT_MAP[$mimeType];
        $filename = bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            http_response_code(500);
            echo json_encode(['error' => 'Impossible de sauvegarder le fichier. Vérifiez les permissions du dossier Views/assets/img/events/.']);
            exit;
        }

        // Chemin relatif stocké en BDD
        $relativePath = self::UPLOAD_DIR . $filename;

        echo json_encode([
            'path'     => $relativePath,
            'filename' => $filename,
        ], JSON_UNESCAPED_SLASHES);
        exit;
    }
}
