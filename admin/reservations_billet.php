<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Vérifier et créer la table si nécessaire avec la structure correcte
try {
    $pdo->query("SELECT 1 FROM demandes_billets_avion LIMIT 1");
} catch (PDOException $e) {
    // Table n'existe pas, créons-la avec la bonne structure
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS demandes_billets_avion (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_email VARCHAR(255) NOT NULL,
            numero_dossier VARCHAR(50),
            email_contact VARCHAR(255) NOT NULL,
            telephone_contact VARCHAR(50),
            type_vol ENUM('aller_simple', 'aller_retour') NOT NULL,
            pays_depart VARCHAR(100) NOT NULL,
            ville_depart VARCHAR(100) NOT NULL,
            pays_arrivee VARCHAR(100) NOT NULL,
            ville_arrivee VARCHAR(100) NOT NULL,
            date_depart DATE NOT NULL,
            date_retour DATE NULL,
            classe VARCHAR(50) NOT NULL,
            compagnie_preferee VARCHAR(100),
            baggage_main VARCHAR(50),
            baggage_soute VARCHAR(50),
            commentaires TEXT,
            statut VARCHAR(50) DEFAULT 'nouveau',
            date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
            date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
            notes_admin TEXT
        )
    ";
    
    try {
        $pdo->exec($create_table_sql);
        $_SESSION['success_message'] = "Table demandes_billets_avion créée avec succès.";
    } catch (PDOException $create_error) {
        die("Erreur lors de la création de la table: " . $create_error->getMessage());
    }
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
                        $stmt = $pdo->prepare("UPDATE demandes_billets_avion SET statut = ? WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut de la réservation #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    $stmt = $pdo->prepare("DELETE FROM demandes_billets_avion WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['success_message'] = "Réservation #$id supprimée avec succès.";
                    break;
                    
                case 'sauvegarder_notes':
                    if (isset($_POST['notes_admin'])) {
                        $notes = $_POST['notes_admin'];
                        $stmt = $pdo->prepare("UPDATE demandes_billets_avion SET notes_admin = ? WHERE id = ?");
                        $stmt->execute([$notes, $id]);
                        $_SESSION['success_message'] = "Notes sauvegardées pour la réservation #$id.";
                    }
                    break;
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'opération: " . $e->getMessage();
        }
        
        header('Location: reservations_billet.php');
        exit();
    }
}

// Fonction sécurisée pour récupérer les valeurs
function getReservationValue($reservation, $key, $default = '') {
    return isset($reservation[$key]) && $reservation[$key] !== '' && $reservation[$key] !== null ? $reservation[$key] : $default;
}

// Initialiser les variables
$reservations = [];
$stats = [
    'total' => 0,
    'nouveau' => 0,
    'en_attente' => 0,
    'en_traitement' => 0,
    'confirmee' => 0,
    'annulee' => 0,
    'aller_simple' => 0,
    'aller_retour' => 0
];
$total_reservations = 0;
$total_pages = 1;

// Récupérer les paramètres de filtrage
$statut_filter = $_GET['statut'] ?? 'tous';
$type_filter = $_GET['type'] ?? 'tous';
$classe_filter = $_GET['classe'] ?? 'tous';
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

    if ($type_filter !== 'tous') {
        $where_conditions[] = "type_vol = ?";
        $params[] = $type_filter;
    }

    if ($classe_filter !== 'tous') {
        $where_conditions[] = "classe = ?";
        $params[] = $classe_filter;
    }

    if (!empty($search)) {
        $where_conditions[] = "(user_email LIKE ? OR email_contact LIKE ? OR telephone_contact LIKE ? OR ville_depart LIKE ? OR ville_arrivee LIKE ? OR numero_dossier LIKE ?)";
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
    $count_sql = "SELECT COUNT(*) FROM demandes_billets_avion $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_reservations = $stmt->fetchColumn();
    $total_pages = ceil($total_reservations / $limit);

    // Récupérer les réservations
    $sql = "SELECT * FROM demandes_billets_avion $where_sql ORDER BY date_creation DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les statistiques
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(statut = 'nouveau' OR statut IS NULL OR statut = '') as nouveau,
            SUM(statut = 'en_attente') as en_attente,
            SUM(statut = 'en_traitement') as en_traitement,
            SUM(statut = 'confirmee') as confirmee,
            SUM(statut = 'annulee') as annulee,
            SUM(type_vol = 'aller_simple') as aller_simple,
            SUM(type_vol = 'aller_retour') as aller_retour
        FROM demandes_billets_avion
    ";
    $stats_result = $pdo->query($stats_sql);
    if ($stats_result) {
        $stats_data = $stats_result->fetch(PDO::FETCH_ASSOC);
        $stats = array_merge($stats, $stats_data);
    }

} catch(PDOException $e) {
    $error_message = "Erreur lors de la récupération des données: " . $e->getMessage();
}

