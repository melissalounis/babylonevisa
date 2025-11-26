<?php
// DÉBUT ABSOLU DU FICHIER - AUCUN ESPACE, AUCUNE LIGNE VIDE AVANT
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['user_email'] ?? '';

// Traitement de l'annulation si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_demande'])) {
    $demande_id = $_POST['demande_id'] ?? '';
    
    if (!empty($demande_id)) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=babylone_service", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Vérifier que la demande appartient bien à l'utilisateur
            $sql_verif = "SELECT id FROM demandes_billets_avion WHERE id = :id AND user_email = :user_email";
            $stmt_verif = $pdo->prepare($sql_verif);
            $stmt_verif->execute([':id' => $demande_id, ':user_email' => $user_email]);
            $demande_existe = $stmt_verif->fetch();
            
            if ($demande_existe) {
                // Mettre à jour le statut de la demande (sans date_modification)
                $sql_update = "UPDATE demandes_billets_avion SET statut = 'annule' WHERE id = :id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([':id' => $demande_id]);
                
                $_SESSION['message_success'] = "La demande a été annulée avec succès.";
            } else {
                $_SESSION['message_error'] = "Demande non trouvée ou vous n'avez pas les droits pour l'annuler.";
            }
            
            // Rediriger pour éviter la resoumission du formulaire
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
            
        } catch (PDOException $e) {
            error_log("Erreur lors de l'annulation: " . $e->getMessage());
            $_SESSION['message_error'] = "Erreur lors de l'annulation : " . $e->getMessage();
        }
    }
}

