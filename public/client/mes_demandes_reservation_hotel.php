<?php
// Connexion à la base de données
$host = 'localhost';
require_once __DIR__ . '../../../config.php';



// Création de la table si elle n'existe pas
$createTableQuery = "
CREATE TABLE IF NOT EXISTS demandes_reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_dossier VARCHAR(20) UNIQUE NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    civilite VARCHAR(10) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    nationalite VARCHAR(100) NOT NULL,
    numero_passeport VARCHAR(50) NOT NULL,
    date_expiration_passeport DATE NOT NULL,
    date_arrivee DATE NOT NULL,
    date_depart DATE NOT NULL,
    heure_arrivee_prevue TIME,
    moyen_transport VARCHAR(50),
    numero_vol_train VARCHAR(100),
    type_hebergement VARCHAR(50),
    categorie_chambre VARCHAR(50),
    nombre_adultes INT NOT NULL,
    nombre_enfants INT DEFAULT 0,
    ages_enfants VARCHAR(255),
    demandes_speciales TEXT,
    raison_sejour VARCHAR(100),
    precisions_financement TEXT,
    status VARCHAR(20) DEFAULT 'en_attente',
    prix_estime DECIMAL(10,2)
)";

try {
    $pdo->exec($createTableQuery);
} catch (PDOException $e) {
    die("Erreur lors de la création de la table : " . $e->getMessage());
}

// Récupération des demandes depuis la base de données
try {
    $stmt = $pdo->prepare("SELECT * FROM demandes_reservation ORDER BY date_creation DESC");
    $stmt->execute();
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;'>
            Erreur lors de la récupération des demandes: " . htmlspecialchars($e->getMessage()) . "
          </div>";
    $demandes = [];
}

// Statistiques
$total_demandes = count($demandes);
$demandes_attente = 0;
$demandes_confirmees = 0;
$demandes_validees = 0;
$demandes_annulees = 0;

foreach ($demandes as $demande) {
    switch ($demande['status']) {
        case 'en_attente':
            $demandes_attente++;
            break;
        case 'confirmee':
            $demandes_confirmees++;
            break;
        case 'validee':
            $demandes_validees++;
            break;
        case 'annulee':
            $demandes_annulees++;
            break;
    }
}

