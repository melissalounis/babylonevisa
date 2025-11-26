<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Campus France</title>
    <!-- Inclusion de Bootstrap pour le style (optionnel) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <style>
        .confirmation-container { 
            margin-top: 50px; 
            padding: 30px; 
            border: 1px solid #28a745; 
            border-radius: 10px; 
            background-color: #f8fff9; 
        }
        .confirmation-header { 
            color: #28a745; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="confirmation-container text-center">
            <!-- Icône de succès -->
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#28a745" class="bi bi-check-circle-fill mb-3" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
            </svg>

            <h2 class="confirmation-header">Dossier soumis avec succès !</h2>
            <p class="lead">Votre dossier de candidature Campus France a été enregistré.</p>

            <div class="my-4">
                <?php
                // Exemple : Récupération d'un numéro de référence (si passé en paramètre)
                if (isset($_GET['ref'])) {
                    echo '<p><strong>Numéro de référence :</strong> ' . htmlspecialchars($_GET['ref']) . '</p>';
                }
                // Affichage de la date et heure de soumission
                date_default_timezone_set('Europe/Paris');
                echo '<p><strong>Date de soumission :</strong> ' . date('d/m/Y à H:i') . '</p>';
                ?>
                <p><strong>Prochaine étape :</strong> Votre dossier va être examiné par notre équipe. Vous recevrez un email de confirmation sous peu.</p>
            </div>

            <a href="../../index.php" class="btn btn-success mt-3">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>