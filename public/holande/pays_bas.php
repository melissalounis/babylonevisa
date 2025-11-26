<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pays-Bas — Services</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Variables de couleurs pour les Pays-Bas */
        :root {
            --paysbas-red: #AE1C28;
            --paysbas-white: #FFFFFF;
            --paysbas-blue: #21468B;
            --paysbas-orange: #FF9B00;
            --paysbas-light-blue: #E6F0FF;
            --paysbas-dark-red: #8A1520;
            --paysbas-dark-blue: #1A3A74;
            --dark: #2C3E50;
            --light: #E6F0FF;
            --grey: #6C757D;
            
            --gradient-paysbas: linear-gradient(135deg, var(--paysbas-red) 0%, var(--paysbas-white) 50%, var(--paysbas-blue) 100%);
            --gradient-paysbas-light: linear-gradient(135deg, var(--paysbas-dark-red) 0%, var(--paysbas-white) 50%, var(--paysbas-dark-blue) 100%);
            --shadow-paysbas: 0 8px 25px rgba(174, 28, 40, 0.15);
            --shadow-paysbas-hover: 0 15px 35px rgba(174, 28, 40, 0.25);
            --transition: all 0.3s ease;
        }

        /* Styles généraux */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Hero Section réduite */
        .paysbas-hero {
            background: var(--gradient-paysbas);
            color: white;
            padding: 80px 0 50px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-bottom: 0;
        }

        .hero-content-wrapper {
            position: relative;
            z-index: 3;
            width: 100%;
        }

        .country-hero-content h1 {
            font-size: 2.8rem;
            margin-bottom: 15px;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out;
        }

        .country-hero-content h1 i {
            margin-right: 15px;
            color: var(--paysbas-orange);
            animation: pulse 2s infinite;
            text-shadow: 0 0 10px rgba(255, 155, 0, 0.5);
        }

        .country-hero-content p {
            font-size: 1.2rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto 25px;
            font-weight: 300;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .hero-stats {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin: 30px 0;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .stat {
            text-align: center;
            position: relative;
            background: rgba(255, 255, 255, 0.15);
            padding: 15px 20px;
            border-radius: 12px;
            backdrop-filter: blur(10px);
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
            font-size: 0.9rem;
            opacity: 0.9;
            font-weight: 500;
            color: white;
        }

        .paysbas-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 30%, rgba(174, 28, 40, 0.1) 2px, transparent 2px),
                radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
                radial-gradient(circle at 40% 80%, rgba(33, 70, 139, 0.1) 3px, transparent 3px);
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
                rgba(174, 28, 40, 0.8) 0%, 
                rgba(255, 255, 255, 0.8) 50%, 
                rgba(33, 70, 139, 0.8) 100%);
            z-index: 2;
        }

        .paysbas-flag-element {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 70px;
            height: 45px;
            background: 
                linear-gradient(180deg, 
                    var(--paysbas-red) 0% 33%, 
                    var(--paysbas-white) 33% 66%, 
                    var(--paysbas-blue) 66% 100%);
            z-index: 3;
            opacity: 0.8;
            border-radius: 3px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
            animation: float 3s ease-in-out infinite;
        }

        /* Section Slider MODIFIÉE avec deux images par vue */
        .paysbas-slider {
            background: linear-gradient(135deg, #FFF5F5 0%, #F0F8FF 100%);
            padding: 60px 20px;
            margin: 0;
            position: relative;
            width: 100%;
        }

        .paysbas-slider::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--paysbas-red) 33%, var(--paysbas-white) 33%, var(--paysbas-white) 66%, var(--paysbas-blue) 66%);
        }

        .slider-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
        }

        .slider-container h2 {
            text-align: center;
            font-size: 2.2rem;
            margin-bottom: 15px;
            color: var(--paysbas-dark-blue);
            font-weight: 700;
        }

        .slider-subtitle {
            text-align: center;
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 40px;
            font-style: italic;
        }

        .slider-wrapper {
            position: relative;
            width: 100%;
            margin: 0 auto;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: var(--shadow-paysbas);
        }

        .slider-track {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        /* Vue contenant deux slides */
        .slide-view {
            min-width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .slide-duo {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            width: 100%;
            max-width: 1000px;
        }

        .slide {
            position: relative;
            height: 350px;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: var(--shadow-paysbas);
            transition: var(--transition);
        }

        .slide:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-paysbas-hover);
        }

        .image-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .slide:hover img {
            transform: scale(1.03);
        }

        .slide-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(174, 28, 40, 0.8));
            color: white;
            padding: 25px 20px 20px;
            transform: translateY(0);
            transition: var(--transition);
        }

        .slide:hover .slide-content {
            background: linear-gradient(transparent, rgba(174, 28, 40, 0.95));
        }

        .slide-content h3 {
            font-size: 1.5rem;
            margin-bottom: 8px;
            font-weight: 700;
        }

        .slide-content p {
            font-size: 1rem;
            opacity: 0.9;
            margin: 0;
        }

        .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
            box-shadow: var(--shadow-paysbas);
            color: var(--paysbas-red);
        }

        .slider-btn:hover {
            background: var(--paysbas-red);
            color: white;
            transform: translateY(-50%) scale(1.1);
        }

        .prev-btn {
            left: 20px;
        }

        .next-btn {
            right: 20px;
        }

        .slider-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 10;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: var(--transition);
        }

        .indicator.active {
            background: white;
            transform: scale(1.3);
        }

        .indicator:hover {
            background: white;
        }

        /* Services Container */
        .services-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 80px 20px;
        }

        .services-container h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--paysbas-dark-blue);
            position: relative;
            font-weight: 700;
        }

        .services-container h2:after {
            content: '';
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 4px;
            background: var(--gradient-paysbas);
            border-radius: 2px;
        }

        .services-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 50px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Services Grid - 3 cartes sur la même ligne */
        .services-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 40px;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .paysbas-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-paysbas);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid rgba(174, 28, 40, 0.1);
            height: fit-content;
        }

        .paysbas-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: var(--shadow-paysbas-hover);
        }

        .card-icon {
            position: absolute;
            top: 18px;
            right: 18px;
            background: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
            box-shadow: 0 6px 15px rgba(174, 28, 40, 0.25);
            transition: all 0.3s ease;
        }

        .paysbas-card:hover .card-icon {
            transform: scale(1.08) rotate(5deg);
            background: var(--gradient-paysbas);
        }

        .paysbas-card:hover .card-icon i {
            color: white;
        }

        .card-icon i {
            font-size: 26px;
            color: var(--paysbas-red);
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
            background: linear-gradient(to bottom, transparent 0%, rgba(174, 28, 40, 0.7) 100%);
            transition: all 0.3s ease;
        }

        .paysbas-card:hover .card-image img {
            transform: scale(1.08);
        }

        .paysbas-card:hover .card-overlay {
            background: linear-gradient(to bottom, transparent 0%, var(--paysbas-red) 100%);
        }

        .card-flag {
            position: absolute;
            top: 18px;
            left: 18px;
            width: 60px;
            height: 42px;
            background: 
                linear-gradient(180deg, 
                    var(--paysbas-red) 0% 33%, 
                    var(--paysbas-white) 33% 66%, 
                    var(--paysbas-blue) 66% 100%);
            border-radius: 4px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        .card-content {
            padding: 25px;
            text-align: center;
        }

        .card-content h3 {
            font-size: 1.4rem;
            margin-bottom: 15px;
            color: var(--paysbas-dark-blue);
            font-weight: 700;
        }

        .card-content p {
            color: #666;
            margin-bottom: 18px;
            line-height: 1.5;
            font-size: 1rem;
            min-height: 50px;
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
            font-size: 0.95rem;
        }

        .service-features i {
            color: var(--paysbas-red);
            margin-right: 8px;
            font-size: 0.9rem;
            min-width: 16px;
        }

        .card-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .paysbas-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 20px;
            background: var(--gradient-paysbas);
            color: white;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            gap: 8px;
            font-size: 1rem;
            cursor: pointer;
        }

        .paysbas-btn:hover {
            background: var(--gradient-paysbas-light);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(174, 28, 40, 0.4);
        }

        .paysbas-btn i {
            transition: transform 0.3s ease;
        }

        .paysbas-btn:hover i {
            transform: translateX(5px);
        }

        .paysbas-info {
            background: linear-gradient(135deg, var(--paysbas-light-blue) 0%, #c2d6f5 100%);
            border-left: 4px solid var(--paysbas-blue);
            border-radius: 12px;
            padding: 25px;
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-top: 50px;
        }

        .info-icon {
            font-size: 1.8rem;
            color: var(--paysbas-blue);
        }

        .info-content h4 {
            color: var(--paysbas-dark-blue);
            margin-bottom: 10px;
            font-size: 1.3rem;
        }

        .info-content p {
            color: #666;
            line-height: 1.6;
            font-size: 1rem;
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
        @media (max-width: 1200px) {
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 25px;
                max-width: 800px;
            }
            
            .paysbas-card {
                max-width: 100%;
            }
        }

        @media (max-width: 992px) {
            .country-hero-content h1 {
                font-size: 2.2rem;
            }
            
            .hero-stats {
                gap: 2rem;
            }
            
            .stat-number {
                font-size: 2rem;
            }
        }

        @media (max-width: 768px) {
            .paysbas-hero {
                padding: 60px 0 40px;
            }
            
            .country-hero-content h1 {
                font-size: 2rem;
            }
            
            .country-hero-content p {
                font-size: 1.1rem;
            }
            
            .hero-stats {
                flex-direction: column;
                gap: 1.5rem;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .card-actions {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
            
            .paysbas-info {
                flex-direction: column;
                text-align: center;
            }
            
            .paysbas-flag-element {
                width: 60px;
                height: 40px;
                top: 10px;
                right: 10px;
            }
            
            .slider-container h2 {
                font-size: 1.8rem;
            }
            
            /* Sur mobile, revenir à une seule image par vue */
            .slide-duo {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .slide {
                height: 300px;
            }
            
            .slide-content {
                padding: 20px 15px 15px;
            }
            
            .slide-content h3 {
                font-size: 1.3rem;
            }
            
            .slide-content p {
                font-size: 0.9rem;
            }
            
            .slider-btn {
                width: 40px;
                height: 40px;
            }
            
            .prev-btn {
                left: 15px;
            }
            
            .next-btn {
                right: 15px;
            }
        }

        @media (max-width: 480px) {
            .country-hero-content h1 {
                font-size: 1.8rem;
            }
            
            .services-container h2 {
                font-size: 2rem;
            }
            
            .card-content {
                padding: 15px;
            }

            .card-content h3 {
                font-size: 1.2rem;
            }
            
            .slide {
                height: 250px;
            }
            
            .slide-content {
                padding: 15px 10px 10px;
            }
            
            .slide-content h3 {
                font-size: 1.2rem;
            }
            
            .slide-content p {
                font-size: 0.8rem;
            }
            
            .slider-btn {
                width: 35px;
                height: 35px;
            }
            
            .prev-btn {
                left: 10px;
            }
            
            .next-btn {
                right: 10px;
            }
            
            .slider-indicators {
                bottom: 15px;
            }
            
            .indicator {
                width: 10px;
                height: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="paysbas-hero">
        <div class="hero-content-wrapper">
            <div class="country-hero-content">
                <h1><i class="fas fa-landmark"></i> Pays-Bas — Services</h1>
                <p>Découvrez nos services spécialisés pour réaliser votre projet aux Pays-Bas</p>
                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number" data-count="90">0</span>
                        <span class="stat-label">de réussite</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" data-count="7">0</span>
                        <span class="stat-label">ans d'expérience</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number" data-count="1200">0</span>
                        <span class="stat-label">clients satisfaits</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="paysbas-pattern"></div>
        <div class="hero-overlay"></div>
        <div class="paysbas-flag-element"></div>
    </div>

    <!-- Section Slider MODIFIÉE avec deux images par vue -->
    <div class="paysbas-slider">
        <div class="slider-container">
            <h2>Découvrez les <span style="background: linear-gradient(135deg, var(--paysbas-red) 0%, var(--paysbas-blue) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Pays-Bas</span></h2>
            <p class="slider-subtitle">Un pays aux multiples facettes et opportunités</p>
            
            <div class="slider-wrapper">
                <div class="slider-track">
                    <!-- Vue 1 -->
                    <div class="slide-view">
                        <div class="slide-duo">
                            <div class="slide">
                                <div class="image-container">
                                    <img src="../images/pays_basacc.png" alt="Amsterdam">
                                </div>
                                <div class="slide-content">
                                    <h3>Amsterdam</h3>
                                    <p>Capitale vibrante aux canaux pittoresques</p>
                                </div>
                            </div>
                            <div class="slide">
                                <div class="image-container">
                                         <img src="../images/pays_basacc.png" alt="Amsterdam">
                                </div>
                                <div class="slide-content">
                                    <h3>Moulins Traditionnels</h3>
                                    <p>Patrimoine UNESCO des Pays-Bas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                 
                
                <!-- Contrôles du slider -->
                <button class="slider-btn prev-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="slider-btn next-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <!-- Indicateurs -->
                <div class="slider-indicators">
                    <span class="indicator active" data-slide="0"></span>
                    <span class="indicator" data-slide="1"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="services-container" id="services">
        <h2>Nos services pour les <span style="background: linear-gradient(135deg, var(--paysbas-red) 0%, var(--paysbas-blue) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Pays-Bas</span></h2>
        <p class="services-subtitle">Choisissez la catégorie qui correspond à votre projet néerlandais</p>
        
        <div class="services-grid">
            <!-- Rendez-vous Card -->
            <div class="paysbas-card">
                <div class="card-icon">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div class="card-image">
                    <img src="../images/visa.jpg" alt="Prise de Rendez-vous Pays-Bas">
                    <div class="card-overlay"></div>
                    <div class="card-flag"></div>
                </div>
                <div class="card-content">
                    <h3>Prise de Rendez-vous</h3>
                    <p>Prenez rendez-vous pour toutes vos démarches consulaires aux Pays-Bas</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle"></i> Rendez-vous ambassade</li>
                        <li><i class="fas fa-check-circle"></i> Entretiens visa</li>
                        <li><i class="fas fa-check-circle"></i> Rappels automatiques</li>
                    </ul>
                    <div class="card-actions">
                        <a class="paysbas-btn" href="/babylone/rendez_vous.php">
                            <span>Prendre RDV</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tourisme Card -->
            <div class="paysbas-card">
                <div class="card-icon">
                    <i class="fa-solid fa-plane"></i>
                </div>
                <div class="card-image">
                    <img src="../images/voy2.jpg" alt="Tourisme aux Pays-Bas">
                    <div class="card-overlay"></div>
                    <div class="card-flag"></div>
                </div>
                <div class="card-content">
                    <h3>Tourisme & Affaire</h3>
                    <p>Visa Schengen, séjours touristiques et découverte des Pays-Bas</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle"></i> Visa Schengen tourisme</li>
                        <li><i class="fas fa-check-circle"></i> Visa Schengen Affaire</li>
                        <li><i class="fas fa-check-circle"></i> Suivi complet</li>
                    </ul>
                    <div class="card-actions">
                        <a class="paysbas-btn" href="/babylone/tourisme/tourisme.php">
                            <span>Découvrir</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Travail Card -->
            <div class="paysbas-card">
                <div class="card-icon">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <div class="card-image">
                    <img src="../images/travail.jpg" alt="Travail aux Pays-Bas">
                    <div class="card-overlay"></div>
                    <div class="card-flag"></div>
                </div>
                <div class="card-content">
                    <h3>Travail</h3>
                    <p>Opportunités professionnelles et permis de travail aux Pays-Bas</p>
                    <ul class="service-features">
                        <li><i class="fas fa-check-circle"></i> Permis de travail</li>
                        <li><i class="fas fa-check-circle"></i> Visa travail Schengen</li>
                        <li><i class="fas fa-check-circle"></i> Recherche d'emploi</li>
                    </ul>
                    <div class="card-actions">
                        <a class="paysbas-btn" href="/babylone/public/travail/travail.php">
                            <span>Découvrir</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="paysbas-info">
            <div class="info-icon">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="info-content">
                <h4>Important : Visa Schengen Pays-Bas</h4>
                <p>Le visa néerlandais vous donne accès à tout l'espace Schengen. Notre expertise couvre l'ensemble des procédures consulaires néerlandaises pour tous types de visas.</p>
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

            // Script pour le slider avec deux images par vue
            const track = document.querySelector('.slider-track');
            const slideViews = document.querySelectorAll('.slide-view');
            const prevBtn = document.querySelector('.prev-btn');
            const nextBtn = document.querySelector('.next-btn');
            const indicators = document.querySelectorAll('.indicator');
            
            let currentView = 0;
            const totalViews = slideViews.length;
            
            // Fonction pour mettre à jour le slider
            function updateSlider() {
                track.style.transform = `translateX(-${currentView * 100}%)`;
                
                // Mettre à jour les indicateurs
                indicators.forEach((indicator, index) => {
                    indicator.classList.toggle('active', index === currentView);
                });
            }
            
            // Événements pour les boutons
            nextBtn.addEventListener('click', function() {
                currentView = (currentView + 1) % totalViews;
                updateSlider();
            });
            
            prevBtn.addEventListener('click', function() {
                currentView = (currentView - 1 + totalViews) % totalViews;
                updateSlider();
            });
            
            // Événements pour les indicateurs
            indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', function() {
                    currentView = index;
                    updateSlider();
                });
            });
            
            // Défilement automatique
            let autoSlide = setInterval(function() {
                currentView = (currentView + 1) % totalViews;
                updateSlider();
            }, 5000);
            
            // Arrêter le défilement automatique au survol
            const sliderWrapper = document.querySelector('.slider-wrapper');
            sliderWrapper.addEventListener('mouseenter', function() {
                clearInterval(autoSlide);
            });
            
            sliderWrapper.addEventListener('mouseleave', function() {
                autoSlide = setInterval(function() {
                    currentView = (currentView + 1) % totalViews;
                    updateSlider();
                }, 5000);
            });
        });
    </script>
</body>
</html>