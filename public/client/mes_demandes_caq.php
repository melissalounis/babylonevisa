<?php
// mes_demandes_caq.php
session_start();

// Configuration DB
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'babylone_service');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}

// Récupérer les demandes
$demandes = [];
$email_recherche = '';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM demandes_caq WHERE user_id = ? ORDER BY date_soumission DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $demandes = $stmt->fetchAll();
} else {
    if (isset($_POST['email_recherche']) || isset($_GET['email'])) {
        $email_recherche = $_POST['email_recherche'] ?? $_GET['email'] ?? '';
        if (!empty($email_recherche)) {
            $stmt = $pdo->prepare("SELECT * FROM demandes_caq WHERE email = ? ORDER BY date_soumission DESC");
            $stmt->execute([$email_recherche]);
            $demandes = $stmt->fetchAll();
        }
    }
}

// Statistiques
$stats = [
    'total' => count($demandes),
    'nouveau' => 0,
    'en_traitement' => 0,
    'approuve' => 0,
    'refuse' => 0
];

foreach ($demandes as $demande) {
    if (isset($stats[$demande['statut']])) {
        $stats[$demande['statut']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes CAQ - Certificat d'Acceptation du Québec</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #8B0000;
            --secondary: #A52A2A;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
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
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #8B0000 0%, #A52A2A 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
        }
        
        h1 {
            color: var(--primary);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }
        
        .search-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .demandes-list {
            display: grid;
            gap: 20px;
        }
        
        .demande-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 5px solid var(--primary);
        }
        
        .demande-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .demande-id {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary);
        }
        
        .statut {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .statut.nouveau { background: #d4edda; color: #155724; }
        .statut.en_traitement { background: #fff3cd; color: #856404; }
        .statut.approuve { background: #d1ecf1; color: #0c5460; }
        .statut.refuse { background: #f8d7da; color: #721c24; }
        
        .demande-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.8rem;
            color: #666;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .info-value {
            font-weight: 600;
            color: #333;
        }
        
        .demande-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .empty-icon {
            font-size: 4rem;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        input[type="email"] {
            width: 100%;
            max-width: 400px;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .btn-search {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header {
                padding: 20px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Mes Demandes de CAQ</h1>
            <p class="subtitle">Certificat d'Acceptation du Québec - Suivi des demandes</p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="search-box">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email_recherche"><i class="fas fa-search"></i> Rechercher mes demandes CAQ par email :</label>
                        <input type="email" id="email_recherche" name="email_recherche" 
                               value="<?php echo htmlspecialchars($email_recherche); ?>" 
                               placeholder="votre@email.com" required>
                        <button type="submit" class="btn-search">Rechercher</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($demandes)): ?>
        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total des demandes CAQ</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #155724;"><?php echo $stats['nouveau']; ?></div>
                <div class="stat-label">Nouvelles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #856404;"><?php echo $stats['en_traitement']; ?></div>
                <div class="stat-label">En traitement</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #0c5460;"><?php echo $stats['approuve']; ?></div>
                <div class="stat-label">Approuvées</div>
            </div>
        </div>

        <!-- Liste des demandes -->
        <div class="demandes-list">
            <?php foreach ($demandes as $demande): ?>
                <div class="demande-card">
                    <div class="demande-header">
                        <div class="demande-id">
                            <i class="fas fa-hashtag"></i>
                            Demande #CAQ<?php echo str_pad($demande['id'], 6, '0', STR_PAD_LEFT); ?>
                        </div>
                        <div class="statut <?php echo $demande['statut']; ?>">
                            <i class="fas 
                                <?php 
                                switch($demande['statut']) {
                                    case 'nouveau': echo 'fa-clock'; break;
                                    case 'en_traitement': echo 'fa-cog fa-spin'; break;
                                    case 'approuve': echo 'fa-check-circle'; break;
                                    case 'refuse': echo 'fa-times-circle'; break;
                                }
                                ?>
                            "></i>
                            <?php echo ucfirst($demande['statut']); ?>
                        </div>
                    </div>
                    
                    <div class="demande-info">
                        <div class="info-item">
                            <span class="info-label">Établissement</span>
                            <span class="info-value">
                                <i class="fas fa-university"></i>
                                <?php echo htmlspecialchars($demande['etablissement_quebec'] ?: 'Non spécifié'); ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Programme</span>
                            <span class="info-value">
                                <i class="fas fa-book"></i>
                                <?php echo htmlspecialchars($demande['programme_etudes'] ?: 'Non spécifié'); ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Date de soumission</span>
                            <span class="info-value">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Début des études</span>
                            <span class="info-value">
                                <i class="fas fa-play-circle"></i>
                                <?php echo $demande['date_debut'] ? date('d/m/Y', strtotime($demande['date_debut'])) : 'Non spécifié'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="demande-info">
                        <div class="info-item">
                            <span class="info-label">Nom complet</span>
                            <span class="info-value">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($demande['nom_complet']); ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value">
                                <i class="fas fa-envelope"></i>
                                <?php echo htmlspecialchars($demande['email']); ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Durée des études</span>
                            <span class="info-value">
                                <i class="fas fa-hourglass-half"></i>
                                <?php echo htmlspecialchars($demande['duree_etudes'] ?: 'Non spécifiée'); ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Pays d'origine</span>
                            <span class="info-value">
                                <i class="fas fa-globe"></i>
                                <?php echo htmlspecialchars($demande['pays_origine'] ?: 'Non spécifié'); ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (!empty($demande['notes_admin'])): ?>
                    <div class="info-item" style="grid-column: 1 / -1; margin-top: 15px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <span class="info-label">Notes de l'administrateur</span>
                        <span class="info-value" style="font-style: italic; color: #666;">
                            <i class="fas fa-sticky-note"></i>
                            <?php echo htmlspecialchars($demande['notes_admin']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="demande-actions">
                        <button class="btn btn-primary" onclick="afficherDetails(<?php echo $demande['id']; ?>)">
                            <i class="fas fa-eye"></i> Voir les détails
                        </button>
                        
                        <?php if ($demande['statut'] === 'approuve'): ?>
                        <button class="btn btn-success" onclick="telechargerCAQ(<?php echo $demande['id']; ?>)">
                            <i class="fas fa-download"></i> Télécharger le CAQ
                        </button>
                        <?php endif; ?>
                        
                        <button class="btn btn-secondary" onclick="contacterSupport(<?php echo $demande['id']; ?>)">
                            <i class="fas fa-headset"></i> Contacter le support
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php elseif (isset($_POST['email_recherche']) || isset($_GET['email'])): ?>
        <!-- Aucune demande trouvée -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-inbox"></i>
            </div>
            <h3>Aucune demande CAQ trouvée</h3>
            <p>Aucune demande de CAQ n'a été trouvée pour l'email "<?php echo htmlspecialchars($email_recherche); ?>"</p>
            <p>Vérifiez l'adresse email ou <a href="upload_caq.php" style="color: var(--primary);">soumettez une nouvelle demande CAQ</a></p>
        </div>
        
        <?php else: ?>
        <!-- État initial (avant recherche) -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>Recherchez vos demandes CAQ</h3>
            <p>Entrez votre adresse email pour afficher l'historique de vos demandes de Certificat d'Acceptation du Québec</p>
        </div>
        <?php endif; ?>
    </div>

    <script>
        function afficherDetails(demandeId) {
            alert('Détails de la demande CAQ #' + demandeId + '\n\nCette fonctionnalité sera implémentée prochainement.');
            // Redirection vers une page de détails spécifique
            // window.location.href = 'details_caq.php?id=' + demandeId;
        }
        
        function telechargerCAQ(demandeId) {
            alert('Téléchargement du CAQ #' + demandeId + '\n\nCette fonctionnalité sera disponible lorsque le CAQ sera approuvé.');
            // window.location.href = 'download_caq.php?id=' + demandeId;
        }
        
        function contacterSupport(demandeId) {
            const email = 'support@babylone-service.com';
            const sujet = `Demande CAQ${demandeId.toString().padStart(6, '0')}`;
            const body = `Bonjour,\n\nJe contacte le support concernant ma demande de CAQ #CAQ${demandeId.toString().padStart(6, '0')}.\n\nPourriez-vous me donner des informations sur l'état de ma demande ?\n\nCordialement,`;
            
            window.location.href = `mailto:${email}?subject=${encodeURIComponent(sujet)}&body=${encodeURIComponent(body)}`;
        }
        
        // Animation d'apparition des cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.demande-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>