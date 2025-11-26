<?php
require_once __DIR__ . '/../../../config.php';

$page_title = "Roumanie — Demande de Visa Étude";
include __DIR__ . '/../../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ===== STYLE ROUMANIE ===== */
:root {
  --roumanie-blue: #002B7F;
  --roumanie-yellow: #FCD116;
  --roumanie-red: #CE1126;
  --roumanie-dark-blue: #001F5C;
  --roumanie-dark-yellow: #E6B800;
  --roumanie-light: #f8f9fa;
  --roumanie-dark: #1a1a1a;
  --gradient-roumanie: linear-gradient(135deg, var(--roumanie-blue) 0%, var(--roumanie-red) 50%, var(--roumanie-yellow) 100%);
  --gradient-roumanie-light: linear-gradient(135deg, var(--roumanie-dark-blue) 0%, #B30E1E 50%, var(--roumanie-dark-yellow) 100%);
  --shadow-roumanie: 0 8px 25px rgba(0, 43, 127, 0.15);
  --shadow-roumanie-hover: 0 15px 35px rgba(0, 43, 127, 0.25);
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Inter', sans-serif;
  line-height: 1.6;
  color: #333;
  overflow-x: hidden;
}

h1, h2, h3, h4, h5, h6 {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
}

.text-roumanie {
  background: var(--gradient-roumanie);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 700;
}

/* Hero Section */
.roumanie-hero {
  background: var(--gradient-roumanie);
  color: white;
  padding: 80px 0;
  text-align: center;
  position: relative;
  overflow: hidden;
  min-height: 40vh;
  display: flex;
  align-items: center;
}

.hero-content-wrapper {
  position: relative;
  z-index: 3;
  width: 100%;
}

.roumanie-hero-content h1 {
  font-size: 2.5rem;
  margin-bottom: 15px;
  font-weight: 800;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.roumanie-hero-content h1 i {
  margin-right: 15px;
  color: var(--roumanie-yellow);
  text-shadow: 0 0 10px rgba(252, 209, 22, 0.5);
}

.roumanie-hero-content p {
  font-size: 1.2rem;
  opacity: 0.95;
  max-width: 600px;
  margin: 0 auto;
  font-weight: 300;
}

.roumanie-pattern {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 20% 30%, rgba(0, 43, 127, 0.1) 2px, transparent 2px),
    radial-gradient(circle at 80% 70%, rgba(206, 17, 38, 0.1) 1px, transparent 1px);
  background-size: 80px 80px;
  z-index: 1;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    rgba(0, 43, 127, 0.8) 0%, 
    rgba(206, 17, 38, 0.8) 50%, 
    rgba(252, 209, 22, 0.8) 100%);
  z-index: 2;
}

/* Form Container */
.form-container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 60px 20px;
}

.form-container h2 {
  text-align: center;
  font-size: 2.2rem;
  margin-bottom: 15px;
  color: var(--roumanie-blue);
  position: relative;
  font-weight: 700;
}

.form-container h2:after {
  content: '';
  position: absolute;
  bottom: -12px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 3px;
  background: var(--gradient-roumanie);
  border-radius: 2px;
}

.form-subtitle {
  text-align: center;
  font-size: 1rem;
  color: #666;
  margin-bottom: 40px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

/* Form Styles */
.visa-form {
  background: white;
  border-radius: 16px;
  padding: 40px;
  box-shadow: var(--shadow-roumanie);
  border: 1px solid rgba(0, 43, 127, 0.1);
}

.form-section {
  margin-bottom: 40px;
  padding-bottom: 30px;
  border-bottom: 1px solid #eee;
}

.form-section:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.section-title {
  font-size: 1.4rem;
  color: var(--roumanie-blue);
  margin-bottom: 25px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.section-title i {
  color: var(--roumanie-red);
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 20px;
  margin-bottom: 20px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: var(--roumanie-dark-blue);
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px 15px;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  font-family: 'Inter', sans-serif;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--roumanie-blue);
  box-shadow: 0 0 0 3px rgba(0, 43, 127, 0.1);
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.required label:after {
  content: ' *';
  color: var(--roumanie-red);
}

.file-upload {
  border: 2px dashed #e1e5e9;
  border-radius: 8px;
  padding: 30px;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
}

.file-upload:hover {
  border-color: var(--roumanie-blue);
  background: #f8f9ff;
}

.file-upload i {
  font-size: 2rem;
  color: var(--roumanie-blue);
  margin-bottom: 10px;
}

.file-upload input {
  display: none;
}

.file-info {
  margin-top: 10px;
  font-size: 0.9rem;
  color: #666;
}

.checkbox-group {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 15px;
}

.checkbox-group input {
  margin-top: 3px;
}

.checkbox-group label {
  font-weight: normal;
  margin-bottom: 0;
}

/* Documents List */
.documents-list {
  background: #f8f9ff;
  border-radius: 8px;
  padding: 20px;
  margin-top: 20px;
}

.documents-list h4 {
  color: var(--roumanie-blue);
  margin-bottom: 15px;
}

.documents-list ul {
  list-style: none;
  padding-left: 0;
}

.documents-list li {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
  color: #555;
}

.documents-list i {
  color: var(--roumanie-red);
  margin-right: 10px;
  font-size: 0.9rem;
}

/* Form Actions */
.form-actions {
  display: flex;
  gap: 15px;
  justify-content: center;
  margin-top: 40px;
  flex-wrap: wrap;
}

.roumanie-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 15px 30px;
  background: var(--gradient-roumanie);
  color: white;
  border-radius: 25px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
  gap: 10px;
  font-size: 1rem;
  cursor: pointer;
  min-width: 200px;
}

.roumanie-btn:hover {
  background: var(--gradient-roumanie-light);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0, 43, 127, 0.3);
}

