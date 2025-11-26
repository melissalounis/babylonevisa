<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Hongrie — Services";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="hongrie-hero">
  <div class="hero-content-wrapper">
    <div class="hongrie-hero-content">
      <h1><i class="fas fa-landmark"></i> Hongrie — Services</h1>
      <p>Découvrez nos services spécialisés pour réaliser votre projet en Hongrie</p>
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
  <div class="hongrie-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="hongrie-flag-element"></div>
</div>

<div class="services-container" id="services">
  <h2>Nos services pour la <span class="text-hongrie">Hongrie</span></h2>
  <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet</p>
  
  <div class="services-grid">
    <!-- Rendez-vous Card -->
    <div class="hongrie-card">
      <div class="card-icon">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="card-image">
        <img src="../images/visa.jpg" alt="Prise de Rendez-vous Hongrie">
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
          <a class="hongrie-btn" href="../../rendez_vous.php">
            <span>Prendre RDV</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Tourisme & Affaires Card -->
    <div class="hongrie-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/tourisme.jpg" alt="Tourisme en Hongrie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & Affaires</h3>
        <p>Voyages et séjours professionnels en Hongrie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Formalités de voyage</li>
          <li><i class="fas fa-check-circle"></i> Séjours d'affaires</li>
          <li><i class="fas fa-check-circle"></i> Guides touristiques</li>
        </ul>
        <div class="card-actions">
          <a class="hongrie-btn" href="../../tourisme/tourisme.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Travail Card -->
    <div class="hongrie-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail en Hongrie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Opportunités professionnelles et insertion en Hongrie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa de travail</li>
          <li><i class="fas fa-check-circle"></i> Formalités administratives</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
        </ul>
        <div class="card-actions">
          <a class="hongrie-btn" href="../travail/travail.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="hongrie-info">
    <div class="info-icon">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="info-content">
      <h4>Important : Visa Hongrie</h4>
      <p>La Hongrie, pays d'Europe centrale au riche patrimoine culturel, offre des opportunités uniques pour les études, le travail et le tourisme. Nos experts vous accompagnent dans toutes les démarches administratives spécifiques à la Hongrie.</p>
    </div>
  </div>
</div>

<style>
/* ===== STYLE HONGRIE ===== */
:root {
  --hongrie-red: #CD2A3E;
  --hongrie-white: #FFFFFF;
  --hongrie-green: #477050;
  --hongrie-dark: #1a1a1a;
  --hongrie-light: #f8f9fa;
  --gradient-hongrie: linear-gradient(135deg, var(--hongrie-red) 0%, var(--hongrie-white) 50%, var(--hongrie-green) 100%);
  --shadow-hongrie: 0 8px 25px rgba(205, 42, 62, 0.15);
  --shadow-hongrie-hover: 0 15px 35px rgba(205, 42, 62, 0.25);
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

.text-hongrie {
  background: linear-gradient(135deg, var(--hongrie-red), var(--hongrie-white), var(--hongrie-green));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 700;
}

/* Hero Section RÉDUITE */
.hongrie-hero {
  background: var(--gradient-hongrie);
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

.hongrie-hero-content h1 {
  font-size: 1.8rem;
  margin-bottom: 10px;
  font-weight: 800;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  animation: fadeInUp 1s ease-out;
  color: white;
}

.hongrie-hero-content h1 i {
  margin-right: 10px;
  color: var(--hongrie-white);
  animation: pulse 2s infinite;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

.hongrie-hero-content p {
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

.hongrie-pattern {
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
    rgba(205, 42, 62, 0.9) 0%, 
    rgba(255, 255, 255, 0.8) 50%, 
    rgba(71, 112, 80, 0.9) 100%);
  z-index: 2;
}

.hongrie-flag-element {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 60px;
  height: 40px;
  background: linear-gradient(180deg, 
    var(--hongrie-red) 0% 33%, 
    var(--hongrie-white) 33% 66%, 
    var(--hongrie-green) 66% 100%);
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
  color: var(--hongrie-red);
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
  background: var(--gradient-hongrie);
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

.hongrie-card {
  background: white;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: var(--shadow-hongrie);
  transition: all 0.3s ease;
  position: relative;
  border: 1px solid rgba(205, 42, 62, 0.1);
  height: fit-content;
}

.hongrie-card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: var(--shadow-hongrie-hover);
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
  box-shadow: 0 4px 12px rgba(205, 42, 62, 0.2);
  transition: all 0.3s ease;
}

.hongrie-card:hover .card-icon {
  transform: scale(1.05) rotate(5deg);
  background: var(--gradient-hongrie);
}

.hongrie-card:hover .card-icon i {
  color: white;
}

.card-icon i {
  font-size: 18px;
  color: var(--hongrie-red);
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
  background: linear-gradient(to bottom, transparent 0%, rgba(205, 42, 62, 0.7) 100%);
  transition: all 0.3s ease;
}

.hongrie-card:hover .card-image img {
  transform: scale(1.05);
}

.hongrie-card:hover .card-overlay {
  background: linear-gradient(to bottom, transparent 0%, var(--hongrie-red) 100%);
}

.card-flag {
  position: absolute;
  top: 10px;
  left: 10px;
  width: 45px;
  height: 30px;
  background: linear-gradient(180deg, 
    var(--hongrie-red) 0% 33%, 
    var(--hongrie-white) 33% 66%, 
    var(--hongrie-green) 66% 100%);
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
  color: var(--hongrie-red);
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
  color: var(--hongrie-red);
  margin-right: 5px;
  font-size: 0.75rem;
  min-width: 12px;
}

.card-actions {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.hongrie-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 8px 14px;
  background: var(--gradient-hongrie);
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

.hongrie-btn:hover {
  background: var(--hongrie-red);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(205, 42, 62, 0.3);
}

.hongrie-btn i {
  transition: transform 0.3s ease;
}

.hongrie-btn:hover i {
  transform: translateX(3px);
}

.hongrie-info {
  background: linear-gradient(135deg, #FFF5F5 0%, #F0F8F0 100%);
  border-left: 3px solid var(--hongrie-red);
  border-radius: 10px;
  padding: 18px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-top: 35px;
}

.info-icon {
  font-size: 1.3rem;
  color: var(--hongrie-red);
}

.info-content h4 {
  color: var(--hongrie-red);
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
  .hongrie-hero {
    padding: 30px 0 20px;
    min-height: 35vh;
  }
  
  .hongrie-hero-content h1 {
    font-size: 1.5rem;
  }
  
  .hongrie-hero-content p {
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
  
  .hongrie-flag-element {
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
  .hongrie-hero-content h1 {
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
  
  .hongrie-btn {
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