<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Italie — Services";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="country-hero italie-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-pizza-slice"></i> Italie — Services</h1>
      <p>Découvrez nos services spécialisés pour réaliser votre projet en Italie</p>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-number" data-count="95">0</span>
          <span class="stat-label">de réussite</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="10">0</span>
          <span class="stat-label">ans d'expérience</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="3200">0</span>
          <span class="stat-label">clients satisfaits</span>
        </div>
      </div>
      <div class="hero-cta">
        <a href="#services" class="cta-button">
          <span>Découvrir nos services</span>
          <i class="fas fa-chevron-down"></i>
        </a>
      </div>
    </div>
  </div>
  <div class="hero-pattern italie-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="italie-flag-element"></div>
</div>



<div class="services-container" id="services">
  <h2>Nos services pour l'<span class="text-italie">Italie</span></h2>
  <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet</p>
  
  <div class="services-grid">
    <!-- Rendez-vous Card -->
    <div class="service-card italie-card">
      <div class="card-icon">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="card-image">
        <img src="../images/visa.jpg" alt="Prise de Rendez-vous Italie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Prise de Rendez-vous</h3>
        <p>Prenez rendez-vous pour toutes vos démarches administratives en Italie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Rendez-vous consulaires</li>
          <li><i class="fas fa-check-circle"></i> Ambassades et consulats</li>
          <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn italie-btn" href="/babylone/rendez_vous.php">
            <span>Prendre RDV</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Études Card -->
    <div class="service-card italie-card">
      <div class="card-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>
      <div class="card-image">
        <img src="../images/etudiant.avif" alt="Études en Italie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Études</h3>
        <p>Universités, Écoles et Établissements d'enseignement supérieur en Italie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa étudiant italien</li>
          <li><i class="fas fa-check-circle"></i> Inscriptions universitaires</li>
          <li><i class="fas fa-check-circle"></i> Bourses d'études</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn italie-btn" href="/babylone/public/italie/etudes/etude.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Tourisme & Affaires Card -->
    <div class="service-card italie-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/voy3.jpg" alt="Tourisme en Italie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & Affaires</h3>
        <p>Visa Schengen, voyages d'affaires et séjours touristiques en Italie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa Schengen Italie</li>
          <li><i class="fas fa-check-circle"></i> Voyages d'affaires</li>
          <li><i class="fas fa-check-circle"></i> Séjours touristiques</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn italie-btn" href="/babylone/tourisme/tourisme.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Travail Card -->
    <div class="service-card italie-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail en Italie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Opportunités professionnelles et démarches pour travailler en Italie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa travail italien</li>
          <li><i class="fas fa-check-circle"></i> Permis de travail</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn italie-btn" href="/babylone/public/travail/travail.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- NOUVELLE CARTE - Demandes de bourse -->
    <div class="service-card italie-card">
      <div class="card-icon">
        <i class="fa-solid fa-money-bill-wave"></i>
      </div>
      <div class="card-image">
        <img src="../images/bourse.jpg" alt="Demandes de bourse Italie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Demandes de Bourse</h3>
        <p>Aides financières et programmes de bourses pour étudier en Italie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Bourses gouvernementales</li>
          <li><i class="fas fa-check-circle"></i> Aides universitaires</li>
          <li><i class="fas fa-check-circle"></i> Programmes internationaux</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn italie-btn" href="/babylone/public/italie/bourses/bourses.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="service-info-box italie-info">
    <div class="info-icon">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="info-content">
      <h4>Important : Visa Schengen Italie</h4>
      <p>Le visa italien vous donne accès à tout l'espace Schengen. Notre expertise couvre l'ensemble des procédures consulaires italiennes.</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<style>
  :root {
    --italie-green: #008C45;
    --italie-white: #FFFFFF;
    --italie-red: #CD212A;
    --italie-gold: #FFD700;
    --italie-dark-green: #006B34;
    --italie-dark-red: #A51C22;
    --italie-light-green: #E8F5E8;
    --gradient-italie: linear-gradient(135deg, var(--italie-green) 0%, var(--italie-white) 50%, var(--italie-red) 100%);
    --gradient-italie-light: linear-gradient(135deg, var(--italie-dark-green) 0%, var(--italie-white) 50%, var(--italie-dark-red) 100%);
    --shadow-italie: 0 8px 20px rgba(0, 140, 69, 0.15);
    --shadow-italie-hover: 0 15px 35px rgba(0, 140, 69, 0.25);
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

  .text-italie {
    background: var(--gradient-italie);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
  }

  /* Hero Section - Plus petite */
  .italie-hero {
    background: var(--gradient-italie);
    color: white;
    padding: 60px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 50vh;
    display: flex;
    align-items: center;
  }

  .hero-content-wrapper {
    position: relative;
    z-index: 3;
    width: 100%;
  }

  .country-hero-content h1 {
    font-size: 2.2rem;
    margin-bottom: 15px;
    font-weight: 800;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    animation: fadeInUp 1s ease-out;
  }

  .country-hero-content h1 i {
    margin-right: 15px;
    color: var(--italie-gold);
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

  .hero-cta {
    animation: fadeInUp 1s ease-out 0.6s both;
  }

  .cta-button {
    display: inline-flex;
    align-items: center;
    padding: 12px 25px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    font-weight: 600;
    font-size: 0.95rem;
  }

  .cta-button:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
  }

  .italie-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 20% 30%, rgba(0, 140, 69, 0.1) 2px, transparent 2px),
      radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
      radial-gradient(circle at 40% 80%, rgba(205, 33, 42, 0.1) 3px, transparent 3px);
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
      rgba(0, 140, 69, 0.8) 0%, 
      rgba(255, 255, 255, 0.8) 50%, 
      rgba(205, 33, 42, 0.8) 100%);
    z-index: 2;
  }

  .italie-flag-element {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 70px;
    height: 45px;
    background: 
      linear-gradient(90deg, 
        var(--italie-green) 0% 33%, 
        var(--italie-white) 33% 66%, 
        var(--italie-red) 66% 100%);
    z-index: 3;
    opacity: 0.8;
    border-radius: 3px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
  }

  /* Section Slides - Images pleine largeur */
  .slides-section {
    padding: 0;
    position: relative;
    width: 100%;
    overflow: hidden;
  }

  .slides-container {
    width: 100%;
    position: relative;
    overflow: hidden;
  }

  .slides-wrapper {
    display: flex;
    transition: transform 0.6s ease;
    height: 70vh;
  }

  .slide {
    min-width: 100%;
    height: 100%;
    position: relative;
  }

  .slide-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .slide-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 40px;
    color: white;
  }

  .slide-content {
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
  }

  .slide-title {
    font-size: 2.5rem;
    margin-bottom: 15px;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
  }

  .slide-subtitle {
    font-size: 1.3rem;
    margin-bottom: 25px;
    max-width: 600px;
    opacity: 0.9;
  }

  .slide-btn {
    display: inline-flex;
    align-items: center;
    padding: 12px 25px;
    background: var(--italie-green);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    gap: 8px;
  }

  .slide-btn:hover {
    background: transparent;
    border-color: white;
    transform: translateY(-3px);
  }

  .slides-nav {
    position: absolute;
    bottom: 30px;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    gap: 15px;
    z-index: 10;
  }

  .slide-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .slide-dot.active {
    background: white;
    transform: scale(1.2);
  }

  .slide-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    transition: all 0.3s ease;
    border: none;
    font-size: 1.5rem;
    color: white;
    backdrop-filter: blur(10px);
  }

  .slide-nav-btn:hover {
    background: var(--italie-green);
    transform: translateY(-50%) scale(1.1);
  }

  .prev-btn {
    left: 30px;
  }

  .next-btn {
    right: 30px;
  }

  /* Services Container */
  .services-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 60px 20px;
  }

  .services-container h2 {
    text-align: center;
    font-size: 2.2rem;
    margin-bottom: 15px;
    color: var(--italie-dark-green);
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
    background: var(--gradient-italie);
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

  /* Services Grid - 5 cartes avec disposition responsive */
  .services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-top: 30px;
    max-width: 1400px;
    margin-left: auto;
    margin-right: auto;
  }

  .italie-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: var(--shadow-italie);
    transition: all 0.3s ease;
    position: relative;
    border: 1px solid rgba(0, 140, 69, 0.1);
    height: fit-content;
  }

  .italie-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: var(--shadow-italie-hover);
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
    box-shadow: 0 4px 12px rgba(0, 140, 69, 0.2);
    transition: all 0.3s ease;
  }

  .italie-card:hover .card-icon {
    transform: scale(1.05) rotate(5deg);
    background: var(--gradient-italie);
  }

  .italie-card:hover .card-icon i {
    color: white;
  }

  .card-icon i {
    font-size: 20px;
    color: var(--italie-green);
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
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 140, 69, 0.7) 100%);
    transition: all 0.3s ease;
  }

  .italie-card:hover .card-image img {
    transform: scale(1.05);
  }

  .italie-card:hover .card-overlay {
    background: linear-gradient(to bottom, transparent 0%, var(--italie-green) 100%);
  }

  .card-flag {
    position: absolute;
    top: 12px;
    left: 12px;
    width: 50px;
    height: 35px;
    background: 
      linear-gradient(90deg, 
        var(--italie-green) 0% 33%, 
        var(--italie-white) 33% 66%, 
        var(--italie-red) 66% 100%);
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
    color: var(--italie-dark-green);
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
    color: var(--italie-green);
    margin-right: 6px;
    font-size: 0.8rem;
    min-width: 14px;
  }

  .card-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .italie-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 16px;
    background: var(--gradient-italie);
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

  .italie-btn:hover {
    background: var(--gradient-italie-light);
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(0, 140, 69, 0.3);
  }

  .italie-btn i {
    transition: transform 0.3s ease;
  }

  .italie-btn:hover i {
    transform: translateX(3px);
  }

  .italie-info {
    background: linear-gradient(135deg, var(--italie-light-green) 0%, #f0f8f0 100%);
    border-left: 3px solid var(--italie-green);
    border-radius: 10px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-top: 40px;
  }

  .info-icon {
    font-size: 1.5rem;
    color: var(--italie-green);
  }

  .info-content h4 {
    color: var(--italie-dark-green);
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
    
    .italie-card {
      max-width: 100%;
    }
  }

  @media (max-width: 768px) {
    .italie-hero {
      padding: 50px 0;
      min-height: auto;
    }
    
    .country-hero-content h1 {
      font-size: 1.8rem;
    }
    
    .country-hero-content p {
      font-size: 1rem;
    }
    
    .hero-stats {
      gap: 25px;
    }
    
    .stat-number {
      font-size: 1.6rem;
    }
    
    .services-container {
      padding: 50px 20px;
    }
    
    .services-container h2 {
      font-size: 1.8rem;
    }
    
    .italie-flag-element {
      width: 60px;
      height: 35px;
      top: 10px;
      right: 10px;
    }
    
    .services-grid {
      grid-template-columns: 1fr;
      max-width: 400px;
    }
    
    .slides-wrapper {
      height: 60vh;
    }
    
    .slide-overlay {
      padding: 30px 20px;
    }
    
    .slide-title {
      font-size: 2rem;
    }
    
    .slide-subtitle {
      font-size: 1.1rem;
    }
    
    .slide-nav-btn {
      width: 50px;
      height: 50px;
      font-size: 1.2rem;
    }
    
    .prev-btn {
      left: 15px;
    }
    
    .next-btn {
      right: 15px;
    }
  }

  @media (max-width: 480px) {
    .country-hero-content h1 {
      font-size: 1.5rem;
    }
    
    .hero-stats {
      flex-direction: column;
      gap: 15px;
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
    
    .slides-wrapper {
      height: 50vh;
    }
    
    .slide-title {
      font-size: 1.6rem;
    }
    
    .slide-subtitle {
      font-size: 1rem;
    }
    
    .slide-nav-btn {
      width: 40px;
      height: 40px;
      font-size: 1rem;
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

    // Gestion du slider
    const slidesWrapper = document.querySelector('.slides-wrapper');
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slide-dot');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    let currentSlide = 0;
    let slideInterval;
    
    function updateSlider() {
      slidesWrapper.style.transform = `translateX(-${currentSlide * 100}%)`;
      
      // Mise à jour des points indicateurs
      dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
      });
    }
    
    // Événements pour les boutons de navigation
    nextBtn.addEventListener('click', () => {
      currentSlide = (currentSlide + 1) % slides.length;
      updateSlider();
      resetInterval();
    });
    
    prevBtn.addEventListener('click', () => {
      currentSlide = (currentSlide - 1 + slides.length) % slides.length;
      updateSlider();
      resetInterval();
    });
    
    // Événements pour les points indicateurs
    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        currentSlide = index;
        updateSlider();
        resetInterval();
      });
    });
    
    // Défilement automatique
    function startInterval() {
      slideInterval = setInterval(() => {
        currentSlide = (currentSlide + 1) % slides.length;
        updateSlider();
      }, 5000);
    }
    
    function resetInterval() {
      clearInterval(slideInterval);
      startInterval();
    }
    
    startInterval();
    
    // Pause au survol
    slidesWrapper.addEventListener('mouseenter', () => {
      clearInterval(slideInterval);
    });
    
    slidesWrapper.addEventListener('mouseleave', () => {
      startInterval();
    });
  });
</script>