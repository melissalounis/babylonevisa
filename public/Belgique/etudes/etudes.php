<?php
require_once __DIR__ . '/../../../config.php';
$page_title = "Études en Belgique — Services";
include __DIR__ . '/../../../includes/header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
  /* Variables couleurs Belgique */
  :root {
    --noir-belgique: #000000;
    --jaune-belgique: #FDDA24;
    --rouge-belgique: #EF3340;
    --blanc-belgique: #ffffff;
    --gris-elegant: #4a4a4a;
    --or-belge: #d4af37;
    --transition: all 0.3s ease;
    --border-radius: 10px;
    --ombre-legere: 0 5px 15px rgba(0, 0, 0, 0.08);
    --ombre-portee: 0 8px 25px rgba(0, 0, 0, 0.12);
  }

  /* Hero Section Études RÉDUITE */
  .etudes-hero {
    background: 
      linear-gradient(135deg, 
        var(--noir-belgique) 0%, 
        var(--noir-belgique) 33%, 
        var(--jaune-belgique) 33%, 
        var(--jaune-belgique) 66%, 
        var(--rouge-belgique) 66%, 
        var(--rouge-belgique) 100%),
      radial-gradient(circle at 30% 70%, rgba(255,255,255,0.1) 0%, transparent 50%),
      radial-gradient(circle at 70% 30%, rgba(255,255,255,0.1) 0%, transparent 50%);
    color: var(--gris-elegant);
    padding: 80px 20px 50px;
    text-align: center;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
    min-height: 40vh;
    display: flex;
    align-items: center;
  }

  .etudes-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--noir-belgique) 33%, var(--jaune-belgique) 33%, var(--jaune-belgique) 66%, var(--rouge-belgique) 66%);
    z-index: 2;
  }

  .etudes-hero-content h1 {
    font-size: 2.2rem;
    margin-bottom: 15px;
    font-weight: 700;
    font-family: 'Playfair Display', serif;
    position: relative;
    display: inline-block;
    background: linear-gradient(135deg, var(--noir-belgique) 30%, var(--jaune-belgique) 50%, var(--rouge-belgique) 70%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .etudes-hero-content h1 i {
    margin-right: 12px;
    color: var(--or-belge);
  }

  .etudes-hero-content p {
    font-size: 1.1rem;
    opacity: 0.9;
    max-width: 500px;
    margin: 20px auto 30px;
    font-weight: 400;
    font-style: italic;
    color: var(--gris-elegant);
    text-shadow: 0 1px 2px rgba(255,255,255,0.8);
  }

  .hero-stats {
    display: flex;
    justify-content: center;
    gap: 40px;
    margin-top: 30px;
    flex-wrap: wrap;
  }

  .stat {
    text-align: center;
    position: relative;
    background: rgba(255, 255, 255, 0.9);
    padding: 15px 25px;
    border-radius: var(--border-radius);
    box-shadow: var(--ombre-legere);
    backdrop-filter: blur(10px);
  }

  .stat:not(:last-child)::after {
    content: '•';
    position: absolute;
    right: -20px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--noir-belgique);
    opacity: 0.7;
    font-size: 1.2rem;
  }

  .stat-number {
    display: block;
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 5px;
    background: linear-gradient(135deg, var(--noir-belgique), var(--jaune-belgique), var(--rouge-belgique));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .stat-label {
    font-size: 0.85rem;
    opacity: 0.9;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--gris-elegant);
  }

  /* Services Container RÉDUIT */
  .services-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px 50px;
  }

  .services-container h2 {
    text-align: center;
    font-size: 2rem;
    margin-bottom: 15px;
    color: var(--gris-elegant);
    position: relative;
    font-weight: 700;
    font-family: 'Playfair Display', serif;
  }

  .services-subtitle {
    text-align: center;
    font-size: 1rem;
    color: #666;
    margin-bottom: 40px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
    font-style: italic;
  }

  .services-container h2::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 2px;
    background: linear-gradient(90deg, var(--noir-belgique), var(--jaune-belgique), var(--rouge-belgique));
  }

  /* Services Grid RÉDUIT */
  .services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
    margin-top: 40px;
  }

  /* Service Cards RÉDUITES */
  .service-card {
    background: var(--blanc-belgique);
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--ombre-legere);
    transition: var(--transition);
    position: relative;
    border: 1px solid #f0f0f0;
  }

  .service-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--ombre-portee);
    border-color: var(--noir-belgique);
  }

  .card-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    background: linear-gradient(135deg, var(--noir-belgique), var(--jaune-belgique), var(--rouge-belgique));
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 3;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: var(--transition);
    border: 2px solid var(--blanc-belgique);
  }

  .service-card:hover .card-icon {
    transform: scale(1.05) rotate(5deg);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3);
  }

  .card-icon i {
    font-size: 20px;
    color: var(--blanc-belgique);
  }

  .card-image {
    height: 160px;
    overflow: hidden;
    position: relative;
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
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, 
      rgba(0, 0, 0, 0.4) 0%, 
      rgba(253, 218, 36, 0.2) 33%, 
      rgba(255, 255, 255, 0.2) 50%, 
      rgba(239, 65, 53, 0.4) 66%);
    opacity: 0.3;
    transition: var(--transition);
  }

  .service-card:hover .card-image img {
    transform: scale(1.1);
  }

  .service-card:hover .card-overlay {
    opacity: 0.5;
  }

  .card-content {
    padding: 25px 20px;
    text-align: center;
    background: linear-gradient(to bottom, var(--blanc-belgique) 0%, #fff9e6 100%);
    position: relative;
  }

  .card-content::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--noir-belgique), var(--jaune-belgique), var(--rouge-belgique));
  }

  .card-content h3 {
    font-size: 1.3rem;
    margin-bottom: 12px;
    color: var(--gris-elegant);
    font-weight: 700;
    font-family: 'Playfair Display', serif;
  }

  .card-content p {
    color: #555;
    margin-bottom: 18px;
    line-height: 1.5;
    font-size: 0.9rem;
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
    color: #444;
    font-size: 0.85rem;
    transition: var(--transition);
  }

  .service-features li:hover {
    color: var(--noir-belgique);
    transform: translateX(3px);
  }

  .service-features i {
    color: var(--rouge-belgique);
    margin-right: 8px;
    font-size: 0.9rem;
    min-width: 14px;
    transition: var(--transition);
  }

  .service-features li:hover i {
    color: var(--jaune-belgique);
    transform: scale(1.1);
  }

  .service-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 25px;
    background: linear-gradient(135deg, var(--noir-belgique) 0%, var(--jaune-belgique) 50%, var(--rouge-belgique) 100%);
    color: var(--blanc-belgique);
    border-radius: 25px;
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
    border: 2px solid transparent;
    gap: 8px;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    position: relative;
    overflow: hidden;
  }

  .service-btn:hover {
    background: transparent;
    color: var(--noir-belgique);
    transform: translateY(-2px);
    border-color: var(--noir-belgique);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
  }

  .service-btn i {
    transition: var(--transition);
  }

  .service-btn:hover i {
    transform: translateX(5px);
    color: var(--rouge-belgique);
  }

  /* Responsive */
  @media (max-width: 768px) {
    .services-grid {
      grid-template-columns: 1fr;
    }
    
    .hero-stats {
      gap: 20px;
      flex-direction: column;
    }
    
    .stat:not(:last-child)::after {
      display: none;
    }
    
    .etudes-hero {
      padding: 60px 20px 40px;
      min-height: auto;
    }
    
    .etudes-hero-content h1 {
      font-size: 1.8rem;
    }
    
    .etudes-hero-content p {
      font-size: 1rem;
    }
    
    .services-container h2 {
      font-size: 1.6rem;
    }
  }

  @media (max-width: 480px) {
    .services-grid {
      grid-template-columns: 1fr;
    }
    
    .service-card {
      margin: 0 10px;
    }
    
    .etudes-hero-content h1 {
      font-size: 1.5rem;
    }
    
    .stat {
      padding: 12px 20px;
    }
    
    .stat-number {
      font-size: 1.5rem;
    }
  }
