<?php
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

header('Content-Type: application/json; charset=UTF-8');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Valider l'ID de demande
    $demande_id = isset($_GET['demande_id']) ? (int)$_GET['demande_id'] : 0;
    
    if ($demande_id <= 0) {
        throw new Exception('ID de demande invalide');
    }
    
    // Vérifier si la table existe
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'documents_demande'")->fetch();
    if (!$tableCheck) {
        throw new Exception('Table documents_demande non trouvée. Veuillez créer la table.');
    }
    
    // Récupérer les documents
    $stmt = $pdo->prepare("
        SELECT * FROM documents_demande 
        WHERE demande_id = ? 
        ORDER BY date_upload DESC
    ");
    $stmt->execute([$demande_id]);
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formater la réponse
    $response = [
        'success' => true,
        'documents' => $documents
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
    
} catch (PDOException $e) {
    error_log("Database Error in get_documents_demande: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erreur de base de données: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General Error in get_documents_demande: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>