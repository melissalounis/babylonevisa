<?php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

// Configuration BDD
define('DB_HOST', 'localhost');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');

// Récupérer les données JSON
$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$statut = $input['statut'] ?? null;

if (!$id || !$statut) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("UPDATE rendez_vous SET statut = ?, date_maj = NOW() WHERE id = ?");
    $stmt->execute([$statut, $id]);
    
    echo json_encode(['success' => true, 'message' => 'Statut mis à jour']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur BDD: ' . $e->getMessage()]);
}
?>