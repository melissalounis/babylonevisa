<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifier si l'ID de la demande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mes_demandes_roumanie.php");
    exit;
}

$demande_id = intval($_GET['id']);

// Paramètres de connexion
require_once __DIR__ . '../../../config.php';
// Initialiser les variables
$demande = null;
$error_message = '';
$success_message = '';

try {
    

    // Récupérer l'email de l'utilisateur
    $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user = $stmt_user->fetch();
    
    if ($user && isset($user['email'])) {
        $user_email = $user['email'];
        
        // Récupérer les détails de la demande
        $stmt = $pdo->prepare("
            SELECT * FROM demandes_etudes_roumanie 
            WHERE id = ? AND email = ? AND statut = 'nouveau'
        ");
        $stmt->execute([$demande_id, $user_email]);
        $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$demande) {
            $error_message = "Demande non trouvée ou non modifiable.";
        }
    } else {
        $error_message = "Utilisateur non trouvé.";
    }

} catch (PDOException $e) {
    $error_message = "Erreur de connexion à la base de données: " . $e->getMessage();
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $demande) {
    try {
        // Gestion de l'upload des fichiers
        $upload_dir = "../../uploads/roumanie/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Fonction pour uploader un fichier
        function uploadFile($file, $upload_dir, $ancien_fichier = null) {
            // Si un nouveau fichier est uploadé
            if ($file['error'] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . basename($file['name']);
                $target_path = $upload_dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    // Supprimer l'ancien fichier s'il existe
                    if ($ancien_fichier && file_exists($upload_dir . $ancien_fichier)) {
                        unlink($upload_dir . $ancien_fichier);
                    }
                    return $filename;
                }
            }
            // Si pas de nouveau fichier, garder l'ancien
            return $ancien_fichier;
        }

        // Préparer les données pour la mise à jour
        $update_data = [
            'nom_complet' => $_POST['nom'],
            'telephone' => $_POST['telephone'],
            'specialite' => $_POST['specialite'],
            'programme_langue' => $_POST['programme_langue'],
            'niveau_etude' => $_POST['niveau'],
            'date_maj' => date('Y-m-d H:i:s')
        ];

        // Gestion du certificat de langue
        if (!empty($_POST['certificat_type']) && !empty($_POST['certificat_score'])) {
            $update_data['certificat_type'] = $_POST['certificat_type'];
            $update_data['certificat_score'] = $_POST['certificat_score'];
            
            // Upload du nouveau fichier certificat
            if (isset($_FILES['certificat_file']) && $_FILES['certificat_file']['error'] === UPLOAD_ERR_OK) {
                $update_data['certificat_file'] = uploadFile($_FILES['certificat_file'], $upload_dir, $demande['certificat_file']);
            } else {
                $update_data['certificat_file'] = $demande['certificat_file'];
            }
        } else {
            $update_data['certificat_type'] = null;
            $update_data['certificat_score'] = null;
            // Supprimer l'ancien fichier certificat s'il existe
            if ($demande['certificat_file'] && file_exists($upload_dir . $demande['certificat_file'])) {
                unlink($upload_dir . $demande['certificat_file']);
            }
            $update_data['certificat_file'] = null;
        }

        // Upload des autres fichiers
        $file_fields = ['releve_2nde', 'releve_1ere', 'releve_terminale', 'releve_bac', 'diplome_bac', 'certificat_scolarite'];
        foreach ($file_fields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $update_data[$field] = uploadFile($_FILES[$field], $upload_dir, $demande[$field]);
            } else {
                $update_data[$field] = $demande[$field];
            }
        }

        // Construction de la requête UPDATE
        $set_parts = [];
        $params = [];
        foreach ($update_data as $key => $value) {
            $set_parts[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $demande_id;
        $params[] = $user_email;

        $sql = "UPDATE demandes_etudes_roumanie SET " . implode(', ', $set_parts) . " 
                WHERE id = ? AND email = ? AND statut = 'nouveau'";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            $success_message = "La demande a été modifiée avec succès !";
            // Recharger les données de la demande
            $stmt = $pdo->prepare("SELECT * FROM demandes_etudes_roumanie WHERE id = ? AND email = ?");
            $stmt->execute([$demande_id, $user_email]);
            $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error_message = "Aucune modification n'a été apportée.";
        }

    } catch (PDOException $e) {
        $error_message = "Erreur lors de la modification : " . $e->getMessage();
    }
}

