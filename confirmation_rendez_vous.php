<?php
session_start();

if (!isset($_SESSION['success_message'])) {
    header('Location: rendez_vous.php');
    exit();
}

$reference = $_SESSION['reference'] ?? '';
$rendez_vous_id = $_SESSION['rendez_vous_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-success text-center">
            <h2>✅ Demande enregistrée avec succès!</h2>
            <p class="fs-4">Votre référence: <strong><?php echo htmlspecialchars($reference); ?></strong></p>
            <p class="fs-5">ID du rendez-vous: <strong><?php echo htmlspecialchars($rendez_vous_id); ?></strong></p>
            <a href="rendez_vous.php" class="btn btn-primary">Nouvelle demande</a>
        </div>
    </div>
</body>
</html>
<?php
// Nettoyer la session après affichage
unset($_SESSION['success_message'], $_SESSION['reference'], $_SESSION['rendez_vous_id']);
?>