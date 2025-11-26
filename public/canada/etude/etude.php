<?php
session_start();

// Configuration de sécurité
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Traitement du formulaire s'il est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Erreur de sécurité. Veuillez réessayer.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
    
    // Régénérer le token CSRF
    unset($_SESSION['csrf_token']);
    
    // Traitement des données du formulaire
    $result = processForm();
    if ($result['success']) {
        $_SESSION['success_message'] = $result['message'];
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Générer un token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Afficher les messages d'erreur s'ils existent
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" style="margin: 20px;">
            <i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($_SESSION['error_message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" style="margin: 20px;">
            <i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success_message']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['success_message']);
}

// Fonction pour traiter le formulaire
function processForm() {
    // Configuration
    $upload_dir = 'uploads/canada/';
    $max_file_size = 5 * 1024 * 1024; // 5MB
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    
    // Créer le répertoire d'upload s'il n'existe pas
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    try {
        // Valider les données du formulaire
        $validation_errors = validateFormData($_POST);
        
        if (!empty($validation_errors)) {
            throw new Exception(implode(' ', $validation_errors));
        }
        
        // Traitement des fichiers
        $file_errors = [];
        $uploaded_data = [];
        
        // Fichiers obligatoires
        $required_files = ['passeport', 'acte_naissance'];
        foreach ($required_files as $file_field) {
            if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] !== UPLOAD_ERR_NO_FILE) {
                $result = handleFileUpload($_FILES[$file_field], $upload_dir, $max_file_size, $allowed_types);
                $file_errors = array_merge($file_errors, $result['errors']);
                $uploaded_data[$file_field] = $result['files'];
            } else {
                $file_errors[] = "Le fichier $file_field est obligatoire.";
            }
        }
        
        // Fichiers optionnels
        $optional_files = [
            'test_langue' => isset($_POST['test_langue_option']) && $_POST['test_langue_option'] === 'oui',
            'cv' => isset($_POST['cv_option']) && $_POST['cv_option'] === 'oui',
            'attestation_province' => isset($_POST['attestation_province_option']) && $_POST['attestation_province_option'] === 'oui'
        ];
        
        foreach ($optional_files as $file_field => $should_upload) {
            if ($should_upload && isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] === UPLOAD_ERR_OK) {
                $result = handleFileUpload($_FILES[$file_field], $upload_dir, $max_file_size, $allowed_types);
                $file_errors = array_merge($file_errors, $result['errors']);
                $uploaded_data[$file_field] = $result['files'];
            }
        }
        
        // Documents académiques selon le niveau
        $niveau = $_POST['niveau_etude'];
        $academic_files = getAcademicFilesByLevel($niveau);
        
        foreach ($academic_files as $academic_file) {
            if (isset($_FILES[$academic_file]) && $_FILES[$academic_file]['error'] === UPLOAD_ERR_OK) {
                $result = handleFileUpload($_FILES[$academic_file], $upload_dir, $max_file_size, $allowed_types);
                $file_errors = array_merge($file_errors, $result['errors']);
                $uploaded_data[$academic_file] = $result['files'];
            } else {
                $file_errors[] = "Le document académique $academic_file est obligatoire.";
            }
        }
        
        // Certificat de scolarité optionnel
        if (isset($_FILES['certificat_scolarite']) && $_FILES['certificat_scolarite']['error'] === UPLOAD_ERR_OK) {
            $result = handleFileUpload($_FILES['certificat_scolarite'], $upload_dir, $max_file_size, $allowed_types);
            $file_errors = array_merge($file_errors, $result['errors']);
            $uploaded_data['certificat_scolarite'] = $result['files'];
        }
        
        // Documents supplémentaires
        $supplementary_files = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'type_document_supp_') === 0) {
                $counter = substr($key, 18);
                $file_key = "fichier_document_supp_$counter";
                
                if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
                    $result = handleFileUpload($_FILES[$file_key], $upload_dir, $max_file_size, $allowed_types);
                    $file_errors = array_merge($file_errors, $result['errors']);
                    
                    $supplementary_files[] = [
                        'type' => $_POST[$key],
                        'description' => $_POST["description_document_supp_$counter"] ?? '',
                        'filename' => $result['files'][0] ?? ''
                    ];
                }
            }
        }
        
        $uploaded_data['documents_supplementaires'] = $supplementary_files;
        
        // Si erreurs de fichiers, annuler
        if (!empty($file_errors)) {
            // Supprimer les fichiers déjà uploadés
            foreach ($uploaded_data as $files) {
                if (is_array($files)) {
                    foreach ($files as $file) {
                        if (file_exists($upload_dir . $file)) {
                            unlink($upload_dir . $file);
                        }
                    }
                }
            }
            throw new Exception(implode(' ', $file_errors));
        }
        
        // Sauvegarde des données dans MySQL
        $demande_data = [
            'nom_complet' => htmlspecialchars($_POST['nom']),
            'date_naissance' => $_POST['naissance'],
            'nationalite' => htmlspecialchars($_POST['nationalite']),
            'email' => htmlspecialchars($_POST['email']),
            'province' => $_POST['province'],
            'niveau_etude' => $_POST['niveau_etude'],
            'fichiers' => $uploaded_data
        ];
        
        // Sauvegarde dans la base de données MySQL
        saveDemandeToMySQL($demande_data);
        
        return [
            'success' => true,
            'message' => "Votre demande a été soumise avec succès! Nous vous contacterons bientôt."
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Erreur: " . $e->getMessage()
        ];
    }
}