.roumanie-btn.secondary {
  background: transparent;
  color: var(--roumanie-blue);
  border: 2px solid var(--roumanie-blue);
}

.roumanie-btn.secondary:hover {
  background: var(--roumanie-blue);
  color: white;
}

/* Info Box */
.roumanie-info {
  background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
  border-left: 3px solid var(--roumanie-red);
  border-radius: 10px;
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
  margin-top: 40px;
}

.info-icon {
  font-size: 1.5rem;
  color: var(--roumanie-red);
}

.info-content h4 {
  color: var(--roumanie-dark-blue);
  margin-bottom: 8px;
  font-size: 1.1rem;
}

.info-content p {
  color: #666;
  line-height: 1.5;
  font-size: 0.9rem;
}

/* Responsive Design */
@media (max-width: 768px) {
  .form-row {
    grid-template-columns: 1fr;
    gap: 0;
  }
  
  .visa-form {
    padding: 25px;
  }
  
  .form-container {
    padding: 40px 15px;
  }
  
  .form-container h2 {
    font-size: 1.8rem;
  }
  
  .roumanie-hero {
    padding: 60px 0;
    min-height: 30vh;
  }
  
  .roumanie-hero-content h1 {
    font-size: 2rem;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .roumanie-btn {
    width: 100%;
  }
}

@media (max-width: 480px) {
  .roumanie-hero-content h1 {
    font-size: 1.6rem;
  }
  
  .form-container h2 {
    font-size: 1.5rem;
  }
  
  .section-title {
    font-size: 1.2rem;
  }
}
</style>

<div class="roumanie-hero">
  <div class="hero-content-wrapper">
    <div class="roumanie-hero-content">
      <h1><i class="fas fa-passport"></i> Demande de Visa Étude</h1>
      <p>Formulaire de demande de visa étudiant pour la Roumanie</p>
    </div>
  </div>
  <div class="roumanie-pattern"></div>
  <div class="hero-overlay"></div>
</div>

<div class="form-container">
  <h2>Formulaire de <span class="text-roumanie">Demande de Visa</span></h2>
  <p class="form-subtitle">Remplissez soigneusement toutes les informations requises pour votre demande de visa étudiant</p>
  
  <form class="visa-form" action="/babylone/public/roumanie/etude/traitement-visa.php" method="POST" enctype="multipart/form-data">
    
    <!-- Informations Personnelles -->
    <div class="form-section">
      <h3 class="section-title"><i class="fas fa-user"></i> Informations Personnelles</h3>
      
      <div class="form-row">
        <div class="form-group required">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" required>
        </div>
        
        <div class="form-group required">
          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" required>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group required">
          <label for="date_naissance">Date de Naissance</label>
          <input type="date" id="date_naissance" name="date_naissance" required>
        </div>
        
        <div class="form-group required">
          <label for="lieu_naissance">Lieu de Naissance</label>
          <input type="text" id="lieu_naissance" name="lieu_naissance" required>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group required">
          <label for="nationalite">Nationalité</label>
          <input type="text" id="nationalite" name="nationalite" required>
        </div>
        
        <div class="form-group required">
          <label for="pays_naissance">Pays de Naissance</label>
          <input type="text" id="pays_naissance" name="pays_naissance" required>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group required">
          <label for="civilite">Civilité</label>
          <select id="civilite" name="civilite" required>
            <option value="">Sélectionnez</option>
            <option value="M">Monsieur</option>
            <option value="Mme">Madame</option>
          </select>
        </div>
        
        <div class="form-group required">
          <label for="situation_familiale">Situation Familiale</label>
          <select id="situation_familiale" name="situation_familiale" required>
            <option value="">Sélectionnez</option>
            <option value="celibataire">Célibataire</option>
            <option value="marie">Marié(e)</option>
            <option value="divorce">Divorcé(e)</option>
            <option value="veuf">Veuf/Veuve</option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Informations de Contact -->
    <div class="form-section">
      <h3 class="section-title"><i class="fas fa-address-book"></i> Informations de Contact</h3>
      
      <div class="form-row">
        <div class="form-group required">
          <label for="adresse">Adresse</label>
          <input type="text" id="adresse" name="adresse" required>
        </div>
        
        <div class="form-group required">
          <label for="ville">Ville</label>
          <input type="text" id="ville" name="ville" required>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group required">
          <label for="code_postal">Code Postal</label>
          <input type="text" id="code_postal" name="code_postal" required>
        </div>
        
        <div class="form-group required">
          <label for="pays">Pays</label>
          <input type="text" id="pays" name="pays" required>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group required">
          <label for="telephone">Téléphone</label>
          <input type="tel" id="telephone" name="telephone" required>
        </div>
        
        <div class="form-group required">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required>
        </div>
      </div>
    </div>
    
    <!-- Informations sur les Études -->
    <div class="form-section">
      <h3 class="section-title"><i class="fas fa-graduation-cap"></i> Informations sur les Études</h3>
      
      <div class="form-row">
        <div class="form-group required">
          <label for="universite">Université en Roumanie</label>
          <input type="text" id="universite" name="universite" required>
        </div>
        
       
      </div>
      
      <div class="form-row">
        <div class="form-group required">
          <label for="duree_etudes">Durée des Études (mois)</label>
          <input type="number" id="duree_etudes" name="duree_etudes" min="1" required>
        </div>
        
        <div class="form-group required">
          <label for="date_debut">Date de Début des Cours</label>
          <input type="date" id="date_debut" name="date_debut" required>
        </div>
      </div>
      

    
    <!-- Documents Requis -->
    <div class="form-section">
      <h3 class="section-title"><i class="fas fa-file-upload"></i> Documents à Fournir</h3>
      
      <div class="documents-list">
        <h4>Liste des documents requis :</h4>
        <ul>
          <li><i class="fas fa-check-circle"></i> Passeport valide (6 mois minimum)</li>
          <li><i class="fas fa-check-circle"></i> Lettre d'admission de l'université</li>
          <li><i class="fas fa-check-circle"></i> Relevés de notes et diplômes</li>
          <li><i class="fas fa-check-circle"></i> Justificatifs de ressources financières</li>
          <li><i class="fas fa-check-circle"></i> Assurance santé internationale</li>
          <li><i class="fas fa-check-circle"></i> Photos d'identité récentes</li>
          <li><i class="fas fa-check-circle"></i> Certificat médical</li>
        </ul>
      </div>
      
      <div class="form-group full-width">
        <label for="documents">Téléchargement des Documents (ZIP ou PDF)</label>
        <div class="file-upload" onclick="document.getElementById('documents').click()">
          <i class="fas fa-cloud-upload-alt"></i>
          <p>Cliquez pour télécharger vos documents</p>
          <p class="file-info">Formats acceptés : PDF, ZIP (max 10MB)</p>
          <input type="file" id="documents" name="documents" accept=".pdf,.zip" required>
        </div>
      </div>
    </div>
    
    <!-- Validation -->
    <div class="form-section">
      <h3 class="section-title"><i class="fas fa-check-circle"></i> Validation</h3>
      
      <div class="checkbox-group required">
        <input type="checkbox" id="confirmation" name="confirmation" required>
        <label for="confirmation">Je certifie que les informations fournies sont exactes et complètes</label>
      </div>
      
      <div class="checkbox-group required">
        <input type="checkbox" id="conditions" name="conditions" required>
        <label for="conditions">J'accepte les conditions générales et la politique de confidentialité</label>
      </div>
    </div>
    
    <!-- Actions du Formulaire -->
    <div class="form-actions">
      <button type="submit" class="roumanie-btn">
        <i class="fas fa-paper-plane"></i>
        Soumettre la Demande
      </button>
      
      <button type="reset" class="roumanie-btn secondary">
        <i class="fas fa-redo"></i>
        Réinitialiser
      </button>
    </div>
  </form>
  
  <div class="roumanie-info">
    <div class="info-icon">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="info-content">
      <h4>Important : Délais de Traitement</h4>
      <p>Le traitement d'une demande de visa étudiant pour la Roumanie prend généralement 4 à 6 semaines. Assurez-vous de soumettre votre demande au moins 2 mois avant la date prévue de votre départ.</p>
    </div>
  </div>
</div>

<script>
// Gestion de l'upload de fichiers
document.getElementById('documents').addEventListener('change', function(e) {
  const file = e.target.files[0];
  if (file) {
    const fileSize = file.size / 1024 / 1024; // Taille en MB
    if (fileSize > 10) {
      alert('Le fichier est trop volumineux. Taille maximum : 10MB');
      e.target.value = '';
    } else {
      const fileUpload = document.querySelector('.file-upload');
      fileUpload.innerHTML = `
        <i class="fas fa-check-circle" style="color: #28a745;"></i>
        <p>Fichier sélectionné : ${file.name}</p>
        <p class="file-info">Taille : ${(fileSize).toFixed(2)} MB</p>
        <input type="file" id="documents" name="documents" accept=".pdf,.zip" style="display: none;">
      `;
    }
  }
});

// Validation du formulaire
document.querySelector('.visa-form').addEventListener('submit', function(e) {
  const requiredFields = this.querySelectorAll('[required]');
  let isValid = true;
  
  requiredFields.forEach(field => {
    if (!field.value) {
      isValid = false;
      field.style.borderColor = 'var(--roumanie-red)';
    } else {
      field.style.borderColor = '';
    }
  });
  
  if (!isValid) {
    e.preventDefault();
    alert('Veuillez remplir tous les champs obligatoires.');
  }
});
</script>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>