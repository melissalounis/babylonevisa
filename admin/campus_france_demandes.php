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
                        $stmt = $pdo->prepare("UPDATE demandes_campus_france SET statut = ?, date_modification = NOW() WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut de la demande #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    // Supprimer la demande et ses fichiers
                    $pdo->beginTransaction();
                    
                    // Récupérer les fichiers associés
                    $stmt = $pdo->prepare("SELECT chemin_fichier FROM demandes_campus_france_fichiers WHERE demande_id = ?");
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
                    $stmt = $pdo->prepare("DELETE FROM demandes_campus_france_fichiers WHERE demande_id = ?");
                    $stmt->execute([$id]);
                    
                    // Supprimer la demande
                    $stmt = $pdo->prepare("DELETE FROM demandes_campus_france WHERE id = ?");
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
        
        header('Location: admin_campus_france.php');
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
    'licence1' => 0,
    'licence2' => 0,
    'licence3' => 0,
    'master1' => 0,
    'master2' => 0,
    'doctorat' => 0,
    'avec_test_fr' => 0,
    'avec_test_en' => 0,
    'avec_pastel' => 0
];
$total_demandes = 0;
$total_pages = 1;

// Récupérer les paramètres de filtrage
$statut_filter = $_GET['statut'] ?? 'tous';
$niveau_filter = $_GET['niveau'] ?? 'tous';
$test_filter = $_GET['test'] ?? 'tous';
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

    if ($test_filter !== 'tous') {
        if ($test_filter === 'avec_test_fr') {
            $where_conditions[] = "tests_francais != 'non'";
        } elseif ($test_filter === 'avec_test_en') {
            $where_conditions[] = "test_anglais != 'non'";
        } elseif ($test_filter === 'avec_pastel') {
            $where_conditions[] = "boite_pastel = 'oui'";
        }
    }

    if (!empty($search)) {
        $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ? OR nationalite LIKE ? OR domaine_etudes LIKE ?)";
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
    $count_sql = "SELECT COUNT(*) FROM demandes_campus_france $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_demandes = $stmt->fetchColumn();
    $total_pages = ceil($total_demandes / $limit);

    // Récupérer les demandes
    $sql = "SELECT * FROM demandes_campus_france $where_sql ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
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
            SUM(niveau_etudes = 'licence1') as licence1,
            SUM(niveau_etudes = 'licence2') as licence2,
            SUM(niveau_etudes = 'licence3') as licence3,
            SUM(niveau_etudes = 'master1') as master1,
            SUM(niveau_etudes = 'master2') as master2,
            SUM(niveau_etudes = 'doctorat') as doctorat,
            SUM(tests_francais != 'non') as avec_test_fr,
            SUM(test_anglais != 'non') as avec_test_en,
            SUM(boite_pastel = 'oui') as avec_pastel
        FROM demandes_campus_france
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
            'licence1' => 0,
            'licence2' => 0,
            'licence3' => 0,
            'master1' => 0,
            'master2' => 0,
            'doctorat' => 0,
            'avec_test_fr' => 0,
            'avec_test_en' => 0,
            'avec_pastel' => 0
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
        'licence1' => 'Licence 1',
        'licence2' => 'Licence 2',
        'licence3' => 'Licence 3',
        'master1' => 'Master 1',
        'master2' => 'Master 2',
        'master_termine' => 'Master terminé',
        'doctorat' => 'Doctorat',
        'bts' => 'BTS',
        'dut' => 'DUT',
        'inge' => 'Ingénieur',
        'commerce' => 'Commerce'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

