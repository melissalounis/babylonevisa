<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Connexion BDD
require_once __DIR__ . '../../../config.php';

$erreurs = [];
$success = false;

try {
    

    // Récupérer les demandes de l'utilisateur pour afficher les références
    $stmt = $pdo->prepare("
        SELECT id, specialite, statut 
        FROM demandes_turquie 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Traitement de l'upload de documents
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $demande_id = intval($_POST['demande_id'] ?? 0);
        
        // Vérifier que la demande appartient à l'utilisateur
        $stmt_demande = $pdo->prepare("SELECT id FROM demandes_turquie WHERE id = ? AND user_id = ?");
        $stmt_demande->execute([$demande_id, $user_id]);
        $demande_valide = $stmt_demande->fetch();
        
        if (!$demande_valide) {
            $erreurs[] = "Demande non valide";
        } else {
            // Dossier d'upload
            $upload_dir = __DIR__ . '/uploads/turquie/' . $user_id . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Types de documents autorisés
            $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            // Traitement de chaque document
            $documents_uploades = [];
            
            foreach (['passeport', 'diplomes', 'releves_notes', 'photo_identite', 'certificat_langue'] as $doc_type) {
                if (isset($_FILES[$doc_type]) && $_FILES[$doc_type]['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES[$doc_type];
                    
                    // Vérification de la taille
                    if ($file['size'] > $max_size) {
                        $erreurs[] = "Le fichier " . $doc_type . " est trop volumineux (max 5MB)";
                        continue;
                    }
                    
                    // Vérification de l'extension
                    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    if (!in_array($file_extension, $allowed_types)) {
                        $erreurs[] = "Type de fichier non autorisé pour " . $doc_type . " (PDF, JPG, PNG, DOC uniquement)";
                        continue;
                    }
                    
                    // Génération d'un nom de fichier unique
                    $new_filename = $demande_id . '_' . $doc_type . '_' . time() . '.' . $file_extension;
                    $file_path = $upload_dir . $new_filename;
                    
                    // Déplacement du fichier
                    if (move_uploaded_file($file['tmp_name'], $file_path)) {
                        $documents_uploades[$doc_type] = $new_filename;
                    } else {
                        $erreurs[] = "Erreur lors de l'upload du document " . $doc_type;
                    }
                }
            }
            
            // Mise à jour en base de données si au moins un document uploadé
            if (!empty($documents_uploades) && empty($erreurs)) {
                $set_parts = [];
                $params = [];
                
                foreach ($documents_uploades as $doc_type => $filename) {
                    $set_parts[] = $doc_type . " = ?";
                    $params[] = $filename;
                }
                
                $params[] = $demande_id;
                $params[] = $user_id;
                
                $sql = "UPDATE demandes_turquie SET " . implode(', ', $set_parts) . " WHERE id = ? AND user_id = ?";
                $stmt_update = $pdo->prepare($sql);
                
                if ($stmt_update->execute($params)) {
                    $success = true;
                    $_SESSION['success'] = "Document(s) uploadé(s) avec succès";
                } else {
                    $erreurs[] = "Erreur lors de la mise à jour en base de données";
                }
            }
        }
    }

    // Récupérer l'état des documents pour chaque demande
    foreach ($demandes as &$demande) {
        $stmt_docs = $pdo->prepare("
            SELECT passeport, diplomes, releves_notes, photo_identite, certificat_langue 
            FROM demandes_turquie 
            WHERE id = ?
        ");
        $stmt_docs->execute([$demande['id']]);
        $documents = $stmt_docs->fetch(PDO::FETCH_ASSOC);
        
        $demande['documents'] = $documents;
        $demande['documents_complets'] = true;
        
        // Vérifier quels documents sont manquants
        $demande['documents_manquants'] = [];
        
        $documents_requis = ['passeport', 'diplomes', 'releves_notes', 'photo_identite'];
        if (!empty($demande['certificat_type']) && $demande['certificat_type'] !== 'sans') {
            $documents_requis[] = 'certificat_langue';
        }
        
        foreach ($documents_requis as $doc) {
            if (empty($documents[$doc])) {
                $demande['documents_complets'] = false;
                $demande['documents_manquants'][] = $doc;
            }
        }
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonction pour formater le nom du document
function formatDocumentName($doc_type) {
    $noms = [
        'passeport' => 'Passeport',
        'diplomes' => 'Diplômes',
        'releves_notes' => 'Relevés de notes',
        'photo_identite' => 'Photo d\'identité',
        'certificat_langue' => 'Certificat de langue',
        'passeport_manquant' => 'Passeport manquant',
        'diplomes_manquant' => 'Diplômes manquant',
        'releves_notes_manquant' => 'Relevés de notes manquant',
        'photo_identite_manquant' => 'Photo d\'identité manquant',
        'certificat_langue_manquant' => 'Certificat de langue manquant'
    ];
    return $noms[$doc_type] ?? $doc_type;
}

// Fonction pour formater le statut
function formatStatutTurquie($statut) {
    $statuts = [
        'en_attente' => ['label' => '⏳ En attente', 'class' => 'en_attente'],
        'en_traitement' => ['label' => '🔧 En traitement', 'class' => 'en_traitement'],
        'approuvee' => ['label' => '✅ Approuvée', 'class' => 'approuvee'],
        'refusee' => ['label' => '❌ Refusée', 'class' => 'refusee']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'en_attente'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents Requis - Turquie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #E30A17;
            --primary-hover: #c90814;
            --secondary-color: #FFFFFF;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-color: #dbe4ee;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            color: var(--dark-text);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-color), #c90814);
            color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color) 0%, white 50%, var(--primary-color) 100%);
        }

        .header h1 {
            margin-bottom: 10px;
            font-size: 2.2rem;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-danger {
            background: #f8d7da;
            border-left-color: var(--danger-color);
            color: #721c24;
        }

        .alert-success {
            background: #d4edda;
            border-left-color: var(--success-color);
            color: #155724;
        }

        .alert-warning {
            background: #fff3cd;
            border-left-color: var(--warning-color);
            color: #856404;
        }

        .alert-info {
            background: #d1ecf1;
            border-left-color: var(--info-color);
            color: #0c5460;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 968px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .card-header {
            background: var(--light-gray);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .card-header h2 {
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 25px;
        }

        .demande-item {
            background: white;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            transition: var(--transition);
        }

        .demande-item:hover {
            border-color: var(--primary-color);
            box-shadow: var(--box-shadow);
        }

        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .demande-ref {
            font-weight: bold;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-en_attente {
            background: #fff3cd;
            color: #856404;
        }

        .status-en_traitement {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-approuvee {
            background: #d4edda;
            color: #155724;
        }

        .status-refusee {
            background: #f8d7da;
            color: #721c24;
        }

        .documents-list {
            list-style: none;
            margin: 15px 0;
        }

        .documents-list li {
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .documents-list li:last-child {
            border-bottom: none;
        }

        .document-status {
            font-size: 0.85rem;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .status-present {
            background: #d4edda;
            color: #155724;
        }

        .status-manquant {
            background: #f8d7da;
            color: #721c24;
        }

        .upload-form {
            background: var(--light-gray);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-top: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #555;
        }

        .form-group select,
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        .form-group input[type="file"] {
            background: white;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--primary-hover));
            color: white;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-success {
            background: var(--success-color);
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .btn i {
            margin-right: 8px;
        }

        .requirements-list {
            list-style: none;
        }

        .requirements-list li {
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .requirements-list li:last-child {
            border-bottom: none;
        }

        .requirement-icon {
            color: var(--primary-color);
            margin-top: 2px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ddd;
        }

        .progress-container {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            margin: 20px 0;
        }

        .progress-bar {
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background: var(--success-color);
            transition: width 0.3s ease;
        }

        .documents-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .summary-item {
            text-align: center;
            padding: 15px;
            border-radius: var(--border-radius);
            background: var(--light-gray);
        }

        .summary-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .documents-summary {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Documents Requis - Turquie</h1>
            <p>Gérez les documents nécessaires pour vos demandes d'études en Turquie</p>
        </div>

        <!-- Messages d'alerte -->
        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-danger">
                <h4><i class="fas fa-exclamation-triangle"></i> Erreurs</h4>
                <ul>
                    <?php foreach ($erreurs as $erreur): ?>
                        <li><?php echo htmlspecialchars($erreur); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success || isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <?php 
                    echo htmlspecialchars($_SESSION['success'] ?? 'Document(s) uploadé(s) avec succès');
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <div class="grid-container">
            <!-- Colonne principale -->
            <div>
                <!-- Liste des demandes et documents -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-list"></i> Mes Demandes et Documents</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($demandes) > 0): ?>
                            <?php foreach ($demandes as $demande): 
                                $statut = formatStatutTurquie($demande['statut']);
                            ?>
                                <div class="demande-item">
                                    <div class="demande-header">
                                        <div class="demande-ref">
                                            TUR-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?>
                                            - <?php echo htmlspecialchars($demande['specialite']); ?>
                                        </div>
                                        <span class="status-badge status-<?php echo $statut['class']; ?>">
                                            <?php echo $statut['label']; ?>
                                        </span>
                                    </div>

                                    <!-- État des documents -->
                                    <div class="documents-list">
                                        <?php 
                                        $documents_requis = ['passeport', 'diplomes', 'releves_notes', 'photo_identite'];
                                        if (!empty($demande['certificat_type']) && $demande['certificat_type'] !== 'sans') {
                                            $documents_requis[] = 'certificat_langue';
                                        }
                                        
                                        foreach ($documents_requis as $doc_type): 
                                            $est_present = !empty($demande['documents'][$doc_type]);
                                        ?>
                                            <li>
                                                <span><?php echo formatDocumentName($doc_type); ?></span>
                                                <span class="document-status status-<?php echo $est_present ? 'present' : 'manquant'; ?>">
                                                    <?php echo $est_present ? '✓ Présent' : '✗ Manquant'; ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Formulaire d'upload -->
                                    <div class="upload-form">
                                        <form method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                            
                                            <div class="form-group">
                                                <label for="documents_<?php echo $demande['id']; ?>">Ajouter des documents :</label>
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                                    <?php foreach ($documents_requis as $doc_type): ?>
                                                        <div>
                                                            <label style="font-size: 0.8rem; color: #666;">
                                                                <?php echo formatDocumentName($doc_type); ?>
                                                            </label>
                                                            <input type="file" 
                                                                   name="<?php echo $doc_type; ?>" 
                                                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                                                   style="font-size: 0.8rem;">
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload"></i> Uploader les documents
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Message d'alerte si documents manquants -->
                                    <?php if (!$demande['documents_complets']): ?>
                                        <div class="alert alert-warning" style="margin-top: 15px;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Documents manquants :</strong>
                                            <?php 
                                                $manquants_formatted = array_map('formatDocumentName', $demande['documents_manquants']);
                                                echo implode(', ', $manquants_formatted);
                                            ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-success" style="margin-top: 15px;">
                                            <i class="fas fa-check-circle"></i>
                                            <strong>Tous les documents requis sont présents</strong>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <h3>Aucune demande Turquie</h3>
                                <p>Vous n'avez pas encore soumis de demande d'études en Turquie.</p>
                                <a href="formulaire_turquie.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Créer une demande
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Colonne latérale -->
            <div>
                <!-- Exigences des documents -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-requirements"></i> Exigences des Documents</h2>
                    </div>
                    <div class="card-body">
                        <ul class="requirements-list">
                            <li>
                                <i class="fas fa-passport requirement-icon"></i>
                                <div>
                                    <strong>Passeport</strong><br>
                                    <small>Copie couleur, valide au moins 1 an</small>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-graduation-cap requirement-icon"></i>
                                <div>
                                    <strong>Diplômes</strong><br>
                                    <small>Diplômes obtenus (Bac, Licence, etc.)</small>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-file-alt requirement-icon"></i>
                                <div>
                                    <strong>Relevés de notes</strong><br>
                                    <small>Relevés des 3 dernières années</small>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-id-card requirement-icon"></i>
                                <div>
                                    <strong>Photo d'identité</strong><br>
                                    <small>Photo récente, fond blanc, format passeport</small>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-language requirement-icon"></i>
                                <div>
                                    <strong>Certificat de langue</strong><br>
                                    <small>TYS, TOEFL ou IELTS si requis</small>
                                </div>
                            </li>
                        </ul>

                        <div class="alert alert-info" style="margin-top: 20px;">
                            <h4><i class="fas fa-info-circle"></i> Informations importantes</h4>
                            <p><strong>Formats acceptés :</strong> PDF, JPG, PNG, DOC, DOCX</p>
                            <p><strong>Taille max :</strong> 5MB par document</p>
                            <p><strong>Conseil :</strong> Scannez vos documents en haute résolution</p>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="card" style="margin-top: 30px;">
                    <div class="card-header">
                        <h2><i class="fas fa-bolt"></i> Actions Rapides</h2>
                    </div>
                    <div class="card-body">
                        <a href="mes_demandes_turquie.php" class="btn btn-outline" style="width: 100%; margin-bottom: 10px;">
                            <i class="fas fa-arrow-left"></i> Mes Demandes
                        </a>
                        <a href="formulaire_turquie.php" class="btn btn-primary" style="width: 100%; margin-bottom: 10px;">
                            <i class="fas fa-plus"></i> Nouvelle Demande
                        </a>
                        <a href="calendrier_turquie.php" class="btn btn-outline" style="width: 100%;">
                            <i class="fas fa-calendar"></i> Calendrier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gestion des formulaires d'upload
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const files = this.querySelectorAll('input[type="file"]');
                    let hasFile = false;
                    
                    files.forEach(file => {
                        if (file.files.length > 0) {
                            hasFile = true;
                        }
                    });
                    
                    if (!hasFile) {
                        e.preventDefault();
                        alert('Veuillez sélectionner au moins un document à uploader.');
                    }
                });
            });
            
            // Affichage des noms de fichiers
            const fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const fileName = this.files[0] ? this.files[0].name : 'Aucun fichier choisi';
                    const label = this.previousElementSibling;
                    if (label && label.tagName === 'LABEL') {
                        label.textContent = fileName;
                    }
                });
            });
        });
    </script>
</body>
</html>