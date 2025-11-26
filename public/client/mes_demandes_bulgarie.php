<?php
session_start();
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Paramètres de connexion
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

// Initialiser les variables
$demandes_bulgarie = [];
$error_message = null;
$stats = ['total' => 0, 'nouveau' => 0, 'en_traitement' => 0, 'approuve' => 0, 'refuse' => 0];

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Récupérer l'email de l'utilisateur
    $user_id = $_SESSION['user_id'];
    $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt_user->execute([$user_id]);
    $user = $stmt_user->fetch();
    $user_email = $user['email'] ?? '';

    // Vérifier si la table Bulgarie existe
    $table_bulgarie = $pdo->query("SHOW TABLES LIKE 'demandes_etudes_bulgarie'")->rowCount();
    
    if ($table_bulgarie == 0) {
        $error_message = "La table des demandes Bulgarie n'existe pas encore.";
    } else {
        // Récupérer les demandes Bulgarie pour cet utilisateur
        $stmt_bulgarie = $pdo->prepare("SELECT * FROM demandes_etudes_bulgarie WHERE email = ? ORDER BY date_soumission DESC");
        $stmt_bulgarie->execute([$user_email]);
        $demandes_bulgarie = $stmt_bulgarie->fetchAll();

        // Calculer les statistiques
        $stats['total'] = count($demandes_bulgarie);
        
        foreach ($demandes_bulgarie as $demande) {
            $statut = strtolower($demande['statut'] ?? 'nouveau');
            switch ($statut) {
                case 'nouveau':
                    $stats['nouveau']++;
                    break;
                case 'en_traitement':
                    $stats['en_traitement']++;
                    break;
                case 'approuve':
                    $stats['approuve']++;
                    break;
                case 'refuse':
                    $stats['refuse']++;
                    break;
            }
        }
    }

} catch (PDOException $e) {
    $error_message = "Erreur de connexion à la base de données : " . $e->getMessage();
}

