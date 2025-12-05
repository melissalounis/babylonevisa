<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Connexion à la base de données
require_once __DIR__ . '../../../config.php';
try {
    
    // Récupérer les rendez-vous de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT 
            id,
            reference,
            pays_destination,
            type_demande,
            type_client,
            nom,
            prenom,
            date_arrivee,
            date_depart,
            statut,
            DATE_FORMAT(date_creation, '%d/%m/%Y à %H:%i') as date_creation_formatted,
            DATE_FORMAT(date_arrivee, '%d/%m/%Y') as date_arrivee_formatted,
            DATE_FORMAT(date_depart, '%d/%m/%Y') as date_depart_formatted
        FROM rendez_vous 
        WHERE email = (SELECT email FROM users WHERE id = ?)
        ORDER BY date_creation DESC
    ");
    
    $stmt->execute([$user_id]);
    $rendez_vous = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $rendez_vous = [];
    $error = "Erreur de connexion à la base de données";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Rendez-vous - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #003366, #0055aa);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-left: 4px solid #003366;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #003366;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
            cursor: pointer;
            min-width: 200px;
        }

        .rendez-vous-list {
            display: grid;
            gap: 20px;
        }

        .rendez-vous-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .rendez-vous-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #003366;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d1edff;
            color: #004085;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .card-info {
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

        .card-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #003366;
            color: white;
        }

        .btn-primary:hover {
            background: #0055aa;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #003366;
            color: #003366;
        }

        .btn-outline:hover {
            background: #003366;
            color: white;
        }

        .btn-danger {
            background: transparent;
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        .btn-danger:hover {
            background: #dc3545;
            color: white;
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

        .reference {
            background: #e8f2ff;
            color: #003366;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #f0f9ff;
            border-color: #28a745;
            color: #155724;
        }

        .alert-error {
            background: #fef2f2;
            border-color: #dc3545;
            color: #721c24;
        }

        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .card-info {
                grid-template-columns: 1fr;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .filter-select {
                min-width: auto;
            }
            
            .card-actions {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-calendar-check"></i> Mes Rendez-vous Visa</h1>
            <p>Consultez l'état de vos demandes de rendez-vous</p>
        </header>
        
        <div class="content">
            <!-- Message de succès -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($rendez_vous); ?></div>
                    <div class="stat-label">Total des rendez-vous</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo count(array_filter($rendez_vous, function($rv) { return $rv['statut'] === 'en_attente'; })); ?>
                    </div>
                    <div class="stat-label">En attente</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo count(array_filter($rendez_vous, function($rv) { return $rv['statut'] === 'confirme'; })); ?>
                    </div>
                    <div class="stat-label">Confirmés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <?php echo count(array_filter($rendez_vous, function($rv) { return $rv['statut'] === 'annule'; })); ?>
                    </div>
                    <div class="stat-label">Annulés</div>
                </div>
            </div>
            
            <!-- Filtres -->
            <div class="filters">
                <select class="filter-select" id="statusFilter" onchange="filterRendezVous()">
                    <option value="all">Tous les statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="confirme">Confirmés</option>
                    <option value="annule">Annulés</option>
                </select>
                
                <select class="filter-select" id="typeFilter" onchange="filterRendezVous()">
                    <option value="all">Tous les types</option>
                    <option value="premiere_demande">Première demande</option>
                    <option value="renouvellement">Renouvellement</option>
                </select>
            </div>
            
            <!-- Liste des rendez-vous -->
            <div class="rendez-vous-list" id="rendezVousList">
                <?php if (empty($rendez_vous)): ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>Aucun rendez-vous trouvé</h3>
                        <p>Vous n'avez pas encore pris de rendez-vous pour un visa.</p>
                        <a href="/../../rendez_vous.php" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Prendre un rendez-vous
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($rendez_vous as $rdv): ?>
                        <div class="rendez-vous-card" 
                             data-statut="<?php echo $rdv['statut']; ?>" 
                             data-type="<?php echo $rdv['type_demande']; ?>">
                            
                            <div class="card-header">
                                <div class="card-title">
                                    Rendez-vous <span class="reference"><?php echo $rdv['reference']; ?></span>
                                </div>
                                <div class="status-badge status-<?php echo $rdv['statut']; ?>">
                                    <?php 
                                        switch($rdv['statut']) {
                                            case 'en_attente': echo 'En attente'; break;
                                            case 'confirme': echo 'Confirmé'; break;
                                            case 'annule': echo 'Annulé'; break;
                                            default: echo $rdv['statut'];
                                        }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="card-info">
                                <div class="info-item">
                                    <span class="info-label">Nom complet</span>
                                    <span class="info-value"><?php echo htmlspecialchars($rdv['prenom'] . ' ' . $rdv['nom']); ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Pays de destination</span>
                                    <span class="info-value"><?php echo htmlspecialchars($rdv['pays_destination']); ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Type de demande</span>
                                    <span class="info-value">
                                        <?php echo $rdv['type_demande'] === 'premiere_demande' ? 'Première demande' : 'Renouvellement'; ?>
                                    </span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Dates de séjour</span>
                                    <span class="info-value">
                                        <?php echo $rdv['date_arrivee_formatted']; ?> - <?php echo $rdv['date_depart_formatted']; ?>
                                    </span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Type de client</span>
                                    <span class="info-value">
                                        <?php 
                                            switch($rdv['type_client']) {
                                                case 'individuel': echo 'Individuel'; break;
                                                case 'famille': echo 'Famille'; break;
                                                case 'groupe': echo 'Groupe'; break;
                                                default: echo $rdv['type_client'];
                                            }
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Date de création</span>
                                    <span class="info-value"><?php echo $rdv['date_creation_formatted']; ?></span>
                                </div>
                            </div>
                            
                            <div class="card-actions">
                                <a href="details_rendez_vous.php?id=<?php echo $rdv['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Voir les détails
                                </a>
                                
                                <?php if ($rdv['statut'] === 'en_attente'): ?>
                                    <a href="modifier_rendez_vous.php?id=<?php echo $rdv['id']; ?>" class="btn btn-outline">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    
                                    <button onclick="annulerRendezVous(<?php echo $rdv['id']; ?>)" 
                                            class="btn btn-danger">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Bouton nouveau rendez-vous -->
            <?php if (!empty($rendez_vous)): ?>
                <div style="text-align: center; margin-top: 30px;">
                    <a href="rendez_vous.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouveau rendez-vous
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Filtrer les rendez-vous
        function filterRendezVous() {
            const statusFilter = document.getElementById('statusFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const cards = document.querySelectorAll('.rendez-vous-card');
            
            cards.forEach(card => {
                const statut = card.getAttribute('data-statut');
                const type = card.getAttribute('data-type');
                
                const statutMatch = statusFilter === 'all' || statut === statusFilter;
                const typeMatch = typeFilter === 'all' || type === typeFilter;
                
                if (statutMatch && typeMatch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        
        // Annuler un rendez-vous
        function annulerRendezVous(rendezVousId) {
            if (confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ? Cette action est irréversible.')) {
                // Envoyer la requête d'annulation
                fetch('annuler_rendez_vous.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + rendezVousId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Rendez-vous annulé avec succès');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erreur lors de l\'annulation');
                    console.error('Error:', error);
                });
            }
        }
        
        // Initialiser les filtres au chargement
        document.addEventListener('DOMContentLoaded', function() {
            filterRendezVous();
        });
    </script>
</body>
</html>