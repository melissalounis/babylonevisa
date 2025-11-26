<?php
require_once __DIR__ . '/../../../../config.php';

$page_title = "Roumanie — Services d'Études";
include __DIR__ . '/../../../../includes/header.php';
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

.text-roumanie {
  background: var(--gradient-roumanie);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 700;
}

/* Hero Section */
.roumanie-hero {
  background: var(--gradient-roumanie);
  color: white;
  padding: 100px 0;
  text-align: center;
  position: relative;
  overflow: hidden;
  min-height: 80vh;
  display: flex;
  align-items: center;
}

.hero-content-wrapper {
  position: relative;
  z-index: 3;
  width: 100%;
}

.roumanie-hero-content h1 {
  font-size: 2.8rem;
  margin-bottom: 15px;
  font-weight: 800;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  animation: fadeInUp 1s ease-out;
}

.roumanie-hero-content h1 i {
  margin-right: 15px;
  color: var(--roumanie-yellow);
  animation: pulse 2s infinite;
  text-shadow: 0 0 10px rgba(252, 209, 22, 0.5);
}

.roumanie-hero-content p {
  font-size: 1.2rem;
  opacity: 0.95;
  max-width: 600px;
  margin: 0 auto 30px;
  font-weight: 300;
  animation: fadeInUp 1s ease-out 0.2s both;
}

.hero-stats {
  display: flex;
  justify-content: center;
  gap: 50px;
  margin: 40px 0;
  flex-wrap: wrap;
  animation: fadeInUp 1s ease-out 0.4s both;
}

.stat {
  text-align: center;
  position: relative;
}

.stat-number {
  display: block;
  font-size: 2.2rem;
  font-weight: 800;
  margin-bottom: 5px;
  color: white;
  transition: all 0.5s ease;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.stat-label {
  font-size: 1rem;
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
  padding: 12px 25px;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  text-decoration: none;
  border-radius: 50px;
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.3);
  transition: all 0.3s ease;
  font-weight: 600;
  font-size: 0.95rem;
}

.cta-button:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
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

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, 
    rgba(0, 43, 127, 0.8) 0%, 
    rgba(206, 17, 38, 0.8) 50%, 
    rgba(252, 209, 22, 0.8) 100%);
  z-index: 2;
}

.roumanie-flag-element {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 80px;
  height: 55px;
  background: 
    linear-gradient(180deg, 
      var(--roumanie-blue) 0% 33%, 
      var(--roumanie-yellow) 33% 66%, 
      var(--roumanie-red) 66% 100%);
  z-index: 3;
  opacity: 0.8;
  border-radius: 3px;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
  animation: float 3s ease-in-out infinite;
}

/* Services Container */
.services-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 60px 20px;
}

.services-container h2 {
  text-align: center;
  font-size: 2.2rem;
  margin-bottom: 15px;
  color: var(--roumanie-blue);
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
  background: var(--gradient-roumanie);
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

/* Services Grid - 2 cartes pour les services d'études */
.services-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 30px;
  margin-top: 30px;
  max-width: 1200px;
  margin-left: auto;
  margin-right: auto;
}

.roumanie-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: var(--shadow-roumanie);
  transition: all 0.3s ease;
  position: relative;
  border: 1px solid rgba(0, 43, 127, 0.1);
  height: fit-content;
}

.roumanie-card:hover {
  transform: translateY(-5px) scale(1.02);
  box-shadow: var(--shadow-roumanie-hover);
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
  box-shadow: 0 4px 12px rgba(0, 43, 127, 0.2);
  transition: all 0.3s ease;
}

.roumanie-card:hover .card-icon {
  transform: scale(1.05) rotate(5deg);
  background: var(--gradient-roumanie);
}

.roumanie-card:hover .card-icon i {
  color: white;
}

.card-icon i {
  font-size: 20px;
  color: var(--roumanie-blue);
  transition: all 0.3s ease;
}

.card-image {
  height: 200px;
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
  background: linear-gradient(to bottom, transparent 0%, rgba(0, 43, 127, 0.7) 100%);
  transition: all 0.3s ease;
}

.roumanie-card:hover .card-image img {
  transform: scale(1.05);
}

.roumanie-card:hover .card-overlay {
  background: linear-gradient(to bottom, transparent 0%, var(--roumanie-blue) 100%);
}

