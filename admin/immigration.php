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
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Récupérer toutes les demandes de Green Card
try {
    $stmt = $pdo->query("
        SELECT dg.*, 
               COUNT(DISTINCT mf.id) as nb_membres_famille,
               COUNT(DISTINCT dd.id) as nb_documents
        FROM demandes_green_card dg
        LEFT JOIN membres_famille mf ON dg.id = mf.demande_id
        LEFT JOIN documents_demande dd ON dg.id = dd.demande_id
        GROUP BY dg.id
        ORDER BY dg.date_soumission DESC
    ");
    $demandes_green_card = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $demandes_green_card = [];
    $error_message = "Erreur lors de la récupération des demandes Green Card: " . $e->getMessage();
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'warning'],
        'en_cours' => ['label' => 'En cours', 'class' => 'info'],
        'complet' => ['label' => 'Complet', 'class' => 'primary'],
        'approuve' => ['label' => 'Approuvé', 'class' => 'success'],
        'refuse' => ['label' => 'Refusé', 'class' => 'danger']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary'];
}

// Fonction pour formater la situation familiale
function formatSituationFamiliale($situation) {
    $situations = [
        'celibataire' => 'Célibataire',
        'marie' => 'Marié(e)',
        'divorce' => 'Divorcé(e)',
        'veuf' => 'Veuf/Veuve'
    ];
    return $situations[$situation] ?? $situation;
}

