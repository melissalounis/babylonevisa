<?php
session_start();
// Initialisation des variables
$error = "";
$success = "";

// Chemin correct vers config.php
require_once __DIR__ . '/../../config.php';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez saisir une adresse email valide.";
    } else {
        try {
            // Vérifier si l'email existe dans la base de données
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Générer un token de réinitialisation
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token valide 1 heure

                // Stocker le token dans la base de données
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $stmt->execute([$email, $token, $expires]);

                // Ici, vous devriez envoyer un email avec le lien de réinitialisation
                // Pour l'instant, nous allons simuler l'envoi et afficher le lien (en développement)
                
                $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/auth/reset_password.php?token=" . $token;
                
                // En production, décommentez et configurez l'envoi d'email :
                /*
                $to = $email;
                $subject = "Réinitialisation de votre mot de passe - Babylone";
                $message = "Bonjour " . $user['name'] . ",\n\n";
                $message .= "Vous avez demandé la réinitialisation de votre mot de passe.\n";
                $message .= "Cliquez sur le lien suivant pour créer un nouveau mot de passe :\n";
                $message .= $reset_link . "\n\n";
                $message .= "Ce lien expirera dans 1 heure.\n";
                $message .= "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n";
                $message .= "Cordialement,\nL'équipe Babylone";
                
                $headers = "From: no-reply@babylone.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                
                mail($to, $subject, $message, $headers);
                */
                
                // Pour le développement, afficher le lien
                $success = "Un email de réinitialisation a été envoyé à " . htmlspecialchars($email) . 
                          "<br><br><strong>Lien de développement :</strong><br>" . 
                          "<a href='" . $reset_link . "' style='color: #0077ff;'>" . $reset_link . "</a>";

            } else {
                $error = "Aucun compte n'est associé à cette adresse email.";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion à la base de données. Veuillez réessayer plus tard.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - Babylone</title>
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
            line-height: 1.5;
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
        
        .instructions {
            background: #f0f7ff;
            border: 1px solid #d0e3ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #333;
        }
        
        .instructions i {
            color: #0077ff;
            margin-right: 10px;
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
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="close-button" onclick="window.location.href='login.php'" aria-label="Retour">
            <i class="fas fa-arrow-left"></i>
        </div>

        <div class="auth-header">
            <h2>Mot de passe oublié</h2>
            <p>Entrez votre email pour recevoir un lien de réinitialisation</p>
        </div>

        <div class="auth-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" role="alert"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status">
                    <i class="fas fa-check-circle"></i>
                    <span><?= $success ?></span>
                </div>
            <?php endif; ?>

            <div class="instructions">
                <i class="fas fa-info-circle"></i>
                Nous vous enverrons un lien par email pour réinitialiser votre mot de passe. Ce lien sera valide pendant 1 heure.
            </div>

            <form method="POST" id="forgotForm">
                <div class="form-group">
                    <label for="email">Email :</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required autocomplete="email" autofocus
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>

                <button type="submit" id="submitButton">
                    <div class="spinner" id="spinner"></div>
                    <span id="buttonText">Envoyer le lien de réinitialisation</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Vous vous souvenez de votre mot de passe ? <a href="login.php">Se connecter</a></p>
            </div>
        </div>
    </div>

    <script>
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('buttonText');
        const submitButton = document.getElementById('submitButton');
        const forgotForm = document.getElementById('forgotForm');

        forgotForm.addEventListener('submit', (e) => {
            const email = document.getElementById('email').value;
            
            if (!email) {
                e.preventDefault();
                forgotForm.classList.add('shake');
                setTimeout(() => forgotForm.classList.remove('shake'), 500);
                return;
            }
            
            spinner.style.display = 'block';
            buttonText.textContent = 'Envoi en cours...';
            submitButton.disabled = true;
        });

        // Effacer les messages flash après 8 secondes (plus long pour lire le lien)
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(() => alert.remove(), 500);
            });
        }, 8000);
    </script>
</body>
</html>