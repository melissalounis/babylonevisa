// Créez le fichier telecharger_fichier_belgique.php
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("HTTP/1.0 403 Forbidden");
    exit;
}

if (!isset($_GET['file']) || empty($_GET['file'])) {
    header("HTTP/1.0 400 Bad Request");
    exit;
}

$filename = basename($_GET['file']);
$filepath = "../../uploads/belgique/" . $filename;

// Vérifier que le fichier existe et est sécurisé
if (!file_exists($filepath) || !is_file($filepath)) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Vérifier que l'utilisateur a le droit d'accéder à ce fichier
// (vous devriez implémenter cette vérification)

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
readfile($filepath);
exit;
// Après la récupération de la demande, vérifiez l'appartenance
if ($demande && $demande['user_id'] != $_SESSION['user_id']) {
    $error_message = "Accès non autorisé à cette demande.";
    $demande = null;
}

// Validation supplémentaire des fichiers uploadés
function uploadFile($file, $upload_dir, $ancien_fichier = null) {
    // Vérifications de sécurité
    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $max_size = 10 * 1024 * 1024; // 10MB
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Vérifier la taille
        if ($file['size'] > $max_size) {
            throw new Exception("Fichier trop volumineux. Maximum 10MB autorisé.");
        }
        
        // Vérifier l'extension
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception("Type de fichier non autorisé.");
        }
        
        $filename = uniqid() . '_' . basename($file['name']);
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            if ($ancien_fichier && file_exists($upload_dir . $ancien_fichier)) {
                unlink($upload_dir . $ancien_fichier);
            }
            return $filename;
        }
    }
    return $ancien_fichier;
}
?>
