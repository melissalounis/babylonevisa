<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_nom = $_SESSION['user_nom'] ?? 'Utilisateur';

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

// Configuration
$config = [
    'programmes' => [
        'francais' => [
            'nom' => 'Français',
            'langues' => ['Français', 'Anglais'],
            'couleur' => '#3498db'
        ],
        'anglais' => [
            'nom' => 'Anglais',
            'langues' => ['Anglais', 'Français'],
            'couleur' => '#e74c3c'
        ],
        'allemand' => [
            'nom' => 'Allemand',
            'langues' => ['Allemand', 'Français', 'Anglais'],
            'couleur' => '#2ecc71'
        ],
        'luxembourgeois' => [
            'nom' => 'Luxembourgeois',
            'langues' => ['Luxembourgeois', 'Français', 'Allemand'],
            'couleur' => '#9b59b6'
        ]
    ],
    'statuts' => [
        'en_attente' => [
            'texte' => 'En attente',
            'badge' => 'secondary',
            'icone' => 'clock'
        ],
        'en_traitement' => [
            'texte' => 'En traitement',
            'badge' => 'warning',
            'icone' => 'cogs'
        ],
        'approuvee' => [
            'texte' => 'Approuvée',
            'badge' => 'success',
            'icone' => 'check-circle'
        ],
        'rejetee' => [
            'texte' => 'Rejetée',
            'badge' => 'danger',
            'icone' => 'times-circle'
        ]
    ]
];

// Fonctions utilitaires
function getProgrammeConfig($programme, $config) {
    return $config['programmes'][$programme] ?? [
        'nom' => ucfirst($programme),
        'langues' => [],
        'couleur' => '#95a5a6'
    ];
}

function getStatutConfig($statut, $config) {
    return $config['statuts'][$statut] ?? [
        'texte' => $statut,
        'badge' => 'secondary',
        'icone' => 'question'
    ];
}

function formatDate($date) {
    return date('d/m/Y à H:i', strtotime($date));
}

