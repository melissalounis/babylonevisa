<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Estonie — Services";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="estonie-hero">
  <div class="hero-content-wrapper">
    <div class="estonie-hero-content">
      <h1><i class="fas fa-tree"></i> Estonie — Services</h1>
      <p>Découvrez nos services spécialisés pour réaliser votre projet en Estonie</p>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-number" data-count="91">0</span>
          <span class="stat-label">de réussite</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="5">0</span>
          <span class="stat-label">ans d'expérience</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="1300">0</span>
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
  <div class="estonie-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="estonie-flag-element"></div>
</div>

<div class="services-container" id="services">
  <h2>Nos services pour l'<span class="text-estonie">Estonie</span></h2>
  <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet</p>
  
  <div class="services-grid">
    <!-- Rendez-vous Card -->
    <div class="estonie-card">
      <div class="card-icon">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="card-image">
        <img src="../images/voy2.jpg" alt="Prise de Rendez-vous Estonie">
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
          <a class="estonie-btn" href="../../rendez_vous.php">
            <span>Prendre RDV</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Tourisme & Affaires Card -->
    <div class="estonie-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/tourisme.jpg" alt="Tourisme en Estonie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & Affaires</h3>
        <p>Voyages et séjours professionnels en Estonie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Formalités de voyage</li>
          <li><i class="fas fa-check-circle"></i> Séjours d'affaires</li>
          <li><i class="fas fa-check-circle"></i> Guides touristiques</li>
        </ul>
        <div class="card-actions">
          <a class="estonie-btn" href="../../tourisme/tourisme.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Travail Card -->
    <div class="estonie-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail en Estonie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Opportunités professionnelles et insertion en Estonie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa de travail</li>
          <li><i class="fas fa-check-circle"></i> Formalités administratives</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
        </ul>
        <div class="card-actions">
          <a class="estonie-btn" href="../travail/travail.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="estonie-info">
    <div class="info-icon">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="info-content">
      <h4>Important : Visa Estonie</h4>
      <p>L'Estonie, pays balte innovant et numériquement avancé, offre des opportunités uniques dans les technologies et un cadre de vie préservé. Nos experts vous accompagnent dans toutes les démarches administratives spécifiques à l'Estonie.</p>
    </div>
  </div>
</div>

