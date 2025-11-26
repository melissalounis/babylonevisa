<?php
// includes/functions.php
require_once __DIR__ . '/../config.php';

function generate_csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


/**
 * Enregistre un fichier uploadé en sécurité.
 * $file = $_FILES['input_name']
 * $subdir = sous-dossier (ex: 'photos', 'passeports')
 * Retourne path relatif (ex: '2025/09/photos/abc.pdf') ou false si erreur
 */
function saveUploadedFile(array $file, string $subdir) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;

    // types autorisés
    $allowed = [
        'image/jpeg' => '.jpg',
        'image/png'  => '.png',
        'application/pdf' => '.pdf',
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!array_key_exists($mime, $allowed)) return false;

    // créer dossier
    $year = date('Y');
    $month = date('m');
    $dir = UPLOAD_BASE . "/$year/$month/$subdir";
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // nom de fichier unique
    $ext = $allowed[$mime];
    $safeName = bin2hex(random_bytes(12)) . $ext;
    $target = $dir . DIRECTORY_SEPARATOR . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $target)) return false;
    // optionnel: chmod($target, 0644);

    // retourner chemin relatif pour stocker en DB
    return "$year/$month/$subdir/$safeName";
}
