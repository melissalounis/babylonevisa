<?php
require_once __DIR__ . '/../../../config.php';
require_login();

// Récupérer l'email de l'utilisateur connecté
// Méthode 1: Depuis la session
if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];
} 
// Méthode 2: Depuis l'objet USER (si Moodle)
elseif (isset($USER) && isset($USER->email)) {
    $user_email = $USER->email;
}
// Méthode 3: Depuis la base de données avec user_id
elseif (isset($_SESSION['user_id'])) {
    try {
        $stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $user_email = $user['email'] ?? '';
    } catch (PDOException $e) {
        $user_email = '';
    }
}
// Méthode 4: Depuis une autre variable de session
elseif (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
} else {
    $user_email = '';
}

// DEBUG: Afficher l'email pour vérification (à supprimer en production)
error_log("Email utilisateur recherché: " . $user_email);
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'Non défini'));

// Récupérer les demandes Canada de l'utilisateur connecté
try {
    // D'abord vérifier si la table existe
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'demandes_etudes_canada'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if ($table_exists && !empty($user_email)) {
        // Chercher dans la table spécifique des études Canada
        $stmt = $pdo->prepare("
            SELECT * 
            FROM demandes_etudes_canada 
            WHERE email = ? 
            ORDER BY date_soumission DESC
        ");
        $stmt->execute([$user_email]);
        $demandes = $stmt->fetchAll();
        
        // DEBUG: Vérifier combien de demandes trouvées
        error_log("Demandes trouvées dans demandes_etudes_canada: " . count($demandes));
        
    } else {
        // Si table n'existe pas ou email vide, chercher dans table générale
        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare("
                SELECT d.*, s.titre as service_titre 
                FROM demandes d 
                LEFT JOIN services s ON d.visa_type = s.titre 
                WHERE d.user_id = ? AND d.visa_type LIKE '%Canada%' 
                ORDER BY d.created_at DESC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $demandes = $stmt->fetchAll();
            
            // DEBUG: Vérifier combien de demandes trouvées
            error_log("Demandes trouvées dans demandes: " . count($demandes));
        } else {
            $demandes = [];
        }
    }
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des demandes: " . $e->getMessage();
    error_log("Erreur DB: " . $e->getMessage());
    $demandes = [];
}

// DEBUG: Vérifier la structure des données
error_log("Structure première demande: " . print_r($demandes[0] ?? 'Aucune demande', true));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes Canada - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Votre CSS reste identique */
        :root {
            --canada-red: #D80621;
            --canada-white: #FFFFFF;
            --canada-dark: #2C2C2C;
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
            background: linear-gradient(135deg, var(--canada-red), #b3001b);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '🍁';
            position: absolute;
            top: 20px;
            left: 30px;
            font-size: 3rem;
            opacity: 0.3;
        }
        
        .header::after {
            content: '🍁';
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
        
        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 8px;
            font-weight: 600;
            border-left: 4px solid;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: var(--danger);
        }
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left-color: var(--info);
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            border-top: 4px solid var(--canada-red);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: var(--canada-red);
            display: block;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--canada-dark);
            font-size: 0.95em;
            font-weight: 600;
        }
        
        .demandes-list {
            display: grid;
            gap: 25px;
        }
        
        .demande-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .demande-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--canada-red);
        }
        
        .demande-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }
        
        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .demande-title {
            font-size: 1.4em;
            font-weight: 700;
            color: var(--canada-dark);
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-nouveau { 
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
        
        .demande-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.8em;
            color: #666;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-weight: 500;
            color: var(--canada-dark);
            font-size: 1em;
        }
        
        .demande-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }
        
        .btn {
            padding: 10px 20px;
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
            background: var(--canada-red);
            color: white;
        }
        
        .btn-primary:hover {
            background: #b3001b;
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--canada-red);
            color: var(--canada-red);
        }
        
        .btn-outline:hover {
            background: var(--canada-red);
            color: white;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-1px);
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 5em;
            margin-bottom: 25px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 1.8em;
            margin-bottom: 15px;
            color: var(--canada-dark);
        }
        
        .empty-state p {
            font-size: 1.1em;
            margin-bottom: 30px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .documents-list {
            margin-top: 15px;
        }
        
        .document-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            background: var(--light-gray);
            border-radius: 6px;
            margin-bottom: 8px;
            transition: background 0.3s ease;
        }
        
        .document-item:hover {
            background: #e9ecef;
        }
        
        .document-icon {
            width: 24px;
            text-align: center;
        }
        
        .document-actions {
            margin-left: auto;
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.8em;
        }
        
        .progress-container {
            margin: 15px 0;
        }
        
        .progress-label {
            display: flex;
            justify-content: between;
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
            background: var(--canada-red);
            border-radius: 4px;
            transition: width 0.3s ease;
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
            
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .demande-info {
                grid-template-columns: 1fr;
            }
            
            .demande-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .stats {
                grid-template-columns: 1fr;
            }
            
            .header h1 {
                font-size: 1.8em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🍁 Mes Demandes Canada</h1>
            <p>Suivi de vos demandes d'admission et de visa pour le Canada</p>
        </div>
        
        <div class="content">
            <!-- Debug info (à supprimer en production) -->
            <?php if (empty($user_email)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    DEBUG: Email utilisateur non trouvé. 
                    Session user_id: <?= $_SESSION['user_id'] ?? 'Non défini' ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if (empty($demandes)): ?>
                <div class="empty-state">
                    <div>📭</div>
                    <h3>Aucune demande Canada trouvée</h3>
                    <p>Vous n'avez pas encore soumis de demande d'admission ou de visa pour le Canada.</p>
                    <a href="../services/index.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Soumettre une nouvelle demande
                    </a>
                    
                    <!-- Debug info -->
                    <div class="mt-4 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Debug info:</strong><br>
                            Email recherché: <?= htmlspecialchars($user_email) ?><br>
                            User ID: <?= $_SESSION['user_id'] ?? 'Non défini' ?><br>
                            Table demandes_etudes_canada existe: <?= $table_exists ? 'Oui' : 'Non' ?>
                        </small>
                    </div>
                </div>
            <?php else: ?>
                <!-- Statistiques -->
                <div class="stats">
                    <?php
                    $stats = [
                        'total' => count($demandes),
                        'nouveau' => 0,
                        'en_cours' => 0,
                        'approuve' => 0,
                        'refuse' => 0
                    ];
                    
                    foreach ($demandes as $demande) {
                        $status = $demande['statut'] ?? 'nouveau';
                        if (isset($stats[$status])) {
                            $stats[$status]++;
                        }
                    }
                    ?>
                    
                    <div class="stat-card">
                        <span class="stat-number"><?= $stats['total'] ?></span>
                        <span class="stat-label">Total des demandes</span>
                    </div>
                    
                    <div class="stat-card">
                        <span class="stat-number"><?= $stats['nouveau'] ?></span>
                        <span class="stat-label">Nouvelles demandes</span>
                    </div>
                    
                    <div class="stat-card">
                        <span class="stat-number"><?= $stats['en_cours'] ?></span>
                        <span class="stat-label">En traitement</span>
                    </div>
                    
                    <div class="stat-card">
                        <span class="stat-number"><?= $stats['approuve'] + $stats['refuse'] ?></span>
                        <span class="stat-label">Terminées</span>
                    </div>
                </div>
                
                <!-- Liste des demandes -->
                <div class="demandes-list">
                    <?php foreach ($demandes as $demande): ?>
                        <?php 
                        // Adapter les noms de champs selon la table utilisée
                        $id = $demande['id'] ?? $demande['id_demande'] ?? '';
                        $nom_complet = $demande['nom_complet'] ?? $demande['nom'] ?? '';
                        $email = $demande['email'] ?? '';
                        $niveau_etude = $demande['niveau_etude'] ?? '';
                        $province = $demande['province'] ?? '';
                        $status = $demande['statut'] ?? $demande['status'] ?? 'nouveau';
                        $date_soumission = $demande['date_soumission'] ?? $demande['created_at'] ?? '';
                        $date_maj = $demande['date_modification'] ?? $demande['updated_at'] ?? '';
                        ?>
                        
                        <div class="demande-card">
                            <div class="demande-header">
                                <div class="demande-title">
                                    <i class="fas fa-file-alt"></i>
                                    Demande #<?= $id ?> - Études Canada
                                </div>
                                <div class="status-badge status-<?= $status ?>">
                                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                </div>
                            </div>
                            
                            <div class="demande-info">
                                <div class="info-item">
                                    <span class="info-label">🆔 Référence</span>
                                    <span class="info-value">CAN-<?= str_pad($id, 6, '0', STR_PAD_LEFT) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">👤 Nom complet</span>
                                    <span class="info-value"><?= htmlspecialchars($nom_complet) ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">📧 Email</span>
                                    <span class="info-value"><?= htmlspecialchars($email) ?></span>
                                </div>
                                
                                <?php if (!empty($niveau_etude)): ?>
                                <div class="info-item">
                                    <span class="info-label">🎓 Niveau d'étude</span>
                                    <span class="info-value">
                                        <?= match($niveau_etude) {
                                            'bac' => 'Baccalauréat (Undergraduate)',
                                            'dec' => 'DEC',
                                            'dep' => 'DEP', 
                                            'aec' => 'AEC',
                                            'maitrise' => 'Maîtrise (Master)',
                                            'phd' => 'Doctorat (PhD)',
                                            'technique' => 'Formation technique',
                                            'langue' => 'Programme de langue',
                                            default => ucfirst($niveau_etude)
                                        } ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($province)): ?>
                                <div class="info-item">
                                    <span class="info-label">📍 Province souhaitée</span>
                                    <span class="info-value">
                                        <?= match($province) {
                                            'quebec' => 'Québec',
                                            'ontario' => 'Ontario',
                                            'colombie_britannique' => 'Colombie-Britannique',
                                            'alberta' => 'Alberta',
                                            'manitoba' => 'Manitoba',
                                            'saskatchewan' => 'Saskatchewan',
                                            'nouvelle_ecosse' => 'Nouvelle-Écosse',
                                            'nouveau_brunswick' => 'Nouveau-Brunswick',
                                            'terre_neuve' => 'Terre-Neuve-et-Labrador',
                                            'ile_du_prince_edouard' => 'Île-du-Prince-Édouard',
                                            default => ucfirst(str_replace('_', ' ', $province))
                                        } ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="info-item">
                                    <span class="info-label">📅 Date de soumission</span>
                                    <span class="info-value">
                                        <?= date('d/m/Y à H:i', strtotime($date_soumission)) ?>
                                    </span>
                                </div>
                                
                                <?php if (!empty($date_maj) && $date_maj != $date_soumission): ?>
                                <div class="info-item">
                                    <span class="info-label">🔄 Dernière mise à jour</span>
                                    <span class="info-value">
                                        <?= date('d/m/Y à H:i', strtotime($date_maj)) ?>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Barre de progression -->
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span>Progression du dossier</span>
                                    <span>
                                        <?= match($status) {
                                            'nouveau' => '25%',
                                            'en_cours' => '60%',
                                            'approuve' => '100%',
                                            'refuse' => '100%',
                                            default => '0%'
                                        } ?>
                                    </span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 
                                        <?= match($status) {
                                            'nouveau' => '25%',
                                            'en_cours' => '60%',
                                            'approuve' => '100%',
                                            'refuse' => '100%',
                                            default => '0%'
                                        } ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Documents déposés -->
                            <div class="documents-list">
                                <span class="info-label">📎 Documents déposés:</span>
                                <?php
                                // Liste des documents selon votre table
                                $documents = [
                                    'Passeport' => $demande['passeport'] ?? null,
                                    'Acte de naissance' => $demande['acte_naissance'] ?? null,
                                    'Test de langue' => $demande['test_langue'] ?? null,
                                    'CV' => $demande['cv'] ?? null,
                                    'Relevé de notes' => $demande['releve_notes'] ?? null,
                                    'Diplôme de fin d\'études' => $demande['diplome_fin_etudes'] ?? null,
                                    'Relevé de notes du baccalauréat' => $demande['releve_bac'] ?? null,
                                    'Diplôme de baccalauréat' => $demande['diplome_bac'] ?? null,
                                    'Relevés universitaires' => $demande['releves_universitaires'] ?? null,
                                    'Relevé de notes de maîtrise' => $demande['releve_maitrise'] ?? null,
                                    'Diplôme de maîtrise' => $demande['diplome_maitrise'] ?? null,
                                    'Projet de recherche' => $demande['projet_recherche'] ?? null,
                                    'CV académique' => $demande['cv_academique'] ?? null,
                                    'Certificat de scolarité' => $demande['certificat_scolarite'] ?? null,
                                    'Attestation de province' => $demande['attestation_province'] ?? null
                                ];
                                
                                $documentsDeposes = array_filter($documents);
                                ?>
                                
                                <?php if (!empty($documentsDeposes)): ?>
                                    <?php foreach ($documentsDeposes as $docName => $docPath): ?>
                                        <?php if (!empty($docPath)): ?>
                                            <div class="document-item">
                                                <div class="document-icon">
                                                    📄
                                                </div>
                                                <span><?= $docName ?></span>
                                                <div class="document-actions">
                                                    <a href="uploads/canada/<?= htmlspecialchars($docPath) ?>" target="_blank" class="btn btn-outline btn-sm">
                                                        <i class="fas fa-eye"></i> Voir
                                                    </a>
                                                    <a href="uploads/canada/<?= htmlspecialchars($docPath) ?>" download class="btn btn-outline btn-sm">
                                                        <i class="fas fa-download"></i> Télécharger
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div style="padding: 12px; background: #f8f9fa; border-radius: 6px; margin-top: 8px;">
                                        <em>Aucun document disponible pour le moment</em>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="demande-actions">
                                <?php if ($status == 'nouveau'): ?>
                                    <a href="modifier_demande_canada.php?id=<?= $id ?>" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Modifier la demande
                                    </a>
                                <?php endif; ?>
                                
                                <a href="imprimer_demande_canada.php?id=<?= $id ?>" class="btn btn-outline">
                                    <i class="fas fa-print"></i> Imprimer
                                </a>
                                
                                <?php if ($status == 'approuve'): ?>
                                    <span class="btn btn-success">
                                        <i class="fas fa-check"></i> Accepté
                                    </span>
                                <?php elseif ($status == 'refuse'): ?>
                                    <span class="btn" style="background: var(--danger); color: white;">
                                        <i class="fas fa-times"></i> Refusé
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>