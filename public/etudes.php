<!-- Section Procédures d'Études -->
<section class="study-procedures-section">
    <div class="study-container">
        <h2 class="study-section-title">Étudier à l'Étranger</h2>
        <p class="study-section-subtitle">
            Informations spécifiques pour les étudiants algériens - Conditions d'admission, frais et procédures
        </p>
        
        <!-- Slider des pays -->
        <div class="study-countries-slider" id="countriesSlider">
            <div class="slider-track" id="sliderTrack">
                <!-- Les cartes seront chargées dynamiquement -->
            </div>
            
            <!-- Contrôles du slider -->
            <button class="slider-nav-btn prev-btn" onclick="slidePrev()">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-nav-btn next-btn" onclick="slideNext()">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Indicateurs -->
            <div class="slider-indicators" id="sliderIndicators"></div>
        </div>

        <!-- Grille des pays avec pagination -->
        <div class="study-countries-grid-container" id="countriesGridContainer">
            <div class="study-countries-grid" id="countriesGrid">
                <!-- Les cartes seront chargées dynamiquement -->
            </div>
            
            <!-- Pagination -->
            <div class="grid-pagination" id="gridPagination">
                <button class="pagination-btn prev-page" onclick="changePage(-1)">
                    <i class="fas fa-chevron-left"></i>
                    <span>Précédent</span>
                </button>
                
                <div class="pagination-dots" id="paginationDots"></div>
                
                <button class="pagination-btn next-page" onclick="changePage(1)">
                    <span>Suivant</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Bouton Voir Plus -->
        <div class="show-more-container">
            <button class="show-more-btn" id="showMoreBtn" onclick="toggleGridView()">
                <i class="fas fa-grid-2" id="viewIcon"></i>
                <span id="viewText">Vue en grille</span>
            </button>
        </div>
    </div>
</section>

<!-- Modal pour les études -->
<div id="study-modal" class="study-modal">
    <div class="study-modal-content">
        <button class="close-study-modal" onclick="closeStudyModal()">
            <i class="fas fa-times"></i>
        </button>
        <div class="study-modal-header">
            <h2 id="study-modal-title">Étudier en France</h2>
            <p id="study-modal-subtitle">Conditions spécifiques pour les étudiants algériens</p>
        </div>
        <div class="study-modal-body" id="study-modal-body">
            <!-- Le contenu sera chargé dynamiquement -->
        </div>
        <div class="study-modal-footer" id="study-modal-footer">
            <!-- Le bouton sera chargé dynamiquement -->
        </div>
    </div>
</div>

