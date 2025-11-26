<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Connexion BDD
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier que l'ID est passé en paramètre
    if (!isset($_GET['id'])) {
        die("ID de demande non spécifié.");
    }

    $demande_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Récupérer la demande existante
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_parcoursup 
        WHERE id = ? AND user_id = ? AND statut = 'en_attente'
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$demande) {
        die("Demande non trouvée ou vous n'êtes pas autorisé à la modifier.");
    }

    // Récupérer les fichiers existants
    $stmt_files = $pdo->prepare("
        SELECT * FROM demandes_parcoursup_fichiers 
        WHERE demande_id = ?
    ");
    $stmt_files->execute([$demande_id]);
    $fichiers_existants = $stmt_files->fetchAll(PDO::FETCH_ASSOC);

    // Traitement du formulaire de modification
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 🔹 Champs obligatoires
        $required_fields = [
            'pays_etudes', 'niveau_etudes', 'domaine_etudes', 'nom', 'prenom', 
            'date_naissance', 'lieu_naissance', 'nationalite', 'adresse', 
            'telephone', 'email', 'num_passeport', 'date_delivrance', 'date_expiration',
            'dernier_diplome', 'etablissement_origine'
        ];

        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est obligatoire.";
            }
        }

        if (!empty($errors)) {
            $error_message = "<div class='alert alert-danger'>" . implode("<br>", $errors) . "</div>";
        } else {
            // 🔹 Récupération des données
            $pays_etudes        = $_POST['pays_etudes'];
            $niveau_etudes      = $_POST['niveau_etudes'];
            $domaine_etudes     = $_POST['domaine_etudes'];
            $nom_formation      = $_POST['nom_formation'];
            $etablissement      = $_POST['etablissement'];
            $date_debut         = $_POST['date_debut'];
            $duree_etudes       = $_POST['duree_etudes'];
            
            $nom                = $_POST['nom'];
            $prenom             = $_POST['prenom'];
            $date_naissance     = $_POST['date_naissance'];
            $lieu_naissance     = $_POST['lieu_naissance'];
            $nationalite        = $_POST['nationalite'];
            $adresse            = $_POST['adresse'];
            $telephone          = $_POST['telephone'];
            $email              = $_POST['email'];
            
            $num_passeport      = $_POST['num_passeport'];
            $date_delivrance    = $_POST['date_delivrance'];
            $date_expiration    = $_POST['date_expiration'];
            
            $niveau_francais    = $_POST['niveau_francais'] ?? '';
            $tests_francais     = $_POST['tests_francais'] ?? 'non';
            $score_test         = $_POST['score_test'] ?? '';
            
            $dernier_diplome    = $_POST['dernier_diplome'];
            $etablissement_origine = $_POST['etablissement_origine'];
            $moyenne_derniere_annee = $_POST['moyenne_derniere_annee'] ?? '';
            
            // 🔹 Récupération des relevés par année
            $releves_annees = [];
            for ($i = 1; $i <= 5; $i++) {
                if (isset($_POST["annee_etude_$i"]) && !empty($_POST["annee_etude_$i"])) {
                    $releves_annees[$i] = $_POST["annee_etude_$i"];
                }
            }
            $releves_annees_json = json_encode($releves_annees);

            // 🔹 Mise à jour de la demande
            $stmt = $pdo->prepare("
                UPDATE demandes_parcoursup SET 
                pays_etudes = ?, niveau_etudes = ?, domaine_etudes = ?, nom_formation = ?, 
                etablissement = ?, date_debut = ?, duree_etudes = ?, nom = ?, prenom = ?, 
                date_naissance = ?, lieu_naissance = ?, nationalite = ?, adresse = ?, 
                telephone = ?, email = ?, num_passeport = ?, date_delivrance = ?, date_expiration = ?, 
                niveau_francais = ?, tests_francais = ?, score_test = ?, dernier_diplome = ?, 
                etablissement_origine = ?, moyenne_derniere_annee = ?, releves_annees = ?,
                date_modification = NOW()
                WHERE id = ? AND user_id = ?
            ");
            
            $success = $stmt->execute([
                $pays_etudes, $niveau_etudes, $domaine_etudes, $nom_formation,
                $etablissement, $date_debut, $duree_etudes, $nom, $prenom, 
                $date_naissance, $lieu_naissance, $nationalite, $adresse, 
                $telephone, $email, $num_passeport, $date_delivrance, $date_expiration,
                $niveau_francais, $tests_francais, $score_test, $dernier_diplome, 
                $etablissement_origine, $moyenne_derniere_annee, $releves_annees_json,
                $demande_id, $user_id
            ]);

            if ($success) {
                // 🔹 Dossier uploads
                $uploadDir = __DIR__ . "/../../../uploads/";
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                // Fonction pour traiter les nouveaux fichiers
                function saveFile($file, $type, $demande_id, $pdo, $uploadDir) {
                    if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                        // Supprimer l'ancien fichier du même type s'il existe
                        $stmt_delete = $pdo->prepare("
                            DELETE FROM demandes_parcoursup_fichiers 
                            WHERE demande_id = ? AND type_fichier = ?
                        ");
                        $stmt_delete->execute([$demande_id, $type]);

                        $filename = uniqid() . "_" . basename($file['name']);
                        $filepath = $uploadDir . $filename;
                        if (move_uploaded_file($file['tmp_name'], $filepath)) {
                            $stmt = $pdo->prepare("
                                INSERT INTO demandes_parcoursup_fichiers 
                                (demande_id, type_fichier, chemin_fichier, date_upload) 
                                VALUES (?, ?, ?, NOW())
                            ");
                            $stmt->execute([$demande_id, $type, $filename]);
                            return true;
                        }
                    }
                    return false;
                }

                // 🔹 Traitement des nouveaux fichiers
                // Fichiers obligatoires
                if (isset($_FILES['copie_passeport']) && !empty($_FILES['copie_passeport']['name'])) {
                    saveFile($_FILES['copie_passeport'], 'copie_passeport', $demande_id, $pdo, $uploadDir);
                }
                if (isset($_FILES['diplomes']) && !empty($_FILES['diplomes']['name'])) {
                    saveFile($_FILES['diplomes'], 'diplomes', $demande_id, $pdo, $uploadDir);
                }
                if (isset($_FILES['releves_notes']) && !empty($_FILES['releves_notes']['name'])) {
                    saveFile($_FILES['releves_notes'], 'releves_notes', $demande_id, $pdo, $uploadDir);
                }

                // Fichiers optionnels
                if (isset($_FILES['lettre_motivation']) && !empty($_FILES['lettre_motivation']['name'])) {
                    saveFile($_FILES['lettre_motivation'], 'lettre_motivation', $demande_id, $pdo, $uploadDir);
                }
                
                if (isset($_FILES['cv']) && !empty($_FILES['cv']['name'])) {
                    saveFile($_FILES['cv'], 'cv', $demande_id, $pdo, $uploadDir);
                }

                // Fichiers conditionnels
                if ($tests_francais !== 'non' && isset($_FILES['attestation_francais']) && !empty($_FILES['attestation_francais']['name'])) {
                    saveFile($_FILES['attestation_francais'], 'attestation_francais', $demande_id, $pdo, $uploadDir);
                }

                // Fichiers des relevés par année
                for ($i = 1; $i <= 5; $i++) {
                    if (isset($_FILES["releve_annee_$i"]) && !empty($_FILES["releve_annee_$i"]['name'])) {
                        saveFile($_FILES["releve_annee_$i"], "releve_annee_$i", $demande_id, $pdo, $uploadDir);
                    }
                }

                // Certificat de scolarité
                if (isset($_FILES['certificat_scolarite']) && !empty($_FILES['certificat_scolarite']['name'])) {
                    saveFile($_FILES['certificat_scolarite'], 'certificat_scolarite', $demande_id, $pdo, $uploadDir);
                }

                // Attestation d'acceptation
                if (isset($_FILES['attestation_acceptation']) && !empty($_FILES['attestation_acceptation']['name'])) {
                    saveFile($_FILES['attestation_acceptation'], 'attestation_acceptation', $demande_id, $pdo, $uploadDir);
                }

                // Redirection vers la page de confirmation
                $_SESSION['success_message'] = "Votre candidature Parcoursup a été modifiée avec succès!";
                header("Location: mes_demandes_parcoursup.php");
                exit;
            } else {
                $error_message = "<div class='alert alert-danger'>Erreur lors de la modification de la demande.</div>";
            }
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
  <title>Modifier Candidature Parcoursup</title>
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
      background: linear-gradient(to right, #3a0066, #6a1cb2);
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
      border-left: 4px solid #8a2be2;
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
    
    .fichier-existant {
      background: #e8f5e8;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 10px;
      border-left: 4px solid #28a745;
    }
    
    .info-box {
      background: #e8f5e8;
      border-left: 4px solid #28a745;
      padding: 15px;
      margin: 15px 0;
      border-radius: var(--border-radius);
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
      document.getElementById("score_test_section").style.display = (hasTest !== "non") ? "block" : "none";
    }
    
    function toggleAcceptation() {
      const hasAcceptation = document.querySelector("select[name='has_acceptation']").value;
      document.getElementById("acceptation_section").style.display = (hasAcceptation === "oui") ? "block" : "none";
    }
    
    function toggleRelevesAnnees() {
      const niveau = document.getElementById("niveau_etudes").value;
      const relevesSection = document.getElementById("releves_annees_section");
      const certificatSection = document.getElementById("certificat_scolarite_section");
      
      if (niveau === "licence1" || niveau === "licence2" || niveau === "licence3" || 
          niveau === "master1" || niveau === "master2" || niveau === "doctorat") {
        relevesSection.style.display = "block";
        certificatSection.style.display = "block";
        genererChampsReleves(niveau);
      } else {
        relevesSection.style.display = "none";
        certificatSection.style.display = "none";
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
          <div class="form-group">
            <input type="hidden" name="annee_etude_${anneeNum}" value="${annee}">
            <label>Relevé de notes ${annee}</label>
            <input type="file" name="releve_annee_${anneeNum}" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Relevé de notes de ${annee} (max 5MB) - Laissez vide pour conserver l'ancien fichier</span>
          </div>
        `;
        container.appendChild(section);
      });
    }
    
    function validateForm() {
      const niveau = document.getElementById("niveau_etudes").value;
      if (!niveau) {
        alert("Veuillez sélectionner votre niveau d'études");
        return false;
      }
      return true;
    }
  </script>
</head>
<body>
<div class="container">
  <header>
    <h1><i class="fas fa-edit"></i> Modifier Candidature Parcoursup</h1>
    <p>Modifiez votre candidature #<?php echo $demande_id; ?></p>
  </header>

  <div class="form-content">
    <?php if (isset($error_message)) echo $error_message; ?>
    
    <div class="info-box">
      <i class="fas fa-info-circle"></i> 
      <strong>Information :</strong> Vous pouvez modifier votre candidature tant qu'elle est en statut "En attente". 
      Les champs marqués d'un astérisque (*) sont obligatoires.
    </div>

    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
      
      <!-- Projet d'études -->
      <div class="form-section">
        <h3><i class="fas fa-book-open"></i> Projet d'études</h3>
        <div class="form-group">
          <label class="required">Pays d'études</label>
          <input type="text" name="pays_etudes" required value="<?php echo htmlspecialchars($demande['pays_etudes']); ?>">
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Niveau d'études visé</label>
            <select id="niveau_etudes" name="niveau_etudes" required onchange="toggleRelevesAnnees()">
              <option value="">-- Sélectionnez --</option>
              <option value="licence1" <?php echo $demande['niveau_etudes'] == 'licence1' ? 'selected' : ''; ?>>Licence 1ère année</option>
              <option value="licence2" <?php echo $demande['niveau_etudes'] == 'licence2' ? 'selected' : ''; ?>>Licence 2ème année</option>
              <option value="licence3" <?php echo $demande['niveau_etudes'] == 'licence3' ? 'selected' : ''; ?>>Licence 3ème année</option>
              <option value="master1" <?php echo $demande['niveau_etudes'] == 'master1' ? 'selected' : ''; ?>>Master 1ère année</option>
              <option value="master2" <?php echo $demande['niveau_etudes'] == 'master2' ? 'selected' : ''; ?>>Master 2ème année</option>
              <option value="doctorat" <?php echo $demande['niveau_etudes'] == 'doctorat' ? 'selected' : ''; ?>>Doctorat</option>
              <option value="bts" <?php echo $demande['niveau_etudes'] == 'bts' ? 'selected' : ''; ?>>BTS</option>
              <option value="dut" <?php echo $demande['niveau_etudes'] == 'dut' ? 'selected' : ''; ?>>DUT</option>
              <option value="inge" <?php echo $demande['niveau_etudes'] == 'inge' ? 'selected' : ''; ?>>École d'ingénieurs</option>
              <option value="commerce" <?php echo $demande['niveau_etudes'] == 'commerce' ? 'selected' : ''; ?>>École de commerce</option>
            </select>
          </div>
          <div class="form-group">
            <label class="required">Domaine d'études</label>
            <input type="text" name="domaine_etudes" required value="<?php echo htmlspecialchars($demande['domaine_etudes']); ?>" placeholder="Informatique, Droit, Médecine...">
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Nom de la formation</label>
          <input type="text" name="nom_formation" required value="<?php echo htmlspecialchars($demande['nom_formation']); ?>" placeholder="Master en Informatique...">
        </div>
        
        <div class="form-group">
          <label class="required">Établissement</label>
          <input type="text" name="etablissement" required value="<?php echo htmlspecialchars($demande['etablissement']); ?>" placeholder="Nom de l'université ou école">
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Date de début des études</label>
            <input type="date" name="date_debut" required value="<?php echo $demande['date_debut']; ?>">
          </div>
          <div class="form-group">
            <label class="required">Durée des études</label>
            <select name="duree_etudes" required>
              <option value="">-- Sélectionnez --</option>
              <option value="1 an" <?php echo $demande['duree_etudes'] == '1 an' ? 'selected' : ''; ?>>1 an</option>
              <option value="2 ans" <?php echo $demande['duree_etudes'] == '2 ans' ? 'selected' : ''; ?>>2 ans</option>
              <option value="3 ans" <?php echo $demande['duree_etudes'] == '3 ans' ? 'selected' : ''; ?>>3 ans</option>
              <option value="4 ans" <?php echo $demande['duree_etudes'] == '4 ans' ? 'selected' : ''; ?>>4 ans</option>
              <option value="5 ans" <?php echo $demande['duree_etudes'] == '5 ans' ? 'selected' : ''; ?>>5 ans</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Informations personnelles -->
      <div class="form-section">
        <h3><i class="fas fa-user-graduate"></i> Informations personnelles</h3>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Nom</label>
            <input type="text" name="nom" required value="<?php echo htmlspecialchars($demande['nom']); ?>">
          </div>
          <div class="form-group">
            <label class="required">Prénom</label>
            <input type="text" name="prenom" required value="<?php echo htmlspecialchars($demande['prenom']); ?>">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Date de naissance</label>
            <input type="date" name="date_naissance" required value="<?php echo $demande['date_naissance']; ?>">
          </div>
          <div class="form-group">
            <label class="required">Lieu de naissance</label>
            <input type="text" name="lieu_naissance" required value="<?php echo htmlspecialchars($demande['lieu_naissance']); ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Nationalité</label>
          <input type="text" name="nationalite" required value="<?php echo htmlspecialchars($demande['nationalite']); ?>">
        </div>
        
        <div class="form-group">
          <label class="required">Adresse complète</label>
          <textarea name="adresse" required rows="3"><?php echo htmlspecialchars($demande['adresse']); ?></textarea>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label class="required">Téléphone</label>
            <input type="tel" name="telephone" required value="<?php echo htmlspecialchars($demande['telephone']); ?>">
          </div>
          <div class="form-group">
            <label class="required">Email</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($demande['email']); ?>">
          </div>
        </div>
      </div>

      <!-- Passeport -->
      <div class="form-section">
        <h3><i class="fas fa-passport"></i> Passeport</h3>
        <div class="form-row">
          <div class="form-group">
            <label class="required">Numéro de passeport</label>
            <input type="text" name="num_passeport" required value="<?php echo htmlspecialchars($demande['num_passeport']); ?>">
          </div>
          <div class="form-group">
            <label class="required">Date de délivrance</label>
            <input type="date" name="date_delivrance" required value="<?php echo $demande['date_delivrance']; ?>">
          </div>
          <div class="form-group">
            <label class="required">Date d'expiration</label>
            <input type="date" name="date_expiration" required value="<?php echo $demande['date_expiration']; ?>">
          </div>
        </div>
        
        <div class="form-group">
          <label class="required">Copie du passeport</label>
          <?php 
          $fichier_passeport = array_filter($fichiers_existants, function($f) { return $f['type_fichier'] == 'copie_passeport'; });
          if (!empty($fichier_passeport)): 
            $fichier = reset($fichier_passeport);
          ?>
            <div class="fichier-existant">
              <i class="fas fa-file-pdf"></i> Fichier actuel : <?php echo $fichier['chemin_fichier']; ?>
              <br><small>Téléchargé le : <?php echo date('d/m/Y à H:i', strtotime($fichier['date_upload'])); ?></small>
            </div>
          <?php endif; ?>
          <input type="file" name="copie_passeport" class="file-input" accept=".pdf,.jpg,.png">
          <span class="file-hint">Pages avec photo et informations personnelles (max 5MB) - Laissez vide pour conserver l'ancien fichier</span>
        </div>
      </div>

      <!-- Niveau de français -->
      <div class="form-section">
        <h3><i class="fas fa-language"></i> Niveau de français (Optionnel)</h3>
        <div class="form-group">
          <label>Niveau de français estimé</label>
          <select name="niveau_francais">
            <option value="">-- Sélectionnez --</option>
            <option value="debutant" <?php echo $demande['niveau_francais'] == 'debutant' ? 'selected' : ''; ?>>Débutant (A1-A2)</option>
            <option value="intermediaire" <?php echo $demande['niveau_francais'] == 'intermediaire' ? 'selected' : ''; ?>>Intermédiaire (B1-B2)</option>
            <option value="avance" <?php echo $demande['niveau_francais'] == 'avance' ? 'selected' : ''; ?>>Avancé (C1-C2)</option>
            <option value="bilingue" <?php echo $demande['niveau_francais'] == 'bilingue' ? 'selected' : ''; ?>>Bilingue</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Avez-vous passé un test de français ?</label>
          <select id="tests_francais" name="tests_francais" onchange="toggleTestFrancais()">
            <option value="non" <?php echo $demande['tests_francais'] == 'non' ? 'selected' : ''; ?>>Non</option>
            <option value="tcf" <?php echo $demande['tests_francais'] == 'tcf' ? 'selected' : ''; ?>>TCF</option>
            <option value="delf" <?php echo $demande['tests_francais'] == 'delf' ? 'selected' : ''; ?>>DELF</option>
            <option value="dalf" <?php echo $demande['tests_francais'] == 'dalf' ? 'selected' : ''; ?>>DALF</option>
            <option value="autre" <?php echo $demande['tests_francais'] == 'autre' ? 'selected' : ''; ?>>Autre</option>
          </select>
        </div>
        
        <div id="score_test_section" class="conditional-section" style="display: <?php echo ($demande['tests_francais'] !== 'non' && $demande['tests_francais'] !== '') ? 'block' : 'none'; ?>">
          <div class="form-group">
            <label>Score/Diplôme obtenu</label>
            <input type="text" name="score_test" value="<?php echo htmlspecialchars($demande['score_test']); ?>" placeholder="Ex: B2, 450 points...">
          </div>
          <div class="form-group">
            <label>Attestation de score/diplôme</label>
            <?php 
            $fichier_francais = array_filter($fichiers_existants, function($f) { return $f['type_fichier'] == 'attestation_francais'; });
            if (!empty($fichier_francais)): 
              $fichier = reset($fichier_francais);
            ?>
              <div class="fichier-existant">
                <i class="fas fa-file-pdf"></i> Fichier actuel : <?php echo $fichier['chemin_fichier']; ?>
              </div>
            <?php endif; ?>
            <input type="file" name="attestation_francais" class="file-input" accept=".pdf,.jpg,.png">
            <span class="file-hint">Copie du diplôme ou attestation de score (max 5MB)</span>
          </div>
        </div>
      </div>

      <!-- Parcours académique -->
      <div class="form-section">
        <h3><i class="fas fa-university"></i> Parcours académique</h3>
        <div class="form-group">
          <label class="required">Dernier diplôme obtenu</label>
          <input type="text" name="dernier_diplome" required value="<?php echo htmlspecialchars($demande['dernier_diplome']); ?>" placeholder="Baccalauréat, Licence...">
        </div>
        
        <div class="form-group">
          <label class="required">Établissement d'origine</label>
          <input type="text" name="etablissement_origine" required value="<?php echo htmlspecialchars($demande['etablissement_origine']); ?>">
        </div>
        
        <div class="form-group">
          <label>Moyenne dernière année</label>
          <input type="text" name="moyenne_derniere_annee" value="<?php echo htmlspecialchars($demande['moyenne_derniere_annee']); ?>" placeholder="Ex: 14.5/20">
        </div>
        
        <div class="form-group">
          <label class="required">Copie des diplômes</label>
          <?php 
          $fichier_diplomes = array_filter($fichiers_existants, function($f) { return $f['type_fichier'] == 'diplomes'; });
          if (!empty($fichier_diplomes)): 
            $fichier = reset($fichier_diplomes);
          ?>
            <div class="fichier-existant">
              <i class="fas fa-file-pdf"></i> Fichier actuel : <?php echo $fichier['chemin_fichier']; ?>
            </div>
          <?php endif; ?>
          <input type="file" name="diplomes" class="file-input" accept=".pdf,.jpg,.png">
          <span class="file-hint">Copie des diplômes obtenus (max 10MB) - Laissez vide pour conserver l'ancien fichier</span>
        </div>
        
        <div class="form-group">
          <label class="required">Relevés de notes globaux</label>
          <?php 
          $fichier_releves = array_filter($fichiers_existants, function($f) { return $f['type_fichier'] == 'releves_notes'; });
          if (!empty($fichier_releves)): 
            $fichier = reset($fichier_releves);
          ?>
            <div class="fichier-existant">
              <i class="fas fa-file-pdf"></i> Fichier actuel : <?php echo $fichier['chemin_fichier']; ?>
            </div>
          <?php endif; ?>
          <input type="file" name="releves_notes" class="file-input" accept=".pdf,.jpg,.png">
          <span class="file-hint">Relevés de notes de toutes les années (max 10MB) - Laissez vide pour conserver l'ancien fichier</span>
        </div>
      </div>

      <!-- Certificat de scolarité année en cours -->
      <div id="certificat_scolarite_section" class="form-section" style="display: <?php echo (in_array($demande['niveau_etudes'], ['licence1', 'licence2', 'licence3', 'master1', 'master2', 'doctorat'])) ? 'block' : 'none'; ?>">
        <h3><i class="fas fa-file-certificate"></i> Certificat de scolarité</h3>
        <div class="form-group">
          <label class="optional">Certificat de scolarité de l'année universitaire en cours</label>
          <?php 
          $fichier_certificat = array_filter($fichiers_existants, function($f) { return $f['type_fichier'] == 'certificat_scolarite'; });
          if (!empty($fichier_certificat)): 
            $fichier = reset($fichier_certificat);
          ?>
            <div class="fichier-existant">
              <i class="fas fa-file-pdf"></i> Fichier actuel : <?php echo $fichier['chemin_fichier']; ?>
            </div>
          <?php endif; ?>
          <input type="file" name="certificat_scolarite" class="file-input" accept=".pdf,.jpg,.png">
          <span class="file-hint">Certificat de scolarité attestant de votre inscription actuelle (max 5MB)</span>
        </div>
      </div>

      <!-- Relevés de notes par année -->
      <div id="releves_annees_section" class="form-section" style="display: <?php echo (in_array($demande['niveau_etudes'], ['licence1', 'licence2', 'licence3', 'master1', 'master2', 'doctorat'])) ? 'block' : 'none'; ?>">
        <h3><i class="fas fa-file-alt"></i> Relevés de notes par année (Optionnel)</h3>
        <div id="releves_annees_container">
          <!-- Les champs seront générés dynamiquement -->
        </div>
      </div>

      <!-- Documents de motivation -->
      <div class="form-section">
        <h3><i class="fas fa-file-alt"></i> Documents de motivation (Optionnels)</h3>
        <div class="form-group">
          <label class="optional">Lettre de motivation</label>
          <?php 
          $fichier_lettre = array_filter($fichiers_existants, function($f) { return $f['type_fichier'] == 'lettre_motivation'; });
          if (!empty($fichier_lettre)): 
            $fichier = reset($fichier_lettre);
          ?>
            <div class="fichier-existant">
              <i class="fas fa-file-pdf"></i> Fichier actuel : <?php echo $fichier['chemin_fichier']; ?>
            </div>
          <?php endif; ?>
          <input type="file" name="lettre_motivation" class="file-input" accept=".pdf,.doc,.docx">
          <span class="file-hint">Lettre de motivation détaillant votre projet (max 5MB)</span>
        </div>
        
        <div class="form-group">
          <label class="optional">Curriculum Vitae (CV)</label>
          <?php 
          $fichier_cv = array_filter($fichiers_existants, function($f) { return $f['type_fichier'] == 'cv'; });
          if (!empty($fichier_cv)): 
            $fichier = reset($fichier_cv);
          ?>
            <div class="fichier-existant">
              <i class="fas fa-file-pdf"></i> Fichier actuel : <?php echo $fichier['chemin_fichier']; ?>
            </div>
          <?php endif; ?>
          <input type="file" name="cv" class="file-input" accept=".pdf,.doc,.docx">
          <span class="file-hint">CV à jour (max 5MB)</span>
        </div>
      </div>

      <!-- Déclaration -->
      <div class="form-section">
        <h3><i class="fas fa-file-signature"></i> Déclaration</h3>
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="declaration" required checked>
            Je certifie que les informations fournies sont exactes et complètes
          </label>
        </div>
        
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="conditions" required checked>
            J'accepte les conditions de traitement de mes données personnelles
          </label>
        </div>
        
        <div class="form-group">
          <label class="required">
            <input type="checkbox" name="parcoursup" required checked>
            Je m'engage à suivre la procédure Parcoursup
          </label>
        </div>
      </div>

      <!-- Boutons -->
      <div class="form-section" style="text-align: center;">
        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Enregistrer les modifications</button>
        <a href="mes_demandes_parcoursup.php" class="btn btn-secondary" style="margin-left: 10px;">
          <i class="fas fa-times"></i> Annuler
        </a>
      </div>
    </form>
  </div>
  
  <footer>
    <p>© 2023 Parcoursup. Tous droits réservés.</p>
  </footer>
</div>

<script>
// Fonction pour initialiser l'état des sections conditionnelles
function initConditionalSections() {
  toggleTestFrancais();
  toggleAcceptation();
  toggleRelevesAnnees();
  
  // Générer les champs de relevés selon le niveau actuel
  const niveauActuel = document.getElementById("niveau_etudes").value;
  if (niveauActuel) {
    genererChampsReleves(niveauActuel);
  }
}

// Appeler les fonctions au chargement de la page
document.addEventListener('DOMContentLoaded', initConditionalSections);
</script>
</body>
</html>