<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Suisse — Services Complets";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root {
    --ch-red: #FF0000;
    --ch-white: #FFFFFF;
    --ch-dark-red: #D70000;
    --ch-light-red: #FFF5F5;
    --ch-dark: #2D3748;
    --ch-light: #F7FAFC;
    --ch-purple: #8B5CF6;
    --ch-dark-purple: #7C3AED;
    --ch-gold: #FFD700;
    
    --gradient-swiss: linear-gradient(135deg, var(--ch-red) 0%, var(--ch-white) 50%, var(--ch-red) 100%);
    --gradient-swiss-dark: linear-gradient(135deg, var(--ch-dark-red) 0%, var(--ch-white) 50%, var(--ch-dark-red) 100%);
    --gradient-red: linear-gradient(135deg, var(--ch-red) 0%, var(--ch-dark-red) 100%);
    --gradient-purple: linear-gradient(135deg, var(--ch-purple) 0%, var(--ch-dark-purple) 100%);
    
    --shadow-swiss: 0 10px 30px rgba(255, 0, 0, 0.15);
    --shadow-swiss-hover: 0 20px 50px rgba(255, 0, 0, 0.25);
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
    color: var(--ch-dark);
    line-height: 1.6;
    background-color: var(--ch-light);
  }

  h1, h2, h3, h4, h5 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .text-swiss {
    background: var(--gradient-swiss);
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

  /* Hero Section RÉDUITE */
  .swiss-hero {
    background: var(--gradient-swiss);
    color: white;
    padding: 50px 0 30px;
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
  }

  .country-hero-content h1 i {
    margin-right: 12px;
  }

  .country-hero-content p {
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
    opacity: 0.9;
    font-weight: 400;
  }

  .hero-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin: 1.5rem 0;
  }

  .stat {
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  .stat-number {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--ch-white);
    line-height: 1;
    margin-bottom: 0.3rem;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
  }

  .stat-label {
    font-size: 0.8rem;
    opacity: 0.9;
    font-weight: 500;
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
    padding: 10px 20px;
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

  .swiss-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 20% 30%, rgba(255, 0, 0, 0.1) 2px, transparent 2px),
      radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
      radial-gradient(circle at 40% 80%, rgba(255, 0, 0, 0.1) 3px, transparent 3px);
    background-size: 80px 80px;
    z-index: 1;
    animation: patternMove 20s linear infinite;
  }

  @keyframes patternMove {
    0% { background-position: 0 0; }
    100% { background-position: 80px 80px; }
  }

  .swiss-flag-element {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 60px;
    height: 60px;
    background: var(--ch-red);
    border-radius: 50%;
    z-index: 3;
    opacity: 0.9;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .swiss-flag-element::before {
    content: "+";
    color: white;
    font-size: 1.5rem;
    font-weight: bold;
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
    background: rgba(0, 0, 0, 0.2);
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
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 30px;
  }

  .service-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-swiss);
    transition: var(--transition);
    position: relative;
    border: 1px solid rgba(255, 0, 0, 0.1);
    height: 100%;
  }

  .service-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-swiss-hover);
  }

  .card-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    background: var(--ch-white);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    color: var(--ch-red);
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
    height: 35px;
    background: var(--ch-red);
    border-radius: 50%;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .card-flag::before {
    content: "+";
    color: white;
    font-size: 1rem;
    font-weight: bold;
  }

  .card-content {
    padding: 18px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
  }

  .card-content h3 {
    font-size: 1.2rem;
    margin-bottom: 10px;
    color: var(--ch-dark-red);
    line-height: 1.3;
  }

  .card-content p {
    color: #6c757d;
    margin-bottom: 12px;
    line-height: 1.5;
    font-size: 0.9rem;
    flex-grow: 1;
  }

  .service-features {
    list-style: none;
    padding: 0;
    margin-bottom: 15px;
  }

  .service-features li {
    display: flex;
    align-items: center;
    margin-bottom: 6px;
    color: #495057;
    font-size: 0.85rem;
  }

  .service-features li i {
    color: var(--ch-red);
    margin-right: 6px;
    font-size: 0.9rem;
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
    background: var(--gradient-red);
    color: white;
    padding: 8px 16px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: none;
    cursor: pointer;
    gap: 5px;
    font-size: 0.85rem;
  }

  .service-btn:hover {
    background: var(--gradient-swiss-dark);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 0, 0, 0.3);
  }

  .service-link {
    color: var(--ch-red);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    font-size: 0.85rem;
  }

  .service-link:hover {
    color: var(--ch-dark-red);
    text-decoration: underline;
  }

  /* Service-specific styles */
  .swiss-card.etudes {
    border-top: 4px solid var(--ch-gold);
  }

  .swiss-card.tourisme-affaires {
    border-top: 4px solid var(--ch-red);
  }

  .swiss-card.travail {
    border-top: 4px solid #28a745;
  }

  .swiss-card.rendezvous {
    border-top: 4px solid var(--ch-purple);
  }

  .service-info-box {
    background: linear-gradient(135deg, var(--ch-light-red) 0%, #f0f8f0 100%);
    border-radius: var(--border-radius);
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-top: 25px;
    box-shadow: var(--shadow-soft);
  }

  .info-icon {
    background: var(--ch-red);
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
    color: var(--ch-dark-red);
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
  @media (max-width: 1200px) {
    .services-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
  }

  @media (max-width: 768px) {
    .swiss-hero {
      padding: 40px 0 25px;
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
    
    .swiss-flag-element {
      width: 50px;
      height: 50px;
      top: 10px;
      right: 10px;
    }
    
    .swiss-flag-element::before {
      font-size: 1.2rem;
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
  }
</style>

<div class="swiss-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-mountain"></i> Suisse — Services Complets</h1>
      <p>Découvrez nos services spécialisés pour tous vos projets en Suisse</p>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-number" data-count="96">0</span>
          <span class="stat-label">de réussite</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="12">0</span>
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
  <div class="swiss-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="swiss-flag-element"></div>
</div>

<div class="services-container" id="services">
  <div class="container">
    <h2>Nos services pour la <span class="text-swiss">Suisse</span></h2>
    <p class="services-subtitle">Des solutions complètes pour tous vos projets en Suisse</p>
    
    <div class="services-grid">
      <!-- Service Études -->
      <div class="service-card swiss-card etudes">
        <div class="card-icon">
          <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div class="card-image">
          <img src="../images/etudiant.avif" alt="Études en Suisse">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Études</h3>
          <p>Accédez aux établissements d'enseignement suisses avec notre accompagnement complet</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Visa étudiant Schengen</li>
            <li><i class="fas fa-check-circle"></i> Inscriptions universitaires</li>
            <li><i class="fas fa-check-circle"></i> Hébergement étudiant</li>
            <li><i class="fas fa-check-circle"></i> Orientation académique</li>
          </ul>
          <div class="card-actions">
            <a class="service-btn" href="etudes/etude.php">
              <span>Découvrir</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </div>
      </div>

      <!-- Service Tourisme & Affaires (fusionné) -->
      <div class="service-card swiss-card tourisme-affaires">
        <div class="card-icon">
          <i class="fa-solid fa-suitcase-rolling"></i>
        </div>
        <div class="card-image">
          <img src="../images/voy2.jpg" alt="Tourisme et Affaires en Suisse">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Tourisme & Affaires</h3>
          <p>Services complets pour vos voyages touristiques et professionnels en Suisse</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Visa Schengen tourisme/affaires</li>
            <li><i class="fas fa-check-circle"></i> Réservations hôtel & vols</li>
            <li><i class="fas fa-check-circle"></i> Organisation de réunions</li>
            <li><i class="fas fa-check-circle"></i> Guides & itinéraires</li>
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
      <div class="service-card swiss-card travail">
        <div class="card-icon">
          <i class="fa-solid fa-briefcase"></i>
        </div>
        <div class="card-image">
          <img src="../images/travail.jpg" alt="Travail en Suisse">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Travail</h3>
          <p>Opportunités d'emploi et démarches pour travailler légalement en Suisse</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Visa travail Schengen</li>
            <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
            <li><i class="fas fa-check-circle"></i> Contrats de travail</li>
            <li><i class="fas fa-check-circle"></i> Permis de séjour</li>
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
      <div class="service-card swiss-card rendezvous">
        <div class="card-icon">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="card-image">
          <img src="../images/visa.jpg" alt="Prise de rendez-vous pour la Suisse">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Demander Rendez-vous</h3>
          <p>Service professionnel de prise de rendez-vous pour vos démarches en Suisse</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Réservation rapide de créneaux</li>
            <li><i class="fas fa-check-circle"></i> Alertes pour les annulations</li>
            <li><i class="fas fa-check-circle"></i> Assistance pour le remplissage</li>
            <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
          </ul>
          <div class="card-actions">
            <a class="service-btn" href="../../rendez_vous.php">
              <span>Prendre RDV</span>
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
        <h4>Important : Visa Schengen</h4>
        <p>Le visa suisse vous donne accès à tout l'espace Schengen. Valable pour études, tourisme, affaires, travail et séjours culturels. Nos experts vous accompagnent dans toutes les démarches administratives spécifiques à la Suisse.</p>
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