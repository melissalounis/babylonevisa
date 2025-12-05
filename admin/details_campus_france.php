<?php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Accès non autorisé']);
    exit();
}

include '../config.php';

// Vérifier que l'ID est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'ID de demande manquant']);
    exit();
}

$demande_id = intval($_GET['id']);

try {
    // Récupérer les données de la demande
    $stmt = $pdo->prepare("
        SELECT * 
        FROM demandes_campus_france 
        WHERE id = ?
    ");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
        exit();
    }

    // Récupérer les fichiers associés (si la table existe)
    $fichiers = [];
    try {
        $stmt_fichiers = $pdo->prepare("
            SELECT * 
            FROM demandes_campus_france_fichiers 
            WHERE demande_id = ?
            ORDER BY type_fichier, date_upload
        ");
        $stmt_fichiers->execute([$demande_id]);
        $fichiers = $stmt_fichiers->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // La table des fichiers n'existe pas encore, on continue sans fichiers
        $fichiers = [];
    }

    // Formater les données pour la réponse
    $response = [
        'success' => true,
        'demande' => [
            'id' => $demande['id'],
            'user_id' => $demande['user_id'],
            
            // Projet d'études
            'pays_etudes' => $demande['pays_etudes'],
            'niveau_etudes' => $demande['niveau_etudes'],
            'niveau_formatted' => formatNiveauEtude($demande['niveau_etudes']),
            'domaine_etudes' => $demande['domaine_etudes'],
            
            // Informations personnelles
            'nom' => $demande['nom'],
            'prenom' => $demande['prenom'],
            'date_naissance' => $demande['date_naissance'],
            'lieu_naissance' => $demande['lieu_naissance'],
            'nationalite' => $demande['nationalite'],
            'adresse' => $demande['adresse'],
            'telephone' => $demande['telephone'],
            'email' => $demande['email'],
            
            // Passeport
            'num_passeport' => $demande['num_passeport'],
            'date_delivrance' => $demande['date_delivrance'],
            'date_expiration' => $demande['date_expiration'],
            
            // Tests de langue
            'niveau_francais' => $demande['niveau_francais'],
            'tests_francais' => $demande['tests_francais'],
            'test_francais_formatted' => formatTestLangue($demande['tests_francais']),
            'score_test' => $demande['score_test'],
            'test_anglais' => $demande['test_anglais'],
            'test_anglais_formatted' => formatTestLangue($demande['test_anglais']),
            'score_anglais' => $demande['score_anglais'],
            
            // Boîte Pastel
            'boite_pastel' => $demande['boite_pastel'],
            'email_pastel' => $demande['email_pastel'],
            'mdp_pastel' => $demande['mdp_pastel'],
            
            // Relevés de notes
            'releves_annees' => json_decode($demande['releves_annees'], true) ?: [],
            
            // Autres documents
            'autres_documents' => json_decode($demande['autres_documents'], true) ?: [],
            
            // Statut et dates
            'statut' => $demande['statut'],
            'statut_formatted' => formatStatut($demande['statut'])['label'],
            'statut_class' => formatStatut($demande['statut'])['class'],
            'created_at' => $demande['created_at'],
            'date_modification' => $demande['date_modification'],
            
            // Fichiers
            'fichiers' => $fichiers
        ]
    ];

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch(PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la récupération des données: ' . $e->getMessage()]);
}

// Fonctions de formatage
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'info'],
        'en_traitement' => ['label' => 'En traitement', 'class' => 'warning'],
        'approuvee' => ['label' => 'Approuvée', 'class' => 'success'],
        'refusee' => ['label' => 'Refusée', 'class' => 'danger']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary'];
}

function formatNiveauEtude($niveau) {
    $niveaux = [
        'licence1' => 'Licence 1',
        'licence2' => 'Licence 2',
        'licence3' => 'Licence 3',
        'master1' => 'Master 1',
        'master2' => 'Master 2',
        'master_termine' => 'Master terminé',
        'doctorat' => 'Doctorat',
        'bts' => 'BTS',
        'dut' => 'DUT',
        'inge' => 'Ingénieur',
        'commerce' => 'Commerce'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

function formatTestLangue($test) {
    $tests = [
        'non' => 'Non',
        'tcf' => 'TCF',
        'delf' => 'DELF',
        'dalf' => 'DALF',
        'ielts' => 'IELTS',
        'toefl' => 'TOEFL',
        'toeic' => 'TOEIC',
        'autre' => 'Autre'
    ];
    return $tests[$test] ?? $test;
}
?>