<?php
// admin_demandes.php
session_start();

// Vérification de l'authentification admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Connexion à la base de données babylone_service
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement des actions (statut, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['demande_id'])) {
        $demande_id = $_POST['demande_id'];
        
        switch ($_POST['action']) {
            case 'traiter':
                $stmt = $pdo->prepare("UPDATE demandes_equivalences SET statut = 'approuvee', date_traitement = NOW() WHERE id = ?");
                $stmt->execute([$demande_id]);
                break;
                
            case 'en_attente':
                $stmt = $pdo->prepare("UPDATE demandes_equivalences SET statut = 'en_attente', date_traitement = NULL WHERE id = ?");
                $stmt->execute([$demande_id]);
                break;
                
            case 'supprimer':
                $stmt = $pdo->prepare("DELETE FROM demandes_equivalences WHERE id = ?");
                $stmt->execute([$demande_id]);
                break;
        }
        
        header('Location:equivalence.php');
        exit();
    }
}

// Récupération des demandes
$stmt = $pdo->query("SELECT * FROM demandes_equivalences ORDER BY date_demande DESC");
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Demandes d'Équivalence</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --success: #4bb543;
            --warning: #ffc107;
            --danger: #dc3545;
            --border-radius: 12px;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f2f5 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .admin-nav {
            background: var(--light);
            padding: 20px 30px;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: var(--light);
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--gray);
            font-weight: 500;
        }
        
        .content {
            padding: 30px;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray-light);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--primary);
        }
        
        .demandes-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        .demandes-table th {
            background: var(--primary);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        .demandes-table td {
            padding: 15px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .demandes-table tr:hover {
            background-color: #f8f9fa;
        }
        
        .statut {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            text-align: center;
        }
        
        .statut-en_attente {
            background: #fff3cd;
            color: #856404;
        }
        
        .statut-approuvee {
            background: #d1edff;
            color: #004085;
        }
        
        .statut-rejetee {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
        }
        
        .btn-traiter {
            background: var(--success);
            color: white;
        }
        
        .btn-attente {
            background: var(--warning);
            color: var(--dark);
        }
        
        .btn-supprimer {
            background: var(--danger);
            color: white;
        }
        
        .btn-voir {
            background: var(--primary);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 1.5px solid var(--gray-light);
            border-radius: var(--border-radius);
            background: white;
            font-family: 'Inter', sans-serif;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .logout-btn {
            background: var(--danger);
            color: white;
            padding: 10px 20px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .logout-btn:hover {
            background: #c82333;
        }
        
        @media (max-width: 768px) {
            .demandes-table {
                display: block;
                overflow-x: auto;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .filters {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Administration - Demandes d'Équivalence</h1>
            <p>Gestion des demandes d'équivalence de baccalauréat</p>
        </div>
        
        <div class="admin-nav">
            <div>
                <strong>Administrateur :</strong> <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?>
            </div>
            <a href="admin_logout.php" class="logout-btn">Déconnexion</a>
        </div>
        
        <div class="stats">
            <?php
            // Statistiques
            $total = count($demandes);
            $approuvees = array_filter($demandes, fn($d) => $d['statut'] === 'approuvee');
            $en_attente = array_filter($demandes, fn($d) => $d['statut'] === 'en_attente');
            $rejetees = array_filter($demandes, fn($d) => $d['statut'] === 'rejetee');
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label">Total des demandes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($approuvees); ?></div>
                <div class="stat-label">Demandes approuvées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($en_attente); ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($rejetees); ?></div>
                <div class="stat-label">Rejetées</div>
            </div>
        </div>
        
        <div class="content">
            <h2 class="section-title">Liste des demandes</h2>
            
            <div class="filters">
                <select class="filter-select" onchange="filterTable()" id="statutFilter">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente">En attente</option>
                    <option value="approuvee">Approuvée</option>
                    <option value="rejetee">Rejetée</option>
                </select>
                
                <input type="text" class="filter-select" placeholder="Rechercher..." onkeyup="searchTable()" id="searchInput">
            </div>
            
            <?php if (empty($demandes)): ?>
                <div class="no-data">
                    Aucune demande d'équivalence trouvée.
                </div>
            <?php else: ?>
                <table class="demandes-table" id="demandesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom & Prénom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Université d'origine</th>
                            <th>Date demande</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($demandes as $demande): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($demande['id']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($demande['email']); ?></td>
                            <td><?php echo htmlspecialchars($demande['telephone'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($demande['universite_origine']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($demande['date_demande'])); ?></td>
                            <td>
                                <span class="statut statut-<?php echo $demande['statut']; ?>">
                                    <?php 
                                    $statut_labels = [
                                        'en_attente' => 'En attente',
                                        'approuvee' => 'Approuvée', 
                                        'rejetee' => 'Rejetée'
                                    ];
                                    echo $statut_labels[$demande['statut']] ?? $demande['statut'];
                                    ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="demande_id" value="<?php echo $demande['id']; ?>">
                                        <?php if ($demande['statut'] === 'en_attente'): ?>
                                            <button type="submit" name="action" value="traiter" class="btn btn-traiter">Approuver</button>
                                            <button type="submit" name="action" value="supprimer" class="btn btn-supprimer" 
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?')">Supprimer</button>
                                        <?php else: ?>
                                            <button type="submit" name="action" value="en_attente" class="btn btn-attente">Remettre en attente</button>
                                        <?php endif; ?>
                                    </form>
                                    <a href="admin_demande_details.php?id=<?php echo $demande['id']; ?>" class="btn btn-voir">Voir</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterTable() {
            const filter = document.getElementById('statutFilter').value.toLowerCase();
            const table = document.getElementById('demandesTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const statutCell = rows[i].getElementsByTagName('td')[6];
                const statut = statutCell.textContent || statutCell.innerText;
                
                if (filter === '' || statut.toLowerCase().includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
        
        function searchTable() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('demandesTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const text = cell.textContent || cell.innerText;
                        if (text.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                rows[i].style.display = found ? '' : 'none';
            }
        }
    </script>
</body>
</html>