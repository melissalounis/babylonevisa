<?php
require_once __DIR__ . '/../../config.php';
$page_title = "États-Unis — Tourisme & Affaires";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root {
    --usa-blue: #3C3B6E;
    --usa-red: #B22234;
    --usa-white: #FFFFFF;
    --usa-light-blue: #5D76A9;
    --usa-dark-blue: #2A2D5A;
    --usa-light-red: #D32D41;
    --usa-green: #228B22;
    --usa-light-green: #32CD32;
    --usa-gold: #FFD700;
    --usa-light: #F7FAFC;
    --usa-dark: #2D3748;
    
    --gradient-usa: linear-gradient(135deg, var(--usa-blue) 0%, var(--usa-white) 50%, var(--usa-red) 100%);
    --gradient-usa-dark: linear-gradient(135deg, var(--usa-dark-blue) 0%, var(--usa-white) 50%, var(--usa-light-red) 100%);
    --gradient-blue: linear-gradient(135deg, var(--usa-blue) 0%, var(--usa-dark-blue) 100%);
    --gradient-red: linear-gradient(135deg, var(--usa-red) 0%, var(--usa-light-red) 100%);
    --gradient-green: linear-gradient(135deg, var(--usa-green) 0%, var(--usa-light-green) 100%);
    --gradient-gold: linear-gradient(135deg, var(--usa-gold) 0%, #FFED4E 100%);
    --gradient-patriotic: linear-gradient(135deg, var(--usa-blue) 0%, var(--usa-white) 50%, var(--usa-red) 100%);
    
    --shadow-usa: 0 10px 30px rgba(60, 59, 110, 0.15);
    --shadow-usa-hover: 0 20px 50px rgba(60, 59, 110, 0.25);
    --shadow-green: 0 10px 30px rgba(34, 139, 34, 0.15);
    --shadow-green-hover: 0 20px 50px rgba(34, 139, 34, 0.25);
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
    color: var(--usa-dark);
    line-height: 1.6;
    background-color: var(--usa-light);
  }

  h1, h2, h3, h4, h5 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .text-usa {
    background: var(--gradient-patriotic);
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
  .usa-hero {
    background: var(--gradient-usa);
    color: white;
    padding: 80px 0 60px;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 60vh;
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
    margin: 2rem 0;
  }

  .stat {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .stat-number {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--usa-white);
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
    font-size: 0.95rem;
  }

  .cta-button:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-3px);
  }

  .usa-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 20% 30%, rgba(60, 59, 110, 0.1) 2px, transparent 2px),
      radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
      radial-gradient(circle at 40% 80%, rgba(178, 34, 52, 0.1) 3px, transparent 3px);
    background-size: 80px 80px;
    z-index: 1;
    animation: patternMove 20s linear infinite;
  }

  @keyframes patternMove {
    0% { background-position: 0 0; }
    100% { background-position: 80px 80px; }
  }

  .usa-flag-element {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 90px;
    height: 55px;
    background: 
      linear-gradient(0deg, 
        var(--usa-red) 0% 40%, 
        var(--usa-white) 40% 45%,
        var(--usa-red) 45% 55%,
        var(--usa-white) 55% 60%,
        var(--usa-red) 60% 70%,
        var(--usa-white) 70% 75%,
        var(--usa-red) 75% 85%,
        var(--usa-white) 85% 90%,
        var(--usa-red) 90% 100%);
    z-index: 3;
    opacity: 0.9;
    border-radius: 6px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
  }

  .usa-flag-element::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 40%;
    height: 50%;
    background-color: var(--usa-blue);
    border-top-left-radius: 6px;
  }

  .usa-flag-element::after {
    content: '★';
    position: absolute;
    top: 4px;
    left: 4px;
    color: white;
    font-size: 8px;
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

  /* Services Section */
  .services-container {
    padding: 80px 0;
  }

  .services-container h2 {
    text-align: center;
    font-size: 2.4rem;
    margin-bottom: 1rem;
  }

  .services-subtitle {
    text-align: center;
    font-size: 1.1rem;
    color: #6c757d;
    margin-bottom: 3rem;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
  }

  .services-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 25px;
    margin-bottom: 40px;
  }

  .service-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-usa);
    transition: var(--transition);
    position: relative;
    border: 1px solid rgba(60, 59, 110, 0.1);
    height: 100%;
  }

  .service-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-usa-hover);
  }

  .usa-card.green-card:hover {
    box-shadow: var(--shadow-green-hover);
  }

  .card-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--usa-white);
    width: 55px;
    height: 55px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    color: var(--usa-blue);
    font-size: 1.6rem;
    box-shadow: var(--shadow-soft);
  }

  .usa-card.green-card .card-icon {
    color: var(--usa-green);
  }

  .usa-card.rendezvous .card-icon {
    color: var(--usa-light-blue);
  }

  .card-image {
    position: relative;
    height: 180px;
    overflow: hidden;
  }

  .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: var(--transition);
  }

  .service-card:hover .card-image img {
    transform: scale(1.08);
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
    height: 22px;
    background: 
      linear-gradient(0deg, 
        var(--usa-red) 0% 40%, 
        var(--usa-white) 40% 45%,
        var(--usa-red) 45% 55%,
        var(--usa-white) 55% 60%,
        var(--usa-red) 60% 70%,
        var(--usa-white) 70% 75%,
        var(--usa-red) 75% 85%,
        var(--usa-white) 85% 90%,
        var(--usa-red) 90% 100%);
    border-radius: 3px;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15);
  }

  .card-flag::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 40%;
    height: 50%;
    background-color: var(--usa-blue);
    border-top-left-radius: 3px;
  }

  .card-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    height: calc(100% - 180px);
  }

  .card-content h3 {
    font-size: 1.4rem;
    margin-bottom: 12px;
    color: var(--usa-dark-blue);
  }

  .usa-card.green-card .card-content h3 {
    color: var(--usa-green);
  }

  .usa-card.rendezvous .card-content h3 {
    color: var(--usa-light-blue);
  }

  .card-content p {
    color: #6c757d;
    margin-bottom: 18px;
    line-height: 1.5;
    flex-grow: 1;
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
    color: var(--usa-blue);
    margin-right: 8px;
    font-size: 0.9rem;
  }

  .usa-card.green-card .service-features li i {
    color: var(--usa-green);
  }

  .usa-card.rendezvous .service-features li i {
    color: var(--usa-light-blue);
  }

  .card-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: auto;
  }

  .service-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--gradient-blue);
    color: white;
    padding: 10px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    gap: 6px;
    font-size: 0.9rem;
  }

  .usa-card.green-card .service-btn {
    background: var(--gradient-green);
  }

  .usa-card.rendezvous .service-btn {
    background: var(--gradient-blue);
  }

  .service-btn:hover {
    background: var(--gradient-usa-dark);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(60, 59, 110, 0.3);
  }

  .usa-card.green-card .service-btn:hover {
    background: var(--gradient-green);
    box-shadow: 0 5px 15px rgba(34, 139, 34, 0.3);
  }

  .usa-card.rendezvous .service-btn:hover {
    background: var(--gradient-usa-dark);
    box-shadow: 0 5px 15px rgba(60, 59, 110, 0.3);
  }

  .service-link {
    color: var(--usa-blue);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    font-size: 0.85rem;
  }

  .service-link:hover {
    color: var(--usa-dark-blue);
    text-decoration: underline;
  }

  /* Service-specific styles */
  .usa-card.tourisme-affaires {
    border-top: 4px solid var(--usa-blue);
  }

  .usa-card.green-card {
    border-top: 4px solid var(--usa-green);
    box-shadow: var(--shadow-green);
  }

  .usa-card.rendezvous {
    border-top: 4px solid var(--usa-light-blue);
  }

  .green-card-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: var(--gradient-gold);
    color: var(--usa-dark-blue);
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    font-weight: 700;
    z-index: 3;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  }

  /* Service Info Box */
  .service-info-box {
    background: linear-gradient(135deg, #f8f9ff 0%, #e8f2ff 100%);
    border-left: 4px solid var(--usa-blue);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-top: 40px;
  }

  .info-icon {
    font-size: 1.5rem;
    color: var(--usa-blue);
  }

  .info-content h4 {
    color: var(--usa-dark-blue);
    margin-bottom: 8px;
    font-size: 1.1rem;
  }

  .info-content p {
    color: #666;
    line-height: 1.5;
    font-size: 0.9rem;
  }

  /* Responsive Design */
  @media (max-width: 1024px) {
    .country-hero-content h1 {
      font-size: 2.2rem;
    }
    
    .hero-stats {
      gap: 2rem;
    }
    
    .stat-number {
      font-size: 2rem;
    }

    .services-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
  }

  @media (max-width: 768px) {
    .usa-hero {
      padding: 60px 0 40px;
      min-height: 50vh;
    }
    
    .country-hero-content h1 {
      font-size: 1.8rem;
    }
    
    .country-hero-content p {
      font-size: 1rem;
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
      gap: 12px;
      align-items: flex-start;
    }
    
    .usa-flag-element {
      width: 70px;
      height: 45px;
      top: 15px;
      right: 15px;
    }
  }

  @media (max-width: 480px) {
    .country-hero-content h1 {
      font-size: 1.6rem;
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
  }
</style>

<div class="usa-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-flag-usa"></i> États-Unis — Services Complets</h1>
      <p>Découvrez nos services spécialisés pour tous vos projets aux États-Unis</p>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-number" data-count="90">0</span>
          <span class="stat-label">de réussite</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="8">0</span>
          <span class="stat-label">ans d'expérience</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="2500">0</span>
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
  <div class="usa-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="usa-flag-element"></div>
</div>

<div class="services-container" id="services">
  <div class="container">
    <h2>Nos services pour les <span class="text-usa">États-Unis</span></h2>
    <p class="services-subtitle">Solutions complètes pour tous vos projets aux USA</p>
    
    <div class="services-grid">
      <!-- Service Tourisme & Affaires -->
      <div class="service-card usa-card tourisme-affaires">
        <div class="card-icon">
          <i class="fa-solid fa-globe-americas"></i>
        </div>
        <div class="card-image">
          <img src="../images/voy2.jpg" alt="Tourisme et Affaires aux États-Unis">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Tourisme & Affaires</h3>
          <p>Voyages touristiques et professionnels aux États-Unis avec assistance complète pour vos visas B1/B2.</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Visa B1/B2 États-Unis</li>
            <li><i class="fas fa-check-circle"></i> Réservations hôtels premium</li>
            <li><i class="fas fa-check-circle"></i> Organisation réunions d'affaires</li>
            <li><i class="fas fa-check-circle"></i> Circuits touristiques personnalisés</li>
          </ul>
          <div class="card-actions">
            <a class="service-btn" href="../../tourisme/tourisme.php">
              <span>Découvrir</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Service Green Card Lottery -->
      <div class="service-card usa-card green-card">
        <div class="green-card-badge">
          <i class="fas fa-gift"></i> LOTERIE
        </div>
        <div class="card-icon">
          <i class="fa-solid fa-id-card"></i>
        </div>
        <div class="card-image">
          <img src="../images/green card.jpg" alt="Green Card Lottery États-Unis">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Green Card Lottery</h3>
          <p>Programme de visa de diversité DV-2025 - Gagnez votre résidence permanente aux USA avec notre assistance professionnelle.</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Inscription DV-2025</li>
            <li><i class="fas fa-check-circle"></i> Assistance complète</li>
            <li><i class="fas fa-check-circle"></i> Vérification éligibilité</li>
            <li><i class="fas fa-check-circle"></i> Photos conformes</li>
            <li><i class="fas fa-check-circle"></i> Suivi des résultats</li>
          </ul>
          <div class="card-actions">
            <a class="service-btn" href="immigration/immigration.php">
              <span>Participer</span>
              <i class="fas fa-ticket-alt"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Service Prise de Rendez-vous -->
      <div class="service-card usa-card rendezvous">
        <div class="card-icon">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="card-image">
          <img src="../images/visa.jpg" alt="Prise de rendez-vous pour les États-Unis">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Prise de Rendez-vous</h3>
          <p>Service professionnel de prise de rendez-vous pour toutes vos démarches consulaires aux États-Unis.</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Prise de RDV ambassade</li>
            <li><i class="fas fa-check-circle"></i> Créneaux urgents</li>
            <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
            <li><i class="fas fa-check-circle"></i> Assistance complète</li>
          </ul>
          <div class="card-actions">
            <a class="service-btn" href="../../rendez_vous.php">
              <span>Prendre RDV</span>
              <i class="fas fa-calendar-plus"></i>
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
        <h4>Important : Types de visas États-Unis</h4>
        <p>Le visa B1/B2 couvre tourisme et affaires. La Green Card Lottery (DV-2025) offre une chance de résidence permanente. Nos experts vous accompagnent dans toutes les démarches spécifiques aux États-Unis.</p>
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