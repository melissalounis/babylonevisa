<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch(PDOException $e) {
    die("Erreur connexion BDD: " . $e->getMessage());
}

// Vérification admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Traitement des actions
if (isset($_GET['action'])) {
    $id = $_GET['id'] ?? 0;
    
    try {
        switch($_GET['action']) {
            case 'confirmer':
                $stmt = $db->prepare("UPDATE demandes_reservation SET status = 'confirmee', date_traitement = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['message_success'] = "Réservation confirmée avec succès";
                break;
                
            case 'annuler':
                $stmt = $db->prepare("UPDATE demandes_reservation SET status = 'annulee', date_traitement = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['message_success'] = "Réservation annulée avec succès";
                break;
                
            case 'supprimer':
                $stmt = $db->prepare("DELETE FROM demandes_reservation WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['message_success'] = "Réservation supprimée avec succès";
                break;
                
            case 'traiter':
                $stmt = $db->prepare("UPDATE demandes_reservation SET status = 'en_traitement', date_traitement = NOW() WHERE id = ?");
                $stmt->execute([$id]);
                $_SESSION['message_success'] = "Réservation marquée comme en traitement";
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['message_error'] = "Erreur: " . $e->getMessage();
    }
    
    header("Location: reservations_hotel.php");
    exit();
}

// Récupération des réservations avec filtres
$statut_filter = $_GET['statut'] ?? 'tous';
$search = $_GET['search'] ?? '';

$where_conditions = [];
$params = [];

if ($statut_filter !== 'tous') {
    $where_conditions[] = "status = ?";
    $params[] = $statut_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR numero_dossier LIKE ? OR numero_passeport LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// Compter les réservations par statut
$stats = [
    'total' => 0,
    'en_attente' => 0,
    'confirmee' => 0,
    'annulee' => 0,
    'en_traitement' => 0
];

try {
    $stmt = $db->query("SELECT status, COUNT(*) as count FROM demandes_reservation GROUP BY status");
    $results = $stmt->fetchAll();
    
    foreach ($results as $row) {
        $stats[$row['status']] = $row['count'];
        $stats['total'] += $row['count'];
    }
    
} catch(PDOException $e) {
    // Si erreur, initialiser avec des zéros
    $stats = [
        'total' => 0,
        'en_attente' => 0,
        'confirmee' => 0,
        'annulee' => 0,
        'en_traitement' => 0
    ];
}

// Récupération des réservations
try {
    $sql = "SELECT * FROM demandes_reservation $where_sql ORDER BY date_creation DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $reservations = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $_SESSION['message_error'] = "Erreur lors de la récupération des réservations: " . $e->getMessage();
    $reservations = [];
}

// Fonction pour formater les valeurs sécuritairement
function getValue($data, $key, $default = '') {
    return isset($data[$key]) && !empty($data[$key]) ? $data[$key] : $default;
}

// Fonction pour formater la date
function formatDate($date, $default = 'N/A') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return $default;
    }
    return date('d/m/Y', strtotime($date));
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'warning'],
        'en_traitement' => ['label' => 'En traitement', 'class' => 'info'],
        'confirmee' => ['label' => 'Confirmée', 'class' => 'success'],
        'annulee' => ['label' => 'Annulée', 'class' => 'danger']
    ];
    
    return $statuts[$statut] ?? ['label' => ucfirst($statut), 'class' => 'secondary'];
}

// Fonction pour obtenir le nom complet
function getNomComplet($civilite, $nom, $prenom) {
    $civilite = ucfirst($civilite);
    return "$civilite $nom $prenom";
}

// Fonction pour obtenir les initiales
function getInitiales($nom, $prenom) {
    $initiales = substr(strtoupper($nom), 0, 1) . substr(strtoupper($prenom), 0, 1);
    return $initiales ?: 'NR';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Réservations Hôtel - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #00966E, #00664d);
            color: white;
            min-height: 100vh;
        }
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
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
        .badge-statut {
            font-size: 0.75em;
            padding: 6px 12px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="sidebar-header p-4 text-center">
                        <h4 class="mb-2">
                            <i class="fas fa-hotel"></i><br>
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
                            <a href="reservations_hotel.php" class="nav-link text-white bg-dark rounded">
                                <i class="fas fa-hotel me-2"></i>
                                Réservations Hôtel
                                <span class="badge bg-warning float-end"><?php echo $stats['en_attente']; ?></span>
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a href="reservations_billet.php" class="nav-link text-white rounded">
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
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-hotel me-2"></i>
                        Réservations Hôtel
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="reservations_hotel.php" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt me-1"></i> Actualiser
                        </a>
                    </div>
                </div>

                <!-- Messages -->
                <?php if (isset($_SESSION['message_success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['message_success']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message_success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['message_error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $_SESSION['message_error']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['message_error']); ?>
                <?php endif; ?>

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-xl-2 col-md-4 col-6 mb-3">
                        <div class="card stat-card border-0">
                            <div class="card-body text-center">
                                <div class="text-primary mb-2">
                                    <i class="fas fa-hotel fa-2x"></i>
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

                <!-- Filtres -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Filtrer par statut</label>
                                <select name="statut" class="form-select" onchange="this.form.submit()">
                                    <option value="tous" <?php echo $statut_filter === 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
                                    <option value="en_attente" <?php echo $statut_filter === 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="en_traitement" <?php echo $statut_filter === 'en_traitement' ? 'selected' : ''; ?>>En traitement</option>
                                    <option value="confirmee" <?php echo $statut_filter === 'confirmee' ? 'selected' : ''; ?>>Confirmées</option>
                                    <option value="annulee" <?php echo $statut_filter === 'annulee' ? 'selected' : ''; ?>>Annulées</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rechercher</label>
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Nom, prénom, email, numéro dossier..." value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <a href="reservations_hotel.php" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-times me-1"></i> Réinitialiser
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="table-container">
                    <?php if (empty($reservations)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-hotel fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">Aucune réservation trouvée</h5>
                            <p class="text-muted">
                                <?php if ($stats['total'] === 0): ?>
                                    Aucune réservation n'a été enregistrée pour le moment.
                                <?php else: ?>
                                    Aucune réservation ne correspond à vos critères de recherche.
                                <?php endif; ?>
                            </p>
                            <?php if ($statut_filter !== 'tous' || !empty($search)): ?>
                                <a href="reservations_hotel.php" class="btn btn-primary">
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
                                        <th>Passeport</th>
                                        <th>Dates séjour</th>
                                        <th>Personnes</th>
                                        <th>Hébergement</th>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Création</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($reservations as $reservation): ?>
                                    <?php 
                                    $civilite = getValue($reservation, 'civilite', '');
                                    $nom = getValue($reservation, 'nom', '');
                                    $prenom = getValue($reservation, 'prenom', '');
                                    $email = getValue($reservation, 'email', 'Non renseigné');
                                    $telephone = getValue($reservation, 'telephone', 'Non renseigné');
                                    $nationalite = getValue($reservation, 'nationalite', 'Non renseignée');
                                    $numero_passeport = getValue($reservation, 'numero_passeport', 'N/A');
                                    $date_arrivee = getValue($reservation, 'date_arrivee');
                                    $date_depart = getValue($reservation, 'date_depart');
                                    $adultes = getValue($reservation, 'nombre_adultes', 1);
                                    $enfants = getValue($reservation, 'nombre_enfants', 0);
                                    $type_hebergement = getValue($reservation, 'type_hebergement', 'Non spécifié');
                                    $categorie_chambre = getValue($reservation, 'categorie_chambre', 'Non spécifiée');
                                    $prix = getValue($reservation, 'prix_estime', 0);
                                    $numero_dossier = getValue($reservation, 'numero_dossier', 'N/A');
                                    $status = getValue($reservation, 'status', 'en_attente');
                                    $date_creation = getValue($reservation, 'date_creation');
                                    
                                    $nom_complet = getNomComplet($civilite, $nom, $prenom);
                                    $statut_info = formatStatut($status);
                                    $initiales = getInitiales($nom, $prenom);
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
                                                    <div class="fw-bold"><?php echo htmlspecialchars($nom_complet); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($nationalite); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="fas fa-envelope me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($email); ?>
                                            </div>
                                            <div class="mt-1">
                                                <i class="fas fa-phone me-1 text-muted"></i>
                                                <?php echo htmlspecialchars($telephone); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <strong><?php echo htmlspecialchars($numero_passeport); ?></strong>
                                                <?php if (!empty($reservation['date_expiration_passeport'])): ?>
                                                <br>
                                                <span class="text-muted">Exp: <?php echo formatDate($reservation['date_expiration_passeport']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold">
                                                <?php echo formatDate($date_arrivee); ?>
                                            </div>
                                            <div class="text-muted small">
                                                Départ: <?php echo formatDate($date_depart); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-center">
                                                <div class="fw-bold text-dark"><?php echo ($adultes + $enfants); ?></div>
                                                <small class="text-muted">
                                                    <?php echo $adultes; ?>A / <?php echo $enfants; ?>E
                                                </small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div><?php echo htmlspecialchars($type_hebergement); ?></div>
                                                <div class="text-muted"><?php echo htmlspecialchars($categorie_chambre); ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                <?php echo number_format($prix, 2, ',', ' '); ?> €
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-statut bg-<?php echo $statut_info['class']; ?>">
                                                <?php echo $statut_info['label']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo formatDate($date_creation); ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="reservations_hotel.php?action=traiter&id=<?php echo $reservation['id']; ?>" 
                                                   class="btn btn-outline-info" title="Marquer en traitement">
                                                    <i class="fas fa-cog"></i>
                                                </a>
                                                <a href="reservations_hotel.php?action=confirmer&id=<?php echo $reservation['id']; ?>" 
                                                   class="btn btn-outline-success" title="Confirmer">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                                <a href="reservations_hotel.php?action=annuler&id=<?php echo $reservation['id']; ?>" 
                                                   class="btn btn-outline-warning" title="Annuler">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                                <a href="reservations_hotel.php?action=supprimer&id=<?php echo $reservation['id']; ?>" 
                                                   class="btn btn-outline-danger" 
                                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')"
                                                   title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>