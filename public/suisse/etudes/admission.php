<?php
session_start();

// Connexion BDD
require_once __DIR__ . '/../../../config.php';

try {
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // 🔹 Champs obligatoires pour Suisse
        $required_fields = [
            'pays_etudes', 'niveau_etudes', 'domaine_etudes', 'nom', 'prenom', 
            'date_naissance', 'lieu_naissance', 'nationalite', 'adresse', 
            'telephone', 'email', 'num_passeport'
        ];

        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est obligatoire.";
            }
        }

        // 🔹 Vérification du passeport
        if (empty($_FILES['copie_passeport']['name'])) {
            $errors[] = "La copie du passeport est obligatoire.";
        }

        // 🔹 Vérification des fichiers obligatoires pour Master
        $niveau_etudes = $_POST['niveau_etudes'] ?? '';
        
        // Pour la Suisse, on accepte seulement Master pour le moment
        if ($niveau_etudes !== 'master1' && $niveau_etudes !== 'master2') {
            $errors[] = "Pour la Suisse, seuls les niveaux Master sont actuellement disponibles.";
        }
        
        $annees_requises = getAnneesRequises($niveau_etudes);
        $diplomes_requis = getDiplomesRequises($niveau_etudes);
        
        foreach ($annees_requises as $index => $annee) {
            $field_name = "releve_annee_" . ($index + 1);
            if (empty($_FILES[$field_name]['name'])) {
                $errors[] = "Le relevé de notes pour $annee est obligatoire.";
            }
        }
        
        foreach ($diplomes_requis as $index => $diplome) {
            $field_name = "diplome_" . ($index + 1);
            if (empty($_FILES[$field_name]['name'])) {
                $errors[] = "Le diplôme de $diplome est obligatoire.";
            }
        }

        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
        } else {
            // 🔹 Récupération des données Suisse
            $pays_etudes        = $_POST['pays_etudes'] ?? '';
            $niveau_etudes      = $_POST['niveau_etudes'] ?? '';
            $domaine_etudes     = $_POST['domaine_etudes'] ?? '';
            $nom_formation      = $_POST['nom_formation'] ?? '';
            $date_debut         = $_POST['date_debut'] ?? '';
            $langue_formation   = $_POST['langue_formation'] ?? '';
            
            $nom                = $_POST['nom'] ?? '';
            $prenom             = $_POST['prenom'] ?? '';
            $date_naissance     = $_POST['date_naissance'] ?? '';
            $lieu_naissance     = $_POST['lieu_naissance'] ?? '';
            $nationalite        = $_POST['nationalite'] ?? '';
            $adresse            = $_POST['adresse'] ?? '';
            $telephone          = $_POST['telephone'] ?? '';
            $email              = $_POST['email'] ?? '';
            
            $num_passeport      = $_POST['num_passeport'] ?? '';
            
            // 🔹 Tests de langues (spécifiques à la Suisse)
            $tests_allemand     = $_POST['tests_allemand'] ?? 'non';
            $tests_francais     = $_POST['tests_francais'] ?? 'non';
            $tests_anglais      = $_POST['tests_anglais'] ?? 'non';
            $tests_italien      = $_POST['tests_italien'] ?? 'non';
            
            // 🔹 Récupération des informations des années
            $releves_annees_json = json_encode($annees_requises);
            
            $user_id = $_SESSION['user_id'] ?? 0;

            // 🔹 Insertion dans `demandes_suisse`
            $stmt = $pdo->prepare("INSERT INTO demandes_suisse 
                (user_id, pays_etudes, niveau_etudes, domaine_etudes, nom_formation, 
                date_debut, langue_formation, nom, prenom, date_naissance, lieu_naissance, 
                nationalite, adresse, telephone, email, num_passeport, tests_allemand, 
                tests_francais, tests_anglais, tests_italien, releves_annees, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
            
            $stmt->execute([
                $user_id, $pays_etudes, $niveau_etudes, $domaine_etudes, $nom_formation,
                $date_debut, $langue_formation, $nom, $prenom, $date_naissance, $lieu_naissance, 
                $nationalite, $adresse, $telephone, $email, $num_passeport, $tests_allemand, 
                $tests_francais, $tests_anglais, $tests_italien, $releves_annees_json
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
                        $stmt = $pdo->prepare("INSERT INTO demandes_suisse_fichiers 
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

            // 🔹 Traitement fichiers des relevés par année selon le niveau
            foreach ($annees_requises as $index => $annee) {
                $field_name = "releve_annee_" . ($index + 1);
                if (isset($_FILES[$field_name]) && !empty($_FILES[$field_name]['name'])) {
                    saveFile($_FILES[$field_name], $field_name, $demande_id, $pdo, $uploadDir);
                }
            }

            // 🔹 Traitement diplômes selon le niveau
            foreach ($diplomes_requis as $index => $diplome) {
                $field_name = "diplome_" . ($index + 1);
                if (isset($_FILES[$field_name]) && !empty($_FILES[$field_name]['name'])) {
                    saveFile($_FILES[$field_name], $field_name, $demande_id, $pdo, $uploadDir);
                }
            }

            // 🔹 Traitement certificat de scolarité (optionnel)
            if (isset($_FILES['certificat_scolarite']) && !empty($_FILES['certificat_scolarite']['name'])) {
                saveFile($_FILES['certificat_scolarite'], 'certificat_scolarite', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Traitement fichiers langues si test passé
            if ($tests_allemand !== 'non' && isset($_FILES['attestation_allemand']) && !empty($_FILES['attestation_allemand']['name'])) {
                saveFile($_FILES['attestation_allemand'], 'attestation_allemand', $demande_id, $pdo, $uploadDir);
            }

            if ($tests_francais !== 'non' && isset($_FILES['attestation_francais']) && !empty($_FILES['attestation_francais']['name'])) {
                saveFile($_FILES['attestation_francais'], 'attestation_francais', $demande_id, $pdo, $uploadDir);
            }

            if ($tests_anglais !== 'non' && isset($_FILES['attestation_anglais']) && !empty($_FILES['attestation_anglais']['name'])) {
                saveFile($_FILES['attestation_anglais'], 'attestation_anglais', $demande_id, $pdo, $uploadDir);
            }

            if ($tests_italien !== 'non' && isset($_FILES['attestation_italien']) && !empty($_FILES['attestation_italien']['name'])) {
                saveFile($_FILES['attestation_italien'], 'attestation_italien', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Traitement documents supplémentaires
            if (isset($_FILES['documents_supplementaires'])) {
                foreach ($_FILES['documents_supplementaires']['name'] as $key => $name) {
                    if (!empty($name)) {
                        $file = [
                            'name' => $name,
                            'type' => $_FILES['documents_supplementaires']['type'][$key],
                            'tmp_name' => $_FILES['documents_supplementaires']['tmp_name'][$key],
                            'error' => $_FILES['documents_supplementaires']['error'][$key],
                            'size' => $_FILES['documents_supplementaires']['size'][$key]
                        ];
                        $type_doc = $_POST['types_documents_supplementaires'][$key] ?? 'document_supplementaire';
                        saveFile($file, $type_doc, $demande_id, $pdo, $uploadDir);
                    }
                }
            }

            // 🔹 Redirection confirmation
            header("Location: confirmation_suisse.php?id=" . $demande_id);
            exit;
        }
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// 🔹 Fonction pour déterminer les années requises selon le niveau
function getAnneesRequises($niveau) {
    switch($niveau) {
        case 'master1':
            return ['Baccalauréat', 'Licence 1', 'Licence 2', 'Licence 3'];
        case 'master2':
            return ['Baccalauréat', 'Licence 1', 'Licence 2', 'Licence 3', 'Master 1'];
        default:
            return ['Baccalauréat', 'Licence 1', 'Licence 2', 'Licence 3'];
    }
}

// 🔹 Fonction pour déterminer les diplômes requis selon le niveau
function getDiplomesRequises($niveau) {
    switch($niveau) {
        case 'master1':
            return ['Baccalauréat', 'Licence'];
        case 'master2':
            return ['Baccalauréat', 'Licence'];
        default:
            return ['Baccalauréat', 'Licence'];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulaire d'Études en Suisse</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #D52B1E;
      --secondary-color: #FFFFFF;
      --accent-color: #F5F5F5;
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
      background: linear-gradient(135deg, #D52B1E, #FFFFFF);
      color: #333;
      padding: 25px 30px;
      text-align: center;
      border-bottom: 5px solid #D52B1E;
    }
    
    header h1 {
      margin-bottom: 10px;
      font-size: 1.8rem;
      color: #D52B1E;
    }
    
    header p {
      color: #666;
    }
    
    .alert-info {
      background: #e7f3ff;
      border-left: 4px solid #2196F3;
      padding: 15px;
      margin: 20px;
      border-radius: var(--border-radius);
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
      color: #D52B1E;
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
      box-shadow: 0 0 0 3px rgba(213, 43, 30, 0.2);
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
      background: linear-gradient(to right, #D52B1E, #FF6B6B);
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
      background: linear-gradient(to right, #B22222, #E05555);
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
      background: var(--accent-color);
      color: #666;
      font-size: 0.9rem;
      border-top: 1px solid var(--border-color);
    }
    
    .conditional-section {
      border-left: 4px solid #D52B1E;
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
    
    .section-title {
      background: var(--primary-color);
      color: white;
      padding: 10px 15px;
      border-radius: var(--border-radius);
      margin-bottom: 15px;
      font-weight: 600;
    }
    
    .niveau-info {
      background: var(--accent-color);
      padding: 15px;
      border-radius: var(--border-radius);
      margin-bottom: 20px;
      border-left: 4px solid var(--primary-color);
    }
    
    .test-option {
      display: grid;
      grid-template-columns: auto 1fr auto;
      gap: 15px;
      margin: 15px 0;
      padding: 15px;
      background: white;
      border-radius: var(--border-radius);
      border: 1px solid var(--border-color);
      align-items: center;
    }
    
    .test-option input[type="radio"] {
      width: auto;
      margin-top: 0;
    }
    
    .test-file {
      display: none;
      margin-top: 10px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: var(--border-radius);
      border: 1px dashed var(--border-color);
    }
    
    .test-file.active {
      display: block;
    }
    
    .btn-test {
      padding: 8px 15px;
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: var(--border-radius);
      text-decoration: none;
      font-weight: 600;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 0.9rem;
      cursor: pointer;
    }
    
    .btn-test:hover {
      background: #B22222;
      transform: translateY(-1px);
    }
    
    .documents-supplementaires {
      margin-top: 20px;
    }
    
    .document-supplementaire {
      display: grid;
      grid-template-columns: 1fr 2fr auto;
      gap: 15px;
      margin-bottom: 15px;
      align-items: end;
    }
    
    .btn-add-doc {
      background: var(--success-color);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-weight: 600;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      gap: 8px;
      margin-top: 15px;
    }
    
    .btn-add-doc:hover {
      background: #218838;
      transform: translateY(-2px);
    }
    
    .btn-remove {
      background: var(--error-color);
      color: white;
      border: none;
      padding: 12px;
      border-radius: var(--border-radius);
      cursor: pointer;
      transition: var(--transition);
    }
    
    .btn-remove:hover {
      background: #C82333;
    }
    
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
        gap: 0;
      }
      
      .form-content {
        padding: 20px;
      }
      
      .test-option {
        grid-template-columns: 1fr;
        gap: 10px;
      }
      
      .document-supplementaire {
        grid-template-columns: 1fr;
        gap: 10px;
      }
    }
  </style>
  <script>
    function toggleTestAllemand() {
      const hasTest = document.querySelector('input[name="tests_allemand"]:checked').value;
      const fileSection = document.getElementById("attestation_allemand_section");
      const btnSection = document.getElementById("btn_test_allemand");
      
      if (hasTest === "non") {
        fileSection.style.display = "none";
        fileSection.classList.remove("active");
        btnSection.style.display = "block";
        document.getElementById("attestation_allemand").value = "";
      } else {
        fileSection.style.display = "block";
        fileSection.classList.add("active");
        btnSection.style.display = "none";
      }
    }
    
    function toggleTestFrancais() {
      const hasTest = document.querySelector('input[name="tests_francais"]:checked').value;
      const fileSection = document.getElementById("attestation_francais_section");
      const btnSection = document.getElementById("btn_test_francais");
      
      if (hasTest === "non") {
        fileSection.style.display = "none";
        fileSection.classList.remove("active");
        btnSection.style.display = "block";
        document.getElementById("attestation_francais").value = "";
      } else {
        fileSection.style.display = "block";
        fileSection.classList.add("active");
        btnSection.style.display = "none";
      }
    }
    
    function toggleTestAnglais() {
      const hasTest = document.querySelector('input[name="tests_anglais"]:checked').value;
      const fileSection = document.getElementById("attestation_anglais_section");
      const btnSection = document.getElementById("btn_test_anglais");
      
      if (hasTest === "non") {
        fileSection.style.display = "none";
        fileSection.classList.remove("active");
        btnSection.style.display = "block";
        document.getElementById("attestation_anglais").value = "";
      } else {
        fileSection.style.display = "block";
        fileSection.classList.add("active");
        btnSection.style.display = "none";
      }
    }
    
    function toggleTestItalien() {
      const hasTest = document.querySelector('input[name="tests_italien"]:checked').value;
      const fileSection = document.getElementById("attestation_italien_section");
      const btnSection = document.getElementById("btn_test_italien");
      
      if (hasTest === "non") {
        fileSection.style.display = "none";
        fileSection.classList.remove("active");
        btnSection.style.display = "block";
        document.getElementById("attestation_italien").value = "";
      } else {
        fileSection.style.display = "block";
        fileSection.classList.add("active");
        btnSection.style.display = "none";
      }
    }
    
    function toggleParcoursAcademique() {
      const niveau = document.getElementById("niveau_etudes").value;
      const parcoursSection = document.getElementById("parcours_academique_section");
      
      if (niveau) {
        parcoursSection.style.display = "block";
        genererChampsParcours(niveau);
      } else {
        parcoursSection.style.display = "none";
      }
    }
    
    function genererChampsParcours(niveau) {
      const container = document.getElementById("parcours_academique_container");
      container.innerHTML = "";
      
      let annees = [];
      let diplomes = [];
      
      // Définir les années et diplômes selon le niveau (Master uniquement)
      switch(niveau) {
        case "master1":
          annees = ["Baccalauréat", "Licence 1", "Licence 2", "Licence 3"];
          diplomes = ["Baccalauréat", "Licence"];
          break;
        case "master2":
          annees = ["Baccalauréat", "Licence 1", "Licence 2", "Licence 3", "Master 1"];
          diplomes = ["Baccalauréat", "Licence"];
          break;
        default:
          // Pour la Suisse, on n'accepte que Master
          container.innerHTML = `
            <div class="alert-info">
              <strong>Information importante :</strong> Pour la Suisse, seuls les niveaux Master sont actuellement disponibles.
              Les demandes pour d'autres niveaux ne seront pas traitées.
            </div>
          `;
          return;
      }
      
      // Afficher les informations du niveau
      const infoSection = document.createElement("div");
      infoSection.className = "niveau-info";
      infoSection.innerHTML = `<strong>Niveau sélectionné :</strong> ${document.getElementById("niveau_etudes").options[document.getElementById("niveau_etudes").selectedIndex].text}`;
      container.appendChild(infoSection);
      
      // Générer les champs pour les relevés de notes
      if (annees.length > 0) {
        const relevesSection = document.createElement("div");
        relevesSection.innerHTML = '<div class="section-title">📊 Relevés de notes (obligatoires)</div>';
        
        annees.forEach((annee, index) => {
          const anneeNum = index + 1;
          const section = document.createElement("div");
          section.className = "annee-section";
          section.innerHTML = `
            <div class="form-group">
              <label class="required">Relevé de notes - ${annee}</label>
              <input type="file" name="releve_annee_${anneeNum}" class="file-input" accept=".pdf,.jpg,.png" required>
              <span class="file-hint">Relevé de notes de ${annee} (PDF, JPG, PNG - max 5MB)</span>
            </div>
          `;
          relevesSection.appendChild(section);
        });
        container.appendChild(relevesSection);
      }
      
      // Générer les champs pour les diplômes
      if (diplomes.length > 0) {
        const diplomesSection = document.createElement("div");
        diplomesSection.innerHTML = '<div class="section-title">🎓 Copie des diplômes (obligatoires)</div>';
        
        diplomes.forEach((diplome, index) => {
          const diplomeNum = index + 1;
          const section = document.createElement("div");
          section.className = "annee-section";
          section.innerHTML = `
            <div class="form-group">
              <label class="required">Diplôme - ${diplome}</label>
              <input type="file" name="diplome_${diplomeNum}" class="file-input" accept=".pdf,.jpg,.png" required>
              <span class="file-hint">Copie du diplôme de ${diplome} (PDF, JPG, PNG - max 5MB)</span>
            </div>
          `;
          diplomesSection.appendChild(section);
        });
        container.appendChild(diplomesSection);
      }
      
      // Ajouter le certificat de scolarité (optionnel)
      const certificatSection = document.createElement("div");
      certificatSection.innerHTML = `
        <div class="section-title">📚 Certificat de scolarité (optionnel)</div>
        <div class="annee-section">
          <div class="form-group">
            <label class="optional">Certificat de scolarité de l'année en cours</label>
            <input type="file" name="certificat_scolarite" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Certificat de l'établissement actuel (PDF, JPG, PNG - max 5MB)</span>
          </div>
        </div>
      `;
      container.appendChild(certificatSection);
    }
    
    function ajouterDocumentSupplementaire() {
      const container = document.getElementById('documents-supplementaires-container');
      const index = container.children.length;
      
      const div = document.createElement('div');
      div.className = 'document-supplementaire';
      div.innerHTML = `
        <div>
          <label>Type de document</label>
          <input type="text" name="types_documents_supplementaires[]" placeholder="Ex: Certificat, Attestation...">
        </div>
        <div>
          <label>Document</label>
          <input type="file" name="documents_supplementaires[]" class="file-input" accept=".pdf,.jpg,.png">
        </div>
        <button type="button" class="btn-remove" onclick="supprimerDocument(this)">
          <i class="fas fa-times"></i>
        </button>
      `;
      
      container.appendChild(div);
    }
    
    function supprimerDocument(button) {
      button.closest('.document-supplementaire').remove();
    }
    
    function validateForm() {
      const niveau = document.getElementById("niveau_etudes").value;
      if (!niveau) {
        alert("Veuillez sélectionner votre niveau d'études");
        return false;
      }
      
      // Vérifier que c'est un niveau Master
      if (niveau !== 'master1' && niveau !== 'master2') {
        alert("Pour la Suisse, seuls les niveaux Master sont actuellement disponibles.");
        return false;
      }
      
      return true;
    }
    
    // Initialiser les sections au chargement
    document.addEventListener('DOMContentLoaded', function() {
      toggleTestAllemand();
      toggleTestFrancais();
      toggleTestAnglais();
      toggleTestItalien();
      toggleParcoursAcademique();
    });
  </script>
</head>
<body>
<div class="container">
  <header>
    <h1><i class="fas fa-graduation-cap"></i> Demande d'Études en Suisse</h1>
    <p>Formulaire d'inscription pour études en Suisse (Niveaux Master uniquement)</p>
  </header>

  <div class="alert-info">
    <strong>⚠️ Information importante :</strong> Pour le moment, seules les demandes pour les niveaux Master sont acceptées pour la Suisse. Les demandes Licence ne sont pas encore disponibles.
  </div>

  <div class="form-content">
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
      
      <!-- Projet d'études -->
      <div class="form-section">
        <h3><i class="fas fa-book-open"></i> Projet d'études</h3>
        <div class="form-group">
          <label class="required">Pays d'études</label>
          <input type="text" name="pays_etudes" required value="Suisse" readonly>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Niveau d'études visé</label>
            <select id="niveau_etudes" name="niveau_etudes" required onchange="toggleParcoursAcademique()">
              <option value="">-- Sélectionnez votre niveau --</option>
              <option value="master1">Master 1ère année</option>
              <option value="master2">Master 2ème année</option>
            </select>
          </div>
          <div class="form-group">
            <label class="required">Domaine d'études</label>
            <input type="text" name="domaine_etudes" required placeholder="Business, Informatique, Médecine, Hôtellerie...">
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Nom de la formation</label>
          <input type="text" name="nom_formation" required placeholder="Master in Business, MSc in Computer Science...">
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Date de début des études</label>
            <input type="date" name="date_debut" required>
          </div>
          <div class="form-group">
            <label class="required">Langue de la formation</label>
            <select name="langue_formation" required>
              <option value="">-- Sélectionnez --</option>
              <option value="allemand">Allemand</option>
              <option value="francais">Français</option>
              <option value="anglais">Anglais</option>
              <option value="italien">Italien</option>
              <option value="bilingue">Bilingue</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Parcours académique (Dynamique) -->
      <div id="parcours_academique_section" class="form-section hidden">
        <h3><i class="fas fa-university"></i> Documents académiques</h3>
        <div id="parcours_academique_container">
          <!-- Les champs seront générés dynamiquement selon le niveau -->
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
      </div>

      <!-- Passeport -->
      <div class="form-section">
        <h3><i class="fas fa-passport"></i> Passeport</h3>
        <div class="form-group">
          <label class="required">Numéro de passeport</label>
          <input type="text" name="num_passeport" required>
        </div>
        
        <div class="form-group">
          <label class="required">Copie du passeport</label>
          <input type="file" name="copie_passeport" class="file-input" accept=".pdf,.jpg,.png" required>
          <span class="file-hint">Pages avec photo et informations personnelles (max 5MB)</span>
        </div>
      </div>

      <!-- Tests de langues -->
      <div class="form-section">
        <h3><i class="fas fa-language"></i> Tests de langues</h3>
        
        <!-- Allemand -->
        <div class="test-option">
          <input type="radio" name="tests_allemand" id="test_allemand_non" value="non" checked onchange="toggleTestAllemand()">
          <label for="test_allemand_non" style="margin-top: 0;">Je n'ai pas de test d'allemand</label>
          <div id="btn_test_allemand">
            <a href="../../test_de_langue.php?langue=allemand" class="btn-test" target="_blank">
              <i class="fas fa-external-link-alt"></i> Demander un test
            </a>
          </div>
        </div>
        <div class="test-option">
          <input type="radio" name="tests_allemand" id="test_allemand_oui" value="oui" onchange="toggleTestAllemand()">
          <label for="test_allemand_oui" style="margin-top: 0;">J'ai un test d'allemand</label>
          <div></div>
        </div>
        
        <div id="attestation_allemand_section" class="test-file">
          <div class="form-group">
            <label>Attestation de test d'allemand</label>
            <input type="file" name="attestation_allemand" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
          </div>
        </div>
        
        <!-- Français -->
        <div class="test-option">
          <input type="radio" name="tests_francais" id="test_francais_non" value="non" checked onchange="toggleTestFrancais()">
          <label for="test_francais_non" style="margin-top: 0;">Je n'ai pas de test de français</label>
          <div id="btn_test_francais">
            <a href="../../test_de_langue.php?langue=francais" class="btn-test" target="_blank">
              <i class="fas fa-external-link-alt"></i> Demander un test
            </a>
          </div>
        </div>
        <div class="test-option">
          <input type="radio" name="tests_francais" id="test_francais_oui" value="oui" onchange="toggleTestFrancais()">
          <label for="test_francais_oui" style="margin-top: 0;">J'ai un test de français</label>
          <div></div>
        </div>
        
        <div id="attestation_francais_section" class="test-file">
          <div class="form-group">
            <label>Attestation de test de français</label>
            <input type="file" name="attestation_francais" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
          </div>
        </div>
        
        <!-- Anglais -->
        <div class="test-option">
          <input type="radio" name="tests_anglais" id="test_anglais_non" value="non" checked onchange="toggleTestAnglais()">
          <label for="test_anglais_non" style="margin-top: 0;">Je n'ai pas de test d'anglais</label>
          <div id="btn_test_anglais">
            <a href="../../test_de_langue.php?langue=anglais" class="btn-test" target="_blank">
              <i class="fas fa-external-link-alt"></i> Demander un test
            </a>
          </div>
        </div>
        <div class="test-option">
          <input type="radio" name="tests_anglais" id="test_anglais_oui" value="oui" onchange="toggleTestAnglais()">
          <label for="test_anglais_oui" style="margin-top: 0;">J'ai un test d'anglais</label>
          <div></div>
        </div>
        
        <div id="attestation_anglais_section" class="test-file">
          <div class="form-group">
            <label>Attestation de test d'anglais</label>
            <input type="file" name="attestation_anglais" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
          </div>
        </div>
        
        <!-- Italien -->
        <div class="test-option">
          <input type="radio" name="tests_italien" id="test_italien_non" value="non" checked onchange="toggleTestItalien()">
          <label for="test_italien_non" style="margin-top: 0;">Je n'ai pas de test d'italien</label>
          <div id="btn_test_italien">
            <a href="../../test_de_langue.php?langue=italien" class="btn-test" target="_blank">
              <i class="fas fa-external-link-alt"></i> Demander un test
            </a>
          </div>
        </div>
        <div class="test-option">
          <input type="radio" name="tests_italien" id="test_italien_oui" value="oui" onchange="toggleTestItalien()">
          <label for="test_italien_oui" style="margin-top: 0;">J'ai un test d'italien</label>
          <div></div>
        </div>
        
        <div id="attestation_italien_section" class="test-file">
          <div class="form-group">
            <label>Attestation de test d'italien</label>
            <input type="file" name="attestation_italien" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
          </div>
        </div>
      </div>

      <!-- Documents supplémentaires -->
      <div class="form-section">
        <h3><i class="fas fa-plus-circle"></i> Documents supplémentaires</h3>
        <p>Ajoutez ici tout autre document que vous souhaitez soumettre</p>
        
        <div class="documents-supplementaires">
          <div id="documents-supplementaires-container">
            <!-- Les documents supplémentaires seront ajoutés ici dynamiquement -->
          </div>
          
          <button type="button" class="btn-add-doc" onclick="ajouterDocumentSupplementaire()">
            <i class="fas fa-plus"></i> Ajouter un document
          </button>
        </div>
      </div>

      <!-- Bouton de soumission -->
      <div style="text-align: center; margin: 30px 0;">
        <button type="submit" class="btn-submit">
          <i class="fas fa-paper-plane"></i> Soumettre ma demande pour la Suisse
        </button>
      </div>
    </form>
  </div>

  <footer>
    <p><i class="fas fa-info-circle"></i> Tous les champs marqués d'un astérisque (*) sont obligatoires</p>
    <p>© 2025 Service d'Études en Suisse - Babylone Service</p>
  </footer>
</div>
</body>
</html>