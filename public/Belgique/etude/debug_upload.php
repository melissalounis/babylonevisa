<?php
// debug_upload.php
session_start();

// Inclure la configuration
require_once '../../../config.php';

if (!isset($_GET['id'])) {
    die("ID non spécifié");
}

$id = intval($_GET['id']);

try {
    $sql = "SELECT * FROM etude_belgique WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demande) {
        die("Demande non trouvée");
    }
    
} catch(PDOException $e) {
    die("Erreur: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Debug Upload - Demande #<?php echo $id; ?></h1>
    
    <div class="row mt-4">
        <div class="col-md-6">
            <h3>Informations de base</h3>
            <table class="table table-bordered">
                <tr>
                    <th>Nom:</th>
                    <td><?php echo htmlspecialchars($demande['nom']); ?></td>
                </tr>
                <tr>
                    <th>Niveau:</th>
                    <td><?php echo htmlspecialchars($demande['niveau_etude']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($demande['email']); ?></td>
                </tr>
                <tr>
                    <th>Créée le:</th>
                    <td><?php echo htmlspecialchars($demande['created_at']); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="col-md-6">
            <h3>Fichiers uploadés</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Champ</th>
                        <th>Valeur</th>
                        <th>Existe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $upload_dir = 'uploads/etude_belgique/';
                    $champs_fichiers = ['photo', 'passport', 'certificat_scolarite_actuel', 'releve_bac', 'diplome_bac', 
                                        'releve_l1', 'releve_l2', 'releve_2nde', 'releve_1ere', 'releve_terminale',
                                        'releves_licence_l1', 'releves_licence_l2', 'releves_licence_l3', 'diplome_licence',
                                        'document_equivalence'];
                    
                    foreach ($champs_fichiers as $champ) {
                        if (!empty($demande[$champ])) {
                            $fichier = $demande[$champ];
                            if (strpos($fichier, ',') !== false) {
                                $fichiers = explode(',', $fichier);
                                echo "<tr>";
                                echo "<td><strong>$champ</strong></td>";
                                echo "<td>";
                                foreach ($fichiers as $f) {
                                    $f = trim($f);
                                    $file_path = $upload_dir . $f;
                                    $exists = file_exists($file_path) ? '✓' : '✗';
                                    echo "$f ($exists)<br>";
                                }
                                echo "</td>";
                                echo "<td>" . (file_exists($upload_dir . trim($fichiers[0])) ? 'Oui' : 'Non') . "</td>";
                                echo "</tr>";
                            } else {
                                $file_path = $upload_dir . $fichier;
                                $exists = file_exists($file_path) ? '✓' : '✗';
                                echo "<tr>";
                                echo "<td><strong>$champ</strong></td>";
                                echo "<td>$fichier ($exists)</td>";
                                echo "<td>" . (file_exists($file_path) ? 'Oui' : 'Non') . "</td>";
                                echo "</tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <h3>Données brutes</h3>
            <pre><?php print_r($demande); ?></pre>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="etude_belgique.php" class="btn btn-primary">Retour au formulaire</a>
        <a href="etude_belgique_admin.php" class="btn btn-secondary">Voir dans l'admin</a>
    </div>
</div>
</body>
</html>