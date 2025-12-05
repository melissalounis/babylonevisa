<?php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include '../config.php';

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
                        $stmt = $pdo->prepare("UPDATE demandes_etudes_roumanie SET statut = ?, date_modification = NOW() WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut de la demande #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    // Supprimer la demande
                    $stmt = $pdo->prepare("DELETE FROM demandes_etudes_roumanie WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $_SESSION['success_message'] = "Demande #$id supprimée avec succès.";
                    break;
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'opération: " . $e->getMessage();
        }
        
        header('Location: admin_etude_roumanie.php');
        exit();
    }
}

// Initialiser les variables
$demandes = [];
$stats = [
    'total' => 0,
    'nouveau' => 0,
    'en_cours' => 0,
    'approuve' => 0,
    'refuse' => 0,
    'bac' => 0,
    'l1' => 0,
    'l2' => 0,
    'l3' => 0,
    'master' => 0,
    'master2_en_cours' => 0,
    'master2' => 0
];
$total_demandes = 0;
$total_pages = 1;

// Récupérer les paramètres de filtrage
$statut_filter = $_GET['statut'] ?? 'tous';
$niveau_filter = $_GET['niveau'] ?? 'tous';
$langue_filter = $_GET['langue'] ?? 'tous';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Construire la requête avec filtres
    $where_conditions = [];
    $params = [];

    if ($statut_filter !== 'tous') {
        $where_conditions[] = "statut = ?";
        $params[] = $statut_filter;
    }

    if ($niveau_filter !== 'tous') {
        $where_conditions[] = "niveau_etude = ?";
        $params[] = $niveau_filter;
    }

    if ($langue_filter !== 'tous') {
        $where_conditions[] = "programme_langue = ?";
        $params[] = $langue_filter;
    }

    if (!empty($search)) {
        $where_conditions[] = "(nom_complet LIKE ? OR email LIKE ? OR telephone LIKE ? OR specialite LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Récupérer le nombre total pour la pagination
    $count_sql = "SELECT COUNT(*) FROM demandes_etudes_roumanie $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_demandes = $stmt->fetchColumn();
    $total_pages = ceil($total_demandes / $limit);

    // Récupérer les demandes
    $sql = "SELECT * FROM demandes_etudes_roumanie $where_sql ORDER BY date_soumission DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les statistiques
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(statut = 'nouveau') as nouveau,
            SUM(statut = 'en_cours') as en_cours,
            SUM(statut = 'approuve') as approuve,
            SUM(statut = 'refuse') as refuse,
            SUM(niveau_etude = 'bac') as bac,
            SUM(niveau_etude = 'l1') as l1,
            SUM(niveau_etude = 'l2') as l2,
            SUM(niveau_etude = 'l3') as l3,
            SUM(niveau_etude = 'master') as master,
            SUM(niveau_etude = 'master2_en_cours') as master2_en_cours,
            SUM(niveau_etude = 'master2') as master2
        FROM demandes_etudes_roumanie
    ";
    $stats_result = $pdo->query($stats_sql);
    if ($stats_result) {
        $stats = $stats_result->fetch(PDO::FETCH_ASSOC);
        // Assurer que toutes les valeurs sont définies
        $stats = array_merge($stats, [
            'total' => $stats['total'] ?? 0,
            'nouveau' => $stats['nouveau'] ?? 0,
            'en_cours' => $stats['en_cours'] ?? 0,
            'approuve' => $stats['approuve'] ?? 0,
            'refuse' => $stats['refuse'] ?? 0,
            'bac' => $stats['bac'] ?? 0,
            'l1' => $stats['l1'] ?? 0,
            'l2' => $stats['l2'] ?? 0,
            'l3' => $stats['l3'] ?? 0,
            'master' => $stats['master'] ?? 0,
            'master2_en_cours' => $stats['master2_en_cours'] ?? 0,
            'master2' => $stats['master2'] ?? 0
        ]);
    }

} catch(PDOException $e) {
    $error_message = "Erreur lors de la récupération des données: " . $e->getMessage();
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'nouveau' => ['label' => 'Nouveau', 'class' => 'info', 'icon' => 'star'],
        'en_cours' => ['label' => 'En cours', 'class' => 'warning', 'icon' => 'clock'],
        'approuve' => ['label' => 'Approuvé', 'class' => 'success', 'icon' => 'check-circle'],
        'refuse' => ['label' => 'Refusé', 'class' => 'danger', 'icon' => 'times-circle']
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
        'master' => 'Master 1',
        'master2_en_cours' => 'Master 2 en cours',
        'master2' => 'Master 2 terminé'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

// Fonction pour formater la langue
function formatLangue($langue) {
    $langues = [
        'français' => 'Français',
        'anglais' => 'Anglais',
        'roumain' => 'Roumain'
    ];
    return $langues[$langue] ?? $langue;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Études Roumanie - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #002B7F;
            --secondary: #FCD116;
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
            background: linear-gradient(135deg, var(--primary), #1a1a2e);
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
            background: linear-gradient(135deg, var(--primary), #1a1a2e);
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
                    <a href="etude_belgique.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Belgique
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_etude_roumanie.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Roumanie
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
                        Gestion des Études en Roumanie
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes d'études en Roumanie</p>
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
                    <div class="card stat-card border-info">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-star fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-info"><?php echo $stats['nouveau']; ?></h3>
                            <p class="mb-0 text-muted small">Nouvelles</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-warning">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-warning"><?php echo $stats['en_cours']; ?></h3>
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
                                <i class="fas fa-language fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-primary"><?php echo $stats['master']; ?></h3>
                            <p class="mb-0 text-muted small">Master 1</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres et Recherche -->
            <div class="filter-section bg-white rounded p-3 mb-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Filtrer par statut</label>
                        <select name="statut" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $statut_filter === 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
                            <option value="nouveau" <?php echo $statut_filter === 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                            <option value="en_cours" <?php echo $statut_filter === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="approuve" <?php echo $statut_filter === 'approuve' ? 'selected' : ''; ?>>Approuvé</option>
                            <option value="refuse" <?php echo $statut_filter === 'refuse' ? 'selected' : ''; ?>>Refusé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrer par niveau</label>
                        <select name="niveau" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $niveau_filter === 'tous' ? 'selected' : ''; ?>>Tous les niveaux</option>
                            <option value="bac" <?php echo $niveau_filter === 'bac' ? 'selected' : ''; ?>>Bac</option>
                            <option value="l1" <?php echo $niveau_filter === 'l1' ? 'selected' : ''; ?>>Licence 1</option>
                            <option value="l2" <?php echo $niveau_filter === 'l2' ? 'selected' : ''; ?>>Licence 2</option>
                            <option value="l3" <?php echo $niveau_filter === 'l3' ? 'selected' : ''; ?>>Licence 3</option>
                            <option value="master" <?php echo $niveau_filter === 'master' ? 'selected' : ''; ?>>Master 1</option>
                            <option value="master2_en_cours" <?php echo $niveau_filter === 'master2_en_cours' ? 'selected' : ''; ?>>Master 2 en cours</option>
                            <option value="master2" <?php echo $niveau_filter === 'master2' ? 'selected' : ''; ?>>Master 2 terminé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrer par langue</label>
                        <select name="langue" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $langue_filter === 'tous' ? 'selected' : ''; ?>>Toutes les langues</option>
                            <option value="français" <?php echo $langue_filter === 'français' ? 'selected' : ''; ?>>Français</option>
                            <option value="anglais" <?php echo $langue_filter === 'anglais' ? 'selected' : ''; ?>>Anglais</option>
                            <option value="roumain" <?php echo $langue_filter === 'roumain' ? 'selected' : ''; ?>>Roumain</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, email, téléphone, spécialité..." value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Spécialité</th>
                                <th>Niveau</th>
                                <th>Langue</th>
                                <th>Contact</th>
                                <th>Certificat Langue</th>
                                <th>Statut</th>
                                <th>Soumission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($demandes)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune demande d'études en Roumanie trouvée</p>
                                        <?php if ($statut_filter !== 'tous' || $niveau_filter !== 'tous' || $langue_filter !== 'tous' || !empty($search)): ?>
                                            <a href="admin_etude_roumanie.php" class="btn btn-primary">
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
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($demande['specialite']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatNiveauEtude($demande['niveau_etude']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatLangue($demande['programme_langue']); ?>
                                            </span>
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
                                            <?php if ($demande['certificat_file']): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Oui
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-times me-1"></i>Non
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-statut bg-<?php echo $statut['class']; ?>">
                                                <i class="fas fa-<?php echo $statut['icon']; ?> me-1"></i>
                                                <?php echo $statut['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($demande['date_soumission'])); ?>
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
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'nouveau')">
                                                            <i class="fas fa-star text-info me-2"></i>Nouveau
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'en_cours')">
                                                            <i class="fas fa-clock text-warning me-2"></i>En cours
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
                    <h5 class="modal-title">Détails de la demande d'études en Roumanie</h5>
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
                    <form method="POST" action="admin_etude_roumanie.php" id="form-delete-user">
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
            fetch(`get_etude_roumanie_details.php?id=${demandeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const demande = data.demande;
                        
                        // Générer la liste des documents
                        let documentsHTML = '';
                        const documents = [
                            {label: 'Relevé de notes 2nde', field: 'releve_2nde'},
                            {label: 'Relevé de notes 1ère', field: 'releve_1ere'},
                            {label: 'Relevé de notes Terminale', field: 'releve_terminale'},
                            {label: 'Relevé de notes Bac', field: 'releve_bac'},
                            {label: 'Diplôme Bac', field: 'diplome_bac'},
                            {label: 'Certificat de scolarité', field: 'certificat_scolarite'},
                            {label: 'Relevé de notes L1', field: 'releve_l1'},
                            {label: 'Relevé de notes L2', field: 'releve_l2'},
                            {label: 'Relevé de notes L3', field: 'releve_l3'},
                            {label: 'Diplôme Licence', field: 'diplome_licence'},
                            {label: 'Relevé de notes M1', field: 'releve_m1'},
                            {label: 'Relevé de notes M2', field: 'releve_m2'},
                            {label: 'Diplôme Master 2', field: 'diplome_m2'},
                            {label: 'Certificat de langue', field: 'certificat_file'}
                        ];
                        
                        let hasDocuments = false;
                        documentsHTML = `<div class="row mt-3"><div class="col-12"><h6><i class="fas fa-paperclip me-2"></i>Documents téléchargés</h6>`;
                        
                        documents.forEach(doc => {
                            if (demande[doc.field]) {
                                hasDocuments = true;
                                const fileUrl = `uploads/roumanie/${demande[doc.field]}`;
                                const fileExtension = demande[doc.field].split('.').pop().toLowerCase();
                                const iconClass = getFileIcon(fileExtension);
                                
                                documentsHTML += `
                                    <div class="col-md-6 mb-3">
                                        <div class="card document-card">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <i class="${iconClass} fa-2x"></i>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="card-title mb-1">${doc.label}</h6>
                                                        <p class="card-text text-muted small mb-2">
                                                            <i class="fas fa-file me-1"></i>${demande[doc.field]}
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
                            }
                        });
                        
                        // Documents supplémentaires
                        if (demande.documents_supplementaires) {
                            try {
                                const docsSupp = JSON.parse(demande.documents_supplementaires);
                                docsSupp.forEach(doc => {
                                    hasDocuments = true;
                                    const fileUrl = `uploads/roumanie/${doc.fichier}`;
                                    const fileExtension = doc.fichier.split('.').pop().toLowerCase();
                                    const iconClass = getFileIcon(fileExtension);
                                    
                                    documentsHTML += `
                                        <div class="col-md-6 mb-3">
                                            <div class="card document-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3">
                                                            <i class="${iconClass} fa-2x"></i>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h6 class="card-title mb-1">${doc.type}</h6>
                                                            <p class="card-text text-muted small mb-2">
                                                                <i class="fas fa-file me-1"></i>${doc.fichier}
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
                            } catch (e) {
                                console.error('Erreur parsing documents supplémentaires:', e);
                            }
                        }
                        
                        documentsHTML += `</div></div>`;
                        
                        if (!hasDocuments) {
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
                                                <td>${demande.nom_complet}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Email:</strong></td>
                                                <td>${demande.email}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Téléphone:</strong></td>
                                                <td>${demande.telephone}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6><i class="fas fa-graduation-cap me-2"></i>Informations académiques</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td width="40%"><strong>Spécialité:</strong></td>
                                                <td><span class="fw-bold">${demande.specialite}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Niveau d'étude:</strong></td>
                                                <td><span class="badge bg-light text-dark">${formatNiveauEtude(demande.niveau_etude)}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Langue du programme:</strong></td>
                                                <td><span class="badge bg-light text-dark">${formatLangue(demande.programme_langue)}</span></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            ${demande.certificat_type ? `
                            <div class="row mt-3">
                                <div class="col-12">
                                    <h6><i class="fas fa-language me-2"></i>Certificat de langue</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td width="30%"><strong>Type:</strong></td>
                                                <td>${demande.certificat_type}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Score/Niveau:</strong></td>
                                                <td>${demande.certificat_score}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-info-circle me-2"></i>Informations techniques</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td width="40%"><strong>Référence:</strong></td>
                                                <td><code>ER-${demande.id.toString().padStart(6, '0')}</code></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Statut:</strong></td>
                                                <td><span class="badge bg-${getStatutClass(demande.statut)}">${getStatutLabel(demande.statut)}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Date de soumission:</strong></td>
                                                <td>${new Date(demande.date_soumission).toLocaleString('fr-FR')}</td>
                                            </tr>
                                            ${demande.date_modification ? `
                                            <tr>
                                                <td><strong>Dernière modification:</strong></td>
                                                <td>${new Date(demande.date_modification).toLocaleString('fr-FR')}</td>
                                            </tr>
                                            ` : ''}
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

        function changerStatut(id, nouveauStatut) {
            if (confirm('Êtes-vous sûr de vouloir changer le statut de cette demande ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_etude_roumanie.php';
                
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
                'master': 'Master 1',
                'master2_en_cours': 'Master 2 en cours',
                'master2': 'Master 2 terminé'
            };
            return niveaux[niveau] || niveau;
        }

        function formatLangue(langue) {
            const langues = {
                'français': 'Français',
                'anglais': 'Anglais',
                'roumain': 'Roumain'
            };
            return langues[langue] || langue;
        }

        function getStatutClass(statut) {
            const classes = {
                'nouveau': 'info',
                'en_cours': 'warning',
                'approuve': 'success',
                'refuse': 'danger'
            };
            return classes[statut] || 'secondary';
        }

        function getStatutLabel(statut) {
            const labels = {
                'nouveau': 'Nouveau',
                'en_cours': 'En cours',
                'approuve': 'Approuvé',
                'refuse': 'Refusé'
            };
            return labels[statut] || statut;
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Administration études Roumanie chargée');
        });
    </script>
</body>
</html>