<?php
session_start();
// Détruire toutes les variables de session
$_SESSION = array();

// Si vous voulez détruire complètement la session, effacez également
// le cookie de session.
// Note : cela détruira la session et pas seulement les données de session !
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalement, détruire la session.
session_destroy();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Déconnexion - Espagne Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e6f0ff 0%, #a7c7ff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .logout-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 81, 255, 0.15);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
            position: relative;
            overflow: hidden;
            border: 1px solid #d0e1ff;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #1a56db;
            font-size: 28px;
            font-weight: 700;
        }

        .logo span {
            color: #3b82f6;
        }

        .logout-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 40px;
            color: white;
            animation: pulse 2s infinite;
        }

        h2 {
            color: #1e3a8a;
            margin-bottom: 15px;
            font-size: 24px;
        }

        p {
            color: #4b5563;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3);
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        }

        .btn i {
            margin-right: 10px;
        }

        .countdown {
            margin-top: 20px;
            font-size: 14px;
            color: #6b7280;
        }

        .decoration {
            position: absolute;
            top: 0;
            right: 0;
            width: 80px;
            height: 80px;
            background: 
                linear-gradient(135deg, 
                #3b82f6 0% 33%, 
                #60a5fa 33% 66%, 
                #93c5fd 66% 100%);
            border-radius: 0 20px 0 20px;
        }

        .wave-decoration {
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 20px;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z' opacity='.25' fill='%233b82f6'/%3E%3Cpath d='M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z' fill='%2360a5fa'/%3E%3C/svg%3E");
            background-size: cover;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7);
            }
            70% {
                transform: scale(1.05);
                box-shadow: 0 0 0 15px rgba(59, 130, 246, 0);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(59, 130, 246, 0);
            }
        }

        @media (max-width: 480px) {
            .logout-container {
                padding: 30px 20px;
            }
            
            .logout-icon {
                width: 80px;
                height: 80px;
                font-size: 30px;
            }
            
            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="decoration"></div>
        <div class="wave-decoration"></div>
        <div class="logo">
            <h1>Espagne<span>Services</span></h1>
        </div>
        
        <div class="logout-icon">
            <i class="fas fa-sign-out-alt"></i>
        </div>
        
        <h2>Vous êtes déconnecté(e)</h2>
        <p>Vous avez été déconnecté avec succès de votre compte. Merci d'avoir utilisé nos services.</p>
        
        <a href="admin-login.php" class="btn">
            <i class="fas fa-sign-in-alt"></i>Se reconnecter
        </a>
        
        <div class="countdown" id="countdown">
            Redirection automatique dans <span id="countdown-number">5</span> secondes...
        </div>
    </div>

    <script>
        // Compte à rebours pour la redirection automatique
        let seconds = 5;
        const countdownElement = document.getElementById('countdown-number');
        
        const countdown = setInterval(function() {
            seconds--;
            countdownElement.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(countdown);
                window.location.href = 'login.php';
            }
        }, 1000);
    </script>
</body>
</html>