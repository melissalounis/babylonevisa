<?php
// Démarrage de session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Chemin vers config.php
require_once __DIR__ . '/../../config.php';

// Initialisation des variables
$error = "";
$success = "";
$user = [];

// Récupérer les informations de l'utilisateur
try {
    $stmt = $pdo->prepare("SELECT id, name, email, phone, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $error = "Utilisateur non trouvé.";
    } else {
        // Séparer le nom complet en prénom et nom
        $name_parts = explode(' ', $user['name'], 2);
        $user['prenom'] = $name_parts[0] ?? '';
        $user['nom'] = $name_parts[1] ?? '';
    }
} catch (PDOException $e) {
    $error = "Erreur de connexion à la base de données.";
}

// Traitement de la modification des informations personnelles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = trim($_POST['phone']);
    
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
    } else {
        try {
            // Vérifier si l'email existe déjà pour un autre utilisateur
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                $error = "Cette adresse email est déjà utilisée par un autre compte.";
            } else {
                $name_complet = $prenom . ' ' . $nom;
                
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                $stmt->execute([$name_complet, $email, $phone, $_SESSION['user_id']]);
                
                if ($stmt->rowCount() > 0) {
                    $success = "Profil mis à jour avec succès.";
                    // Mettre à jour l'affichage
                    $user['name'] = $name_complet;
                    $user['prenom'] = $prenom;
                    $user['nom'] = $nom;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                } else {
                    $error = "Aucune modification effectuée.";
                }
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de la mise à jour du profil.";
        }
    }
}

// Traitement de la modification du mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password)) {
        $error = "Veuillez saisir votre mot de passe actuel.";
    } elseif (empty($new_password)) {
        $error = "Veuillez saisir un nouveau mot de passe.";
    } elseif (strlen($new_password) < 8) {
        $error = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        try {
            // Vérifier le mot de passe actuel
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user_data && password_verify($current_password, $user_data['password_hash'])) {
                // Hasher le nouveau mot de passe
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$new_password_hash, $_SESSION['user_id']]);
                
                if ($stmt->rowCount() > 0) {
                    $success = "Mot de passe mis à jour avec succès.";
                } else {
                    $error = "Aucune modification effectuée.";
                }
            } else {
                $error = "Mot de passe actuel incorrect.";
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de la mise à jour du mot de passe.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Babylone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: #f5f8fa;
            min-height: 100vh;
            padding: 20px;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #0077ff, #0056b3);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 40px;
            border: 3px solid white;
        }
        
        .profile-header h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .profile-header p {
            opacity: 0.9;
        }
        
        .profile-body {
            padding: 30px;
        }
        
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .back-button:hover {
            text-decoration: underline;
        }
        
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
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-section h2 i {
            color: #0077ff;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }
        
        .info-value {
            color: #333;
            font-size: 16px;
            padding: 10px 0;
        }
        
        .edit-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
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
            padding: 12px 45px;
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
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #0077ff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0066dd;
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .btn-edit {
            background: transparent;
            color: #0077ff;
            border: 1px solid #0077ff;
            padding: 8px 16px;
            font-size: 13px;
        }
        
        .btn-edit:hover {
            background: #0077ff;
            color: white;
        }
        
        .edit-toggle {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 10px;
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
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                padding: 30px 20px;
            }
            
            .profile-body {
                padding: 20px;
            }
            
            .button-group {
                flex-direction: column;
            }
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <a href="../index.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h1><?= htmlspecialchars($user['name'] ?? 'Utilisateur') ?></h1>
            <p>Membre depuis <?= isset($user['created_at']) ? date('d/m/Y', strtotime($user['created_at'])) : '--' ?></p>
        </div>

        <div class="profile-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" role="alert">
                    <i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="status">
                    <i class="fas fa-check-circle"></i><?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <!-- Informations personnelles -->
            <div class="info-section">
                <h2><i class="fas fa-user-circle"></i> Informations personnelles</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nom</span>
                        <span class="info-value"><?= htmlspecialchars($user['nom'] ?? 'Non renseigné') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Prénom</span>
                        <span class="info-value"><?= htmlspecialchars($user['prenom'] ?? 'Non renseigné') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email</span>
                        <span class="info-value"><?= htmlspecialchars($user['email'] ?? 'Non renseigné') ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Téléphone</span>
                        <span class="info-value"><?= htmlspecialchars($user['phone'] ?? 'Non renseigné') ?></span>
                    </div>
                </div>

                <div class="edit-toggle">
                    <button class="btn btn-edit" onclick="toggleEdit('profile')">
                        <i class="fas fa-edit"></i> Modifier mes informations
                    </button>
                </div>

                <!-- Formulaire de modification du profil -->
                <div id="profile-edit-form" class="edit-form" style="display: none;">
                    <form method="POST">
                        <div class="info-grid">
                            <div class="form-group">
                                <label for="nom">Nom :</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="nom" name="nom" required 
                                           value="<?= htmlspecialchars($user['nom'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="prenom">Prénom :</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="prenom" name="prenom" required 
                                           value="<?= htmlspecialchars($user['prenom'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email :</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="email" name="email" required 
                                           value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">Téléphone :</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="phone" name="phone" required 
                                           value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                                           placeholder="Ex: +33 1 23 45 67 89">
                                </div>
                            </div>
                        </div>
                        
                        <div class="button-group">
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEdit('profile')">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modification du mot de passe -->
            <div class="info-section">
                <h2><i class="fas fa-lock"></i> Sécurité du compte</h2>
                <div class="edit-toggle">
                    <button class="btn btn-edit" onclick="toggleEdit('password')">
                        <i class="fas fa-key"></i> Modifier le mot de passe
                    </button>
                </div>

                <!-- Formulaire de modification du mot de passe -->
                <div id="password-edit-form" class="edit-form" style="display: none;">
                    <form method="POST" id="passwordForm">
                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel :</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="current_password" name="current_password" required>
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('current_password')"></i>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe :</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="new_password" name="new_password" required>
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('new_password')"></i>
                            </div>
                            <div id="passwordStrength" class="password-strength"></div>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                                <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password')"></i>
                            </div>
                            <div id="passwordMatch" class="password-strength"></div>
                        </div>
                        
                        <div class="button-group">
                            <button type="submit" name="update_password" class="btn btn-primary">
                                <i class="fas fa-save"></i> Changer le mot de passe
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="toggleEdit('password')">
                                <i class="fas fa-times"></i> Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleEdit(type) {
            const form = document.getElementById(`${type}-edit-form`);
            if (form.style.display === 'none') {
                form.style.display = 'block';
                form.classList.add('fade-in');
            } else {
                form.style.display = 'none';
            }
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

        // Validation du mot de passe en temps réel
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordStrength = document.getElementById('passwordStrength');
        const passwordMatch = document.getElementById('passwordMatch');

        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', checkPasswordStrength);
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        }

        function checkPasswordStrength() {
            const password = newPasswordInput.value;
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
            const password = newPasswordInput.value;
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

        // Effacer les messages après 5 secondes
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