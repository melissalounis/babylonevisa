<?php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $action = $_POST['action'];
        
        try {
            switch($action) {
                case 'changer_statut':
                    if (isset($_POST['statut'])) {
                        $statut = $_POST['statut'];
                        $stmt = $pdo->prepare("UPDATE etude_belgique SET status = ?, updated_at = NOW() WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut de la demande #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    // Supprimer la demande
                    $stmt = $pdo->prepare("DELETE FROM etude_belgique WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $_SESSION['success_message'] = "Demande #$id supprimée avec succès.";
                    break;
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'opération: " . $e->getMessage();
        }
        
        header('Location: etude_belgique.php');
        exit();
    }
}

// Initialiser les variables
$demandes = [];
$stats = [
    'total' => 0,
    'en_attente' => 0,
    'approuve' => 0,
    'refuse' => 0,
    'bac' => 0,
    'l1' => 0,
    'l2' => 0,
    'l3' => 0,
    'master' => 0
];
$total_demandes = 0;
$total_pages = 1;

// Récupérer les paramètres de filtrage
$statut_filter = $_GET['statut'] ?? 'tous';
$niveau_filter = $_GET['niveau'] ?? 'tous';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Construire la requête avec filtres
    $where_conditions = [];
    $params = [];

    if ($statut_filter !== 'tous') {
        $where_conditions[] = "status = ?";
        $params[] = $statut_filter;
    }

    if ($niveau_filter !== 'tous') {
        $where_conditions[] = "niveau_etude = ?";
        $params[] = $niveau_filter;
    }

    if (!empty($search)) {
        $where_conditions[] = "(nom LIKE ? OR email LIKE ? OR telephone LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Récupérer le nombre total pour la pagination
    $count_sql = "SELECT COUNT(*) FROM etude_belgique $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_demandes = $stmt->fetchColumn();
    $total_pages = ceil($total_demandes / $limit);

    // Récupérer les demandes
    $sql = "SELECT * FROM etude_belgique $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les statistiques
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(status = 'pending') as en_attente,
            SUM(status = 'approved') as approuve,
            SUM(status = 'rejected') as refuse,
            SUM(niveau_etude = 'bac') as bac,
            SUM(niveau_etude = 'l1') as l1,
            SUM(niveau_etude = 'l2') as l2,
            SUM(niveau_etude = 'l3') as l3,
            SUM(niveau_etude = 'master') as master
        FROM etude_belgique
    ";
    $stats_result = $pdo->query($stats_sql);
    if ($stats_result) {
        $stats = $stats_result->fetch(PDO::FETCH_ASSOC);
        // Assurer que toutes les valeurs sont définies
        $stats = array_merge($stats, [
            'total' => $stats['total'] ?? 0,
            'en_attente' => $stats['en_attente'] ?? 0,
            'approuve' => $stats['approuve'] ?? 0,
            'refuse' => $stats['refuse'] ?? 0,
            'bac' => $stats['bac'] ?? 0,
            'l1' => $stats['l1'] ?? 0,
            'l2' => $stats['l2'] ?? 0,
            'l3' => $stats['l3'] ?? 0,
            'master' => $stats['master'] ?? 0
        ]);
    }

} catch(PDOException $e) {
    $error_message = "Erreur lors de la récupération des données: " . $e->getMessage();
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'pending' => ['label' => 'En attente', 'class' => 'warning', 'icon' => 'clock'],
        'approved' => ['label' => 'Approuvé', 'class' => 'success', 'icon' => 'check-circle'],
        'rejected' => ['label' => 'Refusé', 'class' => 'danger', 'icon' => 'times-circle']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary', 'icon' => 'question-circle'];
}

// Fonction pour formater le niveau d'étude
function formatNiveauEtude($niveau) {
    $niveaux = [
        'bac' => 'Bac',
        'l1' => 'Licence 1',
        'l2' => 'Licence 2',
        'l3' => 'Licence 3',
        'master' => 'Master'
    ];
    return $niveaux[$niveau] ?? $niveau;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Études Belgique - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #070988;
            --secondary: #FDDA24;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary), #2c2c2c);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .stat-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .badge-statut {
            font-size: 0.75em;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .action-buttons {
            min-width: 140px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #2c2c2c);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .document-item {
            border-left: 3px solid var(--primary);
            padding-left: 10px;
            margin-bottom: 8px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header p-4 text-center">
                <h4 class="mb-2">
                    <i class="fas fa-university"></i><br>
                    Babylone Service
                </h4>
                <p class="mb-0 small opacity-75">Espace Administrateur</p>
            </div>
            
            <ul class="nav flex-column p-3">
                <li class="nav-item mb-2">
                    <a href="admin_dashboard.php" class="nav-link text-white rounded">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Tableau de bord
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="rendez_vous.php" class="nav-link text-white rounded">
                        <i class="fas fa-calendar-check me-2"></i>
                        Rendez-vous Visa
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_court_sejour.php" class="nav-link text-white rounded">
                        <i class="fas fa-plane me-2"></i>
                        Visas Court Séjour
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="etude_belgique.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Belgique
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="tests_langue.php" class="nav-link text-white rounded">
                        <i class="fas fa-language me-2"></i>
                        Tests de Langue
                    </a>
                </li>
                <li class="nav-item mt-4 pt-3 border-top">
                    <a href="admin_logout.php" class="nav-link text-white rounded">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Déconnexion
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-graduation-cap text-primary me-2"></i>
                        Gestion des Études en Belgique
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes d'études en Belgique</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" onclick="exporterDonnees()">
                        <i class="fas fa-download me-1"></i> Exporter
                    </button>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt me-1"></i> Actualiser
                    </button>
                </div>
            </div>

            <!-- Messages d'alerte -->
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-0">
                        <div class="card-body text-center">
                            <div class="user-avatar mx-auto mb-3">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['total']; ?></h3>
                            <p class="mb-0 text-muted small">Total</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-warning">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-warning"><?php echo $stats['en_attente']; ?></h3>
                            <p class="mb-0 text-muted small">En attente</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-success">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-success"><?php echo $stats['approuve']; ?></h3>
                            <p class="mb-0 text-muted small">Approuvés</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-danger">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-danger"><?php echo $stats['refuse']; ?></h3>
                            <p class="mb-0 text-muted small">Refusés</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-primary">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-user-graduate fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-primary"><?php echo $stats['bac']; ?></h3>
                            <p class="mb-0 text-muted small">Bac</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-info">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-book fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-info"><?php echo $stats['master']; ?></h3>
                            <p class="mb-0 text-muted small">Master</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres et Recherche -->
            <div class="filter-section bg-white rounded p-3 mb-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Filtrer par statut</label>
                        <select name="statut" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $statut_filter === 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
                            <option value="pending" <?php echo $statut_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                            <option value="approved" <?php echo $statut_filter === 'approved' ? 'selected' : ''; ?>>Approuvé</option>
                            <option value="rejected" <?php echo $statut_filter === 'rejected' ? 'selected' : ''; ?>>Refusé</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtrer par niveau</label>
                        <select name="niveau" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $niveau_filter === 'tous' ? 'selected' : ''; ?>>Tous les niveaux</option>
                            <option value="bac" <?php echo $niveau_filter === 'bac' ? 'selected' : ''; ?>>Bac</option>
                            <option value="l1" <?php echo $niveau_filter === 'l1' ? 'selected' : ''; ?>>Licence 1</option>
                            <option value="l2" <?php echo $niveau_filter === 'l2' ? 'selected' : ''; ?>>Licence 2</option>
                            <option value="l3" <?php echo $niveau_filter === 'l3' ? 'selected' : ''; ?>>Licence 3</option>
                            <option value="master" <?php echo $niveau_filter === 'master' ? 'selected' : ''; ?>>Master</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, email, téléphone..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tableau des demandes -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Étudiant</th>
                                <th>Niveau</th>
                                <th>Nationalité</th>
                                <th>Contact</th>
                                <th>Équivalence Bac</th>
                                <th>Statut</th>
                                <th>Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($demandes)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune demande d'études en Belgique trouvée</p>
                                        <?php if ($statut_filter !== 'tous' || $niveau_filter !== 'tous' || !empty($search)): ?>
                                            <a href="etude_belgique.php" class="btn btn-primary">
                                                <i class="fas fa-times me-1"></i> Réinitialiser les filtres
                                            </a>
                                        <?php else: ?>
                                            <p class="text-muted small mt-2">
                                                Les demandes apparaîtront ici une fois que les étudiants auront soumis leurs formulaires.
                                            </p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($demandes as $demande): ?>
                                    <?php 
                                    $statut = formatStatut($demande['status']);
                                    $initiales = strtoupper(substr($demande['nom'], 0, 2));
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary">#<?php echo $demande['id']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo $initiales; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($demande['nom']); ?></div>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y', strtotime($demande['naissance'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatNiveauEtude($demande['niveau_etude']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($demande['nationalite']); ?></div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="fas fa-envelope me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($demande['email']); ?>
                                            </div>
                                            <div class="mt-1">
                                                <i class="fas fa-phone me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($demande['telephone']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $demande['equivalence_bac'] === 'oui' ? 'bg-success' : 'bg-warning'; ?>">
                                                <?php echo $demande['equivalence_bac'] === 'oui' ? 'Oui' : 'Non'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-statut bg-<?php echo $statut['class']; ?>">
                                                <i class="fas fa-<?php echo $statut['icon']; ?> me-1"></i>
                                                <?php echo $statut['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($demande['created_at'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm action-buttons">
                                                <button class="btn btn-outline-primary" title="Voir les détails"
                                                        onclick="afficherDetails(<?php echo $demande['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                            data-bs-toggle="dropdown" title="Changer le statut">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'pending')">
                                                            <i class="fas fa-clock text-warning me-2"></i>En attente
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'approved')">
                                                            <i class="fas fa-check-circle text-success me-2"></i>Approuver
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'rejected')">
                                                            <i class="fas fa-times-circle text-danger me-2"></i>Refuser
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                <button class="btn btn-outline-danger" title="Supprimer"
                                                        onclick="supprimerDemande(<?php echo $demande['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container bg-white rounded p-3 mt-4">
                    <nav aria-label="Pagination">
                        <ul class="pagination justify-content-center mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                        <i class="fas fa-chevron-left"></i> Précédent
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                        Suivant <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <div class="text-center text-muted small mt-2">
                        Affichage de <?php echo count($demandes); ?> sur <?php echo $total_demandes; ?> demandes
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour afficher les détails -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Détails de la demande d'études</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p class="mt-2">Chargement des détails...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmer la suppression</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer définitivement cette demande ? Cette action est irréversible.</p>
                    <form method="POST" action="etude_belgique.php" id="form-delete-user">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id" id="delete_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="form-delete-user" class="btn btn-danger">Supprimer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
       function afficherDetails(demandeId) {
    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
    const content = document.getElementById('detailsContent');
    
    // Afficher le spinner de chargement
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="mt-2">Chargement des détails...</p>
        </div>
    `;
    
    modal.show();
    
    // Charger les détails via AJAX
    fetch(`get_etude_belgique_details.php?id=${demandeId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const demande = data.demande;
                
                // Générer la liste des documents par catégorie
                let documentsHTML = '';
                if (demande.documents && demande.documents.length > 0) {
                    
                    // Grouper les documents par catégorie
                    const documentsParCategorie = {};
                    demande.documents.forEach(doc => {
                        if (!documentsParCategorie[doc.categorie]) {
                            documentsParCategorie[doc.categorie] = [];
                        }
                        documentsParCategorie[doc.categorie].push(doc);
                    });
                    
                    documentsHTML = `<div class="row mt-3"><div class="col-12"><h6><i class="fas fa-paperclip me-2"></i>Documents téléchargés</h6>`;
                    
                    // Afficher chaque catégorie
                    Object.keys(documentsParCategorie).forEach(categorie => {
                        documentsHTML += `
                            <div class="mb-3">
                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-folder me-2"></i>${categorie}
                                </h6>
                                <div class="row">
                        `;
                        
                        documentsParCategorie[categorie].forEach(doc => {
                            const fileUrl = `uploads/etude_belgique/${doc.fichier}`;
                            const fileExtension = doc.fichier.split('.').pop().toLowerCase();
                            const fileSize = getFileSize(fileUrl); // Fonction pour obtenir la taille
                            
                            documentsHTML += `
                                <div class="col-md-6 mb-3">
                                    <div class="card document-card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <i class="${doc.icon} fa-2x text-${doc.couleur}"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-1">${doc.nom}</h6>
                                                    <p class="card-text text-muted small mb-1">
                                                        <i class="fas fa-file me-1"></i>${doc.fichier}
                                                    </p>
                                                    <p class="card-text text-muted small mb-2">
                                                        <i class="fas fa-hdd me-1"></i>${fileExtension.toUpperCase()} 
                                                        <span class="ms-2"><i class="fas fa-calendar me-1"></i>${new Date().toLocaleDateString('fr-FR')}</span>
                                                    </p>
                                                </div>
                                                <div class="btn-group">
                                                    <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary" title="Voir le document">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="${fileUrl}" download class="btn btn-sm btn-outline-success" title="Télécharger">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        documentsHTML += `</div></div>`;
                    });
                    
                    documentsHTML += `</div></div>`;
                } else {
                    documentsHTML = `
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Aucun document joint à cette demande.
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-user me-2"></i>Informations personnelles</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Nom complet:</strong></td>
                                        <td>${demande.nom}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date de naissance:</strong></td>
                                        <td>${new Date(demande.naissance).toLocaleDateString('fr-FR')}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nationalité:</strong></td>
                                        <td>${demande.nationalite}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Téléphone:</strong></td>
                                        <td>${demande.telephone}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td>${demande.email}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-address-card me-2"></i>Adresse</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                ${demande.adresse}
                            </div>
                            
                            <h6 class="mt-3"><i class="fas fa-graduation-cap me-2"></i>Informations académiques</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Niveau d'étude:</strong></td>
                                        <td><span class="badge bg-light text-dark">${formatNiveauEtude(demande.niveau_etude)}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Équivalence du Bac:</strong></td>
                                        <td>
                                            <span class="badge ${demande.equivalence_bac === 'oui' ? 'bg-success' : 'bg-warning'}">
                                                ${demande.equivalence_bac === 'oui' ? 'Oui' : 'Non'}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            ${demande.equivalence_bac === 'non' ? `
                            <div class="alert alert-warning mt-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                L'étudiant doit demander l'équivalence de son bac
                            </div>
                            ` : ''}
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-info-circle me-2"></i>Informations techniques</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td width="40%"><strong>Référence:</strong></td>
                                        <td><code>EB-${demande.id.toString().padStart(6, '0')}</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Statut:</strong></td>
                                        <td><span class="badge bg-${getStatutClass(demande.status)}">${getStatutLabel(demande.status)}</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date de création:</strong></td>
                                        <td>${new Date(demande.created_at).toLocaleString('fr-FR')}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dernière modification:</strong></td>
                                        <td>${new Date(demande.updated_at).toLocaleString('fr-FR')}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    ${documentsHTML}
                `;
            } else {
                content.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erreur lors du chargement des détails: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erreur réseau: ${error.message}
                </div>
            `;
        });
}

// Fonction pour obtenir l'icône du fichier selon l'extension
function getFileIcon(extension) {
    const icons = {
        'pdf': 'fas fa-file-pdf text-danger',
        'jpg': 'fas fa-file-image text-success',
        'jpeg': 'fas fa-file-image text-success',
        'png': 'fas fa-file-image text-success',
        'doc': 'fas fa-file-word text-primary',
        'docx': 'fas fa-file-word text-primary'
    };
    return icons[extension] || 'fas fa-file text-secondary';
}

// Fonction pour formater la taille du fichier (exemple)
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Ajouter ce CSS pour les cartes de documents
const style = document.createElement('style');
style.textContent = `
    .document-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.3s ease;
    }
    .document-card:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .document-card .card-body {
        padding: 15px;
    }
`;
document.head.appendChild(style);
        function changerStatut(id, nouveauStatut) {
            if (confirm('Êtes-vous sûr de vouloir changer le statut de cette demande ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'etude_belgique.php';
                
                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'changer_statut';
                form.appendChild(inputAction);
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = id;
                form.appendChild(inputId);
                
                const inputStatut = document.createElement('input');
                inputStatut.type = 'hidden';
                inputStatut.name = 'statut';
                inputStatut.value = nouveauStatut;
                form.appendChild(inputStatut);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function supprimerDemande(id) {
            document.getElementById('delete_id').value = id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }

        function exporterDonnees() {
            alert('Fonction d\'exportation à implémenter');
        }

        // Fonctions utilitaires
        function formatNiveauEtude(niveau) {
            const niveaux = {
                'bac': 'Bac',
                'l1': 'Licence 1',
                'l2': 'Licence 2',
                'l3': 'Licence 3',
                'master': 'Master'
            };
            return niveaux[niveau] || niveau;
        }

        function getStatutClass(statut) {
            const classes = {
                'pending': 'warning',
                'approved': 'success',
                'rejected': 'danger'
            };
            return classes[statut] || 'secondary';
        }

        function getStatutLabel(statut) {
            const labels = {
                'pending': 'En attente',
                'approved': 'Approuvé',
                'rejected': 'Refusé'
            };
            return labels[statut] || statut;
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Administration études Belgique chargée');
        });
        function getFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
    </script>
</body>
</html>