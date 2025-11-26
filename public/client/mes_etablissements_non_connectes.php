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

    // Récupérer les demandes de l'utilisateur
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_etablissements_non_connectes 
        WHERE user_id = ? 
        ORDER BY id DESC
    ");
    $stmt->execute([$user_id]);
    $demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les fichiers pour chaque demande
    foreach ($demandes as &$demande) {
        $stmt_files = $pdo->prepare("
            SELECT * FROM demandes_etablissements_non_connectes_fichiers 
            WHERE demande_id = ?
        ");
        $stmt_files->execute([$demande['id']]);
        $demande['fichiers'] = $stmt_files->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // Gestion améliorée des erreurs
    if ($e->getCode() == '42S22') {
        // Colonne non trouvée - créer les tables manquantes
        die("Erreur de structure de base de données. Veuillez exécuter le script de création des tables.");
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
    <title>Mes Demandes - Établissements Non Connectés</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #006400;
            --secondary-color: #228B22;
            --light-green: #f0fff0;
            --border-color: #dbe4ee;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: linear-gradient(135deg, #006400, #228B22);
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
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-details:hover {
            background-color: var(--secondary-color);
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
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-list-alt"></i> Mes Demandes</h1>
                    <p class="lead mb-0">Consultez l'état de vos demandes pour établissements non connectés</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="formulaire_etablissements_non_connectes.php" class="btn btn-light btn-lg">
                        <i class="fas fa-plus-circle"></i> Nouvelle Demande
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li class="breadcrumb-item active">Mes Demandes</li>
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
                                <p>Total Demandes</p>
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
                <div class="card text-white bg-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?php echo count(array_filter($demandes, function($d) { return $d['statut'] == 'refuse'; })); ?></h4>
                                <p>Refusées</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des demandes -->
        <?php if (empty($demandes)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Aucune demande trouvée</h3>
                <p>Vous n'avez pas encore soumis de demande pour les établissements non connectés.</p>
                <a href="formulaire_etablissements_non_connectes.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus-circle"></i> Créer votre première demande
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
                                        <i class="fas fa-university"></i>
                                        <?php echo htmlspecialchars($demande['etablissement']); ?>
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
                                    <strong>Formation:</strong> <?php echo htmlspecialchars($demande['nom_formation']); ?><br>
                                    <strong>Niveau:</strong> 
                                    <?php 
                                    $niveaux = [
                                        'licence1' => 'Licence 1',
                                        'licence2' => 'Licence 2',
                                        'licence3' => 'Licence 3',
                                        'master1' => 'Master 1',
                                        'master2' => 'Master 2',
                                        'doctorat' => 'Doctorat',
                                        'bts' => 'BTS',
                                        'dut' => 'DUT',
                                        'inge' => 'École d\'ingénieurs',
                                        'commerce' => 'École de commerce'
                                    ];
                                    echo $niveaux[$demande['niveau_etudes']] ?? $demande['niveau_etudes'];
                                    ?><br>
                                    <strong>Domaine:</strong> <?php echo htmlspecialchars($demande['domaine_etudes']); ?>
                                </p>
                                
                                <div class="mb-3">
                                    <small class="text-muted date-soumission">
                                        <i class="fas fa-calendar"></i>
                                        ID: <?php echo $demande['id']; ?> 
                                        <?php if (isset($demande['date_soumission'])): ?>
                                            | Soumis le: <?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                                
                                <!-- Fichiers joints -->
                                <?php if (!empty($demande['fichiers'])): ?>
                                    <div class="mb-3">
                                        <h6><i class="fas fa-paperclip"></i> Fichiers joints:</h6>
                                        <div class="fichiers-list">
                                            <?php foreach ($demande['fichiers'] as $fichier): ?>
                                                <div class="fichier-item">
                                                    <small>
                                                        <i class="fas fa-file"></i>
                                                        <?php 
                                                        $types_fichiers = [
                                                            'copie_passeport' => 'Passeport',
                                                            'diplomes' => 'Diplômes',
                                                            'releves_notes' => 'Relevés de notes',
                                                            'lettre_motivation' => 'Lettre de motivation',
                                                            'cv' => 'CV',
                                                            'attestation_francais' => 'Test français',
                                                            'attestation_acceptation' => 'Attestation d\'acceptation'
                                                        ];
                                                        $type_affichage = $types_fichiers[$fichier['type_fichier']] ?? $fichier['type_fichier'];
                                                        echo $type_affichage;
                                                        ?>
                                                    </small>
                                                </div>
                                            <?php endforeach; ?>
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
                                        <a href="modifier_demande.php?id=<?php echo $demande['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i> Modifier
                                        </a>
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
                                    <h5 class="modal-title">Détails de la demande #<?php echo $demande['id']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Informations de formation</h6>
                                            <p><strong>Établissement:</strong> <?php echo htmlspecialchars($demande['etablissement']); ?></p>
                                            <p><strong>Formation:</strong> <?php echo htmlspecialchars($demande['nom_formation']); ?></p>
                                            <p><strong>Niveau:</strong> <?php echo $niveaux[$demande['niveau_etudes']] ?? $demande['niveau_etudes']; ?></p>
                                            <p><strong>Domaine:</strong> <?php echo htmlspecialchars($demande['domaine_etudes']); ?></p>
                                            <p><strong>Date début:</strong> <?php echo !empty($demande['date_debut']) ? date('d/m/Y', strtotime($demande['date_debut'])) : 'Non spécifiée'; ?></p>
                                            <p><strong>Durée:</strong> <?php echo htmlspecialchars($demande['duree_etudes']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Informations personnelles</h6>
                                            <p><strong>Nom:</strong> <?php echo htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']); ?></p>
                                            <p><strong>Date naissance:</strong> <?php echo !empty($demande['date_naissance']) ? date('d/m/Y', strtotime($demande['date_naissance'])) : 'Non spécifiée'; ?></p>
                                            <p><strong>Lieu naissance:</strong> <?php echo htmlspecialchars($demande['lieu_naissance']); ?></p>
                                            <p><strong>Nationalité:</strong> <?php echo htmlspecialchars($demande['nationalite']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($demande['email']); ?></p>
                                            <p><strong>Téléphone:</strong> <?php echo htmlspecialchars($demande['telephone']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Informations passeport -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <h6>Informations passeport</h6>
                                            <p><strong>Numéro:</strong> <?php echo htmlspecialchars($demande['num_passeport']); ?></p>
                                            <p><strong>Délivrance:</strong> <?php echo !empty($demande['date_delivrance']) ? date('d/m/Y', strtotime($demande['date_delivrance'])) : ''; ?></p>
                                            <p><strong>Expiration:</strong> <?php echo !empty($demande['date_expiration']) ? date('d/m/Y', strtotime($demande['date_expiration'])) : ''; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Informations académiques</h6>
                                            <p><strong>Dernier diplôme:</strong> <?php echo htmlspecialchars($demande['dernier_diplome']); ?></p>
                                            <p><strong>Établissement d'origine:</strong> <?php echo htmlspecialchars($demande['etablissement_origine']); ?></p>
                                            <?php if (!empty($demande['moyenne_derniere_annee'])): ?>
                                                <p><strong>Moyenne dernière année:</strong> <?php echo htmlspecialchars($demande['moyenne_derniere_annee']); ?>/20</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($demande['fichiers'])): ?>
                                        <div class="mt-4">
                                            <h6>Fichiers joints</h6>
                                            <div class="list-group">
                                                <?php foreach ($demande['fichiers'] as $fichier): ?>
                                                    <div class="list-group-item">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="fas fa-file-pdf text-danger"></i>
                                                                <strong><?php 
                                                                    $types_fichiers = [
                                                                        'copie_passeport' => 'Copie de passeport',
                                                                        'diplomes' => 'Diplômes',
                                                                        'releves_notes' => 'Relevés de notes globaux',
                                                                        'lettre_motivation' => 'Lettre de motivation',
                                                                        'cv' => 'Curriculum Vitae',
                                                                        'attestation_francais' => 'Attestation de niveau de français',
                                                                        'attestation_acceptation' => 'Attestation d\'acceptation'
                                                                    ];
                                                                    echo $types_fichiers[$fichier['type_fichier']] ?? $fichier['type_fichier'];
                                                                ?></strong>
                                                                <br>
                                                                <small class="text-muted">
                                                                    Uploadé le: <?php echo date('d/m/Y à H:i', strtotime($fichier['date_upload'])); ?>
                                                                </small>
                                                            </div>
                                                            <a href="telecharger_fichier.php?id=<?php echo $fichier['id']; ?>" 
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