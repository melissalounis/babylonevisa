<?php
// telecharger_fichier.php - Version configurable

session_start();

// Configuration
$config = [
    'db_host' => 'localhost',
    'db_name' => 'babylone_service',
    'db_user' => 'root',
    'db_pass' => '',
    'upload_base_dir' => __DIR__ . '/../../uploads/',
    'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip', 'rar'],
    'max_file_size' => 50 * 1024 * 1024, // 50MB
];

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die("Accès non autorisé. Veuillez vous connecter.");
}

// Vérifier les paramètres
if (!isset($_GET['file']) || empty($_GET['file'])) {
    http_response_code(400);
    die("Paramètre 'file' manquant.");
}

$filename = basename($_GET['file']);
$file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Vérifier l'extension du fichier
if (!in_array($file_extension, $config['allowed_extensions'])) {
    http_response_code(403);
    die("Type de fichier non autorisé.");
}

try {
    // Connexion à la base de données
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8",
        $config['db_user'],
        $config['db_pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier les permissions
    $user_email = get_user_email($pdo, $_SESSION['user_id']);
    if (!$user_email) {
        http_response_code(403);
        die("Utilisateur non trouvé.");
    }

    if (!user_has_file_access($pdo, $user_email, $filename)) {
        http_response_code(403);
        die("Accès refusé à ce fichier.");
    }

    // Trouver le fichier
    $file_path = find_file($filename, $config['upload_base_dir']);
    if (!$file_path) {
        http_response_code(404);
        show_detailed_error_page($filename, $config['upload_base_dir']);
        exit;
    }

    // Vérifier la taille du fichier
    $file_size = filesize($file_path);
    if ($file_size > $config['max_file_size']) {
        http_response_code(413);
        die("Fichier trop volumineux.");
    }

    // Télécharger le fichier
    download_file($file_path, $filename, $file_extension);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database error in telecharger_fichier.php: " . $e->getMessage());
    die("Erreur de base de données.");
} catch (Exception $e) {
    http_response_code(500);
    error_log("Error in telecharger_fichier.php: " . $e->getMessage());
    die("Erreur lors du téléchargement.");
}

// Fonctions utilitaires
function get_user_email($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    return $user ? $user['email'] : null;
}

function user_has_file_access($pdo, $user_email, $filename) {
    $file_fields = [
        'certificat_file', 'releve_2nde', 'releve_1ere', 'releve_terminale',
        'releve_bac', 'diplome_bac', 'certificat_scolarite'
    ];

    $placeholders = str_repeat('?, ', count($file_fields) - 1) . '?';
    $params = array_merge([$user_email], array_fill(0, count($file_fields), $filename));

    $sql = "SELECT COUNT(*) as count FROM demandes_etudes_roumanie 
            WHERE email = ? AND (";

    $conditions = [];
    foreach ($file_fields as $field) {
        $conditions[] = "$field = ?";
    }
    $sql .= implode(' OR ', $conditions) . ")";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch();

    return $result['count'] > 0;
}

function find_file($filename, $base_dir) {
    $search_paths = [
        $base_dir . 'roumanie/' . $filename,
        $base_dir . $filename,
        __DIR__ . '/../uploads/roumanie/' . $filename,
        __DIR__ . '/uploads/roumanie/' . $filename,
        './uploads/roumanie/' . $filename
    ];

    foreach ($search_paths as $path) {
        if (file_exists($path) && is_file($path) && is_readable($path)) {
            return $path;
        }
    }

    // Recherche récursive dans le dossier uploads
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getFilename() === $filename) {
            return $file->getPathname();
        }
    }

    return null;
}

function download_file($file_path, $filename, $extension) {
    $mime_types = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'txt' => 'text/plain',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed'
    ];

    $content_type = $mime_types[$extension] ?? 'application/octet-stream';
    $safe_filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    $file_size = filesize($file_path);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $content_type);
    header('Content-Disposition: attachment; filename="' . $safe_filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $file_size);

    // Nettoyer les buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    readfile($file_path);
    exit;
}

function show_detailed_error_page($filename, $base_dir) {
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Fichier non trouvé</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .error { color: #d63031; background: #ffeaa7; padding: 20px; border-radius: 5px; }
            .debug { background: #f8f9fa; padding: 15px; margin: 15px 0; border-left: 4px solid #3498db; }
        </style>
    </head>
    <body>
        <div class="error">
            <h2>❌ Fichier non trouvé</h2>
            <p><strong>Fichier :</strong> <?= htmlspecialchars($filename) ?></p>
            <div class="debug">
                <h3>Informations de débogage :</h3>
                <p><strong>Base directory :</strong> <?= htmlspecialchars($base_dir) ?></p>
                <p><strong>Script path :</strong> <?= htmlspecialchars(__FILE__) ?></p>
                <p>Vérifiez que le fichier existe dans le dossier uploads/roumanie/</p>
            </div>
            <p><a href="javascript:history.back()">← Retour</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>