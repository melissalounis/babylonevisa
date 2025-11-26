<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Espagne — Services";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="country-hero espagne-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-sun"></i> Espagne — Services</h1>
      <p>Découvrez nos services spécialisés pour réaliser votre projet en Espagne</p>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-number" data-count="95">0</span>
          <span class="stat-label">de réussite</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="8">0</span>
          <span class="stat-label">ans d'expérience</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="3200">0</span>
          <span class="stat-label">clients satisfaits</span>
        </div>
      </div>
    </div>
  </div>
  <div class="hero-pattern espagne-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="espagne-flag-element"></div>
</div>

<!-- Section Slider MODIFIÉE avec deux images par vue -->
<div class="image-slider-section espagne-slider">
  <div class="slider-container">
    <h2>Nos Réalisations en Espagne</h2>
    <p class="slider-subtitle">Découvrez nos succès et témoignages</p>
    
    <div class="slider-wrapper">
      <div class="slider-track">
        <!-- Vue 1 -->
        <div class="slide-view">
          <div class="slide-duo">
            <div class="slide">
              <div class="image-container">
                <img src="../images/espsuc.jpg" alt="Étudiants en Espagne">
              </div>
              <div class="slide-content">
                <h3>Étudiants Admis</h3>
                <p>Plus de 800 étudiants admis dans les universités espagnoles</p>
              </div>
            </div>
            <div class="slide">
              <div class="image-container">
                <img src="../images/esp1.jpg" alt="Visa Espagne obtenu">
              </div>
              <div class="slide-content">
                <h3>Visas Accordés</h3>
                <p>95% de taux de réussite pour l'obtention des visas espagnols</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Vue 2 -->
        <div class="slide-view">
          <div class="slide-duo">
            <div class="slide">
              <div class="image-container">
                <img src="../images/espsuc.jpg" alt="Vie en Espagne">
              </div>
              <div class="slide-content">
                <h3>Nouvelles Vies</h3>
                <p>Installation réussie de plus de 1500 personnes en Espagne</p>
              </div>
            </div>
            <div class="slide">
              <div class="image-container">
                <img src="../images/esp famil.png" alt="Projets professionnels">
              </div>
              <div class="slide-content">
                <h3>Carrières Internationales</h3>
                <p>Insertion professionnelle réussie dans toute l'Espagne</p>
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

