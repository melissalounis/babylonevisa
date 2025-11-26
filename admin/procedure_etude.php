<?php
// procedure_etude.php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Connexion BDD pour les statistiques
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $pdo = null;
}

// Récupérer les statistiques des demandes de l'utilisateur
$user_id = $_SESSION['user_id'];
$stats = [
    'campus_france' => 0,
    'parcoursup' => 0,
    'paris_saclay' => 0,
    'total' => 0
];

if ($pdo) {
    try {
        // Campus France
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes_campus_france WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['campus_france'] = $stmt->fetchColumn();
        
        // Parcoursup
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes_parcoursup WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['parcoursup'] = $stmt->fetchColumn();
        
        // Paris Saclay
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes_paris_saclay WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['paris_saclay'] = $stmt->fetchColumn();
        
        $stats['total'] = $stats['campus_france'] + $stats['parcoursup'] + $stats['paris_saclay'];
    } catch (PDOException $e) {
        // Continuer sans statistiques
    }
}

$procedures = [
    'campus_france' => [
        'titre' => 'Campus France',
        'description' => 'Procédure pour les étudiants internationaux hors UE',
        'icone' => 'fa-university',
        'couleur' => '#4b0082',
        'avantages' => [
            'Procédure simplifiée pour les étudiants étrangers',
            'Accompagnement personnalisé',
            'Gestion centralisée des dossiers',
            'Support multilingue disponible'
        ],
        'public' => 'Étudiants internationaux hors Union Européenne',
        'delai' => '2-3 mois',
        'cout' => 'Gratuit',
        'lien' => 'campus_france.php',
        'difficulte' => 'Moyenne'
    ],
    'parcoursup' => [
        'titre' => 'Parcoursup',
        'description' => 'Plateforme nationale pour l\'enseignement supérieur',
        'icone' => 'fa-graduation-cap',
        'couleur' => '#0055a4',
        'avantages' => [
            'Accès à toutes les formations françaises',
            'Plateforme nationale officielle',
            'Procédure transparente et équitable',
            'Suivi en temps réel des candidatures'
        ],
        'public' => 'Tous les étudiants (UE et hors UE)',
        'delai' => '4-6 mois',
        'cout' => 'Gratuit',
        'lien' => 'parcoursup.php',
        'difficulte' => 'Élevée'
    ],
    'paris_saclay' => [
        'titre' => 'Université Paris-Saclay',
        'description' => 'Procédure spécifique pour l\'université d\'excellence',
        'icone' => 'fa-atom',
        'couleur' => '#ff6b35',
        'avantages' => [
            'Université classée parmi les meilleures mondiales',
            'Formations d\'excellence en sciences et technologies',
            'Environnement de recherche de pointe',
            'Réseau d\'entreprises partenaires prestigieuses'
        ],
        'public' => 'Étudiants excellents toutes nationalités',
        'delai' => '3-4 mois',
        'cout' => 'Frais de dossier variables',
        'lien' => 'paris_saclay.php',
        'difficulte' => 'Très élevée'
    ]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choix de la Procédure - Études en France</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #1d4ed8;
            --secondary: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1);
            --radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 25px 0;
            box-shadow: var(--shadow-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--text);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .hero {
            text-align: center;
            margin-bottom: 60px;
            color: white;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .procedures-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }

        .procedure-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            position: relative;
        }

        .procedure-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3);
        }

        .card-header {
            padding: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.2), rgba(0,0,0,0.1));
        }

        .card-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .card-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .card-description {
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .card-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .card-body {
            padding: 30px;
        }

        .card-section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .avantages-list {
            list-style: none;
        }

        .avantages-list li {
            padding: 8px 0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .avantages-list li::before {
            content: '✓';
            color: var(--success);
            font-weight: bold;
            flex-shrink: 0;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .detail-item {
            text-align: center;
            padding: 15px;
            background: var(--background);
            border-radius: var(--radius);
        }

        .detail-label {
            font-size: 0.8rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 600;
            color: var(--text);
        }

        .card-footer {
            padding: 0 30px 30px;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.6);
        }

        .difficulte-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .difficulte-moyenne {
            background: #fef3c7;
            color: #92400e;
        }

        .difficulte-elevee {
            background: #fed7aa;
            color: #ea580c;
        }

        .difficulte-tres-elevee {
            background: #fecaca;
            color: #dc2626;
        }

        .comparison-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--radius);
            padding: 40px;
            margin-top: 50px;
            box-shadow: var(--shadow-lg);
        }

        .comparison-title {
            text-align: center;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--text);
        }

        .comparison-table {
            width: 100%;
            border-collapse: collapse;
        }

        .comparison-table th,
        .comparison-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .comparison-table th {
            background: var(--background);
            font-weight: 600;
            color: var(--text);
        }

        .comparison-table tr:hover {
            background: var(--background);
        }

        .footer {
            text-align: center;
            margin-top: 60px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .procedures-grid {
                grid-template-columns: 1fr;
            }
            
            .nav {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .comparison-section {
                padding: 20px;
                overflow-x: auto;
            }
            
            .comparison-table {
                min-width: 600px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeInUp 0.6s ease-out;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i> Études en France
            </div>
            <div class="user-menu">
                <a href="dashboard.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Tableau de bord
                </a>
                <a href="logout.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </nav>
    </header>

    <div class="container">
        <!-- Section Hero -->
        <div class="hero animate-fade-in">
            <h1 class="hero-title">Choisissez Votre Procédure</h1>
            <p class="hero-subtitle">
                Sélectionnez la procédure d'admission qui correspond à votre profil et à vos ambitions 
                pour poursuivre vos études en France.
            </p>
        </div>

        <!-- Statistiques personnelles -->
        <div class="stats-cards">
            <div class="stat-card animate-fade-in delay-1">
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Demandes Totales</div>
            </div>
            <div class="stat-card animate-fade-in delay-2">
                <div class="stat-number"><?= $stats['campus_france'] ?></div>
                <div class="stat-label">Campus France</div>
            </div>
            <div class="stat-card animate-fade-in delay-1">
                <div class="stat-number"><?= $stats['parcoursup'] ?></div>
                <div class="stat-label">Parcoursup</div>
            </div>
            <div class="stat-card animate-fade-in delay-2">
                <div class="stat-number"><?= $stats['paris_saclay'] ?></div>
                <div class="stat-label">Paris-Saclay</div>
            </div>
        </div>

        <!-- Grille des procédures -->
        <div class="procedures-grid">
            <?php foreach ($procedures as $key => $procedure): ?>
                <div class="procedure-card animate-fade-in">
                    <div class="card-header" style="background: <?= $procedure['couleur'] ?>">
                        <div class="card-icon">
                            <i class="fas <?= $procedure['icone'] ?>"></i>
                        </div>
                        <h3 class="card-title"><?= $procedure['titre'] ?></h3>
                        <p class="card-description"><?= $procedure['description'] ?></p>
                        <div class="card-badge">
                            <span class="difficulte-badge difficulte-<?= 
                                $procedure['difficulte'] === 'Moyenne' ? 'moyenne' : 
                                ($procedure['difficulte'] === 'Élevée' ? 'elevee' : 'tres-elevee')
                            ?>">
                                <?= $procedure['difficulte'] ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="card-section">
                            <h4 class="section-title">
                                <i class="fas fa-check-circle"></i> Avantages
                            </h4>
                            <ul class="avantages-list">
                                <?php foreach ($procedure['avantages'] as $avantage): ?>
                                    <li><?= $avantage ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <div class="details-grid">
                            <div class="detail-item">
                                <div class="detail-label">Public concerné</div>
                                <div class="detail-value"><?= $procedure['public'] ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Délai moyen</div>
                                <div class="detail-value"><?= $procedure['delai'] ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Coût</div>
                                <div class="detail-value"><?= $procedure['cout'] ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Vos demandes</div>
                                <div class="detail-value"><?= $stats[$key] ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <a href="<?= $procedure['lien'] ?>" class="btn-primary">
                            <i class="fas fa-arrow-right"></i>
                            Commencer la procédure
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Section de comparaison -->
        <div class="comparison-section animate-fade-in">
            <h2 class="comparison-title">Comparatif des Procédures</h2>
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Critères</th>
                        <th>Campus France</th>
                        <th>Parcoursup</th>
                        <th>Paris-Saclay</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Public cible</strong></td>
                        <td>Étudiants hors UE</td>
                        <td>Tous les étudiants</td>
                        <td>Étudiants excellents</td>
                    </tr>
                    <tr>
                        <td><strong>Délai de traitement</strong></td>
                        <td>2-3 mois</td>
                        <td>4-6 mois</td>
                        <td>3-4 mois</td>
                    </tr>
                    <tr>
                        <td><strong>Coût</strong></td>
                        <td>Gratuit</td>
                        <td>Gratuit</td>
                        <td>Frais variables</td>
                    </tr>
                    <tr>
                        <td><strong>Difficulté</strong></td>
                        <td>Moyenne</td>
                        <td>Élevée</td>
                        <td>Très élevée</td>
                    </tr>
                    <tr>
                        <td><strong>Accompagnement</strong></td>
                        <td>Personnalisé</td>
                        <td>Standardisé</td>
                        <td>Spécialisé</td>
                    </tr>
                    <tr>
                        <td><strong>Nombre de formations</strong></td>
                        <td>Large</td>
                        <td>Très large</td>
                        <td>Spécialisé</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer animate-fade-in">
            <p>Service d'accompagnement aux études en France &copy; 2024</p>
            <p style="margin-top: 10px; opacity: 0.7;">
                Besoin d'aide pour choisir ? <a href="contact.php" style="color: white; text-decoration: underline;">Contactez nos conseillers</a>
            </p>
        </div>
    </div>

    <script>
        // Animation au scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            // Observer tous les éléments avec animation
            document.querySelectorAll('.animate-fade-in').forEach(el => {
                el.style.animationPlayState = 'paused';
                observer.observe(el);
            });

            // Effet de hover amélioré sur les cartes
            const cards = document.querySelectorAll('.procedure-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.zIndex = '10';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.zIndex = '1';
                });
            });
        });
    </script>
</body>
</html>