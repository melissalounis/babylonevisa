<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Paramètres de connexion
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

// Traitement du formulaire
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer l'email de l'utilisateur
        $stmt_user = $pdo->prepare("SELECT email FROM users WHERE id = ?");
        $stmt_user->execute([$_SESSION['user_id']]);
        $user = $stmt_user->fetch();
        $user_email = $user['email'] ?? '';

        // Gestion de l'upload des fichiers
        $upload_dir = "../../uploads/roumanie/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Fonction pour uploader un fichier
        function uploadFile($file, $upload_dir) {
            if ($file['error'] === UPLOAD_ERR_OK) {
                $filename = uniqid() . '_' . basename($file['name']);
                $target_path = $upload_dir . $filename;
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    return $filename;
                }
            }
            return null;
        }

        // Upload des fichiers obligatoires
        $files_uploaded = [];
        $file_fields = ['releve_2nde', 'releve_1ere', 'releve_terminale', 'releve_bac', 'diplome_bac', 'certificat_scolarite', 'releve_l1', 'releve_l2', 'releve_l3', 'diplome_licence', 'releve_m1', 'releve_m2', 'diplome_m2'];
        
        foreach ($file_fields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $files_uploaded[$field] = uploadFile($_FILES[$field], $upload_dir);
            }
        }

        // Upload du certificat de langue si fourni
        $certificat_file = null;
        if (isset($_FILES['certificat_file']) && $_FILES['certificat_file']['error'] === UPLOAD_ERR_OK) {
            $certificat_file = uploadFile($_FILES['certificat_file'], $upload_dir);
        }

        // Upload des documents supplémentaires
        $documents_supplementaires = [];
        if (isset($_POST['doc_sup_type']) && is_array($_POST['doc_sup_type'])) {
            foreach ($_POST['doc_sup_type'] as $index => $type) {
                if (!empty($type) && isset($_FILES['doc_sup_file']['name'][$index]) && $_FILES['doc_sup_file']['error'][$index] === UPLOAD_ERR_OK) {
                    $file_data = [
                        'name' => $_FILES['doc_sup_file']['name'][$index],
                        'type' => $_FILES['doc_sup_file']['type'][$index],
                        'tmp_name' => $_FILES['doc_sup_file']['tmp_name'][$index],
                        'error' => $_FILES['doc_sup_file']['error'][$index],
                        'size' => $_FILES['doc_sup_file']['size'][$index]
                    ];
                    $filename = uploadFile($file_data, $upload_dir);
                    if ($filename) {
                        $documents_supplementaires[] = [
                            'type' => $type,
                            'fichier' => $filename
                        ];
                    }
                }
            }
        }

        // Convertir les documents supplémentaires en JSON pour le stockage
        $documents_supp_json = !empty($documents_supplementaires) ? json_encode($documents_supplementaires) : null;

        // Insertion dans la base de données
        $sql = "INSERT INTO demandes_etudes_roumanie (
            nom_complet, email, telephone, specialite, programme_langue, 
            certificat_type, certificat_score, certificat_file,
            releve_2nde, releve_1ere, releve_terminale, releve_bac, 
            diplome_bac, certificat_scolarite, releve_l1, releve_l2, releve_l3,
            diplome_licence, releve_m1, releve_m2, diplome_m2,
            niveau_etude, documents_supplementaires, statut, date_soumission
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'nouveau', NOW())";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nom'],
            $user_email,
            $_POST['telephone'],
            $_POST['specialite'],
            $_POST['programme_langue'],
            $_POST['certificat_type'] ?? null,
            $_POST['certificat_score'] ?? null,
            $certificat_file,
            $files_uploaded['releve_2nde'] ?? null,
            $files_uploaded['releve_1ere'] ?? null,
            $files_uploaded['releve_terminale'] ?? null,
            $files_uploaded['releve_bac'] ?? null,
            $files_uploaded['diplome_bac'] ?? null,
            $files_uploaded['certificat_scolarite'] ?? null,
            $files_uploaded['releve_l1'] ?? null,
            $files_uploaded['releve_l2'] ?? null,
            $files_uploaded['releve_l3'] ?? null,
            $files_uploaded['diplome_licence'] ?? null,
            $files_uploaded['releve_m1'] ?? null,
            $files_uploaded['releve_m2'] ?? null,
            $files_uploaded['diplome_m2'] ?? null,
            $_POST['niveau'],
            $documents_supp_json
        ]);

        $success_message = "Votre demande d'études en Roumanie a été soumise avec succès !";

    } catch (PDOException $e) {
        $error_message = "Erreur lors de l'envoi de la demande : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulaire d'Études - Roumanie</title>
  <style>
    :root {
      --primary-color: rgba(0, 43, 127, 0.7);
      --primary-hover: rgba(9, 47, 122, 0.7);
      --secondary-color: #f8f9fa;
      --text-color: #333;
      --light-gray: #e9ecef;
      --border-radius: 8px;
      --box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
      --med-color: #318CE7;
      --eng-color: rgba(9, 55, 146, 0.7);
      --success-color: #28a745;
      --danger-color: #dc3545;
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
      background: linear-gradient(90deg, #ffd700, #ffed4e, #ffd700);
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
      box-shadow: 0 0 0 3px rgba(0, 43, 127, 0.2);
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
    
    .btn-danger {
      background: var(--danger-color);
    }
    
    .btn-danger:hover {
      background: #c82333;
    }
    
    .btn-secondary {
      background: #6c757d;
    }
    
    .btn-secondary:hover {
      background: #545b62;
    }
    
    .file-input-container {
      position: relative;
      margin-bottom: 15px;
    }
    
    .file-input-container input[type="file"] {
      padding: 10px;
      background: white;
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
      background-color: rgba(0, 43, 127, 0.05);
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
    
    .service-card p {
      color: #666;
      font-size: 0.9rem;
    }
    
    .hidden {
      display: none;
    }
    
    .language-certificate {
      margin-top: 20px;
      padding: 15px;
      background: white;
      border-radius: var(--border-radius);
      border-left: 4px solid var(--med-color);
    }
    
    .submit-buttons {
      text-align: center;
      margin-top: 30px;
      padding: 20px;
      border-top: 1px solid #ddd;
    }
    
    .success-message {
      background: #d4edda;
      color: #155724;
      padding: 15px;
      border-radius: var(--border-radius);
      margin-bottom: 20px;
      border: 1px solid #c3e6cb;
    }
    
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 15px;
      border-radius: var(--border-radius);
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
    }
    
    .document-supplementaire {
      background: white;
      padding: 15px;
      border-radius: var(--border-radius);
      border: 1px solid #ddd;
      margin-bottom: 15px;
      position: relative;
    }
    
    .document-supplementaire .btn-danger {
      position: absolute;
      top: 10px;
      right: 10px;
      padding: 5px 10px;
      font-size: 12px;
    }
    
    .documents-supplementaires-container {
      margin-top: 20px;
    }
    
    .language-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: var(--primary-color);
      color: white;
      padding: 2px 8px;
      border-radius: 12px;
      font-size: 0.8rem;
      font-weight: bold;
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
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="container">
    <header>
      <h2><i class="fas fa-graduation-cap"></i> Formulaire d'Études - Roumanie</h2>
    </header>
    
    <form method="post" enctype="multipart/form-data">
      <?php if ($success_message): ?>
        <div class="success-message">
          <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
        </div>
      <?php endif; ?>

      <?php if ($error_message): ?>
        <div class="error-message">
          <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
        </div>
      <?php endif; ?>

      <!-- Section Programme -->
      <div class="section" id="programme-section">
        <h3><i class="fas fa-book-open"></i> Choix du programme</h3>
        
        <div class="form-group">
          <label class="required">Entrez votre spécialité</label>
          <input type="text" id="specialite" name="specialite" placeholder="Ex: Biologie, Informatique médicale..." required
                 value="<?php echo htmlspecialchars($_POST['specialite'] ?? ''); ?>">
        </div>

        <div class="form-group">
          <label class="required">Sélectionnez la langue du programme</label>
          <div class="service-grid">
            <div class="service-card" onclick="selectLanguage(this, 'français')">
              <span class="language-badge">FR</span>
              <i class="fas fa-language"></i>
              <h4>Français</h4>
            </div>
            <div class="service-card" onclick="selectLanguage(this, 'anglais')">
              <span class="language-badge">EN</span>
              <i class="fas fa-language"></i>
              <h4>Anglais</h4>
            </div>
            <div class="service-card" onclick="selectLanguage(this, 'roumain')">
              <span class="language-badge">RM</span>
              <i class="fas fa-language"></i>
              <h4>Roumain</h4>
            </div>
          </div>
          <input type="hidden" id="programme_langue" name="programme_langue" required>
        </div>
        
        <!-- Section pour attestation de langue -->
        <div id="language-certificate-section" class="language-certificate hidden">
          <h4><i class="fas fa-file-certificate"></i> Attestation de niveau de langue</h4>
          <div class="form-group">
            <label class="required">Type de certificat</label>
            <select id="certificat_type" name="certificat_type">
              <option value="">-- Sélectionnez --</option>
              <option id="option-tcf" class="hidden">TCF</option>
              <option id="option-delf" class="hidden">DELF</option>
              <option id="option-dalf" class="hidden">DALF</option>
              <option id="option-toefl" class="hidden">TOEFL</option>
              <option id="option-ielts" class="hidden">IELTS</option>
              <option id="option-toeic" class="hidden">TOEIC</option>
            </select>
          </div>
          
          <div class="form-group">
            <label class="required">Score/Niveau</label>
            <input type="text" id="certificat_score" name="certificat_score" placeholder="Ex: B2, 550, 6.5..."
                   value="<?php echo htmlspecialchars($_POST['certificat_score'] ?? ''); ?>">
          </div>
          
          <div class="form-group">
            <label class="required">Attestation/Certificat</label>
            <input type="file" id="certificat_file" name="certificat_file" accept=".pdf,.jpg,.jpeg,.png">
          </div>
        </div>
      </div>

      <!-- Infos personnelles -->
      <div class="section" id="personal-info-section">
        <h3><i class="fas fa-user"></i> Informations personnelles</h3>
        
        <div class="form-group">
          <label class="required">Nom</label>
          <input type="text" id="nom" name="nom" required pattern="[A-Za-zÀ-ÿ\s\-']{2,}" 
                 value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
          <label class="required">Prénom</label>
          <input type="text" id="prenom" name="prenom" required pattern="[A-Za-zÀ-ÿ\s\-']{2,}"
                 value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
          <label class="required">Email</label>
          <input type="email" id="email" name="email" required
                 value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
          <label class="required">Téléphone</label>
          <input type="tel" id="telephone" name="telephone" required pattern="[\+\d\s\-\(\)]{10,}"
                 value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>">
        </div>
      </div>

      <!-- Niveau d'études -->
      <div class="section" id="niveau-section">
        <h3><i class="fas fa-graduation-cap"></i> Niveau d'études</h3>
        
        <div class="form-group">
          <label class="required">Choisissez votre niveau</label>
          <select id="niveau" name="niveau" required onchange="showDocs()">
            <option value="">-- Sélectionnez --</option>
            <option value="bac" <?php echo ($_POST['niveau'] ?? '') === 'bac' ? 'selected' : ''; ?>>Bac</option>
            <option value="l1" <?php echo ($_POST['niveau'] ?? '') === 'l1' ? 'selected' : ''; ?>>Licence 1</option>
            <option value="l2" <?php echo ($_POST['niveau'] ?? '') === 'l2' ? 'selected' : ''; ?>>Licence 2</option>
            <option value="l3" <?php echo ($_POST['niveau'] ?? '') === 'l3' ? 'selected' : ''; ?>>Licence 3</option>
            <option value="master" <?php echo ($_POST['niveau'] ?? '') === 'master' ? 'selected' : ''; ?>>Master 1</option>
            <option value="master2_en_cours" <?php echo ($_POST['niveau'] ?? '') === 'master2_en_cours' ? 'selected' : ''; ?>>Master 2 en cours</option>
            <option value="master2" <?php echo ($_POST['niveau'] ?? '') === 'master2' ? 'selected' : ''; ?>>Master 2 terminé</option>
          </select>
        </div>
      </div>

      <!-- Documents dynamiques -->
      <div class="section" id="documents-section">
        <h3><i class="fas fa-file-alt"></i> Documents requis</h3>
        <div id="docsContainer"></div>
      </div>

      <!-- Documents supplémentaires -->
      <div class="section" id="documents-supp-section">
        <h3><i class="fas fa-file-plus"></i> Documents supplémentaires</h3>
        <p>Ajoutez ici tout autre document que vous souhaitez inclure dans votre dossier (lettres de recommandation, CV, etc.)</p>
        
        <div class="documents-supplementaires-container" id="documentsSupplementairesContainer">
          <!-- Les documents supplémentaires seront ajoutés ici dynamiquement -->
        </div>
        
        <button type="button" class="btn btn-secondary" onclick="ajouterDocumentSupplementaire()">
          <i class="fas fa-plus"></i> Ajouter un document
        </button>
      </div>

      <!-- Commentaire -->
      <div class="section">
        <h3><i class="fas fa-comment"></i> Commentaire</h3>
        <div class="form-group">
          <label for="commentaire">Commentaire général</label>
          <textarea id="commentaire" name="commentaire" rows="4" placeholder="Informations supplémentaires..."><?php echo htmlspecialchars($_POST['commentaire'] ?? ''); ?></textarea>
        </div>
      </div>
      
      <!-- Boutons de soumission -->
      <div class="submit-buttons">
        <button type="submit" class="btn">
          <i class="fas fa-paper-plane"></i> Soumettre la demande
        </button>
      </div>
    </form>
  </div>

  <script>
    // Configuration des documents par niveau
    const configDocs = {
      bac: [
        { label: "Relevé de notes 1ère année", name: "releve_2nde", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes 2ème année", name: "releve_1ere", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Terminale", name: "releve_terminale", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Bac", name: "releve_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", type: "file", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      l1: [
        { label: "Relevé de notes 1ère année", name: "releve_2nde", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes 2ème année", name: "releve_1ere", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Terminale", name: "releve_terminale", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Bac", name: "releve_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", type: "file", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      l2: [
        { label: "Relevé de notes Bac", name: "releve_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L1", name: "releve_l1", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", type: "file", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      l3: [
        { label: "Relevé de notes Bac", name: "releve_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L1", name: "releve_l1", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L2", name: "releve_l2", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", type: "file", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      master: [
        { label: "Relevé de notes Bac", name: "releve_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L1", name: "releve_l1", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L2", name: "releve_l2", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L3", name: "releve_l3", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Licence", name: "diplome_licence", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", type: "file", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      master2_en_cours: [
        { label: "Relevé de notes Bac", name: "releve_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L1", name: "releve_l1", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L2", name: "releve_l2", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L3", name: "releve_l3", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Licence", name: "diplome_licence", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Master 1", name: "releve_m1", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", type: "file", accept: ".pdf,.jpg,.jpeg,.png" }
      ],
      master2: [
        { label: "Relevé de notes Bac", name: "releve_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Bac", name: "diplome_bac", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L1", name: "releve_l1", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L2", name: "releve_l2", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes L3", name: "releve_l3", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Licence", name: "diplome_licence", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Master 1", name: "releve_m1", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Relevé de notes Master 2", name: "releve_m2", type: "file", accept: ".pdf,.jpg,.jpeg,.png" },
        { label: "Diplôme Master 2", name: "diplome_m2", type: "file", accept: ".pdf,.jpg,.jpeg,.png" }
      ]
    };

    // Compteur pour les documents supplémentaires
    let docSuppCounter = 0;

    // Fonction pour sélectionner la langue
    function selectLanguage(card, langue) {
      // Retirer la sélection sur toutes les cartes
      document.querySelectorAll('.service-card').forEach(el => el.classList.remove('selected'));
      
      // Ajouter la sélection sur la carte cliquée
      card.classList.add('selected');
      
      // Mettre à jour l'input caché
      document.getElementById('programme_langue').value = langue;
      
      // Afficher ou masquer la section d'attestation de langue
      const certificateSection = document.getElementById('language-certificate-section');
      const certificatType = document.getElementById('certificat_type');
      
      if (langue === 'français' || langue === 'anglais') {
        certificateSection.classList.remove('hidden');
        
        // Afficher les options appropriées
        document.querySelectorAll('#certificat_type option').forEach(opt => {
          opt.classList.add('hidden');
        });
        
        if (langue === 'français') {
          document.getElementById('option-tcf').classList.remove('hidden');
          document.getElementById('option-delf').classList.remove('hidden');
          document.getElementById('option-dalf').classList.remove('hidden');
        } else if (langue === 'anglais') {
          document.getElementById('option-toefl').classList.remove('hidden');
          document.getElementById('option-ielts').classList.remove('hidden');
          document.getElementById('option-toeic').classList.remove('hidden');
        }
        
        // Réinitialiser la sélection
        certificatType.value = '';
        document.getElementById('certificat_score').value = '';
        document.getElementById('certificat_file').value = '';
        
        // Rendre les champs requis
        certificatType.required = true;
        document.getElementById('certificat_score').required = true;
        document.getElementById('certificat_file').required = true;
      } else {
        certificateSection.classList.add('hidden');
        
        // Rendre les champs non requis
        certificatType.required = false;
        document.getElementById('certificat_score').required = false;
        document.getElementById('certificat_file').required = false;
      }
    }

    // Afficher les documents en fonction du niveau
    function showDocs() {
      let niveau = document.getElementById("niveau").value;
      let container = document.getElementById("docsContainer");
      container.innerHTML = "";
      
      if (configDocs[niveau]) {
        configDocs[niveau].forEach(doc => {
          const fileId = doc.name + '_' + Math.floor(Math.random() * 1000);
          
          container.innerHTML += `
            <div class="form-group file-input-container">
              <label for="${fileId}" class="required">${doc.label}</label>
              <input type="${doc.type}" id="${fileId}" name="${doc.name}" 
                     accept="${doc.accept}" required>
            </div>
          `;
        });
      }
    }

    // Ajouter un document supplémentaire
    function ajouterDocumentSupplementaire() {
      const container = document.getElementById('documentsSupplementairesContainer');
      const docId = 'doc_sup_' + docSuppCounter;
      
      const docElement = document.createElement('div');
      docElement.className = 'document-supplementaire';
      docElement.innerHTML = `
        <button type="button" class="btn btn-danger" onclick="supprimerDocumentSupplementaire(this)">
          <i class="fas fa-times"></i>
        </button>
        <div class="form-group">
          <label class="required">Type de document</label>
          <select name="doc_sup_type[]" required>
            <option value="">-- Sélectionnez --</option>
            <option value="lettre_recommandation">Lettre de recommandation</option>
            <option value="cv">CV</option>
            <option value="lettre_motivation">Lettre de motivation</option>
            <option value="portfolio">Portfolio</option>
            <option value="certificat_travail">Certificat de travail</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label class="required">Fichier</label>
          <input type="file" name="doc_sup_file[]" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
      `;
      
      container.appendChild(docElement);
      docSuppCounter++;
    }

    // Supprimer un document supplémentaire
    function supprimerDocumentSupplementaire(button) {
      const docElement = button.closest('.document-supplementaire');
      docElement.remove();
    }

    // Validation du formulaire avant envoi
    document.querySelector('form').addEventListener('submit', function(e) {
      const langue = document.getElementById('programme_langue').value;
      
      if (!langue) {
        e.preventDefault();
        alert("Veuillez sélectionner une langue de programme.");
        return;
      }
      
      // Validation pour les attestations de langue
      if (langue === 'français' || langue === 'anglais') {
        const certificatType = document.getElementById('certificat_type').value;
        const certificatScore = document.getElementById('certificat_score').value;
        const certificatFile = document.getElementById('certificat_file').value;
        
        if (!certificatType) {
          e.preventDefault();
          alert("Veuillez sélectionner le type de certificat de langue.");
          return;
        }
        
        if (!certificatScore) {
          e.preventDefault();
          alert("Veuillez entrer votre score/niveau de langue.");
          return;
        }
        
        if (!certificatFile) {
          e.preventDefault();
          alert("Veuillez télécharger votre attestation de langue.");
          return;
        }
      }
    });

    // Initialiser l'affichage des documents si un niveau est déjà sélectionné
    document.addEventListener('DOMContentLoaded', function() {
      const niveauSelect = document.getElementById('niveau');
      if (niveauSelect.value) {
        showDocs();
      }
    });
  </script>
</body>
</html>