function saveDemandeToMySQL($demande_data) {
    $host = 'localhost';
    $dbname = 'babylone_service';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $fichiers = $demande_data['fichiers'];
        $documents_supp_json = !empty($fichiers['documents_supplementaires']) ? 
            json_encode($fichiers['documents_supplementaires']) : null;
        
        $sql = "INSERT INTO demandes_etudes_canada (
            nom_complet, date_naissance, nationalite, email, province, niveau_etude,
            passeport, acte_naissance, test_langue, cv,
            releve_notes, diplome_fin_etudes, releve_bac, diplome_bac,
            releves_universitaires, releve_maitrise, diplome_maitrise,
            projet_recherche, cv_academique, certificat_scolarite,
            attestation_province, documents_supplementaires
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $demande_data['nom_complet'],
            $demande_data['date_naissance'],
            $demande_data['nationalite'],
            $demande_data['email'],
            $demande_data['province'],
            $demande_data['niveau_etude'],
            // Fichiers
            $fichiers['passeport'][0] ?? null,
            $fichiers['acte_naissance'][0] ?? null,
            $fichiers['test_langue'][0] ?? null,
            $fichiers['cv'][0] ?? null,
            $fichiers['releve_notes'][0] ?? null,
            $fichiers['diplome_fin_etudes'][0] ?? null,
            $fichiers['releve_bac'][0] ?? null,
            $fichiers['diplome_bac'][0] ?? null,
            $fichiers['releves_universitaires'][0] ?? null,
            $fichiers['releve_maitrise'][0] ?? null,
            $fichiers['diplome_maitrise'][0] ?? null,
            $fichiers['projet_recherche'][0] ?? null,
            $fichiers['cv_academique'][0] ?? null,
            $fichiers['certificat_scolarite'][0] ?? null,
            $fichiers['attestation_province'][0] ?? null,
            $documents_supp_json
        ]);
        
        return true;
        
    } catch (PDOException $e) {
        throw new Exception("Erreur base de données: " . $e->getMessage());
    }
}

function validateFormData($data) {
    $errors = [];
    
    // Validation des champs requis
    $required_fields = ['nom', 'naissance', 'nationalite', 'email', 'province', 'niveau_etude'];
    foreach ($required_fields as $field) {
        if (empty(trim($data[$field]))) {
            $errors[] = "Le champ $field est obligatoire.";
        }
    }
    
    // Validation email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format d'email invalide.";
    }
    
    // Validation date de naissance (au moins 16 ans)
    $min_age = 16;
    $birthdate = new DateTime($data['naissance']);
    $today = new DateTime();
    $age = $today->diff($birthdate)->y;
    
    if ($age < $min_age) {
        $errors[] = "Vous devez avoir au moins $min_age ans.";
    }
    
    // Validation de la longueur des champs
    if (strlen($data['nom']) > 100) {
        $errors[] = "Le nom ne peut pas dépasser 100 caractères.";
    }
    
    if (strlen($data['nationalite']) > 50) {
        $errors[] = "La nationalité ne peut pas dépasser 50 caractères.";
    }
    
    if (strlen($data['email']) > 100) {
        $errors[] = "L'email ne peut pas dépasser 100 caractères.";
    }
    
    return $errors;
}

function getAcademicFilesByLevel($niveau) {
    $files = [];
    
    switch ($niveau) {
        case 'bac':
        case 'dec':
        case 'dep':
        case 'aec':
        case 'technique':
            $files = ['releve_notes', 'diplome_fin_etudes'];
            break;
        case 'maitrise':
            $files = ['releve_bac', 'diplome_bac', 'releves_universitaires'];
            break;
        case 'phd':
            $files = ['releve_maitrise', 'diplome_maitrise', 'projet_recherche', 'cv_academique'];
            break;
        case 'langue':
            $files = ['releves_notes'];
            break;
    }
    
    return $files;
}

function handleFileUpload($file, $upload_dir, $max_size, $allowed_types) {
    $errors = [];
    $uploaded_files = [];
    
    if (is_array($file['name'])) {
        // Multiple files
        for ($i = 0; $i < count($file['name']); $i++) {
            if ($file['error'][$i] === UPLOAD_ERR_OK) {
                $result = processSingleFile([
                    'name' => $file['name'][$i],
                    'type' => $file['type'][$i],
                    'tmp_name' => $file['tmp_name'][$i],
                    'error' => $file['error'][$i],
                    'size' => $file['size'][$i]
                ], $upload_dir, $max_size, $allowed_types);
                
                if (isset($result['error'])) {
                    $errors[] = $result['error'];
                } else {
                    $uploaded_files[] = $result['filename'];
                }
            }
        }
    } else {
        // Single file
        if ($file['error'] === UPLOAD_ERR_OK) {
            $result = processSingleFile($file, $upload_dir, $max_size, $allowed_types);
            
            if (isset($result['error'])) {
                $errors[] = $result['error'];
            } else {
                $uploaded_files[] = $result['filename'];
            }
        }
    }
    
    return ['files' => $uploaded_files, 'errors' => $errors];
}

function processSingleFile($file, $upload_dir, $max_size, $allowed_types) {
    // Vérifier la taille
    if ($file['size'] > $max_size) {
        return ['error' => "Le fichier {$file['name']} est trop volumineux. Maximum 5MB autorisé."];
    }
    
    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['error' => "Type de fichier non autorisé pour {$file['name']}."];
    }
    
    // Générer un nom de fichier sécurisé
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe_filename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $filepath = $upload_dir . $safe_filename;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['filename' => $safe_filename];
    } else {
        return ['error' => "Erreur lors de l'upload du fichier {$file['name']}."];
    }
}

