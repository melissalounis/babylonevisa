<?php
// mes_demandes_attestation_province.php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Configuration de la base de données
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Connexion à la base de données
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les demandes CAQ
$demandes_caq = [];
$erreur_caq = '';
try {
    $sql_caq = "SELECT * FROM demandes_caq WHERE user_id = :user_id ORDER BY date_soumission DESC";
    $stmt_caq = $pdo->prepare($sql_caq);
    $stmt_caq->execute([':user_id' => $user_id]);
    $demandes_caq = $stmt_caq->fetchAll();
} catch (PDOException $e) {
    $erreur_caq = "Erreur lors de la récupération des demandes CAQ: " . $e->getMessage();
}

// Récupérer les demandes d'attestation de province
$demandes_attestation = [];
$erreur_attestation = '';
try {
    $sql_attestation = "SELECT * FROM attestation_province WHERE user_id = :user_id ORDER BY date_soumission DESC";
    $stmt_attestation = $pdo->prepare($sql_attestation);
    $stmt_attestation->execute([':user_id' => $user_id]);
    $demandes_attestation = $stmt_attestation->fetchAll();
} catch (PDOException $e) {
    $erreur_attestation = "Erreur lors de la récupération des demandes d'attestation de province: " . $e->getMessage();
}

// Fonction pour formater la date
function formaterDate($date) {
    return date('d/m/Y à H:i', strtotime($date));
}

// Fonction pour obtenir la classe CSS du statut
function getStatutClass($statut) {
    switch (strtolower($statut)) {
        case 'approuvé':
        case 'accepté':
        case 'approuve':
            return 'statut-success';
        case 'refusé':
        case 'rejeté':
        case 'refuse':
            return 'statut-error';
        case 'en cours':
        case 'en traitement':
        case 'en_traitement':
            return 'statut-warning';
        case 'nouveau':
        default:
            return 'statut-info';
    }
}

// Fonction pour compter les fichiers (avec vérification de sécurité)
function compterFichiers($json_fichiers) {
    if ($json_fichiers && $json_fichiers !== 'null' && $json_fichiers !== '') {
        $fichiers = json_decode($json_fichiers, true);
        return is_array($fichiers) ? count($fichiers) : 0;
    }
    return 0;
}

