<?php
require_once __DIR__ . '/../../../../config.php';
$page_title = "Études en Espagne — Services";
include __DIR__ . '/../../../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="country-hero espagne-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-graduation-cap"></i> Études en Espagne</h1>
      <p>Choisissez le service qui correspond à votre projet d'études en Espagne</p>
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
          <span class="stat-number" data-count="1200">0</span>
          <span class="stat-label">étudiants accompagnés</span>
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
  <div class="hero-pattern espagne-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="espagne-flag-element"></div>
</div>

<div class="services-container" id="services">
  <h2>Services pour vos <span class="text-espagne">études en Espagne</span></h2>
  <p class="services-subtitle">Sélectionnez le service dont vous avez besoin pour votre projet académique</p>
  
  <div class="services-grid">
    <!-- Demande de Visa Étudiant -->
    <div class="service-card espagne-card">
      <div class="card-icon">
        <i class="fa-solid fa-passport"></i>
      </div>
      <div class="card-image">
        <img src="../../../images/visa-etudiant-belgique.jpg" alt="Visa étudiant Espagne">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Demande de Visa Étudiant</h3>
        <p>Obtenez votre visa étudiant pour poursuivre vos études en Espagne</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Assistance complète</li>
          <li><i class="fas fa-check-circle"></i> Préparation du dossier</li>
          <li><i class="fas fa-check-circle"></i> Prise de rendez-vous</li>
          <li><i class="fas fa-check-circle"></i> Suivi jusqu'à l'obtention</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn espagne-btn" href="/babylone/public/etudes/visa.php">
            <span>Démarrer ma demande</span>
            <i class="fas fa-arrow-right"></i>
          </a>
          <a class="service-link" href="#contact">Plus d'informations</a>
        </div>
      </div>
    </div>

    <!-- Demande d'Admission -->
    <div class="service-card espagne-card">
      <div class="card-icon">
        <i class="fa-solid fa-file-pen"></i>
      </div>
      <div class="card-image">
        <img src="../../../images/admission.jpg" alt="Admission universitaire Espagne">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Demande d'Admission</h3>
        <p>Intégrez l'établissement d'enseignement supérieur de votre choix en Espagne</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Choix des universités</li>
          <li><i class="fas fa-check-circle"></i> Préparation du dossier</li>
          <li><i class="fas fa-check-circle"></i> Traduction des documents</li>
          <li><i class="fas fa-check-circle"></i> Suivi des candidatures</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn espagne-btn" href="/babylone/public/espagne/etudes/etudes.php">
            <span>Démarrer ma candidature</span>
            <i class="fas fa-arrow-right"></i>
          </a>
          <a class="service-link" href="#contact">Plus d'informations</a>
        </div>
      </div>
    </div>
  </div>

  <div class="service-info-box espagne-info">
    <div class="info-icon">
      <i class="fas fa-lightbulb"></i>
    </div>
    <div class="info-content">
      <h4>Conseil important</h4>
      <p>Pour étudier en Espagne, vous devez d'abord obtenir une admission dans un établissement avant de pouvoir demander votre visa étudiant. Nous vous recommandons de commencer par la demande d'admission.</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../../includes/footer.php'; ?>

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
    --shadow-espagne: 0 10px 30px rgba(170, 21, 27, 0.2);
    --shadow-espagne-hover: 0 20px 50px rgba(170, 21, 27, 0.3);
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

  /* Hero Section */
  .espagne-hero {
    background: var(--gradient-espagne);
    color: white;
    padding: 120px 0;
    text-align: center;
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    display: flex;
    align-items: center;
  }

  .hero-content-wrapper {
    position: relative;
    z-index: 3;
    width: 100%;
  }

  .country-hero-content h1 {
    font-size: 3.5rem;
    margin-bottom: 20px;
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
    font-size: 1.4rem;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto 40px;
    font-weight: 300;
    animation: fadeInUp 1s ease-out 0.2s both;
  }

  .hero-stats {
    display: flex;
    justify-content: center;
    gap: 60px;
    margin: 60px 0;
    flex-wrap: wrap;
    animation: fadeInUp 1s ease-out 0.4s both;
  }

  .stat {
    text-align: center;
    position: relative;
  }

  .stat-number {
    display: block;
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 5px;
    color: white;
    transition: all 0.5s ease;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  }

  .stat-label {
    font-size: 1.1rem;
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
    padding: 15px 30px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    font-weight: 600;
  }

  .cta-button:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
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
    background-size: 100px 100px;
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
    top: 20px;
    right: 20px;
    width: 120px;
    height: 80px;
    background: 
      linear-gradient(180deg, 
        var(--espagne-red) 0% 25%, 
        var(--espagne-yellow) 25% 75%, 
        var(--espagne-red) 75% 100%);
    z-index: 3;
    opacity: 0.8;
    border-radius: 4px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    animation: float 3s ease-in-out infinite;
  }

  /* Services Container */
  .services-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 100px 20px;
  }

  .services-container h2 {
    text-align: center;
    font-size: 2.8rem;
    margin-bottom: 20px;
    color: var(--espagne-dark-red);
    position: relative;
    font-weight: 700;
  }

  .services-container h2:after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: var(--gradient-espagne);
    border-radius: 2px;
  }

  .services-subtitle {
    text-align: center;
    font-size: 1.2rem;
    color: #666;
    margin-bottom: 60px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
  }

  /* Services Grid - 2 cartes seulement */
  .services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
    gap: 40px;
    margin-top: 40px;
    max-width: 1000px;
    margin-left: auto;
    margin-right: auto;
  }

  .espagne-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--shadow-espagne);
    transition: all 0.4s ease;
    position: relative;
    border: 1px solid rgba(170, 21, 27, 0.1);
  }

  .espagne-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: var(--shadow-espagne-hover);
  }

  .card-icon {
    position: absolute;
    top: 20px;
    right: 20px;
    background: white;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    box-shadow: 0 10px 30px rgba(170, 21, 27, 0.3);
    transition: all 0.3s ease;
  }

  .espagne-card:hover .card-icon {
    transform: scale(1.1) rotate(10deg);
    background: var(--gradient-espagne);
  }

  .espagne-card:hover .card-icon i {
    color: white;
  }

  .card-icon i {
    font-size: 32px;
    color: var(--espagne-red);
    transition: all 0.3s ease;
  }

  .card-image {
    height: 250px;
    overflow: hidden;
    position: relative;
  }

  .card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: all 0.5s ease;
  }

  .card-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to bottom, transparent 0%, rgba(170, 21, 27, 0.8) 100%);
    transition: all 0.3s ease;
  }

  .espagne-card:hover .card-image img {
    transform: scale(1.1);
  }

  .espagne-card:hover .card-overlay {
    background: linear-gradient(to bottom, transparent 0%, var(--espagne-red) 100%);
  }

  .card-flag {
    position: absolute;
    top: 20px;
    left: 20px;
    width: 80px;
    height: 50px;
    background: 
      linear-gradient(180deg, 
        var(--espagne-red) 0% 25%, 
        var(--espagne-yellow) 25% 75%, 
        var(--espagne-red) 75% 100%);
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  }

  .card-content {
    padding: 30px;
    text-align: center;
  }

  .card-content h3 {
    font-size: 1.8rem;
    margin-bottom: 15px;
    color: var(--espagne-dark-red);
    font-weight: 700;
  }

  .card-content p {
    color: #666;
    margin-bottom: 25px;
    line-height: 1.6;
    font-size: 1.1rem;
  }

  .service-features {
    text-align: left;
    margin-bottom: 30px;
    padding-left: 0;
  }

  .service-features li {
    list-style: none;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    color: #555;
    font-weight: 500;
  }

  .service-features i {
    color: var(--espagne-red);
    margin-right: 12px;
    font-size: 1.2rem;
  }

  .card-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
  }

  .espagne-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 18px 40px;
    background: var(--gradient-espagne);
    color: white;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    gap: 12px;
    font-size: 1.1rem;
    cursor: pointer;
  }

  .espagne-btn:hover {
    background: var(--gradient-espagne-light);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(170, 21, 27, 0.4);
  }

  .espagne-btn i {
    transition: transform 0.3s ease;
  }

  .espagne-btn:hover i {
    transform: translateX(5px);
  }

  .service-link {
    color: var(--espagne-red);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .service-link:hover {
    color: var(--espagne-dark-red);
    text-decoration: underline;
  }

  .espagne-info {
    background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
    border-left: 4px solid var(--espagne-red);
    border-radius: 12px;
    padding: 25px;
    display: flex;
    align-items: flex-start;
    gap: 20px;
    grid-column: 1 / -1;
    margin-top: 40px;
  }

  .info-icon {
    font-size: 2rem;
    color: var(--espagne-red);
  }

  .info-content h4 {
    color: var(--espagne-dark-red);
    margin-bottom: 10px;
    font-size: 1.3rem;
  }

  .info-content p {
    color: #666;
    line-height: 1.6;
  }

  /* Animations */
  @keyframes fadeInUp {
    from {
      opacity: 0;
      transform: translateY(30px);
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
      transform: scale(1.1);
    }
  }

  @keyframes patternMove {
    from {
      background-position: 0 0;
    }
    to {
      background-position: 100px 100px;
    }
  }

  @keyframes float {
    0%, 100% {
      transform: translateY(0);
    }
    50% {
      transform: translateY(-10px);
    }
  }

  /* Responsive Design */
  @media (max-width: 1024px) {
    .country-hero-content h1 {
      font-size: 2.8rem;
    }
    
    .hero-stats {
      gap: 40px;
    }
    
    .stat-number {
      font-size: 2.5rem;
    }
    
    .services-grid {
      grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    }
  }

  @media (max-width: 768px) {
    .country-hero {
      padding: 80px 0;
      min-height: auto;
    }
    
    .country-hero-content h1 {
      font-size: 2.2rem;
    }
    
    .country-hero-content p {
      font-size: 1.1rem;
    }
    
    .hero-stats {
      gap: 30px;
    }
    
    .stat-number {
      font-size: 2rem;
    }
    
    .services-container {
      padding: 60px 20px;
    }
    
    .services-container h2 {
      font-size: 2.2rem;
    }
    
    .espagne-flag-element {
      width: 100px;
      height: 65px;
      top: 10px;
      right: 10px;
    }
    
    .services-grid {
      grid-template-columns: 1fr;
      max-width: 500px;
    }
  }

  @media (max-width: 480px) {
    .country-hero-content h1 {
      font-size: 1.8rem;
    }
    
    .hero-stats {
      flex-direction: column;
      gap: 20px;
    }
    
    .services-container h2 {
      font-size: 1.8rem;
    }
    
    .card-content {
      padding: 20px;
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
  });
</script>