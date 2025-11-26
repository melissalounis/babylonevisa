<?php
// biometrie.php
session_start();

// Vérifier si l'utilisateur est administrateur
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
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

// Récupérer les demandes de biométrie avec les personnes supplémentaires
$demandes = [];
try {
    $sql = "SELECT rb.*, 
                   COUNT(rps.id) as nombre_personnes,
                   GROUP_CONCAT(CONCAT(rps.nom_complet, '|', rps.date_naissance, '|', rps.numero_passeport) SEPARATOR ';;') as personnes_info
            FROM rendez_vous_biometrie rb
            LEFT JOIN rendez_vous_personnes_supp rps ON rb.id = rps.rendez_vous_id
            GROUP BY rb.id
            ORDER BY rb.date_creation DESC";
    $stmt = $pdo->query($sql);
    $demandes = $stmt->fetchAll();
    
    // Formater les données des personnes supplémentaires
    foreach ($demandes as &$demande) {
        $demande['personnes_supplementaires'] = [];
        if (!empty($demande['personnes_info'])) {
            $personnes = explode(';;', $demande['personnes_info']);
            foreach ($personnes as $personne) {
                if (!empty($personne)) {
                    list($nom, $naissance, $passeport) = explode('|', $personne);
                    $demande['personnes_supplementaires'][] = [
                        'nom' => $nom,
                        'naissance' => $naissance,
                        'passeport' => $passeport
                    ];
                }
            }
        }
    }
    unset($demande); // Libérer la référence
    
} catch (PDOException $e) {
    $error_message = "Erreur lors de la récupération des demandes : " . $e->getMessage();
}

