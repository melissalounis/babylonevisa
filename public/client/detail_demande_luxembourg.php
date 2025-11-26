<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifier si un ID de demande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mes_demandes_luxembourg.php");
    exit;
}

$demande_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer la demande spécifique de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_luxembourg 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si la demande existe et appartient à l'utilisateur
    if (!$demande) {
        $_SESSION['error'] = "Demande non trouvée ou vous n'avez pas l'autorisation de la consulter.";
        header("Location: mes_demandes_luxembourg.php");
        exit;
    }

    // Récupérer les fichiers de la demande
    $stmt_files = $pdo->prepare("
        SELECT * FROM demandes_luxembourg_fichiers 
        WHERE demande_id = ?
        ORDER BY date_upload DESC
    ");
    $stmt_files->execute([$demande_id]);
    $fichiers = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

    // Organiser les fichiers par type
    $fichiers_par_type = [];
    foreach ($fichiers as $fichier) {
        $fichiers_par_type[$fichier['type_fichier']] = $fichier;
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonctions de formatage
function formatStatut($statut) {
    $statuts = [
        'en_attente' => 'En attente',
        'en_cours' => 'En cours de traitement',
        'approuve' => 'Approuvée',
        'refuse' => 'Refusée'
    ];
    return $statuts[$statut] ?? ucfirst($statut);
}

function formatNiveau($niveau) {
    $niveaux = [
        'bachelor' => 'Bachelor',
        'master' => 'Master',
        'doctorat' => 'Doctorat'
    ];
    return $niveaux[$niveau] ?? ucfirst($niveau);
}

function formatProgramme($programme) {
    $programmes = [
        'francais' => 'Programme en Français',
        'anglais' => 'Programme en Anglais',
        'allemand' => 'Programme en Allemand',
        'luxembourgeois' => 'Programme en Luxembourgeois'
    ];
    return $programmes[$programme] ?? ucfirst($programme);
}

function getLabelFichier($type) {
    $labels = [
        'test_francais' => 'Test de français',
        'test_anglais' => 'Test d\'anglais',
        'test_allemand' => 'Test d\'allemand',
        'test_luxembourgeois' => 'Test de luxembourgeois',
        'passeport' => 'Passeport/Carte d\'identité',
        'justificatif' => 'Justificatif de ressources',
        'photo' => 'Photo d\'identité',
        'releves_lycee' => 'Relevés de notes Lycée',
        'releve_bac' => 'Relevé de notes Bac',
        'diplome_bac' => 'Diplôme Bac',
        'diplome_bachelor' => 'Diplôme Bachelor',
        'releves_bachelor' => 'Relevés de notes Bachelor',
        'diplome_master' => 'Diplôme Master',
        'releves_master' => 'Relevés de notes Master',
        'projet_recherche' => 'Projet de recherche',
        'cv' => 'CV/Curriculum Vitae',
        'recommandation' => 'Lettre de recommandation'
    ];
    return $labels[$type] ?? $type;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Demande Luxembourg - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --luxembourg-blue: #00A2E0;
            --luxembourg-red: #EF3340;
            --luxembogue-white: #FFFFFF;
            --luxembourg-dark: #2C3E50;
            --light-gray: #f8f9fa;
            --border-color: #dbe4ee;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
            --danger: #dc3545;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--luxembourg-blue), #0088C7);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '🏰';
            position: absolute;
            top: 20px;
            left: 30px;
            font-size: 3rem;
            opacity: 0.3;
        }
        
        .header::after {
            content: '🇱🇺';
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 3rem;
            opacity: 0.3;
        }
        
        .header h1 {
            font-size: 2.8em;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .header p {
            font-size: 1.2em;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .content {
            padding: 30px;
        }
        
        .demande-info-header {
            background: var(--light-gray);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid var(--luxembourg-blue);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.8em;
            color: #666;
            margin-bottom: 5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-weight: 500;
            color: var(--luxembourg-dark);
            font-size: 1em;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-en_attente { 
            background: var(--warning); 
            color: #000; 
        }
        .status-en_cours { 
            background: var(--info); 
            color: white; 
        }
        .status-approuve { 
            background: var(--success); 
            color: white; 
        }
        .status-refuse { 
            background: var(--danger); 
            color: white; 
        }
        
        .section {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }
        
        .section-title {
            font-size: 1.4em;
            font-weight: 700;
            color: var(--luxembourg-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 15px;
        }
        
        .document-card {
            background: var(--light-gray);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s ease;
        }
        
        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .document-type {
            font-weight: 600;
            color: var(--luxembourg-dark);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .document-info {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
        }
        
        .document-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9em;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        
        .btn-primary {
            background: var(--luxembourg-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background: #0088C7;
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--luxembourg-blue);
            color: var(--luxembourg-blue);
        }
        
        .btn-outline:hover {
            background: var(--luxembourg-blue);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .actions-container {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .motivation-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--luxembourg-blue);
        }
        
        .progress-container {
            margin: 15px 0;
        }
        
        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.9em;
            color: #666;
        }
        
        .progress-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: var(--luxembourg-blue);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3em;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 2.2em;
            }
            
            .content {
                padding: 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .documents-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-container {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏰 Détails Demande Luxembourg</h1>
            <p>Détails complets de votre candidature pour les études au Luxembourg</p>
        </div>
        
        <div class="content">
            <!-- En-tête informations -->
            <div class="demande-info-header">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">🆔 Référence</span>
                        <span class="info-value">LUX-<?= str_pad($demande['id'], 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">📊 Statut</span>
                        <span class="status-badge status-<?= $demande['statut'] ?>">
                            <?= formatStatut($demande['statut']) ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">📅 Date de création</span>
                        <span class="info-value">
                            <?= date('d/m/Y à H:i', strtotime($demande['date_creation'])) ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($demande['date_modification'])): ?>
                    <div class="info-item">
                        <span class="info-label">🔄 Dernière mise à jour</span>
                        <span class="info-value">
                            <?= date('d/m/Y à H:i', strtotime($demande['date_modification'])) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Barre de progression -->
            <div class="section">
                <div class="progress-container">
                    <div class="progress-label">
                        <span>Progression du dossier</span>
                        <span>
                            <?= match($demande['statut']) {
                                'en_attente' => '25%',
                                'en_cours' => '60%',
                                'approuve' => '100%',
                                'refuse' => '100%',
                                default => '0%'
                            } ?>
                        </span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 
                            <?= match($demande['statut']) {
                                'en_attente' => '25%',
                                'en_cours' => '60%',
                                'approuve' => '100%',
                                'refuse' => '100%',
                                default => '0%'
                            } ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations personnelles -->
            <div class="section">
                <h3 class="section-title">
                    <i class="fas fa-user"></i> Informations Personnelles
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">👤 Nom complet</span>
                        <span class="info-value"><?= htmlspecialchars($demande['nom']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">📧 Email</span>
                        <span class="info-value"><?= htmlspecialchars($demande['email']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">📞 Téléphone</span>
                        <span class="info-value"><?= htmlspecialchars($demande['telephone']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">🌍 Nationalité</span>
                        <span class="info-value"><?= htmlspecialchars($demande['nationalite']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Informations académiques -->
            <div class="section">
                <h3 class="section-title">
                    <i class="fas fa-graduation-cap"></i> Informations Académiques
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">🎓 Niveau d'études</span>
                        <span class="info-value"><?= formatNiveau($demande['niveau']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">📚 Programme</span>
                        <span class="info-value"><?= formatProgramme($demande['programme']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Lettre de motivation -->
            <?php if (!empty($demande['motivation'])): ?>
            <div class="section">
                <h3 class="section-title">
                    <i class="fas fa-envelope"></i> Lettre de Motivation
                </h3>
                <div class="motivation-section">
                    <?= nl2br(htmlspecialchars($demande['motivation'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Documents déposés -->
            <div class="section">
                <h3 class="section-title">
                    <i class="fas fa-file-alt"></i> Documents Déposés
                </h3>
                
                <?php if (!empty($fichiers)): ?>
                    <div class="documents-grid">
                        <?php foreach ($fichiers as $fichier): ?>
                            <div class="document-card">
                                <div class="document-type">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                    <?= getLabelFichier($fichier['type_fichier']) ?>
                                </div>
                                
                                <div class="document-info">
                                    <small>
                                        <i class="fas fa-calendar"></i>
                                        Uploadé le: <?= date('d/m/Y à H:i', strtotime($fichier['date_upload'])) ?>
                                    </small>
                                </div>
                                
                                <div class="document-actions">
                                    <a href="../../../uploads/luxembourg/<?= basename($fichier['chemin_fichier']) ?>" 
                                       target="_blank" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    <a href="../../../uploads/luxembourg/<?= basename($fichier['chemin_fichier']) ?>" 
                                       download class="btn btn-outline">
                                        <i class="fas fa-download"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-file-alt"></i>
                        <h4>Aucun document déposé</h4>
                        <p>Aucun fichier n'a été uploadé pour cette demande.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div class="actions-container">
                <a href="mes_demandes_luxembourg.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Retour aux demandes
                </a>
                
                <?php if ($demande['statut'] == 'en_attente'): ?>
                    <a href="modifier_demande_luxembourg.php?id=<?= $demande['id'] ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modifier la demande
                    </a>
                <?php endif; ?>
                
                <a href="imprimer_demande_luxembourg.php?id=<?= $demande['id'] ?>" class="btn btn-outline">
                    <i class="fas fa-print"></i> Imprimer
                </a>
                
                <?php if ($demande['statut'] == 'approuve'): ?>
                    <span class="btn btn-success">
                        <i class="fas fa-check"></i> Demande Approuvée
                    </span>
                <?php elseif ($demande['statut'] == 'refuse'): ?>
                    <span class="btn" style="background: var(--danger); color: white;">
                        <i class="fas fa-times"></i> Demande Refusée
                    </span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>