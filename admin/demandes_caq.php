<?php
// admin_demandes_caq.php
session_start();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: admin_login.php');
    exit();
}

// Configuration de la base de données
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Connexion à la base de données
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Variables pour la pagination et la recherche
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = $_GET['search'] ?? '';
$statut = $_GET['statut'] ?? '';
$order_by = $_GET['order_by'] ?? 'date_soumission';
$order_dir = $_GET['order_dir'] ?? 'DESC';

// Construction de la requête avec filtres
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(nom_complet LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($statut)) {
    $where_conditions[] = "statut = :statut";
    $params[':statut'] = $statut;
}

$where_sql = '';
if (!empty($where_conditions)) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Requête pour le nombre total d'enregistrements
$count_sql = "SELECT COUNT(*) as total FROM demandes_caq $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetch()['total'];
$total_pages = ceil($total_records / $limit);

// Requête pour les données
$sql = "SELECT * FROM demandes_caq 
        $where_sql 
        ORDER BY $order_by $order_dir 
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$demandes = $stmt->fetchAll();

// Traitement des actions (changement de statut, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        switch ($_POST['action']) {
            case 'changer_statut':
                $nouveau_statut = $_POST['nouveau_statut'];
                $sql = "UPDATE demandes_caq SET statut = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nouveau_statut, $id]);
                $_SESSION['message'] = "Statut mis à jour avec succès";
                break;
                
            case 'supprimer':
                // Récupérer les chemins des fichiers avant suppression
                $sql = "SELECT passeport_path, preuve_fonds_path, lettre_acceptation_path FROM demandes_caq WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                $fichiers = $stmt->fetch();
                
                // Supprimer les fichiers physiques
                $dossier_upload = "uploads/caq/";
                foreach ($fichiers as $chemin_json) {
                    if ($chemin_json) {
                        $fichiers_array = json_decode($chemin_json, true);
                        if (is_array($fichiers_array)) {
                            foreach ($fichiers_array as $fichier) {
                                $chemin_complet = $dossier_upload . $fichier;
                                if (file_exists($chemin_complet)) {
                                    unlink($chemin_complet);
                                }
                            }
                        }
                    }
                }
                
                // Supprimer de la base de données
                $sql = "DELETE FROM demandes_caq WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$id]);
                $_SESSION['message'] = "Demande supprimée avec succès";
                break;
        }
        
        header("Location: admin_demandes_caq.php");
        exit();
    }
}

