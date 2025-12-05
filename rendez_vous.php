<?php

// Fichier: rendez_vous.php
session_start();

// Configuration de la base de données
require_once __DIR__ . '../config.php';
// Initialisation des variables
$errors = [];
$success = false;
$rendez_vous_id = null;
$reference = '';


// Fonction de validation des entrées
function test_input($data) {
    if (empty($data)) return '';
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fonction pour générer un numéro de référence unique
function generateReference() {
    return 'RDV-' . date('Ymd-His') . '-' . strtoupper(substr(uniqid(), -6));
}

// Fonction pour gérer l'upload de fichiers
function uploadFile($file, $uploadDir = 'uploads/') {
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    
    // Vérification du type de fichier
    $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        throw new Exception("Type de fichier non autorisé. Formats acceptés: JPG, PNG, PDF");
    }
    
    // Vérification de la taille (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("Le fichier est trop volumineux. Taille maximale: 5MB");
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $targetPath;
    } else {
        throw new Exception("Erreur lors de l'upload du fichier");
    }
}

// DEBUG: Activer l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Formulaire soumis - Début du traitement");
    
    // Validation des champs obligatoires
    $required_fields = [
        'pays', 'type_demande', 'type_client', 'motif_voyage',
        'nom', 'prenom', 'date_naissance', 'nationalite',
        'email', 'telephone', 'adresse',
        'type_hebergement', 'adresse_hebergement',
        'date_arrivee', 'date_depart'
    ];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "Le champ " . ucfirst(str_replace('_', ' ', $field)) . " est obligatoire.";
        }
    }
    
    // Validation spécifique des champs
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email n'est pas valide.";
    }
    
    // Validation des dates
    if (!empty($_POST['date_arrivee']) && !empty($_POST['date_depart'])) {
        $date_arrivee = new DateTime($_POST['date_arrivee']);
        $date_depart = new DateTime($_POST['date_depart']);
        
        if ($date_depart <= $date_arrivee) {
            $errors[] = "La date de départ doit être postérieure à la date d'arrivée.";
        }
    }
    
    // Validation du nombre de personnes pour famille/groupe
    if (isset($_POST['type_client']) && $_POST['type_client'] !== 'individuel') {
        if (empty($_POST['nombre_personnes']) || $_POST['nombre_personnes'] < 1) {
            $errors[] = "Veuillez spécifier un nombre valide de personnes.";
        }
    }
    
    // Si pas d'erreurs, sauvegarde en base
    if (empty($errors)) {
        $pdo = connectDB();
        
        if ($pdo) {
            try {
                // Vérifier que la table existe
                $tableExists = $pdo->query("SHOW TABLES LIKE 'rendez_vous'")->rowCount() > 0;
                if (!$tableExists) {
                    // Créer la table si elle n'existe pas
                    $createTableSQL = "
                    CREATE TABLE rendez_vous (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        reference VARCHAR(50) NOT NULL UNIQUE,
                        pays_destination VARCHAR(100) NOT NULL,
                        type_demande ENUM('premiere_demande', 'renouvellement') NOT NULL,
                        type_client ENUM('individuel', 'famille', 'groupe') NOT NULL,
                        nombre_personnes INT DEFAULT 1,
                        motif_voyage VARCHAR(100) NOT NULL,
                        nom VARCHAR(255) NOT NULL,
                        prenom VARCHAR(255) NOT NULL,
                        date_naissance DATE NOT NULL,
                        nationalite VARCHAR(100) NOT NULL,
                        email VARCHAR(255) NOT NULL,
                        telephone VARCHAR(50) NOT NULL,
                        adresse TEXT NOT NULL,
                        type_hebergement VARCHAR(100) NOT NULL,
                        adresse_hebergement TEXT NOT NULL,
                        date_arrivee DATE NOT NULL,
                        date_depart DATE NOT NULL,
                        username VARCHAR(100),
                        password_hash VARCHAR(255),
                        question_securite VARCHAR(255),
                        reponse_securite_hash VARCHAR(255),
                        fichiers_passeports TEXT,
                        fichiers_identite TEXT,
                        fichiers_visa TEXT,
                        statut ENUM('en_attente', 'confirme', 'annule') DEFAULT 'en_attente',
                        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
                        date_maj DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )";
                    $pdo->exec($createTableSQL);
                    error_log("Table rendez_vous créée avec succès");
                }
                
                // Génération du numéro de référence
                $reference = generateReference();
                
                // Gestion des fichiers uploadés
                $fichiers_passeports = [];
                $fichiers_identite = [];
                $fichiers_visa = [];
                
                // Upload des passeports pour famille/groupe
                if (isset($_POST['type_client']) && $_POST['type_client'] !== 'individuel') {
                    $nombre_personnes = intval($_POST['nombre_personnes']);
                    for ($i = 1; $i <= $nombre_personnes; $i++) {
                        if (isset($_FILES["passeport_$i"]) && $_FILES["passeport_$i"]['error'] === 0) {
                            $fichiers_passeports["personne_$i"] = uploadFile($_FILES["passeport_$i"]);
                        }
                    }
                } else {
                    // Upload pour individu
                    if (isset($_FILES['passeport_1']) && $_FILES['passeport_1']['error'] === 0) {
                        $fichiers_passeports["personne_1"] = uploadFile($_FILES['passeport_1']);
                    }
                }
                
                // Upload selfie et carte d'identité/habitation pour Espagne/Italie et USA/UK
                $pays_avec_identite = ['espagne', 'italie', 'usa', 'royaume_uni'];
                if (isset($_POST['pays']) && in_array($_POST['pays'], $pays_avec_identite)) {
                    if (isset($_FILES['selfie']) && $_FILES['selfie']['error'] === 0) {
                        $fichiers_identite['selfie'] = uploadFile($_FILES['selfie']);
                    }
                    if (isset($_FILES['carte_identite']) && $_FILES['carte_identite']['error'] === 0) {
                        $fichiers_identite['carte_identite'] = uploadFile($_FILES['carte_identite']);
                    }
                }
                
                // Upload des visas précédents pour renouvellement
                if (isset($_POST['type_demande']) && $_POST['type_demande'] === 'renouvellement') {
                    if (isset($_FILES['visa_precedent']) && $_FILES['visa_precedent']['error'] === 0) {
                        $fichiers_visa['visa_precedent'] = uploadFile($_FILES['visa_precedent']);
                    }
                }
                
                // Gestion des champs de sécurité pour USA/UK
                $username = !empty($_POST['username']) ? test_input($_POST['username']) : null;
                $password_hash = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
                $question_securite = !empty($_POST['question_securite']) ? test_input($_POST['question_securite']) : null;
                $reponse_securite_hash = !empty($_POST['reponse_securite']) ? password_hash($_POST['reponse_securite'], PASSWORD_DEFAULT) : null;
                
                // Préparation de la requête d'insertion
                $sql = "INSERT INTO rendez_vous (
                    reference, pays_destination, type_demande, type_client, nombre_personnes,
                    motif_voyage, nom, prenom, date_naissance, nationalite, email, telephone,
                    adresse, type_hebergement, adresse_hebergement, date_arrivee, date_depart,
                    username, password_hash, question_securite, reponse_securite_hash,
                    fichiers_passeports, fichiers_identite, fichiers_visa,
                    statut, date_creation, date_maj
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW(), NOW())";
                
                $stmt = $pdo->prepare($sql);
                
                // Nettoyage et insertion des données
                $nombre_personnes = ($_POST['type_client'] === 'individuel') ? 1 : intval($_POST['nombre_personnes']);
                
                $stmt->execute([
                    $reference,
                    test_input($_POST['pays']),
                    test_input($_POST['type_demande']),
                    test_input($_POST['type_client']),
                    $nombre_personnes,
                    test_input($_POST['motif_voyage']),
                    test_input($_POST['nom']),
                    test_input($_POST['prenom']),
                    test_input($_POST['date_naissance']),
                    test_input($_POST['nationalite']),
                    test_input($_POST['email']),
                    test_input($_POST['telephone']),
                    test_input($_POST['adresse']),
                    test_input($_POST['type_hebergement']),
                    test_input($_POST['adresse_hebergement']),
                    test_input($_POST['date_arrivee']),
                    test_input($_POST['date_depart']),
                    $username,
                    $password_hash,
                    $question_securite,
                    $reponse_securite_hash,
                    json_encode($fichiers_passeports),
                    json_encode($fichiers_identite),
                    json_encode($fichiers_visa)
                ]);
                
                $rendez_vous_id = $pdo->lastInsertId();
                $success = true;
                
                error_log("SUCCÈS - Rendez-vous créé: ID $rendez_vous_id, Référence: $reference");
                
            } catch(PDOException $e) {
                error_log("ERREUR BD: " . $e->getMessage());
                $errors[] = "Erreur lors de la sauvegarde des données: " . $e->getMessage();
            } catch(Exception $e) {
                error_log("ERREUR UPLOAD: " . $e->getMessage());
                $errors[] = "Erreur lors de l'upload des fichiers: " . $e->getMessage();
            }
        } else {
            $errors[] = "Impossible de se connecter à la base de données";
        }
    } else {
        error_log("ERREURS VALIDATION: " . implode(", ", $errors));
    }
    
    // Gestion de la réponse
    if ($success) {
        $_SESSION['success_message'] = "Votre demande de rendez-vous a été soumise avec succès!";
        $_SESSION['reference'] = $reference;
        $_SESSION['rendez_vous_id'] = $rendez_vous_id;
        
        header('Location: confirmation_rendez_vous.php');
        exit();
    } else {
        // Stocker les erreurs pour les afficher dans le formulaire
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_data'] = $_POST;
        // Rester sur la même page pour afficher les erreurs
    }
}

