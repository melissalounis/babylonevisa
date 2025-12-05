<?php
require_once __DIR__ . '../config.php';
$page_title = "Administration - Demandes de Visa Études";
include __DIR__ . '../includes/header.php';

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /admin_login.php');
    exit();
}

// Récupérer toutes les demandes
$sql = "SELECT v.*, u.nom as user_nom, u.prenom as user_prenom 
        FROM visa_etudes v 
        JOIN users u ON v.user_id = u.id 
        ORDER BY v.date_demande DESC";
$stmt = $pdo->query($sql);
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$stats_sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
    SUM(CASE WHEN status = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
    SUM(CASE WHEN status = 'approuve' THEN 1 ELSE 0 END) as approuve,
    SUM(CASE WHEN status = 'rejete' THEN 1 ELSE 0 END) as rejete
    FROM visa_etudes";
$stats = $pdo->query($stats_sql)->fetch(PDO::FETCH_ASSOC);
?>

<div class="admin-container">
    <div class="admin-header">
        <h1><i class="fas fa-passport"></i> Administration des Demandes de Visa Études</h1>
        <p>Gérez toutes les demandes de visa étudiant</p>
    </div>

    <!-- Statistiques -->
    <div class="stats-cards">
        <div class="stat-card total">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['total']; ?></h3>
                <p>Demandes totales</p>
            </div>
        </div>

        <div class="stat-card pending">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['en_attente']; ?></h3>
                <p>En attente</p>
            </div>
        </div>

        <div class="stat-card in-progress">
            <div class="stat-icon">
                <i class="fas fa-spinner"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['en_cours']; ?></h3>
                <p>En cours</p>
            </div>
        </div>

        <div class="stat-card approved">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['approuve']; ?></h3>
                <p>Approuvées</p>
            </div>
        </div>

        <div class="stat-card rejected">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stats['rejete']; ?></h3>
                <p>Rejetées</p>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="filters-section">
        <div class="filters-header">
            <h3><i class="fas fa-filter"></i> Filtres</h3>
            <button class="btn btn-secondary" onclick="resetFilters()">
                <i class="fas fa-redo"></i> Réinitialiser
            </button>
        </div>
        
        <div class="filters-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label for="filter_status">Statut</label>
                    <select id="filter_status" class="form-control" onchange="filterTable()">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente">En attente</option>
                        <option value="en_cours">En cours de traitement</option>
                        <option value="approuve">Approuvé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter_pays">Pays de destination</label>
                    <select id="filter_pays" class="form-control" onchange="filterTable()">
                        <option value="">Tous les pays</option>
                        <option value="france">France</option>
                        <option value="belgique">Belgique</option>
                        <option value="espagne">Espagne</option>
                        <option value="italie">Italie</option>
                        <option value="turquie">Turquie</option>
                        <option value="roumanie">Roumanie</option>
                        <option value="luxembourg">Luxembourg</option>
                        <option value="bulgarie">Bulgarie</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter_date">Période</label>
                    <select id="filter_date" class="form-control" onchange="filterTable()">
                        <option value="">Toute période</option>
                        <option value="today">Aujourd'hui</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                        <option value="year">Cette année</option>
                    </select>
                </div>
            </div>

            <div class="filter-row">
                <div class="filter-group">
                    <label for="search">Recherche</label>
                    <div class="search-box">
                        <input type="text" id="search" class="form-control" placeholder="Nom, prénom, numéro passeport..." onkeyup="filterTable()">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des demandes -->
    <div class="table-section">
        <div class="table-header">
            <h3><i class="fas fa-list"></i> Liste des Demandes</h3>
            <div class="table-actions">
                <button class="btn btn-export" onclick="exportToExcel()">
                    <i class="fas fa-file-excel"></i> Exporter Excel
                </button>
                <button class="btn btn-export" onclick="exportToPDF()">
                    <i class="fas fa-file-pdf"></i> Exporter PDF
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="demandesTable" class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Demandeur</th>
                        <th>Pays</th>
                        <th>Établissement</th>
                        <th>Niveau</th>
                        <th>Début</th>
                        <th>Durée</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demandes as $demande): ?>
                        <?php
                        // Déterminer la classe CSS pour le statut
                        $status_class = '';
                        $status_text = '';
                        switch ($demande['status']) {
                            case 'en_attente':
                                $status_class = 'status-pending';
                                $status_text = 'En attente';
                                break;
                            case 'en_cours':
                                $status_class = 'status-in-progress';
                                $status_text = 'En cours';
                                break;
                            case 'approuve':
                                $status_class = 'status-approved';
                                $status_text = 'Approuvé';
                                break;
                            case 'rejete':
                                $status_class = 'status-rejected';
                                $status_text = 'Rejeté';
                                break;
                        }
                        
                        // Formater la date
                        $date_demande = date('d/m/Y H:i', strtotime($demande['date_demande']));
                        $date_debut = date('d/m/Y', strtotime($demande['date_debut']));
                        ?>
                        <tr data-status="<?php echo $demande['status']; ?>" 
                            data-pays="<?php echo $demande['pays_destination']; ?>"
                            data-date="<?php echo $demande['date_demande']; ?>">
                            <td>#<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo $date_demande; ?></td>
                            <td><?php echo htmlspecialchars($demande['user_prenom'] . ' ' . $demande['user_nom']); ?></td>
                            <td>
                                <?php 
                                $flags = [
                                    'france' => '🇫🇷',
                                    'belgique' => '🇧🇪',
                                    'espagne' => '🇪🇸',
                                    'italie' => '🇮🇹',
                                    'turquie' => '🇹🇷',
                                    'roumanie' => '🇷🇴',
                                    'luxembourg' => '🇱🇺',
                                    'bulgarie' => '🇧🇬'
                                ];
                                echo ($flags[$demande['pays_destination']] ?? '') . ' ' . ucfirst($demande['pays_destination']);
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($demande['etablissement']); ?></td>
                            <td>
                                <?php 
                                $niveaux = [
                                    'licence' => 'Licence',
                                    'master' => 'Master',
                                    'doctorat' => 'Doctorat',
                                    'echange' => 'Échange',
                                    'langue' => 'Langue'
                                ];
                                echo $niveaux[$demande['niveau_etudes']] ?? $demande['niveau_etudes'];
                                ?>
                            </td>
                            <td><?php echo $date_debut; ?></td>
                            <td><?php echo $demande['duree_etudes']; ?> mois</td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action view" onclick="viewDemande(<?php echo $demande['id']; ?>)"
                                            title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn-action edit" onclick="editDemande(<?php echo $demande['id']; ?>)"
                                            title="Modifier le statut">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn-action delete" onclick="deleteDemande(<?php echo $demande['id']; ?>)"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn-action download" onclick="downloadDocuments(<?php echo $demande['id']; ?>)"
                                            title="Télécharger les documents">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($demandes)): ?>
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <h4>Aucune demande trouvée</h4>
                                    <p>Aucune demande de visa étudiant n'a été soumise pour le moment.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button class="page-btn" onclick="changePage(-1)" disabled>
                <i class="fas fa-chevron-left"></i> Précédent
            </button>
            <span class="page-info">Page <span id="currentPage">1</span></span>
            <button class="page-btn" onclick="changePage(1)">
                Suivant <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal pour voir les détails -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-file-alt"></i> Détails de la Demande</h3>
            <button class="close-modal" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalDetails">
            <!-- Les détails seront chargés ici via AJAX -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal()">Fermer</button>
        </div>
    </div>
