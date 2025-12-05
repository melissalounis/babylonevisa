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
$success_message = '';
$suppression_reussie = false;

try {
  

    // Récupérer l'email de l'utilisateur
    $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
    $stmt_user->execute([$_SESSION['user_id']]);
    $user = $stmt_user->fetch();
    
    if ($user && isset($user['email'])) {
        $user_email = $user['email'];
        
        // Récupérer les détails de la demande pour vérification
        $stmt = $pdo->prepare("
            SELECT * FROM demandes_etudes_roumanie 
            WHERE id = ? AND email = ? AND statut = 'nouveau'
        ");
        $stmt->execute([$demande_id, $user_email]);
        $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$demande) {
            $error_message = "Demande non trouvée ou non supprimable. Seules les demandes avec le statut 'Nouveau' peuvent être supprimées.";
        }
    } else {
        $error_message = "Utilisateur non trouvé.";
    }

} catch (PDOException $e) {
    $error_message = "Erreur de connexion à la base de données: " . $e->getMessage();
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $demande) {
    try {
        // Commencer une transaction
        $pdo->beginTransaction();

        // Supprimer les fichiers associés
        $upload_dir = "../../uploads/roumanie/";
        
        // Liste des champs fichiers
        $file_fields = [
            'certificat_file', 'releve_2nde', 'releve_1ere', 'releve_terminale', 
            'releve_bac', 'diplome_bac', 'certificat_scolarite'
        ];
        
        foreach ($file_fields as $field) {
            if (!empty($demande[$field]) && file_exists($upload_dir . $demande[$field])) {
                unlink($upload_dir . $demande[$field]);
            }
        }

        // Supprimer l'enregistrement de la base de données
        $stmt = $pdo->prepare("DELETE FROM demandes_etudes_roumanie WHERE id = ? AND email = ?");
        $stmt->execute([$demande_id, $user_email]);

        if ($stmt->rowCount() > 0) {
            $pdo->commit();
            $suppression_reussie = true;
            $success_message = "La demande a été supprimée avec succès.";
        } else {
            $pdo->rollBack();
            $error_message = "Erreur lors de la suppression de la demande.";
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Erreur lors de la suppression : " . $e->getMessage();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Erreur lors de la suppression des fichiers : " . $e->getMessage();
    }
}

// Redirection si suppression réussie
if ($suppression_reussie) {
    header("Refresh: 3; URL=mes_demandes_roumanie.php");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer la Demande - Babylone Service</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        header {
            background: var(--danger-color);
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
            background: linear-gradient(90deg, #ff6b6b, #ff8e8e, #ff6b6b);
        }

        h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .content {
            padding: 30px;
        }

        .warning-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }

        .warning-icon {
            font-size: 3rem;
            color: #856404;
            margin-bottom: 15px;
        }

        .demande-info {
            background: var(--secondary-color);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid var(--danger-color);
        }

        .info-group {
            margin-bottom: 10px;
        }

        .info-label {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 1rem;
            color: var(--text-color);
            font-weight: 500;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 140px;
            justify-content: center;
        }

        .btn-danger {
            background: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #f5c6cb;
        }

        .countdown {
            text-align: center;
            margin-top: 15px;
            font-size: 0.9rem;
            color: #666;
        }

        .file-list {
            margin-top: 15px;
            padding: 15px;
            background: white;
            border-radius: var(--border-radius);
            border: 1px solid #e0e0e0;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .file-item:last-child {
            border-bottom: none;
        }

        .file-name {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            body {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-trash-alt"></i> Supprimer la Demande</h1>
            <p>Confirmation de suppression</p>
        </header>

        <div class="content">
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Erreur</h3>
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                    <div class="actions" style="margin-top: 20px;">
                        <a href="mes_demandes_roumanie.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Retour aux demandes
                        </a>
                    </div>
                </div>

            <?php elseif ($suppression_reussie): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <h3>Suppression réussie</h3>
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                    <p>Vous allez être redirigé vers la liste des demandes dans <span id="countdown">3</span> secondes...</p>
                    <div class="actions" style="margin-top: 20px;">
                        <a href="mes_demandes_roumanie.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Retour maintenant
                        </a>
                    </div>
                </div>

            <?php elseif ($demande): ?>
                <div class="warning-section">
                    <div class="warning-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h2>Attention !</h2>
                    <p>Vous êtes sur le point de supprimer définitivement cette demande. Cette action est irréversible.</p>
                </div>

                <div class="demande-info">
                    <h3 style="color: var(--danger-color); margin-bottom: 15px; text-align: center;">
                        <i class="fas fa-file-alt"></i> Détails de la demande
                    </h3>
                    
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
                    
                    <div class="info-group">
                        <div class="info-label">Date de soumission</div>
                        <div class="info-value"><?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?></div>
                    </div>

                    <!-- Liste des fichiers qui seront supprimés -->
                    <?php
                    $files_to_delete = [];
                    $file_fields = [
                        'certificat_file' => 'Certificat de langue',
                        'releve_2nde' => 'Relevé de notes 2nde',
                        'releve_1ere' => 'Relevé de notes 1ère',
                        'releve_terminale' => 'Relevé de notes Terminale',
                        'releve_bac' => 'Relevé de notes Bac',
                        'diplome_bac' => 'Diplôme Bac',
                        'certificat_scolarite' => 'Certificat de scolarité'
                    ];
                    
                    foreach ($file_fields as $field => $label) {
                        if (!empty($demande[$field])) {
                            $files_to_delete[] = $label;
                        }
                    }
                    
                    if (!empty($files_to_delete)):
                    ?>
                        <div class="info-group">
                            <div class="info-label">Fichiers qui seront supprimés</div>
                            <div class="file-list">
                                <?php foreach ($files_to_delete as $file_label): ?>
                                    <div class="file-item">
                                        <span class="file-name"><?php echo htmlspecialchars($file_label); ?></span>
                                        <i class="fas fa-trash text-danger"></i>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST">
                    <div class="actions">
                        <a href="details_demande.php?id=<?php echo $demande_id; ?>" class="btn btn-outline">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-danger" onclick="return confirmSuppression()">
                            <i class="fas fa-trash-alt"></i> Confirmer la suppression
                        </button>
                    </div>
                </form>

            <?php else: ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Demande non trouvée</h3>
                    <p>La demande que vous essayez de supprimer n'existe pas ou vous n'avez pas l'autorisation de la supprimer.</p>
                    <div class="actions" style="margin-top: 20px;">
                        <a href="mes_demandes_roumanie.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Retour aux demandes
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Compte à rebours pour la redirection
        <?php if ($suppression_reussie): ?>
        let seconds = 3;
        const countdownElement = document.getElementById('countdown');
        
        const countdown = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = 'mes_demandes_roumanie.php';
            }
        }, 1000);
        <?php endif; ?>

        // Confirmation supplémentaire
        function confirmSuppression() {
            const specialite = "<?php echo htmlspecialchars($demande['specialite'] ?? ''); ?>";
            return confirm(`Êtes-vous ABSOLUMENT sûr de vouloir supprimer la demande "${specialite}" ?\n\nCette action supprimera définitivement :\n• La demande et toutes ses informations\n• Tous les fichiers associés\n\nCette action est IRREVERSIBLE !`);
        }

        // Empêcher la soumission multiple du formulaire
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Suppression en cours...';
                    }
                });
            }
        });
    </script>
</body>
</html>