function saveDemande($demande_data) {
    // Configuration de la base de données
    $host = 'localhost';
    $dbname = 'babylone_service';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Préparer les données pour l'insertion
        $fichiers = $demande_data['fichiers'];
        $documents_supp_json = !empty($fichiers['documents_supplementaires']) ? 
            json_encode($fichiers['documents_supplementaires']) : null;
        
        // Requête d'insertion
        $sql = "INSERT INTO demandes_etudes_canada (
            nom_complet, date_naissance, nationalite, email, province, niveau_etude,
            passeport, acte_naissance, test_langue, cv,
            releve_notes, diplome_fin_etudes, releve_bac, diplome_bac,
            releves_universitaires, releve_maitrise, diplome_maitrise,
            projet_recherche, cv_academique, certificat_scolarite,
            attestation_province, documents_supplementaires, statut, date_soumission
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'nouveau', NOW())";
        
        $stmt = $pdo->prepare($sql);
        
        // Exécuter avec les valeurs
        $stmt->execute([
            $demande_data['nom_complet'],
            $demande_data['date_naissance'],
            $demande_data['nationalite'],
            $demande_data['email'],
            $demande_data['province'],
            $demande_data['niveau_etude'],
            // Fichiers obligatoires
            $fichiers['passeport'][0] ?? null,
            $fichiers['acte_naissance'][0] ?? null,
            // Fichiers optionnels
            $fichiers['test_langue'][0] ?? null,
            $fichiers['cv'][0] ?? null,
            // Documents académiques
            $fichiers['releve_notes'][0] ?? null,
            $fichiers['diplome_fin_etudes'][0] ?? null,
            $fichiers['releve_bac'][0] ?? null,
            $fichiers['diplome_bac'][0] ?? null,
            $fichiers['releves_universitaires'][0] ?? null,
            $fichiers['releve_maitrise'][0] ?? null,
            $fichiers['diplome_maitrise'][0] ?? null,
            $fichiers['projet_recherche'][0] ?? null,
            $fichiers['cv_academique'][0] ?? null,
            $fichiers['certificat_scolarite'][0] ?? null,
            // Attestation de province
            $fichiers['attestation_province'][0] ?? null,
            // Documents supplémentaires
            $documents_supp_json
        ]);
        
        return true;
        
    } catch (PDOException $e) {
        // En cas d'erreur, sauvegarder dans le fichier JSON comme fallback
        $demandes_file = 'demandes/demandes_canada.json';
        if (!is_dir('demandes')) {
            mkdir('demandes', 0755, true);
        }
        
        $demandes = [];
        if (file_exists($demandes_file)) {
            $demandes = json_decode(file_get_contents($demandes_file), true) ?? [];
        }
        
        $demandes[] = $demande_data;
        file_put_contents($demandes_file, json_encode($demandes, JSON_PRETTY_PRINT));
        
        return true;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Études - Canada</title>
    <!-- Ajouter Bootstrap pour les alertes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Canada - Couleurs rouge et blanc */
        :root {
            --canada-red: #FF0000;
            --canada-white: #FFFFFF;
            --canada-dark: #1a1a1a;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --light-bg: #f8fafc;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: var(--dark-text);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .card {
            background: var(--canada-white);
            border-radius: 16px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
            transition: var(--transition);
            border: 2px solid var(--canada-red);
        }
        
        .card:hover {
            box-shadow: 0 15px 40px rgba(255, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--canada-red) 0%, #cc0000 100%);
            color: var(--canada-white);
            padding: 35px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.05"><text x="50%" y="50%" font-size="20" text-anchor="middle" dominant-baseline="middle" fill="white">🇨🇦</text></svg>');
            background-size: 100px;
            opacity: 0.1;
        }
        
        .card-header h1 {
            font-size: 2.3rem;
            margin: 0 0 12px 0;
            font-weight: 700;
        }
        
        .card-header h1 i {
            margin-right: 12px;
            color: var(--canada-white);
        }
        
        .card-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin: 0;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form {
            padding: 35px;
        }
        
        .form-section {
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section h2 {
            font-size: 1.5rem;
            color: var(--canada-red);
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h2 i {
            color: var(--canada-white);
            background: var(--canada-red);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .required {
            color: var(--error-color);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            font-family: inherit;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--canada-red);
            box-shadow: 0 0 0 3px rgba(255, 0, 0, 0.1);
        }
        
        .file-input-container {
            position: relative;
            margin-bottom: 8px;
        }
        
        .file-input-container input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-label {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            background: var(--light-bg);
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .file-label:hover {
            border-color: var(--canada-red);
            background: rgba(255, 0, 0, 0.05);
        }
        
        .file-label.file-selected {
            border-color: var(--success-color);
            background: rgba(16, 185, 129, 0.05);
        }
        
        .file-hint {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 6px;
        }
        
        .file-progress {
            margin-top: 10px;
            display: none;
        }
        
        .progress-bar {
            height: 6px;
            background: var(--success-color);
            border-radius: 3px;
            transition: width 0.3s ease;
        }
        
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 18px;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid var(--light-gray);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 14px 28px;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            border: 2px solid transparent;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn i {
            margin-right: 10px;
        }
        
        .btn-primary {
            background: var(--canada-red);
            color: var(--canada-white);
        }
        
        .btn-primary:hover {
            background: #cc0000;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--canada-red);
            border-color: var(--canada-red);
        }
        
        .btn-outline:hover {
            background: var(--canada-red);
            color: var(--canada-white);
        }
        
        .btn-success {
            background: var(--success-color);
            color: var(--canada-white);
        }
        
        .btn-success:hover {
            background: #0da271;
        }
        
        .btn-warning {
            background: var(--warning-color);
            color: var(--canada-white);
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        .btn-info {
            background: var(--info-color);
            color: var(--canada-white);
        }
        
        .btn-info:hover {
            background: #2563eb;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        
        .documents-container h3 {
            font-size: 1.3rem;
            color: var(--canada-red);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--canada-red);
        }
        
        .niveau-info {
            background: #ffe6e6;
            border: 1px solid var(--canada-red);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            display: none;
        }
        
        .niveau-info.active {
            display: block;
        }
        
        .province-select {
            background: var(--light-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 14px 18px;
            width: 100%;
            font-size: 1rem;
        }
        
        .document-optionnel {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .document-optionnel h4 {
            color: var(--canada-red);
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .document-supplementaire {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .document-supplementaire .btn-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--error-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .option-group {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .option-group input[type="radio"] {
            width: 18px;
            height: 18px;
        }
        
        .option-group label {
            margin-bottom: 0;
            cursor: pointer;
        }
        
        .demande-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .lettre-motivation-option {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .error-message {
            color: var(--error-color);
            font-size: 0.9rem;
            margin-top: 5px;
            display: none;
        }
        
        @media (max-width: 768px) {
            .card-header h1 {
                font-size: 1.9rem;
            }
            
            .card-header p {
                font-size: 1.05rem;
            }
            
            .form {
                padding: 25px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 12px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .option-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .demande-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-university"></i> Canada - Admission Universitaire</h1>
            <p>Formulaire pour admission dans les établissements canadiens</p>
        </div>

        <form method="post" action="" class="form" enctype="multipart/form-data" id="canada-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <!-- Informations personnelles -->
            <div class="form-section">
                <h2><i class="fas fa-user-graduate"></i> Informations personnelles</h2>
                <div class="form-group">
                    <label for="nom">Nom complet <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" required maxlength="100" value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
                    <div class="error-message" id="nom-error"></div>
                </div>
                <div class="form-group">
                    <label for="naissance">Date de naissance <span class="required">*</span></label>
                    <input type="date" id="naissance" name="naissance" required value="<?php echo isset($_POST['naissance']) ? htmlspecialchars($_POST['naissance']) : ''; ?>">
                    <div class="error-message" id="naissance-error"></div>
                </div>
                <div class="form-group">
                    <label for="nationalite">Nationalité <span class="required">*</span></label>
                    <input type="text" id="nationalite" name="nationalite" required maxlength="50" value="<?php echo isset($_POST['nationalite']) ? htmlspecialchars($_POST['nationalite']) : ''; ?>">
                    <div class="error-message" id="nationalite-error"></div>
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required maxlength="100" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="error-message" id="email-error"></div>
                </div>
                <div class="form-group">
                    <label for="province">Province canadienne souhaitée <span class="required">*</span></label>
                    <select id="province" name="province" class="province-select" required onchange="toggleProvinceDocuments()">
                        <option value="">-- Choisir une province --</option>
                        <option value="quebec" <?php echo (isset($_POST['province']) && $_POST['province'] == 'quebec') ? 'selected' : ''; ?>>Québec</option>
                        <option value="ontario" <?php echo (isset($_POST['province']) && $_POST['province'] == 'ontario') ? 'selected' : ''; ?>>Ontario</option>
                        <option value="colombie_britannique" <?php echo (isset($_POST['province']) && $_POST['province'] == 'colombie_britannique') ? 'selected' : ''; ?>>Colombie-Britannique</option>
                        <option value="alberta" <?php echo (isset($_POST['province']) && $_POST['province'] == 'alberta') ? 'selected' : ''; ?>>Alberta</option>
                        <option value="manitoba" <?php echo (isset($_POST['province']) && $_POST['province'] == 'manitoba') ? 'selected' : ''; ?>>Manitoba</option>
                        <option value="saskatchewan" <?php echo (isset($_POST['province']) && $_POST['province'] == 'saskatchewan') ? 'selected' : ''; ?>>Saskatchewan</option>
                        <option value="nouvelle_ecosse" <?php echo (isset($_POST['province']) && $_POST['province'] == 'nouvelle_ecosse') ? 'selected' : ''; ?>>Nouvelle-Écosse</option>
                        <option value="nouveau_brunswick" <?php echo (isset($_POST['province']) && $_POST['province'] == 'nouveau_brunswick') ? 'selected' : ''; ?>>Nouveau-Brunswick</option>
                        <option value="terre_neuve" <?php echo (isset($_POST['province']) && $_POST['province'] == 'terre_neuve') ? 'selected' : ''; ?>>Terre-Neuve-et-Labrador</option>
                        <option value="ile_du_prince_edouard" <?php echo (isset($_POST['province']) && $_POST['province'] == 'ile_du_prince_edouard') ? 'selected' : ''; ?>>Île-du-Prince-Édouard</option>
                    </select>
                    <div class="error-message" id="province-error"></div>
                </div>
            </div>
            
            <!-- Section Choix du niveau -->
            <div class="form-section">
                <h2><i class="fas fa-graduation-cap"></i> Choix du niveau d'études</h2>
                
                <div class="form-group">
                    <label for="niveau_etude">Niveau d'études souhaité <span class="required">*</span></label>
                    <select id="niveau_etude" name="niveau_etude" required onchange="afficherDocumentsNiveau()">
                        <option value="">-- Choisir votre niveau --</option>
                        <option value="bac" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'bac') ? 'selected' : ''; ?>>Baccalauréat (Undergraduate)</option>
                        <option value="dec" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'dec') ? 'selected' : ''; ?>>DEC (Diplôme d'études collégiales)</option>
                        <option value="dep" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'dep') ? 'selected' : ''; ?>>DEP (Diplôme d'études professionnelles)</option>
                        <option value="aec" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'aec') ? 'selected' : ''; ?>>AEC (Attestation d'études collégiales)</option>
                        <option value="maitrise" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'maitrise') ? 'selected' : ''; ?>>Maîtrise (Master)</option>
                        <option value="phd" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'phd') ? 'selected' : ''; ?>>Doctorat (PhD)</option>
                        <option value="technique" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'technique') ? 'selected' : ''; ?>>Formation technique/College</option>
                        <option value="langue" <?php echo (isset($_POST['niveau_etude']) && $_POST['niveau_etude'] == 'langue') ? 'selected' : ''; ?>>Programme de langue</option>
                    </select>
                    <div class="error-message" id="niveau_etude-error"></div>
                </div>

                <!-- Informations spécifiques au niveau -->
                <div class="niveau-info" id="info-bac">
                    <h4>🎓 Baccalauréat (Undergraduate)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-dec">
                    <h4>📚 DEC (Diplôme d'études collégiales)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-dep">
                    <h4>⚙️ DEP (Diplôme d'études professionnelles)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-aec">
                    <h4>📝 AEC (Attestation d'études collégiales)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-maitrise">
                    <h4>🎓 Maîtrise (Master)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes du baccalauréat, diplôme de baccalauréat, relevés de notes complets, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-phd">
                    <h4>🔬 Doctorat (PhD)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes de la maîtrise, diplôme de maîtrise, projet de recherche, CV académique, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-technique">
                    <h4>⚙️ Formation technique/College</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-langue">
                    <h4>🌍 Programme de langue</h4>
                    <p><strong>Documents requis :</strong> Derniers relevés de notes, certificat de scolarité (optionnel)</p>
                </div>
            </div>
            
            <!-- Section Documents académiques dynamiques -->
            <div class="form-section">
                <h2><i class="fas fa-file-alt"></i> Documents académiques requis</h2>
                
                <div id="docs_obligatoires" class="documents-container">
                    <div class="alert alert-info">
                        Veuillez d'abord sélectionner votre niveau d'études pour voir les documents requis.
                    </div>
                </div>
                
                <!-- Attestation de province (dynamique selon la province) -->
                <div id="attestation_province_container" style="display: none;">
                    <div class="document-optionnel">
                        <h4 id="attestation_title"><i class="fas fa-file-certificate"></i> Attestation de province</h4>
                        <p style="margin-bottom: 15px; color: #64748b;" id="attestation_description">Avez-vous une attestation de province ?</p>
                        
                        <div class="option-group">
                            <input type="radio" id="attestation_oui" name="attestation_province_option" value="oui" onchange="toggleAttestationProvinceUpload(true)" <?php echo (isset($_POST['attestation_province_option']) && $_POST['attestation_province_option'] == 'oui') ? 'checked' : ''; ?>>
                            <label for="attestation_oui">Oui, j'ai une attestation de province</label>
                        </div>
                        <div class="option-group">
                            <input type="radio" id="attestation_non" name="attestation_province_option" value="non" onchange="toggleAttestationProvinceUpload(false)" <?php echo (!isset($_POST['attestation_province_option']) || $_POST['attestation_province_option'] == 'non') ? 'checked' : ''; ?>>
                            <label for="attestation_non">Non, je n'ai pas d'attestation de province</label>
                        </div>
                        
                        <div id="attestation_province_upload_container" style="display: none; margin-top: 15px;">
                            <div class="file-input-container">
                                <input type="file" id="attestation_province" name="attestation_province" accept=".pdf,.jpg,.jpeg,.png">
                                <label for="attestation_province" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text" id="attestation_file_text">Télécharger l'attestation de province</span>
                                </label>
                            </div>
                            <div class="file-progress" id="attestation_province_progress">
                                <div class="progress-bar" style="width: 0%"></div>
                            </div>
                            <div class="file-hint" id="attestation_file_hint">Attestation de province (PDF, JPG, PNG - Max. 5MB)</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Documents administratifs -->
            <div class="form-section">
                <h2><i class="fas fa-file-upload"></i> Documents administratifs</h2>
                
                <!-- Passeport - Obligatoire -->
                <div class="form-group">
                    <label for="passeport">Passeport <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="passeport" name="passeport" required accept=".pdf,.jpg,.jpeg,.png">
                        <label for="passeport" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-progress" id="passeport_progress">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="file-hint">Pages du passeport avec photo et informations (PDF, JPG, PNG - Max. 5MB)</div>
                </div>
                
                <!-- Acte de naissance - Obligatoire -->
                <div class="form-group">
                    <label for="acte_naissance">Acte de naissance <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="acte_naissance" name="acte_naissance" required accept=".pdf,.jpg,.jpeg,.png">
                        <label for="acte_naissance" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-progress" id="acte_naissance_progress">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="file-hint">Acte de naissance (PDF, JPG, PNG - Max. 5MB)</div>
                </div>
                
                <!-- Test de langue - Optionnel avec choix -->
                <div class="document-optionnel">
                    <h4><i class="fas fa-language"></i> Test de langue (Optionnel)</h4>
                    <p style="margin-bottom: 15px; color: #64748b;">Avez-vous déjà un test de langue (IELTS, TOEFL, TEF, TCF) ?</p>
                    
                    <div class="option-group">
                        <input type="radio" id="test_oui" name="test_langue_option" value="oui" onchange="toggleTestLangueUpload(true)" <?php echo (isset($_POST['test_langue_option']) && $_POST['test_langue_option'] == 'oui') ? 'checked' : ''; ?>>
                            <label for="test_oui">Oui, j'ai déjà un test de langue</label>
                    </div>
                    <div class="option-group">
                        <input type="radio" id="test_non" name="test_langue_option" value="non" onchange="toggleTestLangueUpload(false)" <?php echo (!isset($_POST['test_langue_option']) || $_POST['test_langue_option'] == 'non') ? 'checked' : ''; ?>>
                        <label for="test_non">Non, je n'ai pas encore de test de langue</label>
                    </div>
                    
                    <div id="test_langue_upload_container" style="display: none; margin-top: 15px;">
                        <div class="file-input-container">
                            <input type="file" id="test_langue" name="test_langue" accept=".pdf,.jpg,.jpeg,.png">
                            <label for="test_langue" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Télécharger votre test de langue</span>
                            </label>
                        </div>
                        <div class="file-progress" id="test_langue_progress">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                        <div class="file-hint">IELTS, TOEFL, TEF ou TCF (PDF, JPG, PNG - Max. 5MB)</div>
                    </div>
                </div>
                
                <!-- CV - Optionnel avec choix -->
                <div class="document-optionnel">
                    <h4><i class="fas fa-file-alt"></i> Curriculum Vitae (Optionnel)</h4>
                    <p style="margin-bottom: 15px; color: #64748b;">Avez-vous un CV à jour ?</p>
                    
                    <div class="option-group">
                        <input type="radio" id="cv_oui" name="cv_option" value="oui" onchange="toggleCVUpload(true)" <?php echo (isset($_POST['cv_option']) && $_POST['cv_option'] == 'oui') ? 'checked' : ''; ?>>
                        <label for="cv_oui">Oui, j'ai un CV à jour</label>
                    </div>
                    <div class="option-group">
                        <input type="radio" id="cv_non" name="cv_option" value="non" onchange="toggleCVUpload(false)" <?php echo (!isset($_POST['cv_option']) || $_POST['cv_option'] == 'non') ? 'checked' : ''; ?>>
                        <label for="cv_non">Non, je n'ai pas de CV</label>
                    </div>
                    
                    <div id="cv_upload_container" style="display: none; margin-top: 15px;">
                        <div class="file-input-container">
                            <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx">
                            <label for="cv" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Télécharger votre CV</span>
                            </label>
                        </div>
                        <div class="file-progress" id="cv_progress">
                            <div class="progress-bar" style="width: 0%"></div>
                        </div>
                        <div class="file-hint">CV détaillant votre parcours académique et professionnel (PDF, DOC - Max. 5MB)</div>
                    </div>
                </div>
                
                <!-- Documents supplémentaires -->
                <div class="form-group">
                    <h4 style="margin-bottom: 20px; color: var(--canada-red);">
                        <i class="fas fa-plus-circle"></i> Documents supplémentaires
                    </h4>
                    <p style="margin-bottom: 15px; color: #64748b;">
                        Vous pouvez ajouter d'autres documents comme des lettres de recommandation, attestations de stages, formations supplémentaires, etc.
                    </p>
                    
                    <div id="documents_supplementaires">
                        <!-- Les documents supplémentaires seront ajoutés ici dynamiquement -->
                    </div>
                    
                    <button type="button" class="btn btn-outline btn-sm" onclick="ajouterDocumentSupplementaire()" id="btn-ajouter-doc">
                        <i class="fas fa-plus"></i> Ajouter un document
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="btn-submit">
                    <i class="fas fa-paper-plane"></i> Soumettre la demande
                </button>
                <button type="reset" class="btn btn-outline">
                    <i class="fas fa-undo"></i> Réinitialiser
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Configuration des documents par niveau pour le Canada
    const configDocs = {
        bac: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle"
            }
        ],
        dec: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle"
            }
        ],
        dep: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle"
            }
        ],
        aec: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle"
            }
        ],
        maitrise: [
            { 
                label: "Relevé de notes du baccalauréat", 
                name: "releve_bac", 
                type: "file",
                required: true,
                hint: "Relevés de notes complets du baccalauréat"
            },
            { 
                label: "Diplôme de baccalauréat", 
                name: "diplome_bac", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de baccalauréat"
            },
            { 
                label: "Relevés de notes universitaires complets", 
                name: "releves_universitaires", 
                type: "file",
                required: true,
                hint: "Tous les relevés de notes universitaires"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle en Maîtrise"
            }
        ],
        phd: [
            { 
                label: "Relevé de notes de la maîtrise", 
                name: "releve_maitrise", 
                type: "file",
                required: true,
                hint: "Relevés de notes complets de la maîtrise"
            },
            { 
                label: "Diplôme de maîtrise", 
                name: "diplome_maitrise", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de maîtrise"
            },
            { 
                label: "Projet de recherche", 
                name: "projet_recherche", 
                type: "file",
                required: true,
                hint: "Projet de recherche détaillé pour le doctorat"
            },
            { 
                label: "CV académique", 
                name: "cv_academique", 
                type: "file",
                required: true,
                hint: "Curriculum vitae académique détaillé"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle en Doctorat"
            }
        ],
        technique: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle en formation technique"
            }
        ],
        langue: [
            { 
                label: "Derniers relevés de notes", 
                name: "releves_notes", 
                type: "file",
                required: true,
                hint: "Derniers relevés de notes disponibles"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle en programme de langue"
            }
        ]
    };

    const MAX_FILES = 10;
    const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    let documentCounter = 0;
    let currentFileCount = 0;
    
    // Fonction pour afficher les documents selon le niveau choisi
    function afficherDocumentsNiveau() {
        const niveau = document.getElementById('niveau_etude').value;
        const docsContainer = document.getElementById('docs_obligatoires');
        
        // Masquer toutes les infos de niveau
        document.querySelectorAll('.niveau-info').forEach(info => {
            info.classList.remove('active');
        });
        
        // Afficher l'info du niveau sélectionné
        if (niveau) {
            document.getElementById(`info-${niveau}`).classList.add('active');
        }
        
        if (configDocs[niveau]) {
            const niveauText = document.getElementById('niveau_etude').options[document.getElementById('niveau_etude').selectedIndex].text;
            
            docsContainer.innerHTML = `
                <h3>📚 Documents académiques requis pour ${niveauText}</h3>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Les documents suivants sont obligatoires pour votre niveau d'études au Canada.
                </div>
            `;
            
            configDocs[niveau].forEach(doc => {
                const docElement = document.createElement('div');
                docElement.className = 'form-group';
                
                const requiredStar = doc.required ? '<span class="required">*</span>' : '';
                const requiredAttr = doc.required ? 'required' : '';
                
                docElement.innerHTML = `
                    <label>${doc.label} ${requiredStar}</label>
                    <div class="file-input-container">
                        <input type="${doc.type}" name="${doc.name}" ${requiredAttr} accept=".pdf,.jpg,.jpeg,.png">
                        <label class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-progress" id="${doc.name}_progress">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="file-hint">${doc.hint} - Formats acceptés: JPG, PNG, PDF (Max 5MB)</div>
                `;
                docsContainer.appendChild(docElement);
                
                // Ajouter l'écouteur d'événement pour le fichier
                setupFileInput(docElement.querySelector('input[type="file"]'));
            });
        } else {
            docsContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Veuillez d'abord sélectionner votre niveau d'études pour voir les documents requis.
                </div>
            `;
        }
    }

    // Fonction pour gérer l'affichage des documents selon la province
    function toggleProvinceDocuments() {
        const province = document.getElementById('province').value;
        const attestationContainer = document.getElementById('attestation_province_container');
        const title = document.getElementById('attestation_title');
        const description = document.getElementById('attestation_description');
        const fileText = document.getElementById('attestation_file_text');
        const fileHint = document.getElementById('attestation_file_hint');
        
        if (province) {
            attestationContainer.style.display = 'block';
            
            // Adapter le texte selon la province
            if (province === 'quebec') {
                title.innerHTML = '<i class="fas fa-file-contract"></i> CAQ (Québec)';
                description.textContent = 'Avez-vous deja  un Certificat d\'Acceptation du Québec (CAQ) ?';
                fileText.textContent = 'Télécharger le CAQ';
                fileHint.textContent = 'Certificat d\'Acceptation du Québec (PDF, JPG, PNG - Max. 5MB)';
            } else {
                title.innerHTML = '<i class="fas fa-file-certificate"></i> Attestation de province';
                description.textContent = 'Avez-vous deja une attestation de province ?';
                fileText.textContent = 'Télécharger l\'attestation de province';
                fileHint.textContent = 'Attestation de province (PDF, JPG, PNG - Max. 5MB)';
            }
        } else {
            attestationContainer.style.display = 'none';
        }
        
        // Réinitialiser les options d'upload
        toggleAttestationProvinceUpload(false);
        document.getElementById('attestation_non').checked = true;
    }

    // Fonction pour gérer l'upload de l'attestation de province
    function toggleAttestationProvinceUpload(show) {
        const attestationContainer = document.getElementById('attestation_province_upload_container');
        const attestationInput = document.getElementById('attestation_province');
        
        if (show) {
            attestationContainer.style.display = 'block';
            attestationInput.disabled = false;
        } else {
            attestationContainer.style.display = 'none';
            attestationInput.disabled = true;
            attestationInput.value = '';
            
            // Réinitialiser le label du fichier
            const label = attestationInput.nextElementSibling;
            const province = document.getElementById('province').value;
            
            if (province === 'quebec') {
                fileText.textContent = 'Télécharger le CAQ';
            } else {
                fileText.textContent = 'Télécharger l\'attestation de province';
            }
            label.classList.remove('file-selected');
        }
    }

    // Fonction pour gérer l'upload du CV
    function toggleCVUpload(show) {
        const cvContainer = document.getElementById('cv_upload_container');
        const cvInput = document.getElementById('cv');
        
        if (show) {
            cvContainer.style.display = 'block';
            cvInput.disabled = false;
        } else {
            cvContainer.style.display = 'none';
            cvInput.disabled = true;
            cvInput.value = '';
            
            // Réinitialiser le label du fichier
            const label = cvInput.nextElementSibling;
            const fileText = label.querySelector('.file-text');
            fileText.textContent = 'Télécharger votre CV';
            label.classList.remove('file-selected');
        }
    }

    // Fonction pour gérer l'upload du test de langue
    function toggleTestLangueUpload(show) {
        const testContainer = document.getElementById('test_langue_upload_container');
        const testInput = document.getElementById('test_langue');
        
        if (show) {
            testContainer.style.display = 'block';
            testInput.disabled = false;
        } else {
            testContainer.style.display = 'none';
            testInput.disabled = true;
            testInput.value = '';
            
            // Réinitialiser le label du fichier
            const label = testInput.nextElementSibling;
            const fileText = label.querySelector('.file-text');
            fileText.textContent = 'Télécharger votre test de langue';
            label.classList.remove('file-selected');
        }
    }

    // Fonction pour ajouter un document supplémentaire
    function ajouterDocumentSupplementaire() {
        if (currentFileCount >= MAX_FILES) {
            alert(`Maximum ${MAX_FILES} documents supplémentaires autorisés`);
            return;
        }
        
        documentCounter++;
        currentFileCount++;
        const container = document.getElementById('documents_supplementaires');
        const btnAjouter = document.getElementById('btn-ajouter-doc');
        
        const docElement = document.createElement('div');
        docElement.className = 'document-supplementaire';
        docElement.innerHTML = `
            <button type="button" class="btn-remove" onclick="supprimerDocumentSupplementaire(this)">
                <i class="fas fa-times"></i>
            </button>
            <div class="form-group">
                <label>Type de document <span class="required">*</span></label>
                <select name="type_document_supp_${documentCounter}" required>
                    <option value="">-- Choisir le type --</option>
                    <option value="lettre_recommandation">Lettre de recommandation</option>
                    <option value="attestation_stage">Attestation de stage</option>
                    <option value="formation_supplementaire">Formation supplémentaire</option>
                    <option value="certificat_langue">Certificat de langue supplémentaire</option>
                    <option value="portfolio">Portfolio</option>
                    <option value="publication">Publication</option>
                    <option value="autre">Autre document</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description du document</label>
                <input type="text" name="description_document_supp_${documentCounter}" placeholder="Ex: Lettre de recommandation du professeur Dupont" maxlength="255">
            </div>
            <div class="form-group">
                <label>Fichier <span class="required">*</span></label>
                <div class="file-input-container">
                    <input type="file" name="fichier_document_supp_${documentCounter}" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                    <label class="file-label">
                        <i class="fas fa-upload"></i>
                        <span class="file-text">Choisir un fichier</span>
                    </label>
                </div>
                <div class="file-progress" id="document_supp_${documentCounter}_progress">
                    <div class="progress-bar" style="width: 0%"></div>
                </div>
                <div class="file-hint">Formats acceptés: JPG, PNG, PDF, DOC (Max 5MB)</div>
            </div>
        `;
        
        container.appendChild(docElement);
        
        // Mettre à jour le bouton d'ajout
        if (currentFileCount >= MAX_FILES) {
            btnAjouter.disabled = true;
            btnAjouter.style.opacity = '0.5';
            btnAjouter.title = 'Nombre maximum de documents atteint';
        }
        
        // Ajouter les écouteurs d'événements pour le nouveau fichier
        setupFileInput(docElement.querySelector('input[type="file"]'));
    }

    // Fonction pour supprimer un document supplémentaire
    function supprimerDocumentSupplementaire(button) {
        const docElement = button.closest('.document-supplementaire');
        docElement.remove();
        currentFileCount--;
        
        // Réactiver le bouton d'ajout si nécessaire
        const btnAjouter = document.getElementById('btn-ajouter-doc');
        if (currentFileCount < MAX_FILES) {
            btnAjouter.disabled = false;
            btnAjouter.style.opacity = '1';
            btnAjouter.title = '';
        }
    }

    // Configuration d'un input file
    function setupFileInput(input) {
        input.addEventListener('change', function() {
            const label = this.nextElementSibling;
            const fileText = label.querySelector('.file-text');
            const progress = this.parentElement.nextElementSibling;
            
            if (this.files.length > 0) {
                const file = this.files[0];
                
                // Vérifier la taille du fichier
                if (file.size > MAX_FILE_SIZE) {
                    alert('Fichier trop volumineux. Maximum 5MB autorisé.');
                    this.value = '';
                    fileText.textContent = 'Choisir un fichier';
                    label.classList.remove('file-selected');
                    progress.style.display = 'none';
                    return;
                }
                
                if (this.multiple) {
                    fileText.textContent = `${this.files.length} fichier(s) sélectionné(s)`;
                } else {
                    fileText.textContent = file.name;
                }
                label.classList.add('file-selected');
                
                // Simuler une progression (pour la démo)
                progress.style.display = 'block';
                let width = 0;
                const interval = setInterval(() => {
                    if (width >= 100) {
                        clearInterval(interval);
                        setTimeout(() => {
                            progress.style.display = 'none';
                        }, 1000);
                    } else {
                        width += 10;
                        progress.querySelector('.progress-bar').style.width = width + '%';
                    }
                }, 50);
            } else {
                fileText.textContent = 'Choisir un fichier';
                label.classList.remove('file-selected');
                progress.style.display = 'none';
            }
        });
    }

    // Validation du formulaire
    function validateForm() {
        let isValid = true;
        
        // Réinitialiser les erreurs
        document.querySelectorAll('.error-message').forEach(error => {
            error.style.display = 'none';
            error.textContent = '';
        });
        
        document.querySelectorAll('input, select').forEach(field => {
            field.style.borderColor = '';
        });
        
        // Validation de l'âge (au moins 16 ans)
        const birthdate = new Date(document.getElementById('naissance').value);
        const today = new Date();
        const age = today.getFullYear() - birthdate.getFullYear();
        const monthDiff = today.getMonth() - birthdate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
            age--;
        }
        
        if (age < 16) {
            showError('naissance', 'Vous devez avoir au moins 16 ans');
            isValid = false;
        }
        
        // Validation de l'email
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showError('email', 'Format d\'email invalide');
            isValid = false;
        }
        
        // Validation des champs requis
        const requiredFields = document.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                const fieldName = field.id || field.name;
                showError(fieldName, 'Ce champ est obligatoire');
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    function showError(fieldId, message) {
        const errorElement = document.getElementById(fieldId + '-error');
        const fieldElement = document.getElementById(fieldId);
        
        if (errorElement && fieldElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
            fieldElement.style.borderColor = 'var(--error-color)';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // File input labels pour les champs fixes
        document.querySelectorAll('input[type="file"]').forEach(input => {
            setupFileInput(input);
        });
        
        // Form validation before submission
        document.getElementById('canada-form').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return;
            }
            
            // Désactiver le bouton de soumission
            const submitBtn = document.getElementById('btn-submit');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
        });
        
        // Initialiser l'affichage si un niveau est déjà sélectionné (après rechargement)
        const niveauSelect = document.getElementById('niveau_etude');
        if (niveauSelect.value) {
            afficherDocumentsNiveau();
        }
        
        // Initialiser l'affichage de la province
        const provinceSelect = document.getElementById('province');
        if (provinceSelect.value) {
            toggleProvinceDocuments();
        }
        
        // Initialiser les options radio
        const attestationOui = document.getElementById('attestation_oui');
        if (attestationOui && attestationOui.checked) {
            toggleAttestationProvinceUpload(true);
        }
        
        const testOui = document.getElementById('test_oui');
        if (testOui && testOui.checked) {
            toggleTestLangueUpload(true);
        }
        
        const cvOui = document.getElementById('cv_oui');
        if (cvOui && cvOui.checked) {
            toggleCVUpload(true);
        }
        
        // Validation en temps réel
        document.getElementById('email').addEventListener('blur', function() {
            const email = this.value;
            if (email) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showError('email', 'Format d\'email invalide');
                } else {
                    document.getElementById('email-error').style.display = 'none';
                    this.style.borderColor = '';
                }
            }
        });
        
        document.getElementById('naissance').addEventListener('change', function() {
            if (this.value) {
                const birthdate = new Date(this.value);
                const today = new Date();
                const age = today.getFullYear() - birthdate.getFullYear();
                const monthDiff = today.getMonth() - birthdate.getMonth();
                
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
                    age--;
                }
                
                if (age < 16) {
                    showError('naissance', 'Vous devez avoir au moins 16 ans');
                } else {
                    document.getElementById('naissance-error').style.display = 'none';
                    this.style.borderColor = '';
                }
            }
        });
    });
</script>
</body>
</html>