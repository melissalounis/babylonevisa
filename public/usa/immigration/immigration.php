<?php
// Fichier: green_card_form.php
session_start();

// Configuration de la base de données
require_once __DIR__ . '/../../../config.php';

// Initialisation des variables pour éviter les erreurs
$errors = [];
$success = false;
$demande_id = null;
$reference = '';



// Fonction de validation des entrées
function test_input($data) {
    if (empty($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fonction de validation d'email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Fonction de validation de téléphone
function validatePhone($phone) {
    return preg_match('/^[0-9+\-\s\(\)]{10,20}$/', $phone);
}

// Fonction de validation de date
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Vérifier si la table existe
function checkTableExists($pdo, $tableName) {
    try {
        $result = $pdo->query("SELECT 1 FROM $tableName LIMIT 1");
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Créer la table si elle n'existe pas
function createTables($pdo) {
    $sql = "
    CREATE TABLE IF NOT EXISTS demandes_green_card (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reference VARCHAR(50) UNIQUE,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        date_naissance DATE NOT NULL,
        nationalite VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        telephone VARCHAR(20) NOT NULL,
        adresse TEXT NOT NULL,
        ville VARCHAR(100),
        code_postal VARCHAR(20),
        pays_residence VARCHAR(100) NOT NULL,
        situation_familiale ENUM('celibataire', 'marie', 'divorce', 'veuf') NOT NULL,
        nombre_enfants INT DEFAULT 0,
        profession VARCHAR(100),
        employeur VARCHAR(100),
        revenu_annuel DECIMAL(10,2),
        date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
        statut ENUM('en_attente', 'en_cours', 'complet', 'approuve', 'refuse') DEFAULT 'en_attente'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS membres_famille (
        id INT AUTO_INCREMENT PRIMARY KEY,
        demande_id INT,
        nom VARCHAR(100),
        prenom VARCHAR(100),
        date_naissance DATE,
        relation ENUM('conjoint', 'enfant'),
        type_document VARCHAR(50),
        chemin_document VARCHAR(255),
        FOREIGN KEY (demande_id) REFERENCES demandes_green_card(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS documents_demande (
        id INT AUTO_INCREMENT PRIMARY KEY,
        demande_id INT,
        nom_fichier VARCHAR(255),
        chemin VARCHAR(255),
        type_document VARCHAR(100),
        date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (demande_id) REFERENCES demandes_green_card(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    try {
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        error_log("Erreur création tables: " . $e->getMessage());
        return false;
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Validation des champs obligatoires
    $required_fields = [
        'nom', 'prenom', 'date_naissance', 'nationalite', 'email', 
        'telephone', 'adresse', 'pays_residence', 'situation_familiale'
    ];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Le champ " . ucfirst(str_replace('_', ' ', $field)) . " est obligatoire.";
        }
    }
    
    // Validation spécifique des champs
    if (!empty($_POST['email']) && !validateEmail($_POST['email'])) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    if (!empty($_POST['telephone']) && !validatePhone($_POST['telephone'])) {
        $errors[] = "Le numéro de téléphone n'est pas valide.";
    }
    
    if (!empty($_POST['date_naissance']) && !validateDate($_POST['date_naissance'])) {
        $errors[] = "La date de naissance n'est pas valide.";
    }
    
    // Validation du nombre de personnes si marié
    if ($_POST['situation_familiale'] === 'marie') {
        if (empty($_POST['nombre_enfants']) && $_POST['nombre_enfants'] !== '0') {
            $errors[] = "Veuillez spécifier le nombre d'enfants.";
        }
        
        // Validation des informations du conjoint
        if (empty($_POST['nom_conjoint']) || empty($_POST['prenom_conjoint']) || empty($_POST['date_naissance_conjoint'])) {
            $errors[] = "Veuillez remplir toutes les informations du conjoint.";
        }
        
        if (!empty($_POST['date_naissance_conjoint']) && !validateDate($_POST['date_naissance_conjoint'])) {
            $errors[] = "La date de naissance du conjoint n'est pas valide.";
        }
    }
    
    // Validation des fichiers
    $max_file_size = 5 * 1024 * 1024; // 5MB
    
    // Validation des photos
    if (empty($_FILES['photos']['name'][0])) {
        $errors[] = "Veuillez télécharger au moins une photo.";
    } else {
        // Vérification de chaque photo
        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['photos']['error'][$key] !== UPLOAD_ERR_OK) {
                $errors[] = "Erreur avec la photo " . ($key + 1) . " - Code: " . $_FILES['photos']['error'][$key];
                continue;
            }
            
            if ($_FILES['photos']['size'][$key] > $max_file_size) {
                $errors[] = "La photo " . ($key + 1) . " est trop volumineuse (max 5MB).";
            }
            
            $file_extension = strtolower(pathinfo($_FILES['photos']['name'][$key], PATHINFO_EXTENSION));
            if (!in_array($file_extension, ['jpg', 'jpeg', 'png'])) {
                $errors[] = "La photo " . ($key + 1) . " doit être au format JPG, JPEG ou PNG.";
            }
        }
    }
    
    // Validation des passeports de la famille si marié
    if ($_POST['situation_familiale'] === 'marie') {
        if (empty($_FILES['passeport_conjoint']['name'])) {
            $errors[] = "Veuillez télécharger le passeport du conjoint.";
        }
        
        $nombre_enfants = intval($_POST['nombre_enfants'] ?? 0);
        for ($i = 1; $i <= $nombre_enfants; $i++) {
            if (empty($_FILES["passeport_enfant_$i"]['name'])) {
                $errors[] = "Veuillez télécharger le passeport de l'enfant $i.";
            }
        }
    }
    
    // Si pas d'erreurs, sauvegarde en base
    if (empty($errors)) {
        $pdo = connectDB();
        
        if (!$pdo) {
            $errors[] = "Erreur de connexion à la base de données. Veuillez réessayer.";
        } else {
            // Vérifier et créer les tables si nécessaire
            if (!checkTableExists($pdo, 'demandes_green_card')) {
                if (!createTables($pdo)) {
                    $errors[] = "Erreur lors de l'initialisation de la base de données.";
                }
            }
            
            if (empty($errors)) {
                try {
                    $pdo->beginTransaction();
                    
                    // Génération d'une référence unique
                    $reference = 'GC-' . date('Ymd') . '-' . strtoupper(uniqid());
                    
                    // Préparation de la requête d'insertion
                    $sql = "INSERT INTO demandes_green_card (
                        reference, nom, prenom, date_naissance, nationalite, email, telephone, 
                        adresse, ville, code_postal, pays_residence, 
                        situation_familiale, nombre_enfants, profession, employeur, 
                        revenu_annuel, date_soumission, statut
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'en_attente')";
                    
                    $stmt = $pdo->prepare($sql);
                    
                    // Nettoyage des données avant insertion
                    $stmt->execute([
                        $reference,
                        test_input($_POST['nom']),
                        test_input($_POST['prenom']),
                        test_input($_POST['date_naissance']),
                        test_input($_POST['nationalite']),
                        test_input($_POST['email']),
                        test_input($_POST['telephone']),
                        test_input($_POST['adresse']),
                        !empty($_POST['ville']) ? test_input($_POST['ville']) : null,
                        !empty($_POST['code_postal']) ? test_input($_POST['code_postal']) : null,
                        test_input($_POST['pays_residence']),
                        test_input($_POST['situation_familiale']),
                        !empty($_POST['nombre_enfants']) ? intval($_POST['nombre_enfants']) : 0,
                        !empty($_POST['profession']) ? test_input($_POST['profession']) : null,
                        !empty($_POST['employeur']) ? test_input($_POST['employeur']) : null,
                        !empty($_POST['revenu_annuel']) ? floatval($_POST['revenu_annuel']) : null
                    ]);
                    
                    $demande_id = $pdo->lastInsertId();
                    
                    // Sauvegarde des fichiers uploadés
                    $upload_success = saveUploadedFiles($demande_id, $_POST, $_FILES);
                    
                    // Sauvegarde des membres de la famille si marié
                    if ($_POST['situation_familiale'] === 'marie') {
                        $family_success = saveFamilyMembers($demande_id, $_POST, $_FILES);
                    }
                    
                    $pdo->commit();
                    $success = true;
                    
                    // Envoi d'email de confirmation
                    if ($success) {
                        sendConfirmationEmail($_POST['email'], $_POST['prenom'], $reference, $demande_id);
                    }
                    
                } catch(PDOException $e) {
                    $pdo->rollBack();
                    error_log("Erreur détaillée sauvegarde BD: " . $e->getMessage());
                    
                    // Message d'erreur plus détaillé en mode développement
                    if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
                        $errors[] = "Erreur lors de la sauvegarde: " . $e->getMessage();
                    } else {
                        $errors[] = "Erreur lors de la sauvegarde des données. Veuillez réessayer.";
                    }
                }
            }
        }
    }
}

function saveUploadedFiles($demande_id, $post_data, $files) {
    $upload_dir = "uploads/demandes/" . $demande_id . "/";
    
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            error_log("Erreur création dossier: $upload_dir");
            return false;
        }
    }
    
    $success = true;
    
    // Sauvegarde du diplôme de bac
    if (!empty($files['bac_diploma']['name']) && ($post_data['has_bac'] ?? '') === 'oui') {
        $bac_filename = uniqid() . '_bac_' . basename($files['bac_diploma']['name']);
        $bac_filepath = $upload_dir . $bac_filename;
        
        if (move_uploaded_file($files['bac_diploma']['tmp_name'], $bac_filepath)) {
            saveDocumentToDB($demande_id, $files['bac_diploma']['name'], $bac_filepath, 'bac_diploma');
        } else {
            $success = false;
            error_log("Erreur upload bac_diploma");
        }
    }
    
    // Sauvegarde du diplôme d'étude
    if (!empty($files['study_diploma']['name']) && ($post_data['has_diploma'] ?? '') === 'oui') {
        $diploma_filename = uniqid() . '_diploma_' . basename($files['study_diploma']['name']);
        $diploma_filepath = $upload_dir . $diploma_filename;
        
        if (move_uploaded_file($files['study_diploma']['tmp_name'], $diploma_filepath)) {
            saveDocumentToDB($demande_id, $files['study_diploma']['name'], $diploma_filepath, 'study_diploma');
        } else {
            $success = false;
            error_log("Erreur upload study_diploma");
        }
    }
    
    // Sauvegarde des photos
    foreach ($files['photos']['name'] as $key => $name) {
        if (!empty($name) && $files['photos']['error'][$key] === UPLOAD_ERR_OK) {
            $photo_filename = uniqid() . '_photo_' . ($key + 1) . '_' . basename($name);
            $photo_filepath = $upload_dir . $photo_filename;
            
            if (move_uploaded_file($files['photos']['tmp_name'][$key], $photo_filepath)) {
                saveDocumentToDB($demande_id, $name, $photo_filepath, 'photo_identite');
            } else {
                $success = false;
                error_log("Erreur upload photo $key");
            }
        }
    }
    
    return $success;
}

function saveFamilyMembers($demande_id, $post_data, $files) {
    $upload_dir = "uploads/demandes/" . $demande_id . "/famille/";
    
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            error_log("Erreur création dossier famille: $upload_dir");
            return false;
        }
    }
    
    try {
        $pdo = connectDB();
        if (!$pdo) return false;
        
        $success = true;
        
        // Sauvegarde du conjoint
        if (!empty($post_data['nom_conjoint'])) {
            $conjoint_passport_path = '';
            if (!empty($files['passeport_conjoint']['name'])) {
                $conjoint_filename = uniqid() . '_conjoint_' . basename($files['passeport_conjoint']['name']);
                $conjoint_passport_path = $upload_dir . $conjoint_filename;
                if (!move_uploaded_file($files['passeport_conjoint']['tmp_name'], $conjoint_passport_path)) {
                    $success = false;
                }
            }
            
            $sql = "INSERT INTO membres_famille (demande_id, nom, prenom, date_naissance, relation, type_document, chemin_document) VALUES (?, ?, ?, ?, 'conjoint', 'passeport', ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $demande_id,
                test_input($post_data['nom_conjoint']),
                test_input($post_data['prenom_conjoint']),
                test_input($post_data['date_naissance_conjoint']),
                $conjoint_passport_path
            ]);
            
            if (!empty($conjoint_passport_path)) {
                saveDocumentToDB($demande_id, $files['passeport_conjoint']['name'], $conjoint_passport_path, 'passeport_conjoint');
            }
        }
        
        // Sauvegarde des enfants
        $nombre_enfants = intval($post_data['nombre_enfants'] ?? 0);
        for ($i = 1; $i <= $nombre_enfants; $i++) {
            if (!empty($post_data["nom_enfant_$i"])) {
                $enfant_passport_path = '';
                if (!empty($files["passeport_enfant_$i"]['name'])) {
                    $enfant_filename = uniqid() . "_enfant_{$i}_" . basename($files["passeport_enfant_$i"]['name']);
                    $enfant_passport_path = $upload_dir . $enfant_filename;
                    if (!move_uploaded_file($files["passeport_enfant_$i"]['tmp_name'], $enfant_passport_path)) {
                        $success = false;
                    }
                }
                
                $sql = "INSERT INTO membres_famille (demande_id, nom, prenom, date_naissance, relation, type_document, chemin_document) VALUES (?, ?, ?, ?, 'enfant', 'passeport', ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $demande_id,
                    test_input($post_data["nom_enfant_$i"]),
                    test_input($post_data["prenom_enfant_$i"]),
                    test_input($post_data["date_naissance_enfant_$i"]),
                    $enfant_passport_path
                ]);
                
                if (!empty($enfant_passport_path)) {
                    saveDocumentToDB($demande_id, $files["passeport_enfant_$i"]['name'], $enfant_passport_path, "passeport_enfant_$i");
                }
            }
        }
        
        return $success;
        
    } catch(PDOException $e) {
        error_log("Erreur sauvegarde membres famille: " . $e->getMessage());
        return false;
    }
}

