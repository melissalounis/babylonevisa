<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Pologne — Tourisme & Affaires";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root {
    --pl-red: #DC143C;
    --pl-white: #FFFFFF;
    --pl-dark-red: #B22234;
    --pl-light-red: #F8E8EA;
    --pl-silver: #C0C0C0;
    --pl-dark-silver: #A9A9A9;
    --pl-light: #F7FAFC;
    --pl-dark: #2D3748;
    
    --gradient-pl: linear-gradient(135deg, var(--pl-white) 0%, var(--pl-white) 50%, var(--pl-red) 100%);
    --gradient-pl-dark: linear-gradient(135deg, var(--pl-white) 0%, var(--pl-white) 50%, var(--pl-dark-red) 100%);
    --gradient-red: linear-gradient(135deg, var(--pl-red) 0%, var(--pl-dark-red) 100%);
    --gradient-white-red: linear-gradient(135deg, var(--pl-white) 0%, var(--pl-red) 100%);
    --gradient-polish: linear-gradient(135deg, var(--pl-white) 0%, var(--pl-red) 100%);
    
    --shadow-pl: 0 10px 30px rgba(220, 20, 60, 0.15);
    --shadow-pl-hover: 0 20px 50px rgba(220, 20, 60, 0.25);
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
    color: var(--pl-dark);
    line-height: 1.6;
    background-color: var(--pl-light);
    overflow-x: hidden;
  }

  h1, h2, h3, h4, h5 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .text-pl {
    background: var(--gradient-polish);
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

  /* Hero Section - Plus petite */
  .pl-hero {
    background: var(--gradient-pl);
    color: var(--pl-dark);
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
    color: var(--pl-red);
  }

  .country-hero-content h1 i {
    margin-right: 12px;
  }

  .country-hero-content p {
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    color: var(--pl-dark);
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
    color: var(--pl-red);
    line-height: 1;
    margin-bottom: 0.3rem;
  }

  .stat-label {
    font-size: 0.8rem;
    color: var(--pl-dark);
    font-weight: 500;
  }

  .hero-cta {
    margin-top: 1rem;
  }

  .cta-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--pl-red);
    color: white;
    padding: 8px 16px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid var(--pl-red);
    font-size: 0.85rem;
  }

  .cta-button:hover {
    background: var(--pl-dark-red);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(220, 20, 60, 0.3);
  }

  .pl-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 20% 30%, rgba(220, 20, 60, 0.1) 2px, transparent 2px),
      radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.2) 1px, transparent 1px),
      radial-gradient(circle at 40% 80%, rgba(220, 20, 60, 0.1) 3px, transparent 3px);
    background-size: 80px 80px;
    z-index: 1;
    animation: patternMove 20s linear infinite;
  }

  @keyframes patternMove {
    0% { background-position: 0 0; }
    100% { background-position: 80px 80px; }
  }

  .pl-flag-element {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 60px;
    height: 30px;
    background: 
      linear-gradient(90deg, 
        var(--pl-white) 0% 50%, 
        var(--pl-red) 50% 100%);
    z-index: 3;
    opacity: 0.9;
    border-radius: 4px;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
  }

  @keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
  }

  .hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.3);
    z-index: 2;
  }

  /* Services Section */
  .services-container {
    padding: 60px 0;
  }

  .services-container h2 {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 0.8rem;
    color: var(--pl-red);
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

  .services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .service-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-pl);
    transition: var(--transition);
    position: relative;
    border: 1px solid rgba(220, 20, 60, 0.1);
  }

  .service-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-pl-hover);
  }

  .card-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--pl-white);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    color: var(--pl-red);
    font-size: 1.5rem;
    box-shadow: var(--shadow-soft);
    border: 2px solid var(--pl-red);
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
      linear-gradient(0deg, 
        var(--pl-white) 0% 50%, 
        var(--pl-red) 50% 100%);
    border-radius: 2px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
  }

  .card-content {
    padding: 18px;
  }

  .card-content h3 {
    font-size: 1.2rem;
    margin-bottom: 12px;
    color: var(--pl-red);
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
    color: var(--pl-red);
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
    background: var(--gradient-red);
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
    background: var(--gradient-pl-dark);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(220, 20, 60, 0.3);
  }

  .service-link {
    color: var(--pl-red);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    font-size: 0.85rem;
  }

  .service-link:hover {
    color: var(--pl-dark-red);
    text-decoration: underline;
  }

  /* Service-specific styles */
  .pl-card.tourisme-affaires {
    border-top: 4px solid var(--pl-red);
  }

  .pl-card.travail {
    border-top: 4px solid var(--pl-silver);
  }

  .pl-card.rendezvous {
    border-top: 4px solid var(--pl-dark-red);
  }

  .service-info-box {
    background: linear-gradient(135deg, var(--pl-light-red) 0%, #FDF2F4 100%);
    border-radius: var(--border-radius);
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-top: 25px;
    box-shadow: var(--shadow-soft);
    border-left: 4px solid var(--pl-red);
  }

  .info-icon {
    background: var(--pl-red);
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
    color: var(--pl-red);
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
  @media (max-width: 768px) {
    .pl-hero {
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
    
    .pl-flag-element {
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

<div class="pl-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-landmark"></i> Pologne — Tourisme & Affaires</h1>
      <p>Découvrez nos services spécialisés pour vos voyages en Pologne</p>
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
  <div class="pl-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="pl-flag-element"></div>
</div>

<div class="services-container" id="services">
  <div class="container">
    <h2>Nos services pour la <span class="text-pl">Pologne</span></h2>
    <p class="services-subtitle">Des solutions complètes pour tous vos projets en Pologne</p>
    
    <div class="services-grid">
      <!-- Service Tourisme & Affaires -->
      <div class="service-card pl-card tourisme-affaires">
        <div class="card-icon">
          <i class="fa-solid fa-globe-europe"></i>
        </div>
        <div class="card-image">
          <img src="../images/voy2.jpg" alt="Tourisme et Affaires en Pologne">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Tourisme & Affaires</h3>
          <p>Voyages touristiques et professionnels en Pologne</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Visa Schengen Pologne</li>
            <li><i class="fas fa-check-circle"></i> Réservations hôtels et locations</li>
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

      <!-- Service Travail -->
      <div class="service-card pl-card travail">
        <div class="card-icon">
          <i class="fa-solid fa-briefcase"></i>
        </div>
        <div class="card-image">
          <img src="../images/travail.jpg" alt="Travail en Pologne">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Travail</h3>
          <p>Opportunités professionnelles et permis de travail</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Permis de travail Pologne</li>
            <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
            <li><i class="fas fa-check-circle"></i> CV et lettres de motivation</li>
            <li><i class="fas fa-check-circle"></i> Préparation entretiens</li>
          </ul>
          <div class="card-actions">
            <a class="service-btn" href="../travail/travail.php">
              <span>Découvrir</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Service Prise de Rendez-vous -->
      <div class="service-card pl-card rendezvous">
        <div class="card-icon">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="card-image">
          <img src="../images/visa.jpg" alt="Prise de rendez-vous pour la Pologne">
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
        <h4>Information importante sur la Pologne</h4>
        <p>La Pologne, pays dynamique au cœur de l'Europe, offre d'excellentes opportunités économiques et une qualité de vie remarquable. Membre de l'UE et de l'espace Schengen, la Pologne connaît une croissance économique soutenue. Nous vous accompagnons dans toutes vos démarches administratives pour votre séjour ou installation en Pologne.</p>
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