<div class="services-container" id="services">
  <h2>Nos services pour l'<span class="text-espagne">Espagne</span></h2>
  <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet</p>
  
  <div class="services-grid">
    <!-- Rendez-vous Card -->
    <div class="service-card espagne-card">
      <div class="card-icon">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="card-image">
        <img src="../images/voy2.jpg" alt="Prise de Rendez-vous Espagne">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Prise de Rendez-vous</h3>
        <p>Prenez rendez-vous pour toutes vos démarches administratives en Espagne</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Rendez-vous consulaires</li>
          <li><i class="fas fa-check-circle"></i> Ambassades et consulats</li>
          <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn espagne-btn" href="/babylone/rendez_vous.php">
            <span>Prendre RDV</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Études Card -->
    <div class="service-card espagne-card">
      <div class="card-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>
      <div class="card-image">
        <img src="../images/etudiant.avif" alt="Études en Espagne">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Études</h3>
        <p>Universités, Écoles et Établissements d'enseignement supérieur en Espagne</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa étudiant espagnol</li>
          <li><i class="fas fa-check-circle"></i> Inscriptions universitaires</li>
          <li><i class="fas fa-check-circle"></i> Bourses d'études</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn espagne-btn" href="/babylone/public/espagne/etudes/admission.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Tourisme & Affaires Card -->
    <div class="service-card espagne-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/tourisme.jpg" alt="Tourisme en Espagne">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & Affaires</h3>
        <p>Visa Schengen, voyages d'affaires et séjours touristiques en Espagne</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa Schengen Espagne</li>
          <li><i class="fas fa-check-circle"></i> Voyages d'affaires</li>
          <li><i class="fas fa-check-circle"></i> Séjours touristiques</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn espagne-btn" href="/babylone/tourisme/tourisme.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Travail Card -->
    <div class="service-card espagne-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail en Espagne">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Opportunités professionnelles et démarches pour travailler en Espagne</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa travail espagnol</li>
          <li><i class="fas fa-check-circle"></i> Permis de travail</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn espagne-btn" href="/babylone/public/travail/travail.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="service-info-box espagne-info">
    <div class="info-icon">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="info-content">
      <h4>Important : Visa Schengen Espagne</h4>
      <p>Le visa espagnol vous donne accès à tout l'espace Schengen. Notre expertise couvre l'ensemble des procédures consulaires espagnoles.</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<style>
  :root {
    --espagne-red: #AA151B;
    --espagne-yellow: #F1BF00;
    --espagne-gold: #FFD700;
    --espagne-dark-red: #8A0E15;
    --espagne-dark-yellow: #D4A900;
    --espagne-light-yellow: #FFF8E1;
    --gradient-espagne: linear-gradient(135deg, var(--espagne-red) 0%, var(--espagne-yellow) 50%, var(--espagne-red) 100%);
    --gradient-espagne-light: linear-gradient(135deg, var(--espagne-dark-red) 0%, var(--espagne-dark-yellow) 50%, var(--espagne-dark-red) 100%);
    --shadow-espagne: 0 8px 20px rgba(170, 21, 27, 0.15);
    --shadow-espagne-hover: 0 15px 35px rgba(170, 21, 27, 0.25);
    --transition: all 0.3s ease;
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

  .text-espagne {
    background: var(--gradient-espagne);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
  }

  /* Hero Section réduite */
  .espagne-hero {
    background: var(--gradient-espagne);
    color: white;
    padding: 80px 0 50px;
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-bottom: 0;
  }

  .hero-content-wrapper {
    position: relative;
    z-index: 3;
    width: 100%;
  }

  .country-hero-content h1 {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 800;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    animation: fadeInUp 1s ease-out;
  }

  .country-hero-content h1 i {
    margin-right: 15px;
    color: var(--espagne-gold);
    animation: pulse 2s infinite;
    text-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
  }

  .country-hero-content p {
    font-size: 1.1rem;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto 25px;
    font-weight: 300;
    animation: fadeInUp 1s ease-out 0.2s both;
  }

  .hero-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin: 30px 0;
    flex-wrap: wrap;
    animation: fadeInUp 1s ease-out 0.4s both;
  }

  .stat {
    text-align: center;
    position: relative;
    background: rgba(255, 255, 255, 0.15);
    padding: 15px 20px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
  }

  .stat-number {
    display: block;
    font-size: 2rem;
    font-weight: 800;
    margin-bottom: 5px;
    color: white;
    transition: all 0.5s ease;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }

  .stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 500;
    color: white;
  }

  .espagne-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 20% 30%, rgba(170, 21, 27, 0.1) 2px, transparent 2px),
      radial-gradient(circle at 80% 70%, rgba(241, 191, 0, 0.1) 1px, transparent 1px);
    background-size: 80px 80px;
    z-index: 1;
    animation: patternMove 20s linear infinite;
  }

  .hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, 
      rgba(170, 21, 27, 0.8) 0%, 
      rgba(241, 191, 0, 0.8) 50%, 
      rgba(170, 21, 27, 0.8) 100%);
    z-index: 2;
  }

  .espagne-flag-element {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 70px;
    height: 45px;
    background: 
      linear-gradient(180deg, 
        var(--espagne-red) 0% 25%, 
        var(--espagne-yellow) 25% 75%, 
        var(--espagne-red) 75% 100%);
    z-index: 3;
    opacity: 0.8;
    border-radius: 3px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
  }

  /* Section Slider MODIFIÉE avec deux images par vue */
  .espagne-slider {
    background: linear-gradient(135deg, #FFF8E1 0%, #FFECB3 100%);
    padding: 80px 20px;
    margin: 0;
    position: relative;
    width: 100%;
  }

  .espagne-slider::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--espagne-red) 33%, var(--espagne-yellow) 33%, var(--espagne-yellow) 66%, var(--espagne-red) 66%);
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
    color: var(--espagne-dark-red);
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
    border-radius: 12px;
    box-shadow: var(--shadow-espagne);
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
    box-shadow: var(--shadow-espagne);
    transition: var(--transition);
  }

  .slide:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-espagne-hover);
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
    background: linear-gradient(transparent, rgba(170, 21, 27, 0.8));
    color: white;
    padding: 25px 20px 20px;
    transform: translateY(0);
    transition: var(--transition);
  }

  .slide:hover .slide-content {
    background: linear-gradient(transparent, rgba(170, 21, 27, 0.95));
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
    box-shadow: var(--shadow-espagne);
    color: var(--espagne-red);
  }

  .slider-btn:hover {
    background: var(--espagne-red);
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
    max-width: 1400px;
    margin: 0 auto;
    padding: 80px 20px;
  }

  .services-container h2 {
    text-align: center;
    font-size: 2.2rem;
    margin-bottom: 15px;
    color: var(--espagne-dark-red);
    position: relative;
    font-weight: 700;
  }

  .services-container h2:after {
    content: '';
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: var(--gradient-espagne);
    border-radius: 2px;
  }

  .services-subtitle {
    text-align: center;
    font-size: 1rem;
    color: #666;
    margin-bottom: 40px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
  }

  /* Services Grid - 4 cartes sur la même ligne */
  .services-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 30px;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
  }

  .espagne-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-espagne);
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid rgba(170, 21, 27, 0.1);
    height: fit-content;
  }

  .espagne-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: var(--shadow-espagne-hover);
  }

  .card-icon {
    position: absolute;
    top: 12px;
    right: 12px;
    background: white;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    box-shadow: 0 4px 12px rgba(170, 21, 27, 0.2);
    transition: all 0.3s ease;
  }

  .espagne-card:hover .card-icon {
    transform: scale(1.05) rotate(5deg);
    background: var(--gradient-espagne);
  }

  .espagne-card:hover .card-icon i {
    color: white;
  }

  .card-icon i {
    font-size: 20px;
    color: var(--espagne-red);
    transition: all 0.3s ease;
  }

  .card-image {
    height: 140px;
    overflow: hidden;
    position: relative;
  }

  .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.4s ease;
  }

  .card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 0%, rgba(170, 21, 27, 0.7) 100%);
    transition: all 0.3s ease;
  }

  .espagne-card:hover .card-image img {
    transform: scale(1.05);
  }

  .espagne-card:hover .card-overlay {
    background: linear-gradient(to bottom, transparent 0%, var(--espagne-red) 100%);
  }

  .card-flag {
    position: absolute;
    top: 12px;
    left: 12px;
    width: 50px;
    height: 35px;
    background: 
      linear-gradient(180deg, 
        var(--espagne-red) 0% 25%, 
        var(--espagne-yellow) 25% 75%, 
        var(--espagne-red) 75% 100%);
    border-radius: 3px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  }

  .card-content {
    padding: 18px;
    text-align: center;
  }

  .card-content h3 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: var(--espagne-dark-red);
    font-weight: 700;
  }

  .card-content p {
    color: #666;
    margin-bottom: 12px;
    line-height: 1.4;
    font-size: 0.85rem;
    min-height: 40px;
  }

  .service-features {
    text-align: left;
    margin-bottom: 15px;
    padding-left: 0;
  }

  .service-features li {
    list-style: none;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    color: #555;
    font-weight: 500;
    font-size: 0.8rem;
  }

  .service-features i {
    color: var(--espagne-red);
    margin-right: 6px;
    font-size: 0.8rem;
    min-width: 14px;
  }

  .card-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .espagne-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 16px;
    background: var(--gradient-espagne);
    color: white;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    gap: 6px;
    font-size: 0.85rem;
    cursor: pointer;
  }

  .espagne-btn:hover {
    background: var(--gradient-espagne-light);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(170, 21, 27, 0.3);
  }

  .espagne-btn i {
    transition: transform 0.3s ease;
  }

  .espagne-btn:hover i {
    transform: translateX(3px);
  }

  .espagne-info {
    background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
    border-left: 3px solid var(--espagne-red);
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-top: 40px;
  }

  .info-icon {
    font-size: 1.5rem;
    color: var(--espagne-red);
  }

  .info-content h4 {
    color: var(--espagne-dark-red);
    margin-bottom: 8px;
    font-size: 1.1rem;
  }

  .info-content p {
    color: #666;
    line-height: 1.5;
    font-size: 0.9rem;
  }

  /* Animations */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(20px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
    }
    50% {
      transform: scale(1.05);
    }
  }

  @keyframes patternMove {
    from {
      background-position: 0 0;
    }
    to {
      background-position: 80px 80px;
    }
  }

  @keyframes float {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-5px);
    }
  }

  /* Responsive Design */
  @media (max-width: 1200px) {
    .services-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
      max-width: 800px;
    }
    
    .espagne-card {
      max-width: 100%;
    }
  }

  @media (max-width: 768px) {
    .country-hero {
      padding: 60px 0 40px;
    }
    
    .country-hero-content h1 {
      font-size: 2rem;
    }
    
    .country-hero-content p {
      font-size: 1rem;
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
    
    .services-container {
      padding: 60px 20px;
    }
    
    .services-container h2 {
      font-size: 1.8rem;
    }
    
    .espagne-flag-element {
      width: 60px;
      height: 40px;
      top: 10px;
      right: 10px;
    }
    
    .services-grid {
      grid-template-columns: 1fr;
      max-width: 400px;
    }
  }

  @media (max-width: 480px) {
    .country-hero-content h1 {
      font-size: 1.6rem;
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
    
    .services-container h2 {
      font-size: 1.5rem;
    }
    
    .card-content {
      padding: 15px;
    }
    
    .service-btn {
      width: 100%;
      justify-content: space-between;
    }
  }
</style>

<script>
  // Animation des statistiques
  document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;
    
    counters.forEach(counter => {
      const target = +counter.getAttribute('data-count');
      const count = +counter.innerText;
      const increment = target / speed;
      
      if (count < target) {
        counter.innerText = Math.ceil(count + increment);
        setTimeout(updateCount, 1);
      } else {
        counter.innerText = target;
      }
      
      function updateCount() {
        const current = +counter.innerText;
        if (current < target) {
          counter.innerText = Math.ceil(current + increment);
          setTimeout(updateCount, 1);
        } else {
          counter.innerText = target;
        }
      }
    });

    // Script pour le slider avec deux images par vue
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