function getDaysAgo($date) {
    $now = new DateTime();
    $created = new DateTime($date);
    $interval = $now->diff($created);
    return $interval->days;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les statistiques
    $stmtStats = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(statut = 'approuvee') as approuvees,
            SUM(statut = 'en_traitement') as en_traitement,
            SUM(statut = 'rejetee') as rejetees
        FROM demandes_luxembourg 
        WHERE user_id = ?
    ");
    $stmtStats->execute([$user_id]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

    // Récupérer les demandes avec pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $stmtDemandes = $pdo->prepare("
        SELECT dl.*, 
               COUNT(dlf.id) as nb_fichiers,
               (SELECT COUNT(*) FROM commentaires_demandes WHERE demande_id = dl.id) as nb_commentaires
        FROM demandes_luxembourg dl 
        LEFT JOIN demandes_luxembourg_fichiers dlf ON dl.id = dlf.demande_id 
        WHERE dl.user_id = ? 
        GROUP BY dl.id 
        ORDER BY dl.date_creation DESC
        LIMIT ? OFFSET ?
    ");
    $stmtDemandes->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmtDemandes->bindValue(2, $limit, PDO::PARAM_INT);
    $stmtDemandes->bindValue(3, $offset, PDO::PARAM_INT);
    $stmtDemandes->execute();
    $demandes = $stmtDemandes->fetchAll(PDO::FETCH_ASSOC);

    // Compter le total pour la pagination
    $stmtTotal = $pdo->prepare("SELECT COUNT(*) as total FROM demandes_luxembourg WHERE user_id = ?");
    $stmtTotal->execute([$user_id]);
    $totalDemandes = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalDemandes / $limit);

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes - Luxembourg</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #6c757d;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            margin: 30px auto;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        .header-section {
            background: linear-gradient(135deg, var(--primary), #2980b9);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 30px;
        }
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .demande-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .demande-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .demande-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--card-color, var(--primary));
        }
        
        .programme-badge {
            background: var(--programme-color);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .language-badge {
            background: #e3f2fd;
            color: var(--primary);
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            margin: 2px;
            border: 1px solid #bbdefb;
        }
        
        .file-badge, .comment-badge {
            background: #f8f9fa;
            color: var(--secondary);
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            margin-left: 8px;
        }
        
        .action-btn {
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-details {
            background: var(--primary);
            color: white;
        }
        
        .btn-details:hover {
            background: #2980b9;
            color: white;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }
        
        .page-link {
            color: var(--primary);
            border: 1px solid #dee2e6;
            padding: 8px 16px;
            margin: 0 2px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .page-link:hover, .page-link.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .quick-actions {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .main-container {
                margin: 15px;
                border-radius: 15px;
            }
            
            .header-section {
                padding: 20px;
                border-radius: 15px 15px 0 0;
            }
            
            .demande-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container main-container">
        <!-- En-tête -->
        <div class="header-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold">
                        <i class="fas fa-list-alt me-3"></i>Mes Demandes
                    </h1>
                    <p class="lead mb-0">Gérez vos demandes pour le Luxembourg</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-light text-dark fs-6 p-3">
                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($user_nom); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="container-fluid py-4">
            <!-- Cartes de statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-alt fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0"><?php echo $stats['total']; ?></h4>
                                <p class="text-muted mb-0">Total</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0"><?php echo $stats['approuvees']; ?></h4>
                                <p class="text-muted mb-0">Approuvées</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-cogs fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0"><?php echo $stats['en_traitement']; ?></h4>
                                <p class="text-muted mb-0">En traitement</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-0"><?php echo $stats['rejetees']; ?></h4>
                                <p class="text-muted mb-0">Rejetées</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="quick-actions">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">Actions rapides</h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="../luxembourg/etudes/index.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Nouvelle Demande
                        </a>
                    </div>
                </div>
            </div>

            <!-- Liste des demandes -->
            <?php if (empty($demandes)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3 class="mt-3">Aucune demande pour le moment</h3>
                    <p class="text-muted mb-4">Commencez par créer votre première demande</p>
                    <a href="../luxembourg/etudes/index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus me-2"></i>Créer une demande
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($demandes as $demande): ?>
                    <?php 
                    $programmeConfig = getProgrammeConfig($demande['programme'], $config);
                    $statutConfig = getStatutConfig($demande['statut'], $config);
                    $daysAgo = getDaysAgo($demande['date_creation']);
                    ?>
                    <div class="demande-card" style="--card-color: <?php echo $programmeConfig['couleur']; ?>">
                        <div class="row align-items-start">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-3">
                                    <h4 class="mb-0 me-3"><?php echo htmlspecialchars($demande['nom']); ?></h4>
                                    <span class="programme-badge" style="--programme-color: <?php echo $programmeConfig['couleur']; ?>">
                                        <?php echo $programmeConfig['nom']; ?>
                                    </span>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-sm-6">
                                        <p class="mb-2">
                                            <i class="fas fa-graduation-cap me-2 text-muted"></i>
                                            <strong>Niveau :</strong> <?php echo htmlspecialchars(ucfirst($demande['niveau'])); ?>
                                        </p>
                                        <p class="mb-0">
                                            <i class="fas fa-calendar me-2 text-muted"></i>
                                            <strong>Date :</strong> <?php echo formatDate($demande['date_creation']); ?>
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="mb-2">
                                            <i class="fas fa-clock me-2 text-muted"></i>
                                            <strong>Il y a :</strong> <?php echo $daysAgo; ?> jour<?php echo $daysAgo > 1 ? 's' : ''; ?>
                                        </p>
                                        <p class="mb-0">
                                            <i class="fas fa-file me-2 text-muted"></i>
                                            <strong>Fichiers :</strong> 
                                            <span class="file-badge">
                                                <i class="fas fa-paperclip"></i> <?php echo $demande['nb_fichiers']; ?>
                                            </span>
                                            <?php if ($demande['nb_commentaires'] > 0): ?>
                                                <span class="comment-badge">
                                                    <i class="fas fa-comments"></i> <?php echo $demande['nb_commentaires']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="language-badges">
                                    <?php foreach ($programmeConfig['langues'] as $langue): ?>
                                        <span class="language-badge">
                                            <i class="fas fa-language me-1"></i><?php echo $langue; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-end">
                                <div class="mb-3">
                                    <span class="badge bg-<?php echo $statutConfig['badge']; ?> fs-6 p-2">
                                        <i class="fas fa-<?php echo $statutConfig['icone']; ?> me-1"></i>
                                        <?php echo $statutConfig['texte']; ?>
                                    </span>
                                </div>
                                
                                <div class="btn-group">
                                    <a href="detail_demande_luxembourg.php?id=<?php echo $demande['id']; ?>" 
                                       class="action-btn btn-details">
                                        <i class="fas fa-eye me-1"></i>Détails
                                    </a>
                                    <?php if ($demande['statut'] === 'en_attente'): ?>
                                        <a href="modifier_demande_luxembourg.php?id=<?php echo $demande['id']; ?>" 
                                           class="action-btn" style="background: var(--warning); color: black;">
                                            <i class="fas fa-edit me-1"></i>Modifier
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-container">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.demande-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate__animated', 'animate__fadeInUp');
            });
        });
    </script>
</body>
</html>