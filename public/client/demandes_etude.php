<?php
require_once __DIR__ . '../../../config.php';
$page_title = "Demandes d'étude - Babylone Service";


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
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
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

        .country-flag {
            font-size: 3rem;
            margin-bottom: 15px;
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
            <h1><i class="fas fa-graduation-cap"></i> Demandes d'étude</h1>
            <p>Gérez vos demandes d'étude par pays de destination</p>
        </div>

        <div class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Dashboard</a> &gt; Demandes d'étude
        </div>
        
        <div class="dashboard">
            <!-- Canada -->
            <div class="card">
              
                <div class="country-flag">🇨🇦</div>
                <h3>Canada</h3>
                <span class="status status-pending">Nouvelle</span>
                <p>Universités et collèges canadiens - Visa étudiant</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_canada.php?pays=canada'">Voir détails</button>
                    <button class="btn btn-outline" onclick="location.href='../canada/etude/index.php?pays=canada'">Nouvelle demande</button>
                </div>
            </div>
            
            <!-- France -->
            <div class="card">
                <div class="country-flag">🇫🇷</div>
                <h3>France</h3>
                <span class="status status-approved">Nouvelle</span>
                <p>Universités françaises - Campus France</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='demande_etude_france.php?pays=france'">Voir détails</button>
                    <button class="btn btn-outline" onclick="location.href='../france/etudes/index.php?pays=france'">Nouvelle demande</button>
                </div>
            </div>
            
            <!-- Roumanie -->
            <div class="card">
                <div class="country-flag">🇷🇴</div>
                <h3>Roumanie</h3>
                <span class="status status-new">Nouvelle</span>
                <p>Études médicales et techniques</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_roumanie.php?pays=roumanie'">Voir détails</button>
                    <button class="btn btn-outline" onclick="location.href='../roumanie/etude/index.php?pays=roumanie'">Nouvelle demande</button>
                </div>
            </div>
            
            <!-- Bulgarie -->
            <div class="card">
                <div class="country-flag">🇧🇬</div>
                <h3>Bulgarie</h3>
                <span class="status status-pending">Nouvelle</span>
                <p>Médecine et dentisterie</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_bulgarie.php?pays=bulgarie'">Voir détails</button>
                    <button class="btn btn-outline" onclick="location.href='../bulgarie/etude/index.php?pays=bulgarie'">Nouvelle demande</button>
                </div>
            </div>
            
            <!-- Luxembourg -->
            <div class="card">
                <div class="country-flag">🇱🇺</div>
                <h3>Luxembourg</h3>
                <span class="status status-rejected">Nouvelle</span>
                <p>Études en commerce et finance</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_luxembourg.php?pays=luxembourg'">Voir détails</button>
                    <button class="btn btn-outline" onclick="location.href='../luxembourg/etudes/index.php?pays=luxembourg'">Nouvelle demande</button>
                </div>
            </div>
            
            <!-- Belgique -->
            <div class="card">
                <div class="country-flag">🇧🇪</div>
                <h3>Belgique</h3>
                <span class="status status-approved">Nouvelle</span>
                <p>Universités francophones et néerlandophones</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_belgique.php?pays=belgique'">Voir détails</button>
                    <button class="btn btn-outline" onclick="location.href='../belgique/etude/index.php?pays=belgique'">Nouvelle demande</button>
                </div>
            </div>
            
            <!-- Suisse -->
            <div class="card">
        
                <div class="country-flag">🇨🇭</div>
                <h3>Suisse</h3>
                <span class="status status-pending">Nouvelle</span>
                <p>Écoles hôtelières et universités</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_suisse.php?pays=suisse'">Voir détails</button>
                    <button class="btn btn-outline" onclick="location.href='../suisse/etudes/index.php?pays=suisse'">Nouvelle demande</button>
                </div>
            </div>
            
            <!-- Turquie -->
            <div class="card">
                <div class="country-flag">🇹🇷</div>
                <h3>Turquie</h3>
                <span class="status status-new">Nouvelle</span>
                <p>Universités turques - Programmes en anglais</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_turquie.php?pays=turquie'">Voir détails</button>
                    <button class="btn btn-outline" onclick="location.href='../turquie/etudes/index.php?pays=turquie'">Nouvelle demande</button>
                </div>
            </div>
   
              <!-- Italie -->
            <div class="card">
                <div class="country-flag">It</div>
                <h3>Italie</h3>
                <span class="status status-new">Nouvelle</span>
                <p>Universités Italie - Programmes en anglais</p>
                <div class="actions">
                    <button class="btn" onclick="location.href='mes_demandes_italie.php?pays=turquie'">Voir détails</button>
                    <button class="btn btn-outline" onclick="location.href='../italie/etudes/index.php?pays=turquie'">Nouvelle demande</button>
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