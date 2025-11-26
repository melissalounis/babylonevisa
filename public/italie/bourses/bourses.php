<?php
session_start();

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si la table existe, sinon la créer
    $check_table = $pdo->query("SHOW TABLES LIKE 'demandes_bourse_italie'");
    if ($check_table->rowCount() == 0) {
        $create_table_sql = "
            CREATE TABLE demandes_bourse_italie (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT,
                type_bourse VARCHAR(50) NOT NULL,
                niveau_etudes VARCHAR(50) NOT NULL,
                domaine_etudes VARCHAR(255) NOT NULL,
                universite_choisie VARCHAR(255) NOT NULL,
                programme VARCHAR(255) NOT NULL,
                duree_etudes VARCHAR(50) NOT NULL,
                moyenne DECIMAL(4,2) NOT NULL,
                nom VARCHAR(255) NOT NULL,
                prenom VARCHAR(255) NOT NULL,
                date_naissance DATE NOT NULL,
                lieu_naissance VARCHAR(255) NOT NULL,
                nationalite VARCHAR(100) NOT NULL,
                adresse TEXT NOT NULL,
                telephone VARCHAR(50) NOT NULL,
                email VARCHAR(255) NOT NULL,
                tests_italien VARCHAR(50) NOT NULL,
                tests_anglais VARCHAR(50) NOT NULL,
                consentement TINYINT(1) NOT NULL,
                newsletter TINYINT(1) NOT NULL,
                date_soumission DATETIME DEFAULT CURRENT_TIMESTAMP,
                statut ENUM('en_attente', 'acceptee', 'refusee') DEFAULT 'en_attente',
                notes_admin TEXT,
                date_traitement DATETIME
            )
        ";
        $pdo->exec($create_table_sql);
    }

    // Vérifier si la table des fichiers existe
    $check_files_table = $pdo->query("SHOW TABLES LIKE 'demandes_bourse_fichiers'");
    if ($check_files_table->rowCount() == 0) {
        $create_files_table_sql = "
            CREATE TABLE demandes_bourse_fichiers (
                id INT PRIMARY KEY AUTO_INCREMENT,
                demande_id INT NOT NULL,
                type_fichier VARCHAR(100) NOT NULL,
                chemin_fichier VARCHAR(255) NOT NULL,
                date_upload DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $pdo->exec($create_files_table_sql);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // 🔹 Champs obligatoires pour Bourse Italie
        $required_fields = [
            'type_bourse', 'niveau_etudes', 'domaine_etudes', 'universite_choisie', 
            'nom', 'prenom', 'date_naissance', 'lieu_naissance', 'nationalite', 
            'adresse', 'telephone', 'email'
        ];

        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est obligatoire.";
            }
        }

        // 🔹 Vérification des fichiers obligatoires
        $required_files = [
            'releves_notes', 'diplomes', 'lettres_recommandation', 
            'passeport', 'photo_identite'
        ];

        foreach ($required_files as $file) {
            if (empty($_FILES[$file]['name'])) {
                $errors[] = "Le fichier $file est obligatoire.";
            }
        }

        // 🔹 Vérification note minimale selon le type de bourse
        $type_bourse = $_POST['type_bourse'] ?? '';
        $moyenne = floatval($_POST['moyenne'] ?? 0);
        
        if ($type_bourse === 'excellence' && $moyenne < 16) {
            $errors[] = "Pour la bourse d'excellence, une moyenne minimale de 16/20 est requise.";
        }
        
        if ($type_bourse === 'merite' && $moyenne < 14) {
            $errors[] = "Pour la bourse au mérite, une moyenne minimale de 14/20 est requise.";
        }

        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
        } else {
            // 🔹 Récupération des données Bourse
            $type_bourse         = $_POST['type_bourse'] ?? '';
            $niveau_etudes       = $_POST['niveau_etudes'] ?? '';
            $domaine_etudes      = $_POST['domaine_etudes'] ?? '';
            $universite_choisie  = $_POST['universite_choisie'] ?? '';
            $programme           = $_POST['programme'] ?? '';
            $duree_etudes        = $_POST['duree_etudes'] ?? '';
            $moyenne             = $_POST['moyenne'] ?? '';
            
            $nom                 = $_POST['nom'] ?? '';
            $prenom              = $_POST['prenom'] ?? '';
            $date_naissance      = $_POST['date_naissance'] ?? '';
            $lieu_naissance      = $_POST['lieu_naissance'] ?? '';
            $nationalite         = $_POST['nationalite'] ?? '';
            $adresse             = $_POST['adresse'] ?? '';
            $telephone           = $_POST['telephone'] ?? '';
            $email               = $_POST['email'] ?? '';
            
            $tests_italien       = $_POST['tests_italien'] ?? 'non';
            $tests_anglais       = $_POST['tests_anglais'] ?? 'non';
            $consentement        = isset($_POST['consentement']) ? 1 : 0;
            $newsletter          = isset($_POST['newsletter']) ? 1 : 0;
            
            $user_id = $_SESSION['user_id'] ?? 0;

            // 🔹 Insertion dans `demandes_bourse_italie`
            $stmt = $pdo->prepare("INSERT INTO demandes_bourse_italie 
                (user_id, type_bourse, niveau_etudes, domaine_etudes, universite_choisie, programme, 
                duree_etudes, moyenne, nom, prenom, date_naissance, lieu_naissance, nationalite, 
                adresse, telephone, email, tests_italien, tests_anglais, consentement, newsletter, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
            
            $stmt->execute([
                $user_id, $type_bourse, $niveau_etudes, $domaine_etudes, $universite_choisie, $programme,
                $duree_etudes, $moyenne, $nom, $prenom, $date_naissance, $lieu_naissance, $nationalite, 
                $adresse, $telephone, $email, $tests_italien, $tests_anglais, $consentement, $newsletter
            ]);

            $demande_id = $pdo->lastInsertId();

            // 🔹 Dossier uploads - chemin corrigé
            $uploadDir = __DIR__ . "/uploads/bourses/italie/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Fonction pour traiter les fichiers
            function saveFile($file, $type, $demande_id, $pdo, $uploadDir) {
                if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                    $filename = uniqid() . "_" . basename($file['name']);
                    $filepath = $uploadDir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $stmt = $pdo->prepare("INSERT INTO demandes_bourse_fichiers 
                            (demande_id, type_fichier, chemin_fichier, date_upload) 
                            VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$demande_id, $type, $filename]);
                        return true;
                    }
                }
                return false;
            }

            // 🔹 Traitement fichiers obligatoires
            $fichiers_obligatoires = [
                'releves_notes' => 'releves_notes',
                'diplomes' => 'diplomes',
                'lettres_recommandation' => 'lettres_recommandation',
                'passeport' => 'passeport',
                'photo_identite' => 'photo_identite'
            ];

            $upload_errors = [];
            foreach ($fichiers_obligatoires as $field => $type) {
                if (isset($_FILES[$field])) {
                    if (!saveFile($_FILES[$field], $type, $demande_id, $pdo, $uploadDir)) {
                        $upload_errors[] = "Erreur lors de l'upload du fichier: $field";
                    }
                }
            }

            // 🔹 Traitement fichiers langues si test passé
            if ($tests_italien !== 'non' && isset($_FILES['attestation_italien']) && !empty($_FILES['attestation_italien']['name'])) {
                saveFile($_FILES['attestation_italien'], 'attestation_italien', $demande_id, $pdo, $uploadDir);
            }

            if ($tests_anglais !== 'non' && isset($_FILES['attestation_anglais']) && !empty($_FILES['attestation_anglais']['name'])) {
                saveFile($_FILES['attestation_anglais'], 'attestation_anglais', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Traitement autres documents optionnels
            if (isset($_FILES['autres_documents']) && !empty($_FILES['autres_documents']['name'])) {
                saveFile($_FILES['autres_documents'], 'autres_documents', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Redirection confirmation
            $_SESSION['success_message'] = "Votre demande de bourse a été soumise avec succès! ID: #$demande_id";
            header("Location: confirmation_bourse.php?id=" . $demande_id);
            exit;
        }
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Demande de Bourse d'Études en Italie</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #008C45;
      --secondary-color: #CD212A;
      --accent-color: #F4F5F0;
      --light-gray: #f8f9fa;
      --dark-text: #333;
      --border-color: #dbe4ee;
      --success-color: #28a745;
      --error-color: #dc3545;
      --warning-color: #ffc107;
      --info-color: #17a2b8;
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
      max-width: 1200px;
      margin: auto;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--box-shadow);
    }
    
    header {
      background: linear-gradient(135deg, #008C45, #CD212A);
      color: white;
      padding: 30px;
      text-align: center;
    }
    
    header h1 {
      margin-bottom: 10px;
      font-size: 2.2rem;
    }
    
    header p {
      opacity: 0.9;
      font-size: 1.1rem;
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
      border-bottom: 2px solid var(--accent-color);
      display: flex;
      align-items: center;
    }
    
    .form-section h3 i {
      margin-right: 10px;
      color: #CD212A;
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
    
    .optional::after {
      content: " (Optionnel)";
      color: #666;
      font-weight: normal;
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
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(0, 140, 69, 0.2);
    }
    
    .file-input {
      border: 2px dashed var(--border-color);
      padding: 15px;
      background: var(--accent-color);
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .file-input:hover {
      border-color: var(--primary-color);
    }
    
    .file-hint {
      font-size: 0.85rem;
      color: #666;
      margin-top: 5px;
      display: block;
    }
    
    .btn-submit {
      background: linear-gradient(to right, #008C45, #CD212A);
      color: white;
      padding: 15px 40px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 1.2rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .btn-submit:hover {
      background: linear-gradient(to right, #006B33, #A51C2D);
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
    }
    
    .btn-submit i {
      margin-right: 10px;
    }
    
    .btn-secondary {
      background: #6c757d;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 0.9rem;
      transition: var(--transition);
    }
    
    .btn-secondary:hover {
      background: #545b62;
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
      background: var(--accent-color);
      color: #666;
      font-size: 0.9rem;
      border-top: 1px solid var(--border-color);
    }
    
    .conditional-section {
      border-left: 4px solid #CD212A;
      padding-left: 15px;
      margin-top: 15px;
      margin-bottom: 15px;
    }
    
    .section-title {
      background: var(--primary-color);
      color: white;
      padding: 10px 15px;
      border-radius: var(--border-radius);
      margin-bottom: 15px;
      font-weight: 600;
    }
    
    .conditions-box {
      background: #e7f3ff;
      padding: 20px;
      border-radius: var(--border-radius);
      border: 2px solid var(--info-color);
      margin-bottom: 20px;
    }
    
    .conditions-box h4 {
      color: var(--info-color);
      margin-bottom: 15px;
    }
    
    .conditions-list {
      list-style: none;
      padding: 0;
    }
    
    .conditions-list li {
      margin-bottom: 10px;
      padding-left: 25px;
      position: relative;
    }
    
    .conditions-list li:before {
      content: "✓";
      color: var(--success-color);
      font-weight: bold;
      position: absolute;
      left: 0;
    }
    
    .test-demand-section {
      background: #e7f3ff;
      padding: 15px;
      border-radius: var(--border-radius);
      border: 1px solid #b3d9ff;
      margin-top: 15px;
    }
    
    .alert {
      padding: 15px;
      border-radius: var(--border-radius);
      margin-bottom: 20px;
    }
    
    .alert-danger {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .alert-info {
      background: #d1ecf1;
      color: #0c5460;
      border: 1px solid #bee5eb;
    }
    
    .note-minimale {
      background: #fff3cd;
      padding: 10px 15px;
      border-radius: var(--border-radius);
      border-left: 4px solid var(--warning-color);
      margin-top: 10px;
      font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
        gap: 0;
      }
      
      .form-content {
        padding: 20px;
      }
      
      header h1 {
        font-size: 1.8rem;
      }
    }
  </style>
  <script>
    function toggleConditionsBourse() {
      const typeBourse = document.getElementById("type_bourse").value;
      const conditionsSection = document.getElementById("conditions_bourse_section");
      const noteSection = document.getElementById("note_minimale_section");
      
      if (typeBourse) {
        conditionsSection.style.display = "block";
        
        // Afficher les conditions spécifiques
        let conditions = "";
        let noteMinimale = "";
        
        switch(typeBourse) {
          case 'excellence':
            conditions = "Bourse réservée aux étudiants ayant un excellent dossier académique";
            noteMinimale = "Note minimale requise: 16/20";
            break;
          case 'merite':
            conditions = "Bourse basée sur le mérite académique et les projets personnels";
            noteMinimale = "Note minimale requise: 14/20";
            break;
          case 'sportive':
            conditions = "Bourse pour étudiants athlètes de haut niveau";
            break;
          case 'culturelle':
            conditions = "Bourse pour étudiants avec des talents artistiques ou culturels";
            break;
          case 'recherche':
            conditions = "Bourse pour étudiants en recherche (Master 2, Doctorat)";
            break;
        }
        
        document.getElementById("conditions_details").textContent = conditions;
        
        // Gérer l'affichage des sections conditionnelles
        if (noteMinimale) {
          noteSection.style.display = "block";
          document.getElementById("note_minimale_text").textContent = noteMinimale;
        } else {
          noteSection.style.display = "none";
        }
        
      } else {
        conditionsSection.style.display = "none";
      }
    }
    
    function toggleTestItalien() {
      const hasTest = document.getElementById("tests_italien").value;
      const attestationSection = document.getElementById("attestation_italien_section");
      
      if (hasTest !== "non") {
        attestationSection.style.display = "block";
      } else {
        attestationSection.style.display = "none";
      }
    }
    
    function toggleTestAnglais() {
      const hasTest = document.getElementById("tests_anglais").value;
      const attestationSection = document.getElementById("attestation_anglais_section");
      
      if (hasTest !== "non") {
        attestationSection.style.display = "block";
      } else {
        attestationSection.style.display = "none";
      }
    }
    
    function validateForm() {
      const typeBourse = document.getElementById("type_bourse").value;
      const moyenne = parseFloat(document.getElementById("moyenne").value) || 0;
      
      if (!typeBourse) {
        alert("Veuillez sélectionner le type de bourse");
        return false;
      }
      
      // Validation des notes minimales
      if (typeBourse === 'excellence' && moyenne < 16) {
        alert("Pour la bourse d'excellence, une moyenne minimale de 16/20 est requise.");
        return false;
      }
      
      if (typeBourse === 'merite' && moyenne < 14) {
        alert("Pour la bourse au mérite, une moyenne minimale de 14/20 est requise.");
        return false;
      }
      
      // Validation des fichiers obligatoires
      const requiredFiles = ['releves_notes', 'diplomes', 'lettres_recommandation', 'passeport', 'photo_identite'];
      for (let file of requiredFiles) {
        const fileInput = document.querySelector(`input[name="${file}"]`);
        if (!fileInput || !fileInput.files[0]) {
          alert(`Le fichier ${file} est obligatoire.`);
          return false;
        }
      }
      
      return true;
    }
    
    // Initialiser les sections au chargement
    document.addEventListener('DOMContentLoaded', function() {
      toggleConditionsBourse();
      toggleTestItalien();
      toggleTestAnglais();
    });
  </script>
</head>
<body>
<div class="container">
  <header>
    <h1><i class="fas fa-graduation-cap"></i> Demande de Bourse d'Études en Italie</h1>
    <p>Financez vos études en Italie grâce à nos programmes de bourses</p>
  </header>

  <div class="form-content">
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
      
      <!-- Conditions des bourses -->
      <div class="conditions-box">
        <h4><i class="fas fa-info-circle"></i> Conditions d'éligibilité</h4>
        <ul class="conditions-list">
          <li>Être âgé de 18 à 35 ans</li>
          <li>Avoir une moyenne minimale de 12/20 pour les bourses standard</li>
          <li>Être admis ou en cours d'admission dans une université italienne</li>
          <li>Fournir tous les documents requis</li>
          <li>Remplir les conditions spécifiques selon le type de bourse</li>
        </ul>
      </div>

      <!-- Type de bourse -->
      <div class="form-section">
        <h3><i class="fas fa-award"></i> Type de Bourse</h3>
        <div class="form-group">
          <label class="required">Type de bourse demandée</label>
          <select id="type_bourse" name="type_bourse" required onchange="toggleConditionsBourse()">
            <option value="">-- Sélectionnez le type de bourse --</option>
            <option value="excellence">Bourse d'Excellence</option>
            <option value="merite">Bourse au Mérite</option>
            <option value="sportive">Bourse Sportive</option>
            <option value="culturelle">Bourse Culturelle/Artistique</option>
            <option value="recherche">Bourse de Recherche</option>
          </select>
        </div>
        
        <div id="conditions_bourse_section" class="conditional-section hidden">
          <div class="alert alert-info">
            <strong>Conditions spécifiques :</strong> 
            <span id="conditions_details"></span>
          </div>
          
          <div id="note_minimale_section" class="note-minimale hidden">
            <i class="fas fa-exclamation-triangle"></i>
            <span id="note_minimale_text"></span>
          </div>
        </div>
      </div>

      <!-- Projet d'études -->
      <div class="form-section">
        <h3><i class="fas fa-book-open"></i> Projet d'Études</h3>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Niveau d'études</label>
            <select name="niveau_etudes" required>
              <option value="">-- Sélectionnez votre niveau --</option>
              <option value="licence1">Licence 1</option>
              <option value="licence2">Licence 2</option>
              <option value="licence3">Licence 3</option>
              <option value="master1">Master 1</option>
              <option value="master2">Master 2</option>
              <option value="doctorat">Doctorat</option>
            </select>
          </div>
          <div class="form-group">
            <label class="required">Domaine d'études</label>
            <input type="text" name="domaine_etudes" required placeholder="Design, Architecture, Médecine, Droit...">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Université choisie en Italie</label>
            <input type="text" name="universite_choisie" required placeholder="Università di Bologna, Politecnico di Milano...">
          </div>
          <div class="form-group">
            <label class="required">Programme d'études</label>
            <input type="text" name="programme" required placeholder="Laurea in Design, Master in Business...">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Durée des études (années)</label>
            <input type="number" name="duree_etudes" min="1" max="5" required>
          </div>
          <div class="form-group">
            <label class="required">Moyenne générale /20</label>
            <input type="number" id="moyenne" name="moyenne" step="0.01" min="0" max="20" required>
          </div>
        </div>
      </div>

      <!-- Informations personnelles -->
      <div class="form-section">
        <h3><i class="fas fa-user-graduate"></i> Informations Personnelles</h3>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Nom</label>
            <input type="text" name="nom" required>
          </div>
          <div class="form-group">
            <label class="required">Prénom</label>
            <input type="text" name="prenom" required>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Date de naissance</label>
            <input type="date" name="date_naissance" required>
          </div>
          <div class="form-group">
            <label class="required">Lieu de naissance</label>
            <input type="text" name="lieu_naissance" required>
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Nationalité</label>
          <input type="text" name="nationalite" required>
        </div>
        
        <div class="form-group">
          <label class="required">Adresse complète</label>
          <textarea name="adresse" required rows="3"></textarea>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Téléphone</label>
            <input type="tel" name="telephone" required>
          </div>
          <div class="form-group">
            <label class="required">Email</label>
            <input type="email" name="email" required>
          </div>
        </div>
      </div>

      <!-- Tests de langues -->
      <div class="form-section">
        <h3><i class="fas fa-language"></i> Tests de Langues</h3>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Test d'italien</label>
            <select id="tests_italien" name="tests_italien" onchange="toggleTestItalien()">
              <option value="non">Non</option>
              <option value="celi">CELI</option>
              <option value="cils">CILS</option>
              <option value="plida">PLIDA</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="required">Test d'anglais</label>
            <select id="tests_anglais" name="tests_anglais" onchange="toggleTestAnglais()">
              <option value="non">Non</option>
              <option value="ielts">IELTS</option>
              <option value="toefl">TOEFL</option>
              <option value="cambridge">Cambridge</option>
              <option value="autre">Autre</option>
            </select>
          </div>
        </div>
        
        <div id="attestation_italien_section" class="conditional-section hidden">
          <div class="form-group">
            <label class="required">Attestation de test d'italien</label>
            <input type="file" name="attestation_italien" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
          </div>
        </div>
        
        <div id="attestation_anglais_section" class="conditional-section hidden">
          <div class="form-group">
            <label class="required">Attestation de test d'anglais</label>
            <input type="file" name="attestation_anglais" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
          </div>
        </div>
      </div>

      <!-- Documents requis -->
      <div class="form-section">
        <h3><i class="fas fa-file-upload"></i> Documents Requis</h3>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Relevés de notes</label>
            <input type="file" name="releves_notes" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            <span class="file-hint">Format: PDF, JPG, PNG (Max: 5MB)</span>
          </div>
          <div class="form-group">
            <label class="required">Diplômes obtenus</label>
            <input type="file" name="diplomes" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            <span class="file-hint">Format: PDF, JPG, PNG (Max: 5MB)</span>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Lettres de recommandation</label>
            <input type="file" name="lettres_recommandation" class="file-input" accept=".pdf,.doc,.docx" multiple required>
            <span class="file-hint">2 lettres minimum (PDF, DOC - Max: 2MB chacune)</span>
          </div>
          <div class="form-group">
            <label class="required">Passeport</label>
            <input type="file" name="passeport" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            <span class="file-hint">Pages principales (PDF, JPG, PNG - Max: 3MB)</span>
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Photo d'identité</label>
          <input type="file" name="photo_identite" class="file-input" accept=".jpg,.jpeg,.png" required>
          <span class="file-hint">Format: JPG, PNG (Max: 1MB)</span>
        </div>
        
        <div class="form-group">
          <label class="optional">Autres documents</label>
          <input type="file" name="autres_documents" class="file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
          <span class="file-hint">Certificats, prix, portfolio, etc. (Optionnel)</span>
        </div>
      </div>

      <!-- Consentement -->
      <div class="form-section">
        <h3><i class="fas fa-check-circle"></i> Consentement</h3>
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="consentement" required>
            Je certifie que les informations fournies sont exactes et complètes. 
            J'accepte que mes données soient utilisées pour le traitement de ma demande de bourse. *
          </label>
        </div>
        
        <div class="form-group">
          <label>
            <input type="checkbox" name="newsletter">
            Je souhaite recevoir des informations sur d'autres opportunités de bourses et programmes d'études.
          </label>
        </div>
      </div>

      <!-- Bouton de soumission -->
      <div style="text-align: center; margin: 30px 0;">
        <button type="submit" class="btn-submit">
          <i class="fas fa-paper-plane"></i> Soumettre ma Demande de Bourse
        </button>
      </div>
    </form>
  </div>

  <footer>
    <p>© 2025 Service d'Études à l'Étranger - Tous droits réservés</p>
    <p>Pour toute assistance, contactez-nous à babylone.service15@gmail.com</p>
  </footer>
</div>

<script>
// Validation améliorée des fichiers
document.addEventListener('DOMContentLoaded', function() {
  const fileInputs = document.querySelectorAll('input[type="file"]');
  
  fileInputs.forEach(input => {
    input.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        // Déterminer la taille maximale selon le type de fichier
        let maxSize;
        if (this.name === 'photo_identite') {
          maxSize = 1 * 1024 * 1024; // 1MB
        } else if (this.name === 'lettres_recommandation') {
          maxSize = 2 * 1024 * 1024; // 2MB
        } else {
          maxSize = 5 * 1024 * 1024; // 5MB
        }
        
        if (file.size > maxSize) {
          alert('Le fichier "' + file.name + '" est trop volumineux. Taille maximale autorisée: ' + (maxSize/1024/1024) + 'MB');
          e.target.value = '';
          return;
        }
        
        // Vérification du type de fichier
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg', 
                             'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!allowedTypes.includes(file.type)) {
          alert('Type de fichier non autorisé. Formats acceptés: PDF, JPG, PNG, DOC, DOCX');
          e.target.value = '';
          return;
        }
      }
    });
  });
  
  // Validation des dates
  const dateNaissance = document.querySelector('input[name="date_naissance"]');
  if (dateNaissance) {
    const today = new Date();
    const minDate = new Date(today.getFullYear() - 35, today.getMonth(), today.getDate());
    const maxDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
    
    dateNaissance.max = maxDate.toISOString().split('T')[0];
    dateNaissance.min = minDate.toISOString().split('T')[0];
  }
});

// Afficher un message de confirmation avant soumission
const form = document.querySelector('form');
if (form) {
  form.addEventListener('submit', function(e) {
    if (!validateForm()) {
      e.preventDefault();
      return false;
    }
    
    const confirmation = confirm('Êtes-vous sûr de vouloir soumettre votre demande de bourse ? Vérifiez que tous les documents requis sont joints et que vous remplissez les conditions.');
    if (!confirmation) {
      e.preventDefault();
      return false;
    }
    
    // Afficher le loading
    const submitBtn = document.querySelector('.btn-submit');
    if (submitBtn) {
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
      submitBtn.disabled = true;
    }
  });
}
</script>

</body>
</html>