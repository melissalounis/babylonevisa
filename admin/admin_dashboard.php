<?php
session_start();

// Configuration et sécurité
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Inclure le fichier de configuration
include '../config.php';

// Vérifier et utiliser la connexion existante depuis config.php
if (!isset($pdo)) {
    // Si $pdo n'existe pas dans config.php, créer une nouvelle connexion
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=babylone_service;charset=utf8", "root", "", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    } catch(PDOException $e) {
        error_log("Erreur connexion BDD: " . $e->getMessage());
        die("Erreur de connexion à la base de données.");
    }
}

// Utiliser $pdo pour toutes les opérations (créer un alias $db pour la compatibilité)
$db = $pdo;

// Vérification admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Fonctions utilitaires existantes...
function countDemandesByType($db, $type, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM demandes WHERE type_demande = :type";
        $params = [':type' => $type];
        
        if ($statut) {
            $sql .= " AND statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countDemandesByType: " . $e->getMessage());
        return 0;
    }
}

// NOUVELLES FONCTIONS : Bourse Italie et Paiements
function countBoursesItalieByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM bourses_italie";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countBoursesItalieByStatut: " . $e->getMessage());
        return 0;
    }
}

function countPaiementsByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM paiements";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countPaiementsByStatut: " . $e->getMessage());
        return 0;
    }
}

// Fonctions existantes pour réservations hôtel et billets...
function countReservationsHotelByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM reservations_hotel";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countReservationsHotelByStatut: " . $e->getMessage());
        return 0;
    }
}

function countReservationsBilletByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM reservations_billet";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countReservationsBilletByStatut: " . $e->getMessage());
        return 0;
    }
}

// Fonctions existantes...
function countVisaEtudesByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM visa_etudes";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countVisaEtudesByStatut: " . $e->getMessage());
        return 0;
    }
}

function countContratsTravailByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM demandes_contrat_travail";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countContratsTravailByStatut: " . $e->getMessage());
        return 0;
    }
}

function countDemandesCVByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM demandes_creation_cv";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countDemandesCVByStatut: " . $e->getMessage());
        return 0;
    }
}

function countTotalDemandesByStatut($db, $statut) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM demandes WHERE statut = :statut");
        $stmt->execute([':statut' => $statut]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countTotalDemandesByStatut: " . $e->getMessage());
        return 0;
    }
}

function countNouveauxMessages($db) {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM messages WHERE lu = 0");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countNouveauxMessages: " . $e->getMessage());
        return 0;
    }
}

function countRendezVousByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM rendez_vous";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countRendezVousByStatut: " . $e->getMessage());
        return 0;
    }
}

// Nouvelles fonctions pour les demandes spécifiques
function countAttestationsProvinceByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM attestation_province";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countAttestationsProvinceByStatut: " . $e->getMessage());
        return 0;
    }
}

function countDemandesCAQByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM demandes_caq";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countDemandesCAQByStatut: " . $e->getMessage());
        return 0;
    }
}

function countVisaTouristiqueByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM visa_touristique";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countVisaTouristiqueByStatut: " . $e->getMessage());
        return 0;
    }
}

