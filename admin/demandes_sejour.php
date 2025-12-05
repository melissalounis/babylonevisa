<?php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Inclure la configuration de la base de données
require_once '../config.php';

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
                        $stmt = $pdo->prepare("UPDATE demandes_court_sejour SET statut = ?, date_maj = NOW() WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut de la demande #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    // D'abord supprimer les fichiers associés
                    $stmt = $pdo->prepare("SELECT chemin_fichier FROM demandes_court_sejour_fichiers WHERE demande_id = ?");
                    $stmt->execute([$id]);
                    $fichiers = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Supprimer les fichiers physiques
                    foreach ($fichiers as $fichier) {
                        $file_path = __DIR__ . "/../../../uploads/visas/" . $fichier;
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                    
                    // Supprimer les entrées dans la table des fichiers
                    $stmt = $pdo->prepare("DELETE FROM demandes_court_sejour_fichiers WHERE demande_id = ?");
                    $stmt->execute([$id]);
                    
                    // Supprimer la demande
                    $stmt = $pdo->prepare("DELETE FROM demandes_court_sejour WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $_SESSION['success_message'] = "Demande #$id supprimée avec succès.";
                    break;
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'opération: " . $e->getMessage();
        }
        
        header('Location: admin_court_sejour.php');
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
    $where_conditions[] = "visa_type = ?";
    $params[] = $type_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(nom_complet LIKE ? OR email LIKE ? OR passeport LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Récupérer le nombre total pour la pagination
$count_sql = "SELECT COUNT(*) FROM demandes_court_sejour $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_demandes = $stmt->fetchColumn();
$total_pages = ceil($total_demandes / $limit);

// CORRECTION : Utiliser date_creation au lieu de date_demande
$sql = "SELECT * FROM demandes_court_sejour $where_sql ORDER BY date_creation DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les statistiques
$stats_sql = "
    SELECT 
        COUNT(*) as total,
        SUM(statut = 'en_attente') as en_attente,
        SUM(statut = 'en_cours') as en_cours,
        SUM(statut = 'approuve') as approuve,
        SUM(statut = 'refuse') as refuse,
        SUM(visa_type = 'tourisme') as tourisme,
        SUM(visa_type = 'affaires') as affaires,
        SUM(visa_type = 'visite_familiale') as visite_familiale
    FROM demandes_court_sejour
";
$stats = $pdo->query($stats_sql)->fetch(PDO::FETCH_ASSOC);

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'warning', 'icon' => 'clock'],
        'en_cours' => ['label' => 'En cours', 'class' => 'info', 'icon' => 'sync-alt'],
        'approuve' => ['label' => 'Approuvé', 'class' => 'success', 'icon' => 'check-circle'],
        'refuse' => ['label' => 'Refusé', 'class' => 'danger', 'icon' => 'times-circle']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary', 'icon' => 'question-circle'];
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Visas Court Séjour - Babylone Service</title>
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
                    <a href="rendez_vous.php" class="nav-link text-white rounded">
                        <i class="fas fa-calendar-check me-2"></i>
                        Rendez-vous Visa
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_court_sejour.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-plane me-2"></i>
                        Visas Court Séjour
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
                        <i class="fas fa-plane text-primary me-2"></i>
                        Gestion des Visas Court Séjour
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes de visa court séjour</p>
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
                                <i class="fas fa-plane"></i>
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
                    <div class="card stat-card border-info">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-sync-alt fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-info"><?php echo $stats['en_cours']; ?></h3>
                            <p class="mb-0 text-muted small">En cours</p>
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
                                <i class="fas fa-suitcase-rolling fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-primary"><?php echo $stats['tourisme']; ?></h3>
                            <p class="mb-0 text-muted small">Tourisme</p>
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
                            <option value="en_attente" <?php echo $statut_filter === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="en_cours" <?php echo $statut_filter === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="approuve" <?php echo $statut_filter === 'approuve' ? 'selected' : ''; ?>>Approuvé</option>
                            <option value="refuse" <?php echo $statut_filter === 'refuse' ? 'selected' : ''; ?>>Refusé</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtrer par type</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $type_filter === 'tous' ? 'selected' : ''; ?>>Tous les types</option>
                            <option value="tourisme" <?php echo $type_filter === 'tourisme' ? 'selected' : ''; ?>>Tourisme</option>
                            <option value="affaires" <?php echo $type_filter === 'affaires' ? 'selected' : ''; ?>>Affaires</option>
                            <option value="visite_familiale" <?php echo $type_filter === 'visite_familiale' ? 'selected' : ''; ?>>Visite familiale</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, email, passeport..." value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Demandeur</th>
                                <th>Type</th>
                                <th>Destination</th>
                                <th>Passeport</th>
                                <th>Contact</th>
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
                                        <p class="text-muted">Aucune demande trouvée</p>
                                        <?php if ($statut_filter !== 'tous' || $type_filter !== 'tous' || !empty($search)): ?>
                                            <a href="admin_court_sejour.php" class="btn btn-primary">
                                                <i class="fas fa-times me-1"></i> Réinitialiser les filtres
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($demandes as $demande): ?>
                                    <?php 
                                    $statut = formatStatut($demande['statut']);
                                    $initiales = strtoupper(substr($demande['nom_complet'], 0, 2));
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
                                                    <div class="fw-bold"><?php echo htmlspecialchars($demande['nom_complet']); ?></div>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y', strtotime($demande['date_naissance'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatTypeVisa($demande['visa_type']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($demande['pays_destination']); ?></div>
                                        </td>
                                        <td>
                                            <code><?php echo htmlspecialchars($demande['passeport']); ?></code>
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
                                            <span class="badge badge-statut bg-<?php echo $statut['class']; ?>">
                                                <i class="fas fa-<?php echo $statut['icon']; ?> me-1"></i>
                                                <?php echo $statut['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <!-- CORRECTION : Utiliser date_creation au lieu de date_demande -->
                                            <?php echo date('d/m/Y H:i', strtotime($demande['date_creation'])); ?>
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
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'en_attente')">
                                                            <i class="fas fa-clock text-warning me-2"></i>En attente
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'en_cours')">
                                                            <i class="fas fa-sync-alt text-info me-2"></i>En cours
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'approuve')">
                                                            <i class="fas fa-check-circle text-success me-2"></i>Approuver
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'refuse')">
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
                    <h5 class="modal-title">Détails de la demande de visa</h5>
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
                    <form method="POST" action="admin_court_sejour.php" id="form-delete-user">
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
        // FONCTIONS UTILITAIRES - TOUTES LES FONCTIONS NÉCESSAIRES
        function formatDate(dateString) {
            if (!dateString) return 'Non renseigné';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('fr-FR');
            } catch (e) {
                return 'Date invalide';
            }
        }

        function formatDateTime(dateTimeString) {
            if (!dateTimeString) return 'Non renseigné';
            try {
                const date = new Date(dateTimeString);
                return date.toLocaleString('fr-FR');
            } catch (e) {
                return 'Date invalide';
            }
        }

        function formatTypeVisa(type) {
            const types = {
                'tourisme': 'Tourisme',
                'affaires': 'Affaires',
                'visite_familiale': 'Visite Familiale',
                'autre': 'Autre'
            };
            return types[type] || type;
        }

        function formatTypeFichier(type) {
            const types = {
                'copie_passeport': 'Copie du passeport',
                'billet_avion': 'Billet d\'avion',
                'documents_travail': 'Documents de travail',
                'lettre_invitation': 'Lettre d\'invitation',
                'copie_visa': 'Copie de visa précédent',
                'reservation_hotel': 'Réservation d\'hôtel',
                'invitation_entreprise': 'Lettre d\'invitation entreprise',
                'mission': 'Ordre de mission',
                'billet_avion_familiale': 'Billet d\'avion (visite familiale)',
                'justificatif_ressources': 'Justificatif de ressources',
                'lettre_prise_en_charge': 'Lettre de prise en charge',
                'prise_en_charge_entreprise': 'Prise en charge entreprise',
                'documents_travail_multiple': 'Documents de travail'
            };
            return types[type] || type;
        }

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

        function getStatutClass(statut) {
            const classes = {
                'en_attente': 'warning',
                'en_cours': 'info',
                'approuve': 'success',
                'refuse': 'danger'
            };
            return classes[statut] || 'secondary';
        }

        function getStatutLabel(statut) {
            const labels = {
                'en_attente': 'En attente',
                'en_cours': 'En cours',
                'approuve': 'Approuvé',
                'refuse': 'Refusé'
            };
            return labels[statut] || statut;
        }

        // Fonction pour charger les détails d'une demande via AJAX
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
            fetch(`get_demande_details.php?id=${demandeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const demande = data.demande;
                        const fichiers = data.fichiers;
                        
                        let fichiersHTML = '';
                        if (fichiers && fichiers.length > 0) {
                            fichiersHTML = `
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6><i class="fas fa-paperclip me-2"></i>Documents joints</h6>
                                        <div class="row">
                            `;
                            
                            fichiers.forEach(fichier => {
                                const typeFormate = formatTypeFichier(fichier.type_fichier);
                                const extension = fichier.chemin_fichier.split('.').pop().toLowerCase();
                                const icon = getFileIcon(extension);
                                const fileUrl = `../uploads/visas/${fichier.chemin_fichier}`;
                                
                                fichiersHTML += `
                                    <div class="col-md-6 mb-2">
                                        <div class="document-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="${icon} me-2"></i>
                                                    <strong>${typeFormate}</strong>
                                                    <br>
                                                    <small class="text-muted">${fichier.chemin_fichier}</small>
                                                </div>
                                                <div>
                                                    <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary" title="Télécharger">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                Uploadé le ${new Date(fichier.date_upload).toLocaleDateString('fr-FR')}
                                            </small>
                                        </div>
                                    </div>
                                `;
                            });
                            
                            fichiersHTML += `
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else {
                            fichiersHTML = `
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
                                    <p><strong>Nom complet:</strong> ${demande.nom_complet}</p>
                                    <p><strong>Date de naissance:</strong> ${formatDate(demande.date_naissance)}</p>
                                    <p><strong>Lieu de naissance:</strong> ${demande.lieu_naissance}</p>
                                    <p><strong>État civil:</strong> ${demande.etat_civil}</p>
                                    <p><strong>Nationalité:</strong> ${demande.nationalite}</p>
                                    <p><strong>Profession:</strong> ${demande.profession}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-address-card me-2"></i>Coordonnées</h6>
                                    <p><strong>Adresse:</strong> ${demande.adresse}</p>
                                    <p><strong>Téléphone:</strong> ${demande.telephone}</p>
                                    <p><strong>Email:</strong> ${demande.email}</p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-passport me-2"></i>Passeport</h6>
                                    <p><strong>Numéro:</strong> ${demande.passeport}</p>
                                    <p><strong>Pays de délivrance:</strong> ${demande.pays_delivrance}</p>
                                    <p><strong>Date de délivrance:</strong> ${formatDate(demande.date_delivrance)}</p>
                                    <p><strong>Date d'expiration:</strong> ${formatDate(demande.date_expiration)}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-plane me-2"></i>Visa demandé</h6>
                                    <p><strong>Type:</strong> ${formatTypeVisa(demande.visa_type)}</p>
                                    <p><strong>Pays de destination:</strong> ${demande.pays_destination}</p>
                                    <p><strong>A déjà un visa:</strong> ${demande.a_deja_visa === 'oui' ? 'Oui' : 'Non'}</p>
                                    ${demande.a_deja_visa === 'oui' ? `<p><strong>Nombre de visas précédents:</strong> ${demande.nb_visas}</p>` : ''}
                                </div>
                            </div>
                            ${demande.details_voyages ? `
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h6><i class="fas fa-route me-2"></i>Détails des voyages</h6>
                                    <div class="bg-light p-3 rounded">
                                        ${demande.details_voyages}
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                            ${fichiersHTML}
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-info-circle me-2"></i>Informations techniques</h6>
                                    <p><strong>Référence:</strong> VS-${demande.id.toString().padStart(6, '0')}</p>
                                    <p><strong>Statut:</strong> <span class="badge bg-${getStatutClass(demande.statut)}">${getStatutLabel(demande.statut)}</span></p>
                                    <p><strong>Date de création:</strong> ${formatDateTime(demande.date_creation)}</p>
                                    <p><strong>Dernière modification:</strong> ${formatDateTime(demande.date_maj)}</p>
                                </div>
                            </div>
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

        function changerStatut(id, nouveauStatut) {
            if (confirm('Êtes-vous sûr de vouloir changer le statut de cette demande ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_court_sejour.php';
                
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

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Administration visas court séjour chargée');
        });
    </script>
</body>
</html>