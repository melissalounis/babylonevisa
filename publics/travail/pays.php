<?php
require_once __DIR__ . '/../../config.php';
$page_title = "Destinations - Babylone Service";
include __DIR__ . '/../../includes/header.php';
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
    
    /* --- Hero Section Pays --- */
    .pays-hero {
        position: relative;
        height: 60vh;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-align: center;
        overflow: hidden;
        background:url('../images/travail.avif');
        margin-top: -40px;
    }
    
    .pays-hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: 1;
    }
    
    .pays-hero-content {
        max-width: 800px;
        padding: 0 20px;
        z-index: 2;
        animation: fadeInUp 1s ease-out;
    }
    
    .pays-hero-content h1 {
        font-size: 3.5rem;
        margin-bottom: 20px;
        font-weight: 800;
        text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
        letter-spacing: -0.5px;
    }
    
    .pays-hero-content p {
        font-size: 1.5rem;
        margin-bottom: 30px;
        font-weight: 300;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }
    
    /* PAYS - MODIFIÉ */
    .countries-section {
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
    
    .countries {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 25px;
        max-width: 100%;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .country {
        height: 160px;
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
        border: 1px solid var(--light-gray);
        width: 100%;
        box-sizing: border-box;
    }
    
    .country::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(26, 95, 122, 0.7) 0%, rgba(26, 95, 122, 0.3) 50%, rgba(26, 95, 122, 0.1) 100%);
        border-radius: var(--border-radius);
        transition: var(--transition);
    }
    
    .country-label {
        position: absolute;
        bottom: 15px;
        left: 15px;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 700;
        font-size: 1.2rem;
        transition: var(--transition);
    }
    
    .flag-icon {
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        transition: var(--transition);
    }
    
    .country:hover {
        transform: translateY(-8px) scale(1.05);
        box-shadow: var(--box-shadow-hover);
    }
    
    .country:hover::after {
        background: linear-gradient(to top, rgba(46, 204, 113, 0.8) 0%, rgba(46, 204, 113, 0.4) 50%, rgba(46, 204, 113, 0.2) 100%);
    }
    
    .country:hover .flag-icon {
        transform: scale(1.2) rotate(5deg);
    }
    
    /* Filtres */
    .filters {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 40px;
        padding: 0 20px;
    }
    
    .filter-btn {
        padding: 10px 20px;
        background: var(--white);
        color: var(--primary-blue);
        border: 2px solid var(--primary-blue);
        border-radius: 50px;
        cursor: pointer;
        transition: var(--transition);
        font-weight: 600;
    }
    
    .filter-btn:hover, .filter-btn.active {
        background: var(--primary-blue);
        color: var(--white);
    }
    
    /* Info Section */
    .info-section {
        padding: 80px 20px;
        background: var(--white);
        text-align: center;
    }
    
    .info-content {
        max-width: 800px;
        margin: 0 auto;
    }
    
    .info-content h2 {
        font-size: 2.2rem;
        margin-bottom: 20px;
        color: var(--primary-blue);
    }
    
    .info-content p {
        font-size: 1.1rem;
        line-height: 1.7;
        color: var(--dark-text);
        margin-bottom: 30px;
    }
    
    /* --- Responsive --- */
    @media (max-width: 1200px) {
        .countries {
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        }
    }
    
    @media (max-width: 992px) {
        .pays-hero-content h1 {
            font-size: 2.8rem;
        }
        
        .pays-hero-content p {
            font-size: 1.2rem;
        }
    }
    
    @media (max-width: 768px) {
        .pays-hero-content h1 {
            font-size: 2.2rem;
        }
        
        .section-title {
            font-size: 2rem;
        }
        
        .country {
            height: 140px;
        }
    }
    
    @media (max-width: 576px) {
        .pays-hero {
            height: 50vh;
        }
        
        .pays-hero-content h1 {
            font-size: 1.8rem;
        }
        
        .pays-hero-content p {
            font-size: 1rem;
        }
        
        .country {
            width: 100%;
            max-width: 280px;
            margin: 0 auto;
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
    }
</style>

<script>
    // JavaScript pour le filtrage des pays
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const countries = document.querySelectorAll('.country');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Retirer la classe active de tous les boutons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                
                // Filtrer les pays
                countries.forEach(country => {
                    if (filter === 'all') {
                        country.style.display = 'block';
                    } else {
                        const countryName = country.querySelector('span').textContent.toLowerCase();
                        if (countryName.includes(filter)) {
                            country.style.display = 'block';
                        } else {
                            country.style.display = 'none';
                        }
                    }
                });
            });
        });
        
        // Navigation fluide
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });
    });
