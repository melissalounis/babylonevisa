<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Royaume-Uni — Tourisme & Affaires";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root {
    --uk-blue: #012169;
    --uk-red: #C8102E;
    --uk-white: #FFFFFF;
    --uk-light-blue: #1E3A8A;
    --uk-light-red: #DC2626;
    --uk-gold: #B8860B;
    --uk-dark-blue: #0C1E5B;
    --gradient-uk: linear-gradient(135deg, var(--uk-blue) 0%, var(--uk-red) 100%);
    --gradient-uk-royal: linear-gradient(135deg, var(--uk-blue) 0%, var(--uk-red) 50%, var(--uk-white) 100%);
    --gradient-uk-light: linear-gradient(135deg, var(--uk-light-blue) 0%, var(--uk-light-red) 100%);
    --shadow-uk: 0 10px 30px rgba(1, 33, 105, 0.2);
    --shadow-uk-hover: 0 20px 50px rgba(1, 33, 105, 0.3);
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
    line-height: 1.6;
    color: #333;
    overflow-x: hidden;
    background-color: #f8f9fa;
  }

  h1, h2, h3, h4, h5, h6 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .text-uk {
    background: var(--gradient-uk);
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

  /* Hero Section COMPACTE */
  .uk-hero {
    background: var(--gradient-uk-royal);
    color: var(--uk-white);
    padding: 40px 0 30px;
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

  .country-hero-content {
    max-width: 800px;
    margin: 0 auto;
    padding: 0 20px;
  }

  .country-hero-content h1 {
    font-size: 1.8rem;
    margin-bottom: 0.8rem;
    line-height: 1.2;
    font-weight: 800;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }

  .country-hero-content h1 i {
    margin-right: 12px;
    color: var(--uk-gold);
  }

  .country-hero-content p {
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    opacity: 0.95;
    font-weight: 400;
  }

  .hero-stats {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin: 1.5rem 0;
  }

  .stat {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .stat-number {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--uk-white);
    line-height: 1;
    margin-bottom: 0.3rem;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }

  .stat-label {
    font-size: 0.8rem;
    opacity: 0.9;
    font-weight: 500;
    color: var(--uk-white);
  }

  .hero-cta {
    margin-top: 1rem;
  }

  .cta-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    color: white;
    padding: 8px 16px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid rgba(255, 255, 255, 0.3);
    font-size: 0.85rem;
  }

  .cta-button:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-2px);
  }

  .uk-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      linear-gradient(45deg, transparent 48%, rgba(255, 255, 255, 0.1) 50%, transparent 52%),
      linear-gradient(-45deg, transparent 48%, rgba(255, 255, 255, 0.1) 50%, transparent 52%);
    background-size: 80px 80px;
    z-index: 1;
    animation: patternMove 20s linear infinite;
  }

  @keyframes patternMove {
    0% { background-position: 0 0; }
    100% { background-position: 80px 80px; }
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

  .uk-flag-element {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 60px;
    height: 30px;
    background: 
      linear-gradient(90deg, 
        var(--uk-blue) 0% 40%, 
        var(--uk-white) 40% 60%, 
        var(--uk-red) 60% 100%);
    z-index: 3;
    opacity: 0.8;
    border-radius: 4px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
  }

  /* Services Container */
  .services-container {
    padding: 60px 0;
  }

  .services-container h2 {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 0.8rem;
    color: var(--uk-dark-blue);
  }

  .services-subtitle {
    text-align: center;
    font-size: 1rem;
    color: #6c757d;
    margin-bottom: 2.5rem;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
  }

  /* Services Grid amélioré - 3 cartes */
  .services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 30px;
  }

  .service-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-uk);
    transition: var(--transition);
    position: relative;
    border: 1px solid rgba(1, 33, 105, 0.1);
  }

  .service-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-uk-hover);
  }

  .card-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    background: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    color: var(--uk-blue);
    font-size: 1.5rem;
    box-shadow: var(--shadow-soft);
  }

  .card-image {
    position: relative;
    height: 160px;
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
    bottom: 12px;
    left: 12px;
    width: 35px;
    height: 18px;
    background: 
      linear-gradient(90deg, 
        var(--uk-blue) 0% 40%, 
        var(--uk-white) 40% 60%, 
        var(--uk-red) 60% 100%);
    border-radius: 2px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
  }

  .card-content {
    padding: 18px;
  }

  .card-content h3 {
    font-size: 1.2rem;
    margin-bottom: 12px;
    color: var(--uk-dark-blue);
  }

  .card-content p {
    color: #6c757d;
    margin-bottom: 15px;
    line-height: 1.5;
    font-size: 0.9rem;
  }

  .service-features {
    list-style: none;
    padding: 0;
    margin-bottom: 20px;
  }

  .service-features li {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    color: #495057;
    font-size: 0.85rem;
  }

  .service-features li i {
    color: var(--uk-blue);
    margin-right: 8px;
    font-size: 0.9rem;
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
    background: var(--gradient-uk);
    color: white;
    padding: 8px 16px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    gap: 6px;
    font-size: 0.85rem;
  }

  .service-btn:hover {
    background: var(--gradient-uk-light);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(1, 33, 105, 0.3);
  }

  .service-link {
    color: var(--uk-blue);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    font-size: 0.85rem;
  }

  .service-link:hover {
    color: var(--uk-red);
    text-decoration: underline;
  }

  .service-info-box {
    background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
    border-radius: var(--border-radius);
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-top: 25px;
    box-shadow: var(--shadow-soft);
  }

  .info-icon {
    background: var(--uk-blue);
    color: white;
    width: 45px;
    height: 45px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
  }

  .info-content h4 {
    color: var(--uk-dark-blue);
    margin-bottom: 8px;
    font-size: 1.1rem;
  }

  .info-content p {
    color: #5a6268;
    line-height: 1.5;
    margin: 0;
    font-size: 0.9rem;
  }

  /* Responsive Design */
  @media (max-width: 992px) {
    .services-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 18px;
    }
  }

  @media (max-width: 768px) {
    .uk-hero {
      padding: 30px 0 20px;
      min-height: 35vh;
    }
    
    .country-hero-content h1 {
      font-size: 1.5rem;
    }
    
    .country-hero-content p {
      font-size: 0.85rem;
    }
    
    .hero-stats {
      flex-direction: column;
      gap: 1rem;
    }
    
    .services-grid {
      grid-template-columns: 1fr;
    }
    
    .card-actions {
      flex-direction: column;
      gap: 12px;
      align-items: flex-start;
    }
    
    .service-info-box {
      flex-direction: column;
      text-align: center;
    }
    
    .uk-flag-element {
      width: 50px;
      height: 25px;
      top: 10px;
      right: 10px;
    }
  }

  @media (max-width: 480px) {
    .country-hero-content h1 {
      font-size: 1.3rem;
    }
    
    .services-container h2 {
      font-size: 1.6rem;
    }
    
    .card-content {
      padding: 15px;
    }

    .card-content h3 {
      font-size: 1.1rem;
    }
  }
