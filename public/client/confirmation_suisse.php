<?php
session_start();

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$demande_id = $_GET['id'] ?? 0;

if (!$demande_id) {
    header("Location: mes_demandes_suisse.php");
    exit;
}

// Database connection
require_once __DIR__ . '../../../config.php';

try {
  

    // Get request details
    $stmt = $pdo->prepare("
        SELECT ds.*, u.name as user_name, u.email as user_email 
        FROM demandes_suisse ds 
        LEFT JOIN users u ON ds.user_id = u.id 
        WHERE ds.id = ? AND ds.user_id = ?
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        header("Location: mes_demandes_suisse.php");
        exit;
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Utility functions
function getStatutBadge($statut) {
    $badges = [
        'en_attente' => 'secondary',
        'en_traitement' => 'warning', 
        'approuvee' => 'success',
        'rejetee' => 'danger'
    ];
    return $badges[$statut] ?? 'secondary';
}

function getStatutText($statut) {
    $textes = [
        'en_attente' => 'En attente',
        'en_traitement' => 'En traitement',
        'approuvee' => 'Approuvée', 
        'rejetee' => 'Rejetée'
    ];
    return $textes[$statut] ?? $statut;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Demande Suisse #<?php echo $demande_id; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #D52B1E;
            --secondary-color: #f8f9fa;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .confirmation-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 700px;
            width: 100%;
        }
        
        .confirmation-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .confirmation-header {
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .demande-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            text-align: left;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
        }
        
        .info-value {
            color: #212529;
        }
        
        .btn-custom {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            background: #B22222;
            color: white;
            transform: translateY(-2px);
        }
        
        .statut-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .bg-en-attente { background-color: #6c757d; color: white; }
        .bg-en-traitement { background-color: #ffc107; color: black; }
        .bg-approuvee { background-color: #28a745; color: white; }
        .bg-rejetee { background-color: #dc3545; color: white; }
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .confirmation-container {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="confirmation-header">Demande Soumise avec Succès !</h1>
        <p class="lead">Votre candidature pour les études en Suisse a été enregistrée avec succès.</p>
        
        <div class="demande-info">
            <h5 class="mb-4"><i class="fas fa-info-circle"></i> Détails de votre demande</h5>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Numéro de référence:</span><br>
                    <strong class="info-value">#<?php echo $demande_id; ?></strong>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Statut:</span><br>
                    <span class="statut-badge bg-<?php echo getStatutBadge($demande['statut']); ?>">
                        <?php echo getStatutText($demande['statut']); ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Formation:</span><br>
                    <span class="info-value"><?php echo htmlspecialchars($demande['nom_formation']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Établissement:</span><br>
                    <span class="info-value"><?php echo htmlspecialchars($demande['etablissement']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Nom:</span><br>
                    <span class="info-value"><?php echo htmlspecialchars($demande['user_name']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Email:</span><br>
                    <span class="info-value"><?php echo htmlspecialchars($demande['user_email']); ?></span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Date de soumission:</span><br>
                    <span class="info-value">
                        <?php echo date('d/m/Y à H:i', strtotime($demande['date_creation'])); ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Dernière mise à jour:</span><br>
                    <span class="info-value">
                        <?php 
                        if (isset($demande['date_modification']) && !empty($demande['date_modification'])) {
                            echo date('d/m/Y à H:i', strtotime($demande['date_modification']));
                        } else {
                            echo date('d/m/Y à H:i', strtotime($demande['date_creation']));
                        }
                        ?>
                    </span>
                </div>
            </div>
        </div>
        
        <div class="alert alert-info mt-4">
            <h6><i class="fas fa-clock"></i> Prochaines étapes</h6>
            <p class="mb-0">Votre demande est maintenant en cours de traitement. Vous serez notifié par email à chaque mise à jour de statut. Vous pouvez suivre l'avancement dans la section "Mes Demandes".</p>
        </div>
        
        <div class="d-flex gap-3 justify-content-center mt-4 flex-wrap">
            <a href="mes_demandes_suisse.php" class="btn btn-custom">
                <i class="fas fa-list"></i> Mes Demandes
            </a>
            <a href="../suisse/etudes/index.php" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Nouvelle Demande
            </a>
            <a href="../index.php" class="btn btn-outline-secondary">
                <i class="fas fa-home"></i> Accueil
            </a>
        </div>
        
        <!-- Print button -->
        <div class="mt-4">
            <button class="btn btn-outline-info" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimer cette confirmation
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation on load
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.confirmation-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                container.style.transition = 'all 0.5s ease';
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
        
        // Print functionality
        function printConfirmation() {
            window.print();
        }
    </script>
</body>
</html>