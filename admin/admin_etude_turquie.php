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
                        $stmt = $pdo->prepare("UPDATE demandes_turquie SET statut = ?, date_modification = NOW() WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut de la demande #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    // Supprimer la demande et ses fichiers
                    $pdo->beginTransaction();
                    
                    // Récupérer les fichiers associés
                    $stmt = $pdo->prepare("SELECT chemin_fichier FROM demandes_turquie_fichiers WHERE demande_id = ?");
                    $stmt->execute([$id]);
                    $fichiers = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Supprimer les fichiers physiquement
                    foreach ($fichiers as $fichier) {
                        $filepath = __DIR__ . "/../../../uploads/turquie/" . $fichier;
                        if (file_exists($filepath)) {
                            unlink($filepath);
                        }
                    }
                    
                    // Supprimer les entrées fichiers
                    $stmt = $pdo->prepare("DELETE FROM demandes_turquie_fichiers WHERE demande_id = ?");
                    $stmt->execute([$id]);
                    
                    // Supprimer la demande
                    $stmt = $pdo->prepare("DELETE FROM demandes_turquie WHERE id = ?");
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
        
        header('Location: admin_etude_turquie.php');
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
    'bac' => 0,
    'l1' => 0,
    'l2' => 0,
    'l3' => 0,
    'master' => 0,
    'turc' => 0,
    'anglais' => 0
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
        $where_conditions[] = "niveau = ?";
        $params[] = $niveau_filter;
    }

    if ($langue_filter !== 'tous') {
        $where_conditions[] = "programme_langue = ?";
        $params[] = $langue_filter;
    }

    if (!empty($search)) {
        $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ? OR nationalite LIKE ? OR specialite LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Récupérer le nombre total pour la pagination
    $count_sql = "SELECT COUNT(*) FROM demandes_turquie $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_demandes = $stmt->fetchColumn();
    $total_pages = ceil($total_demandes / $limit);

    // Récupérer les demandes
    $sql = "SELECT * FROM demandes_turquie $where_sql ORDER BY date_creation DESC LIMIT $limit OFFSET $offset";
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
            SUM(niveau = 'bac') as bac,
            SUM(niveau = 'l1') as l1,
            SUM(niveau = 'l2') as l2,
            SUM(niveau = 'l3') as l3,
            SUM(niveau = 'master') as master,
            SUM(programme_langue = 'turc') as turc,
            SUM(programme_langue = 'anglais') as anglais
        FROM demandes_turquie
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
            'bac' => 0,
            'l1' => 0,
            'l2' => 0,
            'l3' => 0,
            'master' => 0,
            'turc' => 0,
            'anglais' => 0
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
        'bac' => 'Bac',
        'l1' => 'Licence 1',
        'l2' => 'Licence 2',
        'l3' => 'Licence 3',
        'master' => 'Master'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

// Fonction pour formater la langue
function formatLangue($langue) {
    $langues = [
        'turc' => 'Turc',
        'anglais' => 'Anglais'
    ];
    return $langues[$langue] ?? $langue;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Études Turquie - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --turkey-red: #E30A17;
            --turkey-white: #FFFFFF;
            --turkey-light-red: #f8d7da;
            --light-gray: #f8f9fa;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--turkey-red), #c90813);
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
            background: linear-gradient(135deg, var(--turkey-red), #c90813);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .turkey-flag {
            background: linear-gradient(180deg, var(--turkey-red) 0%, var(--turkey-red) 50%, var(--turkey-white) 50%, var(--turkey-white) 100%);
            height: 4px;
            margin-bottom: 10px;
            border-radius: 2px;
            position: relative;
        }
        
        .turkey-flag::before {
            content: "★";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            color: var(--turkey-white);
            font-size: 8px;
            z-index: 2;
        }
        
        .turkey-flag::after {
            content: "";
            position: absolute;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            width: 12px;
            height: 12px;
            background: var(--turkey-red);
            border-radius: 50%;
            z-index: 1;
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
        
        .langue-badge {
            font-size: 0.7em;
            padding: 4px 8px;
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
                    <a href="admin_etude_suisse.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Suisse
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_etude_luxembourg.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Luxembourg
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_etude_turquie.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Turquie
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
                    <div class="turkey-flag"></div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-mosque text-danger me-2"></i>
                        Gestion des Études en Turquie
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes d'études en Turquie</p>
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
                            <h3 class="mb-1 text-primary"><?php echo $stats['anglais']; ?></h3>
                            <p class="mb-0 text-muted small">Programmes Anglais</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtres et Recherche -->
            <div class="filter-section bg-white rounded p-3 mb-4">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $statut_filter === 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
                            <option value="en_attente" <?php echo $statut_filter === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="en_traitement" <?php echo $statut_filter === 'en_traitement' ? 'selected' : ''; ?>>En traitement</option>
                            <option value="approuvee" <?php echo $statut_filter === 'approuvee' ? 'selected' : ''; ?>>Approuvée</option>
                            <option value="refusee" <?php echo $statut_filter === 'refusee' ? 'selected' : ''; ?>>Refusée</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Niveau</label>
                        <select name="niveau" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $niveau_filter === 'tous' ? 'selected' : ''; ?>>Tous les niveaux</option>
                            <option value="bac" <?php echo $niveau_filter === 'bac' ? 'selected' : ''; ?>>Bac</option>
                            <option value="l1" <?php echo $niveau_filter === 'l1' ? 'selected' : ''; ?>>Licence 1</option>
                            <option value="l2" <?php echo $niveau_filter === 'l2' ? 'selected' : ''; ?>>Licence 2</option>
                            <option value="l3" <?php echo $niveau_filter === 'l3' ? 'selected' : ''; ?>>Licence 3</option>
                            <option value="master" <?php echo $niveau_filter === 'master' ? 'selected' : ''; ?>>Master</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Langue</label>
                        <select name="langue" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $langue_filter === 'tous' ? 'selected' : ''; ?>>Toutes langues</option>
                            <option value="turc" <?php echo $langue_filter === 'turc' ? 'selected' : ''; ?>>Turc</option>
                            <option value="anglais" <?php echo $langue_filter === 'anglais' ? 'selected' : ''; ?>>Anglais</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, email, spécialité..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <a href="admin_etude_turquie.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i> Réinitialiser
                        </a>
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
                                        <p class="text-muted">Aucune demande d'études en Turquie trouvée</p>
                                        <?php if ($statut_filter !== 'tous' || $niveau_filter !== 'tous' || $langue_filter !== 'tous' || !empty($search)): ?>
                                            <a href="admin_etude_turquie.php" class="btn btn-danger">
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
                                                    <div class="fw-bold"><?php echo htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']); ?></div>
                                                    <small class="text-muted">
                                                        ID: <?php echo $demande['user_id']; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($demande['specialite']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatNiveauEtude($demande['niveau']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge langue-badge <?php echo $demande['programme_langue'] === 'anglais' ? 'bg-primary' : 'bg-warning text-dark'; ?>">
                                                <?php echo formatLangue($demande['programme_langue']); ?>
                                            </span>
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
                    <h5 class="modal-title">Détails de la demande d'études en Turquie</h5>
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
                    <form method="POST" action="admin_etude_turquie.php" id="form-delete-user">
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
            fetch(`details_turquie.php?id=${demandeId}`)
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
    // Fonction pour formater la date
    function formatDate(dateString) {
        if (!dateString) return 'Non définie';
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR') + ' à ' + date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'});
        } catch (e) {
            return dateString;
        }
    }

    // Générer le HTML pour les documents
    function genererDocumentsHTML(fichiers) {
        if (!fichiers || Object.keys(fichiers).length === 0) {
            return `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                    <p>Aucun document joint</p>
                </div>
            `;
        }

        let html = '';
        const categories = {
            'certificats_scolarite': 'Certificats de scolarité',
            'releves_notes': 'Relevés de notes',
            'diplomes': 'Diplômes',
            'certificats_langue': 'Certificats de langue',
            'documents_supplementaires': 'Documents supplémentaires'
        };

        let hasDocuments = false;

        for (const [categorie, nomCategorie] of Object.entries(categories)) {
            if (fichiers[categorie] && fichiers[categorie].length > 0) {
                hasDocuments = true;
                html += `
                    <div class="mb-4">
                        <h6 class="text-danger fw-bold border-bottom pb-2 mb-3">${nomCategorie}</h6>
                        <div class="row">
                `;
                
                fichiers[categorie].forEach(fichier => {
                    const nomAffichage = fichier.nom_affichage || fichier.type_fichier || 'Document';
                    const dateUpload = fichier.date_upload ? formatDate(fichier.date_upload) : '';
                    
                    html += `
                        <div class="col-md-6 mb-3">
                            <div class="border-start border-3 border-danger ps-3 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        <strong class="text-break">${nomAffichage}</strong>
                                    </div>
                                    <a href="../../../uploads/turquie/${fichier.nom_fichier}" 
                                       class="btn btn-sm btn-outline-danger ms-2 flex-shrink-0" 
                                       target="_blank"
                                       download="${fichier.nom_fichier}">
                                        <i class="fas fa-download me-1"></i>Télécharger
                                    </a>
                                </div>
                                ${dateUpload ? `
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-calendar me-1"></i>Uploadé le: ${dateUpload}
                                    </small>
                                ` : ''}
                                ${fichier.type_fichier ? `
                                    <small class="text-muted d-block">
                                        <i class="fas fa-tag me-1"></i>Type: ${fichier.type_fichier}
                                    </small>
                                ` : ''}
                            </div>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }
        }

        if (!hasDocuments) {
            return `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                    <p>Aucun document joint</p>
                </div>
            `;
        }

        return html;
    }

    // Vérifier que les données sont valides
    if (!demande || !demande.id) {
        return `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Données de demande invalides
            </div>
        `;
    }

    return `
        <div class="details-container">
            <!-- En-tête -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Demande #${demande.id}</h4>
                        <span class="badge bg-${demande.statut_class || 'secondary'} fs-6">
                            ${demande.statut_formatted || 'Inconnu'}
                        </span>
                    </div>
                    <p class="text-muted mb-0">Référence: TR-${demande.id.toString().padStart(6, '0')}</p>
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
                                    <td>${demande.nom || ''} ${demande.prenom || ''}</td>
                                </tr>
                                <tr>
                                    <td><strong>Date de naissance:</strong></td>
                                    <td>${formatDate(demande.date_naissance)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nationalité:</strong></td>
                                    <td>${demande.nationalite || 'Non spécifiée'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        <a href="mailto:${demande.email || ''}">${demande.email || 'Non spécifié'}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Téléphone:</strong></td>
                                    <td>
                                        <a href="tel:${demande.telephone || ''}">${demande.telephone || 'Non spécifié'}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>ID Utilisateur:</strong></td>
                                    <td>${demande.user_id || 'Non spécifié'}</td>
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
                                Informations académiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Spécialité:</strong></td>
                                    <td>${demande.specialite || 'Non spécifiée'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Niveau:</strong></td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            ${demande.niveau_formatted || demande.niveau || 'Non spécifié'}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Langue programme:</strong></td>
                                    <td>
                                        <span class="badge ${(demande.programme_langue === 'anglais') ? 'bg-primary' : 'bg-warning text-dark'}">
                                            ${demande.langue_formatted || demande.programme_langue || 'Non spécifiée'}
                                        </span>
                                    </td>
                                </tr>
                                ${demande.certificat_type ? `
                                <tr>
                                    <td><strong>Certificat langue:</strong></td>
                                    <td>${demande.certificat_type} - ${demande.certificat_score || 'Non spécifié'}</td>
                                </tr>
                                ` : ''}
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Documents -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-upload me-2 text-danger"></i>
                                Documents joints (${demande.total_fichiers || 0} fichier${(demande.total_fichiers || 0) > 1 ? 's' : ''})
                            </h5>
                        </div>
                        <div class="card-body">
                            ${genererDocumentsHTML(demande.fichiers)}
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
                                            <td>${formatDate(demande.date_creation)}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dernière modification:</strong></td>
                                            <td>${demande.date_modification ? 
                                                formatDate(demande.date_modification) : 
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

            ${demande.commentaire ? `
            <!-- Commentaire -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-comment me-2"></i>
                                Commentaire
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">${(demande.commentaire || '').replace(/\n/g, '<br>')}</p>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
        </div>
    `;
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
            console.log('Administration études Turquie chargée');
        });
    </script>
</body>
</html>