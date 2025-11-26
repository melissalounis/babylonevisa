<?php
session_start();

// Vérifier si l'ID de demande est présent
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$demande_id = intval($_GET['id']);

// Configuration de la base de données
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails de la demande
    $stmt = $pdo->prepare("SELECT * FROM demandes_court_sejour WHERE id = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        die("Demande non trouvée.");
    }

    // Récupérer les fichiers associés
    $stmt_files = $pdo->prepare("SELECT * FROM demandes_court_sejour_fichiers WHERE demande_id = ?");
    $stmt_files->execute([$demande_id]);
    $fichiers = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonction pour formater le type de visa
function formatTypeVisa($type) {
    $types = [
        'tourisme' => 'Tourisme',
        'affaires' => 'Affaires',
        'visite_familiale' => 'Visite Familiale',
        'autre' => 'Autre'
    ];
    return $types[$type] ?? $type;
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'warning'],
        'en_cours' => ['label' => 'En cours de traitement', 'class' => 'info'],
        'approuve' => ['label' => 'Approuvé', 'class' => 'success'],
        'refuse' => ['label' => 'Refusé', 'class' => 'danger']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary'];
}

$statut = formatStatut($demande['statut']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de Demande - Visa Court Séjour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #003366;
            --secondary: #0055aa;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .confirmation-container {
            max-width: 800px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .confirmation-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .confirmation-body {
            padding: 40px;
        }
        
        .reference-number {
            background: #e8f5e8;
            border: 2px dashed #28a745;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            font-size: 1.5rem;
            font-weight: bold;
            color: #155724;
        }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
        }
        
        .file-list {
            list-style: none;
            padding: 0;
        }
        
        .file-list li {
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            align-items: center;
        }
        
        .file-list li:last-child {
            border-bottom: none;
        }
        
        .file-list i {
            margin-right: 10px;
            color: var(--primary);
        }
        
        .badge-statut {
            font-size: 1rem;
            padding: 8px 16px;
            border-radius: 20px;
        }
        
        .next-steps {
            background: #e7f3ff;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
        }
        
        .step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .step-number {
            background: var(--primary);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-container">
            <div class="confirmation-header">
                <i class="fas fa-check-circle fa-5x mb-3"></i>
                <h1>Demande Enregistrée !</h1>
                <p class="mb-0">Votre demande de visa court séjour a été soumise avec succès</p>
            </div>
            
            <div class="confirmation-body">
                <!-- Référence et statut -->
                <div class="reference-number">
                    <i class="fas fa-hashtag me-2"></i>
                    Votre numéro de référence: 
                    <span class="d-block mt-2">VS-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></span>
                </div>
                
                <div class="text-center mb-4">
                    <span class="badge badge-statut bg-<?php echo $statut['class']; ?>">
                        <i class="fas fa-<?php echo $statut['icon'] ?? 'info-circle'; ?> me-1"></i>
                        <?php echo $statut['label']; ?>
                    </span>
                </div>
                
                <!-- Informations de la demande -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5><i class="fas fa-user me-2"></i>Informations Personnelles</h5>
                            <p><strong>Nom complet:</strong> <?php echo htmlspecialchars($demande['nom_complet']); ?></p>
                            <p><strong>Date de naissance:</strong> <?php echo date('d/m/Y', strtotime($demande['date_naissance'])); ?></p>
                            <p><strong>Nationalité:</strong> <?php echo htmlspecialchars($demande['nationalite']); ?></p>
                            <p><strong>Profession:</strong> <?php echo htmlspecialchars($demande['profession']); ?></p>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="info-card">
                            <h5><i class="fas fa-passport me-2"></i>Détails du Visa</h5>
                            <p><strong>Type de visa:</strong> <?php echo formatTypeVisa($demande['visa_type']); ?></p>
                            <p><strong>Pays de destination:</strong> <?php echo htmlspecialchars($demande['pays_destination']); ?></p>
                            <p><strong>Numéro passeport:</strong> <?php echo htmlspecialchars($demande['passeport']); ?></p>
                            <p><strong>Date de soumission:</strong> <?php echo date('d/m/Y H:i', strtotime($demande['date_creation'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Fichiers téléchargés -->
                <?php if (!empty($fichiers)): ?>
                <div class="info-card">
                    <h5><i class="fas fa-paperclip me-2"></i>Documents Soumis</h5>
                    <ul class="file-list">
                        <?php foreach($fichiers as $fichier): ?>
                            <li>
                                <i class="fas fa-file-pdf"></i>
                                <?php echo htmlspecialchars($fichier['type_fichier']); ?>
                                <small class="text-muted ms-2">
                                    (<?php echo date('d/m/Y H:i', strtotime($fichier['date_upload'])); ?>)
                                </small>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <!-- Prochaines étapes -->
                <div class="next-steps">
                    <h5><i class="fas fa-list-alt me-2"></i> Prochaines étapes:</h5>
                    
                    <div class="step">
                        <div class="step-number">1</div>
                        <div>
                            <strong>Vérification initiale</strong><br>
                            Notre équipe va vérifier la complétude de votre dossier sous 24-48 heures.
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div>
                            <strong>Traitement de la demande</strong><br>
                            Votre dossier sera examiné par nos services consulaires.
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div>
                            <strong>Notification</strong><br>
                            Vous serez informé par email de la décision concernant votre visa.
                        </div>
                    </div>
                </div>
                
                <!-- Informations importantes -->
                <div class="alert alert-info mt-4">
                    <h6><i class="fas fa-info-circle me-2"></i> Informations importantes</h6>
                    <ul class="mb-0">
                        <li>Conservez précieusement votre numéro de référence pour tout suivi</li>
                        <li>Le traitement peut prendre de 5 à 15 jours ouvrables</li>
                        <li>Vous pouvez suivre l'avancement de votre demande dans votre espace personnel</li>
                        <li>En cas de documents manquants, vous serez contacté par email</li>
                    </ul>
                </div>
                
                <!-- Actions -->
                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary me-3">
                        <i class="fas fa-plus me-2"></i>Nouvelle demande
                    </a>
                    <a href="../public/client/index.php" class="btn btn-outline-primary">
                        <i class="fas fa-user me-2"></i>Espace personnel
                    </a>
                </div>
                
                <!-- Contact -->
                <div class="text-center mt-4">
                    <small class="text-muted">
                        <i class="fas fa-phone me-1"></i> Contact: +33 1 23 45 67 89 | 
                        <i class="fas fa-envelope me-1"></i> babylone.service15@gmail.com
                    </small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>