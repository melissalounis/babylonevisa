<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

require_once '../config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM demandes_billets_avion WHERE id = ?");
        $stmt->execute([$id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($reservation) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'reservation' => $reservation]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Réservation non trouvée']);
        }
    } catch(PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur de base de données: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID non spécifié']);
}
?>