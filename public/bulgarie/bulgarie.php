<?php
require_once __DIR__ . '../../../config.php';
$page_title = "Bulgarie — Services";
include __DIR__ . '../../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<div class="bulgarie-hero">
  <div class="hero-content-wrapper">
    <div class="bulgarie-hero-content">
      <h1><i class="fas fa-landmark"></i> Bulgarie — Services</h1>
      <p>Découvrez nos services spécialisés pour réaliser votre projet en Bulgarie</p>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-number" data-count="96">0</span>
          <span class="stat-label">de réussite</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="8">0</span>
          <span class="stat-label">ans d'expérience</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="3000">0</span>
          <span class="stat-label">clients satisfaits</span>
        </div>
      </div>
    </div>
  </div>
  <div class="bulgarie-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="bulgarie-flag-element"></div>
</div>



<div class="services-container">
  <h2>Nos services pour la <span class="text-bulgarie">Bulgarie</span></h2>
  <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet</p>
  
  <div class="services-grid">
    <!-- Carte Rendez-vous -->
    <div class="bulgarie-card">
      <div class="card-icon">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="card-image">
        <img src="../images/visapasport.jpg" alt="Prise de Rendez-vous Bulgarie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Prise de Rendez-vous</h3>
        <p>Service professionnel de prise de rendez-vous pour vos démarches</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Rendez-vous consulaires</li>
          <li><i class="fas fa-check-circle"></i> Ambassades et consulats</li>
          <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
        </ul>
        <div class="card-actions">
          <a class="bulgarie-btn" href="../../rendez_vous.php">
            <span>Prendre RDV</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Carte Études -->
    <div class="bulgarie-card">
      <div class="card-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>
      <div class="card-image">
        <img src="../images/etude.webp" alt="Études en Bulgarie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Études</h3>
        <p>Universités bulgares, médecine, ingénierie et autres spécialisations</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Taux de réussite élevé</li>
          <li><i class="fas fa-check-circle"></i> Procédure simplifiée</li>
          <li><i class="fas fa-check-circle"></i> Orientation académique</li>
        </ul>
        <div class="card-actions">
          <a class="bulgarie-btn" href="../bulgarie/etude/etude.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Carte Tourisme & Affaires -->
    <div class="bulgarie-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/tourisme.jpg" alt="Tourisme en Bulgarie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & Affaires</h3>
        <p>Visa touristique, voyages d'affaires et assurance voyage</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa Schengen</li>
          <li><i class="fas fa-check-circle"></i> Assistance voyage</li>
          <li><i class="fas fa-check-circle"></i> Conseils personnalisés</li>
        </ul>
        <div class="card-actions">
          <a class="bulgarie-btn" href="../../tourisme/tourisme.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Carte Travail -->
    <div class="bulgarie-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail en Bulgarie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Visa travail, contrat et insertion professionnelle</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa de travail</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
          <li><i class="fas fa-check-circle"></i> Contrat de travail</li>
        </ul>
        <div class="card-actions">
          <a class="bulgarie-btn" href="/babylone/public/travail/travail.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

<style>
/* ===== STYLE BULGARIE ===== */
:root {
  --bulgarie-green: #00966E;
  --bulgarie-white: #FFFFFF;
  --bulgarie-red: #D62612;
  --bulgarie-dark-green: #007A57;
  --bulgarie-light: #f8f9fa;
  --bulgarie-dark: #1a1a1a;
  --gradient-bulgarie: linear-gradient(135deg, var(--bulgarie-green) 0%, var(--bulgarie-red) 100%);
  --shadow-bulgarie: 0 8px 25px rgba(0, 150, 110, 0.15);
  --shadow-bulgarie-hover: 0 15px 35px rgba(0, 150, 110, 0.25);
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
  background: var(--bulgarie-light);
}

h1, h2, h3, h4, h5, h6 {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
}

.text-bulgarie {
  color: var(--bulgarie-green);
  font-weight: 700;
}

/* Hero Section réduite */
.bulgarie-hero {
  background: var(--gradient-bulgarie);
  color: white;
  padding: 80px 0 50px;
  text-align: center;
  position: relative;
  overflow: hidden;
  margin-bottom: 0;
}

.hero-content-wrapper {
  position: relative;
  z-index: 3;
  width: 100%;
}

.bulgarie-hero-content h1 {
  font-size: 2.5rem;
  margin-bottom: 15px;
  font-weight: 800;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  animation: fadeInUp 1s ease-out;
}