// Traitement du changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changer_statut'])) {
    $demande_id = $_POST['demande_id'];
    $nouveau_statut = $_POST['nouveau_statut'];
    
    try {
        $stmt = $pdo->prepare("UPDATE demandes_green_card SET statut = ? WHERE id = ?");
        $stmt->execute([$nouveau_statut, $demande_id]);
        
        $_SESSION['success_message'] = "Statut de la demande #$demande_id mis à jour avec succès.";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } catch(PDOException $e) {
        $error_message = "Erreur lors de la mise à jour du statut: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration des Green Cards - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3C3B6E;
            --secondary: #B22234;
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
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .table-responsive {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .badge-statut {
            font-size: 0.8em;
            padding: 6px 12px;
            border-radius: 20px;
        }
        
        .action-buttons {
            min-width: 150px;
        }
        
        .demande-card {
            border-left: 4px solid var(--primary);
            transition: all 0.3s ease;
        }
        
        .demande-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-header p-3">
                <h4 class="text-center">
                    <i class="fas fa-passport"></i><br>
                    Babylone Service
                </h4>
                <p class="text-center mb-0">Espace Administrateur</p>
            </div>
            
            <ul class="nav flex-column p-3">
                <li class="nav-item mb-2">
                    <a href="admin_dashboard.php" class="nav-link text-white">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Tableau de bord
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_rendez_vous.php" class="nav-link text-white">
                        <i class="fas fa-list me-2"></i>
                        Rendez-vous
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_tests_langue.php" class="nav-link text-white">
                        <i class="fas fa-language me-2"></i>
                        Tests de Langue
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_green_card.php" class="nav-link text-white bg-dark rounded">
                        <i class="fas fa-id-card me-2"></i>
                        Green Cards
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="admin_clients.php" class="nav-link text-white">
                        <i class="fas fa-users me-2"></i>
                        Clients
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a href="logout.php" class="nav-link text-white">
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
                        <i class="fas fa-id-card text-primary me-2"></i>
                        Gestion des Demandes Green Card
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez toutes les demandes de Green Card</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary">
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
                    <?php echo $_SESSION['success_message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0"><?php echo count($demandes_green_card); ?></h4>
                                    <p class="mb-0">Total Demandes</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card bg-warning text-dark">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">
                                        <?php 
                                        $en_attente = array_filter($demandes_green_card, function($demande) {
                                            return $demande['statut'] === 'en_attente';
                                        });
                                        echo count($en_attente);
                                        ?>
                                    </h4>
                                    <p class="mb-0">En attente</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">
                                        <?php 
                                        $en_cours = array_filter($demandes_green_card, function($demande) {
                                            return $demande['statut'] === 'en_cours';
                                        });
                                        echo count($en_cours);
                                        ?>
                                    </h4>
                                    <p class="mb-0">En cours</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-spinner fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">
                                        <?php 
                                        $approuves = array_filter($demandes_green_card, function($demande) {
                                            return $demande['statut'] === 'approuve';
                                        });
                                        echo count($approuves);
                                        ?>
                                    </h4>
                                    <p class="mb-0">Approuvés</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Demandes Green Card Table -->
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Référence</th>
                            <th>Demandeur</th>
                            <th>Contact</th>
                            <th>Situation</th>
                            <th>Famille</th>
                            <th>Documents</th>
                            <th>Date soumission</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($demandes_green_card)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune demande Green Card trouvée</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($demandes_green_card as $demande): ?>
                                <?php $statut = formatStatut($demande['statut']); ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary">#<?php echo $demande['id']; ?></strong>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($demande['reference']); ?></code>
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($demande['prenom'] . ' ' . $demande['nom']); ?></div>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($demande['nationalite']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-envelope me-1 text-muted"></i>
                                            <?php echo htmlspecialchars($demande['email']); ?>
                                        </div>
                                        <div class="mt-1">
                                            <i class="fas fa-phone me-1 text-muted"></i>
                                            <?php echo htmlspecialchars($demande['telephone']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo formatSituationFamiliale($demande['situation_familiale']); ?></div>
                                        <?php if ($demande['situation_familiale'] === 'marie'): ?>
                                            <small class="text-muted"><?php echo $demande['nombre_enfants']; ?> enfant(s)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo $demande['nb_membres_famille']; ?> membre(s)
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $demande['nb_documents']; ?> fichier(s)
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo date('d/m/Y H:i', strtotime($demande['date_soumission'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-statut bg-<?php echo $statut['class']; ?>">
                                            <?php echo $statut['label']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm action-buttons">
                                            <button class="btn btn-outline-primary" title="Voir les détails"
                                                    onclick="afficherDetails(<?php echo htmlspecialchars(json_encode($demande)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-info" title="Documents"
                                                    onclick="afficherDocuments(<?php echo $demande['id']; ?>)">
                                                <i class="fas fa-file"></i>
                                            </button>
                                            <div class="dropdown">
                                                <button class="btn btn-outline-secondary dropdown-toggle" 
                                                        type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                                            <input type="hidden" name="nouveau_statut" value="en_cours">
                                                            <button type="submit" name="changer_statut" class="dropdown-item">
                                                                <i class="fas fa-spinner me-2"></i>En cours
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                                            <input type="hidden" name="nouveau_statut" value="complet">
                                                            <button type="submit" name="changer_statut" class="dropdown-item">
                                                                <i class="fas fa-check-circle me-2"></i>Complet
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                                            <input type="hidden" name="nouveau_statut" value="approuve">
                                                            <button type="submit" name="changer_statut" class="dropdown-item text-success">
                                                                <i class="fas fa-thumbs-up me-2"></i>Approuver
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                                            <input type="hidden" name="nouveau_statut" value="refuse">
                                                            <button type="submit" name="changer_statut" class="dropdown-item text-danger">
                                                                <i class="fas fa-thumbs-down me-2"></i>Refuser
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
        </div>
    </div>

    <!-- Modal pour afficher les détails -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Détails de la demande Green Card</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Les détails seront chargés ici par JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher les documents -->
    <div class="modal fade" id="documentsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Documents de la demande</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="documentsContent">
                    <!-- Les documents seront chargés ici par JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function afficherDetails(demande) {
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            const content = document.getElementById('detailsContent');
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations personnelles</h6>
                        <p><strong>Nom complet:</strong> ${demande.prenom} ${demande.nom}</p>
                        <p><strong>Date de naissance:</strong> ${new Date(demande.date_naissance).toLocaleDateString('fr-FR')}</p>
                        <p><strong>Nationalité:</strong> ${demande.nationalite}</p>
                        <p><strong>Email:</strong> ${demande.email}</p>
                        <p><strong>Téléphone:</strong> ${demande.telephone}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Adresse</h6>
                        <p><strong>Adresse:</strong> ${demande.adresse}</p>
                        <p><strong>Ville:</strong> ${demande.ville}</p>
                        <p><strong>Code postal:</strong> ${demande.code_postal}</p>
                        <p><strong>Pays de résidence:</strong> ${demande.pays_residence}</p>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Situation familiale</h6>
                        <p><strong>Situation:</strong> ${formatSituationFamiliale(demande.situation_familiale)}</p>
                        <p><strong>Nombre d'enfants:</strong> ${demande.nombre_enfants}</p>
                        <p><strong>Profession:</strong> ${demande.profession || 'Non spécifié'}</p>
                        <p><strong>Employeur:</strong> ${demande.employeur || 'Non spécifié'}</p>
                        <p><strong>Revenu annuel:</strong> ${demande.revenu_annuel ? `${parseFloat(demande.revenu_annuel).toLocaleString('fr-FR')} USD` : 'Non spécifié'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Informations techniques</h6>
                        <p><strong>Référence:</strong> <code>${demande.reference}</code></p>
                        <p><strong>ID:</strong> ${demande.id}</p>
                        <p><strong>Date soumission:</strong> ${new Date(demande.date_soumission).toLocaleString('fr-FR')}</p>
                        <p><strong>Statut:</strong> <span class="badge bg-${getStatutClass(demande.statut)}">${getStatutLabel(demande.statut)}</span></p>
                        <p><strong>Membres famille:</strong> ${demande.nb_membres_famille}</p>
                        <p><strong>Documents:</strong> ${demande.nb_documents}</p>
                    </div>
                </div>
            `;
            
            modal.show();
        }
        
        function afficherDocuments(demandeId) {
            const modal = new bootstrap.Modal(document.getElementById('documentsModal'));
            const content = document.getElementById('documentsContent');
            
            // Charger les documents via AJAX
            content.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des documents...</p>
                </div>
            `;
            
            fetch(`get_documents_demande.php?demande_id=${demandeId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<div class="row">';
                        
                        if (data.documents.length > 0) {
                            data.documents.forEach(doc => {
                                html += `
                                    <div class="col-md-6 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title">${doc.type_document}</h6>
                                                <p class="card-text">
                                                    <small class="text-muted">${doc.nom_fichier}</small><br>
                                                    <small class="text-muted">${new Date(doc.date_upload).toLocaleString('fr-FR')}</small>
                                                </p>
                                                <a href="${doc.chemin}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download me-1"></i>Télécharger
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });
                        } else {
                            html = '<p class="text-center text-muted">Aucun document trouvé pour cette demande.</p>';
                        }
                        
                        html += '</div>';
                        content.innerHTML = html;
                    } else {
                        content.innerHTML = `<p class="text-center text-danger">Erreur: ${data.message}</p>`;
                    }
                })
                .catch(error => {
                    content.innerHTML = `<p class="text-center text-danger">Erreur de chargement: ${error}</p>`;
                });
            
            modal.show();
        }
        
        function formatSituationFamiliale(situation) {
            const situations = {
                'celibataire': 'Célibataire',
                'marie': 'Marié(e)',
                'divorce': 'Divorcé(e)',
                'veuf': 'Veuf/Veuve'
            };
            return situations[situation] || situation;
        }
        
        function getStatutClass(statut) {
            const classes = {
                'en_attente': 'warning',
                'en_cours': 'info',
                'complet': 'primary',
                'approuve': 'success',
                'refuse': 'danger'
            };
            return classes[statut] || 'secondary';
        }
        
        function getStatutLabel(statut) {
            const labels = {
                'en_attente': 'En attente',
                'en_cours': 'En cours',
                'complet': 'Complet',
                'approuve': 'Approuvé',
                'refuse': 'Refusé'
            };
            return labels[statut] || statut;
        }
    </script>
</body>
</html>