// Traitement des actions (statut, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        
        try {
            if ($_POST['action'] === 'changer_statut') {
                $nouveau_statut = $_POST['statut'];
                $sql = "UPDATE rendez_vous_biometrie SET statut = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nouveau_statut, $id]);
                
                $_SESSION['success_message'] = "Statut mis à jour avec succès";
                
            } elseif ($_POST['action'] === 'supprimer') {
                // Commencer une transaction
                $pdo->beginTransaction();
                
                // Supprimer d'abord les personnes supplémentaires
                $sql_personnes = "DELETE FROM rendez_vous_personnes_supp WHERE rendez_vous_id = ?";
                $stmt_personnes = $pdo->prepare($sql_personnes);
                $stmt_personnes->execute([$id]);
                
                // Puis supprimer le rendez-vous principal
                $sql_principal = "DELETE FROM rendez_vous_biometrie WHERE id = ?";
                $stmt_principal = $pdo->prepare($sql_principal);
                $stmt_principal->execute([$id]);
                
                // Valider la transaction
                $pdo->commit();
                
                $_SESSION['success_message'] = "Demande et personnes associées supprimées avec succès";
            }
            
            header('Location: biometrie.php');
            exit();
            
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_message = "Erreur lors de l'opération : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Demandes Biométrie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --canada-red: #FF0000;
            --canada-white: #FFFFFF;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-header {
            background: linear-gradient(135deg, var(--canada-red) 0%, #cc0000 100%);
            color: var(--canada-white);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .statut-nouveau { background-color: #e3f2fd; color: #1565c0; }
        .statut-confirme { background-color: #e8f5e8; color: #2e7d32; }
        .statut-annule { background-color: #ffebee; color: #c62828; }
        .statut-termine { background-color: #f3e5f5; color: #7b1fa2; }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 2px solid var(--canada-red);
            font-weight: 600;
        }
        
        .badge-statut {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            background-color: var(--canada-red);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .table td {
            vertical-align: middle;
            border-color: var(--border-color);
        }
        
        .btn-action {
            padding: 4px 8px;
            margin: 2px;
            font-size: 0.8rem;
        }
        
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stats-card {
            text-align: center;
            padding: 1.5rem;
            border-radius: 12px;
            color: white;
            margin-bottom: 1rem;
        }
        
        .stats-total { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stats-nouveau { background: linear-gradient(135deg, #2196F3 0%, #21CBF3 100%); }
        .stats-confirme { background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%); }
        .stats-termine { background: linear-gradient(135deg, #9C27B0 0%, #E91E63 100%); }
        
        .personnes-badge {
            background: var(--canada-red);
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-fingerprint"></i> Administration Biométrie</h1>
                    <p class="mb-0">Gestion des demandes de rendez-vous biométrie</p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="admin_dashboard.php" class="btn btn-light me-2">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Messages d'alerte -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card stats-total">
                    <h3><i class="fas fa-list"></i></h3>
                    <h4><?php echo count($demandes); ?></h4>
                    <p>Total Demandes</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card stats-nouveau">
                    <h3><i class="fas fa-clock"></i></h3>
                    <h4><?php echo count(array_filter($demandes, function($d) { return $d['statut'] === 'nouveau'; })); ?></h4>
                    <p>Nouvelles</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card stats-confirme">
                    <h3><i class="fas fa-check-circle"></i></h3>
                    <h4><?php echo count(array_filter($demandes, function($d) { return $d['statut'] === 'confirme'; })); ?></h4>
                    <p>Confirmées</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card stats-termine">
                    <h3><i class="fas fa-flag-checkered"></i></h3>
                    <h4><?php echo count(array_filter($demandes, function($d) { return $d['statut'] === 'termine'; })); ?></h4>
                    <p>Terminées</p>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters">
            <div class="row">
                <div class="col-md-4">
                    <label for="search" class="form-label">Rechercher</label>
                    <input type="text" id="search" class="form-control" placeholder="Nom, email, passeport...">
                </div>
                <div class="col-md-4">
                    <label for="filter_statut" class="form-label">Filtrer par statut</label>
                    <select id="filter_statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="nouveau">Nouveau</option>
                        <option value="confirme">Confirmé</option>
                        <option value="annule">Annulé</option>
                        <option value="termine">Terminé</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter_date" class="form-label">Filtrer par date</label>
                    <input type="date" id="filter_date" class="form-control">
                </div>
            </div>
        </div>

        <!-- Tableau des demandes -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table"></i> Liste des demandes</h5>
                <span class="badge bg-primary"><?php echo count($demandes); ?> demandes</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="table-demandes">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nom Complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Passeport</th>
                                <th>Personnes</th>
                                <th>Nationalité</th>
                                <th>Date Naissance</th>
                                <th>Statut</th>
                                <th>Date Création</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($demandes)): ?>
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-3"></i><br>
                                        Aucune demande de rendez-vous biométrie trouvée
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($demandes as $demande): ?>
                                    <tr data-statut="<?php echo $demande['statut']; ?>" 
                                        data-date="<?php echo date('Y-m-d', strtotime($demande['date_creation'])); ?>">
                                        <td><strong>#<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                        <td>
                                            <?php echo htmlspecialchars($demande['nom_complet']); ?>
                                            <?php if ($demande['nombre_personnes'] > 0): ?>
                                                <span class="personnes-badge" title="<?php echo $demande['nombre_personnes']; ?> personne(s) supplémentaire(s)">
                                                    +<?php echo $demande['nombre_personnes']; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($demande['email']); ?></td>
                                        <td><?php echo htmlspecialchars($demande['telephone']); ?></td>
                                        <td><code><?php echo htmlspecialchars($demande['numero_passeport']); ?></code></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo $demande['nombre_personnes']; ?> pers.
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($demande['nationalite']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($demande['date_naissance'])); ?></td>
                                        <td>
                                            <span class="badge-statut statut-<?php echo $demande['statut']; ?>">
                                                <?php 
                                                $statuts = [
                                                    'nouveau' => 'Nouveau',
                                                    'confirme' => 'Confirmé', 
                                                    'annule' => 'Annulé',
                                                    'termine' => 'Terminé'
                                                ];
                                                echo $statuts[$demande['statut']] ?? $demande['statut'];
                                                ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($demande['date_creation'])); ?></td>
                                        <td>
                                            <div class="btn-group">
                                                <!-- Bouton Voir Détails -->
                                                <button type="button" class="btn btn-info btn-sm btn-action" 
                                                        data-bs-toggle="modal" data-bs-target="#modalDetails"
                                                        onclick="afficherDetails(<?php echo htmlspecialchars(json_encode($demande)); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                <!-- Bouton Changer Statut -->
                                                <div class="dropdown">
                                                    <button class="btn btn-warning btn-sm btn-action dropdown-toggle" 
                                                            type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'nouveau')">Nouveau</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'confirme')">Confirmé</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'annule')">Annulé</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="changerStatut(<?php echo $demande['id']; ?>, 'termine')">Terminé</a></li>
                                                    </ul>
                                                </div>
                                                
                                                <!-- Bouton Supprimer -->
                                                <button type="button" class="btn btn-danger btn-sm btn-action" 
                                                        onclick="supprimerDemande(<?php echo $demande['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
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
        </div>
    </div>

    <!-- Modal Détails -->
    <div class="modal fade" id="modalDetails" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails de la demande #<span id="modalId"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="modalDetailsContent">
                    <!-- Contenu chargé dynamiquement -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtrage du tableau
        document.getElementById('search').addEventListener('input', function() {
            filterTable();
        });

        document.getElementById('filter_statut').addEventListener('change', function() {
            filterTable();
        });

        document.getElementById('filter_date').addEventListener('change', function() {
            filterTable();
        });

        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const statut = document.getElementById('filter_statut').value;
            const date = document.getElementById('filter_date').value;
            
            const rows = document.querySelectorAll('#table-demandes tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatut = row.getAttribute('data-statut');
                const rowDate = row.getAttribute('data-date');
                
                const matchSearch = text.includes(search);
                const matchStatut = !statut || rowStatut === statut;
                const matchDate = !date || rowDate === date;
                
                row.style.display = (matchSearch && matchStatut && matchDate) ? '' : 'none';
            });
        }

        // Afficher les détails dans le modal
        function afficherDetails(demande) {
            document.getElementById('modalId').textContent = demande.id.toString().padStart(6, '0');
            
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Informations Personnelles</h6>
                        <p><strong>Nom complet:</strong> ${demande.nom_complet}</p>
                        <p><strong>Email:</strong> ${demande.email}</p>
                        <p><strong>Téléphone:</strong> ${demande.telephone}</p>
                        <p><strong>Nationalité:</strong> ${demande.nationalite}</p>
                        <p><strong>Date de naissance:</strong> ${new Date(demande.date_naissance).toLocaleDateString('fr-FR')}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Informations Passeport</h6>
                        <p><strong>Numéro passeport:</strong> ${demande.numero_passeport}</p>
                        <p><strong>Statut:</strong> <span class="badge-statut statut-${demande.statut}">${getStatutText(demande.statut)}</span></p>
                        <p><strong>Personnes supplémentaires:</strong> ${demande.nombre_personnes || 0}</p>
                        <p><strong>Date création:</strong> ${new Date(demande.date_creation).toLocaleString('fr-FR')}</p>
                    </div>
                </div>
                ${demande.personnes_supplementaires && demande.personnes_supplementaires.length > 0 ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Personnes Supplémentaires (${demande.personnes_supplementaires.length})</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Nom complet</th>
                                        <th>Date de naissance</th>
                                        <th>Numéro passeport</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${demande.personnes_supplementaires.map(personne => `
                                        <tr>
                                            <td>${personne.nom}</td>
                                            <td>${new Date(personne.naissance).toLocaleDateString('fr-FR')}</td>
                                            <td><code>${personne.passeport}</code></td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                ` : '<div class="row mt-3"><div class="col-12"><p class="text-muted">Aucune personne supplémentaire</p></div></div>'}
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Documents</h6>
                        <div class="d-flex gap-2">
                            ${demande.passeport_path ? `<a href="uploads/biometrie/${demande.passeport_path}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-pdf"></i> Passeport Principal</a>` : '<span class="text-muted">Aucun document</span>'}
                            ${demande.lettre_biometrie_path ? `<a href="uploads/biometrie/${demande.lettre_biometrie_path}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-pdf"></i> Lettre Biométrie</a>` : ''}
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modalDetailsContent').innerHTML = content;
        }

        function getStatutText(statut) {
            const statuts = {
                'nouveau': 'Nouveau',
                'confirme': 'Confirmé',
                'annule': 'Annulé',
                'termine': 'Terminé'
            };
            return statuts[statut] || statut;
        }

        // Changer le statut
        function changerStatut(id, statut) {
            if (confirm('Êtes-vous sûr de vouloir changer le statut de cette demande ?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'biometrie.php';
                
                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'changer_statut';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = id;
                
                const inputStatut = document.createElement('input');
                inputStatut.type = 'hidden';
                inputStatut.name = 'statut';
                inputStatut.value = statut;
                
                form.appendChild(inputAction);
                form.appendChild(inputId);
                form.appendChild(inputStatut);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Supprimer une demande
        function supprimerDemande(id) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette demande et toutes les personnes associées ? Cette action est irréversible.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'biometrie.php';
                
                const inputAction = document.createElement('input');
                inputAction.type = 'hidden';
                inputAction.name = 'action';
                inputAction.value = 'supprimer';
                
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'id';
                inputId.value = id;
                
                form.appendChild(inputAction);
                form.appendChild(inputId);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>