.bulgarie-hero-content h1 i {
  margin-right: 15px;
  color: var(--bulgarie-white);
  animation: pulse 2s infinite;
  text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
}

.bulgarie-hero-content p {
  font-size: 1.1rem;
  opacity: 0.95;
  max-width: 600px;
  margin: 0 auto 25px;
  font-weight: 300;
  animation: fadeInUp 1s ease-out 0.2s both;
}

.hero-stats {
  display: flex;
  justify-content: center;
  gap: 40px;
  margin: 30px 0;
  flex-wrap: wrap;
  animation: fadeInUp 1s ease-out 0.4s both;
}

.stat {
  text-align: center;
  position: relative;
  background: rgba(255, 255, 255, 0.15);
  padding: 15px 20px;
  border-radius: 12px;
  backdrop-filter: blur(10px);
}

.stat-number {
  display: block;
  font-size: 2rem;
  font-weight: 800;
  margin-bottom: 5px;
  color: white;
  transition: all 0.5s ease;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.stat-label {
  font-size: 0.9rem;
  opacity: 0.9;
  font-weight: 500;
  color: white;
}

.bulgarie-pattern {
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
    rgba(0, 150, 110, 0.9) 0%, 
    rgba(214, 38, 18, 0.9) 100%);
  z-index: 2;
}

.bulgarie-flag-element {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 70px;
  height: 45px;
  background: 
    linear-gradient(180deg, 
      var(--bulgarie-white) 0% 33%, 
      var(--bulgarie-green) 33% 66%, 
      var(--bulgarie-red) 66% 100%);
  border-radius: 3px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
  z-index: 3;
  opacity: 0.8;
  animation: float 3s ease-in-out infinite;
}

/* Section Slider en pleine largeur pour la Bulgarie */


/* Services Container */
.services-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 80px 20px;
}

.services-container h2 {
  text-align: center;
  font-size: 2.2rem;
  margin-bottom: 15px;
  color: var(--bulgarie-green);
  position: relative;
  font-weight: 700;
}

.services-container h2:after {
  content: '';
  position: absolute;
  bottom: -12px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 3px;
  background: var(--gradient-bulgarie);
  border-radius: 2px;
}

.services-subtitle {
  text-align: center;
  font-size: 1rem;
  color: #666;
  margin-bottom: 40px;
  max-width: 500px;
  margin-left: auto;
  margin-right: auto;
}

/* Services Grid - 4 cartes sur la même ligne */
.services-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
  margin-top: 30px;
  max-width: 1400px;
  margin-left: auto;
  margin-right: auto;
}

.bulgarie-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow-bulgarie);
  transition: all 0.3s ease;
  position: relative;
  border: 1px solid rgba(0, 150, 110, 0.1);
  height: fit-content;
}

.bulgarie-card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: var(--shadow-bulgarie-hover);
}

.card-icon {
  position: absolute;
  top: 12px;
  right: 12px;
  background: white;
  width: 45px;
  height: 45px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3;
  box-shadow: 0 4px 12px rgba(0, 150, 110, 0.2);
  transition: all 0.3s ease;
}

.bulgarie-card:hover .card-icon {
  transform: scale(1.05) rotate(5deg);
  background: var(--gradient-bulgarie);
}

.bulgarie-card:hover .card-icon i {
  color: white;
}

.card-icon i {
  font-size: 20px;
  color: var(--bulgarie-green);
  transition: all 0.3s ease;
}

.card-image {
  height: 140px;
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
  background: linear-gradient(to bottom, transparent 0%, rgba(0, 150, 110, 0.7) 100%);
  transition: all 0.3s ease;
}

.bulgarie-card:hover .card-image img {
  transform: scale(1.05);
}

.bulgarie-card:hover .card-overlay {
  background: linear-gradient(to bottom, transparent 0%, var(--bulgarie-green) 100%);
}