// Récupérer les statistiques
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN statut = 'nouveau' THEN 1 ELSE 0 END) as nouveaux,
    SUM(CASE WHEN statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
    SUM(CASE WHEN statut = 'approuve' THEN 1 ELSE 0 END) as approuves,
    SUM(CASE WHEN statut = 'rejete' THEN 1 ELSE 0 END) as rejetes
    FROM demandes_caq";

$stats = $pdo->query($stats_sql)->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Demandes CAQ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #8B0000;
            --secondary: #A52A2A;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background: #f5f5f5;
            min-height: 100vh;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 20px 0;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .nav-links {
            list-style: none;
        }
        
        .nav-links li {
            margin-bottom: 5px;
        }
        
        .nav-links a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-links a:hover, .nav-links a.active {
            background: rgba(255,255,255,0.1);
            border-left: 4px solid white;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            overflow-x: auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
        }
        
        .stat-card.nouveau { border-left-color: var(--info); }
        .stat-card.en_cours { border-left-color: var(--warning); }
        .stat-card.approuve { border-left-color: var(--success); }
        .stat-card.rejete { border-left-color: var(--danger); }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        
        .form-group {
            margin-bottom: 0;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark);
        }
        
        input, select {
            width: 100%;
            padding: 8px 12px;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--secondary);
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        
        .btn-success { background: var(--success); color: white; }
        .btn-warning { background: var(--warning); color: var(--dark); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-info { background: var(--info); color: white; }
        
        .table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .badge-nouveau { background: var(--info); color: white; }
        .badge-en_cours { background: var(--warning); color: var(--dark); }
        .badge-approuve { background: var(--success); color: white; }
        .badge-rejete { background: var(--danger); color: white; }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            text-decoration: none;
            color: var(--dark);
        }
        
        .pagination a:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .pagination .current {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: var(--success);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .file-links {
            max-width: 200px;
        }
        
        .file-link {
            display: block;
            font-size: 0.8rem;
            margin-bottom: 2px;
            color: var(--primary);
            text-decoration: none;
        }
        
        .file-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-cogs"></i> Administration</h2>
                <p>CAQ Québec</p>
            </div>
            <ul class="nav-links">
                <li><a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a></li>
                <li><a href="admin_demandes_caq.php"><i class="fas fa-file-alt"></i> Demandes CAQ</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Utilisateurs</a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i> Statistiques</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Paramètres</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-file-alt"></i> Gestion des demandes CAQ</h1>
                <p>Administration des certificats d'acceptation du Québec</p>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <!-- Statistiques -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total']; ?></div>
                    <div class="stat-label">Total demandes</div>
                </div>
                <div class="stat-card nouveau">
                    <div class="stat-number"><?php echo $stats['nouveaux']; ?></div>
                    <div class="stat-label">Nouvelles</div>
                </div>
                <div class="stat-card en_cours">
                    <div class="stat-number"><?php echo $stats['en_cours']; ?></div>
                    <div class="stat-label">En cours</div>
                </div>
                <div class="stat-card approuve">
                    <div class="stat-number"><?php echo $stats['approuves']; ?></div>
                    <div class="stat-label">Approuvées</div>
                </div>
                <div class="stat-card rejete">
                    <div class="stat-number"><?php echo $stats['rejetes']; ?></div>
                    <div class="stat-label">Rejetées</div>
                </div>
            </div>

            <!-- Filtres -->
            <div class="filters">
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label for="search">Recherche</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nom ou email...">
                    </div>
                    <div class="form-group">
                        <label for="statut">Statut</label>
                        <select id="statut" name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="nouveau" <?php echo $statut === 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                            <option value="en_cours" <?php echo $statut === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                            <option value="approuve" <?php echo $statut === 'approuve' ? 'selected' : ''; ?>>Approuvé</option>
                            <option value="rejete" <?php echo $statut === 'rejete' ? 'selected' : ''; ?>>Rejeté</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="order_by">Trier par</label>
                        <select id="order_by" name="order_by">
                            <option value="date_soumission" <?php echo $order_by === 'date_soumission' ? 'selected' : ''; ?>>Date de soumission</option>
                            <option value="nom_complet" <?php echo $order_by === 'nom_complet' ? 'selected' : ''; ?>>Nom</option>
                            <option value="statut" <?php echo $order_by === 'statut' ? 'selected' : ''; ?>>Statut</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="order_dir">Ordre</label>
                        <select id="order_dir" name="order_dir">
                            <option value="DESC" <?php echo $order_dir === 'DESC' ? 'selected' : ''; ?>>Décroissant</option>
                            <option value="ASC" <?php echo $order_dir === 'ASC' ? 'selected' : ''; ?>>Croissant</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                        <a href="admin_demandes_caq.php" class="btn" style="background: #6c757d; color: white;">
                            <i class="fas fa-redo"></i> Réinitialiser
                        </a>
                    </div>
                </form>
            </div>

            <!-- Tableau des demandes -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Date naissance</th>
                            <th>Pays origine</th>
                            <th>Documents</th>
                            <th>Statut</th>
                            <th>Date soumission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($demandes)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center;">Aucune demande trouvée</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($demandes as $demande): ?>
                                <tr>
                                    <td>CAQ<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($demande['nom_complet']); ?></td>
                                    <td><?php echo htmlspecialchars($demande['email']); ?></td>
                                    <td><?php echo htmlspecialchars($demande['telephone']); ?></td>
                                    <td><?php echo $demande['date_naissance'] ? date('d/m/Y', strtotime($demande['date_naissance'])) : '-'; ?></td>
                                    <td><?php echo htmlspecialchars($demande['pays_origine']); ?></td>
                                    <td class="file-links">
                                        <?php
                                        $documents = [
                                            'passeport' => $demande['passeport_path'],
                                            'preuve_fonds' => $demande['preuve_fonds_path'],
                                            'lettre_acceptation' => $demande['lettre_acceptation_path']
                                        ];
                                        
                                        foreach ($documents as $type => $chemin_json) {
                                            if ($chemin_json) {
                                                $fichiers = json_decode($chemin_json, true);
                                                if (is_array($fichiers)) {
                                                    foreach ($fichiers as $fichier) {
                                                        echo '<a href="uploads/caq/' . $fichier . '" target="_blank" class="file-link">';
                                                        echo '<i class="fas fa-file"></i> ' . $type;
                                                        echo '</a><br>';
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $demande['statut']; ?>">
                                            <?php 
                                            $statuts = [
                                                'nouveau' => 'Nouveau',
                                                'en_cours' => 'En cours',
                                                'approuve' => 'Approuvé',
                                                'rejete' => 'Rejeté'
                                            ];
                                            echo $statuts[$demande['statut']] ?? $demande['statut'];
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($demande['date_soumission'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <!-- Bouton Modifier statut -->
                                            <button class="btn btn-sm btn-warning" onclick="openModal(<?php echo $demande['id']; ?>, '<?php echo $demande['statut']; ?>')">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            
                                            <!-- Bouton Supprimer -->
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="id" value="<?php echo $demande['id']; ?>">
                                                <input type="hidden" name="action" value="supprimer">
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour changer le statut -->
    <div id="modalStatut" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Changer le statut</h3>
            <form id="formStatut" method="POST">
                <input type="hidden" name="id" id="modalDemandeId">
                <input type="hidden" name="action" value="changer_statut">
                
                <div class="form-group">
                    <label for="nouveau_statut">Nouveau statut</label>
                    <select id="nouveau_statut" name="nouveau_statut" required>
                        <option value="nouveau">Nouveau</option>
                        <option value="en_cours">En cours</option>
                        <option value="approuve">Approuvé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>
                
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" class="btn" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id, statutActuel) {
            document.getElementById('modalDemandeId').value = id;
            document.getElementById('nouveau_statut').value = statutActuel;
            document.getElementById('modalStatut').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('modalStatut').style.display = 'none';
        }
        
        // Fermer la modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('modalStatut');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>