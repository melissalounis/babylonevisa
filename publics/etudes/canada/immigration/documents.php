<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_eligible = $_SESSION['is_eligible'] ?? false;
$program = $_SESSION['program'] ?? 'express';
$civilStatus = $_SESSION['situation_familiale'] ?? 'single';

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

// Récupérer l'évaluation de l'utilisateur
$stmt = $pdo->prepare("
    SELECT e.* FROM evaluations_immigration e 
    JOIN users u ON e.email = u.email 
    WHERE u.id = ? 
    ORDER BY e.date_soumission DESC 
    LIMIT 1
");
$stmt->execute([$user_id]);
$evaluation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$evaluation) {
    die("Aucune évaluation trouvée. Veuillez d'abord compléter votre évaluation.");
}

$evaluation_id = $evaluation['id'];
$program = $evaluation['programme']; // Utiliser le programme de l'évaluation

// Fonction : documents requis selon le programme
function getRequiredDocuments($program, $civilStatus) {
    // Documents de base communs
    $docs = [
        'passeport' => 'Copie du passeport valide',
        'id_photo' => 'Photo d\'identité récente',
        'cv' => 'Curriculum Vitae (CV)',
        'naissance' => 'Certificat de naissance',
    ];

    if ($program === 'express') {
        $programDocs = [
            'ielts' => 'Résultats IELTS ou CELPIP',
            'tef' => 'Résultats TEF Canada',
            'eca' => 'Évaluation des diplômes (ECA)',
            'experience' => 'Attestations d\'expérience professionnelle',
            'releves_bancaires' => 'Relevés bancaires (6 mois)',
        ];
    } else {
        $programDocs = [
            'tef' => 'Résultats TEF Québec',
            'ielts' => 'Résultats IELTS (si applicable)',
            'diplomes' => 'Diplômes et relevés de notes',
            'experience' => 'Attestations d\'expérience professionnelle',
            'offre_emploi' => 'Offre d\'emploi validée Québec',
        ];
    }

    $familyDocs = [];
    if ($civilStatus === 'marie' || $civilStatus === 'married') {
        $familyDocs = [
            'mariage' => 'Certificat de mariage',
            'spouse_passport' => 'Passeport du conjoint',
            'birth_certificates' => 'Actes de naissance enfants',
        ];
    }

    return array_merge($docs, $programDocs, $familyDocs);
}

$requiredDocs = getRequiredDocuments($program, $evaluation['situation_familiale']);

// Traitement upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document'])) {
    $type_document = $_POST['type_document'];
    $file = $_FILES['document'];
    $errors = [];

    if ($file['error'] === UPLOAD_ERR_OK) {
        // Validation du fichier
        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx'];
        $max_size = 5 * 1024 * 1024; // 5MB
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_types)) {
            $errors[] = "Type de fichier non autorisé. Formats acceptés: PDF, JPG, PNG, DOC, DOCX";
        }
        
        if ($file['size'] > $max_size) {
            $errors[] = "Fichier trop volumineux. Taille maximum: 5MB";
        }
        
        if (empty($type_document)) {
            $errors[] = "Veuillez sélectionner un type de document";
        }

        if (empty($errors)) {
            $uploadDir = __DIR__ . "/uploads/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Générer un nom de fichier unique
            $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Vérifier si un document de ce type existe déjà
                $checkStmt = $pdo->prepare("SELECT id FROM documents_immigration WHERE evaluation_id = ? AND type_document = ?");
                $checkStmt->execute([$evaluation_id, $type_document]);
                $existingDoc = $checkStmt->fetch();

                if ($existingDoc) {
                    // Mettre à jour le document existant
                    $stmt = $pdo->prepare("UPDATE documents_immigration SET nom_fichier = ?, chemin_fichier = ?, taille = ?, date_upload = NOW() WHERE id = ?");
                    $stmt->execute([$file['name'], "uploads/" . $filename, $file['size'], $existingDoc['id']]);
                } else {
                    // Insérer un nouveau document
                    $stmt = $pdo->prepare("INSERT INTO documents_immigration (evaluation_id, type_document, nom_fichier, chemin_fichier, taille, date_upload) 
                                           VALUES (?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$evaluation_id, $type_document, $file['name'], "uploads/" . $filename, $file['size']]);
                }

                $_SESSION['success'] = "✅ Document '" . $requiredDocs[$type_document] . "' chargé avec succès.";
                header("Location: documents.php");
                exit;
            } else {
                $errors[] = "❌ Erreur lors du téléchargement du fichier.";
            }
        }
    } else {
        $errors[] = "⚠️ Erreur lors de l'upload du fichier.";
    }
}