// Fonction pour formater le test de langue
function formatTestLangue($test) {
    $tests = [
        'non' => 'Non',
        'tcf' => 'TCF',
        'delf' => 'DELF',
        'dalf' => 'DALF',
        'ielts' => 'IELTS',
        'toefl' => 'TOEFL',
        'toeic' => 'TOEIC',
        'autre' => 'Autre'
    ];
    return $tests[$test] ?? $test;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Campus France - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --campus-blue: #0055a4;
            --campus-red: #ef4135;
            --campus-white: #FFFFFF;
            --campus-light-blue: #e8f2ff;
            --light-gray: #f8f9fa;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--campus-blue), #003366);
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
            background: linear-gradient(135deg, var(--campus-blue), #003366);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .campus-flag {
            background: linear-gradient(90deg, var(--campus-blue) 0%, var(--campus-blue) 33%, var(--campus-white) 33%, var(--campus-white) 66%, var(--campus-red) 66%, var(--campus-red) 100%);
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
        
        .langue-badge {
            font-size: 0.7em;
            padding: 4px 8px;
        }
        
        .test-badge {
            font-size: 0.65em;
            padding: 3px 6px;
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
                    <a href="admin_etude_turquie.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Turquie
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_campus_france.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Campus France
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
                    <div class="campus-flag"></div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-flag text-primary me-2"></i>
                        Gestion des Demandes Campus France
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes Campus France</p>
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
                            <h3 class="mb-1 text-primary"><?php echo $stats['avec_test_fr']; ?></h3>
                            <p class="mb-0 text-muted small">Tests français</p>
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
                            <option value="licence1" <?php echo $niveau_filter === 'licence1' ? 'selected' : ''; ?>>Licence 1</option>
                            <option value="licence2" <?php echo $niveau_filter === 'licence2' ? 'selected' : ''; ?>>Licence 2</option>
                            <option value="licence3" <?php echo $niveau_filter === 'licence3' ? 'selected' : ''; ?>>Licence 3</option>
                            <option value="master1" <?php echo $niveau_filter === 'master1' ? 'selected' : ''; ?>>Master 1</option>
                            <option value="master2" <?php echo $niveau_filter === 'master2' ? 'selected' : ''; ?>>Master 2</option>
                            <option value="doctorat" <?php echo $niveau_filter === 'doctorat' ? 'selected' : ''; ?>>Doctorat</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tests</label>
                        <select name="test" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $test_filter === 'tous' ? 'selected' : ''; ?>>Tous</option>
                            <option value="avec_test_fr" <?php echo $test_filter === 'avec_test_fr' ? 'selected' : ''; ?>>Avec test FR</option>
                            <option value="avec_test_en" <?php echo $test_filter === 'avec_test_en' ? 'selected' : ''; ?>>Avec test EN</option>
                            <option value="avec_pastel" <?php echo $test_filter === 'avec_pastel' ? 'selected' : ''; ?>>Avec Pastel</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, email, domaine..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <a href="admin_campus_france.php" class="btn btn-outline-secondary w-100">
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
                                <th>Domaine</th>
                                <th>Niveau</th>
                                <th>Tests</th>
                                <th>Contact</th>
                                <th>Statut</th>
                                <th>Soumission</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($demandes)): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune demande Campus France trouvée</p>
                                        <?php if ($statut_filter !== 'tous' || $niveau_filter !== 'tous' || $test_filter !== 'tous' || !empty($search)): ?>
                                            <a href="admin_campus_france.php" class="btn btn-primary">
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
                                            <strong class="text-primary">#<?php echo $demande['id']; ?></strong>
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
                                            <div class="fw-bold"><?php echo htmlspecialchars($demande['domaine_etudes']); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatNiveauEtude($demande['niveau_etudes']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php if ($demande['tests_francais'] !== 'non'): ?>
                                                    <span class="badge test-badge bg-info" title="Test français: <?php echo formatTestLangue($demande['tests_francais']); ?>">
                                                        FR
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($demande['test_anglais'] !== 'non'): ?>
                                                    <span class="badge test-badge bg-warning text-dark" title="Test anglais: <?php echo formatTestLangue($demande['test_anglais']); ?>">
                                                        EN
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($demande['boite_pastel'] === 'oui'): ?>
                                                    <span class="badge test-badge bg-success" title="Boîte Pastel">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
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
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Détails de la demande Campus France</h5>
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
                    <form method="POST" action="admin_campus_france.php" id="form-delete-user">
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
            fetch(`details_campus_france.php?id=${demandeId}`)
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

            // Fonction pour afficher les relevés de notes
            function genererRelevesNotes(releves) {
                if (!releves || Object.keys(releves).length === 0) {
                    return '<p class="text-muted">Aucun relevé de notes fourni</p>';
                }

                let html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
                html += '<thead><tr><th>Année</th><th>Moyenne</th><th>Mention</th></tr></thead><tbody>';
                
                Object.values(releves).forEach(releve => {
                    html += `<tr>
                        <td><strong>${releve.annee || 'Non spécifié'}</strong></td>
                        <td>${releve.moyenne || 'Non spécifié'}</td>
                        <td>${releve.mention || 'Non spécifié'}</td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                return html;
            }

            // Fonction pour afficher les autres documents
            function genererAutresDocuments(documents) {
                if (!documents || Object.keys(documents).length === 0) {
                    return '<p class="text-muted">Aucun document supplémentaire</p>';
                }

                let html = '<div class="list-group">';
                Object.values(documents).forEach(doc => {
                    html += `<div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${doc.type || 'Document'}</strong>
                                ${doc.description ? `<br><small class="text-muted">${doc.description}</small>` : ''}
                            </div>
                        </div>
                    </div>`;
                });
                html += '</div>';
                return html;
            }

            // Fonction pour afficher les fichiers
            function genererFichiers(fichiers) {
                // Si c'est un message d'erreur
                if (fichiers.error) {
                    return `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            La gestion des fichiers n'est pas encore configurée.
                        </div>
                    `;
                }

                // Si aucun fichier
                if (!fichiers || fichiers.length === 0) {
                    return `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Aucun fichier n'a été téléchargé pour cette demande.
                        </div>
                    `;
                }

                let html = '<div class="list-group">';
                fichiers.forEach(fichier => {
                    const typeIcon = getFileIcon(fichier.type_fichier);
                    const fileUrl = `/uploads/${fichier.chemin_fichier}`;
                    const fileSize = fichier.taille_fichier ? formatFileSize(fichier.taille_fichier) : '';
                    
                    html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="${typeIcon} me-2 fa-lg"></i>
                                    <div>
                                        <strong class="d-block">${getFileTypeLabel(fichier.type_fichier)}</strong>
                                        <small class="text-muted">${fichier.nom_original || fichier.chemin_fichier}</small>
                                    </div>
                                </div>
                                ${fileSize ? `<small class="text-muted"><i class="fas fa-hdd me-1"></i>${fileSize}</small>` : ''}
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-calendar me-1"></i>
                                    Uploadé le ${formatDate(fichier.date_upload)}
                                </small>
                            </div>
                            <div class="ms-3">
                                <a href="${fileUrl}" target="_blank" class="btn btn-sm btn-outline-primary" title="Télécharger">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                    </div>`;
                });
                html += '</div>';
                return html;
            }

            function getFileIcon(typeFichier) {
                const icons = {
                    'copie_passeport': 'fas fa-passport text-primary',
                    'photo_identite': 'fas fa-id-card text-success',
                    'certificat_scolarite': 'fas fa-file-certificate text-warning',
                    'lettre_motivation': 'fas fa-envelope-open-text text-info',
                    'cv': 'fas fa-file-alt text-secondary',
                    'attestation_francais': 'fas fa-language text-primary',
                    'attestation_anglais': 'fas fa-globe text-warning',
                    'releve_notes': 'fas fa-chart-bar text-success',
                    'default': 'fas fa-file text-muted'
                };
                return icons[typeFichier] || icons.default;
            }

            function getFileTypeLabel(typeFichier) {
                const labels = {
                    'copie_passeport': 'Copie du passeport',
                    'photo_identite': 'Photo d\'identité',
                    'certificat_scolarite': 'Certificat de scolarité',
                    'lettre_motivation': 'Lettre de motivation',
                    'cv': 'Curriculum Vitae',
                    'attestation_francais': 'Attestation de français',
                    'attestation_anglais': 'Attestation d\'anglais',
                    'releve_notes': 'Relevé de notes',
                    'default': 'Document'
                };
                return labels[typeFichier] || typeFichier;
            }

            function formatFileSize(bytes) {
                if (!bytes) return '';
                if (bytes < 1024) return bytes + ' bytes';
                if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / 1048576).toFixed(1) + ' MB';
            }

            return `
                <div class="details-container">
                    <!-- En-tête -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">Demande Campus France #${demande.id}</h4>
                                <span class="badge bg-${demande.statut_class} fs-6">
                                    ${demande.statut_formatted}
                                </span>
                            </div>
                            <p class="text-muted mb-0">Référence: CF-${demande.id.toString().padStart(6, '0')} | ID Utilisateur: ${demande.user_id}</p>
                        </div>
                    </div>

                    <!-- Informations personnelles -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        Informations personnelles
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Nom complet:</strong></td>
                                            <td>${demande.nom} ${demande.prenom}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date de naissance:</strong></td>
                                            <td>${formatDate(demande.date_naissance)}</td>
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
                                            <td><strong>Adresse:</strong></td>
                                            <td>${demande.adresse}</td>
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
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-graduation-cap me-2 text-primary"></i>
                                        Projet d'études
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Pays d'études:</strong></td>
                                            <td>${demande.pays_etudes}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Domaine:</strong></td>
                                            <td>${demande.domaine_etudes}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Niveau:</strong></td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    ${demande.niveau_formatted}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="card h-100 mt-3">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-passport me-2 text-primary"></i>
                                        Passeport
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Numéro:</strong></td>
                                            <td>${demande.num_passeport}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Délivrance:</strong></td>
                                            <td>${formatDate(demande.date_delivrance)}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Expiration:</strong></td>
                                            <td>${formatDate(demande.date_expiration)}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tests de langue -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-language me-2 text-primary"></i>
                                        Test de français
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Test passé:</strong></td>
                                            <td>${demande.test_francais_formatted}</td>
                                        </tr>
                                        ${demande.score_test ? `
                                        <tr>
                                            <td><strong>Score:</strong></td>
                                            <td>${demande.score_test}</td>
                                        </tr>
                                        ` : ''}
                                        <tr>
                                            <td><strong>Niveau estimé:</strong></td>
                                            <td>${demande.niveau_francais || 'Non spécifié'}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-globe me-2 text-primary"></i>
                                        Test d'anglais
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Test passé:</strong></td>
                                            <td>${demande.test_anglais_formatted}</td>
                                        </tr>
                                        ${demande.score_anglais ? `
                                        <tr>
                                            <td><strong>Score:</strong></td>
                                            <td>${demande.score_anglais}</td>
                                        </tr>
                                        ` : ''}
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Boîte Pastel -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-envelope me-2 text-primary"></i>
                                        Boîte Pastel
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Boîte Pastel créée:</strong></td>
                                            <td>${demande.boite_pastel === 'oui' ? 'Oui' : 'Non'}</td>
                                        </tr>
                                        ${demande.email_pastel ? `
                                        <tr>
                                            <td><strong>Email Pastel:</strong></td>
                                            <td>${demande.email_pastel}</td>
                                        </tr>
                                        ` : ''}
                                        ${demande.mdp_pastel ? `
                                        <tr>
                                            <td><strong>Mot de passe Pastel:</strong></td>
                                            <td><code>${demande.mdp_pastel}</code></td>
                                        </tr>
                                        ` : ''}
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Relevés de notes -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-file-alt me-2 text-primary"></i>
                                        Relevés de notes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    ${genererRelevesNotes(demande.releves_annees)}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Autres documents -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-folder-open me-2 text-primary"></i>
                                        Autres documents
                                    </h5>
                                </div>
                                <div class="card-body">
                                    ${genererAutresDocuments(demande.autres_documents)}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fichiers téléchargés -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-paperclip me-2 text-primary"></i>
                                        Fichiers téléchargés
                                    </h5>
                                </div>
                                <div class="card-body">
                                    ${genererFichiers(demande.fichiers)}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informations techniques -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-info-circle me-2 text-primary"></i>
                                        Informations techniques
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td width="40%"><strong>Date de soumission:</strong></td>
                                                    <td>${formatDate(demande.created_at)}</td>
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
                </div>
            `;
        }

        function changerStatut(id, nouveauStatut) {
            if (confirm('Êtes-vous sûr de vouloir changer le statut de cette demande ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_campus_france.php';
                
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
            console.log('Administration Campus France chargée');
        });
    </script>
</body>
</html>