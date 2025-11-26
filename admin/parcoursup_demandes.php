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

// DEBUG: Vérifier la structure de la table
try {
    $structure = $pdo->query("DESCRIBE demandes_parcoursup")->fetchAll(PDO::FETCH_ASSOC);
    $count_total = $pdo->query("SELECT COUNT(*) FROM demandes_parcoursup")->fetchColumn();
} catch (Exception $e) {
    $debug_error = $e->getMessage();
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
                        // Vérifier d'abord si la colonne date_modification existe
                        $check_column = $pdo->query("SHOW COLUMNS FROM demandes_parcoursup LIKE 'date_modification'")->fetch();
                        if ($check_column) {
                            $stmt = $pdo->prepare("UPDATE demandes_parcoursup SET statut = ?, date_modification = NOW() WHERE id = ?");
                        } else {
                            $stmt = $pdo->prepare("UPDATE demandes_parcoursup SET statut = ? WHERE id = ?");
                        }
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut de la demande #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    // Supprimer d'abord les fichiers associés
                    $stmt_files = $pdo->prepare("DELETE FROM demandes_parcoursup_fichiers WHERE demande_id = ?");
                    $stmt_files->execute([$id]);
                    
                    // Puis supprimer la demande
                    $stmt = $pdo->prepare("DELETE FROM demandes_parcoursup WHERE id = ?");
                    $stmt->execute([$id]);
                    
                    $_SESSION['success_message'] = "Demande #$id supprimée avec succès.";
                    break;
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'opération: " . $e->getMessage();
        }
        
        header('Location: admin_parcoursup.php');
        exit();
    }
}

// Initialiser les variables
$demandes = [];
$stats = [
    'total' => 0,
    'en_attente' => 0,
    'en_cours' => 0,
    'approuve' => 0,
    'refuse' => 0,
    'termine' => 0,
    'annule' => 0,
    'licence1' => 0,
    'licence2' => 0,
    'licence3' => 0,
    'master1' => 0,
    'master2' => 0,
    'doctorat' => 0,
    'bts' => 0,
    'dut' => 0,
    'inge' => 0,
    'commerce' => 0
];
$total_demandes = 0;
$total_pages = 1;

