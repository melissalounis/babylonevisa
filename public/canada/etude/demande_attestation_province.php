<?php
// upload_attestation_province.php
session_start();

include '../config.php';
// Configuration upload
$dossier_upload = "uploads/attestation_province/";
$documents_autorises = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
$taille_max = 10 * 1024 * 1024; // 10MB

// Initialisation des variables
$erreurs = [];
$succes = false;
$pdo = null;
$id_demande = null;



// Créer le dossier d'upload
if (!file_exists($dossier_upload)) {
    if (!mkdir($dossier_upload, 0755, true)) {
        $erreurs[] = "Impossible de créer le dossier d'upload";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    // Nettoyage des données
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $province = $_POST['province'] ?? '';
    $telephone = trim($_POST['telephone'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $adresse = trim($_POST['adresse'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $code_postal = trim($_POST['code_postal'] ?? '');
    $pays = $_POST['pays'] ?? '';
    
    // Récupérer l'user_id si l'utilisateur est connecté
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // Validation
    if (empty($nom_complet)) $erreurs[] = "Le nom complet est obligatoire";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email valide requis";
    if (empty($province)) $erreurs[] = "Province requise";

    // Traitement des fichiers
    $documents_uploades = [];
    
    if (empty($erreurs)) {
        $documents_requis = [
            'passeport' => 'Passeport valide'
        ];
        
        $documents_optionnels = [
            'preuve_fonds' => 'Preuve de fonds',
            'lettre_acceptation' => 'Lettre d\'acceptation'
        ];
        
        // Traitement des documents obligatoires
        foreach ($documents_requis as $champ => $nom_document) {
            if (isset($_FILES[$champ]) && is_array($_FILES[$champ]['name'])) {
                $files = $_FILES[$champ];
                $uploaded_files = [];
                
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $file_name = $files['name'][$i];
                        $file_tmp = $files['tmp_name'][$i];
                        $file_size = $files['size'][$i];
                        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        
                        if (!in_array($extension, $documents_autorises)) {
                            $erreurs[] = "Format invalide pour $nom_document: $file_name";
                            continue;
                        }
                        
                        if ($file_size > $taille_max) {
                            $erreurs[] = "$nom_document trop volumineux (>10MB): $file_name";
                            continue;
                        }
                        
                        $nouveau_nom = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file_name);
                        $chemin_fichier = $dossier_upload . $nouveau_nom;
                        
                        if (move_uploaded_file($file_tmp, $chemin_fichier)) {
                            $uploaded_files[] = $nouveau_nom;
                        } else {
                            $erreurs[] = "Erreur upload $nom_document: $file_name";
                        }
                    } elseif ($files['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                        $erreurs[] = "Erreur avec le fichier $nom_document: " . $files['name'][$i];
                    }
                }
                
                if (!empty($uploaded_files)) {
                    $documents_uploades[$champ] = json_encode($uploaded_files);
                } else {
                    $erreurs[] = "$nom_document requis - au moins un fichier nécessaire";
                }
            } else {
                $erreurs[] = "$nom_document requis";
            }
        }
        
        // Traitement des documents optionnels
        foreach ($documents_optionnels as $champ => $nom_document) {
            if (isset($_FILES[$champ]) && is_array($_FILES[$champ]['name'])) {
                $files = $_FILES[$champ];
                $uploaded_files = [];
                
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $file_name = $files['name'][$i];
                        $file_tmp = $files['tmp_name'][$i];
                        $file_size = $files['size'][$i];
                        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        
                        if (!in_array($extension, $documents_autorises)) {
                            $erreurs[] = "Format invalide pour $nom_document: $file_name";
                            continue;
                        }
                        
                        if ($file_size > $taille_max) {
                            $erreurs[] = "$nom_document trop volumineux (>10MB): $file_name";
                            continue;
                        }
                        
                        $nouveau_nom = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file_name);
                        $chemin_fichier = $dossier_upload . $nouveau_nom;
                        
                        if (move_uploaded_file($file_tmp, $chemin_fichier)) {
                            $uploaded_files[] = $nouveau_nom;
                        } else {
                            $erreurs[] = "Erreur upload $nom_document: $file_name";
                        }
                    }
                }
                
                if (!empty($uploaded_files)) {
                    $documents_uploades[$champ] = json_encode($uploaded_files);
                }
            }
        }
        
        // Enregistrement en base
        if (empty($erreurs)) {
            try {
                $sql = "INSERT INTO attestation_province 
                        (user_id, nom_complet, email, telephone, province, date_naissance, 
                         adresse, ville, code_postal, pays, passeport_path, preuve_fonds_path, 
                         lettre_acceptation_path, statut, date_soumission) 
                        VALUES 
                        (:user_id, :nom_complet, :email, :telephone, :province, :date_naissance,
                         :adresse, :ville, :code_postal, :pays, :passeport_path, :preuve_fonds_path,
                         :lettre_acceptation_path, 'nouveau', NOW())";
                
                $stmt = $pdo->prepare($sql);
                
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':nom_complet' => $nom_complet,
                    ':email' => $email,
                    ':telephone' => $telephone,
                    ':province' => $province,
                    ':date_naissance' => $date_naissance ?: null,
                    ':adresse' => $adresse,
                    ':ville' => $ville,
                    ':code_postal' => $code_postal,
                    ':pays' => $pays,
                    ':passeport_path' => $documents_uploades['passeport'],
                    ':preuve_fonds_path' => $documents_uploades['preuve_fonds'] ?? null,
                    ':lettre_acceptation_path' => $documents_uploades['lettre_acceptation'] ?? null
                ]);
                
                $id_demande = $pdo->lastInsertId();
                $succes = true;
                
            } catch (PDOException $e) {
                $erreurs[] = "Erreur enregistrement : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attestation de Province - Upload Documents</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c5aa0;
            --secondary: #1e3d6f;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .form-section {
            margin-bottom: 30px;
            padding: 25px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
        }
        
        .section-title {
            color: var(--primary);
            font-size: 1.4rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        .required::after {
            content: " *";
            color: var(--danger);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(44, 90, 160, 0.1);
        }
        
        .file-upload-container {
            margin-bottom: 15px;
        }
        
        .file-input-wrapper {
            position: relative;
        }
        
        .file-input-label {
            display: block;
            padding: 20px;
            border: 3px dashed var(--primary);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        .file-input-label:hover {
            background: #e3f2fd;
            transform: translateY(-2px);
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
        
        .file-name {
            margin-top: 8px;
            font-size: 0.9rem;
            color: #666;
            text-align: center;
        }
        
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .document-card {
            padding: 20px;
            background: white;
            border-radius: 10px;
            border-left: 4px solid var(--primary);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .document-card.optionnel {
            border-left-color: var(--warning);
            background: #fffbf0;
        }
        
        .document-card.optionnel .file-input-label {
            border-color: var(--warning);
            background: #fffbf0;
        }
        
        .document-card.optionnel .file-input-label:hover {
            background: #fff3cd;
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: block;
            margin: 30px auto 0;
            transition: all 0.3s;
            width: 100%;
            max-width: 300px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 90, 160, 0.3);
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: var(--success);
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: var(--danger);
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: #17a2b8;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left-color: var(--warning);
        }
        
        .file-list {
            margin-top: 10px;
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            background: #f8f9fa;
        }
        
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px;
            margin-bottom: 5px;
            background: white;
            border-radius: 3px;
        }
        
        .file-item:last-child {
            margin-bottom: 0;
        }
        
        .file-info {
            flex: 1;
            font-size: 0.8rem;
        }
        
        .file-remove {
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            font-size: 0.7rem;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
            margin-left: 8px;
        }
        
        .badge-required {
            background: var(--danger);
            color: white;
        }
        
        .badge-optional {
            background: var(--warning);
            color: var(--dark);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .card {
                padding: 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .documents-grid {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="card">
            <h1><i class="fas fa-file-contract"></i> Attestation de Province</h1>
            <p>Formulaire de demande - Documents requis</p>
        </header>

        <?php if (!$pdo): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> Erreur de connexion à la base de données
            </div>
        <?php endif; ?>
        
        <?php if ($succes): ?>
            <div class="alert alert-success">
                <h3><i class="fas fa-check-circle"></i> Demande enregistrée !</h3>
                <p><strong>Référence :</strong> AP<?php echo str_pad($id_demande, 6, '0', STR_PAD_LEFT); ?></p>
                <p>Votre demande a été soumise avec succès.</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($erreurs)): ?>
            <?php foreach ($erreurs as $erreur): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erreur); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($pdo): ?>
        <form method="POST" action="" enctype="multipart/form-data" class="card" id="demandeForm">
            <!-- Informations personnelles -->
            <div class="form-section">
                <h2 class="section-title"><i class="fas fa-user"></i> Informations personnelles</h2>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom_complet" class="required">Nom complet</label>
                        <input type="text" id="nom_complet" name="nom_complet" required 
                               value="<?php echo htmlspecialchars($_POST['nom_complet'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required">Email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" 
                               value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_naissance">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance" 
                               value="<?php echo htmlspecialchars($_POST['date_naissance'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="province" class="required">Province</label>
                        <select id="province" name="province" required>
                            <option value="">Sélectionnez...</option>
                            <option value="Ontario" <?php echo ($_POST['province'] ?? '') == 'Ontario' ? 'selected' : ''; ?>>Ontario</option>
                            <option value="Colombie-Britannique" <?php echo ($_POST['province'] ?? '') == 'Colombie-Britannique' ? 'selected' : ''; ?>>Colombie-Britannique</option>
                            <option value="Alberta" <?php echo ($_POST['province'] ?? '') == 'Alberta' ? 'selected' : ''; ?>>Alberta</option>
                            <option value="Manitoba" <?php echo ($_POST['province'] ?? '') == 'Manitoba' ? 'selected' : ''; ?>>Manitoba</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="pays">Pays</label>
                        <input type="text" id="pays" name="pays" value="Canada" 
                               value="<?php echo htmlspecialchars($_POST['pays'] ?? 'Canada'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="ville">Ville</label>
                        <input type="text" id="ville" name="ville" 
                               value="<?php echo htmlspecialchars($_POST['ville'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="code_postal">Code postal</label>
                        <input type="text" id="code_postal" name="code_postal" 
                               value="<?php echo htmlspecialchars($_POST['code_postal'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="adresse">Adresse complète</label>
                    <textarea id="adresse" name="adresse" rows="3"><?php echo htmlspecialchars($_POST['adresse'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Documents -->
            <div class="form-section">
                <h2 class="section-title"><i class="fas fa-file-upload"></i> Documents requis</h2>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Formats acceptés : PDF, JPG, PNG, DOC, DOCX (max 10MB par fichier)<br>
                    <strong>Vous pouvez sélectionner plusieurs fichiers pour chaque document</strong>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Documents obligatoires :</strong> Passeport<br>
                    <strong>Documents optionnels :</strong> Preuve de fonds et Lettre d'acceptation
                </div>
                
                <div class="documents-grid">
                    <!-- Passeport -->
                    <div class="document-card">
                        <label class="required">Passeport valide <span class="badge badge-required">OBLIGATOIRE</span></label>
                        <div class="file-upload-container">
                            <div class="file-input-wrapper">
                                <label for="passeport" class="file-input-label">
                                    <i class="fas fa-passport fa-2x"></i><br>
                                    <span>Passeport (pages avec photo et informations)</span><br>
                                    <small>Cliquez pour sélectionner plusieurs fichiers</small>
                                </label>
                                <input type="file" id="passeport" name="passeport[]" class="file-input" required 
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                            </div>
                            <div class="file-list" id="passeport-list">
                                Aucun fichier sélectionné
                            </div>
                        </div>
                    </div>
                    
                    <!-- Preuve de fonds -->
                    <div class="document-card optionnel">
                        <label>Preuve de fonds <span class="badge badge-optional">OPTIONNEL</span></label>
                        <div class="file-upload-container">
                            <div class="file-input-wrapper">
                                <label for="preuve_fonds" class="file-input-label">
                                    <i class="fas fa-file-invoice-dollar fa-2x"></i><br>
                                    <span>Relevés bancaires (6 mois)</span><br>
                                    <small>Cliquez pour sélectionner plusieurs fichiers</small>
                                </label>
                                <input type="file" id="preuve_fonds" name="preuve_fonds[]" class="file-input" 
                                       accept=".pdf,.jpg,.jpeg,.png" multiple>
                            </div>
                            <div class="file-list" id="preuve_fonds-list">
                                Aucun fichier sélectionné
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lettre d'acceptation -->
                    <div class="document-card optionnel">
                        <label>Lettre d'acceptation <span class="badge badge-optional">OPTIONNEL</span></label>
                        <div class="file-upload-container">
                            <div class="file-input-wrapper">
                                <label for="lettre_acceptation" class="file-input-label">
                                    <i class="fas fa-envelope-open-text fa-2x"></i><br>
                                    <span>Lettre d'acceptation de l'établissement</span><br>
                                    <small>Cliquez pour sélectionner plusieurs fichiers</small>
                                </label>
                                <input type="file" id="lettre_acceptation" name="lettre_acceptation[]" class="file-input" 
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                            </div>
                            <div class="file-list" id="lettre_acceptation-list">
                                Aucun fichier sélectionné
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="fas fa-paper-plane"></i> Soumettre la demande
            </button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        // Gestion de l'affichage des fichiers multiples
        document.querySelectorAll('.file-input').forEach(input => {
            input.addEventListener('change', function() {
                const fileList = document.getElementById(this.id + '-list');
                const files = Array.from(this.files);
                
                if (files.length === 0) {
                    fileList.innerHTML = 'Aucun fichier sélectionné';
                    return;
                }
                
                fileList.innerHTML = '';
                files.forEach((file, index) => {
                    const fileItem = document.createElement('div');
                    fileItem.className = 'file-item';
                    fileItem.innerHTML = `
                        <div class="file-info">
                            <strong>${file.name}</strong><br>
                            <small>${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                        </div>
                        <button type="button" class="file-remove" onclick="removeFile(this, '${this.id}', ${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    `;
                    fileList.appendChild(fileItem);
                });
            });
        });

        // Fonction pour supprimer un fichier de la liste
        function removeFile(button, inputId, fileIndex) {
            const input = document.getElementById(inputId);
            const files = Array.from(input.files);
            files.splice(fileIndex, 1);
            
            // Créer un nouveau DataTransfer pour mettre à jour les fichiers
            const dt = new DataTransfer();
            files.forEach(file => dt.items.add(file));
            input.files = dt.files;
            
            // Déclencher l'événement change pour mettre à jour l'affichage
            input.dispatchEvent(new Event('change'));
        }

        // Validation - seulement pour les champs obligatoires
        document.getElementById('demandeForm').addEventListener('submit', function(e) {
            const requiredInputs = document.querySelectorAll('.file-input[required]');
            let valid = true;
            
            requiredInputs.forEach(input => {
                if (!input.files.length) {
                    valid = false;
                    const label = input.previousElementSibling;
                    label.style.borderColor = '#dc3545';
                    label.style.background = '#f8d7da';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Veuillez sélectionner au moins un fichier pour chaque document obligatoire');
            }
        });
    </script>
</body>
</html>