<?php
// DÉBUT ABSOLU DU FICHIER - AUCUN ESPACE, AUCUNE LIGNE VIDE AVANT
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? 'Non spécifié';

// Message de succès
$success_message = '';
if (isset($_GET['success']) && $_GET['success'] == '1' && isset($_GET['dossier'])) {
    $success_message = "Votre demande " . htmlspecialchars($_GET['dossier']) . " a été enregistrée avec succès !";
}

// Connexion à la base de données
$db_success = false;
$demandes = [];
$conn = null;

try {
    require_once __DIR__ . '../../../config.php';
    
    // Vérifier si la connexion est établie
    if ($conn && $conn->connect_error === null) {
        $db_success = true;
        
        // Récupérer les demandes de regroupement familial de l'utilisateur
        $sql = "SELECT * FROM demandes_regroupement_familial WHERE user_id = ? ORDER BY date_creation DESC";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                $demandes = $result->fetch_all(MYSQLI_ASSOC);
            }
            
            $stmt->close();
        }
    }
} catch (Exception $e) {
    $db_success = false;
    error_log("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes de Regroupement Familial - France</title>
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
            --warning-orange: #ff9800;
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
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--white);
            padding: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            padding: 25px 30px;
            text-align: center;
            position: relative;
        }
        
        header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        header p {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .flag-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--accent-red) 33%, var(--white) 33%, var(--white) 66%, var(--accent-red) 66%);
        }
        
        .content {
            padding: 30px;
        }
        
        .user-info {
            background: var(--light-blue);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid var(--primary-blue);
        }
        
        .user-info-content {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info i {
            color: var(--primary-blue);
            font-size: 1.5rem;
        }
        
        .user-email {
            font-weight: bold;
            color: var(--primary-blue);
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(to right, var(--primary-blue), #0066cc);
            color: var(--white);
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 85, 184, 0.3);
        }
        
        .btn:hover {
            background: linear-gradient(to right, #004a9e, #0055b8);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0, 85, 184, 0.4);
        }
        
        .btn-success {
            background: linear-gradient(to right, var(--success-green), #34c759);
        }
        
        .btn-success:hover {
            background: linear-gradient(to right, #218838, #28a745);
        }
        
        .success-message {
            background: var(--success-green);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message i {
            font-size: 1.5rem;
            margin-right: 10px;
        }
        
        .demande-item {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            background: var(--white);
            transition: var(--transition);
        }
        
        .demande-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-blue);
        }
        
        .demande-numero {
            font-weight: bold;
            color: var(--primary-blue);
            font-size: 1.3rem;
        }
        
        .statut {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            border: 1px solid;
        }
        
        .statut-nouveau { background: #d4edda; color: #155724; border-color: #c3e6cb; }
        .statut-en-cours { background: #fff3cd; color: #856404; border-color: #ffeaa7; }
        .statut-confirme { background: #d1ecf1; color: #0c5460; border-color: #b8daff; }
        .statut-refuse { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        .statut-complet { background: #e2e3e5; color: #383d41; border-color: #d6d8db; }
        
        .demande-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: var(--light-gray);
            border-radius: 8px;
        }
        
        .info-item i {
            color: var(--primary-blue);
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .welcome-message {
            text-align: center;
            background: rgba(0, 85, 184, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            color: var(--primary-blue);
            border: 1px solid var(--border-color);
        }
        
        .debug-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
        }
        
        @media (max-width: 768px) {
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .demande-info {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .user-info {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fa-solid fa-people-group"></i> Mes Demandes de Regroupement Familial</h1>
            <p>Suivez l'état d'avancement de vos dossiers de regroupement familial</p>
            <div class="flag-decoration"></div>
        </header>
        
        <div class="content">
            <!-- Message de bienvenue -->
            <div class="welcome-message">
                <h3><i class="fa-solid fa-passport"></i> Espace Personnel - Regroupement Familial</h3>
                <p>Consultez l'état de vos demandes et téléchargez vos documents</p>
            </div>

            <!-- Message de succès -->
            <?php if (!empty($success_message)): ?>
                <div class="success-message">
                    <i class="fa-solid fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- Informations utilisateur -->
            <div class="user-info">
                <div class="user-info-content">
                    <i class="fa-solid fa-user-circle"></i>
                    <div>
                        <strong>Connecté en tant que :</strong> 
                        <span class="user-email"><?php echo htmlspecialchars($user_email); ?></span>
                        <div style="font-size: 0.9rem; color: var(--text-light); margin-top: 5px;">
                            ID Utilisateur: <?php echo $user_id; ?>
                        </div>
                    </div>
                </div>
                <div>
                    <a href="visa_regroupement_familial.php" class="btn btn-success">
                        <i class="fa-solid fa-plus"></i> Nouvelle demande
                    </a>
                </div>
            </div>

            <!-- Informations de débogage -->
            <?php if (!$db_success): ?>
                <div class="debug-info">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <strong>Attention :</strong> Impossible de se connecter à la base de données. 
                    Les données affichées sont limitées.
                </div>
            <?php else: ?>
                <div class="debug-info">
                    <i class="fa-solid fa-database"></i>
                    <strong>Base de données :</strong> Connecté avec succès | 
                    <strong>Demandes trouvées :</strong> <?php echo count($demandes); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($demandes)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    <h3>Aucune demande trouvée</h3>
                    <p>Vous n'avez pas encore effectué de demande de regroupement familial.</p>
                    <p style="color: var(--accent-red); margin-top: 10px;">
                        <i class="fa-solid fa-lightbulb"></i>
                        Assurez-vous d'avoir soumis le formulaire et que la table "demandes_regroupement_familial" existe.
                    </p>
                    <a href="visa_regroupement_familial.php" class="btn" style="margin-top: 20px;">
                        <i class="fa-solid fa-plus"></i> Faire une nouvelle demande
                    </a>
                </div>
            <?php else: ?>
                <div class="demandes-list">
                    <?php foreach ($demandes as $demande): ?>
                        <div class="demande-item">
                            <div class="demande-header">
                                <div class="demande-numero">
                                    <i class="fa-solid fa-file-contract"></i>
                                    <?php echo htmlspecialchars($demande['numero_dossier'] ?? 'N/A'); ?>
                                </div>
                                <div class="statut statut-<?php echo $demande['statut'] ?? 'nouveau'; ?>">
                                    <?php 
                                    $statuts = [
                                        'nouveau' => '🆕 Nouveau',
                                        'en_cours' => '⏳ En cours',
                                        'confirme' => '✅ Confirmé',
                                        'refuse' => '❌ Refusé',
                                        'complet' => '📋 Complet'
                                    ];
                                    echo $statuts[$demande['statut'] ?? 'nouveau'] ?? $demande['statut'] ?? 'Nouveau';
                                    ?>
                                </div>
                            </div>

                            <div class="demande-info">
                                <div class="info-item">
                                    <i class="fa-solid fa-user"></i>
                                    <span><strong>Demandeur :</strong> 
                                        <?php echo htmlspecialchars($demande['nom_complet'] ?? 'Non spécifié'); ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <i class="fa-solid fa-flag"></i>
                                    <span><strong>Nationalité :</strong> 
                                        <?php echo htmlspecialchars($demande['nationalite'] ?? 'Non spécifiée'); ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <i class="fa-solid fa-people-arrows"></i>
                                    <span><strong>Lien familial :</strong> 
                                        <?php 
                                        $liens = [
                                            'conjoint' => 'Conjoint(e)',
                                            'enfant' => 'Enfant',
                                            'parent' => 'Parent',
                                            'autre' => 'Autre'
                                        ];
                                        echo $liens[$demande['lien_parente'] ?? 'autre'] ?? $demande['lien_parente'] ?? 'Non spécifié';
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <i class="fa-solid fa-calendar-day"></i>
                                    <span><strong>Date de création :</strong> 
                                        <?php echo isset($demande['date_creation']) ? date('d/m/Y à H:i', strtotime($demande['date_creation'])) : 'Date inconnue'; ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Informations supplémentaires -->
                            <?php if (!empty($demande['nom_famille'])): ?>
                            <div class="info-item" style="margin-bottom: 15px;">
                                <i class="fa-solid fa-house-user"></i>
                                <span><strong>Membre de famille en France :</strong> 
                                    <?php echo htmlspecialchars($demande['nom_famille']); ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($demande['adresse_famille'])): ?>
                            <div class="info-item" style="margin-bottom: 15px;">
                                <i class="fa-solid fa-location-dot"></i>
                                <span><strong>Adresse en France :</strong> 
                                    <?php echo htmlspecialchars($demande['adresse_famille']); ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($demande['email'])): ?>
                            <div class="info-item" style="margin-bottom: 15px;">
                                <i class="fa-solid fa-envelope"></i>
                                <span><strong>Email :</strong> 
                                    <?php echo htmlspecialchars($demande['email']); ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($demande['telephone'])): ?>
                            <div class="info-item" style="margin-bottom: 15px;">
                                <i class="fa-solid fa-phone"></i>
                                <span><strong>Téléphone :</strong> 
                                    <?php echo htmlspecialchars($demande['telephone']); ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($demande['commentaire'])): ?>
                            <div class="info-item" style="margin-bottom: 15px;">
                                <i class="fa-solid fa-comment"></i>
                                <span><strong>Commentaire :</strong> 
                                    <?php echo htmlspecialchars($demande['commentaire']); ?>
                                </span>
                            </div>
                            <?php endif; ?>

                            <div class="actions">
                                <button class="btn" onclick="afficherDetails(<?php echo $demande['id'] ?? 0; ?>)">
                                    <i class="fa-solid fa-eye"></i> Voir détails complets
                                </button>
                                <button class="btn" onclick="contacterAssistance('<?php echo htmlspecialchars($demande['numero_dossier'] ?? 'N/A'); ?>')">
                                    <i class="fa-solid fa-envelope"></i> Contacter l'assistance
                                </button>
                                <?php if (($demande['statut'] ?? '') == 'nouveau'): ?>
                                    <button class="btn" style="background: var(--accent-red);" onclick="annulerDemande(<?php echo $demande['id'] ?? 0; ?>)">
                                        <i class="fa-solid fa-times"></i> Annuler la demande
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function afficherDetails(demandeId) {
            alert('Détails complets de la demande ID: ' + demandeId + '\nCette fonctionnalité sera implémentée prochainement.');
        }

        function contacterAssistance(numeroDossier) {
            const sujet = `Demande Regroupement Familial - ${numeroDossier}`;
            const corps = `Bonjour,\n\nJe souhaite obtenir des informations concernant ma demande de regroupement familial (${numeroDossier}).\n\nCordialement`;
            window.location.href = `mailto:assistance@babylone-service.fr?subject=${encodeURIComponent(sujet)}&body=${encodeURIComponent(corps)}`;
        }

        function annulerDemande(demandeId) {
            if (confirm('Êtes-vous sûr de vouloir annuler cette demande ? Cette action est irréversible.')) {
                alert('Demande ' + demandeId + ' annulée.\nCette fonctionnalité sera implémentée prochainement.');
            }
        }

        // Animation d'apparition des demandes
        document.addEventListener('DOMContentLoaded', function() {
            const demandes = document.querySelectorAll('.demande-item');
            demandes.forEach((demande, index) => {
                demande.style.opacity = '0';
                demande.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    demande.style.transition = 'all 0.5s ease';
                    demande.style.opacity = '1';
                    demande.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>