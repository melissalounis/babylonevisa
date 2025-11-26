<?php
// Démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification que l'évaluation a été effectuée
if (!isset($_SESSION['evaluation_score']) || !isset($_SESSION['is_eligible'])) {
    // Redirection vers la page d'évaluation si les données sont manquantes
    header("Location: evaluation_arrima.php");
    exit;
}

// Connexion DB
$host = "localhost";
$dbname = "babylone_service";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur DB : " . $e->getMessage());
}

// Récupération sécurisée des données de session avec valeurs par défaut
$score = isset($_SESSION['evaluation_score']) ? intval($_SESSION['evaluation_score']) : 0;
$is_eligible = isset($_SESSION['is_eligible']) ? boolval($_SESSION['is_eligible']) : false;
$seuil = isset($_SESSION['seuil']) ? intval($_SESSION['seuil']) : 50;

// Récupérer l'ID de l'évaluation Arrima
$user_id = $_SESSION['user_id'] ?? null;
$evaluation_id = null;

if ($user_id) {
    $stmt = $pdo->prepare("
        SELECT e.id FROM evaluations_immigration e 
        JOIN users u ON e.email = u.email 
        WHERE u.id = ? AND e.programme = 'arrima'
        ORDER BY e.date_soumission DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $evaluation = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($evaluation) {
        $evaluation_id = $evaluation['id'];
    }
}

// Initialisation des variables
$message = [];
$uploadDir = __DIR__ . '/uploads/';

// Création du répertoire d'upload s'il n'existe pas
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Définition des types de documents requis pour Arrima
$document_types = [
    'passeport' => 'Passeport valide',
    'naissance' => 'Certificat de naissance',
    'diplomes' => 'Diplômes et relevés de notes',
    'tef' => 'Test de français (TEF Québec)',
    'ielts' => 'Test d\'anglais (IELTS, si disponible)',
    'experience' => 'Attestations d\'expérience professionnelle',
    'releves_bancaires' => 'Preuve de fonds suffisants',
    'mariage' => 'Documents d\'état civil',
    'offre_emploi' => 'Offre d\'emploi validée Québec',
    'famille_canada' => 'Preuve de famille au Québec'
];

// Traitement des fichiers uploadés
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $is_eligible && $evaluation_id) {
    
    // Parcours de tous les types de documents
    foreach ($document_types as $key => $label) {
        
        // Vérification si des fichiers ont été uploadés pour ce type
        if (isset($_FILES[$key]) && is_array($_FILES[$key]['name'])) {
            $files = $_FILES[$key];
            $fileCount = count($files['name']);
            
            // Configuration des restrictions
            $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $max_file_size = 5 * 1024 * 1024; // 5MB
            
            // Traitement de chaque fichier
            for ($i = 0; $i < $fileCount; $i++) {
                $fileName = $files['name'][$i];
                $tempFile = $files['tmp_name'][$i];
                $fileSize = $files['size'][$i];
                $errorCode = $files['error'][$i];
                
                // Ignorer si aucun fichier n'a été sélectionné
                if ($errorCode === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                
                // Vérification des erreurs d'upload
                if ($errorCode !== UPLOAD_ERR_OK) {
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux',
                        UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux',
                        UPLOAD_ERR_PARTIAL => 'Upload partiel',
                        UPLOAD_ERR_NO_FILE => 'Aucun fichier',
                        UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                        UPLOAD_ERR_CANT_WRITE => 'Erreur d\'écriture',
                        UPLOAD_ERR_EXTENSION => 'Extension bloquée'
                    ];
                    
                    $errorMsg = $errorMessages[$errorCode] ?? 'Erreur inconnue';
                    $message[] = "❌ Erreur pour $fileName ($label) : $errorMsg";
                    continue;
                }
                
                // Vérification de l'extension du fichier
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($fileExtension, $allowed_extensions)) {
                    $message[] = "❌ Extension non autorisée pour $fileName ($label)";
                    continue;
                }
                
                // Vérification de la taille du fichier
                if ($fileSize > $max_file_size) {
                    $message[] = "❌ Fichier trop volumineux : $fileName ($label)";
                    continue;
                }
                
                // Vérification que le fichier est bien un upload HTTP
                if (!is_uploaded_file($tempFile)) {
                    $message[] = "❌ Fichier non autorisé : $fileName ($label)";
                    continue;
                }
                
                // Génération d'un nom de fichier sécurisé
                $safeFileName = time() . '_' . $key . '_' . uniqid() . '.' . $fileExtension;
                $targetPath = $uploadDir . $safeFileName;
                
                // Déplacement du fichier uploadé
                if (move_uploaded_file($tempFile, $targetPath)) {
                    
                    // ENREGISTREMENT EN BASE DE DONNÉES - CORRECTION AJOUTÉE
                    try {
                        // Vérifier si un document de ce type existe déjà pour cette évaluation
                        $checkStmt = $pdo->prepare("
                            SELECT id FROM documents_immigration 
                            WHERE evaluation_id = ? AND type_document = ?
                        ");
                        $checkStmt->execute([$evaluation_id, $key]);
                        $existingDoc = $checkStmt->fetch();
                        
                        if ($existingDoc) {
                            // Mettre à jour le document existant
                            $stmt = $pdo->prepare("
                                UPDATE documents_immigration 
                                SET nom_fichier = ?, chemin_fichier = ?, taille = ?, date_upload = NOW() 
                                WHERE id = ?
                            ");
                            $stmt->execute([$fileName, "uploads/" . $safeFileName, $fileSize, $existingDoc['id']]);
                        } else {
                            // Insérer un nouveau document
                            $stmt = $pdo->prepare("
                                INSERT INTO documents_immigration 
                                (evaluation_id, type_document, nom_fichier, chemin_fichier, taille, date_upload) 
                                VALUES (?, ?, ?, ?, ?, NOW())
                            ");
                            $stmt->execute([$evaluation_id, $key, $fileName, "uploads/" . $safeFileName, $fileSize]);
                        }
                        
                        $message[] = "✅ $fileName ($label) téléversé avec succès et enregistré en base de données.";
                        
                    } catch (Exception $e) {
                        $message[] = "❌ Erreur base de données pour $fileName ($label) : " . $e->getMessage();
                    }
                    
                } else {
                    $message[] = "❌ Impossible de sauvegarder $fileName ($label)";
                }
            }
        }
    }
    
    // Message de confirmation si au moins un fichier a été uploadé
    if (empty($message)) {
        $message[] = "ℹ️ Aucun fichier n'a été sélectionné pour l'upload.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat de votre évaluation Arrima</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --primary-color: #059669;
            --primary-dark: #047857;
            --secondary-color: #1976d2;
            --success-color: #2e7d32;
            --error-color: #c62828;
            --warning-color: #ed6c02;
            --background-color: #f8f9fa;
            --card-color: #ffffff;
            --text-color: #333333;
            --muted-color: #666666;
            --border-color: #dee2e6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: var(--text-color);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--card-color);
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .score-section {
            background: #d1fae5;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            margin-bottom: 25px;
        }

        .score-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin: 10px 0;
        }

        .threshold {
            font-size: 1.2rem;
            color: var(--muted-color);
        }

        .eligibility-message {
            text-align: center;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            font-size: 1.3rem;
            font-weight: 600;
        }

        .eligible {
            background: #e8f5e9;
            color: var(--success-color);
            border: 1px solid #c8e6c9;
        }

        .not-eligible {
            background: #ffebee;
            color: var(--error-color);
            border: 1px solid #ffcdd2;
        }

        .documents-section {
            margin-top: 30px;
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 1.5rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
        }

        .upload-form {
            background: #fafafa;
            padding: 25px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .form-group:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .document-label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
            color: var(--text-color);
            font-size: 1.1rem;
        }

        .file-input {
            width: 100%;
            padding: 12px;
            border: 2px dashed var(--border-color);
            border-radius: 6px;
            background: var(--card-color);
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .file-input:hover {
            border-color: var(--primary-color);
        }

        .file-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 16px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .submit-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .messages-container {
            margin-top: 25px;
        }

        .message {
            padding: 15px;
            margin: 10px 0;
            border-radius: 6px;
            border-left: 4px solid;
        }

        .success-message {
            background: #e8f5e9;
            color: var(--success-color);
            border-left-color: var(--success-color);
        }

        .error-message {
            background: #ffebee;
            color: var(--error-color);
            border-left-color: var(--error-color);
        }

        .info-message {
            background: #e3f2fd;
            color: var(--secondary-color);
            border-left-color: var(--secondary-color);
        }

        .recommendations {
            background: #fff3e0;
            border: 1px solid #ffe0b2;
            border-radius: 8px;
            padding: 25px;
            margin-top: 25px;
        }

        .recommendations h3 {
            color: var(--warning-color);
            margin-bottom: 15px;
        }

        .recommendations ul {
            padding-left: 25px;
        }

        .recommendations li {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .retry-link {
            display: inline-block;
            margin-top: 15px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 20px;
            border: 2px solid var(--primary-color);
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .retry-link:hover {
            background: var(--primary-color);
            color: white;
            text-decoration: none;
        }

        .file-info {
            font-size: 0.9rem;
            color: var(--muted-color);
            margin-top: 8px;
            font-style: italic;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .container {
                border-radius: 8px;
            }

            .header {
                padding: 20px;
            }

            .header h1 {
                font-size: 1.8rem;
            }

            .content {
                padding: 20px;
            }

            .score-value {
                font-size: 1.6rem;
            }

            .upload-form {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 1.5rem;
            }

            .score-value {
                font-size: 1.4rem;
            }

            .document-label {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>Résultat de votre évaluation Arrima</h1>
            <p>Programme d'immigration du Québec</p>
        </header>

        <div class="content">
            <!-- Section Score -->
            <div class="score-section">
                <h2>Votre résultat</h2>
                <div class="score-value"><?php echo htmlspecialchars($score); ?> points</div>
                <div class="threshold">Seuil requis : <?php echo htmlspecialchars($seuil); ?> points</div>
            </div>

            <!-- Message d'éligibilité -->
            <div class="eligibility-message <?php echo $is_eligible ? 'eligible' : 'not-eligible'; ?>">
                <?php if ($is_eligible): ?>
                    ✅ Félicitations ! Vous êtes admissible au programme Arrima.
                <?php else: ?>
                    ❌ Désolé, vous n'êtes pas admissible au programme Arrima pour le moment.
                <?php endif; ?>
            </div>

            <?php if ($is_eligible && $evaluation_id): ?>
                <!-- Section Documents pour les personnes admissibles -->
                <div class="documents-section">
                    <h2 class="section-title">Téléversement des documents requis</h2>
                    <p style="margin-bottom: 20px; color: var(--muted-color);">
                        Veuillez fournir les documents suivants pour compléter votre dossier Arrima. 
                        Formats acceptés : PDF, JPG, PNG, DOC, DOCX (5 MB maximum par fichier).
                    </p>

                    <form method="post" enctype="multipart/form-data" class="upload-form">
                        <?php foreach ($document_types as $key => $label): ?>
                            <div class="form-group">
                                <label class="document-label">
                                    <?php echo htmlspecialchars($label); ?>
                                </label>
                                <input type="file" 
                                       name="<?php echo htmlspecialchars($key); ?>[]" 
                                       multiple 
                                       class="file-input"
                                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <div class="file-info">Vous pouvez sélectionner plusieurs fichiers</div>
                            </div>
                        <?php endforeach; ?>

                        <button type="submit" class="submit-btn">
                            📤 Envoyer tous les documents Arrima
                        </button>
                    </form>

                    <!-- Affichage des messages de résultat -->
                    <?php if (!empty($message)): ?>
                        <div class="messages-container">
                            <?php foreach ($message as $msg): ?>
                                <div class="message <?php 
                                    echo str_starts_with($msg, '✅') ? 'success-message' : 
                                    (str_starts_with($msg, '❌') ? 'error-message' : 'info-message'); 
                                ?>">
                                    <?php echo htmlspecialchars($msg); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Lien vers la page des documents -->
                    <div style="text-align: center; margin-top: 25px; padding: 15px; background: #f0f9ff; border-radius: 8px;">
                        <p>📁 <strong>Vos documents sont enregistrés !</strong></p>
                        <p>Vous pouvez consulter tous vos documents (Arrima et Entrée Express) sur la page :</p>
                        <a href="../../client/documents.php" style="display: inline-block; margin-top: 10px; padding: 10px 20px; background: var(--primary-color); color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                            Voir tous mes documents
                        </a>
                    </div>
                </div>

            <?php elseif ($is_eligible && !$evaluation_id): ?>
                <div class="alert alert-error">
                    ❌ Erreur : Impossible de trouver votre évaluation Arrima en base de données.
                </div>

            <?php else: ?>
                <!-- Section Recommandations pour les personnes non admissibles -->
                <div class="recommendations">
                    <h3>Que faire pour améliorer votre admissibilité ?</h3>
                    <ul>
                        <li><strong>Améliorer vos compétences linguistiques</strong> - Le français est particulièrement important pour le Québec</li>
                        <li><strong>Acquérir plus d'expérience professionnelle</strong> - Dans un domaine en demande</li>
                        <li><strong>Poursuivre des études supplémentaires</strong> - Un diplôme plus élevé peut augmenter votre score</li>
                        <li><strong>Obtenir une offre d'emploi valide</strong> - Au Québec</li>
                        <li><strong>Consulter un conseiller en immigration</strong> - Pour des conseils personnalisés</li>
                    </ul>
                    
                    <a href="evaluation_arrima.php" class="retry-link">
                        Refaire l'évaluation
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Script pour améliorer l'expérience utilisateur
        document.addEventListener('DOMContentLoaded', function() {
            // Animation simple pour les messages
            const messages = document.querySelectorAll('.message');
            messages.forEach((message, index) => {
                message.style.opacity = '0';
                message.style.transform = 'translateY(-10px)';
                
                setTimeout(() => {
                    message.style.transition = 'all 0.3s ease';
                    message.style.opacity = '1';
                    message.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Validation des fichiers avant envoi
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const fileInputs = this.querySelectorAll('input[type="file"]');
                    let hasFiles = false;
                    
                    fileInputs.forEach(input => {
                        if (input.files.length > 0) {
                            hasFiles = true;
                        }
                    });
                    
                    if (!hasFiles) {
                        e.preventDefault();
                        alert('Veuillez sélectionner au moins un fichier à téléverser.');
                    }
                });
            }
        });
    </script>
</body>
</html>