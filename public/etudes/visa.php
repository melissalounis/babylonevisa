<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Demande de Visa Études";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="visa-hero">
  <div class="visa-hero-content">
    <h1><i class="fas fa-passport"></i> Demande de Visa Études</h1>
    <p>Formulaire unique pour votre visa étudiant en Europe</p>
  </div>
</div>

<div class="visa-container">
  <div class="form-header">
    <h2>Formulaire de Demande de Visa Étudiant</h2>
    <p>Remplissez soigneusement toutes les sections <span class="required-star">*</span></p>
  </div>

  <form method="POST" action="save_visa_etudes.php" enctype="multipart/form-data" id="visaForm" class="visa-form">
    
    <!-- Section 1: Choix du Pays -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-flag"></i>
        <h3>1. Destination d'études</h3>
      </div>
      <div class="form-group">
        <label for="pays_destination"><span class="required-star">*</span> Pays de destination</label>
        <select id="pays_destination" name="pays_destination" class="form-control" required>
          <option value="">-- Sélectionnez votre pays d'études --</option>
          <option value="france">🇫🇷 France</option>
          <option value="belgique">🇧🇪 Belgique</option>
          <option value="espagne">🇪🇸 Espagne</option>
          <option value="italie">🇮🇹 Italie</option>
          <option value="turquie">🇹🇷 Turquie</option>
          <option value="roumanie">🇷🇴 Roumanie</option>
          <option value="luxembourg">🇱🇺 Luxembourg</option>
          <option value="bulgarie">🇧🇬 Bulgarie</option>
        </select>
      </div>
    </div>

    <!-- Section 2: Informations Personnelles -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-user"></i>
        <h3>2. Informations Personnelles</h3>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="nom"><span class="required-star">*</span> Nom</label>
          <input type="text" id="nom" name="nom" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="prenom"><span class="required-star">*</span> Prénom</label>
          <input type="text" id="prenom" name="prenom" class="form-control" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="date_naissance"><span class="required-star">*</span> Date de naissance</label>
          <input type="date" id="date_naissance" name="date_naissance" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="lieu_naissance"><span class="required-star">*</span> Lieu de naissance</label>
          <input type="text" id="lieu_naissance" name="lieu_naissance" class="form-control" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="nationalite"><span class="required-star">*</span> Nationalité</label>
          <input type="text" id="nationalite" name="nationalite" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="sexe"><span class="required-star">*</span> Sexe</label>
          <select id="sexe" name="sexe" class="form-control" required>
            <option value="">-- Sélectionnez --</option>
            <option value="M">Masculin</option>
            <option value="F">Féminin</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="situation_familiale"><span class="required-star">*</span> Situation familiale</label>
          <select id="situation_familiale" name="situation_familiale" class="form-control" required>
            <option value="">-- Sélectionnez --</option>
            <option value="celibataire">Célibataire</option>
            <option value="marie">Marié(e)</option>
            <option value="divorce">Divorcé(e)</option>
            <option value="veuf">Veuf/Veuve</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Section 3: Informations de Contact -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-address-book"></i>
        <h3>3. Informations de Contact</h3>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="adresse"><span class="required-star">*</span> Adresse complète</label>
          <textarea id="adresse" name="adresse" class="form-control" rows="3" required></textarea>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="telephone"><span class="required-star">*</span> Téléphone</label>
          <input type="tel" id="telephone" name="telephone" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="email"><span class="required-star">*</span> Email</label>
          <input type="email" id="email" name="email" class="form-control" required>
        </div>
      </div>
    </div>

    <!-- Section 4: Informations Passeport -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-passport"></i>
        <h3>4. Informations Passeport</h3>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="numero_passeport"><span class="required-star">*</span> Numéro de passeport</label>
          <input type="text" id="numero_passeport" name="numero_passeport" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="date_delivrance"><span class="required-star">*</span> Date de délivrance</label>
          <input type="date" id="date_delivrance" name="date_delivrance" class="form-control" required>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="date_expiration"><span class="required-star">*</span> Date d'expiration</label>
          <input type="date" id="date_expiration" name="date_expiration" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="lieu_delivrance"><span class="required-star">*</span> Lieu de délivrance</label>
          <input type="text" id="lieu_delivrance" name="lieu_delivrance" class="form-control" required>
        </div>
      </div>

      <div class="form-group">
        <label for="passeport"><span class="required-star">*</span> Copie du passeport (pages principales)</label>
        <input type="file" id="passeport" name="passeport" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
        <span class="file-hint">Format: PDF, JPG, PNG (max 5MB)</span>
      </div>
    </div>

    <!-- Section 5: Informations Académiques -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-graduation-cap"></i>
        <h3>5. Informations Académiques</h3>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="etablissement"><span class="required-star">*</span> Établissement d'accueil</label>
          <input type="text" id="etablissement" name="etablissement" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="niveau_etudes"><span class="required-star">*</span> Niveau d'études</label>
          <select id="niveau_etudes" name="niveau_etudes" class="form-control" required>
            <option value="">-- Sélectionnez --</option>
            <option value="licence">Licence/Bachelor</option>
            <option value="master">Master</option>
            <option value="doctorat">Doctorat</option>
            <option value="echange">Échange universitaire</option>
            <option value="langue">Cours de langue</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="domaine_etudes"><span class="required-star">*</span> Domaine d'études</label>
          <input type="text" id="domaine_etudes" name="domaine_etudes" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="duree_etudes"><span class="required-star">*</span> Durée des études (mois)</label>
          <input type="number" id="duree_etudes" name="duree_etudes" class="form-control" min="1" required>
        </div>
      </div>

      <div class="form-group">
        <label for="date_debut"><span class="required-star">*</span> Date de début des cours</label>
        <input type="date" id="date_debut" name="date_debut" class="form-control" required>
      </div>
    </div>

    <!-- Section 6: Test de Langue -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-language"></i>
        <h3>6. Test de Langue</h3>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="type_test_langue">Type de test de langue</label>
          <select id="type_test_langue" name="type_test_langue" class="form-control">
            <option value="">-- Sélectionnez --</option>
            <option value="delf">DELF/DALF</option>
            <option value="tcf">TCF</option>
            <option value="ielts">IELTS</option>
            <option value="toefl">TOEFL</option>
            <option value="celi">CELI (Italie)</option>
            <option value="dele">DELE (Espagne)</option>
            <option value="autre">Autre</option>
          </select>
        </div>
        <div class="form-group">
          <label for="score_langue">Score obtenu</label>
          <input type="text" id="score_langue" name="score_langue" class="form-control">
        </div>
      </div>

      <div class="form-group">
        <label for="test_langue">Certificat de test de langue</label>
        <input type="file" id="test_langue" name="test_langue" class="file-input" accept=".pdf,.jpg,.jpeg,.png">
        <span class="file-hint">Format: PDF, JPG, PNG (max 5MB)</span>
      </div>
    </div>

    <!-- Section 7: Lettre d'Acceptation -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-envelope-open-text"></i>
        <h3>7. Lettre d'Acceptation</h3>
      </div>
      
      <div class="form-group">
        <label for="lettre_acceptation"><span class="required-star">*</span> Lettre d'acceptation de l'établissement</label>
        <input type="file" id="lettre_acceptation" name="lettre_acceptation" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
        <span class="file-hint">Document officiel d'admission (max 5MB)</span>
      </div>
    </div>

    <!-- Section 8: Hébergement (Optionnel) -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-home"></i>
        <h3>8. Hébergement <small>(Optionnel)</small></h3>
      </div>
      
      <div class="form-group">
        <label for="type_hebergement">Type d'hébergement</label>
        <select id="type_hebergement" name="type_hebergement" class="form-control">
          <option value="">-- Sélectionnez --</option>
          <option value="crous">Résidence universitaire (CROUS)</option>
          <option value="residence">Résidence étudiante privée</option>
          <option value="appartement">Appartement</option>
          <option value="colocation">Colocation</option>
          <option value="famille">Famille d'accueil</option>
          <option value="autre">Autre</option>
        </select>
      </div>

      <div class="form-group">
        <label for="adresse_hebergement">Adresse d'hébergement</label>
        <textarea id="adresse_hebergement" name="adresse_hebergement" class="form-control" rows="3"></textarea>
      </div>

      <div class="form-group">
        <label for="justificatif_hebergement">Justificatif d'hébergement</label>
        <input type="file" id="justificatif_hebergement" name="justificatif_hebergement" class="file-input" accept=".pdf,.jpg,.jpeg,.png">
        <span class="file-hint">Contrat de location, attestation d'hébergement, etc. (max 5MB)</span>
      </div>
    </div>

    <!-- Section 9: Garants Financiers (Dynamique) -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-hand-holding-usd"></i>
        <h3>9. Garants Financiers</h3>
        <button type="button" class="btn-add" onclick="addGarant()">
          <i class="fas fa-plus"></i> Ajouter un garant
        </button>
      </div>
      
      <div id="garants-container">
        <!-- Premier garant -->
        <div class="garant-item" data-index="0">
          <div class="garant-header">
            <h4>Garant #1</h4>
            <button type="button" class="btn-remove" onclick="removeGarant(this)" style="display: none;">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="nom_garant_0"><span class="required-star">*</span> Nom du garant</label>
              <input type="text" id="nom_garant_0" name="garants[0][nom]" class="form-control" required>
            </div>
            <div class="form-group">
              <label for="lien_garant_0"><span class="required-star">*</span> Lien avec le garant</label>
              <select id="lien_garant_0" name="garants[0][lien]" class="form-control" required>
                <option value="">-- Sélectionnez --</option>
                <option value="parent">Parent</option>
                <option value="tuteur">Tuteur</option>
                <option value="famille">Membre de la famille</option>
                <option value="ami">Ami</option>
                <option value="autre">Autre</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="adresse_garant_0"><span class="required-star">*</span> Adresse du garant</label>
              <textarea id="adresse_garant_0" name="garants[0][adresse]" class="form-control" rows="3" required></textarea>
            </div>
          </div>
          <div class="form-group">
            <label for="justificatif_garant_0"><span class="required-star">*</span> Justificatif de garantie financière</label>
            <input type="file" id="justificatif_garant_0" name="garants[0][justificatif]" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            <span class="file-hint">Relevés bancaires, attestation de prise en charge, etc. (max 5MB)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 10: Reçu de Paiement -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-receipt"></i>
        <h3>10. Reçu de Paiement</h3>
      </div>

      <div class="form-group">
        <label for="recu_paiement"><span class="required-star">*</span> Reçu de paiement des frais de visa</label>
        <input type="file" id="recu_paiement" name="recu_paiement" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
        <span class="file-hint">Preuve de paiement des frais de visa (max 5MB)</span>
      </div>
    </div>

    <!-- Section 11: Documents Supplémentaires (Dynamique) -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-file-alt"></i>
        <h3>11. Documents Supplémentaires</h3>
        <button type="button" class="btn-add" onclick="addDocument()">
          <i class="fas fa-plus"></i> Ajouter un document
        </button>
      </div>
      
      <div id="documents-container">
        <!-- Premier document -->
        <div class="document-item" data-index="0">
          <div class="document-header">
            <h4>Document #1</h4>
            <button type="button" class="btn-remove" onclick="removeDocument(this)" style="display: none;">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="type_document_0"><span class="required-star">*</span> Type de document</label>
              <input type="text" id="type_document_0" name="documents[0][type]" class="form-control" placeholder="Ex: Diplôme, CV, Assurance, etc." required>
            </div>
            <div class="form-group">
              <label for="fichier_document_0"><span class="required-star">*</span> Fichier</label>
              <input type="file" id="fichier_document_0" name="documents[0][fichier]" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 12: Déclaration -->
    <div class="form-section">
      <div class="section-header">
        <i class="fas fa-shield-alt"></i>
        <h3>12. Déclaration</h3>
      </div>
      
      <div class="declaration">
        <div class="checkbox-group">
          <input type="checkbox" id="declaration" name="declaration" required>
          <label for="declaration">
            <span class="required-star">*</span> Je certifie sur l'honneur l'exactitude des informations fournies dans ce formulaire. 
            J'accepte que ces données soient traitées conformément à la réglementation sur la protection des données.
          </label>
        </div>
      </div>
    </div>

    <!-- Boutons de soumission -->
    <div class="form-actions">
      <button type="button" class="btn btn-secondary" onclick="resetForm()">
        <i class="fas fa-undo"></i>
        Réinitialiser
      </button>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-paper-plane"></i>
        Soumettre la demande
      </button>
    </div>
  </form>