// Filtrage
$filter_status = $_GET['status'] ?? 'tous';
if ($filter_status !== 'tous') {
    $demandes_filtrees = array_filter($demandes, function($d) use ($filter_status) {
        return $d['status'] == $filter_status;
    });
} else {
    $demandes_filtrees = $demandes;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes de Réservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #7991fdff 0%, #ebe7f0ff 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--secondary);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        .demandes-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 20px 30px;
        }

        .card-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 30px;
        }

        .demande-item {
            border: 1px solid #e1e8ed;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .demande-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }

        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e1e8ed;
        }

        .demande-numero {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--primary);
        }

        .demande-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-en-attente {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmee {
            background: #d1edff;
            color: #0c5460;
        }

        .status-validee {
            background: #d4edda;
            color: #155724;
        }

        .status-annulee {
            background: #f8d7da;
            color: #721c24;
        }

        .demande-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark);
        }

        .demande-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-small {
            padding: 8px 15px;
            font-size: 0.8rem;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }

        .btn-outline:hover {
            background: var(--secondary);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #7f8c8d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #bdc3c7;
        }

        .filters {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .filter-group {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-select {
            padding: 8px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .demande-details {
                grid-template-columns: 1fr;
            }

            .demande-actions {
                flex-direction: column;
            }

            .navigation {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-list-alt"></i> Mes Demandes de Réservation</h1>
            <p>Consultez l'état de vos demandes de réservation hôtelière</p>
        </div>

        <div class="navigation">
            <a href="../réservation hotel.php" class="btn">
                <i class="fas fa-plus"></i> Nouvelle Demande
            </a>
            <div>
                <a href="espace_client.php" class="btn btn-secondary">
                    <i class="fas fa-user"></i> Mon Espace
                </a>
            </div>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <i class="fas fa-file-alt"></i>
                <div class="stat-number"><?php echo $total_demandes; ?></div>
                <div class="stat-label">Total des demandes</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <div class="stat-number"><?php echo $demandes_attente; ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <div class="stat-number"><?php echo $demandes_confirmees; ?></div>
                <div class="stat-label">Confirmées</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <div class="stat-number"><?php echo $demandes_validees; ?></div>
                <div class="stat-label">Validées</div>
            </div>
        </div>

        <div class="filters">
            <form method="GET" action="">
                <div class="filter-group">
                    <label for="status">Filtrer par statut :</label>
                    <select id="status" name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="tous" <?php echo $filter_status == 'tous' ? 'selected' : ''; ?>>Tous les statuts</option>
                        <option value="en_attente" <?php echo $filter_status == 'en_attente' ? 'selected' : ''; ?>>En attente</option>
                        <option value="confirmee" <?php echo $filter_status == 'confirmee' ? 'selected' : ''; ?>>Confirmée</option>
                        <option value="validee" <?php echo $filter_status == 'validee' ? 'selected' : ''; ?>>Validée</option>
                        <option value="annulee" <?php echo $filter_status == 'annulee' ? 'selected' : ''; ?>>Annulée</option>
                    </select>
                </div>
            </form>
        </div>

        <div class="demandes-list">
            <div class="card-header">
                <h2><i class="fas fa-history"></i> Historique des Demandes</h2>
            </div>
            <div class="card-body">
                <?php if (empty($demandes_filtrees)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune demande trouvée</h3>
                        <p>Vous n'avez aucune demande de réservation correspondant à vos critères.</p>
                        <a href="../réservation hotel.php" class="btn" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Créer une nouvelle demande
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($demandes_filtrees as $demande): ?>
                        <div class="demande-item">
                            <div class="demande-header">
                                <div class="demande-numero">
                                    <i class="fas fa-file-invoice"></i> <?php echo htmlspecialchars($demande['numero_dossier']); ?>
                                </div>
                                <div class="demande-status <?php echo 'status-' . str_replace('_', '-', $demande['status']); ?>">
                                    <?php 
                                    $status_labels = [
                                        'en_attente' => 'En attente',
                                        'confirmee' => 'Confirmée',
                                        'validee' => 'Validée',
                                        'annulee' => 'Annulée'
                                    ];
                                    echo $status_labels[$demande['status']];
                                    ?>
                                </div>
                            </div>

                            <div class="demande-details">
                                <div class="detail-item">
                                    <span class="detail-label">Client</span>
                                    <span class="detail-value">
                                        <?php echo htmlspecialchars($demande['civilite'] . ' ' . $demande['prenom'] . ' ' . $demande['nom']); ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Dates du séjour</span>
                                    <span class="detail-value">
                                        <?php 
                                        echo date('d/m/Y', strtotime($demande['date_arrivee'])) . ' - ' . 
                                             date('d/m/Y', strtotime($demande['date_depart']));
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Type d'hébergement</span>
                                    <span class="detail-value">
                                        <?php 
                                        $types = [
                                            'hotel' => 'Hôtel',
                                            'appartement' => 'Appartement',
                                            'auberge' => 'Auberge',
                                            'autre' => 'Autre'
                                        ];
                                        echo isset($types[$demande['type_hebergement']]) ? $types[$demande['type_hebergement']] : $demande['type_hebergement'];
                                        ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Voyageurs</span>
                                    <span class="detail-value">
                                        <?php echo $demande['nombre_adultes'] . ' adulte(s)'; ?>
                                        <?php if ($demande['nombre_enfants'] > 0): ?>
                                            , <?php echo $demande['nombre_enfants'] . ' enfant(s)'; ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php if ($demande['prix_estime']): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Prix estimé</span>
                                    <span class="detail-value"><?php echo number_format($demande['prix_estime'], 2, ',', ' ') . ' €'; ?></span>
                                </div>
                                <?php endif; ?>
                                <div class="detail-item">
                                    <span class="detail-label">Date de création</span>
                                    <span class="detail-value">
                                        <?php echo date('d/m/Y H:i', strtotime($demande['date_creation'])); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="demande-actions">
                                <a href="detail_demande.php?id=<?php echo $demande['id']; ?>" class="btn btn-small">
                                    <i class="fas fa-eye"></i> Voir les détails
                                </a>
                                <?php if ($demande['status'] == 'en_attente'): ?>
                                    <a href="modifier_demande.php?id=<?php echo $demande['id']; ?>" class="btn btn-small btn-outline">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                    <button class="btn btn-small btn-outline annuler-btn" 
                                            style="border-color: var(--danger); color: var(--danger);"
                                            data-id="<?php echo $demande['id']; ?>"
                                            data-numero="<?php echo htmlspecialchars($demande['numero_dossier']); ?>">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                <?php endif; ?>
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Fonction pour confirmer l'annulation d'une demande
        function confirmerAnnulation(demandeId, numeroDossier) {
            if (confirm('Êtes-vous sûr de vouloir annuler la demande ' + numeroDossier + ' ?')) {
                // Envoyer la requête d'annulation
                const formData = new FormData();
                formData.append('id', demandeId);
                formData.append('action', 'annuler');

                fetch('mes_demandes_reservation_hotel.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Demande annulée avec succès');
                        location.reload();
                    } else {
                        alert('Erreur lors de l\'annulation : ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erreur lors de l\'annulation');
                });
            }
        }

        // Ajouter des écouteurs d'événements pour les boutons d'annulation
        document.addEventListener('DOMContentLoaded', function() {
            const boutonsAnnulation = document.querySelectorAll('.annuler-btn');
            boutonsAnnulation.forEach(btn => {
                btn.addEventListener('click', function() {
                    const demandeId = this.getAttribute('data-id');
                    const numeroDossier = this.getAttribute('data-numero');
                    confirmerAnnulation(demandeId, numeroDossier);
                });
            });
        });
    </script>

    <?php
    // Traitement de l'annulation
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'annuler') {
        $demandeId = $_POST['id'];
        
        try {
            $stmt = $pdo->prepare("UPDATE demandes_reservation SET status = 'annulee' WHERE id = ?");
            $stmt->execute([$demandeId]);
            
            echo json_encode(['success' => true, 'message' => 'Demande annulée avec succès']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    ?>
</body>
</html>