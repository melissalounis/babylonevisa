<?php
require_once __DIR__ . '../../../config.php';

$page_title = "Turquie — Services";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ===== STYLE TURQUIE ===== */
:root {
  --turquie-red: #E30A17;
  --turquie-white: #FFFFFF;
  --turquie-dark-red: #B50710;
  --turquie-light: #f8f9fa;
  --turquie-dark: #1a1a1a;
  --gradient-turquie: linear-gradient(135deg, var(--turquie-red) 0%, var(--turquie-dark-red) 100%);
  --shadow-turquie: 0 8px 25px rgba(227, 10, 23, 0.15);
  --shadow-turquie-hover: 0 15px 35px rgba(227, 10, 23, 0.25);
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

.text-turquie {
  color: var(--turquie-red);
  font-weight: 700;
}

/* Hero Section - PLUS PETITE */
.turquie-hero {
  background: var(--gradient-turquie);
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

.turquie-hero-content h1 {
  font-size: 2.2rem;
  margin-bottom: 12px;
  font-weight: 800;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  animation: fadeInUp 1s ease-out;
}

.turquie-hero-content h1 i {
  margin-right: 12px;
  color: var(--turquie-white);
  animation: pulse 2s infinite;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

.turquie-hero-content p {
  font-size: 1.1rem;
  opacity: 0.95;
  max-width: 500px;
  margin: 0 auto 20px;
  font-weight: 300;
  animation: fadeInUp 1s ease-out 0.2s both;
}

.hero-stats {
  display: flex;
  justify-content: center;
  gap: 40px;
  margin: 25px 0;
  flex-wrap: wrap;
  animation: fadeInUp 1s ease-out 0.4s both;
}

.stat {
  text-align: center;
  position: relative;
}

.stat-number {
  display: block;
  font-size: 1.8rem;
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
  padding: 10px 20px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  text-decoration: none;
  border-radius: 50px;
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
  font-weight: 600;
  font-size: 0.9rem;
}

.cta-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
}

.turquie-pattern {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.1) 2px, transparent 2px),
    radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 1px, transparent 1px);
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
    rgba(227, 10, 23, 0.9) 0%, 
    rgba(181, 7, 16, 0.9) 100%);
  z-index: 2;
}

.turquie-flag-element {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 70px;
  height: 45px;
  background: var(--turquie-white);
  border-radius: 3px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
  z-index: 3;
  opacity: 0.8;
  animation: float 3s ease-in-out infinite;
  display: flex;
  align-items: center;
  justify-content: center;
}

.turquie-flag-element::before {
  content: "";
  position: absolute;
  width: 25px;
  height: 25px;
  background: var(--turquie-red);
  border-radius: 50%;
}

/* Section Slider - UNE SEULE VUE */
.turquie-slider-section {
  background: linear-gradient(135deg, var(--turquie-light) 0%, #fff 100%);
  padding: 60px 20px;
  margin: 0;
  position: relative;
  width: 100%;
}

.turquie-slider-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--gradient-turquie);
}

.slider-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0;
}

.slider-container h2 {
  text-align: center;
  font-size: 2.2rem;
  margin-bottom: 12px;
  color: var(--turquie-red);
  font-weight: 700;
  font-family: 'Playfair Display', serif;
}

.slider-subtitle {
  text-align: center;
  font-size: 1.1rem;
  color: #666;
  margin-bottom: 40px;
  font-style: italic;
}

.slider-wrapper {
  position: relative;
  width: 100%;
  margin: 0 auto;
  overflow: hidden;
  border-radius: 12px;
  box-shadow: var(--shadow-turquie);
}

.slider-track {
  display: flex;
  transition: transform 0.5s ease-in-out;
}

/* UNE SEULE SLIDE PAR VUE */
.slide-view {
  min-width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.slide {
  position: relative;
  height: 350px;
  overflow: hidden;
  border-radius: 12px;
  box-shadow: var(--shadow-turquie);
  transition: var(--transition);
  width: 100%;
  max-width: 800px;
}

.slide:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-turquie-hover);
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
  background: linear-gradient(transparent, rgba(227, 10, 23, 0.8));
  color: white;
  padding: 20px 15px 15px;
  transform: translateY(0);
  transition: var(--transition);
}

.slide:hover .slide-content {
  background: linear-gradient(transparent, rgba(227, 10, 23, 0.95));
}

.slide-content h3 {
  font-size: 1.3rem;
  margin-bottom: 6px;
  font-weight: 700;
}

.slide-content p {
  font-size: 0.9rem;
  opacity: 0.9;
  margin: 0;
}

.slider-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(255, 255, 255, 0.9);
  border: none;
  width: 45px;
  height: 45px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: var(--transition);
  z-index: 10;
  box-shadow: var(--shadow-turquie);
  color: var(--turquie-red);
}

.slider-btn:hover {
  background: var(--turquie-red);
  color: white;
  transform: translateY(-50%) scale(1.1);
}

