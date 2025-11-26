<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Configuration de la base de données
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

// Créer la connexion
$conn = new mysqli($host, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Générer un numéro de dossier unique
    $numero_dossier = "RF-" . date('Ymd-His') . "-" . rand(1000, 9999);
    
    // Récupérer et échapper les données du formulaire
    $nom_complet = $conn->real_escape_string($_POST['nom_complet']);
    $date_naissance = $conn->real_escape_string($_POST['date_naissance']);
    $nationalite = $conn->real_escape_string($_POST['nationalite']);
    $email = $conn->real_escape_string($_POST['email']);
    $telephone = $conn->real_escape_string($_POST['telephone']);
    $nom_famille = $conn->real_escape_string($_POST['nom_famille']);
    $lien_parente = $conn->real_escape_string($_POST['lien_parente']);
    $adresse_famille = $conn->real_escape_string($_POST['adresse_famille']);
    $commentaire = $conn->real_escape_string($_POST['commentaire'] ?? '');
    
    // Gestion des fichiers uploadés
    $passeport = uploadFile('passeport', $numero_dossier);
    $titre_sejour = uploadFile('titre_sejour', $numero_dossier);
    $acte_mariage = uploadFile('acte_mariage', $numero_dossier);
    $justificatif_logement = uploadFile('justificatif_logement', $numero_dossier);
    $ressources = uploadFile('ressources', $numero_dossier);
    $paiement = uploadFile('paiement', $numero_dossier);
    
    // Gestion des fichiers multiples
    $preuves_liens = uploadMultipleFiles('preuves_liens', $numero_dossier);
    
    // Vérifier si la table existe, sinon la créer
    createTableIfNotExists($conn);
    
    // Préparer et exécuter la requête SQL
    $sql = "INSERT INTO demandes_regroupement_familial (
        user_id, numero_dossier, nom_complet, date_naissance, nationalite, email, telephone, 
        nom_famille, lien_parente, adresse_famille, commentaire, statut, date_creation
    ) VALUES (
        '$user_id', '$numero_dossier', '$nom_complet', '$date_naissance', '$nationalite', '$email', '$telephone',
        '$nom_famille', '$lien_parente', '$adresse_famille', '$commentaire', 'nouveau', NOW()
    )";
    
    if ($conn->query($sql) === TRUE) {
        $demande_id = $conn->insert_id;
        
        // Enregistrer les documents dans la table des documents
        saveDocuments($conn, $demande_id, [
            'passeport' => $passeport,
            'titre_sejour' => $titre_sejour,
            'acte_mariage' => $acte_mariage,
            'justificatif_logement' => $justificatif_logement,
            'ressources' => $ressources,
            'paiement' => $paiement,
            'preuves_liens' => $preuves_liens
        ], $numero_dossier);
        
        // Rediriger vers la page des demandes avec le numéro de dossier
        header("Location: mes_demandes_regroupement_familial.php?success=1&dossier=" . $numero_dossier);
        exit();
    } else {
        echo "Erreur: " . $sql . "<br>" . $conn->error;
    }
    
    // Fermer la connexion
    $conn->close();
}

// Fonction pour créer la table si elle n'existe pas
function createTableIfNotExists($conn) {
    $sql = "CREATE TABLE IF NOT EXISTS demandes_regroupement_familial (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        numero_dossier VARCHAR(50) UNIQUE NOT NULL,
        nom_complet VARCHAR(255) NOT NULL,
        date_naissance DATE NOT NULL,
        nationalite VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        telephone VARCHAR(50) NOT NULL,
        nom_famille VARCHAR(255) NOT NULL,
        lien_parente ENUM('conjoint', 'enfant', 'parent', 'autre') NOT NULL,
        adresse_famille TEXT NOT NULL,
        commentaire TEXT,
        statut ENUM('nouveau', 'en_cours', 'confirme', 'refuse', 'complet') DEFAULT 'nouveau',
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($sql)) {
        die("Erreur création table: " . $conn->error);
    }
    
    // Créer aussi la table des documents
    $sql_docs = "CREATE TABLE IF NOT EXISTS documents_regroupement_familial (
        id INT PRIMARY KEY AUTO_INCREMENT,
        demande_id INT NOT NULL,
        type_document VARCHAR(100) NOT NULL,
        nom_fichier VARCHAR(255) NOT NULL,
        chemin_fichier VARCHAR(500) NOT NULL,
        date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (demande_id) REFERENCES demandes_regroupement_familial(id) ON DELETE CASCADE
    )";
    
    $conn->query($sql_docs);
}

// Fonction pour enregistrer les documents
function saveDocuments($conn, $demande_id, $documents, $numero_dossier) {
    $types_documents = [
        'passeport' => 'Passeport du demandeur',
        'titre_sejour' => 'Titre de séjour',
        'acte_mariage' => 'Acte de mariage/naissance',
        'justificatif_logement' => 'Justificatif de logement',
        'ressources' => 'Preuves de ressources',
        'paiement' => 'Reçu de paiement',
        'preuves_liens' => 'Preuves de liens familiaux'
    ];
    
    foreach ($documents as $type => $fichiers) {
        if (!empty($fichiers)) {
            $fichiers_array = explode(',', $fichiers);
            foreach ($fichiers_array as $fichier) {
                if (!empty($fichier)) {
                    $type_document = $types_documents[$type] ?? $type;
                    $chemin_fichier = "uploads/regroupement_familial/" . $fichier;
                    
                    $sql = "INSERT INTO documents_regroupement_familial 
                           (demande_id, type_document, nom_fichier, chemin_fichier) 
                           VALUES 
                           ('$demande_id', '$type_document', '$fichier', '$chemin_fichier')";
                    
                    $conn->query($sql);
                }
            }
        }
    }
}

// Fonction pour uploader un fichier unique
function uploadFile($fieldName, $numero_dossier) {
    if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/regroupement_familial/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
        $fileName = $numero_dossier . '_' . $fieldName . '_' . time() . '.' . $fileExtension;
        $uploadFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $uploadFile)) {
            return $fileName;
        }
    }
    return '';
}

// Fonction pour uploader plusieurs fichiers
function uploadMultipleFiles($fieldName, $numero_dossier) {
    $fileNames = [];
    
    if (isset($_FILES[$fieldName])) {
        $uploadDir = 'uploads/regroupement_familial/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileCount = count($_FILES[$fieldName]['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES[$fieldName]['error'][$i] === UPLOAD_ERR_OK) {
                $fileExtension = pathinfo($_FILES[$fieldName]['name'][$i], PATHINFO_EXTENSION);
                $fileName = $numero_dossier . '_' . $fieldName . '_' . $i . '_' . time() . '.' . $fileExtension;
                $uploadFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES[$fieldName]['tmp_name'][$i], $uploadFile)) {
                    $fileNames[] = $fileName;
                }
            }
        }
    }
    
    return implode(',', $fileNames);
}
?>