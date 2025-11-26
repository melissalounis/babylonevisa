<?php
session_start();

// Désactiver l'affichage des erreurs pour éviter la pollution du JSON
error_reporting(0);
ini_set('display_errors', 0);

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données']);
    exit();
}

// Récupérer l'ID de la demande
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de demande invalide']);
    exit();
}

$id = intval($_GET['id']);

try {
    // Récupérer les détails de la demande
    $stmt = $pdo->prepare("SELECT * FROM demandes_luxembourg WHERE id = ?");
    $stmt->execute([$id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demande) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
        exit();
    }
    
    // Récupérer les tests de langue
    $tests_langue = [];
    $stmt_tests = $pdo->prepare("SELECT type_fichier FROM demandes_luxembourg_fichiers WHERE demande_id = ? AND type_fichier LIKE 'test_%'");
    $stmt_tests->execute([$id]);
    $tests = $stmt_tests->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tests as $test) {
        if (strpos($test, 'test_francais') !== false) $tests_langue[] = 'FR';
        if (strpos($test, 'test_anglais') !== false) $tests_langue[] = 'EN';
        if (strpos($test, 'test_allemand') !== false) $tests_langue[] = 'DE';
    }
    
    // Fonctions de formatage
    function formatNiveauEtude($niveau) {
        $niveaux = [
            'bachelor' => 'Bachelor',
            'master' => 'Master',
            'doctorat' => 'Doctorat'
        ];
        return $niveaux[$niveau] ?? $niveau;
    }
    
    function formatStatut($statut) {
        $statuts = [
            'en_attente' => 'En attente',
            'en_traitement' => 'En traitement',
            'approuvee' => 'Approuvée',
            'refusee' => 'Refusée'
        ];
        return $statuts[$statut] ?? $statut;
    }
    
    function getStatutClass($statut) {
        $classes = [
            'en_attente' => 'warning',
            'en_traitement' => 'info',
            'approuvee' => 'success',
            'refusee' => 'danger'
        ];
        return $classes[$statut] ?? 'secondary';
    }

    // Préparer la réponse JSON
    $responseData = [
        'success' => true,
        'demande' => [
            'id' => $demande['id'],
            'user_id' => $demande['user_id'] ?? '',
            'nom' => $demande['nom'] ?? '',
            'prenom' => $demande['prenom'] ?? '',
            'email' => $demande['email'] ?? '',
            'telephone' => $demande['telephone'] ?? '',
            'nationalite' => $demande['nationalite'] ?? '',
            'niveau' => $demande['niveau'] ?? '',
            'niveau_formatted' => formatNiveauEtude($demande['niveau'] ?? ''),
            'universite' => $demande['universite'] ?? '',
            'filiere' => $demande['filiere'] ?? '',
            'statut' => $demande['statut'] ?? '',
            'statut_formatted' => formatStatut($demande['statut'] ?? ''),
            'statut_class' => getStatutClass($demande['statut'] ?? ''),
            'date_creation' => $demande['date_creation'] ?? '',
            'date_modification' => $demande['date_modification'] ?? '',
            'tests_langue' => $tests_langue
        ]
    ];

    // Envoyer la réponse JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($responseData, JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
} catch(Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>