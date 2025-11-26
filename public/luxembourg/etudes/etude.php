<?php
require_once __DIR__ . '/../../../config.php';
$page_title = "Études en Italie — Services";
include __DIR__ . '/../../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="country-hero italie-hero">
  <div class="hero-content-wrapper">
    <div class="country-hero-content">
      <h1><i class="fas fa-graduation-cap"></i> Études en Italie</h1>
      <p>Choisissez le service qui correspond à votre projet d'études en Italie</p>
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
          <span class="stat-number" data-count="1500">0</span>
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
  <div class="hero-pattern italie-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="italie-flag-element"></div>
</div>

<div class="services-container" id="services">
  <h2>Services pour vos <span class="text-italie">études en Italie</span></h2>
  <p class="services-subtitle">Sélectionnez le service dont vous avez besoin pour votre projet académique</p>
  
  <div class="services-grid">
    <!-- Demande d'Admission -->
    <div class="service-card italie-card">
      <div class="card-icon">
        <i class="fa-solid fa-file-pen"></i>
      </div>
      <div class="card-image">
        <img src="../../images/admission.jpg" alt="Admission universitaire Italie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Demande d'Admission</h3>
        <p>Intégrez l'établissement d'enseignement supérieur de votre choix en Italie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Choix des universités</li>
          <li><i class="fas fa-check-circle"></i> Préparation du dossier</li>
          <li><i class="fas fa-check-circle"></i> Traduction des documents</li>
          <li><i class="fas fa-check-circle"></i> Suivi des candidatures</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn italie-btn" href="admission.php">
            <span>Démarrer ma candidature</span>
            <i class="fas fa-arrow-right"></i>
          </a>
          <a class="service-link" href="#contact">Plus d'informations</a>
        </div>
      </div>
    </div>

    <!-- Demande de Visa Étudiant -->
    <div class="service-card italie-card">
      <div class="card-icon">
        <i class="fa-solid fa-passport"></i>
      </div>
      <div class="card-image">
        <img src="../../images/visa.jpg" alt="Visa étudiant Italie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Demande de Visa Étudiant</h3>
        <p>Obtenez votre visa étudiant pour poursuivre vos études en Italie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Assistance complète</li>
          <li><i class="fas fa-check-circle"></i> Préparation du dossier</li>
          <li><i class="fas fa-check-circle"></i> Prise de rendez-vous</li>
          <li><i class="fas fa-check-circle"></i> Suivi jusqu'à l'obtention</li>
        </ul>
        <div class="card-actions">
          <a class="service-btn italie-btn" href="/babylone/public/etudes/visa.php">
            <span>Démarrer ma demande</span>
            <i class="fas fa-arrow-right"></i>
          </a>
          <a class="service-link" href="#contact">Plus d'informations</a>
        </div>
      </div>
    </div>
  </div>

  <div class="service-info-box italie-info">
    <div class="info-icon">
      <i class="fas fa-lightbulb"></i>
    </div>
    <div class="info-content">
      <h4>Conseil important</h4>
      <p>Pour étudier en Italie, vous devez d'abord obtenir une admission dans un établissement avant de pouvoir demander votre visa étudiant. Nous vous recommandons de commencer par la demande d'admission.</p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>