.card-flag {
  position: absolute;
  top: 12px;
  left: 12px;
  width: 50px;
  height: 35px;
  background: 
    linear-gradient(180deg, 
      var(--roumanie-blue) 0% 33%, 
      var(--roumanie-yellow) 33% 66%, 
      var(--roumanie-red) 66% 100%);
  border-radius: 3px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.card-content {
  padding: 25px;
  text-align: center;
}

.card-content h3 {
  font-size: 1.4rem;
  margin-bottom: 15px;
  color: var(--roumanie-blue);
  font-weight: 700;
}

.card-content p {
  color: #666;
  margin-bottom: 15px;
  line-height: 1.5;
  font-size: 0.95rem;
}

.service-features {
  text-align: left;
  margin-bottom: 20px;
  padding-left: 0;
}

.service-features li {
  list-style: none;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  color: #555;
  font-weight: 500;
  font-size: 0.9rem;
}

.service-features i {
  color: var(--roumanie-red);
  margin-right: 8px;
  font-size: 0.9rem;
  min-width: 16px;
}

.card-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.roumanie-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 20px;
  background: var(--gradient-roumanie);
  color: white;
  border-radius: 20px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
  gap: 8px;
  font-size: 0.9rem;
  cursor: pointer;
}

.roumanie-btn:hover {
  background: var(--gradient-roumanie-light);
  transform: translateY(-2px);
  box-shadow: 0 6px 15px rgba(0, 43, 127, 0.3);
}

.roumanie-btn i {
  transition: transform 0.3s ease;
}

.roumanie-btn:hover i {
  transform: translateX(3px);
}

.roumanie-info {
  background: linear-gradient(135deg, #fff0f0 0%, #ffe0e0 100%);
  border-left: 3px solid var(--roumanie-red);
  border-radius: 10px;
  padding: 20px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
  margin-top: 40px;
}

.info-icon {
  font-size: 1.5rem;
  color: var(--roumanie-red);
}

.info-content h4 {
  color: var(--roumanie-dark-blue);
  margin-bottom: 8px;
  font-size: 1.1rem;
}

.info-content p {
  color: #666;
  line-height: 1.5;
  font-size: 0.9rem;
}

/* Section Universités */
.universities-section {
  background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
  padding: 60px 20px;
  margin-top: 40px;
}

.universities-container {
  max-width: 1200px;
  margin: 0 auto;
}

.universities-container h2 {
  text-align: center;
  font-size: 2.2rem;
  margin-bottom: 15px;
  color: var(--roumanie-blue);
  position: relative;
  font-weight: 700;
}

.universities-container h2:after {
  content: '';
  position: absolute;
  bottom: -12px;
  left: 50%;
  transform: translateX(-50%);
  width: 80px;
  height: 3px;
  background: var(--gradient-roumanie);
  border-radius: 2px;
}

.universities-subtitle {
  text-align: center;
  font-size: 1rem;
  color: #666;
  margin-bottom: 40px;
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

.universities-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 25px;
}

.university-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  border: 1px solid rgba(0, 43, 127, 0.1);
}

.university-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0, 43, 127, 0.15);
}

.university-image {
  height: 160px;
  overflow: hidden;
}

.university-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.4s ease;
}

.university-card:hover .university-image img {
  transform: scale(1.05);
}

.university-content {
  padding: 20px;
}

.university-content h3 {
  font-size: 1.2rem;
  margin-bottom: 10px;
  color: var(--roumanie-blue);
  font-weight: 700;
}

.university-content p {
  color: #666;
  font-size: 0.9rem;
  line-height: 1.5;
  margin-bottom: 15px;
}

.university-features {
  display: flex;
  justify-content: space-between;
  margin-top: 15px;
}

.feature {
  display: flex;
  align-items: center;
  gap: 5px;
  color: #666;
  font-size: 0.85rem;
}

