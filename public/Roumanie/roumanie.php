<?php
require_once __DIR__ . '../../../config.php';

$page_title = "Roumanie — Services";
include __DIR__ . '/../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
/* ===== STYLE ROUMANIE ===== */
:root {
  --roumanie-blue: #002B7F;
  --roumanie-yellow: #FCD116;
  --roumanie-red: #CE1126;
  --roumanie-dark-blue: #001F5C;
  --roumanie-dark-yellow: #E6B800;
  --roumanie-light: #f8f9fa;
  --roumanie-dark: #1a1a1a;
  --gradient-roumanie: linear-gradient(135deg, var(--roumanie-blue) 0%, var(--roumanie-red) 50%, var(--roumanie-yellow) 100%);
  --gradient-roumanie-light: linear-gradient(135deg, var(--roumanie-dark-blue) 0%, #B30E1E 50%, var(--roumanie-dark-yellow) 100%);
  --shadow-roumanie: 0 8px 25px rgba(0, 43, 127, 0.15);
  --shadow-roumanie-hover: 0 15px 35px rgba(0, 43, 127, 0.25);
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
  background-color: var(--roumanie-light);
}

h1, h2, h3, h4, h5, h6 {
  font-family: 'Playfair Display', serif;
  font-weight: 700;
  margin-bottom: 1rem;
}

.text-roumanie {
  background: var(--gradient-roumanie);
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
.roumanie-hero {
  background: var(--gradient-roumanie);
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

.roumanie-hero-content {
  max-width: 800px;
  margin: 0 auto;
  padding: 0 20px;
}

.roumanie-hero-content h1 {
  font-size: 1.8rem;
  margin-bottom: 0.8rem;
  line-height: 1.2;
  font-weight: 800;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.roumanie-hero-content h1 i {
  margin-right: 12px;
  color: var(--roumanie-yellow);
}

.roumanie-hero-content p {
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
  color: white;
  line-height: 1;
  margin-bottom: 0.3rem;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.stat-label {
  font-size: 0.8rem;
  opacity: 0.9;
  font-weight: 500;
  color: white;
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

.roumanie-pattern {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 20% 30%, rgba(0, 43, 127, 0.1) 2px, transparent 2px),
    radial-gradient(circle at 80% 70%, rgba(206, 17, 38, 0.1) 1px, transparent 1px);
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

.roumanie-flag-element {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 60px;
  height: 30px;
  background: 
    linear-gradient(90deg, 
      var(--roumanie-blue) 0% 33%, 
      var(--roumanie-yellow) 33% 66%, 
      var(--roumanie-red) 66% 100%);
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
  color: var(--roumanie-blue);
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

/* Services Grid - 4 cartes sur la même ligne */
.services-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 18px;
  margin-bottom: 30px;
}

.roumanie-card {
  background: white;
  border-radius: var(--border-radius);
  overflow: hidden;
  box-shadow: var(--shadow-roumanie);
  transition: var(--transition);
  position: relative;
  border: 1px solid rgba(0, 43, 127, 0.1);
  height: 100%;
}

.roumanie-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-roumanie-hover);
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
  color: var(--roumanie-blue);
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

.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
}

.roumanie-card:hover .card-image img {
  transform: scale(1.05);
}

.card-flag {
  position: absolute;
  bottom: 12px;
  left: 12px;
  width: 35px;
  height: 18px;
  background: 
    linear-gradient(90deg, 
      var(--roumanie-blue) 0% 33%, 
      var(--roumanie-yellow) 33% 66%, 
      var(--roumanie-red) 66% 100%);
  border-radius: 2px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
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
  color: var(--roumanie-blue);
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
  color: var(--roumanie-blue);
  margin-right: 6px;
  font-size: 0.9rem;
}

.card-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
}

.roumanie-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--gradient-roumanie);
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

.roumanie-btn:hover {
  background: var(--gradient-roumanie-light);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 43, 127, 0.3);
}

.roumanie-info {
  background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
  border-radius: var(--border-radius);
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
  margin-top: 25px;
  box-shadow: var(--shadow-soft);
}

.info-icon {
  background: var(--roumanie-blue);
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
  color: var(--roumanie-dark-blue);
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
  .roumanie-hero {
    padding: 30px 0 20px;
    min-height: 35vh;
  }
  
  .roumanie-hero-content h1 {
    font-size: 1.5rem;
  }
  
  .roumanie-hero-content p {
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
  
  .roumanie-info {
    flex-direction: column;
    text-align: center;
  }
  
  .roumanie-flag-element {
    width: 50px;
    height: 25px;
    top: 10px;
    right: 10px;
  }
}

@media (max-width: 480px) {
  .roumanie-hero-content h1 {
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

<div class="roumanie-hero">
  <div class="hero-content-wrapper">
    <div class="roumanie-hero-content">
      <h1><i class="fas fa-flag"></i> Roumanie — Services</h1>
      <p>Découvrez nos services spécialisés pour réaliser votre projet en Roumanie</p>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-number" data-count="95">0</span>
          <span class="stat-label">de réussite</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="7">0</span>
          <span class="stat-label">ans d'expérience</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="2000">0</span>
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
  <div class="roumanie-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="roumanie-flag-element"></div>
</div>

<div class="services-container" id="services">
  <h2>Nos services pour la <span class="text-roumanie">Roumanie</span></h2>
  <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet</p>
  
  <div class="services-grid">
    <!-- Rendez-vous Card -->
    <div class="roumanie-card">
      <div class="card-icon">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="card-image">
        <img src="../images/visapasport.jpg" alt="Prise de Rendez-vous Roumanie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Prise de Rendez-vous</h3>
        <p>Prenez rendez-vous pour toutes vos démarches administratives en Roumanie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Rendez-vous consulaires</li>
          <li><i class="fas fa-check-circle"></i> Ambassades et consulats</li>
          <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
        </ul>
        <div class="card-actions">
          <a class="roumanie-btn" href="/babylone/rendez_vous.php?pays=roumanie">
            <span>Prendre RDV</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Études Card -->
    <div class="roumanie-card">
      <div class="card-icon">
        <i class="fa-solid fa-graduation-cap"></i>
      </div>
      <div class="card-image">
        <img src="../images/etude.webp" alt="Études en Roumanie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Études</h3>
        <p>Universités, Écoles et Établissements d'enseignement supérieur en Roumanie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa étudiant roumain</li>
          <li><i class="fas fa-check-circle"></i> Inscriptions universitaires</li>
          <li><i class="fas fa-check-circle"></i> Bourses d'études</li>
        </ul>
        <div class="card-actions">
          <a class="roumanie-btn" href="/babylone/public/roumanie/etude/etude.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Tourisme & Affaires Card -->
    <div class="roumanie-card">
      <div class="card-icon">
        <i class="fa-solid fa-plane"></i>
      </div>
      <div class="card-image">
        <img src="../images/tourisme.jpg" alt="Tourisme en Roumanie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Tourisme & Affaires</h3>
        <p>Visa Schengen, voyages d'affaires et séjours touristiques en Roumanie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa Schengen Roumanie</li>
          <li><i class="fas fa-check-circle"></i> Voyages d'affaires</li>
          <li><i class="fas fa-check-circle"></i> Séjours touristiques</li>
        </ul>
        <div class="card-actions">
          <a class="roumanie-btn" href="/babylone/tourisme/tourisme.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Travail Card -->
    <div class="roumanie-card">
      <div class="card-icon">
        <i class="fa-solid fa-briefcase"></i>
      </div>
      <div class="card-image">
        <img src="../images/travail.jpg" alt="Travail en Roumanie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Travail</h3>
        <p>Opportunités professionnelles et démarches pour travailler en Roumanie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa travail roumain</li>
          <li><i class="fas fa-check-circle"></i> Permis de travail</li>
          <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
        </ul>
        <div class="card-actions">
          <a class="roumanie-btn" href="/babylone/public/travail/travail.php">
            <span>Découvrir</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="roumanie-info">
    <div class="info-icon">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="info-content">
      <h4>Important : Visa Roumanie</h4>
      <p>La Roumanie fait partie de l'Union Européenne et offre des opportunités uniques pour les études, le travail et les affaires.</p>
    </div>
  </div>
</div>

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

    document.querySelectorAll('.roumanie-card').forEach(el => {
      observer.observe(el);
    });
  });
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>