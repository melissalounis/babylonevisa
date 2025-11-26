<?php
// admin_demandes_visa.php
session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Connexion à la base MySQL
$host = "localhost";
$dbname = "babylone_service";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement des actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        $action = $_POST['action'];
        
        try {
            switch ($action) {
                case 'accepter':
                    $stmt = $pdo->prepare("UPDATE demande_visa_court_sejour SET statut = 'accepté', date_traitement = NOW() WHERE id = ?");
                    $stmt->execute([$id]);
                    $message_success = "Demande acceptée avec succès.";
                    break;
                    
                case 'refuser':
                    $stmt = $pdo->prepare("UPDATE demande_visa_court_sejour SET statut = 'refusé', date_traitement = NOW() WHERE id = ?");
                    $stmt->execute([$id]);
                    $message_success = "Demande refusée avec succès.";
                    break;
                    
                case 'supprimer':
                    $stmt = $pdo->prepare("DELETE FROM demande_visa_court_sejour WHERE id = ?");
                    $stmt->execute([$id]);
                    $message_success = "Demande supprimée avec succès.";
                    break;
            }
        } catch (PDOException $e) {
            $message_erreur = "Erreur lors du traitement : " . $e->getMessage();
        }
    }
}

// Récupération des demandes avec filtres
$where_conditions = [];
$params = [];

// Filtres
$statut_filter = $_GET['statut'] ?? '';
$date_debut = $_GET['date_debut'] ?? '';
$date_fin = $_GET['date_fin'] ?? '';
$search = $_GET['search'] ?? '';

if (!empty($statut_filter)) {
    $where_conditions[] = "statut = ?";
    $params[] = $statut_filter;
}

if (!empty($date_debut)) {
    $where_conditions[] = "DATE(date_soumission) >= ?";
    $params[] = $date_debut;
}

if (!empty($date_fin)) {
    $where_conditions[] = "DATE(date_soumission) <= ?";
    $params[] = $date_fin;
}

