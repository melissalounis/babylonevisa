<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Destinations Immigration - Babylone Service";
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
    
    @keyframes gradientShift {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    
    /* --- Hero Section spécialisée Immigration --- */
    .immigration-hero {
        position: relative;
        height: 70vh;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
        overflow: hidden;
        background: url('../images/immigration.jpg');
        background-size: 400% 400%;
        animation: gradientShift 15s ease infinite;
        margin-top: -40px;
    }
    
    .immigration-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.3);
        z-index: 1;
    }
    
    .immigration-hero-content {
        max-width: 900px;
        padding: 0 20px;
        z-index: 2;
        animation: fadeInUp 1s ease-out;
    }
    
    .immigration-hero-content h1 {
        font-size: 3.5rem;
        margin-bottom: 20px;
        font-weight: 800;
        text-shadow: 2px 2px 12px rgba(0, 0, 0, 0.4);
        letter-spacing: -0.5px;
    }
    
    .immigration-hero-content p {
        font-size: 1.5rem;
        margin-bottom: 30px;
        font-weight: 300;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }
    
    /* Stats Section pour immigration */
    .immigration-stats {
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
        border-left: 4px solid var(--primary-blue);
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
    
    /* Filtres améliorés pour immigration */
    .immigration-filters {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 50px;
        padding: 0 20px;
    }
    
    .immigration-filter-btn {
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
    
    .immigration-filter-btn:hover, 
    .immigration-filter-btn.active {
        background: var(--primary-blue);
        color: var(--white);
        transform: translateY(-2px);
    }
    
    /* Countries Section améliorée pour immigration */
    .immigration-countries-section {
        padding: 80px 20px;
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
    
    .immigration-countries {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .immigration-country {
        height: 220px;
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
    
    .immigration-country::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(26, 95, 122, 0.9) 0%, rgba(26, 95, 122, 0.4) 50%, rgba(26, 95, 122, 0.2) 100%);
        border-radius: var(--border-radius);
        transition: var(--transition);
    }
    
    .immigration-country-label {
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
    
    .immigration-info {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 2;
        background: rgba(255, 255, 255, 0.9);
        color: var(--primary-blue);
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: var(--transition);
    }
    
    .immigration-country:hover {
        transform: translateY(-10px) scale(1.03);
        box-shadow: var(--box-shadow-hover);
        border-color: var(--accent-green);
    }
    
    .immigration-country:hover::after {
        background: linear-gradient(to top, rgba(46, 204, 113, 0.9) 0%, rgba(46, 204, 113, 0.5) 50%, rgba(46, 204, 113, 0.3) 100%);
    }
    
    .immigration-country:hover .flag-icon {
        transform: scale(1.2) rotate(5deg);
    }
    
    .immigration-country:hover .immigration-info {
        background: var(--accent-green);
        color: var(--white);
    }
    
    /* Processus Immigration */
    .immigration-process {
        padding: 100px 20px;
        background: var(--white);
        text-align: center;
    }
    
    .process-steps {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 30px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .process-step {
        background: var(--light-blue);
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
        transform: translateY(-5px);
        box-shadow: var(--box-shadow-hover);
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
    
    .process-step:hover i {
        color: var(--accent-green);
        transform: scale(1.1);
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
    
    /* CTA Section améliorée */
    .immigration-cta {
        padding: 100px 20px;
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: var(--white);
        text-align: center;
        position: relative;
    }
    
    .immigration-cta::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.1);
    }
    
    .cta-content {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }
    
    .immigration-cta h2 {
        font-size: 2.5rem;
        margin-bottom: 20px;
        font-weight: 700;
    }
    
    .immigration-cta p {
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
        .immigration-countries {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
    }
    
    @media (max-width: 992px) {
        .immigration-hero-content h1 {
            font-size: 2.8rem;
        }
        
        .immigration-hero-content p {
            font-size: 1.2rem;
        }
        
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .immigration-hero {
            height: 60vh;
        }
        
        .immigration-hero-content h1 {
            font-size: 2.2rem;
        }
        
        .section-title {
            font-size: 2rem;
        }
        
        .immigration-countries {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        }
        
        .immigration-country {
            height: 200px;
        }
        
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .immigration-filters {
            flex-direction: column;
            align-items: center;
        }
        
        .immigration-filter-btn {
            width: 200px;
        }
    }
    
    @media (max-width: 576px) {
        .immigration-hero {
            height: 50vh;
        }
        
        .immigration-hero-content h1 {
            font-size: 1.8rem;
        }
        
        .immigration-hero-content p {
            font-size: 1rem;
        }
        
        .immigration-countries {
            grid-template-columns: 1fr;
        }
        
        .immigration-country-label {
            font-size: 1.1rem;
        }
    }
</style>

<script>
    // JavaScript amélioré pour l'immigration
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.immigration-filter-btn');
        const countries = document.querySelectorAll('.immigration-country');
        
        // Filtrage des pays par type de programme
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
                            const countryType = country.getAttribute('data-type');
                            if (countryType === filter) {
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
        document.querySelectorAll('.process-step, .immigration-country, .stat-item').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    });
</script>

<!-- Hero Section spécialisée Immigration -->
<section class="immigration-hero">
    <div class="immigration-hero-overlay"></div>
    <div class="immigration-hero-content">
        <h1>Destinations d'Immigration</h1>
        <p>Démarrez votre nouvelle vie à l'étranger avec notre accompagnement expert</p>
    </div>
</section>

<!-- Section Statistiques Immigration -->
<section class="immigration-stats">
    <div class="stats-container">
        <div class="stat-item">
            <span class="stat-number">85%</span>
            <span class="stat-label">Taux de Réussite</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">15+</span>
            <span class="stat-label">Pays d'Accueil</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">1000+</span>
            <span class="stat-label">Dossiers Traités</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">24h</span>
            <span class="stat-label">Évaluation Gratuite</span>
        </div>
    </div>
</section>

<!-- Filtres par type d'immigration -->
<section class="immigration-countries-section">
    
    
    <div class="immigration-countries">
        <!-- Canada -->
        <a href="../../public/canada/immigration/immigration.php" class="immigration-country" 
           style="background-image: url('../images/canada.jpg');" 
           data-type="travail">
            <div class="immigration-country-label">
                <img src="https://flagcdn.com/w40/ca.png" class="flag-icon" alt="Drapeau Canada">
                <span>Canada</span>
            </div>
        </a>

        <!-- USA -->
        <a href="../../public/usa/immigration/immigration.php" class="immigration-country" 
           style="background-image: url('../images/USA.jpg');" 
           data-type="travail">
            <div class="immigration-country-label">
                <img src="https://flagcdn.com/w40/us.png" class="flag-icon" alt="Drapeau USA">
                <span>États-Unis</span>
            </div>
        </a>

        
      
        
    </div>
</section>



<?php include __DIR__ . '/../../includes/footer.php'; ?>