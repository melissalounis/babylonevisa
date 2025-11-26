<?php
require_once __DIR__ . '/../config.php';
$page_title = "Nos Services - Babylone Service";
include __DIR__ . '/../includes/header.php';
?>

<style>
    /* --- Variables CSS --- */
    :root {
        --primary-blue: #1a5f7a;
        --secondary-blue: #3d8bb6;
        --accent-green: #2ecc71;
        --light-blue: #e6f7ff;
        --white: #ffffff;
        --dark-text: #2c3e50;
        --light-gray: #ecf0f1;
        --medium-blue: #3498db;
        --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --box-shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.15);
        --border-radius: 16px;
        --hotel-color: #e74c3c;
        --billet-color: #9b59b6;
        --langue-color: #f39c12;
    }
    
    /* --- Animations --- */
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
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    
    /* --- Hero Section Services --- */
   .services-hero {
    position: relative;
    height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-align: center;
    overflow: hidden;
    background: url('images/service.jpg') no-repeat center/cover;
    margin-top: -40px;
}
    
    .services-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(67, 87, 124, 0.4);
        z-index: 1;
    }
    
    .services-hero-content {
        max-width: 800px;
        padding: 0 20px;
        z-index: 2;
        animation: fadeInUp 1s ease-out;
    }
    
    .services-hero-content h1 {
        font-size: 3.5rem;
        margin-bottom: 20px;
        font-weight: 800;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        letter-spacing: -0.5px;
    }
    
    .services-hero-content p {
        font-size: 1.5rem;
        margin-bottom: 30px;
        font-weight: 300;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }
    
    /* Services Section */
    .services-section {
        padding: 100px 20px;
        background: var(--light-blue);
        text-align: center;
        position: relative;
        width: 100%;
        box-sizing: border-box;
    }
    
    .section-title {
        font-size: 2.5rem;
        margin-bottom: 50px;
        color: var(--primary-blue);
        position: relative;
        display: inline-block;
        font-weight: 700;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: -15px;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 4px;
        background: var(--accent-green);
        border-radius: 2px;
    }
    
    .services-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    /* Classes spécifiques pour les images de fond des services */
    .service-card-immigration {
        background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/immigration.jpg');
    }
    
    .service-card-tourisme {
        background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/vol.avif');
    }
    
    .service-card-etudes {
        background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/etudiant.avif');
    }
    
    .service-card-travail {
        background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/travail.jpg');
    }

    .service-card-regroupement {
        background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/famile2.jpg');
    }

    .service-card-hotel {
        background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/hotel.jpg');
    }

    .service-card-billet {
        background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/billet.jpg');
    }

    .service-card-langues {
        background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('images/test.png');
    }

    /* Styles communs pour toutes les cartes de services */
    .service-card-immigration,
    .service-card-tourisme,
    .service-card-etudes,
    .service-card-travail,
    .service-card-regroupement,
    .service-card-hotel,
    .service-card-billet,
    .service-card-langues {
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        transition: var(--transition);
        text-align: center;
        position: relative;
        overflow: hidden;
        min-height: 380px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        width: 100%;
        box-sizing: border-box;
        text-decoration: none;
        color: inherit;
    }
    
    /* Overlay pour améliorer la lisibilité */
    .service-card-immigration::before,
    .service-card-tourisme::before,
    .service-card-etudes::before,
    .service-card-travail::before,
    .service-card-regroupement::before,
    .service-card-hotel::before,
    .service-card-billet::before,
    .service-card-langues::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, 
                    rgba(26, 95, 122, 0.1) 0%, 
                    rgba(26, 95, 122, 0.3) 50%,
                    rgba(26, 95, 122, 0.6) 100%);
        z-index: 1;
        transition: var(--transition);
        border-radius: 20px;
    }
    
    .service-card-hotel::before {
        background: linear-gradient(to bottom, 
                   rgba(155, 89, 182, 0.1) 0%, 
                    rgba(155, 89, 182, 0.3) 50%,
                    rgba(155, 89, 182, 0.6) 100%);
    }
                  

    .service-card-billet::before {
        background: linear-gradient(to bottom, 
                    rgba(155, 89, 182, 0.1) 0%, 
                    rgba(155, 89, 182, 0.3) 50%,
                    rgba(155, 89, 182, 0.6) 100%);
    }

    .service-card-langues::before {
        background: linear-gradient(to bottom, 
                    rgba(243, 156, 18, 0.1) 0%, 
                    rgba(243, 156, 18, 0.3) 50%,
                    rgba(243, 156, 18, 0.6) 100%);
    }
    
    .service-card-immigration > *,
    .service-card-tourisme > *,
    .service-card-etudes > *,
    .service-card-travail > *,
    .service-card-regroupement > *,
    .service-card-hotel > *,
    .service-card-billet > *,
    .service-card-langues > * {
        position: relative;
        z-index: 2;
    }
    
    .service-card-immigration:hover,
    .service-card-tourisme:hover,
    .service-card-etudes:hover,
    .service-card-travail:hover,
    .service-card-regroupement:hover,
    .service-card-hotel:hover,
    .service-card-billet:hover,
    .service-card-langues:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: var(--box-shadow-hover);
    }
    
    .service-card-immigration:hover::before,
    .service-card-tourisme:hover::before,
    .service-card-etudes:hover::before,
    .service-card-travail:hover::before,
    .service-card-regroupement:hover::before {
        background: linear-gradient(to bottom, 
                    rgba(46, 204, 113, 0.1) 0%, 
                    rgba(46, 204, 113, 0.3) 50%,
                    rgba(46, 204, 113, 0.6) 100%);
    }

    .service-card-hotel:hover::before {
        background: linear-gradient(to bottom, 
                    rgba(46, 204, 113, 0.1) 0%, 
                    rgba(46, 204, 113, 0.3) 50%,
                    rgba(46, 204, 113, 0.6) 100%);
    }

    .service-card-billet:hover::before {
        background: linear-gradient(to bottom, 
                    rgba(46, 204, 113, 0.1) 0%, 
                    rgba(46, 204, 113, 0.3) 50%,
                    rgba(46, 204, 113, 0.6) 100%);
    }

    .service-card-langues:hover::before {
        background: linear-gradient(to bottom, 
                    rgba(46, 204, 113, 0.1) 0%, 
                    rgba(46, 204, 113, 0.3) 50%,
                    rgba(46, 204, 113, 0.6) 100%);
    }
    
    /* Styles du contenu des cartes */
    .service-card-immigration i,
    .service-card-tourisme i,
    .service-card-etudes i,
    .service-card-travail i,
    .service-card-regroupement i,
    .service-card-hotel i,
    .service-card-billet i,
    .service-card-langues i {
        font-size: 3rem;
        margin-bottom: 25px;
        display: block;
        color: var(--white);
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
        transition: var(--transition);
    }
    
    .service-card-immigration:hover i,
    .service-card-tourisme:hover i,
    .service-card-etudes:hover i,
    .service-card-travail:hover i,
    .service-card-regroupement:hover i,
    .service-card-hotel:hover i,
    .service-card-billet:hover i,
    .service-card-langues:hover i {
        animation: pulse 1s ease-in-out;
    }
    
    .service-card-immigration h3,
    .service-card-tourisme h3,
    .service-card-etudes h3,
    .service-card-travail h3,
    .service-card-regroupement h3,
    .service-card-hotel h3,
    .service-card-billet h3,
    .service-card-langues h3 {
        font-size: 1.8rem;
        margin-bottom: 20px;
        color: var(--white);
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
        line-height: 1.3;
    }
    
    .service-card-immigration p,
    .service-card-tourisme p,
    .service-card-etudes p,
    .service-card-travail p,
    .service-card-regroupement p,
    .service-card-hotel p,
    .service-card-billet p,
    .service-card-langues p {
        margin-bottom: 25px;
        font-size: 1.1rem;
        line-height: 1.7;
        color: var(--white);
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
    }
    
    .service-features {
        text-align: left;
        margin-top: 25px;
    }
    
    .service-features li {
        margin-bottom: 12px;
        list-style-type: none;
        padding-left: 30px;
        position: relative;
        font-size: 1.05rem;
        color: var(--white);
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
        line-height: 1.5;
    }
    
    .service-features li::before {
        content: '✓';
        position: absolute;
        left: 0;
        font-weight: bold;
        font-size: 1.3rem;
        color: var(--accent-green);
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.7);
    }

  
    /* Process Section */
    .process-section {
        padding: 100px 20px;
        background: var(--white);
        text-align: center;
    }
    
    .process-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .process-step {
        background: var(--light-blue);
        padding: 30px;
        border-radius: var(--border-radius);
        text-align: center;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
    }
    
    .process-step:hover {
        transform: translateY(-5px);
        box-shadow: var(--box-shadow-hover);
    }
    
    .process-step i {
        font-size: 3rem;
        color: var(--primary-blue);
        margin-bottom: 20px;
        transition: var(--transition);
    }
    
    .process-step:hover i {
        transform: scale(1.1);
        color: var(--accent-green);
    }
    
    .process-step h3 {
        font-size: 1.5rem;
        margin-bottom: 15px;
        color: var(--primary-blue);
    }
    
    .process-step p {
        color: var(--dark-text);
        line-height: 1.6;
    }
    
    .step-number {
        display: inline-block;
        width: 40px;
        height: 40px;
        background: var(--accent-green);
        color: var(--white);
        border-radius: 50%;
        line-height: 40px;
        margin-bottom: 15px;
        font-weight: bold;
        transition: var(--transition);
    }
    
    .process-step:hover .step-number {
        transform: scale(1.1);
        background: var(--primary-blue);
    }
    
   

    /* Statistiques */
    .stats-section {
        padding: 80px 20px;
        background: var(--white);
        text-align: center;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 30px;
        max-width: 1000px;
        margin: 0 auto;
    }

    .stat-item {
        padding: 30px;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: bold;
        color: var(--primary-blue);
        display: block;
        margin-bottom: 10px;
    }

    .stat-label {
        color: var(--dark-text);
        font-size: 1.1rem;
    }
    
    /* --- Responsive --- */
    @media (max-width: 1200px) {
        .services-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 992px) {
        .services-hero-content h1 {
            font-size: 2.8rem;
        }
        
        .services-hero-content p {
            font-size: 1.2rem;
        }

        .services-container {
            gap: 20px;
        }
    }
    
    @media (max-width: 768px) {
        .services-hero-content h1 {
            font-size: 2.2rem;
        }
        
        .section-title {
            font-size: 2rem;
        }
        
        .services-container {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .service-card-immigration,
        .service-card-tourisme,
        .service-card-etudes,
        .service-card-travail,
        .service-card-regroupement,
        .service-card-hotel,
        .service-card-billet,
        .service-card-langues {
            min-height: 350px;
            padding: 30px 20px;
        }

        .service-features li {
            font-size: 1rem;
        }

        .process-steps {
            grid-template-columns: 1fr;
            gap: 20px;
        }
    }
    
    @media (max-width: 576px) {
        .services-hero {
            height: 50vh;
        }
        
        .services-hero-content h1 {
            font-size: 1.8rem;
        }
        
        .services-hero-content p {
            font-size: 1rem;
        }
        
        .cta-section h2 {
            font-size: 2rem;
        }
        
        .cta-section p {
            font-size: 1rem;
        }

        .service-card-immigration h3,
        .service-card-tourisme h3,
        .service-card-etudes h3,
        .service-card-travail h3,
        .service-card-regroupement h3,
        .service-card-hotel h3,
        .service-card-billet h3,
        .service-card-langues h3 {
            font-size: 1.5rem;
        }
    }
</style>

<!-- Hero Section Services -->
<section class="services-hero">
    <div class="services-hero-overlay"></div>
    <div class="services-hero-content">
        <h1>Nos Services</h1>
        <p>Découvrez toutes nos solutions pour concrétiser vos projets à l'international</p>
    </div>
</section>

<!-- Services Section -->
<section class="services-section">
    <h2 class="section-title">Ce que nous vous proposons</h2>
    <div class="services-container">
        <a href="../publics/immigration/pays.php" class="service-card-immigration">
            <i class="fas fa-passport"></i>
            <h3>Immigration</h3>
            <p>Obtenez votre visa et réalisez votre projet d'immigration en toute sérénité.</p>
            <ul class="service-features">
                <li>Demandes de visa</li>
                <li>Certificat de visa</li>
                <li>Résidence permanente</li>
                <li>Citoyenneté</li>
            </ul>
        </a>

        <a href="../publics/tourisme/pays.php" class="service-card-tourisme">
            <i class="fas fa-plane"></i>
            <h3>Voyages & Tourisme</h3>
            <p>Organisez le voyage de vos rêves avec nos services complets.</p>
            <ul class="service-features">
                <li>Billets d'avion</li>
                <li>Réservations d'hôtel</li>
                <li>Circuits touristiques</li>
                <li>Assurances voyage</li>
            </ul>
        </a>

        <a href="../publics/etudes/pays.php" class="service-card-etudes">
            <i class="fas fa-graduation-cap"></i>
            <h3>Études à l'étranger</h3>
            <p>Étudiez dans les meilleures universités à l'international.</p>
            <ul class="service-features">
                <li>Admissions universitaires</li>
                <li>Bourses d'études</li>
                <li>Hébergement étudiant</li>
                <li>Orientation académique</li>
            </ul>
        </a>

        <a href="../publics/travail/pays.php" class="service-card-travail">
            <i class="fas fa-briefcase"></i>
            <h3>Emploi à l'étranger</h3>
            <p>Trouvez l'emploi de vos rêves à l'international.</p>
            <ul class="service-features">
                <li>Permis de travail</li>
                <li>Recherche d'emploi</li>
                <li>CV et préparation aux entretiens</li>
                <li>Reconnaissance des diplômes</li>
            </ul>
        </a>

        <a href="../publics/famille/pays.php" class="service-card-regroupement">
            <i class="fas fa-users"></i>
            <h3>Regroupement Familial</h3>
            <p>Faites venir votre famille vous rejoindre en toute légalité.</p>
            <ul class="service-features">
                <li>Conjoint et enfants mineurs</li>
                <li>Résidence régulière requise</li>
                <li>Conditions de ressources</li>
                <li>Procédure accompagnée</li>
            </ul>
        </a>

        <!-- Nouveau service: Réservation d'Hôtel -->
        <a href="réservation hotel.php" class="service-card-hotel">
            <i class="fas fa-hotel"></i>
            <h3>Réservation d'Hôtel</h3>
            <p>Trouvez l'hébergement parfait pour votre séjour où que vous soyez.</p>
            <ul class="service-features">
                <li>Hôtels de luxe et économiques</li>
                <li>Meilleurs prix garantis</li>
            </ul>
        </a>

        <!-- Nouveau service: Réservation de Billet -->
        <a href="réservation billet.php" class="service-card-billet">
            <i class="fas fa-ticket-alt"></i>
            <h3>Réservation de Billets</h3>
            <p>Accédez aux meilleurs tarifs pour tous vos déplacements.</p>
            <ul class="service-features">
                <li>Billets d'avion au meilleur prix</li>
            </ul>
        </a>

        <!-- Nouveau service: Test de Langues -->
        <a href="test_langues.php" class="service-card-langues">
            <i class="fas fa-language"></i>
            <h3>Test de Langues</h3>
            <p>Préparez et passez vos tests de langue officiels pour vos projets internationaux.</p>
            <ul class="service-features">
                <li>TOEFL, IELTS, DELF, DALF</li>
                <li>Préparation aux examens</li>
                <li>Inscriptions facilitées</li>
                <li>Centres agréés</li>
            </ul>
        </a>
    </div>
</section>

<!-- Section Statistiques -->
<section class="stats-section">
    <div class="stats-container">
        <div class="stat-item">
            <span class="stat-number">1000+</span>
            <span class="stat-label">Dossiers traités</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">85%</span>
            <span class="stat-label">Taux de réussite</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">15+</span>
            <span class="stat-label">Pays couverts</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">24h</span>
            <span class="stat-label">Réponse garantie</span>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>