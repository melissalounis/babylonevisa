<?php
// Initialisation de la session
session_start();

// Initialisation des variables AVANT toute logique
$error = "";
$success = "";

// Vérifier si déjà connecté
if (isset($_SESSION['user_id'])) {
    header("Location: /../../index.php");
    exit;
}

// Chemin correct vers config.php
require_once __DIR__ . '/../../config.php';

// Fonction pour envoyer l'email de confirmation
function envoyerEmailConfirmation($email, $nom, $prenom) {
    $sujet = "Confirmation de création de compte - Babylone";
    
    // Version HTML de l'email
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Confirmation de création de compte</title>
        <style>
            body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0077ff; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { padding: 30px; background: #f9f9f9; border-radius: 0 0 10px 10px; }
            .button { background: #0077ff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; margin-top: 20px; }
            .security-note { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Bienvenue sur Babylone !</h1>
            </div>
            <div class='content'>
                <h2>Bonjour $prenom $nom,</h2>
                <p><strong>Votre compte a été créé avec succès !</strong></p>
                
                <p>Vous pouvez dès maintenant accéder à votre espace personnel et découvrir tous nos services.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/login.php' class='button'>🔐 Me connecter à mon compte</a>
                </div>
                
                <div class='security-note'>
                    <h3>🛡️ Conseils de sécurité :</h3>
                    <ul>
                        <li>Ne partagez jamais vos identifiants</li>
                        <li>Utilisez un mot de passe fort et unique</li>
                        <li>Changez régulièrement votre mot de passe</li>
                    </ul>
                </div>
                
                <p>Si vous rencontrez des difficultés pour vous connecter, n'hésitez pas à nous contacter.</p>
                
                <p>Cordialement,<br><strong>L'équipe Babylone</strong></p>
            </div>
            <div class='footer'>
                <p>© " . date('Y') . " Babylone. Tous droits réservés.</p>
                <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Headers pour email HTML
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Babylone <babylone.service15@gmail.com>" . "\r\n";
    $headers .= "Reply-To: babylone.service15@gmail.com" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $headers .= "X-Priority: 1" . "\r\n"; // Haute priorité
    
    // Essayer d'envoyer l'email
    if (mail($email, $sujet, $message, $headers)) {
        // Log de succès
        error_log("[" . date('Y-m-d H:i:s') . "] Email envoyé à: $email");
        file_put_contents(__DIR__ . '/email_success.log', 
            "[" . date('Y-m-d H:i:s') . "] SUCCÈS - Email à: $email | Nom: $prenom $nom\n", 
            FILE_APPEND
        );
        return true;
    } else {
        // Log d'erreur
        error_log("[" . date('Y-m-d H:i:s') . "] Échec envoi email à: $email");
        file_put_contents(__DIR__ . '/email_error.log', 
            "[" . date('Y-m-d H:i:s') . "] ERREUR - Échec envoi à: $email | Nom: $prenom $nom\n", 
            FILE_APPEND
        );
        return false;
    }
}

// Messages URL (après inscription réussie)
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $success = "Inscription réussie ! Un email de confirmation vous a été envoyé.";
    } elseif ($_GET['success'] == 2) {
        $success = "Inscription réussie ! Vous pouvez maintenant vous connecter. (Email de confirmation non envoyé)";
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation des champs
    if (empty($nom)) {
        $error = "Veuillez saisir votre nom.";
    } elseif (strlen($nom) < 2 || strlen($nom) > 60) {
        $error = "Le nom doit contenir entre 2 et 60 caractères.";
    } elseif (empty($prenom)) {
        $error = "Veuillez saisir votre prénom.";
    } elseif (strlen($prenom) < 2 || strlen($prenom) > 60) {
        $error = "Le prénom doit contenir entre 2 et 60 caractères.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez saisir une adresse email valide.";
    } elseif (empty($phone)) {
        $error = "Veuillez saisir votre numéro de téléphone.";
    } elseif (!preg_match('/^[0-9+\-\s()]{10,20}$/', $phone)) {
        $error = "Veuillez saisir un numéro de téléphone valide.";
    } elseif (empty($password)) {
        $error = "Veuillez saisir un mot de passe.";
    } elseif (strlen($password) < 8) {
        $error = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = "Cette adresse email est déjà utilisée.";
            } else {
                // Hasher le mot de passe
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $name_complet = $prenom . ' ' . $nom;
                
                // Insérer le nouvel utilisateur
                $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name_complet, $email, $phone, $password_hash]);
                
                if ($stmt->rowCount() > 0) {
                    // Envoyer l'email de confirmation
                    if (envoyerEmailConfirmation($email, $nom, $prenom)) {
                        header("Location: login.php?success=1");
                        exit;
                    } else {
                        // Si l'email n'a pas pu être envoyé, on redirige quand même mais avec un message différent
                        header("Location: login.php?success=2");
                        exit;
                    }
                } else {
                    $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion à la base de données. Veuillez réessayer plus tard.";
            error_log("Erreur PDO: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Babylone</title>
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
        
        .required-field::after {
            content: " *";
            color: #c62828;
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
        
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
            display: none;
        }
        
        .password-weak {
            color: #c62828;
        }
        
        .password-medium {
            color: #f57c00;
        }
        
        .password-strong {
            color: #2e7d32;
        }
        
        .terms {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .terms input {
            margin-right: 10px;
            margin-top: 3px;
        }
        
        .terms a {
            color: #0077ff;
            text-decoration: none;
        }
        
        .terms a:hover {
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
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="close-button" onclick="window.history.back()" aria-label="Fermer">
            <i class="fas fa-times"></i>
        </div>

        <div class="auth-header">
            <h2>Inscription</h2>
            <p>Créez votre compte personnel</p>
        </div>

        <div class="auth-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" role="alert"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status"><i class="fas fa-check-circle"></i><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" id="registerForm">
                <div class="form-group">
                    <label for="nom" class="required-field">Nom :</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="nom" name="nom" required autocomplete="family-name" autofocus
                               value="<?= isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="prenom" class="required-field">Prénom :</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="prenom" name="prenom" required autocomplete="given-name"
                               value="<?= isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="email" class="required-field">Email :</label>
                    <div class="input-with-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required autocomplete="email"
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone" class="required-field">Téléphone :</label>
                    <div class="input-with-icon">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" required autocomplete="tel"
                               placeholder="Ex: +213 21 23 45 67 89"
                               value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password" class="required-field">Mot de passe :</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required autocomplete="new-password">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('password')" aria-label="Afficher le mot de passe"></i>
                    </div>
                    <div id="passwordStrength" class="password-strength"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="required-field">Confirmer le mot de passe :</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                        <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')" aria-label="Afficher le mot de passe"></i>
                    </div>
                    <div id="passwordMatch" class="password-strength"></div>
                </div>

                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms" class="required-field">J'accepte les <a href="#">conditions d'utilisation</a> et la <a href="#">politique de confidentialité</a></label>
                </div>

                <button type="submit" id="submitButton">
                    <div class="spinner" id="spinner"></div>
                    <span id="buttonText">S'inscrire</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Déjà inscrit ? <a href="login.php">Se connecter</a></p>
            </div>
        </div>
    </div>

    <script>
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('buttonText');
        const submitButton = document.getElementById('submitButton');
        const registerForm = document.getElementById('registerForm');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordMatch = document.getElementById('passwordMatch');

        // Validation du mot de passe en temps réel
        passwordInput.addEventListener('input', checkPasswordStrength);
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);

        function checkPasswordStrength() {
            const password = passwordInput.value;
            let strength = 0;
            let message = '';
            let className = '';
            
            if (password.length === 0) {
                passwordStrength.style.display = 'none';
                return;
            }
            
            // Critères de force
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            if (strength <= 2) {
                message = 'Faible';
                className = 'password-weak';
            } else if (strength <= 4) {
                message = 'Moyen';
                className = 'password-medium';
            } else {
                message = 'Fort';
                className = 'password-strong';
            }
            
            passwordStrength.textContent = `Force du mot de passe : ${message}`;
            passwordStrength.className = `password-strength ${className}`;
            passwordStrength.style.display = 'block';
        }

        function checkPasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            if (confirmPassword.length === 0) {
                passwordMatch.style.display = 'none';
                return;
            }
            
            if (password === confirmPassword) {
                passwordMatch.textContent = 'Les mots de passe correspondent';
                passwordMatch.className = 'password-strength password-strong';
            } else {
                passwordMatch.textContent = 'Les mots de passe ne correspondent pas';
                passwordMatch.className = 'password-strength password-weak';
            }
            passwordMatch.style.display = 'block';
        }

        function togglePassword(fieldId) {
            const pwd = document.getElementById(fieldId);
            const icon = pwd.parentNode.querySelector('.toggle-password');
            
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

        registerForm.addEventListener('submit', (e) => {
            // Validation côté client
            const nom = document.getElementById('nom').value;
            const prenom = document.getElementById('prenom').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            let isValid = true;
            
            if (!nom || nom.length < 2) {
                isValid = false;
            }
            
            if (!prenom || prenom.length < 2) {
                isValid = false;
            }
            
            if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                isValid = false;
            }
            
            if (!phone || !/^[0-9+\-\s()]{10,20}$/.test(phone)) {
                isValid = false;
            }
            
            if (!password || password.length < 8) {
                isValid = false;
            }
            
            if (password !== confirmPassword) {
                isValid = false;
            }
            
            if (!terms) {
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                registerForm.classList.add('shake');
                setTimeout(() => registerForm.classList.remove('shake'), 500);
                return;
            }
            
            spinner.style.display = 'block';
            buttonText.textContent = 'Inscription...';
            submitButton.disabled = true;
        });

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