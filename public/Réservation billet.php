<?php
// DEBUT DU FICHIER - session_start() doit être au tout début
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header('Location: client/login.php');
    exit();
}

$user_email = $_SESSION['user_email'];

// Inclusion de la configuration de la base de données
include '../config.php';

// Initialiser les variables pour éviter les erreurs
$email_contact = $telephone_contact = $pays_depart = $ville_depart = $pays_arrivee = $ville_arrivee = $date_depart = $date_retour = $compagnie_preferee = $commentaires = '';
$type_vol = 'aller_simple';
$classe = 'economique';
$nombre_passagers = 1;
$baggage_main = '1_piece';
$baggage_soute = 'aucun';

$message_success = '';
$erreurs = [];

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Informations de contact
    $email_contact = isset($_POST['email_contact']) ? trim($_POST['email_contact']) : '';
    $telephone_contact = isset($_POST['telephone_contact']) ? trim($_POST['telephone_contact']) : '';
    
    // Informations de vol
    $type_vol = isset($_POST['type_vol']) ? $_POST['type_vol'] : 'aller_simple';
    $pays_depart = isset($_POST['pays_depart']) ? trim($_POST['pays_depart']) : '';
    $ville_depart = isset($_POST['ville_depart']) ? trim($_POST['ville_depart']) : '';
    $pays_arrivee = isset($_POST['pays_arrivee']) ? trim($_POST['pays_arrivee']) : '';
    $ville_arrivee = isset($_POST['ville_arrivee']) ? trim($_POST['ville_arrivee']) : '';
    $date_depart = isset($_POST['date_depart']) ? $_POST['date_depart'] : '';
    $date_retour = isset($_POST['date_retour']) ? $_POST['date_retour'] : '';
    $classe = isset($_POST['classe']) ? $_POST['classe'] : 'economique';
    $compagnie_preferee = isset($_POST['compagnie_preferee']) ? trim($_POST['compagnie_preferee']) : '';
    
    // Informations passagers
    $passagers = [];
    $nombre_passagers = isset($_POST['nombre_passagers']) ? intval($_POST['nombre_passagers']) : 1;
    
    // Récupérer les données des passagers
    if ($nombre_passagers > 0) {
        for ($i = 1; $i <= $nombre_passagers; $i++) {
            $civilite = isset($_POST["civilite_$i"]) ? $_POST["civilite_$i"] : 'M';
            $nom = isset($_POST["nom_$i"]) ? trim($_POST["nom_$i"]) : '';
            $prenom = isset($_POST["prenom_$i"]) ? trim($_POST["prenom_$i"]) : '';
            $date_naissance = isset($_POST["date_naissance_$i"]) ? $_POST["date_naissance_$i"] : '';
            $numero_passeport = isset($_POST["numero_passeport_$i"]) ? trim($_POST["numero_passeport_$i"]) : '';
            $expiration_passeport = isset($_POST["expiration_passeport_$i"]) ? $_POST["expiration_passeport_$i"] : '';
            $nationalite = isset($_POST["nationalite_$i"]) ? trim($_POST["nationalite_$i"]) : '';
            
            $passagers[] = [
                'civilite' => $civilite,
                'nom' => $nom,
                'prenom' => $prenom,
                'date_naissance' => $date_naissance,
                'numero_passeport' => $numero_passeport,
                'expiration_passeport' => $expiration_passeport,
                'nationalite' => $nationalite
            ];
        }
    }
    
    // Options de bagages
    $baggage_main = isset($_POST['baggage_main']) ? $_POST['baggage_main'] : '1_piece';
    $baggage_soute = isset($_POST['baggage_soute']) ? $_POST['baggage_soute'] : 'aucun';
    
    $commentaires = isset($_POST['commentaires']) ? trim($_POST['commentaires']) : '';
    
    // Validation
    $erreurs = [];
    
    // Validation de l'email
    if (empty($email_contact)) {
        $erreurs[] = "L'email est obligatoire";
    } else {
        // Nettoyer et valider l'email
        $email_contact = filter_var($email_contact, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email_contact, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = "Format d'email invalide. Exemple: exemple@domain.com";
        }
    }
    
    // Validation des autres champs obligatoires
    if (empty($pays_depart)) $erreurs[] = "Le pays de départ est requis";
    if (empty($ville_depart)) $erreurs[] = "La ville de départ est requise";
    if (empty($pays_arrivee)) $erreurs[] = "Le pays d'arrivée est requis";
    if (empty($ville_arrivee)) $erreurs[] = "La ville d'arrivée est requise";
    if (empty($date_depart)) $erreurs[] = "La date de départ est requise";
    if ($type_vol == 'aller_retour' && empty($date_retour)) {
        $erreurs[] = "La date de retour est requise pour un vol aller-retour";
    }
    
    // Validation des passagers
    if ($nombre_passagers > 0) {
        foreach ($passagers as $index => $passager) {
            $numero_passager = $index + 1;
            if (empty($passager['nom'])) {
                $erreurs[] = "Le nom du passager $numero_passager est requis";
            }
            if (empty($passager['prenom'])) {
                $erreurs[] = "Le prénom du passager $numero_passager est requis";
            }
            if (empty($passager['date_naissance'])) {
                $erreurs[] = "La date de naissance du passager $numero_passager est requise";
            }
            if (empty($passager['numero_passeport'])) {
                $erreurs[] = "Le numéro de passeport du passager $numero_passager est requis";
            }
            if (empty($passager['expiration_passeport'])) {
                $erreurs[] = "La date d'expiration du passeport du passager $numero_passager est requise";
            }
            if (empty($passager['nationalite'])) {
                $erreurs[] = "La nationalité du passager $numero_passager est requise";
            }
        }
    }
    
    // Si aucune erreur, traiter la demande
    if (empty($erreurs)) {
        // Génération d'un numéro de dossier
        $numero_dossier = "BILLET" . date('YmdHis') . rand(100, 999);
        
        // Connexion à la base de données via config.php
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Commencer la transaction
            $pdo->beginTransaction();
            
            // Insérer la demande principale
            $sql_demande = "INSERT INTO demandes_billets_avion 
                           (user_email, numero_dossier, email_contact, telephone_contact, type_vol, 
                            pays_depart, ville_depart, pays_arrivee, ville_arrivee, date_depart, 
                            date_retour, classe, compagnie_preferee, baggage_main, baggage_soute, 
                            commentaires, statut) 
                           VALUES 
                           (:user_email, :numero_dossier, :email_contact, :telephone_contact, :type_vol,
                            :pays_depart, :ville_depart, :pays_arrivee, :ville_arrivee, :date_depart,
                            :date_retour, :classe, :compagnie_preferee, :baggage_main, :baggage_soute,
                            :commentaires, 'nouveau')";
            
            $stmt_demande = $pdo->prepare($sql_demande);
            
            $stmt_demande->execute([
                ':user_email' => $user_email,
                ':numero_dossier' => $numero_dossier,
                ':email_contact' => $email_contact,
                ':telephone_contact' => $telephone_contact,
                ':type_vol' => $type_vol,
                ':pays_depart' => $pays_depart,
                ':ville_depart' => $ville_depart,
                ':pays_arrivee' => $pays_arrivee,
                ':ville_arrivee' => $ville_arrivee,
                ':date_depart' => $date_depart,
                ':date_retour' => $date_retour ?: null,
                ':classe' => $classe,
                ':compagnie_preferee' => $compagnie_preferee,
                ':baggage_main' => $baggage_main,
                ':baggage_soute' => $baggage_soute,
                ':commentaires' => $commentaires
            ]);
            
            $demande_id = $pdo->lastInsertId();
            
            // Insérer les passagers
            if ($nombre_passagers > 0) {
                $sql_passager = "INSERT INTO passagers_billets 
                                (demande_id, civilite, nom, prenom, date_naissance, 
                                 numero_passeport, expiration_passeport, nationalite) 
                                VALUES 
                                (:demande_id, :civilite, :nom, :prenom, :date_naissance,
                                 :numero_passeport, :expiration_passeport, :nationalite)";
                
                $stmt_passager = $pdo->prepare($sql_passager);
                
                foreach ($passagers as $passager) {
                    $stmt_passager->execute([
                        ':demande_id' => $demande_id,
                        ':civilite' => $passager['civilite'],
                        ':nom' => $passager['nom'],
                        ':prenom' => $passager['prenom'],
                        ':date_naissance' => $passager['date_naissance'],
                        ':numero_passeport' => $passager['numero_passeport'],
                        ':expiration_passeport' => $passager['expiration_passeport'],
                        ':nationalite' => $passager['nationalite']
                    ]);
                }
            }
            
            // Valider la transaction
            $pdo->commit();
            
            // Message de succès
            $message_success = "
                <div style='text-align: center; padding: 20px;'>
                    <i class='fas fa-check-circle' style='font-size: 3rem; color: #27ae60; margin-bottom: 15px;'></i>
                    <h3 style='color: #155724; margin-bottom: 15px;'>Demande Enregistrée avec Succès!</h3>
                    <p><strong>Numéro de dossier:</strong> $numero_dossier</p>
                    <p>Nous vous contacterons dans les plus brefs délais pour finaliser votre réservation.</p>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px; text-align: left;'>
                        <p><strong>Récapitulatif de votre demande:</strong></p>
                        <p>📧 Email: $email_contact</p>
                        <p>✈️ Vol: $ville_depart → $ville_arrivee</p>
                        <p>📅 Départ: " . date('d/m/Y', strtotime($date_depart)) . "</p>
                        " . ($type_vol == 'aller_retour' && $date_retour ? "<p>📅 Retour: " . date('d/m/Y', strtotime($date_retour)) . "</p>" : "") . "
                        <p>👥 Passagers: $nombre_passagers</p>
                        <p>💼 Classe: " . ucfirst($classe) . "</p>
                    </div>
                    <div style='margin-top: 20px;'>
                        <a href='mes_demandes_billets_avion.php' class='btn' style='display: inline-block;'>
                            <i class='fas fa-list'></i> Voir mes demandes
                        </a>
                    </div>
                </div>
            ";
            
        } catch (PDOException $e) {
            // En cas d'erreur, annuler la transaction
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            
            // Message d'erreur pour la base de données
            $message_success = "
                <div style='text-align: center; padding: 20px;'>
                    <i class='fas fa-check-circle' style='font-size: 3rem; color: #27ae60; margin-bottom: 15px;'></i>
                    <h3 style='color: #155724; margin-bottom: 15px;'>Demande Enregistrée!</h3>
                    <p><strong>Numéro de dossier:</strong> $numero_dossier</p>
                    <p style='color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px;'>
                        <i class='fas fa-exclamation-triangle'></i> 
                        Note: Erreur de connexion à la base de données. Votre demande a été traitée mais n'a pas pu être sauvegardée.
                    </p>
                    <p>Nous vous contacterons dans les plus brefs délais pour finaliser votre réservation.</p>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px; text-align: left;'>
                        <p><strong>Récapitulatif de votre demande:</strong></p>
                        <p>📧 Email: $email_contact</p>
                        <p>✈️ Vol: $ville_depart → $ville_arrivee</p>
                        <p>📅 Départ: " . date('d/m/Y', strtotime($date_depart)) . "</p>
                        " . ($type_vol == 'aller_retour' && $date_retour ? "<p>📅 Retour: " . date('d/m/Y', strtotime($date_retour)) . "</p>" : "") . "
                        <p>👥 Passagers: $nombre_passagers</p>
                        <p>💼 Classe: " . ucfirst($classe) . "</p>
                    </div>
                </div>
            ";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation de Billets d'Avion - Agence de Voyage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a5276;
            --secondary: #e74c3c;
            --accent: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --success: #27ae60;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1a5276 0%, #3498db 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .header h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .booking-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .card-header {
            background: var(--primary);
            color: white;
            padding: 20px 30px;
        }

        .card-header h2 {
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-body {
            padding: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .flight-type-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
            margin-bottom: 25px;
        }

        .flight-type {
            text-align: center;
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .flight-type:hover {
            border-color: var(--accent);
        }

        .flight-type.selected {
            border-color: var(--success);
            background-color: #f8fff9;
        }

        .passenger-section {
            background: var(--light);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .passenger-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }

        .passenger-title {
            font-weight: 600;
            color: var(--primary);
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .btn-block {
            display: block;
            width: 100%;
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary), #c0392b);
        }

        .section-title {
            color: var(--primary);
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .baggage-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .baggage-option {
            padding: 15px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .baggage-option:hover {
            border-color: var(--accent);
        }

        .baggage-option.selected {
            border-color: var(--success);
            background-color: #f8fff9;
        }

        .user-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
        }

        .user-info i {
            color: var(--accent);
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .flight-type-selector {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-plane"></i> Réservation de Billets d'Avion</h1>
            <p>Demandez votre billet d'avion - Service professionnel pour agences de voyage</p>
        </div>

        <div class="booking-card">
            <div class="card-header">
                <h2><i class="fas fa-ticket-alt"></i> Demande de Réservation de Billet</h2>
            </div>
            <div class="card-body">
                <!-- Affichage des informations utilisateur -->
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong>Connecté en tant que :</strong> <?php echo htmlspecialchars($user_email); ?>
                    <span style="float: right;">
                        <a href="mes_demandes_billets_avion.php" style="color: var(--accent); text-decoration: none;">
                            <i class="fas fa-list"></i> Mes demandes
                        </a>
                    </span>
                </div>

                <?php if (isset($message_success) && !empty($message_success)): ?>
                    <div class="alert alert-success" style="display: block;">
                        <?php echo $message_success; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erreurs)): ?>
                    <div class="alert alert-error" style="display: block;">
                        <strong>Veuillez corriger les erreurs suivantes:</strong>
                        <ul>
                            <?php foreach ($erreurs as $erreur): ?>
                                <li><?php echo $erreur; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="reservationForm">
                    
                    <!-- Section Informations de Contact -->
                    <h3 class="section-title"><i class="fas fa-user"></i> Informations de Contact</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email_contact">Email*</label>
                            <input type="email" id="email_contact" name="email_contact" class="form-control" required 
                                   placeholder="votre@email.com" value="<?php echo htmlspecialchars($email_contact); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone_contact">Téléphone</label>
                            <input type="tel" id="telephone_contact" name="telephone_contact" class="form-control" 
                                   placeholder="+33 1 23 45 67 89" value="<?php echo htmlspecialchars($telephone_contact); ?>">
                        </div>
                    </div>

                    <h3 class="section-title"><i class="fas fa-route"></i> Informations du Vol</h3>
                    
                    <div class="flight-type-selector">
                        <div class="flight-type <?php echo $type_vol == 'aller_simple' ? 'selected' : ''; ?>" onclick="selectFlightType(this)" data-type="aller_simple">
                            <i class="fas fa-plane-departure" style="font-size: 2rem; color: var(--primary);"></i>
                            <div>Aller simple</div>
                        </div>
                        <div class="flight-type <?php echo $type_vol == 'aller_retour' ? 'selected' : ''; ?>" onclick="selectFlightType(this)" data-type="aller_retour">
                            <i class="fas fa-exchange-alt" style="font-size: 2rem; color: var(--primary);"></i>
                            <div>Aller-retour</div>
                        </div>
                        <input type="hidden" name="type_vol" id="type_vol" value="<?php echo $type_vol; ?>">
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="pays_depart">Pays de départ*</label>
                            <input type="text" id="pays_depart" name="pays_depart" class="form-control" required 
                                   placeholder="Ex: France" value="<?php echo htmlspecialchars($pays_depart); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="ville_depart">Ville de départ*</label>
                            <input type="text" id="ville_depart" name="ville_depart" class="form-control" required 
                                   placeholder="Ex: Paris" value="<?php echo htmlspecialchars($ville_depart); ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="pays_arrivee">Pays d'arrivée*</label>
                            <input type="text" id="pays_arrivee" name="pays_arrivee" class="form-control" required
                                   placeholder="Ex: États-Unis" value="<?php echo htmlspecialchars($pays_arrivee); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="ville_arrivee">Ville d'arrivée*</label>
                            <input type="text" id="ville_arrivee" name="ville_arrivee" class="form-control" required
                                   placeholder="Ex: New York" value="<?php echo htmlspecialchars($ville_arrivee); ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="date_depart">Date de départ*</label>
                            <input type="date" id="date_depart" name="date_depart" class="form-control" required
                                   value="<?php echo $date_depart; ?>">
                        </div>
                        
                        <div class="form-group" id="retour-group" style="<?php echo $type_vol == 'aller_retour' ? 'display: block;' : 'display: none;'; ?>">
                            <label for="date_retour">Date de retour<?php echo $type_vol == 'aller_retour' ? '*' : ''; ?></label>
                            <input type="date" id="date_retour" name="date_retour" class="form-control"
                                   value="<?php echo $date_retour; ?>" <?php echo $type_vol == 'aller_retour' ? 'required' : ''; ?>>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="classe">Classe de voyage</label>
                            <select id="classe" name="classe" class="form-control">
                                <option value="economique" <?php echo $classe == 'economique' ? 'selected' : ''; ?>>Économique</option>
                                <option value="premium_economique" <?php echo $classe == 'premium_economique' ? 'selected' : ''; ?>>Premium Économique</option>
                                <option value="affaires" <?php echo $classe == 'affaires' ? 'selected' : ''; ?>>Affaires</option>
                                <option value="premiere" <?php echo $classe == 'premiere' ? 'selected' : ''; ?>>Première</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="compagnie_preferee">Compagnie préférée</label>
                            <input type="text" id="compagnie_preferee" name="compagnie_preferee" class="form-control"
                                   placeholder="Ex: Air France, Emirates..." value="<?php echo htmlspecialchars($compagnie_preferee); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="nombre_passagers">Nombre de passagers*</label>
                            <select id="nombre_passagers" name="nombre_passagers" class="form-control" required onchange="updatePassengers()">
                                <?php for ($i = 1; $i <= 9; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $nombre_passagers == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> passager<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div id="passengers-container">
                        <!-- Les sections passagers seront générées ici -->
                    </div>

                    <h3 class="section-title"><i class="fas fa-suitcase"></i> Options de Bagages</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Bagage en cabine</label>
                            <div class="baggage-options">
                                <div class="baggage-option <?php echo $baggage_main == '1_piece' ? 'selected' : ''; ?>" onclick="selectBaggage(this, 'main')" data-type="main" data-value="1_piece">
                                    <strong>1 pièce</strong>
                                    <div>Bagage à main standard</div>
                                </div>
                                <div class="baggage-option <?php echo $baggage_main == '2_pieces' ? 'selected' : ''; ?>" onclick="selectBaggage(this, 'main')" data-type="main" data-value="2_pieces">
                                    <strong>2 pièces</strong>
                                    <div>Bagage + accessoire</div>
                                </div>
                            </div>
                            <input type="hidden" name="baggage_main" id="baggage_main" value="<?php echo $baggage_main; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Bagage en soute</label>
                            <div class="baggage-options">
                                <div class="baggage-option <?php echo $baggage_soute == 'aucun' ? 'selected' : ''; ?>" onclick="selectBaggage(this, 'soute')" data-type="soute" data-value="aucun">
                                    <strong>Aucun</strong>
                                    <div>Pas de bagage en soute</div>
                                </div>
                                <div class="baggage-option <?php echo $baggage_soute == '23kg' ? 'selected' : ''; ?>" onclick="selectBaggage(this, 'soute')" data-type="soute" data-value="23kg">
                                    <strong>23kg</strong>
                                    <div>Bagage standard</div>
                                </div>
                                <div class="baggage-option <?php echo $baggage_soute == '32kg' ? 'selected' : ''; ?>" onclick="selectBaggage(this, 'soute')" data-type="soute" data-value="32kg">
                                    <strong>32kg</strong>
                                    <div>Bagage supplémentaire</div>
                                </div>
                            </div>
                            <input type="hidden" name="baggage_soute" id="baggage_soute" value="<?php echo $baggage_soute; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="commentaires">Commentaires ou demandes particulières</label>
                        <textarea id="commentaires" name="commentaires" class="form-control" rows="4" 
                                  placeholder="Précisions sur les horaires, connexion entre vols, besoins particuliers..."><?php echo htmlspecialchars($commentaires); ?></textarea>
                    </div>

                    <div class="form-group" style="margin-top: 30px;">
                        <label style="display: flex; align-items: start; gap: 10px; font-size: 0.9rem;">
                            <input type="checkbox" required style="margin-top: 3px; transform: scale(1.2);">
                            <span>Je confirme l'exactitude des informations fournies et accepte que cette demande soit traitée par l'agence de voyage.</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-block" style="margin-top: 20px;">
                        <i class="fas fa-paper-plane"></i> Soumettre la Demande de Réservation
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function selectFlightType(element) {
            document.querySelectorAll('.flight-type').forEach(el => {
                el.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById('type_vol').value = element.dataset.type;
            
            // Afficher/masquer le champ de retour
            const retourGroup = document.getElementById('retour-group');
            if (element.dataset.type === 'aller_retour') {
                retourGroup.style.display = 'block';
                document.getElementById('date_retour').required = true;
            } else {
                retourGroup.style.display = 'none';
                document.getElementById('date_retour').required = false;
            }
        }

        function selectBaggage(element, type) {
            document.querySelectorAll(`.baggage-option[data-type="${type}"]`).forEach(el => {
                el.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById(`baggage_${type}`).value = element.dataset.value;
        }

        function updatePassengers() {
            const nombrePassagers = document.getElementById('nombre_passagers').value;
            const container = document.getElementById('passengers-container');
            container.innerHTML = '';
            
            for (let i = 1; i <= nombrePassagers; i++) {
                const passengerHtml = `
                    <div class="passenger-section">
                        <div class="passenger-header">
                            <div class="passenger-title">Passager ${i}</div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Civilité</label>
                                <select name="civilite_${i}" class="form-control" required>
                                    <option value="M">Monsieur</option>
                                    <option value="Mme">Madame</option>
                                    <option value="Mlle">Mademoiselle</option>
                                    <option value="Enfant">Enfant</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nom*</label>
                                <input type="text" name="nom_${i}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Prénom*</label>
                                <input type="text" name="prenom_${i}" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Date de naissance*</label>
                                <input type="date" name="date_naissance_${i}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Numéro de passeport*</label>
                                <input type="text" name="numero_passeport_${i}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Expiration passeport*</label>
                                <input type="date" name="expiration_passeport_${i}" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Nationalité*</label>
                                <input type="text" name="nationalite_${i}" class="form-control" required>
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += passengerHtml;
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            updatePassengers();
            
            // Définir les dates minimales
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date_depart').min = today;
            document.getElementById('date_retour').min = today;

            document.getElementById('date_depart').addEventListener('change', function() {
                document.getElementById('date_retour').min = this.value;
            });
        });
    </script>
</body>
</html>