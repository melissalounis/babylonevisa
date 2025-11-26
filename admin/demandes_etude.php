<?php
// demandes_etude.php
session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit;
}

// Connexion à la base de données pour les statistiques
$host = "localhost";
$dbname = "babylone_service";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Continuer sans statistiques si la connexion échoue
    $pdo = null;
}

$pays_options = [
    'france' => [
        'nom' => 'France', 
        'icon' => 'fa-tower-eiffel', 
        'color' => '#0055A4',
        'page' => 'etude_france.php'
    ],
    'belgique' => [
        'nom' => 'Belgique', 
        'icon' => 'fa-crown', 
        'color' => '#000000',
        'page' => 'etude_belgique.php'
    ],
    'canada' => [
        'nom' => 'Canada', 
        'icon' => 'fa-maple-leaf', 
        'color' => '#FF0000',
        'page' => 'admin_etude_canada.php'
    ],
    'roumanie' => [
        'nom' => 'Roumanie', 
        'icon' => 'fa-landmark', 
        'color' => '#002B7F',
        'page' => 'admin_etude_roumanie.php'
    ],
    'bulgarie' => [
        'nom' => 'Bulgarie', 
        'icon' => 'fa-lion', 
        'color' => '#00966E',
        'page' => 'admin_etude_bulgarie.php'
    ],
    'sussie' => [
        'nom' => 'Sussie', 
        'icon' => 'fa-flag', 
        'color' => '#D52B1E',
        'page' => 'etude_sussie.php'
    ],
    'luxembourg' => [
        'nom' => 'Luxembourg', 
        'icon' => 'fa-euro-sign',
        'color' => '#00A1DE',
        'page' => 'etude_luxembourg.php'
    ],
    'turquie' => [
        'nom' => 'Turquie', 
        'icon' => 'fa-moon', 
        'color' => '#E30A17',
        'page' => 'admin_etude_turquie.php'
    ],
     'espagne' => [
        'nom' => 'Eespagne', 
        'icon' => 'fa-moon', 
        'color' => '#E30A17',
        'page' => 'etude_espagne.php'
    ]
];

// Récupérer les statistiques pour chaque pays
$stats_par_pays = [];
if ($pdo) {
    foreach ($pays_options as $pays_code => $pays_info) {
        $table_name = "demande_etudes_" . $pays_code;
        try {
            $sql = "SHOW TABLES LIKE '$table_name'";
            $stmt = $pdo->query($sql);
            if ($stmt->rowCount() > 0) {
                $sql_stats = "
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                        SUM(CASE WHEN statut = 'accepté' THEN 1 ELSE 0 END) as acceptes,
                        SUM(CASE WHEN statut = 'refusé' THEN 1 ELSE 0 END) as refusés
                    FROM $table_name
                ";
                $stmt_stats = $pdo->query($sql_stats);
                $stats_par_pays[$pays_code] = $stmt_stats->fetch(PDO::FETCH_ASSOC);
            } else {
                $stats_par_pays[$pays_code] = [
                    'total' => 0, 
                    'en_attente' => 0, 
                    'acceptes' => 0, 
                    'refusés' => 0
                ];
            }
        } catch (PDOException $e) {
            $stats_par_pays[$pays_code] = [
                'total' => 0, 
                'en_attente' => 0, 
                'acceptes' => 0, 
                'refusés' => 0
            ];
        }
    }
}