function saveDocumentToDB($demande_id, $nom_fichier, $chemin, $type_document) {
    try {
        $pdo = connectDB();
        if (!$pdo) return false;
        
        $sql = "INSERT INTO documents_demande (demande_id, nom_fichier, chemin, type_document, date_upload) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$demande_id, $nom_fichier, $chemin, $type_document]);
        return true;
    } catch(PDOException $e) {
        error_log("Erreur sauvegarde document: " . $e->getMessage());
        return false;
    }
}

function sendConfirmationEmail($email, $prenom, $reference, $demande_id) {
    $subject = "Confirmation de votre demande de Green Card - Référence: $reference";
    $message = "
    <html>
    <head>
        <title>Confirmation de demande Green Card</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #3C3B6E, #B22234); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { padding: 30px; background: #f9f9f9; border-radius: 0 0 10px 10px; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; margin-top: 20px; }
            .info-box { background: white; padding: 20px; border-radius: 8px; border-left: 4px solid #3C3B6E; margin: 20px 0; }
            .reference { font-size: 1.2em; font-weight: bold; color: #3C3B6E; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌎 Demande de Green Card</h1>
                <p>Confirmation de réception</p>
            </div>
            <div class='content'>
                <h2>Bonjour $prenom,</h2>
                <p>Nous accusons réception de votre demande de Green Card auprès de Babylone Service.</p>
                
                <div class='info-box'>
                    <h3>📋 Détails de votre demande</h3>
                    <p><strong>Référence:</strong> <span class='reference'>$reference</span></p>
                    <p><strong>Numéro de dossier:</strong> #$demande_id</p>
                    <p><strong>Date de soumission:</strong> " . date('d/m/Y à H:i') . "</p>
                </div>
                
                <h3>🔄 Prochaines étapes</h3>
                <ul>
                    <li>Vérification de votre dossier sous 48h</li>
                    <li>Analyse complète de votre éligibilité</li>
                    <li>Contact pour les documents complémentaires si nécessaire</li>
                    <li>Suivi personnalisé de votre dossier</li>
                </ul>
                
                <p><strong>📞 Contact:</strong> Si vous avez des questions, contactez-nous à <a href='mailto:usa@babylone-service.com'>usa@babylone-service.com</a></p>
                
                <br>
                <p>Cordialement,<br><strong>Service Immigration USA</strong><br>Babylone Service</p>
            </div>
            <div class='footer'>
                <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                <p>© 2024 Babylone Service - Tous droits réservés</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@babylone-service.com" . "\r\n";
    $headers .= "Reply-To: usa@babylone-service.com" . "\r\n";
    
    return @mail($email, $subject, $message, $headers);
}

// Récupération des données du formulaire en cas d'erreur
$form_data = $_POST ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Green Card - Formulaire Officiel</title>
    <style>
        :root {
            --usa-blue: #3C3B6E;
            --usa-red: #B22234;
            --usa-white: #FFFFFF;
            --usa-gold: #FFD700;
            --dark: #2C3E50;
            --light: #ECF0F1;
            --grey: #95A5A6;
            --success: #27ae60;
            --error: #e74c3c;
            --warning: #f39c12;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #d8f2f8ff 0%, #e8e5ebff 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--usa-blue) 0%, var(--usa-red) 100%);
            color: white;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: "★";
            position: absolute;
            top: 10px;
            left: 10px;
            font-size: 24px;
            color: var(--usa-gold);
        }
        
        .header::after {
            content: "★";
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            color: var(--usa-gold);
        }
        
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 40px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: var(--success);
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: var(--error);
            border: 1px solid #f5c6cb;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: var(--warning);
            border: 1px solid #ffeaa7;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding: 25px;
            border: 2px solid var(--light);
            border-radius: 10px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .form-section.active {
            border-color: var(--usa-blue);
            box-shadow: 0 5px 15px rgba(60, 59, 110, 0.1);
        }
        
        .section-title {
            font-size: 1.3rem;
            color: var(--usa-blue);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--usa-red);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--usa-blue);
            box-shadow: 0 0 0 3px rgba(60, 59, 110, 0.1);
        }
        
        .form-control.error {
            border-color: var(--error);
        }
        
        .required::after {
            content: " *";
            color: var(--usa-red);
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--usa-blue), var(--usa-red));
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .btn-secondary {
            background: var(--light);
            color: var(--dark);
            border: 2px solid var(--grey);
        }
        
        .btn-secondary:hover {
            border-color: var(--usa-blue);
            color: var(--usa-blue);
        }
        
        .file-input {
            padding: 10px;
            border: 2px dashed #ddd;
            border-radius: 8px;
            text-align: center;
            background: #fafafa;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .file-input:hover {
            border-color: var(--usa-blue);
            background: #f0f8ff;
        }
        
        .file-info {
            font-size: 0.85rem;
            color: var(--grey);
            margin-top: 5px;
        }
        
        .family-member-section {
            background: linear-gradient(135deg, #f0f8ff 0%, #f5f0ff 100%);
            padding: 20px;
            margin: 15px 0;
            border-radius: 8px;
            border-left: 4px solid var(--usa-blue);
        }
        
        .navigation-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .navigation-buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .navigation-buttons .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Demande de Green Card</h1>
            <p>Formulaire officiel de demande de résidence permanente aux États-Unis</p>
        </div>
        
        <div class="form-container">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <strong>❌ Des erreurs ont été détectées:</strong>
                    <ul style="margin-top: 10px; margin-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.5rem;">✅</span>
                        <div>
                            <strong>Félicitations!</strong> Votre demande a été soumise avec succès.
                        </div>
                    </div>
                    <div style="margin-top: 15px; padding: 15px; background: white; border-radius: 5px;">
                        <p><strong>Référence:</strong> <?php echo htmlspecialchars($reference); ?></p>
                        <p><strong>Numéro de dossier:</strong> #<?php echo $demande_id; ?></p>
                        <p>Un email de confirmation vous a été envoyé à <?php echo htmlspecialchars($_POST['email'] ?? ''); ?></p>
                    </div>
                    <div style="margin-top: 15px;">
                        <a href="green_card_form.php" class="btn btn-primary">Nouvelle demande</a>
                    </div>
                </div>
            <?php else: ?>
            <form method="POST" enctype="multipart/form-data" action="" id="greenCardForm">
                <!-- Informations Personnelles -->
                <div class="form-section active" id="section-personnel">
                    <h3 class="section-title">👤 Informations Personnelles</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom" class="required">Nom</label>
                            <input type="text" id="nom" name="nom" class="form-control" required 
                                   value="<?php echo htmlspecialchars($form_data['nom'] ?? ''); ?>"
                                   placeholder="Votre nom de famille">
                        </div>
                        <div class="form-group">
                            <label for="prenom" class="required">Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="form-control" required
                                   value="<?php echo htmlspecialchars($form_data['prenom'] ?? ''); ?>"
                                   placeholder="Votre prénom">
                        </div>
                        <div class="form-group">
                            <label for="date_naissance" class="required">Date de Naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance" class="form-control" required
                                   value="<?php echo htmlspecialchars($form_data['date_naissance'] ?? ''); ?>"
                                   max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="nationalite" class="required">Nationalité</label>
                            <input type="text" id="nationalite" name="nationalite" class="form-control" required
                                   value="<?php echo htmlspecialchars($form_data['nationalite'] ?? ''); ?>"
                                   placeholder="Ex: Française, Marocaine...">
                        </div>
                        <div class="form-group">
                            <label for="pays_residence" class="required">Pays de résidence</label>
                            <input type="text" id="pays_residence" name="pays_residence" class="form-control" required
                                   value="<?php echo htmlspecialchars($form_data['pays_residence'] ?? ''); ?>"
                                   placeholder="Pays où vous résidez actuellement">
                        </div>
                    
                        <div class="form-group">
                            <label for="situation_familiale" class="required">Situation familiale</label>
                            <select id="situation_familiale" name="situation_familiale" class="form-control" required onchange="toggleFamilySection()">
                                <option value="">-- Sélectionnez votre situation --</option>
                                <option value="celibataire" <?php echo (($form_data['situation_familiale'] ?? '') == 'celibataire') ? 'selected' : ''; ?>>Célibataire</option>
                                <option value="marie" <?php echo (($form_data['situation_familiale'] ?? '') == 'marie') ? 'selected' : ''; ?>>Marié(e)</option>
                                <option value="divorce" <?php echo (($form_data['situation_familiale'] ?? '') == 'divorce') ? 'selected' : ''; ?>>Divorcé(e)</option>
                                <option value="veuf" <?php echo (($form_data['situation_familiale'] ?? '') == 'veuf') ? 'selected' : ''; ?>>Veuf/Veuve</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section Famille (apparaît seulement si marié) -->
                <div id="family_section" class="form-section" style="display: none;">
                    <h3 class="section-title">👨‍👩‍👧‍👦 Informations Familiales</h3>
                    
                    <div class="alert alert-warning">
                        <strong>📝 Important:</strong> Tous les membres de la famille inclus dans la demande doivent fournir leurs documents.
                    </div>
                    
                    <div class="form-group">
                        <label for="nombre_enfants" class="required">Nombre d'enfants à inclure dans la demande</label>
                        <input type="number" id="nombre_enfants" name="nombre_enfants" class="form-control" min="0" max="10" 
                               value="<?php echo htmlspecialchars($form_data['nombre_enfants'] ?? '0'); ?>" onchange="loadFamilyMembers()"
                               placeholder="0 si aucun enfant">
                        <div class="file-info">Note: Seuls les enfants de moins de 21 ans peuvent être inclus</div>
                    </div>

                    <!-- Section Conjoint -->
                    <div class="family-member-section">
                        <h4>💍 Informations du Conjoint</h4>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="nom_conjoint" class="required">Nom du conjoint</label>
                                <input type="text" id="nom_conjoint" name="nom_conjoint" class="form-control"
                                       value="<?php echo htmlspecialchars($form_data['nom_conjoint'] ?? ''); ?>"
                                       placeholder="Nom de famille du conjoint">
                            </div>
                            <div class="form-group">
                                <label for="prenom_conjoint" class="required">Prénom du conjoint</label>
                                <input type="text" id="prenom_conjoint" name="prenom_conjoint" class="form-control"
                                       value="<?php echo htmlspecialchars($form_data['prenom_conjoint'] ?? ''); ?>"
                                       placeholder="Prénom du conjoint">
                            </div>
                            <div class="form-group">
                                <label for="date_naissance_conjoint" class="required">Date de naissance</label>
                                <input type="date" id="date_naissance_conjoint" name="date_naissance_conjoint" class="form-control"
                                       value="<?php echo htmlspecialchars($form_data['date_naissance_conjoint'] ?? ''); ?>"
                                       max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="passeport_conjoint" class="required">Passeport du conjoint</label>
                                <input type="file" id="passeport_conjoint" name="passeport_conjoint" class="form-control" 
                                       accept=".pdf,.jpg,.jpeg,.png">
                                <div class="file-info">Format: PDF, JPG, PNG • Max: 5MB • Passeport valide</div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Enfants -->
                    <div id="children_section">
                        <!-- Les champs enfants seront ajoutés dynamiquement ici -->
                    </div>
                </div>
                
                <!-- Coordonnées -->
                <div class="form-section">
                    <h3 class="section-title">📞 Coordonnées</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required
                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>"
                                   placeholder="votre.email@example.com">
                        </div>
                        <div class="form-group">
                            <label for="telephone" class="required">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" class="form-control" required
                                   value="<?php echo htmlspecialchars($form_data['telephone'] ?? ''); ?>"
                                   placeholder="+33 1 23 45 67 89">
                        </div>
                        <div class="form-group" style="grid-column: span 2;">
                            <label for="adresse" class="required">Adresse complète</label>
                            <textarea id="adresse" name="adresse" class="form-control" rows="3" required
                                      placeholder="Numéro, rue, code postal, ville, pays"><?php echo htmlspecialchars($form_data['adresse'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="ville">Ville</label>
                            <input type="text" id="ville" name="ville" class="form-control"
                                   value="<?php echo htmlspecialchars($form_data['ville'] ?? ''); ?>"
                                   placeholder="Votre ville">
                        </div>
                        <div class="form-group">
                            <label for="code_postal">Code postal</label>
                            <input type="text" id="code_postal" name="code_postal" class="form-control"
                                   value="<?php echo htmlspecialchars($form_data['code_postal'] ?? ''); ?>"
                                   placeholder="Code postal">
                        </div>
                    </div>
                </div>

                <!-- Section Éducation -->
                <div class="form-section">
                    <h3 class="section-title">🎓 Situation Éducative</h3>
                    
                    <div class="form-group">
                        <label for="has_bac">Possédez-vous un baccalauréat ?</label>
                        <select id="has_bac" name="has_bac" class="form-control" onchange="toggleBacSection()">
                            <option value="">-- Sélectionnez --</option>
                            <option value="oui" <?php echo (($form_data['has_bac'] ?? '') == 'oui') ? 'selected' : ''; ?>>Oui</option>
                            <option value="non" <?php echo (($form_data['has_bac'] ?? '') == 'non') ? 'selected' : ''; ?>>Non</option>
                        </select>
                    </div>
                    
                    <div id="bac_section" style="display: none;">
                        <div class="form-group">
                            <label for="bac_diploma">Diplôme du Baccalauréat</label>
                            <input type="file" id="bac_diploma" name="bac_diploma" class="form-control" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <div class="file-info">Format: PDF, JPG, PNG • Max: 5MB • Copie du diplôme</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="has_diploma">Possédez-vous d'autres diplômes d'étude ?</label>
                        <select id="has_diploma" name="has_diploma" class="form-control" onchange="toggleDiplomaSection()">
                            <option value="">-- Sélectionnez --</option>
                            <option value="oui" <?php echo (($form_data['has_diploma'] ?? '') == 'oui') ? 'selected' : ''; ?>>Oui (Licence, Master, Doctorat...)</option>
                            <option value="non" <?php echo (($form_data['has_diploma'] ?? '') == 'non') ? 'selected' : ''; ?>>Non</option>
                        </select>
                    </div>
                    
                    <div id="diploma_section" style="display: none;">
                        <div class="form-group">
                            <label for="study_diploma">Diplôme d'Étude Supérieure</label>
                            <input type="file" id="study_diploma" name="study_diploma" class="form-control" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <div class="file-info">Format: PDF, JPG, PNG • Max: 5MB • Copie du diplôme</div>
                        </div>
                    </div>
                </div>

                <!-- Section Photos -->
                <div class="form-section">
                    <h3 class="section-title">📷 Photos Requises</h3>
                    <div class="alert alert-warning">
                        <strong>ℹ️ Information:</strong> Photo d'identité récente format 5x5 cm, fond blanc, prise il y a moins de 6 mois.
                    </div>
                    
                    <div class="form-grid">
                        <?php for ($i = 1; $i <= 1; $i++): ?>
                        <div class="form-group">
                            <label for="photo_<?php echo $i; ?>" class="required">Photo d'identité <?php echo $i; ?></label>
                            <input type="file" id="photo_<?php echo $i; ?>" name="photos[]" class="form-control" 
                                   accept=".jpg,.jpeg,.png" required>
                            <div class="file-info">Format: JPG, JPEG, PNG • Max: 5MB • Photo récente 5x5 cm</div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>     
                
                <!-- Situation Professionnelle -->
                <div class="form-section">
                    <h3 class="section-title">💼 Situation Professionnelle</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="profession">Profession</label>
                            <input type="text" id="profession" name="profession" class="form-control"
                                   value="<?php echo htmlspecialchars($form_data['profession'] ?? ''); ?>"
                                   placeholder="Votre métier actuel">
                        </div>
                        <div class="form-group">
                            <label for="employeur">Employeur actuel</label>
                            <input type="text" id="employeur" name="employeur" class="form-control"
                                   value="<?php echo htmlspecialchars($form_data['employeur'] ?? ''); ?>"
                                   placeholder="Nom de votre entreprise">
                        </div>
                        <div class="form-group">
                            <label for="revenu_annuel">Revenu annuel (USD)</label>
                            <input type="number" id="revenu_annuel" name="revenu_annuel" class="form-control"
                                   value="<?php echo htmlspecialchars($form_data['revenu_annuel'] ?? ''); ?>"
                                   placeholder="Revenu annuel en dollars US">
                            <div class="file-info">Facultatif mais recommandé pour l'évaluation</div>
                        </div>
                    </div>
                </div>
                
                <!-- Validation finale -->
                <div class="form-section">
                    <h3 class="section-title">✅ Validation Finale</h3>
                    <div class="alert alert-warning">
                        <strong>⚠️ Attention:</strong> Vérifiez attentivement toutes les informations avant de soumettre votre demande.
                    </div>
                    
                    <div class="form-group">
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <input type="checkbox" id="confirmation" name="confirmation" required style="margin-top: 3px;">
                            <label for="confirmation" style="margin-bottom: 0;">
                                <strong>Je certifie sur l'honneur que les informations fournies dans ce formulaire sont exactes et complètes.</strong><br>
                                J'accepte les conditions d'utilisation et la politique de confidentialité de Babylone Service.
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Bouton de soumission -->
                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        📤 Soumettre la Demande de Green Card
                    </button>
                    <div class="file-info" style="margin-top: 10px;">
                        ⏱️ Le traitement peut prendre plusieurs minutes. Ne quittez pas cette page.
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        // Fonctions pour gérer l'affichage des sections conditionnelles
        function toggleFamilySection() {
            const situation = document.getElementById('situation_familiale').value;
            const familySection = document.getElementById('family_section');
            
            if (situation === 'marie') {
                familySection.style.display = 'block';
                loadFamilyMembers();
            } else {
                familySection.style.display = 'none';
            }
        }

        function loadFamilyMembers() {
            const nombreEnfants = parseInt(document.getElementById('nombre_enfants').value) || 0;
            const childrenSection = document.getElementById('children_section');
            
            childrenSection.innerHTML = '';
            
            for (let i = 1; i <= nombreEnfants; i++) {
                const childSection = document.createElement('div');
                childSection.className = 'family-member-section';
                childSection.innerHTML = `
                    <h4>👶 Enfant ${i}</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom_enfant_${i}" class="required">Nom</label>
                            <input type="text" id="nom_enfant_${i}" name="nom_enfant_${i}" class="form-control" 
                                   placeholder="Nom de famille">
                        </div>
                        <div class="form-group">
                            <label for="prenom_enfant_${i}" class="required">Prénom</label>
                            <input type="text" id="prenom_enfant_${i}" name="prenom_enfant_${i}" class="form-control"
                                   placeholder="Prénom">
                        </div>
                        <div class="form-group">
                            <label for="date_naissance_enfant_${i}" class="required">Date de naissance</label>
                            <input type="date" id="date_naissance_enfant_${i}" name="date_naissance_enfant_${i}" class="form-control"
                                   max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="passeport_enfant_${i}" class="required">Passeport</label>
                            <input type="file" id="passeport_enfant_${i}" name="passeport_enfant_${i}" class="form-control" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <div class="file-info">Format: PDF, JPG, PNG • Max: 5MB • Passeport valide</div>
                        </div>
                    </div>
                `;
                childrenSection.appendChild(childSection);
            }
        }

        function toggleBacSection() {
            const hasBac = document.getElementById('has_bac').value;
            document.getElementById('bac_section').style.display = (hasBac === 'oui') ? 'block' : 'none';
        }

        function toggleDiplomaSection() {
            const hasDiploma = document.getElementById('has_diploma').value;
            document.getElementById('diploma_section').style.display = (hasDiploma === 'oui') ? 'block' : 'none';
        }

        // Validation côté client améliorée
        document.getElementById('greenCardForm')?.addEventListener('submit', function(e) {
            let valid = true;
            const required = document.querySelectorAll('[required]');
            
            // Réinitialiser les erreurs
            document.querySelectorAll('.form-control').forEach(field => {
                field.classList.remove('error');
            });
            
            // Validation des champs requis
            required.forEach(field => {
                if (!field.value.trim()) {
                    valid = false;
                    field.classList.add('error');
                    field.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
            
            // Validation spécifique pour la situation familiale
            const situationFamiliale = document.getElementById('situation_familiale').value;
            if (situationFamiliale === 'marie') {
                const nomConjoint = document.getElementById('nom_conjoint');
                const prenomConjoint = document.getElementById('prenom_conjoint');
                const dateNaissanceConjoint = document.getElementById('date_naissance_conjoint');
                const passeportConjoint = document.getElementById('passeport_conjoint');
                
                if (!nomConjoint.value.trim() || !prenomConjoint.value.trim() || !dateNaissanceConjoint.value || !passeportConjoint.files[0]) {
                    valid = false;
                    if (!nomConjoint.value.trim()) nomConjoint.classList.add('error');
                    if (!prenomConjoint.value.trim()) prenomConjoint.classList.add('error');
                    if (!dateNaissanceConjoint.value) dateNaissanceConjoint.classList.add('error');
                    if (!passeportConjoint.files[0]) passeportConjoint.classList.add('error');
                    
                    document.getElementById('family_section').scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                
                // Validation des enfants
                const nombreEnfants = parseInt(document.getElementById('nombre_enfants').value) || 0;
                for (let i = 1; i <= nombreEnfants; i++) {
                    const nomEnfant = document.getElementById(`nom_enfant_${i}`);
                    const prenomEnfant = document.getElementById(`prenom_enfant_${i}`);
                    const dateNaissanceEnfant = document.getElementById(`date_naissance_enfant_${i}`);
                    const passeportEnfant = document.getElementById(`passeport_enfant_${i}`);
                    
                    if (nomEnfant && (!nomEnfant.value.trim() || !prenomEnfant.value.trim() || !dateNaissanceEnfant.value || !passeportEnfant.files[0])) {
                        valid = false;
                        if (!nomEnfant.value.trim()) nomEnfant.classList.add('error');
                        if (!prenomEnfant.value.trim()) prenomEnfant.classList.add('error');
                        if (!dateNaissanceEnfant.value) dateNaissanceEnfant.classList.add('error');
                        if (!passeportEnfant.files[0]) passeportEnfant.classList.add('error');
                    }
                }
            }
            
            // Validation des photos
            const photos = document.querySelectorAll('input[name="photos[]"]');
            let hasPhotos = false;
            photos.forEach(photo => {
                if (photo.files[0]) {
                    hasPhotos = true;
                }
            });
            
            if (!hasPhotos) {
                valid = false;
                photos[0].classList.add('error');
                photos[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            if (!valid) {
                e.preventDefault();
                alert('❌ Veuillez corriger les erreurs dans le formulaire avant de soumettre. Les champs en rouge sont obligatoires.');
            } else {
                // Afficher un indicateur de chargement
                const submitBtn = document.getElementById('submit-btn');
                submitBtn.innerHTML = '⏳ Traitement en cours...';
                submitBtn.disabled = true;
            }
        });

        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            toggleFamilySection();
            toggleBacSection();
            toggleDiplomaSection();
            
            // Restaurer les valeurs des champs enfants si existantes
            const nombreEnfants = parseInt(document.getElementById('nombre_enfants').value) || 0;
            if (nombreEnfants > 0) {
                loadFamilyMembers();
            }
        });

        // Empêcher les dates futures
        const today = new Date().toISOString().split('T')[0];
        document.querySelectorAll('input[type="date"]').forEach(dateInput => {
            dateInput.max = today;
        });
    </script>
</body>
</html>