</div>

<!-- Modal pour modifier le statut -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Modifier le Statut</h3>
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm">
                <input type="hidden" id="editDemandeId">
                <div class="form-group">
                    <label for="editStatus">Nouveau statut *</label>
                    <select id="editStatus" class="form-control" required>
                        <option value="en_attente">En attente</option>
                        <option value="en_cours">En cours de traitement</option>
                        <option value="approuve">Approuvé</option>
                        <option value="rejete">Rejeté</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editComment">Commentaire (optionnel)</label>
                    <textarea id="editComment" class="form-control" rows="3" 
                              placeholder="Ajouter un commentaire sur le changement de statut..."></textarea>
                </div>
                <div class="form-group">
                    <label for="editNotify">Notification</label>
                    <div class="checkbox-group">
                        <input type="checkbox" id="editNotify" checked>
                        <label for="editNotify">Envoyer un email au demandeur</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeEditModal()">Annuler</button>
            <button class="btn btn-primary" onclick="saveStatus()">Enregistrer</button>
        </div>
    </div>
</div>

<style>
    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }

    .admin-header {
        background: linear-gradient(135deg, #0055a4, #2c3e50);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
        text-align: center;
    }

    .admin-header h1 {
        margin: 0 0 10px 0;
        font-size: 2rem;
    }

    .admin-header p {
        margin: 0;
        opacity: 0.9;
        font-size: 1.1rem;
    }

    /* Statistiques */
    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-card.total .stat-icon { background: #0055a4; }
    .stat-card.pending .stat-icon { background: #ff9800; }
    .stat-card.in-progress .stat-icon { background: #2196f3; }
    .stat-card.approved .stat-icon { background: #4caf50; }
    .stat-card.rejected .stat-icon { background: #f44336; }

    .stat-info h3 {
        margin: 0;
        font-size: 1.8rem;
        font-weight: bold;
    }

    .stat-info p {
        margin: 5px 0 0 0;
        color: #666;
        font-size: 0.9rem;
    }

    /* Filtres */
    .filters-section {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .filters-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .filters-header h3 {
        margin: 0;
        color: #2c3e50;
    }

    .filters-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .filter-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .filter-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .search-box {
        position: relative;
    }

    .search-box i {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #666;
    }

    /* Tableau */
    .table-section {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .table-header h3 {
        margin: 0;
        color: #2c3e50;
    }

    .table-actions {
        display: flex;
        gap: 10px;
    }

    .btn-export {
        background: #28a745;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        transition: background 0.3s;
    }

    .btn-export:hover {
        background: #218838;
    }

    .table-responsive {
        overflow-x: auto;
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }

    .admin-table th {
        background: #0055a4;
        color: white;
        padding: 15px;
        text-align: left;
        font-weight: 600;
        position: sticky;
        top: 0;
    }

    .admin-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }

    .admin-table tr:hover {
        background-color: #f8f9fa;
    }

    /* Badges de statut */
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-in-progress {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    /* Boutons d'action */
    .action-buttons {
        display: flex;
        gap: 8px;
    }

    .btn-action {
        width: 35px;
        height: 35px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .btn-action.view {
        background: #17a2b8;
        color: white;
    }

    .btn-action.edit {
        background: #ffc107;
        color: white;
    }

    .btn-action.delete {
        background: #dc3545;
        color: white;
    }

    .btn-action.download {
        background: #28a745;
        color: white;
    }

    .btn-action:hover {
        transform: scale(1.1);
        opacity: 0.9;
    }

    /* État vide */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }

    .empty-state h4 {
        margin: 0 0 10px 0;
        font-size: 1.5rem;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 20px;
        gap: 20px;
        background: #f8f9fa;
    }

    .page-btn {
        padding: 8px 16px;
        background: #0055a4;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: background 0.3s;
    }

    .page-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    .page-btn:hover:not(:disabled) {
        background: #004494;
    }

    .page-info {
        font-weight: 600;
        color: #333;
    }

    /* Modal */
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
        padding: 20px;
    }

    .modal.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 10px;
        width: 100%;
        max-width: 700px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
        background: #f8f9fa;
    }

    .modal-header h3 {
        margin: 0;
        color: #2c3e50;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #666;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .close-modal:hover {
        background: #e9ecef;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        padding: 20px;
        border-top: 1px solid #dee2e6;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Styles pour les détails dans le modal */
    .detail-section {
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #0055a4;
    }

    .detail-section h4 {
        margin: 0 0 10px 0;
        color: #0055a4;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .detail-item {
        margin-bottom: 10px;
    }

    .detail-label {
        font-weight: 600;
        color: #333;
        font-size: 0.9rem;
        margin-bottom: 5px;
        display: block;
    }

    .detail-value {
        color: #666;
        font-size: 1rem;
        padding: 8px;
        background: white;
        border-radius: 5px;
        border: 1px solid #dee2e6;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .stats-cards {
            grid-template-columns: repeat(2, 1fr);
        }

        .admin-header {
            padding: 20px;
        }

        .admin-header h1 {
            font-size: 1.5rem;
        }

        .table-header {
            flex-direction: column;
            gap: 15px;
            align-items: stretch;
        }

        .table-actions {
            justify-content: center;
        }

        .modal-content {
            width: 95%;
        }

        .action-buttons {
            flex-wrap: wrap;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .stats-cards {
            grid-template-columns: 1fr;
        }

        .filter-row {
            grid-template-columns: 1fr;
        }

        .admin-table th,
        .admin-table td {
            padding: 8px;
            font-size: 0.9rem;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
        }
    }
</style>

<script>
    // Variables globales
    let currentPage = 1;
    const rowsPerPage = 10;
    let currentDemandeId = null;

    // Fonction pour filtrer le tableau
    function filterTable() {
        const status = document.getElementById('filter_status').value;
        const pays = document.getElementById('filter_pays').value;
        const dateFilter = document.getElementById('filter_date').value;
        const search = document.getElementById('search').value.toLowerCase();
        
        const rows = document.querySelectorAll('#demandesTable tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowPays = row.getAttribute('data-pays');
            const rowDate = new Date(row.getAttribute('data-date'));
            const rowText = row.textContent.toLowerCase();
            
            let show = true;
            
            // Filtre par statut
            if (status && rowStatus !== status) show = false;
            
            // Filtre par pays
            if (pays && rowPays !== pays) show = false;
            
            // Filtre par date
            if (dateFilter) {
                const today = new Date();
                switch(dateFilter) {
                    case 'today':
                        if (!isSameDay(rowDate, today)) show = false;
                        break;
                    case 'week':
                        if (!isSameWeek(rowDate, today)) show = false;
                        break;
                    case 'month':
                        if (!isSameMonth(rowDate, today)) show = false;
                        break;
                    case 'year':
                        if (!isSameYear(rowDate, today)) show = false;
                        break;
                }
            }
            
            // Filtre par recherche
            if (search && !rowText.includes(search)) show = false;
            
            // Afficher ou cacher la ligne
            if (show) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Mettre à jour la pagination
        currentPage = 1;
        updatePagination(visibleCount);
    }

    // Fonctions d'aide pour les dates
    function isSameDay(date1, date2) {
        return date1.getDate() === date2.getDate() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getFullYear() === date2.getFullYear();
    }

    function isSameWeek(date1, date2) {
        const startOfWeek = new Date(date2);
        startOfWeek.setDate(date2.getDate() - date2.getDay());
        const endOfWeek = new Date(startOfWeek);
        endOfWeek.setDate(startOfWeek.getDate() + 6);
        
        return date1 >= startOfWeek && date1 <= endOfWeek;
    }

    function isSameMonth(date1, date2) {
        return date1.getMonth() === date2.getMonth() &&
               date1.getFullYear() === date2.getFullYear();
    }

    function isSameYear(date1, date2) {
        return date1.getFullYear() === date2.getFullYear();
    }

    // Fonction pour réinitialiser les filtres
    function resetFilters() {
        document.getElementById('filter_status').value = '';
        document.getElementById('filter_pays').value = '';
        document.getElementById('filter_date').value = '';
        document.getElementById('search').value = '';
        filterTable();
    }

    // Fonction pour changer de page
    function changePage(direction) {
        const rows = document.querySelectorAll('#demandesTable tbody tr:not([style*="display: none"])');
        const totalPages = Math.ceil(rows.length / rowsPerPage);
        
        currentPage += direction;
        
        if (currentPage < 1) currentPage = 1;
        if (currentPage > totalPages) currentPage = totalPages;
        
        updatePagination(rows.length);
        showPage(currentPage);
    }

    // Fonction pour mettre à jour la pagination
    function updatePagination(totalRows) {
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        document.getElementById('currentPage').textContent = currentPage;
        
        // Désactiver les boutons si nécessaire
        const prevBtn = document.querySelector('.page-btn:first-child');
        const nextBtn = document.querySelector('.page-btn:last-child');
        
        prevBtn.disabled = currentPage === 1;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
        
        // Afficher les lignes de la page courante
        showPage(currentPage);
    }

    // Fonction pour afficher une page spécifique
    function showPage(page) {
        const allRows = document.querySelectorAll('#demandesTable tbody tr');
        const visibleRows = Array.from(allRows).filter(row => row.style.display !== 'none');
        
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        // Cacher toutes les lignes visibles
        visibleRows.forEach(row => row.style.display = 'none');
        
        // Afficher seulement les lignes de la page courante
        for (let i = start; i < end && i < visibleRows.length; i++) {
            visibleRows[i].style.display = '';
        }
    }

    // Fonction pour voir les détails d'une demande
    function viewDemande(id) {
        currentDemandeId = id;
        
        // Charger les détails via AJAX
        fetch(`get_visa_details.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('modalDetails').innerHTML = html;
                document.getElementById('viewModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors du chargement des détails');
            });
    }

    // Fonction pour modifier le statut
    function editDemande(id) {
        currentDemandeId = id;
        
        // Charger le statut actuel via AJAX
        fetch(`get_visa_status.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('editDemandeId').value = id;
                document.getElementById('editStatus').value = data.status;
                document.getElementById('editModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                console.error('Erreur:', error);
                document.getElementById('editModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            });
    }

    // Fonction pour enregistrer le nouveau statut
    function saveStatus() {
        const id = document.getElementById('editDemandeId').value;
        const status = document.getElementById('editStatus').value;
        const comment = document.getElementById('editComment').value;
        const notify = document.getElementById('editNotify').checked;
        
        if (!status) {
            alert('Veuillez sélectionner un statut');
            return;
        }
        
        const formData = new FormData();
        formData.append('id', id);
        formData.append('status', status);
        formData.append('comment', comment);
        formData.append('notify', notify ? '1' : '0');
        
        fetch('update_visa_status.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Statut mis à jour avec succès');
                closeEditModal();
                location.reload(); // Recharger la page pour voir les changements
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la mise à jour');
        });
    }

    // Fonction pour supprimer une demande
    function deleteDemande(id) {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.')) {
            return;
        }
        
        fetch(`delete_visa_demande.php?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Demande supprimée avec succès');
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la suppression');
            });
    }

    // Fonction pour télécharger les documents
    function downloadDocuments(id) {
        window.open(`download_visa_documents.php?id=${id}`, '_blank');
    }

    // Fonction pour exporter en Excel
    function exportToExcel() {
        // Ici, vous pouvez implémenter l'export Excel
        // Pour l'exemple, nous redirigeons vers un script PHP
        window.open('export_visas_excel.php', '_blank');
    }

    // Fonction pour exporter en PDF
    function exportToPDF() {
        // Ici, vous pouvez implémenter l'export PDF
        // Pour l'exemple, nous redirigeons vers un script PHP
        window.open('export_visas_pdf.php', '_blank');
    }

    // Fonctions pour fermer les modals
    function closeModal() {
        document.getElementById('viewModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Fermer les modals en cliquant en dehors
    window.onclick = function(event) {
        const viewModal = document.getElementById('viewModal');
        const editModal = document.getElementById('editModal');
        
        if (event.target === viewModal) {
            closeModal();
        }
        if (event.target === editModal) {
            closeEditModal();
        }
    }

    // Initialiser la pagination au chargement
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('#demandesTable tbody tr');
        updatePagination(rows.length);
        showPage(1);
    });
</script>

<?php
include __DIR__ . '../includes/footer.php';
?>