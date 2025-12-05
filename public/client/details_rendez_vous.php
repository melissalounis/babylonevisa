<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Vérifier si l'ID du rendez-vous est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mes_rendez_vous.php');
    exit;
}

$rendez_vous_id = $_GET['id'];

// Connexion à la base de données
require_once __DIR__ . '../../../config.php';

try {
   

    // D'abord, récupérer l'email de l'utilisateur
    $user_stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: login.php');
        exit;
    }

    // Récupérer les colonnes existantes de la table rendez_vous
    $columns_stmt = $pdo->prepare("SHOW COLUMNS FROM rendez_vous");
    $columns_stmt->execute();
    $columns = $columns_stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Construire la requête dynamiquement avec seulement les colonnes existantes
    $select_fields = [
        "id",
        "reference", 
        "pays_destination",
        "type_demande",
        "type_client",
        "nom",
        "prenom", 
        "email",
        "date_arrivee",
        "date_depart",
        "statut",
        "DATE_FORMAT(date_creation, '%d/%m/%Y à %H:%i') as date_creation_formatted",
        "DATE_FORMAT(date_arrivee, '%d/%m/%Y') as date_arrivee_formatted",
        "DATE_FORMAT(date_depart, '%d/%m/%Y') as date_depart_formatted"
    ];
    
    // Ajouter les colonnes optionnelles si elles existent
    if (in_array('date_rendez_vous', $columns)) {
        $select_fields[] = "DATE_FORMAT(date_rendez_vous, '%d/%m/%Y à %H:%i') as date_rendez_vous_formatted";
        $select_fields[] = "date_rendez_vous";
    }
    
    if (in_array('notes', $columns)) {
        $select_fields[] = "notes";
    }
    
    if (in_array('nombre_personnes', $columns)) {
        $select_fields[] = "nombre_personnes";
    }
    
    if (in_array('telephone', $columns)) {
        $select_fields[] = "telephone";
    }
    
    $select_sql = implode(", ", $select_fields);
    
    // Récupérer le rendez-vous
    $stmt = $pdo->prepare("
        SELECT $select_sql
        FROM rendez_vous 
        WHERE id = ? AND email = ?
    ");
    
    $stmt->execute([$rendez_vous_id, $user['email']]);
    $rendez_vous = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rendez_vous) {
        $error_message = "Rendez-vous non trouvé ou accès refusé.";
    }

} catch (PDOException $e) {
    $error_message = "Erreur de connexion à la base de données: " . $e->getMessage();
    $rendez_vous = null;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du Rendez-vous - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #003366, #0055aa);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .breadcrumb {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .breadcrumb a {
            color: #003366;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .details-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #003366;
        }

        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background: #d1edff;
            color: #004085;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .info-section {
            margin-bottom: 10px;
        }

        .info-section h3 {
            color: #003366;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e8f2ff;
            font-size: 1.2rem;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 180px;
        }

        .info-value {
            color: #333;
            text-align: right;
            flex: 1;
        }

        .reference {
            background: #e8f2ff;
            color: #003366;
            padding: 6px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-weight: 600;
            font-size: 1rem;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-primary {
            background: #003366;
            color: white;
        }

        .btn-primary:hover {
            background: #0055aa;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #003366;
            color: #003366;
        }

        .btn-outline:hover {
            background: #003366;
            color: white;
        }

        .btn-danger {
            background: transparent;
            border: 2px solid #dc3545;
            color: #dc3545;
        }

        .btn-danger:hover {
            background: #dc3545;
            color: white;
        }

        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #f0f9ff;
            border-color: #28a745;
            color: #155724;
        }

        .alert-error {
            background: #fef2f2;
            border-color: #dc3545;
            color: #721c24;
        }

        .document-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .document-list {
            list-style: none;
            margin-top: 15px;
        }

        .document-list li {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .document-list li:last-child {
            border-bottom: none;
        }

        .document-list i {
            color: #28a745;
        }

        @media (max-width: 768px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .info-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
            
            .info-value {
                text-align: left;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-file-alt"></i> Détails du Rendez-vous</h1>
            <p>Informations complètes sur votre demande de visa</p>
        </header>
        
        <div class="breadcrumb">
            <a href="mes_rendez_vous.php"><i class="fas fa-arrow-left"></i> Retour à mes rendez-vous</a>
        </div>

        <!-- Message de succès -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Message d'erreur -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($rendez_vous): ?>
            <div class="details-card">
                <div class="card-header">
                    <div class="card-title">
                        Rendez-vous <span class="reference"><?php echo htmlspecialchars($rendez_vous['reference']); ?></span>
                    </div>
                    <div class="status-badge status-<?php echo $rendez_vous['statut']; ?>">
                        <?php 
                            switch($rendez_vous['statut']) {
                                case 'en_attente': echo 'En attente'; break;
                                case 'confirme': echo 'Confirmé'; break;
                                case 'annule': echo 'Annulé'; break;
                                default: echo $rendez_vous['statut'];
                            }
                        ?>
                    </div>
                </div>

                <div class="info-grid">
                    <!-- Informations personnelles -->
                    <div class="info-section">
                        <h3><i class="fas fa-user"></i> Informations Personnelles</h3>
                        <div class="info-item">
                            <span class="info-label">Nom complet :</span>
                            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['prenom'] . ' ' . $rendez_vous['nom']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email :</span>
                            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Type de client :</span>
                            <span class="info-value">
                                <?php 
                                    switch($rendez_vous['type_client']) {
                                        case 'individuel': echo 'Individuel'; break;
                                        case 'famille': echo 'Famille'; break;
                                        case 'groupe': echo 'Groupe'; break;
                                        default: echo $rendez_vous['type_client'];
                                    }
                                ?>
                            </span>
                        </div>
                        <?php if (isset($rendez_vous['telephone']) && !empty($rendez_vous['telephone'])): ?>
                        <div class="info-item">
                            <span class="info-label">Téléphone :</span>
                            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['telephone']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (isset($rendez_vous['nombre_personnes'])): ?>
                        <div class="info-item">
                            <span class="info-label">Nombre de personnes :</span>
                            <span class="info-value"><?php echo $rendez_vous['nombre_personnes']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Informations du voyage -->
                    <div class="info-section">
                        <h3><i class="fas fa-plane"></i> Informations du Voyage</h3>
                        <div class="info-item">
                            <span class="info-label">Pays de destination :</span>
                            <span class="info-value"><?php echo htmlspecialchars($rendez_vous['pays_destination']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Type de demande :</span>
                            <span class="info-value">
                                <?php echo $rendez_vous['type_demande'] === 'premiere_demande' ? 'Première demande' : 'Renouvellement'; ?>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date d'arrivée :</span>
                            <span class="info-value"><?php echo $rendez_vous['date_arrivee_formatted']; ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Date de départ :</span>
                            <span class="info-value"><?php echo $rendez_vous['date_depart_formatted']; ?></span>
                        </div>
                        <?php if (isset($rendez_vous['date_rendez_vous_formatted']) && !empty($rendez_vous['date_rendez_vous_formatted'])): ?>
                        <div class="info-item">
                            <span class="info-label">Date du rendez-vous :</span>
                            <span class="info-value"><?php echo $rendez_vous['date_rendez_vous_formatted']; ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Informations supplémentaires -->
                    <div class="info-section">
                        <h3><i class="fas fa-info-circle"></i> Informations Supplémentaires</h3>
                        <div class="info-item">
                            <span class="info-label">Date de création :</span>
                            <span class="info-value"><?php echo $rendez_vous['date_creation_formatted']; ?></span>
                        </div>
                        <?php if (isset($rendez_vous['notes']) && !empty($rendez_vous['notes'])): ?>
                        <div class="info-item">
                            <span class="info-label">Notes :</span>
                            <span class="info-value"><?php echo nl2br(htmlspecialchars($rendez_vous['notes'])); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Documents requis -->
                <div class="document-section">
                    <h3><i class="fas fa-file-pdf"></i> Documents Requis</h3>
                    <p>Liste des documents à apporter pour votre rendez-vous :</p>
                    <ul class="document-list">
                        <li><i class="fas fa-check-circle"></i> Passeport valide</li>
                        <li><i class="fas fa-check-circle"></i> Formulaire de demande complété</li>
                        <li><i class="fas fa-check-circle"></i> Photos d'identité récentes</li>
                        <li><i class="fas fa-check-circle"></i> Justificatifs de réservation</li>
                        <li><i class="fas fa-check-circle"></i> Assurance voyage</li>
                        <li><i class="fas fa-check-circle"></i> Justificatifs financiers</li>
                        <?php if ($rendez_vous['type_demande'] === 'renouvellement'): ?>
                        <li><i class="fas fa-check-circle"></i> Ancien visa</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="actions">
                    <a href="mes_rendez_vous.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                    
                    <?php if ($rendez_vous['statut'] === 'en_attente'): ?>
                        <a href="modifier_rendez_vous.php?id=<?php echo $rendez_vous['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                        
                        <button onclick="annulerRendezVous(<?php echo $rendez_vous['id']; ?>)" 
                                class="btn btn-danger">
                            <i class="fas fa-times"></i> Annuler le rendez-vous
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($rendez_vous['statut'] === 'confirme'): ?>
                        <button onclick="imprimerDetails()" class="btn btn-primary">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Annuler un rendez-vous
        function annulerRendezVous(rendezVousId) {
            if (confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ? Cette action est irréversible.')) {
                // Envoyer la requête d'annulation
                fetch('annuler_rendez_vous.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + rendezVousId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Rendez-vous annulé avec succès');
                        location.reload();
                    } else {
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Erreur lors de l\'annulation');
                    console.error('Error:', error);
                });
            }
        }

        // Imprimer les détails
        function imprimerDetails() {
            window.print();
        }
    </script>
</body>
</html>