</div>

<style>
  :root {
    --primary-blue: #0055a4;
    --secondary-blue: #2c3e50;
    --accent-red: #ef4135;
    --success-green: #28a745;
    --warning-orange: #ff9800;
    --light-gray: #f8f9fa;
    --border-color: #dee2e6;
    --text-dark: #333;
    --text-light: #6c757d;
    --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
  }

  .visa-hero {
    background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
    color: white;
    padding: 60px 20px;
    text-align: center;
  }

  .visa-hero-content h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 700;
  }

  .visa-hero-content p {
    font-size: 1.2rem;
    opacity: 0.9;
  }

  .visa-container {
    max-width: 1000px;
    margin: 40px auto;
    background: white;
    border-radius: 12px;
    box-shadow: var(--shadow);
    overflow: hidden;
  }

  .form-header {
    background: var(--light-gray);
    padding: 30px;
    text-align: center;
    border-bottom: 1px solid var(--border-color);
  }

  .form-header h2 {
    color: var(--primary-blue);
    margin-bottom: 10px;
    font-size: 1.8rem;
  }

  .form-header p {
    color: var(--text-light);
    font-size: 1.1rem;
  }

  .required-star {
    color: var(--accent-red);
    font-weight: bold;
    margin-right: 4px;
  }

  .visa-form {
    padding: 0;
  }

  .form-section {
    padding: 30px;
    border-bottom: 1px solid var(--border-color);
  }

  .form-section:last-of-type {
    border-bottom: none;
  }

  .section-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--light-gray);
  }

  .section-header i {
    font-size: 1.5rem;
    color: var(--primary-blue);
  }

  .section-header h3 {
    color: var(--secondary-blue);
    font-size: 1.4rem;
    margin: 0;
  }

  .form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text-dark);
  }

  .form-control {
    width: 100%;
    padding: 14px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 1rem;
    transition: var(--transition);
    background: white;
  }

  .form-control:focus {
    outline: none;
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(0, 85, 164, 0.1);
  }

  textarea.form-control {
    resize: vertical;
    min-height: 100px;
  }

  .file-input {
    border: 2px dashed var(--border-color);
    padding: 20px;
    background: var(--light-gray);
    text-align: center;
    cursor: pointer;
    transition: var(--transition);
    width: 100%;
  }

  .file-input:hover {
    border-color: var(--primary-blue);
    background: #e8f1ff;
  }

  .file-hint {
    font-size: 0.85rem;
    color: var(--text-light);
    margin-top: 6px;
    display: block;
  }

  .declaration {
    background: #fff9e6;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid var(--warning-orange);
  }

  .checkbox-group {
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }

  .checkbox-group input[type="checkbox"] {
    margin-top: 3px;
    transform: scale(1.2);
  }

  .checkbox-group label {
    margin-bottom: 0;
    font-weight: normal;
    line-height: 1.5;
  }

  .form-actions {
    padding: 30px;
    background: var(--light-gray);
    display: flex;
    gap: 15px;
    justify-content: center;
    border-top: 1px solid var(--border-color);
  }

  .btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 30px;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
  }

  .btn-primary {
    background: linear-gradient(135deg, var(--primary-blue), #004494);
    color: white;
    box-shadow: 0 4px 15px rgba(0, 85, 164, 0.3);
  }

  .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 85, 164, 0.4);
  }

  .btn-secondary {
    background: white;
    color: var(--text-dark);
    border: 2px solid var(--border-color);
  }

  .btn-secondary:hover {
    background: var(--light-gray);
    border-color: var(--text-light);
  }

  .btn-add {
    background: var(--success-green);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .btn-add:hover {
    background: #218838;
    transform: translateY(-1px);
  }

  .btn-remove {
    background: var(--accent-red);
    color: white;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
  }

  .btn-remove:hover {
    background: #d32f2f;
    transform: scale(1.1);
  }

  .garant-item, .document-item {
    background: var(--light-gray);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid var(--primary-blue);
  }

  .garant-header, .document-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
  }

  .garant-header h4, .document-header h4 {
    color: var(--secondary-blue);
    margin: 0;
  }

  /* Validation styles */
  .field-valid {
    border-color: var(--success-green) !important;
    background-color: #f8fff9;
  }

  .field-invalid {
    border-color: var(--accent-red) !important;
    background-color: #fff8f8;
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .visa-container {
      margin: 20px;
      border-radius: 8px;
    }

    .form-section {
      padding: 20px;
    }

    .form-row {
      grid-template-columns: 1fr;
      gap: 15px;
    }

    .visa-hero-content h1 {
      font-size: 2rem;
    }

    .form-actions {
      flex-direction: column;
    }

    .btn {
      width: 100%;
      justify-content: center;
    }

    .section-header {
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
    }
  }

  @media (max-width: 480px) {
    .visa-hero {
      padding: 40px 15px;
    }

    .visa-hero-content h1 {
      font-size: 1.8rem;
    }

    .form-header {
      padding: 20px;
    }

    .form-header h2 {
      font-size: 1.5rem;
    }
  }

  /* Animation pour les nouvelles sections */
  .garant-item, .document-item {
    animation: slideDown 0.3s ease-out;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
      transform: translateY(-10px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  /* Style pour les sections optionnelles */
  .form-section h3 small {
    font-size: 0.8em;
    color: var(--text-light);
    font-weight: normal;
  }

  /* Amélioration du file input */
  input[type="file"] {
    padding: 12px;
  }

  /* Style pour les selects */
  select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
  }

  /* Loading state */
  .btn-loading {
    opacity: 0.7;
    pointer-events: none;
  }

  .btn-loading::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }

  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>

