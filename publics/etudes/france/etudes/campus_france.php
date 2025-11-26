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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // 🔹 Champs obligatoires pour Campus France
        $required_fields = [
            'pays_etudes', 'niveau_etudes', 'domaine_etudes', 'nom', 'prenom', 
            'date_naissance', 'lieu_naissance', 'nationalite', 'adresse', 
            'telephone', 'email', 'num_passeport', 'date_delivrance', 'date_expiration'
        ];

        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est obligatoire.";
            }
        }

        // 🔹 Vérification du certificat de scolarité (obligatoire pour tous les niveaux)
        if (empty($_FILES['certificat_scolarite']['name'])) {
            $errors[] = "Le certificat de scolarité est obligatoire pour tous les niveaux.";
        }

        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
        } else {
            // 🔹 Récupération des données Campus France
            $pays_etudes        = $_POST['pays_etudes'] ?? '';
            $niveau_etudes      = $_POST['niveau_etudes'] ?? '';
            $domaine_etudes     = $_POST['domaine_etudes'] ?? '';
            
            $nom                = $_POST['nom'] ?? '';
            $prenom             = $_POST['prenom'] ?? '';
            $date_naissance     = $_POST['date_naissance'] ?? '';
            $lieu_naissance     = $_POST['lieu_naissance'] ?? '';
            $nationalite        = $_POST['nationalite'] ?? '';
            $adresse            = $_POST['adresse'] ?? '';
            $telephone          = $_POST['telephone'] ?? '';
            $email              = $_POST['email'] ?? '';
            
            $num_passeport      = $_POST['num_passeport'] ?? '';
            $date_delivrance    = $_POST['date_delivrance'] ?? '';
            $date_expiration    = $_POST['date_expiration'] ?? '';
            
            $niveau_francais    = $_POST['niveau_francais'] ?? '';
            $tests_francais     = $_POST['tests_francais'] ?? 'non';
            $score_test         = $_POST['score_test'] ?? '';
            
            // 🔹 Nouvelles données
            $test_anglais       = $_POST['test_anglais'] ?? 'non';
            $score_anglais      = $_POST['score_anglais'] ?? '';
            $boite_pastel       = $_POST['boite_pastel'] ?? 'non';
            $email_pastel       = $_POST['email_pastel'] ?? '';
            $mdp_pastel         = $_POST['mdp_pastel'] ?? '';
            
            // 🔹 Récupération des relevés par année
            $releves_annees = [];
            for ($i = 1; $i <= 5; $i++) {
                if (isset($_POST["annee_etude_$i"]) && !empty($_POST["annee_etude_$i"])) {
                    $releves_annees[$i] = [
                        'annee' => $_POST["annee_etude_$i"],
                        'moyenne' => $_POST["moyenne_annee_$i"] ?? '',
                        'mention' => $_POST["mention_annee_$i"] ?? ''
                    ];
                }
            }
            $releves_annees_json = json_encode($releves_annees);
            
            // 🔹 Récupération des autres documents
            $autres_documents = [];
            $nb_documents = $_POST['nb_documents'] ?? 0;
            for ($i = 1; $i <= $nb_documents; $i++) {
                if (isset($_POST["type_document_$i"]) && !empty($_POST["type_document_$i"])) {
                    $autres_documents[$i] = [
                        'type' => $_POST["type_document_$i"],
                        'description' => $_POST["description_document_$i"] ?? ''
                    ];
                }
            }
            $autres_documents_json = json_encode($autres_documents);
            
            $user_id = $_SESSION['user_id'] ?? 0;

            // 🔹 CORRECTION : Ajout du champ manquant 'niveau_francais' dans la requête
            $stmt = $pdo->prepare("INSERT INTO demandes_campus_france 
                (user_id, pays_etudes, niveau_etudes, domaine_etudes, nom, prenom, 
                date_naissance, lieu_naissance, nationalite, adresse, telephone, email, 
                num_passeport, date_delivrance, date_expiration, niveau_francais, 
                tests_francais, score_test, test_anglais, score_anglais, boite_pastel,
                email_pastel, mdp_pastel, releves_annees, autres_documents, statut, date_soumission) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())");
            
            // 🔹 CORRECTION : Ajout de la variable $niveau_francais dans le tableau d'exécution
            $stmt->execute([
                $user_id, $pays_etudes, $niveau_etudes, $domaine_etudes, $nom, $prenom,
                $date_naissance, $lieu_naissance, $nationalite, $adresse, $telephone, $email,
                $num_passeport, $date_delivrance, $date_expiration, $niveau_francais,
                $tests_francais, $score_test, $test_anglais, $score_anglais, $boite_pastel,
                $email_pastel, $mdp_pastel, $releves_annees_json, $autres_documents_json
            ]);

            $demande_id = $pdo->lastInsertId();

            // 🔹 Dossier uploads
            $uploadDir = __DIR__ . "/../../../uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            // Fonction pour traiter les fichiers
            function saveFile($file, $type, $demande_id, $pdo, $uploadDir) {
                if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                    $filename = uniqid() . "_" . basename($file['name']);
                    $filepath = $uploadDir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $stmt = $pdo->prepare("INSERT INTO demandes_campus_france_fichiers 
                            (demande_id, type_fichier, chemin_fichier, date_upload) 
                            VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$demande_id, $type, $filename]);
                    }
                }
            }

            // 🔹 Traitement fichiers obligatoires
            if (isset($_FILES['copie_passeport'])) {
                saveFile($_FILES['copie_passeport'], 'copie_passeport', $demande_id, $pdo, $uploadDir);
            }
            if (isset($_FILES['photo_identite'])) {
                saveFile($_FILES['photo_identite'], 'photo_identite', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Traitement du certificat de scolarité (obligatoire pour tous)
            if (isset($_FILES['certificat_scolarite'])) {
                saveFile($_FILES['certificat_scolarite'], 'certificat_scolarite', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Traitement fichiers optionnels
            if (isset($_FILES['lettre_motivation']) && !empty($_FILES['lettre_motivation']['name'])) {
                saveFile($_FILES['lettre_motivation'], 'lettre_motivation', $demande_id, $pdo, $uploadDir);
            }
            
            if (isset($_FILES['cv']) && !empty($_FILES['cv']['name'])) {
                saveFile($_FILES['cv'], 'cv', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Traitement fichiers conditionnels
            if ($tests_francais !== 'non' && isset($_FILES['attestation_francais']) && !empty($_FILES['attestation_francais']['name'])) {
                saveFile($_FILES['attestation_francais'], 'attestation_francais', $demande_id, $pdo, $uploadDir);
            }
            
            if ($test_anglais !== 'non' && isset($_FILES['attestation_anglais']) && !empty($_FILES['attestation_anglais']['name'])) {
                saveFile($_FILES['attestation_anglais'], 'attestation_anglais', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Traitement fichiers des relevés par année
            for ($i = 1; $i <= 5; $i++) {
                if (isset($_FILES["releve_annee_$i"]) && !empty($_FILES["releve_annee_$i"]['name'])) {
                    saveFile($_FILES["releve_annee_$i"], "releve_annee_$i", $demande_id, $pdo, $uploadDir);
                }
            }

            // 🔹 Traitement autres documents
            for ($i = 1; $i <= $nb_documents; $i++) {
                if (isset($_FILES["fichier_document_$i"]) && !empty($_FILES["fichier_document_$i"]['name'])) {
                    saveFile($_FILES["fichier_document_$i"], "document_$i", $demande_id, $pdo, $uploadDir);
                }
            }

            // 🔹 Redirection confirmation
            header("Location: confirmation_campus.php?id=" . $demande_id);
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
  <title>Formulaire Campus France</title>
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
      background: linear-gradient(135deg, #4b0082, #8a2be2);
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
      color: #4b0082;
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
      background: linear-gradient(to right, #4b0082, #8a2be2);
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
      background: linear-gradient(to right, #050447ff, #2724d4ff);
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
      border-left: 4px solid #220bf0ff;
      padding-left: 15px;
      margin-top: 15px;
      margin-bottom: 15px;
    }
    
    .annee-section {
      background: white;
      padding: 15px;
      border-radius: var(--border-radius);
      margin-bottom: 15px;
      border: 1px solid var(--border-color);
    }
    
    .test-rdv-link {
      display: inline-block;
      background: var(--accent-color);
      color: white;
      padding: 10px 20px;
      border-radius: var(--border-radius);
      text-decoration: none;
      margin-top: 10px;
      font-weight: 600;
      transition: var(--transition);
    }
    
    .test-rdv-link:hover {
      background: #e55a2b;
      transform: translateY(-2px);
    }
    
    .document-section {
      background: white;
      padding: 15px;
      border-radius: var(--border-radius);
      margin-bottom: 15px;
      border: 1px solid var(--border-color);
    }
    
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
        gap: 0;
      }
      
      .form-content {
        padding: 20px;
      }
    }
  </style>
  <script>
    function toggleTestFrancais() {
      const hasTest = document.getElementById("tests_francais").value;
      const scoreTestSection = document.getElementById("score_test_section");
      const rdvButton = document.getElementById("test_rdv_button");
      
      scoreTestSection.style.display = (hasTest !== "non") ? "block" : "none";
      rdvButton.style.display = (hasTest === "non") ? "block" : "none";
    }
    // Dans votre formulaire d'inscription, modifiez la fonction saveFile :
function saveFile($file, $type, $demande_id, $pdo, $uploadDir) {
    if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
        $filename = uniqid() . "_" . basename($file['name']);
        $filepath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Enregistrer dans la table des fichiers
            $stmt = $pdo->prepare("INSERT INTO demandes_campus_france_fichiers 
                (demande_id, type_fichier, chemin_fichier, nom_original, taille_fichier, date_upload) 
                VALUES (?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $demande_id, 
                $type, 
                $filename, 
                $file['name'],
                $file['size']
            ]);
            return true;
        }
    }
    return false;
}
    
    function toggleTestAnglais() {
      const hasTest = document.getElementById("test_anglais").value;
      document.getElementById("score_anglais_section").style.display = (hasTest !== "non") ? "block" : "none";
    }
    
    function toggleBoitePastel() {
      const hasBoite = document.getElementById("boite_pastel").value;
      document.getElementById("boite_pastel_section").style.display = (hasBoite === "oui") ? "block" : "none";
    }
    
    function toggleAcceptation() {
      const hasAcceptation = document.querySelector("select[name='has_acceptation']").value;
      document.getElementById("acceptation_section").style.display = (hasAcceptation === "oui") ? "block" : "none";
    }
    
    function toggleRelevesAnnees() {
      const niveau = document.getElementById("niveau_etudes").value;
      const relevesSection = document.getElementById("releves_annees_section");
      
      // Afficher les relevés pour tous les niveaux sauf "Master terminé"
      if (niveau !== "master_termine" && 
          (niveau === "licence1" || niveau === "licence2" || niveau === "licence3" || 
           niveau === "master1" || niveau === "master2" || niveau === "doctorat" ||
           niveau === "bts" || niveau === "dut" || niveau === "inge" || niveau === "commerce")) {
        relevesSection.style.display = "block";
        genererChampsReleves(niveau);
      } else {
        relevesSection.style.display = "none";
      }
    }
    
    function genererChampsReleves(niveau) {
      const container = document.getElementById("releves_annees_container");
      container.innerHTML = "";
      
      let annees = [];
      
      switch(niveau) {
        case "licence1":
          annees = ["Année du Bac"];
          break;
        case "licence2":
          annees = ["Année du Bac", "Licence 1"];
          break;
        case "licence3":
          annees = ["Année du Bac", "Licence 1", "Licence 2"];
          break;
        case "master1":
          annees = ["Année du Bac", "Licence 1", "Licence 2", "Licence 3"];
          break;
        case "master2":
          annees = ["Année du Bac", "Licence 1", "Licence 2", "Licence 3", "Master 1"];
          break;
        case "doctorat":
          annees = ["Année du Bac", "Licence 1", "Licence 2", "Licence 3", "Master 1", "Master 2"];
          break;
        case "bts":
          annees = ["Année du Bac", "BTS 1ère année"];
          break;
        case "dut":
          annees = ["Année du Bac", "DUT 1ère année"];
          break;
        case "inge":
          annees = ["Année du Bac", "1ère année ingénieur", "2ème année ingénieur"];
          break;
        case "commerce":
          annees = ["Année du Bac", "1ère année commerce", "2ème année commerce"];
          break;
        default:
          annees = ["Année du Bac"];
      }
      
      annees.forEach((annee, index) => {
        const anneeNum = index + 1;
        const section = document.createElement("div");
        section.className = "annee-section";
        section.innerHTML = `
          <div class="annee-header">
            <h4>${annee}</h4>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="required">Année d'étude</label>
              <input type="text" name="annee_etude_${anneeNum}" value="${annee}" readonly>
            </div>
            <div class="form-group">
              <label class="required">Moyenne</label>
              <input type="text" name="moyenne_annee_${anneeNum}" placeholder="Ex: 14.5/20" required>
            </div>
            <div class="form-group">
              <label class="required">Mention</label>
              <input type="text" name="mention_annee_${anneeNum}" placeholder="Ex: Assez Bien" required>
            </div>
          </div>
          <div class="form-group">
            <label class="required">Relevé de notes ${annee}</label>
            <input type="file" name="releve_annee_${anneeNum}" class="file-input" accept=".pdf,.jpg,.png" required>
            <span class="file-hint">Relevé de notes de ${annee} (max 5MB)</span>
          </div>
        `;
        container.appendChild(section);
      });
    }
    
    function genererChampsDocuments() {
      const nbDocuments = document.getElementById("nb_documents").value;
      const container = document.getElementById("autres_documents_container");
      container.innerHTML = "";
      
      for (let i = 1; i <= nbDocuments; i++) {
        const section = document.createElement("div");
        section.className = "document-section";
        section.innerHTML = `
          <div class="form-row">
            <div class="form-group">
              <label class="required">Type de document ${i}</label>
              <select name="type_document_${i}" required>
                <option value="">-- Sélectionnez --</option>
                <option value="lettre_recommandation">Lettre de recommandation</option>
                <option value="attestation_travail">Attestation de travail</option>
                <option value="attestation_stage">Attestation de stage</option>
                <option value="certificat_competence">Certificat de compétence</option>
                <option value="autre">Autre document</option>
              </select>
            </div>
            <div class="form-group">
              <label>Description</label>
              <input type="text" name="description_document_${i}" placeholder="Description du document">
            </div>
          </div>
          <div class="form-group">
            <label class="required">Fichier document ${i}</label>
            <input type="file" name="fichier_document_${i}" class="file-input" accept=".pdf,.jpg,.png,.doc,.docx" required>
            <span class="file-hint">Document ${i} (max 5MB)</span>
          </div>
        `;
        container.appendChild(section);
      }
    }
    
    function validateForm() {
      const niveau = document.getElementById("niveau_etudes").value;
      if (!niveau) {
        alert("Veuillez sélectionner votre niveau d'études");
        return false;
      }
      
      // Validation du certificat de scolarité (obligatoire pour tous)
      const certificatScolarite = document.getElementById("certificat_scolarite");
      if (!certificatScolarite.files.length) {
        alert("Le certificat de scolarité est obligatoire pour tous les niveaux.");
        return false;
      }
      
      return true;
    }
  </script>
</head>
<body>
<div class="container">
  <header>
    <h1><i class="fas fa-graduation-cap"></i> Demande Campus France</h1>
    <p>Formulaire d'inscription pour études en France</p>
  </header>

  <div class="form-content">
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
      
      <!-- Projet d'études -->
      <div class="form-section">
        <h3><i class="fas fa-book-open"></i> Projet d'études</h3>
        <div class="form-group">
          <label class="required">Pays d'études</label>
          <input type="text" name="pays_etudes" required value="France" readonly>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Niveau d'études visé</label>
            <select id="niveau_etudes" name="niveau_etudes" required onchange="toggleRelevesAnnees()">
              <option value="">-- Sélectionnez --</option>
              <option value="licence1">Licence 1ère année</option>
              <option value="licence2">Licence 2ème année</option>
              <option value="licence3">Licence 3ème année</option>
              <option value="master1">Master 1ère année</option>
              <option value="master2">Master 2ème année</option>
              <option value="master_termine">Master terminé (diplômé)</option>
              <option value="doctorat">Doctorat</option>
              <option value="bts">BTS</option>
              <option value="dut">DUT</option>
              <option value="inge">École d'ingénieurs</option>
              <option value="commerce">École de commerce</option>
            </select>
          </div>
          <div class="form-group">
            <label class="required">Domaine d'études</label>
            <input type="text" name="domaine_etudes" required placeholder="Informatique, Droit, Médecine...">
          </div>
        </div>
      </div>

      <!-- Informations personnelles -->
      <div class="form-section">
        <h3><i class="fas fa-user-graduate"></i> Informations personnelles</h3>
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
        
        <div class="form-group">
          <label class="required">Photo d'identité</label>
          <input type="file" name="photo_identite" class="file-input" accept=".jpg,.jpeg,.png" required>
          <span class="file-hint">Photo format passeport (jpg, png - max 2MB)</span>
        </div>
      </div>

      <!-- Passeport -->
      <div class="form-section">
        <h3><i class="fas fa-passport"></i> Passeport</h3>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Numéro de passeport</label>
            <input type="text" name="num_passeport" required>
          </div>
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
          <label class="required">Copie du passeport</label>
          <input type="file" name="copie_passeport" class="file-input" accept=".pdf,.jpg,.png" required>
          <span class="file-hint">Pages avec photo et informations personnelles (max 5MB)</span>
        </div>
      </div>

      <!-- Niveau de français -->
      <div class="form-section">
        <h3><i class="fas fa-language"></i> Niveau de français</h3>
       
        <div class="form-group">
          <label>Avez-vous passé un test de français ?</label>
          <select id="tests_francais" name="tests_francais" onchange="toggleTestFrancais()">
            <option value="non">Non</option>
            <option value="tcf">TCF</option>
            <option value="delf">DELF</option>
            <option value="dalf">DALF</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        
        <div id="test_rdv_button" class="hidden">
          <a href="/babylone/public/test_de_langue.php" class="test-rdv-link">
            <i class="fas fa-calendar-check"></i> Demander un rendez-vous test de langue
          </a>
        </div>
        
        <div id="score_test_section" class="conditional-section hidden">
          <div class="form-group">
            <label>Score/Diplôme obtenu</label>
            <input type="text" name="score_test" placeholder="Ex: B2, 450 points...">
          </div>
          <div class="form-group">
            <label>Attestation de score/diplôme</label>
            <input type="file" name="attestation_francais" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
          </div>
        </div>
      </div>

      <!-- Test d'anglais -->
      <div class="form-section">
        <h3><i class="fas fa-globe"></i> Test d'anglais</h3>
       
        <div class="form-group">
          <label>Avez-vous passé un test d'anglais ?</label>
          <select id="test_anglais" name="test_anglais" onchange="toggleTestAnglais()">
            <option value="non">Non</option>
            <option value="ielts">IELTS</option>
            <option value="toefl">TOEFL</option>
            <option value="toeic">TOEIC</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        
        <div id="score_anglais_section" class="conditional-section hidden">
          <div class="form-group">
            <label>Score obtenu</label>
            <input type="text" name="score_anglais" placeholder="Ex: 6.5, 85...">
          </div>
          <div class="form-group">
            <label>Attestation de score</label>
            <input type="file" name="attestation_anglais" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie de l'attestation de score (max 5MB)</span>
          </div>
        </div>
      </div>

      <!-- Boîte Pastel -->
      <div class="form-section">
        <h3><i class="fas fa-envelope"></i> Boîte Pastel</h3>
       
        <div class="form-group">
          <label>Avez-vous déjà une boîte Pastel ?</label>
          <select id="boite_pastel" name="boite_pastel" onchange="toggleBoitePastel()">
            <option value="non">Non</option>
            <option value="oui">Oui</option>
          </select>
        </div>
        
        <div id="boite_pastel_section" class="conditional-section hidden">
          <div class="form-row">
            <div class="form-group">
              <label class="required">Email Pastel</label>
              <input type="email" name="email_pastel" placeholder="votre.email@pastel.fr">
            </div>
            <div class="form-group">
              <label class="required">Mot de passe Pastel</label>
              <input type="password" name="mdp_pastel" placeholder="Votre mot de passe">
            </div>
          </div>
        </div>
      </div>

      <!-- Relevés de notes par année -->
      <div id="releves_annees_section" class="form-section hidden">
        <h3><i class="fas fa-file-alt"></i> Relevés de notes par année (Obligatoire)</h3>
        <div id="releves_annees_container">
          <!-- Les champs seront générés dynamiquement -->
        </div>
      </div>

      <!-- Documents de motivation -->
      <div class="form-section">
        <h3><i class="fas fa-file-alt"></i> Documents de motivation</h3>
        <div class="form-group">
          <label class="optional">Lettre de motivation</label>
          <input type="file" name="lettre_motivation" class="file-input" accept=".pdf,.doc,.docx">
          <span class="file-hint">Lettre de motivation détaillant votre projet (max 5MB)</span>
        </div>
        
        <div class="form-group">
          <label class="optional">Curriculum Vitae (CV)</label>
          <input type="file" name="cv" class="file-input" accept=".pdf,.doc,.docx">
          <span class="file-hint">CV à jour (max 5MB)</span>
        </div>
        
        <div class="form-group">
          <label class="required">Certificat de scolarité</label>
          <input type="file" id="certificat_scolarite" name="certificat_scolarite" class="file-input" accept=".pdf,.doc,.docx" required>
          <span class="file-hint">Certificat de scolarité (obligatoire pour tous les niveaux - max 5MB)</span>
        </div>
      </div>

      <!-- Autres documents -->
      <div class="form-section">
        <h3><i class="fas fa-folder-open"></i> Autres documents</h3>
       
        <div class="form-group">
          <label>Nombre d'autres documents à ajouter</label>
          <select id="nb_documents" name="nb_documents" onchange="genererChampsDocuments()">
            <option value="0">0 - Aucun document supplémentaire</option>
            <option value="1">1 document</option>
            <option value="2">2 documents</option>
            <option value="3">3 documents</option>
            <option value="4">4 documents</option>
            <option value="5">5 documents</option>
          </select>
        </div>
        
        <div id="autres_documents_container">
          <!-- Les champs seront générés dynamiquement -->
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
        
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="campus_france" required>
            Je m'engage à suivre la procédure Campus France
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
    <p>© 2025 Campus France. Tous droits réservés.</p>
  </footer>
</div>

<script>
// Fonction pour initialiser l'état des sections conditionnelles
function initConditionalSections() {
  toggleTestFrancais();
  toggleTestAnglais();
  toggleBoitePastel();
  toggleAcceptation();
  toggleRelevesAnnees();
  genererChampsDocuments();
}

// Appeler les fonctions au chargement de la page
document.addEventListener('DOMContentLoaded', initConditionalSections);
</script>
</body>
</html>