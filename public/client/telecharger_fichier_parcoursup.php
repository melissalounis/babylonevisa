<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer le fichier
    if (isset($_GET['id'])) {
        $stmt = $pdo->prepare("
            SELECT f.*, d.user_id 
            FROM demandes_parcoursup_fichiers f
            JOIN demandes_parcoursup d ON f.demande_id = d.id
            WHERE f.id = ? AND d.user_id = ?
        ");
        $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
        $fichier = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fichier) {
            $filepath = __DIR__ . "/../../../uploads/" . $fichier['chemin_fichier'];
            
            if (file_exists($filepath)) {
                // Déterminer le type MIME
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $filepath);
                finfo_close($finfo);
                
                // En-têtes pour le téléchargement
                header('Content-Type: ' . $mime_type);
                header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
                header('Content-Length: ' . filesize($filepath));
                
                // Lire le fichier
                readfile($filepath);
                exit;
            } else {
                die("Fichier non trouvé sur le serveur.");
            }
        } else {
            die("Fichier non trouvé ou accès non autorisé.");
        }
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>