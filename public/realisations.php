<?php
// realisations.php - Slider automatique des visas
?>

<!-- Section Slider des Réalisations -->
<section class="realisations-slider-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Nos Réalisations</h2>
            <p class="section-subtitle">
                Quelques visas obtenus avec succès pour nos clients
            </p>
        </div>

        <div class="slider-container">
            <div class="slider-track">
                <!-- Visa 1 -->
                <div class="slide">
                    <div class="visa-card">
                        <img src="images/accturq.jpeg" alt="Visa Canada" loading="lazy">
                    </div>
                </div>

                <!-- Visa 2 -->
                <div class="slide">
                    <div class="visa-card">
                        <img src="images/belgacc.png" alt="Visa France" loading="lazy">
                    </div>
                </div>

                <!-- Visa 3 -->
                <div class="slide">
                    <div class="visa-card">
                        <img src="images/canada famille.png" alt="Visa Belgique" loading="lazy">
                    </div>
                </div>

                <!-- Visa 4 -->
                <div class="slide">
                    <div class="visa-card">
                        <img src="images/esp famil.png" alt="Visa Roumanie" loading="lazy">
                    </div>
                </div>

                <!-- Visa 5 -->
                <div class="slide">
                    <div class="visa-card">
                        <img src="images/accp frans.jpg" alt="Visa USA" loading="lazy">
                    </div>
                </div>

                <!-- Visa 6 -->
                <div class="slide">
                    <div class="visa-card">
                        <img src="images/esp famil.png" alt="Visa Allemagne" loading="lazy">
                    </div>
                </div>

                <!-- Visa 7 -->
                <div class="slide">
                    <div class="visa-card">
                        <img src="images/espsuc.jpg" alt="Visa Italie" loading="lazy">
                    </div>
                </div>

                <!-- Visa 8 -->
                <div class="slide">
                    <div class="visa-card">
                        <img src="images/canada famille.png" alt="Visa Espagne" loading="lazy">
                    </div>
                </div>

                <!-- Dupliquer pour effet infini -->
                <div class="slide">
                    <div class="visa-card">
                        <img src="images/accturq.jpeg" alt="Visa Canada" loading="lazy">
                    </div>
                </div>

                <div class="slide">
                    <div class="visa-card">
                        <img src="images/pays_basacc.png" alt="Visa France" loading="lazy">
                    </div>
                </div>

                <div class="slide">
                    <div class="visa-card">
                        <img src="images/frsuc1.jpg" alt="Visa Belgique" loading="lazy">
                    </div>
                </div>

                <div class="slide">
                    <div class="visa-card">
                        <img src="images/suc1.jpg" alt="Visa Roumanie" loading="lazy">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* ===== SECTION SLIDER RÉALISATIONS ===== */
.realisations-slider-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    position: relative;
    overflow: hidden;
}

.realisations-slider-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 80%, rgba(26, 95, 122, 0.05) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.05) 0%, transparent 50%);
    z-index: 1;
}

.realisations-slider-section .container {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* En-tête de section centrée */
.section-header {
    text-align: center;
    width: 100%;
    margin-bottom: 50px;
}

.section-title {
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    margin-bottom: 15px;
    color: var(--accent-dark, #2c3e50);
    text-align: center;
    width: 100%;
}

.section-subtitle {
    font-size: 1.2rem;
    color: var(--text-light, #6c757d);
    max-width: 600px;
    margin: 0 auto;
    text-align: center;
    line-height: 1.6;
}

/* Container du slider */
.slider-container {
    position: relative;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    overflow: hidden;
    padding: 20px 0;
}

/* Piste du slider */
.slider-track {
    display: flex;
    animation: slide 32s linear infinite; /* 8 visas × 1s × 4 = 32s pour boucle complète */
    gap: 20px;
}

/* Animation de défilement */
@keyframes slide {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(calc(-250px * 8 - 20px * 7)); /* Déplacement total */
    }
}

/* Chaque slide */
.slide {
    flex: 0 0 250px;
    height: 160px;
}

/* Carte visa */
.visa-card {
    width: 100%;
    height: 100%;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(26, 95, 122, 0.15);
    transition: all 0.3s ease;
    border: 3px solid transparent;
    background: var(--white, #ffffff);
}

.visa-card:hover {
    transform: scale(1.05);
    box-shadow: 0 12px 35px rgba(26, 95, 122, 0.25);
    border-color: var(--accent-gold, #d4af37);
}

.visa-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.visa-card:hover img {
    transform: scale(1.1);
}

/* Effet de gradient sur les bords du slider */
.slider-container::before,
.slider-container::after {
    content: '';
    position: absolute;
    top: 0;
    width: 100px;
    height: 100%;
    z-index: 2;
    pointer-events: none;
}

.slider-container::before {
    left: 0;
    background: linear-gradient(90deg, 
        rgba(248, 249, 250, 1) 0%, 
        rgba(248, 249, 250, 0) 100%);
}

.slider-container::after {
    right: 0;
    background: linear-gradient(90deg, 
        rgba(248, 249, 250, 0) 0%, 
        rgba(248, 249, 250, 1) 100%);
}

/* Pause au survol */
.slider-track:hover {
    animation-play-state: paused;
}

/* Responsive */
@media (max-width: 768px) {
    .realisations-slider-section {
        padding: 60px 0;
    }
    
    .slide {
        flex: 0 0 200px;
        height: 130px;
    }
    
    @keyframes slide {
        100% {
            transform: translateX(calc(-200px * 8 - 20px * 7));
        }
    }
    
    .slider-container::before,
    .slider-container::after {
        width: 50px;
    }
    
    .section-header {
        margin-bottom: 40px;
    }
}

@media (max-width: 576px) {
    .slide {
        flex: 0 0 150px;
        height: 100px;
    }
    
    @keyframes slide {
        100% {
            transform: translateX(calc(-150px * 8 - 20px * 7));
        }
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .section-subtitle {
        font-size: 1rem;
        padding: 0 20px;
    }
    
    .section-header {
        margin-bottom: 30px;
    }
}

/* Animation d'apparition */
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

.section-title,
.section-subtitle {
    animation: fadeInUp 0.8s ease-out;
}
</style>

<script>
// Script pour un défilement plus fluide et gestion de la performance
document.addEventListener('DOMContentLoaded', function() {
    const sliderTrack = document.querySelector('.slider-track');
    
    // Optimisation pour les mobiles
    if (window.innerWidth <= 576) {
        sliderTrack.style.animationDuration = '24s'; // Plus rapide sur mobile
    }
    
    // Pause automatique quand la page n'est pas visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            sliderTrack.style.animationPlayState = 'paused';
        } else {
            sliderTrack.style.animationPlayState = 'running';
        }
    });
});
</script>