// Récupérer les paramètres de filtrage
$statut_filter = $_GET['statut'] ?? 'tous';
$niveau_filter = $_GET['niveau'] ?? 'tous';
$domaine_filter = $_GET['domaine'] ?? 'tous';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // D'abord, vérifier la structure de la table
    $table_structure = $pdo->query("DESCRIBE demandes_parcoursup")->fetchAll(PDO::FETCH_COLUMN);
    
    // Déterminer la colonne de date à utiliser pour le tri
    $date_column = 'created_at'; // valeur par défaut
    if (in_array('date_demande', $table_structure)) {
        $date_column = 'date_demande';
    } elseif (in_array('date_soumission', $table_structure)) {
        $date_column = 'date_soumission';
    } elseif (in_array('created_at', $table_structure)) {
        $date_column = 'created_at';
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

    if ($domaine_filter !== 'tous') {
        $where_conditions[] = "domaine_etudes LIKE ?";
        $params[] = "%$domaine_filter%";
    }

    if (!empty($search)) {
        $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ? OR nationalite LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Récupérer le nombre total pour la pagination
    $count_sql = "SELECT COUNT(*) FROM demandes_parcoursup $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_demandes = $stmt->fetchColumn();
    $total_pages = ceil($total_demandes / $limit);

    // Récupérer les demandes avec jointure utilisateur
    $sql = "SELECT 
                dp.*,
                u.name as user_name,
                u.email as user_email
            FROM demandes_parcoursup dp 
            LEFT JOIN users u ON dp.user_id = u.id 
            $where_sql 
            ORDER BY $date_column DESC 
            LIMIT $limit OFFSET $offset";
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
            SUM(statut = 'termine') as termine,
            SUM(statut = 'annule') as annule,
            SUM(niveau_etudes = 'licence1') as licence1,
            SUM(niveau_etudes = 'licence2') as licence2,
            SUM(niveau_etudes = 'licence3') as licence3,
            SUM(niveau_etudes = 'master1') as master1,
            SUM(niveau_etudes = 'master2') as master2,
            SUM(niveau_etudes = 'doctorat') as doctorat,
            SUM(niveau_etudes = 'bts') as bts,
            SUM(niveau_etudes = 'dut') as dut,
            SUM(niveau_etudes = 'inge') as inge,
            SUM(niveau_etudes = 'commerce') as commerce
        FROM demandes_parcoursup
    ";
    $stats_result = $pdo->query($stats_sql);
    if ($stats_result) {
        $stats = $stats_result->fetch(PDO::FETCH_ASSOC);
        // Assurer que toutes les valeurs sont définies
        $stats = array_merge([
            'total' => 0,
            'en_attente' => 0,
            'en_cours' => 0,
            'approuve' => 0,
            'refuse' => 0,
            'termine' => 0,
            'annule' => 0,
            'licence1' => 0,
            'licence2' => 0,
            'licence3' => 0,
            'master1' => 0,
            'master2' => 0,
            'doctorat' => 0,
            'bts' => 0,
            'dut' => 0,
            'inge' => 0,
            'commerce' => 0
        ], $stats);
    }

} catch(PDOException $e) {
    $error_message = "Erreur lors de la récupération des données: " . $e->getMessage();
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'info', 'icon' => 'clock'],
        'en_cours' => ['label' => 'En cours', 'class' => 'warning', 'icon' => 'cog'],
        'approuve' => ['label' => 'Approuvé', 'class' => 'success', 'icon' => 'check-circle'],
        'refuse' => ['label' => 'Refusé', 'class' => 'danger', 'icon' => 'times-circle'],
        'termine' => ['label' => 'Terminé', 'class' => 'success', 'icon' => 'flag-checkered'],
        'annule' => ['label' => 'Annulé', 'class' => 'secondary', 'icon' => 'ban']
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
        'inge' => 'École ingénieur',
        'commerce' => 'École commerce'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

// Fonction pour formater la date (gère les différentes colonnes possibles)
function formatDateDemande($demande) {
    if (isset($demande['date_demande']) && !empty($demande['date_demande'])) {
        return date('d/m/Y H:i', strtotime($demande['date_demande']));
    } elseif (isset($demande['date_soumission']) && !empty($demande['date_soumission'])) {
        return date('d/m/Y H:i', strtotime($demande['date_soumission']));
    } elseif (isset($demande['created_at']) && !empty($demande['created_at'])) {
        return date('d/m/Y H:i', strtotime($demande['created_at']));
    } else {
        return 'Date non disponible';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Parcoursup - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4b0082;
            --secondary: #FFFFFF;
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
            background: linear-gradient(135deg, var(--primary), #6a0dad);
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
            background: linear-gradient(135deg, var(--primary), #6a0dad);
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
        
        .parcoursup-flag {
            background: linear-gradient(90deg, #4b0082 0%, #4b0082 50%, #FFFFFF 50%, #FFFFFF 100%);
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
                    <a href="demandes_sejour.php" class="nav-link text-white rounded">
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
                    <a href="etude_roumanie.php" class="nav-link text-white rounded">
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
                    <a href="parcoursup_demandes.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Parcoursup
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
                    <div class="parcoursup-flag"></div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-list-alt text-primary me-2"></i>
                        Gestion des Demandes Parcoursup
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes d'inscription Parcoursup</p>
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

            <!-- Debug Info (visible seulement en cas de problème) -->
            <?php if (isset($debug_error)): ?>
                <div class="alert alert-warning">
                    <h6>Debug Information:</h6>
                    <p>Erreur structure table: <?php echo $debug_error; ?></p>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-0">
                        <div class="card-body text-center">
                            <div class="user-avatar mx-auto mb-3">
                                <i class="fas fa-list-alt"></i>
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
                                <i class="fas fa-cog fa-2x"></i>
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
                                <i class="fas fa-graduation-cap fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-primary"><?php echo $stats['licence1']; ?></h3>
                            <p class="mb-0 text-muted small">Licence 1</p>
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
                            <option value="en_cours" <?php echo $statut_filter === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="approuve" <?php echo $statut_filter === 'approuve' ? 'selected' : ''; ?>>Approuvé</option>
                            <option value="refuse" <?php echo $statut_filter === 'refuse' ? 'selected' : ''; ?>>Refusé</option>
                            <option value="termine" <?php echo $statut_filter === 'termine' ? 'selected' : ''; ?>>Terminé</option>
                            <option value="annule" <?php echo $statut_filter === 'annule' ? 'selected' : ''; ?>>Annulé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrer par niveau</label>
                        <select name="niveau" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $niveau_filter === 'tous' ? 'selected' : ''; ?>>Tous les niveaux</option>
                            <option value="licence1" <?php echo $niveau_filter === 'licence1' ? 'selected' : ''; ?>>Licence 1</option>
                            <option value="licence2" <?php echo $niveau_filter === 'licence2' ? 'selected' : ''; ?>>Licence 2</option>
                            <option value="licence3" <?php echo $niveau_filter === 'licence3' ? 'selected' : ''; ?>>Licence 3</option>
                            <option value="master1" <?php echo $niveau_filter === 'master1' ? 'selected' : ''; ?>>Master 1</option>
                            <option value="master2" <?php echo $niveau_filter === 'master2' ? 'selected' : ''; ?>>Master 2</option>
                            <option value="doctorat" <?php echo $niveau_filter === 'doctorat' ? 'selected' : ''; ?>>Doctorat</option>
                            <option value="bts" <?php echo $niveau_filter === 'bts' ? 'selected' : ''; ?>>BTS</option>
                            <option value="dut" <?php echo $niveau_filter === 'dut' ? 'selected' : ''; ?>>DUT</option>
                            <option value="inge" <?php echo $niveau_filter === 'inge' ? 'selected' : ''; ?>>École ingénieur</option>
                            <option value="commerce" <?php echo $niveau_filter === 'commerce' ? 'selected' : ''; ?>>École commerce</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Filtrer par domaine</label>
                        <select name="domaine" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $domaine_filter === 'tous' ? 'selected' : ''; ?>>Tous les domaines</option>
                            <option value="Informatique" <?php echo $domaine_filter === 'Informatique' ? 'selected' : ''; ?>>Informatique</option>
                            <option value="Droit" <?php echo $domaine_filter === 'Droit' ? 'selected' : ''; ?>>Droit</option>
                            <option value="Médecine" <?php echo $domaine_filter === 'Médecine' ? 'selected' : ''; ?>>Médecine</option>
                            <option value="Commerce" <?php echo $domaine_filter === 'Commerce' ? 'selected' : ''; ?>>Commerce</option>
                            <option value="Ingénierie" <?php echo $domaine_filter === 'Ingénierie' ? 'selected' : ''; ?>>Ingénierie</option>
                            <option value="Sciences" <?php echo $domaine_filter === 'Sciences' ? 'selected' : ''; ?>>Sciences</option>
                            <option value="Lettres" <?php echo $domaine_filter === 'Lettres' ? 'selected' : ''; ?>>Lettres</option>
                            <option value="Arts" <?php echo $domaine_filter === 'Arts' ? 'selected' : ''; ?>>Arts</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, prénom, email, téléphone..." value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Domaine</th>
                                <th>Nationalité</th>
                                <th>Contact</th>
                                <th>Test Langue</th>
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
                                        <p class="text-muted">Aucune demande Parcoursup trouvée</p>
                                        <?php if ($statut_filter !== 'tous' || $niveau_filter !== 'tous' || $domaine_filter !== 'tous' || !empty($search)): ?>
                                            <a href="admin_parcoursup.php" class="btn btn-primary">
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
                                    $initiales = strtoupper(substr($demande['prenom'], 0, 1) . substr($demande['nom'], 0, 1));
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
                                            <?php if (!empty($demande['tests_francais']) && $demande['tests_francais'] !== 'non'): ?>
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
                                            <?php echo formatDateDemande($demande); ?>
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
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'en_cours')">
                                                            <i class="fas fa-cog text-warning me-2"></i>En cours
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'approuve')">
                                                            <i class="fas fa-check-circle text-success me-2"></i>Approuver
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'refuse')">
                                                            <i class="fas fa-times-circle text-danger me-2"></i>Refuser
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'termine')">
                                                            <i class="fas fa-flag-checkered text-success me-2"></i>Terminer
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'annule')">
                                                            <i class="fas fa-ban text-secondary me-2"></i>Annuler
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
                    <h5 class="modal-title">Détails de la demande Parcoursup</h5>
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
                    <form method="POST" action="admin_parcoursup.php" id="form-delete-user">
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
            fetch(`get_parcoursup_details.php?id=${demandeId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const demande = data.demande;
                        
                        content.innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><i class="fas fa-user me-2"></i>Informations personnelles</h6>
                                    <div class="table-responsive">
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
                                    <h6><i class="fas fa-graduation-cap me-2"></i>Projet d'études</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless">
                                            <tr>
                                                <td width="40%"><strong>Niveau:</strong></td>
                                                <td><span class="badge bg-light text-dark">${formatNiveauEtude(demande.niveau_etudes)}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Domaine:</strong></td>
                                                <td><span class="fw-bold">${demande.domaine_etudes}</span></td>
                                            </tr>
                                        </table>
                                    </div>
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
                                                <td><code>PS-${demande.id.toString().padStart(6, '0')}</code></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Statut:</strong></td>
                                                <td><span class="badge bg-${getStatutClass(demande.statut)}">${getStatutLabel(demande.statut)}</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Date de soumission:</strong></td>
                                                <td>${new Date(demande.date_demande || demande.created_at).toLocaleString('fr-FR')}</td>
                                            </tr>
                                        </table>
                                    </div>
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
                form.action = 'admin_parcoursup.php';
                
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
                'licence1': 'Licence 1',
                'licence2': 'Licence 2',
                'licence3': 'Licence 3',
                'master1': 'Master 1',
                'master2': 'Master 2',
                'master_termine': 'Master terminé',
                'doctorat': 'Doctorat',
                'bts': 'BTS',
                'dut': 'DUT',
                'inge': 'École ingénieur',
                'commerce': 'École commerce'
            };
            return niveaux[niveau] || niveau;
        }

        function getStatutClass(statut) {
            const classes = {
                'en_attente': 'info',
                'en_cours': 'warning',
                'approuve': 'success',
                'refuse': 'danger',
                'termine': 'success',
                'annule': 'secondary'
            };
            return classes[statut] || 'secondary';
        }

        function getStatutLabel(statut) {
            const labels = {
                'en_attente': 'En attente',
                'en_cours': 'En cours',
                'approuve': 'Approuvé',
                'refuse': 'Refusé',
                'termine': 'Terminé',
                'annule': 'Annulé'
            };
            return labels[statut] || statut;
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Administration Parcoursup chargée');
        });
    </script>
</body>
</html>