
<?php
// save_biometrie.php
session_start();

// Configuration de la base de données
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuration upload
$dossier_upload = "uploads/biometrie/";
$documents_autorises = ['pdf', 'jpg', 'jpeg', 'png'];
$taille_max = 5 * 1024 * 1024; // 5MB

// Connexion à la base de données
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur de connexion à la base de données. Veuillez réessayer.";
    header('Location: rendez_vous_biometrie.php');
    exit();
}

// Créer le dossier d'upload
if (!file_exists($dossier_upload)) {
    mkdir($dossier_upload, 0755, true);
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $nationalite = trim($_POST['nationalite'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $numero_passeport = trim($_POST['numero_passeport'] ?? '');

    // Traitement des personnes supplémentaires
    $personnes_supplementaires = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'personne_nom_') === 0) {
            $index = substr($key, 13);
            $nom = trim($value);
            $naissance = trim($_POST['personne_naissance_' . $index] ?? '');
            $passeport = trim($_POST['personne_passeport_' . $index] ?? '');
            
            if (!empty($nom) && !empty($naissance) && !empty($passeport)) {
                $personnes_supplementaires[] = [
                    'nom' => $nom,
                    'naissance' => $naissance,
                    'passeport' => $passeport
                ];
            }
        }
    }

    // Traitement des fichiers
    $documents_uploades = [];
    $upload_reussi = true;

    // Passeport
    if (isset($_FILES['passeport']) && $_FILES['passeport']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['passeport']['name'];
        $file_tmp = $_FILES['passeport']['tmp_name'];
        $file_size = $_FILES['passeport']['size'];
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($extension, $documents_autorises) && $file_size <= $taille_max) {
            $nouveau_nom = uniqid() . '_passeport_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file_name);
            $chemin_fichier = $dossier_upload . $nouveau_nom;
            
            if (move_uploaded_file($file_tmp, $chemin_fichier)) {
                $documents_uploades['passeport'] = $nouveau_nom;
            } else {
                $upload_reussi = false;
            }
        } else {
            $upload_reussi = false;
        }
    } else {
        $upload_reussi = false;
    }

    // Lettre de biométrie
    if (isset($_FILES['lettre_biometrie']) && $_FILES['lettre_biometrie']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['lettre_biometrie']['name'];
        $file_tmp = $_FILES['lettre_biometrie']['tmp_name'];
        $file_size = $_FILES['lettre_biometrie']['size'];
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($extension, $documents_autorises) && $file_size <= $taille_max) {
            $nouveau_nom = uniqid() . '_lettre_biometrie_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file_name);
            $chemin_fichier = $dossier_upload . $nouveau_nom;
            
            if (move_uploaded_file($file_tmp, $chemin_fichier)) {
                $documents_uploades['lettre_biometrie'] = $nouveau_nom;
            } else {
                $upload_reussi = false;
            }
        } else {
            $upload_reussi = false;
        }
    } else {
        $upload_reussi = false;
    }

    // Enregistrement en base de données
    if ($upload_reussi && isset($documents_uploades['passeport']) && isset($documents_uploades['lettre_biometrie'])) {
        try {
            // Créer la table si elle n'existe pas
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS rendez_vous_biometrie (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    nom_complet VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    telephone VARCHAR(50) NOT NULL,
                    nationalite VARCHAR(100) NOT NULL,
                    date_naissance DATE NOT NULL,
                    numero_passeport VARCHAR(100) NOT NULL,
                    personnes_supplementaires TEXT NULL,
                    passeport_path VARCHAR(255) NOT NULL,
                    lettre_biometrie_path VARCHAR(255) NOT NULL,
                    statut ENUM('nouveau', 'confirme', 'annule', 'termine') DEFAULT 'nouveau',
                    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ");

            $sql = "INSERT INTO rendez_vous_biometrie 
                    (nom_complet, email, telephone, nationalite, date_naissance, 
                     numero_passeport, personnes_supplementaires, passeport_path, 
                     lettre_biometrie_path, statut) 
                    VALUES 
                    (:nom_complet, :email, :telephone, :nationalite, :date_naissance,
                     :numero_passeport, :personnes_supplementaires, :passeport_path,
                     :lettre_biometrie_path, 'nouveau')";
            
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':nom_complet' => $nom_complet,
                ':email' => $email,
                ':telephone' => $telephone,
                ':nationalite' => $nationalite,
                ':date_naissance' => $date_naissance,
                ':numero_passeport' => $numero_passeport,
                ':personnes_supplementaires' => !empty($personnes_supplementaires) ? json_encode($personnes_supplementaires) : null,
                ':passeport_path' => $documents_uploades['passeport'],
                ':lettre_biometrie_path' => $documents_uploades['lettre_biometrie']
            ]);
            
            // Message de succès
            $_SESSION['success_message'] = "Votre demande de rendez-vous biométrie a été envoyée avec succès !";
            
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Une erreur est survenue lors de l'enregistrement. Veuillez réessayer.";
        }
    } else {
        $_SESSION['error_message'] = "Erreur lors du téléchargement des fichiers. Veuillez vérifier les formats et la taille.";
    }
}

// Redirection vers le formulaire
header('Location: rendez_vous_biometrie.php');
exit();
?>