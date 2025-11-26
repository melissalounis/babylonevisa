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
    $stmt = $pdo->prepare("SELECT * FROM demandes_suisse WHERE id = ?");
    $stmt->execute([$id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demande) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
        exit();
    }
    
    // Fonctions de formatage
    function formatNiveauEtude($niveau) {
        $niveaux = [
            'master1' => 'Master 1',
            'master2' => 'Master 2'
        ];
        return $niveaux[$niveau] ?? $niveau;
    }
    
    function formatLangue($langue) {
        $langues = [
            'allemand' => 'Allemand',
            'francais' => 'Français',
            'anglais' => 'Anglais',
            'italien' => 'Italien',
            'bilingue' => 'Bilingue'
        ];
        return $langues[$langue] ?? $langue;
    }
    
    function formatStatut($statut) {
        $statuts = [
            'en_attente' => 'En attente',
            'en_cours' => 'En cours',
            'approuve' => 'Approuvé',
            'refuse' => 'Refusé'
        ];
        return $statuts[$statut] ?? $statut;
    }
    
    function getStatutClass($statut) {
        $classes = [
            'en_attente' => 'info',
            'en_cours' => 'warning',
            'approuve' => 'success',
            'refuse' => 'danger'
        ];
        return $classes[$statut] ?? 'secondary';
    }
    
    function formatTestLangue($test) {
        return $test === 'oui' ? 'Oui' : 'Non';
    }

    // Préparer la réponse
    $responseData = [
        'success' => true,
        'demande' => [
            'id' => $demande['id'],
            'nom' => $demande['nom'] ?? '',
            'prenom' => $demande['prenom'] ?? '',
            'date_naissance' => $demande['date_naissance'] ?? '',
            'lieu_naissance' => $demande['lieu_naissance'] ?? '',
            'nationalite' => $demande['nationalite'] ?? '',
            'adresse' => $demande['adresse'] ?? '',
            'telephone' => $demande['telephone'] ?? '',
            'email' => $demande['email'] ?? '',
            'num_passeport' => $demande['num_passeport'] ?? '',
            'niveau_etudes' => $demande['niveau_etudes'] ?? '',
            'niveau_etudes_formatted' => formatNiveauEtude($demande['niveau_etudes'] ?? ''),
            'domaine_etudes' => $demande['domaine_etudes'] ?? '',
            'nom_formation' => $demande['nom_formation'] ?? '',
            'date_debut' => $demande['date_debut'] ?? '',
            'langue_formation' => $demande['langue_formation'] ?? '',
            'langue_formation_formatted' => formatLangue($demande['langue_formation'] ?? ''),
            'tests_allemand' => $demande['tests_allemand'] ?? 'non',
            'tests_allemand_formatted' => formatTestLangue($demande['tests_allemand'] ?? 'non'),
            'tests_francais' => $demande['tests_francais'] ?? 'non',
            'tests_francais_formatted' => formatTestLangue($demande['tests_francais'] ?? 'non'),
            'tests_anglais' => $demande['tests_anglais'] ?? 'non',
            'tests_anglais_formatted' => formatTestLangue($demande['tests_anglais'] ?? 'non'),
            'tests_italien' => $demande['tests_italien'] ?? 'non',
            'tests_italien_formatted' => formatTestLangue($demande['tests_italien'] ?? 'non'),
            'statut' => $demande['statut'] ?? '',
            'statut_formatted' => formatStatut($demande['statut'] ?? ''),
            'statut_class' => getStatutClass($demande['statut'] ?? ''),
            'date_soumission' => $demande['date_soumission'] ?? '',
            'date_modification' => $demande['date_modification'] ?? ''
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