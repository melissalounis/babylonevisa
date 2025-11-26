<?php
$page_title = "À propos — Babylone Service";
require_once __DIR__ . '/../config.php';
include __DIR__ . '/../includes/header.php';
?>

<style>
    :root {
        --primary-blue: #0056b3;
        --secondary-blue: #0077ff;
        --accent-orange: #ff6b35;
        --light-bg: #f8fafc;
        --dark-text: #2d3748;
        --white: #ffffff;
        --light-gray: #e2e8f0;
        --transition: all 0.3s ease;
    }

    .about-hero {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
        color: var(--white);
        padding: 80px 20px;
        text-align: center;
        margin-bottom: 60px;
        position: relative;
        overflow: hidden;
    }

    .about-hero-content h1 {
        font-size: 3rem;
        margin-bottom: 20px;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .about-hero-content p {
        font-size: 1.3rem;
        opacity: 0.95;
        max-width: 700px;
        margin: 0 auto;
        font-weight: 300;
    }

    .hero-pattern {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
        z-index: 1;
    }

    .about-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 20px 80px;
    }

    .about-section {
        margin-bottom: 60px;
    }

    .about-section h2 {
        font-size: 2.2rem;
        text-align: center;
        margin-bottom: 30px;
        color: var(--primary-blue);
        position: relative;
        font-weight: 700;
    }

    .about-section h2:after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 4px;
        background: var(--accent-orange);
        border-radius: 2px;
    }

    .about-content {
        background: var(--white);
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .about-content p {
        font-size: 1.1rem;
        line-height: 1.8;
        margin-bottom: 20px;
        color: var(--dark-text);
        text-align: justify;
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }

    .value-card {
        background: var(--white);
        border-radius: 12px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        transition: var(--transition);
        border: 1px solid var(--light-gray);
    }

    .value-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
    }

    .value-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }

    .value-icon i {
        font-size: 32px;
        color: var(--white);
    }

    .value-card h3 {
        font-size: 1.4rem;
        margin-bottom: 15px;
        color: var(--dark-text);
        font-weight: 600;
    }

    .value-card p {
        color: #64748b;
        line-height: 1.6;
    }

    .why-choose-us {
        background: var(--light-bg);
        padding: 60px 0;
        border-radius: 16px;
    }

    .features-list {
        max-width: 700px;
        margin: 0 auto;
    }

    .feature-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 25px;
        background: var(--white);
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .feature-icon {
        background: var(--accent-orange);
        color: var(--white);
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .feature-content h4 {
        font-size: 1.2rem;
        margin-bottom: 8px;
        color: var(--dark-text);
        font-weight: 600;
    }

    .feature-content p {
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    .stats-section {
        background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
        color: var(--white);
        padding: 80px 0;
        border-radius: 16px;
        margin: 60px 0;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 40px;
        max-width: 900px;
        margin: 0 auto;
        text-align: center;
    }

    .stat-item {
        padding: 20px;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 10px;
        display: block;
    }

    .stat-label {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .about-hero-content h1 {
            font-size: 2.2rem;
        }
        
        .about-hero-content p {
            font-size: 1.1rem;
        }
        
        .about-section h2 {
            font-size: 1.8rem;
        }
        
        .values-grid {
            grid-template-columns: 1fr;
        }
        
        .about-content {
            padding: 25px;
        }
        
        .feature-item {
            flex-direction: column;
            text-align: center;
        }
        
        .feature-icon {
            margin-right: 0;
            margin-bottom: 15px;
        }
        
        .stats-container {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .stat-number {
            font-size: 2.2rem;
        }
    }

    @media (max-width: 480px) {
        .about-hero {
            padding: 60px 15px;
        }
        
        .about-hero-content h1 {
            font-size: 1.8rem;
        }
        
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .stat-item {
            padding: 15px;
        }
    }
</style>

<div class="about-hero">
    <div class="hero-pattern"></div>
    <div class="about-hero-content">
        <h1>À propos de Babylone Service</h1>
        <p>Votre partenaire de confiance pour toutes vos démarches administratives et voyages</p>
    </div>
</div>

<div class="about-container">
    <div class="about-section">
        <div class="about-content">
            <h2>Notre Histoire</h2>
            <p>
                Fondé avec la vision de simplifier les démarches complexes, <strong>Babylone Service</strong> est devenu 
                un acteur de référence dans l'accompagnement administratif et de voyage. Notre équipe 
                passionnée travaille quotidiennement pour offrir des services de qualité et rendre 
                accessibles les procedures qui semblent souvent insurmontables.
            </p>
            <p>
                De la simple demande de visa à l'organisation complète de votre projet d'études ou 
                de travail à l'étranger, nous mettons notre expertise à votre service pour transformer 
                vos aspirations en réalités concrètes.
            </p>
        </div>
    </div>

    <div class="about-section">
        <h2>Nos Valeurs</h2>
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Professionnalisme</h3>
                <p>Un service sérieux et fiable pour toutes vos démarches administratives et voyages</p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Accompagnement Personnalisé</h3>
                <p>Des solutions sur mesure adaptées à vos besoins spécifiques et objectifs</p>
            </div>

            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h3>Confidentialité</h3>
                <p>Vos données personnelles sont protégées et traitées avec la plus grande discrétion</p>
            </div>
        </div>
    </div>

    <div class="stats-section">
        <div class="stats-container">
            <div class="stat-item">
                <span class="stat-number">5000+</span>
                <span class="stat-label">Clients satisfaits</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">98%</span>
                <span class="stat-label">Taux de réussite</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">15+</span>
                <span class="stat-label">Pays couverts</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">10+</span>
                <span class="stat-label">Ans d'expérience</span>
            </div>
        </div>
    </div>

    <div class="about-section">
        <div class="why-choose-us">
            <h2>Pourquoi nous choisir ?</h2>
            <div class="features-list">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Gain de temps précieux</h4>
                        <p>Nous gérons toutes les démarches complexes à votre place, vous permettant de vous concentrer sur l'essentiel</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Expertise confirmée</h4>
                        <p>Notre équipe experte maîtrise parfaitement les procedures administratives et les exigences des différents pays</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Support continu</h4>
                        <p>Un accompagnement personnalisé à chaque étape de votre projet, du premier contact jusqu'à la concrétisation</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <div class="feature-content">
                        <h4>Engagement total</h4>
                        <p>Votre satisfaction est notre priorité absolue, nous nous engageons à vous offrir le meilleur service possible</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>