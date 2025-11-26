<?php
require_once __DIR__ . '/../../config.php';
$page_title = "France — Services";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="france-hero">
  <div class="france-hero-content">
    <h1><i class="fas fa-french-bread"></i> France — Services</h1>
    <p>Découvrez nos services spécialisés pour réaliser votre projet en France</p>
    <div class="hero-stats">
      <div class="stat">
        <span class="stat-number">85%</span>
        <span class="stat-label">de réussite</span>
      </div>
      <div class="stat">
        <span class="stat-number">5+</span>
        <span class="stat-label">ans d'expérience</span>
      </div>
      <div class="stat">
        <span class="stat-number">1000+</span>
        <span class="stat-label">clients satisfaits</span>
      </div>
    </div>
  </div>
  <div class="hero-pattern"></div>
</div>

<!-- Section Slider avec deux images par vue -->
<div class="image-slider-section">
  <div class="slider-container">
    <h2>Nos Réalisations en France</h2>
    <p class="slider-subtitle">Découvrez nos succès et témoignages</p>
    
    <div class="slider-wrapper">
      <div class="slider-track">
        <!-- Vue 1 -->
        <div class="slide-view">
          <div class="slide-duo">
            <div class="slide">
              <div class="image-container">
                <img src="../images/1.jpeg" alt="Succès étudiant">
              </div>
              <div class="slide-content">
                <h3>Étudiants Admis</h3>
                <p>Plus de 500 étudiants admis dans les grandes écoles françaises</p>
              </div>
            </div>
            <div class="slide">
              <div class="image-container">
                <img src="../images/2.jpeg" alt="Visa obtenu">
              </div>
              <div class="slide-content">
                <h3>Visas Accordés</h3>
                <p>85% de taux de réussite pour l'obtention des visas</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Vue 2 -->
        <div class="slide-view">
          <div class="slide-duo">
            <div class="slide">
              <div class="image-container">
                <img src="../images/3.jpeg" alt="Famille réunie">
              </div>
              <div class="slide-content">
                <h3>Familles Réunies</h3>
                <p>Regroupement familial réussi pour plus de 200 familles</p>
              </div>
            </div>
            <div class="slide">
              <div class="image-container">
                <img src="../images/frsuc1.jpg" alt="Projet professionnel">
              </div>
              <div class="slide-content">
                <h3>Carrières Lancées</h3>
                <p>Insertion professionnelle réussie en France</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Contrôles du slider -->
      <button class="slider-btn prev-btn">
        <i class="fas fa-chevron-left"></i>
      </button>
      <button class="slider-btn next-btn">
        <i class="fas fa-chevron-right"></i>
      </button>
      
      <!-- Indicateurs -->
      <div class="slider-indicators">
        <span class="indicator active" data-slide="0"></span>
        <span class="indicator" data-slide="1"></span>
      </div>
    </div>
  </div>
</div>

