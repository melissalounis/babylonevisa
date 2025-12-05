<?php
// provinceadmin.php
session_start();

// Vérifier si l'utilisateur est admin (compatible avec le dashboard)
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Configuration de la base de données (identique au dashboard)
require_once '../config.php';

// Initialisation des variables
$erreurs = [];
$pdo = null;
$demandes = [];
$statistiques = [];



// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $action = $_POST['action'] ?? '';
    $demande_id = $_POST['demande_id'] ?? '';
    
    if ($action && $demande_id) {
        try {
            switch ($action) {
                case 'accepter':
                    $stmt = $pdo->prepare("UPDATE attestation_province SET statut = 'accepté', date_traitement = NOW() WHERE id = ?");
                    $stmt->execute([$demande_id]);
                    $_SESSION['success_message'] = "Demande #$demande_id acceptée avec succès";
                    break;
                    
                case 'refuser':
                    $raison = $_POST['raison'] ?? 'Raison non spécifiée';
                    $stmt = $pdo->prepare("UPDATE attestation_province SET statut = 'refusé', raison_refus = ?, date_traitement = NOW() WHERE id = ?");
                    $stmt->execute([$raison, $demande_id]);
                    $_SESSION['success_message'] = "Demande #$demande_id refusée avec succès";
                    break;
                    
                case 'supprimer':
                    $stmt = $pdo->prepare("DELETE FROM attestation_province WHERE id = ?");
                    $stmt->execute([$demande_id]);
                    $_SESSION['success_message'] = "Demande #$demande_id supprimée avec succès";
                    break;
            }
            
            header('Location: provinceadmin.php');
            exit();
            
        } catch (PDOException $e) {
            $erreurs[] = "Erreur lors du traitement : " . $e->getMessage();
        }
    }
}

