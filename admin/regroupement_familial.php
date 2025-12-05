<?php
// Fichier: regroupement_familial.php
// Espace administrateur pour gérer les demandes de regroupement familial

// Connexion à la base de données
require_once '../config.php';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $demande_id = $_POST['demande_id'];
        
        switch ($_POST['action']) {
            case 'valider':
                $stmt = $pdo->prepare("UPDATE demandes_regroupement_familial SET statut = 'validé', date_traitement = NOW() WHERE id = ?");
                $stmt->execute([$demande_id]);
                $message = "Demande validée avec succès";
                break;
                
            case 'rejeter':
                $raison = $_POST['raison_rejet'] ?? '';
                $stmt = $pdo->prepare("UPDATE demandes_regroupement_familial SET statut = 'rejeté', raison_rejet = ?, date_traitement = NOW() WHERE id = ?");
                $stmt->execute([$raison, $demande_id]);
                $message = "Demande rejetée avec succès";
                break;
                
            case 'supprimer':
                $stmt = $pdo->prepare("DELETE FROM demandes_regroupement_familial WHERE id = ?");
                $stmt->execute([$demande_id]);
                $message = "Demande supprimée avec succès";
                break;
        }
    }
}

// Récupération des demandes
$statut_filter = $_GET['statut'] ?? 'tous';
$search_term = $_GET['search'] ?? '';

$sql = "SELECT * FROM demandes_regroupement_familial WHERE 1=1";
$params = [];

if ($statut_filter !== 'tous') {
    $sql .= " AND statut = ?";
    $params[] = $statut_filter;
}

if (!empty($search_term)) {
    $sql .= " AND (nom_complet LIKE ? OR email LIKE ? OR numero_dossier LIKE ?)";
    $search_like = "%$search_term%";
    $params[] = $search_like;
    $params[] = $search_like;
    $params[] = $search_like;
}

$sql .= " ORDER BY date_soumission DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
        SUM(CASE WHEN statut = 'validé' THEN 1 ELSE 0 END) as valide,
        SUM(CASE WHEN statut = 'rejeté' THEN 1 ELSE 0 END) as rejete
    FROM demandes_regroupement_familial
