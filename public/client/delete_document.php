<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}

$document_id = $_GET['id'] ?? 0;

// Vérifier que le document appartient à l'utilisateur
$stmt = $pdo->prepare("
    SELECT d.* 
    FROM documents_immigration d
    JOIN evaluations_immigration e ON d.evaluation_id = e.id
    WHERE d.id = :id AND e.email = :email
");
$stmt->execute([
    ':id' => $document_id,
    ':email' => $_SESSION['user_email']
]);
$document = $stmt->fetch(PDO::FETCH_ASSOC);

if ($document) {
    // Supprimer le fichier physique
    if (file_exists($document['chemin_fichier'])) {
        unlink($document['chemin_fichier']);
    }
    
    // Supprimer de la base
    $stmt = $pdo->prepare("DELETE FROM documents_immigration WHERE id = :id");
    $stmt->execute([':id' => $document_id]);
    
    $_SESSION['success'] = "Document supprimé avec succès";
} else {
    $_SESSION['error'] = "Document non trouvé";
}

header("Location: documents.php");
exit;
?>