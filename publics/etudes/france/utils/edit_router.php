<?php
// Pré-remplir les formulaires pour modification
$host = "localhost";
$db   = "babylone_service";
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Exception $e) {
    die("Connexion BD échouée: " . $e->getMessage());
}

$id = $_GET['id'] ?? null;

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM travail WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    // $row contient les données pour pré-remplir le formulaire
}
?>