// Récupérer les erreurs et données du formulaire depuis la session
$form_errors = $_SESSION['form_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];

// Nettoyer la session après récupération
unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Rendez-vous Visa - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
         :root {
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --primary-dark: #1d4ed8;
            --primary-ultra-light: #dbeafe;
            --white: #ffffff;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --border: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius: 12px;
            --radius-sm: 8px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--white);
            min-height: 100vh;
            padding: 40px 20px;
            line-height: 1.6;
            color: var(--dark);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: var(--dark);
        }

        .header h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            font-size: 1.2rem;
            color: var(--gray);
        }

        .security-badge {
            background: var(--primary-ultra-light);
            color: var(--primary-dark);
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--primary-light);
        }

        .form-container {
            background: var(--white);
            padding: 40px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--border);
        }

        .card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            transition: var(--transition);
            background: var(--white);
            overflow: hidden;
        }

        .card:hover {
            box-shadow: 0 8px 25px -8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            padding: 20px 25px;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .form-control {
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 12px 16px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            transform: translateY(-1px);
        }

        .form-control:hover {
            border-color: var(--primary-light);
        }

        .form-check-input {
            width: 1.2em;
            height: 1.2em;
            margin-top: 0.15em;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            font-weight: 500;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            border-radius: var(--radius-sm);
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
        }

        .alert {
            border: none;
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 30px;
        }

        .alert-danger {
            background: var(--primary-ultra-light);
            color: var(--primary-dark);
            border-left: 4px solid var(--primary);
        }

        .required {
            color: var(--primary-dark);
            font-weight: 600;
        }

        .optional {
            color: var(--gray);
            font-weight: 500;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Animation pour les erreurs */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .error {
            color: var(--primary-dark);
            font-size: 0.85rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
            animation: shake 0.5s ease-in-out;
        }

        /* Style pour les groupes de boutons radio */
        .radio-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .radio-card {
            flex: 1;
            min-width: 140px;
        }

        .radio-card input[type="radio"] {
            display: none;
        }

        .radio-card label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
            font-weight: 500;
            background: var(--white);
        }

        .radio-card label:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .radio-card input[type="radio"]:checked + label {
            border-color: var(--primary);
            background: var(--primary-ultra-light);
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        /* Style pour les selects */
        select.form-control {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%232563eb' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        /* Style pour les fichiers */
        .file-upload-info {
            font-size: 0.875rem;
            color: var(--gray);
            margin-top: 5px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }
            
            .form-container {
                padding: 25px 20px;
            }
            
            .header h1 {
                font-size: 2.2rem;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .radio-group {
                flex-direction: column;
            }
            
            .radio-card {
                min-width: auto;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.8rem;
            }
            
            .form-container {
                padding: 20px 15px;
            }
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Success message */
        .success-message {
            background: var(--primary-ultra-light);
            color: var(--primary-dark);
            border-left: 4px solid var(--primary);
            padding: 20px;
            border-radius: var(--radius-sm);
            margin-bottom: 20px;
        }

        /* Style pour les sections conditionnelles */
        .conditional-section {
            transition: all 0.3s ease-in-out;
        }
    </style>
   </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-passport"></i> Demande Rendez-vous</h1>
            <p>Babylone Service - Demande de rendez-vous simplifiée</p>
        </div>
        
        <div class="form-container">
            <?php if (!empty($form_errors)): ?>
                <div class="alert alert-danger">
                    <h4><i class="fas fa-exclamation-triangle"></i> Erreurs de validation:</h4>
                    <ul class="mb-0">
                        <?php foreach ($form_errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="rendez_vous.php" id="visaForm" enctype="multipart/form-data">
                <!-- Section Informations Personnelles -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-user"></i> Informations Personnelles</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nom" class="form-label">Nom <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nom" name="nom" 
                                           value="<?php echo htmlspecialchars($form_data['nom'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="prenom" class="form-label">Prénom <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="prenom" name="prenom"
                                           value="<?php echo htmlspecialchars($form_data['prenom'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_naissance" class="form-label">Date de naissance <span class="required">*</span></label>
                                    <input type="date" class="form-control" id="date_naissance" name="date_naissance"
                                           value="<?php echo htmlspecialchars($form_data['date_naissance'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nationalite" class="form-label">Nationalité <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="nationalite" name="nationalite"
                                           value="<?php echo htmlspecialchars($form_data['nationalite'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Coordonnées -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-address-card"></i> Coordonnées</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="required">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email"
                                           value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telephone" class="form-label">Téléphone <span class="required">*</span></label>
                                    <input type="tel" class="form-control" id="telephone" name="telephone"
                                           value="<?php echo htmlspecialchars($form_data['telephone'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label">Adresse <span class="required">*</span></label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="3" required><?php echo htmlspecialchars($form_data['adresse'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section Destination -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-globe-americas"></i> Destination et Type de Demande</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="pays" class="form-label">Pays de destination <span class="required">*</span></label>
                            <select class="form-control" id="pays" name="pays" required>
                                <option value="">Sélectionnez un pays</option>
                                <option value="france" <?php echo ($form_data['pays'] ?? '') === 'france' ? 'selected' : ''; ?>>🇫🇷 France</option>
                                <option value="espagne" <?php echo ($form_data['pays'] ?? '') === 'espagne' ? 'selected' : ''; ?>>🇪🇸 Espagne</option>
                                <option value="italie" <?php echo ($form_data['pays'] ?? '') === 'italie' ? 'selected' : ''; ?>>🇮🇹 Italie</option>
                                <option value="allemagne" <?php echo ($form_data['pays'] ?? '') === 'allemagne' ? 'selected' : ''; ?>>🇩🇪 Allemagne</option>
                                <option value="belgique" <?php echo ($form_data['pays'] ?? '') === 'belgique' ? 'selected' : ''; ?>>🇧🇪 Belgique</option>
                                <option value="usa" <?php echo ($form_data['pays'] ?? '') === 'usa' ? 'selected' : ''; ?>>🇺🇸 États-Unis</option>
                                <option value="royaume_uni" <?php echo ($form_data['pays'] ?? '') === 'royaume_uni' ? 'selected' : ''; ?>>🇬🇧 Royaume-Uni</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Type de demande <span class="required">*</span></label>
                            <div class="radio-group">
                                <div class="radio-card">
                                    <input class="form-check-input" type="radio" id="premiere_demande" name="type_demande" value="premiere_demande" 
                                           <?php echo ($form_data['type_demande'] ?? 'premiere_demande') === 'premiere_demande' ? 'checked' : ''; ?> required>
                                    <label for="premiere_demande">
                                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                                        Première demande
                                    </label>
                                </div>
                                <div class="radio-card">
                                    <input class="form-check-input" type="radio" id="renouvellement" name="type_demande" value="renouvellement"
                                           <?php echo ($form_data['type_demande'] ?? '') === 'renouvellement' ? 'checked' : ''; ?>>
                                    <label for="renouvellement">
                                        <i class="fas fa-sync-alt fa-2x mb-2"></i>
                                        Renouvellement
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Type de client <span class="required">*</span></label>
                            <div class="radio-group">
                                <div class="radio-card">
                                    <input class="form-check-input" type="radio" id="individuel" name="type_client" value="individuel" 
                                           <?php echo ($form_data['type_client'] ?? 'individuel') === 'individuel' ? 'checked' : ''; ?> required>
                                    <label for="individuel">
                                        <i class="fas fa-user fa-2x mb-2"></i>
                                        Individuel
                                    </label>
                                </div>
                                <div class="radio-card">
                                    <input class="form-check-input" type="radio" id="famille" name="type_client" value="famille"
                                           <?php echo ($form_data['type_client'] ?? '') === 'famille' ? 'checked' : ''; ?>>
                                    <label for="famille">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        Famille
                                    </label>
                                </div>
                                <div class="radio-card">
                                    <input class="form-check-input" type="radio" id="groupe" name="type_client" value="groupe"
                                           <?php echo ($form_data['type_client'] ?? '') === 'groupe' ? 'checked' : ''; ?>>
                                    <label for="groupe">
                                        <i class="fas fa-user-friends fa-2x mb-2"></i>
                                        Groupe
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="nombre_personnes_container" style="display: none;">
                            <label for="nombre_personnes" class="form-label">Nombre de personnes <span class="required">*</span></label>
                            <input type="number" class="form-control" id="nombre_personnes" name="nombre_personnes" 
                                   value="<?php echo htmlspecialchars($form_data['nombre_personnes'] ?? '1'); ?>" min="1">
                        </div>
                        
                        <div class="mb-3">
                            <label for="motif_voyage" class="form-label">Motif de voyage <span class="required">*</span></label>
                            <select class="form-control" id="motif_voyage" name="motif_voyage" required>
                                <option value="">Sélectionnez un motif</option>
                                <option value="tourisme" <?php echo ($form_data['motif_voyage'] ?? '') === 'tourisme' ? 'selected' : ''; ?>>🏖️ Tourisme</option>
                                <option value="affaires" <?php echo ($form_data['motif_voyage'] ?? '') === 'affaires' ? 'selected' : ''; ?>>💼 Affaires</option>
                                <option value="etudes" <?php echo ($form_data['motif_voyage'] ?? '') === 'etudes' ? 'selected' : ''; ?>>🎓 Études</option>
                                <option value="visite_familiale" <?php echo ($form_data['motif_voyage'] ?? '') === 'visite_familiale' ? 'selected' : ''; ?>>👨‍👩‍👧‍👦 Visite familiale</option>
                                <option value="travail" <?php echo ($form_data['motif_voyage'] ?? '') === 'travail' ? 'selected' : ''; ?>>💻 Travail</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Section Compte Sécurisé (USA/UK) -->
                <div class="card" id="compteSection" style="display: none;">
                    <div class="card-header">
                        <h5><i class="fas fa-user-shield"></i> Compte Sécurisé Optionnel</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Optionnel :</strong> Créez un compte sécurisé pour suivre plus facilement votre demande USA/UK.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Nom d'utilisateur <span class="optional">(optionnel)</span></label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           value="<?php echo htmlspecialchars($form_data['username'] ?? ''); ?>">
                                    <div class="file-upload-info">Minimum 4 caractères</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mot de passe <span class="optional">(optionnel)</span></label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div class="file-upload-info">Minimum 8 caractères</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="question_securite" class="form-label">Question de sécurité <span class="optional">(optionnel)</span></label>
                                    <select class="form-control" id="question_securite" name="question_securite">
                                        <option value="">Sélectionnez une question</option>
                                        <option value="nom_animal" <?php echo ($form_data['question_securite'] ?? '') === 'nom_animal' ? 'selected' : ''; ?>>Quel est le nom de votre animal de compagnie ?</option>
                                        <option value="ville_naissance" <?php echo ($form_data['question_securite'] ?? '') === 'ville_naissance' ? 'selected' : ''; ?>>Dans quelle ville êtes-vous né(e) ?</option>
                                        <option value="premier_ecole" <?php echo ($form_data['question_securite'] ?? '') === 'premier_ecole' ? 'selected' : ''; ?>>Quel est le nom de votre première école ?</option>
                                        <option value="film_prefere" <?php echo ($form_data['question_securite'] ?? '') === 'film_prefere' ? 'selected' : ''; ?>>Quel est votre film préféré ?</option>
                                        <option value="personnage_historique" <?php echo ($form_data['question_securite'] ?? '') === 'personnage_historique' ? 'selected' : ''; ?>>Quel est votre personnage historique préféré ?</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reponse_securite" class="form-label">Réponse de sécurité <span class="optional">(optionnel)</span></label>
                                    <input type="text" class="form-control" id="reponse_securite" name="reponse_securite" 
                                           value="<?php echo htmlspecialchars($form_data['reponse_securite'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Hébergement -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-hotel"></i> Hébergement</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="type_hebergement" class="form-label">Type d'hébergement <span class="required">*</span></label>
                            <select class="form-control" id="type_hebergement" name="type_hebergement" required>
                                <option value="">Sélectionnez un type</option>
                                <option value="hotel" <?php echo ($form_data['type_hebergement'] ?? '') === 'hotel' ? 'selected' : ''; ?>>🏨 Hôtel</option>
                                <option value="chez_amis" <?php echo ($form_data['type_hebergement'] ?? '') === 'chez_amis' ? 'selected' : ''; ?>>🏠 Chez des amis/famille</option>
                                <option value="location" <?php echo ($form_data['type_hebergement'] ?? '') === 'location' ? 'selected' : ''; ?>>🏡 Location</option>
                                <option value="auberge" <?php echo ($form_data['type_hebergement'] ?? '') === 'auberge' ? 'selected' : ''; ?>>🛏️ Auberge de jeunesse</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse_hebergement" class="form-label">Adresse de l'hébergement <span class="required">*</span></label>
                            <textarea class="form-control" id="adresse_hebergement" name="adresse_hebergement" rows="3" required><?php echo htmlspecialchars($form_data['adresse_hebergement'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section Dates de Séjour -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar-alt"></i> Dates de Séjour</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_arrivee" class="form-label">Date d'arrivée <span class="required">*</span></label>
                                    <input type="date" class="form-control" id="date_arrivee" name="date_arrivee"
                                           value="<?php echo htmlspecialchars($form_data['date_arrivee'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date_depart" class="form-label">Date de départ <span class="required">*</span></label>
                                    <input type="date" class="form-control" id="date_depart" name="date_depart"
                                           value="<?php echo htmlspecialchars($form_data['date_depart'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Documents Passeport (dynamique) -->
                <div class="card" id="passeportsSection">
                    <div class="card-header">
                        <h5><i class="fas fa-passport"></i> Documents Passeport</h5>
                    </div>
                    <div class="card-body">
                        <div id="passeportsContainer">
                            <div class="mb-3">
                                <label for="passeport_1" class="form-label">Passeport <span class="required">*</span></label>
                                <input type="file" class="form-control" id="passeport_1" name="passeport_1" accept="image/*,.pdf" required>
                                <div class="file-upload-info">Format: JPG, PNG, PDF (max 5MB)</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Documents Identité (pour Espagne/Italie/USA/UK) -->
                <div class="card" id="identiteSection" style="display: none;">
                    <div class="card-header">
                        <h5><i class="fas fa-id-card"></i> Documents d'Identité Supplémentaires</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="selfie" class="form-label">Photo Selfie <span class="required">*</span></label>
                            <input type="file" class="form-control" id="selfie" name="selfie" accept="image/*">
                            <div class="file-upload-info">Format: JPG, PNG (max 5MB)</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="carte_identite" class="form-label"> Carte d'Habitation / CIB <span class="required">*</span></label>
                            <input type="file" class="form-control" id="carte_identite" name="carte_identite" accept="image/*,.pdf">
                            <div class="file-upload-info">Recto et verso dans le même fichier PDF, ou photo des deux côtés. Format: JPG, PNG, PDF (max 5MB)</div>
                        </div>
                    </div>
                </div>

                <!-- Section Visa Précédent (pour renouvellement) -->
                <div class="card" id="visaSection" style="display: none;">
                    <div class="card-header">
                        <h5><i class="fas fa-stamp"></i> Visa Précédent</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="visa_precedent" class="form-label">Scan du visa précédent <span class="required">*</span></label>
                            <input type="file" class="form-control" id="visa_precedent" name="visa_precedent" accept="image/*,.pdf">
                            <div class="file-upload-info">Format: JPG, PNG, PDF (max 5MB)</div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>
                        Soumettre la demande
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gestion de l'affichage du champ nombre de personnes
        function toggleNombrePersonnes() {
            const typeClient = document.querySelector('input[name="type_client"]:checked');
            const container = document.getElementById('nombre_personnes_container');
            
            if (typeClient && typeClient.value === 'individuel') {
                container.style.display = 'none';
                document.getElementById('nombre_personnes').value = '1';
                generatePassportFields(1);
            } else {
                container.style.display = 'block';
                const nbPersonnes = document.getElementById('nombre_personnes').value || '1';
                generatePassportFields(parseInt(nbPersonnes));
            }
        }

        // Génération des champs de passeport
        function generatePassportFields(nbPersonnes) {
            const container = document.getElementById('passeportsContainer');
            container.innerHTML = '';
            
            for (let i = 1; i <= nbPersonnes; i++) {
                const div = document.createElement('div');
                div.className = 'mb-3';
                div.innerHTML = `
                    <label for="passeport_${i}" class="form-label">Passeport Personne ${i} <span class="required">*</span></label>
                    <input type="file" class="form-control" id="passeport_${i}" name="passeport_${i}" accept="image/*,.pdf" required>
                    <div class="file-upload-info">Format: JPG, PNG, PDF (max 5MB)</div>
                `;
                container.appendChild(div);
            }
        }

        // Gestion de l'affichage des sections documents identité
        function toggleIdentiteSection() {
            const pays = document.getElementById('pays').value;
            const identiteSection = document.getElementById('identiteSection');
            const paysAvecIdentite = ['espagne', 'italie', 'usa', 'royaume_uni'];
            
            if (paysAvecIdentite.includes(pays)) {
                identiteSection.style.display = 'block';
                // Rendre les champs obligatoires
                document.getElementById('selfie').required = true;
                document.getElementById('carte_identite').required = true;
            } else {
                identiteSection.style.display = 'none';
                // Rendre les champs facultatifs
                document.getElementById('selfie').required = false;
                document.getElementById('carte_identite').required = false;
            }
        }

        // Gestion de l'affichage de la section compte sécurisé
        function toggleCompteSection() {
            const pays = document.getElementById('pays').value;
            const compteSection = document.getElementById('compteSection');
            const paysAvecCompte = ['usa', 'royaume_uni'];
            
            if (paysAvecCompte.includes(pays)) {
                compteSection.style.display = 'block';
            } else {
                compteSection.style.display = 'none';
                // Réinitialiser les champs optionnels
                document.getElementById('username').value = '';
                document.getElementById('password').value = '';
                document.getElementById('question_securite').value = '';
                document.getElementById('reponse_securite').value = '';
            }
        }

        // Gestion de l'affichage de la section visa
        function toggleVisaSection() {
            const typeDemande = document.querySelector('input[name="type_demande"]:checked');
            const visaSection = document.getElementById('visaSection');
            
            if (typeDemande && typeDemande.value === 'renouvellement') {
                visaSection.style.display = 'block';
                document.getElementById('visa_precedent').required = true;
            } else {
                visaSection.style.display = 'none';
                document.getElementById('visa_precedent').required = false;
            }
        }

        // Validation des dates
        function validateDates() {
            const dateArrivee = document.getElementById('date_arrivee').value;
            const dateDepart = document.getElementById('date_depart').value;
            
            if (dateArrivee && dateDepart) {
                const arrivee = new Date(dateArrivee);
                const depart = new Date(dateDepart);
                
                if (depart <= arrivee) {
                    alert('La date de départ doit être postérieure à la date d\'arrivée.');
                    return false;
                }
            }
            return true;
        }

        // Validation des fichiers
        function validateFiles() {
            const typeClient = document.querySelector('input[name="type_client"]:checked');
            const pays = document.getElementById('pays').value;
            const typeDemande = document.querySelector('input[name="type_demande"]:checked');
            
            // Validation des passeports
            if (typeClient && typeClient.value !== 'individuel') {
                const nbPersonnes = parseInt(document.getElementById('nombre_personnes').value);
                for (let i = 1; i <= nbPersonnes; i++) {
                    const fileInput = document.getElementById(`passeport_${i}`);
                    if (fileInput && !fileInput.files[0]) {
                        alert(`Veuillez télécharger le passeport pour la personne ${i}`);
                        return false;
                    }
                }
            } else {
                const fileInput = document.getElementById('passeport_1');
                if (fileInput && !fileInput.files[0]) {
                    alert('Veuillez télécharger votre passeport');
                    return false;
                }
            }
            
            // Validation des documents identité pour Espagne/Italie/USA/UK
            const paysAvecIdentite = ['espagne', 'italie', 'usa', 'royaume_uni'];
            if (paysAvecIdentite.includes(pays)) {
                const selfie = document.getElementById('selfie');
                const carteIdentite = document.getElementById('carte_identite');
                
                if (!selfie.files[0] || !carteIdentite.files[0]) {
                    alert('Veuillez télécharger tous les documents d\'identité requis');
                    return false;
                }
            }
            
            // Validation du visa précédent pour renouvellement
            if (typeDemande && typeDemande.value === 'renouvellement') {
                const visaFile = document.getElementById('visa_precedent');
                if (!visaFile.files[0]) {
                    alert('Veuillez télécharger le scan de votre visa précédent');
                    return false;
                }
            }
            
            return true;
        }

        // Validation du formulaire
        function validateForm() {
            if (!validateDates()) {
                return false;
            }
            
            if (!validateFiles()) {
                return false;
            }
            
            // Validation supplémentaire si nécessaire
            const typeClient = document.querySelector('input[name="type_client"]:checked');
            if (typeClient && typeClient.value !== 'individuel') {
                const nbPersonnes = document.getElementById('nombre_personnes').value;
                if (!nbPersonnes || nbPersonnes < 1) {
                    alert('Veuillez spécifier un nombre valide de personnes.');
                    return false;
                }
            }
            
            return true;
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            toggleNombrePersonnes();
            toggleIdentiteSection();
            toggleCompteSection();
            toggleVisaSection();
            
            // Écouteurs d'événements
            document.querySelectorAll('input[name="type_client"]').forEach(function(radio) {
                radio.addEventListener('change', toggleNombrePersonnes);
            });
            
            document.getElementById('nombre_personnes').addEventListener('change', function() {
                const nbPersonnes = parseInt(this.value) || 1;
                generatePassportFields(nbPersonnes);
            });
            
            document.getElementById('pays').addEventListener('change', function() {
                toggleIdentiteSection();
                toggleCompteSection();
            });
            
            document.querySelectorAll('input[name="type_demande"]').forEach(function(radio) {
                radio.addEventListener('change', toggleVisaSection);
            });
            
            // Validation avant soumission
            document.getElementById('visaForm').addEventListener('submit', function(e) {
                if (!validateForm()) {
                    e.preventDefault();
                }
            });
            
            // Empêcher les dates passées
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date_arrivee').min = today;
            document.getElementById('date_depart').min = today;
        });
    </script>
</body>
</html>