<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header('Location: login.php');
    exit();
}

$user_email = $_SESSION['user_email'];

// Configuration de la base de données
$db_host = 'localhost';
$db_name = 'babylone_service';
$db_user = 'root';
$db_pass = '';

// Initialiser les variables avec des valeurs par défaut
$email_contact = $telephone_contact = $pays_destination = $ville_destination = $nom_hotel = $date_arrivee = $date_depart = $adresse_hotel = $commentaires = '';
$type_hebergement = 'hotel_3_etoiles';
$categorie_chambre = 'standard';
$nombre_adultes = 2;
$nombre_enfants = 0;
$nombre_chambres = 1;
$petit_dejeuner = 'non';
$options_specifiques = [];
$voyageurs_data = [];

$message_success = '';
$erreurs = [];

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Informations de contact
    $email_contact = isset($_POST['email_contact']) ? trim($_POST['email_contact']) : '';
    $telephone_contact = isset($_POST['telephone_contact']) ? trim($_POST['telephone_contact']) : '';
    
    // Informations de séjour
    $pays_destination = isset($_POST['pays_destination']) ? trim($_POST['pays_destination']) : '';
    $ville_destination = isset($_POST['ville_destination']) ? trim($_POST['ville_destination']) : '';
    $nom_hotel = isset($_POST['nom_hotel']) ? trim($_POST['nom_hotel']) : '';
    $adresse_hotel = isset($_POST['adresse_hotel']) ? trim($_POST['adresse_hotel']) : '';
    $date_arrivee = isset($_POST['date_arrivee']) ? $_POST['date_arrivee'] : '';
    $date_depart = isset($_POST['date_depart']) ? $_POST['date_depart'] : '';
    $type_hebergement = isset($_POST['type_hebergement']) ? $_POST['type_hebergement'] : 'hotel_3_etoiles';
    $categorie_chambre = isset($_POST['categorie_chambre']) ? $_POST['categorie_chambre'] : 'standard';
    
    // Informations des voyageurs
    $nombre_adultes = isset($_POST['nombre_adultes']) ? intval($_POST['nombre_adultes']) : 2;
    $nombre_enfants = isset($_POST['nombre_enfants']) ? intval($_POST['nombre_enfants']) : 0;
    $nombre_chambres = isset($_POST['nombre_chambres']) ? intval($_POST['nombre_chambres']) : 1;
    $ages_enfants = isset($_POST['ages_enfants']) ? trim($_POST['ages_enfants']) : '';
    
    // Options
    $petit_dejeuner = isset($_POST['petit_dejeuner']) ? $_POST['petit_dejeuner'] : 'non';
    $options_specifiques = isset($_POST['options_specifiques']) ? $_POST['options_specifiques'] : [];
    
    // Récupérer les données des voyageurs
    $voyageurs = [];
    $total_voyageurs = $nombre_adultes + $nombre_enfants;
    
    for ($i = 1; $i <= $total_voyageurs; $i++) {
        $civilite = isset($_POST["civilite_$i"]) ? $_POST["civilite_$i"] : 'M';
        $nom = isset($_POST["nom_$i"]) ? trim($_POST["nom_$i"]) : '';
        $prenom = isset($_POST["prenom_$i"]) ? trim($_POST["prenom_$i"]) : '';
        $date_naissance = isset($_POST["date_naissance_$i"]) ? $_POST["date_naissance_$i"] : '';
        $numero_passeport = isset($_POST["numero_passeport_$i"]) ? trim($_POST["numero_passeport_$i"]) : '';
        $expiration_passeport = isset($_POST["expiration_passeport_$i"]) ? $_POST["expiration_passeport_$i"] : '';
        $nationalite = isset($_POST["nationalite_$i"]) ? trim($_POST["nationalite_$i"]) : '';
        
        // Stocker les données pour pré-remplir le formulaire en cas d'erreur
        $voyageurs_data[$i] = [
            'civilite' => $civilite,
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => $date_naissance,
            'numero_passeport' => $numero_passeport,
            'expiration_passeport' => $expiration_passeport,
            'nationalite' => $nationalite
        ];
        
        $voyageurs[] = [
            'civilite' => $civilite,
            'nom' => $nom,
            'prenom' => $prenom,
            'date_naissance' => $date_naissance,
            'numero_passeport' => $numero_passeport,
            'expiration_passeport' => $expiration_passeport,
            'nationalite' => $nationalite
        ];
    }
    
    $commentaires = isset($_POST['commentaires']) ? trim($_POST['commentaires']) : '';
    
    // Validation
    if (empty($email_contact)) {
        $erreurs[] = "L'email est obligatoire";
    } elseif (!filter_var($email_contact, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "Format d'email invalide";
    }
    
    if (empty($pays_destination)) $erreurs[] = "Le pays de destination est requis";
    if (empty($ville_destination)) $erreurs[] = "La ville de destination est requise";
    if (empty($nom_hotel)) $erreurs[] = "Le nom de l'hôtel est requis";
    if (empty($date_arrivee)) $erreurs[] = "La date d'arrivée est requise";
    if (empty($date_depart)) $erreurs[] = "La date de départ est requise";
    
    // Validation des dates
    if (!empty($date_arrivee) && !empty($date_depart)) {
        if (strtotime($date_depart) <= strtotime($date_arrivee)) {
            $erreurs[] = "La date de départ doit être postérieure à la date d'arrivée";
        }
    }
    
    // Validation des voyageurs
    foreach ($voyageurs as $index => $voyageur) {
        $numero_voyageur = $index + 1;
        if (empty($voyageur['nom'])) $erreurs[] = "Le nom du voyageur $numero_voyageur est requis";
        if (empty($voyageur['prenom'])) $erreurs[] = "Le prénom du voyageur $numero_voyageur est requis";
        if (empty($voyageur['date_naissance'])) $erreurs[] = "La date de naissance du voyageur $numero_voyageur est requise";
        if (empty($voyageur['numero_passeport'])) $erreurs[] = "Le numéro de passeport du voyageur $numero_voyageur est requis";
        if (empty($voyageur['nationalite'])) $erreurs[] = "La nationalité du voyageur $numero_voyageur est requise";
    }
    
    // Si aucune erreur, traiter la demande
    if (empty($erreurs)) {
        // Génération d'un numéro de dossier
        $numero_dossier = "HOTEL" . date('YmdHis') . rand(100, 999);
        
        // Créer le nom complet (premier voyageur)
        $nom_complet = $voyageurs[0]['prenom'] . ' ' . $voyageurs[0]['nom'];
        $nationalite = $voyageurs[0]['nationalite'];
        
        // Calcul du nombre de nuits
        $nuits = (strtotime($date_depart) - strtotime($date_arrivee)) / (60 * 60 * 24);
        
        // Calcul du prix estimé
        $prix_base_nuit = 80;
        $prix_estime = $nuits * $nombre_chambres * $prix_base_nuit;
        
        // Ajustements selon le type d'hébergement
        if ($type_hebergement == 'hotel_4_etoiles') $prix_estime *= 1.5;
        if ($type_hebergement == 'hotel_5_etoiles') $prix_estime *= 2.0;
        if ($type_hebergement == 'appartement') $prix_estime *= 1.2;
        if ($type_hebergement == 'villa') $prix_estime *= 2.5;
        
        // Ajustements selon la catégorie de chambre
        if ($categorie_chambre == 'superieure') $prix_estime *= 1.3;
        if ($categorie_chambre == 'deluxe') $prix_estime *= 1.6;
        if ($categorie_chambre == 'suite') $prix_estime *= 2.0;
        
        // Ajouter le petit déjeuner
        if ($petit_dejeuner == 'oui') {
            $prix_estime += ($nuits * ($nombre_adultes + $nombre_enfants) * 15);
        }
        
        // Connexion à la base de données
        try {
            $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Vérifier si la table existe et créer les colonnes manquantes
            $check_table_sql = "SHOW TABLES LIKE 'demandes_reservation'";
            $table_exists = $pdo->query($check_table_sql)->rowCount() > 0;
            
            if ($table_exists) {
                // Vérifier et ajouter les colonnes manquantes
                $columns_to_add = [
                    'user_email' => "ALTER TABLE demandes_reservation ADD COLUMN user_email VARCHAR(255) NOT NULL AFTER id",
                    'numero_dossier' => "ALTER TABLE demandes_reservation ADD COLUMN numero_dossier VARCHAR(50) NOT NULL AFTER user_email",
                    'civilite' => "ALTER TABLE demandes_reservation ADD COLUMN civilite VARCHAR(10) AFTER numero_dossier",
                    'nom' => "ALTER TABLE demandes_reservation ADD COLUMN nom VARCHAR(100) NOT NULL AFTER civilite",
                    'prenom' => "ALTER TABLE demandes_reservation ADD COLUMN prenom VARCHAR(100) NOT NULL AFTER nom",
                    'email' => "ALTER TABLE demandes_reservation ADD COLUMN email VARCHAR(255) NOT NULL AFTER prenom",
                    'telephone' => "ALTER TABLE demandes_reservation ADD COLUMN telephone VARCHAR(50) AFTER email",
                    'nationalite' => "ALTER TABLE demandes_reservation ADD COLUMN nationalite VARCHAR(100) AFTER telephone",
                    'numero_passeport' => "ALTER TABLE demandes_reservation ADD COLUMN numero_passeport VARCHAR(100) AFTER nationalite",
                    'date_expiration_passeport' => "ALTER TABLE demandes_reservation ADD COLUMN date_expiration_passeport DATE AFTER numero_passeport",
                    'pays_destination' => "ALTER TABLE demandes_reservation ADD COLUMN pays_destination VARCHAR(100) NOT NULL AFTER date_expiration_passeport",
                    'ville_destination' => "ALTER TABLE demandes_reservation ADD COLUMN ville_destination VARCHAR(100) NOT NULL AFTER pays_destination",
                    'nom_hotel' => "ALTER TABLE demandes_reservation ADD COLUMN nom_hotel VARCHAR(255) NOT NULL AFTER ville_destination",
                    'adresse_hotel' => "ALTER TABLE demandes_reservation ADD COLUMN adresse_hotel TEXT AFTER nom_hotel",
                    'date_arrivee' => "ALTER TABLE demandes_reservation ADD COLUMN date_arrivee DATE NOT NULL AFTER adresse_hotel",
                    'date_depart' => "ALTER TABLE demandes_reservation ADD COLUMN date_depart DATE NOT NULL AFTER date_arrivee",
                    'nombre_nuits' => "ALTER TABLE demandes_reservation ADD COLUMN nombre_nuits INT(11) AFTER date_depart",
                    'type_hebergement' => "ALTER TABLE demandes_reservation ADD COLUMN type_hebergement VARCHAR(50) AFTER nombre_nuits",
                    'categorie_chambre' => "ALTER TABLE demandes_reservation ADD COLUMN categorie_chambre VARCHAR(50) AFTER type_hebergement",
                    'nombre_chambres' => "ALTER TABLE demandes_reservation ADD COLUMN nombre_chambres INT(11) DEFAULT 1 AFTER categorie_chambre",
                    'nombre_adultes' => "ALTER TABLE demandes_reservation ADD COLUMN nombre_adultes INT(11) DEFAULT 1 AFTER nombre_chambres",
                    'nombre_enfants' => "ALTER TABLE demandes_reservation ADD COLUMN nombre_enfants INT(11) DEFAULT 0 AFTER nombre_adultes",
                    'ages_enfants' => "ALTER TABLE demandes_reservation ADD COLUMN ages_enfants VARCHAR(255) AFTER nombre_enfants",
                    'petit_dejeuner' => "ALTER TABLE demandes_reservation ADD COLUMN petit_dejeuner ENUM('oui', 'non') DEFAULT 'non' AFTER ages_enfants",
                    'options_specifiques' => "ALTER TABLE demandes_reservation ADD COLUMN options_specifiques TEXT AFTER petit_dejeuner",
                    'commentaires' => "ALTER TABLE demandes_reservation ADD COLUMN commentaires TEXT AFTER options_specifiques",
                    'prix_estime' => "ALTER TABLE demandes_reservation ADD COLUMN prix_estime DECIMAL(10,2) AFTER commentaires",
                    'status' => "ALTER TABLE demandes_reservation ADD COLUMN status ENUM('en_attente', 'en_traitement', 'confirmee', 'annulee') DEFAULT 'en_attente' AFTER prix_estime",
                    'date_creation' => "ALTER TABLE demandes_reservation ADD COLUMN date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER status",
                    'date_modification' => "ALTER TABLE demandes_reservation ADD COLUMN date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER date_creation"
                ];
                
                foreach ($columns_to_add as $column => $sql) {
                    try {
                        $check_column_sql = "SHOW COLUMNS FROM demandes_reservation LIKE '$column'";
                        $column_exists = $pdo->query($check_column_sql)->rowCount() > 0;
                        
                        if (!$column_exists) {
                            $pdo->exec($sql);
                        }
                    } catch (PDOException $e) {
                        // Ignorer les erreurs si la colonne existe déjà
                    }
                }
            } else {
                // Créer la table si elle n'existe pas
                $sql_create_table = "CREATE TABLE IF NOT EXISTS demandes_reservation (
                    id INT(11) AUTO_INCREMENT PRIMARY KEY,
                    user_email VARCHAR(255) NOT NULL,
                    numero_dossier VARCHAR(50) NOT NULL UNIQUE,
                    civilite VARCHAR(10),
                    nom VARCHAR(100) NOT NULL,
                    prenom VARCHAR(100) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    telephone VARCHAR(50),
                    nationalite VARCHAR(100),
                    numero_passeport VARCHAR(100),
                    date_expiration_passeport DATE,
                    pays_destination VARCHAR(100) NOT NULL,
                    ville_destination VARCHAR(100) NOT NULL,
                    nom_hotel VARCHAR(255) NOT NULL,
                    adresse_hotel TEXT,
                    date_arrivee DATE NOT NULL,
                    date_depart DATE NOT NULL,
                    nombre_nuits INT(11),
                    type_hebergement VARCHAR(50),
                    categorie_chambre VARCHAR(50),
                    nombre_chambres INT(11) DEFAULT 1,
                    nombre_adultes INT(11) DEFAULT 1,
                    nombre_enfants INT(11) DEFAULT 0,
                    ages_enfants VARCHAR(255),
                    petit_dejeuner ENUM('oui', 'non') DEFAULT 'non',
                    options_specifiques TEXT,
                    commentaires TEXT,
                    prix_estime DECIMAL(10,2),
                    status ENUM('en_attente', 'en_traitement', 'confirmee', 'annulee') DEFAULT 'en_attente',
                    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
                
                $pdo->exec($sql_create_table);
            }
            
            // Insérer la demande principale
            $sql_demande = "INSERT INTO demandes_reservation 
                           (user_email, numero_dossier, civilite, nom, prenom, email, telephone, nationalite, 
                            numero_passeport, date_expiration_passeport, pays_destination, ville_destination, 
                            nom_hotel, adresse_hotel, date_arrivee, date_depart, nombre_nuits, type_hebergement, 
                            categorie_chambre, nombre_chambres, nombre_adultes, nombre_enfants, ages_enfants, 
                            petit_dejeuner, options_specifiques, commentaires, prix_estime) 
                           VALUES 
                           (:user_email, :numero_dossier, :civilite, :nom, :prenom, :email, :telephone, :nationalite,
                            :numero_passeport, :date_expiration_passeport, :pays_destination, :ville_destination,
                            :nom_hotel, :adresse_hotel, :date_arrivee, :date_depart, :nombre_nuits, :type_hebergement,
                            :categorie_chambre, :nombre_chambres, :nombre_adultes, :nombre_enfants, :ages_enfants,
                            :petit_dejeuner, :options_specifiques, :commentaires, :prix_estime)";
            
            $stmt_demande = $pdo->prepare($sql_demande);
            
            $stmt_demande->execute([
                ':user_email' => $user_email,
                ':numero_dossier' => $numero_dossier,
                ':civilite' => $voyageurs[0]['civilite'],
                ':nom' => $voyageurs[0]['nom'],
                ':prenom' => $voyageurs[0]['prenom'],
                ':email' => $email_contact,
                ':telephone' => $telephone_contact,
                ':nationalite' => $nationalite,
                ':numero_passeport' => $voyageurs[0]['numero_passeport'],
                ':date_expiration_passeport' => $voyageurs[0]['expiration_passeport'],
                ':pays_destination' => $pays_destination,
                ':ville_destination' => $ville_destination,
                ':nom_hotel' => $nom_hotel,
                ':adresse_hotel' => $adresse_hotel,
                ':date_arrivee' => $date_arrivee,
                ':date_depart' => $date_depart,
                ':nombre_nuits' => $nuits,
                ':type_hebergement' => $type_hebergement,
                ':categorie_chambre' => $categorie_chambre,
                ':nombre_chambres' => $nombre_chambres,
                ':nombre_adultes' => $nombre_adultes,
                ':nombre_enfants' => $nombre_enfants,
                ':ages_enfants' => $ages_enfants,
                ':petit_dejeuner' => $petit_dejeuner,
                ':options_specifiques' => implode(', ', $options_specifiques),
                ':commentaires' => $commentaires,
                ':prix_estime' => $prix_estime
            ]);
            
            $message_success = "
                <div style='text-align: center; padding: 20px; background: #d4edda; color: #155724; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;'>
                    <i class='fas fa-check-circle' style='font-size: 3rem; color: #28a745; margin-bottom: 15px;'></i>
                    <h3 style='color: #155724; margin-bottom: 15px;'>Demande de Réservation Enregistrée avec Succès!</h3>
                    <p><strong>Numéro de dossier:</strong> $numero_dossier</p>
                    <p>Nous vous contacterons dans les plus brefs délais pour finaliser votre réservation.</p>
                    <div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px; text-align: left;'>
                        <p><strong>Récapitulatif de votre demande:</strong></p>
                        <p>🏨 Hôtel: $nom_hotel</p>
                        <p>📍 Destination: $ville_destination, $pays_destination</p>
                        <p>📅 Arrivée: " . date('d/m/Y', strtotime($date_arrivee)) . "</p>
                        <p>📅 Départ: " . date('d/m/Y', strtotime($date_depart)) . "</p>
                        <p>🌙 Nombre de nuits: $nuits</p>
                        <p>👥 Voyageurs: $nombre_adultes adulte(s) + $nombre_enfants enfant(s)</p>
                        <p>🛏️ Chambres: $nombre_chambres (" . ucfirst(str_replace('_', ' ', $type_hebergement)) . " - " . ucfirst($categorie_chambre) . ")</p>
                        <p>🍳 Petit déjeuner: " . ($petit_dejeuner == 'oui' ? 'Inclus' : 'Non inclus') . "</p>
                        <p>💰 Prix estimé: " . number_format($prix_estime, 2, ',', ' ') . " €</p>
                    </div>
                    <div style='margin-top: 20px;'>
                        <a href='client/demandes.php' class='btn' style='display: inline-block; background: #28a745; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;'>
                            <i class='fas fa-list'></i> Voir mes demandes
                        </a>
                    </div>
                </div>
            ";
            
        } catch (PDOException $e) {
            $erreurs[] = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réservation d'Hôtel - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00966E;
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
            background: linear-gradient(135deg, #00966E 0%, #00664d 100%);
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

        .section-title {
            color: var(--primary);
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
        }

        .voyageur-section {
            background: var(--light);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .voyageur-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 15px;
        }

        .voyageur-title {
            font-weight: 600;
            color: var(--primary);
        }

        .user-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--accent);
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .option-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option-item:hover {
            border-color: var(--accent);
        }

        .option-item.selected {
            border-color: var(--success);
            background-color: #f8fff9;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-hotel"></i> Réservation d'Hôtel</h1>
            <p>Réservez votre hôtel - Service professionnel Babylone Service</p>
        </div>

        <div class="booking-card">
            <div class="card-header">
                <h2><i class="fas fa-bed"></i> Demande de Réservation d'Hôtel</h2>
            </div>
            <div class="card-body">
                <!-- Affichage des informations utilisateur -->
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <strong>Connecté en tant que :</strong> <?php echo htmlspecialchars($user_email); ?>
                    <span style="float: right;">
                        <a href="client/mes_demandes.php" style="color: var(--accent); text-decoration: none;">
                            <i class="fas fa-list"></i> Mes demandes
                        </a>
                    </span>
                </div>

                <?php if (isset($message_success) && !empty($message_success)): ?>
                    <?php echo $message_success; ?>
                <?php endif; ?>

                <?php if (!empty($erreurs)): ?>
                    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
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
                            <label for="telephone_contact">Téléphone*</label>
                            <input type="tel" id="telephone_contact" name="telephone_contact" class="form-control" required 
                                   placeholder="+33 1 23 45 67 89" value="<?php echo htmlspecialchars($telephone_contact); ?>">
                        </div>
                    </div>

                    <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> Informations du Séjour</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="pays_destination">Pays de destination*</label>
                            <input type="text" id="pays_destination" name="pays_destination" class="form-control" required 
                                   placeholder="Ex: France" value="<?php echo htmlspecialchars($pays_destination); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="ville_destination">Ville de destination*</label>
                            <input type="text" id="ville_destination" name="ville_destination" class="form-control" required 
                                   placeholder="Ex: Paris" value="<?php echo htmlspecialchars($ville_destination); ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom_hotel">Nom de l'hôtel*</label>
                            <input type="text" id="nom_hotel" name="nom_hotel" class="form-control" required 
                                   placeholder="Ex: Hôtel Plaza Athénée" value="<?php echo htmlspecialchars($nom_hotel); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="adresse_hotel">Adresse de l'hôtel</label>
                            <input type="text" id="adresse_hotel" name="adresse_hotel" class="form-control"
                                   placeholder="Ex: 25 Avenue Montaigne, 75008 Paris" value="<?php echo htmlspecialchars($adresse_hotel); ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="date_arrivee">Date d'arrivée*</label>
                            <input type="date" id="date_arrivee" name="date_arrivee" class="form-control" required
                                   value="<?php echo $date_arrivee; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_depart">Date de départ*</label>
                            <input type="date" id="date_depart" name="date_depart" class="form-control" required
                                   value="<?php echo $date_depart; ?>">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="type_hebergement">Type d'hébergement</label>
                            <select id="type_hebergement" name="type_hebergement" class="form-control">
                                <option value="hotel_3_etoiles" <?php echo $type_hebergement == 'hotel_3_etoiles' ? 'selected' : ''; ?>>Hôtel 3 étoiles</option>
                                <option value="hotel_4_etoiles" <?php echo $type_hebergement == 'hotel_4_etoiles' ? 'selected' : ''; ?>>Hôtel 4 étoiles</option>
                                <option value="hotel_5_etoiles" <?php echo $type_hebergement == 'hotel_5_etoiles' ? 'selected' : ''; ?>>Hôtel 5 étoiles</option>
                                <option value="appartement" <?php echo $type_hebergement == 'appartement' ? 'selected' : ''; ?>>Appartement</option>
                                <option value="villa" <?php echo $type_hebergement == 'villa' ? 'selected' : ''; ?>>Villa</option>
                                <option value="auberge" <?php echo $type_hebergement == 'auberge' ? 'selected' : ''; ?>>Auberge</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="categorie_chambre">Catégorie de chambre</label>
                            <select id="categorie_chambre" name="categorie_chambre" class="form-control">
                                <option value="standard" <?php echo $categorie_chambre == 'standard' ? 'selected' : ''; ?>>Standard</option>
                                <option value="superieure" <?php echo $categorie_chambre == 'superieure' ? 'selected' : ''; ?>>Supérieure</option>
                                <option value="deluxe" <?php echo $categorie_chambre == 'deluxe' ? 'selected' : ''; ?>>Deluxe</option>
                                <option value="suite" <?php echo $categorie_chambre == 'suite' ? 'selected' : ''; ?>>Suite</option>
                                <option value="familiale" <?php echo $categorie_chambre == 'familiale' ? 'selected' : ''; ?>>Familiale</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="nombre_chambres">Nombre de chambres*</label>
                            <select id="nombre_chambres" name="nombre_chambres" class="form-control" required>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $nombre_chambres == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> chambre<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <h3 class="section-title"><i class="fas fa-users"></i> Informations des Voyageurs</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre_adultes">Nombre d'adultes*</label>
                            <select id="nombre_adultes" name="nombre_adultes" class="form-control" required onchange="updateVoyageurs()">
                                <?php for ($i = 1; $i <= 8; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $nombre_adultes == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> adulte<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="nombre_enfants">Nombre d'enfants</label>
                            <select id="nombre_enfants" name="nombre_enfants" class="form-control" onchange="updateVoyageurs()">
                                <?php for ($i = 0; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $nombre_enfants == $i ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> enfant<?php echo $i > 1 ? 's' : ''; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="ages_enfants">Âges des enfants</label>
                            <input type="text" id="ages_enfants" name="ages_enfants" class="form-control" 
                                   placeholder="Ex: 3 ans, 7 ans" value="<?php echo htmlspecialchars($ages_enfants); ?>">
                        </div>
                    </div>

                    <div id="voyageurs-container">
                        <!-- Les sections voyageurs seront générées ici -->
                    </div>

                    <h3 class="section-title"><i class="fas fa-concierge-bell"></i> Options et Services</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Petit déjeuner inclus</label>
                            <div class="options-grid">
                                <div class="option-item <?php echo $petit_dejeuner == 'oui' ? 'selected' : ''; ?>" onclick="selectOption(this, 'petit_dejeuner')" data-value="oui">
                                    <i class="fas fa-check" style="color: var(--success);"></i>
                                    <div>
                                        <strong>Oui</strong>
                                        <div>Petit déjeuner inclus</div>
                                    </div>
                                </div>
                                <div class="option-item <?php echo $petit_dejeuner == 'non' ? 'selected' : ''; ?>" onclick="selectOption(this, 'petit_dejeuner')" data-value="non">
                                    <i class="fas fa-times" style="color: var(--secondary);"></i>
                                    <div>
                                        <strong>Non</strong>
                                        <div>Sans petit déjeuner</div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="petit_dejeuner" id="petit_dejeuner" value="<?php echo $petit_dejeuner; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Options spécifiques</label>
                        <div class="options-grid">
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 2px solid #e1e8ed; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="options_specifiques[]" value="vue_mer" style="transform: scale(1.2);" <?php echo in_array('vue_mer', $options_specifiques) ? 'checked' : ''; ?>>
                                <div>
                                    <strong>Vue mer</strong>
                                    <div>Chambre avec vue sur la mer</div>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 2px solid #e1e8ed; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="options_specifiques[]" value="parking" style="transform: scale(1.2);" <?php echo in_array('parking', $options_specifiques) ? 'checked' : ''; ?>>
                                <div>
                                    <strong>Parking</strong>
                                    <div>Place de parking incluse</div>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 2px solid #e1e8ed; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="options_specifiques[]" value="animaux" style="transform: scale(1.2);" <?php echo in_array('animaux', $options_specifiques) ? 'checked' : ''; ?>>
                                <div>
                                    <strong>Animaux</strong>
                                    <div>Animaux acceptés</div>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 10px; padding: 10px; border: 2px solid #e1e8ed; border-radius: 8px; cursor: pointer;">
                                <input type="checkbox" name="options_specifiques[]" value="spa" style="transform: scale(1.2);" <?php echo in_array('spa', $options_specifiques) ? 'checked' : ''; ?>>
                                <div>
                                    <strong>Spa</strong>
                                    <div>Accès au spa inclus</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="commentaires">Commentaires ou demandes particulières</label>
                        <textarea id="commentaires" name="commentaires" class="form-control" rows="4" 
                                  placeholder="Précisions sur les horaires, besoins particuliers, demandes spéciales..."><?php echo htmlspecialchars($commentaires); ?></textarea>
                    </div>

                    <div class="form-group" style="margin-top: 30px;">
                        <label style="display: flex; align-items: start; gap: 10px; font-size: 0.9rem;">
                            <input type="checkbox" required style="margin-top: 3px; transform: scale(1.2);">
                            <span>Je confirme l'exactitude des informations fournies et accepte que cette demande soit traitée par Babylone Service.</span>
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
        function selectOption(element, type) {
            document.querySelectorAll(`.option-item[data-value]`).forEach(el => {
                if (el.parentElement === element.parentElement) {
                    el.classList.remove('selected');
                }
            });
            element.classList.add('selected');
            document.getElementById(type).value = element.dataset.value;
        }

        function updateVoyageurs() {
            const nombreAdultes = parseInt(document.getElementById('nombre_adultes').value);
            const nombreEnfants = parseInt(document.getElementById('nombre_enfants').value);
            const totalVoyageurs = nombreAdultes + nombreEnfants;
            const container = document.getElementById('voyageurs-container');
            container.innerHTML = '';
            
            for (let i = 1; i <= totalVoyageurs; i++) {
                const isEnfant = i > nombreAdultes;
                const typeVoyageur = isEnfant ? 'enfant' : 'adulte';
                
                // Récupérer les données existantes pour pré-remplir
                const voyageurData = <?php echo json_encode($voyageurs_data); ?>;
                const data = voyageurData[i] || {};
                
                const voyageurHtml = `
                    <div class="voyageur-section">
                        <div class="voyageur-header">
                            <div class="voyageur-title">Voyageur ${i} (${typeVoyageur})</div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Civilité</label>
                                <select name="civilite_${i}" class="form-control" required>
                                    <option value="M" ${data.civilite === 'M' ? 'selected' : ''}>Monsieur</option>
                                    <option value="Mme" ${data.civilite === 'Mme' ? 'selected' : ''}>Madame</option>
                                    <option value="Mlle" ${data.civilite === 'Mlle' ? 'selected' : ''}>Mademoiselle</option>
                                    ${isEnfant ? '<option value="Enfant" ${data.civilite === "Enfant" ? "selected" : ""}>Enfant</option>' : ''}
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nom*</label>
                                <input type="text" name="nom_${i}" class="form-control" required value="${data.nom || ''}">
                            </div>
                            <div class="form-group">
                                <label>Prénom*</label>
                                <input type="text" name="prenom_${i}" class="form-control" required value="${data.prenom || ''}">
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Date de naissance*</label>
                                <input type="date" name="date_naissance_${i}" class="form-control" required value="${data.date_naissance || ''}">
                            </div>
                            <div class="form-group">
                                <label>Numéro de passeport*</label>
                                <input type="text" name="numero_passeport_${i}" class="form-control" required value="${data.numero_passeport || ''}">
                            </div>
                            <div class="form-group">
                                <label>Expiration passeport*</label>
                                <input type="date" name="expiration_passeport_${i}" class="form-control" required value="${data.expiration_passeport || ''}">
                            </div>
                            <div class="form-group">
                                <label>Nationalité*</label>
                                <input type="text" name="nationalite_${i}" class="form-control" required value="${data.nationalite || ''}">
                            </div>
                        </div>
                    </div>
                `;
                container.innerHTML += voyageurHtml;
            }
        }

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            updateVoyageurs();
            
            // Définir les dates minimales
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('date_arrivee').min = today;
            document.getElementById('date_depart').min = today;

            document.getElementById('date_arrivee').addEventListener('change', function() {
                document.getElementById('date_depart').min = this.value;
            });
        });
    </script>
</body>
</html>