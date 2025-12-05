<?php
require_once __DIR__ . '../../../config.php';
$page_title = "Demandes d'étude France - Babylone Service";

// Récupération des informations utilisateur
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Client';
$user_type = isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'Client';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6a11cb;
            --secondary-color: #2575fc;
            --accent-color: #ff6b6b;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --text-color: #333;
            --text-light: #666;
            --shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .page-header h1 {
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .breadcrumb {
            background: var(--light-color);
            padding: 15px 30px;
            border-bottom: 1px solid #eee;
        }

        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            padding: 40px;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border: 1px solid rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-hover);
        }

        .icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }

        .card h3 {
            font-size: 1.4rem;
            margin-bottom: 12px;
            color: var(--text-color);
        }

        .status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-approved {
            background: #d1edff;
            color: #0c5460;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .status-new {
            background: #d4edda;
            color: #155724;
        }

        .card p {
            color: var(--text-light);
            line-height: 1.5;
            margin-bottom: 15px;
            font-size: 0.95rem;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.4);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .notification-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent-color);
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        footer {
            text-align: center;
            padding: 20px;
            background: var(--light-color);
            color: var(--text-light);
            border-top: 1px solid #eee;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
                padding: 20px;
            }
            
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-graduation-cap"></i> Demandes d'étude - France</h1>
            <p>Gérez vos demandes d'étude en France par type de procédure</p>
        </div>

        <div class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Dashboard</a> &gt; 
            <a href="demandes_etude.php">Demandes d'étude</a> &gt; France
        </div>
        
        <div class="dashboard">
            <!-- Campus France -->
            <div class="card">
        
                <div class="icon"><i class="fas fa-university"></i></div>
                <h3>Campus France</h3>
                <span class="status status-pending">Nouvelle</span>
                <p>Procédure Campus France pour les universités publiques françaises</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_campus_france.php'">Voir mes demandes</button>
                    <button class="btn btn-outline" onclick="location.href='../france/etudes/campus_france.php'">Nouvelle demande</button>
                </div>
            </div>
            
            <!-- Parcoursup -->
            <div class="card">
                <div class="icon"><i class="fas fa-list-ol"></i></div>
                <h3>Parcoursup</h3>
                <span class="status status-approved">Nouvelle</span>
                <p>Plateforme nationale pour les formations post-bac en France</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_parcoursup.php'">Voir mes demandes</button>
                    <button class="btn btn-outline" onclick="location.href='../france/etudes/parcoursup.php'">Nouvelle demande</button>
                </div>
            </div>
            
            <!-- Établissements Non Connectés -->
            <div class="card">
               
                <div class="icon"><i class="fas fa-building"></i></div>
                <h3>Établissements Non Connectés</h3>
                <span class="status status-new">Nouvelle</span>
                <p>Universités et écoles privées hors procédures centralisées</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_etablissements_non_connectes.php'">Voir mes demandes</button>
                    <button class="btn btn-outline" onclick="location.href='../france/etudes/ecole_privee.php'">Nouvelle demande</button>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; <?php echo date('Y'); ?> Babylone Service - Tous droits réservés</p>
        </footer>
    </div>

    <script>
        // Animation d'entrée pour les cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
            
            // Ajout d'un effet de clic sur les cartes
            cards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Ne pas déclencher si on clique sur un bouton
                    if (!e.target.classList.contains('btn')) {
                        this.style.transform = 'scale(0.98)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 150);
                    }
                });
            });
        });
    </script>
</body>
</html>

<?php
include __DIR__ . '/../../includes/footer.php';
?>