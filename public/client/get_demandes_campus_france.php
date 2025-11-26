<?php
// get_demande_campus_details.php
session_start();

// Vérification connexion
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Accès refusé. Veuillez vous connecter.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Vérifier que l'ID est passé
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "ID de la demande invalide.";
    exit;
}

$demande_id = (int) $_GET['id'];

// Connexion à la base
try {
    $pdo = new PDO("mysql:host=localhost;dbname=babylone_service;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur BDD : " . $e->getMessage();
    exit;
}

// Récupérer les détails de la demande
$sql = "SELECT * FROM campus_france_demandes 
        WHERE id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$demande_id, $user_id]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$demande) {
    echo "<p>Aucune demande trouvée.</p>";
    exit;
}

// Traduction service
$types_service = [
    'test_langue' => 'Test de langue',
    'remplissage_docs' => 'Remplissage documents',
    'demande_visa' => 'Demande de visa',
    'complete' => 'Procédure complète'
];
$service_libelle = $types_service[$demande['service_type']] ?? $demande['service_type'];

// Formatage dates
function formatDateTime($date) {
    if (empty($date) || $date == '0000-00-00 00:00:00') return 'Non spécifié';
    return date('d/m/Y à H:i', strtotime($date));
}

// Récupérer les documents liés
$sqlDocs = "SELECT * FROM campus_france_documents WHERE demande_id = ?";
$stmtDocs = $pdo->prepare($sqlDocs);
$stmtDocs->execute([$demande_id]);
$documents = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l’historique si tu as une table (optionnel)
$sqlHist = "SELECT * FROM campus_france_historique 
            WHERE demande_id = ? 
            ORDER BY date_action DESC";
try {
    $stmtHist = $pdo->prepare($sqlHist);
    $stmtHist->execute([$demande_id]);
    $historiques = $stmtHist->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $historiques = [];
}
?>

<!-- Affichage -->
<div>
    <h3>Demande n°<?= htmlspecialchars($demande['id']) ?> (Référence : <?= htmlspecialchars($demande['reference'] ?? 'CF-'.$demande['id']) ?>)</h3>
    <p><strong>Nom complet :</strong> <?= htmlspecialchars($demande['nom_complet']) ?></p>
    <p><strong>Type de service :</strong> <?= htmlspecialchars($service_libelle) ?></p>
    <p><strong>Statut :</strong> <?= htmlspecialchars($demande['statut']) ?></p>
    <p><strong>Date de création :</strong> <?= formatDateTime($demande['date_creation']) ?></p>
    <hr>

    <h4>📂 Documents déposés</h4>
    <?php if (!empty($documents)): ?>
        <ul>
            <?php foreach ($documents as $doc): ?>
                <li>
                    <?= htmlspecialchars($doc['nom_document']) ?> 
                    - <a href="../uploads/campus_france/<?= htmlspecialchars($doc['fichier']) ?>" target="_blank">Voir</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun document ajouté.</p>
    <?php endif; ?>

    <hr>
    <h4>📜 Historique</h4>
    <?php if (!empty($historiques)): ?>
        <ul>
            <?php foreach ($historiques as $h): ?>
                <li>[<?= formatDateTime($h['date_action']) ?>] 
                    <?= htmlspecialchars($h['action']) ?> - 
                    <?= htmlspecialchars($h['commentaire']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun historique disponible.</p>
    <?php endif; ?>
</div>