<div class="services-container">
  <h2>Nos services pour la France</h2>
  <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet</p>
  
  <div class="services-grid">
    <!-- Rendez-vous Card -->
    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="card-image">
        <img src="../images/visa.jpg" alt="Prise de Rendez-vous">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Prise de Rendez-vous</h3>
        <p>Prenez rendez-vous pour toutes vos démarches administratives</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Rendez-vous en ligne</li>
          <li><i class="fas fa-check-circle"></i> Disponibilités en temps réel</li>
          <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
        </ul>
        <a class="service-btn" href="/babylone/rendez_vous.php">
          <span>Prendre RDV</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <!-- Études Card -->
    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>
      <div class="card-image">
        <img src="../images/etude.webp" alt="Études en France">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Études</h3>
        <p>Campus France, Parcoursup, Universités et Grandes Écoles</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Un grand taux d'admission</li>
          <li><i class="fas fa-check-circle"></i> Toutes les procédures études</li>
          <li><i class="fas fa-check-circle"></i> Orientation académique</li>
        </ul>
        <a class="service-btn" href="/babylone/public/france/etudes/etudes.php">
          <span>Découvrir</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <!-- Tourisme Card -->
    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/tourisme.jpg" alt="Tourisme en France">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & affaires</h3>
        <p>Visa touristique,visa affaire, réservations et assurance voyage</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa Schengen</li>
          <li><i class="fas fa-check-circle"></i> Réservations d'hôtel</li>
          <li><i class="fas fa-check-circle"></i> Assurance voyage</li>
        </ul>
        <a class="service-btn" href="/babylone/tourisme/tourisme.php">
          <span>Découvrir</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <!-- Travail Card -->
    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail en France">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Visa travail, contrat et insertion professionnelle</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa de travail</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
          <li><i class="fas fa-check-circle"></i> Contrat de travail</li>
        </ul>
        <a class="service-btn" href="/babylone/public/travail/travail.php">
          <span>Découvrir</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
    
    <!-- Famille Card -->
    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-users"></i>
      </div>
      <div class="card-image">
        <img src="../images/famile2.jpg" alt="Regroupement Familial">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Regroupement Familial</h3>
        <p>Procédures pour le regroupement familial et visas de long séjour.</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa long séjour</li>
          <li><i class="fas fa-check-circle"></i> Démarches familiales</li>
          <li><i class="fas fa-check-circle"></i> Accompagnement administratif</li>
        </ul>
        <a class="service-btn" href="/babylone/public/famille/famille.php">
          <span>Découvrir</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<style>
  /* Variables aux couleurs de la France */
  :root {
    --bleu-france: #0055a4;
    --blanc-france: #ffffff;
    --rouge-france: #ef4135;
    --bleu-clair: #e8f1ff;
    --gris-elegant: #4a4a4a;
    --or-francais: #d4af37;
    --transition: all 0.3s ease;
    --border-radius: 12px;
    --ombre-legere: 0 8px 25px rgba(0, 0, 0, 0.08);
    --ombre-portee: 0 15px 40px rgba(0, 0, 0, 0.12);
  }
  
  /* Hero Section réduite */
  .france-hero {
    background: 
      linear-gradient(135deg, 
        var(--bleu-france) 0%, 
        var(--bleu-france) 33%, 
        var(--blanc-france) 33%, 
        var(--blanc-france) 66%, 
        var(--rouge-france) 66%, 
        var(--rouge-france) 100%),
      radial-gradient(circle at 30% 70%, rgba(255,255,255,0.1) 0%, transparent 50%),
      radial-gradient(circle at 70% 30%, rgba(255,255,255,0.1) 0%, transparent 50%);
    color: var(--gris-elegant);
    padding: 80px 20px 50px;
    text-align: center;
    margin-bottom: 0;
    position: relative;
    overflow: hidden;
  }
  
  .france-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--bleu-france) 33%, var(--blanc-france) 33%, var(--blanc-france) 66%, var(--rouge-france) 66%);
    z-index: 2;
  }
  
  .france-hero-content h1 {
    font-size: 2.8rem;
    margin-bottom: 15px;
    font-weight: 700;
    font-family: 'Playfair Display', serif;
    position: relative;
    display: inline-block;
    background: linear-gradient(135deg, var(--bleu-france) 30%, var(--rouge-france) 70%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  .france-hero-content h1::after {
    content: '';
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 2px;
    background: var(--or-francais);
  }
  
  .france-hero-content h1 i {
    margin-right: 12px;
    color: var(--or-francais);
  }
  
  .france-hero-content p {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 600px;
    margin: 20px auto 25px;
    font-weight: 400;
    font-style: italic;
    color: var(--gris-elegant);
    text-shadow: 0 1px 2px rgba(255,255,255,0.8);
  }
  
  .hero-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 30px;
    flex-wrap: wrap;
  }
  
  .stat {
    text-align: center;
    position: relative;
    background: rgba(255, 255, 255, 0.9);
    padding: 15px 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--ombre-legere);
    backdrop-filter: blur(10px);
  }
  
  .stat:not(:last-child)::after {
    content: '•';
    position: absolute;
    right: -20px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--bleu-france);
    opacity: 0.7;
    font-size: 1.2rem;
  }
  
  .stat-number {
    display: block;
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 5px;
    background: linear-gradient(135deg, var(--bleu-france), var(--rouge-france));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  
  .stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--gris-elegant);
  }
  
  /* Section Slider avec deux images par vue */
  .image-slider-section {
    background: linear-gradient(135deg, var(--blanc-france) 0%, var(--bleu-clair) 100%);
    padding: 80px 20px;
    margin: 0;
    position: relative;
    width: 100%;
  }
  
  .image-slider-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--bleu-france) 33%, var(--blanc-france) 33%, var(--blanc-france) 66%, var(--rouge-france) 66%);
  }
  
  .slider-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0;
  }
  
  .slider-container h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: var(--gris-elegant);
    font-weight: 700;
    font-family: 'Playfair Display', serif;
  }
  
  .slider-subtitle {
    text-align: center;
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 50px;
    font-style: italic;
  }
  
  .slider-wrapper {
    position: relative;
    width: 100%;
    margin: 0 auto;
    overflow: hidden;
    border-radius: var(--border-radius);
    box-shadow: var(--ombre-portee);
  }
  
  .slider-track {
    display: flex;
    transition: transform 0.5s ease-in-out;
  }
  
  /* NOUVEAU: Vue contenant deux slides */
  .slide-view {
    min-width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
  }
  
  .slide-duo {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    width: 100%;
    max-width: 1000px;
  }
  
  .slide {
    position: relative;
    height: 450px;
    overflow: hidden;
    border-radius: var(--border-radius);
    box-shadow: var(--ombre-legere);
    transition: var(--transition);
  }
  
  .slide:hover {
    transform: translateY(-5px);
    box-shadow: var(--ombre-portee);
  }
  
  .image-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
  }
  
  .slide img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    transition: transform 0.5s ease;
  }
  
  .slide:hover img {
    transform: scale(1.03);
  }
  
  .slide-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    color: var(--blanc-france);
    padding: 25px 20px 20px;
    transform: translateY(0);
    transition: var(--transition);
  }
  
  .slide:hover .slide-content {
    background: linear-gradient(transparent, rgba(0, 85, 164, 0.9));
  }
  
  .slide-content h3 {
    font-size: 1.5rem;
    margin-bottom: 8px;
    font-weight: 700;
  }
  
  .slide-content p {
    font-size: 1rem;
    opacity: 0.9;
    margin: 0;
  }
  
  .slider-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.9);
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    z-index: 10;
    box-shadow: var(--ombre-legere);
  }
  
  .slider-btn:hover {
    background: var(--bleu-france);
    color: var(--blanc-france);
    transform: translateY(-50%) scale(1.1);
  }
  
  .prev-btn {
    left: 20px;
  }
  
  .next-btn {
    right: 20px;
  }
  
  .slider-indicators {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 12px;
    z-index: 10;
  }
  
  .indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: var(--transition);
  }
  
  .indicator.active {
    background: var(--blanc-france);
    transform: scale(1.3);
  }
  
  .indicator:hover {
    background: var(--blanc-france);
  }
  
  /* Services Container */
  .services-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 80px 20px;
  }
  
  .services-container h2 {
    text-align: center;
    font-size: 2.8rem;
    margin-bottom: 20px;
    color: var(--gris-elegant);
    position: relative;
    font-weight: 700;
    font-family: 'Playfair Display', serif;
  }
  
  .services-subtitle {
    text-align: center;
    font-size: 1.3rem;
    color: #666;
    margin-bottom: 60px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    font-style: italic;
  }
  
  .services-container h2::after {
    content: '';
    position: absolute;
    bottom: -20px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 3px;
    background: linear-gradient(90deg, var(--bleu-france), var(--rouge-france));
  }
  
  /* Services Grid */
  .services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
    gap: 40px;
    margin-top: 60px;
  }
  
  /* Service Card - Style français amélioré */
  .service-card {
    background: var(--blanc-france);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--ombre-legere);
    transition: var(--transition);
    position: relative;
    border: 1px solid #f0f0f0;
  }
  
  .service-card:hover {
    transform: translateY(-12px);
    box-shadow: var(--ombre-portee);
    border-color: var(--bleu-france);
  }
  
  .card-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, var(--bleu-france), var(--rouge-france));
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
    transition: var(--transition);
    border: 2px solid var(--blanc-france);
  }
  
  .service-card:hover .card-icon {
    transform: scale(1.1) rotate(8deg);
    box-shadow: 0 8px 25px rgba(0, 85, 164, 0.3);
  }
  
  .card-icon i {
    font-size: 30px;
    color: var(--blanc-france);
    transition: var(--transition);
  }
  
  .card-image {
    height: 240px;
    overflow: hidden;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
  }
  
  .card-image img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    transition: var(--transition);
  }
  
  .card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, 
      rgba(0, 85, 164, 0.4) 0%, 
      rgba(255, 255, 255, 0.2) 50%, 
      rgba(239, 65, 53, 0.4) 100%);
    opacity: 0.3;
    transition: var(--transition);
  }
  
  .service-card:hover .card-image img {
    transform: scale(1.1);
  }
  
  .service-card:hover .card-overlay {
    opacity: 0.6;
  }
  
  .card-content {
    padding: 35px 30px;
    text-align: center;
    background: linear-gradient(to bottom, var(--blanc-france) 0%, var(--bleu-clair) 100%);
    position: relative;
  }
  
  .card-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--bleu-france), var(--rouge-france));
  }
  
  .card-content h3 {
    font-size: 1.7rem;
    margin-bottom: 18px;
    color: var(--gris-elegant);
    font-weight: 700;
    font-family: 'Playfair Display', serif;
  }
  
  .card-content p {
    color: #555;
    margin-bottom: 25px;
    line-height: 1.7;
    font-size: 1.1rem;
  }
  
  .service-features {
    text-align: left;
    margin-bottom: 35px;
    padding-left: 0;
  }
  
  .service-features li {
    list-style: none;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    color: #444;
    font-size: 1.05rem;
    transition: var(--transition);
  }
  
  .service-features li:hover {
    color: var(--bleu-france);
    transform: translateX(5px);
  }
  
  .service-features i {
    color: var(--rouge-france);
    margin-right: 12px;
    font-size: 1.2rem;
    min-width: 20px;
    transition: var(--transition);
  }
  
  .service-features li:hover i {
    color: var(--bleu-france);
    transform: scale(1.2);
  }
  
  .service-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 16px 40px;
    background: linear-gradient(135deg, var(--bleu-france) 0%, var(--rouge-france) 100%);
    color: var(--blanc-france);
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid transparent;
    gap: 12px;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
  }
  
  .service-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: var(--transition);
  }
  
  .service-btn:hover {
    background: transparent;
    color: var(--bleu-france);
    transform: translateY(-3px);
    border-color: var(--bleu-france);
    box-shadow: 0 10px 25px rgba(0, 85, 164, 0.3);
  }
  
  .service-btn:hover::before {
    left: 100%;
  }
  
  .service-btn i {
    transition: var(--transition);
  }
  
  .service-btn:hover i {
    transform: translateX(8px);
    color: var(--rouge-france);
  }
  
  /* Responsive Design */
  @media (max-width: 1024px) {
    .services-grid {
      grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
      gap: 35px;
    }
    
    .hero-stats {
      gap: 30px;
    }
    
    .stat:not(:last-child)::after {
      right: -15px;
    }
    
    .slide {
      height: 400px;
    }
    
    .slider-container {
      max-width: 90%;
    }
  }
  
  @media (max-width: 768px) {
    .france-hero {
      padding: 60px 20px 40px;
    }
    
    .france-hero-content h1 {
      font-size: 2.2rem;
    }
    
    .france-hero-content p {
      font-size: 1.1rem;
    }
    
    .hero-stats {
      gap: 20px;
      flex-direction: column;
    }
    
    .stat:not(:last-child)::after {
      display: none;
    }
    
    .stat-number {
      font-size: 1.8rem;
    }
    
    .slider-container h2 {
      font-size: 2rem;
    }
    
    /* Sur mobile, revenir à une seule image par vue */
    .slide-duo {
      grid-template-columns: 1fr;
      gap: 15px;
    }
    
    .slide {
      height: 350px;
    }
    
    .slide-content {
      padding: 20px 15px 15px;
    }
    
    .slide-content h3 {
      font-size: 1.3rem;
    }
    
    .slide-content p {
      font-size: 0.9rem;
    }
    
    .slider-btn {
      width: 45px;
      height: 45px;
    }
    
    .prev-btn {
      left: 15px;
    }
    
    .next-btn {
      right: 15px;
    }
    
    .services-container h2 {
      font-size: 2.3rem;
    }
    
    .services-grid {
      grid-template-columns: 1fr;
      max-width: 500px;
      margin: 0 auto;
    }
    
    .slider-container {
      max-width: 95%;
    }
  }
  
  @media (max-width: 480px) {
    .france-hero {
      padding: 50px 15px 30px;
    }
    
    .france-hero-content h1 {
      font-size: 1.8rem;
    }
    
    .france-hero-content p {
      font-size: 1rem;
    }
    
    .slide {
      height: 300px;
    }
    
    .slide-content {
      padding: 15px 10px 10px;
    }
    
    .slide-content h3 {
      font-size: 1.2rem;
    }
    
    .slide-content p {
      font-size: 0.8rem;
    }
    
    .slider-btn {
      width: 40px;
      height: 40px;
    }
    
    .prev-btn {
      left: 10px;
    }
    
    .next-btn {
      right: 10px;
    }
    
    .slider-indicators {
      bottom: 15px;
    }
    
    .indicator {
      width: 10px;
      height: 10px;
    }
    
    .card-content {
      padding: 25px 20px;
    }
    
    .service-btn {
      width: 100%;
      justify-content: space-between;
    }
    
    .services-container {
      padding: 60px 15px;
    }
  }
