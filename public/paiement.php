<?php
// Connexion à la base de données
require_once __DIR__ . '../../../config.php';
// Initialisation des variables
$message_erreur = '';
$message_success = '';
$reference = '';

error_log("=== DÉBUT DU SCRIPT DE PAIEMENT ===");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("📨 METHODE POST DÉTECTÉE");
    
    try {
        
        
        // Récupération des données du formulaire
        $nom = $_POST['lastname'] ?? '';
        $prenom = $_POST['firstname'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['phone'] ?? '';
        $montant = $_POST['amount'] ?? '';
        $service = $_POST['service'] ?? '';
        $message = $_POST['message'] ?? '';
        
        error_log("📝 Données reçues:");
        error_log("  - Nom: " . $nom);
        error_log("  - Prénom: " . $prenom);
        error_log("  - Email: " . $email);
        error_log("  - Téléphone: " . $telephone);
        error_log("  - Montant: " . $montant);
        error_log("  - Service: " . $service);
        
        // Validation des champs obligatoires
        if (empty($nom) || empty($prenom) || empty($email) || empty($telephone) || empty($montant)) {
            $message_erreur = '<div class="alert alert-error">❌ Tous les champs obligatoires doivent être remplis.</div>';
            error_log("❌ Champs obligatoires manquants");
        } else {
            // Génération d'une référence unique
            $reference = 'REF-' . date('Ymd-His') . '-' . rand(1000, 9999);
            error_log("🔢 Référence générée: " . $reference);
            
            // Gestion du fichier uploadé
            $fichier_nom = '';
            $fichier_chemin = '';
            
            if (isset($_FILES['receipt-file']) && $_FILES['receipt-file']['error'] === UPLOAD_ERR_OK) {
                error_log("📁 Fichier détecté, erreur: " . $_FILES['receipt-file']['error']);
                
                $file_tmp_name = $_FILES['receipt-file']['tmp_name'];
                $file_name = $_FILES['receipt-file']['name'];
                $file_size = $_FILES['receipt-file']['size'];
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                
                error_log("📄 Détails du fichier:");
                error_log("  - Nom original: " . $file_name);
                error_log("  - Taille: " . $file_size . " bytes");
                error_log("  - Extension: " . $file_extension);
                
                // Vérification de l'extension
                $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
                if (in_array($file_extension, $allowed_extensions)) {
                    // Vérification de la taille
                    if ($file_size > 5 * 1024 * 1024) {
                        $message_erreur = '<div class="alert alert-error">❌ Le fichier est trop volumineux (max 5MB).</div>';
                        error_log("❌ Fichier trop volumineux: " . $file_size . " bytes");
                    } else {
                        // Création du dossier uploads s'il n'existe pas
                        if (!is_dir('uploads')) {
                            if (mkdir('uploads', 0755, true)) {
                                error_log("📁 Dossier uploads créé");
                            } else {
                                error_log("❌ Impossible de créer le dossier uploads");
                                $message_erreur = '<div class="alert alert-error">❌ Erreur lors de la création du dossier de stockage.</div>';
                            }
                        }
                        
                        if (empty($message_erreur)) {
                            // Nouveau nom de fichier
                            $new_file_name = $reference . '.' . $file_extension;
                            $upload_path = 'uploads/' . $new_file_name;
                            
                            error_log("💾 Tentative d'upload vers: " . $upload_path);
                            
                            if (move_uploaded_file($file_tmp_name, $upload_path)) {
                                $fichier_nom = $new_file_name;
                                $fichier_chemin = $upload_path;
                                error_log("✅ Fichier uploadé avec succès: " . $fichier_nom);
                            } else {
                                error_log("❌ Échec du move_uploaded_file");
                                $message_erreur = '<div class="alert alert-error">❌ Erreur lors de l\'upload du fichier.</div>';
                            }
                        }
                    }
                } else {
                    $message_erreur = '<div class="alert alert-error">❌ Format de fichier non autorisé. Utilisez PDF, JPG, JPEG ou PNG.</div>';
                    error_log("❌ Extension non autorisée: " . $file_extension);
                }
            } else {
                $file_error = $_FILES['receipt-file']['error'] ?? 'N/A';
                error_log("❌ Aucun fichier ou erreur d'upload: " . $file_error);
                $message_erreur = '<div class="alert alert-error">❌ Veuillez sélectionner un reçu de virement.</div>';
            }
            
            // Si pas d'erreur avec le fichier, procéder à l'insertion
            if (empty($message_erreur)) {
                // Insertion dans la base de données - ADAPTÉ À VOTRE STRUCTURE
                $sql = "INSERT INTO paiements (
                    reference, 
                    nom, 
                    prenom, 
                    email, 
                    telephone, 
                    montant, 
                    service, 
                    message, 
                    fichier_nom, 
                    fichier_chemin, 
                    statut
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                error_log("🚀 Préparation de la requête SQL");
                
                $stmt = $pdo->prepare($sql);
                
                $params = [
                    $reference,
                    $nom,
                    $prenom,
                    $email,
                    $telephone,
                    $montant,
                    $service,
                    $message,
                    $fichier_nom,
                    $fichier_chemin,
                    'en_attente' // Statut par défaut
                ];
                
                error_log("📋 Paramètres pour l'insertion:");
                error_log("  - reference: " . $reference);
                error_log("  - nom: " . $nom);
                error_log("  - prenom: " . $prenom);
                error_log("  - email: " . $email);
                error_log("  - telephone: " . $telephone);
                error_log("  - montant: " . $montant);
                error_log("  - service: " . $service);
                error_log("  - fichier_nom: " . $fichier_nom);
                error_log("  - fichier_chemin: " . $fichier_chemin);
                
                if ($stmt->execute($params)) {
                    $last_id = $pdo->lastInsertId();
                    $message_success = '<div class="alert alert-success">✅ Votre demande a été enregistrée avec succès ! Référence: <strong>' . $reference . '</strong></div>';
                    error_log("✅ Insertion réussie, ID: " . $last_id);
                    
                    // Réinitialiser le formulaire après succès
                    echo '<script>
                        setTimeout(function() {
                            document.getElementById("client-form").reset();
                            resetFileDisplay();
                        }, 1000);
                    </script>';
                } else {
                    $error_info = $stmt->errorInfo();
                    $message_erreur = '<div class="alert alert-error">❌ Erreur lors de l\'enregistrement dans la base de données: ' . $error_info[2] . '</div>';
                    error_log("❌ Erreur d'insertion: " . print_r($error_info, true));
                }
            }
        }
        
    } catch (PDOException $e) {
        $message_erreur = '<div class="alert alert-error">❌ Erreur de connexion à la base de données: ' . $e->getMessage() . '</div>';
        error_log("❌ Erreur PDO: " . $e->getMessage());
    }
} else {
    error_log("📭 Méthode GET ou autre");
}

