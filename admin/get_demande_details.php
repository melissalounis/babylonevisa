<?php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

require_once '../config.php';

header('Content-Type: application/json');

try {
   

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de demande invalide');
    }

    $demande_id = intval($_GET['id']);

    // Récupérer les détails de la demande
    $stmt = $pdo->prepare("SELECT * FROM demandes_court_sejour WHERE id = ?");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        throw new Exception('Demande non trouvée');
    }

    // Récupérer les fichiers associés
    $stmt = $pdo->prepare("SELECT * FROM demandes_court_sejour_fichiers WHERE demande_id = ? ORDER BY type_fichier, date_upload");
    $stmt->execute([$demande_id]);
    $fichiers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'demande' => $demande,
        'fichiers' => $fichiers
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>