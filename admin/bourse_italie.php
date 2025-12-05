<?php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

include '../config.php';

// Vérifier et créer la table si nécessaire
try {
    $pdo->query("SELECT 1 FROM demandes_bourse_italie LIMIT 1");
} catch (PDOException $e) {
    // Table n'existe pas, créons-la
    $create_table_sql = "
        CREATE TABLE IF NOT EXISTS demandes_bourse_italie (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT,
            type_bourse VARCHAR(50) NOT NULL,
            niveau_etudes VARCHAR(50) NOT NULL,
            domaine_etudes VARCHAR(255) NOT NULL,
            universite_choisie VARCHAR(255) NOT NULL,
            programme VARCHAR(255) NOT NULL,
            duree_etudes VARCHAR(50) NOT NULL,
            moyenne DECIMAL(4,2) NOT NULL,
            nom VARCHAR(255) NOT NULL,
            prenom VARCHAR(255) NOT NULL,
            date_naissance DATE NOT NULL,
            lieu_naissance VARCHAR(255) NOT NULL,
            nationalite VARCHAR(100) NOT NULL,
            adresse TEXT NOT NULL,
            telephone VARCHAR(50) NOT NULL,
            email VARCHAR(255) NOT NULL,
            tests_italien VARCHAR(50) NOT NULL,
            tests_anglais VARCHAR(50) NOT NULL,
            consentement TINYINT(1) NOT NULL,
            newsletter TINYINT(1) NOT NULL,
            date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
            statut ENUM('en_attente', 'acceptee', 'refusee') DEFAULT 'en_attente',
            notes_admin TEXT,
            date_traitement DATETIME
        )
    ";
    
    try {
        $pdo->exec($create_table_sql);
        $_SESSION['success_message'] = "Table demandes_bourse_italie créée avec succès.";
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
                        $notes_admin = $_POST['notes_admin'] ?? '';
                        
                        $stmt = $pdo->prepare("UPDATE demandes_bourse_italie SET statut = ?, notes_admin = ?, date_traitement = NOW() WHERE id = ?");
                        $stmt->execute([$statut, $notes_admin, $id]);
                        
                        $_SESSION['success_message'] = "Statut de la demande #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    $stmt = $pdo->prepare("DELETE FROM demandes_bourse_italie WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['success_message'] = "Demande #$id supprimée avec succès.";
                    break;
                    
                case 'ajouter_note':
                    $notes_admin = $_POST['notes_admin'] ?? '';
                    $stmt = $pdo->prepare("UPDATE demandes_bourse_italie SET notes_admin = ? WHERE id = ?");
                    $stmt->execute([$notes_admin, $id]);
                    $_SESSION['success_message'] = "Notes mises à jour pour la demande #$id.";
                    break;
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'opération: " . $e->getMessage();
        }
        
        header('Location: admin_bourses.php');
        exit();
    }
}

// Initialiser les variables
$demandes = [];
$stats = [
    'total' => 0,
    'en_attente' => 0,
    'acceptee' => 0,
    'refusee' => 0
];
$total_demandes = 0;
$total_pages = 1;

// Récupérer les paramètres de filtrage
$statut_filter = $_GET['statut'] ?? 'tous';
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

    if (!empty($search)) {
        $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
    }

    $where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Récupérer le nombre total pour la pagination
    $count_sql = "SELECT COUNT(*) FROM demandes_bourse_italie $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_demandes = $stmt->fetchColumn();
    $total_pages = ceil($total_demandes / $limit);

    // DÉTERMINER LA COLONNE DE TRI - CORRECTION ICI
    $order_column = "id"; // Par défaut
    
    // Vérifier quelles colonnes existent
    $check_columns = $pdo->query("SHOW COLUMNS FROM demandes_bourse_italie");
    $columns = $check_columns->fetchAll(PDO::FETCH_COLUMN);
    
    if (in_array('date_soumission', $columns)) {
        $order_column = "date_soumission";
    } elseif (in_array('date_creation', $columns)) {
        $order_column = "date_creation";
    }
    // Sinon, on garde 'id' par défaut

    // Récupérer les demandes avec la bonne colonne de tri
    $sql = "SELECT * FROM demandes_bourse_italie $where_sql ORDER BY $order_column DESC LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les statistiques
    $stats_sql = "
        SELECT 
            COUNT(*) as total,
            SUM(statut = 'en_attente') as en_attente,
            SUM(statut = 'acceptee') as acceptee,
            SUM(statut = 'refusee') as refusee
        FROM demandes_bourse_italie
    ";
    $stats_result = $pdo->query($stats_sql);
    if ($stats_result) {
        $stats = $stats_result->fetch(PDO::FETCH_ASSOC);
        // Assurer que toutes les valeurs sont définies
        $stats = array_merge([
            'total' => 0,
            'en_attente' => 0,
            'acceptee' => 0,
            'refusee' => 0
        ], $stats);
        
        // Convertir les valeurs NULL en 0
        foreach ($stats as $key => $value) {
            if ($value === null) {
                $stats[$key] = 0;
            }
        }
    }

} catch(PDOException $e) {
    $error_message = "Erreur lors de la récupération des données: " . $e->getMessage();
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'warning', 'icon' => 'clock'],
        'acceptee' => ['label' => 'Acceptée', 'class' => 'success', 'icon' => 'check-circle'],
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
        'doctorat' => 'Doctorat'
    ];
    return $niveaux[$niveau] ?? ($niveau ?: 'Non spécifié');
}

