<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '../../../config.php';


$document_id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("
    SELECT d.*, e.email 
    FROM documents_immigration d
    JOIN evaluations_immigration e ON d.evaluation_id = e.id
    WHERE d.id = :id AND e.email = :email
");
$stmt->execute([
    ':id' => $document_id,
    ':email' => $_SESSION['user_email']
]);
$document = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$document || !file_exists($document['chemin_fichier'])) {
    die("Document non trouvé");
}

// Headers pour le téléchargement
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $document['nom_fichier'] . '"');
header('Content-Length: ' . filesize($document['chemin_fichier']));
readfile($document['chemin_fichier']);
exit;
?>