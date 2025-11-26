<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Avenir en France - Guide des Procédures</title>
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #283593;
            --accent: #3949ab;
            --light: #e8eaf6;
            --dark: #0d1440;
            --text: #212121;
            --text-light: #757575;
            --success: #2e7d32;
            --warning: #f57c00;
            --gradient: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
            --campus: #1565c0;
            --parcoursup: #00838f;
            --saclay: #6a1b9a;
            --ecoles: #d84315;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text);
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .hero {
            background: var(--gradient);
            color: white;
            padding: 80px 20px;
            text-align: center;
            border-radius: 0 0 20px 20px;
            margin-bottom: 60px;
            box-shadow: 0 10px 30px rgba(26, 35, 126, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
        }
        
        .hero h1 {
            font-size: 3.2rem;
            margin-bottom: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        
        .hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 30px;
            opacity: 0.9;
        }
        
        .cta-button {
            display: inline-block;
            background: white;
            color: var(--primary);
            padding: 16px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        /* Introduction */
        .intro {
            text-align: center;
            margin-bottom: 70px;
            padding: 0 20px;
        }
        
        .intro h2 {
            font-size: 2.2rem;
            color: var(--primary);
            margin-bottom: 25px;
        }
        
        .intro p {
            font-size: 1.2rem;
            max-width: 900px;
            margin: 0 auto;
            color: var(--text-light);
        }
        
        /* Procedure Sections */
        .procedure {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 50px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        
        .procedure:hover {
            transform: translateY(-5px);
        }
        
        .procedure-header {
            padding: 30px;
            color: white;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .campus-header {
            background: var(--campus);
        }
        
        .parcoursup-header {
            background: var(--parcoursup);
        }
        
        .saclay-header {
            background: var(--saclay);
        }
        
        .ecoles-header {
            background: var(--ecoles);
        }
        
        .procedure-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        
        .procedure-title {
            flex: 1;
        }
        
        .procedure-header h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .procedure-tag {
            display: inline-block;
            background: rgba(255, 255, 255, 0.3);
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .procedure-body {
            padding: 40px;
            display: flex;
            gap: 40px;
            align-items: flex-start;
        }
        
        .procedure-content {
            flex: 1;
        }
        
        .procedure-image {
            flex: 0 0 300px;
            height: 200px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }
        
        .image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .campus-image {
            background: linear-gradient(135deg, #1565c0, #42a5f5);
        }
        
        .parcoursup-image {
            background: linear-gradient(135deg, #00838f, #4dd0e1);
        }
        
        .saclay-image {
            background: linear-gradient(135deg, #6a1b9a, #ab47bc);
        }
        
        .ecoles-image {
            background: linear-gradient(135deg, #d84315, #ff8a65);
        }
        
        .procedure-body p {
            margin-bottom: 25px;
            font-size: 1.1rem;
            line-height: 1.7;
            color: var(--text-light);
        }
        
        .highlight {
            background: var(--light);
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
            border-left: 5px solid var(--accent);
        }
        
        .highlight h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        
        .benefits {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .benefit-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid var(--accent);
        }
        
        .benefit-item h4 {
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        /* Language Section */
        .language-section {
            background: white;
            border-radius: 16px;
            padding: 50px 40px;
            margin: 60px 0;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            text-align: center;
        }
        
        .language-section h2 {
            color: var(--primary);
            margin-bottom: 40px;
            font-size: 2rem;
        }
        
        .language-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .language-card {
            background: var(--light);
            padding: 30px;
            border-radius: 12px;
            transition: transform 0.3s ease;
        }
        
        .language-card:hover {
            transform: translateY(-5px);
        }
        
        .language-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        
        .language-card p {
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .language-level {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 10px;
        }
        
        /* Navigation */
        .navigation {
            text-align: center;
            margin: 70px 0 40px;
        }
        
        .nav-button {
            display: inline-block;
            background: var(--gradient);
            color: white;
            padding: 18px 50px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(26, 35, 126, 0.3);
        }
        
        .nav-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(26, 35, 126, 0.4);
        }
        
        /* Footer */
        footer {
            text-align: center;
            padding: 40px 0;
            color: var(--text-light);
            font-size: 0.95rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            margin-top: 60px;
        }
        
        /* Stats Section */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 50px 0;
        }
        
        .stat-card {
            background: white;
            padding: 30px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 1rem;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .procedure-body {
                flex-direction: column;
                padding: 25px;
            }
            
            .procedure-image {
                flex: 0 0 200px;
                width: 100%;
            }
            
            .benefits {
                grid-template-columns: 1fr;
            }
            
            .language-cards {
                grid-template-columns: 1fr;
            }
            
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Réalisez votre rêve d'étudier en France</h1>
                <p>Découvrez les procédures simplifiées pour intégrer les meilleures formations françaises</p>
                <a href="#procedures" class="cta-button">Découvrir les opportunités</a>
            </div>
        </section>
        
        <!-- Stats Section -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">95%</div>
                <div class="stat-label">de réussite aux procédures</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">3500+</div>
                <div class="stat-label">formations disponibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">72</div>
                <div class="stat-label">pays éligibles</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">48h</div>
                <div class="stat-label">délai moyen de réponse</div>
            </div>
        </div>
        
        <!-- Introduction -->
        <section class="intro">
            <h2>Un avenir prometteur vous attend en France</h2>
            <p>Chaque année, des milliers d'étudiants internationaux réalisent leur projet d'études en France grâce à des procédures adaptées à leur profil. Trouvez la vôtre et préparez votre succès.</p>
        </section>
        
        <!-- Campus France Procedure -->
        <section id="procedures" class="procedure">
            <div class="procedure-header campus-header">
                <div class="procedure-icon">🌍</div>
                <div class="procedure-title">
                    <h2>Campus France</h2>
                    <span class="procedure-tag">La voie d'excellence</span>
                </div>
            </div>
            <div class="procedure-body">
                <div class="procedure-content">
                    <p><strong>Votre porte d'entrée vers l'enseignement supérieur français.</strong> Campus France est bien plus qu'une simple procédure administrative : c'est un accompagnement personnalisé qui vous guide pas à pas vers la formation idéale.</p>
                    
                    <div class="highlight">
                        <h3>Pourquoi choisir Campus France ?</h3>
                        <p>Avec plus de 85% de taux de satisfaction parmi les étudiants internationaux, Campus France a aidé des milliers d'étudiants à concrétiser leur projet d'études en France. Notre réseau de conseillers dédiés vous accompagne depuis votre pays d'origine jusqu'à votre installation en France.</p>
                    </div>
                    
                    <p>Imaginez-vous dans un an : vous suivez des cours dans l'établissement de vos rêves, vous découvrez la richesse culturelle française, et vous construisez un réseau international qui ouvrira les portes de votre carrière. Campus France rend ce rêve accessible.</p>
                    
                    <div class="benefits">
                        <div class="benefit-item">
                            <h4>Accompagnement personnalisé</h4>
                            <p>Un conseiller dédié vous guide à chaque étape de votre projet</p>
                        </div>
                        <div class="benefit-item">
                            <h4>Large choix de formations</h4>
                            <p>Accédez à plus de 3 500 formations dans tous les domaines</p>
                        </div>
                        <div class="benefit-item">
                            <h4>Procédure simplifiée</h4>
                            <p>Une plateforme unique pour gérer toutes vos candidatures</p>
                        </div>
                    </div>
                </div>
                <div class="procedure-image">
                    <div class="image-placeholder campus-image">
                        Plateforme Campus France<br>Interface Moderne
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Parcoursup Procedure -->
        <section class="procedure">
            <div class="procedure-header parcoursup-header">
                <div class="procedure-icon">🚀</div>
                <div class="procedure-title">
                    <h2>Parcoursup</h2>
                    <span class="procedure-tag">Première année d'études</span>
                </div>
            </div>
            <div class="procedure-body">
                <div class="procedure-content">
                    <p><strong>Votre tremplin vers la réussite dans l'enseignement supérieur français.</strong> Parcoursup est la plateforme nationale qui vous permet d'intégrer la première année de licence, de BTS ou de DUT dans l'établissement de votre choix.</p>
                    
                    <div class="highlight">
                        <h3>La force du système français</h3>
                        <p>Contrairement à d'autres systèmes, Parcoursup vous permet de formuler jusqu'à 10 vœux sans classement, maximisant ainsi vos chances d'admission. Notre algorithme sophistiqué prend en compte votre profil, vos aspirations et les spécificités de chaque formation.</p>
                    </div>
                    
                    <p>En 2023, plus de 92% des candidats ont reçu au moins une proposition d'admission via Parcoursup. Ce système équitable et transparent a déjà permis à des centaines de milliers d'étudiants de trouver la formation qui correspond parfaitement à leur projet professionnel.</p>
                    
                    <div class="benefits">
                        <div class="benefit-item">
                            <h4>Transparence totale</h4>
                            <p>Chaque formation détaille ses critères d'admission et ses débouchés</p>
                        </div>
                        <div class="benefit-item">
                            <h4>Équité des chances</h4>
                            <p>Un système conçu pour valoriser le potentiel de chaque candidat</p>
                        </div>
                        <div class="benefit-item">
                            <h4>Accompagnement continu</h4>
                            <p>Des commissions d'accès à l'enseignement supérieur vous conseillent</p>
                        </div>
                    </div>
                </div>
                <div class="procedure-image">
                    <div class="image-placeholder parcoursup-image">
                        Interface Parcoursup<br>Navigation Intuitive
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Paris-Saclay Procedure -->
        <section class="procedure">
            <div class="procedure-header saclay-header">
                <div class="procedure-icon">🏆</div>
                <div class="procedure-title">
                    <h2>Université Paris-Saclay</h2>
                    <span class="procedure-tag">L'excellence académique</span>
                </div>
            </div>
            <div class="procedure-body">
                <div class="procedure-content">
                    <p><strong>Rejoignez l'une des meilleures universités d'Europe.</strong> Classée parmi les 15 meilleures universités mondiales dans plusieurs domaines, l'Université Paris-Saclay offre un environnement unique alliant excellence académique et innovation de pointe.</p>
                    
                    <div class="highlight">
                        <h3>Un écosystème d'exception</h3>
                        <p>Étudier à Paris-Saclay, c'est bénéficier d'un environnement unique au monde : 15% de la recherche française, 300 laboratoires de recherche, et un réseau de 50 000 étudiants et chercheurs venant de 130 pays. C'est aussi être au cœur de la French Tech, avec des opportunités de stages et d'emplois dans les entreprises les plus innovantes.</p>
                    </div>
                    
                    <p>Nos diplômés sont parmi les plus recherchés sur le marché du travail international. 90% d'entre eux trouvent un emploi dans les 6 mois suivant l'obtention de leur diplôme, avec des salaires d'embauche parmi les plus élevés de France.</p>
                    
                    <div class="benefits">
                        <div class="benefit-item">
                            <h4>Excellence internationale</h4>
                            <p>Classée parmi les meilleures universités mondiales dans 9 disciplines</p>
                        </div>
                        <div class="benefit-item">
                            <h4>Recherche de pointe</h4>
                            <p>Accédez à des laboratoires leaders dans leur domaine</p>
                        </div>
                        <div class="benefit-item">
                            <h4>Réseau puissant</h4>
                            <p>Rejoignez une communauté d'anciens influents dans le monde entier</p>
                        </div>
                    </div>
                </div>
                <div class="procedure-image">
                    <div class="image-placeholder saclay-image">
                        Campus Paris-Saclay<br>Environnement Moderne
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Écoles Privées Procedure -->
        <section class="procedure">
            <div class="procedure-header ecoles-header">
                <div class="procedure-icon">💼</div>
                <div class="procedure-title">
                    <h2>Écoles Privées</h2>
                    <span class="procedure-tag">L'excellence professionnelle</span>
                </div>
            </div>
            <div class="procedure-body">
                <div class="procedure-content">
                    <p><strong>Une formation sur mesure pour votre carrière internationale.</strong> Les écoles de commerce, d'ingénieurs et les établissements spécialisés français sont reconnus dans le monde entier pour leur approche professionnalisante et leurs réseaux d'anciens influents.</p>
                    
                    <div class="highlight">
                        <h3>L'avantage des écoles françaises</h3>
                        <p>Les écoles privées françaises offrent un enseignement en petits groupes, un suivi personnalisé et des liens étroits avec le monde professionnel. 85% de leurs étudiants effectuent au moins un stage à l'international durant leur formation, et 40% des diplômés travaillent à l'étranger dans les 5 ans suivant leur diplôme.</p>
                    </div>
                    
                    <p>Contrairement aux idées reçues, étudier dans une école privée française est accessible grâce à de nombreux dispositifs de financement. Près de 60% des étudiants internationaux bénéficient d'une forme d'aide financière, et les établissements proposent souvent des échelons de paiement adaptés.</p>
                    
                    <div class="benefits">
                        <div class="benefit-item">
                            <h4>Approche professionnalisante</h4>
                            <p>Des formations conçues avec et pour les entreprises</p>
                        </div>
                        <div class="benefit-item">
                            <h4>Réseaux internationaux</h4>
                            <p>Des partenariats avec plus de 5 000 entreprises dans le monde</p>
                        </div>
                        <div class="benefit-item">
                            <h4>Insertion rapide</h4>
                            <p>95% de nos diplômés en emploi dans les 6 mois</p>
                        </div>
                    </div>
                </div>
                <div class="procedure-image">
                    <div class="image-placeholder ecoles-image">
                        Campus École Privée<br>Infrastructures Premium
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Language Section -->
        <section class="language-section">
            <h2>Votre niveau de langue, notre force</h2>
            <div class="language-cards">
                <div class="language-card">
                    <h3>Formations en français</h3>
                    <p>Plus de 1 500 formations accessibles avec un niveau B2</p>
                    <p><strong>Tests acceptés :</strong> DELF, DALF, TCF</p>
                    <span class="language-level">Niveau B2 recommandé</span>
                </div>
                <div class="language-card">
                    <h3>Formations en anglais</h3>
                    <p>Plus de 1 200 programmes enseignés en anglais</p>
                    <p><strong>Tests acceptés :</strong> TOEFL, IELTS, TOEIC</p>
                    <span class="language-level">Niveau variable</span>
                </div>
                <div class="language-card">
                    <h3>Programmes bilingues</h3>
                    <p>Le meilleur des deux mondes pour maximiser vos opportunités</p>
                    <p><strong>Avantage :</strong> Développez un profil international unique</p>
                    <span class="language-level">Double compétence</span>
                </div>
            </div>
        </section>
        
        <!-- Navigation -->
        <div class="navigation">
            <a href="#formulaire-complet" class="nav-button">Découvrir ma procédure personnalisée</a>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>© 2025 Votre Avenir en France - Tous droits réservés. Rejoignez les milliers d'étudiants qui ont réalisé leur rêve d'études en France.</p>
        </div>
    </footer>
    
    <?php
    // Traitement du formulaire de contact
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nom = htmlspecialchars($_POST['nom'] ?? '');
        $email = htmlspecialchars($_POST['email'] ?? '');
        $pays = htmlspecialchars($_POST['pays'] ?? '');
        $niveau = htmlspecialchars($_POST['niveau'] ?? '');
        $procedure = htmlspecialchars($_POST['procedure'] ?? '');
        
        // Enregistrement des données (à adapter selon vos besoins)
        $donnees = "Nom: $nom\nEmail: $email\nPays: $pays\nNiveau: $niveau\nProcédure: $procedure\n\n";
        file_put_contents('contacts.txt', $donnees, FILE_APPEND);
        
        // Redirection vers une page de confirmation
        header('Location: merci.html');
        exit;
    }
    ?>
</body>
</html>