.prev-btn {
  left: 15px;
}

.next-btn {
  right: 15px;
}

.slider-indicators {
  position: absolute;
  bottom: 15px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 10px;
  z-index: 10;
}

.indicator {
  width: 10px;
  height: 10px;
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
  padding: 50px 20px;
}

.services-container h2 {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 12px;
  color: var(--turquie-red);
  position: relative;
  font-weight: 700;
}

.services-container h2:after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 70px;
  height: 3px;
  background: var(--gradient-turquie);
  border-radius: 2px;
}

.services-subtitle {
  text-align: center;
  font-size: 0.95rem;
  color: #666;
  margin-bottom: 35px;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

/* Services Grid - 4 cartes sur la même ligne */
.services-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  margin-top: 25px;
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
}

.turquie-card {
  background: white;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: var(--shadow-turquie);
  transition: all 0.3s ease;
  position: relative;
  border: 1px solid rgba(227, 10, 23, 0.1);
  height: fit-content;
}

.turquie-card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: var(--shadow-turquie-hover);
}

.card-icon {
  position: absolute;
  top: 10px;
  right: 10px;
  background: white;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3;
  box-shadow: 0 4px 12px rgba(227, 10, 23, 0.2);
  transition: all 0.3s ease;
}

.turquie-card:hover .card-icon {
  transform: scale(1.05) rotate(5deg);
  background: var(--gradient-turquie);
}

.turquie-card:hover .card-icon i {
  color: white;
}

.card-icon i {
  font-size: 18px;
  color: var(--turquie-red);
  transition: all 0.3s ease;
}

.card-image {
  height: 130px;
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
  background: linear-gradient(to bottom, transparent 0%, rgba(227, 10, 23, 0.7) 100%);
  transition: all 0.3s ease;
}

.turquie-card:hover .card-image img {
  transform: scale(1.05);
}

.turquie-card:hover .card-overlay {
  background: linear-gradient(to bottom, transparent 0%, var(--turquie-red) 100%);
}

.card-flag {
  position: absolute;
  top: 10px;
  left: 10px;
  width: 45px;
  height: 30px;
  background: var(--turquie-white);
  border-radius: 3px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-flag::before {
  content: "";
  position: absolute;
  width: 18px;
  height: 18px;
  background: var(--turquie-red);
  border-radius: 50%;
}

.card-content {
  padding: 16px;
  text-align: center;
}

.card-content h3 {
  font-size: 1rem;
  margin-bottom: 8px;
  color: var(--turquie-red);
  font-weight: 700;
}

.card-content p {
  color: #666;
  margin-bottom: 10px;
  line-height: 1.4;
  font-size: 0.82rem;
  min-height: 35px;
}

.service-features {
  text-align: left;
  margin-bottom: 12px;
  padding-left: 0;
}

.service-features li {
  list-style: none;
  margin-bottom: 5px;
  display: flex;
  align-items: center;
  color: #555;
  font-weight: 500;
  font-size: 0.78rem;
}

.service-features i {
  color: var(--turquie-red);
  margin-right: 5px;
  font-size: 0.75rem;
  min-width: 12px;
}

.card-actions {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.turquie-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 14px;
  background: var(--gradient-turquie);
  color: white;
  border-radius: 18px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
  gap: 5px;
  font-size: 0.8rem;
  cursor: pointer;
}

.turquie-btn:hover {
  background: var(--turquie-dark-red);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(227, 10, 23, 0.3);
}

.turquie-btn i {
  transition: transform 0.3s ease;
}

.turquie-btn:hover i {
  transform: translateX(3px);
}

.turquie-info {
  background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
  border-left: 3px solid var(--turquie-red);
  border-radius: 8px;
  padding: 18px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-top: 35px;
}

.info-icon {
  font-size: 1.3rem;
  color: var(--turquie-red);
}

.info-content h4 {
  color: var(--turquie-dark-red);
  margin-bottom: 6px;
  font-size: 1rem;
}

.info-content p {
  color: #666;
  line-height: 1.5;
  font-size: 0.85rem;
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
    gap: 18px;
    max-width: 800px;
  }
  
  .slide {
    height: 300px;
  }
}