// Si la demande n'existe pas ou n'est pas modifiable, rediriger
if (!$demande && empty($error_message)) {
    header("Location: mes_demandes_roumanie.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Demande - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgba(0, 43, 127, 0.7);
            --primary-hover: rgba(9, 47, 122, 0.7);
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --light-gray: #e9ecef;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        header {
            background: var(--primary-color);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }

        header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);
        }

        h1 {
            margin: 0;
            font-size: 2rem;
        }

        .breadcrumb {
            background: var(--light-gray);
            padding: 15px 25px;
            border-bottom: 1px solid #ddd;
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .content {
            padding: 25px;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: var(--light-gray);
            border-radius: var(--border-radius);
            transition: var(--transition);
            position: relative;
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary-color);
            border-radius: var(--border-radius) 0 0 var(--border-radius);
        }

        .form-section:hover {
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .section-title {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .required::after {
            content: " *";
            color: var(--danger-color);
        }

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 43, 127, 0.2);
        }

        .file-input-container {
            position: relative;
        }

        .file-info {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #666;
        }

        .file-info a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .file-info a:hover {
            text-decoration: underline;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .service-card {
            background: white;
            border: 2px solid #ddd;
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .service-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .service-card.selected {
            border-color: var(--primary-color);
            background-color: rgba(0, 43, 127, 0.05);
        }

        .service-card i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .language-certificate {
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--info-color);
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid var(--light-gray);
            flex-wrap: wrap;
            gap: 15px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .warning-message {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .service-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-edit"></i> Modifier la Demande</h1>
            <p>Modifiez les informations de votre demande d'études en Roumanie</p>
        </header>

        <div class="breadcrumb">
            <a href="../../index.php"><i class="fas fa-home"></i> Dashboard</a> &gt; 
            <a href="../demandes_etude.php">Demandes d'étude</a> &gt; 
            <a href="index.php">Roumanie</a> &gt; 
            <a href="mes_demandes_roumanie.php">Mes demandes</a> &gt; 
            <a href="details_demande.php?id=<?php echo $demande_id; ?>">Détails</a> &gt; Modifier
        </div>

        <div class="content">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($demande): ?>
                <div class="warning-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Attention :</strong> Vous ne pouvez modifier que les demandes avec le statut "Nouveau". 
                    Les champs marqués d'une astérisque (*) sont obligatoires.
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <!-- Section Programme -->
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-book-open"></i> Programme d'études</h3>
                        
                        <div class="form-group">
                            <label for="specialite" class="required">Spécialité</label>
                            <input type="text" id="specialite" name="specialite" required
                                   value="<?php echo htmlspecialchars($demande['specialite']); ?>"
                                   placeholder="Ex: Médecine, Informatique, Génie civil...">
                        </div>

                        <div class="form-group">
                            <label class="required">Langue du programme</label>
                            <div class="service-grid">
                                <div class="service-card <?php echo $demande['programme_langue'] === 'français' ? 'selected' : ''; ?>" 
                                     onclick="selectLanguage(this, 'français')">
                                    <span class="language-badge">FR</span>
                                    <i class="fas fa-language"></i>
                                    <h4>Français</h4>
                                </div>
                                <div class="service-card <?php echo $demande['programme_langue'] === 'anglais' ? 'selected' : ''; ?>" 
                                     onclick="selectLanguage(this, 'anglais')">
                                    <span class="language-badge">EN</span>
                                    <i class="fas fa-language"></i>
                                    <h4>Anglais</h4>
                                </div>
                                <div class="service-card <?php echo $demande['programme_langue'] === 'roumain' ? 'selected' : ''; ?>" 
                                     onclick="selectLanguage(this, 'roumain')">
                                    <span class="language-badge">RO</span>
                                    <i class="fas fa-language"></i>
                                    <h4>Roumain</h4>
                                </div>
                            </div>
                            <input type="hidden" id="programme_langue" name="programme_langue" required 
                                   value="<?php echo htmlspecialchars($demande['programme_langue']); ?>">
                        </div>
                        
                        <!-- Section pour attestation de langue -->
                        <div id="language-certificate-section" class="language-certificate <?php echo (in_array($demande['programme_langue'], ['français', 'anglais'])) ? '' : 'hidden'; ?>">
                            <h4><i class="fas fa-file-certificate"></i> Attestation de niveau de langue</h4>
                            <div class="form-group">
                                <label class="required">Type de certificat</label>
                                <select id="certificat_type" name="certificat_type">
                                    <option value="">-- Sélectionnez --</option>
                                    <option id="option-tcf" class="hidden" value="TCF" <?php echo $demande['certificat_type'] === 'TCF' ? 'selected' : ''; ?>>TCF</option>
                                    <option id="option-delf" class="hidden" value="DELF" <?php echo $demande['certificat_type'] === 'DELF' ? 'selected' : ''; ?>>DELF</option>
                                    <option id="option-dalf" class="hidden" value="DALF" <?php echo $demande['certificat_type'] === 'DALF' ? 'selected' : ''; ?>>DALF</option>
                                    <option id="option-toefl" class="hidden" value="TOEFL" <?php echo $demande['certificat_type'] === 'TOEFL' ? 'selected' : ''; ?>>TOEFL</option>
                                    <option id="option-ielts" class="hidden" value="IELTS" <?php echo $demande['certificat_type'] === 'IELTS' ? 'selected' : ''; ?>>IELTS</option>
                                    <option id="option-toeic" class="hidden" value="TOEIC" <?php echo $demande['certificat_type'] === 'TOEIC' ? 'selected' : ''; ?>>TOEIC</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="required">Score/Niveau</label>
                                <input type="text" id="certificat_score" name="certificat_score" 
                                       value="<?php echo htmlspecialchars($demande['certificat_score'] ?? ''); ?>"
                                       placeholder="Ex: B2, 550, 6.5...">
                            </div>
                            
                            <div class="form-group">
                                <label>Nouveau certificat (laisser vide pour conserver l'actuel)</label>
                                <input type="file" id="certificat_file" name="certificat_file" accept=".pdf,.jpg,.jpeg,.png">
                                <?php if (!empty($demande['certificat_file'])): ?>
                                    <div class="file-info">
                                        Fichier actuel : 
                                        <a href="telecharger_fichier.php?file=<?php echo urlencode($demande['certificat_file']); ?>&type=certificat" target="_blank">
                                            <i class="fas fa-download"></i> Télécharger
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Informations personnelles -->
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-user"></i> Informations personnelles</h3>
                        
                        <div class="form-group">
                            <label for="nom" class="required">Nom complet</label>
                            <input type="text" id="nom" name="nom" required 
                                   value="<?php echo htmlspecialchars($demande['nom_complet']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone" class="required">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" required 
                                   value="<?php echo htmlspecialchars($demande['telephone']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="niveau" class="required">Niveau d'études</label>
                            <select id="niveau" name="niveau" required onchange="showDocs()">
                                <option value="">-- Sélectionnez --</option>
                                <option value="bac" <?php echo $demande['niveau_etude'] === 'bac' ? 'selected' : ''; ?>>Baccalauréat</option>
                                <option value="l1" <?php echo $demande['niveau_etude'] === 'l1' ? 'selected' : ''; ?>>Licence 1</option>
                                <option value="l2" <?php echo $demande['niveau_etude'] === 'l2' ? 'selected' : ''; ?>>Licence 2</option>
                                <option value="l3" <?php echo $demande['niveau_etude'] === 'l3' ? 'selected' : ''; ?>>Licence 3</option>
                                <option value="master" <?php echo $demande['niveau_etude'] === 'master' ? 'selected' : ''; ?>>Master</option>
                            </select>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="form-section">
                        <h3 class="section-title"><i class="fas fa-file-alt"></i> Documents</h3>
                        <div class="warning-message">
                            <i class="fas fa-info-circle"></i>
                            Vous pouvez remplacer les fichiers existants en uploadant de nouveaux documents.
                            Si vous ne sélectionnez pas de nouveau fichier, le document actuel sera conservé.
                        </div>
                        <div id="docsContainer"></div>
                    </div>

                    <!-- Actions -->
                    <div class="form-actions">
                        <div>
                            <a href="details_demande.php?id=<?php echo $demande_id; ?>" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Annuler
                            </a>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Demande non modifiable</h3>
                    <p>Cette demande ne peut pas être modifiée. Elle a peut-être déjà été traitée ou vous n'avez pas l'autorisation de la modifier.</p>
                    <a href="mes_demandes_roumanie.php" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-arrow-left"></i> Retour aux demandes
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Configuration des documents par niveau
        const configDocs = {
            bac: [
                { label: "Relevé de notes 2nde", name: "releve_2nde", current: "<?php echo htmlspecialchars($demande['releve_2nde'] ?? ''); ?>" },
                { label: "Relevé de notes 1ère", name: "releve_1ere", current: "<?php echo htmlspecialchars($demande['releve_1ere'] ?? ''); ?>" },
                { label: "Relevé de notes Terminale", name: "releve_terminale", current: "<?php echo htmlspecialchars($demande['releve_terminale'] ?? ''); ?>" },
                { label: "Relevé de notes Bac", name: "releve_bac", current: "<?php echo htmlspecialchars($demande['releve_bac'] ?? ''); ?>" },
                { label: "Certificat de scolarité", name: "certificat_scolarite", current: "<?php echo htmlspecialchars($demande['certificat_scolarite'] ?? ''); ?>" }
            ],
            l1: [
                { label: "Relevé de notes 2nde", name: "releve_2nde", current: "<?php echo htmlspecialchars($demande['releve_2nde'] ?? ''); ?>" },
                { label: "Relevé de notes 1ère", name: "releve_1ere", current: "<?php echo htmlspecialchars($demande['releve_1ere'] ?? ''); ?>" },
                { label: "Relevé de notes Terminale", name: "releve_terminale", current: "<?php echo htmlspecialchars($demande['releve_terminale'] ?? ''); ?>" },
                { label: "Relevé de notes Bac", name: "releve_bac", current: "<?php echo htmlspecialchars($demande['releve_bac'] ?? ''); ?>" },
                { label: "Diplôme Bac", name: "diplome_bac", current: "<?php echo htmlspecialchars($demande['diplome_bac'] ?? ''); ?>" },
                { label: "Certificat de scolarité", name: "certificat_scolarite", current: "<?php echo htmlspecialchars($demande['certificat_scolarite'] ?? ''); ?>" }
            ],
            l2: [
                { label: "Relevé de notes Bac", name: "releve_bac", current: "<?php echo htmlspecialchars($demande['releve_bac'] ?? ''); ?>" },
                { label: "Diplôme Bac", name: "diplome_bac", current: "<?php echo htmlspecialchars($demande['diplome_bac'] ?? ''); ?>" },
                { label: "Relevé de notes L1", name: "releve_l1", current: "<?php echo htmlspecialchars($demande['releve_l1'] ?? ''); ?>" },
                { label: "Certificat de scolarité", name: "certificat_scolarite", current: "<?php echo htmlspecialchars($demande['certificat_scolarite'] ?? ''); ?>" }
            ],
            l3: [
                { label: "Relevé de notes Bac", name: "releve_bac", current: "<?php echo htmlspecialchars($demande['releve_bac'] ?? ''); ?>" },
                { label: "Diplôme Bac", name: "diplome_bac", current: "<?php echo htmlspecialchars($demande['diplome_bac'] ?? ''); ?>" },
                { label: "Relevé de notes L1", name: "releve_l1", current: "<?php echo htmlspecialchars($demande['releve_l1'] ?? ''); ?>" },
                { label: "Relevé de notes L2", name: "releve_l2", current: "<?php echo htmlspecialchars($demande['releve_l2'] ?? ''); ?>" },
                { label: "Certificat de scolarité", name: "certificat_scolarite", current: "<?php echo htmlspecialchars($demande['certificat_scolarite'] ?? ''); ?>" }
            ],
            master: [
                { label: "Relevé de notes Bac", name: "releve_bac", current: "<?php echo htmlspecialchars($demande['releve_bac'] ?? ''); ?>" },
                { label: "Diplôme Bac", name: "diplome_bac", current: "<?php echo htmlspecialchars($demande['diplome_bac'] ?? ''); ?>" },
                { label: "Relevé de notes L1", name: "releve_l1", current: "<?php echo htmlspecialchars($demande['releve_l1'] ?? ''); ?>" },
                { label: "Relevé de notes L2", name: "releve_l2", current: "<?php echo htmlspecialchars($demande['releve_l2'] ?? ''); ?>" },
                { label: "Relevé de notes L3", name: "releve_l3", current: "<?php echo htmlspecialchars($demande['releve_l3'] ?? ''); ?>" },
                { label: "Diplôme Licence", name: "diplome_licence", current: "<?php echo htmlspecialchars($demande['diplome_licence'] ?? ''); ?>" },
                { label: "Certificat de scolarité", name: "certificat_scolarite", current: "<?php echo htmlspecialchars($demande['certificat_scolarite'] ?? ''); ?>" }
            ]
        };

        // Fonction pour sélectionner la langue
        function selectLanguage(card, langue) {
            // Retirer la sélection sur toutes les cartes
            document.querySelectorAll('.service-card').forEach(el => el.classList.remove('selected'));
            
            // Ajouter la sélection sur la carte cliquée
            card.classList.add('selected');
            
            // Mettre à jour l'input caché
            document.getElementById('programme_langue').value = langue;
            
            // Afficher ou masquer la section d'attestation de langue
            const certificateSection = document.getElementById('language-certificate-section');
            const certificatType = document.getElementById('certificat_type');
            const certificatScore = document.getElementById('certificat_score');
            
            if (langue === 'français' || langue === 'anglais') {
                certificateSection.classList.remove('hidden');
                
                // Afficher les options appropriées
                document.querySelectorAll('#certificat_type option').forEach(opt => {
                    opt.classList.add('hidden');
                });
                
                if (langue === 'français') {
                    document.getElementById('option-tcf').classList.remove('hidden');
                    document.getElementById('option-delf').classList.remove('hidden');
                    document.getElementById('option-dalf').classList.remove('hidden');
                } else if (langue === 'anglais') {
                    document.getElementById('option-toefl').classList.remove('hidden');
                    document.getElementById('option-ielts').classList.remove('hidden');
                    document.getElementById('option-toeic').classList.remove('hidden');
                }
                
                // Rendre les champs requis
                certificatType.required = true;
                certificatScore.required = true;
            } else {
                certificateSection.classList.add('hidden');
                
                // Rendre les champs non requis
                certificatType.required = false;
                certificatScore.required = false;
                certificatType.value = '';
                certificatScore.value = '';
            }
        }

        // Afficher les documents en fonction du niveau
        function showDocs() {
            let niveau = document.getElementById("niveau").value;
            let container = document.getElementById("docsContainer");
            container.innerHTML = "";
            
            if (configDocs[niveau]) {
                configDocs[niveau].forEach(doc => {
                    const fileId = doc.name + '_' + Math.floor(Math.random() * 1000);
                    let fileInfo = '';
                    
                    if (doc.current) {
                        fileInfo = `
                            <div class="file-info">
                                Fichier actuel : 
                                <a href="telecharger_fichier.php?file=${encodeURIComponent(doc.current)}&type=${doc.name}" target="_blank">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                            </div>
                        `;
                    }
                    
                    container.innerHTML += `
                        <div class="form-group file-input-container">
                            <label for="${fileId}">${doc.label} (remplacer)</label>
                            <input type="file" id="${fileId}" name="${doc.name}" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            ${fileInfo}
                        </div>
                    `;
                });
            }
        }

        // Initialiser la page
        document.addEventListener('DOMContentLoaded', function() {
            // Afficher les documents pour le niveau actuel
            showDocs();
            
            // Configurer les options de certificat selon la langue actuelle
            const currentLangue = "<?php echo htmlspecialchars($demande['programme_langue']); ?>";
            if (currentLangue === 'français' || currentLangue === 'anglais') {
                document.querySelectorAll('#certificat_type option').forEach(opt => {
                    opt.classList.add('hidden');
                });
                
                if (currentLangue === 'français') {
                    document.getElementById('option-tcf').classList.remove('hidden');
                    document.getElementById('option-delf').classList.remove('hidden');
                    document.getElementById('option-dalf').classList.remove('hidden');
                } else if (currentLangue === 'anglais') {
                    document.getElementById('option-toefl').classList.remove('hidden');
                    document.getElementById('option-ielts').classList.remove('hidden');
                    document.getElementById('option-toeic').classList.remove('hidden');
                }
            }
        });

        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const langue = document.getElementById('programme_langue').value;
            
            if (!langue) {
                e.preventDefault();
                alert("Veuillez sélectionner une langue de programme.");
                return;
            }
            
            // Validation pour les attestations de langue
            if (langue === 'français' || langue === 'anglais') {
                const certificatType = document.getElementById('certificat_type').value;
                const certificatScore = document.getElementById('certificat_score').value;
                
                if (!certificatType) {
                    e.preventDefault();
                    alert("Veuillez sélectionner le type de certificat de langue.");
                    return;
                }
                
                if (!certificatScore) {
                    e.preventDefault();
                    alert("Veuillez entrer votre score/niveau de langue.");
                    return;
                }
            }
        });
    </script>
</body>
</html>