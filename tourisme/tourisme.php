<?php
session_start();

// Configuration d'erreurs
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // 🔹 Fonction de validation des entrées
        function validateInput($data) {
            if (empty($data)) return '';
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
            return $data;
        }

        // 🔹 Fonction sécurisée pour l'upload des fichiers
        function saveFile($file, $type, $demande_id, $pdo, $uploadDir) {
            if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                
                // Validation du type MIME
                $allowed_types = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png', 
                    'image/jpg' => 'jpg',
                    'application/pdf' => 'pdf'
                ];
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!array_key_exists($mime_type, $allowed_types)) {
                    throw new Exception("Type de fichier non autorisé: " . $mime_type);
                }
                
                // Limite de taille (5MB)
                if ($file['size'] > 5 * 1024 * 1024) {
                    throw new Exception("Fichier trop volumineux (max 5MB): " . $file['name']);
                }
                
                // Nom de fichier sécurisé
                $extension = $allowed_types[$mime_type];
                $filename = uniqid() . "_" . md5(basename($file['name'])) . "." . $extension;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $stmt = $pdo->prepare("INSERT INTO demandes_court_sejour_fichiers 
                        (demande_id, type_fichier, chemin_fichier, date_upload) 
                        VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$demande_id, $type, $filename]);
                    return true;
                }
            }
            return false;
        }

        // 🔹 Champs obligatoires
        $required_fields = [
            'pays_destination', 'visa_type', 'nom', 'date_naissance', 'lieu_naissance',
            'etat_civil', 'nationalite', 'profession', 'adresse', 'telephone',
            'email', 'num_passeport', 'pays_delivrance', 'date_delivrance', 'date_expiration'
        ];

        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ " . str_replace('_', ' ', $field) . " est obligatoire.";
            }
        }

        // 🔹 Validation email
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            $errors[] = "Adresse email invalide.";
        }

        // 🔹 Validation des dates
        $date_expiration = $_POST['date_expiration'] ?? '';
        if ($date_expiration && strtotime($date_expiration) <= time()) {
            $errors[] = "La date d'expiration du passeport doit être dans le futur.";
        }

        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
        } else {
            // 🔹 Récupération et validation des données
            $pays_destination   = validateInput($_POST['pays_destination'] ?? '');
            $visa_type          = validateInput($_POST['visa_type'] ?? '');
            $nom                = validateInput($_POST['nom'] ?? '');
            $date_naissance     = $_POST['date_naissance'] ?? '';
            $lieu_naissance     = validateInput($_POST['lieu_naissance'] ?? '');
            $etat_civil         = validateInput($_POST['etat_civil'] ?? '');
            $nationalite        = validateInput($_POST['nationalite'] ?? '');
            $profession         = validateInput($_POST['profession'] ?? '');
            $adresse            = validateInput($_POST['adresse'] ?? '');
            $telephone          = validateInput($_POST['telephone'] ?? '');
            $email              = $email;
            $num_passeport      = validateInput($_POST['num_passeport'] ?? '');
            $pays_delivrance    = validateInput($_POST['pays_delivrance'] ?? '');
            $date_delivrance    = $_POST['date_delivrance'] ?? '';
            $date_expiration    = $date_expiration;
            $a_deja_visa        = $_POST['a_deja_visa'] ?? 'non';
            $nb_visas           = intval($_POST['nb_visas'] ?? 0);
            $details_voyages    = validateInput($_POST['details_voyages'] ?? '');

            // 🔹 Champs conditionnels pour hébergement
            $nom_hote           = validateInput($_POST['nom_hote'] ?? null);
            $adresse_hote       = validateInput($_POST['adresse_hote'] ?? null);
            $telephone_hote     = validateInput($_POST['telephone_hote'] ?? null);
            $email_hote         = filter_var($_POST['email_hote'] ?? null, FILTER_VALIDATE_EMAIL) ?: null;
            $lien_parente       = validateInput($_POST['lien_parente'] ?? null);

            $user_id = $_SESSION['user_id'] ?? 0;

            // 🔹 Insertion dans `demandes_court_sejour`
            $stmt = $pdo->prepare("INSERT INTO demandes_court_sejour 
                (user_id, visa_type, pays_destination, nom_complet, date_naissance, lieu_naissance, etat_civil, 
                nationalite, profession, adresse, telephone, email, passeport, pays_delivrance, date_delivrance, 
                date_expiration, a_deja_visa, nb_visas, details_voyages, nom_hote, adresse_hote, telephone_hote, 
                email_hote, lien_parente, statut, date_demande) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())");
            
            $stmt->execute([
                $user_id, $visa_type, $pays_destination, $nom, $date_naissance, $lieu_naissance, $etat_civil,
                $nationalite, $profession, $adresse, $telephone, $email, $num_passeport, $pays_delivrance,
                $date_delivrance, $date_expiration, $a_deja_visa, $nb_visas, $details_voyages,
                $nom_hote, $adresse_hote, $telephone_hote, $email_hote, $lien_parente
            ]);

            $demande_id = $pdo->lastInsertId();

            // 🔹 Dossier uploads sécurisé
            $uploadDir = __DIR__ . "/../../../uploads/visas/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // 🔹 Traitement fichiers simples avec gestion d'erreurs
            $file_fields = [
                'copie_passeport' => 'copie_passeport',
                'documents_travail' => 'documents_travail',
                'lettre_invitation' => 'lettre_invitation',
                'justificatif_ressources' => 'justificatif_ressources',
                'lettre_prise_en_charge' => 'lettre_prise_en_charge',
                'prise_en_charge_entreprise' => 'prise_en_charge_entreprise',
                'invitation_entreprise' => 'invitation_entreprise'
            ];

            foreach ($file_fields as $field => $type) {
                if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                    try {
                        saveFile($_FILES[$field], $type, $demande_id, $pdo, $uploadDir);
                    } catch (Exception $e) {
                        error_log("Erreur upload fichier $field: " . $e->getMessage());
                        // Continuer le traitement même si un fichier échoue
                    }
                }
            }

            // 🔹 Traitement fichiers multiples (documents de travail)
            if (!empty($_FILES['documents_travail_multiple']['name'][0])) {
                $doc_files = $_FILES['documents_travail_multiple'];
                for ($i = 0; $i < count($doc_files['name']); $i++) {
                    if ($doc_files['error'][$i] === UPLOAD_ERR_OK) {
                        $file_data = [
                            'name' => $doc_files['name'][$i],
                            'type' => $doc_files['type'][$i],
                            'tmp_name' => $doc_files['tmp_name'][$i],
                            'error' => $doc_files['error'][$i],
                            'size' => $doc_files['size'][$i]
                        ];
                        try {
                            saveFile($file_data, 'documents_travail', $demande_id, $pdo, $uploadDir);
                        } catch (Exception $e) {
                            error_log("Erreur upload document travail multiple: " . $e->getMessage());
                        }
                    }
                }
            }

            // 🔹 Traitement fichiers multiples (visas précédents)
            if ($a_deja_visa === 'oui' && !empty($_FILES['copies_visas']['name'][0])) {
                $visa_files = $_FILES['copies_visas'];
                for ($i = 0; $i < count($visa_files['name']); $i++) {
                    if ($visa_files['error'][$i] === UPLOAD_ERR_OK) {
                        $file_data = [
                            'name' => $visa_files['name'][$i],
                            'type' => $visa_files['type'][$i],
                            'tmp_name' => $visa_files['tmp_name'][$i],
                            'error' => $visa_files['error'][$i],
                            'size' => $visa_files['size'][$i]
                        ];
                        try {
                            saveFile($file_data, 'copie_visa', $demande_id, $pdo, $uploadDir);
                        } catch (Exception $e) {
                            error_log("Erreur upload visa multiple: " . $e->getMessage());
                        }
                    }
                }
            }

            // 🔹 Redirection confirmation
            header("Location: confirmation.php?id=" . $demande_id);
            exit;
        }
    }

} catch (PDOException $e) {
    error_log("Erreur BDD formulaire visa: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Une erreur de base de données est survenue. Veuillez réessayer.</div>";
} catch (Exception $e) {
    error_log("Erreur générale formulaire visa: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Une erreur est survenue: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulaire de Visa Court Séjour</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #003366;
      --secondary-color: #0055aa;
      --accent-color: #ff6b35;
      --light-blue: #e8f2ff;
      --light-gray: #f8f9fa;
      --dark-text: #333;
      --border-color: #dbe4ee;
      --success-color: #28a745;
      --error-color: #dc3545;
      --border-radius: 8px;
      --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }
    
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: var(--light-gray);
      color: var(--dark-text);
      line-height: 1.6;
      padding: 20px;
    }
    
    .container {
      max-width: 1000px;
      margin: auto;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--box-shadow);
    }
    
    header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 25px 30px;
      text-align: center;
    }
    
    header h1 {
      margin-bottom: 10px;
      font-size: 1.8rem;
    }
    
    header p {
      opacity: 0.9;
    }
    
    .form-content {
      padding: 30px;
    }
    
    .form-section {
      margin-bottom: 30px;
      padding: 25px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      background: var(--light-gray);
    }
    
    .form-section h3 {
      color: var(--primary-color);
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--light-blue);
      display: flex;
      align-items: center;
    }
    
    .form-section h3 i {
      margin-right: 10px;
      color: var(--primary-color);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      font-weight: 600;
      margin-bottom: 8px;
      color: var(--dark-text);
    }
    
    .required::after {
      content: " *";
      color: var(--error-color);
    }
    
    input, select, textarea {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      font-size: 1rem;
      transition: var(--transition);
    }
    
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--secondary-color);
      box-shadow: 0 0 0 3px rgba(0, 85, 170, 0.2);
    }
    
    .file-input {
      border: 2px dashed var(--border-color);
      padding: 15px;
      background: var(--light-blue);
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .file-input:hover {
      border-color: var(--secondary-color);
    }
    
    .file-hint {
      font-size: 0.85rem;
      color: #666;
      margin-top: 5px;
      display: block;
    }
    
    .btn-submit {
      background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 15px 30px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 1.1rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .btn-submit:hover {
      background: linear-gradient(to right, #002244, #004488);
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }
    
    .btn-submit i {
      margin-right: 10px;
    }
    
    .hidden {
      display: none;
    }
    
    .form-row {
      display: flex;
      gap: 20px;
    }
    
    .form-row .form-group {
      flex: 1;
    }
    
    footer {
      text-align: center;
      padding: 20px;
      background: var(--light-blue);
      color: #666;
      font-size: 0.9rem;
      border-top: 1px solid var(--border-color);
    }
    
    .conditional-section {
      border-left: 4px solid var(--secondary-color);
      padding-left: 15px;
      margin-top: 15px;
      margin-bottom: 15px;
    }
    
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: var(--border-radius);
    }
    
    .alert-danger {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
    }
    
    .alert-success {
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
    }
    
    .progress-container {
      margin-bottom: 30px;
      background: var(--light-gray);
      padding: 20px;
      border-radius: var(--border-radius);
    }
    
    .progress-steps {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      font-weight: 600;
    }
    
    .progress-bar {
      height: 10px;
      background: #f0f0f0;
      border-radius: 5px;
      overflow: hidden;
    }
    
    .progress-fill {
      height: 100%;
      background: var(--primary-color);
      border-radius: 5px;
      transition: width 0.3s ease;
      width: 33%;
    }
    
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
        gap: 0;
      }
      
      .form-content {
        padding: 20px;
      }
      
      .progress-steps {
        font-size: 0.8rem;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <header>
    <h1><i class="fas fa-passport"></i> Demande de Visa Court Séjour</h1>
    <p>Remplissez soigneusement tous les champs obligatoires (*)</p>
  </header>

  <!-- Indicateur de progression -->
  <div class="progress-container">
    <div class="progress-steps">
      <span>Informations personnelles</span>
      <span>Documents</span>
      <span>Validation</span>
    </div>
    <div class="progress-bar">
      <div class="progress-fill" id="progress-fill"></div>
    </div>
  </div>

  <div class="form-content">
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
      
      <!-- Destination -->
      <div class="form-section">
        <h3><i class="fas fa-globe"></i> Destination</h3>
        <div class="form-group">
          <label class="required">Pays de destination</label>
          <input type="text" name="pays_destination" required placeholder="Entrez le pays de destination">
        </div>
      </div>
      
      <!-- Choix du type de visa -->
      <div class="form-section">
        <h3><i class="fas fa-tasks"></i> Type de visa</h3>
        <div class="form-group">
          <label class="required">Choisissez le type de visa</label>
          <select id="visa_type" name="visa_type" required onchange="toggleVisaType()">
            <option value="">-- Sélectionnez --</option>
            <option value="tourisme">Visa Tourisme</option>
            <option value="affaires">Visa Affaires</option>
            <option value="visite_familiale">Visite Familiale</option>
            <option value="autre">Autre</option>
          </select>
        </div>
      </div>

      <!-- Informations personnelles -->
      <div class="form-section">
        <h3><i class="fas fa-user"></i> Informations personnelles</h3>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Nom complet</label>
            <input type="text" name="nom" required>
          </div>
          <div class="form-group">
            <label class="required">Date de naissance</label>
            <input type="date" name="date_naissance" required>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Lieu de naissance</label>
            <input type="text" name="lieu_naissance" required>
          </div>
          <div class="form-group">
            <label class="required">État civil</label>
            <select name="etat_civil" required>
              <option value="">-- Sélectionnez --</option>
              <option value="celibataire">Célibataire</option>
              <option value="marie">Marié(e)</option>
              <option value="divorce">Divorcé(e)</option>
              <option value="veuf">Veuf/Veuve</option>
            </select>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Nationalité</label>
            <input type="text" name="nationalite" required>
          </div>
          <div class="form-group">
            <label class="required">Profession</label>
            <input type="text" name="profession" required>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Adresse</label>
            <input type="text" name="adresse" required>
          </div>
          <div class="form-group">
            <label class="required">Téléphone</label>
            <input type="tel" name="telephone" required>
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Email</label>
          <input type="email" name="email" required>
        </div>

        <div class="form-group">
          <label>Documents de travail (plusieurs fichiers possibles)</label>
          <input type="file" name="documents_travail_multiple[]" class="file-input" accept=".pdf,.jpg,.png" multiple>
          <span class="file-hint">Contrat de travail, fiche de paie, etc. (PDF, JPG, PNG - max 5MB par fichier)</span>
        </div>
      </div>

      <!-- Passeport -->
      <div class="form-section">
        <h3><i class="fas fa-id-card"></i> Passeport</h3>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Numéro de passeport</label>
            <input type="text" name="num_passeport" required>
          </div>
          <div class="form-group">
            <label class="required">Pays de délivrance</label>
            <input type="text" name="pays_delivrance" required>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Date de délivrance</label>
            <input type="date" name="date_delivrance" required>
          </div>
          <div class="form-group">
            <label class="required">Date d'expiration</label>
            <input type="date" name="date_expiration" required>
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Copie du passeport (PDF ou image)</label>
          <input type="file" name="copie_passeport" class="file-input" accept=".pdf,.jpg,.png" required>
          <span class="file-hint">Pages avec photo et informations personnelles (PDF, JPG, PNG - max 5MB)</span>
        </div>
      </div>

      <!-- Section Tourisme -->
      <div id="tourisme_section" class="form-section hidden">
        <h3><i class="fas fa-suitcase-rolling"></i> Voyage (Tourisme)</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Date d'arrivée prévue</label>
            <input type="date" name="date_arrivee">
          </div>
          <div class="form-group">
            <label>Date de départ prévue</label>
            <input type="date" name="date_depart">
          </div>
        </div>

        <h3><i class="fas fa-hotel"></i> Hébergement</h3>
        <div class="form-group">
          <label>Type d'hébergement</label>
          <select id="hebergement_type" name="hebergement_type" onchange="toggleHebergementFields()">
            <option value="">-- Sélectionnez --</option>
            <option value="hotel">Hôtel</option>
            <option value="particulier">Chez un particulier</option>
          </select>
        </div>
        
        <!-- Champs conditionnels pour l'hébergement -->
        <div id="hotel_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Nom de l'hôtel</label>
            <input type="text" name="nom_hotel" placeholder="Nom de l'établissement">
          </div>
          <div class="form-group">
            <label>Adresse de l'hôtel</label>
            <input type="text" name="adresse_hotel" placeholder="Adresse complète de l'hôtel">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Téléphone de l'hôtel</label>
              <input type="tel" name="telephone_hotel">
            </div>
            <div class="form-group">
              <label>Email de l'hôtel</label>
              <input type="email" name="email_hotel">
            </div>
          </div>
        </div>
        
        <div id="particulier_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Nom et prénom de l'hôte</label>
            <input type="text" name="nom_hote">
          </div>
          <div class="form-group">
            <label>Adresse complète de l'hôte</label>
            <input type="text" name="adresse_hote">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Téléphone de l'hôte</label>
              <input type="tel" name="telephone_hote">
            </div>
            <div class="form-group">
              <label>Email de l'hôte</label>
              <input type="email" name="email_hote">
            </div>
          </div>
          <div class="form-group">
            <label>Lien de parenté</label>
            <input type="text" name="lien_parente" placeholder="Ex: Frère, Cousin, Ami, etc.">
          </div>
          <div class="form-group">
            <label>Lettre d'invitation</label>
            <input type="file" name="lettre_invitation" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Lettre d'invitation de votre hôte (PDF, JPG, PNG - max 5MB)</span>
          </div>
        </div>
        
        <div class="form-group">
          <label>Itinéraire de voyage</label>
          <textarea name="itineraire" rows="3" placeholder="Décrivez votre itinéraire et les villes que vous prévoyez de visiter..."></textarea>
        </div>
      </div>

      <!-- Section Affaires -->
      <div id="affaires_section" class="form-section hidden">
        <h3><i class="fas fa-briefcase"></i> Informations professionnelles</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Nom de l'entreprise</label>
            <input type="text" name="entreprise_origine">
          </div>
          <div class="form-group">
            <label>Votre poste</label>
            <input type="text" name="poste">
          </div>
        </div>
        
        <div class="form-group">
          <label>Adresse de l'entreprise</label>
          <input type="text" name="adresse_entreprise">
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Téléphone de l'entreprise</label>
            <input type="tel" name="tel_entreprise">
          </div>
          <div class="form-group">
            <label>Email de l'entreprise</label>
            <input type="email" name="email_entreprise">
          </div>
        </div>
        
        <div class="form-group">
          <label>Nom de l'entreprise de destination</label>
          <input type="text" name="entreprise_destination">
        </div>
        
        <div class="form-group">
          <label>Adresse de l'entreprise de destination</label>
          <input type="text" name="adresse_entreprise_destination">
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Personne à contacter</label>
            <input type="text" name="contact_destination">
          </div>
          <div class="form-group">
            <label>Téléphone du contact</label>
            <input type="tel" name="tel_contact_destination">
          </div>
        </div>
        
        <div class="form-group">
          <label>Objet de la mission</label>
          <textarea name="objet_mission" rows="3"></textarea>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>Date de début de mission</label>
            <input type="date" name="debut_mission">
          </div>
          <div class="form-group">
            <label>Date de fin de mission</label>
            <input type="date" name="fin_mission">
          </div>
        </div>
        
        <div class="form-group">
          <label>Lettre d'invitation de l'entreprise</label>
          <input type="file" name="invitation_entreprise" class="file-input" accept=".pdf,.jpg,.png">
          <span class="file-hint">Document officiel sur papier entête de l'entreprise (PDF, JPG, PNG - max 5MB)</span>
        </div>
      </div>

      <!-- Section Visite Familiale -->
      <div id="visite_familiale_section" class="form-section hidden">
        <h3><i class="fas fa-users"></i> Visite Familiale</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Date d'arrivée prévue</label>
            <input type="date" name="date_arrivee_familiale">
          </div>
          <div class="form-group">
            <label>Date de départ prévue</label>
            <input type="date" name="date_depart_familiale">
          </div>
        </div>

        <h3><i class="fas fa-home"></i> Hébergement chez un particulier</h3>
        <div class="form-group">
          <label>Nom et prénom de l'hôte</label>
          <input type="text" name="nom_hote">
        </div>
        <div class="form-group">
          <label>Adresse complète de l'hôte</label>
          <input type="text" name="adresse_hote">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Téléphone de l'hôte</label>
            <input type="tel" name="telephone_hote">
          </div>
          <div class="form-group">
            <label>Email de l'hôte</label>
            <input type="email" name="email_hote">
          </div>
        </div>
        <div class="form-group">
          <label>Lien de parenté</label>
          <input type="text" name="lien_parente" placeholder="Ex: Frère, Cousin, Ami, etc.">
        </div>
        <div class="form-group">
          <label>Lettre d'invitation</label>
          <input type="file" name="lettre_invitation" class="file-input" accept=".pdf,.jpg,.png">
          <span class="file-hint">Lettre d'invitation de votre hôte (PDF, JPG, PNG - max 5MB)</span>
        </div>
      </div>

      <!-- Ressources financières -->
      <div class="form-section">
        <h3><i class="fas fa-euro-sign"></i> Ressources financières</h3>
        <div class="form-group">
          <label>Moyens de subsistance</label>
          <select id="ressources" name="ressources" onchange="toggleRessourcesFields()">
            <option value="">-- Sélectionnez --</option>
            <option value="moi_meme">Moi-même</option>
            <option value="garant">Prise en charge par garant</option>
            <option value="entreprise">Prise en charge par l'entreprise</option>
          </select>
        </div>
        
        <!-- Champs conditionnels pour les ressources -->
        <div id="moi_meme_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Justificatif de ressources personnelles</label>
            <input type="file" name="justificatif_ressources" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Relevés bancaires, fiches de paie, etc. (PDF, JPG, PNG - max 5MB)</span>
          </div>
        </div>
        
        <div id="garant_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Nom et prénom du garant</label>
            <input type="text" name="nom_garant">
          </div>
          <div class="form-group">
            <label>Adresse du garant</label>
            <input type="text" name="adresse_garant">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Téléphone du garant</label>
              <input type="tel" name="telephone_garant">
            </div>
            <div class="form-group">
              <label>Email du garant</label>
              <input type="email" name="email_garant">
            </div>
          </div>
          <div class="form-group">
            <label>Lettre de prise en charge</label>
            <input type="file" name="lettre_prise_en_charge" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Lettre de prise en charge signée (PDF, JPG, PNG - max 5MB)</span>
          </div>
        </div>
        
        <div id="entreprise_fields" class="conditional-section hidden">
          <div class="form-group">
            <label>Lettre de prise en charge de l'entreprise</label>
            <input type="file" name="prise_en_charge_entreprise" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Lettre de prise en charge de l'entreprise (PDF, JPG, PNG - max 5MB)</span>
          </div>
        </div>
      </div>

      <!-- Voyages précédents -->
      <div class="form-section">
        <h3><i class="fas fa-plane-departure"></i> Voyages précédents</h3>
        <div class="form-group">
          <label>Avez-vous déjà voyagé dans le pays de destination ou dans l'espace Schengen ?</label>
          <select name="voyages_precedents" onchange="toggleVisaFields()">
            <option value="non">Non</option>
            <option value="oui">Oui</option>
          </select>
          <!-- Champ caché pour faciliter le traitement -->
          <input type="hidden" name="a_deja_visa" id="a_deja_visa" value="non">
        </div>
        
        <div id="details_visa" style="display:none;">
          <div class="form-group">
            <label>Nombre de visas obtenus</label>
            <input type="number" name="nb_visas" id="nb_visas" min="1" onchange="generateVisaInputs()">
          </div>
          <div id="visa_inputs"></div>
          
          <div class="form-group">
            <label>Détails des voyages précédents</label>
            <textarea name="details_voyages" rows="3" placeholder="Décrivez vos voyages précédents, dates et destinations..."></textarea>
          </div>
        </div>
      </div>

      <!-- Déclaration -->
      <div class="form-section">
        <h3><i class="fas fa-file-signature"></i> Déclaration</h3>
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="declaration" required>
            Je certifie que les informations fournies sont exactes et complètes
          </label>
        </div>
        
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="conditions" required>
            J'accepte les conditions de traitement de mes données personnelles
          </label>
        </div>
      </div>

      <!-- Bouton -->
      <div class="form-section" style="text-align: center;">
        <button type="submit" class="btn-submit"><i class="fas fa-paper-plane"></i> Soumettre la demande</button>
      </div>
    </form>
  </div>
  
  <footer>
    <p>© 2025 Babylone Service. Tous droits réservés.</p>
  </footer>
</div>

<script>
// Fonction pour valider le formulaire
function validateForm() {
    const type = document.getElementById("visa_type").value;
    if (!type) {
        alert("Veuillez sélectionner un type de visa");
        return false;
    }
    
    // Validation des emails
    const email = document.querySelector("input[name='email']");
    if (email && !isValidEmail(email.value)) {
        alert("Veuillez entrer une adresse email valide");
        email.focus();
        return false;
    }
    
    // Validation email hôte si présent
    const emailHote = document.querySelector("input[name='email_hote']");
    if (emailHote && emailHote.value && !isValidEmail(emailHote.value)) {
        alert("Veuillez entrer une adresse email valide pour l'hôte");
        emailHote.focus();
        return false;
    }
    
    // Validation des dates
    const dateExpiration = document.querySelector("input[name='date_expiration']");
    if (dateExpiration && new Date(dateExpiration.value) <= new Date()) {
        alert("La date d'expiration du passeport doit être dans le futur");
        dateExpiration.focus();
        return false;
    }
    
    // Validation de la date de naissance
    const dateNaissance = document.querySelector("input[name='date_naissance']");
    if (dateNaissance && new Date(dateNaissance.value) >= new Date()) {
        alert("La date de naissance doit être dans le passé");
        dateNaissance.focus();
        return false;
    }
    
    // Validation des fichiers
    const fileInputs = document.querySelectorAll('input[type="file"]');
    for (let input of fileInputs) {
        if (input.files.length > 0) {
            for (let file of input.files) {
                if (file.size > 5 * 1024 * 1024) {
                    alert(`Le fichier ${file.name} est trop volumineux (max 5MB)`);
                    return false;
                }
            }
        }
    }
    
    return true;
}

function isValidEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Fonction pour initialiser l'état des sections conditionnelles
function initConditionalSections() {
  toggleVisaType();
  toggleHebergementFields();
  toggleRessourcesFields();
  toggleVisaFields();
  
  // Si on a déjà des visas, générer les champs
  <?php if (isset($_POST['nb_visas']) && $_POST['nb_visas'] > 0): ?>
    document.getElementById("nb_visas").value = <?php echo intval($_POST['nb_visas']); ?>;
    generateVisaInputs();
  <?php endif; ?>
}

function toggleVisaType() {
  const type = document.getElementById("visa_type").value;
  document.getElementById("tourisme_section").style.display = type === "tourisme" ? "block" : "none";
  document.getElementById("affaires_section").style.display = type === "affaires" ? "block" : "none";
  document.getElementById("visite_familiale_section").style.display = type === "visite_familiale" ? "block" : "none";
  
  // Mise à jour de la barre de progression
  updateProgressBar();
}

function toggleHebergementFields() {
  const type = document.getElementById("hebergement_type").value;
  document.getElementById("hotel_fields").style.display = type === "hotel" ? "block" : "none";
  document.getElementById("particulier_fields").style.display = type === "particulier" ? "block" : "none";
}

function toggleRessourcesFields() {
  const type = document.getElementById("ressources").value;
  document.getElementById("moi_meme_fields").style.display = type === "moi_meme" ? "block" : "none";
  document.getElementById("garant_fields").style.display = type === "garant" ? "block" : "none";
  document.getElementById("entreprise_fields").style.display = type === "entreprise" ? "block" : "none";
}

function toggleVisaFields() {
  const select = document.querySelector("select[name='voyages_precedents']");
  const details = document.getElementById("details_visa");
  if (select) {
    details.style.display = (select.value === "oui") ? "block" : "none";
    // Mettre à jour le champ hidden pour a_deja_visa
    document.getElementById("a_deja_visa").value = select.value;
  }
}

function generateVisaInputs() {
  const nb = parseInt(document.getElementById("nb_visas").value) || 0;
  const container = document.getElementById("visa_inputs");
  container.innerHTML = "";

  for (let i = 1; i <= nb; i++) {
    const div = document.createElement("div");
    div.classList.add("form-group");
    div.innerHTML = `<label>Copie du visa n°${i}</label>
                     <input type="file" name="copies_visas[]" class="file-input" accept=".pdf,.jpg,.png">
                     <span class="file-hint">Copie du visa précédent (PDF, JPG, PNG - max 5MB)</span>`;
    container.appendChild(div);
  }
}

function updateProgressBar() {
  const progressFill = document.getElementById('progress-fill');
  const visaType = document.getElementById('visa_type').value;
  
  if (visaType) {
    progressFill.style.width = '66%';
  } else {
    progressFill.style.width = '33%';
  }
}

// Mettre à jour la progression lors de la saisie
document.addEventListener('input', function(e) {
  if (e.target.matches('input, select, textarea')) {
    updateProgressBar();
  }
});

// Appeler les fonctions au chargement de la page pour initialiser l'état
document.addEventListener('DOMContentLoaded', initConditionalSections);
</script>
</body>
</html>