// Fonction pour formater les dates
function formatDate($date) {
    if (empty($date) || $date == '0000-00-00') return 'Non spécifié';
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($date) {
    if (empty($date) || $date == '0000-00-00 00:00:00') return 'Non spécifié';
    return date('d/m/Y à H:i', strtotime($date));
}

// Fonction pour traduire le statut
function traduireStatut($statut) {
    $traductions = [
        'nouveau' => 'Nouvelle demande',
        'en_traitement' => 'En traitement',
        'approuve' => 'Approuvée',
        'refuse' => 'Refusée'
    ];
    return $traductions[$statut] ?? ucfirst($statut);
}

// Fonction pour traduire le programme
function traduireProgramme($programme) {
    $traductions = [
        'anglais' => 'Programme en Anglais',
        'preparatoire' => 'Année préparatoire'
    ];
    return $traductions[$programme] ?? ucfirst($programme);
}

// Fonction pour traduire le niveau
function traduireNiveau($niveau) {
    $traductions = [
        'l1' => 'Licence 1',
        'l2' => 'Licence 2',
        'l3' => 'Licence 3',
        'm1' => 'Master 1',
        'm2' => 'Master 2'
    ];
    return $traductions[$niveau] ?? ucfirst($niveau);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes demandes Bulgarie - Espace client</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --bulgarie-color: #d9534f;
            --light-bg: #f8f9fa;
            --dark-text: #2c3e50;
            --white: #ffffff;
            --light-gray: #e9ecef;
            --border-color: #dee2e6;
            --success-color: #28a745;
            --error-color: #dc3545;
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: var(--dark-text);
            line-height: 1.6;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            background: var(--primary-color);
            color: var(--white);
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .dashboard-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .dashboard-header h1 i {
            margin-right: 15px;
        }

        .user-info {
            opacity: 0.9;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        .error-message {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            color: #721c24;
        }

        .error-icon {
            font-size: 24px;
            margin-right: 10px;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            transition: var(--transition);
            border-left: 4px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .stat-icon {
            font-size: 35px;
            margin-right: 15px;
            color: var(--primary-color);
        }

        .stat-info h3 {
            font-size: 1.8em;
            margin: 0;
            color: var(--dark-text);
        }

        .stat-info p {
            margin: 5px 0 0;
            color: #6c757d;
            font-weight: 500;
        }

        .dashboard-actions {
            margin-bottom: 40px;
        }

        .dashboard-actions h2 {
            color: var(--dark-text);
            margin-bottom: 20px;
            font-size: 1.4rem;
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
            display: block;
            border: 1px solid var(--border-color);
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
            text-decoration: none;
            color: inherit;
        }

        .action-icon {
            font-size: 35px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .action-card h3 {
            color: var(--dark-text);
            margin: 0 0 10px;
            font-size: 1.2rem;
        }

        .action-card p {
            color: #6c757d;
            margin: 0;
            font-size: 0.9rem;
        }

        .demandes-section {
            margin-bottom: 40px;
        }

        .demandes-section h2 {
            color: var(--dark-text);
            margin-bottom: 20px;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .demandes-list {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .demande-item {
            display: flex;
            padding: 20px;
            border-bottom: 1px solid var(--light-gray);
            align-items: center;
            justify-content: space-between;
        }

        .demande-item:last-child {
            border-bottom: none;
        }

        .demande-icon {
            font-size: 24px;
            margin-right: 15px;
            flex-shrink: 0;
            color: var(--primary-color);
        }

        .demande-details {
            flex-grow: 1;
        }

        .demande-details p {
            margin: 0 0 5px;
            color: var(--dark-text);
        }

        .demande-time {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .demande-time i {
            margin-right: 5px;
        }

        .statut-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .statut-nouveau { background: #fff3cd; color: #856404; }
        .statut-en_traitement { background: #cce7ff; color: #004085; }
        .statut-approuve { background: #d4edda; color: #155724; }
        .statut-refuse { background: #f8d7da; color: #721c24; }

        .demande-actions {
            display: flex;
            gap: 10px;
            flex-shrink: 0;
        }

        .btn-action {
            padding: 8px 12px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.8rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: none;
            cursor: pointer;
        }

        .btn-action:hover {
            background: var(--secondary-color);
            color: white;
            text-decoration: none;
        }

        .btn-action:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .no-demande {
            display: flex;
            padding: 40px;
            text-align: center;
            justify-content: center;
            opacity: 0.7;
            align-items: center;
            flex-direction: column;
        }

        .no-demande .demande-icon {
            font-size: 50px;
            color: #adb5bd;
            margin-bottom: 15px;
        }

        .type-badge {
            display: inline-block;
            padding: 3px 8px;
            background: var(--light-bg);
            border-radius: 4px;
            font-size: 0.7rem;
            margin-left: 8px;
            color: var(--dark-text);
            border: 1px solid var(--border-color);
        }

        .type-badge.bulgarie {
            background: #ffe6e6;
            color: var(--bulgarie-color);
            border-color: #ffcccc;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .dashboard-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-header {
                padding: 20px;
            }
            
            .dashboard-header h1 {
                font-size: 1.8rem;
            }
            
            .demande-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .demande-icon {
                margin-right: 0;
            }
            
            .demande-actions {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- En-tête -->
        <div class="dashboard-header">
            <h1><i class="fas fa-graduation-cap"></i> Mes demandes en Bulgarie</h1>
            <p>Consultez l'état de toutes vos demandes d'études en Bulgarie</p>
            <div class="user-info">
                <small><i class="fas fa-user-circle"></i> Connecté en tant que : <?= htmlspecialchars($user_email) ?></small>
            </div>
        </div>

        <!-- Message d'erreur -->
        <?php if ($error_message): ?>
            <div class="error-message">
                <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <p><?= htmlspecialchars($error_message) ?></p>
            </div>
        <?php endif; ?>

        <!-- Cartes de statistiques -->
        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
                <div class="stat-info">
                    <h3><?= $stats['total'] ?></h3>
                    <p>Total des demandes</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3><?= $stats['nouveau'] ?></h3>
                    <p>Nouvelles demandes</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-cog"></i></div>
                <div class="stat-info">
                    <h3><?= $stats['en_traitement'] ?></h3>
                    <p>En traitement</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3><?= $stats['approuve'] ?></h3>
                    <p>Approuvées</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-info">
                    <h3><?= $stats['refuse'] ?></h3>
                    <p>Refusées</p>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="dashboard-actions">
            <h2><i class="fas fa-bolt"></i> Actions rapides</h2>
            <div class="action-grid">
                <a href="../bulgarie/etude/index.php" class="action-card">
                    <div class="action-icon"><i class="fas fa-plus-circle"></i></div>
                    <h3>Nouvelle demande</h3>
                    <p>Soumettre une nouvelle demande d'études en Bulgarie</p>
                </a>
                
                <a href="index.php" class="action-card">
                    <div class="action-icon"><i class="fas fa-tachometer-alt"></i></div>
                    <h3>Tableau de bord</h3>
                    <p>Retour à l'accueil principal</p>
                </a>
            </div>
        </div>

        <!-- Demandes Bulgarie -->
        <div class="demandes-section">
            <h2><i class="fas fa-graduation-cap"></i> Mes demandes en Bulgarie (<?= count($demandes_bulgarie) ?>)</h2>
            <div class="demandes-list">
                <?php if (!empty($demandes_bulgarie)): ?>
                    <?php foreach ($demandes_bulgarie as $demande): ?>
                        <?php 
                        $demande_id = htmlspecialchars($demande['id']);
                        $statut = $demande['statut'] ?? 'nouveau';
                        $date_soumission = formatDateTime($demande['date_soumission'] ?? '');
                        $programme = traduireProgramme($demande['programme'] ?? '');
                        $niveau = traduireNiveau($demande['niveau_etude'] ?? '');
                        
                        // Déterminer l'icône et la classe CSS selon le statut
                        switch ($statut) {
                            case 'approuve':
                                $icone = "<i class='fas fa-check-circle'></i>";
                                $classe_statut = "statut-approuve";
                                break;
                            case 'refuse':
                                $icone = "<i class='fas fa-times-circle'></i>";
                                $classe_statut = "statut-refuse";
                                break;
                            case 'en_traitement':
                                $icone = "<i class='fas fa-cog'></i>";
                                $classe_statut = "statut-en_traitement";
                                break;
                            case 'nouveau':
                            default:
                                $icone = "<i class='fas fa-clock'></i>";
                                $classe_statut = "statut-nouveau";
                        }
                        ?>
                        
                        <div class="demande-item">
                            <div class="demande-icon"><?= $icone ?></div>
                            <div class="demande-details">
                                <p>
                                    <strong>Demande #<?= $demande_id ?></strong> 
                                    <span class="type-badge bulgarie">Bulgarie</span>
                                </p>
                                <p>
                                    <strong>Programme:</strong> <?= $programme ?> |
                                    <strong>Niveau:</strong> <?= $niveau ?> |
                                    <strong>Statut:</strong> 
                                    <span class="statut-badge <?= $classe_statut ?>"><?= traduireStatut($statut) ?></span>
                                </p>
                                <span class="demande-time">
                                    <i class="fas fa-calendar-alt"></i> Soumise le <?= $date_soumission ?>
                                </span>
                            </div>
                            <div class="demande-actions">
                                <button class="btn-action" onclick="afficherDetailsBulgarie(<?= $demande_id ?>)">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                                
                                <?php if ($statut === 'nouveau'): ?>
                                    <a href="modifier_demande_bulgarie.php?id=<?= $demande_id ?>" class="btn-action">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>
                                <?php else: ?>
                                    <button class="btn-action" disabled title="Modification non autorisée">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-demande">
                        <div class="demande-icon"><i class="fas fa-graduation-cap"></i></div>
                        <div class="demande-details">
                            <p>Aucune demande d'études en Bulgarie trouvée pour votre compte.</p>
                            <span class="demande-time"><i class="fas fa-info-circle"></i> Créez votre première demande d'études en Bulgarie</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal pour les détails -->
    <div id="detailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="background: white; margin: 50px auto; padding: 30px; border-radius: 15px; max-width: 600px; max-height: 80vh; overflow-y: auto; position: relative;">
            <span style="position: absolute; right: 20px; top: 20px; font-size: 2rem; cursor: pointer;" onclick="fermerModal()">&times;</span>
            <h2 id="modalTitle">Détails de la demande</h2>
            <div id="modalContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <button class="btn-action" onclick="fermerModal()">
                    <i class="fas fa-times"></i> Fermer
                </button>
            </div>
        </div>
    </div>

    <script>
        // Fonction pour afficher les détails Bulgarie
        function afficherDetailsBulgarie(id) {
            const details = `
                <div class="demande-details-modal">
                    <h3><i class="fas fa-graduation-cap"></i> Détails de la demande #${id}</h3>
                    <div style="display: grid; gap: 15px; margin-top: 20px;">
                        <div>
                            <strong><i class="fas fa-info-circle"></i> Informations générales</strong>
                            <p>Référence: BG-${id}<br>Type: Demande d'admission<br>Statut: Nouvelle demande</p>
                        </div>
                        <div>
                            <strong><i class="fas fa-calendar"></i> Dates</strong>
                            <p>Date de soumission: ${new Date().toLocaleDateString('fr-FR')}</p>
                        </div>
                        <div>
                            <strong><i class="fas fa-university"></i> Informations académiques</strong>
                            <p>Programme: Programme en Anglais<br>Niveau: Licence 1</p>
                        </div>
                        <div>
                            <strong><i class="fas fa-file-upload"></i> Documents déposés</strong>
                            <p>Passeport, Justificatif financier, Photos d'identité, Relevés de notes...</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modalTitle').textContent = 'Détails de la demande Bulgarie';
            document.getElementById('modalContent').innerHTML = details;
            document.getElementById('detailsModal').style.display = 'block';
        }

        // Fonction pour fermer la modal
        function fermerModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        // Fermer la modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                fermerModal();
            }
        }
    </script>
</body>
</html>