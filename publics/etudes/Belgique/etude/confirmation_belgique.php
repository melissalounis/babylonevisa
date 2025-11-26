<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$demande_id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Belgique</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .confirmation-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div style="font-size: 4rem; color: #28a745; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h1 style="color: #070988;">Candidature soumise avec succès !</h1>
        <p class="lead">Votre candidature pour la Belgique a été enregistrée.</p>
        
        <div style="background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: left;">
            <p><strong>Numéro de référence :</strong> #<?php echo $demande_id; ?></p>
            <p><strong>Date de soumission :</strong> <?php echo date('d/m/Y à H:i'); ?></p>
            <p><strong>Statut :</strong> <span class="badge bg-warning">En attente</span></p>
        </div>
        
        <div class="d-flex gap-3 justify-content-center mt-4 flex-wrap">
            <a href="../../client/mes_demandes_belgique.php" class="btn btn-primary">
                <i class="fas fa-list"></i> Mes demandes
            </a>
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Nouvelle demande
            </a>
        </div>
    </div>
</body>
</html>