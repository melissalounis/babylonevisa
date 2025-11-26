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
                        $stmt = $pdo->prepare("UPDATE demandes_suisse SET statut = ?, date_modification = NOW() WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut de la demande #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    // Supprimer la demande et ses fichiers
                    $pdo->beginTransaction();
                    
                    // Récupérer les fichiers associés
                    $stmt = $pdo->prepare("SELECT chemin_fichier FROM demandes_suisse_fichiers WHERE demande_id = ?");
                    $stmt->execute([$id]);
                    $fichiers = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Supprimer les fichiers physiquement
                    foreach ($fichiers as $fichier) {
                        $filepath = __DIR__ . "/../../../uploads/" . $fichier;
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }
                    
                    // Supprimer les entrées fichiers
                    $stmt = $pdo->prepare("DELETE FROM demandes_suisse_fichiers WHERE demande_id = ?");
                    $stmt->execute([$id]);
                    
                    // Supprimer la demande
                    $stmt = $pdo->prepare("DELETE FROM demandes_suisse WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $pdo->commit();
                    
                    $_SESSION['success_message'] = "Demande #$id supprimée avec succès.";
                    break;
            }
        } catch(PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $_SESSION['error_message'] = "Erreur lors de l'opération: " . $e->getMessage();
        }
        
        header('Location: admin_etude_suisse.php');
        exit();
    }
}

// Initialiser les variables
$demandes = [];
$stats = [
    'total' => 0,
    'en_attente' => 0,
    'en_traitement' => 0,
    'approuvee' => 0,
    'refusee' => 0,
    'master1' => 0,
    'master2' => 0,
    'allemand' => 0,
    'francais' => 0,
    'anglais' => 0,
    'italien' => 0
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
        $where_conditions[] = "niveau_etudes = ?";
        $params[] = $niveau_filter;
    }

    if ($langue_filter !== 'tous') {
        $where_conditions[] = "langue_formation = ?";
        $params[] = $langue_filter;
    }

    if (!empty($search)) {
        $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ? OR nationalite LIKE ? OR domaine_etudes LIKE ? OR nom_formation LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Récupérer le nombre total pour la pagination
    $count_sql = "SELECT COUNT(*) FROM demandes_suisse $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_demandes = $stmt->fetchColumn();
    $total_pages = ceil($total_demandes / $limit);

    // Récupérer les demandes avec la colonne date_creation
    $sql = "SELECT * FROM demandes_suisse $where_sql ORDER BY date_creation DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les statistiques
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(statut = 'en_attente') as en_attente,
            SUM(statut = 'en_traitement') as en_traitement,
            SUM(statut = 'approuvee') as approuvee,
            SUM(statut = 'refusee') as refusee,
            SUM(niveau_etudes = 'master1') as master1,
            SUM(niveau_etudes = 'master2') as master2,
            SUM(tests_allemand = 'oui') as allemand,
            SUM(tests_francais = 'oui') as francais,
            SUM(tests_anglais = 'oui') as anglais,
            SUM(tests_italien = 'oui') as italien
        FROM demandes_suisse
    ";
    $stats_result = $pdo->query($stats_sql);
    if ($stats_result) {
        $stats = $stats_result->fetch(PDO::FETCH_ASSOC);
        // Assurer que toutes les valeurs sont définies
        $stats = array_merge([
            'total' => 0,
            'en_attente' => 0,
            'en_traitement' => 0,
            'approuvee' => 0,
            'refusee' => 0,
            'master1' => 0,
            'master2' => 0,
            'allemand' => 0,
            'francais' => 0,
            'anglais' => 0,
            'italien' => 0
        ], $stats);
    }

} catch(PDOException $e) {
    $error_message = "Erreur lors de la récupération des données: " . $e->getMessage();
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'info', 'icon' => 'clock'],
        'en_traitement' => ['label' => 'En traitement', 'class' => 'warning', 'icon' => 'play-circle'],
        'approuvee' => ['label' => 'Approuvée', 'class' => 'success', 'icon' => 'check-circle'],
        'refusee' => ['label' => 'Refusée', 'class' => 'danger', 'icon' => 'times-circle']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary', 'icon' => 'question-circle'];
}

