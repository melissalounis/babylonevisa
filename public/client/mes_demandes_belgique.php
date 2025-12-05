<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Connexion BDD
require_once __DIR__ . '../../../config.php';

try {
    

    // Récupérer les demandes Belgique de l'utilisateur
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_belgique 
        WHERE user_id = ? 
        ORDER BY date_soumission DESC
    ");
    $stmt->execute([$user_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les fichiers pour chaque demande
    foreach ($demandes as &$demande) {
        $stmt_files = $pdo->prepare("
            SELECT * FROM demandes_belgique_fichiers 
            WHERE demande_id = ?
        ");
        $stmt_files->execute([$demande['id']]);
        $demande['fichiers'] = $stmt_files->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // Gestion des erreurs de structure de table
    if ($e->getCode() == '42S02') {
        die("Erreur : La table 'demandes_belgique' n'existe pas. Veuillez exécuter le script SQL de création.");
    } else {
        die("Erreur BDD : " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes Belgique</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --belgique-black: #070988;
            --belgique-yellow: #FDDA24;
            --belgique-red: #EF3340;
            --light-gray: #f8f9fa;
            --border-color: #dbe4ee;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: linear-gradient(135deg, var(--belgique-black), #2c2c2c);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .demande-card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .demande-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .statut {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.85rem;
        }
        
        .statut-en_attente {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .statut-en_cours {
            background-color: #cce7ff;
            color: #004085;
            border: 1px solid #b3d7ff;
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
        
        .fichiers-list {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .fichier-item {
            padding: 8px 12px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            margin-bottom: 5px;
            background: #f8f9fa;
        }
        
        .btn-details {
            background-color: var(--belgique-black);
            color: white;
            border: none;
        }
        
        .btn-details:hover {
            background-color: var(--belgique-red);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        .date-soumission {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .badge-belgique {
            background: linear-gradient(135deg, var(--belgique-black), var(--belgique-red));
            color: white;
        }
        
        .niveau-badge {
            background-color: var(--belgique-yellow);
            color: var(--belgique-black);
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-university"></i> Mes Demandes Belgique</h1>
                    <p class="lead mb-0">Suivez l'état de vos candidatures pour les études en Belgique</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="../belgique/etude/index.php" class="btn btn-light btn-lg">
                        <i class="fas fa-plus-circle"></i> Nouvelle Candidature
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li class="breadcrumb-item active">Mes Demandes Belgique</li>
            </ol>
        </nav>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count($demandes); ?></h4>
                                <p>Total Candidatures</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count(array_filter($demandes, function($d) { return $d['statut'] == 'en_attente'; })); ?></h4>
                                <p>En Attente</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count(array_filter($demandes, function($d) { return $d['statut'] == 'approuve'; })); ?></h4>
                                <p>Approuvées</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count(array_filter($demandes, function($d) { return $d['statut'] == 'en_cours'; })); ?></h4>
                                <p>En Cours</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-spinner fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des demandes -->
        <?php if (empty($demandes)): ?>
            <div class="empty-state">
                <i class="fas fa-university"></i>
                <h3>Aucune candidature Belgique trouvée</h3>
                <p>Vous n'avez pas encore soumis de candidature pour les études en Belgique.</p>
                <a href="../belgique/etude/index.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Créer votre première candidature
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($demandes as $demande): ?>
                    <div class="col-md-6">
                        <div class="demande-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title">
                                        <i class="fas fa-user-graduate"></i>
                                        <?php echo htmlspecialchars($demande['nom']); ?>
                                    </h5>
                                    <span class="statut statut-<?php echo $demande['statut']; ?>">
                                        <?php 
                                        $statuts = [
                                            'en_attente' => 'En Attente',
                                            'en_cours' => 'En Cours',
                                            'approuve' => 'Approuvé',
                                            'refuse' => 'Refusé'
                                        ];
                                        echo $statuts[$demande['statut']] ?? $demande['statut'];
                                        ?>
                                    </span>
                                </div>
                                
                                <p class="card-text">
                                    <strong>Email:</strong> <?php echo htmlspecialchars($demande['email']); ?><br>
                                    <strong>Nationalité:</strong> <?php echo htmlspecialchars($demande['nationalite']); ?><br>
                                    <strong>Niveau:</strong> 
                                    <span class="niveau-badge">
                                        <?php 
                                        $niveaux = [
                                            'bac' => 'Bac',
                                            'l1' => 'Licence 1',
                                            'l2' => 'Licence 2',
                                            'l3' => 'Licence 3',
                                            'master' => 'Master'
                                        ];
                                        echo $niveaux[$demande['niveau_etude']] ?? $demande['niveau_etude'];
                                        ?>
                                    </span>
                                </p>
                                
                                <div class="mb-3">
                                    <small class="text-muted date-soumission">
                                        <i class="fas fa-calendar"></i>
                                        Candidature #<?php echo $demande['id']; ?> 
                                        | Soumis le: <?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?>
                                    </small>
                                </div>
                                
                                <!-- Fichiers joints -->
                                <?php if (!empty($demande['fichiers'])): ?>
                                    <div class="mb-3">
                                        <h6><i class="fas fa-paperclip"></i> Documents joints:</h6>
                                        <div class="fichiers-list">
                                            <?php foreach (array_slice($demande['fichiers'], 0, 3) as $fichier): ?>
                                                <div class="fichier-item">
                                                    <small>
                                                        <i class="fas fa-file"></i>
                                                        <?php 
                                                        $types_fichiers = [
                                                            'lettre_admission' => 'Lettre admission',
                                                            'photo' => 'Photo',
                                                            'certificat_medical' => 'Certificat médical',
                                                            'casier_judiciaire' => 'Casier judiciaire',
                                                            'releve_2nde' => 'Relevé 2nde',
                                                            'releve_1ere' => 'Relevé 1ère',
                                                            'releve_terminale' => 'Relevé Terminale',
                                                            'releve_bac' => 'Relevé Bac',
                                                            'diplome_bac' => 'Diplôme Bac',
                                                            'releve_l1' => 'Relevé L1',
                                                            'relevé_l2' => 'Relevé L2',
                                                            'releve_l3' => 'Relevé L3',
                                                            'diplome_licence' => 'Diplôme Licence',
                                                            'certificat_scolarite' => 'Certificat scolarité'
                                                        ];
                                                        $type_affichage = $types_fichiers[$fichier['type_fichier']] ?? $fichier['type_fichier'];
                                                        echo $type_affichage;
                                                        ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($demande['fichiers']) > 3): ?>
                                                <div class="fichier-item text-center">
                                                    <small class="text-muted">
                                                        + <?php echo count($demande['fichiers']) - 3; ?> autre(s) document(s)
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Actions -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <button type="button" class="btn btn-details btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailsModal<?php echo $demande['id']; ?>">
                                        <i class="fas fa-eye"></i> Détails
                                    </button>
                                    
                                    <?php if ($demande['statut'] == 'en_attente'): ?>
                                        <a href="modifier_demande_belgique.php?id=<?php echo $demande['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($demande['statut'] == 'approuve'): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Approuvé
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Détails -->
                    <div class="modal fade" id="detailsModal<?php echo $demande['id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">
                                        <i class="fas fa-university"></i> 
                                        Détails Candidature #<?php echo $demande['id']; ?>
                                        <span class="badge badge-belgique ms-2">Belgique</span>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Informations personnelles</h6>
                                            <p><strong>Nom complet:</strong> <?php echo htmlspecialchars($demande['nom']); ?></p>
                                            <p><strong>Date de naissance:</strong> <?php echo !empty($demande['naissance']) ? date('d/m/Y', strtotime($demande['naissance'])) : 'Non spécifiée'; ?></p>
                                            <p><strong>Nationalité:</strong> <?php echo htmlspecialchars($demande['nationalite']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($demande['email']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Informations académiques</h6>
                                            <p><strong>Niveau d'études:</strong> <?php echo $niveaux[$demande['niveau_etude']] ?? $demande['niveau_etude']; ?></p>
                                            <p><strong>Statut:</strong> 
                                                <span class="statut statut-<?php echo $demande['statut']; ?>">
                                                    <?php echo $statuts[$demande['statut']] ?? $demande['statut']; ?>
                                                </span>
                                            </p>
                                            <p><strong>Date soumission:</strong> <?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?></p>
                                            <?php if (!empty($demande['date_modification'])): ?>
                                                <p><strong>Dernière modification:</strong> <?php echo date('d/m/Y à H:i', strtotime($demande['date_modification'])); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($demande['fichiers'])): ?>
                                        <div class="mt-4">
                                            <h6>Documents joints</h6>
                                            <div class="list-group">
                                                <?php foreach ($demande['fichiers'] as $fichier): ?>
                                                    <div class="list-group-item">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="fas fa-file-pdf text-danger"></i>
                                                                <strong><?php 
                                                                    $types_fichiers = [
                                                                        'lettre_admission' => 'Lettre d\'admission',
                                                                        'photo' => 'Photo',
                                                                        'certificat_medical' => 'Certificat médical',
                                                                        'casier_judiciaire' => 'Casier judiciaire',
                                                                        'releve_2nde' => 'Relevé de notes 2nde',
                                                                        'releve_1ere' => 'Relevé de notes 1ère',
                                                                        'releve_terminale' => 'Relevé de notes Terminale',
                                                                        'releve_bac' => 'Relevé de notes Bac',
                                                                        'diplome_bac' => 'Diplôme Bac',
                                                                        'releve_l1' => 'Relevé de notes Licence 1',
                                                                        'releve_l2' => 'Relevé de notes Licence 2',
                                                                        'releve_l3' => 'Relevé de notes Licence 3',
                                                                        'diplome_licence' => 'Diplôme Licence',
                                                                        'certificat_scolarite' => 'Certificat de scolarité'
                                                                    ];
                                                                    echo $types_fichiers[$fichier['type_fichier']] ?? $fichier['type_fichier'];
                                                                ?></strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    Uploadé le: <?php echo date('d/m/Y à H:i', strtotime($fichier['date_upload'])); ?>
                                                                </small>
                                                            </div>
                                                            <a href="telecharger_fichier_belgique.php?id=<?php echo $fichier['id']; ?>" 
                                                               class="btn btn-outline-primary btn-sm">
                                                                <i class="fas fa-download"></i> Télécharger
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                    <?php if ($demande['statut'] == 'en_attente'): ?>
                                        <a href="modifier_belgique.php?id=<?php echo $demande['id']; ?>" class="btn btn-primary">
                                            <i class="fas fa-edit"></i> Modifier
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>