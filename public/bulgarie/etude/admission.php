<?php
session_start();

// Inclure votre config.php existant
require_once '../../../config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../client/login.php");
    exit;
}

// Plus besoin de ces lignes, elles sont dans config.php
// $host = 'localhost';
// $dbname = 'babylone_service';
// $username = 'root';
// $password = '';

// Traitement du formulaire
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type']) && $_POST['type'] === 'admission') {
    try {
        // Utiliser la connexion PDO depuis config.php (si elle existe)
        // Ou créer la connexion avec vos variables
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer l'email de l'utilisateur
        $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt_user->execute([$_SESSION['user_id']]);
        $user = $stmt_user->fetch();
        $user_email = $user['email'] ?? '';

        // Gestion de l'upload des fichiers
        $upload_dir = "uploads/bulgarie/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Fonction pour uploader un fichier
        function uploadFile($file, $upload_dir) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . basename($file['name']);
                $target_path = $upload_dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    return $filename;
                }
            }
            return null;
        }

        // Fonction pour uploader plusieurs fichiers
        function uploadMultipleFiles($files, $upload_dir) {
            $uploaded_files = [];
            if (isset($files['tmp_name']) && is_array($files['tmp_name'])) {
                foreach ($files['tmp_name'] as $key => $tmp_name) {
                    if ($files['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $files['name'][$key],
                            'tmp_name' => $tmp_name,
                            'error' => $files['error'][$key]
                        ];
                        $uploaded_file = uploadFile($file, $upload_dir);
                        if ($uploaded_file) {
                            $uploaded_files[] = $uploaded_file;
                        }
                    }
                }
            }
            return $uploaded_files;
        }

        // Upload des fichiers obligatoires
        $passeport_file = uploadFile($_FILES['passeport'], $upload_dir);
        $justificatif_file = uploadFile($_FILES['justificatif'], $upload_dir);
        
        // Upload des photos
        $photos_files = uploadMultipleFiles($_FILES['photo'], $upload_dir);
        $photos = implode(',', $photos_files);

        // Upload des documents supplémentaires
        $documents_supplementaires = [];
        if (isset($_FILES['doc_supplementaire']) && is_array($_FILES['doc_supplementaire']['tmp_name'])) {
            foreach ($_FILES['doc_supplementaire']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['doc_supplementaire']['error'][$key] === UPLOAD_ERR_OK) {
                    $doc_file = [
                        'name' => $_FILES['doc_supplementaire']['name'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['doc_supplementaire']['error'][$key]
                    ];
                    $uploaded_doc = uploadFile($doc_file, $upload_dir);
                    if ($uploaded_doc) {
                        $type_doc = $_POST['type_doc_supplementaire'][$key] ?? 'autre';
                        $documents_supplementaires[] = $uploaded_doc . '|' . $type_doc;
                    }
                }
            }
        }
        $docs_supp = implode(';', $documents_supplementaires);

        // Upload des documents spécifiques
        $test_en_files = uploadMultipleFiles($_FILES['test_en'], $upload_dir);
        $test_en = implode(',', $test_en_files);
        
        $certificat_scolarite_files = uploadMultipleFiles($_FILES['certificat_scolarite'], $upload_dir);
        $certificat_scolarite = implode(',', $certificat_scolarite_files);
        
        $certificat_medical_files = uploadMultipleFiles($_FILES['certificat_medical'], $upload_dir);
        $certificat_medical = implode(',', $certificat_medical_files);
        
        $casier_judiciaire_files = uploadMultipleFiles($_FILES['casier_judiciaire'], $upload_dir);
        $casier_judiciaire = implode(',', $casier_judiciaire_files);

        // Gestion de la demande de test
        $demande_test = isset($_POST['demander_test']) ? 1 : 0;

        // Insertion dans la base de données
        $sql = "INSERT INTO demandes_etudes_bulgarie (
            nom_complet, email, telephone, programme, niveau_etude, 
            passeport, justificatif_financier, photos, documents_supplementaires,
            test_en, certificat_scolarite, certificat_medical, casier_judiciaire,
            demande_test, statut, date_soumission, user_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'nouveau', NOW(), ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nom'],
            $user_email,
            $_POST['telephone'],
            $_POST['programme'],
            $_POST['niveau'],
            $passeport_file,
            $justificatif_file,
            $photos,
            $docs_supp,
            $test_en,
            $certificat_scolarite,
            $certificat_medical,
            $casier_judiciaire,
            $demande_test,
            $_SESSION['user_id']  // Ajouter l'ID utilisateur
        ]);

        $success_message = "Votre demande d'admission a été soumise avec succès !";

    } catch (PDOException $e) {
        $error_message = "Erreur lors de l'envoi de la demande : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission en Bulgarie - Formulaire</title>
    <!-- Ajouter Bootstrap pour les alertes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Variables de couleurs pour la Bulgarie */
        :root {
            --bulgarie-green: #00966E;
            --bulgarie-red: #D62612;
            --bulgarie-white: #FFFFFF;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 150, 110, 0.1);
            --transition: all 0.3s ease;
            --primary-green: #00966E;
            --secondary-green: #007a5a;
            --accent-orange: #ff6b35;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --error-color: #ef4444;
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
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
            transition: var(--transition);
        }
        
        .card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--bulgarie-green) 0%, var(--secondary-green) 100%);
            color: var(--white);
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
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.05"><text x="50%" y="50%" font-size="20" text-anchor="middle" dominant-baseline="middle" fill="white">🇧🇬</text></svg>');
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
            color: var(--bulgarie-white);
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
            color: var(--bulgarie-green);
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h2 i {
            color: var(--bulgarie-white);
            background: var(--bulgarie-green);
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
            border-color: var(--bulgarie-green);
            box-shadow: 0 0 0 3px rgba(0, 150, 110, 0.1);
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
            border-color: var(--bulgarie-green);
            background: rgba(0, 150, 110, 0.05);
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
            background: var(--bulgarie-green);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--secondary-green);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--bulgarie-green);
            border-color: var(--bulgarie-green);
        }
        
        .btn-outline:hover {
            background: var(--bulgarie-green);
            color: var(--white);
        }
        
        .btn-secondary {
            background: var(--accent-orange);
            color: var(--white);
        }
        
        .btn-secondary:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }
        
        .btn-success:hover {
            background: #0da271;
            transform: translateY(-2px);
        }
        
        .hidden {
            display: none;
        }
        
        .document-item {
            background: var(--light-bg);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
            position: relative;
        }
        
        .remove-document {
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
            font-size: 0.9rem;
        }
        
        .remove-document:hover {
            background: #dc2625;
        }
        
        .add-document-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            background: transparent;
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius);
            color: var(--bulgarie-green);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }
        
        .add-document-btn:hover {
            border-color: var(--bulgarie-green);
            background: rgba(0, 150, 110, 0.05);
        }
        
        .programme-section {
            background: rgba(0, 150, 110, 0.05);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border-left: 4px solid var(--bulgarie-green);
        }
        
        .test-section {
            background: rgba(255, 107, 53, 0.05);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border-left: 4px solid var(--accent-orange);
        }
        
        .niveau-section {
            background: rgba(16, 185, 129, 0.05);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border-left: 4px solid var(--success-color);
        }
        
        .info-text {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 8px;
            padding: 10px;
            background: #e3f2fd;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--bulgarie-green);
        }
        
        .warning-text {
            font-size: 0.9rem;
            color: var(--accent-orange);
            margin-top: 8px;
            padding: 10px;
            background: #fff3cd;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--accent-orange);
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
            
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-university"></i> Bulgarie - Admission Universitaire</h1>
            <p>Formulaire pour admission dans les établissements bulgares</p>
        </div>

        <!-- Afficher les messages d'erreur/succès -->
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" style="margin: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" style="margin: 20px;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="post" action="" class="form" enctype="multipart/form-data" id="bulgarie-form">
            <input type="hidden" name="type" value="admission">
            
            <!-- Informations personnelles -->
            <div class="form-section">
                <h2><i class="fas fa-user-graduate"></i> Informations personnelles</h2>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label for="nom">Nom complet <span class="required">*</span></label>
                        <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" placeholder="Votre nom complet">
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Numéro de téléphone <span class="required">*</span></label>
                        <input type="tel" id="telephone" name="telephone" required value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>" placeholder="Votre numéro de téléphone">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="votre@email.com">
                </div>
            </div>
            
            <!-- Programme et Niveau -->
            <div class="form-section">
                <h2><i class="fas fa-book"></i> Programme et Niveau d'Études</h2>
                
                <div class="form-group">
                    <label for="programme">Programme souhaité <span class="required">*</span></label>
                    <select id="programme" name="programme" required onchange="toggleProgramme()">
                        <option value="">-- Choisir votre programme --</option>
                        <option value="anglais" <?php echo ($_POST['programme'] ?? '') === 'anglais' ? 'selected' : ''; ?>>Programme en Anglais</option>
                        <option value="preparatoire" <?php echo ($_POST['programme'] ?? '') === 'preparatoire' ? 'selected' : ''; ?>>Année préparatoire</option>
                    </select>
                </div>

                <!-- Programme en Anglais -->
                <div id="prog-en" class="programme-section hidden">
                    <h3><i class="fas fa-language"></i> Test de Langue Anglaise</h3>
                    <div class="form-group">
                        <label>Possédez-vous un test de langue (TOEFL, IELTS) ? <span class="required">*</span></label>
                        <div style="display: flex; gap: 15px; margin-top: 10px;">
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="radio" name="has_test" value="oui" onchange="toggleTestOption()" style="margin-right: 8px;">
                                Oui, j'ai un test
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer;">
                                <input type="radio" name="has_test" value="non" onchange="toggleTestOption()" style="margin-right: 8px;">
                                Non, je n'ai pas de test
                            </label>
                        </div>
                    </div>
                    
                    <!-- Section Upload Test -->
                    <div id="test-upload-section" class="form-group hidden">
                        <label for="test_en">Test(s) de langue (TOEFL, IELTS, etc.) <span class="required">*</span></label>
                        <div class="file-input-container">
                            <input type="file" id="test_en" name="test_en[]" multiple>
                            <label for="test_en" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un ou plusieurs fichiers</span>
                            </label>
                        </div>
                        <div class="file-hint">Formats acceptés: PDF, JPG, PNG. Taille max: 5MB par fichier</div>
                    </div>

                    <!-- Section Demande de Test -->
                    <div id="demande-test-section" class="test-section hidden">
                        <h4><i class="fas fa-calendar-plus"></i> Demander un Test de Langue</h4>
                        <p class="warning-text">Vous n'avez pas de test de langue ? Nous pouvons vous aider à en organiser un.</p>
                        
                        <input type="hidden" name="demander_test" id="demander-test-checkbox" value="0">
                        <button type="button" class="btn btn-secondary" onclick="demanderTest()">
                            <i class="fas fa-calendar-check"></i> Demander un Test de Langue
                        </button>
                        <p class="info-text">En cliquant sur ce bouton, vous serez redirigé vers notre page de demande de test de langue.</p>
                    </div>
                </div>

                <!-- Année Préparatoire -->
                <div id="prog-prep" class="programme-section hidden">
                    <h3><i class="fas fa-book-open"></i> Informations Complémentaires pour l'Année Préparatoire</h3>
                    <p class="info-text">Les documents obligatoires (certificat de scolarité, certificat médical et casier judiciaire) sont déjà requis ci-dessous pour tous les programmes.</p>
                </div>

                <!-- Niveau d'études -->
                <div class="form-group">
                    <label for="niveau">Niveau actuel <span class="required">*</span></label>
                    <select id="niveau" name="niveau" required onchange="toggleNiveau()">
                        <option value="">-- Choisir votre niveau --</option>
                        <option value="l1" <?php echo ($_POST['niveau'] ?? '') === 'l1' ? 'selected' : ''; ?>>Licence 1 (L1)</option>
                        <option value="l2" <?php echo ($_POST['niveau'] ?? '') === 'l2' ? 'selected' : ''; ?>>Licence 2 (L2)</option>
                        <option value="l3" <?php echo ($_POST['niveau'] ?? '') === 'l3' ? 'selected' : ''; ?>>Licence 3 (L3)</option>
                        <option value="m1" <?php echo ($_POST['niveau'] ?? '') === 'm1' ? 'selected' : ''; ?>>Master 1 (M1)</option>
                        <option value="m2" <?php echo ($_POST['niveau'] ?? '') === 'm2' ? 'selected' : ''; ?>>Master 2 (M2)</option>
                    </select>
                </div>
            </div>
            
            <!-- Documents obligatoires pour tous -->
            <div class="form-section">
                <h2><i class="fas fa-file-medical"></i> Documents Obligatoires pour Tous les Programmes</h2>
                
                <div class="grid-2">
                    <div class="form-group">
                        <label for="certificat_scolarite">Certificat(s) de scolarité <span class="required">*</span></label>
                        <div class="file-input-container">
                            <input type="file" id="certificat_scolarite" name="certificat_scolarite[]" multiple required>
                            <label for="certificat_scolarite" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un ou plusieurs fichiers</span>
                            </label>
                        </div>
                        <div class="file-hint">Formats: PDF, JPG, PNG (5MB max par fichier)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="certificat_medical">Certificat(s) médical <span class="required">*</span></label>
                        <div class="file-input-container">
                            <input type="file" id="certificat_medical" name="certificat_medical[]" multiple required>
                            <label for="certificat_medical" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un ou plusieurs fichiers</span>
                            </label>
                        </div>
                        <div class="file-hint">Formats: PDF, JPG, PNG (5MB max par fichier)</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="casier_judiciaire">Casier(s) judiciaire <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="casier_judiciaire" name="casier_judiciaire[]" multiple required>
                        <label for="casier_judiciaire" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un ou plusieurs fichiers</span>
                            </label>
                        </div>
                        <div class="file-hint">Formats: PDF, JPG, PNG (5MB max par fichier)</div>
                    </div>
                </div>
                
                <!-- Documents d'identité et financiers -->
                <div class="form-section">
                    <h2><i class="fas fa-file-alt"></i> Documents d'Identité et Financiers</h2>
                    
                    <div class="grid-2">
                        <div class="form-group">
                            <label for="passeport">Passeport <span class="required">*</span></label>
                            <div class="file-input-container">
                                <input type="file" id="passeport" name="passeport" required>
                                <label for="passeport" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text">Choisir un fichier</span>
                                </label>
                            </div>
                            <div class="file-hint">Passeport en cours de validité (PDF, JPG - Max. 5MB)</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="justificatif">Justificatif financier <span class="required">*</span></label>
                            <div class="file-input-container">
                                <input type="file" id="justificatif" name="justificatif" required>
                                <label for="justificatif" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text">Choisir un fichier</span>
                                </label>
                            </div>
                            <div class="file-hint">Formats acceptés: PDF, JPG, PNG. Taille max: 5MB</div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="photo">Photos d'identité <span class="required">*</span></label>
                        <div class="file-input-container">
                            <input type="file" id="photo" name="photo[]" multiple required>
                            <label for="photo" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un ou plusieurs fichiers</span>
                            </label>
                        </div>
                        <div class="file-hint">Photos d'identité (JPG, PNG - Max. 5MB par fichier)</div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Soumettre la demande
                    </button>
                    <a href="index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Retour à l'accueil
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Les fonctions JavaScript restent les mêmes
    let documentCounter = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // File input labels
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.nextElementSibling;
                const fileText = label.querySelector('.file-text');
                
                if (this.files.length > 0) {
                    if (this.multiple && this.files.length > 1) {
                        fileText.textContent = this.files.length + ' fichiers sélectionnés';
                    } else {
                        fileText.textContent = this.files[0].name;
                    }
                    label.classList.add('file-selected');
                } else {
                    fileText.textContent = this.multiple ? 'Choisir un ou plusieurs fichiers' : 'Choisir un fichier';
                    label.classList.remove('file-selected');
                }
            });
        });
        
        // Form validation before submission
        document.getElementById('bulgarie-form').addEventListener('submit', function(e) {
            // Validation basique
            const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value) {
                    valid = false;
                    field.style.borderColor = 'var(--error-color)';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            // Vérification spécifique pour le test de langue
            const programme = document.getElementById('programme').value;
            const progEnSection = document.getElementById('prog-en');
            
            if (!progEnSection.classList.contains('hidden')) {
                const hasTest = document.querySelector('input[name="has_test"]:checked');
                if (!hasTest) {
                    valid = false;
                    alert('Veuillez indiquer si vous avez un test de langue');
                    return false;
                }
                
                if (hasTest.value === 'oui') {
                    const testFiles = document.getElementById('test_en');
                    if (!testFiles || !testFiles.files.length) {
                        valid = false;
                        alert('Veuillez télécharger votre/vos test(s) de langue');
                        return false;
                    }
                }
            }
            
            if (!valid) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires');
                return;
            }
        });
        
        // Initialiser les sections
        toggleProgramme();
        toggleNiveau();
        toggleTestOption();
    });
    
    // Fonction pour afficher/masquer les sections de programme
    function toggleProgramme() {
        const programme = document.getElementById('programme').value;
        document.getElementById('prog-en').classList.add('hidden');
        document.getElementById('prog-prep').classList.add('hidden');

        if (programme === 'anglais') document.getElementById('prog-en').classList.remove('hidden');
        if (programme === 'preparatoire') document.getElementById('prog-prep').classList.remove('hidden');
    }
    
    // Fonction pour afficher/masquer les sections de niveau
    function toggleNiveau() {
        const niveau = document.getElementById('niveau').value;
        // J'ai simplifié ici pour ne pas afficher les sections spécifiques
        // Vous pouvez les réactiver si nécessaire
    }
    
    // Fonction pour gérer l'affichage des options de test
    function toggleTestOption() {
        const hasTest = document.querySelector('input[name="has_test"]:checked');
        const uploadSection = document.getElementById('test-upload-section');
        const demandeSection = document.getElementById('demande-test-section');
        
        if (hasTest) {
            if (hasTest.value === 'oui') {
                uploadSection.classList.remove('hidden');
                demandeSection.classList.add('hidden');
                const input = uploadSection.querySelector('input[type="file"]');
                input.required = true;
            } else {
                uploadSection.classList.add('hidden');
                demandeSection.classList.remove('hidden');
                const input = uploadSection.querySelector('input[type="file"]');
                input.required = false;
            }
        }
    }
    
    // Fonction pour simuler la demande de test
    function demanderTest() {
        document.getElementById('demander-test-checkbox').value = "1";
        // Optionnel : redirection vers une page de test
        // window.location.href = 'test_langue.php';
        alert('Demande de test enregistrée. Notre équipe vous contactera pour organiser le test.');
    }
</script>
</body>
</html>