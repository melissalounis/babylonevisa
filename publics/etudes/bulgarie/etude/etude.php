<?php
require_once __DIR__ . '/../../../../config.php';

$page_title = "Bulgarie — Services Études";
include __DIR__ . '/../../../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

/* Hero Section */
.bulgarie-hero {
  background: var(--gradient-bulgarie);
  color: white;
  padding: 80px 0;
  text-align: center;
  position: relative;
  overflow: hidden;
  min-height: 50vh;
  display: flex;
  align-items: center;
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
  font-size: 1.2rem;
  opacity: 0.95;
  max-width: 600px;
  margin: 0 auto 30px;
  font-weight: 300;
  animation: fadeInUp 1s ease-out 0.2s both;
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
  width: 80px;
  height: 55px;
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

/* Services Container */
.services-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 60px 20px;
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

/* Services Grid */
.services-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 30px;
  margin-top: 30px;
  max-width: 1000px;
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
  top: 20px;
  right: 20px;
  background: white;
  width: 60px;
  height: 60px;
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
  font-size: 24px;
  color: var(--bulgarie-green);
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
  top: 20px;
  left: 20px;
  width: 60px;
  height: 42px;
  background: 
    linear-gradient(180deg, 
      var(--bulgarie-white) 0% 33%, 
      var(--bulgarie-green) 33% 66%, 
      var(--bulgarie-red) 66% 100%);
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
  color: var(--bulgarie-green);
  font-weight: 700;
}

.card-content p {
  color: #666;
  margin-bottom: 20px;
  line-height: 1.5;
  font-size: 0.95rem;
}

.service-features {
  text-align: left;
  margin-bottom: 25px;
  padding-left: 0;
}

.service-features li {
  list-style: none;
  margin-bottom: 10px;
  display: flex;
  align-items: center;
  color: #555;
  font-weight: 500;
  font-size: 0.9rem;
}

.service-features i {
  color: var(--bulgarie-green);
  margin-right: 8px;
  font-size: 0.9rem;
  min-width: 16px;
}

.card-actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.bulgarie-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 12px 24px;
  background: var(--gradient-bulgarie);
  color: white;
  border-radius: 25px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
  gap: 8px;
  font-size: 0.95rem;
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

.bulgarie-info {
  background: linear-gradient(135deg, #f0fff8 0%, #e0f7ec 100%);
  border-left: 3px solid var(--bulgarie-green);
  border-radius: 10px;
  padding: 25px;
  display: flex;
  align-items: flex-start;
  gap: 15px;
  margin-top: 50px;
  max-width: 1000px;
  margin-left: auto;
  margin-right: auto;
}

.info-icon {
  font-size: 1.5rem;
  color: var(--bulgarie-green);
}

.info-content h4 {
  color: var(--bulgarie-dark-green);
  margin-bottom: 10px;
  font-size: 1.2rem;
}

.info-content p {
  color: #666;
  line-height: 1.6;
  font-size: 0.95rem;
}

/* Process Section */
.process-section {
  background: white;
  padding: 60px 20px;
  margin: 60px 0;
  border-radius: 16px;
  box-shadow: var(--shadow-bulgarie);
  max-width: 1000px;
  margin-left: auto;
  margin-right: auto;
}

.process-section h2 {
  text-align: center;
  font-size: 2.2rem;
  margin-bottom: 40px;
  color: var(--bulgarie-green);
  position: relative;
}

.process-section h2:after {
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

.process-steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

.process-step {
  background: var(--bulgarie-light);
  padding: 25px;
  border-radius: 12px;
  text-align: center;
  transition: all 0.3s ease;
  position: relative;
}

.process-step:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-bulgarie);
}

.step-number {
  position: absolute;
  top: -15px;
  left: 50%;
  transform: translateX(-50%);
  width: 40px;
  height: 40px;
  background: var(--gradient-bulgarie);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1.2rem;
}