function countRendezVousBiometrieByStatut($db, $statut = null) {
    try {
        $sql = "SELECT COUNT(*) as count FROM rendez_vous_biometrie";
        $params = [];
        
        if ($statut) {
            $sql .= " WHERE statut = :statut";
            $params[':statut'] = $statut;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    } catch(PDOException $e) {
        error_log("Erreur countRendezVousBiometrieByStatut: " . $e->getMessage());
        return 0;
    }
}

// Fonction pour récupérer TOUTES les demandes récentes (tous types) - MISE À JOUR
function getAllRecentDemandes($db, $limit = 15) {
    try {
        // Union de toutes les tables de demandes
        $query = "
            (SELECT id, nom_complet, email, date_creation as date, statut, 'demande_generale' as type_source, type_demande as sous_type
             FROM demandes 
             ORDER BY date_creation DESC 
             LIMIT 5)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_soumission as date, statut, 'contrat_travail' as type_source, type_contrat as sous_type
             FROM demandes_contrat_travail 
             ORDER BY date_soumission DESC 
             LIMIT 3)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_creation as date, statut, 'creation_cv' as type_source, 'cv' as sous_type
             FROM demandes_creation_cv 
             ORDER BY date_creation DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_soumission as date, statut, 'attestation_province' as type_source, province as sous_type
             FROM attestation_province 
             ORDER BY date_soumission DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_soumission as date, statut, 'caq' as type_source, 'caq' as sous_type
             FROM demandes_caq 
             ORDER BY date_soumission DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_soumission as date, statut, 'visa_touristique' as type_source, 'touristique' as sous_type
             FROM visa_touristique 
             ORDER BY date_soumission DESC 
             LIMIT 1)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_soumission as date, statut, 'visa_etudes' as type_source, pays_destination as sous_type
             FROM visa_etudes 
             ORDER BY date_soumission DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_reservation as date, statut, 'reservation_hotel' as type_source, ville as sous_type
             FROM reservations_hotel 
             ORDER BY date_reservation DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_reservation as date, statut, 'reservation_billet' as type_source, destination as sous_type
             FROM reservations_billet 
             ORDER BY date_reservation DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_soumission as date, statut, 'bourse_italie' as type_source, universite as sous_type
             FROM bourses_italie 
             ORDER BY date_soumission DESC 
             LIMIT 2)
            
            UNION ALL
            
            (SELECT id, nom_complet, email, date_paiement as date, statut, 'paiement' as type_source, type_paiement as sous_type
             FROM paiements 
             ORDER BY date_paiement DESC 
             LIMIT 2)
            
            ORDER BY date DESC 
            LIMIT $limit
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
        
    } catch(PDOException $e) {
        error_log("Erreur getAllRecentDemandes: " . $e->getMessage());
        return [];
    }
}

// Récupération des données
$stats = [];
$toutes_dernieres_demandes = getAllRecentDemandes($db, 15);
$prochains_rendez_vous = [];
$derniers_contrats = [];
$dernieres_demandes_cv = [];
$dernieres_attestations_province = [];
$dernieres_demandes_caq = [];
$dernieres_visas_touristiques = [];
$dernieres_visas_etudes = [];
$dernieres_reservations_hotel = [];
$dernieres_reservations_billet = [];
$dernieres_bourses_italie = []; // NOUVEAU
$derniers_paiements = []; // NOUVEAU
$prochains_rdv_biometrie = [];

try {
    // Stats des demandes
    $types_demandes = ['sejour', 'etude', 'visa_travail', 'regroupement_familial', 'immigration', 'test_langue', 'rendez_vous'];
    
    foreach ($types_demandes as $type) {
        $query = "SELECT statut, COUNT(*) as count FROM demandes WHERE type_demande = :type GROUP BY statut";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':type', $type);
        $stmt->execute();
        $stats[$type] = $stmt->fetchAll();
    }
    
    // Dernières demandes de contrat de travail
    $query = "SELECT id, nom_complet, email, domaine_competence, type_contrat, date_soumission, statut 
              FROM demandes_contrat_travail 
              ORDER BY date_soumission DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $derniers_contrats = $stmt->fetchAll();
    
    // Dernières demandes de CV
    $query = "SELECT id, nom_complet, email, date_creation, statut 
              FROM demandes_creation_cv 
              ORDER BY date_creation DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dernieres_demandes_cv = $stmt->fetchAll();
    
    // Dernières attestations de province
    $query = "SELECT id, nom_complet, email, province, date_soumission, statut 
              FROM attestation_province 
              ORDER BY date_soumission DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dernieres_attestations_province = $stmt->fetchAll();
    
    // Dernières demandes CAQ
    $query = "SELECT id, nom_complet, email, date_soumission, statut 
              FROM demandes_caq 
              ORDER BY date_soumission DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dernieres_demandes_caq = $stmt->fetchAll();
    
    // Derniers visas touristiques
    $query = "SELECT id, nom_complet, email, date_depart, date_retour, date_soumission, statut 
              FROM visa_touristique 
              ORDER BY date_soumission DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dernieres_visas_touristiques = $stmt->fetchAll();
    
    // Derniers visas études
    $query = "SELECT id, nom_complet, email, pays_destination, etablissement, date_soumission, statut 
              FROM visa_etudes 
              ORDER BY date_soumission DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dernieres_visas_etudes = $stmt->fetchAll();
    
    // Dernières réservations d'hôtel
    $query = "SELECT id, nom_complet, email, ville, date_arrivee, date_depart, date_reservation, statut 
              FROM reservations_hotel 
              ORDER BY date_reservation DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dernieres_reservations_hotel = $stmt->fetchAll();
    
    // Dernières réservations de billets
    $query = "SELECT id, nom_complet, email, destination, date_depart, date_retour, date_reservation, statut 
              FROM reservations_billet 
              ORDER BY date_reservation DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dernieres_reservations_billet = $stmt->fetchAll();
    
    // NOUVEAU : Dernières bourses Italie
    $query = "SELECT id, nom_complet, email, universite, programme, date_soumission, statut 
              FROM bourses_italie 
              ORDER BY date_soumission DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dernieres_bourses_italie = $stmt->fetchAll();
    
    // NOUVEAU : Derniers paiements
    $query = "SELECT id, nom_complet, email, type_paiement, montant, date_paiement, statut 
              FROM paiements 
              ORDER BY date_paiement DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $derniers_paiements = $stmt->fetchAll();
    
    // Prochains rendez-vous
    $query = "SELECT rv.id, rv.date_rdv, rv.heure_rdv, rv.duree, rv.statut, 
                     d.nom_complet, d.email, d.telephone,
                     rv.type_rendez_vous, rv.notes
              FROM rendez_vous rv
              LEFT JOIN demandes d ON rv.demande_id = d.id
              WHERE rv.date_rdv >= CURDATE()
              ORDER BY rv.date_rdv, rv.heure_rdv 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $prochains_rendez_vous = $stmt->fetchAll();
    
    // Prochains rendez-vous biométrie
    $query = "SELECT id, nom_complet, email, date_rdv, heure_rdv, centre_biometrie, statut 
              FROM rendez_vous_biometrie 
              WHERE date_rdv >= CURDATE()
              ORDER BY date_rdv, heure_rdv 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $prochains_rdv_biometrie = $stmt->fetchAll();
    
} catch(PDOException $e) {
    error_log("Erreur récupération données: " . $e->getMessage());
}

