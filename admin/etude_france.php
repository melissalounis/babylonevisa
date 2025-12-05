<?php
// etude_france.php
session_start();

require_once '../config.php';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $user_id = $_SESSION['user_id'] ?? 0;
    
    // Compter le nombre de demandes pour chaque type
    $stats = [
        'campus_france' => 0,
        'parcoursup' => 0,
        'universites' => 0,
        'ecoles_privees' => 0
    ];
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes_campus_france WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['campus_france'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table peut ne pas exister
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes_parcoursup WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['parcoursup'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table peut ne pas exister
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes_paris_saclay WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['universites'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table peut ne pas exister
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM demandes_ecoles_privees WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['ecoles_privees'] = $stmt->fetchColumn();
    } catch (Exception $e) {
        // Table peut ne pas exister
    }
    
} catch (PDOException $e) {
    die("Erreur de connexion BDD : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Études en France - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #003366;
            --secondary-color: #0055aa;
            --accent-color: #ff6b35;
            --campus-color: #4b0082;
            --parcoursup-color: #008080;
            --universite-color: #d35400;
            --privee-color: #27ae60;
            --light-blue: #e8f2ff;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-color: #dbe4ee;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --error-color: #dc3545;
            --border-radius: 12px;
            --box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--dark-text);
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: auto;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 30px;
            border-radius: var(--border-radius);
            margin-bottom: 40px;
            text-align: center;
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }
        
        header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-20px, -20px) rotate(360deg); }
        }
        
        header h1 {
            margin-bottom: 15px;
            font-size: 2.5rem;
            font-weight: 700;
            position: relative;
        }
        
        header p {
            opacity: 0.9;
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            border-top: 4px solid var(--primary-color);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            display: block;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: var(--dark-text);
        }
        
        .procedures-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .procedure-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
        }
        
        .procedure-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        }
        
        .card-header {
            padding: 25px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .campus-header {
            background: linear-gradient(135deg, var(--campus-color), #6a0dad);
        }
        
        .parcoursup-header {
            background: linear-gradient(135deg, var(--parcoursup-color), #00a0a0);
        }
        
        .universite-header {
            background: linear-gradient(135deg, var(--universite-color), #e67e22);
        }
        
        .privee-header {
            background: linear-gradient(135deg, var(--privee-color), #2ecc71);
        }
        
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .card-count {
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .card-content {
            padding: 25px;
        }
        
        .card-description {
            margin-bottom: 20px;
            color: #666;
            line-height: 1.6;
        }
        
        .card-features {
            margin-bottom: 25px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        
        .feature-item i {
            color: var(--success-color);
            margin-right: 10px;
            font-size: 0.9rem;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: var(--transition);
            flex: 1;
            justify-content: center;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--border-color);
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .alert {
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            border-left: 4px solid;
            background: white;
            box-shadow: var(--box-shadow);
        }
        
        .alert-warning {
            background: #fff3cd;
            border-color: var(--warning-color);
            color: #856404;
        }
        
        .quick-actions {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
        }
        
        .quick-actions h2 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        @media (max-width: 768px) {
            .procedures-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            header h1 {
                font-size: 2rem;
            }
            
            header p {
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .stats-overview {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-graduation-cap"></i> Études en France</h1>
            <p>Gérez vos demandes d'études en France via nos différentes procédures</p>
        </header>
        
        <?php if ($user_id == 0): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Attention :</strong> Vous n'êtes pas connecté. Connectez-vous pour accéder à vos demandes.
        </div>
        <?php endif; ?>
        
        <!-- Aperçu des statistiques -->
        <div class="stats-overview">
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['campus_france']; ?></span>
                <span class="stat-label">Demandes Campus France</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['parcoursup']; ?></span>
                <span class="stat-label">Demandes Parcoursup</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['universites']; ?></span>
                <span class="stat-label">Demandes Universités</span>
            </div>
            <div class="stat-card">
                <span class="stat-number"><?php echo $stats['ecoles_privees']; ?></span>
                <span class="stat-label">Demandes Écoles Privées</span>
            </div>
        </div>
        
        <!-- Grille des procédures -->
        <div class="procedures-grid">
            <!-- Carte Campus France -->
            <div class="procedure-card">
                <div class="card-header campus-header">
                    <div class="card-icon">
                        <i class="fas fa-university"></i>
                    </div>
                    <h3 class="card-title">Campus France</h3>
                    <span class="card-count"><?php echo $stats['campus_france']; ?> demande(s)</span>
                </div>
                <div class="card-content">
                    <p class="card-description">
                        Procédure pour les étudiants internationaux hors Union Européenne souhaitant étudier en France.
                    </p>
                    <div class="card-features">
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Étudiants internationaux</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Hors Union Européenne</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Demande de visa incluse</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="campus_france_demandes.php" class="btn btn-primary">
                            <i class="fas fa-list"></i> Voir mes demandes
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Carte Parcoursup -->
            <div class="procedure-card">
                <div class="card-header parcoursup-header">
                    <div class="card-icon">
                        <i class="fas fa-list-alt"></i>
                    </div>
                    <h3 class="card-title">Parcoursup</h3>
                    <span class="card-count"><?php echo $stats['parcoursup']; ?> demande(s)</span>
                </div>
                <div class="card-content">
                    <p class="card-description">
                        Plateforme nationale pour l'entrée dans l'enseignement supérieur en France.
                    </p>
                    <div class="card-features">
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Étudiants français et UE</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Première année d'études</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Formations diverses</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="parcoursup_demandes.php" class="btn btn-primary">
                            <i class="fas fa-list"></i> Voir mes demandes
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Carte Universités non connectées -->
            <div class="procedure-card">
                <div class="card-header universite-header">
                    <div class="card-icon">
                        <i class="fas fa-gem"></i>
                    </div>
                    <h3 class="card-title">Universités</h3>
                    <span class="card-count"><?php echo $stats['universites']; ?> demande(s)</span>
                </div>
                <div class="card-content">
                    <p class="card-description">
                        Demandes directes auprès des universités françaises (Paris-Saclay, Sorbonne, etc.)
                    </p>
                    <div class="card-features">
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Admission directe</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Formations spécifiques</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Procédures variées</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="universites_demandes.php" class="btn btn-primary">
                            <i class="fas fa-list"></i> Voir mes demandes
                        </a>
                    </div>
                </div>
            </div>

            <!-- Carte Écoles Privées -->
            <div class="procedure-card">
                <div class="card-header privee-header">
                    <div class="card-icon">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="card-title">Écoles Privées</h3>
                    <span class="card-count"><?php echo $stats['ecoles_privees']; ?> demande(s)</span>
                </div>
                <div class="card-content">
                    <p class="card-description">
                        Grandes écoles de commerce, d'ingénieurs, écoles spécialisées et établissements privés en France.
                    </p>
                    <div class="card-features">
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Grandes écoles (HEC, ESSEC, etc.)</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Écoles d'ingénieurs privées</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Formations professionnalisantes</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>Concours spécifiques</span>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="ecoles_privees_demandes.php" class="btn btn-primary">
                            <i class="fas fa-list"></i> Voir mes demandes
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions rapides -->
        <div class="quick-actions">
            <h2>Actions Rapides</h2>
            <div class="action-buttons">
                <a href="campus_france_form.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Nouvelle demande Campus France
                </a>
                <a href="parcoursup_form.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Nouvelle demande Parcoursup
                </a>
                <a href="paris_saclay_form.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Nouvelle demande Université
                </a>
                <a href="ecoles_privees_form.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Nouvelle demande École Privée
                </a>
            </div>
        </div>
    </div>

    <script>
        // Animation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.procedure-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>