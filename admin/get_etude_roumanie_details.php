<?php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

require_once '../config.php';
// Récupérer l'ID de la demande
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit();
}

try {
    // Récupérer les détails de la demande
    $stmt = $pdo->prepare("SELECT * FROM demandes_etudes_roumanie WHERE id = ?");
    $stmt->execute([$id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
        exit();
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'demande' => $demande]);

} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des données']);
}