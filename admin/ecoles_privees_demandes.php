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
                        $stmt = $pdo->prepare("UPDATE demandes_ecoles_privees SET statut = ?, date_modification = NOW() WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut de la demande #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    // Supprimer la demande et ses fichiers
                    $pdo->beginTransaction();
                    
                    // Récupérer les fichiers associés
                    $stmt = $pdo->prepare("SELECT chemin_fichier FROM demandes_ecoles_privees_fichiers WHERE demande_id = ?");
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
                    $stmt = $pdo->prepare("DELETE FROM demandes_ecoles_privees_fichiers WHERE demande_id = ?");
                    $stmt->execute([$id]);
                    
                    // Supprimer la demande
                    $stmt = $pdo->prepare("DELETE FROM demandes_ecoles_privees WHERE id = ?");
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
        
        header('Location: ecoles_privees_demandes.php');
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
    'avec_pastel' => 0,
    'budget_5000' => 0,
    'budget_10000' => 0,
    'session_septembre' => 0,
    'session_janvier' => 0,
    'session_avril' => 0
];
$total_demandes = 0;
$total_pages = 1;

// Récupérer les paramètres de filtrage
$statut_filter = $_GET['statut'] ?? 'tous';
$niveau_filter = $_GET['niveau'] ?? 'tous';
$budget_filter = $_GET['budget'] ?? 'tous';
$session_filter = $_GET['session'] ?? 'tous';
$test_filter = $_GET['test'] ?? 'tous';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // D'abord, vérifions la structure de la table
    $stmt = $pdo->query("DESCRIBE demandes_ecoles_privees");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Déterminer la colonne de date à utiliser
    $date_column = 'created_at'; // colonne par défaut
    if (in_array('date_demande', $columns)) {
        $date_column = 'date_demande';
    } elseif (in_array('created_at', $columns)) {
        $date_column = 'created_at';
    } elseif (in_array('date_creation', $columns)) {
        $date_column = 'date_creation';
    }

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

    if ($budget_filter !== 'tous') {
        $where_conditions[] = "budget_etudes = ?";
        $params[] = $budget_filter;
    }

    if ($session_filter !== 'tous') {
        $where_conditions[] = "session_formation = ?";
        $params[] = $session_filter;
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
    $count_sql = "SELECT COUNT(*) FROM demandes_ecoles_privees $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_demandes = $stmt->fetchColumn();
    $total_pages = ceil($total_demandes / $limit);

    // Récupérer les demandes avec la bonne colonne de date
    $sql = "SELECT * FROM demandes_ecoles_privees $where_sql ORDER BY $date_column DESC LIMIT $limit OFFSET $offset";
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
            SUM(boite_pastel = 'oui') as avec_pastel,
            SUM(budget_etudes = '5000') as budget_5000,
            SUM(budget_etudes = '10000') as budget_10000,
            SUM(session_formation = 'session_septembre') as session_septembre,
            SUM(session_formation = 'session_janvier') as session_janvier,
            SUM(session_formation = 'session_avril') as session_avril
        FROM demandes_ecoles_privees
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
            'avec_pastel' => 0,
            'budget_5000' => 0,
            'budget_10000' => 0,
            'session_septembre' => 0,
            'session_janvier' => 0,
            'session_avril' => 0
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

// Fonction pour formater le budget
function formatBudget($budget) {
    $budgets = [
        '5000' => '5 000 €',
        '6000' => '6 000 €',
        '7000' => '7 000 €',
        '8000' => '8 000 €',
        '9000' => '9 000 €',
        '10000' => '10 000 €',
        '11000' => '11 000 €',
        '12000' => '12 000 €'
    ];
    return $budgets[$budget] ?? $budget . ' €';
}

// Fonction pour formater la session
function formatSession($session) {
    $sessions = [
        'session_septembre' => 'Septembre',
        'session_janvier' => 'Janvier',
        'session_avril' => 'Avril'
    ];
    return $sessions[$session] ?? $session;
}

// Fonction pour obtenir la date de soumission
function getDateSoumission($demande, $date_column) {
    if (isset($demande[$date_column])) {
        return date('d/m/Y H:i', strtotime($demande[$date_column]));
    }
    return 'Date inconnue';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Écoles Privées - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --ecole-blue: #0055a4;
            --ecole-gold: #ffd700;
            --ecole-white: #FFFFFF;
            --ecole-light-blue: #e8f2ff;
            --light-gray: #f8f9fa;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--ecole-blue), #003366);
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
            background: linear-gradient(135deg, var(--ecole-blue), #003366);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .ecole-banner {
            background: linear-gradient(90deg, var(--ecole-blue) 0%, var(--ecole-blue) 70%, var(--ecole-gold) 70%, var(--ecole-gold) 100%);
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
        
        .budget-badge {
            font-size: 0.7em;
            padding: 4px 8px;
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }
        
        .session-badge {
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
                    <a href="admin_campus_france.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Campus France
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="universites_demandes.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Paris-Saclay
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="ecoles_privees_demandes.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Écoles Privées
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
                    <div class="ecole-banner"></div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-school text-primary me-2"></i>
                        Gestion des Demandes Écoles Privées
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes pour les écoles privées en France</p>
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
                                <i class="fas fa-school"></i>
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
                    <div class="card stat-card border-primary">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-euro-sign fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-primary"><?php echo $stats['budget_5000'] + $stats['budget_10000']; ?></h3>
                            <p class="mb-0 text-muted small">Avec budget</p>
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
                        <label class="form-label">Budget</label>
                        <select name="budget" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $budget_filter === 'tous' ? 'selected' : ''; ?>>Tous les budgets</option>
                            <option value="5000" <?php echo $budget_filter === '5000' ? 'selected' : ''; ?>>5 000 €</option>
                            <option value="6000" <?php echo $budget_filter === '6000' ? 'selected' : ''; ?>>6 000 €</option>
                            <option value="7000" <?php echo $budget_filter === '7000' ? 'selected' : ''; ?>>7 000 €</option>
                            <option value="8000" <?php echo $budget_filter === '8000' ? 'selected' : ''; ?>>8 000 €</option>
                            <option value="9000" <?php echo $budget_filter === '9000' ? 'selected' : ''; ?>>9 000 €</option>
                            <option value="10000" <?php echo $budget_filter === '10000' ? 'selected' : ''; ?>>10 000 €</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Session</label>
                        <select name="session" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $session_filter === 'tous' ? 'selected' : ''; ?>>Toutes sessions</option>
                            <option value="session_septembre" <?php echo $session_filter === 'session_septembre' ? 'selected' : ''; ?>>Septembre</option>
                            <option value="session_janvier" <?php echo $session_filter === 'session_janvier' ? 'selected' : ''; ?>>Janvier</option>
                            <option value="session_avril" <?php echo $session_filter === 'session_avril' ? 'selected' : ''; ?>>Avril</option>
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
                    <div class="col-md-2">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, email, domaine..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <a href="ecoles_privees_demandes.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Réinitialiser tous les filtres
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
                                <th>Budget</th>
                                <th>Session</th>
                                <th>Tests Langues</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($demandes)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucune demande trouvée</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($demandes as $demande): ?>
                                    <?php 
                                    $statut_info = formatStatut($demande['statut']);
                                    $niveau_etude = formatNiveauEtude($demande['niveau_etudes']);
                                    $budget = formatBudget($demande['budget_etudes']);
                                    $session = formatSession($demande['session_formation']);
                                    $date_soumission = getDateSoumission($demande, $date_column);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $demande['id']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo strtoupper(substr($demande['prenom'], 0, 1) . substr($demande['nom'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($demande['email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium"><?php echo htmlspecialchars($demande['domaine_etudes']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark"><?php echo $niveau_etude; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge budget-badge"><?php echo $budget; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge session-badge bg-info"><?php echo $session; ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 flex-wrap">
                                                <?php if ($demande['tests_francais'] !== 'non'): ?>
                                                    <span class="badge test-badge bg-warning text-dark" title="Test Français: <?php echo formatTestLangue($demande['tests_francais']); ?>">
                                                        <i class="fas fa-language me-1"></i>FR
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($demande['test_anglais'] !== 'non'): ?>
                                                    <span class="badge test-badge bg-danger" title="Test Anglais: <?php echo formatTestLangue($demande['test_anglais']); ?>">
                                                        <i class="fas fa-globe me-1"></i>EN
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($demande['boite_pastel'] === 'oui'): ?>
                                                    <span class="badge test-badge bg-success" title="Boîte Pastel">
                                                        <i class="fas fa-envelope me-1"></i>Pastel
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-statut bg-<?php echo $statut_info['class']; ?>">
                                                <i class="fas fa-<?php echo $statut_info['icon']; ?> me-1"></i>
                                                <?php echo $statut_info['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo $date_soumission; ?></small>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <div class="btn-group btn-group-sm">
                                                    <!-- Bouton Voir -->
                                                    <button type="button" class="btn btn-outline-primary" 
                                                            onclick="voirDemande(<?php echo $demande['id']; ?>)"
                                                            title="Voir les détails">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <!-- Menu déroulant Actions -->
                                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" 
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                        <span class="visually-hidden">Actions</span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <!-- Changer statut -->
                                                        <li>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="id" value="<?php echo $demande['id']; ?>">
                                                                <input type="hidden" name="action" value="changer_statut">
                                                                <button type="submit" name="statut" value="en_attente" 
                                                                        class="dropdown-item <?php echo $demande['statut'] === 'en_attente' ? 'active' : ''; ?>">
                                                                    <i class="fas fa-clock me-2"></i>En attente
                                                                </button>
                                                                <button type="submit" name="statut" value="en_traitement" 
                                                                        class="dropdown-item <?php echo $demande['statut'] === 'en_traitement' ? 'active' : ''; ?>">
                                                                    <i class="fas fa-play-circle me-2"></i>En traitement
                                                                </button>
                                                                <button type="submit" name="statut" value="approuvee" 
                                                                        class="dropdown-item <?php echo $demande['statut'] === 'approuvee' ? 'active' : ''; ?>">
                                                                    <i class="fas fa-check-circle me-2"></i>Approuvée
                                                                </button>
                                                                <button type="submit" name="statut" value="refusee" 
                                                                        class="dropdown-item <?php echo $demande['statut'] === 'refusee' ? 'active' : ''; ?>">
                                                                    <i class="fas fa-times-circle me-2"></i>Refusée
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <!-- Supprimer -->
                                                        <li>
                                                            <form method="POST" class="d-inline" 
                                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.')">
                                                                <input type="hidden" name="id" value="<?php echo $demande['id']; ?>">
                                                                <input type="hidden" name="action" value="supprimer">
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="fas fa-trash me-2"></i>Supprimer
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="p-3 border-top">
                        <nav aria-label="Pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <!-- Page précédente -->
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                                
                                <!-- Pages -->
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <!-- Page suivante -->
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <div class="text-center text-muted small mt-2">
                            Affichage de <?php echo min(($page - 1) * $limit + 1, $total_demandes); ?> 
                            à <?php echo min($page * $limit, $total_demandes); ?> 
                            sur <?php echo $total_demandes; ?> demandes
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function exporterDonnees() {
            // Récupérer les paramètres de filtrage actuels
            const params = new URLSearchParams(window.location.search);
            
            // Ajouter le paramètre d'export
            params.set('export', 'excel');
            
            // Rediriger vers la même page avec les paramètres d'export
            window.location.href = 'ecoles_privees_demandes.php?' + params.toString();
        }

        function voirDemande(id) {
            // Ouvrir une modal ou rediriger vers la page de détails
            window.open('ecoles_privees_details.php?id=' + id, '_blank');
        }

        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Confirmation pour la suppression
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('form[onsubmit*="confirm"]');
            deleteForms.forEach(form => {
                form.onsubmit = function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.')) {
                        e.preventDefault();
                    }
                };
            });
        });
    </script>
</body>
</html>