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

        // 🔹 Champs obligatoires pour Italie
        $required_fields = [
            'pays_etudes', 'niveau_etudes', 'domaine_etudes', 'type_programme', 'nom', 'prenom', 
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

        // 🔹 Vérification des fichiers obligatoires selon le niveau
        $niveau_etudes = $_POST['niveau_etudes'] ?? '';
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
        
        if (needCertificatScolarite($niveau_etudes) && empty($_FILES['certificat_scolarite']['name'])) {
            $errors[] = "Le certificat de scolarité est obligatoire pour ce niveau.";
        }

        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
        } else {
            // 🔹 Récupération des données Italie
            $pays_etudes        = $_POST['pays_etudes'] ?? '';
            $niveau_etudes      = $_POST['niveau_etudes'] ?? '';
            $domaine_etudes     = $_POST['domaine_etudes'] ?? '';
            $type_programme     = $_POST['type_programme'] ?? '';
            $nom_formation      = $_POST['nom_formation'] ?? '';
            $date_debut         = $_POST['date_debut'] ?? '';
            
            $nom                = $_POST['nom'] ?? '';
            $prenom             = $_POST['prenom'] ?? '';
            $date_naissance     = $_POST['date_naissance'] ?? '';
            $lieu_naissance     = $_POST['lieu_naissance'] ?? '';
            $nationalite        = $_POST['nationalite'] ?? '';
            $adresse            = $_POST['adresse'] ?? '';
            $telephone          = $_POST['telephone'] ?? '';
            $email              = $_POST['email'] ?? '';
            
            $num_passeport      = $_POST['num_passeport'] ?? '';
            
            // 🔹 Tests de langues
            $tests_italien      = $_POST['tests_italien'] ?? 'non';
            $tests_anglais      = $_POST['tests_anglais'] ?? 'non';
            
            // 🔹 Récupération des informations des années
            $releves_annees_json = json_encode($annees_requises);
            
            // 🔹 Récupération des documents supplémentaires
            $documents_supplementaires = [];
            if (isset($_POST['doc_supp_type']) && is_array($_POST['doc_supp_type'])) {
                foreach ($_POST['doc_supp_type'] as $index => $type) {
                    if (!empty($type) && isset($_FILES['doc_supp_file']['name'][$index]) && !empty($_FILES['doc_supp_file']['name'][$index])) {
                        $documents_supplementaires[] = [
                            'type' => $type,
                            'file' => $_FILES['doc_supp_file']['name'][$index],
                            'tmp_name' => $_FILES['doc_supp_file']['tmp_name'][$index]
                        ];
                    }
                }
            }
            
            $user_id = $_SESSION['user_id'] ?? 0;

            // 🔹 Insertion dans `demandes_italie`
            $stmt = $pdo->prepare("INSERT INTO demandes_italie 
                (user_id, pays_etudes, niveau_etudes, domaine_etudes, type_programme, nom_formation, 
                date_debut, nom, prenom, date_naissance, lieu_naissance, nationalite, adresse, 
                telephone, email, num_passeport, tests_italien, tests_anglais, releves_annees, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')");
            
            $stmt->execute([
                $user_id, $pays_etudes, $niveau_etudes, $domaine_etudes, $type_programme, $nom_formation,
                $date_debut, $nom, $prenom, $date_naissance, $lieu_naissance, $nationalite, $adresse, 
                $telephone, $email, $num_passeport, $tests_italien, $tests_anglais, $releves_annees_json
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
                        $stmt = $pdo->prepare("INSERT INTO demandes_italie_fichiers 
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

            // 🔹 Traitement certificat de scolarité si nécessaire
            if (needCertificatScolarite($niveau_etudes) && isset($_FILES['certificat_scolarite']) && !empty($_FILES['certificat_scolarite']['name'])) {
                saveFile($_FILES['certificat_scolarite'], 'certificat_scolarite', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Traitement fichiers langues si test passé
            if ($tests_italien !== 'non' && isset($_FILES['attestation_italien']) && !empty($_FILES['attestation_italien']['name'])) {
                saveFile($_FILES['attestation_italien'], 'attestation_italien', $demande_id, $pdo, $uploadDir);
            }

            if ($tests_anglais !== 'non' && isset($_FILES['attestation_anglais']) && !empty($_FILES['attestation_anglais']['name'])) {
                saveFile($_FILES['attestation_anglais'], 'attestation_anglais', $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Traitement documents supplémentaires
            foreach ($documents_supplementaires as $doc) {
                $file = [
                    'name' => $doc['file'],
                    'tmp_name' => $doc['tmp_name'],
                    'error' => UPLOAD_ERR_OK
                ];
                saveFile($file, 'doc_supp_' . $doc['type'], $demande_id, $pdo, $uploadDir);
            }

            // 🔹 Redirection confirmation
            header("Location: confirmation_italie.php?id=" . $demande_id);
            exit;
        }
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// 🔹 Fonction pour déterminer les années requises selon le niveau
function getAnneesRequises($niveau) {
    switch($niveau) {
        case 'licence1':
            return ['Baccalauréat'];
        case 'licence2':
            return ['Baccalauréat', 'Licence 1'];
        case 'licence3':
            return ['Baccalauréat', 'Licence 1', 'Licence 2'];
        case 'master1':
            return ['Baccalauréat', 'Licence 1', 'Licence 2', 'Licence 3'];
        case 'master2':
            return ['Baccalauréat', 'Licence 1', 'Licence 2', 'Licence 3', 'Master 1'];
        case 'master2_termine':
            return ['Baccalauréat', 'Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2'];
        case 'doctorat':
            return ['Baccalauréat', 'Licence 1', 'Licence 2', 'Licence 3', 'Master 1', 'Master 2'];
        default:
            return ['Baccalauréat'];
    }
}

// 🔹 Fonction pour déterminer les diplômes requis selon le niveau
function getDiplomesRequises($niveau) {
    switch($niveau) {
        case 'licence1':
            return ['Baccalauréat'];
        case 'licence2':
            return ['Baccalauréat'];
        case 'licence3':
            return ['Baccalauréat'];
        case 'master1':
            return ['Baccalauréat', 'Licence'];
        case 'master2':
            return ['Baccalauréat', 'Licence'];
        case 'master2_termine':
            return ['Baccalauréat', 'Licence', 'Master'];
        case 'doctorat':
            return ['Baccalauréat', 'Licence', 'Master'];
        default:
            return ['Baccalauréat'];
    }
}

// 🔹 Fonction pour déterminer si le certificat de scolarité est nécessaire
function needCertificatScolarite($niveau) {
    return in_array($niveau, ['licence2', 'licence3', 'master2', 'master2_termine']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulaire d'Études en Italie</title>
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
      background: linear-gradient(135deg, #008C45, #CD212A);
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
    
    .btn-test {
      background: var(--primary-color);
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-size: 1rem;
      text-decoration: none;
      display: inline-block;
      text-align: center;
      margin-top: 10px;
      transition: var(--transition);
    }
    
    .btn-test:hover {
      background: #006B33;
      transform: translateY(-2px);
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
    
    .doc-supp-item {
      background: white;
      padding: 15px;
      border-radius: var(--border-radius);
      margin-bottom: 15px;
      border: 1px solid var(--border-color);
      display: flex;
      gap: 15px;
      align-items: end;
    }
    
    .doc-supp-item .form-group {
      flex: 1;
      margin-bottom: 0;
    }
    
    .remove-doc {
      background: var(--error-color);
      color: white;
      border: none;
      border-radius: var(--border-radius);
      padding: 10px 15px;
      cursor: pointer;
    }
    
    .remove-doc:hover {
      background: #c82333;
    }
    
    .test-demand-section {
      background: #e7f3ff;
      padding: 15px;
      border-radius: var(--border-radius);
      border: 1px solid #b3d9ff;
      margin-top: 15px;
    }
    
    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
        gap: 0;
      }
      
      .doc-supp-item {
        flex-direction: column;
        align-items: stretch;
      }
      
      .form-content {
        padding: 20px;
      }
    }
  </style>
  <script>
    function toggleTypeProgramme() {
      const typeProgramme = document.getElementById("type_programme").value;
      const sectionItalien = document.getElementById("test_italien_section");
      const sectionAnglais = document.getElementById("test_anglais_section");
      
      if (typeProgramme === 'italien') {
        sectionItalien.style.display = "block";
        sectionAnglais.style.display = "none";
      } else if (typeProgramme === 'anglais') {
        sectionItalien.style.display = "none";
        sectionAnglais.style.display = "block";
      } else {
        sectionItalien.style.display = "none";
        sectionAnglais.style.display = "none";
      }
    }
    
    function toggleTestItalien() {
      const hasTest = document.getElementById("tests_italien").value;
      const attestationSection = document.getElementById("attestation_italien_section");
      const demandeTestSection = document.getElementById("demande_test_italien_section");
      
      if (hasTest !== "non") {
        attestationSection.style.display = "block";
        demandeTestSection.style.display = "none";
      } else {
        attestationSection.style.display = "none";
        demandeTestSection.style.display = "block";
      }
    }
    
    function toggleTestAnglais() {
      const hasTest = document.getElementById("tests_anglais").value;
      const attestationSection = document.getElementById("attestation_anglais_section");
      const demandeTestSection = document.getElementById("demande_test_anglais_section");
      
      if (hasTest !== "non") {
        attestationSection.style.display = "block";
        demandeTestSection.style.display = "none";
      } else {
        attestationSection.style.display = "none";
        demandeTestSection.style.display = "block";
      }
    }
    
    function demanderTestLangue(type) {
      // Redirection vers la page de demande de test de langue
      window.location.href = `test_de_langue.php?type=${type}`;
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
      let besoinCertificat = false;
      
      // Définir les années et diplômes selon le niveau
      switch(niveau) {
        case "licence1":
          annees = ["Baccalauréat"];
          diplomes = ["Baccalauréat"];
          break;
        case "licence2":
          annees = ["Baccalauréat", "Licence 1"];
          diplomes = ["Baccalauréat"];
          besoinCertificat = true;
          break;
        case "licence3":
          annees = ["Baccalauréat", "Licence 1", "Licence 2"];
          diplomes = ["Baccalauréat"];
          besoinCertificat = true;
          break;
        case "master1":
          annees = ["Baccalauréat", "Licence 1", "Licence 2", "Licence 3"];
          diplomes = ["Baccalauréat", "Licence"];
          break;
        case "master2":
          annees = ["Baccalauréat", "Licence 1", "Licence 2", "Licence 3", "Master 1"];
          diplomes = ["Baccalauréat", "Licence"];
          besoinCertificat = true;
          break;
        case "master2_termine":
          annees = ["Baccalauréat", "Licence 1", "Licence 2", "Licence 3", "Master 1", "Master 2"];
          diplomes = ["Baccalauréat", "Licence", "Master"];
          besoinCertificat = true;
          break;
        case "doctorat":
          annees = ["Baccalauréat", "Licence 1", "Licence 2", "Licence 3", "Master 1", "Master 2"];
          diplomes = ["Baccalauréat", "Licence", "Master"];
          break;
        default:
          annees = ["Baccalauréat"];
          diplomes = ["Baccalauréat"];
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
      
      // Ajouter le certificat de scolarité si nécessaire
      if (besoinCertificat) {
        const certificatSection = document.createElement("div");
        certificatSection.innerHTML = `
          <div class="section-title">📋 Certificat de scolarité (obligatoire)</div>
          <div class="annee-section">
            <div class="form-group">
              <label class="required">Certificat de scolarité de l'année en cours</label>
              <input type="file" name="certificat_scolarite" class="file-input" accept=".pdf,.jpg,.png" required>
              <span class="file-hint">Certificat attestant de votre inscription actuelle (PDF, JPG, PNG - max 5MB)</span>
            </div>
          </div>
        `;
        container.appendChild(certificatSection);
      }
    }
    
    function ajouterDocumentSupplementaire() {
      const container = document.getElementById("documents_supplementaires_container");
      const index = container.children.length;
      
      const docItem = document.createElement("div");
      docItem.className = "doc-supp-item";
      docItem.innerHTML = `
        <div class="form-group">
          <label>Type de document</label>
          <input type="text" name="doc_supp_type[]" placeholder="Lettre de recommandation, Portfolio, etc.">
        </div>
        <div class="form-group">
          <label>Fichier</label>
          <input type="file" name="doc_supp_file[]" class="file-input" accept=".pdf,.jpg,.png,.doc,.docx">
          <span class="file-hint">Document supplémentaire (max 5MB)</span>
        </div>
        <button type="button" class="remove-doc" onclick="supprimerDocument(this)"><i class="fas fa-times"></i></button>
      `;
      
      container.appendChild(docItem);
    }
    
    function supprimerDocument(button) {
      button.closest('.doc-supp-item').remove();
    }
    
    function validateForm() {
      const niveau = document.getElementById("niveau_etudes").value;
      const typeProgramme = document.getElementById("type_programme").value;
      
      if (!niveau) {
        alert("Veuillez sélectionner votre niveau d'études");
        return false;
      }
      
      if (!typeProgramme) {
        alert("Veuillez sélectionner le type de programme");
        return false;
      }
      
      return true;
    }
    
    // Initialiser les sections au chargement
    document.addEventListener('DOMContentLoaded', function() {
      toggleTypeProgramme();
      toggleTestItalien();
      toggleTestAnglais();
      toggleParcoursAcademique();
    });
  </script>
</head>
<body>
<div class="container">
  <header>
    <h1><i class="fas fa-graduation-cap"></i> Demande d'Études en Italie</h1>
    <p>Formulaire d'inscription pour études en Italie</p>
  </header>

  <div class="form-content">
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
      
      <!-- Projet d'études -->
      <div class="form-section">
        <h3><i class="fas fa-book-open"></i> Projet d'études</h3>
        <div class="form-group">
          <label class="required">Pays d'études</label>
          <input type="text" name="pays_etudes" required value="Italie" readonly>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Type de programme</label>
            <select id="type_programme" name="type_programme" required onchange="toggleTypeProgramme()">
              <option value="">-- Sélectionnez le type de programme --</option>
              <option value="italien">Programme en Italien</option>
              <option value="anglais">Programme en Anglais</option>
            </select>
          </div>
          <div class="form-group">
            <label class="required">Niveau d'études visé</label>
            <select id="niveau_etudes" name="niveau_etudes" required onchange="toggleParcoursAcademique()">
              <option value="">-- Sélectionnez votre niveau --</option>
              <option value="licence1">Laurea Triennale 1ère année (Licence 1)</option>
              <option value="licence2">Laurea Triennale 2ème année (Licence 2)</option>
              <option value="licence3">Laurea Triennale 3ème année (Licence 3)</option>
              <option value="master1">Laurea Magistrale 1ère année (Master 1)</option>
              <option value="master2">Laurea Magistrale 2ème année (Master 2)</option>
              <option value="master2_termine">Master 2 Terminé</option>
              <option value="doctorat">Dottorato di Ricerca (Doctorat)</option>
            </select>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Domaine d'études</label>
            <input type="text" name="domaine_etudes" required placeholder="Design, Architecture, Médecine, Droit...">
          </div>
          <div class="form-group">
            <label class="required">Nom de la formation</label>
            <input type="text" name="nom_formation" required placeholder="Laurea in Design, Master in Business...">
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Date de début des études</label>
          <input type="date" name="date_debut" required>
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
        
        <!-- Italien -->
        <div id="test_italien_section" class="conditional-section hidden">
          <div class="form-group">
            <label class="required">Avez-vous passé un test d'italien ?</label>
            <select id="tests_italien" name="tests_italien" onchange="toggleTestItalien()">
              <option value="non">Non - Demander un test</option>
              <option value="celi">CELI</option>
              <option value="cils">CILS</option>
              <option value="plida">PLIDA</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          
          <div id="attestation_italien_section" class="conditional-section hidden">
            <div class="form-group">
              <label class="required">Attestation de test d'italien</label>
              <input type="file" name="attestation_italien" class="file-input" accept=".pdf,.jpg,.png">
              <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
            </div>
          </div>
          
          <div id="demande_test_italien_section" class="test-demand-section">
            <div class="form-group">
              <p><strong>Vous n'avez pas de test d'italien ?</strong></p>
              <p>Nous pouvons vous aider à organiser un test de langue reconnu par les universités italiennes.</p>
              <a href="../../test_de_langue.php?type=italien" class="btn-test">
                <i class="fas fa-language"></i> Demander un test d'italien
              </a>
            </div>
          </div>
        </div>
        
        <!-- Anglais -->
        <div id="test_anglais_section" class="conditional-section hidden">
          <div class="form-group">
            <label class="required">Avez-vous passé un test d'anglais ?</label>
            <select id="tests_anglais" name="tests_anglais" onchange="toggleTestAnglais()">
              <option value="non">Non - Demander un test</option>
              <option value="ielts">IELTS</option>
              <option value="toefl">TOEFL</option>
              <option value="cambridge">Cambridge</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          
          <div id="attestation_anglais_section" class="conditional-section hidden">
            <div class="form-group">
              <label class="required">Attestation de test d'anglais</label>
              <input type="file" name="attestation_anglais" class="file-input" accept=".pdf,.jpg,.png">
              <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
            </div>
          </div>
          
          <div id="demande_test_anglais_section" class="test-demand-section">
            <div class="form-group">
              <p><strong>Vous n'avez pas de test d'anglais ?</strong></p>
              <p>Nous pouvons vous aider à organiser un test de langue reconnu par les universités italiennes.</p>
              <a href="../../test_de_langue.php?type=anglais" class="btn-test">
                <i class="fas fa-language"></i> Demander un test d'anglais
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Documents supplémentaires -->
      <div class="form-section">
        <h3><i class="fas fa-file-alt"></i> Documents supplémentaires</h3>
        <div id="documents_supplementaires_container">
          <!-- Les documents supplémentaires seront ajoutés dynamiquement -->
        </div>
        <button type="button" class="btn-secondary" onclick="ajouterDocumentSupplementaire()">
          <i class="fas fa-plus"></i> Ajouter un document
        </button>
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
            <input type="checkbox" name="procedure" required>
            Je m'engage à suivre la procédure pour les études en Italie
          </label>
        </div>
      </div>

      <!-- Bouton de soumission -->
      <div style="text-align: center; margin: 30px 0;">
        <button type="submit" class="btn-submit">
          <i class="fas fa-paper-plane"></i> Soumettre la demande
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
// Amélioration de la validation des fichiers
document.addEventListener('DOMContentLoaded', function() {
  const fileInputs = document.querySelectorAll('input[type="file"]');
  
  fileInputs.forEach(input => {
    input.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        // Vérification de la taille (5MB max)
        const maxSize = 5 * 1024 * 1024; // 5MB en bytes
        if (file.size > maxSize) {
          alert('Le fichier "' + file.name + '" est trop volumineux. Taille maximale autorisée: 5MB');
          e.target.value = ''; // Réinitialiser l'input
          return;
        }
        
        // Vérification du type de fichier
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
          alert('Type de fichier non autorisé. Formats acceptés: PDF, JPG, PNG');
          e.target.value = '';
          return;
        }
      }
    });
  });
  
  // Validation des dates
  const dateDebut = document.querySelector('input[name="date_debut"]');
  if (dateDebut) {
    const today = new Date().toISOString().split('T')[0];
    dateDebut.min = today;
  }
  
  const dateNaissance = document.querySelector('input[name="date_naissance"]');
  if (dateNaissance) {
    const today = new Date();
    const minDate = new Date(today.getFullYear() - 70, today.getMonth(), today.getDate());
    const maxDate = new Date(today.getFullYear() - 16, today.getMonth(), today.getDate());
    
    dateNaissance.max = maxDate.toISOString().split('T')[0];
    dateNaissance.min = minDate.toISOString().split('T')[0];
  }
});

// Amélioration de l'expérience utilisateur
function showLoading() {
  const submitBtn = document.querySelector('.btn-submit');
  if (submitBtn) {
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
    submitBtn.disabled = true;
  }
}

// Afficher un message de confirmation avant soumission
const form = document.querySelector('form');
if (form) {
  form.addEventListener('submit', function(e) {
    if (!validateForm()) {
      e.preventDefault();
      return false;
    }
    
    const confirmation = confirm('Êtes-vous sûr de vouloir soumettre votre demande ? Vérifiez que tous les documents requis sont joints.');
    if (!confirmation) {
      e.preventDefault();
      return false;
    }
    
    showLoading();
  });
}

// Fonction pour prévisualiser les noms de fichiers
document.querySelectorAll('input[type="file"]').forEach(input => {
  input.addEventListener('change', function() {
    const fileName = this.files[0] ? this.files[0].name : 'Aucun fichier sélectionné';
    const hint = this.nextElementSibling;
    if (hint && hint.classList.contains('file-hint')) {
      const originalText = hint.getAttribute('data-original') || hint.textContent;
      hint.setAttribute('data-original', originalText);
      hint.textContent = fileName + ' - ' + originalText;
    }
  });
});
</script>

</body>
</html>