@media (max-width: 768px) {
  .turquie-hero {
    padding: 50px 0;
    min-height: auto;
  }
  
  .turquie-hero-content h1 {
    font-size: 1.8rem;
  }
  
  .turquie-hero-content p {
    font-size: 1rem;
  }
  
  .hero-stats {
    gap: 20px;
  }
  
  .stat-number {
    font-size: 1.5rem;
  }
  
  .services-container {
    padding: 40px 20px;
  }
  
  .services-container h2 {
    font-size: 1.6rem;
  }
  
  .turquie-flag-element {
    width: 60px;
    height: 40px;
    top: 10px;
    right: 10px;
  }
  
  .services-grid {
    grid-template-columns: 1fr;
    max-width: 400px;
  }
  
  .slider-container h2 {
    font-size: 1.8rem;
  }
  
  .slide {
    height: 250px;
  }
  
  .slide-content {
    padding: 15px 12px 12px;
  }
  
  .slide-content h3 {
    font-size: 1.1rem;
  }
  
  .slide-content p {
    font-size: 0.85rem;
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
}

@media (max-width: 480px) {
  .turquie-hero-content h1 {
    font-size: 1.5rem;
  }
  
  .hero-stats {
    flex-direction: column;
    gap: 12px;
  }
  
  .services-container h2 {
    font-size: 1.4rem;
  }
  
  .card-content {
    padding: 14px;
  }
  
  .turquie-btn {
    width: 100%;
    justify-content: space-between;
  }
  
  .slide {
    height: 200px;
  }
  
  .slide-content {
    padding: 12px 10px 10px;
  }
  
  .slide-content h3 {
    font-size: 1rem;
  }
  
  .slide-content p {
    font-size: 0.8rem;
  }
  
  .slider-btn {
    width: 35px;
    height: 35px;
  }
  
  .prev-btn {
    left: 8px;
  }
  
  .next-btn {
    right: 8px;
  }
  
  .slider-indicators {
    bottom: 12px;
  }
  
  .indicator {
    width: 8px;
    height: 8px;
  }
}
</style>

<div class="turquie-hero">
  <div class="hero-content-wrapper">
    <div class="turquie-hero-content">
      <h1><i class="fas fa-mosque"></i> Turquie — Services</h1>
      <p>Découvrez nos services spécialisés pour réaliser votre projet en Turquie</p>
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
          <span class="stat-number" data-count="4000">0</span>
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
  <div class="turquie-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="turquie-flag-element"></div>
</div>

<!-- Section Slider - UNE SEULE VUE -->
<div class="turquie-slider-section">
  <div class="slider-container">
    <h2>Nos Réalisations en <span class="text-turquie">Turquie</span></h2>
    <p class="slider-subtitle">Découvrez nos succès et témoignages</p>
    
    <div class="slider-wrapper">
      <div class="slider-track">
        <!-- Vue unique -->
        <div class="slide-view">
          <div class="slide">
            <div class="image-container">
              <img src="../images/accturq.jpeg" alt="Projets professionnels">
            </div>
            <div class="slide-content">
              <h3>Carrières Lancées</h3>
              <p>Insertion professionnelle réussie en Turquie</p>
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
      </div>
    </div>
  </div>
</div>

<div class="services-container" id="services">
  <h2>Nos services pour la <span class="text-turquie">Turquie</span></h2>
  <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet</p>
  
  <div class="services-grid">
    <!-- Rendez-vous Card -->
    <div class="turquie-card">
      <div class="card-icon">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="card-image">
        <img src="../images/visapasport.jpg" alt="Prise de Rendez-vous Turquie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Prise de Rendez-vous</h3>
        <p>Service professionnel de prise de rendez-vous pour vos démarches</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Rendez-vous consulaires</li>
          <li><i class="fas fa-check-circle"></i> Prise de rendez-vous en ligne</li>
          <li><i class="fas fa-check-circle"></i> Assistance administrative</li>
        </ul>
        <div class="card-actions">
          <a class="turquie-btn" href="../../rendez_vous.php">
            <span>Prendre RDV</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Études Card -->
    <div class="turquie-card">
      <div class="card-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>
      <div class="card-image">
        <img src="../images/etudiant.avif" alt="Études en Turquie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Études</h3>
        <p>Universités turques et programmes d'enseignement supérieur</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Procédures académiques</li>
          <li><i class="fas fa-check-circle"></i> Orientation universitaire</li>
          <li><i class="fas fa-check-circle"></i> Bourses d'études</li>
        </ul>
        <div class="card-actions">
          <a class="turquie-btn" href="etude/index2.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Tourisme & Affaires Card -->
    <div class="turquie-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/voy3.jpg" alt="Tourisme en Turquie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & Affaires</h3>
        <p>Voyages et séjours professionnels en Turquie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Formalités de voyage</li>
          <li><i class="fas fa-check-circle"></i> Séjours d'affaires</li>
          <li><i class="fas fa-check-circle"></i> Guides touristiques</li>
        </ul>
        <div class="card-actions">
          <a class="turquie-btn" href="../../tourisme/tourisme.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Travail Card -->
    <div class="turquie-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail en Turquie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Opportunités professionnelles et insertion en Turquie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa de travail</li>
          <li><i class="fas fa-check-circle"></i> Formalités administratives</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
        </ul>
        <div class="card-actions">
          <a class="turquie-btn" href="../travail/travail.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="turquie-info">
    <div class="info-icon">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="info-content">
      <h4>Important : Visa Turquie</h4>
      <p>La Turquie offre un accès unique entre l'Europe et l'Asie avec des opportunités variées pour les études, le travail et le tourisme. Nos experts vous accompagnent dans toutes les démarches administratives spécifiques à la Turquie.</p>
    </div>
  </div>
</div>

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
  });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>