<?php
// Inclure la configuration
require_once __DIR__ . '../../../config.php';

// Vérifier si la session est déjà démarrée, sinon la démarrer
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Journalisation pour le débogage
error_log("Tentative de déconnexion - User ID: " . ($_SESSION['user_id'] ?? 'non connecté'));

// Réinitialiser toutes les variables de session
$_SESSION = array();

// Supprimer le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil avec un paramètre de succès
header("Location: /babylone/public/index.php?logout=success");
exit();
?>