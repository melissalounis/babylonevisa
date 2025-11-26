<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Dashboard Client - Babylone Service";

// Récupération des informations utilisateur (exemple)
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
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 15px;
            border-radius: 50px;
        }

        .user-info i {
            margin-right: 8px;
        }

        header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            padding: 40px;
        }

        .card {
            background: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
            padding: 30px;
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
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .card i {
            font-size: 3.5rem;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--text-color);
        }

        .card p {
            color: var(--text-light);
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.4);
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
            
            header h1 {
                font-size: 2rem;
            }
            
            .user-info {
                position: static;
                justify-content: center;
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Espace Client Babylone Service</h1>
            <p>Gérez toutes vos demandes en un seul endroit</p>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($user_name); ?> (<?php echo htmlspecialchars($user_type); ?>)</span>
            </div>
        </header>
        
        <div class="dashboard">
            <div class="card" onclick="location.href='demandes_etude.php'">
                <i class="fas fa-graduation-cap"></i>
                <h3>Demandes d'étude</h3>
                <p>Consultez et gérez vos demandes liées aux études et formations</p>
                <button class="btn">Accéder</button>
            </div>
            
            <div class="card" onclick="location.href='mes_demandes_court_sejour.php'">
                <i class="fas fa-map-marked-alt"></i>
                <h3>Demandes touristiques et affaires</h3>
                <p>Vos demandes de voyages et activités touristiques et affaires</p>
                <button class="btn">Accéder</button>
            </div>
            
            <div class="card" onclick="location.href='mes_demandes_travail.php'">
                <i class="fas fa-briefcase"></i>
                <h3>Demandes de travail</h3>
                <p>Gérez vos demandes d'emploi et autorisations de travail</p>
                <button class="btn">Accéder</button>
            </div>
            
            <div class="card" onclick="location.href='mes_demandes_rendez_vous.php'">
                <i class="fas fa-calendar-alt"></i>
                <h3>Demandes rendez-vous</h3>
                <p>Prenez et consultez vos rendez-vous programmés</p>
                <button class="btn">Accéder</button>
            </div>
            
            <div class="card" onclick="location.href='mes_rendezvous_test_langue.php'">
                <i class="fas fa-language"></i>
                <h3>Test de langue</h3>
                <p>Accédez à vos tests de langue et résultats</p>
                <button class="btn">Accéder</button>
            </div>
            
            <div class="card" onclick="location.href='mes_demandes_immigration.php'">
                <i class="fas fa-passport"></i>
                <h3>Demandes immigration</h3>
                <p>Suivez vos démarches d'immigration et visas</p>
                <button class="btn">Accéder</button>
            </div>
            
            <div class="card" onclick="location.href='mes_demandes_regroupement_familial.php'">
                <i class="fas fa-users"></i>
                <h3>Regroupement familial</h3>
                <p>Gérez vos demandes de regroupement familial</p>
                <button class="btn">Accéder</button>
            </div>
            
            <!-- Nouvelles sections ajoutées -->
            <div class="card" onclick="location.href='mes_demandes_attestation_province.php'">
                <i class="fas fa-file-certificate"></i>
                <h3>Attestations de province</h3>
                <p>Demandez et suivez vos attestations provinciales</p>
                <button class="btn">Accéder</button>
            </div>
            
            <div class="card" onclick="location.href='mes_demandes_cv.php'">
                <i class="fas fa-file-alt"></i>
                <h3>Demandes de CV</h3>
                <p>Gérez vos demandes de création et optimisation de CV</p>
                <button class="btn">Accéder</button>
            </div>
            
            <div class="card" onclick="location.href='mes_demandes_billet_avion.php'">
                <i class="fas fa-plane"></i>
                <h3>Billets d'avion</h3>
                <p>Consultez et gérez vos réservations de billets d'avion</p>
                <button class="btn">Accéder</button>
            </div>
            
            <div class="card" onclick="location.href='mes_demandes_reservation_hotel.php'">
                <i class="fas fa-hotel"></i>
                <h3>Réservations d'hôtel</h3>
                <p>Suivez vos réservations et demandes d'hébergement</p>
                <button class="btn">Accéder</button>
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
                card.addEventListener('click', function() {
                    this.style.transform = 'scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        });
    </script>
</body>
</html>

<?php
include __DIR__ . '/../../includes/footer.php';
?>