// Fonctions de formatage sécurisées
function formatStatut($statut) {
    if (empty($statut)) {
        return ['label' => 'Nouveau', 'class' => 'primary', 'icon' => 'star'];
    }
    
    $statuts = [
        'nouveau' => ['label' => 'Nouveau', 'class' => 'primary', 'icon' => 'star'],
        'en_attente' => ['label' => 'En attente', 'class' => 'warning', 'icon' => 'clock'],
        'en_traitement' => ['label' => 'En traitement', 'class' => 'info', 'icon' => 'cog'],
        'confirmee' => ['label' => 'Confirmée', 'class' => 'success', 'icon' => 'check-circle'],
        'annulee' => ['label' => 'Annulée', 'class' => 'danger', 'icon' => 'times-circle']
    ];
    return $statuts[$statut] ?? ['label' => ucfirst($statut), 'class' => 'secondary', 'icon' => 'question-circle'];
}

function formatClasse($classe) {
    if (empty($classe)) return 'Économique';
    
    $classes = [
        'economique' => 'Économique',
        'affaire' => 'Affaire',
        'premiere' => 'Première',
        'business' => 'Affaire'
    ];
    return $classes[$classe] ?? ucfirst($classe);
}

function formatTypeVol($type_vol) {
    $types = [
        'aller_simple' => 'Aller simple',
        'aller_retour' => 'Aller-retour'
    ];
    return $types[$type_vol] ?? $type_vol;
}

function formatDate($date) {
    if (empty($date) || $date === '0000-00-00') return 'N/A';
    return date('d/m/Y', strtotime($date));
}

function getInitiales($email) {
    if (empty($email)) return 'NR';
    $username = strstr($email, '@', true);
    if (empty($username)) return 'NR';
    
    // Prendre les deux premières lettres du nom d'utilisateur
    return strtoupper(substr($username, 0, 2));
}

function formatDateReservation($reservation) {
    if (!empty($reservation['date_creation']) && $reservation['date_creation'] !== '0000-00-00 00:00:00') {
        return date('d/m/Y', strtotime($reservation['date_creation']));
    }
    return 'N/A';
}

