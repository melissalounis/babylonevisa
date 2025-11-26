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
        // Validation des champs obligatoires
        $required_fields = [
            'specialite', 'programme_langue', 'nom', 'prenom', 'date_naissance',
            'nationalite', 'email', 'telephone', 'niveau'
        ];

        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est obligatoire.";
            }
        }

        // Vérification des fichiers obligatoires selon le niveau
        $niveau = $_POST['niveau'] ?? '';
        $docs_requis = getDocumentsRequises($niveau);
        
        foreach ($docs_requis as $doc) {
            if (empty($_FILES[$doc]['name'])) {
                $errors[] = "Le document $doc est obligatoire.";
            }
        }

        // Vérification du certificat de scolarité
        if (empty($_FILES['certificat_scolarite']['name'])) {
            $errors[] = "Le certificat de scolarité de l'année en cours est obligatoire.";
        }

        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
        } else {
            // Récupération des données
            $specialite = $_POST['specialite'] ?? '';
            $programme_langue = $_POST['programme_langue'] ?? '';
            $certificat_type = $_POST['certificat_type'] ?? '';
            $certificat_score = $_POST['certificat_score'] ?? '';
            $nom = $_POST['nom'] ?? '';
            $prenom = $_POST['prenom'] ?? '';
            $date_naissance = $_POST['date_naissance'] ?? '';
            $nationalite = $_POST['nationalite'] ?? '';
            $email = $_POST['email'] ?? '';
            $telephone = $_POST['telephone'] ?? '';
            $niveau = $_POST['niveau'] ?? '';
            $commentaire = $_POST['commentaire'] ?? '';
            
            $user_id = $_SESSION['user_id'] ?? 0;

            // Insertion dans la base de données
            $stmt = $pdo->prepare("INSERT INTO demandes_turquie 
                (user_id, specialite, programme_langue, certificat_type, certificat_score,
                nom, prenom, date_naissance, nationalite, email, telephone, niveau, commentaire, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
            
            $stmt->execute([
                $user_id, $specialite, $programme_langue, $certificat_type, $certificat_score,
                $nom, $prenom, $date_naissance, $nationalite, $email, $telephone, $niveau, $commentaire
            ]);

            $demande_id = $pdo->lastInsertId();

            // Dossier uploads
            $uploadDir = __DIR__ . "/../../../uploads/turquie/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            // Fonction pour sauvegarder les fichiers
            function saveFile($file, $type, $demande_id, $pdo, $uploadDir) {
                if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                    $filename = uniqid() . "_" . basename($file['name']);
                    $filepath = $uploadDir . $filename;
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $stmt = $pdo->prepare("INSERT INTO demandes_turquie_fichiers 
                            (demande_id, type_fichier, chemin_fichier, date_upload) 
                            VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$demande_id, $type, $filename]);
                    }
                }
            }

            // Sauvegarde des fichiers selon le niveau
            foreach ($docs_requis as $doc_type) {
                if (isset($_FILES[$doc_type])) {
                    saveFile($_FILES[$doc_type], $doc_type, $demande_id, $pdo, $uploadDir);
                }
            }

            // Sauvegarde du certificat de scolarité
            saveFile($_FILES['certificat_scolarite'], 'certificat_scolarite', $demande_id, $pdo, $uploadDir);

            // Sauvegarde du certificat de langue si fourni
            if (!empty($_FILES['certificat_file']['name'])) {
                saveFile($_FILES['certificat_file'], 'certificat_langue', $demande_id, $pdo, $uploadDir);
            }

            // Sauvegarde des documents supplémentaires
            if (isset($_FILES['documents_supplementaires'])) {
                foreach ($_FILES['documents_supplementaires']['name'] as $index => $name) {
                    if (!empty($name)) {
                        $file = [
                            'name' => $name,
                            'type' => $_FILES['documents_supplementaires']['type'][$index],
                            'tmp_name' => $_FILES['documents_supplementaires']['tmp_name'][$index],
                            'error' => $_FILES['documents_supplementaires']['error'][$index],
                            'size' => $_FILES['documents_supplementaires']['size'][$index]
                        ];
                        saveFile($file, 'document_supplementaire', $demande_id, $pdo, $uploadDir);
                    }
                }
            }

            // Redirection vers la confirmation
            header("Location: confirmation_turquie.php?id=" . $demande_id);
            exit;
        }
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonction pour déterminer les documents requis selon le niveau
function getDocumentsRequises($niveau) {
    switch($niveau) {
        case 'bac':
            return ['releve_2nde', 'releve_1ere', 'releve_terminale', 'releve_bac'];
        case 'l1':
            return ['releve_2nde', 'releve_1ere', 'releve_terminale', 'releve_bac', 'diplome_bac'];
        case 'l2':
            return ['releve_bac', 'diplome_bac', 'releve_l1'];
        case 'l3':
            return ['releve_bac', 'diplome_bac', 'releve_l1', 'releve_l2'];
        case 'master':
            return ['releve_bac', 'diplome_bac', 'releve_l1', 'releve_l2', 'releve_l3', 'diplome_licence'];
        default:
            return [];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulaire d'Études - Turquie</title>
  <style>
    :root {
      --primary-color: rgba(227, 10, 23, 0.8);
      --primary-hover: rgba(199, 0, 11, 0.8);
      --secondary-color: #f8f9fa;
      --text-color: #333;
      --light-gray: #e9ecef;
      --border-radius: 8px;
      --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
      --success-color: #28a745;
      --turkey-white: #FFFFFF;
      --turkey-red: #E30A17;
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
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      padding: 20px;
      min-height: 100vh;
    }
    
    .container {
      max-width: 1000px;
      margin: 0 auto;
      background: white;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
    }
    
    header {
      background: var(--primary-color);
      color: white;
      padding: 25px;
      text-align: center;
      position: relative;
    }
    
    header::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 5px;
      background: linear-gradient(90deg, var(--turkey-white), #f0f0f0, var(--turkey-white));
    }
    
    h2 {
      margin: 0;
      font-size: 1.8rem;
    }
    
    h3 {
      margin-bottom: 15px;
      padding-bottom: 8px;
      border-bottom: 2px solid var(--primary-color);
      color: var(--primary-color);
      display: flex;
      align-items: center;
    }
    
    h3 i {
      margin-right: 10px;
    }
    
    form {
      padding: 25px;
    }
    
    .section {
      margin-bottom: 30px;
      padding: 20px;
      background: var(--light-gray);
      border-radius: var(--border-radius);
      transition: var(--transition);
      position: relative;
    }
    
    .section::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 5px;
      height: 100%;
      background: var(--primary-color);
      border-radius: var(--border-radius) 0 0 var(--border-radius);
    }
    
    .section:hover {
      box-shadow: 0 0 0 2px var(--primary-color);
      transform: translateY(-3px);
    }
    
    .form-group {
      margin-bottom: 15px;
    }
    
    label {
      display: block;
      margin-bottom: 5px;
      font-weight: 600;
    }
    
    input, select, textarea {
      width: 100%;
      padding: 12px;
      border: 1px solid #ced4da;
      border-radius: var(--border-radius);
      font-size: 16px;
      transition: var(--transition);
    }
    
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(227, 10, 23, 0.2);
    }
    
    .btn {
      background: var(--primary-color);
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      transition: var(--transition);
      display: inline-block;
      margin: 10px 5px;
    }
    
    .btn:hover {
      background: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .btn-secondary {
      background: #6c757d;
    }
    
    .btn-secondary:hover {
      background: #5a6268;
    }
    
    .file-input-container {
      position: relative;
      margin-bottom: 15px;
    }
    
    .required::after {
      content: " *";
      color: var(--primary-color);
    }
    
    .service-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      margin-top: 15px;
    }
    
    .service-card {
      background: white;
      border: 2px solid #ddd;
      border-radius: var(--border-radius);
      padding: 25px 20px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      position: relative;
    }
    
    .service-card:hover {
      border-color: var(--primary-color);
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .service-card.selected {
      border-color: var(--primary-color);
      background-color: rgba(227, 10, 23, 0.05);
    }
    
    .service-card i {
      font-size: 2.5rem;
      color: var(--primary-color);
      margin-bottom: 15px;
    }
    
    .service-card h4 {
      margin: 10px 0 5px;
      color: var(--primary-color);
      font-size: 1.2rem;
    }
    
    .hidden {
      display: none;
    }
    
    .language-certificate {
      margin-top: 20px;
      padding: 15px;
      background: white;
      border-radius: var(--border-radius);
      border-left: 4px solid var(--primary-color);
    }
    
    .test-option {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
    }
    
    .test-option-btn {
      flex: 1;
      padding: 15px;
      border: 2px solid #ddd;
      border-radius: var(--border-radius);
      background: white;
      cursor: pointer;
      transition: var(--transition);
      text-align: center;
    }
    
    .test-option-btn:hover {
      border-color: var(--primary-color);
    }
    
    .test-option-btn.selected {
      border-color: var(--primary-color);
      background-color: rgba(227, 10, 23, 0.05);
    }
    
    .supplementary-docs {
      margin-top: 20px;
    }
    
    .doc-item {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
      align-items: center;
    }
    
    .doc-item input[type="file"] {
      flex: 1;
    }
    
    .remove-doc {
      background: #dc3545;
      color: white;
      border: none;
      border-radius: var(--border-radius);
      padding: 8px 12px;
      cursor: pointer;
    }
    
    .remove-doc:hover {
      background: #c82333;
    }
    
    .submit-buttons {
      text-align: center;
      margin-top: 30px;
      padding: 20px;
      border-top: 1px solid #ddd;
    }
    
    .turkey-flag {
      position: absolute;
      top: 15px;
      right: 15px;
      width: 80px;
      height: 56px;
      background: white;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    
    .turkey-flag::before {
      content: "";
      position: absolute;
      width: 24px;
      height: 24px;
      background: var(--turkey-red);
      border-radius: 50%;
    }
    
    .turkey-flag::after {
      content: "";
      position: absolute;
      width: 8px;
      height: 8px;
      background: white;
      border-radius: 50%;
      transform: rotate(30deg);
      box-shadow: -4px 6px 0 white, -6px 2px 0 white, -4px -5px 0 white, -1px -6px 0 white, 
                  4px -5px 0 white, 6px 0px 0 white, 4px 6px 0 white, 0px 6px 0 white;
    }
    
    @media (max-width: 768px) {
      body {
        padding: 10px;
      }
      
      form {
        padding: 15px;
      }
      
      .section {
        padding: 15px;
      }
      
      .btn {
        width: 100%;
        margin: 5px 0;
      }
      
      .service-grid {
        grid-template-columns: 1fr;
      }
      
      .turkey-flag {
        width: 60px;
        height: 42px;
        top: 10px;
        right: 10px;
      }
      
      .test-option {
        flex-direction: column;
      }
      
      .doc-item {
        flex-direction: column;
      }
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <header>
      <div class="turkey-flag"></div>
      <h2><i class="fas fa-graduation-cap"></i> Formulaire d'Études - Turquie</h2>
    </header>
    
    <form method="post" enctype="multipart/form-data">
      <!-- Section Programme -->
      <div class="section">
        <h3><i class="fas fa-book-open"></i> Choix du programme</h3>
        
        <div class="form-group">
          <label class="required">Entrez votre spécialité</label>
          <input type="text" name="specialite" placeholder="Ex: Médecine, Ingénierie, Commerce..." required>
        </div>

        <div class="form-group">
          <label class="required">Sélectionnez la langue du programme</label>
          <div class="service-grid">
            <div class="service-card" onclick="selectLanguage(this, 'turc')">
              <span>TR</span>
              <i class="fas fa-language"></i>
              <h4>Turc</h4>
            </div>
            <div class="service-card" onclick="selectLanguage(this, 'anglais')">
              <span>EN</span>
              <i class="fas fa-language"></i>
              <h4>Anglais</h4>
            </div>
          </div>
          <input type="hidden" id="programme_langue" name="programme_langue" required>
        </div>
        
        <!-- Section pour attestation de langue -->
        <div id="language-certificate-section" class="language-certificate hidden">
          <h4><i class="fas fa-file-certificate"></i> Test de niveau de langue</h4>
          
          <div class="test-option">
            <div class="test-option-btn" onclick="selectTestOption(this, 'oui')">
              <i class="fas fa-check-circle"></i>
              <h5>J'ai un test de langue</h5>
              <p>Charger mon certificat</p>
            </div>
            <div class="test-option-btn" onclick="selectTestOption(this, 'non')">
              <i class="fas fa-calendar-plus"></i>
              <h5>Je n'ai pas de test</h5>
              <p>Demander un test</p>
            </div>
          </div>
          
          <div id="test-details" class="hidden">
            <div class="form-group">
              <label class="required">Type de certificat</label>
              <select id="certificat_type" name="certificat_type">
                <option value="">-- Sélectionnez --</option>
                <option value="toefl">TOEFL</option>
                <option value="ielts">IELTS</option>
                <option value="autre">Autre</option>
              </select>
            </div>
            
            <div class="form-group">
              <label class="required">Score/Niveau</label>
              <input type="text" name="certificat_score" placeholder="Ex: B2, 550, 6.5...">
            </div>
            
            <div class="form-group">
              <label class="required">Certificat de test</label>
              <input type="file" name="certificat_file" accept=".pdf,.jpg,.jpeg,.png">
            </div>
          </div>
        </div>
      </div>

      <!-- Infos personnelles -->
      <div class="section">
        <h3><i class="fas fa-user"></i> Informations personnelles</h3>
        
        <div class="form-group">
          <label class="required">Nom</label>
          <input type="text" name="nom" required>
        </div>
        
        <div class="form-group">
          <label class="required">Prénom</label>
          <input type="text" name="prenom" required>
        </div>
        
        <div class="form-group">
          <label class="required">Date de naissance</label>
          <input type="date" name="date_naissance" required max="<?php echo date('Y-m-d', strtotime('-16 years')); ?>">
        </div>
        
        <div class="form-group">
          <label class="required">Nationalité</label>
          <input type="text" name="nationalite" required>
        </div>
        
        <div class="form-group">
          <label class="required">Email</label>
          <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
          <label class="required">Téléphone</label>
          <input type="tel" name="telephone" required>
        </div>
      </div>

      <!-- Niveau d'études -->
      <div class="section">
        <h3><i class="fas fa-graduation-cap"></i> Niveau d'études</h3>
        
        <div class="form-group">
          <label class="required">Choisissez votre niveau</label>
          <select name="niveau" required onchange="showDocs()">
            <option value="">-- Sélectionnez --</option>
            <option value="bac">Bac</option>
            <option value="l1">Licence 1</option>
            <option value="l2">Licence 2</option>
            <option value="l3">Licence 3</option>
            <option value="master">Master</option>
          </select>
        </div>
      </div>

      <!-- Documents dynamiques -->
      <div class="section">
        <h3><i class="fas fa-file-alt"></i> Documents requis</h3>
        
        <!-- Certificat de scolarité obligatoire -->
        <div class="form-group">
          <label class="required">Certificat de scolarité (année en cours)</label>
          <input type="file" name="certificat_scolarite" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
        
        <div id="docsContainer"></div>
        
        <!-- Documents supplémentaires -->
        <div class="supplementary-docs">
          <h4><i class="fas fa-plus-circle"></i> Documents supplémentaires</h4>
          <div id="supplementaryDocsContainer">
            <!-- Les documents supplémentaires seront ajoutés ici dynamiquement -->
          </div>
          <button type="button" class="btn btn-secondary" onclick="addSupplementaryDoc()">
            <i class="fas fa-plus"></i> Ajouter un document
          </button>
        </div>
      </div>

      <!-- Commentaire -->
      <div class="section">
        <h3><i class="fas fa-comment"></i> Commentaire</h3>
        <div class="form-group">
          <label for="commentaire">Commentaire général</label>
          <textarea name="commentaire" rows="4" placeholder="Informations supplémentaires..."></textarea>
        </div>
      </div>
      
      <!-- Boutons de soumission -->
      <div class="submit-buttons">
        <button type="submit" class="btn">
          <i class="fas fa-paper-plane"></i> Envoyer ma demande
        </button>
      </div>
    </form>
  </div>

  <script>
    // Configuration des documents par niveau
    const configDocs = {
      bac: [
        { label: "Relevé de notes 1ère année", name: "releve_2nde", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes 2ème année", name: "releve_1ere", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Terminale", name: "releve_terminale", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Bac", name: "releve_bac", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      l1: [
        { label: "Relevé de notes 1ère année", name: "releve_2nde", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes 2ème année", name: "releve_1ere", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Terminale", name: "releve_terminale", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Bac", name: "releve_bac", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      l2: [
        { label: "Relevé de notes Bac", name: "releve_bac", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L1", name: "releve_l1", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      l3: [
        { label: "Relevé de notes Bac", name: "releve_bac", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L1", name: "releve_l1", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L2", name: "releve_l2", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      master: [
        { label: "Relevé de notes Bac", name: "releve_bac", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L1", name: "releve_l1", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L2", name: "releve_l2", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L3", name: "releve_l3", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Licence", name: "diplome_licence", accept: ".pdf,.jpg,.jpeg,.png" }
      ]
    };

    let supplementaryDocCount = 0;

    // Fonction pour sélectionner la langue
    function selectLanguage(card, langue) {
      document.querySelectorAll('.service-card').forEach(el => el.classList.remove('selected'));
      card.classList.add('selected');
      document.getElementById('programme_langue').value = langue;
      
      const certificateSection = document.getElementById('language-certificate-section');
      if (langue === 'anglais') {
        certificateSection.classList.remove('hidden');
      } else {
        certificateSection.classList.add('hidden');
      }
    }

    // Fonction pour sélectionner l'option de test
    function selectTestOption(btn, option) {
      document.querySelectorAll('.test-option-btn').forEach(el => el.classList.remove('selected'));
      btn.classList.add('selected');
      
      const testDetails = document.getElementById('test-details');
      if (option === 'oui') {
        testDetails.classList.remove('hidden');
      } else {
        testDetails.classList.add('hidden');
        // Rediriger vers la page de demande de test
        window.open('/babylone/public/test_de_langue.php', '_blank');
      }
    }

    // Afficher les documents en fonction du niveau
    function showDocs() {
      let niveau = document.querySelector("select[name='niveau']").value;
      let container = document.getElementById("docsContainer");
      container.innerHTML = "";
      
      if (configDocs[niveau]) {
        configDocs[niveau].forEach(doc => {
          container.innerHTML += `
            <div class="form-group file-input-container">
              <label class="required">${doc.label}</label>
              <input type="file" name="${doc.name}" accept="${doc.accept}" required>
            </div>
          `;
        });
      }
    }

    // Ajouter un document supplémentaire
    function addSupplementaryDoc() {
      supplementaryDocCount++;
      const container = document.getElementById('supplementaryDocsContainer');
      const docDiv = document.createElement('div');
      docDiv.className = 'doc-item';
      docDiv.innerHTML = `
        <input type="file" name="documents_supplementaires[]" accept=".pdf,.jpg,.jpeg,.png">
        <button type="button" class="remove-doc" onclick="this.parentElement.remove()">
          <i class="fas fa-times"></i>
        </button>
      `;
      container.appendChild(docDiv);
    }

    // Validation du formulaire
    document.querySelector('form').addEventListener('submit', function(e) {
      const langue = document.getElementById('programme_langue').value;
      
      if (!langue) {
        e.preventDefault();
        alert("Veuillez sélectionner une langue de programme.");
        return;
      }
      
      if (langue === 'anglais') {
        const testOption = document.querySelector('.test-option-btn.selected');
        if (!testOption) {
          e.preventDefault();
          alert("Veuillez sélectionner une option pour le test de langue.");
          return;
        }
        
        if (testOption.textContent.includes('J\'ai un test')) {
          const certificatType = document.querySelector("select[name='certificat_type']").value;
          const certificatScore = document.querySelector("input[name='certificat_score']").value;
          const certificatFile = document.querySelector("input[name='certificat_file']").value;
          
          if (!certificatType || !certificatScore || !certificatFile) {
            e.preventDefault();
            alert("Veuillez compléter les informations de certification de langue.");
            return;
          }
        }
      }
    });

    // Initialiser l'ajout de documents
    document.addEventListener('DOMContentLoaded', function() {
      addSupplementaryDoc(); // Ajouter un document supplémentaire par défaut
    });
  </script>
</body>
</html>