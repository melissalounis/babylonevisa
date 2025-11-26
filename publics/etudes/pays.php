<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Destinations - Babylone Service";
include __DIR__ . '/../../includes/header.php';
?>

<style>
    /* --- Variables CSS étendues --- */
    :root {
        --primary-blue: #1a5f7a;
        --secondary-blue: #3d8bb6;
        --accent-green: #2ecc71;
        --accent-orange: #e67e22;
        --light-blue: #e6f7ff;
        --white: #ffffff;
        --dark-text: #2c3e50;
        --light-gray: #ecf0f1;
        --medium-blue: #3498db;
        --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        --box-shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.15);
        --border-radius: 16px;
    }
    
    /* --- Animations améliorées --- */
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
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    /* --- Hero Section améliorée --- */
    .procedure-hero {
        position: relative;
        height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
        overflow: hidden;
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        margin-top: -40px;
        background:url('../images/etudiant.avif');

    }
    
    .procedure-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }
    
    .procedure-hero-content {
        max-width: 900px;
        padding: 0 20px;
        z-index: 2;
        animation: fadeInUp 1s ease-out;
    }
    
    .procedure-hero-content h1 {
        font-size: 3.5rem;
        margin-bottom: 20px;
        font-weight: 800;
        text-shadow: 2px 2px 12px rgba(0, 0, 0, 0.4);
        letter-spacing: -0.5px;
    }
    
    .procedure-hero-content p {
        font-size: 1.5rem;
        margin-bottom: 30px;
        font-weight: 300;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }
    
    /* Stats Section nouvelle */
    .stats-section {
        padding: 60px 20px;
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
        padding: 30px 20px;
        background: var(--light-blue);
        border-radius: var(--border-radius);
        transition: var(--transition);
    }
    
    .stat-item:hover {
        transform: translateY(-5px);
        box-shadow: var(--box-shadow);
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        color: var(--primary-blue);
        display: block;
        margin-bottom: 10px;
    }
    
    .stat-label {
        color: var(--dark-text);
        font-size: 1rem;
        font-weight: 600;
    }
    
    /* Process Section améliorée */
    .process-section {
        padding: 100px 20px;
        background: var(--light-blue);
        text-align: center;
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
    
    .process-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .process-step {
        background: var(--white);
        padding: 40px 30px;
        border-radius: var(--border-radius);
        text-align: center;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    
    .process-step::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-blue), var(--accent-green));
    }
    
    .process-step:hover {
        transform: translateY(-10px);
        box-shadow: var(--box-shadow-hover);
    }
    
    .process-step:hover i {
        animation: pulse 1s ease-in-out;
        color: var(--accent-green);
    }
    
    .step-number {
        display: inline-block;
        width: 50px;
        height: 50px;
        background: var(--primary-blue);
        color: var(--white);
        border-radius: 50%;
        line-height: 50px;
        margin-bottom: 20px;
        font-weight: bold;
        font-size: 1.2rem;
        transition: var(--transition);
    }
    
    .process-step:hover .step-number {
        background: var(--accent-green);
        transform: scale(1.1);
    }
    
    .process-step i {
        font-size: 2.5rem;
        color: var(--primary-blue);
        margin-bottom: 20px;
        display: block;
        transition: var(--transition);
    }
    
    .process-step h3 {
        font-size: 1.4rem;
        margin-bottom: 15px;
        color: var(--primary-blue);
    }
    
    .process-step p {
        color: var(--dark-text);
        line-height: 1.6;
        font-size: 1rem;
    }
    
    /* Countries Section améliorée */
    .countries-section {
        padding: 100px 20px;
        background: var(--white);
        text-align: center;
    }
    
    .filters {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 50px;
        padding: 0 20px;
    }
    
    .filter-btn {
        padding: 12px 25px;
        background: var(--white);
        color: var(--primary-blue);
        border: 2px solid var(--primary-blue);
        border-radius: 50px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 600;
        font-size: 1rem;
    }
    
    .filter-btn:hover, .filter-btn.active {
        background: var(--primary-blue);
        color: var(--white);
        transform: translateY(-2px);
    }
    
    .countries {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .country {
        height: 200px;
        border-radius: var(--border-radius);
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        color: var(--white);
        display: block;
        box-shadow: var(--box-shadow);
        border: 3px solid transparent;
    }
    
    .country::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(26, 95, 122, 0.9) 0%, rgba(26, 95, 122, 0.4) 50%, rgba(26, 95, 122, 0.2) 100%);
        border-radius: var(--border-radius);
        transition: var(--transition);
    }
    
    .country-label {
        position: absolute;
        bottom: 20px;
        left: 20px;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 700;
        font-size: 1.3rem;
        transition: var(--transition);
        text-align: left;
    }
    
    .flag-icon {
        border-radius: 6px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        transition: var(--transition);
        width: 40px;
        height: 30px;
        object-fit: cover;
    }
    
    .country-duration {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 2;
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary-blue);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: var(--transition);
    }
    
    .country:hover {
        transform: translateY(-10px) scale(1.03);
        box-shadow: var(--box-shadow-hover);
        border-color: var(--accent-green);
    }
    
    .country:hover::after {
        background: linear-gradient(to top, rgba(46, 204, 113, 0.9) 0%, rgba(46, 204, 113, 0.5) 50%, rgba(46, 204, 113, 0.3) 100%);
    }
    
    .country:hover .flag-icon {
        transform: scale(1.2) rotate(5deg);
    }
    
    .country:hover .country-duration {
        background: var(--accent-green);
        color: var(--white);
    }
    
    /* Info Section améliorée */
    .info-section {
        padding: 100px 20px;
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: var(--white);
        text-align: center;
        position: relative;
    }
    
    .info-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.1);
    }
    
    .info-content {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    
    .info-content h2 {
        font-size: 2.5rem;
        margin-bottom: 20px;
        font-weight: 700;
    }
    
    .info-content p {
        font-size: 1.2rem;
        line-height: 1.7;
        margin-bottom: 40px;
        opacity: 0.9;
    }
    
    .hero-btn {
        display: inline-block;
        padding: 15px 35px;
        background: var(--accent-green);
        color: var(--white);
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 1.1rem;
        transition: var(--transition);
        box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4);
        border: 2px solid transparent;
    }
    
    .hero-btn:hover {
        background: var(--white);
        color: var(--accent-green);
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(46, 204, 113, 0.5);
        border-color: var(--accent-green);
    }
    
    /* --- Responsive amélioré --- */
    @media (max-width: 1200px) {
        .countries {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
        
        .process-steps {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
    }
    
    @media (max-width: 992px) {
        .procedure-hero-content h1 {
            font-size: 2.8rem;
        }
        
        .procedure-hero-content p {
            font-size: 1.2rem;
        }
        
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .procedure-hero {
            height: 60vh;
            background:url('../images/etudiant.avif');
              }
        
        .procedure-hero-content h1 {
            font-size: 2.2rem;
        }
        
        .section-title {
            font-size: 2rem;
        }
        
        .process-steps {
            grid-template-columns: 1fr;
        }
        
        .countries {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        }
        
        .country {
            height: 180px;
        }
        
        .stats-container {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 576px) {
        .procedure-hero {
            height: 50vh;
            background:url('../images/etudiant.avif');

        }
        
        .procedure-hero-content h1 {
            font-size: 1.8rem;
        }
        
        .procedure-hero-content p {
            font-size: 1rem;
        }
        
        .countries {
            grid-template-columns: 1fr;
        }
        
        .filters {
            flex-direction: column;
            align-items: center;
        }
        
        .filter-btn {
            width: 200px;
        }
        
        .country-label {
            font-size: 1.1rem;
        }
    }
</style>

<script>
    // JavaScript amélioré avec plus de fonctionnalités
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const countries = document.querySelectorAll('.country');
        
        // Filtrage des pays
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Animation des boutons
                filterButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.transform = 'translateY(0)';
                });
                
                this.classList.add('active');
                this.style.transform = 'translateY(-2px)';
                
                const filter = this.getAttribute('data-filter');
                
                // Animation de filtrage
                countries.forEach((country, index) => {
                    setTimeout(() => {
                        if (filter === 'all') {
                            country.style.display = 'block';
                            setTimeout(() => {
                                country.style.opacity = '1';
                                country.style.transform = 'translateY(0) scale(1)';
                            }, 50);
                        } else {
                            const countryRegion = country.getAttribute('data-region');
                            if (countryRegion === filter) {
                                country.style.display = 'block';
                                setTimeout(() => {
                                    country.style.opacity = '1';
                                    country.style.transform = 'translateY(0) scale(1)';
                                }, 50);
                            } else {
                                country.style.opacity = '0';
                                country.style.transform = 'translateY(20px) scale(0.9)';
                                setTimeout(() => {
                                    country.style.display = 'none';
                                }, 300);
                            }
                        }
                    }, index * 50);
                });
            });
        });
        
        // Animation au défilement
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        // Observer les éléments à animer
        document.querySelectorAll('.process-step, .country, .stat-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
        
        // Navigation fluide améliorée
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    const offsetTop = targetElement.offsetTop - 80;
                    
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
</script>

<!-- Hero Section améliorée -->
<section class="procedure-hero">
    <div class="procedure-hero-overlay"></div>
    <div class="procedure-hero-content">
        <h1>Procédures d'Études à l'Étranger</h1>
        <p>Votre guide étape par étape pour concrétiser votre projet d'études à l'international</p>
    </div>
</section>

<!

<!-- PAYS Section améliorée -->
<section class="countries-section">
    <h2 class="section-title">Explorez Nos Destinations</h2>
    
    <!-- Filtres par région -->
    <div class="filters">
        <button class="filter-btn active" data-filter="all">Toutes les destinations</button>
        <button class="filter-btn" data-filter="europe">Europe</button>
        <button class="filter-btn" data-filter="amerique">Amérique</button>
    </div>
    
    <div class="countries">
        <!-- Europe -->
        <a href="france/etudes/etudes.php" class="country" style="background-image:url('../images/france.jpg');" data-region="europe">
      
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/fr.png" alt="Drapeau France">
                <span>France</span>
            </div>
        </a>

        <a href="canada/etude/admission.php" class="country" style="background-image: url('../images/canada.jpg');" data-region="amerique">
        
            <div class="country-label">
                <img src="https://flagcdn.com/w40/ca.png" class="flag-icon" alt="Drapeau Canada">
                <span>Canada</span>
            </div>
        </a>

        <a href="espagne/etudes/admission.php" class="country" style="background-image: url('../images/espagne.jpg');" data-region="europe">
      
            <div class="country-label">
                <img src="https://flagcdn.com/w40/es.png" class="flag-icon" alt="Drapeau Espagne">
                <span>Espagne</span>
            </div>
        </a>

        <a href="bulgarie/etude/etude.php" class="country" style="background-image: url('../images/bulgarie.jpg');" data-region="europe">
         
            <div class="country-label">
                <img src="https://flagcdn.com/w40/bg.png" class="flag-icon" alt="Drapeau Bulgarie">
                <span>Bulgarie</span>
            </div>
        </a>

        <a href="turquie/etude/etudes.php" class="country" style="background-image: url('../images/turquie.jpg');" data-region="europe">
       
            <div class="country-label">
                <img src="https://flagcdn.com/w40/tr.png" class="flag-icon" alt="Drapeau Turquie">
                <span>Turquie</span>
            </div>
        </a>
        <a href="espagne/etudes/admission.php" class="country" style="background-image: url('../images/espagne.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/es.png" alt="Drapeau Espagne">
                <span>Espagne</span>
            </div>
        </a>

        <a href="luxembourg/etudes/etude.php" class="country" style="background-image: url('../images/luxembourg.jpg');" data-region="europe">
           
            <div class="country-label">
                <img src="https://flagcdn.com/w40/lu.png" class="flag-icon" alt="Drapeau Luxembourg">
                <span>Luxembourg</span>
            </div>
        </a>

        <a href="Belgique/etudes/etudes.php" class="country" style="background-image: url('../images/belgique.jpg');" data-region="europe">
     
            <div class="country-label">
                <img src="https://flagcdn.com/w40/be.png" class="flag-icon" alt="Drapeau Belgique">
                <span>Belgique</span>
            </div>
        </a>

        <a href="Roumanie/etude/etude.php" class="country" style="background-image: url('../images/roumanie.jpg');" data-region="europe">
            <div class="country-label">
                <img src="https://flagcdn.com/w40/ro.png" class="flag-icon" alt="Drapeau Roumanie">
                <span>Roumanie</span>
            </div>
        </a>

        <a href="suisse/etudes/etude.php" class="country" style="background-image: url('../images/suisse.jpg');" data-region="europe">
    
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/ch.png" alt="Drapeau Suisse">
                <span>Suisse</span>
            </div>
        </a>
    </div>
</section>



<?php include __DIR__ . '/../../includes/footer.php'; ?>