<style>
    /* ===== VARIABLES MODERNES ===== */
    :root {
        --primary-blue: #2563eb;
        --primary-dark: #1e40af;
        --light-blue: #f0f9ff;
        --accent-green: #10b981;
        --accent-orange: #f59e0b;
        --accent-purple: #8b5cf6;
        --white: #ffffff;
        --light-gray: #f8fafc;
        --dark-text: #1e293b;
        --gradient-primary: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
        --gradient-accent: linear-gradient(135deg, #10b981 0%, #059669 100%);
        --gradient-orange: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        --box-shadow-hover: 0 30px 60px rgba(0, 0, 0, 0.15);
        --transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        --font-weight-normal: 400;
        --font-weight-semibold: 600;
        --font-weight-bold: 700;
        --font-weight-extrabold: 800;
        --border-radius: 20px;
        --border-radius-sm: 12px;
    }

    /* ===== SECTION PRINCIPALE ===== */
    .study-procedures-section {
        padding: 100px 0;
        background: linear-gradient(135deg, var(--light-blue) 0%, #fefefe 100%);
        position: relative;
        overflow: hidden;
    }

    .study-procedures-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: 
            radial-gradient(circle at 20% 80%, rgba(37, 99, 235, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.05) 0%, transparent 50%);
        pointer-events: none;
    }

    .study-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px;
        position: relative;
        z-index: 2;
    }

    /* ===== TITRES ===== */
    .study-section-title {
        text-align: center;
        font-size: 3.5rem;
        margin-bottom: 20px;
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--accent-purple) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: var(--font-weight-extrabold);
        letter-spacing: -1px;
        line-height: 1.1;
    }

    .study-section-subtitle {
        text-align: center;
        font-size: 1.3rem;
        color: #64748b;
        margin-bottom: 70px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
        font-weight: var(--font-weight-normal);
    }

    /* ===== SLIDER MODERNE ===== */
    .study-countries-slider {
        position: relative;
        margin: 0 auto 50px;
        padding: 0 60px;
        overflow: hidden;
    }

    .slider-track {
        display: flex;
        transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        gap: 30px;
        padding: 20px 0;
    }

    /* Cartes de pays */
    .study-country-card {
        flex: 0 0 calc(33.333% - 20px);
        background: var(--white);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: var(--box-shadow);
        transition: var(--transition);
        cursor: pointer;
        position: relative;
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        min-width: 0;
    }

    .study-country-card:hover {
        transform: translateY(-15px) scale(1.02);
        box-shadow: var(--box-shadow-hover);
    }

    .study-country-header {
        position: relative;
        height: 180px;
        overflow: hidden;
    }

    .study-country-flag {
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .country-flag-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .study-country-card:hover .country-flag-img {
        transform: scale(1.1);
    }

    .study-country-icon {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        transition: var(--transition);
        color: var(--primary-blue);
    }

    .study-country-card:hover .study-country-icon {
        transform: scale(1.1) rotate(5deg);
        background: var(--white);
    }

    .study-country-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: var(--gradient-accent);
        color: white;
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: var(--font-weight-bold);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
        z-index: 2;
    }

    .study-country-content {
        padding: 30px;
        position: relative;
        background: var(--white);
    }

    .study-country-content h3 {
        font-size: 1.6rem;
        margin-bottom: 15px;
        color: var(--dark-text);
        font-weight: var(--font-weight-bold);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .country-description {
        color: #64748b;
        margin-bottom: 25px;
        line-height: 1.5;
        font-size: 1rem;
    }

    .study-country-stats {
        display: flex;
        justify-content: space-between;
        margin: 25px 0 30px;
        gap: 15px;
    }

    .study-stat {
        text-align: center;
        flex: 1;
        padding: 15px 10px;
        background: var(--light-gray);
        border-radius: var(--border-radius-sm);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .study-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--gradient-primary);
    }

    .study-country-card:hover .study-stat {
        background: var(--light-blue);
        transform: translateY(-3px);
    }

    .study-stat-number {
        display: block;
        font-size: 1.3rem;
        font-weight: var(--font-weight-bold);
        color: var(--primary-blue);
        margin-bottom: 5px;
    }

    .study-stat-label {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .study-country-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px 25px;
        border-radius: 15px;
        text-decoration: none;
        font-weight: var(--font-weight-semibold);
        transition: var(--transition);
        border: none;
        cursor: pointer;
        width: 100%;
        gap: 12px;
        background: var(--gradient-primary);
        color: var(--white);
        font-size: 1rem;
        position: relative;
        overflow: hidden;
    }

    .study-country-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
    }

    /* Navigation du slider */
    .slider-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 60px;
        height: 60px;
        background: var(--white);
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: var(--transition);
        z-index: 10;
        box-shadow: var(--box-shadow);
        font-size: 1.2rem;
        color: var(--primary-blue);
    }

    .slider-nav-btn:hover {
        background: var(--primary-blue);
        color: var(--white);
        transform: translateY(-50%) scale(1.1);
    }

    .prev-btn {
        left: 0;
    }

    .next-btn {
        right: 0;
    }

    .slider-indicators {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 30px;
    }

    .slider-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #cbd5e1;
        cursor: pointer;
        transition: var(--transition);
        border: none;
    }

    .slider-indicator.active {
        background: var(--primary-blue);
        transform: scale(1.3);
    }

    /* ===== GRILLE AVEC PAGINATION ===== */
    .study-countries-grid-container {
        display: none; /* Caché par défaut */
        margin-bottom: 50px;
    }

    .study-countries-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    /* Pagination */
    .grid-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 20px;
        margin-top: 40px;
        padding: 20px 0;
    }

    .pagination-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 25px;
        background: var(--white);
        color: var(--primary-blue);
        border: 2px solid var(--primary-blue);
        border-radius: 25px;
        font-weight: var(--font-weight-semibold);
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .pagination-btn:hover:not(:disabled) {
        background: var(--primary-blue);
        color: var(--white);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
    }

    .pagination-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    .pagination-dots {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .pagination-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #cbd5e1;
        cursor: pointer;
        transition: var(--transition);
        border: none;
    }

    .pagination-dot.active {
        background: var(--primary-blue);
        transform: scale(1.3);
    }

    .pagination-dot:hover {
        background: var(--primary-dark);
    }

    /* Bouton Vue Grille/Slider */
    .show-more-container {
        text-align: center;
        margin-top: 40px;
    }

    .show-more-btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 16px 35px;
        background: transparent;
        color: var(--primary-blue);
        text-decoration: none;
        border-radius: 25px;
        font-weight: var(--font-weight-semibold);
        font-size: 1.1rem;
        transition: var(--transition);
        border: 2px solid var(--primary-blue);
        cursor: pointer;
    }

    .show-more-btn:hover {
        background: var(--primary-blue);
        color: var(--white);
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(37, 99, 235, 0.3);
    }

    /* ===== MODAL MODERNE ===== */
    .study-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 10000;
        overflow-y: auto;
        backdrop-filter: blur(10px);
    }

    .study-modal-content {
        background: var(--white);
        margin: 40px auto;
        border-radius: var(--border-radius);
        max-width: 1000px;
        position: relative;
        animation: modalSlideIn 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        box-shadow: var(--box-shadow-hover);
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    @keyframes modalSlideIn {
        from { 
            opacity: 0;
            transform: translateY(-30px) scale(0.95);
        }
        to { 
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .close-study-modal {
        position: absolute;
        top: 25px;
        right: 25px;
        background: var(--accent-orange);
        color: var(--white);
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10;
        font-size: 1.3rem;
        transition: var(--transition);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .close-study-modal:hover {
        background: var(--primary-blue);
        transform: scale(1.1) rotate(90deg);
    }

    .study-modal-header {
        padding: 50px 50px 30px;
        border-bottom: 1px solid var(--light-gray);
        background: var(--gradient-primary);
        color: var(--white);
        border-radius: var(--border-radius) var(--border-radius) 0 0;
    }

    .study-modal-header h2 {
        font-size: 2.5rem;
        margin-bottom: 15px;
        font-weight: var(--font-weight-bold);
    }

    .study-modal-header p {
        opacity: 0.9;
        font-size: 1.2rem;
    }

    .study-modal-body {
        padding: 40px 50px;
        max-height: calc(90vh - 200px);
        overflow-y: auto;
        flex: 1;
    }

    .study-modal-footer {
        padding: 30px 50px;
        background: var(--light-gray);
        border-top: 1px solid #e2e8f0;
        text-align: center;
        border-radius: 0 0 var(--border-radius) var(--border-radius);
    }

    .postuler-btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 18px 45px;
        background: var(--gradient-accent);
        color: var(--white);
        text-decoration: none;
        border-radius: 15px;
        font-weight: var(--font-weight-semibold);
        font-size: 1.2rem;
        transition: var(--transition);
        border: none;
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
    }

    .postuler-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.6);
    }

    .study-procedures-detailed {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 40px;
    }

    .study-procedure-card {
        background: var(--light-blue);
        padding: 30px;
        border-radius: var(--border-radius-sm);
        border-left: 5px solid var(--accent-green);
        transition: var(--transition);
    }

    .study-procedure-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--box-shadow);
    }

    .study-procedure-card h3 {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--dark-text);
        font-weight: var(--font-weight-semibold);
        font-size: 1.3rem;
    }

    .study-procedure-card ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .study-procedure-card li {
        margin-bottom: 12px;
        padding-left: 25px;
        position: relative;
        color: #475569;
        line-height: 1.6;
        font-size: 1rem;
    }

    .study-procedure-card li:before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--accent-green);
        font-weight: bold;
    }

    .study-calendar-section {
        background: var(--light-gray);
        padding: 35px;
        border-radius: var(--border-radius-sm);
        margin: 40px 0;
        border-left: 5px solid var(--accent-orange);
    }

    .study-calendar-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-top: 25px;
    }

    .study-calendar-item {
        text-align: center;
        padding: 25px 20px;
        background: var(--white);
        border-radius: var(--border-radius-sm);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: var(--transition);
    }

    .study-calendar-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }

    .study-calendar-month {
        font-weight: var(--font-weight-bold);
        color: var(--primary-blue);
        margin-bottom: 10px;
        font-size: 1.1rem;
    }

    .study-calendar-dates {
        color: #64748b;
        font-size: 0.95rem;
    }

    .study-advantages-section {
        background: var(--light-blue);
        padding: 35px;
        border-radius: var(--border-radius-sm);
        margin: 40px 0;
        border-left: 5px solid var(--accent-purple);
    }

    .advantages-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 25px;
    }

    .advantage-item {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px;
        background: var(--white);
        border-radius: var(--border-radius-sm);
        transition: var(--transition);
    }

    .advantage-item:hover {
        transform: translateX(10px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }

    .advantage-item i {
        width: 50px;
        height: 50px;
        background: var(--gradient-primary);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        font-size: 1.2rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1200px) {
        .study-country-card {
            flex: 0 0 calc(50% - 15px);
        }
        .study-section-title {
            font-size: 3rem;
        }
    }

    @media (max-width: 768px) {
        .study-procedures-section {
            padding: 60px 0;
        }
        .study-section-title {
            font-size: 2.5rem;
        }
        .study-countries-slider {
            padding: 0 40px;
        }
        .study-country-card {
            flex: 0 0 100%;
        }
        .slider-nav-btn {
            width: 50px;
            height: 50px;
        }
        .study-country-header {
            height: 160px;
        }
        .study-country-content {
            padding: 25px;
        }
        .study-modal-content {
            margin: 20px;
            width: calc(100% - 40px);
        }
        .study-modal-header {
            padding: 40px 30px 20px;
        }
        .study-modal-body {
            padding: 30px;
        }
        .study-modal-footer {
            padding: 25px 30px;
        }
        .study-procedures-detailed {
            grid-template-columns: 1fr;
        }
        .study-countries-grid {
            grid-template-columns: 1fr;
        }
        .grid-pagination {
            flex-direction: column;
            gap: 15px;
        }
    }

    @media (max-width: 480px) {
        .study-section-title {
            font-size: 2rem;
        }
        .study-country-content {
            padding: 20px;
        }
        .study-modal-header h2 {
            font-size: 2rem;
        }
        .slider-nav-btn {
            width: 45px;
            height: 45px;
        }
        .study-countries-slider {
            padding: 0 20px;
        }
        .postuler-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
    // Configuration
    let currentSlide = 0;
    let currentPage = 0;
    let isGridView = false;
    const slidesPerView = 3;
    const cardsPerPage = 6;
    let allCards = [];
    let totalPages = 0;

    // Données des pays
    const countriesData = [
        {
            id: 'france',
            name: 'France',
            flag: 'https://flagcdn.com/w320/fr.png',
            icon: 'fa-landmark',
            badge: 'Populaire',
            description: '4 voies d\'admission : Campus France, Parcoursup, Paris-Saclay et écoles privées',
            stats: { admission: '85%', frais: '3,500€' }
        },
        {
            id: 'belgique',
            name: 'Belgique',
            flag: 'https://flagcdn.com/w320/be.png',
            icon: 'fa-university',
            description: 'Équivalence de diplôme nécessaire pour les Algériens',
            stats: { admission: '78%', frais: '4,200€' }
        },
        {
            id: 'roumanie',
            name: 'Roumanie',
            flag: 'https://flagcdn.com/w320/ro.png',
            icon: 'fa-graduation-cap',
            description: 'Programmes en français accessibles sans équivalence',
            stats: { admission: '82%', frais: '3,000€' }
        },
        {
            id: 'bulgarie',
            name: 'Bulgarie',
            flag: 'https://flagcdn.com/w320/bg.png',
            icon: 'fa-stethoscope',
            description: 'Programmes en anglais et bulgare avec année préparatoire',
            stats: { admission: '79%', frais: '3,500€' }
        },
        {
            id: 'turquie',
            name: 'Turquie',
            flag: 'https://flagcdn.com/w320/tr.png',
            icon: 'fa-globe-europe',
            description: 'Test YÖS obligatoire pour les étudiants algériens',
            stats: { admission: '81%', frais: '1,500€' }
        },
        {
            id: 'espagne',
            name: 'Espagne',
            flag: 'https://flagcdn.com/w320/es.png',
            icon: 'fa-sun',
            description: 'Homologation du Bac nécessaire pour Algériens',
            stats: { admission: '84%', frais: '2,500€' }
        }
    ];

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        initCountries();
        initSlider();
        initModal();
        initGridPagination();
    });

    // Initialiser les cartes des pays
    function initCountries() {
        const sliderTrack = document.getElementById('sliderTrack');
        const gridContainer = document.getElementById('countriesGrid');
        
        // Vider les conteneurs
        sliderTrack.innerHTML = '';
        gridContainer.innerHTML = '';
        
        // Créer les cartes pour le slider
        countriesData.forEach(country => {
            const card = createCountryCard(country);
            sliderTrack.appendChild(card);
        });
        
        // Créer les cartes pour la grille (copies)
        countriesData.forEach(country => {
            const card = createCountryCard(country);
            gridContainer.appendChild(card);
        });
        
        // Stocker toutes les cartes pour la pagination
        allCards = Array.from(gridContainer.children);
        totalPages = Math.ceil(allCards.length / cardsPerPage);
    }

    // Créer une carte de pays
    function createCountryCard(country) {
        const card = document.createElement('div');
        card.className = 'study-country-card';
        card.onclick = () => openStudyModal(country.id);
        
        card.innerHTML = `
            <div class="study-country-header">
                <div class="study-country-flag">
                    <img src="${country.flag}" alt="Drapeau ${country.name}" class="country-flag-img">
                    <div class="study-country-icon">
                        <i class="fas ${country.icon}"></i>
                    </div>
                </div>
                ${country.badge ? `<div class="study-country-badge">${country.badge}</div>` : ''}
            </div>
            <div class="study-country-content">
                <h3>${country.name} ${getCountryFlagEmoji(country.name)}</h3>
                <p class="country-description">${country.description}</p>
                <div class="study-country-stats">
                    <div class="study-stat">
                        <span class="study-stat-number">${country.stats.admission}</span>
                        <span class="study-stat-label">Admission</span>
                    </div>
                    <div class="study-stat">
                        <span class="study-stat-number">${country.stats.frais}</span>
                        <span class="study-stat-label">Frais moyens</span>
                    </div>
                </div>
                <button class="study-country-btn">
                    <span>Voir les conditions</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        `;
        
        return card;
    }

    // Obtenir l'emoji du drapeau
    function getCountryFlagEmoji(countryName) {
        const emojis = {
            'France': '🇫🇷',
            'Belgique': '🇧🇪',
            'Roumanie': '🇷🇴',
            'Bulgarie': '🇧🇬',
            'Turquie': '🇹🇷',
            'Espagne': '🇪🇸'
        };
        return emojis[countryName] || '🇺🇳';
    }

    // Initialisation du slider
    function initSlider() {
        const totalSlides = countriesData.length;
        const totalIndicators = Math.ceil(totalSlides / slidesPerView);
        createSliderIndicators(totalIndicators);
        updateSlider();
    }

    function createSliderIndicators(totalIndicators) {
        const indicatorsContainer = document.getElementById('sliderIndicators');
        indicatorsContainer.innerHTML = '';
        
        for (let i = 0; i < totalIndicators; i++) {
            const indicator = document.createElement('button');
            indicator.className = `slider-indicator ${i === 0 ? 'active' : ''}`;
            indicator.setAttribute('data-slide', i);
            indicator.addEventListener('click', () => goToSlide(i));
            indicatorsContainer.appendChild(indicator);
        }
    }

    function updateSlider() {
        const track = document.getElementById('sliderTrack');
        const slideWidth = 100 / slidesPerView;
        const translateX = -currentSlide * slideWidth;
        track.style.transform = `translateX(${translateX}%)`;
        
        document.querySelectorAll('.slider-indicator').forEach((indicator, index) => {
            indicator.classList.toggle('active', index === currentSlide);
        });
    }

    function slideNext() {
        const maxSlide = Math.ceil(countriesData.length / slidesPerView) - 1;
        if (currentSlide < maxSlide) {
            currentSlide++;
        } else {
            currentSlide = 0; // Retour au début
        }
        updateSlider();
    }

    function slidePrev() {
        const maxSlide = Math.ceil(countriesData.length / slidesPerView) - 1;
        if (currentSlide > 0) {
            currentSlide--;
        } else {
            currentSlide = maxSlide; // Aller à la fin
        }
        updateSlider();
    }

    function goToSlide(slideIndex) {
        const maxSlide = Math.ceil(countriesData.length / slidesPerView) - 1;
        if (slideIndex >= 0 && slideIndex <= maxSlide) {
            currentSlide = slideIndex;
            updateSlider();
        }
    }

    // Initialisation de la pagination de la grille
    function initGridPagination() {
        createPaginationDots();
        showPage(0);
    }

    function createPaginationDots() {
        const dotsContainer = document.getElementById('paginationDots');
        dotsContainer.innerHTML = '';
        
        for (let i = 0; i < totalPages; i++) {
            const dot = document.createElement('button');
            dot.className = `pagination-dot ${i === 0 ? 'active' : ''}`;
            dot.setAttribute('data-page', i);
            dot.addEventListener('click', () => showPage(i));
            dotsContainer.appendChild(dot);
        }
    }

    function showPage(pageIndex) {
        const grid = document.getElementById('countriesGrid');
        const startIndex = pageIndex * cardsPerPage;
        const endIndex = startIndex + cardsPerPage;
        
        // Cacher toutes les cartes
        allCards.forEach(card => {
            card.style.display = 'none';
        });
        
        // Afficher seulement les cartes de la page courante
        const pageCards = allCards.slice(startIndex, endIndex);
        pageCards.forEach(card => {
            card.style.display = 'block';
        });
        
        updatePaginationState(pageIndex);
    }

    function updatePaginationState(pageIndex) {
        currentPage = pageIndex;
        
        document.querySelectorAll('.pagination-dot').forEach((dot, index) => {
            dot.classList.toggle('active', index === pageIndex);
        });
        
        const prevBtn = document.querySelector('.prev-page');
        const nextBtn = document.querySelector('.next-page');
        
        prevBtn.disabled = pageIndex === 0;
        nextBtn.disabled = pageIndex === totalPages - 1;
    }

    function changePage(direction) {
        const newPage = currentPage + direction;
        if (newPage >= 0 && newPage < totalPages) {
            showPage(newPage);
        }
    }

    // Basculer entre vue slider et grille
    function toggleGridView() {
        const slider = document.getElementById('countriesSlider');
        const gridContainer = document.getElementById('countriesGridContainer');
        const viewIcon = document.getElementById('viewIcon');
        const viewText = document.getElementById('viewText');
        
        isGridView = !isGridView;
        
        if (isGridView) {
            slider.style.display = 'none';
            gridContainer.style.display = 'block';
            viewIcon.className = 'fas fa-sliders-h';
            viewText.textContent = 'Vue slider';
            showPage(currentPage);
        } else {
            gridContainer.style.display = 'none';
            slider.style.display = 'block';
            viewIcon.className = 'fas fa-grid-2';
            viewText.textContent = 'Vue en grille';
        }
    }

    // Données des modals
    const studyData = {
        'france': {
            title: "Étudier en France",
            subtitle: "4 voies d'admission : Campus France, Parcoursup, Paris-Saclay et écoles privées",
            procedures: [
                {
                    title: "🎯 Campus France - Boîte Pastel",
                    description: "Procédure obligatoire pour tous les étudiants algériens",
                    details: [
                        "Avoir obtenu le baccalauréat (note non conditionnée)",
                        "Test de français : niveau minimum B1 pour postuler",
                        "Niveau recommandé : B2 pour la majorité des formations",
                        "Inscription sur la plateforme 'Études en France'",
                        "Entretien obligatoire à l'espace Campus France Algérie",
                        "Frais de dossier Campus France : 180 €",
                        "Clôture DAP : 15 décembre",
                        "Clôture hors DAP : 30 novembre"
                    ]
                },
                {
                    title: "📱 Parcoursup",
                    description: "Plateforme pour BTS, BUT, écoles spécialisées",
                    details: [
                        "Public concerné : candidats avec BTS en Algérie, bac ancien, ou réorientation",
                        "TCF non obligatoire pour postuler mais requis si admission obtenue",
                        "Ouverture de la plateforme : à partir du 15 décembre",
                        "Nécessite de finaliser via Campus France après admission"
                    ]
                },
                {
                    title: "🏛️ Université Paris-Saclay",
                    description: "Procédure spécifique pour formations sélectives",
                    details: [
                        "Fournir les contacts d'au moins deux enseignants",
                        "Questionnaire envoyé aux enseignants pour validation académique",
                        "Étape de vérification et de recommandations obligatoire"
                    ]
                },
                {
                    title: "🎓 Écoles privées",
                    description: "Admission plus simple mais frais élevés",
                    details: [
                        "Conditions : baccalauréat + niveau français B2",
                        "Entretien de motivation parfois requis",
                        "Frais de scolarité : 5 000 € à 14 000 € par an",
                        "Procédure plus accessible que les universités publiques"
                    ]
                }
            ],
            calendrier: [
                { mois: "1er Oct", dates: "Ouverture Campus France" },
                { mois: "15 Déc", dates: "Clôture DAP" },
                { mois: "30 Nov", dates: "Clôture hors DAP" },
                { mois: "15 Déc", dates: "Ouverture Parcoursup" }
            ],
            avantages: [
                "Procédure Campus France spécifique pour Algériens",
                "4 voies d'admission différentes selon le profil",
                "Enseignement de qualité reconnu internationalement"
            ],
            frais: {
                inscription: "180 € (Campus France)",
                scolarite: "Gratuit à 14 000 €/an selon formation"
            },
            lien: "/babylone/public/france/etudes/etudes.php"
        },
        'belgique': {
            title: "Étudier en Belgique",
            subtitle: "Conditions pour étudiants algériens - Équivalence requise",
            procedures: [
                {
                    title: "🎓 Équivalence de Diplôme",
                    description: "Reconnaissance du Bac algérien obligatoire",
                    details: [
                        "Demande d'équivalence du Bac obligatoire",
                        "Délai de traitement : 2 à 3 mois",
                        "Coût : 150€ à 400 €",
                        "Dossier à déposer à la Fédération Wallonie-Bruxelles",
                        "Traduction assermentée des documents requise"
                    ]
                }
            ],
            calendrier: [
                { mois: "Nov-Juil", dates: "Demande équivalence" },
                { mois: "Fév-Avr", dates: "Candidature universités" }
            ],
            avantages: [
                "Frais de scolarité accessibles",
                "Enseignement de qualité en français",
                "Reconnaissance internationale des diplômes"
            ],
            frais: {
                inscription: "150€ à 400 € (équivalence)",
                scolarite: "Accessible (varie selon l'université)"
            },
            lien: "/babylone/public/belgique/etudes/etudes.php"
        },
        'roumanie': {
            title: "Étudier en Roumanie",
            subtitle: "Programmes en français accessibles sans équivalence",
            procedures: [
                {
                    title: "📚 Admission Directe",
                    description: "Procédure simplifiée pour Algériens",
                    details: [
                        "Baccalauréat algérien accepté sans équivalence",
                        "Dossier académique : relevés de notes",
                        "Test de français TCF DELF/DALF",
                        "Inscription en ligne via les universités",
                        "Frais d'inscription : 50 € à 200 € (non remboursables)",
                        "Programmes en français de qualité"
                    ]
                }
            ],
            calendrier: [
                { mois: "Janv-Mar", dates: "Candidatures universités" },
                { mois: "Avr-Juin", dates: "Délivrance lettres admission" }
            ],
            avantages: [
                "Pas d'équivalence de diplôme nécessaire",
                "Programmes en français de qualité",
                "Frais de scolarité compétitifs"
            ],
            frais: {
                inscription: "50 € à 200 €",
                scolarite: "Compétitifs (varie selon le programme)"
            },
            lien: "/babylone/public/roumanie/etude/etude.php"
        },
        'bulgarie': {
            title: "Étudier en Bulgarie",
            subtitle: "Programmes en anglais et bulgare avec année préparatoire",
            procedures: [
                {
                    title: "🌍 Programmes Disponibles",
                    description: "Deux types de formations",
                    details: [
                        "Programmes en anglais : test TOEFL/IELTS requis",
                        "Programmes en bulgare : test de langue ou année préparatoire",
                        "Année préparatoire pour apprendre le bulgare",
                        "Avoir le baccalauréat (pas de conditions spécifiques de moyenne)",
                        "Frais d'inscription : 50 € à 200 € (non remboursables)",
                        "Acompte seulement, pas de paiement total immédiat"
                    ]
                }
            ],
            calendrier: [
                { mois: "Janv-Avr", dates: "Candidatures universités" },
                { mois: "Mai-Juin", dates: "Admission" }
            ],
            avantages: [
                "Programmes en anglais accessibles",
                "Acompte seulement, pas de paiement total immédiat",
                "Pas d'équivalence de diplôme requise"
            ],
            frais: {
                inscription: "50 € à 200 €",
                scolarite: "Acompte requis puis paiement échelonné"
            },
            lien: "/babylone/public/bulgarie/etude/etude.php"
        },
        'turquie': {
            title: "Étudier en Turquie",
            subtitle: "Test YÖS obligatoire pour les étudiants algériens",
            procedures: [
                {
                    title: "🎯 Test YÖS",
                    description: "Examen d'entrée obligatoire",
                    details: [
                        "Test YÖS requis pour l'admission",
                        "Examen de mathématiques et de raisonnement",
                        "Inscription en ligne sur le site officiel",
                        "Préparation recommandée 3-6 mois à l'avance",
                        "Score minimum variable selon les universités"
                    ]
                }
            ],
            calendrier: [
                { mois: "Janv-Mar", dates: "Inscription YÖS" },
                { mois: "Avr-Mai", dates: "Examen YÖS" }
            ],
            avantages: [
                "Universités de qualité reconnues internationalement",
                "Coût de la vie abordable",
                "Diversité des programmes disponibles"
            ],
            frais: {
                inscription: "Variable selon l'université",
                scolarite: "Abordable et compétitif"
            },
            lien: "/babylone/public/turquie/etude/etude.php"
        },
        'espagne': {
            title: "Étudier en Espagne",
            subtitle: "Homologation du Bac nécessaire pour étudiants algériens",
            procedures: [
                {
                    title: "📄 Homologation du Bac",
                    description: "Reconnaissance officielle obligatoire",
                    details: [
                        "Dossier à déposer au Ministère espagnol",
                        "Délai de traitement : 4 à 6 mois",
                        "Coût de la procédure : 160 €",
                        "Traduction assermentée des documents",
                        "Processus long nécessitant une anticipation"
                    ]
                }
            ],
            calendrier: [
                { mois: "Janv-Mars", dates: "Demande admission" },
                { mois: "Avr-Mai", dates: "La selection" }
            ],
            avantages: [
                "Qualité de vie exceptionnelle",
                "Enseignement supérieur reconnu",
                "Proximité géographique et culturelle"
            ],
            frais: {
                inscription: "160 € (homologation)",
                scolarite: "Variable selon l'université et la région"
            },
            lien: "/babylone/public/espagne/etudes/etudes.php"
        }
    };

    // Gestion des modals
    function initModal() {
        const modal = document.getElementById('study-modal');
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeStudyModal();
            }
        });
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeStudyModal();
            }
        });
    }

    function openStudyModal(countryId) {
        const data = studyData[countryId];
        if (!data) return;

        const modal = document.getElementById('study-modal');
        const title = document.getElementById('study-modal-title');
        const subtitle = document.getElementById('study-modal-subtitle');
        const body = document.getElementById('study-modal-body');
        const footer = document.getElementById('study-modal-footer');

        title.textContent = data.title;
        subtitle.textContent = data.subtitle;

        body.innerHTML = `
            <div class="study-procedures-detailed">
                ${data.procedures.map(proc => `
                    <div class="study-procedure-card">
                        <h3>${proc.title}</h3>
                        <p style="color: #64748b; margin-bottom: 20px; font-size: 1rem; line-height: 1.5;">${proc.description}</p>
                        <ul>
                            ${proc.details.map(item => `<li>${item}</li>`).join('')}
                        </ul>
                    </div>
                `).join('')}
            </div>

            <div class="study-calendar-section">
                <h3 style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
                    <i class="fas fa-calendar-alt" style="color: var(--accent-orange);"></i>
                    Calendrier Académique 2024-2025
                </h3>
                <div class="study-calendar-grid">
                    ${data.calendrier.map(item => `
                        <div class="study-calendar-item">
                            <div class="study-calendar-month">${item.mois}</div>
                            <div class="study-calendar-dates">${item.dates}</div>
                        </div>
                    `).join('')}
                </div>
            </div>

            <div class="study-advantages-section">
                <h3 style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
                    <i class="fas fa-star" style="color: var(--accent-purple);"></i>
                    Avantages pour étudiants algériens
                </h3>
                <div class="advantages-grid">
                    ${data.avantages.map(avantage => `
                        <div class="advantage-item">
                            <i class="fas fa-check"></i>
                            <span style="font-size: 1rem; font-weight: 500;">${avantage}</span>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;

        footer.innerHTML = `
            <a href="${data.lien}" class="postuler-btn">
                <i class="fas fa-paper-plane"></i>
                <span>Postuler pour ${data.title.split(' ')[2]}</span>
            </a>
        `;

        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeStudyModal() {
        document.getElementById('study-modal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Slider automatique
    setInterval(() => {
        if (!isGridView) {
            slideNext();
        }
    }, 5000);
</script>