.feature i {
  color: var(--roumanie-red);
  font-size: 0.9rem;
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
@media (max-width: 900px) {
  .services-grid {
    grid-template-columns: 1fr;
    gap: 25px;
    max-width: 600px;
  }
  
  .universities-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .roumanie-hero {
    padding: 70px 0;
    min-height: auto;
  }
  
  .roumanie-hero-content h1 {
    font-size: 2rem;
  }
  
  .roumanie-hero-content p {
    font-size: 1.1rem;
  }
  
  .hero-stats {
    gap: 25px;
  }
  
  .stat-number {
    font-size: 1.8rem;
  }
  
  .services-container {
    padding: 50px 20px;
  }
  
  .services-container h2 {
    font-size: 1.8rem;
  }
  
  .roumanie-flag-element {
    width: 70px;
    height: 45px;
    top: 10px;
    right: 10px;
  }
  
  .universities-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .roumanie-hero-content h1 {
    font-size: 1.6rem;
  }
  
  .hero-stats {
    flex-direction: column;
    gap: 15px;
  }
  
  .services-container h2 {
    font-size: 1.5rem;
  }
  
  .card-content {
    padding: 20px;
  }
  
  .roumanie-btn {
    width: 100%;
    justify-content: space-between;
  }
}
</style>

<div class="roumanie-hero">
  <div class="hero-content-wrapper">
    <div class="roumanie-hero-content">
      <h1><i class="fas fa-graduation-cap"></i> Études en Roumanie</h1>
      <p>Poursuivez vos études supérieures dans les prestigieuses universités roumaines</p>
      <div class="hero-stats">
        <div class="stat">
          <span class="stat-number" data-count="50">0</span>
          <span class="stat-label">universités partenaires</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="1000">0</span>
          <span class="stat-label">étudiants accompagnés</span>
        </div>
        <div class="stat">
          <span class="stat-number" data-count="95">0</span>
          <span class="stat-label">de réussite</span>
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
  <h2>Services d'<span class="text-roumanie">Études</span> en Roumanie</h2>
  <p class="services-subtitle">Accompagnement complet pour vos études supérieures en Roumanie</p>
  
  <div class="services-grid">
    <!-- Demande d'Admission Card -->
    <div class="roumanie-card">
      <div class="card-icon">
        <i class="fa-solid fa-file-pen"></i>
      </div>
      <div class="card-image">
        <img src="../../../images/admission.jpg" alt="Demande d'Admission en Roumanie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Demande d'Admission</h3>
        <p>Inscription dans les universités roumaines avec accompagnement personnalisé</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Choix de l'université et programme</li>
          <li><i class="fas fa-check-circle"></i> Préparation du dossier d'admission</li>
          <li><i class="fas fa-check-circle"></i> Traduction et légalisation des documents</li>
          <li><i class="fas fa-check-circle"></i> Suivi jusqu'à l'acceptation</li>
          <li><i class="fas fa-check-circle"></i> Assistance pour l'hébergement</li>
        </ul>
        <div class="card-actions">
          <a class="roumanie-btn" href="/babylone/public/roumanie/etude/admission.php">
            <span>Déposer une demande</span>
            <i class="fas fa-arrow-right"></i>
          </a>

        </div>
      </div>
    </div>

    <!-- Demande de Visa Étude Card -->
    <div class="roumanie-card">
      <div class="card-icon">
        <i class="fa-solid fa-passport"></i>
      </div>
      <div class="card-image">
        <img src="../../../images/visa.jpg" alt="Visa Étude Roumanie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Demande de Visa Étude</h3>
        <p>Obtention du visa étudiant pour la Roumanie avec assistance complète</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Préparation du dossier de visa</li>
          <li><i class="fas fa-check-circle"></i> Prise de rendez-vous au consulat</li>
          <li><i class="fas fa-check-circle"></i> Assistance pour l'assurance santé</li>
          <li><i class="fas fa-check-circle"></i> Justificatifs financiers</li>
          <li><i class="fas fa-check-circle"></i> Suivi jusqu'à l'obtention du visa</li>
        </ul>
        <div class="card-actions">
          <a class="roumanie-btn" href="/babylone/public/roumanie/etude/visa.php">
            <span>Demander un visa</span>
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
      <h4>Important : Études en Roumanie</h4>
      <p>La Roumanie dispose d'un système d'enseignement supérieur de qualité avec des frais de scolarité abordables. Les diplômes roumains sont reconnus dans toute l'Union Européenne.</p>
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
  });
</script>

<?php include __DIR__ . '/../../../../includes/footer.php'; ?>