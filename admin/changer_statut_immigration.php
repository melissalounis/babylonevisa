<?php
// Fichier: changer_statut_immigration.php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

include '../config.php';
// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);

// Debug: Vérifier ce qui est reçu
error_log("Données reçues: " . print_r($input, true));

$demande_id = $input['id'] ?? null;
$nouveau_statut = $input['statut'] ?? null;
$commentaire = $input['commentaire'] ?? '';

if (!$demande_id || !$nouveau_statut) {
    error_log("Données manquantes - ID: $demande_id, Statut: $nouveau_statut");
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

// Statuts valides
$statuts_valides = ['en_attente', 'en_cours', 'complet', 'approuve', 'refuse'];

if (!in_array($nouveau_statut, $statuts_valides)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Statut invalide']);
    exit();
}

try {
    // Mettre à jour le statut
    $stmt = $pdo->prepare("UPDATE demandes_green_card SET statut = ? WHERE id = ?");
    $stmt->execute([$nouveau_statut, $demande_id]);
    
    // Enregistrer l'historique si un commentaire est fourni
    if (!empty($commentaire)) {
        // Créer la table historique si elle n'existe pas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS historique_statuts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                demande_id INT,
                ancien_statut VARCHAR(50),
                nouveau_statut VARCHAR(50),
                commentaire TEXT,
                date_changement DATETIME,
                admin_id INT,
                FOREIGN KEY (demande_id) REFERENCES demandes_green_card(id)
            )
        ");
        
        $stmt_histo = $pdo->prepare("
            INSERT INTO historique_statuts (demande_id, ancien_statut, nouveau_statut, commentaire, date_changement, admin_id) 
            VALUES (?, (SELECT statut FROM demandes_green_card WHERE id = ?), ?, ?, NOW(), ?)
        ");
        $stmt_histo->execute([$demande_id, $demande_id, $nouveau_statut, $commentaire, $_SESSION['admin_id'] ?? 1]);
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Statut mis à jour avec succès']);
    
} catch(PDOException $e) {
    error_log("Erreur BD: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
}
?>