// Statistiques globales
$stats_globales = ['total' => 0, 'en_attente' => 0, 'acceptes' => 0, 'refusés' => 0];
foreach ($stats_par_pays as $stats) {
    $stats_globales['total'] += $stats['total'];
    $stats_globales['en_attente'] += $stats['en_attente'];
    $stats_globales['acceptes'] += $stats['acceptes'];
    $stats_globales['refusés'] += $stats['refusés'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Centre de Gestion des Études à l'Étranger</title>
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

        .admin-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: var(--text);
            padding: 20px 0;
            box-shadow: var(--shadow-lg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .admin-nav {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-title {
            font-size: 1.75rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: var(--radius);
            font-size: 0.9rem;
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
            box-shadow: var(--shadow-lg);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 50px;
            color: white;
        }

        .welcome-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 16px;
            text-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .welcome-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }

        .stats-global {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }

        .stat-global-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }

        .stat-global-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .stat-global-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .stat-global-label {
            color: var(--text-light);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .section-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            text-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .pays-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .pays-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            text-align: center;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        .pays-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .pays-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3);
        }

        .pays-icon {
            font-size: 3.5rem;
            margin-bottom: 20px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pays-name {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: var(--text);
        }

        .pays-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .pays-stat {
            text-align: center;
        }

        .pays-stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .pays-stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.6);
        }

        .footer {
            text-align: center;
            margin-top: 60px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .pays-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-nav {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .welcome-title {
                font-size: 2rem;
            }
            
            .section-title {
                font-size: 1.75rem;
            }
            
            .stats-global {
                grid-template-columns: repeat(2, 1fr);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <nav class="admin-nav">
            <div class="admin-title">
                <i class="fas fa-globe-europe"></i> Centre de Gestion des Études
            </div>
            <div class="admin-actions">
                <a href="admin_dashboard.php" class="btn btn-outline">
                    <i class="fas fa-chart-line"></i> Tableau de bord
                </a>
                <a href="admin_logout.php" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </nav>
    </header>

    <div class="container">
        <!-- Section de bienvenue -->
        <div class="welcome-section animate-fade-in">
            <h1 class="welcome-title">Études à l'Étranger</h1>
            <p class="welcome-subtitle">Gérez les demandes d'études internationales en temps réel</p>
        </div>

        <!-- Statistiques globales -->
        <div class="stats-global">
            <div class="stat-global-card animate-fade-in delay-1">
                <div class="stat-global-number"><?= $stats_globales['total'] ?></div>
                <div class="stat-global-label">Total des demandes</div>
            </div>
            <div class="stat-global-card animate-fade-in delay-2">
                <div class="stat-global-number"><?= $stats_globales['en_attente'] ?></div>
                <div class="stat-global-label">En attente</div>
            </div>
            <div class="stat-global-card animate-fade-in delay-1">
                <div class="stat-global-number"><?= $stats_globales['acceptes'] ?></div>
                <div class="stat-global-label">Acceptées</div>
            </div>
            <div class="stat-global-card animate-fade-in delay-2">
                <div class="stat-global-number"><?= $stats_globales['refusés'] ?></div>
                <div class="stat-global-label">Refusées</div>
            </div>
        </div>

        <!-- Section des pays -->
        <h2 class="section-title animate-fade-in">Destinations disponibles</h2>
        
        <div class="pays-grid">
            <?php foreach ($pays_options as $pays_code => $pays_info): 
                $stats = $stats_par_pays[$pays_code] ?? [
                    'total' => 0, 
                    'en_attente' => 0, 
                    'acceptes' => 0, 
                    'refusés' => 0
                ];
                $icon = $pays_info['icon'] ?? 'fa-flag';
                $color = $pays_info['color'] ?? '#3b82f6';
                $page = $pays_info['page'] ?? 'etude_' . $pays_code . '.php';
            ?>
                <div class="pays-card animate-fade-in">
                    <div class="pays-icon">
                        <i class="fas <?= $icon ?>" style="color: <?= $color ?>"></i>
                    </div>
                    <div class="pays-name"><?= htmlspecialchars($pays_info['nom']) ?></div>
                    
                    <div class="pays-stats">
                        <div class="pays-stat">
                            <div class="pays-stat-number"><?= $stats['total'] ?></div>
                            <div class="pays-stat-label">Total</div>
                        </div>
                        <div class="pays-stat">
                            <div class="pays-stat-number" style="color: <?= $stats['en_attente'] > 0 ? '#f59e0b' : '#64748b' ?>">
                                <?= $stats['en_attente'] ?>
                            </div>
                            <div class="pays-stat-label">En attente</div>
                        </div>
                    </div>

                    <a href="<?= $page ?>" class="btn-primary">
                        <i class="fas fa-arrow-right"></i>
                        Accéder aux études
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer -->
        <div class="footer animate-fade-in">
            <p>Système de gestion des études à l'étranger &copy; 2024</p>
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
        });
    </script>
</body>
</html>