// Récupérer les demandes
if ($pdo) {
    try {
        // Récupérer toutes les demandes avec pagination
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Filtrer par statut
        $statut = $_GET['statut'] ?? '';
        $where = '';
        $params = [];
        
        if ($statut && in_array($statut, ['nouveau', 'en_cours', 'accepté', 'refusé'])) {
            $where = "WHERE statut = ?";
            $params[] = $statut;
        }
        
        // Compter le total
        $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM attestation_province $where");
        $count_stmt->execute($params);
        $total_demandes = $count_stmt->fetch()['total'];
        $total_pages = ceil($total_demandes / $limit);
        
        // Récupérer les demandes
        $sql = "SELECT * FROM attestation_province $where ORDER BY date_soumission DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $demandes = $stmt->fetchAll();
        
        // Récupérer les statistiques
        $stats_stmt = $pdo->query("
            SELECT 
                statut,
                COUNT(*) as count,
                COUNT(*) * 100.0 / (SELECT COUNT(*) FROM attestation_province) as percentage
            FROM attestation_province 
            GROUP BY statut
            ORDER BY count DESC
        ");
        $statistiques = $stats_stmt->fetchAll();
        
    } catch (PDOException $e) {
        $erreurs[] = "Erreur lors de la récupération des données : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Attestations de Province - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1e4a7b;
            --secondary: #2c6aa0;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, #153a5e 100%);
            color: white;
            width: 250px;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            min-height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: 8px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-brand {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 10px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
        }
        
        .stat-card.success {
            border-left-color: var(--success);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning);
        }
        
        .stat-card.danger {
            border-left-color: var(--danger);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .demande-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
        }
        
        .demande-card.nouveau {
            border-left-color: var(--info);
        }
        
        .demande-card.en_cours {
            border-left-color: var(--warning);
        }
        
        .demande-card.accepté {
            border-left-color: var(--success);
        }
        
        .demande-card.refusé {
            border-left-color: var(--danger);
        }
        
        .badge-statut {
            font-size: 0.8rem;
            padding: 6px 12px;
            border-radius: 20px;
        }
        
        .btn-action {
            margin: 2px;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .document-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .document-item {
            padding: 8px;
            margin: 5px 0;
            background: var(--light);
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            border-radius: 10px;
            padding: 10px 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Navigation (identique au dashboard) -->
        <nav class="sidebar">
            <div class="sidebar-brand">
                <h3><i class="fas fa-cogs me-2"></i>Babylone Service</h3>
                <small class="text-white-50">Espace Administrateur</small>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="demandes_sejour.php">
                        <i class="fas fa-passport me-2"></i>
                        Demandes de séjour
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="demandes_etude.php">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Demandes d'étude
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="visa_travail.php">
                        <i class="fas fa-briefcase me-2"></i>
                        Visa de travail
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contrat_travail.php">
                        <i class="fas fa-file-contract me-2"></i>
                        Contrats de travail
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="demandes_cv.php">
                        <i class="fas fa-file-alt me-2"></i>
                        Création de CV
                    </a>
                </li>
                <!-- Nouvelles sections ajoutées -->
                <li class="nav-item">
                    <a class="nav-link active" href="provinceadmin.php">
                        <i class="fas fa-file-certificate me-2"></i>
                        Attestations Province
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="demandes_caq.php">
                        <i class="fas fa-graduation-cap me-2"></i>
                        CAQ Québec
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="visa_touristique.php">
                        <i class="fas fa-plane me-2"></i>
                        Visa Touristique
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="biometrie.php">
                        <i class="fas fa-fingerprint me-2"></i>
                        Biométrie
                    </a>
                </li>
                <!-- Fin nouvelles sections -->
                <li class="nav-item">
                    <a class="nav-link" href="regroupement_familial.php">
                        <i class="fas fa-users me-2"></i>
                        Regroupement familial
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="immigration.php">
                        <i class="fas fa-globe-americas me-2"></i>
                        Immigration
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tests_langue.php">
                        <i class="fas fa-language me-2"></i>
                        Tests de langue
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rendez_vous.php">
                        <i class="fas fa-calendar-check me-2"></i>
                        Rendez-vous
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="messages.php">
                        <i class="fas fa-envelope me-2"></i>
                        Messages
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="parametres.php">
                        <i class="fas fa-cog me-2"></i>
                        Paramètres
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Déconnexion
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation Bar (identique au dashboard) -->
            <nav class="navbar navbar-expand-lg navbar-custom">
                <div class="container-fluid">
                    <button class="btn btn-primary d-md-none" type="button" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-none d-md-block">
                        <span class="navbar-text">
                            <i class="fas fa-calendar-day me-1"></i>
                            <?php echo date('d/m/Y'); ?>
                        </span>
                    </div>
                    
                    <div class="navbar-nav ms-auto">
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2 fa-lg"></i>
                                <div class="d-none d-sm-block">
                                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin_nom'] ?? 'Administrateur'); ?></div>
                                    <small class="text-muted">Administrateur</small>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                                <li><a class="dropdown-item" href="parametres.php"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-dark">
                        <i class="fas fa-file-contract me-2"></i>Gestion des Attestations de Province
                    </h1>
                    <p class="text-muted mb-0">Gérez les demandes d'attestation de province</p>
                </div>
            </div>

            <?php if (!empty($erreurs)): ?>
                <?php foreach ($erreurs as $erreur): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($erreur); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-number text-primary"><?php echo $total_demandes ?? 0; ?></div>
                        <div class="stat-label">Total Demandes</div>
                    </div>
                </div>
                <?php foreach ($statistiques as $stat): ?>
                <div class="col-md-3">
                    <div class="stat-card <?php echo $stat['statut']; ?>">
                        <div class="stat-number 
                            <?php echo $stat['statut'] == 'accepté' ? 'text-success' : ''; ?>
                            <?php echo $stat['statut'] == 'refusé' ? 'text-danger' : ''; ?>
                            <?php echo $stat['statut'] == 'en_cours' ? 'text-warning' : ''; ?>
                            <?php echo $stat['statut'] == 'nouveau' ? 'text-info' : ''; ?>">
                            <?php echo $stat['count']; ?>
                        </div>
                        <div class="stat-label">
                            <?php echo ucfirst($stat['statut']); ?> 
                            <small class="text-muted">(<?php echo number_format($stat['percentage'], 1); ?>%)</small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Filtres -->
            <div class="filters">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select" onchange="this.form.submit()">
                            <option value="">Tous les statuts</option>
                            <option value="nouveau" <?php echo ($statut ?? '') == 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                            <option value="en_cours" <?php echo ($statut ?? '') == 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="accepté" <?php echo ($statut ?? '') == 'accepté' ? 'selected' : ''; ?>>Accepté</option>
                            <option value="refusé" <?php echo ($statut ?? '') == 'refusé' ? 'selected' : ''; ?>>Refusé</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Province</label>
                        <select name="province" class="form-select" onchange="this.form.submit()">
                            <option value="">Toutes les provinces</option>
                            <option value="Ontario">Ontario</option>
                            <option value="Colombie-Britannique">Colombie-Britannique</option>
                            <option value="Alberta">Alberta</option>
                            <option value="Manitoba">Manitoba</option>
                        </select>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                        <a href="provinceadmin.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            <!-- Liste des demandes -->
            <div class="row">
                <?php foreach ($demandes as $demande): ?>
                <div class="col-12">
                    <div class="demande-card <?php echo $demande['statut']; ?>">
                        <div class="row">
                            <div class="col-md-8">
                                <h5>
                                    #AP<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?> - 
                                    <?php echo htmlspecialchars($demande['nom_complet']); ?>
                                </h5>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($demande['email']); ?> | 
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($demande['telephone'] ?? 'Non renseigné'); ?> |
                                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($demande['province']); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Adresse:</strong> <?php echo htmlspecialchars($demande['adresse'] ?? 'Non renseignée'); ?>, 
                                    <?php echo htmlspecialchars($demande['ville'] ?? ''); ?> 
                                    <?php echo htmlspecialchars($demande['code_postal'] ?? ''); ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Date de naissance:</strong> <?php echo $demande['date_naissance'] ? date('d/m/Y', strtotime($demande['date_naissance'])) : 'Non renseignée'; ?>
                                </p>
                                <p class="mb-2">
                                    <strong>Soumis le:</strong> <?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?>
                                </p>
                                
                                <?php if ($demande['statut'] == 'refusé' && $demande['raison_refus']): ?>
                                    <div class="alert alert-danger mt-2">
                                        <strong>Raison du refus:</strong> <?php echo htmlspecialchars($demande['raison_refus']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Documents - VERSION CORRIGÉE -->
                                <div class="mt-3">
                                    <h6><i class="fas fa-file"></i> Documents:</h6>
                                    <div class="document-list">
                                        <?php
                                        // Liste sécurisée des documents avec vérification d'existence
                                        $documents = [
                                            'passeport' => $demande['passeport_path'] ?? null,
                                            'preuve_fonds' => $demande['preuve_fonds_path'] ?? null,
                                            'lettre_acceptation' => $demande['lettre_acceptation_path'] ?? null,
                                            'photo_identite' => $demande['photo_identite_path'] ?? null,
                                            'autres_documents' => $demande['autres_documents_path'] ?? null
                                        ];
                                        
                                        $has_documents = false;
                                        
                                        foreach ($documents as $type => $paths):
                                            if (!empty($paths)):
                                                $files = json_decode($paths, true);
                                                if (is_array($files) && !empty($files)):
                                                    $has_documents = true;
                                                    foreach ($files as $file):
                                                        if (!empty($file)):
                                        ?>
                                        <div class="document-item">
                                            <span>
                                                <i class="fas fa-file-pdf text-danger"></i>
                                                <?php echo htmlspecialchars($type); ?>: <?php echo htmlspecialchars($file); ?>
                                            </span>
                                            <a href="uploads/attestation_province/<?php echo htmlspecialchars($file); ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-download"></i> Voir
                                            </a>
                                        </div>
                                        <?php 
                                                        endif;
                                                    endforeach;
                                                endif;
                                            endif;
                                        endforeach;
                                        
                                        if (!$has_documents):
                                        ?>
                                        <div class="text-muted text-center py-2">
                                            <i class="fas fa-info-circle"></i> Aucun document disponible
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 text-end">
                                <div class="mb-3">
                                    <span class="badge badge-statut 
                                        <?php echo $demande['statut'] == 'nouveau' ? 'bg-info' : ''; ?>
                                        <?php echo $demande['statut'] == 'en_cours' ? 'bg-warning' : ''; ?>
                                        <?php echo $demande['statut'] == 'accepté' ? 'bg-success' : ''; ?>
                                        <?php echo $demande['statut'] == 'refusé' ? 'bg-danger' : ''; ?>">
                                        <?php echo ucfirst($demande['statut']); ?>
                                    </span>
                                </div>
                                
                                <div class="btn-group-vertical">
                                    <?php if ($demande['statut'] == 'nouveau' || $demande['statut'] == 'en_cours'): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                            <input type="hidden" name="action" value="accepter">
                                            <button type="submit" class="btn btn-success btn-action">
                                                <i class="fas fa-check"></i> Accepter
                                            </button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-warning btn-action" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#refuserModal"
                                                data-demande-id="<?php echo $demande['id']; ?>">
                                            <i class="fas fa-times"></i> Refuser
                                        </button>
                                    <?php endif; ?>
                                    
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
                                        <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                        <input type="hidden" name="action" value="supprimer">
                                        <button type="submit" class="btn btn-danger btn-action">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($demandes)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> Aucune demande trouvée.
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&statut=<?php echo $statut ?? ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour refus -->
    <div class="modal fade" id="refuserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Refuser la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="demande_id" id="refuserDemandeId">
                        <input type="hidden" name="action" value="refuser">
                        <div class="mb-3">
                            <label for="raison" class="form-label">Raison du refus</label>
                            <textarea class="form-control" id="raison" name="raison" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-danger">Confirmer le refus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Gestion du modal de refus
        var refuserModal = document.getElementById('refuserModal');
        if (refuserModal) {
            refuserModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var demandeId = button.getAttribute('data-demande-id');
                var modal = this;
                modal.querySelector('#refuserDemandeId').value = demandeId;
            });
        }
        
        // Close sidebar when clicking on a link in mobile view
        if (window.innerWidth < 768) {
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    document.querySelector('.sidebar').classList.remove('active');
                });
            });
        }
    </script>
</body>
</html>