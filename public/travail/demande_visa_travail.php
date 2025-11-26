<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    die("Vous devez être connecté pour soumettre une demande.");
}

$host = "localhost";
$dbname = "babylone_service";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Récupération des données du formulaire
        $pays_destination  = $_POST['pays_destination'];
        $nom_complet       = $_POST['nom_complet'];
        $date_naissance    = $_POST['date_naissance'];
        $nationalite       = $_POST['nationalite'];
        $email             = $_POST['email'];
        $telephone         = $_POST['telephone'];
        $passeport         = $_POST['passeport'];
        $date_delivrance   = $_POST['date_delivrance'];
        $date_expiration   = $_POST['date_expiration'];
        $employeur         = $_POST['employeur'];
        $adresse_employeur = $_POST['adresse_employeur'];
        $type_contrat      = $_POST['type_contrat'];
        $duree_sejour      = $_POST['duree_sejour'];
        $user_id           = $_SESSION['user_id'];

        // Gestion des fichiers uploadés (non obligatoires)
        $photo_identite        = $_FILES['photo_identite']['name'] ?? null;
        $copie_passeport       = $_FILES['copie_passeport']['name'] ?? null;
        $contrat_travail       = $_FILES['contrat_travail']['name'] ?? null;
        $documents_employeur   = $_FILES['documents_employeur']['name'] ?? null;
        $logement              = $_FILES['logement']['name'] ?? null;
        $autorisation_travail  = $_FILES['autorisation_travail']['name'] ?? null;

        // Déplacement des fichiers uploadés (si présents)
        $upload_dir = "uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir);

        foreach ($_FILES as $key => $file) {
            if (!empty($file['name'])) {
                move_uploaded_file($file['tmp_name'], $upload_dir . basename($file['name']));
            }
        }

        // Vérifier si la table a les nouvelles colonnes
        try {
            // Test avec l'ancienne structure (sans pays_destination)
            $sql = "INSERT INTO demandes_visa_travail 
                    (nom_complet, date_naissance, nationalite, email, telephone, passeport, 
                     date_delivrance, date_expiration, employeur, adresse_employeur, type_contrat, 
                     duree_sejour, photo_identite, copie_passeport, contrat_travail, attestation_employeur, 
                     logement, assurance, user_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nom_complet, $date_naissance, $nationalite, $email, $telephone, $passeport,
                $date_delivrance, $date_expiration, $employeur, $adresse_employeur, $type_contrat,
                $duree_sejour, $photo_identite, $copie_passeport, $contrat_travail, $documents_employeur,
                $logement, $autorisation_travail, $user_id
            ]);
        } catch (PDOException $e) {
            // Si erreur, essayer avec la nouvelle structure
            $sql = "INSERT INTO demandes_visa_travail 
                    (pays_destination, nom_complet, date_naissance, nationalite, email, telephone, passeport, 
                     date_delivrance, date_expiration, employeur, adresse_employeur, type_contrat, 
                     duree_sejour, photo_identite, copie_passeport, contrat_travail, documents_employeur, 
                     logement, autorisation_travail, user_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $pays_destination, $nom_complet, $date_naissance, $nationalite, $email, $telephone, $passeport,
                $date_delivrance, $date_expiration, $employeur, $adresse_employeur, $type_contrat,
                $duree_sejour, $photo_identite, $copie_passeport, $contrat_travail, $documents_employeur,
                $logement, $autorisation_travail, $user_id
            ]);
        }

        echo "<p style='color:green;'>✅ Demande soumise avec succès !</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erreur : " . $e->getMessage() . "</p>";
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande Visa Travail</title>
    <style>
        :root {
            --primary-color: #1a237e;
            --primary-light: #534bae;
            --primary-dark: #000051;
            --secondary-color: #e8eaf6;
            --accent-color: #ff6d00;
            --text-color: #212121;
            --text-light: #757575;
            --background: #f5f5f5;
            --white: #ffffff;
            --border-color: #ddd;
            --success-color: #4caf50;
            --error-color: #f44336;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--background);
            padding: 0;
            margin: 0;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 2rem;
            text-align: center;
            border-bottom: 5px solid var(--accent-color);
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        header h1 {
            font-weight: 600;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        
        header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .form-container {
            background-color: var(--white);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .form-section {
            margin-bottom: 2.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-section:last-of-type {
            border-bottom: none;
        }
        
        .form-section h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--secondary-color);
            display: flex;
            align-items: center;
        }
        
        .form-section h2::before {
            content: "•";
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }
        
        .required::after {
            content: " *";
            color: var(--error-color);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-size: 1rem;
            transition: border 0.3s, box-shadow 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-light);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }
        
        input[type="file"] {
            padding: 10px;
            background-color: var(--secondary-color);
            border: 1px dashed var(--primary-light);
        }
        
        .file-hint {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.3rem;
        }
        
        .note {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin: 15px 0;
            font-size: 0.9rem;
        }
        
        button {
            background: linear-gradient(to right, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            width: 100%;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        button:hover {
            background: linear-gradient(to right, var(--primary-dark), var(--primary-color));
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        footer {
            text-align: center;
            padding: 1.5rem;
            color: var(--text-light);
            font-size: 0.9rem;
            border-top: 1px solid var(--border-color);
            margin-top: 2rem;
        }
        
        .conditional-field {
            display: none;
        }
        
        @media (min-width: 768px) {
            .form-row {
                display: flex;
                gap: 20px;
            }
            
            .form-row .form-group {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>Demande de Visa Travail</h1>
        <p>Remplissez ce formulaire pour soumettre votre demande de visa de travail</p>
    </header>

    <div class="container">
        <div class="form-container">
            <?php if (isset($success_message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <h2>Destination</h2>
                    
                    <div class="form-group">
                        <label for="pays_destination" class="required">Pays de destination</label>
                        <select id="pays_destination" name="pays_destination" required onchange="toggleFranceFields()">
                            <option value="">Sélectionnez un pays</option>
                            <option value="France" <?= (isset($_POST['pays_destination']) && $_POST['pays_destination'] == 'France') ? 'selected' : '' ?>>France</option>
                            <option value="Allemagne" <?= (isset($_POST['pays_destination']) && $_POST['pays_destination'] == 'Allemagne') ? 'selected' : '' ?>>Allemagne</option>
                            <option value="Belgique" <?= (isset($_POST['pays_destination']) && $_POST['pays_destination'] == 'Belgique') ? 'selected' : '' ?>>Belgique</option>
                            <option value="Suisse" <?= (isset($_POST['pays_destination']) && $_POST['pays_destination'] == 'Suisse') ? 'selected' : '' ?>>Suisse</option>
                            <option value="Canada" <?= (isset($_POST['pays_destination']) && $_POST['pays_destination'] == 'Canada') ? 'selected' : '' ?>>Canada</option>
                            <option value="Autre" <?= (isset($_POST['pays_destination']) && $_POST['pays_destination'] == 'Autre') ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Informations personnelles</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom_complet" class="required">Nom complet</label>
                            <input type="text" id="nom_complet" name="nom_complet" required value="<?= isset($_POST['nom_complet']) ? htmlspecialchars($_POST['nom_complet']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_naissance" class="required">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance" required value="<?= isset($_POST['date_naissance']) ? htmlspecialchars($_POST['date_naissance']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nationalite" class="required">Nationalité</label>
                            <input type="text" id="nationalite" name="nationalite" required value="<?= isset($_POST['nationalite']) ? htmlspecialchars($_POST['nationalite']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone" class="required">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" required value="<?= isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : '' ?>">
                    </div>
                </div>

                <div class="form-section">
                    <h2>Détails du passeport</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="passeport" class="required">Numéro de passeport</label>
                            <input type="text" id="passeport" name="passeport" required value="<?= isset($_POST['passeport']) ? htmlspecialchars($_POST['passeport']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_delivrance" class="required">Date de délivrance</label>
                            <input type="date" id="date_delivrance" name="date_delivrance" required value="<?= isset($_POST['date_delivrance']) ? htmlspecialchars($_POST['date_delivrance']) : '' ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_expiration" class="required">Date d'expiration</label>
                            <input type="date" id="date_expiration" name="date_expiration" required value="<?= isset($_POST['date_expiration']) ? htmlspecialchars($_POST['date_expiration']) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Informations professionnelles</h2>
                    
                    <div class="form-group">
                        <label for="employeur" class="required">Nom de l'employeur</label>
                        <input type="text" id="employeur" name="employeur" required value="<?= isset($_POST['employeur']) ? htmlspecialchars($_POST['employeur']) : '' ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse_employeur" class="required">Adresse de l'employeur</label>
                        <textarea id="adresse_employeur" name="adresse_employeur" rows="3" required><?= isset($_POST['adresse_employeur']) ? htmlspecialchars($_POST['adresse_employeur']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type_contrat" class="required">Type de contrat</label>
                            <select id="type_contrat" name="type_contrat" required>
                                <option value="">Sélectionnez...</option>
                                <option value="CDI" <?= (isset($_POST['type_contrat']) && $_POST['type_contrat'] == 'CDI') ? 'selected' : '' ?>>CDI</option>
                                <option value="CDD" <?= (isset($_POST['type_contrat']) && $_POST['type_contrat'] == 'CDD') ? 'selected' : '' ?>>CDD</option>
                                <option value="Stage" <?= (isset($_POST['type_contrat']) && $_POST['type_contrat'] == 'Stage') ? 'selected' : '' ?>>Stage</option>
                                <option value="Mission Temporaire" <?= (isset($_POST['type_contrat']) && $_POST['type_contrat'] == 'Mission Temporaire') ? 'selected' : '' ?>>Mission Temporaire</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="duree_sejour" class="required">Durée prévue du séjour (en mois)</label>
                            <input type="number" id="duree_sejour" name="duree_sejour" min="1" required value="<?= isset($_POST['duree_sejour']) ? htmlspecialchars($_POST['duree_sejour']) : '' ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2>Documents à fournir</h2>
                    
                    <div class="form-group">
                        <label for="photo_identite">Photo d'identité</label>
                        <input type="file" id="photo_identite" name="photo_identite">
                        <p class="file-hint">Format JPEG ou PNG, taille maximale 2MB</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="copie_passeport">Copie du passeport</label>
                        <input type="file" id="copie_passeport" name="copie_passeport">
                        <p class="file-hint">Pages avec photo et informations personnelles</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="contrat_travail">Contrat de travail signé</label>
                        <input type="file" id="contrat_travail" name="contrat_travail">
                        <p class="file-hint">PDF de préférence</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="documents_employeur">Documents de l'employeur</label>
                        <input type="file" id="documents_employeur" name="documents_employeur">
                        <p class="file-hint">Lettre d'attestation, statut de l'entreprise, avis d'imposition, etc.</p>
                        <div class="note">
                            <strong>Note :</strong> Veuillez fusionner tous les documents de l'employeur en un seul fichier PDF (lettre d'attestation, statut de l'entreprise, avis d'imposition, etc.)
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="logement">Justificatif de logement</label>
                        <input type="file" id="logement" name="logement">
                    </div>
                    
                    <div class="form-group conditional-field" id="autorisation-travail-field">
                        <label for="autorisation_travail">Autorisation de travail</label>
                        <input type="file" id="autorisation_travail" name="autorisation_travail">
                        <p class="file-hint">Document d'autorisation de travail délivré par les autorités françaises</p>
                    </div>
                </div>

                <button type="submit" name="submit_demande">
                    <i class="fas fa-paper-plane"></i> Soumettre la demande
                </button>
            </form>
        </div>
    </div>

    <footer>
        <p>© 2025 Service des visas. Tous droits réservés.</p>
    </footer>

    <!-- Ajout de Font Awesome pour l'icône -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        function toggleFranceFields() {
            const paysDestination = document.getElementById('pays_destination').value;
            const autorisationField = document.getElementById('autorisation-travail-field');
            
            if (paysDestination === 'France') {
                autorisationField.style.display = 'block';
            } else {
                autorisationField.style.display = 'none';
            }
        }
        
        // Initialiser l'état au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            toggleFranceFields();
        });
    </script>
</body>
</html>