// Définition des constantes - MISE À JOUR
$TYPES_DEMANDES = [
    'sejour' => 'Séjour', 
    'etude' => 'Étude',
    'visa_travail' => 'Visa Travail',
    'regroupement_familial' => 'Regroupement Familial',
    'immigration' => 'Immigration',
    'test_langue' => 'Test Langue',
    'rendez_vous' => 'Rendez-vous',
    'contrat_travail' => 'Contrat Travail',
    'creation_cv' => 'Création CV',
    'attestation_province' => 'Attestation Province',
    'caq' => 'CAQ Québec',
    'visa_touristique' => 'Visa Touristique Canada',
    'visa_etudes' => 'Visa Études',
    'reservation_hotel' => 'Réservation Hôtel',
    'reservation_billet' => 'Réservation Billet',
    'biometrie' => 'Rendez-vous Biométrie',
    'bourse_italie' => 'Bourse Italie', // NOUVEAU
    'paiement' => 'Paiements' // NOUVEAU
];

$SOURCES_TYPES = [
    'demande_generale' => 'Demande Générale',
    'contrat_travail' => 'Contrat Travail',
    'creation_cv' => 'Création CV',
    'attestation_province' => 'Attestation Province',
    'caq' => 'CAQ Québec',
    'visa_touristique' => 'Visa Touristique',
    'visa_etudes' => 'Visa Études',
    'reservation_hotel' => 'Réservation Hôtel',
    'reservation_billet' => 'Réservation Billet',
    'bourse_italie' => 'Bourse Italie', // NOUVEAU
    'paiement' => 'Paiement' // NOUVEAU
];

$STATUTS_CLASSES = [
    'en_attente' => 'bg-warning',
    'en_cours' => 'bg-info',
    'confirme' => 'bg-success',
    'annule' => 'bg-danger',
    'rejetee' => 'bg-danger',
    'traitee' => 'bg-success',
    'en_traitement' => 'bg-info',
    'nouveau' => 'bg-primary',
    'accepte' => 'bg-success',
    'refuse' => 'bg-danger',
    'approuvee' => 'bg-success',
    'confirmee' => 'bg-success',
    'en_attente_paiement' => 'bg-warning',
    'paye' => 'bg-success',
    'en_attente_validation' => 'bg-warning'
];