");
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Admin - Regroupement Familial</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0055b8;
            --secondary-blue: #2c3e50;
            --accent-red: #ce1126;
            --light-blue: #e8f2ff;
            --light-gray: #f4f7fa;
            --white: #ffffff;
            --border-color: #dbe4ee;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --success-green: #28a745;
            --warning-orange: #ffc107;
            --danger-red: #dc3545;
            --shadow: 0 4px 12px rgba(0, 85, 184, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-gray);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 20px;
        }
        
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            padding: 25px 30px;
            position: relative;
        }
        
        .admin-header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-nav {
            background: var(--light-blue);
            padding: 15px 30px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 25px 30px;
            background: var(--light-gray);
        }
        
        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-blue);
        }
        
        .stat-card.en_attente { border-left-color: var(--warning-orange); }
        .stat-card.valide { border-left-color: var(--success-green); }
        .stat-card.rejete { border-left-color: var(--danger-red); }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .filters {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }
        
        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }
        
        select, button {
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            background: var(--white);
        }
        
        .btn {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn:hover {
            background: #004a9e;
        }
        
        .btn-success { background: var(--success-green); }
        .btn-success:hover { background: #218838; }
        
        .btn-danger { background: var(--danger-red); }
        .btn-danger:hover { background: #c82333; }
        
        .btn-warning { background: var(--warning-orange); color: var(--text-dark); }
        .btn-warning:hover { background: #e0a800; }
        
        .demandes-container {
            padding: 0 30px 30px;
        }
        
        .demandes-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: var(--white);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .demandes-table th,
        .demandes-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        .demandes-table th {
            background: var(--light-blue);
            font-weight: 600;
            color: var(--primary-blue);
        }
        
        .demandes-table tr:hover {
            background: var(--light-gray);
        }
        
        .statut-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .statut-en_attente {
            background: #fff3cd;
            color: #856404;
        }
        
        .statut-valide {
            background: #d4edda;
            color: #155724;
        }
        
        .statut-rejete {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
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
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: var(--white);
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow);
        }
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .modal-footer {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            resize: vertical;
            min-height: 100px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--border-color);
        }
        
        @media (max-width: 768px) {
            .admin-nav {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .search-box {
                min-width: 100%;
            }
            
            .demandes-table {
                display: block;
                overflow-x: auto;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>
                <i class="fa-solid fa-users-gear"></i>
                Espace Administrateur - Regroupement Familial
            </h1>
            <p>Gestion des demandes de visa regroupement familial</p>
        </header>
        
        <nav class="admin-nav">
            <div class="filters">
                <div class="search-box">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher par nom, email ou numéro de dossier..." 
                           value="<?= htmlspecialchars($search_term) ?>">
                </div>
                
                <select id="statutFilter">
                    <option value="tous" <?= $statut_filter === 'tous' ? 'selected' : '' ?>>Tous les statuts</option>
                    <option value="en_attente" <?= $statut_filter === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="validé" <?= $statut_filter === 'validé' ? 'selected' : '' ?>>Validé</option>
                    <option value="rejeté" <?= $statut_filter === 'rejeté' ? 'selected' : '' ?>>Rejeté</option>
                </select>
                
                <button class="btn" onclick="applyFilters()">
                    <i class="fa-solid fa-filter"></i> Appliquer
                </button>
            </div>
            
            <button class="btn btn-success" onclick="exporterExcel()">
                <i class="fa-solid fa-file-excel"></i> Exporter Excel
            </button>
        </nav>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Total des demandes</div>
            </div>
            <div class="stat-card en_attente">
                <div class="stat-number"><?= $stats['en_attente'] ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card valide">
                <div class="stat-number"><?= $stats['valide'] ?></div>
                <div class="stat-label">Validées</div>
            </div>
            <div class="stat-card rejete">
                <div class="stat-number"><?= $stats['rejete'] ?></div>
                <div class="stat-label">Rejetées</div>
            </div>
        </div>
        
        <div class="demandes-container">
            <?php if (empty($demandes)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    <h3>Aucune demande trouvée</h3>
                    <p>Aucune demande ne correspond à vos critères de recherche.</p>
                </div>
            <?php else: ?>
                <table class="demandes-table">
                    <thead>
                        <tr>
                            <th>Numéro Dossier</th>
                            <th>Nom Complet</th>
                            <th>Email</th>
                            <th>Lien Parenté</th>
                            <th>Date Soumission</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demandes as $demande): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($demande['numero_dossier']) ?></strong></td>
                            <td><?= htmlspecialchars($demande['nom_complet']) ?></td>
                            <td><?= htmlspecialchars($demande['email']) ?></td>
                            <td><?= htmlspecialchars($demande['lien_parente']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($demande['date_soumission'])) ?></td>
                            <td>
                                <span class="statut-badge statut-<?= str_replace('é', 'e', $demande['statut']) ?>">
                                    <?= $demande['statut'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="action-btn btn" onclick="voirDetails(<?= $demande['id'] ?>)">
                                        <i class="fa-solid fa-eye"></i> Voir
                                    </button>
                                    
                                    <?php if ($demande['statut'] === 'en_attente'): ?>
                                        <button class="action-btn btn-success" onclick="validerDemande(<?= $demande['id'] ?>)">
                                            <i class="fa-solid fa-check"></i> Valider
                                        </button>
                                        <button class="action-btn btn-danger" onclick="rejeterDemande(<?= $demande['id'] ?>)">
                                            <i class="fa-solid fa-times"></i> Rejeter
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button class="action-btn btn-warning" onclick="supprimerDemande(<?= $demande['id'] ?>)">
                                        <i class="fa-solid fa-trash"></i> Supprimer
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal pour rejet -->
    <div class="modal" id="rejetModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-times-circle"></i> Rejeter la demande</h3>
            </div>
            <form method="POST" id="rejetForm">
                <input type="hidden" name="demande_id" id="demandeIdRejet">
                <input type="hidden" name="action" value="rejeter">
                
                <div class="form-group">
                    <label for="raison_rejet">Raison du rejet :</label>
                    <textarea id="raison_rejet" name="raison_rejet" required placeholder="Veuillez préciser la raison du rejet..."></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="fermerModal()">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer le rejet</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal pour suppression -->
    <div class="modal" id="suppressionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa-solid fa-trash"></i> Supprimer la demande</h3>
            </div>
            <p>Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.</p>
            <form method="POST" id="suppressionForm">
                <input type="hidden" name="demande_id" id="demandeIdSuppression">
                <input type="hidden" name="action" value="supprimer">
                
                <div class="modal-footer">
                    <button type="button" class="btn" onclick="fermerModal()">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer la suppression</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const statut = document.getElementById('statutFilter').value;
            
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (statut !== 'tous') params.append('statut', statut);
            
            window.location.href = '?' + params.toString();
        }
        
        function validerDemande(id) {
            if (confirm('Êtes-vous sûr de vouloir valider cette demande ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="demande_id" value="${id}">
                    <input type="hidden" name="action" value="valider">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function rejeterDemande(id) {
            document.getElementById('demandeIdRejet').value = id;
            document.getElementById('rejetModal').style.display = 'flex';
        }
        
        function supprimerDemande(id) {
            document.getElementById('demandeIdSuppression').value = id;
            document.getElementById('suppressionModal').style.display = 'flex';
        }
        
        function fermerModal() {
            document.getElementById('rejetModal').style.display = 'none';
            document.getElementById('suppressionModal').style.display = 'none';
        }
        
        function voirDetails(id) {
            window.open(`details_demande.php?id=${id}`, '_blank');
        }
        
        function exporterExcel() {
            // Implémentation basique d'export Excel
            const search = document.getElementById('searchInput').value;
            const statut = document.getElementById('statutFilter').value;
            
            let url = 'export_excel.php';
            const params = [];
            if (search) params.push(`search=${encodeURIComponent(search)}`);
            if (statut !== 'tous') params.push(`statut=${statut}`);
            
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            window.location.href = url;
        }
        
        // Recherche en temps réel
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
        
        // Fermer les modals en cliquant à l'extérieur
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
        
        // Afficher message de succès si présent
        <?php if (isset($message)): ?>
            alert('<?= $message ?>');
        <?php endif; ?>
    </script>
</body>
</html>