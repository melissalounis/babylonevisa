<?php
session_start();

// Configuration de sécurité
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = "Erreur de sécurité. Veuillez réessayer.";
    header('Location: etude.php');
    exit;
}

// Régénérer le token CSRF
unset($_SESSION['csrf_token']);

// Configuration
$upload_dir = 'uploads/canada/';
$max_file_size = 5 * 1024 * 1024; // 5MB
$allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

// Créer le répertoire d'upload s'il n'existe pas
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Fonction de validation
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

// Fonction pour gérer l'upload sécurisé des fichiers
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

// Traitement principal
try {
    // Valider les données du formulaire
    $validation_errors = validateFormData($_POST);
    
    if (!empty($validation_errors)) {
        throw new Exception(implode(' ', $validation_errors));
    }
    
    // Traitement des fichiers obligatoires
    $file_errors = [];
    $uploaded_data = [];
    
    // Fichiers obligatoires
    $required_files = ['passeport', 'photo', 'documents_identite'];
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
        'attestation_province' => isset($_POST['attestation_province_option']) && $_POST['attestation_province_option'] === 'oui',
        'caq' => isset($_POST['caq_option']) && $_POST['caq_option'] === 'oui',
        'test_langue' => isset($_POST['test_langue_option']) && $_POST['test_langue_option'] === 'oui',
        'cv' => isset($_POST['cv_option']) && $_POST['cv_option'] === 'oui'
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
    $academic_files = [];
    
    switch ($niveau) {
        case 'bac':
        case 'dec':
        case 'dep':
        case 'aec':
        case 'technique':
            $academic_files = ['releve_notes', 'diplome_fin_etudes'];
            break;
        case 'maitrise':
            $academic_files = ['releve_bac', 'diplome_bac', 'releves_universitaires'];
            break;
        case 'phd':
            $academic_files = ['releve_maitrise', 'diplome_maitrise', 'projet_recherche', 'cv_academique'];
            break;
        case 'langue':
            $academic_files = ['releves_notes'];
            break;
    }
    
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
    
    // Sauvegarde en base de données (exemple)
    $demande_data = [
        'nom_complet' => htmlspecialchars($_POST['nom']),
        'date_naissance' => $_POST['naissance'],
        'nationalite' => htmlspecialchars($_POST['nationalite']),
        'email' => htmlspecialchars($_POST['email']),
        'province' => $_POST['province'],
        'niveau_etude' => $_POST['niveau_etude'],
        'fichiers' => $uploaded_data,
        'date_soumission' => date('Y-m-d H:i:s')
    ];
    
    // Ici, vous devriez insérer dans votre base de données
    // Exemple: saveToDatabase($demande_data);
    
    // Pour l'exemple, on sauvegarde dans un fichier JSON
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
    
    $_SESSION['success_message'] = "Votre demande a été soumise avec succès! Nous vous contacterons bientôt.";
    
} catch (Exception $e) {
    $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
}

// Redirection vers le formulaire
header('Location: index.php');
exit;
?>