</style>

<div class="etudes-hero">
  <div class="etudes-hero-content">
    <h1><i class="fas fa-graduation-cap"></i> Études en Belgique</h1>
    <p>Votre porte d'entrée vers l'excellence académique en Belgique</p>
    <div class="hero-stats">
      <div class="stat">
        <span class="stat-number">92%</span>
        <span class="stat-label">d'admission</span>
      </div>
      <div class="stat">
        <span class="stat-number">50+</span>
        <span class="stat-label">universités</span>
      </div>
      <div class="stat">
        <span class="stat-number">100%</span>
        <span class="stat-label">accompagnement</span>
      </div>
    </div>
  </div>
</div>

<div class="services-container">
  <h2>Nos services pour vos études</h2>
  <p class="services-subtitle">Choisissez le service qui correspond à votre besoin</p>
  
  <div class="services-grid">
    <!-- Demande d'Admission -->
    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-file-pen"></i>
      </div>
      <div class="card-image">
        <img src="../../images/admission-belgique.jpg" alt="Demande d'Admission Belgique">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Demande d'Admission</h3>
        <p>Procédures complètes d'inscription dans les établissements belges</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Choix de l'établissement</li>
          <li><i class="fas fa-check-circle"></i> Dossier académique</li>
          <li><i class="fas fa-check-circle"></i> Lettres de motivation</li>
          <li><i class="fas fa-check-circle"></i> Suivi des candidatures</li>
        </ul>
        <a class="service-btn" href="../etude/etude.php">
          <span>Démarrer la demande</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>

    <!-- Demande de Visa Étudiant -->
    <div class="service-card">
      <div class="card-icon">
        <i class="fa-solid fa-passport"></i>
      </div>
      <div class="card-image">
        <img src="../../images/visa-etudiant-belgique.jpg" alt="Visa Étudiant Belgique">
        <div class="card-overlay"></div>
      </div>
      <div class="card-content">
        <h3>Visa Étudiant</h3>
        <p>Obtenez votre visa étudiant pour la Belgique en toute sérénité</p>
        <ul class="service-features">
          <li><i class="fas fa-check-circle"></i> Visa long séjour</li>
          <li><i class="fas fa-check-circle"></i> Documents requis</li>
          <li><i class="fas fa-check-circle"></i> Rendez-vous consulaire</li>
          <li><i class="fas fa-check-circle"></i> Suivi du dossier</li>
        </ul>
        <a class="service-btn" href="visa.php">
          <span>Démarrer la demande</span>
          <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../../includes/footer.php'; ?>