<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Canada — Services";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="canada-hero">
  <div class="canada-hero-content">
    <h1><i class="fas fa-maple-leaf"></i> Canada — Services</h1>
    <p>Découvrez nos services spécialisés pour réaliser votre projet au Canada</p>
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

<!-- Section Slider MODIFIÉE avec deux images par vue -->
<div class="image-slider-section canada-slider">
  <div class="slider-container">
    <h2>Nos Réalisations au Canada</h2>
    <p class="slider-subtitle">Découvrez nos succès et témoignages</p>
    
    <div class="slider-wrapper">
      <div class="slider-track">
        <!-- Vue 1 -->
        <div class="slide-view">
          <div class="slide-duo">
            <div class="slide">
              <div class="image-container">
                <img src="../images/canadasuc.jpg" alt="Étudiants au Canada">
              </div>
              <div class="slide-content">
                <h3>Étudiants Admis</h3>
                <p>Plus de 700 étudiants admis dans les universités canadiennes</p>
              </div>
            </div>
            <div class="slide">
              <div class="image-container">
                <img src="../images/suc1.jpg" alt="Visa Canada obtenu">
              </div>
              <div class="slide-content">
                <h3>Visas Accordés</h3>
                <p>85% de taux de réussite pour l'obtention des visas canadiens</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Vue 2 -->
        <div class="slide-view">
          <div class="slide-duo">
            <div class="slide">
              <div class="image-container">
                <img src="../images/suc5.jpg" alt="Résidence permanente">
              </div>
              <div class="slide-content">
                <h3>Résidences Permanentes</h3>
                <p>Plus de 500 résidences permanentes obtenues avec succès</p>
              </div>
            </div>
            <div class="slide">
              <div class="image-container">
                <img src="../images/suc4.jpg" alt="Vie au Canada">
              </div>
              <div class="slide-content">
                <h3>Nouvelles Vies</h3>
                <p>Installation réussie de familles dans tout le Canada</p>
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
  <h2>Nos services pour le Canada</h2>
  <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet</p>
  
  <div class="services-grid">
    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>
      <div class="card-image">
        <img src="../images/etude.webp" alt="Études au Canada">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Études</h3>
        <p>Universities, Colleges, Student Direct Stream, Permis d'études</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Permis d'études</li>
          <li><i class="fas fa-check-circle"></i> Orientation académique</li>
        </ul>
        <a class="service-btn" href="/babylone/public/canada/etude/admission.php">
          <span>Découvrir</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/canda images.jpg" alt="Tourisme au Canada">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & affaires</h3>
        <p>Visa visiteur, voyages d'affaires, réservations</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa touristique</li>
          <li><i class="fas fa-check-circle"></i> Visa affaires</li>
          <li><i class="fas fa-check-circle"></i> Visa Visite familiale</li>
        </ul>
        <a class="service-btn" href="tourisme/tourisme.php">
          <span>Découvrir</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail au Canada">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Permis de travail, Expérience Internationale Canada</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Permis de travail</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
        </ul>
        <a class="service-btn" href="/babylone/public/travail/travail.php">
          <span>Découvrir</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-users"></i>
      </div>
      <div class="card-image">
        <img src="../images/famile2.jpg" alt="Immigration Canada">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Immigration</h3>
        <p>Résidence permanente, Express Entry, parrainage familial</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Express Entry</li>
          <li><i class="fas fa-check-circle"></i> Parrainage familial</li>
          <li><i class="fas fa-check-circle"></i> Résidence permanente</li>
        </ul>
        <a class="service-btn" href="/babylone/public/canada/immigration/immigration.php">
          <span>Découvrir</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <!-- Service modifié: Prise de rendez-vous biométrie -->
    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-fingerprint"></i>
      </div>
      <div class="card-image">
        <img src="../images/biometrie.jpg" alt="Prise de rendez-vous biométrie">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Prise de rendez-vous biométrie</h3>
        <p>Collecte des données biométriques pour votre visa canadien</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Empreintes digitales</li>
          <li><i class="fas fa-check-circle"></i> Photo biométrique</li>
          <li><i class="fas fa-check-circle"></i> Centres agréés</li>
        </ul>
        <a class="service-btn" href="rendez_vous_biometrie.php">
          <span>Prendre rendez-vous</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<style>
  /* Variables Canada */
  :root {
    --primary-red: #FF0000;
    --secondary-red: #D52B1E;
    --accent-red: #EF4135;
    --canada-red: #FF0000;
    --canada-white: #FFFFFF;
    --light-bg: #f8fafc;
    --dark-text: #2d3748;
    --white: #ffffff;
    --light-gray: #e2e8f0;
    --transition: all 0.3s ease;
    --border-radius: 16px;
    --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    --box-shadow-hover: 0 20px 50px rgba(0, 0, 0, 0.15);
  }
  
  /* Hero Section Canada réduite */
  .canada-hero {
    background: 
        linear-gradient(135deg, rgba(255, 0, 0, 0.8) 0%, rgba(245, 137, 130, 0.8) 100%),
        url('../images/canada c.png') no-repeat center center;
    color: var(--white);
    padding: 80px 20px 50px;
    text-align: center;
    margin-bottom: 0;
    position: relative;
    overflow: hidden;
    background-size: cover;
  }
  
  .canada-hero-content h1 {
    font-size: 2.8rem;
    margin-bottom: 15px;
    font-weight: 800;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }
  
  .canada-hero-content h1 i {
    margin-right: 15px;
    color: #fff;
  }
  
  .canada-hero-content p {
    font-size: 1.2rem;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto 25px;
    font-weight: 300;
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
    background: rgba(255, 255, 255, 0.15);
    padding: 15px 25px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
  }
  
  .stat-number {
    display: block;
    font-size: 2.2rem;
    font-weight: 800;
    margin-bottom: 5px;
  }
  
  .stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 500;
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

  /* Section Slider MODIFIÉE avec deux images par vue */
  .canada-slider {
    background: linear-gradient(135deg, #FFF5F5 0%, #FED7D7 100%);
    padding: 80px 20px;
    margin: 0;
    position: relative;
    width: 100%;
  }

  .canada-slider::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-red) 0%, var(--canada-white) 50%, var(--primary-red) 100%);
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
    color: var(--dark-text);
    font-weight: 700;
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
    border-radius: 12px;
    box-shadow: var(--box-shadow);
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
    height: 400px;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
  }

  .slide:hover {
    transform: translateY(-5px);
    box-shadow: var(--box-shadow-hover);
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
    background: linear-gradient(transparent, rgba(255, 0, 0, 0.8));
    color: white;
    padding: 25px 20px 20px;
    transform: translateY(0);
    transition: var(--transition);
  }

  .slide:hover .slide-content {
    background: linear-gradient(transparent, rgba(255, 0, 0, 0.95));
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
    box-shadow: var(--box-shadow);
    color: var(--primary-red);
  }

  .slider-btn:hover {
    background: var(--primary-red);
    color: white;
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
    background: white;
    transform: scale(1.3);
  }

  .indicator:hover {
    background: white;
  }
  
  /* Services Container */
  .services-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 80px 20px;
  }
  
  .services-container h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 15px;
    color: var(--dark-text);
    position: relative;
    font-weight: 700;
  }
  
  .services-subtitle {
    text-align: center;
    font-size: 1.2rem;
    color: #64748b;
    margin-bottom: 60px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
  }
  
  .services-container h2:after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: var(--accent-red);
    border-radius: 2px;
  }
  
  /* Services Grid */
  .services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 40px;
    margin-top: 40px;
  }
  
  /* Service Card */
  .service-card {
    background: var(--white);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--box-shadow);
    transition: var(--transition);
    position: relative;
  }
  
  .service-card:hover {
    transform: translateY(-15px);
    box-shadow: var(--box-shadow-hover);
  }
  
  .card-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--white);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    transition: var(--transition);
  }
  
  .service-card:hover .card-icon {
    transform: scale(1.1) rotate(5deg);
  }
  
  .card-icon i {
    font-size: 28px;
    color: var(--primary-red);
  }
  
  .card-image {
    height: 220px;
    overflow: hidden;
    position: relative;
  }
  
  .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
  }
  
  .card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
    opacity: 0.3;
    transition: var(--transition);
  }
  
  .service-card:hover .card-image img {
    transform: scale(1.1);
  }
  
  .service-card:hover .card-overlay {
    opacity: 0.5;
  }
  
  .card-content {
    padding: 30px;
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
    font-size: 1.05rem;
  }
  
  .service-features {
    text-align: left;
    margin-bottom: 30px;
    padding-left: 0;
  }
  
  .service-features li {
    list-style: none;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    color: #475569;
  }
  
  .service-features i {
    color: var(--accent-red);
    margin-right: 10px;
    font-size: 1.1rem;
  }
  
  .service-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 15px 35px;
    background: var(--primary-red);
    color: var(--white);
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid var(--primary-red);
    gap: 10px;
    cursor: pointer;
    border: none;
    font-family: inherit;
  }
  
  .service-btn:hover {
    background: transparent;
    color: var(--primary-red);
    transform: translateY(-2px);
  }
  
  .service-btn i {
    transition: var(--transition);
  }
  
  .service-btn:hover i {
    transform: translateX(5px);
  }
  
  /* Responsive Design */
  @media (max-width: 1024px) {
    .services-grid {
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 30px;
    }
  }
  
  @media (max-width: 768px) {
    .canada-hero {
      padding: 60px 20px 40px;
    }
    
    .canada-hero-content h1 {
      font-size: 2.2rem;
    }
    
    .canada-hero-content p {
      font-size: 1.1rem;
    }
    
    .hero-stats {
      gap: 20px;
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
      font-size: 2rem;
    }
    
    .services-grid {
      grid-template-columns: 1fr;
      max-width: 500px;
      margin: 0 auto;
    }
  }
  
  @media (max-width: 480px) {
    .canada-hero {
      padding: 50px 15px 30px;
    }
    
    .canada-hero-content h1 {
      font-size: 1.8rem;
    }
    
    .canada-hero-content p {
      font-size: 1rem;
    }
    
    .hero-stats {
      flex-direction: column;
      gap: 15px;
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
      padding: 20px;
    }
    
    .service-btn {
      width: 100%;
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