<style>
/* ===== STYLE ESTONIE ===== */
:root {
  --estonie-blue: #0072CE;
  --estonie-black: #000000;
  --estonie-white: #FFFFFF;
  --estonie-dark: #1a1a1a;
  --estonie-light: #f8f9fa;
  --gradient-estonie: linear-gradient(135deg, var(--estonie-blue) 0%, var(--estonie-black) 50%, var(--estonie-white) 100%);
  --shadow-estonie: 0 8px 25px rgba(0, 114, 206, 0.15);
  --shadow-estonie-hover: 0 15px 35px rgba(0, 114, 206, 0.25);
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

.text-estonie {
  background: linear-gradient(135deg, var(--estonie-blue), var(--estonie-black), var(--estonie-white));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 700;
}

/* Hero Section RÉDUITE */
.estonie-hero {
  background: var(--gradient-estonie);
  color: white;
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

.estonie-hero-content h1 {
  font-size: 1.8rem;
  margin-bottom: 10px;
  font-weight: 800;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  animation: fadeInUp 1s ease-out;
  color: white;
}

.estonie-hero-content h1 i {
  margin-right: 10px;
  color: var(--estonie-white);
  animation: pulse 2s infinite;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

.estonie-hero-content p {
  font-size: 0.9rem;
  opacity: 0.95;
  max-width: 500px;
  margin: 0 auto 15px;
  font-weight: 300;
  animation: fadeInUp 1s ease-out 0.2s both;
  color: white;
}

.hero-stats {
  display: flex;
  justify-content: center;
  gap: 30px;
  margin: 20px 0;
  flex-wrap: wrap;
  animation: fadeInUp 1s ease-out 0.4s both;
}

.stat {
  text-align: center;
  position: relative;
}

.stat-number {
  display: block;
  font-size: 1.5rem;
  font-weight: 800;
  margin-bottom: 5px;
  color: white;
  transition: all 0.5s ease;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.stat-label {
  font-size: 0.8rem;
  opacity: 0.9;
  font-weight: 500;
  color: white;
}

.hero-cta {
  animation: fadeInUp 1s ease-out 0.6s both;
  margin-top: 15px;
}

.cta-button {
  display: inline-flex;
  align-items: center;
  padding: 8px 16px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  text-decoration: none;
  border-radius: 50px;
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
  font-weight: 600;
  font-size: 0.8rem;
}

.cta-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
}

.estonie-pattern {
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
    rgba(0, 114, 206, 0.9) 0%, 
    rgba(0, 0, 0, 0.8) 50%, 
    rgba(255, 255, 255, 0.9) 100%);
  z-index: 2;
}

.estonie-flag-element {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 60px;
  height: 40px;
  background: linear-gradient(180deg, 
    var(--estonie-blue) 0% 33%, 
    var(--estonie-black) 33% 66%, 
    var(--estonie-white) 66% 100%);
  border-radius: 3px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
  z-index: 3;
  opacity: 0.8;
  animation: float 3s ease-in-out infinite;
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
  color: var(--estonie-blue);
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
  background: var(--gradient-estonie);
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

/* Services Grid - 3 cartes sur la même ligne */
.services-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
  margin-top: 25px;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.estonie-card {
  background: white;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: var(--shadow-estonie);
  transition: all 0.3s ease;
  position: relative;
  border: 1px solid rgba(0, 114, 206, 0.1);
  height: fit-content;
}

.estonie-card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: var(--shadow-estonie-hover);
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
  box-shadow: 0 4px 12px rgba(0, 114, 206, 0.2);
  transition: all 0.3s ease;
}

.estonie-card:hover .card-icon {
  transform: scale(1.05) rotate(5deg);
  background: var(--gradient-estonie);
}

.estonie-card:hover .card-icon i {
  color: white;
}

.card-icon i {
  font-size: 18px;
  color: var(--estonie-blue);
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
  background: linear-gradient(to bottom, transparent 0%, rgba(0, 114, 206, 0.7) 100%);
  transition: all 0.3s ease;
}

.estonie-card:hover .card-image img {
  transform: scale(1.05);
}

.estonie-card:hover .card-overlay {
  background: linear-gradient(to bottom, transparent 0%, var(--estonie-blue) 100%);
}

.card-flag {
  position: absolute;
  top: 10px;
  left: 10px;
  width: 45px;
  height: 30px;
  background: linear-gradient(180deg, 
    var(--estonie-blue) 0% 33%, 
    var(--estonie-black) 33% 66%, 
    var(--estonie-white) 66% 100%);
  border-radius: 3px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.card-content {
  padding: 16px;
  text-align: center;
}

.card-content h3 {
  font-size: 1rem;
  margin-bottom: 8px;
  color: var(--estonie-blue);
  font-weight: 700;
}

.card-content p {
  color: #666;
  margin-bottom: 10px;
  line-height: 1.4;
  font-size: 0.8rem;
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
  font-size: 0.75rem;
}

.service-features i {
  color: var(--estonie-blue);
  margin-right: 5px;
  font-size: 0.75rem;
  min-width: 12px;
}

.card-actions {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.estonie-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 14px;
  background: var(--gradient-estonie);
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

.estonie-btn:hover {
  background: var(--estonie-blue);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0, 114, 206, 0.3);
}

.estonie-btn i {
  transition: transform 0.3s ease;
}

.estonie-btn:hover i {
  transform: translateX(3px);
}

.estonie-info {
  background: linear-gradient(135deg, #F0F8FF 0%, #E6F2FF 100%);
  border-left: 3px solid var(--estonie-blue);
  border-radius: 10px;
  padding: 18px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-top: 35px;
}

.info-icon {
  font-size: 1.3rem;
  color: var(--estonie-blue);
}

.info-content h4 {
  color: var(--estonie-blue);
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
}

@media (max-width: 768px) {
  .estonie-hero {
    padding: 30px 0 20px;
    min-height: 35vh;
  }
  
  .estonie-hero-content h1 {
    font-size: 1.5rem;
  }
  
  .estonie-hero-content p {
    font-size: 0.8rem;
  }
  
  .hero-stats {
    gap: 20px;
  }
  
  .stat-number {
    font-size: 1.3rem;
  }
  
  .services-container {
    padding: 40px 20px;
  }
  
  .services-container h2 {
    font-size: 1.6rem;
  }
  
  .estonie-flag-element {
    width: 50px;
    height: 35px;
    top: 8px;
    right: 8px;
  }
  
  .services-grid {
    grid-template-columns: 1fr;
    max-width: 400px;
  }
}

@media (max-width: 480px) {
  .estonie-hero-content h1 {
    font-size: 1.3rem;
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
  
  .estonie-btn {
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>