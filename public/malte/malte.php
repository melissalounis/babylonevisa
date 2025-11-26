<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Malte — Tourisme & Affaires";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root {
    --mt-red: #CF142B;
    --mt-white: #FFFFFF;
    --mt-gray: #7B7B7B;
    --mt-dark-red: #B01024;
    --mt-light-red: #F8E8EA;
    --mt-gold: #D4AF37;
    --mt-light: #F7FAFC;
    --mt-dark: #2D3748;
    
    --gradient-mt: linear-gradient(135deg, var(--mt-red) 0%, var(--mt-white) 50%, var(--mt-red) 100%);
    --gradient-mt-dark: linear-gradient(135deg, var(--mt-dark-red) 0%, var(--mt-white) 50%, var(--mt-dark-red) 100%);
    --gradient-red: linear-gradient(135deg, var(--mt-red) 0%, var(--mt-dark-red) 100%);
    --gradient-red-white: linear-gradient(135deg, var(--mt-red) 0%, var(--mt-white) 100%);
    --gradient-maltese: linear-gradient(135deg, var(--mt-red) 0%, var(--mt-white) 50%, var(--mt-red) 100%);
    
    --shadow-mt: 0 10px 30px rgba(207, 20, 43, 0.15);
    --shadow-mt-hover: 0 20px 50px rgba(207, 20, 43, 0.25);
    --shadow-soft: 0 4px 12px rgba(0, 0, 0, 0.08);
    
    --border-radius: 16px;
    --transition: all 0.3s ease;
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Inter', sans-serif;
    color: var(--mt-dark);
    line-height: 1.6;
    background-color: var(--mt-light);
    overflow-x: hidden;
  }

  h1, h2, h3, h4, h5 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .text-mt {
    background: var(--gradient-maltese);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
  }

  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
  }

  /* Hero Section */
  .mt-hero {
    background: var(--gradient-mt);
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

  .country-hero-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
  }

  .country-hero-content h1 {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    line-height: 1.2;
  }

  .country-hero-content h1 i {
    margin-right: 15px;
  }

  .country-hero-content p {
    font-size: 1.2rem;
    margin-bottom: 2rem;
    opacity: 0.9;
    font-weight: 400;
  }

  .hero-stats {
    display: flex;
    justify-content: center;
    gap: 2.5rem;
    margin: 2.5rem 0;
  }

  .stat {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .stat-number {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--mt-white);
    line-height: 1;
    margin-bottom: 0.5rem;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
  }

  .stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
    font-weight: 500;
  }

  .hero-cta {
    margin-top: 1.5rem;
  }

  .cta-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    color: white;
    padding: 12px 25px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid rgba(255, 255, 255, 0.3);
  }

  .cta-button:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-3px);
  }

  .mt-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 20% 30%, rgba(207, 20, 43, 0.1) 2px, transparent 2px),
      radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
      radial-gradient(circle at 40% 80%, rgba(207, 20, 43, 0.1) 3px, transparent 3px);
    background-size: 100px 100px;
    z-index: 1;
    animation: patternMove 20s linear infinite;
  }

  @keyframes patternMove {
    0% { background-position: 0 0; }
    100% { background-position: 100px 100px; }
  }

  .mt-flag-element {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 90px;
    height: 45px;
    background: 
      linear-gradient(0deg, 
        var(--mt-white) 0% 50%, 
        var(--mt-red) 50% 100%);
    z-index: 3;
    opacity: 0.9;
    border-radius: 6px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
    position: relative;
  }

  .mt-flag-element::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 22px;
    height: 22px;
    background-color: var(--mt-white);
    border-top-left-radius: 6px;
  }

  .mt-flag-element::after {
    content: '✠';
    position: absolute;
    top: 4px;
    left: 4px;
    color: var(--mt-red);
    font-size: 12px;
    font-weight: bold;
    z-index: 4;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
  }

  .hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.2);
    z-index: 2;
  }

  /* Section Slider MODIFIÉE avec deux images par vue */
  .mt-slider-section {
    background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
    padding: 60px 20px;
    margin: 0;
    position: relative;
    width: 100%;
  }

  .mt-slider-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--gradient-mt);
  }

  .slider-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0;
  }

  .slider-container h2 {
    text-align: center;
    font-size: 2.2rem;
    margin-bottom: 15px;
    color: var(--mt-red);
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
    box-shadow: var(--shadow-mt);
  }

  .slider-track {
    display: flex;
    transition: transform 0.5s ease-in-out;
  }

  /* Vue contenant deux slides */
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
    height: 350px;
    overflow: hidden;
    border-radius: 12px;
    box-shadow: var(--shadow-mt);
    transition: var(--transition);
  }

  .slide:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-mt-hover);
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
    width: 100%;
    height: 100%;
    object-fit: cover;
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
    background: linear-gradient(transparent, rgba(207, 20, 43, 0.8));
    color: white;
    padding: 25px 20px 20px;
    transform: translateY(0);
    transition: var(--transition);
  }

  .slide:hover .slide-content {
    background: linear-gradient(transparent, rgba(207, 20, 43, 0.95));
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
    box-shadow: var(--shadow-mt);
    color: var(--mt-red);
  }

  .slider-btn:hover {
    background: var(--mt-red);
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

  /* Services Section */
  .services-container {
    padding: 80px 0;
  }

  .services-container h2 {
    text-align: center;
    font-size: 2.5rem;
    margin-bottom: 1rem;
  }

  .services-subtitle {
    text-align: center;
    font-size: 1.2rem;
    color: #6c757d;
    margin-bottom: 4rem;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
  }

  .services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
  }

  .service-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-mt);
    transition: var(--transition);
    position: relative;
    border: 1px solid rgba(207, 20, 43, 0.1);
  }

  .service-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-mt-hover);
  }

  .card-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--mt-white);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    color: var(--mt-red);
    font-size: 1.8rem;
    box-shadow: var(--shadow-soft);
  }

  .card-image {
    position: relative;
    height: 200px;
    overflow: hidden;
  }

  .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
  }

  .service-card:hover .card-image img {
    transform: scale(1.05);
  }

  .card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
  }

  .card-flag {
    position: absolute;
    bottom: 15px;
    left: 15px;
    width: 40px;
    height: 20px;
    background: 
      linear-gradient(0deg, 
        var(--mt-white) 0% 50%, 
        var(--mt-red) 50% 100%);
    border-radius: 3px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    position: relative;
  }

  .card-flag::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 12px;
    height: 10px;
    background-color: var(--mt-white);
    border-top-left-radius: 3px;
  }

  .card-flag::after {
    content: '✠';
    position: absolute;
    top: 1px;
    left: 1px;
    color: var(--mt-red);
    font-size: 8px;
    font-weight: bold;
    z-index: 4;
  }

  .card-content {
    padding: 25px;
  }

  .card-content h3 {
    font-size: 1.6rem;
    margin-bottom: 15px;
    color: var(--mt-red);
  }

  .card-content p {
    color: #6c757d;
    margin-bottom: 20px;
    line-height: 1.6;
  }

  .service-features {
    list-style: none;
    padding: 0;
    margin-bottom: 25px;
  }

  .service-features li {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    color: #495057;
    font-size: 0.95rem;
  }

  .service-features li i {
    color: var(--mt-red);
    margin-right: 10px;
    font-size: 1.1rem;
  }

  .card-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .service-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-red);
    color: white;
    padding: 12px 25px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    gap: 8px;
  }

  .service-btn:hover {
    background: var(--gradient-mt-dark);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(207, 20, 43, 0.3);
  }

  .service-info-box {
    background: linear-gradient(135deg, var(--mt-light-red) 0%, #FDF2F4 100%);
    border-radius: var(--border-radius);
    padding: 25px;
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-top: 30px;
    box-shadow: var(--shadow-soft);
  }

  .info-icon {
    background: var(--mt-red);
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
  }

  .info-content h4 {
    color: var(--mt-red);
    margin-bottom: 10px;
    font-size: 1.3rem;
  }

  .info-content p {
    color: #5a6268;
    line-height: 1.6;
    margin: 0;
  }

  /* Responsive Design */
  @media (max-width: 992px) {
    .country-hero-content h1 {
      font-size: 2.2rem;
    }
    
    .hero-stats {
      gap: 2rem;
    }
    
    .stat-number {
      font-size: 2rem;
    }
  }

  @media (max-width: 768px) {
    .mt-hero {
      padding: 50px 0;
      min-height: auto;
    }
    
    .country-hero-content h1 {
      font-size: 2rem;
    }
    
    .country-hero-content p {
      font-size: 1.1rem;
    }
    
    .hero-stats {
      flex-direction: column;
      gap: 1.5rem;
    }
    
    .services-grid {
      grid-template-columns: 1fr;
    }
    
    .card-actions {
      flex-direction: column;
      gap: 15px;
      align-items: flex-start;
    }
    
    .service-info-box {
      flex-direction: column;
      text-align: center;
    }
    
    .mt-flag-element {
      width: 70px;
      height: 35px;
      top: 15px;
      right: 15px;
    }
    
    .slider-container h2 {
      font-size: 1.8rem;
    }
    
    /* Sur mobile, revenir à une seule image par vue */
    .slide-duo {
      grid-template-columns: 1fr;
      gap: 15px;
    }
    
    .slide {
      height: 300px;
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
      width: 40px;
      height: 40px;
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
      font-size: 1.8rem;
    }
    
    .services-container h2 {
      font-size: 2rem;
    }
    
    .card-content {
      padding: 15px;
    }

    .card-content h3 {
      font-size: 1.2rem;
    }
    
    .slide {
      height: 250px;
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
      width: 35px;
      height: 35px;
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
  }
</style>

<div class="mt-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-umbrella-beach"></i> Malte — Tourisme & Affaires</h1>
      <p>Découvrez nos services spécialisés pour vos voyages à Malte</p>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-number" data-count="85">0</span>
          <span class="stat-label">de réussite</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="5">0</span>
          <span class="stat-label">ans d'expérience</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="1000">0</span>
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
  <div class="mt-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="mt-flag-element"></div>
</div>

<!-- Section Slider MODIFIÉE avec deux images par vue -->
<div class="mt-slider-section">
  <div class="slider-container">
    <h2>Découvrez <span class="text-mt">Malte</span></h2>
    <p class="slider-subtitle">Un archipel méditerranéen au riche patrimoine historique</p>
    
    <div class="slider-wrapper">
      <div class="slider-track">
        <!-- Vue 1 -->
        <div class="slide-view">
          <div class="slide-duo">
            <div class="slide">
              <div class="image-container">
                <img src="https://images.unsplash.com/photo-1594736797933-d0d69bc6f0a7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80" alt="La Valette, Malte">
              </div>
              <div class="slide-content">
                <h3>La Valette</h3>
                <p>Capitale historique classée au patrimoine mondial de l'UNESCO</p>
              </div>
            </div>
            <div class="slide">
              <div class="image-container">
                <img src="https://images.unsplash.com/photo-1549918864-6f6d2b9a4b13?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Plages de Malte">
              </div>
              <div class="slide-content">
                <h3>Plages Paradisiaques</h3>
                <p>Criques cristallines et eaux turquoise de l'archipel maltais</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Vue 2 -->
        <div class="slide-view">
          <div class="slide-duo">
            <div class="slide">
              <div class="image-container">
                <img src="https://images.unsplash.com/photo-1601918774946-25832a4be0d6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80" alt="Temples mégalithiques de Malte">
              </div>
              <div class="slide-content">
                <h3>Patrimoine Archéologique</h3>
                <p>Temples mégalithiques parmi les plus anciens du monde</p>
              </div>
            </div>
            <div class="slide">
              <div class="image-container">
                <img src="https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2068&q=80" alt="Vie nocturne à Malte">
              </div>
              <div class="slide-content">
                <h3>Vie Nocturne</h3>
                <p>Animation et divertissement dans les villes maltaises</p>
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
  <div class="container">
    <h2>Nos services pour <span class="text-mt">Malte</span></h2>
    <p class="services-subtitle">Solutions complètes pour tous vos projets à Malte</p>
    
    <div class="services-grid">
      <!-- Service Tourisme & Affaires -->
      <div class="service-card mt-card tourisme-affaires">
        <div class="card-icon">
          <i class="fa-solid fa-globe-europe"></i>
        </div>
        <div class="card-image">
          <img src="../images/voy2.jpg" alt="Tourisme et Affaires à Malte">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Tourisme & Affaires</h3>
          <p>Voyages touristiques et professionnels à Malte</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Visa Schengen Malte</li>
            <li><i class="fas fa-check-circle"></i> Organisation réunions d'affaires</li>
            <li><i class="fas fa-check-circle"></i> Circuits touristiques personnalisés</li>
            <li><i class="fas fa-check-circle"></i> Suivi complet</li>
          </ul>
          <div class="card-actions">
            <a class="service-btn" href="../../tourisme/index.php">
              <span>Découvrir</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Service Travail -->
      <div class="service-card mt-card travail">
        <div class="card-icon">
          <i class="fa-solid fa-briefcase"></i>
        </div>
        <div class="card-image">
          <img src="../images/travail.jpg" alt="Travail à Malte">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Travail</h3>
          <p>Opportunités professionnelles et permis de travail</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Permis de travail Malte</li>
            <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
            <li><i class="fas fa-check-circle"></i> CV et lettres de motivation</li>
            <li><i class="fas fa-check-circle"></i> Préparation entretiens</li>
          </ul>
          <div class="card-actions">
            <a class="service-btn" href="../travail/index.php">
              <span>Découvrir</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Service Prise de Rendez-vous -->
      <div class="service-card mt-card rendezvous">
        <div class="card-icon">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="card-image">
          <img src="../images/visa.jpg" alt="Prise de rendez-vous pour Malte">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Prise de Rendez-vous</h3>
          <p>Service professionnel de prise de rendez-vous</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Prise de RDV </li>
            <li><i class="fas fa-check-circle"></i> Créneaux urgents</li>
            <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
          </ul>
          <div class="card-actions">
            <a class="service-btn" href="../../rendez_vous.php">
              <span>Découvrir</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="service-info-box">
      <div class="info-icon">
        <i class="fas fa-info-circle"></i>
      </div>
      <div class="info-content">
        <h4>Important : Visa Schengen Malte</h4>
        <p>Malte fait partie de l'espace Schengen. Notre expertise couvre l'ensemble des procédures consulaires maltaises pour tous types de visas (tourisme, affaires, études, travail).</p>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Animation des compteurs
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

    // Animation au défilement
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
        }
      });
    }, observerOptions);

    document.querySelectorAll('.service-card').forEach(el => {
      observer.observe(el);
    });
  });
</script>