.step-icon {
  width: 70px;
  height: 70px;
  background: rgba(0, 150, 110, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 1.8rem;
  color: var(--bulgarie-green);
}

.process-step h3 {
  color: var(--bulgarie-green);
  margin-bottom: 15px;
  font-size: 1.2rem;
}

.process-step p {
  color: #666;
  line-height: 1.6;
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
@media (max-width: 768px) {
  .bulgarie-hero {
    padding: 60px 0;
    min-height: auto;
  }
  
  .bulgarie-hero-content h1 {
    font-size: 2rem;
  }
  
  .services-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .process-steps {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
  }
  
  .bulgarie-flag-element {
    width: 70px;
    height: 45px;
    top: 10px;
    right: 10px;
  }
}

@media (max-width: 480px) {
  .bulgarie-hero-content h1 {
    font-size: 1.6rem;
  }
  
  .process-steps {
    grid-template-columns: 1fr;
  }
  
  .card-content {
    padding: 20px;
  }
  
  .bulgarie-btn {
    width: 100%;
    justify-content: space-between;
  }
}
</style>

<div class="bulgarie-hero">
  <div class="hero-content-wrapper">
    <div class="bulgarie-hero-content">
      <h1><i class="fas fa-graduation-cap"></i> Études en Bulgarie</h1>
      <p>Services spécialisés pour vos démarches d'études supérieures en Bulgarie</p>
    </div>
  </div>
  <div class="bulgarie-pattern"></div>
  <div class="hero-overlay"></div>
  <div class="bulgarie-flag-element"></div>
</div>

<div class="services-container">
  <h2>Services <span class="text-bulgarie">Études</span></h2>
  <p class="services-subtitle">Choisissez le service qui correspond à votre besoin</p>
  
  <div class="services-grid">
    <!-- Demande d'Admission -->
    <div class="bulgarie-card">
      <div class="card-icon">
        <i class="fa-solid fa-university"></i>
      </div>
      <div class="card-image">
        <img src="../../../images/admission.jpg" alt="Demande d'Admission Université Bulgarie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Demande d'Admission</h3>
        <p>Admission dans les universités bulgares avec un taux de réussite élevé</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Choix de l'université et programme</li>
          <li><i class="fas fa-check-circle"></i> Préparation du dossier académique</li>
          <li><i class="fas fa-check-circle"></i> Procédure d'inscription simplifiée</li>
          <li><i class="fas fa-check-circle"></i> Taux de réussite élevé</li>
        </ul>
        <div class="card-actions">
          <a class="bulgarie-btn" href="admission.php">
            <span>Démarrer la demande</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- Demande de Visa Étude -->
    <div class="bulgarie-card">
      <div class="card-icon">
        <i class="fa-solid fa-passport"></i>
      </div>
      <div class="card-image">
        <img src="../../../images/visa.jpg" alt="Demande de Visa Étude Bulgarie">
        <div class="card-overlay"></div>
        <div class="card-flag"></div>
      </div>
      <div class="card-content">
        <h3>Demande de Visa Étude</h3>
        <p>Obtenez votre visa étudiant pour poursuivre vos études en Bulgarie</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Assistance complète visa étudiant</li>
          <li><i class="fas fa-check-circle"></i> Préparation des documents requis</li>
          <li><i class="fas fa-check-circle"></i> Suivi du dossier jusqu'à obtention</li>
          <li><i class="fas fa-check-circle"></i> Conseils pour l'entretien</li>
        </ul>
        <div class="card-actions">
          <a class="bulgarie-btn" href="../../etudes/visa.php">
            <span>Démarrer la demande</span>
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="process-section">
    <h2>Processus de <span class="text-bulgarie">Demande</span></h2>
    <div class="process-steps">
      <div class="process-step">
        <div class="step-number">1</div>
        <div class="step-icon">
          <i class="fas fa-file-alt"></i>
        </div>
        <h3>Évaluation</h3>
        <p>Analyse de votre profil académique et conseils personnalisés</p>
      </div>
      <div class="process-step">
        <div class="step-number">2</div>
        <div class="step-icon">
          <i class="fas fa-clipboard-list"></i>
        </div>
        <h3>Dossier</h3>
        <p>Préparation complète de votre dossier de candidature</p>
      </div>
      <div class="process-step">
        <div class="step-number">3</div>
        <div class="step-icon">
          <i class="fas fa-paper-plane"></i>
        </div>
        <h3>Soumission</h3>
        <p>Envoi de votre dossier à l'université et suivi</p>
      </div>
      <div class="process-step">
        <div class="step-number">4</div>
        <div class="step-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h3>Acceptation</h3>
        <p>Réception de votre lettre d'acceptation universitaire</p>
      </div>
    </div>
  </div>

  <div class="bulgarie-info">
    <div class="info-icon">
      <i class="fas fa-info-circle"></i>
    </div>
    <div class="info-content">
      <h4>Important : Études en Bulgarie</h4>
      <p>La Bulgarie offre un système éducatif de qualité avec des universités reconnues internationalement, particulièrement réputées pour la médecine, l'ingénierie et l'informatique. Les frais de scolarité sont compétitifs et de nombreuses formations sont disponibles en anglais. Notre équipe vous accompagne dans toutes vos démarches pour maximiser vos chances de réussite.</p>
    </div>
  </div>
</div>

<script>
  // Animation au chargement de la page
  document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.bulgarie-card');
    cards.forEach((card, index) => {
      card.style.animation = `fadeInUp 0.6s ease-out ${index * 0.2}s both`;
    });
  });
</script>

<?php include __DIR__ . '/../../../../includes/footer.php'; ?>