error_log("=== FIN DU SCRIPT DE PAIEMENT ===");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - Compte Algérie Poste KACIMI SOUFYANE</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 25px 0;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .main-content {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .client-form-section {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .bank-info-section {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: #2a5298;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }
        
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            transition: border 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            border-color: #2a5298;
            outline: none;
            box-shadow: 0 0 0 2px rgba(42, 82, 152, 0.2);
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: linear-gradient(135deg, #2a5298, #3a6bd1);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(42, 82, 152, 0.3);
        }
        
        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .bank-details {
            background: #f8faff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .bank-card {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .bank-card:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .bank-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2a5298;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .bank-logo {
            width: 40px;
            height: 40px;
            background: #2a5298;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        .bank-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .info-item {
            margin-bottom: 12px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-weight: 500;
            color: #333;
            word-break: break-all;
            padding: 8px 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e0e0e0;
        }
        
        .receipt-section {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e0e0e0;
        }
        
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }
        
        .upload-area:hover {
            border-color: #2a5298;
            background-color: #f8faff;
        }
        
        .upload-area.has-file {
            border-color: #28a745;
            background-color: #f8fff9;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #2a5298;
            margin-bottom: 15px;
        }
        
        .upload-text {
            margin-bottom: 15px;
            font-weight: 500;
        }
        
        .upload-subtext {
            color: #666;
            font-size: 0.9rem;
        }
        
        .file-input {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .file-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            display: none;
        }
        
        .file-info.show {
            display: block;
        }
        
        .file-name {
            font-weight: 600;
            color: #28a745;
        }
        
        .file-size {
            color: #666;
            font-size: 0.9rem;
        }
        
        .paid-requests {
            margin-top: 40px;
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .requests-list {
            display: grid;
            gap: 15px;
        }
        
        .request-item {
            background: #f8faff;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .request-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .request-info h3 {
            color: #2a5298;
            margin-bottom: 5px;
        }
        
        .request-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .request-status {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            color: #666;
            font-size: 0.9rem;
            border-top: 1px solid #e0e0e0;
        }
        
        .instructions {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 0 8px 8px 0;
        }
        
        .instructions h3 {
            color: #2a5298;
            margin-bottom: 10px;
        }
        
        .instructions ol {
            margin-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        .form-note {
            font-size: 0.85rem;
            color: #666;
            margin-top: 5px;
            font-style: italic;
        }
        
        .highlight-box {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .highlight-box h3 {
            margin-bottom: 10px;
            font-size: 1.3rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .reference-display {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .reference-display h3 {
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        
        .reference-code {
            font-size: 2rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            letter-spacing: 2px;
        }
        
        .reference-note {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            
            .bank-info {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .reference-code {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Paiement par Virement Bancaire</h1>
            <p class="subtitle">Remplissez vos informations et utilisez les coordonnées Algérie Poste pour effectuer votre paiement</p>
        </header>
        
        <!-- Affichage des messages -->
        <?php if (isset($message_success) && !empty($message_success)): ?>
        <div class="reference-display">
            <h3>✅ Votre demande a été enregistrée avec succès !</h3>
            <div class="reference-code"><?php echo htmlspecialchars($reference); ?></div>
            <p class="reference-note">Utilisez cette référence dans la communication de votre virement bancaire</p>
        </div>
        <?php endif; ?>
        
        <?php
        // Afficher les messages d'erreur
        if (isset($message_erreur) && !empty($message_erreur)) {
            echo $message_erreur;
        }
        ?>
        
        <div class="instructions">
            <h3>📋 Instructions de paiement</h3>
            <ol>
                <li>Remplissez le formulaire avec vos informations personnelles</li>
                <li>Effectuez un virement vers le compte Algérie Poste de KACIMI SOUFYANE</li>
                <li>La référence de votre demande sera générée automatiquement après enregistrement</li>
                <li>Indiquez cette référence dans la communication du virement</li>
                <li>Téléchargez le reçu de virement une fois la transaction effectuée</li>
                <li>Votre demande sera traitée dans les 24 heures suivant la réception du paiement</li>
            </ol>
        </div>
        
        <div class="main-content">
            <section class="client-form-section">
                <h2 class="section-title">👤 Informations Personnelles</h2>
                
                <form id="client-form" method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="required">Référence de la Demande</label>
                        <div class="info-value" style="background: #e8f5e9; color: #2e7d32; font-weight: bold; text-align: center; padding: 15px;">
                            🎯 Générée automatiquement après enregistrement
                        </div>
                        <div class="form-note">La référence unique sera créée automatiquement et affichée après soumission du formulaire</div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="lastname" class="required">Nom</label>
                            <input type="text" id="lastname" name="lastname" placeholder="Votre nom" value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="firstname" class="required">Prénom</label>
                            <input type="text" id="firstname" name="firstname" placeholder="Votre prénom" value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required">Adresse Email</label>
                        <input type="email" id="email" name="email" placeholder="votre@email.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone" class="required">Numéro de Téléphone</label>
                        <input type="tel" id="phone" name="phone" placeholder="+213 XXX XX XX XX" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount" class="required">Montant à Payer (DZD)</label>
                        <input type="number" id="amount" name="amount" placeholder="1000.00" min="1" step="0.01" value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="service">Service Demandé</label>
                        <select id="service" name="service">
                            <option value="">Sélectionnez un service</option>
                            <option value="premium" <?php echo (isset($_POST['service']) && $_POST['service'] == 'premium') ? 'selected' : ''; ?>>Service Premium</option>
                            <option value="standard" <?php echo (isset($_POST['service']) && $_POST['service'] == 'standard') ? 'selected' : ''; ?>>Service Standard</option>
                            <option value="basic" <?php echo (isset($_POST['service']) && $_POST['service'] == 'basic') ? 'selected' : ''; ?>>Service Basique</option>
                            <option value="visa" <?php echo (isset($_POST['service']) && $_POST['service'] == 'visa') ? 'selected' : ''; ?>>Demande de Visa</option>
                            <option value="etudes" <?php echo (isset($_POST['service']) && $_POST['service'] == 'etudes') ? 'selected' : ''; ?>>Études à l'étranger</option>
                            <option value="travail" <?php echo (isset($_POST['service']) && $_POST['service'] == 'travail') ? 'selected' : ''; ?>>Travail à l'étranger</option>
                            <option value="other" <?php echo (isset($_POST['service']) && $_POST['service'] == 'other') ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message (Optionnel)</label>
                        <textarea id="message" name="message" rows="3" placeholder="Informations supplémentaires..."><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="required">Reçu de Virement</label>
                        <div class="upload-area" id="upload-area">
                            <div class="upload-icon">📁</div>
                            <p class="upload-text">Cliquez pour sélectionner votre reçu de virement</p>
                            <p class="upload-subtext">Formats acceptés: PDF, JPG, PNG (Max. 5MB)</p>
                            <input type="file" id="receipt-file" name="receipt-file" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        
                        <div class="file-info" id="file-info">
                            <div class="file-name" id="file-name"></div>
                            <div class="file-size" id="file-size"></div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn" id="submit-btn">
                        <span id="btn-text">💾 Enregistrer ma Demande</span>
                        <span id="btn-loading" class="loading" style="display: none;"></span>
                    </button>
                </form>
            </section>
            
            <section class="bank-info-section">
                <div class="highlight-box">
                    <h3>🏦 Compte Algérie Poste</h3>
                    <p>Veuillez effectuer le virement vers ce compte</p>
                </div>
                
                <h2 class="section-title">💰 Coordonnées Bancaires</h2>
                
                <div class="bank-details">
                    <div class="bank-card">
                        <div class="bank-name">
                            <div class="bank-logo">AP</div>
                            <span>Algérie Poste - KACIMI SOUFYANE</span>
                        </div>
                        <div class="bank-info">
                            <div class="info-item">
                                <div class="info-label">Titulaire du Compte</div>
                                <div class="info-value">KACIMI SOUFYANE</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Numéro de Compte Complet</div>
                                <div class="info-value">007 99999 0004215004 17</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Code Banque</div>
                                <div class="info-value">007</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Code Guichet</div>
                                <div class="info-value">99999</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Numéro de Compte</div>
                                <div class="info-value">0004215004</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Clé RIB</div>
                                <div class="info-value">17</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Banque</div>
                                <div class="info-value">Algérie Poste</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <section class="paid-requests">
            <h2 class="section-title">📋 Vos Demandes Récentes</h2>
            
            <div class="requests-list" id="requests-list">
                <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <h3>Aucune demande pour le moment</h3>
                    <p>Vos demandes payées apparaîtront ici après soumission</p>
                </div>
            </div>
        </section>
        
        <footer>
            <p>© 2025 Service de Paiement. Tous droits réservés.</p>
            <p>📞 Assistance: babylone.service15@gmail.com | Tél: +213 554 310 047 </p>
        </footer>
    </div>

    <script>
        // Gestion de l'interface utilisateur
        const uploadArea = document.getElementById('upload-area');
        const fileInput = document.getElementById('receipt-file');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const fileSize = document.getElementById('file-size');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const btnLoading = document.getElementById('btn-loading');
        
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const fileNameText = file.name;
                const fileSizeText = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                
                if (file.size > 5 * 1024 * 1024) {
                    alert('❌ Le fichier est trop volumineux. Veuillez sélectionner un fichier de moins de 5MB.');
                    this.value = '';
                    resetFileDisplay();
                    return;
                }
                
                fileName.textContent = '📄 ' + fileNameText;
                fileSize.textContent = '📏 Taille: ' + fileSizeText;
                fileInfo.classList.add('show');
                uploadArea.classList.add('has-file');
                
                uploadArea.innerHTML = `
                    <div class="upload-icon">✅</div>
                    <p class="upload-text">Fichier sélectionné avec succès</p>
                    <p class="upload-subtext">Cliquez sur "Enregistrer ma Demande" pour valider</p>
                    <p class="upload-subtext">Fichier: ${fileNameText} (${fileSizeText})</p>
                    <input type="file" id="receipt-file" name="receipt-file" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                `;
                
                document.getElementById('receipt-file').addEventListener('change', fileInput.onchange);
            }
        });

        function resetFileDisplay() {
            fileInfo.classList.remove('show');
            uploadArea.classList.remove('has-file');
            uploadArea.innerHTML = `
                <div class="upload-icon">📁</div>
                <p class="upload-text">Cliquez pour sélectionner votre reçu de virement</p>
                <p class="upload-subtext">Formats acceptés: PDF, JPG, PNG (Max. 5MB)</p>
                <input type="file" id="receipt-file" name="receipt-file" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            `;
            document.getElementById('receipt-file').addEventListener('change', fileInput.onchange);
        }

        document.getElementById('client-form').addEventListener('submit', function(e) {
            const form = this;
            
            submitBtn.disabled = true;
            btnText.textContent = 'Enregistrement en cours...';
            btnLoading.style.display = 'inline-block';
            
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                alert('❌ Veuillez sélectionner un reçu de virement.');
                resetButtons();
                return;
            }
            
            const requiredFields = form.querySelectorAll('input[required], select[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (field.type !== 'file' && !field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('❌ Veuillez remplir tous les champs obligatoires.');
                resetButtons();
                return;
            }
            
            const email = document.getElementById('email').value;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                alert('❌ Veuillez entrer une adresse email valide.');
                resetButtons();
                return;
            }
        });

        function resetButtons() {
            submitBtn.disabled = false;
            btnText.textContent = '💾 Enregistrer ma Demande';
            btnLoading.style.display = 'none';
        }

        window.addEventListener('pageshow', function() {
            resetButtons();
        });
    </script>
</body>
</html>