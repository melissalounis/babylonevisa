<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Vous devez être connecté pour consulter vos demandes.");
}

// Initialiser les variables
$demandes = [];
$error_message = null;

try {
    // Inclure le fichier de configuration de la base de données
    require_once __DIR__ . '/../../config.php';
    
    // Vérifier si la connexion PDO existe
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("Connexion à la base de données non disponible");
    }
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user_id = $_SESSION['user_id'];

    // Vérifier si la table existe
    $stmt_check = $pdo->query("SHOW TABLES LIKE 'demandes_visa_travail'");
    if ($stmt_check->rowCount() == 0) {
        $error_message = "La table des demandes de visa travail n'existe pas encore.";
    } else {
        // Récupération des demandes
        $stmt = $pdo->prepare("SELECT * FROM demandes_visa_travail WHERE user_id = ? ORDER BY date_soumission DESC");
        $stmt->execute([$user_id]);
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    $error_message = "Erreur de base de données : " . $e->getMessage();
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Demandes de Visa Travail</title>
    <style>
        :root {
            --primary-color: #1a237e;
            --primary-light: #534bae;
            --primary-dark: #000051;
            --secondary-color: #e8eaf6;
            --accent-color: #ff6d00;
            --text-color: #212121;
            --text-light: #757575;
            --background: #f5f5f5;
            --white: #ffffff;
            --border-color: #ddd;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --error-color: #f44336;
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
            background-color: var(--background);
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 2rem;
            text-align: center;
            border-bottom: 5px solid var(--accent-color);
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        header h1 {
            font-weight: 600;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .error {
            background-color: #ffebee;
            border: 1px solid var(--error-color);
            color: #721c24;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-en_attente { color: var(--warning-color); }
        .stat-approuvee { color: var(--success-color); }
        .stat-rejetee { color: var(--error-color); }
        
        .demandes-container {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .demande-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }
        
        .demande-card:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-light);
        }
        
        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .demande-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .statut {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .statut-en_attente {
            background-color: #fff3e0;
            color: var(--warning-color);
            border: 1px solid #ffcc80;
        }
        
        .statut-approuvee {
            background-color: #e8f5e9;
            color: var(--success-color);
            border: 1px solid #a5d6a7;
        }
        
        .statut-rejetee {
            background-color: #ffebee;
            color: var(--error-color);
            border: 1px solid #ef9a9a;
        }
        
        .demande-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-group {
            margin-bottom: 0.5rem;
        }
        
        .info-group label {
            font-weight: 600;
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            display: block;
        }
        
        .info-group div {
            color: var(--text-color);
            padding: 0.5rem 0;
        }
        
        .demande-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(26, 35, 126, 0.3);
        }
        
        .btn-secondary {
            background-color: var(--secondary-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }
        
        .btn-secondary:hover {
            background-color: #e0e0e0;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border-color);
        }
        
        footer {
            text-align: center;
            padding: 1.5rem;
            color: var(--text-light);
            margin-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        
        @media (max-width: 768px) {
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .demande-info {
                grid-template-columns: 1fr;
            }
            
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .demande-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1><i class="fas fa-briefcase"></i> Mes Demandes de Visa Travail</h1>
        <p>Consultez l'état de vos demandes de visa de travail</p>
    </header>

    <div class="container">
        <?php if (isset($error_message)): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistiques -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($demandes); ?></div>
                <div>Total des demandes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-en_attente">
                    <?php 
                    $en_attente = count(array_filter($demandes, function($d) { 
                        return isset($d['statut']) && $d['statut'] == 'en_attente'; 
                    })); 
                    echo $en_attente;
                    ?>
                </div>
                <div>En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-approuvee">
                    <?php 
                    $approuvee = count(array_filter($demandes, function($d) { 
                        return isset($d['statut']) && $d['statut'] == 'approuvee'; 
                    })); 
                    echo $approuvee;
                    ?>
                </div>
                <div>Approuvées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-rejetee">
                    <?php 
                    $rejetee = count(array_filter($demandes, function($d) { 
                        return isset($d['statut']) && $d['statut'] == 'rejetee'; 
                    })); 
                    echo $rejetee;
                    ?>
                </div>
                <div>Rejetées</div>
            </div>
        </div>
        
        <!-- Liste des demandes -->
        <div class="demandes-container">
            <h2 style="margin-bottom: 1.5rem; color: var(--primary-color);">
                <i class="fas fa-history"></i> Historique des demandes
            </h2>
            
            <?php if (empty($demandes)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Aucune demande trouvée</h3>
                    <p>Vous n'avez soumis aucune demande de visa de travail pour le moment.</p>
                    <a href="formulaire_visa.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Nouvelle demande
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($demandes as $demande): ?>
                    <div class="demande-card">
                        <div class="demande-header">
                            <div class="demande-title">
                                <i class="fas fa-file-alt"></i> Demande #<?php echo htmlspecialchars($demande['id'] ?? ''); ?> - 
                                <?php echo htmlspecialchars($demande['nom_complet'] ?? 'Non spécifié'); ?>
                            </div>
                            <div class="statut statut-<?php echo htmlspecialchars($demande['statut'] ?? 'en_attente'); ?>">
                                <?php 
                                $statuts = [
                                    'en_attente' => 'En attente',
                                    'approuvee' => 'Approuvée',
                                    'rejetee' => 'Rejetée'
                                ];
                                $statut = $demande['statut'] ?? 'en_attente';
                                echo $statuts[$statut] ?? ucfirst($statut);
                                ?>
                            </div>
                        </div>
                        
                        <div class="demande-info">
                            <div class="info-group">
                                <label><i class="fas fa-calendar-alt"></i> Date de soumission</label>
                                <div><?php 
                                    if (isset($demande['date_soumission']) && $demande['date_soumission'] != '0000-00-00 00:00:00') {
                                        echo date('d/m/Y H:i', strtotime($demande['date_soumission']));
                                    } else {
                                        echo 'Non spécifiée';
                                    }
                                ?></div>
                            </div>
                            <div class="info-group">
                                <label><i class="fas fa-building"></i> Employeur</label>
                                <div><?php echo htmlspecialchars($demande['employeur'] ?? 'Non spécifié'); ?></div>
                            </div>
                            <div class="info-group">
                                <label><i class="fas fa-file-contract"></i> Type de contrat</label>
                                <div><?php echo htmlspecialchars($demande['type_contrat'] ?? 'Non spécifié'); ?></div>
                            </div>
                            <div class="info-group">
                                <label><i class="fas fa-clock"></i> Durée du séjour</label>
                                <div><?php echo htmlspecialchars($demande['duree_sejour'] ?? '0'); ?> mois</div>
                            </div>
                        </div>
                        
                        <?php if (isset($demande['statut']) && $demande['statut'] != 'en_attente' && !empty($demande['date_traitement'])): ?>
                            <div class="info-group">
                                <label><i class="fas fa-calendar-check"></i> Date de traitement</label>
                                <div><?php 
                                    if ($demande['date_traitement'] != '0000-00-00 00:00:00') {
                                        echo date('d/m/Y H:i', strtotime($demande['date_traitement']));
                                    } else {
                                        echo 'Non spécifiée';
                                    }
                                ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($demande['notes'])): ?>
                            <div class="info-group">
                                <label><i class="fas fa-sticky-note"></i> Notes</label>
                                <div><?php echo htmlspecialchars($demande['notes']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="demande-actions">
                            <a href="voir_demande.php?id=<?php echo $demande['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Voir les détails
                            </a>
                            <?php if (isset($demande['statut']) && $demande['statut'] == 'en_attente'): ?>
                                <a href="modifier_demande.php?id=<?php echo $demande['id']; ?>" class="btn btn-secondary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled title="Modification non autorisée">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-secondary" onclick="imprimerDemande(<?php echo $demande['id']; ?>)">
                                <i class="fas fa-print"></i> Imprimer
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>© <?php echo date('Y'); ?> Service des visas de la France. Tous droits réservés.</p>
        <p style="margin-top: 0.5rem; font-size: 0.9rem;">
            <a href="index.php" style="color: var(--primary-color); text-decoration: none;">
                <i class="fas fa-home"></i> Retour au tableau de bord
            </a>
        </p>
    </footer>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        // Fonction pour imprimer une demande
        function imprimerDemande(id) {
            alert('Impression de la demande #' + id + '\nCette fonctionnalité sera implémentée prochainement.');
        }
        
        // Animation d'apparition progressive
        document.addEventListener('DOMContentLoaded', function() {
            const demandeCards = document.querySelectorAll('.demande-card');
            demandeCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>