if (!empty($search)) {
    $where_conditions[] = "(nom_famille LIKE ? OR prenoms LIKE ? OR numero_dossier LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Construction de la requête
$sql = "SELECT * FROM demande_visa_court_sejour";
if (!empty($where_conditions)) {
    $sql .= " WHERE " . implode(" AND ", $where_conditions);
}
$sql .= " ORDER BY date_soumission DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$stmt_stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
        SUM(CASE WHEN statut = 'accepté' THEN 1 ELSE 0 END) as acceptes,
        SUM(CASE WHEN statut = 'refusé' THEN 1 ELSE 0 END) as refusés
    FROM demande_visa_court_sejour
");
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Demandes de Visa Court Séjour</title>
    <style>
        :root {
            --primary: #dc2626;
            --primary-dark: #b91c1c;
            --secondary: #64748b;
            --success: #059669;
            --warning: #d97706;
            --error: #dc2626;
            --info: #0369a1;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --radius: 8px;
            --transition: all 0.2s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .admin-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow-lg);
        }

        .admin-nav {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-title {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .admin-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: var(--radius);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--text);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-success {
            background: var(--success);
            color: white;
        }

        .btn-warning {
            background: var(--warning);
            color: white;
        }

        .btn-error {
            background: var(--error);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8rem;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--surface);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-align: center;
            border-left: 4px solid var(--primary);
        }

        .stat-card.en-attente {
            border-left-color: var(--warning);
        }

        .stat-card.accepte {
            border-left-color: var(--success);
        }

        .stat-card.refuse {
            border-left-color: var(--error);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .filters-card {
            background: var(--surface);
            padding: 24px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
        }

        .filters-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 16px;
            color: var(--primary);
        }

        .filters-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        label {
            font-weight: 500;
            color: var(--text);
            font-size: 0.9rem;
        }

        input, select {
            padding: 10px 12px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 0.9rem;
            font-family: inherit;
            transition: var(--transition);
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
        }

        .table-container {
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: var(--primary);
            color: white;
        }

        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            font-weight: 600;
            font-size: 0.9rem;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .statut-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .statut-en_attente {
            background: #fef3c7;
            color: #92400e;
        }

        .statut-accepté {
            background: #d1fae5;
            color: #065f46;
        }

        .statut-refusé {
            background: #fee2e2;
            color: #991b1b;
        }

        .actions-cell {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .alert {
            padding: 16px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #f0fdf4;
            border-color: var(--success);
            color: #065f46;
        }

        .alert-error {
            background: #fef2f2;
            border-color: var(--error);
            color: #991b1b;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 16px;
            margin-top: 20px;
            padding: 20px;
        }

        .pagination-info {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: var(--surface);
            margin: 5% auto;
            padding: 0;
            border-radius: var(--radius);
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
        }

        .close {
            color: var(--text-light);
            font-size: 1.5rem;
            cursor: pointer;
        }

        .close:hover {
            color: var(--text);
        }

        .modal-body {
            padding: 20px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .detail-section {
            margin-bottom: 24px;
        }

        .detail-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--border);
        }

        .detail-item {
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
        }

        .detail-label {
            font-weight: 500;
            color: var(--text-light);
        }

        .detail-value {
            color: var(--text);
            text-align: right;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 16px;
            color: var(--border);
        }

        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-form {
                grid-template-columns: 1fr;
            }

            .table-container {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }

            .actions-cell {
                flex-direction: column;
            }

            .modal-content {
                width: 95%;
                margin: 10% auto;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <nav class="admin-nav">
            <div class="admin-title">
                <i class="fas fa-passport"></i> Administration - Demandes de Visa
            </div>
            <div class="admin-actions">
                <span>Bienvenue, Admin</span>
                <a href="admin_logout.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </nav>
    </header>

    <div class="container">
        <?php if (isset($message_success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $message_success ?>
            </div>
        <?php endif; ?>

        <?php if (isset($message_erreur)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?= $message_erreur ?>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Total des demandes</div>
            </div>
            <div class="stat-card en-attente">
                <div class="stat-number"><?= $stats['en_attente'] ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card accepte">
                <div class="stat-number"><?= $stats['acceptes'] ?></div>
                <div class="stat-label">Acceptées</div>
            </div>
            <div class="stat-card refuse">
                <div class="stat-number"><?= $stats['refusés'] ?></div>
                <div class="stat-label">Refusées</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters-card">
            <div class="filters-title">
                <i class="fas fa-filter"></i> Filtres de recherche
            </div>
            <form method="GET" class="filters-form">
                <div class="form-group">
                    <label for="search">Recherche</label>
                    <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Nom, prénom, numéro dossier...">
                </div>
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" <?= $statut_filter === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                        <option value="accepté" <?= $statut_filter === 'accepté' ? 'selected' : '' ?>>Accepté</option>
                        <option value="refusé" <?= $statut_filter === 'refusé' ? 'selected' : '' ?>>Refusé</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="date_debut">Date début</label>
                    <input type="date" id="date_debut" name="date_debut" value="<?= $date_debut ?>">
                </div>
                <div class="form-group">
                    <label for="date_fin">Date fin</label>
                    <input type="date" id="date_fin" name="date_fin" value="<?= $date_fin ?>">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Appliquer
                    </button>
                    <a href="admin_demandes_visa.php" class="btn btn-outline">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Tableau des demandes -->
        <div class="table-container">
            <?php if (empty($demandes)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>Aucune demande trouvée</h3>
                    <p>Aucune demande ne correspond à vos critères de recherche.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Numéro Dossier</th>
                            <th>Nom & Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Date Soumission</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demandes as $demande): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($demande['numero_dossier']) ?></strong>
                                </td>
                                <td>
                                    <div><strong><?= htmlspecialchars($demande['nom_famille']) ?></strong></div>
                                    <div class="text-muted"><?= htmlspecialchars($demande['prenoms']) ?></div>
                                </td>
                                <td><?= htmlspecialchars($demande['email']) ?></td>
                                <td><?= htmlspecialchars($demande['telephone']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($demande['date_soumission'])) ?></td>
                                <td>
                                    <span class="statut-badge statut-<?= $demande['statut'] ?>">
                                        <?= $demande['statut'] ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <button class="btn btn-primary btn-sm" onclick="afficherDetails(<?= htmlspecialchars(json_encode($demande)) ?>)">
                                        <i class="fas fa-eye"></i> Voir
                                    </button>
                                    <?php if ($demande['statut'] === 'en_attente'): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $demande['id'] ?>">
                                            <input type="hidden" name="action" value="accepter">
                                            <button type="submit" class="btn btn-success btn-sm" 
                                                    onclick="return confirm('Êtes-vous sûr de vouloir accepter cette demande ?')">
                                                <i class="fas fa-check"></i> Accepter
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $demande['id'] ?>">
                                            <input type="hidden" name="action" value="refuser">
                                            <button type="submit" class="btn btn-error btn-sm"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir refuser cette demande ?')">
                                                <i class="fas fa-times"></i> Refuser
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="id" value="<?= $demande['id'] ?>">
                                        <input type="hidden" name="action" value="supprimer">
                                        <button type="submit" class="btn btn-outline btn-sm"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="pagination">
                    <div class="pagination-info">
                        <?= count($demandes) ?> demande(s) trouvée(s)
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour afficher les détails -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Détails de la demande</h3>
                <span class="close" onclick="fermerModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Les détails seront chargés ici -->
            </div>
        </div>
    </div>

    <script>
        function afficherDetails(demande) {
            const modal = document.getElementById('detailsModal');
            const modalBody = document.getElementById('modalBody');
            
            // Construction du contenu des détails
            let content = `
                <div class="detail-grid">
                    <div class="detail-section">
                        <h4 class="detail-section-title">
                            <i class="fas fa-user"></i> Informations Personnelles
                        </h4>
                        <div class="detail-item">
                            <span class="detail-label">Nom complet:</span>
                            <span class="detail-value">${demande.nom_famille} ${demande.prenoms}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date de naissance:</span>
                            <span class="detail-value">${new Date(demande.date_naissance).toLocaleDateString('fr-FR')}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Lieu de naissance:</span>
                            <span class="detail-value">${demande.lieu_naissance}, ${demande.pays_naissance}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Nationalité:</span>
                            <span class="detail-value">${demande.nationalite}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Sexe:</span>
                            <span class="detail-value">${demande.sexe === 'M' ? 'Masculin' : 'Féminin'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Statut matrimonial:</span>
                            <span class="detail-value">${demande.statut_matrimonial}</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4 class="detail-section-title">
                            <i class="fas fa-address-card"></i> Coordonnées
                        </h4>
                        <div class="detail-item">
                            <span class="detail-label">Adresse:</span>
                            <span class="detail-value">${demande.adresse}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Ville:</span>
                            <span class="detail-value">${demande.ville}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Code postal:</span>
                            <span class="detail-value">${demande.code_postal || 'Non renseigné'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Pays de résidence:</span>
                            <span class="detail-value">${demande.pays_residence}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Téléphone:</span>
                            <span class="detail-value">${demande.telephone}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">${demande.email}</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4 class="detail-section-title">
                            <i class="fas fa-passport"></i> Passeport
                        </h4>
                        <div class="detail-item">
                            <span class="detail-label">Numéro:</span>
                            <span class="detail-value">${demande.passeport_numero}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Pays de délivrance:</span>
                            <span class="detail-value">${demande.passeport_pays}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date d'émission:</span>
                            <span class="detail-value">${new Date(demande.passeport_date_emission).toLocaleDateString('fr-FR')}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date d'expiration:</span>
                            <span class="detail-value">${new Date(demande.passeport_date_expiration).toLocaleDateString('fr-FR')}</span>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h4 class="detail-section-title">
                            <i class="fas fa-plane"></i> Séjour au Canada
                        </h4>
                        <div class="detail-item">
                            <span class="detail-label">But de la visite:</span>
                            <span class="detail-value">${demande.but_visite}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date d'arrivée:</span>
                            <span class="detail-value">${new Date(demande.date_arrivee).toLocaleDateString('fr-FR')}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date de départ:</span>
                            <span class="detail-value">${new Date(demande.date_depart).toLocaleDateString('fr-FR')}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Durée du séjour:</span>
                            <span class="detail-value">${demande.duree_sejour}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Type d'hébergement:</span>
                            <span class="detail-value">${demande.hebergement_type}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h4 class="detail-section-title">
                        <i class="fas fa-briefcase"></i> Situation Professionnelle
                    </h4>
                    <div class="detail-item">
                        <span class="detail-label">Situation:</span>
                        <span class="detail-value">${demande.situation_professionnelle}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Profession:</span>
                        <span class="detail-value">${demande.profession || 'Non renseigné'}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Employeur:</span>
                        <span class="detail-value">${demande.employeur_nom || 'Non renseigné'}</span>
                    </div>
                </div>
            `;

            modalBody.innerHTML = content;
            modal.style.display = 'block';
        }

        function fermerModal() {
            const modal = document.getElementById('detailsModal');
            modal.style.display = 'none';
        }

        // Fermer la modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                fermerModal();
            }
        }

        // Empêcher la soumission des formulaires si des champs sont vides
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
                    }
                });
            });
        });
    </script>
</body>
</html>