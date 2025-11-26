<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étudier en Belgique | Procédures & Universités</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #000000;
            --secondary: #FDDA24;
            --accent: #EF3340;
            --light: #f8f9fa;
            --dark: #2c3e50;
            --success: #28a745;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #000000 0%, #333333 50%, #FDDA24 100%);
            color: #333;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header Styles */
        header {
            background-color: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 60px 0 80px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1516450360452-9312f5e86fc7?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80') center/cover;
            opacity: 0.3;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
        }

        .hero-content h1 {
            font-size: 3.2rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        /* Navigation */
        nav {
            background-color: rgba(253, 218, 36, 0.95);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-left: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--primary);
            font-weight: 500;
            transition: color 0.3s ease;
            position: relative;
        }

        .nav-links a:hover {
            color: var(--accent);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--accent);
            transition: width 0.3s ease;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        /* Section Styles */
        section {
            padding: 80px 0;
        }

        .section-light {
            background-color: white;
        }

        .section-gray {
            background-color: #f8f9fa;
        }

        .section-yellow {
            background-color: #fff9e6;
        }

        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
            position: relative;
            display: inline-block;
        }

        .section-title h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            border-radius: 2px;
        }

        .section-title p {
            font-size: 1.2rem;
            color: var(--dark);
            max-width: 700px;
            margin: 0 auto;
        }

        /* Grid Layouts */
        .procedures-grid, .universities-grid, .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 50px;
        }

        /* Card Styles */
        .procedure-card, .university-card, .service-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }

        .procedure-card:hover, .university-card:hover, .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .card-image {
            height: 200px;
            overflow: hidden;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .procedure-card:hover .card-image img,
        .university-card:hover .card-image img,
        .service-card:hover .card-image img {
            transform: scale(1.05);
        }

        .card-content {
            padding: 25px;
        }

        .card-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .card-content h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--primary);
        }

        .card-content p {
            color: #666;
            margin-bottom: 20px;
        }

        .feature-list {
            list-style: none;
            margin-top: 20px;
        }

        .feature-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            position: relative;
            padding-left: 25px;
        }

        .feature-list li:before {
            content: '✓';
            position: absolute;
            left: 0;
            color: var(--success);
            font-weight: bold;
        }

        /* Timeline */
        .timeline {
            position: relative;
            max-width: 800px;
            margin: 50px auto;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 4px;
            background: var(--secondary);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -2px;
        }

        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }

        .timeline-item:nth-child(odd) {
            left: 0;
        }

        .timeline-item:nth-child(even) {
            left: 50%;
        }

        .timeline-content {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border-left: 4px solid var(--primary);
        }

        .timeline-date {
            font-weight: 600;
            color: var(--accent);
            margin-bottom: 10px;
        }

        /* CTA Button */
        .cta-button {
            display: inline-block;
            background: linear-gradient(to right, var(--primary), var(--accent));
            color: white;
            padding: 15px 40px;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            color: white;
        }

        .cta-section {
            text-align: center;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: 80px 0;
        }

        .cta-section h2 {
            color: white;
            margin-bottom: 20px;
        }

        .cta-section p {
            max-width: 700px;
            margin: 0 auto 30px;
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Footer */
        footer {
            background-color: var(--dark);
            color: white;
            padding: 50px 0 20px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-column h3 {
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background-color: var(--secondary);
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #ccc;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #444;
            font-size: 0.9rem;
            color: #aaa;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .timeline::after {
                left: 31px;
            }
            
            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }
            
            .timeline-item:nth-child(even) {
                left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <div class="container nav-container">
            <div class="logo">ÉtudesBelgique</div>
            <ul class="nav-links">
                <li><a href="#accueil">Accueil</a></li>
                <li><a href="#procedures">Procédures</a></li>
                <li><a href="#universites">Universités</a></li>
                <li><a href="#ecoles">Écoles</a></li>
                <li><a href="#visa">Visa</a></li>
                <li><a href="#services">Services</a></li>
            </ul>
        </div>
    </nav>

    <!-- Header/Hero Section -->
    <header id="accueil">
        <div class="container hero-content">
            <h1>Étudier en Belgique</h1>
            <p>Découvrez les procédures d'admission, les universités d'excellence et les écoles renommées de la Belgique. Votre avenir académique commence ici.</p>
            <a href="#postuler" class="cta-button">Commencer Maintenant</a>
        </div>
    </header>

    <!-- Procedures Section -->
    <section id="procedures" class="section-light">
        <div class="container">
            <div class="section-title">
                <h2>Procédures d'Études en Belgique</h2>
                <p>Découvrez les démarches spécifiques pour étudier en Belgique selon la communauté linguistique</p>
            </div>
            
            <div class="procedures-grid">
                <div class="procedure-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1580852300657-4b7bb0bc4d5c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Fédération Wallonie-Bruxelles">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-french-fries"></i>
                        </div>
                        <h3>Fédération Wallonie-Bruxelles</h3>
                        <p>Procédures pour les études en français en Wallonie et à Bruxelles</p>
                        <ul class="feature-list">
                            <li>Demande d'équivalence de diplôme</li>
                            <li>Inscription directe aux universités</li>
                            <li>Test linguistique possible</li>
                            <li>Délais spécifiques à respecter</li>
                        </ul>
                    </div>
                </div>
                
                <div class="procedure-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1583212292456-acea866dc5e9?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Flandre">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-wind"></i>
                        </div>
                        <h3>Communauté Flamande</h3>
                        <p>Études en néerlandais avec procédures spécifiques</p>
                        <ul class="feature-list">
                            <li>Procédure via les universités flamandes</li>
                            <li>Tests de néerlandais requis</li>
                            <li>Pré-inscription souvent nécessaire</li>
                            <li>Examen d'admission possible</li>
                        </ul>
                    </div>
                </div>
                
                <div class="procedure-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1555881400-74cce8d6c9b4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Équivalence de diplôme">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3>Équivalence de Diplôme</h3>
                        <p>Processus obligatoire pour la reconnaissance des diplômes étrangers</p>
                        <ul class="feature-list">
                            <li>Dossier complet à constituer</li>
                            <li>Délai de traitement : 2-4 mois</li>
                            <li>Frais de dossier variables</li>
                            <li>Documents traduits requis</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Universities Section -->
    <section id="universites" class="section-gray">
        <div class="container">
            <div class="section-title">
                <h2>Universités Belges Renommées</h2>
                <p>Découvrez les établissements d'enseignement supérieur d'excellence en Belgique</p>
            </div>
            
            <div class="universities-grid">
                <div class="university-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Université Catholique de Louvain">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <h3>UCLouvain</h3>
                        <p>Université Catholique de Louvain - Excellence en recherche</p>
                        <ul class="feature-list">
                            <li>Classée parmi les top 150 mondiaux</li>
                            <li>Large choix de programmes</li>
                            <li>Vie étudiante dynamique</li>
                            <li>Campus international</li>
                        </ul>
                    </div>
                </div>
                
                <div class="university-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1562774053-701939374585?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Université Libre de Bruxelles">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-atom"></i>
                        </div>
                        <h3>ULB</h3>
                        <p>Université Libre de Bruxelles - Tradition d'excellence</p>
                        <ul class="feature-list">
                            <li>Forte dimension internationale</li>
                            <li>Recherche de pointe</li>
                            <li>Située au cœur de l'Europe</li>
                            <li>Multiculturalisme</li>
                        </ul>
                    </div>
                </div>
                
                <div class="university-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1541336032412-2048a678540d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="KU Leuven">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-flask"></i>
                        </div>
                        <h3>KU Leuven</h3>
                        <p>Meilleure université de Belgique, classée top 50 mondiale</p>
                        <ul class="feature-list">
                            <li>Enseignement en néerlandais</li>
                            <li>Programmes internationaux en anglais</li>
                            <li>Centre de recherche innovant</li>
                            <li>Réseau alumni prestigieux</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Schools Section -->
    <section id="ecoles" class="section-light">
        <div class="container">
            <div class="section-title">
                <h2>Écoles Spécialisées et Hautes Écoles</h2>
                <p>Formations professionnelles et techniques d'excellence</p>
            </div>
            
            <div class="procedures-grid">
                <div class="procedure-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1553877522-43269d4ea984?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Écoles d'art">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3>Écoles d'Art et Design</h3>
                        <p>Formations artistiques de renommée internationale</p>
                        <ul class="feature-list">
                            <li>École nationale supérieure des arts visuels</li>
                            <li>Formations en design graphique</li>
                            <li>Architecture d'intérieur</li>
                            <li>Portfolio requis</li>
                        </ul>
                    </div>
                </div>
                
                <div class="procedure-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1532094349884-543bc11b234d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Écoles de commerce">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Écoles de Commerce</h3>
                        <p>Formations en gestion et management</p>
                        <ul class="feature-list">
                            <li>Solvay Brussels School</li>
                            <li>ICHEC Brussels Management School</li>
                            <li>Programmes en français/anglais</li>
                            <li>Stages en entreprises</li>
                        </ul>
                    </div>
                </div>
                
                <div class="procedure-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1581094794329-c6fe60aee3f2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Écoles d'ingénieurs">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3>Écoles d'Ingénieurs</h3>
                        <p>Formations techniques et scientifiques</p>
                        <ul class="feature-list">
                            <li>École Polytechnique de Bruxelles</li>
                            <li>Facultés des sciences appliquées</li>
                            <li>Programmes spécialisés</li>
                            <li>Recherche appliquée</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Visa Section -->
    <section id="visa" class="section-yellow">
        <div class="container">
            <div class="section-title">
                <h2>Procédure Visa Étudiant Belgique</h2>
                <p>Les étapes essentielles pour obtenir votre visa étudiant</p>
            </div>
            
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">Étape 1</div>
                        <h3>Admission dans un établissement</h3>
                        <p>Obtenir une attestation d'inscription ou de pré-inscription d'une université ou école belge.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">Étape 2</div>
                        <h3>Équivalence de diplôme</h3>
                        <p>Faire reconnaître son diplôme par la Fédération Wallonie-Bruxelles ou la Communauté flamande.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">Étape 3</div>
                        <h3>Preuve de moyens financiers</h3>
                        <p>Démontrer sa capacité à financer ses études et son séjour (environ 700€/mois).</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">Étape 4</div>
                        <h3>Assurance maladie</h3>
                        <p>Souscrire une assurance maladie valable en Belgique pour toute la durée du séjour.</p>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-date">Étape 5</div>
                        <h3>Dépôt du dossier</h3>
                        <p>Déposer la demande de visa long séjour auprès de l'ambassade ou consulat de Belgique.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="section-light">
        <div class="container">
            <div class="section-title">
                <h2>Nos Services d'Accompagnement</h2>
                <p>Nous vous guidons à chaque étape de votre projet d'études en Belgique</p>
            </div>
            
            <div class="services-grid">
                <div class="service-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1581094794329-c6fe60aee3f2?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Orientation académique">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-compass"></i>
                        </div>
                        <h3>Orientation Académique</h3>
                        <p>Aide au choix de l'établissement et du programme correspondant à votre profil.</p>
                    </div>
                </div>
                
                <div class="service-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1450101499163-c8848c66ca85?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Dossier d'équivalence">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>Dossier d'Équivalence</h3>
                        <p>Assistance complète pour la reconnaissance de vos diplômes.</p>
                    </div>
                </div>
                
                <div class="service-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1586281380349-632531db7ed4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Procédure visa">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-passport"></i>
                        </div>
                        <h3>Accompagnement Visa</h3>
                        <p>Guide complet pour la constitution de votre dossier de visa étudiant.</p>
                    </div>
                </div>
                
                <div class="service-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1523240795612-9a054b0db644?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Logement étudiant">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <h3>Recherche de Logement</h3>
                        <p>Aide à la recherche de résidence étudiante ou appartement.</p>
                    </div>
                </div>
                
                <div class="service-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Préparation linguistique">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-language"></i>
                        </div>
                        <h3>Préparation Linguistique</h3>
                        <p>Cours de français ou néerlandais selon la communauté choisie.</p>
                    </div>
                </div>
                
                <div class="service-card">
                    <div class="card-image">
                        <img src="https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Intégration">
                    </div>
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-hands-helping"></i>
                        </div>
                        <h3>Service d'Intégration</h3>
                        <p>Accompagnement pour vos démarches administratives en Belgique.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" id="postuler">
        <div class="container">
            <h2>Prêt à Démarrer Vos Études en Belgique?</h2>
            <p>Notre équipe d'experts vous accompagne dans toutes les démarches pour réussir votre projet d'études en Belgique. Contactez-nous dès maintenant pour une consultation personnalisée.</p>
            <a href="#postuler" class="cta-button">Commencer Mon Projet</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>ÉtudesBelgique</h3>
                    <p>Votre partenaire de confiance pour réussir votre projet d'études en Belgique. Accompagnement personnalisé et expertise depuis plus de 10 ans.</p>
                </div>
                
                <div class="footer-column">
                    <h3>Liens Rapides</h3>
                    <ul class="footer-links">
                        <li><a href="#accueil">Accueil</a></li>
                        <li><a href="#procedures">Procédures</a></li>
                        <li><a href="#universites">Universités</a></li>
                        <li><a href="#ecoles">Écoles</a></li>
                        <li><a href="#visa">Visa</a></li>
                        <li><a href="#services">Services</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul class="footer-links">
                        <li><i class="fas fa-envelope"></i> info@etudesbelgique.com</li>
                        <li><i class="fas fa-phone"></i> +32 2 123 45 67</li>
                        <li><i class="fas fa-map-marker-alt"></i> Avenue de l'Université, 1000 Bruxelles</li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2025 ÉtudesBelgique. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <script>
        // Animation au défilement
        document.addEventListener('DOMContentLoaded', function() {
            const fadeElements = document.querySelectorAll('.procedure-card, .university-card, .service-card');
            
            const fadeInOnScroll = function() {
                fadeElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementVisible = 150;
                    
                    if (elementTop < window.innerHeight - elementVisible) {
                        element.style.opacity = "1";
                        element.style.transform = "translateY(0)";
                    }
                });
            };
            
            // Initialiser l'opacité
            fadeElements.forEach(element => {
                element.style.opacity = "0";
                element.style.transform = "translateY(30px)";
                element.style.transition = "opacity 0.6s ease, transform 0.6s ease";
            });
            
            // Vérifier la position au chargement
            fadeInOnScroll();
            
            // Vérifier la position au défilement
            window.addEventListener('scroll', fadeInOnScroll);
            
            // Lissage des ancres
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const targetId = this.getAttribute('href');
                    if(targetId === '#') return;
                    
                    const targetElement = document.querySelector(targetId);
                    if(targetElement) {
                        window.scrollTo({
                            top: targetElement.offsetTop - 80,
                            behavior: 'smooth'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>