<style>
  :root {
    --luxembourg-red: #EF3340;
    --luxembourg-white: #FFFFFF;
    --luxembourg-blue: #00A3E0;
    --luxembourg-light-blue: #6EC1E4;
    --luxembourg-dark-blue: #1B365D;
    --luxembourg-light-gray: #F5F7FA;
    --luxembourg-gradient: linear-gradient(135deg, var(--luxembourg-red) 0%, var(--luxembourg-white) 50%, var(--luxembourg-blue) 100%);
    --luxembourg-gradient-light: linear-gradient(135deg, #D32F2F 0%, var(--luxembourg-white) 50%, #0088CC 100%);
    --luxembourg-shadow: 0 10px 30px rgba(239, 51, 64, 0.15);
    --luxembourg-shadow-hover: 0 20px 50px rgba(239, 51, 64, 0.25);
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
    background: var(--luxembourg-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
  }

  /* Hero Section */
  .italie-hero {
    background: var(--luxembourg-gradient);
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
    color: var(--luxembourg-white);
    animation: pulse 2s infinite;
    text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
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

  .italie-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: 
      radial-gradient(circle at 20% 30%, rgba(239, 51, 64, 0.1) 2px, transparent 2px),
      radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
      radial-gradient(circle at 40% 80%, rgba(0, 163, 224, 0.1) 3px, transparent 3px);
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
      rgba(239, 51, 64, 0.8) 0%, 
      rgba(255, 255, 255, 0.8) 50%, 
      rgba(0, 163, 224, 0.8) 100%);
    z-index: 2;
  }

  .italie-flag-element {
    position: absolute;
    top: 20px;
    right: 20px;
    width: 120px;
    height: 80px;
    background: 
      linear-gradient(180deg, 
        var(--luxembourg-red) 0% 33%, 
        var(--luxembourg-white) 33% 66%, 
        var(--luxembourg-blue) 66% 100%);
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
    color: var(--luxembourg-dark-blue);
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
    background: var(--luxembourg-gradient);
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

  /* Services Grid - 2 cartes côte à côte */
  .services-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 40px;
    margin-top: 40px;
    max-width: 1100px;
    margin-left: auto;
    margin-right: auto;
    align-items: stretch;
  }

  .italie-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: var(--luxembourg-shadow);
    transition: all 0.4s ease;
    position: relative;
    border: 1px solid rgba(239, 51, 64, 0.1);
    display: flex;
    flex-direction: column;
    height: 100%;
  }

  .italie-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: var(--luxembourg-shadow-hover);
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
    box-shadow: 0 10px 30px rgba(239, 51, 64, 0.2);
    transition: all 0.3s ease;
  }

  .italie-card:hover .card-icon {
    transform: scale(1.1) rotate(10deg);
    background: var(--luxembourg-gradient);
  }

  .italie-card:hover .card-icon i {
    color: white;
  }

  .card-icon i {
    font-size: 32px;
    color: var(--luxembourg-red);
    transition: all 0.3s ease;
  }

  .card-image {
    height: 250px;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
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
    background: linear-gradient(to bottom, transparent 0%, rgba(239, 51, 64, 0.8) 100%);
    transition: all 0.3s ease;
  }

  .italie-card:hover .card-image img {
    transform: scale(1.1);
  }

  .italie-card:hover .card-overlay {
    background: linear-gradient(to bottom, transparent 0%, var(--luxembourg-red) 100%);
  }

  .card-flag {
    position: absolute;
    top: 20px;
    left: 20px;
    width: 80px;
    height: 50px;
    background: 
      linear-gradient(180deg, 
        var(--luxembourg-red) 0% 33%, 
        var(--luxembourg-white) 33% 66%, 
        var(--luxembourg-blue) 66% 100%);
    border-radius: 4px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
  }

  .card-content {
    padding: 30px;
    text-align: center;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
  }

  .card-content h3 {
    font-size: 1.8rem;
    margin-bottom: 15px;
    color: var(--luxembourg-dark-blue);
    font-weight: 700;
  }

  .card-content p {
    color: #666;
    margin-bottom: 25px;
    line-height: 1.6;
    font-size: 1.1rem;
    flex-grow: 0;
  }

  .service-features {
    text-align: left;
    margin-bottom: 30px;
    padding-left: 0;
    flex-grow: 1;
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
    color: var(--luxembourg-red);
    margin-right: 12px;
    font-size: 1.2rem;
  }

  .card-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
    margin-top: auto;
  }

  .italie-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 18px 40px;
    background: var(--luxembourg-gradient);
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

  .italie-btn:hover {
    background: var(--luxembourg-gradient-light);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(239, 51, 64, 0.4);
  }

  .italie-btn i {
    transition: transform 0.3s ease;
  }

  .italie-btn:hover i {
    transform: translateX(5px);
  }

  .service-link {
    color: var(--luxembourg-red);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
  }

  .service-link:hover {
    color: var(--luxembourg-dark-blue);
    text-decoration: underline;
  }

  .italie-info {
    background: linear-gradient(135deg, var(--luxembourg-light-gray) 0%, #f0f8f0 100%);
    border-left: 4px solid var(--luxembourg-red);
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
    color: var(--luxembourg-red);
  }

  .info-content h4 {
    color: var(--luxembourg-dark-blue);
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
      grid-template-columns: repeat(2, 1fr);
      gap: 30px;
    }
  }

  @media (max-width: 768px) {
    .italie-hero {
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
    
    .italie-flag-element {
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
    
    .services-grid {
      grid-template-columns: 1fr;
      gap: 20px;
    }
    
    .card-image {
      height: 200px;
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