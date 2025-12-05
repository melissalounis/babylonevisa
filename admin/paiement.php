<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion des Paiements</title>
    <style>
        :root {
            --primary: #1e3c72;
            --primary-dark: #2a5298;
            --secondary: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
            --gray: #6c757d;
            --border: #e0e0e0;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --radius: 10px;
        }
        
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        }
        
        body { 
            background-color: #f5f7fa; 
            color: #333; 
            line-height: 1.6; 
        }
        
        .container { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 20px; 
        }
        
        header { 
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)); 
            color: white; 
            padding: 25px 0; 
            text-align: center; 
            border-radius: var(--radius); 
            margin-bottom: 30px; 
            box-shadow: var(--shadow);
        }
        
        .admin-stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        
        .stat-card { 
            background: white; 
            padding: 25px; 
            border-radius: var(--radius); 
            text-align: center; 
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number { 
            font-size: 2.5rem; 
            font-weight: bold; 
            margin-bottom: 10px; 
        }
        
        .stat-label { 
            color: var(--gray); 
            font-size: 0.9rem; 
            font-weight: 600;
        }
        
        .stat-en_attente { border-left: 4px solid var(--warning); }
        .stat-paye { border-left: 4px solid var(--info); }
        .stat-confirme { border-left: 4px solid var(--secondary); }
        .stat-annule { border-left: 4px solid var(--danger); }
        
        .filters { 
            background: white; 
            padding: 20px; 
            border-radius: var(--radius); 
            margin-bottom: 30px; 
            box-shadow: var(--shadow); 
        }
        
        .filter-group { 
            display: flex; 
            gap: 15px; 
            align-items: center; 
            flex-wrap: wrap; 
        }
        
        .filter-item { 
            display: flex; 
            flex-direction: column; 
            gap: 5px; 
        }
        
        .filter-item label { 
            font-weight: 600; 
            color: #444; 
            font-size: 0.9rem; 
        }
        
        select, input { 
            padding: 10px 15px; 
            border: 1px solid var(--border); 
            border-radius: 6px; 
            font-size: 1rem; 
            transition: border 0.3s ease;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(30, 60, 114, 0.2);
        }
        
        .btn { 
            padding: 10px 20px; 
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)); 
            color: white; 
            border: none; 
            border-radius: 6px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.3s ease; 
            text-decoration: none; 
            display: inline-block; 
        }
        
        .btn:hover { 
            background: linear-gradient(135deg, var(--primary-dark), #3a6bd1); 
            transform: translateY(-2px); 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-success { background: linear-gradient(135deg, var(--secondary), #20c997); }
        .btn-warning { background: linear-gradient(135deg, var(--warning), #fd7e14); }
        .btn-danger { background: linear-gradient(135deg, var(--danger), #e83e8c); }
        .btn-info { background: linear-gradient(135deg, var(--info), #138496); }
        
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }
        
        .paiements-table { 
            background: white; 
            border-radius: var(--radius); 
            overflow: hidden; 
            box-shadow: var(--shadow); 
            margin-bottom: 30px; 
            overflow-x: auto;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            min-width: 1000px;
        }
        
        th, td { 
            padding: 15px; 
            text-align: left; 
            border-bottom: 1px solid var(--border); 
        }
        
        th { 
            background: var(--light); 
            font-weight: 600; 
            color: var(--primary-dark); 
            position: sticky;
            top: 0;
        }
        
        tr:hover { 
            background: #f8faff; 
        }
        
        .status-badge { 
            padding: 5px 12px; 
            border-radius: 20px; 
            font-size: 0.8rem; 
            font-weight: 600; 
            text-transform: uppercase; 
            display: inline-block;
        }
        
        .status-en_attente { background: #fff3cd; color: #856404; }
        .status-paye { background: #d1edff; color: #004085; }
        .status-confirme { background: #d4edda; color: #155724; }
        .status-annule { background: #f8d7da; color: #721c24; }
        
        .action-buttons { 
            display: flex; 
            gap: 5px; 
            flex-wrap: wrap;
        }
        
        .alert { 
            padding: 15px; 
            border-radius: 6px; 
            margin-bottom: 20px; 
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb; 
        }
        
        .alert-error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb; 
        }
        
        .alert-warning { 
            background: #fff3cd; 
            color: #856404; 
            border: 1px solid #ffeaa7; 
        }
        
        .has-proof { 
            position: relative; 
            display: inline-block;
        }
        
        .proof-indicator { 
            position: absolute; 
            top: -5px; 
            right: -5px; 
            background: var(--secondary); 
            color: white; 
            border-radius: 50%; 
            width: 20px; 
            height: 20px; 
            font-size: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        
        .detail-container { 
            max-width: 800px; 
            margin: 0 auto; 
        }
        
        .detail-card { 
            background: white; 
            padding: 30px; 
            border-radius: var(--radius); 
            box-shadow: var(--shadow); 
            margin-bottom: 30px; 
        }
        
        .detail-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 20px; 
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .detail-title { 
            font-size: 1.5rem; 
            font-weight: 600; 
            color: var(--primary-dark); 
        }
        
        .detail-section { 
            margin-bottom: 25px; 
        }
        
        .detail-section h3 { 
            color: var(--primary); 
            margin-bottom: 15px; 
            padding-bottom: 8px; 
            border-bottom: 1px solid var(--border); 
        }
        
        .detail-row { 
            display: flex; 
            margin-bottom: 12px; 
            flex-wrap: wrap;
        }
        
        .detail-label { 
            font-weight: 600; 
            width: 200px; 
            color: #555; 
        }
        
        .detail-value { 
            flex: 1; 
        }
        
        .proof-image { 
            max-width: 100%; 
            max-height: 400px; 
            border: 1px solid var(--border); 
            border-radius: 6px; 
            margin-top: 10px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .back-link { 
            display: inline-block; 
            margin-bottom: 20px; 
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a, .pagination span {
            padding: 8px 15px;
            border: 1px solid var(--border);
            border-radius: 6px;
            text-decoration: none;
            color: var(--primary);
            font-weight: 600;
        }
        
        .pagination a:hover {
            background: var(--primary);
            color: white;
        }
        
        .pagination .current {
            background: var(--primary);
            color: white;
        }
        
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-item {
                width: 100%;
            }
            
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
            
            .detail-label {
                width: 100%;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .detail-header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        // Fonctions de sécurité
        function sanitize($data) {
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
        }
        
        function validateReference($ref) {
            return preg_match('/^[a-zA-Z0-9_-]+$/', $ref);
        }
        
        // Journalisation
        function logAction($action, $reference, $details = '') {
            $log = date('Y-m-d H:i:s') . " | $action | $reference | $details" . PHP_EOL;
            file_put_contents('admin_logs.txt', $log, FILE_APPEND | LOCK_EX);
        }
        
        // Connexion à la base de données
       require_once '../config.php';
        // Initialiser les variables
        $mode_detail = false;
        $reference_detail = '';
        $paiement_detail = [];
        $paiements = [];
        $stats = ['en_attente' => 0, 'paye' => 0, 'confirme' => 0, 'annule' => 0, 'total' => 0];
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        try {
           
            
            // Vérifier si on est en mode détail
            $mode_detail = isset($_GET['reference']) && !empty($_GET['reference']);
            $reference_detail = $mode_detail ? sanitize($_GET['reference']) : '';
            
            if ($mode_detail && !validateReference($reference_detail)) {
                echo '<div class="alert alert-error">Référence invalide</div>';
                $mode_detail = false;
            }
            
            if ($mode_detail) {
                // Mode détail - Afficher les détails d'un paiement spécifique
                $stmt = $pdo->prepare("SELECT * FROM paiements WHERE reference = ?");
                $stmt->execute([$reference_detail]);
                $paiement_detail = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$paiement_detail) {
                    echo '<div class="alert alert-error">Paiement non trouvé</div>';
                    $mode_detail = false;
                }
            }
        } catch (PDOException $e) {
            error_log("Erreur DB: " . $e->getMessage());
            echo '<div class="alert alert-error">✗ Une erreur est survenue avec la base de données. Veuillez réessayer.</div>';
            $paiements = [];
            $stats = ['en_attente' => 0, 'paye' => 0, 'confirme' => 0, 'annule' => 0, 'total' => 0];
        }
        
        // Si on n'est pas en mode détail, afficher la liste normale
        if (!$mode_detail):
        ?>
        
        <header>
            <h1>Administration des Paiements</h1>
            <p class="subtitle">Gestion des demandes de paiement Algérie Poste</p>
        </header>
        
        <?php
            // Traitement de la confirmation rapide
            if (isset($_GET['confirmer']) && !empty($_GET['confirmer'])) {
                $reference = sanitize($_GET['confirmer']);
                
                if (validateReference($reference)) {
                    $nouveau_statut = 'confirme';
                    
                    try {
                        $stmt = $pdo->prepare("UPDATE paiements SET statut = ? WHERE reference = ?");
                        if ($stmt->execute([$nouveau_statut, $reference])) {
                            echo '<div class="alert alert-success">✓ Paiement confirmé avec succès!</div>';
                            logAction('CONFIRMATION', $reference, "Statut changé vers: $nouveau_statut");
                        } else {
                            echo '<div class="alert alert-error">✗ Erreur lors de la confirmation</div>';
                        }
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-error">✗ Erreur base de données: ' . $e->getMessage() . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-error">✗ Référence invalide</div>';
                }
            }
            
            // Traitement de la suppression
            if (isset($_GET['supprimer']) && !empty($_GET['supprimer'])) {
                $reference = sanitize($_GET['supprimer']);
                
                if (validateReference($reference)) {
                    try {
                        // Récupérer le nom du fichier avant suppression pour le supprimer du serveur
                        $stmt = $pdo->prepare("SELECT fichier_nom FROM paiements WHERE reference = ?");
                        $stmt->execute([$reference]);
                        $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        $stmt = $pdo->prepare("DELETE FROM paiements WHERE reference = ?");
                        if ($stmt->execute([$reference])) {
                            // Supprimer le fichier associé s'il existe
                            if (!empty($paiement['fichier_nom']) && file_exists('uploads/' . $paiement['fichier_nom'])) {
                                unlink('uploads/' . $paiement['fichier_nom']);
                            }
                            
                            echo '<div class="alert alert-success">✓ Paiement supprimé avec succès!</div>';
                            logAction('SUPPRESSION', $reference, "Paiement supprimé de la base");
                        } else {
                            echo '<div class="alert alert-error">✗ Erreur lors de la suppression</div>';
                        }
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-error">✗ Erreur base de données: ' . $e->getMessage() . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-error">✗ Référence invalide</div>';
                }
            }
            
            // Traitement du changement de statut
            if (isset($_POST['changer_statut']) && isset($_POST['reference']) && isset($_POST['nouveau_statut'])) {
                $reference = sanitize($_POST['reference']);
                $nouveau_statut = sanitize($_POST['nouveau_statut']);
                
                if (validateReference($reference)) {
                    try {
                        $stmt = $pdo->prepare("UPDATE paiements SET statut = ? WHERE reference = ?");
                        if ($stmt->execute([$nouveau_statut, $reference])) {
                            echo '<div class="alert alert-success">✓ Statut mis à jour avec succès!</div>';
                            logAction('CHANGEMENT_STATUT', $reference, "Nouveau statut: $nouveau_statut");
                        } else {
                            echo '<div class="alert alert-error">✗ Erreur lors de la mise à jour du statut</div>';
                        }
                    } catch (PDOException $e) {
                        echo '<div class="alert alert-error">✗ Erreur base de données: ' . $e->getMessage() . '</div>';
                    }
                } else {
                    echo '<div class="alert alert-error">✗ Référence invalide</div>';
                }
            }
            
            // Récupérer les statistiques
            try {
                $stats = [
                    'en_attente' => 0,
                    'paye' => 0,
                    'confirme' => 0,
                    'annule' => 0,
                    'total' => 0
                ];
                
                $stmt = $pdo->query("SELECT statut, COUNT(*) as count FROM paiements GROUP BY statut");
                $statuts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($statuts as $stat) {
                    $stats[$stat['statut']] = $stat['count'];
                    $stats['total'] += $stat['count'];
                }
            } catch (PDOException $e) {
                echo '<div class="alert alert-error">✗ Erreur lors du chargement des statistiques</div>';
            }
            
            // Récupérer les paiements avec filtres
            try {
                $whereConditions = [];
                $params = [];
                
                if (isset($_GET['statut']) && !empty($_GET['statut'])) {
                    $whereConditions[] = "statut = ?";
                    $params[] = sanitize($_GET['statut']);
                }
                
                if (isset($_GET['search']) && !empty($_GET['search'])) {
                    $whereConditions[] = "(nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR reference LIKE ?)";
                    $searchTerm = '%' . sanitize($_GET['search']) . '%';
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                }
                
                $whereClause = '';
                if (!empty($whereConditions)) {
                    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
                }
                
                // Compter le total pour la pagination
                $countSql = "SELECT COUNT(*) as total FROM paiements $whereClause";
                $stmt = $pdo->prepare($countSql);
                $stmt->execute($params);
                $totalPaiements = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                $totalPages = ceil($totalPaiements / $limit);
                
                // Récupérer les données paginées
                $sql = "SELECT * FROM paiements $whereClause ORDER BY date_creation DESC LIMIT $limit OFFSET $offset";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                echo '<div class="alert alert-error">✗ Erreur lors du chargement des paiements</div>';
                $paiements = [];
            }
        ?>
        
        <div class="admin-stats">
            <div class="stat-card stat-en_attente">
                <div class="stat-number"><?php echo $stats['en_attente']; ?></div>
                <div class="stat-label">En Attente</div>
            </div>
            <div class="stat-card stat-paye">
                <div class="stat-number"><?php echo $stats['paye']; ?></div>
                <div class="stat-label">Payés</div>
            </div>
            <div class="stat-card stat-confirme">
                <div class="stat-number"><?php echo $stats['confirme']; ?></div>
                <div class="stat-label">Confirmés</div>
            </div>
            <div class="stat-card stat-annule">
                <div class="stat-number"><?php echo $stats['annule']; ?></div>
                <div class="stat-label">Annulés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Demandes</div>
            </div>
        </div>
        
        <div class="filters">
            <form method="GET" action="" id="filterForm">
                <div class="filter-group">
                    <div class="filter-item">
                        <label>Statut</label>
                        <select name="statut">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'en_attente') ? 'selected' : ''; ?>>En Attente</option>
                            <option value="paye" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'paye') ? 'selected' : ''; ?>>Payé</option>
                            <option value="confirme" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'confirme') ? 'selected' : ''; ?>>Confirmé</option>
                            <option value="annule" <?php echo (isset($_GET['statut']) && $_GET['statut'] == 'annule') ? 'selected' : ''; ?>>Annulé</option>
                        </select>
                    </div>
                    <div class="filter-item">
                        <label>Recherche</label>
                        <input type="text" name="search" placeholder="Nom, Prénom, Email, Référence..." value="<?php echo isset($_GET['search']) ? sanitize($_GET['search']) : ''; ?>">
                    </div>
                    <div class="filter-item" style="align-self: flex-end;">
                        <button type="submit" class="btn">Appliquer</button>
                    </div>
                    <div class="filter-item" style="align-self: flex-end;">
                        <a href="?" class="btn btn-info">Actualiser</a>
                    </div>
                    <div class="filter-item" style="align-self: flex-end;">
                        <a href="export.php" class="btn btn-success">Exporter</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="paiements-table">
            <table>
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Montant</th>
                        <th>Service</th>
                        <th>Date Demande</th>
                        <th>Statut</th>
                        <th>Preuve</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($paiements)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 40px;">
                                Aucun paiement trouvé
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($paiements as $paiement): ?>
                        <tr>
                            <td><?php echo sanitize($paiement['reference'] ?? ''); ?></td>
                            <td><?php echo sanitize($paiement['nom'] ?? ''); ?></td>
                            <td><?php echo sanitize($paiement['prenom'] ?? ''); ?></td>
                            <td><?php echo sanitize($paiement['email'] ?? ''); ?></td>
                            <td><?php echo sanitize($paiement['telephone'] ?? ''); ?></td>
                            <td><?php echo number_format($paiement['montant'] ?? 0, 2, ',', ' '); ?> DZD</td>
                            <td><?php echo sanitize($paiement['service'] ?? 'Non spécifié'); ?></td>
                            <td>
                                <?php 
                                if (!empty($paiement['date_creation'])) {
                                    echo date('d/m/Y H:i', strtotime($paiement['date_creation']));
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $paiement['statut'] ?? ''; ?>">
                                    <?php echo str_replace('_', ' ', $paiement['statut'] ?? ''); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($paiement['fichier_nom'])): ?>
                                    <div class="has-proof">
                                        <a href="uploads/<?php echo sanitize($paiement['fichier_nom']); ?>" target="_blank" style="color: #28a745; text-decoration: none;">
                                            ✓
                                        </a>
                                        <div class="proof-indicator" title="Preuve disponible">!</div>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #dc3545;">✗</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?reference=<?php echo $paiement['reference'] ?? ''; ?>" 
                                       class="btn btn-sm" 
                                       title="Voir les détails">
                                        Détails
                                    </a>
                                    
                                    <!-- Formulaire pour changer le statut -->
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="reference" value="<?php echo $paiement['reference'] ?? ''; ?>">
                                        <select name="nouveau_statut" onchange="this.form.submit()" style="padding: 6px; border-radius: 4px; border: 1px solid #ddd;">
                                            <option value="en_attente" <?php echo ($paiement['statut'] ?? '') == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                            <option value="paye" <?php echo ($paiement['statut'] ?? '') == 'paye' ? 'selected' : ''; ?>>Payé</option>
                                            <option value="confirme" <?php echo ($paiement['statut'] ?? '') == 'confirme' ? 'selected' : ''; ?>>Confirmé</option>
                                            <option value="annule" <?php echo ($paiement['statut'] ?? '') == 'annule' ? 'selected' : ''; ?>>Annulé</option>
                                        </select>
                                        <input type="hidden" name="changer_statut" value="1">
                                    </form>
                                    
                                    <!-- Bouton de confirmation rapide -->
                                    <?php if (($paiement['statut'] ?? '') == 'paye'): ?>
                                        <a href="?confirmer=<?php echo $paiement['reference'] ?? ''; ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Confirmer ce paiement ?')"
                                           title="Confirmer le paiement">
                                            ✓
                                        </a>
                                    <?php endif; ?>

                                    <!-- Bouton de suppression -->
                                    <a href="?supprimer=<?php echo $paiement['reference'] ?? ''; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')"
                                       title="Supprimer">
                                        🗑️
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Précédent</a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Suivant</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php else: ?>
        <!-- Mode détail -->
        <a href="?" class="btn back-link">← Retour à la liste</a>
        
        <div class="detail-container">
            <div class="detail-card">
                <div class="detail-header">
                    <h1 class="detail-title">Détails du Paiement #<?php echo sanitize($paiement_detail['reference']); ?></h1>
                    <span class="status-badge status-<?php echo $paiement_detail['statut']; ?>">
                        <?php echo str_replace('_', ' ', $paiement_detail['statut']); ?>
                    </span>
                </div>

                <div class="detail-section">
                    <h3>Informations Personnelles</h3>
                    <div class="detail-row">
                        <div class="detail-label">Nom complet:</div>
                        <div class="detail-value"><?php echo sanitize($paiement_detail['prenom'] . ' ' . $paiement_detail['nom']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email:</div>
                        <div class="detail-value"><?php echo sanitize($paiement_detail['email']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Téléphone:</div>
                        <div class="detail-value"><?php echo sanitize($paiement_detail['telephone']); ?></div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>Détails de la Transaction</h3>
                    <div class="detail-row">
                        <div class="detail-label">Référence:</div>
                        <div class="detail-value"><?php echo sanitize($paiement_detail['reference']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Service:</div>
                        <div class="detail-value"><?php echo sanitize($paiement_detail['service']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Montant:</div>
                        <div class="detail-value"><strong><?php echo number_format($paiement_detail['montant'], 2, ',', ' '); ?> DZD</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Date de demande:</div>
                        <div class="detail-value"><?php echo date('d/m/Y H:i', strtotime($paiement_detail['date_creation'])); ?></div>
                    </div>
                </div>

                <?php if (!empty($paiement_detail['fichier_nom'])): ?>
                <div class="detail-section">
                    <h3>Preuve de Paiement</h3>
                    <div class="detail-row">
                        <div class="detail-label">Fichier:</div>
                        <div class="detail-value">
                            <a href="uploads/<?php echo sanitize($paiement_detail['fichier_nom']); ?>" 
                               target="_blank" 
                               class="btn btn-sm">
                                Voir la preuve
                            </a>
                        </div>
                    </div>
                    <?php
                    $extension = strtolower(pathinfo($paiement_detail['fichier_nom'], PATHINFO_EXTENSION));
                    if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])): 
                    ?>
                    <div class="detail-row">
                        <div class="detail-label">Aperçu:</div>
                        <div class="detail-value">
                            <img src="uploads/<?php echo sanitize($paiement_detail['fichier_nom']); ?>" 
                                 alt="Preuve de paiement" 
                                 class="proof-image">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="detail-section">
                    <h3>Actions</h3>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="reference" value="<?php echo $paiement_detail['reference']; ?>">
                            <select name="nouveau_statut" onchange="this.form.submit()" style="padding: 10px; border-radius: 6px; border: 1px solid #ddd;">
                                <option value="en_attente" <?php echo $paiement_detail['statut'] == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                                <option value="paye" <?php echo $paiement_detail['statut'] == 'paye' ? 'selected' : ''; ?>>Payé</option>
                                <option value="confirme" <?php echo $paiement_detail['statut'] == 'confirme' ? 'selected' : ''; ?>>Confirmé</option>
                                <option value="annule" <?php echo $paiement_detail['statut'] == 'annule' ? 'selected' : ''; ?>>Annulé</option>
                            </select>
                            <input type="hidden" name="changer_statut" value="1">
                        </form>
                        
                        <?php if ($paiement_detail['statut'] == 'paye'): ?>
                            <a href="?confirmer=<?php echo $paiement_detail['reference']; ?>" 
                               class="btn btn-success"
                               onclick="return confirm('Confirmer ce paiement ?')">
                                Confirmer le paiement
                            </a>
                        <?php endif; ?>

                        <a href="?supprimer=<?php echo $paiement_detail['reference']; ?>" 
                           class="btn btn-danger"
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')">
                            Supprimer
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script>
        // Auto-actualisation après action
        <?php if (isset($_GET['confirmer']) || isset($_POST['changer_statut']) || isset($_GET['supprimer'])): ?>
            setTimeout(function() {
                window.location.href = window.location.href.split('?')[0];
            }, 2000);
        <?php endif; ?>
        
        // Gestion du chargement
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function() {
                    this.classList.add('loading');
                });
            });
        });
    </script>
</body>
</html>