// Fonction pour vérifier si un champ existe et n'est pas vide
function champExiste($demande, $champ) {
    return isset($demande[$champ]) && !empty($demande[$champ]) && $demande[$champ] !== 'null';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes - Espace Client</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #8B0000;
            --secondary: #A52A2A;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #17a2b8;
            --light: #f8f9fa;
            --dark: #343a40;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: var(--dark);
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #dee2e6;
        }
        
        .tab {
            padding: 15px 30px;
            background: none;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
            color: var(--dark);
        }
        
        .tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }
        
        .tab:hover {
            color: var(--primary);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .demande-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            background: white;
            transition: all 0.3s;
        }
        
        .demande-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .demande-info h3 {
            color: var(--primary);
            margin-bottom: 10px;
            font-size: 1.3rem;
        }
        
        .demande-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }
        
        .statut {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .statut-success {
            background: #d4edda;
            color: #155724;
        }
        
        .statut-error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .statut-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .statut-info {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .demande-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-group {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 0.9rem;
        }
        
        .detail-value {
            color: #555;
            font-size: 1rem;
        }
        
        .documents-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .documents-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
        }
        
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .document-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        
        .document-icon {
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        .document-info {
            flex: 1;
        }
        
        .document-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }
        
        .document-count {
            font-size: 0.8rem;
            color: #666;
        }
        
        .btn-download {
            background: var(--primary);
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            font-size: 0.8rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-download:hover {
            background: var(--secondary);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            margin-bottom: 10px;
            color: #555;
        }
        
        .btn-nouvelle-demande {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .btn-nouvelle-demande:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 0, 0, 0.3);
        }
        
        .badge-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .badge-caq {
            background: #e7f3ff;
            color: #0066cc;
            border: 1px solid #b3d9ff;
        }
        
        .badge-attestation {
            background: #f0fff0;
            color: #228b22;
            border: 1px solid #98fb98;
        }
        
        .badge {
            background: var(--primary);
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8rem;
            margin-left: 8px;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--danger);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .card {
                padding: 20px;
            }
            
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .demande-details {
                grid-template-columns: 1fr;
            }
            
            .documents-grid {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .tab {
                text-align: left;
                border-bottom: 1px solid #dee2e6;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="card">
            <h1><i class="fas fa-user-circle"></i> Mon Espace Client</h1>
            <p class="subtitle">Gérez vos demandes de CAQ et d'Attestation de Province</p>
        </header>

        <div class="card">
            <div class="tabs">
                <button class="tab active" onclick="openTab('caq')">
                    <i class="fas fa-graduation-cap"></i> Demandes CAQ
                    <span class="badge"><?php echo count($demandes_caq); ?></span>
                </button>
                <button class="tab" onclick="openTab('attestation')">
                    <i class="fas fa-file-certificate"></i> Demandes d'Attestation de Province
                    <span class="badge"><?php echo count($demandes_attestation); ?></span>
                </button>
            </div>

            <!-- Onglet CAQ -->
            <div id="caq" class="tab-content active">
                <?php if ($erreur_caq): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $erreur_caq; ?>
                    </div>
                <?php elseif (!empty($demandes_caq)): ?>
                    <?php foreach ($demandes_caq as $demande): ?>
                        <div class="demande-card">
                            <div class="demande-header">
                                <div class="demande-info">
                                    <h3>
                                        Demande CAQ 
                                        <span class="badge-type badge-caq">CAQ</span>
                                    </h3>
                                    <div class="demande-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-hashtag"></i>
                                            <span>Réf: CAQ<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo formaterDate($demande['date_soumission']); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($demande['nom_complet']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="statut <?php echo getStatutClass($demande['statut']); ?>">
                                    <?php echo htmlspecialchars($demande['statut']); ?>
                                </div>
                            </div>

                            <div class="demande-details">
                                <div class="detail-group">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($demande['email']); ?></div>
                                </div>
                                <div class="detail-group">
                                    <div class="detail-label">Téléphone</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($demande['telephone'] ?? 'Non renseigné'); ?></div>
                                </div>
                                <div class="detail-group">
                                    <div class="detail-label">Date de naissance</div>
                                    <div class="detail-value"><?php echo $demande['date_naissance'] ? date('d/m/Y', strtotime($demande['date_naissance'])) : 'Non renseignée'; ?></div>
                                </div>
                                <div class="detail-group">
                                    <div class="detail-label">Pays d'origine</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($demande['pays_origine']); ?></div>
                                </div>
                            </div>

                            <div class="documents-section">
                                <div class="documents-title">Documents déposés</div>
                                <div class="documents-grid">
                                    <div class="document-item">
                                        <i class="fas fa-passport document-icon"></i>
                                        <div class="document-info">
                                            <div class="document-name">Passeport</div>
                                            <div class="document-count"><?php echo compterFichiers($demande['passeport_path'] ?? ''); ?> fichier(s)</div>
                                        </div>
                                        <?php if (champExiste($demande, 'passeport_path')): ?>
                                        <a href="#" class="btn-download" onclick="telechargerDocuments('<?php echo $demande['passeport_path']; ?>')">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="document-item">
                                        <i class="fas fa-envelope-open-text document-icon"></i>
                                        <div class="document-info">
                                            <div class="document-name">Lettre d'acceptation</div>
                                            <div class="document-count"><?php echo compterFichiers($demande['lettre_acceptation_path'] ?? ''); ?> fichier(s)</div>
                                        </div>
                                        <?php if (champExiste($demande, 'lettre_acceptation_path')): ?>
                                        <a href="#" class="btn-download" onclick="telechargerDocuments('<?php echo $demande['lettre_acceptation_path']; ?>')">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (champExiste($demande, 'preuve_fonds_path')): ?>
                                    <div class="document-item">
                                        <i class="fas fa-file-invoice-dollar document-icon"></i>
                                        <div class="document-info">
                                            <div class="document-name">Preuve de fonds</div>
                                            <div class="document-count"><?php echo compterFichiers($demande['preuve_fonds_path']); ?> fichier(s)</div>
                                        </div>
                                        <a href="#" class="btn-download" onclick="telechargerDocuments('<?php echo $demande['preuve_fonds_path']; ?>')">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>Aucune demande CAQ</h3>
                        <p>Vous n'avez pas encore soumis de demande de Certificat d'Acceptation du Québec.</p>
                        <a href="../upload_caq.php" class="btn-nouvelle-demande">
                            <i class="fas fa-plus"></i> Nouvelle demande CAQ
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Onglet Attestation de Province -->
            <div id="attestation" class="tab-content">
                <?php if ($erreur_attestation): ?>
                    <div class="alert-error">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $erreur_attestation; ?>
                    </div>
                <?php elseif (!empty($demandes_attestation)): ?>
                    <?php foreach ($demandes_attestation as $demande): ?>
                        <div class="demande-card">
                            <div class="demande-header">
                                <div class="demande-info">
                                    <h3>
                                        Demande d'Attestation de Province
                                        <span class="badge-type badge-attestation">Attestation</span>
                                    </h3>
                                    <div class="demande-meta">
                                        <div class="meta-item">
                                            <i class="fas fa-hashtag"></i>
                                            <span>Réf: ATT<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo formaterDate($demande['date_soumission']); ?></span>
                                        </div>
                                        <div class="meta-item">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($demande['nom_complet']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="statut <?php echo getStatutClass($demande['statut']); ?>">
                                    <?php echo htmlspecialchars($demande['statut']); ?>
                                </div>
                            </div>

                            <div class="demande-details">
                                <div class="detail-group">
                                    <div class="detail-label">Email</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($demande['email']); ?></div>
                                </div>
                                <div class="detail-group">
                                    <div class="detail-label">Téléphone</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($demande['telephone'] ?? 'Non renseigné'); ?></div>
                                </div>
                                <div class="detail-group">
                                    <div class="detail-label">Province</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($demande['province'] ?? 'Non renseignée'); ?></div>
                                </div>
                                <div class="detail-group">
                                    <div class="detail-label">Pays</div>
                                    <div class="detail-value"><?php echo htmlspecialchars($demande['pays'] ?? 'Non renseigné'); ?></div>
                                </div>
                            </div>

                            <div class="documents-section">
                                <div class="documents-title">Documents déposés</div>
                                <div class="documents-grid">
                                    <?php if (champExiste($demande, 'passeport_path')): ?>
                                    <div class="document-item">
                                        <i class="fas fa-passport document-icon"></i>
                                        <div class="document-info">
                                            <div class="document-name">Passeport</div>
                                            <div class="document-count"><?php echo compterFichiers($demande['passeport_path']); ?> fichier(s)</div>
                                        </div>
                                        <a href="#" class="btn-download" onclick="telechargerDocuments('<?php echo $demande['passeport_path']; ?>')">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (champExiste($demande, 'photos_identite_path')): ?>
                                    <div class="document-item">
                                        <i class="fas fa-id-card document-icon"></i>
                                        <div class="document-info">
                                            <div class="document-name">Photos d'identité</div>
                                            <div class="document-count"><?php echo compterFichiers($demande['photos_identite_path']); ?> fichier(s)</div>
                                        </div>
                                        <a href="#" class="btn-download" onclick="telechargerDocuments('<?php echo $demande['photos_identite_path']; ?>')">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (champExiste($demande, 'releves_bancaires_path')): ?>
                                    <div class="document-item">
                                        <i class="fas fa-file-invoice-dollar document-icon"></i>
                                        <div class="document-info">
                                            <div class="document-name">Relevés bancaires</div>
                                            <div class="document-count"><?php echo compterFichiers($demande['releves_bancaires_path']); ?> fichier(s)</div>
                                        </div>
                                        <a href="#" class="btn-download" onclick="telechargerDocuments('<?php echo $demande['releves_bancaires_path']; ?>')">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (champExiste($demande, 'lettre_acceptation_path')): ?>
                                    <div class="document-item">
                                        <i class="fas fa-envelope-open-text document-icon"></i>
                                        <div class="document-info">
                                            <div class="document-name">Lettre d'acceptation</div>
                                            <div class="document-count"><?php echo compterFichiers($demande['lettre_acceptation_path']); ?> fichier(s)</div>
                                        </div>
                                        <a href="#" class="btn-download" onclick="telechargerDocuments('<?php echo $demande['lettre_acceptation_path']; ?>')">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (champExiste($demande, 'preuve_fonds_path')): ?>
                                    <div class="document-item">
                                        <i class="fas fa-money-bill-wave document-icon"></i>
                                        <div class="document-info">
                                            <div class="document-name">Preuve de fonds</div>
                                            <div class="document-count"><?php echo compterFichiers($demande['preuve_fonds_path']); ?> fichier(s)</div>
                                        </div>
                                        <a href="#" class="btn-download" onclick="telechargerDocuments('<?php echo $demande['preuve_fonds_path']; ?>')">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (champExiste($demande, 'autres_documents_path')): ?>
                                    <div class="document-item">
                                        <i class="fas fa-file-alt document-icon"></i>
                                        <div class="document-info">
                                            <div class="document-name">Autres documents</div>
                                            <div class="document-count"><?php echo compterFichiers($demande['autres_documents_path']); ?> fichier(s)</div>
                                        </div>
                                        <a href="#" class="btn-download" onclick="telechargerDocuments('<?php echo $demande['autres_documents_path']; ?>')">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-certificate"></i>
                        <h3>Aucune demande d'attestation de province</h3>
                        <p>Vous n'avez pas encore soumis de demande d'attestation de province.</p>
                        <a href="../upload_attestation_province.php" class="btn-nouvelle-demande">
                            <i class="fas fa-plus"></i> Nouvelle demande d'attestation de province
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Gestion des onglets
        function openTab(tabName) {
            // Masquer tous les onglets
            const tabContents = document.getElementsByClassName('tab-content');
            for (let i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // Désactiver tous les boutons d'onglet
            const tabs = document.getElementsByClassName('tab');
            for (let i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            
            // Activer l'onglet sélectionné
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        // Fonction pour télécharger les documents (à implémenter)
        function telechargerDocuments(jsonFichiers) {
            alert('Fonction de téléchargement à implémenter');
            // Implémentation future pour télécharger les fichiers
            // const fichiers = JSON.parse(jsonFichiers);
            // fichiers.forEach(fichier => {
            //     window.open('../download.php?file=' + encodeURIComponent(fichier), '_blank');
            // });
        }

        // Afficher un message de confirmation si une demande vient d'être soumise
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('success') === '1') {
            alert('Votre demande a été soumise avec succès !');
        }
    </script>
</body>
</html>