<?php
session_start();

// Vérifier si l'administrateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}


// Connexion à la base de données
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID manquant']);
    exit();
}

$id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM etude_belgique WHERE id = ?");
    $stmt->execute([$id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($demande) {
        
        // Préparer la liste des documents avec leurs informations
        $documents = [];
        
        // Documents obligatoires
        $documents_obligatoires = [
            'photo' => ['nom' => 'Photo d\'identité', 'icon' => 'fas fa-image', 'couleur' => 'success'],
            'passport' => ['nom' => 'Passeport', 'icon' => 'fas fa-passport', 'couleur' => 'primary']
        ];
        
        foreach ($documents_obligatoires as $champ => $info) {
            if (!empty($demande[$champ])) {
                $documents[] = [
                    'type' => $champ,
                    'nom' => $info['nom'],
                    'fichier' => $demande[$champ],
                    'icon' => $info['icon'],
                    'couleur' => $info['couleur'],
                    'categorie' => 'Documents obligatoires'
                ];
            }
        }
        
        // Document d'équivalence
        if (!empty($demande['document_equivalence'])) {
            $documents[] = [
                'type' => 'document_equivalence',
                'nom' => 'Équivalence du Bac',
                'fichier' => $demande['document_equivalence'],
                'icon' => 'fas fa-balance-scale',
                'couleur' => 'info',
                'categorie' => 'Équivalence'
            ];
        }
        
        // Documents académiques
        $documents_academiques = [
            'releve_2nde' => 'Relevé de notes 2nde',
            'releve_1ere' => 'Relevé de notes 1ère',
            'releve_terminale' => 'Relevé de notes Terminale',
            'releve_bac' => 'Relevé de notes Bac',
            'diplome_bac' => 'Diplôme du Bac',
            'releve_l1' => 'Relevé de notes Licence 1',
            'releve_l2' => 'Relevé de notes Licence 2',
            'releve_l3' => 'Relevé de notes Licence 3',
            'diplome_licence' => 'Diplôme de Licence',
            'certificat_scolarite' => 'Certificat de scolarité'
        ];
        
        foreach ($documents_academiques as $champ => $nom) {
            if (!empty($demande[$champ])) {
                $documents[] = [
                    'type' => $champ,
                    'nom' => $nom,
                    'fichier' => $demande[$champ],
                    'icon' => 'fas fa-file-certificate',
                    'couleur' => 'warning',
                    'categorie' => 'Documents académiques'
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'demande' => [
                'id' => $demande['id'],
                'nom' => $demande['nom'],
                'naissance' => $demande['naissance'],
                'nationalite' => $demande['nationalite'],
                'telephone' => $demande['telephone'],
                'email' => $demande['email'],
                'adresse' => $demande['adresse'],
                'niveau_etude' => $demande['niveau_etude'],
                'equivalence_bac' => $demande['equivalence_bac'],
                'status' => $demande['status'],
                'created_at' => $demande['created_at'],
                'updated_at' => $demande['updated_at'],
                'documents' => $documents
            ]
        ]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Demande non trouvée']);
    }
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données: ' . $e->getMessage()]);
}
?>