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

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les demandes de court séjour de l'utilisateur (avec date_creation)
    $stmt = $pdo->prepare("
        SELECT 
            dcs.*,
            COUNT(df.id) as nb_fichiers,
            DATE_FORMAT(dcs.date_creation, '%d/%m/%Y à %H:%i') as date_formatted
        FROM demandes_court_sejour dcs
        LEFT JOIN demandes_court_sejour_fichiers df ON dcs.id = df.demande_id
        WHERE dcs.user_id = ?
        GROUP BY dcs.id
        ORDER BY dcs.date_creation DESC
    ");
    
    $stmt->execute([$user_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Erreur BDD mes_demandes: " . $e->getMessage());
    $demandes = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes de Visa Court Séjour</title>
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
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--box-shadow);
            border-left: 4px solid var(--primary-color);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .demandes-list {
            display: grid;
            gap: 20px;
        }
        
        .demande-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }
        
        .demande-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }
        
        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .demande-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
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
        
        .demande-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
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
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #999;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            background: white;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .demande-info {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-passport"></i> Mes Demandes de Visa Court Séjour</h1>
            <p>Consultez l'état de vos demandes et gérez vos documents</p>
        </header>
        
        <div class="content">
            <!-- Statistiques -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($demandes); ?></div>
                    <div class="stat-label">Total des demandes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo count(array_filter($demandes, function($d) { return $d['statut'] === 'en_attente'; })); ?>
                    </div>
                    <div class="stat-label">En attente</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo count(array_filter($demandes, function($d) { return $d['statut'] === 'approuve'; })); ?>
                    </div>
                    <div class="stat-label">Approuvées</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo count(array_filter($demandes, function($d) { return $d['statut'] === 'refuse'; })); ?>
                    </div>
                    <div class="stat-label">Refusées</div>
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="filters">
                <select class="filter-select" onchange="filterDemandes(this.value)">
                    <option value="all">Toutes les demandes</option>
                    <option value="en_attente">En attente</option>
                    <option value="en_cours">En cours de traitement</option>
                    <option value="approuve">Approuvées</option>
                    <option value="refuse">Refusées</option>
                </select>
                
                <select class="filter-select" onchange="filterByType(this.value)">
                    <option value="all">Tous les types</option>
                    <option value="tourisme">Tourisme</option>
                    <option value="affaires">Affaires</option>
                    <option value="visite_familiale">Visite familiale</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            
            <!-- Liste des demandes -->
            <div class="demandes-list">
                <?php if (empty($demandes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune demande trouvée</h3>
                        <p>Vous n'avez pas encore soumis de demande de visa court séjour.</p>
                        <a href="formulaire_visa.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Nouvelle demande
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($demandes as $demande): ?>
                        <div class="demande-card" data-statut="<?php echo $demande['statut']; ?>" data-type="<?php echo $demande['visa_type']; ?>">
                            <div class="demande-header">
                                <div class="demande-title">
                                    Demande #<?php echo $demande['id']; ?> - 
                                    <?php 
                                        $types = [
                                            'tourisme' => 'Tourisme',
                                            'affaires' => 'Affaires', 
                                            'visite_familiale' => 'Visite Familiale',
                                            'autre' => 'Autre'
                                        ];
                                        echo $types[$demande['visa_type']] ?? 'Autre';
                                    ?>
                                </div>
                                <div class="statut-badge statut-<?php echo $demande['statut']; ?>">
                                    <?php 
                                        $statuts = [
                                            'en_attente' => 'En attente',
                                            'en_cours' => 'En cours',
                                            'approuve' => 'Approuvé',
                                            'refuse' => 'Refusé'
                                        ];
                                        echo $statuts[$demande['statut']] ?? $demande['statut'];
                                    ?>
                                </div>
                            </div>
                            
                            <div class="demande-info">
                                <div class="info-item">
                                    <span class="info-label">Pays de destination</span>
                                    <span class="info-value"><?php echo htmlspecialchars($demande['pays_destination']); ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Nom complet</span>
                                    <span class="info-value"><?php echo htmlspecialchars($demande['nom_complet']); ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Date de création</span>
                                    <span class="info-value"><?php echo $demande['date_formatted']; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Fichiers joints</span>
                                    <span class="info-value">
                                        <i class="fas fa-paperclip"></i> 
                                        <?php echo $demande['nb_fichiers']; ?> fichier(s)
                                    </span>
                                </div>
                            </div>
                            
                            <?php if ($demande['statut'] === 'refuse' && !empty($demande['motif_refus'])): ?>
                                <div class="info-item" style="grid-column: 1 / -1;">
                                    <span class="info-label">Motif du refus</span>
                                    <span class="info-value" style="color: var(--error-color);">
                                        <?php echo htmlspecialchars($demande['motif_refus']); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="demande-actions">
                                <a href="details_demande_court_sejour.php?id=<?php echo $demande['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Voir les détails
                                </a>
                                
                                <?php if ($demande['statut'] === 'en_attente'): ?>
                                    <a href="modifier_demande.php?id=<?php echo $demande['id']; ?>" class="btn btn-outline">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    
                                    <button onclick="annulerDemande(<?php echo $demande['id']; ?>)" 
                                            class="btn btn-outline" 
                                            style="color: var(--error-color); border-color: var(--error-color);">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Bouton nouvelle demande -->
            <?php if (!empty($demandes)): ?>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="formulaire_visa.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle demande de visa
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterDemandes(statut) {
            const cards = document.querySelectorAll('.demande-card');
            cards.forEach(card => {
                if (statut === 'all' || card.getAttribute('data-statut') === statut) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        function filterByType(type) {
            const cards = document.querySelectorAll('.demande-card');
            cards.forEach(card => {
                if (type === 'all' || card.getAttribute('data-type') === type) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
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
                        location.reload();
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
        
        // Initialiser les filtres
        document.addEventListener('DOMContentLoaded', function() {
            // Afficher toutes les demandes par défaut
            filterDemandes('all');
        });
    </script>
</body>
</html>