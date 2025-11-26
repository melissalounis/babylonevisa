<?php
require_once __DIR__ . '/../../../../config.php';
$page_title = "Études - France";
include __DIR__ . '/../../../../includes/header.php';
?>

<div class="studies-hero">
  <div class="hero-content">
    <h1><i class="fas fa-graduation-cap"></i> Études en France</h1>
    <p>Choisissez la voie qui correspond à votre projet d'études en France</p>
  </div>
  <div class="hero-pattern"></div>
</div>

<div class="studies-container">
  <div class="studies-intro">
    <h2>Votre avenir académique en France</h2>
    <p>Notre équipe vous accompagne dans toutes les démarches pour réussir votre projet d'études en France, 
       de l'inscription jusqu'à l'obtention de votre visa.</p>
  </div>

  <div class="services-grid">
    
    <div class="service-card">
      <div class="card-icon">
        <i class="fas fa-language"></i>
      </div>
      <div class="card-content">
        <h3>Test de Langue</h3>
        <p>Préparation et inscription aux tests de français requis</p>
        <ul class="card-features">
          <li><i class="fas fa-check"></i> TCF, DELF, DALF</li>
          <li><i class="fas fa-check"></i> Préparation en ligne</li>
          <li><i class="fas fa-check"></i> Inscription et paiement en ligne</li>
        </ul>
        <a class="service-btn" href="/babylone/public/test_de_langue.php">
          <span>Commencer</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <div class="service-card">
      <div class="card-icon">
        <i class="fas fa-university"></i>
      </div>
      <div class="card-content">
        <h3>Campus France</h3>
        <p>Accompagnement jusqu'à la fin de la procédure</p>
        <ul class="card-features">
          <li><i class="fas fa-check"></i> Ouverture et remplissage de la boîte Pastel</li>
          <li><i class="fas fa-check"></i> Préparation entretien</li>
          <li><i class="fas fa-check"></i> Paiement et validation</li>
        </ul>
        <a class="service-btn" href="/babylone/public/france/etudes/campus_france.php">
          <span>Commencer</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <div class="service-card">
      <div class="card-icon">
        <i class="fas fa-book-open"></i>
      </div>
      <div class="card-content">
        <h3>Parcoursup</h3>
        <p>Accompagnement pour la plateforme d'admission post-bac</p>
        <ul class="card-features">
          <li><i class="fas fa-check"></i> Choix des formations</li>
          <li><i class="fas fa-check"></i> Rédaction des projets</li>
          <li><i class="fas fa-check"></i> Gestion des vœux</li>
        </ul>
        <a class="service-btn" href="/babylone/public/france/etudes/parcoursup.php">
          <span>Commencer</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <div class="service-card">
      <div class="card-icon">
        <i class="fas fa-school"></i>
      </div>
      <div class="card-content">
        <h3>Écoles Privées</h3>
        <p>Admission directe dans les établissements privés</p>
        <ul class="card-features">
          <li><i class="fas fa-check"></i> Inscription directe</li>
          <li><i class="fas fa-check"></i> Dossier personnalisé</li>
          <li><i class="fas fa-check"></i> Admission garantie</li>
        </ul>
        <a class="service-btn" href="/babylone/public/france/etudes/ecole_privee.php">
          <span>Commencer</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <div class="service-card">
      <div class="card-icon">
        <i class="fas fa-graduation-cap"></i>
      </div>
      <div class="card-content">
        <h3>Université Paris Saclay</h3>
        <p>Admission à l'une des meilleures universités françaises</p>
        <ul class="card-features">
          <li><i class="fas fa-check"></i> Procédure spécifique</li>
          <li><i class="fas fa-check"></i> Accompagnement complet</li>
          <li><i class="fas fa-check"></i> Excellence académique</li>
        </ul>
        <a class="service-btn" href="/babylone/public/france/etudes/paris_saclay.php">
          <span>Commencer</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <!-- Nouvelle section Demande de Visa Études -->
    <div class="service-card visa-card">
      <div class="card-icon">
        <i class="fas fa-passport"></i>
      </div>
      <div class="card-content">
        <h3>Demande de Visa Études</h3>
        <p>Formulaire complet pour votre visa étudiant en Europe</p>
        <ul class="card-features">
          <li><i class="fas fa-check"></i> Formulaire unique</li>
          <li><i class="fas fa-check"></i> Tous pays européens</li>
          <li><i class="fas fa-check"></i> Assistance complète</li>
        </ul>
        <a class="service-btn visa-btn" href="../../etudes/visa.php">
          <span>Démarrer la demande</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>

  <div class="studies-info">
    <div class="info-card">
      <div class="info-icon">
        <i class="fas fa-clock"></i>
      </div>
      <h4>Processus Rapide</h4>
      <p>Traitement de votre dossier sous 48h</p>
    </div>
    <div class="info-card">
      <div class="info-icon">
        <i class="fas fa-shield-alt"></i>
      </div>
      <h4>Sécurité</h4>
      <p>Données cryptées et confidentielles</p>
    </div>
    <div class="info-card">
      <div class="info-icon">
        <i class="fas fa-headset"></i>
      </div>
      <h4>Support 7j/7</h4>
      <p>Notre équipe vous accompagne</p>
    </div>
    <div class="info-card">
      <div class="info-icon">
        <i class="fas fa-globe"></i>
      </div>
      <h4>Tests Internationaux</h4>
      <p>Reconnus dans le monde entier</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../../includes/footer.php'; ?>