// Récupérer les documents déjà uploadés
$stmt = $pdo->prepare("SELECT * FROM documents_immigration WHERE evaluation_id = ?");
$stmt->execute([$evaluation_id]);
$uploadedDocs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Créer un tableau des documents uploadés par type
$uploadedDocsByType = [];
foreach ($uploadedDocs as $doc) {
    $uploadedDocsByType[$doc['type_document']] = $doc;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chargement des documents - Immigration Canada</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            color: #2d3748;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            border-radius: 0 0 25px 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 8px 20px;
            border-radius: 25px;
            margin: 8px;
            font-size: 0.95rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .main-content {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 8px 25px rgba(220, 38, 38, 0.15);
            margin-bottom: 25px;
            border: 1px solid #fecaca;
        }

        .section-title {
            font-size: 1.6rem;
            color: #dc2626;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #fecaca;
            font-weight: 600;
        }

        .documents-list {
            space-y: 20px;
        }

        .doc-item {
            background: #fef2f2;
            border: 2px solid #fecaca;
            border-radius: 15px;
            padding: 25px;
            transition: all 0.3s ease;
        }

        .doc-item:hover {
            border-color: #dc2626;
            box-shadow: 0 5px 15px rgba(220, 38, 38, 0.1);
            transform: translateY(-2px);
        }

        .doc-item.uploaded {
            background: #f0f9ff;
            border-color: #bae6fd;
        }

        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .doc-title {
            font-weight: 600;
            color: #1e293b;
            font-size: 1.2rem;
        }

        .doc-status {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .status-uploaded {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .upload-form {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }

        input[type="file"] {
            flex: 1;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            background: white;
            transition: all 0.3s ease;
        }

        input[type="file"]:focus {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
            outline: none;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #b91c1c, #991b1b);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(220, 38, 38, 0.4);
        }

        .btn-outline {
            background: white;
            border: 2px solid #dc2626;
            color: #dc2626;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .btn-outline:hover {
            background: #dc2626;
            color: white;
            transform: translateY(-2px);
        }

        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 2px solid;
            font-weight: 500;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border-color: #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-color: #fecaca;
        }

        .alert-info {
            background: #eff6ff;
            color: #1e40af;
            border-color: #dbeafe;
        }

        .file-info {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 12px;
            padding: 10px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #dc2626;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }

        .progress-section {
            text-align: center;
        }

        .progress-bar {
            width: 100%;
            height: 12px;
            background: #e5e7eb;
            border-radius: 10px;
            margin: 15px 0;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            border-radius: 10px;
            transition: width 0.5s ease;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.3);
        }

        .progress-text {
            font-size: 1.1rem;
            font-weight: 600;
            color: #dc2626;
            margin: 10px 0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            header {
                padding: 30px 15px;
            }
            
            header h1 {
                font-size: 2rem;
            }
            
            .upload-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            input[type="file"] {
                width: 100%;
            }
            
            .navigation {
                flex-direction: column;
            }
            
            .doc-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1> Chargement des Documents</h1>
        <div>
            <span class="badge">Programme : <?= $program === 'express' ? 'Entrée Express' : 'Arrima Québec' ?></span>
            <span class="badge">Statut : <?= $is_eligible ? 'Éligible' : 'En attente' ?></span>
            <span class="badge">Documents : <?= count($uploadedDocs) ?>/<?= count($requiredDocs) ?></span>
        </div>
    </header>

    <div class="container">
        <!-- Navigation -->
        <div class="navigation">
            <a href="../../client/documents.php" class="btn btn-outline">
                 Voir mes documents
            </a>
            <a href="../../client/mes_demandes_immigration.php" class="btn btn-outline">
                 Mes demandes
            </a>
        </div>

        <!-- Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success'] ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($is_eligible): ?>
            <div class="alert alert-info">
                🎉 Félicitations ! Vous êtes admissible au programme <?= $program === 'express' ? 'Entrée Express' : 'Arrima Québec' ?>.
                Veuillez uploader tous les documents requis pour compléter votre demande.
            </div>
        <?php endif; ?>

        <!-- Liste des documents -->
        <div class="main-content">
            <h2 class="section-title"> Documents Requis</h2>
            
            <div class="documents-list">
                <?php foreach ($requiredDocs as $type => $docName): ?>
                    <?php $isUploaded = isset($uploadedDocsByType[$type]); ?>
                    <div class="doc-item <?= $isUploaded ? 'uploaded' : '' ?>">
                        <div class="doc-header">
                            <div class="doc-title"><?= htmlspecialchars($docName) ?></div>
                            <div class="doc-status <?= $isUploaded ? 'status-uploaded' : 'status-pending' ?>">
                                <?= $isUploaded ? '✅ Uploadé' : '⏳ En attente' ?>
                            </div>
                        </div>
                        
                        <form method="post" enctype="multipart/form-data" class="upload-form">
                            <input type="hidden" name="type_document" value="<?= $type ?>">
                            <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                            <button type="submit" class="btn btn-primary">
                                <?= $isUploaded ? '🔄 Remplacer' : '📤 Uploader' ?>
                            </button>
                        </form>

                        <?php if ($isUploaded): ?>
                            <div class="file-info">
                                <strong>Fichier :</strong> <?= htmlspecialchars($uploadedDocsByType[$type]['nom_fichier']) ?> 
                                | <strong>Taille :</strong> <?= round($uploadedDocsByType[$type]['taille'] / 1024 / 1024, 2) ?> MB
                                | <strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($uploadedDocsByType[$type]['date_upload'])) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Progression -->
        <div class="main-content">
            <h2 class="section-title"> Progression</h2>
            <div class="progress-section">
                <?php
                $totalDocs = count($requiredDocs);
                $uploadedCount = count($uploadedDocs);
                $percentage = $totalDocs > 0 ? round(($uploadedCount / $totalDocs) * 100) : 0;
                ?>
                <div class="progress-text">
                    <?= $uploadedCount ?> sur <?= $totalDocs ?> documents uploadés
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $percentage ?>%;"></div>
                </div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #dc2626;">
                    <?= $percentage ?>%
                </div>
            </div>
            
            <?php if ($uploadedCount === $totalDocs): ?>
                <div class="alert alert-success" style="text-align: center; margin-top: 20px;">
                    🎉 Tous vos documents sont uploadés ! Votre demande est complète.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Afficher le nom du fichier sélectionné
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name;
                if (fileName) {
                    console.log('Fichier sélectionné:', fileName);
                }
            });
        });

        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('.doc-item');
            items.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    item.style.transition = 'all 0.5s ease-out';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>