// Calcul des totaux
$total_en_attente = countTotalDemandesByStatut($db, 'en_attente');
$total_en_cours = countTotalDemandesByStatut($db, 'en_cours');
$total_traitee = countTotalDemandesByStatut($db, 'traitee');
$total_rejetee = countTotalDemandesByStatut($db, 'rejetee');
$total_rdv_confirmes = countRendezVousByStatut($db, 'confirme');
$total_rdv_en_attente = countRendezVousByStatut($db, 'en_attente');

// Totaux pour contrats de travail
$total_contrats_attente = countContratsTravailByStatut($db, 'en_attente');
$total_contrats_cours = countContratsTravailByStatut($db, 'en_cours');
$total_contrats = countContratsTravailByStatut($db);

// Totaux pour demandes CV
$total_cv_attente = countDemandesCVByStatut($db, 'en_traitement');
$total_cv = countDemandesCVByStatut($db);

// Totaux pour attestations de province
$total_attestations_attente = countAttestationsProvinceByStatut($db, 'nouveau');
$total_attestations = countAttestationsProvinceByStatut($db);

// Totaux pour demandes CAQ
$total_caq_attente = countDemandesCAQByStatut($db, 'nouveau');
$total_caq = countDemandesCAQByStatut($db);

// Totaux pour visas touristiques
$total_visa_touristique_attente = countVisaTouristiqueByStatut($db, 'nouveau');
$total_visa_touristique = countVisaTouristiqueByStatut($db);

// Totaux pour visas études
$total_visa_etudes_attente = countVisaEtudesByStatut($db, 'nouveau');
$total_visa_etudes = countVisaEtudesByStatut($db);

// Totaux pour réservations hôtel
$total_hotel_attente = countReservationsHotelByStatut($db, 'en_attente');
$total_hotel = countReservationsHotelByStatut($db);

// Totaux pour réservations billets
$total_billet_attente = countReservationsBilletByStatut($db, 'en_attente');
$total_billet = countReservationsBilletByStatut($db);

// NOUVEAU : Totaux pour bourses Italie
$total_bourse_italie_attente = countBoursesItalieByStatut($db, 'en_attente');
$total_bourse_italie = countBoursesItalieByStatut($db);

// NOUVEAU : Totaux pour paiements
$total_paiement_attente = countPaiementsByStatut($db, 'en_attente_paiement');
$total_paiement = countPaiementsByStatut($db);

// Totaux pour rendez-vous biométrie
$total_biometrie_attente = countRendezVousBiometrieByStatut($db, 'en_attente');
$total_biometrie = countRendezVousBiometrieByStatut($db);