</script>

<!-- Hero Section Pays -->
<section class="pays-hero">
    <div class="pays-hero-overlay"></div>
    <div class="pays-hero-content">
        <h1>Nos Destinations</h1>
        <p>Découvrez tous les pays où nous pouvons vous accompagner</p>
    </div>
</section>

<!-- PAYS -->
    <h2 class="section-title">Destinations populaires</h2>
    <div class="countries">
        <a href="../../public/travail/travail.php" class="country" style="background-image:url('../../public/images/france.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/fr.png" alt="Drapeau France">
                <span>France</span>
            </div>
        </a>

        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/canada.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/ca.png" alt="Drapeau Canada">
                <span>Canada</span>
            </div>
        </a>

        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/espagne.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/es.png" alt="Drapeau Espagne">
                <span>Espagne</span>
            </div>
        </a>
        
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/bulgarie.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/bg.png" alt="Drapeau Bulgarie">
                <span>Bulgarie</span>
            </div>
        </a>

        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/turquie.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/tr.png" alt="Drapeau Turquie">
                <span>Turquie</span>
            </div>
        </a>

        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/italie.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/it.png" alt="Drapeau Italie">
                <span>Italie</span>
            </div>
        </a>

        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/usa.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/us.png" alt="Drapeau USA">
                <span>USA</span>
            </div>
        </a>
        
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/allemagne.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/de.png" alt="Drapeau Allemagne">
                <span>Allemagne</span>
            </div>
        </a>
        
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/istonie.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/ee.png" alt="Drapeau Estonie">
                <span>Estonie</span>
            </div>
        </a>
        
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/malte.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/mt.png" alt="Drapeau Malte">
                <span>Malte</span>
            </div>
        </a>
        
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/pologne.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/pl.png" alt="Drapeau Pologne">
                <span>Pologne</span>
            </div>
        </a>
         <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/hongrie.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/hn.png" alt="Drapeau hongrie">
                <span>Hongrie</span>
            </div>
        </a>
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/holande.jpg');">
    <div class="country-label">
        <img class="flag-icon" src="https://flagcdn.com/w40/nl.png" alt="Drapeau des Pays-Bas">
        <span>Pays-Bas</span>
    </div>
</a>

        
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/belgique.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/be.png" alt="Drapeau Belgique">
                <span>Belgique</span>
            </div>
        </a>
         
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/roumanie.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/ro.png" alt="Drapeau Roumanie">
                <span>Roumanie</span>
            </div>
        </a>
         
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/portugal.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/pt.png" alt="Drapeau Portugal">
                <span>Portugal</span>
            </div>
        </a>
         
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/uk.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/gb.png" alt="Drapeau Royaume-Uni">
                <span>Royaume-Uni</span>
            </div>
        </a>
        
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/irlande.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/ie.png" alt="Drapeau Irlande">
                <span>Irlande</span>
            </div>
        </a>
       
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/luxembourg.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/lu.png" alt="Drapeau Luxembourg">
                <span>Luxembourg</span>
            </div>
        </a>
        
        <a href="../../public/travail/travail.php" class="country" style="background-image: url('../../public/images/suisse.jpg');">
            <div class="country-label">
                <img class="flag-icon" src="https://flagcdn.com/w40/ch.png" alt="Drapeau Suisse">
                <span>Suisse</span>
            </div>
        </a>
    </div>
</section>



<?php include __DIR__ . '/../../includes/footer.php'; ?>