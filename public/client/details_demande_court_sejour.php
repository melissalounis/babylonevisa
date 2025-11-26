<?php
session_start();

// Configuration d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérification simple de la connexion
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo "<script>alert('Veuillez vous connecter pour accéder à cette page.'); window.location.href = 'login.php';</script>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer l'ID de la demande
$demande_id = $_GET['id'] ?? 0;

if (!$demande_id) {
    echo "<script>alert('Aucune demande spécifiée.'); window.location.href = 'mes_demandes_court_sejour.php';</script>";
    exit;
}

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les détails de la demande (avec date_creation)
    $stmt = $pdo->prepare("
        SELECT dcs.*, DATE_FORMAT(dcs.date_creation, '%d/%m/%Y à %H:%i') as date_formatted
        FROM demandes_court_sejour dcs
        WHERE dcs.id = ? AND dcs.user_id = ?
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        echo "<script>alert('Demande non trouvée ou accès non autorisé.'); window.location.href = 'mes_demandes_court_sejour.php';</script>";
        exit;
    }

    // Récupérer les fichiers associés
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_court_sejour_fichiers 
        WHERE demande_id = ? 
        ORDER BY type_fichier, date_upload
    ");
    $stmt->execute([$demande_id]);
    $fichiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur BDD details_demande: " . $e->getMessage());
    echo "<script>alert('Erreur de base de données.'); window.location.href = 'mes_demandes_court_sejour.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Demande #<?php echo $demande_id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #003366;
            --secondary-color: #0055aa;
            --accent-color: #ff6b35;
            --light-blue: #e8f2ff;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-color: #dbe4ee;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --error-color: #dc3545;
            --info-color: #17a2b8;
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
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--box-shadow);
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        header h1 {
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
        
        header p {
            opacity: 0.9;
        }
        
        .content {
            padding: 30px;
        }
        
        .detail-section {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
        }
        
        .detail-section h3 {
            color: var(--primary-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-blue);
        }
        
        .fichiers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .fichier-card {
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 15px;
            text-align: center;
        }
        
        .fichier-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .demande-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-weight: 500;
        }
        
        .statut-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .statut-en_attente {
            background: var(--warning-color);
            color: #000;
        }
        
        .statut-en_cours {
            background: var(--info-color);
            color: white;
        }
        
        .statut-approuve {
            background: var(--success-color);
            color: white;
        }
        
        .statut-refuse {
            background: var(--error-color);
            color: white;
        }
        
        .demande-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            padding-top: 20px;
            justify-content: center;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background: var(--light-blue);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-file-alt"></i> Détails de la Demande #<?php echo $demande_id; ?></h1>
            <p>Informations complètes de votre demande de visa</p>
        </header>
        
        <div class="content">
            <a href="mes_demandes_court_sejour.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Retour à mes demandes
            </a>
            
            <!-- Statut et informations générales -->
            <div class="detail-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>État de la demande</h3>
                    <div class="statut-badge statut-<?php echo $demande['statut']; ?>" style="font-size: 1rem;">
                        <?php 
                            $statuts = [
                                'en_attente' => 'En attente',
                                'en_cours' => 'En cours de traitement',
                                'approuve' => 'Approuvé',
                                'refuse' => 'Refusé'
                            ];
                            echo $statuts[$demande['statut']] ?? $demande['statut'];
                        ?>
                    </div>
                </div>
                
                <div class="demande-info">
                    <div class="info-item">
                        <span class="info-label">Date de soumission</span>
                        <span class="info-value"><?php echo $demande['date_formatted']; ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Type de visa</span>
                        <span class="info-value">
                            <?php 
                                $types = [
                                    'tourisme' => 'Tourisme',
                                    'affaires' => 'Affaires', 
                                    'visite_familiale' => 'Visite Familiale',
                                    'autre' => 'Autre'
                                ];
                                echo $types[$demande['visa_type']] ?? 'Autre';
                            ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Pays de destination</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['pays_destination']); ?></span>
                    </div>
                </div>
                
                <?php if ($demande['statut'] === 'refuse' && !empty($demande['motif_refus'])): ?>
                    <div class="info-item" style="margin-top: 15px;">
                        <span class="info-label">Motif du refus</span>
                        <span class="info-value" style="color: var(--error-color); font-style: italic;">
                            <?php echo htmlspecialchars($demande['motif_refus']); ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Informations personnelles -->
            <div class="detail-section">
                <h3><i class="fas fa-user"></i> Informations personnelles</h3>
                <div class="demande-info">
                    <div class="info-item">
                        <span class="info-label">Nom complet</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['nom_complet']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Date de naissance</span>
                        <span class="info-value">
                            <?php 
                                if (!empty($demande['date_naissance'])) {
                                    echo date('d/m/Y', strtotime($demande['date_naissance']));
                                } else {
                                    echo 'Non spécifiée';
                                }
                            ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Lieu de naissance</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['lieu_naissance'] ?? 'Non spécifié'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">État civil</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['etat_civil'] ?? 'Non spécifié'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Nationalité</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['nationalite'] ?? 'Non spécifiée'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Profession</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['profession'] ?? 'Non spécifiée'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Adresse</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['adresse'] ?? 'Non spécifiée'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Téléphone</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['telephone'] ?? 'Non spécifié'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['email'] ?? 'Non spécifié'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Informations passeport -->
            <div class="detail-section">
                <h3><i class="fas fa-id-card"></i> Passeport</h3>
                <div class="demande-info">
                    <div class="info-item">
                        <span class="info-label">Numéro de passeport</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['passeport'] ?? 'Non spécifié'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Pays de délivrance</span>
                        <span class="info-value"><?php echo htmlspecialchars($demande['pays_delivrance'] ?? 'Non spécifié'); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Date de délivrance</span>
                        <span class="info-value">
                            <?php 
                                if (!empty($demande['date_delivrance'])) {
                                    echo date('d/m/Y', strtotime($demande['date_delivrance']));
                                } else {
                                    echo 'Non spécifiée';
                                }
                            ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Date d'expiration</span>
                        <span class="info-value">
                            <?php 
                                if (!empty($demande['date_expiration'])) {
                                    echo date('d/m/Y', strtotime($demande['date_expiration']));
                                } else {
                                    echo 'Non spécifiée';
                                }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Fichiers joints -->
            <div class="detail-section">
                <h3><i class="fas fa-paperclip"></i> Documents joints (<?php echo count($fichiers); ?>)</h3>
                
                <?php if (empty($fichiers)): ?>
                    <p style="text-align: center; color: #666; padding: 20px;">
                        Aucun document joint à cette demande
                    </p>
                <?php else: ?>
                    <div class="fichiers-grid">
                        <?php foreach ($fichiers as $fichier): ?>
                            <div class="fichier-card">
                                <div class="fichier-icon">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div style="font-weight: 600; margin-bottom: 5px;">
                                    <?php 
                                        $types = [
                                            'copie_passeport' => 'Copie passeport',
                                            'documents_travail' => 'Documents travail',
                                            'lettre_invitation' => 'Lettre invitation',
                                            'justificatif_ressources' => 'Justificatif ressources',
                                            'lettre_prise_en_charge' => 'Lettre prise en charge',
                                            'prise_en_charge_entreprise' => 'Prise en charge entreprise',
                                            'invitation_entreprise' => 'Invitation entreprise',
                                            'copie_visa' => 'Copie visa'
                                        ];
                                        echo $types[$fichier['type_fichier']] ?? $fichier['type_fichier'];
                                    ?>
                                </div>
                                <div style="font-size: 0.8rem; color: #666; margin-bottom: 10px;">
                                    <?php 
                                        if (!empty($fichier['date_upload'])) {
                                            echo date('d/m/Y H:i', strtotime($fichier['date_upload']));
                                        } else {
                                            echo 'Date inconnue';
                                        }
                                    ?>
                                </div>
                                <a href="telecharger_fichier.php?id=<?php echo $fichier['id']; ?>" 
                                   class="btn btn-outline" style="font-size: 0.8rem; padding: 5px 10px;">
                                    <i class="fas fa-download"></i> Télécharger
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Actions -->
            <div class="detail-section" style="text-align: center;">
                <div class="demande-actions">
                    <?php if ($demande['statut'] === 'en_attente'): ?>
                        <a href="modifier_demande.php?id=<?php echo $demande_id; ?>" class="btn btn-outline">
                            <i class="fas fa-edit"></i> Modifier la demande
                        </a>
                        
                        <button onclick="annulerDemande(<?php echo $demande_id; ?>)" class="btn btn-outline" 
                                style="color: var(--error-color); border-color: var(--error-color);">
                            <i class="fas fa-times"></i> Annuler la demande
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function annulerDemande(demandeId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette demande ? Cette action est irréversible.')) {
                fetch('annuler_demande.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + demandeId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Demande annulée avec succès');
                        window.location.href = 'mes_demandes_court_sejour.php';
                    } else {
                        alert('Erreur lors de l\'annulation: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors de l\'annulation');
                });
            }
        }
    </script>
</body>
</html>