<script>
  let garantCount = 1;
  let documentCount = 1;

  // Ajouter un garant
  function addGarant() {
    const container = document.getElementById('garants-container');
    const newGarant = document.createElement('div');
    newGarant.className = 'garant-item';
    newGarant.setAttribute('data-index', garantCount);
    
    newGarant.innerHTML = `
      <div class="garant-header">
        <h4>Garant #${garantCount + 1}</h4>
        <button type="button" class="btn-remove" onclick="removeGarant(this)">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="nom_garant_${garantCount}"><span class="required-star">*</span> Nom du garant</label>
          <input type="text" id="nom_garant_${garantCount}" name="garants[${garantCount}][nom]" class="form-control" required>
        </div>
        <div class="form-group">
          <label for="lien_garant_${garantCount}"><span class="required-star">*</span> Lien avec le garant</label>
          <select id="lien_garant_${garantCount}" name="garants[${garantCount}][lien]" class="form-control" required>
            <option value="">-- Sélectionnez --</option>
            <option value="parent">Parent</option>
            <option value="tuteur">Tuteur</option>
            <option value="famille">Membre de la famille</option>
            <option value="ami">Ami</option>
            <option value="autre">Autre</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="adresse_garant_${garantCount}"><span class="required-star">*</span> Adresse du garant</label>
          <textarea id="adresse_garant_${garantCount}" name="garants[${garantCount}][adresse]" class="form-control" rows="3" required></textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="justificatif_garant_${garantCount}"><span class="required-star">*</span> Justificatif de garantie financière</label>
        <input type="file" id="justificatif_garant_${garantCount}" name="garants[${garantCount}][justificatif]" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
        <span class="file-hint">Relevés bancaires, attestation de prise en charge, etc. (max 5MB)</span>
      </div>
    `;
    
    container.appendChild(newGarant);
    garantCount++;
  }

  // Supprimer un garant
  function removeGarant(button) {
    const garantItem = button.closest('.garant-item');
    garantItem.remove();
    // Recalculer les numéros
    const garants = document.querySelectorAll('.garant-item');
    garants.forEach((garant, index) => {
      garant.querySelector('h4').textContent = `Garant #${index + 1}`;
    });
    
    // Cacher le bouton supprimer s'il ne reste qu'un garant
    if (garants.length === 1) {
      garants[0].querySelector('.btn-remove').style.display = 'none';
    }
  }

  // Ajouter un document
  function addDocument() {
    const container = document.getElementById('documents-container');
    const newDocument = document.createElement('div');
    newDocument.className = 'document-item';
    newDocument.setAttribute('data-index', documentCount);
    
    newDocument.innerHTML = `
      <div class="document-header">
        <h4>Document #${documentCount + 1}</h4>
        <button type="button" class="btn-remove" onclick="removeDocument(this)">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="type_document_${documentCount}"><span class="required-star">*</span> Type de document</label>
          <input type="text" id="type_document_${documentCount}" name="documents[${documentCount}][type]" class="form-control" placeholder="Ex: Diplôme, CV, Assurance, etc." required>
        </div>
        <div class="form-group">
          <label for="fichier_document_${documentCount}"><span class="required-star">*</span> Fichier</label>
          <input type="file" id="fichier_document_${documentCount}" name="documents[${documentCount}][fichier]" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
      </div>
    `;
    
    container.appendChild(newDocument);
    documentCount++;
  }

  // Supprimer un document
  function removeDocument(button) {
    const documentItem = button.closest('.document-item');
    documentItem.remove();
    // Recalculer les numéros
    const documents = document.querySelectorAll('.document-item');
    documents.forEach((doc, index) => {
      doc.querySelector('h4').textContent = `Document #${index + 1}`;
    });
    
    // Cacher le bouton supprimer s'il ne reste qu'un document
    if (documents.length === 1) {
      documents[0].querySelector('.btn-remove').style.display = 'none';
    }
  }

  // Validation du formulaire améliorée
  document.getElementById('visaForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Ajouter l'état de chargement
    submitBtn.classList.add('btn-loading');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Traitement en cours...';
    
    try {
      // Validation des champs requis
      const requiredFields = this.querySelectorAll('[required]');
      let isValid = true;
      
      requiredFields.forEach(field => {
        field.classList.remove('field-invalid', 'field-valid');
        if (!field.value || (field.type === 'checkbox' && !field.checked) || (field.type === 'file' && !field.files.length)) {
          field.classList.add('field-invalid');
          isValid = false;
        } else {
          field.classList.add('field-valid');
        }
      });
      
      if (!isValid) {
        alert('Veuillez remplir tous les champs obligatoires marqués d\'un astérisque (*)');
        return;
      }
      
      // Validation des fichiers
      const files = this.querySelectorAll('input[type="file"]');
      let totalSize = 0;
      
      for (let fileInput of files) {
        if (fileInput.files.length > 0) {
          for (let file of fileInput.files) {
            if (file.size > 5 * 1024 * 1024) {
              alert(`Le fichier "${file.name}" dépasse la taille maximale de 5MB`);
              fileInput.classList.add('field-invalid');
              return;
            }
            totalSize += file.size;
          }
        }
      }
      
      if (totalSize > 50 * 1024 * 1024) {
        alert('La taille totale des fichiers dépasse 50MB. Veuillez compresser vos fichiers.');
        return;
      }
      
      // Validation des dates
      const dateDebut = document.getElementById('date_debut');
      const dateExpiration = document.getElementById('date_expiration');
      const aujourdhui = new Date();
      
      if (new Date(dateDebut.value) < aujourdhui) {
        alert('La date de début des cours doit être dans le futur');
        dateDebut.classList.add('field-invalid');
        return;
      }
      
      if (new Date(dateExpiration.value) < aujourdhui) {
        alert('La date d\'expiration du passeport doit être dans le futur');
        dateExpiration.classList.add('field-invalid');
        return;
      }
      
      // Confirmation finale
      const confirmation = confirm(
        'Êtes-vous sûr de vouloir soumettre votre demande de visa étudiant ?\n\n' +
        'Vérifiez que toutes les informations sont correctes avant de continuer.'
      );
      
      if (confirmation) {
        // Simulation d'envoi (remplacer par l'appel réel)
        console.log('Envoi du formulaire...');
        
        // Pour la démo, on simule un délai
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        alert('Votre demande a été soumise avec succès !\n\nVous recevrez un email de confirmation sous peu.');
        this.submit();
      }
      
    } catch (error) {
      console.error('Erreur lors de la soumission:', error);
      alert('Une erreur est survenue lors de la soumission. Veuillez réessayer.');
    } finally {
      // Restaurer le bouton
      submitBtn.classList.remove('btn-loading');
      submitBtn.disabled = false;
      submitBtn.innerHTML = originalText;
    }
  });

  function resetForm() {
    if (confirm('Êtes-vous sûr de vouloir réinitialiser le formulaire ? Toutes les données seront perdues.')) {
      document.getElementById('visaForm').reset();
      // Réinitialiser les compteurs dynamiques
      garantCount = 1;
      documentCount = 1;
      
      // Garder seulement le premier garant et document
      const garants = document.querySelectorAll('.garant-item');
      const documents = document.querySelectorAll('.document-item');
      
      garants.forEach((garant, index) => {
        if (index > 0) garant.remove();
      });
      
      documents.forEach((doc, index) => {
        if (index > 0) doc.remove();
      });
      
      // Réinitialiser les styles de validation
      const fields = document.querySelectorAll('.form-control, input[type="file"]');
      fields.forEach(field => {
        field.classList.remove('field-valid', 'field-invalid');
      });
      
      // Cacher les boutons supprimer pour les premiers éléments
      const firstGarantRemove = document.querySelector('.garant-item .btn-remove');
      const firstDocRemove = document.querySelector('.document-item .btn-remove');
      
      if (firstGarantRemove) firstGarantRemove.style.display = 'none';
      if (firstDocRemove) firstDocRemove.style.display = 'none';
    }
  }

  // Validation en temps réel
  document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-control, input[type="file"]');
    
    inputs.forEach(input => {
      input.addEventListener('blur', function() {
        if (this.hasAttribute('required')) {
          this.classList.remove('field-valid', 'field-invalid');
          if (!this.value || (this.type === 'file' && !this.files.length)) {
            this.classList.add('field-invalid');
          } else {
            this.classList.add('field-valid');
          }
        }
      });
      
      input.addEventListener('input', function() {
        if (this.classList.contains('field-invalid') && this.value) {
          this.classList.remove('field-invalid');
          this.classList.add('field-valid');
        }
      });
    });

    // Validation des dates
    const dateDebut = document.getElementById('date_debut');
    const dateExpiration = document.getElementById('date_expiration');
    const aujourdhui = new Date().toISOString().split('T')[0];
    
    if (dateDebut) dateDebut.min = aujourdhui;
    if (dateExpiration) dateExpiration.min = aujourdhui;

    // Cacher les boutons de suppression pour les premiers éléments
    const firstGarantRemove = document.querySelector('.garant-item .btn-remove');
    const firstDocRemove = document.querySelector('.document-item .btn-remove');
    
    if (firstGarantRemove) firstGarantRemove.style.display = 'none';
    if (firstDocRemove) firstDocRemove.style.display = 'none';
  });

  // Fonction pour formater l'affichage des fichiers
  document.querySelectorAll('input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
      if (this.files.length > 0) {
        const fileName = this.files[0].name;
        const fileSize = (this.files[0].size / (1024 * 1024)).toFixed(2);
        const hint = this.nextElementSibling;
        if (hint && hint.classList.contains('file-hint')) {
          hint.textContent = `Fichier sélectionné: ${fileName} (${fileSize} MB)`;
        }
        this.classList.add('field-valid');
      }
    });
  });
</script>

<?php
include __DIR__ . '/../../includes/footer.php';
?>