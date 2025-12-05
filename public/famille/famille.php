<?php
// Fichier: famille.php

// Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connexion à la base de données
require_once __DIR__ . '../../../config.php';



// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    traiterFormulaire($pdo);
} else {
    afficherFormulaire();
}

// Fonction pour traiter la soumission du formulaire
function traiterFormulaire($pdo) {
    // Générer un numéro de dossier unique
    $numero_dossier = 'FR-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Fonction pour uploader les fichiers
    function uploadFile($file, $dossier) {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            if ($file['size'] > 5 * 1024 * 1024) {
                return null;
            }
            
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $extensions_autorisees = ['pdf', 'jpg', 'jpeg', 'png'];
            
            if (!in_array($extension, $extensions_autorisees)) {
                return null;
            }
            
            $nouveau_nom = uniqid() . '.' . $extension;
            $chemin = $dossier . $nouveau_nom;
            
            if (move_uploaded_file($file['tmp_name'], $chemin)) {
                return $nouveau_nom;
            }
        }
        return null;
    }
    
    // Créer le dossier d'upload
    $upload_dir = "uploads/regroupement_familial/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Récupération des données
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $date_naissance = $_POST['date_naissance'] ?? '';
    $nationalite = trim($_POST['nationalite'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $nom_famille = trim($_POST['nom_famille'] ?? '');
    $lien_parente = $_POST['lien_parente'] ?? '';
    $adresse_famille = trim($_POST['adresse_famille'] ?? '');
    $commentaire = trim($_POST['commentaire'] ?? '');
    
    // Validation des champs obligatoires
    $erreurs = [];
    
    if (empty($nom_complet)) $erreurs[] = "Le nom complet est obligatoire";
    if (empty($date_naissance)) $erreurs[] = "La date de naissance est obligatoire";
    if (empty($nationalite)) $erreurs[] = "La nationalité est obligatoire";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Un email valide est obligatoire";
    if (empty($telephone)) $erreurs[] = "Le téléphone est obligatoire";
    if (empty($nom_famille)) $erreurs[] = "Le nom du membre de famille est obligatoire";
    if (empty($lien_parente)) $erreurs[] = "Le lien de parenté est obligatoire";
    if (empty($adresse_famille)) $erreurs[] = "L'adresse en France est obligatoire";
    
    // Upload des fichiers
    $passeport = uploadFile($_FILES['passeport'] ?? null, $upload_dir);
    $titre_sejour = uploadFile($_FILES['titre_sejour'] ?? null, $upload_dir);
    $acte_mariage = uploadFile($_FILES['acte_mariage'] ?? null, $upload_dir);
    $justificatif_logement = uploadFile($_FILES['justificatif_logement'] ?? null, $upload_dir);
    $ressources = uploadFile($_FILES['ressources'] ?? null, $upload_dir);
    $paiement = uploadFile($_FILES['paiement'] ?? null, $upload_dir);
    
    // Vérification des fichiers obligatoires
    if (!$passeport) $erreurs[] = "Le passeport est obligatoire";
    if (!$titre_sejour) $erreurs[] = "Le titre de séjour est obligatoire";
    if (!$acte_mariage) $erreurs[] = "L'acte de mariage/naissance est obligatoire";
    if (!$justificatif_logement) $erreurs[] = "Le justificatif de logement est obligatoire";
    if (!$ressources) $erreurs[] = "Les preuves de ressources sont obligatoires";
    if (!$paiement) $erreurs[] = "Le reçu de paiement est obligatoire";
    
    // Upload des preuves de liens (facultatif)
    $preuves_liens = [];
    if (isset($_FILES['preuves_liens']) && is_array($_FILES['preuves_liens']['name'])) {
        foreach ($_FILES['preuves_liens']['name'] as $key => $name) {
            if ($_FILES['preuves_liens']['error'][$key] === UPLOAD_ERR_OK) {
                $file_data = [
                    'name' => $name,
                    'type' => $_FILES['preuves_liens']['type'][$key],
                    'tmp_name' => $_FILES['preuves_liens']['tmp_name'][$key],
                    'error' => $_FILES['preuves_liens']['error'][$key],
                    'size' => $_FILES['preuves_liens']['size'][$key]
                ];
                $uploaded_file = uploadFile($file_data, $upload_dir);
                if ($uploaded_file) {
                    $preuves_liens[] = $uploaded_file;
                }
            }
        }
    }
    $preuves_liens_str = !empty($preuves_liens) ? implode(',', $preuves_liens) : null;
    
    // Si aucune erreur, procéder à l'enregistrement
    if (empty($erreurs)) {
        try {
            // Insertion dans la base de données
            $sql = "INSERT INTO demandes_regroupement_familial (
                numero_dossier, nom_complet, date_naissance, nationalite, email, telephone,
                nom_famille, lien_parente, adresse_famille, commentaire, passeport, titre_sejour,
                acte_mariage, preuves_liens, justificatif_logement, ressources, paiement, statut
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')";
            
            $stmt = $pdo->prepare($sql);
            
            $result = $stmt->execute([
                $numero_dossier,
                $nom_complet,
                $date_naissance,
                $nationalite,
                $email,
                $telephone,
                $nom_famille,
                $lien_parente,
                $adresse_famille,
                $commentaire ?: null,
                $passeport,
                $titre_sejour,
                $acte_mariage,
                $preuves_liens_str,
                $justificatif_logement,
                $ressources,
                $paiement
            ]);
            
            if ($result) {
                afficherConfirmation($numero_dossier);
                exit;
            } else {
                throw new Exception("Erreur lors de l'enregistrement");
            }
            
        } catch (PDOException $e) {
            $erreurs[] = "Erreur base de données: " . $e->getMessage();
        } catch (Exception $e) {
            $erreurs[] = $e->getMessage();
        }
    }
    
    // Afficher les erreurs
    afficherFormulaire($erreurs);
}

// Fonction pour afficher le formulaire
function afficherFormulaire($erreurs = []) {
    ?>
    <!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visa Regroupement Familial - France</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-blue: #0055b8;
      --secondary-blue: #2c3e50;
      --accent-red: #ce1126;
      --light-blue: #e8f2ff;
      --light-gray: #f4f7fa;
      --white: #ffffff;
      --border-color: #dbe4ee;
      --text-dark: #2c3e50;
      --text-light: #6c757d;
      --success-green: #28a745;
      --shadow: 0 4px 12px rgba(0, 85, 184, 0.1);
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
      color: var(--text-dark);
      line-height: 1.6;
      padding: 20px;
    }
    
    .container {
      max-width: 900px;
      margin: 0 auto;
      background: var(--white);
      padding: 0;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--shadow);
    }
    
    header {
      background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
      color: var(--white);
      padding: 25px 30px;
      text-align: center;
      position: relative;
    }
    
    header h1 {
      font-size: 1.8rem;
      margin-bottom: 8px;
      font-weight: 600;
    }
    
    header p {
      font-size: 1rem;
      opacity: 0.9;
    }
    
    .flag-decoration {
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(90deg, var(--accent-red) 33%, var(--white) 33%, var(--white) 66%, var(--accent-red) 66%);
    }
    
    .form-content {
      padding: 30px;
    }
    
    .progress-container {
      margin-bottom: 30px;
      background: var(--light-blue);
      padding: 15px;
      border-radius: 8px;
      display: flex;
      align-items: center;
    }
    
    .progress-icon {
      background: var(--primary-blue);
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      flex-shrink: 0;
    }
    
    .progress-text {
      font-size: 0.9rem;
    }
    
    .progress-text strong {
      color: var(--primary-blue);
    }
    
    .form-section {
      margin-bottom: 35px;
      padding-bottom: 25px;
      border-bottom: 1px solid var(--border-color);
    }
    
    .form-section:last-of-type {
      border-bottom: none;
      margin-bottom: 25px;
    }
    
    .form-section h2 {
      color: var(--primary-blue);
      font-size: 1.4rem;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--light-blue);
      display: flex;
      align-items: center;
    }
    
    .form-section h2 i {
      margin-right: 10px;
      color: var(--primary-blue);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      font-weight: 600;
      display: block;
      margin-bottom: 8px;
      color: var(--text-dark);
    }
    
    .required::after {
      content: " *";
      color: var(--accent-red);
    }
    
    input, select, textarea {
      width: 100%;
      padding: 14px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-size: 1rem;
      transition: var(--transition);
      background: var(--white);
    }
    
    input:focus, select:focus, textarea:focus {
      outline: none;
      border-color: var(--primary-blue);
      box-shadow: 0 0 0 3px rgba(0, 85, 184, 0.2);
    }
    
    .file-input {
      border: 2px dashed var(--border-color);
      padding: 20px;
      background: var(--light-blue);
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
    }
    
    .file-input:hover {
      border-color: var(--primary-blue);
      background: #e1ecff;
    }
    
    .file-hint {
      font-size: 0.85rem;
      color: var(--text-light);
      margin-top: 6px;
      display: block;
    }
    
    .form-actions {
      text-align: center;
      margin-top: 30px;
      padding-top: 20px;
      border-top: 1px solid var(--border-color);
    }
    
    .btn {
      background: linear-gradient(to right, var(--primary-blue), #0066cc);
      color: var(--white);
      padding: 16px 35px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1.1rem;
      font-weight: 600;
      transition: var(--transition);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 8px rgba(0, 85, 184, 0.3);
    }
    
    .btn:hover {
      background: linear-gradient(to right, #004a9e, #0055b8);
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0, 85, 184, 0.4);
    }
    
    .btn:active {
      transform: translateY(0);
    }
    
    .btn i {
      margin-right: 10px;
    }
    
    footer {
      text-align: center;
      padding: 20px;
      background: var(--light-blue);
      color: var(--text-light);
      font-size: 0.9rem;
      border-top: 1px solid var(--border-color);
    }
    
    /* Styles pour les erreurs */
    .error-message {
      background: #f8d7da;
      color: #721c24;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border-left: 4px solid #dc3545;
    }
    
    .error-message ul {
      margin: 10px 0 0 20px;
    }
    
    .checkbox-group {
      display: flex;
      align-items: flex-start;
      margin-bottom: 15px;
    }
    
    .checkbox-group input[type="checkbox"] {
      width: auto;
      margin-right: 10px;
      margin-top: 5px;
    }
    
    .checkbox-group label {
      margin-bottom: 0;
      font-weight: normal;
    }
    
    .confirmation-message {
      background-color: var(--success-green);
      color: white;
      padding: 15px;
      border-radius: 8px;
      margin-top: 20px;
      text-align: center;
      display: none;
    }
    
    .confirmation-message i {
      font-size: 2rem;
      margin-bottom: 10px;
    }
    
    @media (min-width: 768px) {
      .form-row {
        display: flex;
        gap: 20px;
      }
      
      .form-row .form-group {
        flex: 1;
      }
      
      header h1 {
        font-size: 2.2rem;
      }
    }
    
    @media (max-width: 767px) {
      .form-content {
        padding: 20px;
      }
      
      .progress-container {
        flex-direction: column;
        text-align: center;
      }
      
      .progress-icon {
        margin-right: 0;
        margin-bottom: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <header>
      <h1><i class="fa-solid fa-people-group"></i> Demande de Visa - Regroupement Familial</h1>
      <p>Remplissez ce formulaire pour initier votre demande de regroupement familial en France</p>
      <div class="flag-decoration"></div>
    </header>
    
    <div class="form-content">
      <?php if (!empty($erreurs)): ?>
        <div class="error-message">
          <strong><i class="fas fa-exclamation-triangle"></i> Erreurs :</strong>
          <ul>
            <?php foreach ($erreurs as $erreur): ?>
              <li><?= htmlspecialchars($erreur) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      
      <div class="progress-container">
        <div class="progress-icon">
          <i class="fa-solid fa-file-lines"></i>
        </div>
        <div class="progress-text">
          <strong>Formulaire administratif</strong> - Veuillez remplir tous les champs obligatoires <span style="color: var(--accent-red)">*</span> et fournir les documents demandés.
        </div>
      </div>
      
      <form method="post" action="famille.php" enctype="multipart/form-data" id="visaForm">
        
        <!-- Infos personnelles demandeur -->
        <div class="form-section">
          <h2><i class="fa-solid fa-user"></i> Informations personnelles du demandeur</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="nom_complet" class="required">Nom et prénom</label>
              <input type="text" id="nom_complet" name="nom_complet" value="<?= htmlspecialchars($_POST['nom_complet'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
              <label for="date_naissance" class="required">Date de naissance</label>
              <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="nationalite" class="required">Nationalité</label>
              <input type="text" id="nationalite" name="nationalite" value="<?= htmlspecialchars($_POST['nationalite'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
              <label for="email" class="required">Email</label>
              <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="telephone" class="required">Téléphone</label>
            <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>" required>
          </div>
          
          <div class="form-group">
            <label for="passeport" class="required">Copie du passeport</label>
            <input type="file" id="passeport" name="passeport" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            <span class="file-hint">Formats acceptés: PDF, JPG, JPEG, PNG (taille max: 5MB)</span>
          </div>
        </div>

        <!-- Infos sur la famille -->
        <div class="form-section">
          <h2><i class="fa-solid fa-house-user"></i> Informations sur la famille en France</h2>
          
          <div class="form-group">
            <label for="nom_famille" class="required">Nom et prénom du membre de la famille</label>
            <input type="text" id="nom_famille" name="nom_famille" value="<?= htmlspecialchars($_POST['nom_famille'] ?? '') ?>" required>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="lien_parente" class="required">Lien de parenté</label>
              <select id="lien_parente" name="lien_parente" required>
                <option value="">-- Sélectionner --</option>
                <option value="conjoint" <?= ($_POST['lien_parente'] ?? '') === 'conjoint' ? 'selected' : '' ?>>Conjoint(e)</option>
                <option value="enfant" <?= ($_POST['lien_parente'] ?? '') === 'enfant' ? 'selected' : '' ?>>Enfant</option>
                <option value="parent" <?= ($_POST['lien_parente'] ?? '') === 'parent' ? 'selected' : '' ?>>Parent</option>
                <option value="autre" <?= ($_POST['lien_parente'] ?? '') === 'autre' ? 'selected' : '' ?>>Autre</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="adresse_famille" class="required">Adresse complète en France</label>
              <textarea id="adresse_famille" name="adresse_famille" rows="3" required><?= htmlspecialchars($_POST['adresse_famille'] ?? '') ?></textarea>
            </div>
          </div>
          
          <div class="form-group">
            <label for="titre_sejour" class="required">Copie du titre de séjour / nationalité du membre de famille</label>
            <input type="file" id="titre_sejour" name="titre_sejour" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            <span class="file-hint">Document attestant du statut légal de votre famille en France</span>
          </div>
        </div>

        <!-- Documents requis -->
        <div class="form-section">
          <h2><i class="fa-solid fa-file-lines"></i> Documents justificatifs</h2>
          
          <div class="form-group">
            <label for="acte_mariage" class="required">Acte de mariage / acte de naissance</label>
            <input type="file" id="acte_mariage" name="acte_mariage" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            <span class="file-hint">Document officiel attestant du lien familial</span>
          </div>
          
          <div class="form-group">
            <label for="preuves_liens">Preuves de liens familiaux (photos, documents, etc.)</label>
            <input type="file" id="preuves_liens" name="preuves_liens[]" multiple class="file-input" accept=".pdf,.jpg,.jpeg,.png">
            <span class="file-hint">Vous pouvez sélectionner plusieurs fichiers (max 10MB au total)</span>
          </div>
          
          <div class="form-group">
            <label for="justificatif_logement" class="required">Justificatif de logement en France</label>
            <input type="file" id="justificatif_logement" name="justificatif_logement" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            <span class="file-hint">Facture récente, bail de location ou attestation d'hébergement</span>
          </div>
          
          <div class="form-group">
            <label for="ressources" class="required">Preuves de ressources financières</label>
            <input type="file" id="ressources" name="ressources" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            <span class="file-hint">Relevés bancaires, fiches de paie, avis d'imposition (3 derniers mois)</span>
          </div>
        </div>

        <!-- Paiement et avis favorable -->
        <div class="form-section">
          <h2><i class="fa-solid fa-credit-card"></i> Paiement et validation</h2>
          
          <div class="form-group">
            <label for="paiement" class="required">Reçu de paiement</label>
            <input type="file" id="paiement" name="paiement" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>
          
          <div class="checkbox-group">
            <input type="checkbox" id="avis_favorable" name="avis_favorable" required>
            <label for="avis_favorable" class="required">Je certifie sur l'honneur que toutes les informations fournies dans ce formulaire sont exactes et complètes. J'accepte que ces données soient traitées conformément à la réglementation sur la protection des données.</label>
          </div>
        </div>

        <!-- Commentaire -->
        <div class="form-section">
          <h2><i class="fa-solid fa-comment"></i> Informations supplémentaires</h2>
          <div class="form-group">
            <label for="commentaire">Commentaire ou informations complémentaires</label>
            <textarea id="commentaire" name="commentaire" rows="4" placeholder="Vous pouvez ajouter ici toute information que vous jugez utile pour le traitement de votre dossier..."><?= htmlspecialchars($_POST['commentaire'] ?? '') ?></textarea>
          </div>
        </div>

        <!-- Boutons -->
        <div class="form-actions">
          <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Envoyer la demande</button>
        </div>
      </form>
    </div>
    
    <footer>
      <p>© 2025 Babylone service. Tous droits réservés.</p>
    </footer>
  </div>

  <script>
    document.getElementById('visaForm').addEventListener('submit', function(e) {
      // La validation est maintenant gérée côté serveur
      // Vous pouvez ajouter ici de la validation JavaScript supplémentaire si nécessaire
    });
  </script>
</body>
</html>
    <?php
}

// Fonction pour afficher la confirmation
function afficherConfirmation($numero_dossier) {
    ?>
    <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de demande</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0055b8;
            --success-green: #28a745;
            --light-gray: #f4f7fa;
            --white: #ffffff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-gray);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .confirmation-container {
            background: var(--white);
            padding: 40px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        
        .success-icon {
            font-size: 4rem;
            color: var(--success-green);
            margin-bottom: 20px;
        }
        
        .dossier-number {
            background: var(--light-gray);
            padding: 15px;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: bold;
            margin: 20px 0;
            border-left: 4px solid var(--success-green);
        }
        
        .btn {
            background: var(--primary-blue);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            background: #004494;
            transform: translateY(-2px);
        }
        
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
            border-left: 4px solid var(--primary-blue);
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1>Demande Enregistrée avec Succès !</h1>
        <p>Votre demande de regroupement familial a été enregistrée avec succès.</p>
        
        <div class="dossier-number">
            <i class="fas fa-file-alt"></i><br>
            Numéro de dossier: <strong><?= htmlspecialchars($numero_dossier) ?></strong>
        </div>
        
        <div class="info-box">
            <h3><i class="fas fa-info-circle"></i> Prochaines étapes :</h3>
            <ul>
                <li>Vous recevrez un email de confirmation sous 24 heures</li>
                <li>Votre dossier sera examiné sous 15 jours ouvrés</li>
                <li>Conservez précieusement votre numéro de dossier</li>
            </ul>
        </div>
        
        <div>
            <a href="famille.php" class="btn">
                <i class="fas fa-plus"></i> Nouvelle demande
            </a>
        </div>
    </div>
</body>
</html>
    <?php
}
?>