// Fonction pour formater le niveau d'étude
function formatNiveauEtude($niveau) {
    $niveaux = [
        'master1' => 'Master 1',
        'master2' => 'Master 2'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

// Fonction pour formater la langue
function formatLangue($langue) {
    $langues = [
        'allemand' => 'Allemand',
        'francais' => 'Français',
        'anglais' => 'Anglais',
        'italien' => 'Italien',
        'bilingue' => 'Bilingue'
    ];
    return $langues[$langue] ?? $langue;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Études Suisse - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --suisse-red: #FF0000;
            --suisse-white: #FFFFFF;
            --suisse-dark: #1B1B1B;
            --light-gray: #f8f9fa;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--suisse-red), #cc0000);
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
            background: linear-gradient(135deg, var(--suisse-red), #cc0000);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .suisse-cross {
            background: 
                linear-gradient(90deg, transparent 0%, transparent 45%, var(--suisse-red) 45%, var(--suisse-red) 55%, transparent 55%, transparent 100%),
                linear-gradient(0deg, transparent 0%, transparent 45%, var(--suisse-red) 45%, var(--suisse-red) 55%, transparent 55%, transparent 100%);
            height: 4px;
            margin-bottom: 10px;
            border-radius: 2px;
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
                    <a href="admin_etude_belgique.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Belgique
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_etude_roumanie.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Roumanie
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_etude_canada.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Canada
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_etude_espagne.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Espagne
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_etude_suisse.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Suisse
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
                    <div class="suisse-cross"></div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-mountain text-danger me-2"></i>
                        Gestion des Études en Suisse
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes d'études en Suisse</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-danger" onclick="exporterDonnees()">
                        <i class="fas fa-download me-1"></i> Exporter
                    </button>
                    <button class="btn btn-danger" onclick="location.reload()">
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
                                <i class="fas fa-flag"></i>
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
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-info"><?php echo $stats['en_attente']; ?></h3>
                            <p class="mb-0 text-muted small">En attente</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-warning">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-play-circle fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-warning"><?php echo $stats['en_traitement']; ?></h3>
                            <p class="mb-0 text-muted small">En traitement</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-success">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-success"><?php echo $stats['approuvee']; ?></h3>
                            <p class="mb-0 text-muted small">Approuvées</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-danger">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-danger"><?php echo $stats['refusee']; ?></h3>
                            <p class="mb-0 text-muted small">Refusées</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-primary">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-language fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-primary"><?php echo $stats['allemand'] + $stats['francais'] + $stats['anglais'] + $stats['italien']; ?></h3>
                            <p class="mb-0 text-muted small">Tests langue</p>
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
                            <option value="en_attente" <?php echo $statut_filter === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="en_traitement" <?php echo $statut_filter === 'en_traitement' ? 'selected' : ''; ?>>En traitement</option>
                            <option value="approuvee" <?php echo $statut_filter === 'approuvee' ? 'selected' : ''; ?>>Approuvée</option>
                            <option value="refusee" <?php echo $statut_filter === 'refusee' ? 'selected' : ''; ?>>Refusée</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrer par niveau</label>
                        <select name="niveau" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $niveau_filter === 'tous' ? 'selected' : ''; ?>>Tous les niveaux</option>
                            <option value="master1" <?php echo $niveau_filter === 'master1' ? 'selected' : ''; ?>>Master 1</option>
                            <option value="master2" <?php echo $niveau_filter === 'master2' ? 'selected' : ''; ?>>Master 2</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrer par langue</label>
                        <select name="langue" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $langue_filter === 'tous' ? 'selected' : ''; ?>>Toutes les langues</option>
                            <option value="allemand" <?php echo $langue_filter === 'allemand' ? 'selected' : ''; ?>>Allemand</option>
                            <option value="francais" <?php echo $langue_filter === 'francais' ? 'selected' : ''; ?>>Français</option>
                            <option value="anglais" <?php echo $langue_filter === 'anglais' ? 'selected' : ''; ?>>Anglais</option>
                            <option value="italien" <?php echo $langue_filter === 'italien' ? 'selected' : ''; ?>>Italien</option>
                            <option value="bilingue" <?php echo $langue_filter === 'bilingue' ? 'selected' : ''; ?>>Bilingue</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, prénom, email, domaine..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-danger">
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
                                <th>Domaine</th>
                                <th>Formation</th>
                                <th>Langue</th>
                                <th>Nationalité</th>
                                <th>Contact</th>
                                <th>Tests Langue</th>
                                <th>Statut</th>
                                <th>Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($demandes)): ?>
                                <tr>
                                    <td colspan="12" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune demande d'études en Suisse trouvée</p>
                                        <?php if ($statut_filter !== 'tous' || $niveau_filter !== 'tous' || $langue_filter !== 'tous' || !empty($search)): ?>
                                            <a href="admin_etude_suisse.php" class="btn btn-danger">
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
                                    $initiales = strtoupper(substr($demande['nom'], 0, 1) . substr($demande['prenom'], 0, 1));
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="text-danger">#<?php echo $demande['id']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo $initiales; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']); ?></div>
                                                    <small class="text-muted">
                                                        <?php echo date('d/m/Y', strtotime($demande['date_naissance'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatNiveauEtude($demande['niveau_etudes']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($demande['domaine_etudes']); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($demande['nom_formation'])): ?>
                                                <?php echo htmlspecialchars($demande['nom_formation']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Non spécifié</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                <?php echo formatLangue($demande['langue_formation']); ?>
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
                                            <?php if (!empty($demande['telephone'])): ?>
                                            <div class="mt-1">
                                                <i class="fas fa-phone me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($demande['telephone']); ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <?php if ($demande['tests_allemand'] === 'oui'): ?>
                                                    <span class="badge bg-success mb-1">DE</span>
                                                <?php endif; ?>
                                                <?php if ($demande['tests_francais'] === 'oui'): ?>
                                                    <span class="badge bg-success mb-1">FR</span>
                                                <?php endif; ?>
                                                <?php if ($demande['tests_anglais'] === 'oui'): ?>
                                                    <span class="badge bg-success mb-1">EN</span>
                                                <?php endif; ?>
                                                <?php if ($demande['tests_italien'] === 'oui'): ?>
                                                    <span class="badge bg-success mb-1">IT</span>
                                                <?php endif; ?>
                                                <?php if ($demande['tests_allemand'] === 'non' && $demande['tests_francais'] === 'non' && $demande['tests_anglais'] === 'non' && $demande['tests_italien'] === 'non'): ?>
                                                    <span class="badge bg-secondary">Aucun</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-statut bg-<?php echo $statut['class']; ?>">
                                                <i class="fas fa-<?php echo $statut['icon']; ?> me-1"></i>
                                                <?php echo $statut['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('d/m/Y H:i', strtotime($demande['date_creation'])); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm action-buttons">
                                                <button class="btn btn-outline-danger" title="Voir les détails"
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
                                                            <i class="fas fa-clock text-info me-2"></i>En attente
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'en_traitement')">
                                                            <i class="fas fa-play-circle text-warning me-2"></i>En traitement
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'approuvee')">
                                                            <i class="fas fa-check-circle text-success me-2"></i>Approuver
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'refusee')">
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
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Détails de la demande d'études en Suisse</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-danger" role="status">
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
                    <form method="POST" action="admin_etude_suisse.php" id="form-delete-user">
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
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des détails...</p>
                </div>
            `;
            
            modal.show();
            
            // Charger les détails via AJAX
            fetch(`get_etude_suisse_details.php?id=${demandeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const demande = data.demande;
                        content.innerHTML = genererContenuDetails(demande);
                    } else {
                        content.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Erreur: ${data.message}
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

        function genererContenuDetails(demande) {
            return `
                <div class="details-container">
                    <!-- En-tête -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">Demande #${demande.id}</h4>
                                <span class="badge bg-${demande.statut_class} fs-6">
                                    ${demande.statut_formatted}
                                </span>
                            </div>
                            <p class="text-muted mb-0">Référence: CH-${demande.id.toString().padStart(6, '0')}</p>
                        </div>
                    </div>

                    <!-- Informations personnelles -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user me-2 text-danger"></i>
                                        Informations personnelles
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Nom complet:</strong></td>
                                            <td>${demande.prenom} ${demande.nom}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date de naissance:</strong></td>
                                            <td>${new Date(demande.date_naissance).toLocaleDateString('fr-FR')}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Lieu de naissance:</strong></td>
                                            <td>${demande.lieu_naissance}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Nationalité:</strong></td>
                                            <td>${demande.nationalite}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>
                                                <a href="mailto:${demande.email}">${demande.email}</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Téléphone:</strong></td>
                                            <td>
                                                <a href="tel:${demande.telephone}">${demande.telephone}</a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Adresse:</strong></td>
                                            <td>${demande.adresse}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Passeport:</strong></td>
                                            <td>${demande.num_passeport}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-graduation-cap me-2 text-danger"></i>
                                        Projet d'études
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Niveau:</strong></td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    ${demande.niveau_etudes_formatted}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Domaine:</strong></td>
                                            <td>${demande.domaine_etudes}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Formation:</strong></td>
                                            <td>${demande.nom_formation || 'Non spécifié'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date de début:</strong></td>
                                            <td>${demande.date_debut ? new Date(demande.date_debut).toLocaleDateString('fr-FR') : 'Non spécifiée'}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Langue:</strong></td>
                                            <td>
                                                <span class="badge bg-info text-dark">
                                                    ${demande.langue_formation_formatted}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tests de langue -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-language me-2 text-danger"></i>
                                        Tests de langue
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <div class="mb-3">
                                                <h6>Allemand</h6>
                                                <span class="badge ${demande.tests_allemand === 'oui' ? 'bg-success' : 'bg-secondary'}">
                                                    ${demande.tests_allemand_formatted}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="mb-3">
                                                <h6>Français</h6>
                                                <span class="badge ${demande.tests_francais === 'oui' ? 'bg-success' : 'bg-secondary'}">
                                                    ${demande.tests_francais_formatted}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="mb-3">
                                                <h6>Anglais</h6>
                                                <span class="badge ${demande.tests_anglais === 'oui' ? 'bg-success' : 'bg-secondary'}">
                                                    ${demande.tests_anglais_formatted}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="mb-3">
                                                <h6>Italien</h6>
                                                <span class="badge ${demande.tests_italien === 'oui' ? 'bg-success' : 'bg-secondary'}">
                                                    ${demande.tests_italien_formatted}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations techniques -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2 text-danger"></i>
                                        Informations techniques
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td width="40%"><strong>Date de création:</strong></td>
                                                    <td>${new Date(demande.date_creation).toLocaleString('fr-FR')}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Dernière modification:</strong></td>
                                                    <td>${demande.date_modification ? 
                                                        new Date(demande.date_modification).toLocaleString('fr-FR') : 
                                                        'Non modifiée'
                                                    }</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-grid gap-2">
                                                <button class="btn btn-outline-success" onclick="changerStatut(${demande.id}, 'approuvee')">
                                                    <i class="fas fa-check me-1"></i>Approuver
                                                </button>
                                                <button class="btn btn-outline-warning" onclick="changerStatut(${demande.id}, 'en_traitement')">
                                                    <i class="fas fa-play me-1"></i>En traitement
                                                </button>
                                                <button class="btn btn-outline-danger" onclick="changerStatut(${demande.id}, 'refusee')">
                                                    <i class="fas fa-times me-1"></i>Refuser
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function changerStatut(id, nouveauStatut) {
            if (confirm('Êtes-vous sûr de vouloir changer le statut de cette demande ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_etude_suisse.php';
                
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
            console.log('Administration études Suisse chargée');
        });
    </script>
</body>
</html>