<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? '';

// Initialiser les variables
$error_message = '';
$success_message = '';

require_once __DIR__ . '/../../config.php';

// Vérifier la connexion
if (!isset($conn) || $conn->connect_error) {
    die("Erreur de connexion à la base de données: " . ($conn->connect_error ?? 'Connexion non définie'));
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Générer un numéro de dossier unique
    $numero_dossier = "RF-" . date('Ymd-His') . "-" . rand(1000, 9999);
    
    // Récupérer et échapper les données du formulaire
    $nom_complet = $conn->real_escape_string($_POST['nom_complet'] ?? '');
    $date_naissance = $conn->real_escape_string($_POST['date_naissance'] ?? '');
    $nationalite = $conn->real_escape_string($_POST['nationalite'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $telephone = $conn->real_escape_string($_POST['telephone'] ?? '');
    $nom_famille = $conn->real_escape_string($_POST['nom_famille'] ?? '');
    $lien_parente = $conn->real_escape_string($_POST['lien_parente'] ?? '');
    $adresse_famille = $conn->real_escape_string($_POST['adresse_famille'] ?? '');
    $commentaire = $conn->real_escape_string($_POST['commentaire'] ?? '');
    
    // Validation des champs requis
    $errors = [];
    if (empty($nom_complet)) $errors[] = "Le nom complet est requis";
    if (empty($date_naissance)) $errors[] = "La date de naissance est requise";
    if (empty($nationalite)) $errors[] = "La nationalité est requise";
    if (empty($email)) $errors[] = "L'email est requis";
    if (empty($telephone)) $errors[] = "Le téléphone est requis";
    if (empty($nom_famille)) $errors[] = "Le nom du membre de famille est requis";
    if (empty($lien_parente)) $errors[] = "Le lien de parenté est requis";
    if (empty($adresse_famille)) $errors[] = "L'adresse en France est requise";
    
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
    } else {
        // Gestion des fichiers uploadés
        $upload_errors = [];
        $passeport = uploadFile('passeport', $numero_dossier);
        $titre_sejour = uploadFile('titre_sejour', $numero_dossier);
        $acte_mariage = uploadFile('acte_mariage', $numero_dossier);
        $justificatif_logement = uploadFile('justificatif_logement', $numero_dossier);
        $ressources = uploadFile('ressources', $numero_dossier);
        $paiement = uploadFile('paiement', $numero_dossier);
        
        // Gestion des fichiers multiples
        $preuves_liens = uploadMultipleFiles('preuves_liens', $numero_dossier);
        
        // Vérifier si les documents requis sont présents
        if (empty($passeport)) $upload_errors[] = "Le passeport est requis";
        if (empty($titre_sejour)) $upload_errors[] = "Le titre de séjour est requis";
        
        if (!empty($upload_errors)) {
            $error_message = "Documents manquants:<br>" . implode('<br>', $upload_errors);
        } else {
            try {
                // Vérifier si la table existe, sinon la créer
                createTableIfNotExists($conn);
                
                // Démarrer une transaction
                $conn->begin_transaction();
                
                // Préparer et exécuter la requête SQL
                $sql = "INSERT INTO demandes_regroupement_familial (
                    user_id, numero_dossier, nom_complet, date_naissance, nationalite, email, telephone, 
                    nom_famille, lien_parente, adresse_famille, commentaire, statut, date_creation
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'nouveau', NOW())";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param(
                    "issssssssss",
                    $user_id,
                    $numero_dossier,
                    $nom_complet,
                    $date_naissance,
                    $nationalite,
                    $email,
                    $telephone,
                    $nom_famille,
                    $lien_parente,
                    $adresse_famille,
                    $commentaire
                );
                
                if ($stmt->execute()) {
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
                    
                    // Valider la transaction
                    $conn->commit();
                    
                    // Rediriger vers la page des demandes avec le numéro de dossier
                    header("Location: mes_demandes_regroupement_familial.php?success=1&dossier=" . urlencode($numero_dossier));
                    exit();
                } else {
                    $conn->rollback();
                    $error_message = "Erreur lors de l'enregistrement: " . $conn->error;
                }
                
                $stmt->close();
                
            } catch (Exception $e) {
                $conn->rollback();
                $error_message = "Erreur: " . $e->getMessage();
            }
        }
    }
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
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        die("Erreur création table demandes: " . $conn->error);
    }
    
    // Créer aussi la table des documents
    $sql_docs = "CREATE TABLE IF NOT EXISTS documents_regroupement_familial (
        id INT PRIMARY KEY AUTO_INCREMENT,
        demande_id INT NOT NULL,
        type_document VARCHAR(100) NOT NULL,
        nom_fichier VARCHAR(255) NOT NULL,
        chemin_fichier VARCHAR(500) NOT NULL,
        date_upload DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (demande_id) REFERENCES demandes_regroupement_familial(id) ON DELETE CASCADE,
        INDEX idx_demande_id (demande_id),
        INDEX idx_type_document (type_document)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql_docs)) {
        die("Erreur création table documents: " . $conn->error);
    }
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
    
    $stmt = $conn->prepare("
        INSERT INTO documents_regroupement_familial 
        (demande_id, type_document, nom_fichier, chemin_fichier) 
        VALUES (?, ?, ?, ?)
    ");
    
    foreach ($documents as $type => $fichiers) {
        if (!empty($fichiers)) {
            $fichiers_array = explode(',', $fichiers);
            foreach ($fichiers_array as $fichier) {
                if (!empty($fichier)) {
                    $type_document = $types_documents[$type] ?? $type;
                    $chemin_fichier = "uploads/regroupement_familial/" . $fichier;
                    
                    $stmt->bind_param("isss", $demande_id, $type_document, $fichier, $chemin_fichier);
                    $stmt->execute();
                }
            }
        }
    }
    $stmt->close();
}

