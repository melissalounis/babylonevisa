<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$user_id = $_SESSION['user_id'];
$rendez_vous_id = $_POST['id'] ?? 0;

if (!$rendez_vous_id) {
    echo json_encode(['success' => false, 'message' => 'ID de rendez-vous manquant']);
    exit;
}

// Connexion à la base de données
require_once __DIR__ . '../../../config.php';

try {
   

    // Vérifier que l'utilisateur peut annuler ce rendez-vous
    $stmt = $pdo->prepare("
        SELECT id FROM rendez_vous 
        WHERE id = ? 
        AND email = (SELECT email FROM users WHERE id = ?)
        AND statut = 'en_attente'
    ");
    $stmt->execute([$rendez_vous_id, $user_id]);
    $rendez_vous = $stmt->fetch();

    if (!$rendez_vous) {
        echo json_encode(['success' => false, 'message' => 'Rendez-vous non trouvé ou déjà traité']);
        exit;
    }

    // Annuler le rendez-vous
    $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE id = ?");
    $stmt->execute([$rendez_vous_id]);

    echo json_encode(['success' => true, 'message' => 'Rendez-vous annulé avec succès']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
}
?>