<?php
// save_belgique.php
session_start();

// Inclure la configuration
require_once '../../../config.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Valider les données
        $required_fields = ['nom', 'naissance', 'nationalite', 'telephone', 'email', 'adresse', 'niveau_etude'];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Le champ $field est obligatoire.");
            }
        }
        
        // Créer le dossier d'upload s'il n'existe pas
        $upload_dir = 'uploads/etude_belgique/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Fonction pour uploader un fichier
        function uploadFile($file, $field_name, $upload_dir, $multiple = false) {
            if ($multiple && is_array($file['name'])) {
                $uploaded_files = [];
                foreach ($file['name'] as $key => $name) {
                    if ($file['error'][$key] === UPLOAD_ERR_OK) {
                        $file_info = [
                            'name' => $file['name'][$key],
                            'tmp_name' => $file['tmp_name'][$key],
                            'error' => $file['error'][$key]
                        ];
                        $filename = uploadSingleFile($file_info, $upload_dir);
                        if ($filename) {
                            $uploaded_files[] = $filename;
                        }
                    }
                }
                return implode(',', $uploaded_files);
            } else {
                return uploadSingleFile($file, $upload_dir);
            }
        }
        
        function uploadSingleFile($file, $upload_dir) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return null;
            }
            
            // Validation du type de fichier
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
            $file_type = mime_content_type($file['tmp_name']);
            
            if (!in_array($file_type, $allowed_types)) {
                throw new Exception("Type de fichier non autorisé. Formats acceptés: JPG, PNG, PDF");
            }
            
            // Validation de la taille (max 5MB)
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                throw new Exception("Le fichier est trop volumineux. Taille max: 5MB");
            }
            
            // Générer un nom de fichier unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file['name']);
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                return $filename;
            }
            
            return null;
        }
        
        // Upload des fichiers communs
        $data = [
            'nom' => $_POST['nom'],
            'naissance' => $_POST['naissance'],
            'nationalite' => $_POST['nationalite'],
            'telephone' => $_POST['telephone'],
            'email' => $_POST['email'],
            'adresse' => $_POST['adresse'],
            'niveau_etude' => $_POST['niveau_etude'],
            'equivalence_bac' => $_POST['equivalence_bac'] ?? 'non',
            'photo' => uploadFile($_FILES['photo'], 'photo', $upload_dir),
            'passport' => uploadFile($_FILES['passport'], 'passport', $upload_dir),
            'certificat_scolarite_actuel' => uploadFile($_FILES['certificat_scolarite_actuel'], 'certificat_scolarite_actuel', $upload_dir),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Upload des documents selon le niveau
        $niveau = $_POST['niveau_etude'];
        
        // Documents pour tous les niveaux sauf Master
        if ($niveau !== 'master') {
            // Documents communs selon la configuration
            if (isset($_FILES['releve_bac']) && !empty($_FILES['releve_bac']['name'])) {
                $data['releve_bac'] = uploadFile($_FILES['releve_bac'], 'releve_bac', $upload_dir);
            }
            
            if (isset($_FILES['diplome_bac']) && !empty($_FILES['diplome_bac']['name'])) {
                $data['diplome_bac'] = uploadFile($_FILES['diplome_bac'], 'diplome_bac', $upload_dir);
            }
            
            if (isset($_FILES['releve_l1']) && !empty($_FILES['releve_l1']['name'])) {
                $data['releve_l1'] = uploadFile($_FILES['releve_l1'], 'releve_l1', $upload_dir);
            }
            
            if (isset($_FILES['releve_l2']) && !empty($_FILES['releve_l2']['name'])) {
                $data['releve_l2'] = uploadFile($_FILES['releve_l2'], 'releve_l2', $upload_dir);
            }
            
            if (isset($_FILES['releve_2nde']) && !empty($_FILES['releve_2nde']['name'])) {
                $data['releve_2nde'] = uploadFile($_FILES['releve_2nde'], 'releve_2nde', $upload_dir);
            }
            
            if (isset($_FILES['releve_1ere']) && !empty($_FILES['releve_1ere']['name'])) {
                $data['releve_1ere'] = uploadFile($_FILES['releve_1ere'], 'releve_1ere', $upload_dir);
            }
            
            if (isset($_FILES['releve_terminale']) && !empty($_FILES['releve_terminale']['name'])) {
                $data['releve_terminale'] = uploadFile($_FILES['releve_terminale'], 'releve_terminale', $upload_dir);
            }
        }
        
        // Documents spécifiques pour Master
        if ($niveau === 'master') {
            if (isset($_FILES['releve_bac']) && !empty($_FILES['releve_bac']['name'])) {
                $data['releve_bac'] = uploadFile($_FILES['releve_bac'], 'releve_bac', $upload_dir);
            }
            
            if (isset($_FILES['diplome_bac']) && !empty($_FILES['diplome_bac']['name'])) {
                $data['diplome_bac'] = uploadFile($_FILES['diplome_bac'], 'diplome_bac', $upload_dir);
            }
            
            // Documents de licence pour Master
            if (isset($_FILES['releves_licence_l1']) && !empty($_FILES['releves_licence_l1']['name'][0])) {
                $data['releves_licence_l1'] = uploadFile($_FILES['releves_licence_l1'], 'releves_licence_l1', $upload_dir, true);
            }
            
            if (isset($_FILES['releves_licence_l2']) && !empty($_FILES['releves_licence_l2']['name'][0])) {
                $data['releves_licence_l2'] = uploadFile($_FILES['releves_licence_l2'], 'releves_licence_l2', $upload_dir, true);
            }
            
            if (isset($_FILES['releves_licence_l3']) && !empty($_FILES['releves_licence_l3']['name'][0])) {
                $data['releves_licence_l3'] = uploadFile($_FILES['releves_licence_l3'], 'releves_licence_l3', $upload_dir, true);
            }
            
            if (isset($_FILES['diplome_licence']) && !empty($_FILES['diplome_licence']['name'])) {
                $data['diplome_licence'] = uploadFile($_FILES['diplome_licence'], 'diplome_licence', $upload_dir);
            }
        }
        
        // Upload de l'équivalence si nécessaire
        if (isset($_POST['equivalence_bac']) && $_POST['equivalence_bac'] === 'oui' && isset($_FILES['document_equivalence'])) {
            $data['document_equivalence'] = uploadFile($_FILES['document_equivalence'], 'document_equivalence', $upload_dir);
        }
        
        // Upload des documents supplémentaires
        if (isset($_FILES['document_supp']) && is_array($_FILES['document_supp']['name'])) {
            $documents_supp = [];
            for ($i = 0; $i < count($_FILES['document_supp']['name']); $i++) {
                if ($_FILES['document_supp']['error'][$i] === UPLOAD_ERR_OK) {
                    $file_info = [
                        'name' => $_FILES['document_supp']['name'][$i],
                        'tmp_name' => $_FILES['document_supp']['tmp_name'][$i],
                        'error' => $_FILES['document_supp']['error'][$i]
                    ];
                    $filename = uploadSingleFile($file_info, $upload_dir);
                    if ($filename) {
                        $type = $_POST['type_document_supp'][$i] ?? 'autre';
                        $documents_supp[] = [
                            'fichier' => $filename,
                            'type' => $type
                        ];
                    }
                }
            }
            if (!empty($documents_supp)) {
                $data['documents_supplementaires'] = json_encode($documents_supp);
            }
        }
        
        // Préparer et exécuter la requête d'insertion
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO etude_belgique ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        
        if ($stmt->execute()) {
            $last_id = $pdo->lastInsertId();
            $success_message = "Votre demande d'études en Belgique a été soumise avec succès. Référence: EB-" . str_pad($last_id, 6, '0', STR_PAD_LEFT);
            $_SESSION['success_message'] = $success_message;
            
            // Stocker l'ID pour debug
            $_SESSION['last_demande_id'] = $last_id;
            
        } else {
            throw new Exception("Erreur lors de l'enregistrement dans la base de données.");
        }
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
        $_SESSION['error_message'] = $error_message;
        
        // Log de l'erreur pour debug
        error_log("Erreur save_belgique.php: " . $error_message);
    }
    
    // Rediriger vers le formulaire
    header('Location: ' . (isset($_SESSION['last_demande_id']) ? 'debug_upload.php?id=' . $_SESSION['last_demande_id'] : 'etude_belgique.php'));
    exit();
} else {
    $_SESSION['error_message'] = 'Méthode non autorisée.';
    header('Location: etude.php');
    exit();
}
?>