// Fonction pour formater le type de bourse
function formatTypeBourse($type) {
    $types = [
        'excellence' => 'Bourse d\'Excellence',
        'merite' => 'Bourse au Mérite',
        'sportive' => 'Bourse Sportive',
        'culturelle' => 'Bourse Culturelle',
        'recherche' => 'Bourse de Recherche'
    ];
    return $types[$type] ?? $type;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Bourses Italie - Babylone Service</title>
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
            background: linear-gradient(135deg, var(--primary), #00664d);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1em;
        }
        
        .bourse-badge {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8em;
            font-weight: 500;
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
                    <a href="admin_bourses.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Bourses Italie
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
                        <i class="fas fa-money-bill-wave text-success me-2"></i>
                        Gestion des Demandes de Bourse Italie
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes de bourse pour l'Italie</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success" onclick="exporterDonnees()">
                        <i class="fas fa-download me-1"></i> Exporter
                    </button>
                    <button class="btn btn-success" onclick="location.reload()">
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
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card border-0">
                        <div class="card-body text-center">
                            <div class="user-avatar mx-auto mb-3">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['total']; ?></h3>
                            <p class="mb-0 text-muted small">Total Demandes</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
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
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card border-success">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-success"><?php echo $stats['acceptee']; ?></h3>
                            <p class="mb-0 text-muted small">Acceptées</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
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
                    <div class="col-md-4">
                        <label class="form-label">Filtrer par statut</label>
                        <select name="statut" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $statut_filter === 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
                            <option value="en_attente" <?php echo $statut_filter === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                            <option value="acceptee" <?php echo $statut_filter === 'acceptee' ? 'selected' : ''; ?>>Acceptée</option>
                            <option value="refusee" <?php echo $statut_filter === 'refusee' ? 'selected' : ''; ?>>Refusée</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, prénom, email, téléphone..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="admin_bourses.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tableau des demandes -->
            <div class="table-container">
                <?php if (empty($demandes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>Aucune demande de bourse trouvée</h4>
                        <p class="text-muted">
                            <?php if ($statut_filter !== 'tous' || !empty($search)): ?>
                                Aucune demande ne correspond à vos critères de recherche.
                            <?php else: ?>
                                Aucune demande de bourse pour l'Italie n'a été soumise pour le moment.
                            <?php endif; ?>
                        </p>
                        <?php if ($statut_filter !== 'tous' || !empty($search)): ?>
                            <a href="admin_bourses.php" class="btn btn-success mt-3">
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
                                    <th>Étudiant</th>
                                    <th>Type Bourse</th>
                                    <th>Niveau</th>
                                    <th>Domaine</th>
                                    <th>Université</th>
                                    <th>Contact</th>
                                    <th>Statut</th>
                                    <th>Soumission</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($demandes as $demande): ?>
                                    <?php 
                                    $statut = formatStatut($demande['statut']);
                                    $initiales = strtoupper(substr($demande['prenom'], 0, 1) . substr($demande['nom'], 0, 1));
                                    $nom_complet = $demande['prenom'] . ' ' . $demande['nom'];
                                    
                                    // Gérer l'affichage de la date de soumission
                                    $date_soumission = '';
                                    if (!empty($demande['date_soumission'])) {
                                        $date_soumission = date('d/m/Y H:i', strtotime($demande['date_soumission']));
                                    } elseif (!empty($demande['date_creation'])) {
                                        $date_soumission = date('d/m/Y H:i', strtotime($demande['date_creation']));
                                    } else {
                                        $date_soumission = 'N/A';
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <strong class="text-success">#<?php echo $demande['id']; ?></strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo $initiales; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($nom_complet); ?></div>
                                                    <small class="text-muted">
                                                        <?php echo !empty($demande['date_naissance']) ? date('d/m/Y', strtotime($demande['date_naissance'])) : 'Date naissance non renseignée'; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <?php echo formatTypeBourse($demande['type_bourse']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark"><?php echo formatNiveauEtude($demande['niveau_etudes']); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                <?php echo !empty($demande['domaine_etudes']) ? htmlspecialchars($demande['domaine_etudes']) : 'Non spécifié'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo !empty($demande['universite_choisie']) ? htmlspecialchars($demande['universite_choisie']) : 'Non spécifié'; ?></small>
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
                                            <?php echo $date_soumission; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm action-buttons">
                                                <button class="btn btn-outline-success" title="Voir les détails"
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
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'acceptee')">
                                                            <i class="fas fa-check-circle text-success me-2"></i>Accepter
                                                        </a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'refusee')">
                                                            <i class="fas fa-times-circle text-danger me-2"></i>Refuser
                                                        </a></li>
                                                    </ul>
                                                </div>
                                                <button class="btn btn-outline-info" title="Ajouter des notes"
                                                        onclick="ajouterNotes(<?php echo $demande['id']; ?>)">
                                                    <i class="fas fa-sticky-note"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" title="Supprimer"
                                                        onclick="supprimerDemande(<?php echo $demande['id']; ?>)">
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
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Détails de la demande de bourse Italie</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <div class="text-center">
                        <div class="spinner-border text-success" role="status">
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
                    <form method="POST" action="admin_bourses.php" id="form-delete-user">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id" id="delete_id">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="form-delete-user" tu termine ca                    <button type="submit" form="form-delete-user" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour changer le statut -->
    <div class="modal fade" id="statutModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Changer le statut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="admin_bourses.php" id="form-statut-user">
                        <input type="hidden" name="action" value="changer_statut">
                        <input type="hidden" name="id" id="statut_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Nouveau statut</label>
                            <select name="statut" class="form-select" required>
                                <option value="en_attente">En attente</option>
                                <option value="acceptee">Acceptée</option>
                                <option value="refusee">Refusée</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes administratives (optionnel)</label>
                            <textarea name="notes_admin" class="form-control" rows="3" placeholder="Ajoutez des notes ou commentaires..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="form-statut-user" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour ajouter des notes -->
    <div class="modal fade" id="notesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Notes administratives</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="admin_bourses.php" id="form-notes-user">
                        <input type="hidden" name="action" value="ajouter_note">
                        <input type="hidden" name="id" id="notes_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes_admin" class="form-control" rows="5" placeholder="Ajoutez vos notes ou commentaires sur cette demande..." id="notes_content"></textarea>
                            <div class="form-text">
                                Ces notes sont visibles uniquement par les administrateurs.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="form-notes-user" class="btn btn-success">
                        <i class="fas fa-save me-1"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Fonction pour afficher les détails d'une demande
    function afficherDetails(id) {
        // Afficher le loading
        document.getElementById('detailsContent').innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Chargement...</span>
                </div>
                <p class="mt-2">Chargement des détails...</p>
            </div>
        `;
        
        const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
        modal.show();

        // Créer une requête AJAX pour récupérer les détails
        fetch('get_bourse_details.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.demande) {
                    const demande = data.demande;
                    const fichiers = data.fichiers || [];
                    afficherDetailsDansModal(demande, fichiers);
                } else {
                    document.getElementById('detailsContent').innerHTML = 
                        '<div class="alert alert-danger">' + (data.message || 'Erreur lors du chargement des détails') + '</div>';
                }
            })
            .catch(error => {
                document.getElementById('detailsContent').innerHTML = 
                    '<div class="alert alert-danger">Erreur lors du chargement des détails: ' + error + '</div>';
            });
    }

    // Fonction pour formater et afficher les détails dans le modal
    function afficherDetailsDansModal(demande, fichiers) {
        // Fonctions de formatage
        function formatStatut(statut) {
            const statuts = {
                'en_attente': ['En attente', 'warning', 'clock'],
                'acceptee': ['Acceptée', 'success', 'check-circle'],
                'refusee': ['Refusée', 'danger', 'times-circle']
            };
            return statuts[statut] || [statut, 'secondary', 'question-circle'];
        }
        
        function formatNiveauEtude(niveau) {
            const niveaux = {
                'licence1': 'Licence 1',
                'licence2': 'Licence 2', 
                'licence3': 'Licence 3',
                'master1': 'Master 1',
                'master2': 'Master 2',
                'doctorat': 'Doctorat'
            };
            return niveaux[niveau] || niveau;
        }
        
        function formatTypeBourse(type) {
            const types = {
                'excellence': 'Bourse d\'Excellence',
                'merite': 'Bourse au Mérite',
                'sportive': 'Bourse Sportive',
                'culturelle': 'Bourse Culturelle',
                'recherche': 'Bourse de Recherche'
            };
            return types[type] || type;
        }
        
        function formatTypeFichier(type) {
            const types = {
                'releves_notes': 'Relevés de notes',
                'diplomes': 'Diplômes',
                'lettres_recommandation': 'Lettres de recommandation',
                'passeport': 'Passeport',
                'photo_identite': 'Photo d\'identité',
                'attestation_italien': 'Attestation d\'italien',
                'attestation_anglais': 'Attestation d\'anglais',
                'autres_documents': 'Autres documents'
            };
            return types[type] || type;
        }
        
        function getIconeFichier(nomFichier) {
            const extension = nomFichier.split('.').pop().toLowerCase();
            if (['pdf'].includes(extension)) return 'file-pdf';
            if (['jpg', 'jpeg', 'png', 'gif', 'bmp'].includes(extension)) return 'file-image';
            if (['doc', 'docx'].includes(extension)) return 'file-word';
            return 'file';
        }

        const [statutLabel, statutClass, statutIcon] = formatStatut(demande.statut);
        
        // Construction du HTML pour les fichiers
        let fichiersHTML = '';
        if (fichiers && fichiers.length > 0) {
            const fichiersParType = {};
            
            // Grouper les fichiers par type
            fichiers.forEach(fichier => {
                if (!fichiersParType[fichier.type_fichier]) {
                    fichiersParType[fichier.type_fichier] = [];
                }
                fichiersParType[fichier.type_fichier].push(fichier);
            });
            
            fichiersHTML = `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0"><i class="fas fa-paperclip me-2"></i>Fichiers Joints (${fichiers.length})</h6>
                            </div>
                            <div class="card-body">
            `;
            
            // Afficher les fichiers groupés par type
            Object.keys(fichiersParType).forEach(typeFichier => {
                const fichiersDuType = fichiersParType[typeFichier];
                
                fichiersHTML += `
                    <div class="mb-3">
                        <h6 class="text-primary">${formatTypeFichier(typeFichier)}</h6>
                        <div class="row">
                `;
                
                fichiersDuType.forEach(fichier => {
                    const icone = getIconeFichier(fichier.chemin_fichier);
                    const dateUpload = new Date(fichier.date_upload).toLocaleString('fr-FR');
                    
                    fichiersHTML += `
                        <div class="col-md-6 mb-2">
                            <div class="d-flex align-items-center p-2 border rounded">
                                <i class="fas fa-${icone} text-danger me-3 fs-5"></i>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small text-truncate">${fichier.chemin_fichier}</div>
                                    <div class="text-muted xsmall">Uploadé: ${dateUpload}</div>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="telechargerFichier(${fichier.id})" title="Télécharger">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                
                fichiersHTML += `
                        </div>
                    </div>
                `;
            });
            
            fichiersHTML += `
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            fichiersHTML = `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body text-center text-muted">
                                <i class="fas fa-folder-open fa-3x mb-3"></i>
                                <h5>Aucun fichier joint</h5>
                                <p>Aucun document n'a été uploadé avec cette demande.</p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        const detailsHTML = `
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-user me-2"></i>Informations Personnelles</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%">Nom complet:</td>
                                    <td>${demande.prenom} ${demande.nom}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Date de naissance:</td>
                                    <td>${new Date(demande.date_naissance).toLocaleDateString('fr-FR')}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Lieu de naissance:</td>
                                    <td>${demande.lieu_naissance}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Nationalité:</td>
                                    <td>${demande.nationalite}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Adresse:</td>
                                    <td>${demande.adresse.replace(/\n/g, '<br>')}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-address-card me-2"></i>Coordonnées</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%">Email:</td>
                                    <td>${demande.email}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Téléphone:</td>
                                    <td>${demande.telephone}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Test italien:</td>
                                    <td>${demande.tests_italien === 'non' ? 'Non' : demande.tests_italien}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Test anglais:</td>
                                    <td>${demande.tests_anglais === 'non' ? 'Non' : demande.tests_anglais}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Informations Académiques</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 50%">Type de bourse:</td>
                                    <td><span class="badge bg-primary">${formatTypeBourse(demande.type_bourse)}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Niveau d'études:</td>
                                    <td>${formatNiveauEtude(demande.niveau_etudes)}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Domaine d'études:</td>
                                    <td>${demande.domaine_etudes}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Université choisie:</td>
                                    <td>${demande.universite_choisie}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Programme:</td>
                                    <td>${demande.programme}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Durée des études:</td>
                                    <td>${demande.duree_etudes} année(s)</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Moyenne:</td>
                                    <td><span class="badge bg-warning text-dark">${demande.moyenne}/20</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Statut et Métadonnées</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 50%">Statut:</td>
                                    <td>
                                        <span class="badge bg-${statutClass}">
                                            <i class="fas fa-${statutIcon} me-1"></i>${statutLabel}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Date de soumission:</td>
                                    <td>${new Date(demande.date_soumission).toLocaleString('fr-FR')}</td>
                                </tr>
                                ${demande.date_traitement ? `
                                <tr>
                                    <td class="fw-bold">Date de traitement:</td>
                                    <td>${new Date(demande.date_traitement).toLocaleString('fr-FR')}</td>
                                </tr>
                                ` : ''}
                                <tr>
                                    <td class="fw-bold">Consentement:</td>
                                    <td>${demande.consentement ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-danger">Non</span>'}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Newsletter:</td>
                                    <td>${demande.newsletter ? '<span class="badge bg-success">Oui</span>' : '<span class="badge bg-secondary">Non</span>'}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">ID Demande:</td>
                                    <td><strong class="text-primary">#${demande.id}</strong></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">ID Utilisateur:</td>
                                    <td>${demande.user_id || 'Non connecté'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            ${demande.notes_admin ? `
            <div class="row">
                <div class="col-12">
                    <div class="card mb-3">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes Administratives</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">${demande.notes_admin.replace(/\n/g, '<br>')}</p>
                        </div>
                    </div>
                </div>
            </div>
            ` : ''}
            
            ${fichiersHTML}
        `;
        
        document.getElementById('detailsContent').innerHTML = detailsHTML;
    }

    // Fonction pour télécharger un fichier
    function telechargerFichier(fichierId) {
        window.open('download_bourse_file.php?id=' + fichierId, '_blank');
    }

    // Fonction pour supprimer une demande
    function supprimerDemande(id) {
        document.getElementById('delete_id').value = id;
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }

    // Fonction pour changer le statut
    function changerStatut(id, statut) {
        document.getElementById('statut_id').value = id;
        document.querySelector('#form-statut-user select[name="statut"]').value = statut;
        const modal = new bootstrap.Modal(document.getElementById('statutModal'));
        modal.show();
    }

    // Fonction pour ajouter des notes
    function ajouterNotes(id) {
        document.getElementById('notes_id').value = id;
        
        // Récupérer les notes existantes via AJAX
        fetch('get_bourse_notes.php?id=' + id)
            .then(response => response.json())
            .then(data => {
                document.getElementById('notes_content').value = data.notes_admin || '';
                const modal = new bootstrap.Modal(document.getElementById('notesModal'));
                modal.show();
            })
            .catch(error => {
                document.getElementById('notes_content').value = '';
                const modal = new bootstrap.Modal(document.getElementById('notesModal'));
                modal.show();
            });
    }

    // Fonction pour exporter les données
    function exporterDonnees() {
        // Récupérer les paramètres de filtrage actuels
        const params = new URLSearchParams(window.location.search);
        
        // Ouvrir une nouvelle fenêtre/onglet pour l'export
        window.open('export_bourses.php?' + params.toString(), '_blank');
    }

    // Auto-dismiss alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
</script>
</body>
</html>