<?php
require_once __DIR__ . '/../config.php';
$page_title = "Procédures d'Études à l'Étranger";
include __DIR__ . '/../includes/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procédures d'Études à l'Étranger</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ===== VARIABLES ET STYLES GÉNÉRAUX ===== */
        :root {
            --france-blue: #0055A4;
            --france-white: #FFFFFF;
            --france-red: #EF4135;
            
            --belgique-black: #000000;
            --belgique-yellow: #FDDA24;
            --belgique-red: #EF3340;
            
            --roumanie-blue: #002B7F;
            --roumanie-yellow: #FCD116;
            --roumanie-red: #CE1126;
            
            --bulgarie-green: #00966E;
            --bulgarie-white: #FFFFFF;
            --bulgarie-red: #D62612;
            
            --turquie-red: #E30A17;
            --turquie-white: #FFFFFF;
            
            --espagne-red: #AA151B;
            --espagne-yellow: #F1BF00;
            
            --italie-green: #009246;
            --italie-white: #FFFFFF;
            --italie-red: #CE2B37;
            
            --dark: #2C3E50;
            --light: #F8F9FA;
            --shadow: 0 10px 30px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
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
            background: var(--light);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ===== HERO SECTION ===== */
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 3;
        }

        .hero-title {
            font-size: 3.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.9;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .flags-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.1;
            background-image: 
                linear-gradient(45deg, var(--france-blue) 25%, transparent 25%),
                linear-gradient(-45deg, var(--france-blue) 25%, transparent 25%),
                linear-gradient(45deg, transparent 75%, var(--france-red) 75%),
                linear-gradient(-45deg, transparent 75%, var(--france-red) 75%);
            background-size: 100px 100px;
            background-position: 0 0, 0 50px, 50px -50px, -50px 0px;
            animation: flagMove 20s linear infinite;
        }

        @keyframes flagMove {
            0% { background-position: 0 0, 0 50px, 50px -50px, -50px 0px; }
            100% { background-position: 100px 100px, 100px 150px, 150px 50px, 50px 100px; }
        }

        /* ===== COUNTRIES GRID ===== */
        .countries-section {
            padding: 80px 0;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 60px;
            color: var(--dark);
            position: relative;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--france-blue), var(--france-red));
            border-radius: 2px;
        }

        .countries-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }

        .country-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            position: relative;
            cursor: pointer;
        }

        .country-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        /* Styles spécifiques pour chaque pays */
        .country-card.france {
            border-top: 5px solid var(--france-blue);
        }

        .country-card.belgique {
            border-top: 5px solid var(--belgique-black);
        }

        .country-card.roumanie {
            border-top: 5px solid var(--roumanie-blue);
        }

        .country-card.bulgarie {
            border-top: 5px solid var(--bulgarie-green);
        }

        .country-card.turquie {
            border-top: 5px solid var(--turquie-red);
        }

        .country-card.espagne {
            border-top: 5px solid var(--espagne-red);
        }

        .country-card.italie {
            border-top: 5px solid var(--italie-green);
        }

        .country-flag {
            height: 200px;
            position: relative;
            overflow: hidden;
        }

        .country-flag.france {
            background: linear-gradient(90deg, var(--france-blue) 33%, var(--france-white) 33% 66%, var(--france-red) 66%);
        }

        .country-flag.belgique {
            background: linear-gradient(90deg, var(--belgique-black) 33%, var(--belgique-yellow) 33% 66%, var(--belgique-red) 66%);
        }

        .country-flag.roumanie {
            background: linear-gradient(90deg, var(--roumanie-blue) 33%, var(--roumanie-yellow) 33% 66%, var(--roumanie-red) 66%);
        }

        .country-flag.bulgarie {
            background: linear-gradient(180deg, var(--bulgarie-white) 33%, var(--bulgarie-green) 33% 66%, var(--bulgarie-red) 66%);
        }

        .country-flag.turquie {
            background: var(--turquie-white);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .country-flag.turquie::before {
            content: '';
            width: 80px;
            height: 80px;
            background: var(--turquie-red);
            border-radius: 50%;
            position: relative;
        }

        .country-flag.turquie::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            background: white;
            border-radius: 50%;
            transform: rotate(30deg);
            box-shadow: -5px 8px 0 white, -8px 2px 0 white, -5px -6px 0 white, -1px -8px 0 white, 
                        5px -6px 0 white, 8px 0px 0 white, 5px 8px 0 white, 0px 8px 0 white;
        }

        .country-flag.espagne {
            background: linear-gradient(180deg, var(--espagne-red) 25%, var(--espagne-yellow) 25% 75%, var(--espagne-red) 75%);
        }

        .country-flag.italie {
            background: linear-gradient(90deg, var(--italie-green) 33%, var(--italie-white) 33% 66%, var(--italie-red) 66%);
        }

        .country-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .country-content {
            padding: 25px;
        }

        .country-name {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: var(--dark);
        }

        .country-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .country-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            display: block;
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
        }

        .country-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            width: 100%;
            gap: 8px;
        }

        /* Boutons personnalisés pour chaque pays */
        .france .country-btn {
            background: linear-gradient(135deg, var(--france-blue), var(--france-red));
            color: white;
        }

        .belgique .country-btn {
            background: linear-gradient(135deg, var(--belgique-black), var(--belgique-red));
            color: white;
        }

        .roumanie .country-btn {
            background: linear-gradient(135deg, var(--roumanie-blue), var(--roumanie-red));
            color: white;
        }

        .bulgarie .country-btn {
            background: linear-gradient(135deg, var(--bulgarie-green), var(--bulgarie-red));
            color: white;
        }

        .turquie .country-btn {
            background: linear-gradient(135deg, var(--turquie-red), #B50710);
            color: white;
        }

        .espagne .country-btn {
            background: linear-gradient(135deg, var(--espagne-red), var(--espagne-yellow));
            color: white;
        }

        .italie .country-btn {
            background: linear-gradient(135deg, var(--italie-green), var(--italie-red));
            color: white;
        }

        .country-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        /* ===== DETAILS MODAL ===== */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal-content {
            background: white;
            margin: 50px auto;
            border-radius: 20px;
            max-width: 1000px;
            position: relative;
            animation: modalSlideIn 0.5s ease;
        }

        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close-modal {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--error);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 10;
            font-size: 1.2rem;
        }

        .modal-header {
            padding: 40px 40px 20px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .modal-subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .modal-body {
            padding: 30px 40px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-card {
            background: var(--light);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid;
        }

        .info-card h3 {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card ul {
            list-style: none;
        }

        .info-card li {
            margin-bottom: 8px;
            padding-left: 20px;
            position: relative;
        }

        .info-card li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--success);
            font-weight: bold;
        }

        /* Slideshow */
        .slideshow {
            position: relative;
            height: 400px;
            border-radius: 15px;
            overflow: hidden;
            margin: 40px 0;
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease;
            background-size: cover;
            background-position: center;
        }

        .slide.active {
            opacity: 1;
        }

        .slide-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            color: white;
            padding: 30px;
        }

        .slide-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.9);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            z-index: 5;
        }

        .prev-slide { left: 20px; }
        .next-slide { right: 20px; }

        /* Calendrier */
        .calendar-section {
            background: var(--light);
            padding: 30px;
            border-radius: 15px;
            margin: 40px 0;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .calendar-item {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .calendar-month {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }

        .calendar-dates {
            color: #666;
            font-size: 0.9rem;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .countries-grid {
                grid-template-columns: 1fr;
            }
            
            .modal-content {
                margin: 20px;
                width: calc(100% - 40px);
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Styles spécifiques pour chaque pays dans le modal */
        .france-modal .info-card { border-left-color: var(--france-blue); }
        .belgique-modal .info-card { border-left-color: var(--belgique-black); }
        .roumanie-modal .info-card { border-left-color: var(--roumanie-blue); }
        .bulgarie-modal .info-card { border-left-color: var(--bulgarie-green); }
        .turquie-modal .info-card { border-left-color: var(--turquie-red); }
        .espagne-modal .info-card { border-left-color: var(--espagne-red); }
        .italie-modal .info-card { border-left-color: var(--italie-green); }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="flags-background"></div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Étudier à l'Étranger</h1>
                <p class="hero-subtitle">Découvrez les opportunités d'études en France, Belgique, Roumanie, Bulgarie, Turquie, Espagne et Italie</p>
            </div>
        </div>
    </section>

    <!-- Countries Grid -->
    <section class="countries-section">
        <div class="container">
            <h2 class="section-title">Destinations Disponibles</h2>
            <div class="countries-grid">
                <!-- France -->
                <div class="country-card france" onclick="openModal('france')">
                    <div class="country-flag france">
                        <div class="country-icon">
                            <i class="fas fa-landmark"></i>
                        </div>
                    </div>
                    <div class="country-content">
                        <h3 class="country-name">France</h3>
                        <p class="country-description">Système éducatif d'excellence avec des universités prestigieuses et des grandes écoles reconnues internationalement.</p>
                        <div class="country-stats">
                            <div class="stat">
                                <span class="stat-number">3,500€</span>
                                <span class="stat-label">Frais annuels</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">85%</span>
                                <span class="stat-label">Réussite</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">2</span>
                                <span class="stat-label">Sessions</span>
                            </div>
                        </div>
                        <button class="country-btn">
                            <span>Découvrir la Procédure</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Belgique -->
                <div class="country-card belgique" onclick="openModal('belgique')">
                    <div class="country-flag belgique">
                        <div class="country-icon">
                            <i class="fas fa-atom"></i>
                        </div>
                    </div>
                    <div class="country-content">
                        <h3 class="country-name">Belgique</h3>
                        <p class="country-description">Enseignement de qualité avec des frais de scolarité accessibles et une reconnaissance européenne.</p>
                        <div class="country-stats">
                            <div class="stat">
                                <span class="stat-number">835€</span>
                                <span class="stat-label">Frais annuels</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">88%</span>
                                <span class="stat-label">Réussite</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">2</span>
                                <span class="stat-label">Sessions</span>
                            </div>
                        </div>
                        <button class="country-btn">
                            <span>Découvrir la Procédure</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Roumanie -->
                <div class="country-card roumanie" onclick="openModal('roumanie')">
                    <div class="country-flag roumanie">
                        <div class="country-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                    </div>
                    <div class="country-content">
                        <h3 class="country-name">Roumanie</h3>
                        <p class="country-description">Programmes en français et anglais avec des frais de scolarité compétitifs et une riche culture.</p>
                        <div class="country-stats">
                            <div class="stat">
                                <span class="stat-number">2,000€</span>
                                <span class="stat-label">Frais annuels</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">82%</span>
                                <span class="stat-label">Réussite</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">3</span>
                                <span class="stat-label">Sessions</span>
                            </div>
                        </div>
                        <button class="country-btn">
                            <span>Découvrir la Procédure</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Bulgarie -->
                <div class="country-card bulgarie" onclick="openModal('bulgarie')">
                    <div class="country-flag bulgarie">
                        <div class="country-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                    </div>
                    <div class="country-content">
                        <h3 class="country-name">Bulgarie</h3>
                        <p class="country-description">Études médicales réputées avec des programmes en anglais et des coûts de vie abordables.</p>
                        <div class="country-stats">
                            <div class="stat">
                                <span class="stat-number">3,500€</span>
                                <span class="stat-label">Frais annuels</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">79%</span>
                                <span class="stat-label">Réussite</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">2</span>
                                <span class="stat-label">Sessions</span>
                            </div>
                        </div>
                        <button class="country-btn">
                            <span>Découvrir la Procédure</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Turquie -->
                <div class="country-card turquie" onclick="openModal('turquie')">
                    <div class="country-flag turquie">
                        <div class="country-icon">
                            <i class="fas fa-mosque"></i>
                        </div>
                    </div>
                    <div class="country-content">
                        <h3 class="country-name">Turquie</h3>
                        <p class="country-description">Universités modernes avec des programmes en anglais et une position stratégique entre l'Europe et l'Asie.</p>
                        <div class="country-stats">
                            <div class="stat">
                                <span class="stat-number">1,500€</span>
                                <span class="stat-label">Frais annuels</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">81%</span>
                                <span class="stat-label">Réussite</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">2</span>
                                <span class="stat-label">Sessions</span>
                            </div>
                        </div>
                        <button class="country-btn">
                            <span>Découvrir la Procédure</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Espagne -->
                <div class="country-card espagne" onclick="openModal('espagne')">
                    <div class="country-flag espagne">
                        <div class="country-icon">
                            <i class="fas fa-sun"></i>
                        </div>
                    </div>
                    <div class="country-content">
                        <h3 class="country-name">Espagne</h3>
                        <p class="country-description">Qualité de vie exceptionnelle avec des universités historiques et un climat méditerranéen.</p>
                        <div class="country-stats">
                            <div class="stat">
                                <span class="stat-number">2,500€</span>
                                <span class="stat-label">Frais annuels</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">84%</span>
                                <span class="stat-label">Réussite</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">2</span>
                                <span class="stat-label">Sessions</span>
                            </div>
                        </div>
                        <button class="country-btn">
                            <span>Découvrir la Procédure</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Italie -->
                <div class="country-card italie" onclick="openModal('italie')">
                    <div class="country-flag italie">
                        <div class="country-icon">
                            <i class="fas fa-pizza-slice"></i>
                        </div>
                    </div>
                    <div class="country-content">
                        <h3 class="country-name">Italie</h3>
                        <p class="country-description">Patrimoine culturel riche avec des universités prestigieuses et une gastronomie mondialement reconnue.</p>
                        <div class="country-stats">
                            <div class="stat">
                                <span class="stat-number">2,000€</span>
                                <span class="stat-label">Frais annuels</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">83%</span>
                                <span class="stat-label">Réussite</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number">2</span>
                                <span class="stat-label">Sessions</span>
                            </div>
                        </div>
                        <button class="country-btn">
                            <span>Découvrir la Procédure</span>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal France -->
    <div id="france-modal" class="modal france-modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal()">×</button>
            <div class="modal-header">
                <h2 class="modal-title">Étudier en France</h2>
                <p class="modal-subtitle">Procédures, conditions et calendrier pour étudier en France</p>
            </div>
            <div class="modal-body">
                <!-- Slideshow -->
                <div class="slideshow">
                    <div class="slide active" style="background-image: url('../images/france-1.jpg')">
                        <div class="slide-content">
                            <h3>Universités d'Excellence</h3>
                            <p>Découvrez les établissements prestigieux français</p>
                        </div>
                    </div>
                    <div class="slide" style="background-image: url('../images/france-2.jpg')">
                        <div class="slide-content">
                            <h3>Vie Étudiante</h3>
                            <p>Une expérience culturelle et académique unique</p>
                        </div>
                    </div>
                    <button class="slide-nav prev-slide" onclick="changeSlide(-1)">‹</button>
                    <button class="slide-nav next-slide" onclick="changeSlide(1)">›</button>
                </div>

                <!-- Informations détaillées -->
                <div class="info-grid">
                    <div class="info-card">
                        <h3><i class="fas fa-graduation-cap"></i> Conditions d'Admission</h3>
                        <ul>
                            <li>Baccalauréat ou équivalent</li>
                            <li>Test de langue française (DELF/DALF)</li>
                            <li>Lettre de motivation</li>
                            <li>Relevés de notes</li>
                            <li>CV académique</li>
                        </ul>
                    </div>

                    <div class="info-card">
                        <h3><i class="fas fa-file-alt"></i> Documents Requis</h3>
                        <ul>
                            <li>Passeport valide</li>
                            <li>Diplômes et relevés de notes</li>
                            <li>Test de langue</li>
                            <li>Lettres de recommandation</li>
                            <li>Justificatif financier</li>
                        </ul>
                    </div>

                    <div class="info-card">
                        <h3><i class="fas fa-euro-sign"></i> Frais de Scolarité</h3>
                        <ul>
                            <li>Licence : 2,770€/an</li>
                            <li>Master : 3,770€/an</li>
                            <li>Doctorat : 380€/an</li>
                            <li>Frais de visa : 50-99€</li>
                            <li>Assurance santé : 200-600€/an</li>
                        </ul>
                    </div>
                </div>

                <!-- Calendrier -->
                <div class="calendar-section">
                    <h3><i class="fas fa-calendar-alt"></i> Calendrier Académique</h3>
                    <div class="calendar-grid">
                        <div class="calendar-item">
                            <div class="calendar-month">Janvier-Février</div>
                            <div class="calendar-dates">Dépôt des dossiers Session 1</div>
                        </div>
                        <div class="calendar-item">
                            <div class="calendar-month">Mars-Avril</div>
                            <div class="calendar-dates">Entretiens et décisions</div>
                        </div>
                        <div class="calendar-item">
                            <div class="calendar-month">Mai-Juin</div>
                            <div class="calendar-dates">Dépôt des dossiers Session 2</div>
                        </div>
                        <div class="calendar-item">
                            <div class="calendar-month">Septembre</div>
                            <div class="calendar-dates">Rentrée universitaire</div>
                        </div>
                    </div>
                </div>

                <!-- Bouton de postulation -->
                <a href="/babylone/public/france/etude/index.php" class="country-btn" style="text-decoration: none;">
                    <span>Postuler pour la France</span>
                    <i class="fas fa-paper-plane"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Les autres modals suivent la même structure -->
    <!-- Belgique Modal -->
    <div id="belgique-modal" class="modal belgique-modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal()">×</button>
            <div class="modal-header">
                <h2 class="modal-title">Étudier en Belgique</h2>
                <p class="modal-subtitle">Procédures, conditions et calendrier pour étudier en Belgique</p>
            </div>
            <div class="modal-body">
                <!-- Contenu similaire à la France -->
                <div class="info-grid">
                    <div class="info-card">
                        <h3><i class="fas fa-graduation-cap"></i> Conditions d'Admission</h3>
                        <ul>
                            <li>Diplôme de fin d'études secondaires</li>
                            <li>Test d'équivalence si nécessaire</li>
                            <li>Test de langue française/néerlandais</li>
                            <li>Examen d'entrée pour certaines filières</li>
                        </ul>
                    </div>
                    <!-- Autres sections... -->
                </div>
                <a href="/babylone/public/belgique/etude/index.php" class="country-btn" style="text-decoration: none;">
                    <span>Postuler pour la Belgique</span>
                    <i class="fas fa-paper-plane"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Script JavaScript -->
    <script>
        let currentSlide = 0;
        let currentModal = null;

        function openModal(country) {
            currentModal = document.getElementById(`${country}-modal`);
            currentModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            currentSlide = 0;
            showSlide(0);
        }

        function closeModal() {
            if (currentModal) {
                currentModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }

        function changeSlide(direction) {
            const slides = document.querySelectorAll('.slide');
            currentSlide = (currentSlide + direction + slides.length) % slides.length;
            showSlide(currentSlide);
        }

        function showSlide(n) {
            const slides = document.querySelectorAll('.slide');
            slides.forEach(slide => slide.classList.remove('active'));
            slides[n].classList.add('active');
        }

        // Fermer le modal en cliquant à l'extérieur
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeModal();
            }
        }

        // Navigation au clavier
        document.addEventListener('keydown', function(event) {
            if (currentModal && currentModal.style.display === 'block') {
                if (event.key === 'Escape') {
                    closeModal();
                } else if (event.key === 'ArrowLeft') {
                    changeSlide(-1);
                } else if (event.key === 'ArrowRight') {
                    changeSlide(1);
                }
            }
        });

        // Auto-slideshow
        setInterval(() => {
            if (currentModal && currentModal.style.display === 'block') {
                changeSlide(1);
            }
        }, 5000);
    </script>
</body>
</html>

<?php include __DIR__ . '/../includes/footer.php'; ?>