</style>

<script>
  // Script pour le slider avec deux images par vue
  document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.slider-track');
    const slideViews = document.querySelectorAll('.slide-view');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const indicators = document.querySelectorAll('.indicator');
    
    let currentView = 0;
    const totalViews = slideViews.length;
    
    // Fonction pour mettre à jour le slider
    function updateSlider() {
      track.style.transform = `translateX(-${currentView * 100}%)`;
      
      // Mettre à jour les indicateurs
      indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentView);
      });
    }
    
    // Événements pour les boutons
    nextBtn.addEventListener('click', function() {
      currentView = (currentView + 1) % totalViews;
      updateSlider();
    });
    
    prevBtn.addEventListener('click', function() {
      currentView = (currentView - 1 + totalViews) % totalViews;
      updateSlider();
    });
    
    // Événements pour les indicateurs
    indicators.forEach((indicator, index) => {
      indicator.addEventListener('click', function() {
        currentView = index;
        updateSlider();
      });
    });
    
    // Défilement automatique
    let autoSlide = setInterval(function() {
      currentView = (currentView + 1) % totalViews;
      updateSlider();
    }, 5000);
    
    // Arrêter le défilement automatique au survol
    const sliderWrapper = document.querySelector('.slider-wrapper');
    sliderWrapper.addEventListener('mouseenter', function() {
      clearInterval(autoSlide);
    });
    
    sliderWrapper.addEventListener('mouseleave', function() {
      autoSlide = setInterval(function() {
        currentView = (currentView + 1) % totalViews;
        updateSlider();
      }, 5000);
    });
  });
</script>