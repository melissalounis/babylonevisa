<?php
// config.php - Connexion BD + fonctions utilitaires

// ====================
// CONFIGURATION BD
// ====================
$DB_HOST = 'localhost';
$DB_NAME = 'babylone_service';
$DB_USER = 'root';
$DB_PASS = ''; // XAMPP : mot de passe vide par défaut

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Connexion BD échouée: " . htmlspecialchars($e->getMessage()));
}

// ====================
// CONFIGURATION UPLOAD
// ====================
// Chemin absolu du dossier upload (ex: /xampp/htdocs/babylone/uploads)
define('UPLOAD_BASE', __DIR__ . '/uploads');
// Taille max upload (ici 5 Mo)
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// ====================
// FONCTIONS GÉNÉRALES
// ====================

// Échapper les données pour éviter les XSS
function e($s) {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

// Vérifie si l'utilisateur est connecté
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Vérifie si l'utilisateur est admin
function is_admin() {
    return !empty($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirection si non connecté
function require_login() {
    if (!is_logged_in()) {
        header("Location: /babylone/public/client/login.php");
        exit;
    }
}
