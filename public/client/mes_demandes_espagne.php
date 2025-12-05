<?php
session_start();
// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Paramètres de connexion
require_once __DIR__ . '../../../config.php';
// Initialiser les variables
$demandes_espagne = [];
$error_message = null;
$stats = ['total' => 0, 'nouveau' => 0, 'en_traitement' => 0, 'approuve' => 0, 'refuse' => 0];
$user_info = ['email' => 'Utilisateur', 'nom' => 'Utilisateur'];

try {
    // Connexion à la base de données
  

    // Récupérer les informations de l'utilisateur depuis la table users
    $user_id = $_SESSION['user_id'];
    
    // D'abord, vérifier la structure de la table users
    $stmt_columns = $pdo->query("DESCRIBE users");
    $columns = $stmt_columns->fetchAll(PDO::FETCH_COLUMN);
    
    // Déterminer les colonnes disponibles
    $available_columns = [];
    if (in_array('email', $columns)) $available_columns[] = 'email';
    if (in_array('nom', $columns)) $available_columns[] = 'nom';
    if (in_array('prenom', $columns)) $available_columns[] = 'prenom';
    if (in_array('username', $columns)) $available_columns[] = 'username';
    if (in_array('name', $columns)) $available_columns[] = 'name';
    if (in_array('full_name', $columns)) $available_columns[] = 'full_name';
    
    if (empty($available_columns)) {
        // Si aucune colonne n'est trouvée, utiliser une valeur par défaut
        $user_info['email'] = 'utilisateur@example.com';
        $user_info['nom'] = 'Utilisateur';
    } else {
        // Construire la requête avec les colonnes disponibles
        $columns_sql = implode(', ', $available_columns);
        $stmt_user = $pdo->prepare("SELECT $columns_sql FROM users WHERE id = ?");
        $stmt_user->execute([$user_id]);
        $user_data = $stmt_user->fetch();
        
        if ($user_data) {
            // Assigner les valeurs disponibles
            $user_info['email'] = $user_data['email'] ?? 
                                 $user_data['username'] ?? 
                                 'utilisateur@example.com';
            
            // Trouver un nom d'utilisateur
            $user_info['nom'] = $user_data['nom'] ?? 
                               $user_data['prenom'] ?? 
                               $user_data['name'] ?? 
                               $user_data['full_name'] ?? 
                               $user_data['username'] ?? 
                               'Utilisateur';
        }
    }

    // Vérifier si la table Espagne existe
    $table_espagne = $pdo->query("SHOW TABLES LIKE 'demandes_etudes_espagne'")->rowCount();
    
    if ($table_espagne == 0) {
        $error_message = "La table des demandes Espagne n'existe pas encore.";
    } else {
        // Récupérer les demandes Espagne pour cet utilisateur par son email
        $stmt_espagne = $pdo->prepare("SELECT * FROM demandes_etudes_espagne WHERE email = ? ORDER BY date_soumission DESC");
        $stmt_espagne->execute([$user_info['email']]);
        $demandes_espagne = $stmt_espagne->fetchAll();

        // Calculer les statistiques
        $stats['total'] = count($demandes_espagne);
        
        foreach ($demandes_espagne as $demande) {
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

// Fonction pour traduire le type de service
function traduireTypeService($type) {
    $traductions = [
        'admission' => 'Demande d\'admission',
        'visa' => 'Demande de visa',
        'complete' => 'Procédure complète'
    ];
    return $traductions[$type] ?? ucfirst($type);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes demandes Espagne - Espace client</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* [Le CSS reste exactement le même que précédemment] */
        :root {
            --primary-color: #c60b1e;
            --secondary-color: #ff6b35;
            --espagne-color: #c60b1e;
            --light-bg: #f8fafc;
            --dark-text: #2d3748;
            --white: #ffffff;
            --light-gray: #e2e8f0;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --error-color: #ef4444;
            --transition: all 0.3s ease;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark-text);
            line-height: 1.6;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .dashboard-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .dashboard-header h1 i {
            margin-right: 15px;
        }

        .user-info {
            opacity: 0.9;
            margin-top: 10px;
        }

        .error-message {
            background: #fee;
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
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .stat-icon {
            font-size: 40px;
            margin-right: 15px;
        }

        .stat-nouveau .stat-icon { color: #f59e0b; }
        .stat-traitement .stat-icon { color: #3b82f6; }
        .stat-approuve .stat-icon { color: #10b981; }
        .stat-refuse .stat-icon { color: #ef4444; }

        .stat-info h3 {
            font-size: 2em;
            margin: 0;
            color: #2c3e50;
        }

        .stat-info p {
            margin: 5px 0 0;
            color: #7f8c8d;
            font-weight: 500;
        }

        .dashboard-actions {
            margin-bottom: 40px;
        }

        .dashboard-actions h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .dashboard-actions h2 i {
            margin-right: 10px;
            color: var(--primary-color);
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
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            text-decoration: none;
            color: inherit;
            transition: var(--transition);
            display: block;
            border: 2px solid transparent;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            border-color: var(--primary-color);
            text-decoration: none;
            color: inherit;
        }

        .action-icon {
            font-size: 40px;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .action-card h3 {
            color: #2c3e50;
            margin: 0 0 10px;
            font-size: 1.2em;
        }

        .action-card p {
            color: #7f8c8d;
            margin: 0;
            font-size: 0.9em;
        }

        .demandes-section {
            margin-bottom: 40px;
        }

        .demandes-section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5em;
        }

        .demandes-section h2 i {
            margin-right: 10px;
            color: var(--primary-color);
        }

        .demandes-list {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
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
            color: #2c3e50;
        }

        .demande-time {
            font-size: 0.85em;
            color: #95a5a6;
        }

        .demande-time i {
            margin-right: 5px;
        }

        .statut-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .statut-nouveau { background: #fff3cd; color: #856404; }
        .statut-en_traitement { background: #dbeafe; color: #1e40af; }
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
            font-size: 0.8em;
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
            background: #9ca3af;
            cursor: not-allowed;
        }

        .no-demande {
            display: flex;
            padding: 30px;
            text-align: center;
            justify-content: center;
            opacity: 0.7;
            align-items: center;
        }

        .no-demande .demande-icon {
            font-size: 40px;
            color: #bdc3c7;
        }

        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            background: var(--light-bg);
            border-radius: 4px;
            font-size: 0.7em;
            margin-left: 8px;
            color: var(--dark-text);
        }

        .type-badge.espagne {
            background: #ffebee;
            color: var(--espagne-color);
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
                font-size: 2em;
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
            <h1><i class="fas fa-graduation-cap"></i> Mes demandes d'études en Espagne</h1>
            <p>Consultez l'état de toutes vos demandes d'études pour l'Espagne</p>
            <div class="user-info">
                <small><i class="fas fa-user-circle"></i> Connecté en tant que : <?= htmlspecialchars($user_info['email']) ?></small>
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
            <div class="stat-card stat-nouveau">
                <div class="stat-icon"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3><?= $stats['nouveau'] ?></h3>
                    <p>Nouvelles demandes</p>
                </div>
            </div>
            <div class="stat-card stat-traitement">
                <div class="stat-icon"><i class="fas fa-cog"></i></div>
                <div class="stat-info">
                    <h3><?= $stats['en_traitement'] ?></h3>
                    <p>En traitement</p>
                </div>
            </div>
            <div class="stat-card stat-approuve">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-info">
                    <h3><?= $stats['approuve'] ?></h3>
                    <p>Approuvées</p>
                </div>
            </div>
            <div class="stat-card stat-refuse">
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
                <a href="nouvelle_demande_espagne.php" class="action-card">
                    <div class="action-icon"><i class="fas fa-plus-circle"></i></div>
                    <h3>Nouvelle demande</h3>
                    <p>Soumettre une nouvelle demande d'études en Espagne</p>
                </a>
                
                <a href="index.php" class="action-card">
                    <div class="action-icon"><i class="fas fa-tachometer-alt"></i></div>
                    <h3>Tableau de bord</h3>
                    <p>Retour à l'accueil principal</p>
                </a>
            </div>
        </div>

        <!-- Demandes Espagne -->
        <div class="demandes-section">
            <h2><i class="fas fa-graduation-cap"></i> Mes demandes d'études en Espagne (<?= count($demandes_espagne) ?>)</h2>
            <div class="demandes-list">
                <?php if (!empty($demandes_espagne)): ?>
                    <?php foreach ($demandes_espagne as $demande): ?>
                        <?php 
                        $demande_id = htmlspecialchars($demande['id']);
                        $statut = $demande['statut'] ?? 'nouveau';
                        $date_soumission = formatDateTime($demande['date_soumission'] ?? '');
                        $type_service = traduireTypeService($demande['type_service'] ?? 'admission');
                        $niveau_etude = htmlspecialchars($demande['niveau_etude'] ?? 'Non spécifié');
                        $universite = htmlspecialchars($demande['universite_souhaitee'] ?? 'Non spécifiée');
                        $programme = htmlspecialchars($demande['programme_etude'] ?? 'Non spécifié');
                        
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
                                    <span class="type-badge espagne">Études Espagne</span>
                                </p>
                                <p>
                                    <strong>Type:</strong> <?= $type_service ?> |
                                    <strong>Niveau:</strong> <?= $niveau_etude ?> |
                                    <strong>Université:</strong> <?= $universite ?> |
                                    <strong>Statut:</strong> 
                                    <span class="statut-badge <?= $classe_statut ?>"><?= traduireStatut($statut) ?></span>
                                </p>
                                <p>
                                    <strong>Programme:</strong> <?= $programme ?>
                                </p>
                                <span class="demande-time">
                                    <i class="fas fa-calendar-alt"></i> Soumise le <?= $date_soumission ?>
                                </span>
                            </div>
                            <div class="demande-actions">
                                <button class="btn-action" onclick="afficherDetailsEspagne(<?= $demande_id ?>)">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                                
                                <?php if ($statut === 'nouveau'): ?>
                                    <a href="modifier_demande_espagne.php?id=<?= $demande_id ?>" class="btn-action">
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
                            <p>Aucune demande d'études en Espagne trouvée pour votre compte.</p>
                            <span class="demande-time"><i class="fas fa-info-circle"></i> Créez votre première demande d'études en Espagne</span>
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
        // Fonction pour afficher les détails Espagne
        function afficherDetailsEspagne(id) {
            const details = `
                <div class="demande-details-modal">
                    <h3><i class="fas fa-graduation-cap"></i> Détails de la demande #${id}</h3>
                    <div style="display: grid; gap: 15px; margin-top: 20px;">
                        <div>
                            <strong><i class="fas fa-info-circle"></i> Informations générales</strong>
                            <p>Référence: ES-${id}<br>Type de service: Demande d'admission<br>Statut: Nouvelle demande</p>
                        </div>
                        <div>
                            <strong><i class="fas fa-calendar"></i> Dates</strong>
                            <p>Date de soumission: ${new Date().toLocaleDateString('fr-FR')}</p>
                        </div>
                        <div>
                            <strong><i class="fas fa-university"></i> Informations académiques</strong>
                            <p>Niveau d'étude: Licence<br>Université souhaitée: Université de Barcelone<br>Programme: Informatique</p>
                        </div>
                        <div>
                            <strong><i class="fas fa-file-upload"></i> Documents déposés</strong>
                            <p>Relevés de notes, Diplômes, Lettre de motivation, CV...</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modalTitle').textContent = 'Détails de la demande Espagne';
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