<?php
session_start();
// Initialisation des variables AVANT toute logique
$error = "";
$success = "";

// Vérifier si déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: /../../index.php");
    exit;
}

// Chemin correct vers config.php (adapté à la structure de vos dossiers)
require_once __DIR__ . '../../../config.php';

// Protection contre les attaques par force brute
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_login_attempt'] = 0;
}

// Messages URL (après inscription / logout) - Déplacé avant le traitement POST
if (isset($_GET['registered'])) {
    $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
}
if (isset($_GET['logout'])) {
    $success = "Vous avez été déconnecté avec succès.";
}
if (isset($_GET['session_expired'])) {
    $error = "Votre session a expiré. Veuillez vous reconnecter.";
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du délai entre les tentatives (protection force brute)
    if (time() - $_SESSION['last_login_attempt'] < 2) {
        $error = "Veuillez patienter avant de réessayer.";
    }
    // Vérification du nombre maximum de tentatives
    elseif ($_SESSION['login_attempts'] >= 5) {
        $error = "Trop de tentatives de connexion. Veuillez réessayer dans 15 minutes.";
        $_SESSION['last_login_attempt'] = time();
    } else {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Veuillez saisir une adresse email valide.";
        } elseif (!$password) {
            $error = "Veuillez saisir votre mot de passe.";
        } else {
            try {
                // CORRECTION : La table 'users' n'a pas de colonne 'is_admin' selon votre structure
                $stmt = $pdo->prepare("SELECT id, name, email, password_hash, created_at FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    // Réinitialiser le compteur de tentatives après une connexion réussie
                    $_SESSION['login_attempts'] = 0;
                    
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    // CORRECTION : Pas de colonne is_admin, donc on ne peut pas définir ce rôle
                    // $_SESSION['is_admin'] = false; // Par défaut, pas administrateur
                    
                    header("Location: ../index.php");
                    exit;
                } else {
                    $error = "Identifiants invalides. Veuillez réessayer.";
                }
            } catch (PDOException $e) {
                $error = "Erreur de connexion à la base de données. Veuillez réessayer plus tard.";
            }
        }
        
        // Incrémenter le compteur de tentatives après une tentative échouée
        $_SESSION['login_attempts']++;
        $_SESSION['last_login_attempt'] = time();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Babylone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* --- RESET & BASE STYLES --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f8fa;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        /* --- CONTAINER & LAYOUT --- */
        .auth-container {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            border: 1px solid #e0e0e0;
            animation: fadeIn 0.5s ease-out;
        }
        
        .close-button {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: 0.3s;
            z-index: 10;
        }
        
        .close-button:hover {
            background: #eeeeee;
            transform: rotate(90deg);
        }
        
        .close-button i {
            color: #666;
            font-size: 18px;
        }
        
        .auth-header {
            background: #f9f9f9;
            color: #333;
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid #eeeeee;
        }
        
        .auth-header h2 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: #0077ff;
        }
        
        .auth-header p {
            color: #666;
        }
        
        .auth-body {
            padding: 30px;
        }
        
        /* --- ALERT MESSAGES --- */
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        
        .alert-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .alert i {
            margin-right: 10px;
        }
        
        /* --- FORM ELEMENTS --- */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            display: block;
            margin-bottom: 8px;
        }
        
        .input-with-icon {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-with-icon .fas {
            position: absolute;
            color: #0077ff;
            z-index: 2;
            left: 15px;
        }
        
        .input-with-icon .toggle-password {
            left: auto;
            right: 15px;
            color: #999;
            cursor: pointer;
        }
        
        .input-with-icon .toggle-password:hover {
            color: #0077ff;
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 14px 45px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: 0.3s;
        }
        
        .input-with-icon input:focus {
            border-color: #0077ff;
            box-shadow: 0 0 0 3px rgba(0, 119, 255, 0.1);
            outline: none;
        }
        
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .remember {
            display: flex;
            align-items: center;
        }
        
        .remember input {
            margin-right: 8px;
        }
        
        .forgot-password {
            color: #0077ff;
            text-decoration: none;
            font-weight: 600;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        button {
            width: 100%;
            padding: 14px;
            border-radius: 8px;
            border: none;
            background: #0077ff;
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        button:hover {
            background: #0066dd;
            box-shadow: 0 4px 12px rgba(0, 119, 255, 0.2);
        }
        
        button:active {
            transform: translateY(1px);
        }
        
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }
        
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s infinite;
            margin-right: 10px;
        }
        
        .auth-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #555;
        }
        
        .auth-footer a {
            color: #0077ff;
            font-weight: 600;
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        /* --- ANIMATIONS --- */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(5px);
            }
        }
        
        .shake {
            animation: shake 0.5s;
        }
        
        /* --- RESPONSIVE DESIGN --- */
        @media (max-width: 480px) {
            .auth-container {
                margin: 15px;
            }
            
            .auth-body {
                padding: 25px;
            }
            
            .remember-forgot {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .forgot-password {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="close-button" onclick="window.history.back()" aria-label="Fermer">
            <i class="fas fa-times"></i>
        </div>

        <div class="auth-header">
            <h2>Connexion</h2>
            <p>Accédez à votre espace personnel</p>
        </div>

        <div class="auth-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" role="alert"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" id="loginForm">
                <div class="form-group">
                    <label for="email">Email :</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required autocomplete="email" autofocus
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe :</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword()" aria-label="Afficher le mot de passe"></i>
                    </div>
                </div>

                <div class="remember-forgot">
                    <div class="remember">
                        <input type="checkbox" id="remember" name="remember" <?= isset($_POST['remember']) ? 'checked' : '' ?>>
                        <label for="remember">Se souvenir de moi</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Mot de passe oublié ?</a>
                </div>

                <button type="submit" id="submitButton">
                    <div class="spinner" id="spinner"></div>
                    <span id="buttonText">Se connecter</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>
            </div>
        </div>
    </div>

    <script>
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('buttonText');
        const submitButton = document.getElementById('submitButton');
        const loginForm = document.getElementById('loginForm');

        loginForm.addEventListener('submit', (e) => {
            // Validation côté client basique
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                loginForm.classList.add('shake');
                setTimeout(() => loginForm.classList.remove('shake'), 500);
                return;
            }
            
            spinner.style.display = 'block';
            buttonText.textContent = 'Connexion...';
            submitButton.disabled = true;
        });

        function togglePassword() {
            const pwd = document.getElementById("password");
            const icon = document.querySelector('.toggle-password');
            
            if (pwd.type === "password") {
                pwd.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Effacer les messages flash après 5 secondes
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>