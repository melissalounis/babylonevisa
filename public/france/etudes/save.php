<?php
// save_campus.php - Traitement du formulaire Campus France
session_start();

// Connexion à votre base existante babylone_service
$config = [
    'host' => 'localhost',
    'dbname' => 'babylone_service', 
    'username' => 'root',
    'password' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4", 
        $config['username'], 
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Fonction pour générer une référence unique
function genererReference($service_type) {
    $prefixes = [
        'test_langue' => 'TL',
        'remplissage_docs' => 'RD', 
        'demande_visa' => 'DV',
        'complete' => 'CF'
    ];
    $prefix = $prefixes[$service_type] ?? 'CF';
    return $prefix . date('Ymd') . '-' . substr(uniqid(), -6);
}

// Fonction pour uploader un fichier
function uploaderFichier($file, $dossier = 'uploads/campus_france/') {
    if (!file_exists($dossier)) {
        mkdir($dossier, 0777, true);
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Vérification de la taille (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    
    // Vérification de l'extension
    $extensions_autorisees = ['jpg', 'jpeg', 'png', 'pdf'];
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extension, $extensions_autorisees)) {
        return null;
    }
    
    $nomFichier = uniqid() . '_' . time() . '.' . $extension;
    $cheminComplet = $dossier . $nomFichier;
    
    if (move_uploaded_file($file['tmp_name'], $cheminComplet)) {
        return [
            'nom' => $file['name'],
            'chemin' => $cheminComplet,
            'taille' => $file['size']
        ];
    }
    
    return null;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validation des champs obligatoires
    if (empty($_POST['service_type'])) {
        $errors[] = "Le type de service est obligatoire";
    }
    
    if (empty($_POST['nom_complet'])) {
        $errors[] = "Le nom complet est obligatoire";
    }
    
    // Récupération de l'ID utilisateur (à adapter selon votre système d'authentification)
    $user_id = $_SESSION['user_id'] ?? 1; // Exemple, à modifier
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Génération de la référence
            $reference = genererReference($_POST['service_type']);
            
            // Insertion dans la table des demandes
            $sql_demande = "INSERT INTO campus_france_demandes (
                user_id, reference, service_type, nom_complet, email, telephone,
                type_test, test_commentaire, niveau_etude, remplissage_commentaire, statut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_demande = $pdo->prepare($sql_demande);
            
            // Récupération des données
            $email = $_POST['email'] ?? '';
            $telephone = $_POST['telephone'] ?? '';
            $type_test = $_POST['type_test'] ?? null;
            $test_commentaire = $_POST['test_commentaire'] ?? null;
            $niveau_etude = $_POST['niveau_etude'] ?? null;
            $remplissage_commentaire = $_POST['remplissage_commentaire'] ?? null;
            
            $statut = ($_POST['action'] === 'submit') ? 'soumis' : 'brouillon';
            
            $stmt_demande->execute([
                $user_id,
                $reference,
                $_POST['service_type'],
                $_POST['nom_complet'],
                $email,
                $telephone,
                $type_test,
                $test_commentaire,
                $niveau_etude,
                $remplissage_commentaire,
                $statut
            ]);
            
            $demande_id = $pdo->lastInsertId();
            
            // Upload et enregistrement des documents
            $documents_uploades = [];
            
            // Liste des champs fichiers possibles
            $champs_fichiers = ['piece_identite', 'passport'];
            
            // Ajout des fichiers spécifiques selon le niveau d'étude
            if ($niveau_etude) {
                $fichiers_niveau = [
                    'releve_2nde', 'releve_1ere', 'releve_terminale', 'releve_bac',
                    'diplome_bac', 'releve_l1', 'releve_l2', 'releve_l3', 
                    'diplome_licence', 'certificat_scolarite'
                ];
                $champs_fichiers = array_merge($champs_fichiers, $fichiers_niveau);
            }
            
            foreach ($champs_fichiers as $type_doc) {
                if (isset($_FILES[$type_doc]) && $_FILES[$type_doc]['error'] === UPLOAD_ERR_OK) {
                    $fichier = uploaderFichier($_FILES[$type_doc]);
                    if ($fichier) {
                        $sql_doc = "INSERT INTO campus_france_documents 
                                  (demande_id, type_document, nom_fichier, chemin_fichier, taille) 
                                  VALUES (?, ?, ?, ?, ?)";
                        $stmt_doc = $pdo->prepare($sql_doc);
                        $stmt_doc->execute([
                            $demande_id,
                            $type_doc,
                            $fichier['nom'],
                            $fichier['chemin'],
                            $fichier['taille']
                        ]);
                        $documents_uploades[] = $type_doc;
                    }
                }
            }
            
            // Mise à jour de la date de soumission si envoi définitif
            if ($_POST['action'] === 'submit') {
                $sql_update = "UPDATE campus_france_demandes SET date_soumission = NOW() WHERE id = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([$demande_id]);
            }
            
            // Enregistrement dans le suivi
            $action_suivi = ($_POST['action'] === 'submit') ? 'soumission' : 'enregistrement';
            $description = "Demande {$action_suivi} avec succès. " . count($documents_uploades) . " document(s) uploadé(s)";
            
            $sql_suivi = "INSERT INTO campus_france_suivi (demande_id, user_id, action, description) VALUES (?, ?, ?, ?)";
            $stmt_suivi = $pdo->prepare($sql_suivi);
            $stmt_suivi->execute([$demande_id, $user_id, $action_suivi, $description]);
            
            $pdo->commit();
            
            // Redirection
            if ($_POST['action'] === 'submit') {
                $_SESSION['success'] = "Votre demande a été soumise avec succès. Référence : $reference";
                header('Location: confirmation_campus.php?ref=' . $reference);
            } else {
                $_SESSION['success'] = "Demande enregistrée en brouillon. Référence : $reference";
                header('Location: formulaire_campus.php?saved=' . $demande_id);
            }
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur lors de l'enregistrement : " . $e->getMessage();
            header('Location: formulaire_campus.php?error=1');
            exit;
        }
    } else {
        $_SESSION['errors'] = $errors;
        header('Location: formulaire_campus.php?error=validation');
        exit;
    }
}
?>