<?php
// Start session and check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '../../../config.php';

// Get request ID and validate
$demande_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch the specific request, ensuring it belongs to the logged-in user
$stmt = $pdo->prepare("
    SELECT db.*, u.name as user_name, u.email as user_email 
    FROM demandes_belgique db 
    LEFT JOIN users u ON db.user_id = u.id 
    WHERE db.id = ? AND db.user_id = ?
");
$stmt->execute([$demande_id, $user_id]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);

// If request not found, redirect
if (!$demande) {
    header("Location: mes_demandes_belgique.php");
    exit;
}

// Fetch uploaded files for this request
$stmt_fichiers = $pdo->prepare("SELECT * FROM demandes_belgique_fichiers WHERE demande_id = ?");
$stmt_fichiers->execute([$demande_id]);
$fichiers = $stmt_fichiers->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la demande Belgique #<?php echo $demande_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Détails de votre demande pour la Belgique</h1>
        <div class="card">
            <div class="card-header">
                <h2>Référence : #<?php echo $demande_id; ?></h2>
            </div>
            <div class="card-body">
                <!-- Personal Information -->
                <h4>Informations personnelles</h4>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Nom complet :</strong> <?php echo htmlspecialchars($demande['user_name']); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Email :</strong> <?php echo htmlspecialchars($demande['user_email']); ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Date de naissance :</strong> <?php echo date('d/m/Y', strtotime($demande['naissance'])); ?>
                    </div>
                    <div class="col-md-6">
                        <strong>Nationalité :</strong> <?php echo htmlspecialchars($demande['nationalite']); ?>
                    </div>
                </div>

                <!-- Study Information -->
                <h4>Informations sur la formation</h4>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Niveau d'étude :</strong> <?php echo htmlspecialchars($demande['niveau_etude']); ?>
                    </div>
                </div>

                <!-- Request Status -->
                <h4>Statut de la demande</h4>
                <div class="mb-3">
                    <?php 
                    $badge_class = [
                        'en_attente' => 'bg-warning',
                        'en_traitement' => 'bg-info',
                        'approuvee' => 'bg-success',
                        'rejetee' => 'bg-danger'
                    ][$demande['statut']] ?? 'bg-secondary';
                    ?>
                    <span class="badge <?php echo $badge_class; ?>">
                        <?php echo ucfirst(str_replace('_', ' ', $demande['statut'])); ?>
                    </span>
                </div>

                <!-- Attached Documents -->
                <h4>Documents attachés</h4>
                <?php if (count($fichiers) > 0): ?>
                    <ul class="list-group">
                        <?php foreach ($fichiers as $fichier): ?>
                            <li class="list-group-item">
                                <?php echo htmlspecialchars($fichier['type_fichier']); ?> 
                                - <small class="text-muted">Téléchargé le <?php echo date('d/m/Y H:i', strtotime($fichier['date_upload'])); ?></small>
                                <a href="telecharger_fichier_belgique.php?id=<?php echo $fichier['id']; ?>" class="btn btn-sm btn-outline-primary float-end">Télécharger</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Aucun document attaché.</p>
                <?php endif; ?>
            </div>
            <div class="card-footer">
                <a href="mes_demandes_belgique.php" class="btn btn-secondary">Retour à la liste</a>
                <?php if ($demande['statut'] === 'en_attente'): ?>
                    <a href="modifier_demande_belgique.php?id=<?php echo $demande_id; ?>" class="btn btn-warning">Modifier la demande</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>