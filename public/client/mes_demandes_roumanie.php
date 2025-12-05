<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Paramètres de connexion
require_once __DIR__ . '../../../config.php';

// Initialiser les variables
$demandes = [];
$error_message = '';
$stats = [
    'total' => 0,
    'nouveau' => 0,
    'en_cours' => 0,
    'approuve' => 0,
    'refuse' => 0
];

try {
   

    // Récupérer l'email de l'utilisateur
    $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user = $stmt_user->fetch();
    
    if ($user && isset($user['email'])) {
        $user_email = $user['email'];
        
        // Récupérer les demandes de l'utilisateur
        $stmt = $pdo->prepare("
            SELECT * FROM demandes_etudes_roumanie 
            WHERE email = ?
            ORDER BY date_soumission DESC
        ");
        $stmt->execute([$user_email]);
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculer les statistiques
        $stats['total'] = count($demandes);
        foreach ($demandes as $demande) {
            $statut = $demande['statut'] ?? 'nouveau';
            if (isset($stats[$statut])) {
                $stats[$statut]++;
            }
        }
    } else {
        $error_message = "Utilisateur non trouvé.";
    }

} catch (PDOException $e) {
    $error_message = "Erreur de connexion à la base de données: " . $e->getMessage();
}

// Vérifier si la table existe, sinon créer un message d'information
if (empty($demandes) && empty($error_message)) {
    // Vérifier si la table existe
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'demandes_etudes_roumanie'");
        $table_exists = $stmt->fetch();
        
        if (!$table_exists) {
            $error_message = "La table des demandes n'existe pas encore. Veuillez soumettre votre première demande.";
        }
    } catch (Exception $e) {
        // Ignorer les erreurs de vérification de table
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes Roumanie - Babylone Service</title>
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
            max-width: 1200px;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .stat-label {
            color: var(--text-color);
            font-size: 0.9rem;
        }

        .demandes-list {
            margin-top: 30px;
        }

        .demande-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .demande-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .demande-title {
            font-size: 1.3rem;
            color: var(--primary-color);
            margin: 0;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-nouveau { background: var(--info-color); color: white; }
        .status-en_cours { background: var(--warning-color); color: black; }
        .status-approuve { background: var(--success-color); color: white; }
        .status-refuse { background: var(--danger-color); color: white; }

        .demande-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 500;
            color: var(--text-color);
        }

        .demande-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-color);
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
            transform: translateY(-1px);
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
            color: #666;
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            background: white;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .info-message {
            background: #d1ecf1;
            color: #0c5460;
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
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
                align-items: flex-start;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-graduation-cap"></i> Mes Demandes - Roumanie</h1>
            <p>Suivez l'état de vos demandes d'études en Roumanie</p>
        </header>

        <div class="breadcrumb">
            <a href="../../index.php"><i class="fas fa-home"></i> Dashboard</a> &gt; 
            <a href="../demandes_etude.php">Demandes d'étude</a> &gt; 
            <a href="index.php">Roumanie</a> &gt; Mes demandes
        </div>

        <div class="content">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total des demandes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['nouveau']; ?></div>
                    <div class="stat-label">Nouvelles demandes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['en_cours']; ?></div>
                    <div class="stat-label">En cours de traitement</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['approuve']; ?></div>
                    <div class="stat-label">Demandes approuvées</div>
                </div>
            </div>

            <!-- Filtres -->
            <?php if (!empty($demandes)): ?>
            <div class="filters">
                <div class="filter-group">
                    <label for="filter-statut">Filtrer par statut:</label>
                    <select id="filter-statut" class="filter-select" onchange="filterDemandes()">
                        <option value="all">Tous les statuts</option>
                        <option value="nouveau">Nouveau</option>
                        <option value="en_cours">En cours</option>
                        <option value="approuve">Approuvé</option>
                        <option value="refuse">Refusé</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filter-specialite">Filtrer par spécialité:</label>
                    <select id="filter-specialite" class="filter-select" onchange="filterDemandes()">
                        <option value="all">Toutes les spécialités</option>
                        <?php
                        $specialites = array_unique(array_column($demandes, 'specialite'));
                        foreach ($specialites as $specialite):
                            if (!empty($specialite)):
                        ?>
                            <option value="<?php echo htmlspecialchars($specialite); ?>">
                                <?php echo htmlspecialchars($specialite); ?>
                            </option>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </select>
                </div>
            </div>
            <?php endif; ?>

            <!-- Liste des demandes -->
            <div class="demandes-list">
                <?php if (empty($demandes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune demande pour le moment</h3>
                        <p>Vous n'avez pas encore soumis de demande d'études en Roumanie.</p>
                        <a href="nouvelle_demande.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-plus"></i> Nouvelle demande
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($demandes as $demande): ?>
                        <div class="demande-card" data-statut="<?php echo htmlspecialchars($demande['statut']); ?>" 
                             data-specialite="<?php echo htmlspecialchars($demande['specialite']); ?>">
                            <div class="demande-header">
                                <h3 class="demande-title">
                                    <?php echo htmlspecialchars($demande['specialite']); ?>
                                </h3>
                                <span class="status-badge status-<?php echo htmlspecialchars($demande['statut']); ?>">
                                    <?php 
                                    $statut_labels = [
                                        'nouveau' => 'Nouveau',
                                        'en_cours' => 'En cours',
                                        'approuve' => 'Approuvé',
                                        'refuse' => 'Refusé'
                                    ];
                                    echo $statut_labels[$demande['statut']] ?? $demande['statut'];
                                    ?>
                                </span>
                            </div>

                            <div class="demande-info">
                                <div class="info-item">
                                    <span class="info-label">Langue du programme</span>
                                    <span class="info-value"><?php echo htmlspecialchars($demande['programme_langue'] ?? 'Non spécifié'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Niveau</span>
                                    <span class="info-value">
                                        <?php 
                                        $niveau_labels = [
                                            'bac' => 'Baccalauréat',
                                            'l1' => 'Licence 1',
                                            'l2' => 'Licence 2',
                                            'l3' => 'Licence 3',
                                            'master' => 'Master'
                                        ];
                                        echo $niveau_labels[$demande['niveau_etude']] ?? ($demande['niveau_etude'] ?? 'Non spécifié');
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Date de soumission</span>
                                    <span class="info-value">
                                        <?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Certificat de langue</span>
                                    <span class="info-value">
                                        <?php 
                                        if (!empty($demande['certificat_type']) && !empty($demande['certificat_score'])) {
                                            echo htmlspecialchars($demande['certificat_type']) . ' - ' . htmlspecialchars($demande['certificat_score']);
                                        } else {
                                            echo 'Non requis';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>

                            <div class="demande-actions">
                                <button class="btn btn-primary" onclick="voirDetails(<?php echo $demande['id']; ?>)">
                                    <i class="fas fa-eye"></i> Voir détails
                                </button>
                                <button class="btn btn-outline" onclick="modifierDemande(<?php echo $demande['id']; ?>)">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <?php if (($demande['statut'] ?? '') === 'nouveau'): ?>
                                    <button class="btn btn-danger" onclick="supprimerDemande(<?php echo $demande['id']; ?>)">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Filtrage des demandes
        function filterDemandes() {
            const statutFilter = document.getElementById('filter-statut').value;
            const specialiteFilter = document.getElementById('filter-specialite').value;
            const demandes = document.querySelectorAll('.demande-card');

            demandes.forEach(demande => {
                const statut = demande.getAttribute('data-statut');
                const specialite = demande.getAttribute('data-specialite');

                const statutMatch = statutFilter === 'all' || statut === statutFilter;
                const specialiteMatch = specialiteFilter === 'all' || specialite === specialiteFilter;

                if (statutMatch && specialiteMatch) {
                    demande.style.display = 'block';
                } else {
                    demande.style.display = 'none';
                }
            });
        }

        // Fonctions d'actions
        function voirDetails(demandeId) {
            window.location.href = `details_demande_roumanie.php?id=${demandeId}`;
        }

        function modifierDemande(demandeId) {
            if (confirm('Voulez-vous modifier cette demande ?')) {
                window.location.href = `modifier_demande_roumanie.php?id=${demandeId}`;
            }
        }

        function supprimerDemande(demandeId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.')) {
                window.location.href = `supprimer_demande_roumanie.php?id=${demandeId}`;
            }
        }

        // Tri initial par date (le plus récent en premier)
        document.addEventListener('DOMContentLoaded', function() {
            const demandesContainer = document.querySelector('.demandes-list');
            const demandes = Array.from(document.querySelectorAll('.demande-card'));
            
            // Trier par date (le plus récent en premier)
            demandes.sort((a, b) => {
                const dateA = a.querySelector('.info-value').textContent;
                const dateB = b.querySelector('.info-value').textContent;
                return new Date(dateB.split(' à ')[0].split('/').reverse().join('-')) - 
                       new Date(dateA.split(' à ')[0].split('/').reverse().join('-'));
            });

            // Réorganiser dans le container
            demandes.forEach(demande => demandesContainer.appendChild(demande));
        });
    </script>
</body>
</html>