
<style>
    /* ===== SECTION DESTINATIONS MODERNE ===== */
    .countries-section {
        padding: 120px 20px;
        background: var(--light-blue);
        text-align: center;
        position: relative;
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
    }
    
    .section-title {
        font-size: 3rem;
        margin-bottom: 80px;
        color: var(--primary-blue);
        position: relative;
        display: inline-block;
        font-weight: 800;
        text-align: center;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        bottom: -20px;
        left: 50%;
        transform: translateX(-50%);
        width: 100px;
        height: 5px;
        background: var(--gradient-accent);
        border-radius: 3px;
    }
    
    .countries-container {
        position: relative;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
    }
    
    .countries {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin-bottom: 40px;
    }
    
    .country {
        height: 220px;
        border-radius: 20px;
        background-size: cover;
        background-position: center;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        text-decoration: none;
        color: var(--white);
        display: block;
        box-shadow: 
            0 10px 30px rgba(0, 0, 0, 0.1),
            0 4px 12px rgba(0, 0, 0, 0.05);
        border: none;
        transform-style: preserve-3d;
        perspective: 1000px;
    }

    .country::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            135deg, 
            rgba(0, 0, 0, 0) 0%, 
            rgba(0, 0, 0, 0.1) 50%, 
            rgba(0, 0, 0, 0.4) 100%
        );
        opacity: 0.7;
        transition: all 0.5s ease;
        z-index: 1;
    }

    .country::after {
        content: '';
        position: absolute;
        top: 15px;
        left: 15px;
        right: 15px;
        bottom: 15px;
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        opacity: 0;
        transition: all 0.4s ease;
        z-index: 2;
    }

    .country:hover {
        transform: translateY(-12px) scale(1.03);
        box-shadow: 
            0 25px 50px rgba(0, 0, 0, 0.2),
            0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .country:hover::before {
        opacity: 0.9;
        background: linear-gradient(
            135deg, 
            rgba(0, 0, 0, 0.1) 0%, 
            rgba(0, 0, 0, 0.3) 50%, 
            rgba(0, 0, 0, 0.6) 100%
        );
    }

    .country:hover::after {
        opacity: 1;
        top: 10px;
        left: 10px;
        right: 10px;
        bottom: 10px;
        border-color: rgba(255, 255, 255, 0.4);
    }

    .country-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 30px 25px;
        z-index: 3;
        transform: translateY(0);
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .country:hover .country-content {
        transform: translateY(-10px);
    }

    .country-label {
        display: flex;
        align-items: center;
        gap: 15px;
        color: white;
        font-weight: 700;
        font-size: 1.3rem;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        transition: all 0.4s ease;
    }

    .flag-icon {
        width: 40px;
        height: 28px;
        object-fit: cover;
        border-radius: 4px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        transform: rotate(0deg);
        filter: brightness(1.1) saturate(1.2);
    }

    .country:hover .flag-icon {
        transform: scale(1.3) rotate(2deg);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.6);
    }

    .country-description {
        position: absolute;
        top: 25px;
        left: 25px;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.5s ease;
        z-index: 3;
        color: white;
        font-size: 0.9rem;
        text-shadow: 0 2px 5px rgba(0, 0, 0, 0.5);
        max-width: calc(100% - 50px);
    }

    .country:hover .country-description {
        opacity: 1;
        transform: translateY(0);
    }

    /* Effet de brillance au survol */
    .country-shine {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent
        );
        transition: left 0.8s ease;
        z-index: 2;
    }

    .country:hover .country-shine {
        left: 100%;
    }

    /* Animation d'apparition */
    .country {
        opacity: 0;
        transform: translateY(30px);
        animation: countryReveal 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    }

    @keyframes countryReveal {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Pays cachés */
    .country.hidden {
        display: none;
    }

    .country.visible {
        display: block;
        animation: countrySlideIn 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
    }

    @keyframes countrySlideIn {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Bouton Voir Plus amélioré */
    .show-more-container {
        margin-top: 60px;
        text-align: center;
        position: relative;
    }

    .show-more-btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 16px 35px;
        background: var(--gradient-accent);
        color: white;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        border: none;
        cursor: pointer;
        box-shadow: 
            0 10px 30px rgba(46, 204, 113, 0.3),
            0 5px 15px rgba(46, 204, 113, 0.2);
        position: relative;
        overflow: hidden;
    }

    .show-more-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.6s ease;
    }

    .show-more-btn:hover {
        transform: translateY(-5px) scale(1.05);
        box-shadow: 
            0 20px 40px rgba(46, 204, 113, 0.4),
            0 10px 25px rgba(46, 204, 113, 0.3);
    }

    .show-more-btn:hover::before {
        left: 100%;
    }

    .show-more-btn i {
        transition: all 0.4s ease;
    }

    .show-more-btn:hover i {
        transform: translateY(2px);
    }

    /* Indicateur de compteur */
    .countries-count {
        margin-top: 15px;
        color: var(--primary-blue);
        font-weight: 600;
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* Animation de fond subtile */
    .countries-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary-blue), transparent);
        opacity: 0.3;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .countries {
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 992px) {
        .countries {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
    }

    @media (max-width: 768px) {
        .countries-section {
            padding: 80px 15px;
        }
        
        .section-title {
            font-size: 2.5rem;
            margin-bottom: 60px;
        }
        
        .countries {
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }
        
        .country {
            height: 180px;
            border-radius: 16px;
        }
        
        .country-content {
            padding: 25px 20px;
        }
        
        .country-label {
            font-size: 1.2rem;
            gap: 12px;
        }
        
        .flag-icon {
            width: 35px;
            height: 25px;
        }
        
        .show-more-btn {
            padding: 14px 30px;
            font-size: 1rem;
        }
    }

    @media (max-width: 576px) {
        .countries-section {
            padding: 60px 10px;
        }
        
        .section-title {
            font-size: 2rem;
            margin-bottom: 40px;
        }
        
        .countries {
            grid-template-columns: 1fr;
            gap: 15px;
            max-width: 400px;
            margin: 0 auto;
        }
        
        .country {
            height: 160px;
            border-radius: 14px;
        }
        
        .country-content {
            padding: 20px;
        }
        
        .country-label {
            font-size: 1.1rem;
            justify-content: center;
        }
        
        .flag-icon {
            width: 32px;
            height: 22px;
        }
        
        .show-more-btn {
            padding: 12px 25px;
            font-size: 0.95rem;
            width: 100%;
            max-width: 280px;
            justify-content: center;
        }
    }
</style>

<section class="countries-section" id="count">
    <h2 class="section-title">Destinations populaires</h2>
    
    <div class="countries-container">
        <div class="countries">
            <!-- 4 premiers pays visibles -->
            <a href="france/france.php" class="country visible" style="background-image:url('images/france.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Découvrez la culture française</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/fr.png" alt="Drapeau France">
                        <span>France</span>
                    </div>
                </div>
            </a>

            <a href="canada/canada.php" class="country visible" style="background-image: url('images/canada.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Nature sauvage et villes dynamiques</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/ca.png" alt="Drapeau Canada">
                        <span>Canada</span>
                    </div>
                </div>
            </a>

            <a href="espagne/espagne.php" class="country visible" style="background-image: url('images/espagne.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Soleil et traditions</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/es.png" alt="Drapeau Espagne">
                        <span>Espagne</span>
                    </div>
                </div>
            </a>
            
            <a href="bulgarie/bulgarie.php" class="country visible" style="background-image: url('images/bulgarie.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Trésors de l'Est</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/bg.png" alt="Drapeau Bulgarie">
                        <span>Bulgarie</span>
                    </div>
                </div>
            </a>

            <!-- Pays cachés par défaut -->
            <a href="turquie/turquie.php" class="country hidden" style="background-image: url('images/turquie.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Entre Europe et Asie</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/tr.png" alt="Drapeau Turquie">
                        <span>Turquie</span>
                    </div>
                </div>
            </a>

            <a href="italie/italie.php" class="country hidden" style="background-image: url('images/italie.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Art et gastronomie</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/it.png" alt="Drapeau Italie">
                        <span>Italie</span>
                    </div>
                </div>
            </a>

            <a href="usa/usa.php" class="country hidden" style="background-image: url('images/usa.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">La diversité américaine</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/us.png" alt="Drapeau USA">
                        <span>USA</span>
                    </div>
                </div>
            </a>
            
            <a href="allemagne/allemagne.php" class="country hidden" style="background-image: url('images/allemagne.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Efficacité et histoire</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/de.png" alt="Drapeau Allemagne">
                        <span>Allemagne</span>
                    </div>
                </div>
            </a>
            
            <a href="estonie/estonie.php" class="country hidden" style="background-image: url('images/istonie.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Innovation et nature</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/ee.png" alt="Drapeau Estonie">
                        <span>Estonie</span>
                    </div>
                </div>
            </a>
            
            <a href="malte/malte.php" class="country hidden" style="background-image: url('images/malte.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Perle méditerranéenne</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/mt.png" alt="Drapeau Malte">
                        <span>Malte</span>
                    </div>
                </div>
            </a>
            
            <a href="pologne/pologne.php" class="country hidden" style="background-image: url('images/pologne.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Tradition et modernité</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/pl.png" alt="Drapeau Pologne">
                        <span>Pologne</span>
                    </div>
                </div>
            </a>
            
            <a href="belgique/belgique.php" class="country hidden" style="background-image: url('images/belgique.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Chocolat et architecture</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/be.png" alt="Drapeau Belgique">
                        <span>Belgique</span>
                    </div>
                </div>
            </a>
            
            <a href="hongrie/hongrie.php" class="country hidden" style="background-image: url('images/hongrie.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Thermes et culture</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/hu.png" alt="Drapeau Hongrie">
                        <span>Hongrie</span>
                    </div>
                </div>
            </a>
           
            <a href="holande/pays_bas.php" class="country hidden" style="background-image: url('images/holande.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Moulins et canaux</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/nl.png" alt="Drapeau Pays-Bas">
                        <span>Pays-Bas</span>
                    </div>
                </div>
            </a>

            <a href="roumanie/roumanie.php" class="country hidden" style="background-image: url('images/roumanie.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Carpates mystérieuses</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/ro.png" alt="Drapeau Roumanie">
                        <span>Roumanie</span>
                    </div>
                </div>
            </a>
             
            <a href="portugal/portugal.php" class="country hidden" style="background-image: url('images/portugal.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Océan et découvertes</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/pt.png" alt="Drapeau Portugal">
                        <span>Portugal</span>
                    </div>
                </div>
            </a>
             
            <a href="uk/Royaume_uni.php" class="country hidden" style="background-image: url('images/uk.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Tradition britannique</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/gb.png" alt="Drapeau Royaume-Uni">
                        <span>Royaume-Uni</span>
                    </div>
                </div>
            </a>
            
            <a href="irlande/irlande.php" class="country hidden" style="background-image: url('images/irlande.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Paysages verdoyants</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/ie.png" alt="Drapeau Irlande">
                        <span>Irlande</span>
                    </div>
                </div>
            </a>
           
            <a href="luxembourg/luxembourg.php" class="country hidden" style="background-image: url('images/luxembourg.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Cœur de l'Europe</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/lu.png" alt="Drapeau Luxembourg">
                        <span>Luxembourg</span>
                    </div>
                </div>
            </a>
            
            <a href="suisse/suisse.php" class="country hidden" style="background-image: url('images/suisse.jpg');">
                <div class="country-shine"></div>
                <div class="country-description">Précision et nature</div>
                <div class="country-content">
                    <div class="country-label">
                        <img class="flag-icon" src="https://flagcdn.com/w40/ch.png" alt="Drapeau Suisse">
                        <span>Suisse</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Bouton Voir Plus -->
    <div class="show-more-container">
        <button class="show-more-btn" id="showMoreBtn">
            <i class="fas fa-chevron-down"></i>
            Voir plus de destinations
        </button>
        <div class="countries-count" id="countriesCount">4 sur 20 destinations</div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const showMoreBtn = document.getElementById('showMoreBtn');
        const countriesCount = document.getElementById('countriesCount');
        const allCountries = document.querySelectorAll('.country');
        const visibleCountries = document.querySelectorAll('.country.visible');
        const hiddenCountries = document.querySelectorAll('.country.hidden');
        
        let allVisible = false;
        const totalCountries = allCountries.length;
        const initialVisible = visibleCountries.length;

        // Mettre à jour le compteur
        function updateCounter() {
            const visibleCount = document.querySelectorAll('.country.visible').length;
            countriesCount.textContent = `${visibleCount} sur ${totalCountries} destinations`;
        }

        // Animation d'entrée des pays
        const countryObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, { threshold: 0.1 });

        // Observer les pays visibles
        visibleCountries.forEach(country => {
            countryObserver.observe(country);
        });

        // Fonction pour afficher plus de pays
        function showMoreCountries() {
            hiddenCountries.forEach((country, index) => {
                setTimeout(() => {
                    country.classList.remove('hidden');
                    country.classList.add('visible');
                    countryObserver.observe(country);
                    
                    // Animation d'apparition
                    country.style.opacity = '0';
                    country.style.transform = 'translateY(30px)';
                    
                    setTimeout(() => {
                        country.style.opacity = '1';
                        country.style.transform = 'translateY(0)';
                        country.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                    }, 50);
                }, index * 100);
            });

            // Mettre à jour le bouton et le compteur
            setTimeout(() => {
                showMoreBtn.innerHTML = '<i class="fas fa-chevron-up"></i> Voir moins';
                allVisible = true;
                updateCounter();
            }, hiddenCountries.length * 100 + 300);
        }

        // Fonction pour cacher les pays supplémentaires
        function hideExtraCountries() {
            const extraCountries = document.querySelectorAll('.country.visible:not(:nth-child(-n+4))');
            
            extraCountries.forEach((country, index) => {
                setTimeout(() => {
                    country.style.opacity = '0';
                    country.style.transform = 'translateY(30px)';
                    
                    setTimeout(() => {
                        country.classList.remove('visible');
                        country.classList.add('hidden');
                        country.style.opacity = '';
                        country.style.transform = '';
                    }, 300);
                }, index * 50);
            });

            // Mettre à jour le bouton et le compteur
            setTimeout(() => {
                showMoreBtn.innerHTML = '<i class="fas fa-chevron-down"></i> Voir plus de destinations';
                allVisible = false;
                updateCounter();
            }, extraCountries.length * 50 + 500);
        }

        // Gestion du clic sur le bouton
        showMoreBtn.addEventListener('click', function() {
            if (!allVisible) {
                showMoreCountries();
            } else {
                hideExtraCountries();
            }
        });

        // Gestion des erreurs d'images
        allCountries.forEach(country => {
            const img = country.style.backgroundImage;
            if (!img || img === 'none' || img === 'url("")') {
                const countryName = country.querySelector('span').textContent;
                country.style.background = 'linear-gradient(135deg, var(--primary-blue), var(--secondary-blue))';
                country.style.display = 'flex';
                country.style.alignItems = 'center';
                country.style.justifyContent = 'center';
                country.innerHTML = `
                    <div style="color: white; font-weight: bold; font-size: 1.4rem; text-align: center; z-index: 10; text-shadow: 0 2px 10px rgba(0,0,0,0.5);">
                        ${countryName}
                    </div>
                `;
            }
        });

        // Initialiser le compteur
        updateCounter();

        // Animation au survol
        allCountries.forEach(country => {
            country.addEventListener('mouseenter', function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                this.style.setProperty('--mouse-x', `${x}px`);
                this.style.setProperty('--mouse-y', `${y}px`);
            });
        });
    });
</script>