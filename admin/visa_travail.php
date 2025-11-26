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

// Initialiser les variables AVANT le try-catch
$demandes = [];
$error = null;
$total = 0;
$en_attente = 0;
$approuve = 0;
$rejete = 0;
$en_cours = 0;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer toutes les demandes
    $sql = "SELECT d.*, u.email as user_email, u.nom as user_nom 
            FROM demandes_visa_travail d 
            LEFT JOIN users u ON d.user_id = u.id 
            ORDER BY d.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculer les statistiques
    $total = count($demandes);
    $en_attente = count(array_filter($demandes, fn($d) => ($d['statut'] ?? '') === 'en_attente'));
    $approuve = count(array_filter($demandes, fn($d) => ($d['statut'] ?? '') === 'approuve'));
    $rejete = count(array_filter($demandes, fn($d) => ($d['statut'] ?? '') === 'rejete'));
    $en_cours = count(array_filter($demandes, fn($d) => ($d['statut'] ?? '') === 'en_cours'));

    // Gérer les actions (statut, suppression)
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (isset($_POST['changer_statut'])) {
            $demande_id = $_POST['demande_id'];
            $nouveau_statut = $_POST['statut'];
            $commentaire = $_POST['commentaire'] ?? '';

            $sql = "UPDATE demandes_visa_travail SET statut = ?, commentaire_admin = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nouveau_statut, $commentaire, $demande_id]);

            header('Location: visa_travail.php?success=Statut mis à jour');
            exit();
        }

        if (isset($_POST['supprimer'])) {
            $demande_id = $_POST['demande_id'];
            
            $sql = "DELETE FROM demandes_visa_travail WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$demande_id]);

            header('Location: visa_travail.php?success=Demande supprimée');
            exit();
        }
    }

} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données : " . $e->getMessage();
    // S'assurer que $demandes reste un tableau vide même en cas d'erreur
    $demandes = [];
} catch (Exception $e) {
    $error = "Erreur : " . $e->getMessage();
    $demandes = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin - Gestion des visas travail</title>
    <style>
        /* Le CSS reste identique */
        :root {
            --primary-color: #1a237e;
            --primary-light: #534bae;
            --primary-dark: #000051;
            --secondary-color: #e8eaf6;
            --accent-color: #ff6d00;
            --text-color: #212121;
            --text-light: #757575;
            --background: #f5f5f5;
            --white: #ffffff;
            --border-color: #ddd;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --error-color: #f44336;
            --info-color: #2196f3;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background);
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        header h1 {
            font-weight: 600;
            font-size: 1.8rem;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .back-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            transition: all 0.3s;
            margin-right: 10px;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-pending { color: var(--warning-color); }
        .stat-approved { color: var(--success-color); }
        .stat-rejected { color: var(--error-color); }
        .stat-total { color: var(--primary-color); }
        
        .filters {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filters select, .filters input {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        
        .demandes-table {
            background: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        
        th {
            background: var(--secondary-color);
            font-weight: 600;
            color: var(--primary-color);
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .statut {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .statut-en-attente { background: #fff3cd; color: #856404; }
        .statut-approuve { background: #d4edda; color: #155724; }
        .statut-rejete { background: #f8d7da; color: #721c24; }
        .statut-en-cours { background: #cce7ff; color: #004085; }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-primary { background: var(--primary-color); color: white; }
        .btn-success { background: var(--success-color); color: white; }
        .btn-warning { background: var(--warning-color); color: white; }
        .btn-danger { background: var(--error-color); color: white; }
        .btn-info { background: var(--info-color); color: white; }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background: var(--white);
            margin: 5% auto;
            padding: 2rem;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .close {
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-group select, .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }
        
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .documents-list {
            margin-top: 1rem;
        }
        
        .document-item {
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .no-data {
            text-align: center;
            padding: 2rem;
            color: var(--text-light);
        }

        .detail-section {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: #f9f9f9;
        }

        .detail-row {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .detail-label {
            font-weight: 600;
            min-width: 200px;
            color: var(--primary-color);
        }

        .detail-value {
            flex: 1;
        }

        .document-link {
            color: var(--primary-color);
            text-decoration: none;
            padding: 5px 10px;
            border: 1px solid var(--primary-color);
            border-radius: 4px;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 5px;
        }

        .document-link:hover {
            background: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            th, td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }
            
            .filters {
                flex-direction: column;
                align-items: stretch;
            }

            .detail-row {
                flex-direction: column;
            }

            .detail-label {
                min-width: auto;
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Administration - Demandes de Visa Travail</h1>
        <div>
            <a href="admin_dashboard.php" class="back-btn">
                ← Retour au Dashboard
            </a>
            <a href="logout.php" class="logout-btn">Déconnexion</a>
        </div>
    </header>

    <div class="container">
        <?php if (isset($_GET['success'])): ?>
            <div class="message success">
                ✅ <?php echo htmlspecialchars($_GET['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="message error">
                ❌ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number stat-total"><?php echo $total; ?></div>
                <div>Total des demandes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-pending"><?php echo $en_attente; ?></div>
                <div>En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-approved"><?php echo $approuve; ?></div>
                <div>Approuvées</div>
            </div>
            <div class="stat-card">
                <div class="stat-number stat-rejected"><?php echo $rejete; ?></div>
                <div>Rejetées</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters">
            <select id="filterStatut">
                <option value="">Tous les statuts</option>
                <option value="en_attente">En attente</option>
                <option value="en_cours">En cours de traitement</option>
                <option value="approuve">Approuvé</option>
                <option value="rejete">Rejeté</option>
            </select>
            
            <select id="filterPays">
                <option value="">Tous les pays</option>
                <option value="France">France</option>
                <option value="Allemagne">Allemagne</option>
                <option value="Belgique">Belgique</option>
                <option value="Suisse">Suisse</option>
                <option value="Canada">Canada</option>
            </select>
            
            <input type="text" id="filterSearch" placeholder="Rechercher...">
        </div>

        <!-- Tableau des demandes -->
        <div class="demandes-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom Complet</th>
                        <th>Pays</th>
                        <th>Employeur</th>
                        <th>Type Contrat</th>
                        <th>Durée</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                        <tr>
                            <td colspan="9" class="no-data">
                                <?php echo isset($error) ? 'Erreur de chargement des données' : 'Aucune demande de visa travail trouvée'; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($demandes as $demande): ?>
                        <tr data-statut="<?php echo $demande['statut'] ?? ''; ?>" data-pays="<?php echo $demande['pays_destination'] ?? ''; ?>">
                            <td><?php echo $demande['id'] ?? ''; ?></td>
                            <td><?php echo htmlspecialchars($demande['nom_complet'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($demande['pays_destination'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($demande['employeur'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($demande['type_contrat'] ?? ''); ?></td>
                            <td><?php echo $demande['duree_sejour'] ?? ''; ?> mois</td>
                            <td>
                                <span class="statut statut-<?php echo str_replace('_', '-', $demande['statut'] ?? ''); ?>">
                                    <?php 
                                    $statuts = [
                                        'en_attente' => 'En attente',
                                        'en_cours' => 'En cours',
                                        'approuve' => 'Approuvé',
                                        'rejete' => 'Rejeté'
                                    ];
                                    echo $statuts[$demande['statut'] ?? ''] ?? ($demande['statut'] ?? 'Inconnu');
                                    ?>
                                </span>
                            </td>
                            <td><?php echo isset($demande['created_at']) ? date('d/m/Y', strtotime($demande['created_at'])) : ''; ?></td>
                            <td>
                                <button class="btn btn-info" onclick="voirDemande(<?php echo $demande['id'] ?? ''; ?>)">
                                    Voir
                                </button>
                                <button class="btn btn-primary" onclick="changerStatut(<?php echo $demande['id'] ?? ''; ?>, '<?php echo $demande['statut'] ?? ''; ?>')">
                                    Statut
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Voir Détails -->
    <div id="modalVoir" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails de la demande</h2>
                <span class="close" onclick="fermerModal('modalVoir')">&times;</span>
            </div>
            <div id="detailsContent">
                <div class="no-data">Chargement des détails...</div>
            </div>
        </div>
    </div>

    <!-- Modal Changer Statut -->
    <div id="modalStatut" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Changer le statut</h2>
                <span class="close" onclick="fermerModal('modalStatut')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="demande_id" id="demandeId">
                <div class="form-group">
                    <label for="statut">Nouveau statut</label>
                    <select name="statut" id="statutSelect" required>
                        <option value="en_attente">En attente</option>
                        <option value="en_cours">En cours de traitement</option>
                        <option value="approuve">Approuvé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="commentaire">Commentaire (optionnel)</label>
                    <textarea name="commentaire" placeholder="Commentaire pour l'utilisateur..."></textarea>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                    <button type="submit" name="changer_statut" class="btn btn-success">Enregistrer</button>
                    <button type="button" class="btn btn-danger" onclick="fermerModal('modalStatut')">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Filtres
        document.getElementById('filterStatut').addEventListener('change', filtrerTableau);
        document.getElementById('filterPays').addEventListener('change', filtrerTableau);
        document.getElementById('filterSearch').addEventListener('input', filtrerTableau);

        function filtrerTableau() {
            const filtreStatut = document.getElementById('filterStatut').value;
            const filtrePays = document.getElementById('filterPays').value;
            const filtreSearch = document.getElementById('filterSearch').value.toLowerCase();
            
            const lignes = document.querySelectorAll('tbody tr');
            
            lignes.forEach(ligne => {
                const statut = ligne.getAttribute('data-statut');
                const pays = ligne.getAttribute('data-pays');
                const texte = ligne.textContent.toLowerCase();
                
                const matchStatut = !filtreStatut || statut === filtreStatut;
                const matchPays = !filtrePays || pays === filtrePays;
                const matchSearch = !filtreSearch || texte.includes(filtreSearch);
                
                ligne.style.display = (matchStatut && matchPays && matchSearch) ? '' : 'none';
            });
        }

        // Modals
        function voirDemande(id) {
            // Charger les détails via AJAX
            fetch('get_demande_details_travail.php?id=' + id)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.text();
                })
                .then(data => {
                    document.getElementById('detailsContent').innerHTML = data;
                })
                .catch(error => {
                    document.getElementById('detailsContent').innerHTML = 
                        '<div class="error">Erreur lors du chargement des détails: ' + error.message + '</div>';
                });
            
            document.getElementById('modalVoir').style.display = 'block';
        }

        function changerStatut(id, statutActuel) {
            document.getElementById('demandeId').value = id;
            document.getElementById('statutSelect').value = statutActuel;
            document.getElementById('modalStatut').style.display = 'block';
        }

        function fermerModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Fermer les modals en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>