.card-flag {
  position: absolute;
  top: 12px;
  left: 12px;
  width: 50px;
  height: 35px;
  background: 
    linear-gradient(180deg, 
      var(--bulgarie-white) 0% 33%, 
      var(--bulgarie-green) 33% 66%, 
      var(--bulgarie-red) 66% 100%);
  border-radius: 3px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.card-content {
  padding: 18px;
  text-align: center;
}

.card-content h3 {
  font-size: 1.1rem;
  margin-bottom: 10px;
  color: var(--bulgarie-green);
  font-weight: 700;
}

.card-content p {
  color: #666;
  margin-bottom: 12px;
  line-height: 1.4;
  font-size: 0.85rem;
  min-height: 40px;
}

.service-features {
  text-align: left;
  margin-bottom: 15px;
  padding-left: 0;
}

.service-features li {
  list-style: none;
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  color: #555;
  font-weight: 500;
  font-size: 0.8rem;
}

.service-features i {
  color: var(--bulgarie-green);
  margin-right: 6px;
  font-size: 0.8rem;
  min-width: 14px;
}

.card-actions {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.bulgarie-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 10px 16px;
  background: var(--gradient-bulgarie);
  color: white;
  border-radius: 20px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
  gap: 6px;
  font-size: 0.85rem;
  cursor: pointer;
}

.bulgarie-btn:hover {
  background: var(--bulgarie-dark-green);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0, 150, 110, 0.3);
}

.bulgarie-btn i {
  transition: transform 0.3s ease;
}

.bulgarie-btn:hover i {
  transform: translateX(3px);
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
    gap: 20px;
    max-width: 800px;
  }
}

@media (max-width: 768px) {
  .bulgarie-hero {
    padding: 60px 0 40px;
  }
  
  .bulgarie-hero-content h1 {
    font-size: 2rem;
  }
  
  .bulgarie-hero-content p {
    font-size: 1rem;
  }
  
  .hero-stats {
    gap: 20px;
  }
  
  .stat-number {
    font-size: 1.8rem;
  }
  
  .slider-container h2 {
    font-size: 2rem;
  }
  
  .slide {
    height: 400px;
  }
  
  .slide-content {
    padding: 40px 30px 30px;
  }
  
  .slide-content h3 {
    font-size: 1.8rem;
  }
  
  .slide-content p {
    font-size: 1.1rem;
  }
  
  .slider-btn {
    width: 50px;
    height: 50px;
  }
  
  .prev-btn {
    left: 20px;
  }
  
  .next-btn {
    right: 20px;
  }
  
  .services-container {
    padding: 60px 20px;
  }
  
  .services-container h2 {
    font-size: 1.8rem;
  }
  
  .bulgarie-flag-element {
    width: 60px;
    height: 40px;
    top: 10px;
    right: 10px;
  }
  
  .services-grid {
    grid-template-columns: 1fr;
    max-width: 400px;
  }
}

@media (max-width: 480px) {
  .bulgarie-hero-content h1 {
    font-size: 1.6rem;
  }
  
  .hero-stats {
    flex-direction: column;
    gap: 15px;
  }
  
  .slide {
    height: 350px;
  }
  
  .slide-content {
    padding: 30px 20px 20px;
  }
  
  .slide-content h3 {
    font-size: 1.5rem;
  }
  
  .slide-content p {
    font-size: 1rem;
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
  
  .slider-indicators {
    bottom: 20px;
  }
  
  .indicator {
    width: 12px;
    height: 12px;
  }
  
  .services-container h2 {
    font-size: 1.5rem;
  }
  
  .card-content {
    padding: 15px;
  }
  
  .bulgarie-btn {
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

    // Script pour le slider
    const track = document.querySelector('.slider-track');
    const slides = document.querySelectorAll('.slide');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');
    const indicators = document.querySelectorAll('.indicator');
    
    let currentSlide = 0;
    const totalSlides = slides.length;
    
    // Fonction pour mettre à jour le slider
    function updateSlider() {
      track.style.transform = `translateX(-${currentSlide * 100}%)`;
      
      // Mettre à jour les indicateurs
      indicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === currentSlide);
      });
    }
    
    // Événements pour les boutons
    nextBtn.addEventListener('click', function() {
      currentSlide = (currentSlide + 1) % totalSlides;
      updateSlider();
    });
    
    prevBtn.addEventListener('click', function() {
      currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
      updateSlider();
    });
    
    // Événements pour les indicateurs
    indicators.forEach((indicator, index) => {
      indicator.addEventListener('click', function() {
        currentSlide = index;
        updateSlider();
      });
    });
    
    // Défilement automatique
    let autoSlide = setInterval(function() {
      currentSlide = (currentSlide + 1) % totalSlides;
      updateSlider();
    }, 5000);
    
    // Arrêter le défilement automatique au survol
    const sliderWrapper = document.querySelector('.slider-wrapper');
    sliderWrapper.addEventListener('mouseenter', function() {
      clearInterval(autoSlide);
    });
    
    sliderWrapper.addEventListener('mouseleave', function() {
      autoSlide = setInterval(function() {
        currentSlide = (currentSlide + 1) % totalSlides;
        updateSlider();
      }, 5000);
    });
  });
</script>