// Fonction pour uploader un fichier unique
function uploadFile($fieldName, $numero_dossier) {
    if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../uploads/regroupement_familial/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Vérifier le type de fichier
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        $fileType = mime_content_type($_FILES[$fieldName]['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            return '';
        }
        
        // Vérifier la taille (max 5MB)
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($_FILES[$fieldName]['size'] > $maxSize) {
            return '';
        }
        
        $fileExtension = pathinfo($_FILES[$fieldName]['name'], PATHINFO_EXTENSION);
        $fileName = $numero_dossier . '_' . $fieldName . '_' . time() . '.' . strtolower($fileExtension);
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
        $uploadDir = __DIR__ . '/../../uploads/regroupement_familial/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        $fileCount = is_array($_FILES[$fieldName]['name']) ? count($_FILES[$fieldName]['name']) : 0;
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES[$fieldName]['error'][$i] === UPLOAD_ERR_OK) {
                // Vérifier le type de fichier
                $fileType = mime_content_type($_FILES[$fieldName]['tmp_name'][$i]);
                
                if (!in_array($fileType, $allowedTypes)) {
                    continue;
                }
                
                // Vérifier la taille
                if ($_FILES[$fieldName]['size'][$i] > $maxSize) {
                    continue;
                }
                
                $fileExtension = pathinfo($_FILES[$fieldName]['name'][$i], PATHINFO_EXTENSION);
                $fileName = $numero_dossier . '_' . $fieldName . '_' . $i . '_' . time() . '.' . strtolower($fileExtension);
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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Regroupement Familial - France</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0055b8;
            --secondary-blue: #2c3e50;
            --accent-red: #ce1126;
            --light-blue: #e8f2ff;
            --light-gray: #f4f7fa;
            --white: #ffffff;
            --border-color: #dbe4ee;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --success-green: #28a745;
            --warning-orange: #ff9800;
            --error-red: #dc3545;
            --shadow: 0 4px 12px rgba(0, 85, 184, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-gray);
            color: var(--text-dark);
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--accent-red) 33%, var(--white) 33%, var(--white) 66%, var(--accent-red) 66%);
        }
        
        header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .breadcrumb {
            background: var(--white);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid var(--primary-blue);
        }
        
        .breadcrumb a {
            color: var(--primary-blue);
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .form-container {
            background: var(--white);
            padding: 40px;
            border-radius: 12px;
            box-shadow: var(--shadow);
        }
        
        .form-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .section-title {
            color: var(--primary-blue);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-blue);
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1.4rem;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .required::after {
            content: " *";
            color: var(--accent-red);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 1rem;
            transition: var(--transition);
            background: var(--white);
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 85, 184, 0.1);
        }
        
        textarea {
            min-height: 120px;
            resize: vertical;
        }
        
        .file-upload {
            position: relative;
            margin-bottom: 15px;
        }
        
        .file-upload input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
            z-index: 2;
        }
        
        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 15px;
            background: var(--light-blue);
            border: 2px dashed var(--primary-blue);
            border-radius: 6px;
            color: var(--primary-blue);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            text-align: center;
        }
        
        .file-upload-label:hover {
            background: #d4e3ff;
        }
        
        .file-list {
            margin-top: 10px;
            font-size: 0.9rem;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px;
            background: var(--light-gray);
            border-radius: 4px;
            margin-bottom: 5px;
        }
        
        .help-text {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 5px;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid var(--light-gray);
        }
        
        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary-blue), #0066cc);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: linear-gradient(to right, #004a9e, #0055b8);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 85, 184, 0.3);
        }
        
        .btn-secondary {
            background: var(--light-gray);
            color: var(--text-dark);
        }
        
        .btn-secondary:hover {
            background: #e2e6ea;
            transform: translateY(-2px);
        }
        
        .progress-container {
            margin: 30px 0;
        }
        
        .progress-bar {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-bottom: 30px;
        }
        
        .progress-bar::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--border-color);
            transform: translateY(-50%);
            z-index: 1;
        }
        
        .progress-step {
            position: relative;
            z-index: 2;
            background: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 3px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: var(--text-light);
        }
        
        .progress-step.active {
            border-color: var(--primary-blue);
            color: var(--primary-blue);
            background: var(--light-blue);
        }
        
        .step-label {
            position: absolute;
            top: 45px;
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            font-size: 0.85rem;
            color: var(--text-light);
        }
        
        .active .step-label {
            color: var(--primary-blue);
            font-weight: 600;
        }
        
        .form-navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 15px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .progress-bar {
                flex-wrap: wrap;
                gap: 15px;
            }
            
            .progress-step {
                width: 35px;
                height: 35px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <header>
            <h1><i class="fa-solid fa-people-group"></i> Demande de Regroupement Familial</h1>
            <p>Remplissez le formulaire ci-dessous pour soumettre votre demande de regroupement familial en France</p>
        </header>
        
        <!-- Fil d'Ariane -->
        <div class="breadcrumb">
            <a href="index.php"><i class="fa-solid fa-home"></i> Tableau de bord</a> &gt;
            <a href="mes_demandes_regroupement_familial.php"><i class="fa-solid fa-list"></i> Mes demandes</a> &gt;
            <span>Nouvelle demande</span>
        </div>
        
        <!-- Messages d'alerte -->
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-exclamation-triangle"></i>
                <div><?= $error_message ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-check-circle"></i>
                <div><?= $success_message ?></div>
            </div>
        <?php endif; ?>
        
        <!-- Formulaire -->
        <form method="POST" action="" enctype="multipart/form-data" class="form-container">
            <!-- Informations du demandeur -->
            <div class="form-section">
                <h3 class="section-title"><i class="fa-solid fa-user"></i> Informations du demandeur</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom_complet" class="required">Nom complet</label>
                        <input type="text" id="nom_complet" name="nom_complet" required
                               value="<?= htmlspecialchars($_POST['nom_complet'] ?? '') ?>"
                               placeholder="Nom et prénom">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_naissance" class="required">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance" required
                               value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="nationalite" class="required">Nationalité</label>
                        <input type="text" id="nationalite" name="nationalite" required
                               value="<?= htmlspecialchars($_POST['nationalite'] ?? '') ?>"
                               placeholder="Votre nationalité">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="email" class="required">Adresse email</label>
                        <input type="email" id="email" name="email" required
                               value="<?= htmlspecialchars($_POST['email'] ?? $user_email) ?>"
                               placeholder="votre@email.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone" class="required">Numéro de téléphone</label>
                        <input type="tel" id="telephone" name="telephone" required
                               value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>"
                               placeholder="+33 1 23 45 67 89">
                    </div>
                </div>
            </div>
            
            <!-- Informations sur le membre de famille en France -->
            <div class="form-section">
                <h3 class="section-title"><i class="fa-solid fa-house-user"></i> Membre de famille en France</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom_famille" class="required">Nom complet</label>
                        <input type="text" id="nom_famille" name="nom_famille" required
                               value="<?= htmlspecialchars($_POST['nom_famille'] ?? '') ?>"
                               placeholder="Nom et prénom du membre de famille">
                    </div>
                    
                    <div class="form-group">
                        <label for="lien_parente" class="required">Lien de parenté</label>
                        <select id="lien_parente" name="lien_parente" required>
                            <option value="">Sélectionnez un lien</option>
                            <option value="conjoint" <?= ($_POST['lien_parente'] ?? '') === 'conjoint' ? 'selected' : '' ?>>Conjoint(e)</option>
                            <option value="enfant" <?= ($_POST['lien_parente'] ?? '') === 'enfant' ? 'selected' : '' ?>>Enfant</option>
                            <option value="parent" <?= ($_POST['lien_parente'] ?? '') === 'parent' ? 'selected' : '' ?>>Parent</option>
                            <option value="autre" <?= ($_POST['lien_parente'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="adresse_famille" class="required">Adresse en France</label>
                    <textarea id="adresse_famille" name="adresse_famille" required
                              placeholder="Adresse complète du membre de famille en France (rue, code postal, ville)"><?= htmlspecialchars($_POST['adresse_famille'] ?? '') ?></textarea>
                </div>
            </div>
            
            <!-- Documents à fournir -->
            <div class="form-section">
                <h3 class="section-title"><i class="fa-solid fa-file-upload"></i> Documents à fournir</h3>
                
                <div class="help-text" style="margin-bottom: 25px;">
                    <i class="fa-solid fa-info-circle"></i>
                    Tous les documents doivent être en format PDF, JPEG ou PNG (max 5MB par fichier)
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="required">Passeport du demandeur</label>
                        <div class="file-upload">
                            <input type="file" name="passeport" id="passeport" accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="file-upload-label">
                                <i class="fa-solid fa-passport"></i>
                                <span>Choisir un fichier</span>
                            </div>
                        </div>
                        <div class="help-text">Copie du passeport en cours de validité</div>
                        <div class="file-list" id="passeport-list"></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="required">Titre de séjour</label>
                        <div class="file-upload">
                            <input type="file" name="titre_sejour" id="titre_sejour" accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="file-upload-label">
                                <i class="fa-solid fa-id-card"></i>
                                <span>Choisir un fichier</span>
                            </div>
                        </div>
                        <div class="help-text">Titre de séjour en cours de validité</div>
                        <div class="file-list" id="titre_sejour-list"></div>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Acte de mariage/naissance</label>
                        <div class="file-upload">
                            <input type="file" name="acte_mariage" id="acte_mariage" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="file-upload-label">
                                <i class="fa-solid fa-heart"></i>
                                <span>Choisir un fichier</span>
                            </div>
                        </div>
                        <div class="help-text">Acte de mariage ou acte de naissance des enfants</div>
                        <div class="file-list" id="acte_mariage-list"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Justificatif de logement</label>
                        <div class="file-upload">
                            <input type="file" name="justificatif_logement" id="justificatif_logement" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="file-upload-label">
                                <i class="fa-solid fa-house"></i>
                                <span>Choisir un fichier</span>
                            </div>
                        </div>
                        <div class="help-text">Contrat de location, facture EDF, quittance de loyer</div>
                        <div class="file-list" id="justificatif_logement-list"></div>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label>Preuves de ressources</label>
                        <div class="file-upload">
                            <input type="file" name="ressources" id="ressources" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="file-upload-label">
                                <i class="fa-solid fa-euro-sign"></i>
                                <span>Choisir un fichier</span>
                            </div>
                        </div>
                        <div class="help-text">Bulletins de salaire, avis d'imposition</div>
                        <div class="file-list" id="ressources-list"></div>
                    </div>
                    
                    <div class="form-group">
                        <label>Reçu de paiement</label>
                        <div class="file-upload">
                            <input type="file" name="paiement" id="paiement" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="file-upload-label">
                                <i class="fa-solid fa-receipt"></i>
                                <span>Choisir un fichier</span>
                            </div>
                        </div>
                        <div class="help-text">Reçu du paiement des frais de dossier</div>
                        <div class="file-list" id="paiement-list"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Preuves de liens familiaux</label>
                    <div class="help-text" style="margin-bottom: 10px;">
                        Vous pouvez sélectionner plusieurs fichiers (photos de famille, correspondance, etc.)
                    </div>
                    <div class="file-upload">
                        <input type="file" name="preuves_liens[]" id="preuves_liens" accept=".pdf,.jpg,.jpeg,.png" multiple>
                        <div class="file-upload-label">
                            <i class="fa-solid fa-images"></i>
                            <span>Choisir plusieurs fichiers</span>
                        </div>
                    </div>
                    <div class="file-list" id="preuves_liens-list"></div>
                </div>
            </div>
            
            <!-- Commentaires -->
            <div class="form-section">
                <h3 class="section-title"><i class="fa-solid fa-comment"></i> Informations complémentaires</h3>
                
                <div class="form-group">
                    <label for="commentaire">Commentaire (facultatif)</label>
                    <textarea id="commentaire" name="commentaire" 
                              placeholder="Ajoutez toute information complémentaire qui pourrait être utile pour le traitement de votre demande..."><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
                    <div class="help-text">
                        Maximum 500 caractères. Vous pouvez préciser des circonstances particulières ici.
                    </div>
                </div>
            </div>
            
            <!-- Actions du formulaire -->
            <div class="form-actions">
                <a href="mes_demandes_regroupement_familial.php" class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i> Annuler
                </a>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> Soumettre la demande
                </button>
            </div>
        </form>
    </div