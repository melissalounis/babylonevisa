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
                        $stmt = $pdo->prepare("UPDATE langue_tests SET statut = ? WHERE id = ?");
                        $stmt->execute([$statut, $id]);
                        $_SESSION['success_message'] = "Statut du test #$id mis à jour avec succès.";
                    }
                    break;
                    
                case 'supprimer':
                    $stmt = $pdo->prepare("DELETE FROM langue_tests WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['success_message'] = "Test #$id supprimé avec succès.";
                    break;
                    
                case 'ajouter_notes':
                    if (isset($_POST['notes_admin'])) {
                        $notes_admin = $_POST['notes_admin'];
                        $stmt = $pdo->prepare("UPDATE langue_tests SET notes_admin = ? WHERE id = ?");
                        $stmt->execute([$notes_admin, $id]);
                        $_SESSION['success_message'] = "Notes administratives du test #$id enregistrées.";
                    }
                    break;
            }
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Erreur lors de l'opération: " . $e->getMessage();
        }
        
        header('Location: admin_tests_langue.php');
        exit();
    }
}

// Récupérer les paramètres de filtrage
$statut_filter = $_GET['statut'] ?? 'tous';
$type_test_filter = $_GET['type_test'] ?? 'tous';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Construire la requête avec filtres
$where_conditions = [];
$params = [];

if ($statut_filter !== 'tous') {
    $where_conditions[] = "statut = ?";
    $params[] = $statut_filter;
}

if ($type_test_filter !== 'tous') {
    $where_conditions[] = "type_test = ?";
    $params[] = $type_test_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR type_test LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_sql = $where_conditions ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Récupérer le nombre total pour la pagination
$count_sql = "SELECT COUNT(*) FROM langue_tests $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_tests = $stmt->fetchColumn();
$total_pages = ceil($total_tests / $limit);

// Récupérer les tests
$sql = "SELECT * FROM langue_tests $where_sql ORDER BY date_creation DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les statistiques
$stats_sql = "
    SELECT 
        COUNT(*) as total,
        SUM(statut = 'en_attente') as en_attente,
        SUM(statut = 'confirme') as confirme,
        SUM(statut = 'annule') as annule,
        SUM(statut = 'termine') as termine,
        SUM(type_test LIKE '%francais%' OR type_test LIKE '%french%' OR type_test LIKE '%DELF%' OR type_test LIKE '%TCF%' OR type_test LIKE '%TEF%') as francais,
        SUM(type_test LIKE '%anglais%' OR type_test LIKE '%english%' OR type_test LIKE '%IELTS%' OR type_test LIKE '%TOEFL%' OR type_test LIKE '%TOEIC%') as anglais
    FROM langue_tests
";
$stats = $pdo->query($stats_sql)->fetch(PDO::FETCH_ASSOC);

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'warning', 'icon' => 'clock'],
        'confirme' => ['label' => 'Confirmé', 'class' => 'success', 'icon' => 'check-circle'],
        'annule' => ['label' => 'Annulé', 'class' => 'danger', 'icon' => 'times-circle'],
        'termine' => ['label' => 'Terminé', 'class' => 'info', 'icon' => 'flag-checkered']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary', 'icon' => 'question-circle'];
}

