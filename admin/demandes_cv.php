<?php
session_start();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

$host = "localhost";
$dbname = "babylone_service";
$username = "root";
$password = "";

// Initialiser TOUTES les variables AVANT le try-catch
$success = $error = "";
$demandes = [];
$stats = [
    'total' => 0,
    'en_traitement' => 0,
    'traitee' => 0,
    'annulee' => 0
];
$filtre_statut = $_GET['statut'] ?? '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Gestion du changement de statut
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['changer_statut'])) {
        $demande_id = $_POST['demande_id'];
        $nouveau_statut = $_POST['statut'];
        $notes = $_POST['notes_admin'] ?? '';

        $sql = "UPDATE demandes_creation_cv SET statut = ?, notes_admin = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nouveau_statut, $notes, $demande_id]);

        $success = "Statut de la demande mis à jour avec succès !";
    }

    // Récupération des demandes avec filtres
    $sql = "SELECT * FROM demandes_creation_cv WHERE 1=1";
    $params = [];

    if ($filtre_statut) {
        $sql .= " AND statut = ?";
        $params[] = $filtre_statut;
    }

    $sql .= " ORDER BY date_creation DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcul des statistiques
    $stats['total'] = count($demandes);
    $stats['en_traitement'] = count(array_filter($demandes, function($d) { 
        return $d['statut'] === 'en_traitement'; 
    }));
    $stats['traitee'] = count(array_filter($demandes, function($d) { 
        return $d['statut'] === 'traitee'; 
    }));
    $stats['annulee'] = count(array_filter($demandes, function($d) { 
        return $d['statut'] === 'annulee'; 
    }));

} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
    // Les variables $demandes et $stats conservent leurs valeurs par défaut
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des Demandes de CV - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e4a7b;
            --secondary-color: #2c6aa0;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --pink-color: #e83e8c;
            --border-radius: 10px;
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        
        .dashboard-card {
            transition: var(--transition);
            border: none;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card {
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card.en-traitement { border-left-color: var(--warning-color); }
        .stat-card.traitee { border-left-color: var(--success-color); }
        .stat-card.annulee { border-left-color: var(--danger-color); }
        .stat-card.total { border-left-color: var(--pink-color); }
        
        .demande-card {
            transition: var(--transition);
            border: none;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 4px solid var(--pink-color);
        }
        
        .demande-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .badge-statut {
            font-size: 0.75rem;
            padding: 6px 12px;
            border-radius: 20px;
        }
        
        .btn-pink {
            background-color: var(--pink-color);
            border-color: var(--pink-color);
            color: white;
        }
        
        .btn-pink:hover {
            background-color: #c2176c;
            border-color: #c2176c;
            color: white;
        }
        
        .filter-section {
            background: white;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .detail-label {
            font-weight: 600;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .back-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
        }
        
        .back-btn:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .formation-item, .experience-item {
            background: #f8f9fa;
            border-left: 3px solid var(--pink-color);
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-cogs me-2"></i>Babylone Service - Admin
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['admin_nom'] ?? 'Administrateur'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header avec bouton retour -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-dark">
                    <i class="fas fa-file-alt me-2 text-pink"></i>
                    Demandes de Création de CV
                </h1>
                <p class="text-muted mb-0">Gérez et suivez toutes les demandes de création de CV</p>
            </div>
            <a href="admin_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left me-1"></i>
                Retour au Dashboard
            </a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card total dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Total</h6>
                                <h3 class="fw-bold text-pink"><?php echo $stats['total']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-alt fa-2x text-pink"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card en-traitement dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">En Traitement</h6>
                                <h3 class="fw-bold text-warning"><?php echo $stats['en_traitement']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card traitee dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Traitées</h6>
                                <h3 class="fw-bold text-success"><?php echo $stats['traitee']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card stat-card annulee dashboard-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title text-muted mb-2">Annulées</h6>
                                <h3 class="fw-bold text-danger"><?php echo $stats['annulee']; ?></h3>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-times-circle fa-2x text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filter-section">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="statut" class="form-label">Filtrer par statut</label>
                    <select id="statut" name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_traitement" <?php echo $filtre_statut === 'en_traitement' ? 'selected' : ''; ?>>En traitement</option>
                        <option value="traitee" <?php echo $filtre_statut === 'traitee' ? 'selected' : ''; ?>>Traitée</option>
                        <option value="annulee" <?php echo $filtre_statut === 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-pink w-100">
                        <i class="fas fa-filter me-1"></i>Appliquer les filtres
                    </button>
                    <a href="demandes_cv.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-refresh me-1"></i>Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Liste des demandes -->
        <div class="row">
            <?php if (empty($demandes)): ?>
                <div class="col-12">
                    <div class="card dashboard-card">
                        <div class="card-body text-center py-5">
                            <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">Aucune demande trouvée</h4>
                            <p class="text-muted">
                                <?php echo $stats['total'] === 0 ? 'Aucune demande de CV n\'a été soumise pour le moment.' : 'Aucune demande ne correspond à vos critères de recherche.'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($demandes as $demande): ?>
                    <div class="col-12 mb-3">
                        <div class="card demande-card">
                            <div class="card-body">
                                <div class="row align-items-start">
                                    <div class="col-md-8">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($demande['nom_complet']); ?></h5>
                                                <p class="text-muted mb-2">
                                                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($demande['email']); ?> • 
                                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($demande['telephone']); ?>
                                                </p>
                                            </div>
                                            <span class="badge-statut 
                                                <?php echo $demande['statut'] === 'en_traitement' ? 'bg-warning' : ''; ?>
                                                <?php echo $demande['statut'] === 'traitee' ? 'bg-success' : ''; ?>
                                                <?php echo $demande['statut'] === 'annulee' ? 'bg-danger' : ''; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $demande['statut'])); ?>
                                            </span>
                                        </div>

                                        <!-- Informations Personnelles -->
                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <span class="detail-label">Adresse:</span>
                                                <div><?php echo !empty($demande['adresse']) ? htmlspecialchars($demande['adresse']) : 'Non renseignée'; ?></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="detail-label">Date de naissance:</span>
                                                <div><?php echo !empty($demande['date_naissance']) ? date('d/m/Y', strtotime($demande['date_naissance'])) : 'Non renseignée'; ?></div>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-sm-6">
                                                <span class="detail-label">Nationalité:</span>
                                                <div><?php echo !empty($demande['nationalite']) ? htmlspecialchars($demande['nationalite']) : 'Non renseignée'; ?></div>
                                            </div>
                                            <div class="col-sm-6">
                                                <span class="detail-label">Situation familiale:</span>
                                                <div><?php echo !empty($demande['situation_familiale']) ? htmlspecialchars($demande['situation_familiale']) : 'Non renseignée'; ?></div>
                                            </div>
                                        </div>

                                        <!-- Formations -->
                                        <?php if (!empty($demande['formations'])): 
                                            $formations = json_decode($demande['formations'], true);
                                            if (is_array($formations) && count($formations) > 0): ?>
                                                <div class="mb-3">
                                                    <span class="detail-label">Formations:</span>
                                                    <?php foreach($formations as $formation): ?>
                                                        <div class="formation-item">
                                                            <strong><?php echo htmlspecialchars($formation['diplome'] ?? ''); ?></strong><br>
                                                            <small><?php echo htmlspecialchars($formation['etablissement'] ?? ''); ?></small>
                                                            <?php if (!empty($formation['annee_obtention'])): ?>
                                                                <br><small>Année: <?php echo htmlspecialchars($formation['annee_obtention']); ?></small>
                                                            <?php endif; ?>
                                                            <?php if (!empty($formation['description'])): ?>
                                                                <br><small><?php echo nl2br(htmlspecialchars($formation['description'])); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif;
                                        endif; ?>

                                        <!-- Expériences Professionnelles -->
                                        <?php if (!empty($demande['experiences'])): 
                                            $experiences = json_decode($demande['experiences'], true);
                                            if (is_array($experiences) && count($experiences) > 0): ?>
                                                <div class="mb-3">
                                                    <span class="detail-label">Expériences professionnelles:</span>
                                                    <?php foreach($experiences as $experience): ?>
                                                        <div class="experience-item">
                                                            <strong><?php echo htmlspecialchars($experience['poste'] ?? ''); ?></strong><br>
                                                            <small><?php echo htmlspecialchars($experience['entreprise'] ?? ''); ?></small>
                                                            <?php if (!empty($experience['date_debut']) || !empty($experience['date_fin'])): ?>
                                                                <br><small>
                                                                    <?php if (!empty($experience['date_debut'])): ?>
                                                                        <?php echo date('m/Y', strtotime($experience['date_debut'])); ?>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($experience['date_debut']) && !empty($experience['date_fin'])): ?>
                                                                         - 
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($experience['date_fin'])): ?>
                                                                        <?php echo date('m/Y', strtotime($experience['date_fin'])); ?>
                                                                    <?php endif; ?>
                                                                </small>
                                                            <?php endif; ?>
                                                            <?php if (!empty($experience['description'])): ?>
                                                                <br><small><?php echo nl2br(htmlspecialchars($experience['description'])); ?></small>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif;
                                        endif; ?>

                                        <!-- Compétences -->
                                        <?php if (!empty($demande['competences_techniques'])): ?>
                                            <div class="mb-3">
                                                <span class="detail-label">Compétences techniques:</span>
                                                <div class="mt-1"><?php echo nl2br(htmlspecialchars($demande['competences_techniques'])); ?></div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($demande['competences_linguistiques'])): ?>
                                            <div class="mb-3">
                                                <span class="detail-label">Compétences linguistiques:</span>
                                                <div class="mt-1"><?php echo nl2br(htmlspecialchars($demande['competences_linguistiques'])); ?></div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($demande['competences_interpersonnelles'])): ?>
                                            <div class="mb-3">
                                                <span class="detail-label">Compétences interpersonnelles:</span>
                                                <div class="mt-1"><?php echo nl2br(htmlspecialchars($demande['competences_interpersonnelles'])); ?></div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Centres d'intérêt -->
                                        <?php if (!empty($demande['centres_interet'])): ?>
                                            <div class="mb-3">
                                                <span class="detail-label">Centres d'intérêt:</span>
                                                <div class="mt-1"><?php echo nl2br(htmlspecialchars($demande['centres_interet'])); ?></div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Notes administrateur -->
                                        <?php if (!empty($demande['notes_admin'])): ?>
                                            <div class="mb-3">
                                                <span class="detail-label">Notes administrateur:</span>
                                                <div class="mt-1 p-2 bg-light rounded"><?php echo nl2br(htmlspecialchars($demande['notes_admin'])); ?></div>
                                            </div>
                                        <?php endif; ?>

                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Soumis le <?php echo date('d/m/Y à H:i', strtotime($demande['date_creation'])); ?>
                                        </small>
                                    </div>

                                    <div class="col-md-4 border-start">
                                        <h6 class="mb-3">Actions</h6>
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-outline-primary btn-sm" onclick="ouvrirModalStatut(<?php echo $demande['id']; ?>)">
                                                <i class="fas fa-edit me-1"></i>Changer statut
                                            </button>
                                            
                                            <a href="mailto:<?php echo htmlspecialchars($demande['email']); ?>" class="btn btn-outline-success btn-sm">
                                                <i class="fas fa-envelope me-1"></i>Contacter
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour changer le statut -->
    <div class="modal fade" id="modalStatut" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Changer le statut de la demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="formStatut">
                    <div class="modal-body">
                        <input type="hidden" name="demande_id" id="demande_id">
                        
                        <div class="mb-3">
                            <label for="statut" class="form-label">Nouveau statut</label>
                            <select id="statut" name="statut" class="form-select" required>
                                <option value="en_traitement">En traitement</option>
                                <option value="traitee">Traitée</option>
                                <option value="annulee">Annulée</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes_admin" class="form-label">Notes (optionnel)</label>
                            <textarea id="notes_admin" name="notes_admin" class="form-control" rows="4" placeholder="Ajoutez des notes pour le suivi..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" name="changer_statut" class="btn btn-pink">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function ouvrirModalStatut(demandeId) {
            document.getElementById('demande_id').value = demandeId;
            const modal = new bootstrap.Modal(document.getElementById('modalStatut'));
            modal.show();
        }
    </script>
</body>
</html>