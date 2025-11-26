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

// Traitement des actions (changer statut, supprimer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = intval($_POST['id']);
        $action = $_POST['action'];
        
        try {
            switch($action) {
                case 'changer_statut':
                    if (isset($_POST['statut'])) {
                        $statut = $_POST['statut'];
                        $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = ?, date_maj = NOW() WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut du rendez-vous #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    $stmt = $pdo->prepare("DELETE FROM rendez_vous WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['success_message'] = "Rendez-vous #$id supprimé avec succès.";
                    break;
                    
                case 'exporter':
                    // Logique d'exportation (à implémenter)
                    $_SESSION['success_message'] = "Exportation du rendez-vous #$id initiée.";
                    break;
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'opération: " . $e->getMessage();
        }
        
        header('Location: rendez_vous.php');
        exit();
    }
}

// Récupérer les paramètres de filtrage
$statut_filter = $_GET['statut'] ?? 'tous';
$type_filter = $_GET['type'] ?? 'tous';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construire la requête avec filtres
$where_conditions = [];
$params = [];

if ($statut_filter !== 'tous') {
    $where_conditions[] = "statut = ?";
    $params[] = $statut_filter;
}

if ($type_filter !== 'tous') {
    $where_conditions[] = "type_demande = ?";
    $params[] = $type_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR reference LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Récupérer le nombre total pour la pagination
$count_sql = "SELECT COUNT(*) FROM rendez_vous $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_rendez_vous = $stmt->fetchColumn();
$total_pages = ceil($total_rendez_vous / $limit);

// Récupérer les rendez-vous
$sql = "SELECT * FROM rendez_vous $where_sql ORDER BY date_creation DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rendez_vous = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les statistiques
$stats_sql = "
    SELECT 
        COUNT(*) as total,
        SUM(statut = 'en_attente') as en_attente,
        SUM(statut = 'confirme') as confirmes,
        SUM(statut = 'annule') as annules,
        SUM(type_demande = 'premiere_demande') as premieres_demandes,
        SUM(type_demande = 'renouvellement') as renouvellements
    FROM rendez_vous
";
$stats = $pdo->query($stats_sql)->fetch(PDO::FETCH_ASSOC);

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'warning', 'icon' => 'clock'],
        'confirme' => ['label' => 'Confirmé', 'class' => 'success', 'icon' => 'check-circle'],
        'annule' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'times-circle']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary', 'icon' => 'question-circle'];
}

// Fonction pour formater le type de demande
function formatTypeDemande($type) {
    $types = [
        'premiere_demande' => 'Première demande',
        'renouvellement' => 'Renouvellement'
    ];
    return $types[$type] ?? $type;
}