// Calcul du total général de toutes les demandes
$total_general_demandes = $total_en_attente + $total_en_cours + $total_traitee + $total_rejetee;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Tableau de bord administrateur Babylone Service">
    <title>Tableau de Bord Administrateur - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1e4a7b;
            --secondary-color: #2c6aa0;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --purple-color: #6f42c1;
            --orange-color: #fd7e14;
            --teal-color: #20c997;
            --indigo-color: #6610f2;
            --pink-color: #e83e8c;
            --cyan-color: #0dcaf0;
            --brown-color: #795548;
            --hotel-color: #17a2b8;
            --billet-color: #6f42c1;
            --bourse-color: #e91e63; /* NOUVEAU : Couleur pour bourses */
            --paiement-color: #4caf50; /* NOUVEAU : Couleur pour paiements */
            --border-radius: 10px;
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html, body {
            height: 100%;
            overflow-x: hidden;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, #153a5e 100%);
            color: white;
            width: 250px;
            transition: var(--transition);
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            transition: var(--transition);
            background-color: #f8f9fa;
            min-height: 100vh;
            overflow-y: auto;
        }
        
        .dashboard-card {
            transition: var(--transition);
            border: none;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card {
            border-left: 4px solid;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .stat-card.en-attente { border-left-color: var(--warning-color); }
        .stat-card.refusees { border-left-color: var(--danger-color); }
        .stat-card.acceptees { border-left-color: var(--success-color); }
        .stat-card.total { border-left-color: var(--primary-color); }
        
        .navbar-custom {
            background-color: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            border-radius: var(--border-radius);
            padding: 10px 20px;
            margin-bottom: 20px;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 12px 20px;
            margin: 4px 10px;
            border-radius: var(--border-radius);
            transition: var(--transition);
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-brand {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 10px;
        }
        
        .badge-notification {
            font-size: 0.7em;
            padding: 4px 8px;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .text-purple {
            color: var(--purple-color) !important;
        }
        
        .text-orange {
            color: var(--orange-color) !important;
        }
        
        .text-teal {
            color: var(--teal-color) !important;
        }
        
        .text-indigo {
            color: var(--indigo-color) !important;
        }
        
        .text-pink {
            color: var(--pink-color) !important;
        }
        
        .text-cyan {
            color: var(--cyan-color) !important;
        }
        
        .text-brown {
            color: var(--brown-color) !important;
        }
        
        .text-hotel {
            color: var(--hotel-color) !important;
        }
        
        .text-billet {
            color: var(--billet-color) !important;
        }
        
        .text-bourse {
            color: var(--bourse-color) !important; /* NOUVEAU */
        }
        
        .text-paiement {
            color: var(--paiement-color) !important; /* NOUVEAU */
        }
        
        .activity-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        /* Scrollbar personnalisée */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.5);
        }
        
        .main-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .main-content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .main-content::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .main-content::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                width: 250px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            .stat-card::before {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="sidebar-brand">
                <h3><i class="fas fa-cogs me-2"></i>Babylone Service</h3>
                <small class="text-white-50">Espace Administrateur</small>
            </div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="admin_dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="demandes_sejour.php">
                        <i class="fas fa-passport me-2"></i>
                        Demandes de séjour
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo countDemandesByType($db, 'sejour', 'en_attente'); ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="demandes_etude.php">
                        <i class="fas fa-graduation-cap me-2"></i>
                        Demandes d'étude
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo countDemandesByType($db, 'etude', 'en_attente'); ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="visa_travail.php">
                        <i class="fas fa-briefcase me-2"></i>
                        Visa de travail
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo countDemandesByType($db, 'visa_travail', 'en_attente'); ?>
                        </span>
                    </a>
                </li>
                <!-- Visa Études -->
                <li class="nav-item">
                    <a class="nav-link" href="visa_etudes.php">
                        <i class="fas fa-university me-2"></i>
                        Visa Études
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_visa_etudes_attente; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contrat_travail.php">
                        <i class="fas fa-file-contract me-2"></i>
                        Contrats de travail
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_contrats_attente; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="demandes_cv.php">
                        <i class="fas fa-file-alt me-2"></i>
                        Création de CV
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_cv_attente; ?>
                        </span>
                    </a>
                </li>
                <!-- Nouvelles sections ajoutées -->
                <li class="nav-item">
                    <a class="nav-link" href="provinceadmin.php">
                        <i class="fas fa-file-certificate me-2"></i>
                        Attestations Province
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_attestations_attente; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="demandes_caq.php">
                        <i class="fas fa-graduation-cap me-2"></i>
                        CAQ Québec
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_caq_attente; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="visa_touristique.php">
                        <i class="fas fa-plane me-2"></i>
                        Visa Touristique canada
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_visa_touristique_attente; ?>
                        </span>
                    </a>
                </li>
                <!-- Réservations Hôtel et Billets -->
                <li class="nav-item">
                    <a class="nav-link" href="reservations_hotel.php">
                        <i class="fas fa-hotel me-2"></i>
                        Réservations Hôtel
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_hotel_attente; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reservations_billet.php">
                        <i class="fas fa-ticket-alt me-2"></i>
                        Réservations Billets
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_billet_attente; ?>
                        </span>
                    </a>
                </li>
                <!-- NOUVELLES SECTIONS : Bourse Italie et Paiements -->
                <li class="nav-item">
                    <a class="nav-link" href="bourse_italie.php">
                        <i class="fas fa-graduation-cap me-2 text-bourse"></i>
                        Bourse Italie
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_bourse_italie_attente; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="paiement.php">
                        <i class="fas fa-credit-card me-2 text-paiement"></i>
                        Paiements
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_paiement_attente; ?>
                        </span>
                    </a>
                </li>
                <!-- Fin nouvelles sections -->
                <li class="nav-item">
                    <a class="nav-link" href="biometrie.php">
                        <i class="fas fa-fingerprint me-2"></i>
                        Biométrie
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_biometrie_attente; ?>
                        </span>
                    </a>
                </li>
                <!-- Fin nouvelles sections -->
                <li class="nav-item">
                    <a class="nav-link" href="regroupement_familial.php">
                        <i class="fas fa-users me-2"></i>
                        Regroupement familial
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo countDemandesByType($db, 'regroupement_familial', 'en_attente'); ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="immigration.php">
                        <i class="fas fa-globe-americas me-2"></i>
                        Immigration
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo countDemandesByType($db, 'immigration', 'en_attente'); ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="tests_langue.php">
                        <i class="fas fa-language me-2"></i>
                        Tests de langue
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo countDemandesByType($db, 'test_langue', 'en_attente'); ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rendez_vous.php">
                        <i class="fas fa-calendar-check me-2"></i>
                        Rendez-vous
                        <span class="badge bg-warning badge-notification float-end">
                            <?php echo $total_rdv_en_attente; ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="messages.php">
                        <i class="fas fa-envelope me-2"></i>
                        Messages
                        <span class="badge bg-primary badge-notification float-end">
                            <?php echo countNouveauxMessages($db); ?>
                        </span>
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="parametres.php">
                        <i class="fas fa-cog me-2"></i>
                        Paramètres
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');">
                        <i class="fas fa-sign-out-alt me-2"></i>
                        Déconnexion
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Navigation Bar -->
            <nav class="navbar navbar-expand-lg navbar-custom">
                <div class="container-fluid">
                    <button class="btn btn-primary d-md-none" type="button" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="d-none d-md-block">
                        <span class="navbar-text">
                            <i class="fas fa-calendar-day me-1"></i>
                            <?php echo date('d/m/Y'); ?>
                        </span>
                    </div>
                    
                    <div class="navbar-nav ms-auto">
                        <div class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2 fa-lg"></i>
                                <div class="d-none d-sm-block">
                                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['admin_nom'] ?? 'Administrateur'); ?></div>
                                    <small class="text-muted">Administrateur</small>
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="profil.php"><i class="fas fa-user me-2"></i>Mon profil</a></li>
                                <li><a class="dropdown-item" href="parametres.php"><i class="fas fa-cog me-2"></i>Paramètres</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?');"><i class="fas fa-sign-out-alt me-2"></i>Déconnexion</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Dashboard Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1 text-dark">Tableau de Bord Administrateur</h1>
                    <p class="text-muted mb-0">Bienvenue, <?php echo htmlspecialchars($_SESSION['admin_nom'] ?? 'Administrateur'); ?></p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary" id="exportBtn">
                        <i class="fas fa-download me-1"></i> Exporter
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRequestModal">
                        <i class="fas fa-plus me-1"></i> Nouvelle Demande
                    </button>
                </div>
            </div>

            <!-- Statistics Cards - SEULEMENT 4 CARTES -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card en-attente">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted mb-2">En Attente</h6>
                                    <h3 class="fw-bold text-warning"><?php echo $total_en_attente; ?></h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                </div>
                            </div>
                            <p class="card-text text-muted mb-0">Demandes en attente de traitement</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card refusees">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted mb-2">Refusées</h6>
                                    <h3 class="fw-bold text-danger"><?php echo $total_rejetee; ?></h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                </div>
                            </div>
                            <p class="card-text text-muted mb-0">Demandes refusées</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card acceptees">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted mb-2">Acceptées</h6>
                                    <h3 class="fw-bold text-success"><?php echo $total_traitee; ?></h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                </div>
                            </div>
                            <p class="card-text text-muted mb-0">Demandes acceptées/traitées</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card total">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title text-muted mb-2">Total Demandes</h6>
                                    <h3 class="fw-bold text-primary"><?php echo $total_general_demandes; ?></h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-bar fa-2x text-primary"></i>
                                </div>
                            </div>
                            <p class="card-text text-muted mb-0">Total des demandes reçues</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Access Cards -->
            <div class="row mb-5">
                <div class="col-lg-12">
                    <!-- Recent Activity Table - TOUTES LES DEMANDES -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history me-2"></i>
                                Toutes les Demandes Récentes
                            </h5>
                            <a href="toutes_demandes.php" class="btn btn-sm btn-outline-primary">
                                Voir toutes les demandes
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Type</th>
                                            <th>Sous-type</th>
                                            <th>Nom Complet</th>
                                            <th>Email</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($toutes_dernieres_demandes)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4">
                                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                    <p class="text-muted">Aucune demande récente</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach($toutes_dernieres_demandes as $demande): ?>
                                            <tr>
                                                <td class="fw-bold">#<?php echo htmlspecialchars($demande['id']); ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo htmlspecialchars($SOURCES_TYPES[$demande['type_source']] ?? $demande['type_source']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($demande['sous_type']); ?>
                                                    </small>
                                                </td>
                                                <td><?php echo htmlspecialchars($demande['nom_complet']); ?></td>
                                                <td>
                                                    <a href="mailto:<?php echo htmlspecialchars($demande['email']); ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars($demande['email']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($demande['date'])); ?></td>
                                                <td>
                                                    <span class="badge <?php echo $STATUTS_CLASSES[$demande['statut']] ?? 'bg-secondary'; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $demande['statut'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php
                                                        $detail_url = '';
                                                        switch($demande['type_source']) {
                                                            case 'demande_generale':
                                                                $detail_url = "voir_demande.php?id={$demande['id']}";
                                                                break;
                                                            case 'contrat_travail':
                                                                $detail_url = "voir_contrat.php?id={$demande['id']}";
                                                                break;
                                                            case 'creation_cv':
                                                                $detail_url = "voir_cv.php?id={$demande['id']}";
                                                                break;
                                                            case 'attestation_province':
                                                                $detail_url = "voir_attestation.php?id={$demande['id']}";
                                                                break;
                                                            case 'caq':
                                                                $detail_url = "voir_caq.php?id={$demande['id']}";
                                                                break;
                                                            case 'visa_touristique':
                                                                $detail_url = "voir_visa_touristique.php?id={$demande['id']}";
                                                                break;
                                                            case 'visa_etudes':
                                                                $detail_url = "voir_visa_etudes.php?id={$demande['id']}";
                                                                break;
                                                            case 'reservation_hotel':
                                                                $detail_url = "voir_reservation_hotel.php?id={$demande['id']}";
                                                                break;
                                                            case 'reservation_billet':
                                                                $detail_url = "voir_reservation_billet.php?id={$demande['id']}";
                                                                break;
                                                            case 'bourse_italie': // NOUVEAU
                                                                $detail_url = "voir_bourse_italie.php?id={$demande['id']}";
                                                                break;
                                                            case 'paiement': // NOUVEAU
                                                                $detail_url = "voir_paiement.php?id={$demande['id']}";
                                                                break;
                                                            default:
                                                                $detail_url = "#";
                                                        }
                                                        ?>
                                                        <a href="<?php echo $detail_url; ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="Voir les détails">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information Row -->
            <div class="row">
                <!-- Sidebar Right -->
                <div class="col-lg-4">
                    <!-- NOUVEAU : Dernières Bourses Italie -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-graduation-cap me-2 text-bourse"></i>
                                Dernières Bourses Italie
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dernieres_bourses_italie)): ?>
                                <p class="text-muted text-center">Aucune bourse Italie récente</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($dernieres_bourses_italie as $bourse): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($bourse['nom_complet']); ?></h6>
                                            <small class="text-<?php echo ($bourse['statut'] == 'approuvee') ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($bourse['statut']); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1 small">
                                            <i class="fas fa-university me-1"></i>
                                            <?php echo htmlspecialchars($bourse['universite']); ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($bourse['date_soumission'])); ?>
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="bourse_italie.php" class="btn btn-outline-bourse btn-sm mt-3 w-100">
                                    Voir toutes les bourses
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Derniers Visas Études -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-university me-2"></i>
                                Derniers Visas Études
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dernieres_visas_etudes)): ?>
                                <p class="text-muted text-center">Aucun visa études récent</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($dernieres_visas_etudes as $visa): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($visa['nom_complet']); ?></h6>
                                            <small class="text-<?php echo ($visa['statut'] == 'approuve') ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($visa['statut']); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1 small">
                                            <i class="fas fa-globe me-1"></i>
                                            <?php echo htmlspecialchars($visa['pays_destination']); ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($visa['date_soumission'])); ?>
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="visa_etudes.php" class="btn btn-outline-info btn-sm mt-3 w-100">
                                    Voir tous les visas études
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- NOUVEAU : Derniers Paiements -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-credit-card me-2 text-paiement"></i>
                                Derniers Paiements
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($derniers_paiements)): ?>
                                <p class="text-muted text-center">Aucun paiement récent</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($derniers_paiements as $paiement): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($paiement['nom_complet']); ?></h6>
                                            <small class="text-<?php echo ($paiement['statut'] == 'paye') ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($paiement['statut']); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1 small">
                                            <i class="fas fa-money-bill-wave me-1"></i>
                                            <?php echo htmlspecialchars($paiement['type_paiement']); ?> - 
                                            <?php echo number_format($paiement['montant'], 2, ',', ' '); ?> €
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($paiement['date_paiement'])); ?>
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="paiement.php" class="btn btn-outline-paiement btn-sm mt-3 w-100">
                                    Voir tous les paiements
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Derniers Visas Touristiques -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-plane me-2"></i>
                                Derniers Visas Touristiques
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($dernieres_visas_touristiques)): ?>
                                <p class="text-muted text-center">Aucun visa touristique récent</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($dernieres_visas_touristiques as $visa): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($visa['nom_complet']); ?></h6>
                                            <small class="text-<?php echo ($visa['statut'] == 'approuve') ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($visa['statut']); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1 small">
                                            <i class="fas fa-plane-departure me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($visa['date_depart'])); ?> - 
                                            <?php echo date('d/m/Y', strtotime($visa['date_retour'])); ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($visa['date_soumission'])); ?>
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="visa_touristique.php" class="btn btn-outline-success btn-sm mt-3 w-100">
                                    Voir tous les visas
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Prochains Rendez-vous -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt me-2"></i>
                                Prochains RDV
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($prochains_rendez_vous)): ?>
                                <p class="text-muted text-center">Aucun rendez-vous à venir</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($prochains_rendez_vous as $rdv): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($rdv['nom_complet'] ?? 'Non spécifié'); ?></h6>
                                            <small class="text-<?php echo ($rdv['statut'] == 'confirme') ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($rdv['statut']); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1 small">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($rdv['date_rdv'])); ?>
                                            à <?php echo $rdv['heure_rdv']; ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo $rdv['duree'] ?? '30'; ?> min
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="rendez_vous.php" class="btn btn-outline-primary btn-sm mt-3 w-100">
                                    Voir tous les rendez-vous
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Derniers Contrats de Travail -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-file-contract me-2"></i>
                                Derniers Contrats
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($derniers_contrats)): ?>
                                <p class="text-muted text-center">Aucun contrat récent</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($derniers_contrats as $contrat): ?>
                                    <div class="list-group-item px-0">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($contrat['nom_complet']); ?></h6>
                                            <small class="text-<?php echo ($contrat['statut'] == 'approuvee') ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($contrat['statut']); ?>
                                            </small>
                                        </div>
                                        <p class="mb-1 small">
                                            <i class="fas fa-briefcase me-1"></i>
                                            <?php echo htmlspecialchars($contrat['domaine_competence']); ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo date('d/m/Y', strtotime($contrat['date_soumission'])); ?>
                                        </small>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <a href="contrat_travail.php" class="btn btn-outline-primary btn-sm mt-3 w-100">
                                    Voir tous les contrats
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Request Modal -->
    <div class="modal fade" id="newRequestModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Demande</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Sélectionnez le type de demande à créer :</p>
                    <div class="d-grid gap-2">
                        <?php foreach($TYPES_DEMANDES as $key => $value): ?>
                        <a href="nouvelle_demande.php?type=<?php echo $key; ?>" class="btn btn-outline-primary text-start">
                            <i class="fas fa-arrow-right me-2"></i><?php echo $value; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
        
        // Export functionality
        document.getElementById('exportBtn').addEventListener('click', function() {
            const btn = this;
            const originalHTML = btn.innerHTML;
            
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Export...';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
                alert('Export terminé avec succès!');
            }, 2000);
        });
        
        // Close sidebar when clicking on a link in mobile view
        if (window.innerWidth < 768) {
            document.querySelectorAll('.sidebar .nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    document.querySelector('.sidebar').classList.remove('active');
                });
            });
        }

        // Force scrollbars
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');
            
            // S'assurer que les éléments peuvent scroller
            sidebar.style.overflowY = 'auto';
            mainContent.style.overflowY = 'auto';
            
            // Forcer l'affichage des scrollbars si le contenu dépasse
            if (sidebar.scrollHeight > sidebar.clientHeight) {
                sidebar.style.overflowY = 'scroll';
            }
            if (mainContent.scrollHeight > mainContent.clientHeight) {
                mainContent.style.overflowY = 'scroll';
            }
        });
    </script>
</body>
</html>