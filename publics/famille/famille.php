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
      <div class="progress-container">
        <div class="progress-icon">
          <i class="fa-solid fa-file-lines"></i>
        </div>
        <div class="progress-text">
          <strong>Formulaire administratif</strong> - Veuillez remplir tous les champs obligatoires <span style="color: var(--accent-red)">*</span> et fournir les documents demandés.
        </div>
      </div>
      
      <form method="post" action="save_famille.php" enctype="multipart/form-data">
        
        <!-- Infos personnelles demandeur -->
        <div class="form-section">
          <h2><i class="fa-solid fa-user"></i> Informations personnelles du demandeur</h2>
          
          <div class="form-row">
            <div class="form-group">
              <label for="nom_complet" class="required">Nom et prénom</label>
              <input type="text" id="nom_complet" name="nom_complet" required>
            </div>
            
            <div class="form-group">
              <label for="date_naissance" class="required">Date de naissance</label>
              <input type="date" id="date_naissance" name="date_naissance" required>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="nationalite" class="required">Nationalité</label>
              <input type="text" id="nationalite" name="nationalite" required>
            </div>
            
            <div class="form-group">
              <label for="email" class="required">Email</label>
              <input type="email" id="email" name="email" required>
            </div>
          </div>
          
          <div class="form-group">
            <label for="telephone" class="required">Téléphone</label>
            <input type="tel" id="telephone" name="telephone" required>
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
            <input type="text" id="nom_famille" name="nom_famille" required>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="lien_parente" class="required">Lien de parenté</label>
              <select id="lien_parente" name="lien_parente" required>
                <option value="">-- Sélectionner --</option>
                <option value="conjoint">Conjoint(e)</option>
                <option value="enfant">Enfant</option>
                <option value="parent">Parent</option>
                <option value="autre">Autre</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="adresse_famille" class="required">Adresse complète en France</label>
              <textarea id="adresse_famille" name="adresse_famille" rows="3" required></textarea>
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

        <!-- Commentaire -->
        <div class="form-section">
          <h2><i class="fa-solid fa-comment"></i> Informations supplémentaires</h2>
          <div class="form-group">
            <label for="commentaire">Commentaire ou informations complémentaires</label>
            <textarea id="commentaire" name="commentaire" rows="4" placeholder="Vous pouvez ajouter ici toute information que vous jugez utile pour le traitement de votre dossier..."></textarea>
          </div>
        </div>

        <!-- Boutons -->
        <div class="form-actions">
          <button type="submit" class="btn"><i class="fa-solid fa-paper-plane"></i> Envoyer la demande</button>
        </div>
      </form>
    </div>
    
    <footer>
      <p>© 2023 Ministère de l'Intérieur - République Française. Tous droits réservés.</p>
    </footer>
  </div>
</body>
</html>