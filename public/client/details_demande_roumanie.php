<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifier si l'ID de la demande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mes_demandes_roumanie.php");
    exit;
}

$demande_id = intval($_GET['id']);

// Paramètres de connexion

require_once __DIR__ . '../../../config.php';
// Initialiser les variables
$demande = null;
$error_message = '';

try {
    

    // Récupérer l'email de l'utilisateur
    $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user = $stmt_user->fetch();
    
    if ($user && isset($user['email'])) {
        $user_email = $user['email'];
        
        // Récupérer les détails de la demande
        $stmt = $pdo->prepare("
            SELECT * FROM demandes_etudes_roumanie 
            WHERE id = ? AND email = ?
        ");
        $stmt->execute([$demande_id, $user_email]);
        $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$demande) {
            $error_message = "Demande non trouvée ou vous n'avez pas l'autorisation de la consulter.";
        }
    } else {
        $error_message = "Utilisateur non trouvé.";
    }

} catch (PDOException $e) {
    $error_message = "Erreur de connexion à la base de données: " . $e->getMessage();
}

// Si la demande n'existe pas, rediriger
if (!$demande && empty($error_message)) {
    header("Location: mes_demandes_roumanie.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la Demande - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: rgba(0, 43, 127, 0.7);
            --primary-hover: rgba(9, 47, 122, 0.7);
            --secondary-color: #f8f9fa;
            --text-color: #333;
            --light-gray: #e9ecef;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        header {
            background: var(--primary-color);
            color: white;
            padding: 25px;
            text-align: center;
            position: relative;
        }

        header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);
        }

        h1 {
            margin: 0;
            font-size: 2rem;
        }

        .breadcrumb {
            background: var(--light-gray);
            padding: 15px 25px;
            border-bottom: 1px solid #ddd;
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .content {
            padding: 25px;
        }

        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light-gray);
            flex-wrap: wrap;
            gap: 15px;
        }

        .demande-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin: 0;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-nouveau { background: var(--info-color); color: white; }
        .status-en_cours { background: var(--warning-color); color: black; }
        .status-approuve { background: var(--success-color); color: white; }
        .status-refuse { background: var(--danger-color); color: white; }

        .info-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2rem;
            color: var(--primary-color);
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            font-size: 1.1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .info-card {
            background: var(--secondary-color);
            padding: 20px;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
        }

        .info-group {
            margin-bottom: 15px;
        }

        .info-group:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .info-value {
            font-size: 1rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .file-list {
            list-style: none;
            padding: 0;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: white;
            border-radius: var(--border-radius);
            margin-bottom: 8px;
            border: 1px solid #e0e0e0;
        }

        .file-name {
            flex: 1;
            font-size: 0.9rem;
        }

        .file-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }

        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid var(--light-gray);
            flex-wrap: wrap;
            gap: 15px;
        }

        .timeline {
            margin-top: 30px;
        }

        .timeline-item {
            display: flex;
            margin-bottom: 20px;
            position: relative;
        }

        .timeline-marker {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: var(--primary-color);
            margin-right: 15px;
            flex-shrink: 0;
            position: relative;
            z-index: 2;
        }

        .timeline-content {
            flex: 1;
            padding-bottom: 20px;
            border-left: 2px solid var(--light-gray);
            padding-left: 20px;
        }

        .timeline-date {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 5px;
        }

        .timeline-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .timeline-description {
            font-size: 0.9rem;
            color: #666;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            text-align: center;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .actions-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .file-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .file-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-file-alt"></i> Détails de la Demande</h1>
            <p>Informations complètes sur votre demande d'études en Roumanie</p>
        </header>

        <div class="breadcrumb">
            <a href="../../index.php"><i class="fas fa-home"></i> Dashboard</a> &gt; 
            <a href="../demandes_etude.php">Demandes d'étude</a> &gt; 
            <a href="index.php">Roumanie</a> &gt; 
            <a href="mes_demandes_roumanie.php">Mes demandes</a> &gt; Détails
        </div>

        <div class="content">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <h3>Erreur</h3>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                    <a href="mes_demandes_roumanie.php" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-arrow-left"></i> Retour aux demandes
                    </a>
                </div>
            <?php elseif ($demande): ?>
                <!-- En-tête de la demande -->
                <div class="demande-header">
                    <h2 class="demande-title"><?php echo htmlspecialchars($demande['specialite']); ?></h2>
                    <span class="status-badge status-<?php echo htmlspecialchars($demande['statut']); ?>">
                        <?php 
                        $statut_labels = [
                            'nouveau' => 'Nouvelle demande',
                            'en_cours' => 'En cours de traitement',
                            'approuve' => 'Demande approuvée',
                            'refuse' => 'Demande refusée'
                        ];
                        echo $statut_labels[$demande['statut']] ?? $demande['statut'];
                        ?>
                    </span>
                </div>

                <!-- Informations du programme -->
                <div class="info-section">
                    <h3 class="section-title"><i class="fas fa-book-open"></i> Programme d'études</h3>
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-group">
                                <div class="info-label">Spécialité</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['specialite']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Langue du programme</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['programme_langue']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Niveau d'études</div>
                                <div class="info-value">
                                    <?php 
                                    $niveau_labels = [
                                        'bac' => 'Baccalauréat',
                                        'l1' => 'Licence 1',
                                        'l2' => 'Licence 2',
                                        'l3' => 'Licence 3',
                                        'master' => 'Master'
                                    ];
                                    echo $niveau_labels[$demande['niveau_etude']] ?? $demande['niveau_etude'];
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($demande['certificat_type'])): ?>
                        <div class="info-card">
                            <div class="info-group">
                                <div class="info-label">Certificat de langue</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['certificat_type']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Score/Niveau</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['certificat_score']); ?></div>
                            </div>
                            <?php if (!empty($demande['certificat_file'])): ?>
                            <div class="info-group">
                                <div class="info-label">Fichier du certificat</div>
                                <div class="info-value">
                                    <button class="btn btn-outline" onclick="telechargerFichier('<?php echo htmlspecialchars($demande['certificat_file']); ?>', 'certificat')">
                                        <i class="fas fa-download"></i> Télécharger
                                    </button>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Informations personnelles -->
                <div class="info-section">
                    <h3 class="section-title"><i class="fas fa-user"></i> Informations personnelles</h3>
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-group">
                                <div class="info-label">Nom complet</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['nom_complet']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['email']); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Téléphone</div>
                                <div class="info-value"><?php echo htmlspecialchars($demande['telephone']); ?></div>
                            </div>
                        </div>
                        <div class="info-card">
                            <div class="info-group">
                                <div class="info-label">Date de soumission</div>
                                <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?></div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Dernière mise à jour</div>
                                <div class="info-value">
                                    <?php 
                                    if (!empty($demande['date_maj'])) {
                                        echo date('d/m/Y à H:i', strtotime($demande['date_maj']));
                                    } else {
                                        echo 'Non mise à jour';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="info-group">
                                <div class="info-label">Référence</div>
                                <div class="info-value">ROU-<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documents déposés -->
                <div class="info-section">
                    <h3 class="section-title"><i class="fas fa-file-upload"></i> Documents déposés</h3>
                    <div class="info-card">
                        <ul class="file-list">
                            <?php
                            $documents = [
                                'releve_2nde' => 'Relevé de notes 2nde',
                                'releve_1ere' => 'Relevé de notes 1ère',
                                'releve_terminale' => 'Relevé de notes Terminale',
                                'releve_bac' => 'Relevé de notes Bac',
                                'diplome_bac' => 'Diplôme Bac',
                                'certificat_scolarite' => 'Certificat de scolarité'
                            ];
                            
                            foreach ($documents as $key => $label): 
                                if (!empty($demande[$key])):
                            ?>
                                <li class="file-item">
                                    <span class="file-name"><?php echo htmlspecialchars($label); ?></span>
                                    <div class="file-actions">
                                        <button class="btn btn-outline" onclick="telechargerFichier('<?php echo htmlspecialchars($demande[$key]); ?>', '<?php echo htmlspecialchars($key); ?>')">
                                            <i class="fas fa-download"></i> Télécharger
                                        </button>
                                        <button class="btn btn-primary" onclick="previsualiserFichier('<?php echo htmlspecialchars($demande[$key]); ?>')">
                                            <i class="fas fa-eye"></i> Prévisualiser
                                        </button>
                                    </div>
                                </li>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </ul>
                    </div>
                </div>

                <!-- Timeline de la demande -->
                <div class="info-section">
                    <h3 class="section-title"><i class="fas fa-history"></i> Historique de la demande</h3>
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-date"><?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?></div>
                                <div class="timeline-title">Demande soumise</div>
                                <div class="timeline-description">Votre demande d'études en Roumanie a été créée avec succès.</div>
                            </div>
                        </div>
                        
                        <?php if ($demande['statut'] === 'en_cours'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-date">En attente</div>
                                <div class="timeline-title">En cours de traitement</div>
                                <div class="timeline-description">Votre demande est en cours d'analyse par notre équipe.</div>
                            </div>
                        </div>
                        <?php elseif ($demande['statut'] === 'approuve'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-date"><?php echo !empty($demande['date_maj']) ? date('d/m/Y à H:i', strtotime($demande['date_maj'])) : 'Date inconnue'; ?></div>
                                <div class="timeline-title">Demande approuvée</div>
                                <div class="timeline-description">Félicitations ! Votre demande a été approuvée.</div>
                            </div>
                        </div>
                        <?php elseif ($demande['statut'] === 'refuse'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="timeline-date"><?php echo !empty($demande['date_maj']) ? date('d/m/Y à H:i', strtotime($demande['date_maj'])) : 'Date inconnue'; ?></div>
                                <div class="timeline-title">Demande refusée</div>
                                <div class="timeline-description">Votre demande n'a pas pu être approuvée.</div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Actions -->
                <div class="actions-bar">
                    <div>
                        <a href="mes_demandes_roumanie.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Retour aux demandes
                        </a>
                    </div>
                    <div class="action-buttons">
                        <?php if ($demande['statut'] === 'nouveau'): ?>
                            <a href="modifier_demande_roumanie.php?id=<?php echo $demande['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Modifier la demande
                            </a>
                            <button class="btn btn-danger" onclick="supprimerDemande(<?php echo $demande['id']; ?>)">
                                <i class="fas fa-trash"></i> Supprimer
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-outline" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <h3>Demande non trouvée</h3>
                    <p>La demande que vous recherchez n'existe pas ou vous n'avez pas l'autorisation de la consulter.</p>
                    <a href="mes_demandes_roumanie.php" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fas fa-arrow-left"></i> Retour aux demandes
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function telechargerFichier(nomFichier, type) {
            const url = `telecharger_fichier.php?file=${encodeURIComponent(nomFichier)}&type=${type}`;
            window.open(url, '_blank');
        }

        function previsualiserFichier(nomFichier) {
            const url = `previsualiser_fichier.php?file=${encodeURIComponent(nomFichier)}`;
            window.open(url, '_blank', 'width=800,height=600');
        }

        function supprimerDemande(demandeId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est irréversible.')) {
                window.location.href = `supprimer_demande_roumanie.php?id=${demandeId}`;
            }
        }

        // Animation d'entrée
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.info-card, .timeline-item');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>