// Fonction pour formater le type de test
function formatTypeTest($type_test) {
    $types = [
        'tcf_tp' => 'TCF Tout Public',
        'tcf_dap' => 'TCF DAP',
        'tcf_anf' => 'TCF ANF',
        'tcf_canada' => 'TCF Canada',
        'tcf_quebec' => 'TCF Québec',
        'delf_a1' => 'DELF A1',
        'delf_a2' => 'DELF A2',
        'delf_b1' => 'DELF B1',
        'delf_b2' => 'DELF B2',
        'dalf_c1' => 'DALF C1',
        'dalf_c2' => 'DALF C2',
        'tef_canada' => 'TEF Canada',
        'tef_quebec' => 'TEF Québec',
        'ielts_academic' => 'IELTS Academic',
        'ielts_general' => 'IELTS General',
        'toefl_ibt' => 'TOEFL iBT',
        'toeic' => 'TOEIC',
        'cambridge_b2' => 'Cambridge B2',
        'cambridge_c1' => 'Cambridge C1',
        'celpip_general' => 'CELPIP General',
        'pte_academic' => 'PTE Academic'
    ];
    return $types[$type_test] ?? $type_test;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Tests de Langue - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #003366, #0055aa);
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
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #003366, #0055aa);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
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
                    <i class="fas fa-language"></i><br>
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
                    <a href="admin_tests_langue.php" class="nav-link text-white bg-dark rounded">
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
                        <i class="fas fa-language text-primary me-2"></i>
                        Gestion des Tests de Langue
                    </h1>
                    <p class="text-muted mb-0">Consultez et gérez tous les rendez-vous de tests de langue</p>
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card border-0">
                        <div class="card-body text-center">
                            <div class="user-avatar mx-auto mb-3">
                                <i class="fas fa-language"></i>
                            </div>
                            <h3 class="mb-1"><?php echo $stats['total']; ?></h3>
                            <p class="mb-0 text-muted small">Total Tests</p>
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
                            <h3 class="mb-1 text-success"><?php echo $stats['confirme']; ?></h3>
                            <p class="mb-0 text-muted small">Confirmés</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card stat-card border-primary">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-flag fa-2x"></i>
                            </div>
                            <h3 class="mb-1 text-primary"><?php echo $stats['francais']; ?></h3>
                            <p class="mb-0 text-muted small">Tests Français</p>
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
                            <option value="confirme" <?php echo $statut_filter === 'confirme' ? 'selected' : ''; ?>>Confirmé</option>
                            <option value="annule" <?php echo $statut_filter === 'annule' ? 'selected' : ''; ?>>Annulé</option>
                            <option value="termine" <?php echo $statut_filter === 'termine' ? 'selected' : ''; ?>>Terminé</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filtrer par type</label>
                        <select name="type_test" class="form-select" onchange="this.form.submit()">
                            <option value="tous" <?php echo $type_test_filter === 'tous' ? 'selected' : ''; ?>>Tous les types</option>
                            <option value="tcf_tp" <?php echo $type_test_filter === 'tcf_tp' ? 'selected' : ''; ?>>TCF Tout Public</option>
                            <option value="delf_b1" <?php echo $type_test_filter === 'delf_b1' ? 'selected' : ''; ?>>DELF B1</option>
                            <option value="delf_b2" <?php echo $type_test_filter === 'delf_b2' ? 'selected' : ''; ?>>DELF B2</option>
                            <option value="ielts_academic" <?php echo $type_test_filter === 'ielts_academic' ? 'selected' : ''; ?>>IELTS Academic</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rechercher</label>
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Nom, prénom, email..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tableau des tests -->
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Candidat</th>
                                <th>Contact</th>
                                <th>Type de test</th>
                                <th>Date & Heure</th>
                                <th>Statut</th>
                                <th>Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($tests)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Aucun test trouvé</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($tests as $test): ?>
                                    <?php 
                                    $statut = formatStatut($test['statut']);
                                    $initiales = strtoupper(substr($test['prenom'], 0, 1) . substr($test['nom'], 0, 1));
                                    ?>
                                    <tr>
                                        <td><strong class="text-primary">#<?php echo $test['id']; ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?php echo $initiales; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($test['prenom'] . ' ' . $test['nom']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($test['email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div><i class="fas fa-phone me-1 text-muted"></i> <?php echo htmlspecialchars($test['telephone']); ?></div>
                                            <div><i class="fas fa-map-marker-alt me-1 text-muted"></i> <?php echo htmlspecialchars($test['ville']); ?></div>
                                        </td>
                                        <td><?php echo formatTypeTest($test['type_test']); ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo date('d/m/Y', strtotime($test['date_rendezvous'])); ?></div>
                                            <small class="text-muted"><?php echo date('H:i', strtotime($test['heure_rendezvous'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $statut['class']; ?>">
                                                <i class="fas fa-<?php echo $statut['icon']; ?> me-1"></i>
                                                <?php echo $statut['label']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($test['date_creation'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" title="Voir les détails" onclick="afficherDetails(<?php echo $test['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" title="Confirmer" onclick="changerStatut(<?php echo $test['id']; ?>, 'confirme')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="btn btn-outline-danger" title="Annuler" onclick="changerStatut(<?php echo $test['id']; ?>, 'annule')">
                                                    <i class="fas fa-times"></i>
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
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour afficher les détails -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Détails du rendez-vous</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Les détails seront chargés ici via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function afficherDetails(testId) {
            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            const content = document.getElementById('detailsContent');
            
            // Afficher le spinner de chargement
            content.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <p class="mt-2">Chargement des détails...</p>
                </div>
            `;
            
            modal.show();
            
            // Charger les détails via AJAX avec meilleure gestion d'erreur
            fetch(`get_test_details.php?id=${testId}`)
                .then(response => {
                    // D'abord, vérifier si la requête a réussi
                    if (!response.ok) {
                        throw new Error(`Erreur HTTP: ${response.status}`);
                    }
                    
                    // Ensuite, essayer de parser comme JSON
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Réponse non-JSON:', text);
                            throw new Error('Le serveur a retourné une réponse invalide. Vérifiez que get_test_details.php existe.');
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        content.innerHTML = data.html;
                        
                        // Ajouter un gestionnaire d'événement pour le formulaire de notes
                        const notesForm = document.getElementById('notesForm');
                        if (notesForm) {
                            notesForm.addEventListener('submit', function(e) {
                                e.preventDefault();
                                const formData = new FormData(this);
                                fetch('admin_tests_langue.php', {
                                    method: 'POST',
                                    body: formData
                                }).then(response => {
                                    modal.hide();
                                    location.reload();
                                });
                            });
                        }
                    } else {
                        content.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                ${data.message}
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Erreur de chargement</h6>
                            <p class="mb-2">${error.message}</p>
                            <small class="text-muted">Vérifiez que le fichier get_test_details.php existe dans le même dossier.</small>
                        </div>
                    `;
                });
        }

        // Fonctions pour les boutons dans la modal
        function changerStatutModal(id, nouveauStatut) {
            if (confirm('Êtes-vous sûr de vouloir changer le statut de ce test ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_tests_langue.php';
                
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

        function supprimerTestModal(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer définitivement ce test ? Cette action est irréversible.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_tests_langue.php';
                
                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'supprimer';
                form.appendChild(inputAction);
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = id;
                form.appendChild(inputId);
                
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Fonctions pour les boutons dans le tableau principal
        function changerStatut(id, nouveauStatut) {
            if (confirm('Êtes-vous sûr de vouloir changer le statut de ce test ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_tests_langue.php';
                
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

        function supprimerTest(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer définitivement ce test ? Cette action est irréversible.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin_tests_langue.php';
                
                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'supprimer';
                form.appendChild(inputAction);
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = id;
                form.appendChild(inputId);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>