</style>

<div class="uk-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-crown"></i> Royaume-Uni — Services</h1>
      <p>Découvrez nos services spécialisés pour tous vos projets au Royaume-Uni</p>
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
  <div class="uk-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="uk-flag-element"></div>
</div>

<div class="services-container" id="services">
  <h2>Nos services pour le <span class="text-uk">Royaume-Uni</span></h2>
  <p class="services-subtitle">Des solutions complètes pour tous vos projets au Royaume-Uni</p>
  
  <div class="services-grid">
    <!-- Service Rendez-vous -->
    <div class="service-card uk-card">
      <div class="card-icon">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="card-image">
        <img src="../images/visa.jpg" alt="Prise de Rendez-vous Royaume-Uni">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Prise de Rendez-vous</h3>
        <p>Prenez rendez-vous pour toutes vos démarches consulaires au Royaume-Uni</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Rendez-vous consulaires</li>
          <li><i class="fas fa-check-circle"></i> Centres de biométrie</li>
          <li><i class="fas fa-check-circle"></i> Assistance complète</li>
          <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn" href="/babylone/rendez_vous.php">
            <span>Prendre RDV</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Service Tourisme & Affaires -->
    <div class="service-card uk-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/voy2.jpg" alt="Tourisme au Royaume-Uni">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & Affaires</h3>
        <p>Visa visiteur standard, voyages d'affaires, réservations et itinéraires personnalisés</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa touristique UK</li>
          <li><i class="fas fa-check-circle"></i> Visa visite familiale UK</li>
          <li><i class="fas fa-check-circle"></i> Visa affaire UK</li>
          <li><i class="fas fa-check-circle"></i> Assistance 24/7</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn" href="../../tourisme/tourisme.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Service Travail -->
    <div class="service-card uk-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail au Royaume-Uni">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Opportunités professionnelles et permis de travail au Royaume-Uni</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Permis de travail UK</li>
          <li><i class="fas fa-check-circle"></i> Visa travail longue durée</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
          <li><i class="fas fa-check-circle"></i> Accompagnement personnalisé</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn" href="/babylone/public/travail/travail.php">
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
      <h4>Important : Types de visas Royaume-Uni</h4>
      <p>Le visa visiteur standard couvre tourisme, affaires et études de courte durée. Pour des séjours plus longs ou d'autres activités, des visas spécifiques sont requis (travail, études longues, regroupement familial).</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

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