// Fonction pour formater le type de client
function formatTypeClient($type) {
    $types = [
        'individuel' => 'Individuel',
        'famille' => 'Famille',
        'groupe' => 'Groupe'
    ];
    return $types[$type] ?? $type;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des Rendez-vous - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #7209b7;
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
            background: linear-gradient(135deg, var(--primary), var(--secondary));
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
        
        .search-box {
            max-width: 300px;
        }
        
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .pagination-container {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--primary);
            background-color: #f8f9fa;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
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
            
            .search-box {
                max-width: 100%;
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
                    <i class="fas fa-passport"></i><br>
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
                    <a href="rendez_vous.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-calendar-check me-2"></i>
                        Rendez-vous Visa
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_tests_langue.php" class="nav-link text-white rounded">
                        <i class="fas fa-language me-2"></i>
                        Tests de Langue
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_clients.php" class="nav-link text-white rounded">
                        <i class="fas fa-users me-2"></i>
                        Clients
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_parametres.php" class="nav-link text-white rounded">
                        <i class="fas fa-cog me-2"></i>
                        Paramètres
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
                        <i class="fas fa-calendar-check text-primary me-2"></i>
                        Gestion des Rendez-vous Visa
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez tous les rendez-vous pour les visas</p>
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-0">
                        <div class="card-body text-center">
                            <div class="user-avatar mx-auto mb-3">
                                <i class="fas fa-calendar"></i>
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
                            <h3 class="mb-1 text-success"><?php echo $stats['confirmes']; ?></h3>
                            <p class="mb-0 text-muted small">Confirmés</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-danger">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-danger"><?php echo $stats['annules']; ?></h3>
                            <p class="mb-0 text-muted small">Annulés</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-info">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-info"><?php echo $stats['premieres_demandes']; ?></h3>
                            <p class="mb-0 text-muted small">Premières demandes</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-secondary">
                        <div class="card-body text-center">
                            <div class="text-secondary mb-2">
                                <i class="fas fa-sync-alt fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-secondary"><?php echo $stats['renouvellements']; ?></h3>
                            <p class="mb-0 text-muted small">Renouvellements</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres et Recherche -->
            <div class="filter-section">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Filtrer par statut</label>
                        <select name="statut" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $statut_filter === 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
                            <option value="en_attente" <?php echo $statut_filter === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="confirme" <?php echo $statut_filter === 'confirme' ? 'selected' : ''; ?>>Confirmé</option>
                            <option value="annule" <?php echo $statut_filter === 'annule' ? 'selected' : ''; ?>>Annulé</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtrer par type</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $type_filter === 'tous' ? 'selected' : ''; ?>>Tous les types</option>
                            <option value="premiere_demande" <?php echo $type_filter === 'premiere_demande' ? 'selected' : ''; ?>>Première demande</option>
                            <option value="renouvellement" <?php echo $type_filter === 'renouvellement' ? 'selected' : ''; ?>>Renouvellement</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, prénom, email..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tableau des rendez-vous -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Référence</th>
                                <th>Destination</th>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Contact</th>
                                <th>Statut</th>
                                <th>Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rendez_vous)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucun rendez-vous trouvé</p>
                                        <?php if ($statut_filter !== 'tous' || $type_filter !== 'tous' || !empty($search)): ?>
                                            <a href="rendez_vous.php" class="btn btn-primary">
                                                <i class="fas fa-times me-1"></i> Réinitialiser les filtres
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($rendez_vous as $rdv): ?>
                                    <?php 
                                    $statut = formatStatut($rdv['statut']);
                                    $initiales = strtoupper(substr($rdv['prenom'], 0, 1) . substr($rdv['nom'], 0, 1));
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary">#<?php echo $rdv['id']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo $initiales; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($rdv['prenom'] . ' ' . $rdv['nom']); ?></div>
                                                    <small class="text-muted">
                                                        <?php echo formatTypeClient($rdv['type_client']); ?>
                                                        <?php if ($rdv['type_client'] !== 'individuel'): ?>
                                                            (<?php echo $rdv['nombre_personnes']; ?> pers.)
                                                        <?php endif; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="text-primary"><?php echo $rdv['reference']; ?></code>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars(ucfirst($rdv['pays_destination'])); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($rdv['motif_voyage']); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatTypeDemande($rdv['type_demande']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted">Arrivée:</small><br>
                                                <?php echo date('d/m/Y', strtotime($rdv['date_arrivee'])); ?>
                                            </div>
                                            <div class="mt-1">
                                                <small class="text-muted">Départ:</small><br>
                                                <?php echo date('d/m/Y', strtotime($rdv['date_depart'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="fas fa-envelope me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($rdv['email']); ?>
                                            </div>
                                            <div class="mt-1">
                                                <i class="fas fa-phone me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($rdv['telephone']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-statut bg-<?php echo $statut['class']; ?>">
                                                <i class="fas fa-<?php echo $statut['icon']; ?> me-1"></i>
                                                <?php echo $statut['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($rdv['date_creation'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm action-buttons">
                                                <button class="btn btn-outline-primary" title="Voir les détails"
                                                        onclick="afficherDetails(<?php echo htmlspecialchars(json_encode($rdv)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                            data-bs-toggle="dropdown" title="Changer le statut">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $rdv['id']; ?>, 'en_attente')">
                                                            <i class="fas fa-clock text-warning me-2"></i>En attente
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $rdv['id']; ?>, 'confirme')">
                                                            <i class="fas fa-check-circle text-success me-2"></i>Confirmer
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $rdv['id']; ?>, 'annule')">
                                                            <i class="fas fa-times-circle text-danger me-2"></i>Annuler
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                <button class="btn btn-outline-danger" title="Supprimer"
                                                        onclick="supprimerRendezVous(<?php echo $rdv['id']; ?>)">
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
                <div class="pagination-container">
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
                        Affichage de <?php echo count($rendez_vous); ?> sur <?php echo $total_rendez_vous; ?> rendez-vous
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour afficher les détails -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Détails du rendez-vous</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Les détails seront chargés ici par JavaScript -->
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
                    <p>Êtes-vous sûr de vouloir supprimer définitivement ce rendez-vous ? Cette action est irréversible.</p>
                    <form method="POST" action="rendez_vous.php" id="form-delete-user">
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        function afficherDetails(rdv) {
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            const content = document.getElementById('detailsContent');
            
            // Calculer la durée du séjour
            const dateArrivee = new Date(rdv.date_arrivee);
            const dateDepart = new Date(rdv.date_depart);
            const diffTime = Math.abs(dateDepart - dateArrivee);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations personnelles</h6>
                        <p><strong>Nom complet:</strong> ${rdv.prenom} ${rdv.nom}</p>
                        <p><strong>Date de naissance:</strong> ${new Date(rdv.date_naissance).toLocaleDateString('fr-FR')}</p>
                        <p><strong>Nationalité:</strong> ${rdv.nationalite}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Coordonnées</h6>
                        <p><strong>Email:</strong> ${rdv.email}</p>
                        <p><strong>Téléphone:</strong> ${rdv.telephone}</p>
                        <p><strong>Adresse:</strong> ${rdv.adresse}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Détails du voyage</h6>
                        <p><strong>Pays de destination:</strong> ${rdv.pays_destination}</p>
                        <p><strong>Type de demande:</strong> ${formatTypeDemande(rdv.type_demande)}</p>
                        <p><strong>Type de client:</strong> ${formatTypeClient(rdv.type_client)}</p>
                        <p><strong>Nombre de personnes:</strong> ${rdv.nombre_personnes}</p>
                        <p><strong>Motif de voyage:</strong> ${rdv.motif_voyage}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Dates du séjour</h6>
                        <p><strong>Date d'arrivée:</strong> ${new Date(rdv.date_arrivee).toLocaleDateString('fr-FR')}</p>
                        <p><strong>Date de départ:</strong> ${new Date(rdv.date_depart).toLocaleDateString('fr-FR')}</p>
                        <p><strong>Durée du séjour:</strong> ${diffDays} jour(s)</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Hébergement</h6>
                        <p><strong>Type d'hébergement:</strong> ${rdv.type_hebergement}</p>
                        <p><strong>Adresse d'hébergement:</strong> ${rdv.adresse_hebergement}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Informations techniques</h6>
                        <p><strong>Référence:</strong> <code>${rdv.reference}</code></p>
                        <p><strong>Statut:</strong> <span class="badge bg-${getStatutClass(rdv.statut)}">${getStatutLabel(rdv.statut)}</span></p>
                        <p><strong>Date de création:</strong> ${new Date(rdv.date_creation).toLocaleString('fr-FR')}</p>
                        <p><strong>Dernière modification:</strong> ${new Date(rdv.date_maj).toLocaleString('fr-FR')}</p>
                    </div>
                </div>
            `;
            
            modal.show();
        }
        
        function formatTypeDemande(type) {
            const types = {
                'premiere_demande': 'Première demande',
                'renouvellement': 'Renouvellement'
            };
            return types[type] || type;
        }
        
        function formatTypeClient(type) {
            const types = {
                'individuel': 'Individuel',
                'famille': 'Famille', 
                'groupe': 'Groupe'
            };
            return types[type] || type;
        }
        
        function getStatutClass(statut) {
            const classes = {
                'en_attente': 'warning',
                'confirme': 'success',
                'annule': 'danger'
            };
            return classes[statut] || 'secondary';
        }
        
        function getStatutLabel(statut) {
            const labels = {
                'en_attente': 'En attente',
                'confirme': 'Confirmé',
                'annule': 'Annulé'
            };
            return labels[statut] || statut;
        }
        
        function changerStatut(id, nouveauStatut) {
            if (confirm('Êtes-vous sûr de vouloir changer le statut de ce rendez-vous ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'rendez_vous.php';
                
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
        
        function supprimerRendezVous(id) {
            // Remplit le champ caché du formulaire avec l'ID du rendez-vous à supprimer
            document.getElementById('delete_id').value = id;
            // Affiche la modale de confirmation
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
        
        function exporterDonnees() {
            // Implémentation de l'exportation
            alert('Fonction d\'exportation à implémenter');
        }
        
        // Initialisation DataTables
        document.addEventListener('DOMContentLoaded', function() {
            // Vous pouvez activer DataTables si nécessaire
            // $('table').DataTable();
        });
    </script>
</body>
</html>