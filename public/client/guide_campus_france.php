<?php
session_start();
$page_title = "Guide Campus France - Procédure Complète";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title; ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #4b0082;
      --secondary-color: #8a2be2;
      --success-color: #28a745;
      --warning-color: #ffc107;
      --info-color: #17a2b8;
      --light-blue: #e8f2ff;
      --light-gray: #f8f9fa;
      --dark-text: #333;
      --border-color: #dbe4ee;
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
      padding: 0;
    }
    
    .container {
      max-width: 1200px;
      margin: auto;
      background: #fff;
    }
    
    /* Header */
    .guide-header {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      color: white;
      padding: 60px 30px;
      text-align: center;
    }
    
    .guide-header h1 {
      font-size: 2.5rem;
      margin-bottom: 15px;
    }
    
    .guide-header p {
      font-size: 1.2rem;
      opacity: 0.9;
      max-width: 800px;
      margin: 0 auto;
    }
    
    /* Navigation */
    .guide-nav {
      background: white;
      padding: 20px;
      border-bottom: 1px solid var(--border-color);
      position: sticky;
      top: 0;
      z-index: 100;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .nav-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }
    
    .nav-links {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }
    
    .nav-links a {
      text-decoration: none;
      color: var(--primary-color);
      font-weight: 600;
      padding: 8px 16px;
      border-radius: var(--border-radius);
      transition: var(--transition);
    }
    
    .nav-links a:hover, .nav-links a.active {
      background: var(--primary-color);
      color: white;
    }
    
    /* Content */
    .guide-content {
      padding: 40px 30px;
    }
    
    .section {
      margin-bottom: 50px;
      scroll-margin-top: 100px;
    }
    
    .section h2 {
      color: var(--primary-color);
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--light-blue);
      display: flex;
      align-items: center;
    }
    
    .section h2 i {
      margin-right: 15px;
      font-size: 1.5rem;
    }
    
    .info-card {
      background: var(--light-gray);
      padding: 25px;
      border-radius: var(--border-radius);
      margin-bottom: 25px;
      border-left: 4px solid var(--secondary-color);
    }
    
    .warning-card {
      background: #fff3cd;
      border-left-color: var(--warning-color);
    }
    
    .success-card {
      background: #d4edda;
      border-left-color: var(--success-color);
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      margin: 25px 0;
    }
    
    .step-card {
      background: white;
      padding: 25px;
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      border: 1px solid var(--border-color);
    }
    
    .step-number {
      background: var(--primary-color);
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      margin-bottom: 15px;
    }
    
    .timeline {
      position: relative;
      margin: 40px 0;
    }
    
    .timeline::before {
      content: '';
      position: absolute;
      left: 20px;
      top: 0;
      bottom: 0;
      width: 2px;
      background: var(--secondary-color);
    }
    
    .timeline-item {
      position: relative;
      margin-bottom: 30px;
      padding-left: 60px;
    }
    
    .timeline-date {
      position: absolute;
      left: 0;
      top: 0;
      background: var(--secondary-color);
      color: white;
      padding: 5px 12px;
      border-radius: var(--border-radius);
      font-weight: 600;
    }
    
    .documents-list {
      list-style: none;
    }
    
    .documents-list li {
      padding: 8px 0;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      align-items: center;
    }
    
    .documents-list li i {
      margin-right: 10px;
      color: var(--success-color);
    }
    
    .btn {
      display: inline-flex;
      align-items: center;
      padding: 12px 25px;
      background: var(--primary-color);
      color: white;
      text-decoration: none;
      border-radius: var(--border-radius);
      font-weight: 600;
      transition: var(--transition);
      margin: 10px 5px;
    }
    
    .btn:hover {
      background: var(--secondary-color);
      transform: translateY(-2px);
    }
    
    .btn i {
      margin-right: 8px;
    }
    
    .btn-outline {
      background: transparent;
      border: 2px solid var(--primary-color);
      color: var(--primary-color);
    }
    
    .btn-outline:hover {
      background: var(--primary-color);
      color: white;
    }
    
    /* FAQ */
    .faq-item {
      margin-bottom: 15px;
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      overflow: hidden;
    }
    
    .faq-question {
      padding: 15px 20px;
      background: var(--light-gray);
      font-weight: 600;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .faq-answer {
      padding: 0 20px;
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease;
    }
    
    .faq-item.active .faq-answer {
      padding: 20px;
      max-height: 500px;
    }
    
    /* Footer */
    .guide-footer {
      background: var(--dark-text);
      color: white;
      padding: 40px 30px;
      text-align: center;
    }
    
    .contact-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      margin: 30px 0;
    }
    
    .contact-item {
      text-align: center;
    }
    
    .contact-item i {
      font-size: 2rem;
      margin-bottom: 15px;
      color: var(--secondary-color);
    }
    
    @media (max-width: 768px) {
      .nav-container {
        flex-direction: column;
        gap: 15px;
      }
      
      .nav-links {
        justify-content: center;
      }
      
      .guide-header {
        padding: 40px 20px;
      }
      
      .guide-header h1 {
        font-size: 2rem;
      }
      
      .guide-content {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <!-- Header -->
  <header class="guide-header">
    <h1><i class="fas fa-graduation-cap"></i> Guide Campus France 2024</h1>
    <p>Procédure complète "Études en France" - Tout ce que vous devez savoir pour réussir votre candidature</p>
  </header>

  <!-- Navigation -->
  <nav class="guide-nav">
    <div class="nav-container">
      <div class="nav-links">
        <a href="#introduction" class="active">Introduction</a>
        <a href="#procedure">Procédure</a>
        <a href="#documents">Documents</a>

      </div>
      <div class="nav-actions">
        <a href="../france/etudes/campus_france.php" class="btn btn-outline">
          <i class="fas fa-paper-plane"></i> Démarrer ma demande
        </a>
      </div>
    </div>
  </nav>

  <!-- Content -->
  <main class="guide-content">
    <!-- Introduction -->
    <section id="introduction" class="section">
      <h2><i class="fas fa-info-circle"></i> Introduction à la procédure</h2>
      
      <div class="info-card">
        <h3>Qu'est-ce que Campus France ?</h3>
        <p>Campus France est l'agence nationale française chargée de la promotion de l'enseignement supérieur, de l'accueil et de la mobilité internationale des étudiants.</p>
      </div>
      
      <div class="info-grid">
        <div class="step-card">
          <div class="step-number">72</div>
          <h4>Pays concernés</h4>
          <p>La procédure "Études en France" concerne les étudiants résidant dans 72 pays et territoires.</p>
        </div>
        
        <div class="step-card">
          <div class="step-number">100%</div>
          <h4>Dématérialisée</h4>
          <p>Une procédure entièrement en ligne, de la candidature jusqu'à la demande de visa.</p>
        </div>
        
        <div class="step-card">
          <div class="step-number">3</div>
          <h4>Voies d'accès</h4>
          <p>Parcoursup ,école privé ,Université </p>
        </div>
      </div>
    </section>

    

    <!-- Procédure détaillée -->
    <section id="procedure" class="section">
      <h2><i class="fas fa-list-ol"></i> Procédure étape par étape</h2>
      
      <div class="info-grid">
        <div class="step-card">
          <div class="step-number">1</div>
          <h4>Création du compte</h4>
          <p>Inscription sur la plateforme Études en France</p>
          <ul>
            <li>Email valide</li>
            <li>Pièce d'identité</li>
            <li>Photo d'identité</li>
          </ul>
        </div>
        
        <div class="step-card">
          <div class="step-number">2</div>
          <h4>Compléter le dossier</h4>
          <p>Renseignements personnels et académiques</p>
          <ul>
            <li>Informations personnelles</li>
            <li>Parcours académique</li>
            <li>Projet d'études</li>
          </ul>
        </div>
        
        <div class="step-card">
          <div class="step-number">3</div>
          <h4>Choix des formations</h4>
          <p>Sélection des établissements et formations</p>
          <ul>
            <li>Maximum 15 voeux</li>
            <li>Classement par ordre de préférence</li>
          </ul>
        </div>
        
       
        
        <div class="step-card">
          <div class="step-number">5</div>
          <h4>Validation du dossier</h4>
          <p>Vérification par Campus France</p>
          <ul>
            <li>Complétude des documents</li>
            <li>Conformité administrative</li>
          </ul>
        </div>
        
        <div class="step-card">
          <div class="step-number">6</div>
          <h4>Entretien obligatoire</h4>
          <p>Entretien avec un conseiller Campus France</p>
          <ul>
            <li>Évaluation du projet</li>
            <li>Vérification linguistique</li>
            <li>Conseils personnalisés</li>
          </ul>
        </div>
      </div>
    </section>

    <!-- Documents requis -->
    <section id="documents" class="section">
      <h2><i class="fas fa-file-alt"></i> Documents requis</h2>
      
      <div class="info-card">
        <h4>📋 Liste des documents obligatoires</h4>
        <p>Tous les documents doivent être traduits en français par un traducteur assermenté</p>
      </div>
      
      <div class="info-grid">
        <div class="step-card">
          <h4><i class="fas fa-id-card"></i> Pièces d'identité</h4>
          <ul class="documents-list">
            <li><i class="fas fa-check"></i> Passeport valide</li>
            <li><i class="fas fa-check"></i> Carte nationale d'identité</li>
            <li><i class="fas fa-check"></i> Acte de naissance</li>
          </ul>
        </div>
        
        <div class="step-card">
          <h4><i class="fas fa-graduation-cap"></i> Documents académiques</h4>
          <ul class="documents-list">
            <li><i class="fas fa-check"></i> Diplômes obtenus</li>
            <li><i class="fas fa-check"></i> Relevés de notes</li>
            <li><i class="fas fa-check"></i> Attestations de scolarité</li>
          </ul>
        </div>
        
        <div class="step-card">
          <h4><i class="fas fa-language"></i> Tests de langue</h4>
          <ul class="documents-list">
            <li><i class="fas fa-check"></i> TCF/DELF/DALF (français)</li>
            <li><i class="fas fa-check"></i> IELTS/TOEFL (anglais)</li>
            <li><i class="fas fa-check"></i> Attestations de niveau</li>
          </ul>
        </div>
        
        <div class="step-card">
          <h4><i class="fas fa-euro-sign"></i> Ressources financières</h4>
          <ul class="documents-list">
            <li><i class="fas fa-check"></i> Justificatifs de ressources</li>
            <li><i class="fas fa-check"></i> Prise en charge</li>
            <li><i class="fas fa-check"></i> Bourse d'études</li>
          </ul>
        </div>
      </div>
    </section>

   

    <!-- Actions -->
    <div style="text-align: center; margin: 50px 0;">
      <a href="../france/etudes/campus_france.php" class="btn">
        <i class="fas fa-rocket"></i> Commencer ma demande maintenant
      </a>
      <a href="mes_demandes_campus_france.php" class="btn btn-outline">
        <i class="fas fa-list"></i> Voir mes demandes en cours
      </a>
    </div>
  </main>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
      

<script>
// FAQ functionality
document.querySelectorAll('.faq-question').forEach(question => {
  question.addEventListener('click', () => {
    const item = question.parentElement;
    item.classList.toggle('active');
  });
});

// Smooth scrolling for navigation
document.querySelectorAll('.nav-links a').forEach(link => {
  link.addEventListener('click', function(e) {
    e.preventDefault();
    
    const targetId = this.getAttribute('href');
    const targetSection = document.querySelector(targetId);
    
    if (targetSection) {
      window.scrollTo({
        top: targetSection.offsetTop - 80,
        behavior: 'smooth'
      });
      
      // Update active nav link
      document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
      this.classList.add('active');
    }
  });
});

// Update active nav link on scroll
window.addEventListener('scroll', () => {
  const sections = document.querySelectorAll('.section');
  const navLinks = document.querySelectorAll('.nav-links a');
  
  let current = '';
  sections.forEach(section => {
    const sectionTop = section.offsetTop;
    const sectionHeight = section.clientHeight;
    if (scrollY >= sectionTop - 100) {
      current = section.getAttribute('id');
    }
  });
  
  navLinks.forEach(link => {
    link.classList.remove('active');
    if (link.getAttribute('href') === `#${current}`) {
      link.classList.add('active');
    }
  });
});

// Print functionality
function printGuide() {
  window.print();
}
</script>
</body>
</html>