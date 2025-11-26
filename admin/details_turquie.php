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
    echo json_encode(['success' => false, 'message' => 'Erreur de connexion à la base de données: ' . $e->getMessage()]);
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
    $stmt = $pdo->prepare("SELECT * FROM demandes_turquie WHERE id = ?");
    $stmt->execute([$id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$demande) {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
        exit();
    }
    
    // Récupérer tous les fichiers associés
    $fichiers = [];
    $stmt_fichiers = $pdo->prepare("SELECT * FROM demandes_turquie_fichiers WHERE demande_id = ?");
    $stmt_fichiers->execute([$id]);
    $fichiers_data = $stmt_fichiers->fetchAll(PDO::FETCH_ASSOC);
    
    // Organiser les fichiers par type
    $fichiers_organises = [
        'diplomes' => [],
        'releves_notes' => [],
        'certificats_scolarite' => [],
        'certificats_langue' => [],
        'documents_supplementaires' => []
    ];
    
    // Fonction pour obtenir le nom d'affichage du fichier
    function getNomFichier($type_fichier) {
        $noms = [
            'diplome_bac' => 'Diplôme du Baccalauréat',
            'diplome_licence' => 'Diplôme de Licence',
            'releve_2nde' => 'Relevé de notes 2nde',
            'releve_1ere' => 'Relevé de notes 1ère',
            'releve_terminale' => 'Relevé de notes Terminale',
            'releve_bac' => 'Relevé de notes Bac',
            'releve_l1' => 'Relevé de notes Licence 1',
            'releve_l2' => 'Relevé de notes Licence 2',
            'releve_l3' => 'Relevé de notes Licence 3',
            'certificat_scolarite' => 'Certificat de scolarité',
            'certificat_langue' => 'Certificat de langue',
            'document_supplementaire' => 'Document supplémentaire'
        ];
        
        foreach ($noms as $key => $value) {
            if (strpos($type_fichier, $key) !== false) {
                return $value;
            }
        }
        
        return $type_fichier;
    }
    
    foreach ($fichiers_data as $fichier) {
        $type = $fichier['type_fichier'];
        
        if (strpos($type, 'diplome') !== false) {
            $fichiers_organises['diplomes'][] = [
                'id' => $fichier['id'],
                'nom_fichier' => $fichier['nom_fichier'],
                'type_fichier' => $fichier['type_fichier'],
                'chemin_fichier' => $fichier['chemin_fichier'],
                'date_upload' => $fichier['date_upload'],
                'nom_affichage' => getNomFichier($fichier['type_fichier'])
            ];
        } elseif (strpos($type, 'releve_') !== false) {
            $fichiers_organises['releves_notes'][] = [
                'id' => $fichier['id'],
                'nom_fichier' => $fichier['nom_fichier'],
                'type_fichier' => $fichier['type_fichier'],
                'chemin_fichier' => $fichier['chemin_fichier'],
                'date_upload' => $fichier['date_upload'],
                'nom_affichage' => getNomFichier($fichier['type_fichier'])
            ];
        } elseif (strpos($type, 'certificat_scolarite') !== false) {
            $fichiers_organises['certificats_scolarite'][] = [
                'id' => $fichier['id'],
                'nom_fichier' => $fichier['nom_fichier'],
                'type_fichier' => $fichier['type_fichier'],
                'chemin_fichier' => $fichier['chemin_fichier'],
                'date_upload' => $fichier['date_upload'],
                'nom_affichage' => getNomFichier($fichier['type_fichier'])
            ];
        } elseif (strpos($type, 'certificat_langue') !== false) {
            $fichiers_organises['certificats_langue'][] = [
                'id' => $fichier['id'],
                'nom_fichier' => $fichier['nom_fichier'],
                'type_fichier' => $fichier['type_fichier'],
                'chemin_fichier' => $fichier['chemin_fichier'],
                'date_upload' => $fichier['date_upload'],
                'nom_affichage' => getNomFichier($fichier['type_fichier'])
            ];
        } elseif (strpos($type, 'document_supplementaire') !== false) {
            $fichiers_organises['documents_supplementaires'][] = [
                'id' => $fichier['id'],
                'nom_fichier' => $fichier['nom_fichier'],
                'type_fichier' => $fichier['type_fichier'],
                'chemin_fichier' => $fichier['chemin_fichier'],
                'date_upload' => $fichier['date_upload'],
                'nom_affichage' => getNomFichier($fichier['type_fichier'])
            ];
        } else {
            // Fichier non catégorisé
            $fichiers_organises['documents_supplementaires'][] = [
                'id' => $fichier['id'],
                'nom_fichier' => $fichier['nom_fichier'],
                'type_fichier' => $fichier['type_fichier'],
                'chemin_fichier' => $fichier['chemin_fichier'],
                'date_upload' => $fichier['date_upload'],
                'nom_affichage' => getNomFichier($fichier['type_fichier'])
            ];
        }
    }
    
    // Fonctions de formatage
    function formatNiveauEtude($niveau) {
        $niveaux = [
            'bac' => 'Bac',
            'l1' => 'Licence 1',
            'l2' => 'Licence 2',
            'l3' => 'Licence 3',
            'master' => 'Master'
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
    
    function formatLangue($langue) {
        $langues = [
            'turc' => 'Turc',
            'anglais' => 'Anglais'
        ];
        return $langues[$langue] ?? $langue;
    }

    // Préparer la réponse JSON
    $responseData = [
        'success' => true,
        'demande' => [
            'id' => $demande['id'],
            'user_id' => $demande['user_id'] ?? '',
            'nom' => $demande['nom'] ?? '',
            'prenom' => $demande['prenom'] ?? '',
            'date_naissance' => $demande['date_naissance'] ?? '',
            'nationalite' => $demande['nationalite'] ?? '',
            'email' => $demande['email'] ?? '',
            'telephone' => $demande['telephone'] ?? '',
            'specialite' => $demande['specialite'] ?? '',
            'programme_langue' => $demande['programme_langue'] ?? '',
            'langue_formatted' => formatLangue($demande['programme_langue'] ?? ''),
            'certificat_type' => $demande['certificat_type'] ?? '',
            'certificat_score' => $demande['certificat_score'] ?? '',
            'niveau' => $demande['niveau'] ?? '',
            'niveau_formatted' => formatNiveauEtude($demande['niveau'] ?? ''),
            'statut' => $demande['statut'] ?? '',
            'statut_formatted' => formatStatut($demande['statut'] ?? ''),
            'statut_class' => getStatutClass($demande['statut'] ?? ''),
            'date_creation' => $demande['date_creation'] ?? '',
            'date_modification' => $demande['date_modification'] ?? '',
            'commentaire' => $demande['commentaire'] ?? '',
            'fichiers' => $fichiers_organises,
            'total_fichiers' => count($fichiers_data)
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