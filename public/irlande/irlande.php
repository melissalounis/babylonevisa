<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Irlande — Tourisme & Affaires";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
  :root {
    --ie-green: #169B62;
    --ie-white: #FFFFFF;
    --ie-orange: #FF883E;
    --ie-dark-green: #0F7A4D;
    --ie-light-green: #F0FAF6;
    --ie-dark: #2D3748;
    --ie-light: #F7FAFC;
    --ie-purple: #8B5CF6;
    --ie-dark-purple: #7C3AED;
    --ie-blue: #3B82F6;
    --ie-dark-blue: #1D4ED8;
    
    --gradient-ireland: linear-gradient(135deg, var(--ie-green) 0%, var(--ie-white) 50%, var(--ie-orange) 100%);
    --gradient-ireland-dark: linear-gradient(135deg, var(--ie-dark-green) 0%, var(--ie-white) 50%, #E67A2E 100%);
    --gradient-green: linear-gradient(135deg, var(--ie-green) 0%, var(--ie-dark-green) 100%);
    --gradient-orange: linear-gradient(135deg, var(--ie-orange) 0%, #E67A2E 100%);
    --gradient-purple: linear-gradient(135deg, var(--ie-purple) 0%, var(--ie-dark-purple) 100%);
    --gradient-blue: linear-gradient(135deg, var(--ie-blue) 0%, var(--ie-dark-blue) 100%);
    
    --shadow-ireland: 0 10px 30px rgba(22, 155, 98, 0.15);
    --shadow-ireland-hover: 0 20px 50px rgba(22, 155, 98, 0.25);
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
    color: var(--ie-dark);
    line-height: 1.6;
    background-color: var(--ie-light);
    overflow-x: hidden;
  }

  h1, h2, h3, h4, h5 {
    font-family: 'Playfair Display', serif;
    font-weight: 700;
    margin-bottom: 1rem;
  }

  .text-ireland {
    background: var(--gradient-ireland);
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
  .ireland-hero {
    background: var(--gradient-ireland);
    color: white;
    padding: 40px 0;
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
    font-size: 2.2rem;
    margin-bottom: 0.8rem;
    line-height: 1.2;
  }

  .country-hero-content h1 i {
    margin-right: 15px;
  }

  .country-hero-content p {
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
    opacity: 0.9;
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
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--ie-white);
    line-height: 1;
    margin-bottom: 0.3rem;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
  }

  .stat-label {
    font-size: 0.85rem;
    opacity: 0.9;
    font-weight: 500;
  }

  .hero-cta {
    margin-top: 1rem;
  }

  .cta-button {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(10px);
    color: white;
    padding: 10px 20px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid rgba(255, 255, 255, 0.3);
    font-size: 0.9rem;
  }

  .cta-button:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-3px);
  }

  .ireland-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 20% 30%, rgba(22, 155, 98, 0.1) 2px, transparent 2px),
      radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
      radial-gradient(circle at 40% 80%, rgba(255, 136, 62, 0.1) 3px, transparent 3px);
    background-size: 100px 100px;
    z-index: 1;
    animation: patternMove 20s linear infinite;
  }

  @keyframes patternMove {
    0% { background-position: 0 0; }
    100% { background-position: 100px 100px; }
  }

  .ireland-flag-element {
    position: absolute;
    top: 15px;
    right: 15px;
    width: 70px;
    height: 35px;
    background: 
      linear-gradient(90deg, 
        var(--ie-green) 0% 33%, 
        var(--ie-white) 33% 66%, 
        var(--ie-orange) 66% 100%);
    z-index: 3;
    opacity: 0.9;
    border-radius: 6px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
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
    padding: 60px 0;
  }

  .services-container h2 {
    text-align: center;
    font-size: 2.3rem;
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
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
  }

  .service-card {
    background: white;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--shadow-ireland);
    transition: var(--transition);
    position: relative;
    border: 1px solid rgba(22, 155, 98, 0.1);
  }

  .service-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--shadow-ireland-hover);
  }

  .card-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--ie-white);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    color: var(--ie-green);
    font-size: 1.8rem;
    box-shadow: var(--shadow-soft);
  }

  .ireland-card.travail .card-icon {
    color: var(--ie-blue);
  }

  .ireland-card.rendezvous .card-icon {
    color: var(--ie-purple);
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
      linear-gradient(90deg, 
        var(--ie-green) 0% 33%, 
        var(--ie-white) 33% 66%, 
        var(--ie-orange) 66% 100%);
    border-radius: 3px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
  }

  .card-content {
    padding: 25px;
  }

  .card-content h3 {
    font-size: 1.6rem;
    margin-bottom: 15px;
    color: var(--ie-dark-green);
  }

  .ireland-card.travail .card-content h3 {
    color: var(--ie-blue);
  }

  .ireland-card.rendezvous .card-content h3 {
    color: var(--ie-purple);
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
    color: var(--ie-green);
    margin-right: 10px;
    font-size: 1.1rem;
  }

  .ireland-card.travail .service-features li i {
    color: var(--ie-blue);
  }

  .ireland-card.rendezvous .service-features li i {
    color: var(--ie-purple);
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
    background: var(--gradient-green);
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

  .ireland-card.travail .service-btn {
    background: var(--gradient-blue);
  }

  .ireland-card.rendezvous .service-btn {
    background: var(--gradient-purple);
  }

  .service-btn:hover {
    background: var(--gradient-ireland-dark);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(22, 155, 98, 0.3);
  }

  .ireland-card.travail .service-btn:hover {
    background: var(--gradient-blue);
    box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3);
  }

  .ireland-card.rendezvous .service-btn:hover {
    background: var(--gradient-purple);
    box-shadow: 0 5px 15px rgba(139, 92, 246, 0.3);
  }

  .service-link {
    color: var(--ie-green);
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
  }

  .service-link:hover {
    color: var(--ie-dark-green);
    text-decoration: underline;
  }

  /* Service-specific styles */
  .ireland-card.tourisme-affaires {
    border-top: 5px solid var(--ie-green);
  }

  .ireland-card.travail {
    border-top: 5px solid var(--ie-blue);
  }

  .ireland-card.rendezvous {
    border-top: 5px solid var(--ie-purple);
  }

  .service-info-box {
    background: linear-gradient(135deg, var(--ie-light-green) 0%, #f0f8f0 100%);
    border-radius: var(--border-radius);
    padding: 25px;
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-top: 30px;
    box-shadow: var(--shadow-soft);
  }

  .info-icon {
    background: var(--ie-green);
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
    color: var(--ie-dark-green);
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
      font-size: 2rem;
    }
    
    .hero-stats {
      gap: 1.2rem;
    }
    
    .stat-number {
      font-size: 1.6rem;
    }

    .services-grid {
      grid-template-columns: repeat(2, 1fr);
      gap: 20px;
    }
  }

  @media (max-width: 768px) {
    .ireland-hero {
      padding: 35px 0;
      min-height: auto;
    }
    
    .country-hero-content h1 {
      font-size: 1.8rem;
    }
    
    .country-hero-content p {
      font-size: 1rem;
    }
    
    .hero-stats {
      flex-direction: column;
      gap: 1.2rem;
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
    
    .ireland-flag-element {
      width: 60px;
      height: 30px;
      top: 10px;
      right: 10px;
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

<div class="ireland-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-clover"></i> Irlande — Tourisme & Affaires</h1>
      <p>Découvrez nos services spécialisés pour vos voyages en Irlande</p>
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
          <span class="stat-number" data-count="1500">0</span>
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
  <div class="ireland-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="ireland-flag-element"></div>
</div>

<div class="services-container" id="services">
  <div class="container">
    <h2>Nos services pour l'<span class="text-ireland">Irlande</span></h2>
    <p class="services-subtitle">Solutions complètes pour tous vos projets en Irlande</p>
    
    <div class="services-grid">
      <!-- Service Tourisme & Affaires -->
      <div class="service-card ireland-card tourisme-affaires">
        <div class="card-icon">
          <i class="fa-solid fa-globe-europe"></i>
        </div>
        <div class="card-image">
          <img src="../images/voy2.jpg" alt="Tourisme et Affaires en Irlande">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Tourisme & Affaires</h3>
          <p>Voyages touristiques et professionnels en Irlande</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Visa Irlande et Royaume-Uni</li>
            <li><i class="fas fa-check-circle"></i> Réservations hôtels et B&B</li>
            <li><i class="fas fa-check-circle"></i> Organisation réunions d'affaires</li>
            <li><i class="fas fa-check-circle"></i> Circuits touristiques</li>
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
      <div class="service-card ireland-card travail">
        <div class="card-icon">
          <i class="fa-solid fa-briefcase"></i>
        </div>
        <div class="card-image">
          <img src="../images/travail.jpg" alt="Travail en Irlande">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Travail</h3>
          <p>Opportunités professionnelles et permis de travail</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Permis de travail Irlande</li>
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
      <div class="service-card ireland-card rendezvous">
        <div class="card-icon">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="card-image">
          <img src="../images/visa.jpg" alt="Prise de rendez-vous pour l'Irlande">
          <div class="card-overlay"></div>
          <div class="card-flag"></div>
        </div>
        <div class="card-content">
          <h3>Prise de Rendez-vous</h3>
          <p>Service professionnel de prise de rendez-vous</p>
          <ul class="service-features">
            <li><i class="fas fa-check-circle"></i> Rendez-vous ambassade</li>
            <li><i class="fas fa-check-circle"></i> Prise de RDV en ligne</li>
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
        <h4>Important : Visa Irlande</h4>
        <p>L'Irlande n'appartient pas à l'espace Schengen. Un visa spécifique est nécessaire pour entrer sur le territoire irlandais. Notre expertise couvre l'ensemble des procédures consulaires pour tous types de visas.</p>
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