<style>
  /* Variables */
  :root {
    --primary-blue: #0056b3;
    --secondary-blue: #0077ff;
    --accent-orange: #ff6b35;
    --visa-green: #28a745;
    --light-bg: #f8fafc;
    --dark-text: #2d3748;
    --white: #ffffff;
    --light-gray: #e2e8f0;
    --transition: all 0.3s ease;
    --border-radius: 16px;
    --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    --box-shadow-hover: 0 20px 50px rgba(0, 0, 0, 0.15);
  }

  /* Hero Section */
  .studies-hero {
    background-image: url('../../images/etude fr.jpg');
    background-size: cover;
    background-position: center;
    color: var(--white);
    padding: 80px 20px;
    text-align: center;
    margin-bottom: 60px;
    position: relative;
    overflow: hidden;
  }

  .studies-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 86, 179, 0.8), rgba(0, 119, 255, 0.8));
    z-index: 1;
  }

  .hero-content {
    position: relative;
    z-index: 2;
  }

  .hero-content h1 {
    font-size: 2.8rem;
    margin-bottom: 20px;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }

  .hero-content h1 i {
    margin-right: 15px;
  }

  .hero-content p {
    font-size: 1.3rem;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
    font-weight: 300;
  }

  .hero-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
    z-index: 1;
  }

  /* Studies Container */
  .studies-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px 80px;
  }

  .studies-intro {
    text-align: center;
    margin-bottom: 60px;
  }

  .studies-intro h2 {
    font-size: 2.2rem;
    color: var(--dark-text);
    margin-bottom: 20px;
    font-weight: 700;
  }

  .studies-intro p {
    font-size: 1.1rem;
    color: #64748b;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;
  }

  /* Services Grid */
  .services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
  }

  /* Service Card */
  .service-card {
    background: var(--white);
    border-radius: var(--border-radius);
    padding: 30px;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    position: relative;
    border: 1px solid var(--light-gray);
  }

  .service-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--box-shadow-hover);
    border-color: var(--primary-blue);
  }

  /* Style spécifique pour la carte Visa */
  .visa-card {
    border: 2px solid var(--visa-green);
  }

  .visa-card:hover {
    border-color: var(--visa-green);
    box-shadow: 0 20px 50px rgba(40, 167, 69, 0.2);
  }

  .visa-card .card-icon {
    background: linear-gradient(135deg, var(--visa-green), #34ce57);
  }

  .visa-btn {
    background: var(--visa-green);
    border-color: var(--visa-green);
  }

  .visa-btn:hover {
    background: transparent;
    color: var(--visa-green);
  }

  .card-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
  }

  .card-icon i {
    font-size: 28px;
    color: var(--white);
  }

  .card-content {
    text-align: center;
  }

  .card-content h3 {
    font-size: 1.6rem;
    margin-bottom: 15px;
    color: var(--dark-text);
    font-weight: 700;
  }

  .card-content p {
    color: #64748b;
    margin-bottom: 25px;
    line-height: 1.6;
  }

  .card-features {
    text-align: left;
    margin-bottom: 30px;
    padding-left: 0;
  }

  .card-features li {
    list-style: none;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    color: #475569;
    font-size: 0.95rem;
  }

  .card-features i {
    color: var(--accent-orange);
    margin-right: 12px;
    font-size: 1rem;
  }

  .visa-card .card-features i {
    color: var(--visa-green);
  }

  .service-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 14px 32px;
    background: var(--primary-blue);
    color: var(--white);
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid var(--primary-blue);
    gap: 10px;
    width: 100%;
    max-width: 200px;
    margin: 0 auto;
  }

  .service-btn:hover {
    background: transparent;
    color: var(--primary-blue);
    transform: translateY(-2px);
  }

  .service-btn i {
    transition: var(--transition);
  }

  .service-btn:hover i {
    transform: translateX(5px);
  }

  /* Studies Info */
  .studies-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 40px;
  }

  .info-card {
    background: var(--white);
    padding: 25px;
    border-radius: var(--border-radius);
    text-align: center;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
  }

  .info-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-hover);
  }

  .info-icon {
    width: 60px;
    height: 60px;
    background: var(--light-bg);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }

  .info-icon i {
    font-size: 24px;
    color: var(--primary-blue);
  }

  .info-card h4 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: var(--dark-text);
    font-weight: 600;
  }

  .info-card p {
    color: #64748b;
    font-size: 0.95rem;
  }

  /* Responsive Design */
  @media (max-width: 1024px) {
    .services-grid {
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    }
  }

  @media (max-width: 768px) {
    .studies-hero {
      padding: 60px 20px;
    }
    
    .hero-content h1 {
      font-size: 2.2rem;
    }
    
    .hero-content p {
      font-size: 1.1rem;
    }
    
    .studies-intro h2 {
      font-size: 1.8rem;
    }
    
    .services-grid {
      grid-template-columns: 1fr;
      max-width: 500px;
      margin: 0 auto 50px;
    }
    
    .studies-info {
      grid-template-columns: 1fr;
      max-width: 400px;
      margin: 0 auto;
    }
  }

  @media (max-width: 480px) {
    .studies-hero {
      padding: 50px 15px;
    }
    
    .hero-content h1 {
      font-size: 1.8rem;
    }
    
    .service-card {
      padding: 25px 20px;
    }
    
    .card-icon {
      width: 60px;
      height: 60px;
      margin-bottom: 20px;
    }
    
    .card-icon i {
      font-size: 24px;
    }
    
    .service-btn {
      width: 100%;
      max-width: none;
    }
  }
</style>