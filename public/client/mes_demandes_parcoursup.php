<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer les demandes Parcoursup de l'utilisateur
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_parcoursup 
        WHERE user_id = ? 
        ORDER BY id DESC
    ");
    $stmt->execute([$user_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les fichiers pour chaque demande
    foreach ($demandes as &$demande) {
        $stmt_files = $pdo->prepare("
            SELECT * FROM demandes_parcoursup_fichiers 
            WHERE demande_id = ?
        ");
        $stmt_files->execute([$demande['id']]);
        $demande['fichiers'] = $stmt_files->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // Gestion des erreurs de structure de table
    if ($e->getCode() == '42S02') {
        die("Erreur : La table 'demandes_parcoursup' n'existe pas. Veuillez exécuter le script SQL de création.");
    } else {
        die("Erreur BDD : " . $e->getMessage());
    }
}

// Fonction pour formater le statut
function formatStatut($statut) {
    $statuts = [
        'en_attente' => ['label' => 'En attente', 'class' => 'warning', 'icon' => 'clock'],
        'en_cours' => ['label' => 'En cours', 'class' => 'info', 'icon' => 'cog'],
        'approuve' => ['label' => 'Approuvé', 'class' => 'success', 'icon' => 'check-circle'],
        'refuse' => ['label' => 'Refusé', 'class' => 'danger', 'icon' => 'times-circle'],
        'termine' => ['label' => 'Terminé', 'class' => 'success', 'icon' => 'flag-checkered'],
        'annule' => ['label' => 'Annulé', 'class' => 'secondary', 'icon' => 'ban'],
        'admis' => ['label' => 'Admis', 'class' => 'success', 'icon' => 'graduation-cap'],
        'liste_attente' => ['label' => 'Liste d\'attente', 'class' => 'warning', 'icon' => 'list']
    ];
    return $statuts[$statut] ?? ['label' => $statut, 'class' => 'secondary', 'icon' => 'question-circle'];
}

// Fonction pour formater le niveau d'étude
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
        'inge' => 'École ingénieur',
        'commerce' => 'École commerce'
    ];
    return $niveaux[$niveau] ?? $niveau;
}

// Fonction pour formater la date
function formatDateDemande($demande) {
    if (isset($demande['date_demande']) && !empty($demande['date_demande'])) {
        return date('d/m/Y H:i', strtotime($demande['date_demande']));
    } elseif (isset($demande['date_soumission']) && !empty($demande['date_soumission'])) {
        return date('d/m/Y H:i', strtotime($demande['date_soumission']));
    } elseif (isset($demande['created_at']) && !empty($demande['created_at'])) {
        return date('d/m/Y H:i', strtotime($demande['created_at']));
    } else {
        return 'Date non disponible';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes Parcoursup - Babylone Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4b0082;
            --secondary-color: #8a2be2;
            --light-purple: #f0e6ff;
            --border-color: #dbe4ee;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .demande-card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .demande-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }
        
        .statut {
            padding: 6px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.8rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .statut-en_attente {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .statut-en_cours {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .statut-approuve {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .statut-refuse {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .statut-admis {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .statut-liste_attente {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .statut-termine {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .statut-annule {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }
        
        .fichiers-list {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 10px;
            background: #f8f9fa;
        }
        
        .fichier-item {
            padding: 8px 12px;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            margin-bottom: 8px;
            background: white;
            transition: background-color 0.2s;
        }
        
        .fichier-item:hover {
            background-color: #f8f9fa;
        }
        
        .btn-details {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-details:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(74, 0, 130, 0.3);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #6c757d;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        
        .empty-state i {
            font-size: 5rem;
            margin-bottom: 20px;
            color: #dee2e6;
            opacity: 0.7;
        }
        
        .date-soumission {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .badge-parcoursup {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 15px;
        }
        
        .progress-container {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .progress-step {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #6c757d;
        }
        
        .step-active .step-icon {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .step-completed .step-icon {
            background: #28a745;
            color: white;
        }
        
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
            border: none;
            padding: 12px 24px;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background: transparent;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 20px;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
            border: 2px solid white;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold">
                        <i class="fas fa-graduation-cap me-3"></i>Mes Demandes Parcoursup
                    </h1>
                    <p class="lead mb-0 opacity-90">Suivez l'état de vos candidatures Parcoursup en temps réel</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="../france/etudes/parcoursup.php" class="btn btn-light btn-lg shadow-sm">
                        <i class="fas fa-plus-circle me-2"></i> Nouvelle Candidature
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none"><i class="fas fa-home me-1"></i> Accueil</a></li>
                <li class="breadcrumb-item"><a href="espace_personnel.php" class="text-decoration-none">Espace Personnel</a></li>
                <li class="breadcrumb-item active text-purple">Mes Demandes Parcoursup</li>
            </ol>
        </nav>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-primary border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0"><?php echo count($demandes); ?></h3>
                                <p class="mb-0 opacity-90">Total Candidatures</p>
                            </div>
                            <div class="display-6 opacity-75">
                                <i class="fas fa-file-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-warning border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0"><?php echo count(array_filter($demandes, function($d) { return $d['statut'] == 'en_attente'; })); ?></h3>
                                <p class="mb-0 opacity-90">En Attente</p>
                            </div>
                            <div class="display-6 opacity-75">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-success border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0"><?php echo count(array_filter($demandes, function($d) { return $d['statut'] == 'admis' || $d['statut'] == 'approuve'; })); ?></h3>
                                <p class="mb-0 opacity-90">Admis/Approuvés</p>
                            </div>
                            <div class="display-6 opacity-75">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card text-white bg-info border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0"><?php echo count(array_filter($demandes, function($d) { return $d['statut'] == 'liste_attente'; })); ?></h3>
                                <p class="mb-0 opacity-90">Liste d'attente</p>
                            </div>
                            <div class="display-6 opacity-75">
                                <i class="fas fa-list"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation par onglets -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="demandes-tab" data-bs-toggle="tab" data-bs-target="#demandes" type="button" role="tab">
                            <i class="fas fa-list me-2"></i>Toutes mes demandes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="guide-tab" data-bs-toggle="tab" data-bs-target="#guide" type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i>Guide Parcoursup
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="myTabContent">
                    <!-- Onglet Demandes -->
                    <div class="tab-pane fade show active" id="demandes" role="tabpanel">
                        <!-- Liste des demandes -->
                        <?php if (empty($demandes)): ?>
                            <div class="empty-state">
                                <i class="fas fa-graduation-cap"></i>
                                <h3 class="mb-3">Aucune candidature Parcoursup trouvée</h3>
                                <p class="lead mb-4">Vous n'avez pas encore soumis de candidature via Parcoursup.</p>
                                <a href="../france/etudes/parcoursup.php" class="btn btn-primary btn-lg shadow">
                                    <i class="fas fa-plus-circle me-2"></i> Créer votre première candidature
                                </a>
                                <div class="mt-4">
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Besoin d'aide ? Consultez notre guide Parcoursup
                                    </small>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($demandes as $demande): ?>
                                    <?php 
                                    $statut = formatStatut($demande['statut']);
                                    $initiales = strtoupper(substr($demande['prenom'], 0, 1) . substr($demande['nom'], 0, 1));
                                    ?>
                                    <div class="col-lg-6 mb-4">
                                        <div class="demande-card">
                                            <div class="card-body">
                                                <!-- En-tête de la carte -->
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="user-avatar">
                                                            <?php echo $initiales; ?>
                                                        </div>
                                                        <div>
                                                            <h5 class="card-title mb-1">
                                                                <?php echo htmlspecialchars($demande['etablissement'] ?? 'Établissement non spécifié'); ?>
                                                            </h5>
                                                            <span class="badge-parcoursup">
                                                                <i class="fas fa-university me-1"></i>Parcoursup
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <span class="statut statut-<?php echo $demande['statut']; ?>">
                                                        <i class="fas fa-<?php echo $statut['icon']; ?>"></i>
                                                        <?php echo $statut['label']; ?>
                                                    </span>
                                                </div>
                                                
                                                <!-- Informations de formation -->
                                                <div class="mb-3">
                                                    <p class="card-text mb-2">
                                                        <strong><i class="fas fa-book me-2 text-primary"></i>Formation:</strong> 
                                                        <?php echo htmlspecialchars($demande['nom_formation'] ?? 'Non spécifiée'); ?>
                                                    </p>
                                                    <p class="card-text mb-2">
                                                        <strong><i class="fas fa-graduation-cap me-2 text-primary"></i>Niveau:</strong> 
                                                        <?php echo formatNiveauEtude($demande['niveau_etudes']); ?>
                                                    </p>
                                                    <p class="card-text mb-2">
                                                        <strong><i class="fas fa-tags me-2 text-primary"></i>Domaine:</strong> 
                                                        <?php echo htmlspecialchars($demande['domaine_etudes'] ?? 'Non spécifié'); ?>
                                                    </p>
                                                </div>
                                                
                                                <!-- Date de soumission -->
                                                <div class="mb-3">
                                                    <small class="text-muted date-soumission">
                                                        <i class="fas fa-calendar-alt me-1"></i>
                                                        Candidature #<?php echo $demande['id']; ?> 
                                                        | Soumis le: <?php echo formatDateDemande($demande); ?>
                                                    </small>
                                                </div>
                                                
                                                <!-- Fichiers joints -->
                                                <?php if (!empty($demande['fichiers'])): ?>
                                                    <div class="mb-3">
                                                        <h6 class="mb-2">
                                                            <i class="fas fa-paperclip me-2 text-primary"></i> 
                                                            Documents joints (<?php echo count($demande['fichiers']); ?>)
                                                        </h6>
                                                        <div class="fichiers-list">
                                                            <?php foreach (array_slice($demande['fichiers'], 0, 3) as $fichier): ?>
                                                                <div class="fichier-item d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                                                        <small class="fw-bold">
                                                                            <?php 
                                                                            $types_fichiers = [
                                                                                'copie_passeport' => 'Passeport',
                                                                                'diplomes' => 'Diplômes',
                                                                                'releves_notes' => 'Relevés de notes',
                                                                                'lettre_motivation' => 'Lettre de motivation',
                                                                                'cv' => 'CV',
                                                                                'attestation_francais' => 'Test français',
                                                                                'attestation_acceptation' => 'Attestation d\'acceptation',
                                                                                'certificat_scolarite' => 'Certificat de scolarité'
                                                                            ];
                                                                            echo $types_fichiers[$fichier['type_fichier']] ?? $fichier['type_fichier'];
                                                                            ?>
                                                                        </small>
                                                                    </div>
                                                                    <a href="telecharger_fichier_parcoursup.php?id=<?php echo $fichier['id']; ?>" 
                                                                       class="btn btn-sm btn-outline-primary">
                                                                        <i class="fas fa-download"></i>
                                                                    </a>
                                                                </div>
                                                            <?php endforeach; ?>
                                                            <?php if (count($demande['fichiers']) > 3): ?>
                                                                <div class="text-center mt-2">
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-ellipsis-h me-1"></i>
                                                                        + <?php echo count($demande['fichiers']) - 3; ?> autre(s) document(s)
                                                                    </small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <!-- Actions -->
                                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                                    <button type="button" class="btn btn-details" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#detailsModal<?php echo $demande['id']; ?>">
                                                        <i class="fas fa-eye me-2"></i> Détails complets
                                                    </button>
                                                    
                                                    <div class="d-flex gap-2">
                                                        <?php if ($demande['statut'] == 'en_attente'): ?>
                                                            <a href="modifier_parcoursup.php?id=<?php echo $demande['id']; ?>" 
                                                               class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-edit me-1"></i> Modifier
                                                            </a>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($demande['statut'] == 'admis' || $demande['statut'] == 'approuve'): ?>
                                                            <span class="badge bg-success p-2">
                                                                <i class="fas fa-check me-1"></i> Admis
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Modal Détails -->
                                    <div class="modal fade" id="detailsModal<?php echo $demande['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-graduation-cap me-2"></i> 
                                                        Détails Candidature #<?php echo $demande['id']; ?>
                                                        <span class="badge bg-light text-dark ms-2">Parcoursup</span>
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6 class="border-bottom pb-2 mb-3">
                                                                <i class="fas fa-user-graduate me-2 text-primary"></i>
                                                                Informations de formation
                                                            </h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless">
                                                                    <tr>
                                                                        <td width="40%"><strong>Établissement:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['etablissement'] ?? 'Non spécifié'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Formation:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['nom_formation'] ?? 'Non spécifiée'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Niveau:</strong></td>
                                                                        <td><?php echo formatNiveauEtude($demande['niveau_etudes']); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Domaine:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['domaine_etudes'] ?? 'Non spécifié'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Date début:</strong></td>
                                                                        <td><?php echo !empty($demande['date_debut']) ? date('d/m/Y', strtotime($demande['date_debut'])) : 'Non spécifiée'; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Durée:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['duree_etudes'] ?? 'Non spécifiée'); ?></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="border-bottom pb-2 mb-3">
                                                                <i class="fas fa-user me-2 text-primary"></i>
                                                                Informations personnelles
                                                            </h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless">
                                                                    <tr>
                                                                        <td width="40%"><strong>Nom complet:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Date naissance:</strong></td>
                                                                        <td><?php echo !empty($demande['date_naissance']) ? date('d/m/Y', strtotime($demande['date_naissance'])) : 'Non spécifiée'; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Lieu naissance:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['lieu_naissance'] ?? 'Non spécifié'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Nationalité:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['nationalite']); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Email:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['email']); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Téléphone:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['telephone'] ?? 'Non spécifié'); ?></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Informations académiques -->
                                                    <div class="row mt-4">
                                                        <div class="col-md-6">
                                                            <h6 class="border-bottom pb-2 mb-3">
                                                                <i class="fas fa-university me-2 text-primary"></i>
                                                                Informations académiques
                                                            </h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless">
                                                                    <tr>
                                                                        <td width="40%"><strong>Dernier diplôme:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['dernier_diplome'] ?? 'Non spécifié'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Établissement d'origine:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['etablissement_origine'] ?? 'Non spécifié'); ?></td>
                                                                    </tr>
                                                                    <?php if (!empty($demande['moyenne_derniere_annee'])): ?>
                                                                    <tr>
                                                                        <td><strong>Moyenne dernière année:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['moyenne_derniere_annee']); ?>/20</td>
                                                                    </tr>
                                                                    <?php endif; ?>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6 class="border-bottom pb-2 mb-3">
                                                                <i class="fas fa-passport me-2 text-primary"></i>
                                                                Informations passeport
                                                            </h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless">
                                                                    <tr>
                                                                        <td width="40%"><strong>Numéro:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['num_passeport'] ?? 'Non spécifié'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Délivrance:</strong></td>
                                                                        <td><?php echo !empty($demande['date_delivrance']) ? date('d/m/Y', strtotime($demande['date_delivrance'])) : 'Non spécifiée'; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Expiration:</strong></td>
                                                                        <td><?php echo !empty($demande['date_expiration']) ? date('d/m/Y', strtotime($demande['date_expiration'])) : 'Non spécifiée'; ?></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Niveau de français -->
                                                    <?php if (!empty($demande['niveau_francais']) || !empty($demande['score_test'])): ?>
                                                    <div class="row mt-4">
                                                        <div class="col-12">
                                                            <h6 class="border-bottom pb-2 mb-3">
                                                                <i class="fas fa-language me-2 text-primary"></i>
                                                                Niveau de français
                                                            </h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-borderless">
                                                                    <?php if (!empty($demande['niveau_francais'])): ?>
                                                                    <tr>
                                                                        <td width="30%"><strong>Niveau estimé:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['niveau_francais']); ?></td>
                                                                    </tr>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($demande['tests_francais']) && $demande['tests_francais'] !== 'non'): ?>
                                                                    <tr>
                                                                        <td><strong>Test passé:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['tests_francais']); ?></td>
                                                                    </tr>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($demande['score_test'])): ?>
                                                                    <tr>
                                                                        <td><strong>Score/Diplôme:</strong></td>
                                                                        <td><?php echo htmlspecialchars($demande['score_test']); ?></td>
                                                                    </tr>
                                                                    <?php endif; ?>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                    
                                                    <!-- Fichiers joints -->
                                                    <?php if (!empty($demande['fichiers'])): ?>
                                                        <div class="mt-4">
                                                            <h6 class="border-bottom pb-2 mb-3">
                                                                <i class="fas fa-paperclip me-2 text-primary"></i>
                                                                Documents joints (<?php echo count($demande['fichiers']); ?>)
                                                            </h6>
                                                            <div class="list-group">
                                                                <?php foreach ($demande['fichiers'] as $fichier): ?>
                                                                    <div class="list-group-item">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div class="d-flex align-items-center">
                                                                                <i class="fas fa-file-pdf text-danger fa-2x me-3"></i>
                                                                                <div>
                                                                                    <strong class="d-block">
                                                                                        <?php 
                                                                                        $types_fichiers = [
                                                                                            'copie_passeport' => 'Copie de passeport',
                                                                                            'diplomes' => 'Diplômes',
                                                                                            'releves_notes' => 'Relevés de notes globaux',
                                                                                            'lettre_motivation' => 'Lettre de motivation',
                                                                                            'cv' => 'Curriculum Vitae',
                                                                                            'attestation_francais' => 'Attestation de niveau de français',
                                                                                            'attestation_acceptation' => 'Attestation d\'acceptation',
                                                                                            'certificat_scolarite' => 'Certificat de scolarité'
                                                                                        ];
                                                                                        echo $types_fichiers[$fichier['type_fichier']] ?? $fichier['type_fichier'];
                                                                                        ?>
                                                                                    </strong>
                                                                                    <small class="text-muted">
                                                                                        <i class="fas fa-calendar me-1"></i>
                                                                                        Uploadé le: <?php echo date('d/m/Y à H:i', strtotime($fichier['date_upload'])); ?>
                                                                                    </small>
                                                                                </div>
                                                                            </div>
                                                                            <a href="telecharger_fichier_parcoursup.php?id=<?php echo $fichier['id']; ?>" 
                                                                               class="btn btn-outline-primary">
                                                                                <i class="fas fa-download me-1"></i> Télécharger
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="fas fa-times me-1"></i> Fermer
                                                    </button>
                                                    <?php if ($demande['statut'] == 'en_attente'): ?>
                                                        <a href="modifier_parcoursup.php?id=<?php echo $demande['id']; ?>" class="btn btn-primary">
                                                            <i class="fas fa-edit me-1"></i> Modifier
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Onglet Guide -->
                    <div class="tab-pane fade" id="guide" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <h4 class="mb-4 text-primary">
                                    <i class="fas fa-info-circle me-2"></i>Guide Parcoursup
                                </h4>
                                
                                <div class="progress-container">
                                    <h5 class="mb-4">Processus de candidature</h5>
                                    
                                    <div class="progress-step step-completed">
                                        <div class="step-icon">
                                            <i class="fas fa-user-edit"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">1. Saisie du dossier</h6>
                                            <p class="mb-0 text-muted">Remplissez votre profil et vos vœux</p>
                                        </div>
                                    </div>
                                    
                                    <div class="progress-step step-completed">
                                        <div class="step-icon">
                                            <i class="fas fa-file-upload"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">2. Confirmation des vœux</h6>
                                            <p class="mb-0 text-muted">Validez définitivement vos choix</p>
                                        </div>
                                    </div>
                                    
                                    <div class="progress-step step-active">
                                        <div class="step-icon">
                                            <i class="fas fa-search"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">3. Examen du dossier</h6>
                                            <p class="mb-0 text-muted">Les établissements étudient votre candidature</p>
                                        </div>
                                    </div>
                                    
                                    <div class="progress-step">
                                        <div class="step-icon">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">4. Réponses des formations</h6>
                                            <p class="mb-0 text-muted">Réception des propositions d'admission</p>
                                        </div>
                                    </div>
                                    
                                    <div class="progress-step">
                                        <div class="step-icon">
                                            <i class="fas fa-check-double"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">5. Réponse du candidat</h6>
                                            <p class="mb-0 text-muted">Acceptation ou refus des propositions</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <h5 class="mb-3">Conseils importants</h5>
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <h6>Vérifiez régulièrement vos emails</h6>
                                            <p class="text-muted">Les établissements peuvent vous contacter pour des informations complémentaires.</p>
                                        </div>
                                        <div class="timeline-item">
                                            <h6>Respectez les délais</h6>
                                            <p class="text-muted">Chaque étape a des dates limites impératives.</p>
                                        </div>
                                        <div class="timeline-item">
                                            <h6>Préparez vos justificatifs</h6>
                                            <p class="text-muted">Ayez tous vos documents prêts pour les phases d'admission.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Besoin d'aide ?</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="#" class="btn btn-outline-primary">
                                                <i class="fas fa-phone me-2"></i>Nous contacter
                                            </a>
                                            <a href="#" class="btn btn-outline-primary">
                                                <i class="fas fa-file-pdf me-2"></i>Guide utilisateur
                                            </a>
                                            <a href="#" class="btn btn-outline-primary">
                                                <i class="fas fa-calendar me-2"></i>Calendrier Parcoursup
                                            </a>
                                        </div>
                                        
                                        <div class="mt-4 p-3 bg-light rounded">
                                            <h6 class="mb-2">Dates importantes 2024</h6>
                                            <ul class="small mb-0">
                                                <li>18 mars : Fin des inscriptions</li>
                                                <li>2 avril : Dernier jour des vœux</li>
                                                <li>4 juin : Début des réponses</li>
                                                <li>17 juillet : Fin de la procédure</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation pour les cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.demande-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('animate__animated', 'animate__fadeInUp');
            });
        });

        // Gestion des onglets
        const triggerTabList = document.querySelectorAll('#myTab button');
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', event => {
                event.preventDefault();
                tabTrigger.show();
            });
        });
    </script>
</body>
</html>