// Connexion à la base de données pour afficher les demandes
$db_success = false;
$demandes = [];
$db_error = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=babylone_service", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_success = true;
    
    if ($db_success) {
        // Vérifier la structure de la table
        $columns = $pdo->query("SHOW COLUMNS FROM demandes_billets_avion")->fetchAll(PDO::FETCH_COLUMN);
        
        // Déterminer la colonne de tri - utiliser 'id' si 'date_creation' n'existe pas
        $order_column = in_array('date_creation', $columns) ? 'date_creation' : 'id';
        
        // Récupérer les demandes de l'utilisateur
        $sql = "SELECT * FROM demandes_billets_avion WHERE user_email = :user_email ORDER BY $order_column DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_email' => $user_email]);
        $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Demandes trouvées pour $user_email: " . count($demandes));
        
        // Pour chaque demande, récupérer les passagers
        foreach ($demandes as &$demande) {
            $sql_passagers = "SELECT * FROM passagers_billets WHERE demande_id = :demande_id";
            $stmt_passagers = $pdo->prepare($sql_passagers);
            $stmt_passagers->execute([':demande_id' => $demande['id']]);
            $demande['passagers'] = $stmt_passagers->fetchAll(PDO::FETCH_ASSOC);
            
            // Si date_creation n'existe pas, utiliser une date par défaut
            if (!isset($demande['date_creation'])) {
                $demande['date_creation'] = date('Y-m-d H:i:s');
            }
        }
    }
} catch (PDOException $e) {
    error_log("Erreur DB: " . $e->getMessage());
    $db_error = $e->getMessage();
    $db_success = false;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes de Billets d'Avion - Agence de Voyage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a5276;
            --secondary: #e74c3c;
            --accent: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #27ae60;
            --warning: #f39c12;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a5276 0%, #3498db 100%);
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
            font-size: 2.8rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .card {
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

        .user-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
        }

        .user-info i {
            color: var(--accent);
            margin-right: 10px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--accent));
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
            background: linear-gradient(135deg, var(--secondary), #c0392b);
        }

        .demande-item {
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: white;
            transition: all 0.3s ease;
        }

        .demande-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }

        .demande-numero {
            font-weight: bold;
            color: var(--primary);
            font-size: 1.2rem;
        }

        .statut {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }

        .statut-nouveau { background: #d4edda; color: #155724; }
        .statut-en-cours { background: #fff3cd; color: #856404; }
        .statut-confirme { background: #d1ecf1; color: #0c5460; }
        .statut-annule { background: #f8d7da; color: #721c24; }

        .demande-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-item i {
            color: var(--primary);
            width: 20px;
        }

        .passagers-section {
            margin-top: 20px;
        }

        .passager-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid var(--accent);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .date-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .debug-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ffc107;
            font-family: monospace;
            font-size: 0.9rem;
        }

        /* Styles pour les messages */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .message-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            padding: 0;
            border-radius: 15px;
            max-width: 800px;
            width: 90%;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
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
            background: var(--primary);
            color: white;
            padding: 20px 30px;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.4rem;
        }

        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            transform: scale(1.1);
        }

        .modal-body {
            padding: 30px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .detail-section {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f1f1;
        }

        .detail-section:last-child {
            border-bottom: none;
        }

        .detail-section h4 {
            color: var(--primary);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f8f9fa;
        }

        .detail-label {
            font-weight: 600;
            color: var(--dark);
        }

        .detail-value {
            color: #666;
            text-align: right;
        }

        .passager-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .passager-header {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .baggage-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .baggage-item {
            background: #e8f4fd;
            padding: 10px 15px;
            border-radius: 8px;
            border-left: 4px solid var(--accent);
        }

        /* Modal de confirmation d'annulation */
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1001;
        }
        
        .confirmation-content {
            background: white;
            margin: 100px auto;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        
        .confirmation-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
        }
        
        .btn-confirm {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }
        
        .btn-cancel {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        
        .btn:disabled:hover {
            transform: none !important;
            box-shadow: none !important;
        }

        @media (max-width: 768px) {
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .demande-info {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .modal-content {
                width: 95%;
                margin: 20px auto;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
            }
            
            .confirmation-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-plane"></i> Mes Demandes de Billets d'Avion</h1>
            <p>Consultez l'état de vos réservations en cours</p>
        </div>

        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> Historique des Demandes</h2>
            </div>
            <div class="card-body">
                <!-- Affichage des messages -->
                <?php if (isset($_SESSION['message_success'])): ?>
                    <div class="message message-success">
                        <i class="fas fa-check-circle"></i> 
                        <?php echo $_SESSION['message_success']; ?>
                        <?php unset($_SESSION['message_success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['message_error'])): ?>
                    <div class="message message-error">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <?php echo $_SESSION['message_error']; ?>
                        <?php unset($_SESSION['message_error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Informations utilisateur -->
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong>Connecté en tant que :</strong> 
                    <?php echo htmlspecialchars($user_email); ?> 
                    (ID: <?php echo $user_id; ?>)
                    <span style="float: right;">
                        <a href="reservation_billets_avion.php" class="btn" style="padding: 8px 15px;">
                            <i class="fas fa-plus"></i> Nouvelle demande
                        </a>
                    </span>
                </div>

                <!-- Informations de débogage -->
                <div class="debug-info">
                    <strong>Informations de débogage :</strong><br>
                    - Connexion DB : <?php echo $db_success ? '✅ Réussie' : '❌ Échec'; ?><br>
                    - Demandes trouvées : <?php echo count($demandes); ?><br>
                    - Email utilisateur : <?php echo htmlspecialchars($user_email); ?>
                </div>

                <?php if (!$db_success): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Erreur de connexion :</strong> <?php echo htmlspecialchars($db_error); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($demandes) && $db_success): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Aucune demande trouvée</h3>
                        <p>Vous n'avez pas encore effectué de demande de réservation de billet d'avion.</p>
                        <div style="margin-top: 20px; background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: left; max-width: 500px; margin-left: auto; margin-right: auto;">
                            <p><strong>Pour commencer :</strong></p>
                            <ul style="text-align: left; margin-left: 20px;">
                                <li>Cliquez sur "Nouvelle demande" pour créer votre première réservation</li>
                                <li>Remplissez tous les champs du formulaire</li>
                                <li>Validez votre demande</li>
                                <li>Revenez sur cette page pour voir vos demandes</li>
                            </ul>
                        </div>
                        <a href="reservation_billets_avion.php" class="btn" style="margin-top: 20px;">
                            <i class="fas fa-plus"></i> Faire une nouvelle demande
                        </a>
                    </div>
                <?php elseif (!empty($demandes)): ?>
                    <div class="demandes-list">
                        <?php foreach ($demandes as $demande): ?>
                            <div class="demande-item">
                                <div class="demande-header">
                                    <div class="demande-numero">
                                        <i class="fas fa-ticket-alt"></i>
                                        <?php echo htmlspecialchars($demande['numero_dossier']); ?>
                                    </div>
                                    <div class="statut statut-<?php echo $demande['statut'] ?? 'nouveau'; ?>">
                                        <?php 
                                        $statuts = [
                                            'nouveau' => 'Nouveau',
                                            'en_cours' => 'En cours',
                                            'confirme' => 'Confirmé',
                                            'annule' => 'Annulé'
                                        ];
                                        echo $statuts[$demande['statut'] ?? 'nouveau'] ?? ($demande['statut'] ?? 'Nouveau');
                                        ?>
                                    </div>
                                </div>

                                <div class="demande-info">
                                    <div class="info-item">
                                        <i class="fas fa-route"></i>
                                        <span><strong>Vol :</strong> 
                                            <?php echo htmlspecialchars($demande['ville_depart']); ?> → 
                                            <?php echo htmlspecialchars($demande['ville_arrivee']); ?>
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><strong>Départ :</strong> 
                                            <?php echo date('d/m/Y', strtotime($demande['date_depart'])); ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($demande['date_retour'])): ?>
                                    <div class="info-item">
                                        <i class="fas fa-calendar-check"></i>
                                        <span><strong>Retour :</strong> 
                                            <?php echo date('d/m/Y', strtotime($demande['date_retour'])); ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="info-item">
                                        <i class="fas fa-user-friends"></i>
                                        <span><strong>Passagers :</strong> 
                                            <?php echo count($demande['passagers'] ?? []); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="actions">
                                    <button class="btn" onclick="afficherDetails(<?php echo htmlspecialchars(json_encode($demande)); ?>)">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </button>
                                    <?php if (($demande['statut'] ?? '') == 'nouveau'): ?>
                                        <button class="btn btn-secondary" onclick="confirmerAnnulation(<?php echo $demande['id']; ?>, '<?php echo htmlspecialchars($demande['numero_dossier'] ?? $demande['id']); ?>')">
                                            <i class="fas fa-times"></i> Annuler
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal pour afficher les détails -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Détails de la Demande</h3>
                <button class="close-modal" onclick="fermerModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Le contenu sera injecté ici par JavaScript -->
            </div>
        </div>
    </div>

    <!-- Modal de confirmation d'annulation -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="confirmation-content">
            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #e74c3c; margin-bottom: 20px;"></i>
            <h3>Confirmer l'annulation</h3>
            <p id="confirmationText">Êtes-vous sûr de vouloir annuler cette demande ?</p>
            <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                <i class="fas fa-info-circle"></i> Cette action est irréversible.
            </p>
            
            <form id="annulationForm" method="POST" style="display: none;">
                <input type="hidden" name="annuler_demande" value="1">
                <input type="hidden" name="demande_id" id="demandeIdInput">
            </form>
            
            <div class="confirmation-buttons">
                <button id="confirmAnnulation" class="btn btn-confirm">
                    <i class="fas fa-check"></i> Oui, annuler
                </button>
                <button onclick="fermerConfirmation()" class="btn btn-cancel">
                    <i class="fas fa-times"></i> Non, garder
                </button>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        let annulationEnCours = false;

        function afficherDetails(demande) {
            const modal = document.getElementById('detailsModal');
            const modalBody = document.getElementById('modalBody');
            
            // Formater les données
            const civilites = {'M': 'Monsieur', 'Mme': 'Madame', 'Mlle': 'Mademoiselle', 'Enfant': 'Enfant'};
            const classes = {
                'economique': 'Économique',
                'premium_economique': 'Premium Économique', 
                'affaires': 'Affaires',
                'premiere': 'Première'
            };
            const typesVol = {
                'aller_simple': 'Aller simple',
                'aller_retour': 'Aller-retour'
            };
            const bagagesMain = {
                '1_piece': '1 pièce (bagage à main standard)',
                '2_pieces': '2 pièces (bagage + accessoire)'
            };
            const bagagesSoute = {
                'aucun': 'Aucun bagage en soute',
                '23kg': 'Bagage 23kg (standard)',
                '32kg': 'Bagage 32kg (supplémentaire)'
            };
            const statuts = {
                'nouveau': 'Nouveau',
                'en_cours': 'En cours de traitement',
                'confirme': 'Confirmé',
                'annule': 'Annulé'
            };

            // Construire le HTML des détails
            let html = `
                <div class="detail-section">
                    <h4><i class="fas fa-info-circle"></i> Informations Générales</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Numéro de dossier</span>
                            <span class="detail-value">${demande.numero_dossier}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Statut</span>
                            <span class="detail-value" style="color: ${getStatusColor(demande.statut)}">${statuts[demande.statut] || demande.statut}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date de création</span>
                            <span class="detail-value">${formatDate(demande.date_creation)}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h4><i class="fas fa-user"></i> Informations de Contact</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Email de contact</span>
                            <span class="detail-value">${demande.email_contact}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Téléphone</span>
                            <span class="detail-value">${demande.telephone_contact || 'Non renseigné'}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h4><i class="fas fa-route"></i> Détails du Vol</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Type de vol</span>
                            <span class="detail-value">${typesVol[demande.type_vol] || demande.type_vol}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Départ</span>
                            <span class="detail-value">${demande.ville_depart}, ${demande.pays_depart}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Arrivée</span>
                            <span class="detail-value">${demande.ville_arrivee}, ${demande.pays_arrivee}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Date de départ</span>
                            <span class="detail-value">${formatDate(demande.date_depart)}</span>
                        </div>
                        ${demande.date_retour ? `
                        <div class="detail-item">
                            <span class="detail-label">Date de retour</span>
                            <span class="detail-value">${formatDate(demande.date_retour)}</span>
                        </div>
                        ` : ''}
                        <div class="detail-item">
                            <span class="detail-label">Classe</span>
                            <span class="detail-value">${classes[demande.classe] || demande.classe}</span>
                        </div>
                        ${demande.compagnie_preferee ? `
                        <div class="detail-item">
                            <span class="detail-label">Compagnie préférée</span>
                            <span class="detail-value">${demande.compagnie_preferee}</span>
                        </div>
                        ` : ''}
                    </div>
                </div>

                <div class="detail-section">
                    <h4><i class="fas fa-suitcase"></i> Bagages</h4>
                    <div class="baggage-info">
                        <div class="baggage-item">
                            <strong>Cabine :</strong><br>
                            ${bagagesMain[demande.baggage_main] || demande.baggage_main}
                        </div>
                        <div class="baggage-item">
                            <strong>Soute :</strong><br>
                            ${bagagesSoute[demande.baggage_soute] || demande.baggage_soute}
                        </div>
                    </div>
                </div>
            `;

            // Ajouter les passagers
            if (demande.passagers && demande.passagers.length > 0) {
                html += `
                    <div class="detail-section">
                        <h4><i class="fas fa-users"></i> Passagers (${demande.passagers.length})</h4>
                `;
                
                demande.passagers.forEach((passager, index) => {
                    html += `
                        <div class="passager-details">
                            <div class="passager-header">
                                <i class="fas fa-user"></i>
                                Passager ${index + 1}: ${civilites[passager.civilite] || passager.civilite} ${passager.prenom} ${passager.nom}
                            </div>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span class="detail-label">Date de naissance</span>
                                    <span class="detail-value">${formatDate(passager.date_naissance)}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Numéro de passeport</span>
                                    <span class="detail-value">${passager.numero_passeport}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Expiration passeport</span>
                                    <span class="detail-value">${formatDate(passager.expiration_passeport)}</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Nationalité</span>
                                    <span class="detail-value">${passager.nationalite}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += `</div>`;
            }

            // Ajouter les commentaires
            if (demande.commentaires) {
                html += `
                    <div class="detail-section">
                        <h4><i class="fas fa-comment"></i> Commentaires</h4>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid var(--accent);">
                            ${demande.commentaires.replace(/\n/g, '<br>')}
                        </div>
                    </div>
                `;
            }

            modalBody.innerHTML = html;
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function fermerModal() {
            const modal = document.getElementById('detailsModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function confirmerAnnulation(demandeId, numeroDossier) {
            const modal = document.getElementById('confirmationModal');
            const confirmationText = document.getElementById('confirmationText');
            const demandeIdInput = document.getElementById('demandeIdInput');
            const confirmButton = document.getElementById('confirmAnnulation');
            
            confirmationText.textContent = `Êtes-vous sûr de vouloir annuler la demande ${numeroDossier} ?`;
            demandeIdInput.value = demandeId;
            
            // Réactiver le bouton si une annulation précédente était en cours
            confirmButton.disabled = false;
            confirmButton.innerHTML = '<i class="fas fa-check"></i> Oui, annuler';
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function fermerConfirmation() {
            const modal = document.getElementById('confirmationModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function getStatusColor(statut) {
            const colors = {
                'nouveau': '#27ae60',
                'en_cours': '#f39c12', 
                'confirme': '#3498db',
                'annule': '#e74c3c'
            };
            return colors[statut] || '#666';
        }

        function formatDate(dateString) {
            if (!dateString) return 'Non spécifié';
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Gestionnaire pour le bouton de confirmation d'annulation
            document.getElementById('confirmAnnulation').addEventListener('click', function() {
                if (!annulationEnCours) {
                    annulationEnCours = true;
                    
                    // Désactiver le bouton et afficher un indicateur de chargement
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Annulation en cours...';
                    
                    // Soumettre le formulaire
                    document.getElementById('annulationForm').submit();
                }
            });
            
            // Animation pour les cartes de demande
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

        // Fermer les modals en cliquant à l'extérieur
        window.onclick = function(event) {
            const detailsModal = document.getElementById('detailsModal');
            const confirmationModal = document.getElementById('confirmationModal');
            
            if (event.target === detailsModal) {
                fermerModal();
            }
            if (event.target === confirmationModal) {
                fermerConfirmation();
            }
        }

        // Fermer les modals avec la touche Échap
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                fermerModal();
                fermerConfirmation();
            }
        });
    </script>
</body>
</html>