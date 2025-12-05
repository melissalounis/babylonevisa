<?php
session_start();

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
        // Récupérer les informations de la demande
        $stmt = $pdo->prepare("SELECT * FROM demandes_bourse_italie WHERE id = ?");
        $stmt->execute([$id]);
        $demande = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($demande) {
            // Récupérer les fichiers associés
            $fichiers = [];
            try {
                $fichiers_stmt = $pdo->prepare("SELECT * FROM demandes_bourse_fichiers WHERE demande_id = ? ORDER BY type_fichier, date_upload");
                $fichiers_stmt->execute([$id]);
                $fichiers = $fichiers_stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Table des fichiers peut ne pas exister, on ignore l'erreur
                $fichiers = [];
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'demande' => $demande,
                'fichiers' => $fichiers
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
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