function getNomAffichage($reservation) {
    // Utiliser l'email comme nom d'affichage ou extraire le nom de l'email
    $email = getReservationValue($reservation, 'user_email');
    $username = strstr($email, '@', true);
    return $username ? ucfirst($username) : 'Client';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservations Billets d'Avion - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00966E;
            --secondary: #D62612;
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
            background: linear-gradient(135deg, var(--primary), #00664d);
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
            background: linear-gradient(135deg, #6f42c1, #5a32a3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .avion-header {
            background: linear-gradient(135deg, #6f42c1, #5a32a3);
            color: white;
            padding: 25px 0;
            margin: -20px -20px 30px -20px;
        }
        
        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
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
                    <i class="fas fa-plane"></i><br>
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
                    <a href="admin_etude_bulgarie.php" class="nav-link text-white rounded">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Études Bulgarie
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="reservations_hotel.php" class="nav-link text-white rounded">
                        <i class="fas fa-hotel me-2"></i>
                        Réservations Hôtel
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="reservations_billet.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-ticket-alt me-2"></i>
                        Réservations Billets
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
            <div class="avion-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-1">
                            <i class="fas fa-ticket-alt me-2"></i>
                            Gestion des Réservations Billets d'Avion
                        </h1>
                        <p class="mb-0 opacity-75">Administration et suivi des demandes de billets d'avion</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-light" onclick="exporterDonnees()">
                            <i class="fas fa-download me-1"></i> Exporter
                        </button>
                        <button class="btn btn-light" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Actualiser
                        </button>
                    </div>
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
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['total']; ?></h3>
                            <p class="mb-0 text-muted small">Total</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-primary">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-star fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-primary"><?php echo $stats['nouveau']; ?></h3>
                            <p class="mb-0 text-muted small">Nouveaux</p>
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
                                <i class="fas fa-cog fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-info"><?php echo $stats['en_traitement']; ?></h3>
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
                            <h3 class="mb-1 text-success"><?php echo $stats['confirmee']; ?></h3>
                            <p class="mb-0 text-muted small">Confirmées</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card stat-card border-danger">
                        <div class="card-body text-center">
                            <div class="text-danger mb-2">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-danger"><?php echo $stats['annulee']; ?></h3>
                            <p class="mb-0 text-muted small">Annulées</p>
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
                            <option value="en_attente" <?php echo $statut_filter === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="en_traitement" <?php echo $statut_filter === 'en_traitement' ? 'selected' : ''; ?>>En traitement</option>
                            <option value="confirmee" <?php echo $statut_filter === 'confirmee' ? 'selected' : ''; ?>>Confirmée</option>
                            <option value="annulee" <?php echo $statut_filter === 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type de vol</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $type_filter === 'tous' ? 'selected' : ''; ?>>Tous les types</option>
                            <option value="aller_simple" <?php echo $type_filter === 'aller_simple' ? 'selected' : ''; ?>>Aller simple</option>
                            <option value="aller_retour" <?php echo $type_filter === 'aller_retour' ? 'selected' : ''; ?>>Aller-retour</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Classe</label>
                        <select name="classe" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $classe_filter === 'tous' ? 'selected' : ''; ?>>Toutes les classes</option>
                            <option value="economique" <?php echo $classe_filter === 'economique' ? 'selected' : ''; ?>>Économique</option>
                            <option value="affaire" <?php echo $classe_filter === 'affaire' ? 'selected' : ''; ?>>Affaire</option>
                            <option value="premiere" <?php echo $classe_filter === 'premiere' ? 'selected' : ''; ?>>Première</option>
                            <option value="business" <?php echo $classe_filter === 'business' ? 'selected' : ''; ?>>Business</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Email, téléphone, ville départ, ville arrivée, numéro dossier..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tableau des réservations -->
            <div class="table-container">
                <?php if (empty($reservations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-ticket-alt"></i>
                        <h4>Aucune réservation de billet trouvée</h4>
                        <p class="text-muted">
                            <?php if ($statut_filter !== 'tous' || $type_filter !== 'tous' || $classe_filter !== 'tous' || !empty($search)): ?>
                                Aucune réservation ne correspond à vos critères de recherche.
                            <?php else: ?>
                                Aucune réservation de billet d'avion n'a été soumise pour le moment.
                            <?php endif; ?>
                        </p>
                        <?php if ($statut_filter !== 'tous' || $type_filter !== 'tous' || $classe_filter !== 'tous' || !empty($search)): ?>
                            <a href="reservations_billet.php" class="btn btn-primary mt-3">
                                <i class="fas fa-times me-1"></i> Réinitialiser les filtres
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Contact</th>
                                    <th>Vol</th>
                                    <th>Dates</th>
                                    <th>Type</th>
                                    <th>Classe</th>
                                    <th>Bagages</th>
                                    <th>Statut</th>
                                    <th>Réservation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($reservations as $reservation): ?>
                                    <?php 
                                    $user_email = getReservationValue($reservation, 'user_email');
                                    $email_contact = getReservationValue($reservation, 'email_contact', $user_email);
                                    $telephone = getReservationValue($reservation, 'telephone_contact');
                                    $numero_dossier = getReservationValue($reservation, 'numero_dossier', 'N/A');
                                    $type_vol = getReservationValue($reservation, 'type_vol');
                                    $ville_depart = getReservationValue($reservation, 'ville_depart');
                                    $ville_arrivee = getReservationValue($reservation, 'ville_arrivee');
                                    $pays_depart = getReservationValue($reservation, 'pays_depart');
                                    $pays_arrivee = getReservationValue($reservation, 'pays_arrivee');
                                    $date_depart = getReservationValue($reservation, 'date_depart');
                                    $date_retour = getReservationValue($reservation, 'date_retour');
                                    $classe = getReservationValue($reservation, 'classe', 'economique');
                                    $baggage_main = getReservationValue($reservation, 'baggage_main');
                                    $baggage_soute = getReservationValue($reservation, 'baggage_soute');
                                    $statut_val = getReservationValue($reservation, 'statut', 'nouveau');
                                    $compagnie = getReservationValue($reservation, 'compagnie_preferee');
                                    
                                    $statut = formatStatut($statut_val);
                                    $nom_affichage = getNomAffichage($reservation);
                                    $initiales = getInitiales($user_email);
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary">#<?php echo $reservation['id']; ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo $numero_dossier; ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo $initiales; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($nom_affichage); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($user_email); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="fas fa-envelope me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($email_contact); ?>
                                            </div>
                                            <?php if (!empty($telephone)): ?>
                                            <div class="mt-1">
                                                <i class="fas fa-phone me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($telephone); ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark">
                                                <i class="fas fa-plane-departure text-info me-1"></i>
                                                <?php echo htmlspecialchars($ville_depart); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($pays_depart); ?>)</small>
                                            </div>
                                            <div class="text-dark">
                                                <i class="fas fa-plane-arrival text-success me-1"></i>
                                                <?php echo htmlspecialchars($ville_arrivee); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($pays_arrivee); ?>)</small>
                                            </div>
                                            <?php if (!empty($compagnie)): ?>
                                            <div class="text-muted small mt-1">
                                                <i class="fas fa-building me-1"></i>
                                                <?php echo htmlspecialchars($compagnie); ?>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?php echo formatDate($date_depart); ?>
                                            </div>
                                            <?php if (!empty($date_retour)): ?>
                                                <div class="text-muted small">
                                                    Retour: <?php echo formatDate($date_retour); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo formatTypeVol($type_vol); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-white">
                                                <?php echo formatClasse($classe); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <?php if (!empty($baggage_main)): ?>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-briefcase me-1"></i><?php echo htmlspecialchars($baggage_main); ?>
                                                    </small>
                                                <?php endif; ?>
                                                <?php if (!empty($baggage_soute)): ?>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-suitcase me-1"></i><?php echo htmlspecialchars($baggage_soute); ?>
                                                    </small>
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
                                            <?php echo formatDateReservation($reservation); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm action-buttons">
                                                <button class="btn btn-outline-primary" title="Voir les détails"
                                                        onclick="afficherDetails(<?php echo $reservation['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                                                            data-bs-toggle="dropdown" title="Changer le statut">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $reservation['id']; ?>, 'nouveau')">
                                                            <i class="fas fa-star text-primary me-2"></i>Nouveau
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $reservation['id']; ?>, 'en_attente')">
                                                            <i class="fas fa-clock text-warning me-2"></i>En attente
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $reservation['id']; ?>, 'en_traitement')">
                                                            <i class="fas fa-cog text-info me-2"></i>En traitement
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $reservation['id']; ?>, 'confirmee')">
                                                            <i class="fas fa-check-circle text-success me-2"></i>Confirmer
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $reservation['id']; ?>, 'annulee')">
                                                            <i class="fas fa-times-circle text-danger me-2"></i>Annuler
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                <button class="btn btn-outline-danger" title="Supprimer"
                                                        onclick="supprimerReservation(<?php echo $reservation['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
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
                        Affichage de <?php echo count($reservations); ?> sur <?php echo $total_reservations; ?> réservations
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
                    <h5 class="modal-title">Détails de la réservation de billet</h5>
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
                    <p>Êtes-vous sûr de vouloir supprimer définitivement cette réservation ? Cette action est irréversible.</p>
                    <form method="POST" action="reservations_billet.php" id="form-delete-reservation">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id" id="delete_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="form-delete-reservation" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function afficherDetails(reservationId) {
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            const content = document.getElementById('detailsContent');
            
            content.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des détails...</p>
                </div>
            `;
            
            modal.show();
            
            // Simulation de chargement des détails
            setTimeout(() => {
                chargerDetailsReservation(reservationId, content);
            }, 500);
        }

        function chargerDetailsReservation(reservationId, contentElement) {
            // Dans une implémentation réelle, vous feriez un appel AJAX ici
            const detailsHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Informations Client</h6>
                        <div class="mb-3">
                            <strong>Email utilisateur:</strong><br>
                            <span id="detail-user-email">Chargement...</span>
                        </div>
                        <div class="mb-3">
                            <strong>Email de contact:</strong><br>
                            <span id="detail-email-contact">Chargement...</span>
                        </div>
                        <div class="mb-3">
                            <strong>Téléphone:</strong><br>
                            <span id="detail-telephone">Chargement...</span>
                        </div>
                        <div class="mb-3">
                            <strong>Numéro dossier:</strong><br>
                            <span id="detail-dossier">Chargement...</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Détails du Vol</h6>
                        <div class="mb-3">
                            <strong>Départ:</strong><br>
                            <span id="detail-depart">Chargement...</span>
                        </div>
                        <div class="mb-3">
                            <strong>Arrivée:</strong><br>
                            <span id="detail-arrivee">Chargement...</span>
                        </div>
                        <div class="mb-3">
                            <strong>Dates:</strong><br>
                            <span id="detail-dates">Chargement...</span>
                        </div>
                        <div class="mb-3">
                            <strong>Type de vol:</strong><br>
                            <span id="detail-type">Chargement...</span>
                        </div>
                        <div class="mb-3">
                            <strong>Classe:</strong><br>
                            <span id="detail-classe">Chargement...</span>
                        </div>
                        <div class="mb-3">
                            <strong>Compagnie préférée:</strong><br>
                            <span id="detail-compagnie">Chargement...</span>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Bagages</h6>
                        <div class="mb-3">
                            <strong>Bagage main:</strong><br>
                            <span id="detail-baggage-main">Chargement...</span>
                        </div>
                        <div class="mb-3">
                            <strong>Bagage soute:</strong><br>
                            <span id="detail-baggage-soute">Chargement...</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Informations de Réservation</h6>
                        <div class="mb-2">
                            <strong>Statut:</strong><br>
                            <span id="detail-statut">Chargement...</span>
                        </div>
                        <div class="mb-2">
                            <strong>Date soumission:</strong><br>
                            <span id="detail-soumission">Chargement...</span>
                        </div>
                        <div class="mb-2">
                            <strong>Date création:</strong><br>
                            <span id="detail-creation">Chargement...</span>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2">Commentaires & Notes</h6>
                        <div class="mb-3">
                            <strong>Commentaires client:</strong><br>
                            <div id="detail-commentaires" class="border p-2 bg-light rounded">Chargement...</div>
                        </div>
                        <form id="form-notes">
                            <input type="hidden" name="id" value="${reservationId}">
                            <input type="hidden" name="action" value="sauvegarder_notes">
                            <div class="mb-3">
                                <label class="form-label"><strong>Notes administrateur:</strong></label>
                                <textarea class="form-control" name="notes_admin" id="notes_admin" rows="4" placeholder="Ajoutez des notes administratives ici..."></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Sauvegarder les notes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            contentElement.innerHTML = detailsHtml;
            
            // Simulation de chargement des données
            setTimeout(() => {
                simulerChargementDetails(reservationId);
            }, 300);
        }

        function simulerChargementDetails(reservationId) {
            // Simulation de données basées sur l'ID
            const donneesSimulees = {
                user_email: "client" + reservationId + "@email.com",
                email_contact: "contact" + reservationId + "@email.com",
                telephone_contact: "+33 1 23 45 67 8" + reservationId,
                numero_dossier: "BLT-2024-" + reservationId.toString().padStart(3, '0'),
                pays_depart: "France",
                ville_depart: "Paris (CDG)",
                pays_arrivee: "États-Unis",
                ville_arrivee: "New York (JFK)",
                date_depart: "2024-03-15",
                date_retour: "2024-03-22",
                type_vol: "aller_retour",
                classe: "affaire",
                compagnie_preferee: "Air France",
                baggage_main: "1 cabine",
                baggage_soute: "1 valise 23kg",
                commentaires: "Vol avec préférence pour siège côté couloir si possible.",
                statut: "en_traitement",
                date_soumission: "2024-01-15 14:30:00",
                date_creation: "2024-01-15 14:30:00",
                notes_admin: "Client VIP - À traiter en priorité"
            };
            
            afficherDonneesDetails(donneesSimulees);
        }

        function afficherDonneesDetails(donnees) {
            document.getElementById('detail-user-email').textContent = donnees.user_email;
            document.getElementById('detail-email-contact').textContent = donnees.email_contact;
            document.getElementById('detail-telephone').textContent = donnees.telephone_contact;
            document.getElementById('detail-dossier').textContent = donnees.numero_dossier;
            document.getElementById('detail-depart').textContent = `${donnees.ville_depart}, ${donnees.pays_depart}`;
            document.getElementById('detail-arrivee').textContent = `${donnees.ville_arrivee}, ${donnees.pays_arrivee}`;
            document.getElementById('detail-dates').textContent = `Départ: ${formatDateAffichage(donnees.date_depart)}${donnees.date_retour ? `, Retour: ${formatDateAffichage(donnees.date_retour)}` : ''}`;
            document.getElementById('detail-type').textContent = formatTypeVolAffichage(donnees.type_vol);
            document.getElementById('detail-classe').textContent = formatClasse(donnees.classe);
            document.getElementById('detail-compagnie').textContent = donnees.compagnie_preferee || 'Non spécifiée';
            document.getElementById('detail-baggage-main').textContent = donnees.baggage_main || 'Non spécifié';
            document.getElementById('detail-baggage-soute').textContent = donnees.baggage_soute || 'Non spécifié';
            document.getElementById('detail-statut').innerHTML = `<span class="badge bg-info">${formatStatutAffichage(donnees.statut)}</span>`;
            document.getElementById('detail-soumission').textContent = formatDateTime(donnees.date_soumission);
            document.getElementById('detail-creation').textContent = formatDateTime(donnees.date_creation);
            document.getElementById('detail-commentaires').textContent = donnees.commentaires || 'Aucun commentaire';
            
            if (donnees.notes_admin) {
                document.getElementById('notes_admin').value = donnees.notes_admin;
            }
        }

        function formatDateAffichage(dateStr) {
            if (!dateStr || dateStr === '0000-00-00') return 'N/A';
            const date = new Date(dateStr);
            return date.toLocaleDateString('fr-FR');
        }

        function formatDateTime(dateTimeStr) {
            if (!dateTimeStr || dateTimeStr === '0000-00-00 00:00:00') return 'N/A';
            const date = new Date(dateTimeStr);
            return date.toLocaleString('fr-FR');
        }

        function formatTypeVolAffichage(typeVol) {
            const types = {
                'aller_simple': 'Aller simple',
                'aller_retour': 'Aller-retour'
            };
            return types[typeVol] || typeVol;
        }

        function formatStatutAffichage(statut) {
            const statuts = {
                'nouveau': 'Nouveau',
                'en_attente': 'En attente',
                'en_traitement': 'En traitement',
                'confirmee': 'Confirmée',
                'annulee': 'Annulée'
            };
            return statuts[statut] || statut;
        }

        function formatClasse(classe) {
            const classes = {
                'economique': 'Économique',
                'affaire': 'Affaire',
                'premiere': 'Première',
                'business': 'Business'
            };
            return classes[classe] || classe;
        }

        function changerStatut(reservationId, nouveauStatut) {
            if (confirm(`Voulez-vous vraiment changer le statut de la réservation #${reservationId} ?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'reservations_billet.php';
                
                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'changer_statut';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = reservationId;
                
                const inputStatut = document.createElement('input');
                inputStatut.type = 'hidden';
                inputStatut.name = 'statut';
                inputStatut.value = nouveauStatut;
                
                form.appendChild(inputAction);
                form.appendChild(inputId);
                form.appendChild(inputStatut);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        function supprimerReservation(reservationId) {
            document.getElementById('delete_id').value = reservationId;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }

        function exporterDonnees() {
            // Récupérer les paramètres de filtrage actuels
            const params = new URLSearchParams(window.location.search);
            
            // Ouvrir une nouvelle fenêtre/onglet pour l'export
            const url = `export_reservations_billets.php?${params.toString()}`;
            window.open(url, '_blank');
        }

        // Gestion du formulaire de notes
        document.addEventListener('DOMContentLoaded', function() {
            const formNotes = document.getElementById('form-notes');
            if (formNotes) {
                formNotes.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    fetch('reservations_billet.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        alert('Notes sauvegardées avec succès !');
                    })
                    .catch(error => {
                        console.error('Erreur:', error);
                        alert('Erreur lors de la sauvegarde des notes.');
                    });
                });
            }
        });
    </script>
</body>
</html>