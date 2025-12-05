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

require_once '../config.php';

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
    $stmt = $pdo->prepare("SELECT * FROM demandes_etudes_espagne WHERE id = ?");
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
            'bac' => 'Baccalauréat',
            'l1' => 'Licence 1',
            'l2' => 'Licence 2',
            'l3' => 'Licence 3',
            'master1' => 'Master 1',
            'master2' => 'Master 2',
            'master2_termine' => 'Master 2 terminé'
        ];
        return $niveaux[$niveau] ?? $niveau;
    }
    
    function formatTypeService($type) {
        $types = [
            'admission' => 'Admission',
            'admission_logement' => 'Admission + Logement',
            'admission_visa' => 'Admission + Visa',
            'complet' => 'Service complet'
        ];
        return $types[$type] ?? $type;
    }
    
    function formatStatut($statut) {
        $statuts = [
            'nouveau' => 'Nouveau',
            'en_cours' => 'En cours',
            'approuve' => 'Approuvé',
            'refuse' => 'Refusé'
        ];
        return $statuts[$statut] ?? $statut;
    }
    
    function getStatutClass($statut) {
        $classes = [
            'nouveau' => 'info',
            'en_cours' => 'warning',
            'approuve' => 'success',
            'refuse' => 'danger'
        ];
        return $classes[$statut] ?? 'secondary';
    }

    // Préparer la réponse
    $responseData = [
        'success' => true,
        'demande' => [
            'id' => $demande['id'],
            'nom_complet' => $demande['nom_complet'] ?? '',
            'date_naissance' => $demande['date_naissance'] ?? '',
            'nationalite' => $demande['nationalite'] ?? '',
            'email' => $demande['email'] ?? '',
            'telephone' => $demande['telephone'] ?? '',
            'adresse' => $demande['adresse'] ?? '',
            'niveau_etude' => $demande['niveau_etude'] ?? '',
            'niveau_etude_formatted' => formatNiveauEtude($demande['niveau_etude'] ?? ''),
            'universite_souhaitee' => $demande['universite_souhaitee'] ?? '',
            'programme_etude' => $demande['programme_etude'] ?? '',
            'type_service' => $demande['type_service'] ?? '',
            'type_service_formatted' => formatTypeService($demande['type_service'] ?? ''),
            'test_langue' => $demande['test_langue'] ?? '',
            'score_test' => $demande['score_test'] ?? '',
            'demander_test' => $demande['demander_test'] ?? '',
            'statut' => $demande['statut'] ?? '',
            'statut_formatted' => formatStatut($demande['statut'] ?? ''),
            'statut_class' => getStatutClass($demande['statut'] ?? ''),
            'date_soumission' => $demande['date_soumission'] ?? '',
            'date_modification' => $demande['date_modification'] ?? '',
            // Documents
            'passeport' => $demande['passeport'] ?? '',
            'lettre_admission' => $demande['lettre_admission'] ?? '',
            'photo' => $demande['photo'] ?? '',
            'certificat_medical' => $demande['certificat_medical'] ?? '',
            'casier_judiciaire' => $demande['casier_judiciaire'] ?? '',
            'releve_2nde' => $demande['releve_2nde'] ?? '',
            'releve_1ere' => $demande['releve_1ere'] ?? '',
            'releve_terminale' => $demande['releve_terminale'] ?? '',
            'releve_bac' => $demande['releve_bac'] ?? '',
            'diplome_bac' => $demande['diplome_bac'] ?? '',
            'releve_l1' => $demande['releve_l1'] ?? '',
            'releve_l2' => $demande['releve_l2'] ?? '',
            'releve_l3' => $demande['releve_l3'] ?? '',
            'diplome_licence' => $demande['diplome_licence'] ?? '',
            'certificat_scolarite' => $demande['certificat_scolarite'] ?? '',
            'releve_M1' => $demande['releve_M1'] ?? '',
            'releve_M2' => $demande['releve_M2'] ?? '',
            'diplome_Master' => $demande['diplome_Master'] ?? '',
            'test_langue_fichier' => $demande['test_langue_fichier'] ?? '',
            'annexe' => $demande['annexe'] ?? '',
            'documents_supplementaires' => $demande['documents_supplementaires'] ?? ''
        ]
    ];

    // Envoyer la réponse JSON
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($responseData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
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