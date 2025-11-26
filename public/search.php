<?php
require_once __DIR__ . '/../config.php';

$results = [];
$keyword = '';
$safe_keyword = '';

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $keyword = trim($_GET['q']);
    $safe_keyword = htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8');
    
    // Recherche dans la base de données
    $sql = "SELECT * FROM services 
            WHERE titre LIKE :q OR description LIKE :q 
            OR mots_cles LIKE :q
            ORDER BY 
                CASE 
                    WHEN titre LIKE :q_exact THEN 1
                    WHEN titre LIKE :q_start THEN 2
                    WHEN description LIKE :q_start THEN 3
                    ELSE 4
                END,
                titre ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'q' => "%$keyword%",
        'q_exact' => "$keyword",
        'q_start' => "$keyword%"
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si un seul résultat exact est trouvé, redirection immédiate
    if (count($results) === 1 && stripos($results[0]['titre'], $keyword) === 0) {
        header("Location: " . $results[0]['slug']);
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="search-hero">
    <div class="hero-content">
        <h1><i class="fas fa-search"></i> Recherche</h1>
        <p>Trouvez le service qui correspond à vos besoins</p>
    </div>
    <div class="hero-pattern"></div>
</div>

<div class="search-container">
    <div class="search-box-container">
        <form method="GET" action="/babylone/public/search.php" class="search-form">
            <div class="search-input-group">
                <input type="text" 
                       name="q" 
                       value="<?= $safe_keyword ?>" 
                       placeholder="Rechercher un service..." 
                       class="search-input"
                       autocomplete="off">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <?php if ($keyword): ?>
    <div class="search-results-section">
        <div class="results-header">
            <h2>Résultats pour : <span class="keyword">"<?= $safe_keyword ?>"</span></h2>
            <p class="results-count"><?= count($results) ?> résultat(s) trouvé(s)</p>
        </div>

        <?php if ($results): ?>
        <div class="results-grid">
            <?php foreach ($results as $row): 
                $title = htmlspecialchars($row['titre']);
                $description = htmlspecialchars($row['description']);
                $slug = htmlspecialchars($row['slug']);
                $highlighted_title = preg_replace("/(" . preg_quote($keyword, '/') . ")/i", '<mark>$1</mark>', $title);
                $highlighted_desc = preg_replace("/(" . preg_quote($keyword, '/') . ")/i", '<mark>$1</mark>', $description);
            ?>
            <div class="result-card">
                <div class="card-header">
                    <h3 class="result-title"><?= $highlighted_title ?></h3>
                    <span class="result-category">Service</span>
                </div>
                <div class="card-body">
                    <p class="result-description"><?= nl2br($highlighted_desc) ?></p>
                </div>
                <div class="card-footer">
                    <a href="/babylone/public/<?= $slug ?>" class="result-link">
                        Voir le service
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-results">
            <div class="no-results-icon">
                <i class="fas fa-search"></i>
            </div>
            <h3>Aucun résultat trouvé</h3>
            <p>Nous n'avons trouvé aucun service correspondant à votre recherche.</p>
            <div class="suggestions">
                <p>Suggestions :</p>
                <ul>
                    <li>Vérifiez l'orthographe des mots</li>
                    <li>Utilisez des termes plus généraux</li>
                    <li>Essayez d'autres mots-clés</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="search-intro">
        <div class="intro-content">
            <i class="fas fa-search fa-3x"></i>
            <h2>Que recherchez-vous ?</h2>
            <p>Utilisez la barre de recherche pour trouver des services, des informations ou de l'aide.</p>
            
            <div class="popular-searches">
                <h3>Recherches populaires :</h3>
                <div class="tags">
                    <a href="/babylone/public/search.php?q=visa" class="tag">Visa</a>
                    <a href="/babylone/public/search.php?q=études" class="tag">Études</a>
                    <a href="/babylone/public/search.php?q=travail" class="tag">Travail</a>
                    <a href="/babylone/public/search.php?q=france" class="tag">France</a>
                    <a href="/babylone/public/search.php?q=tourisme" class="tag">Tourisme</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Variables */
:root {
    --primary-blue: #0056b3;
    --secondary-blue: #0077ff;
    --accent-orange: #ff6b35;
    --light-bg: #f8fafc;
    --dark-text: #2d3748;
    --white: #ffffff;
    --light-gray: #e2e8f0;
    --border-color: #e5e7eb;
    --success-color: #10b981;
    --error-color: #ef4444;
    --transition: all 0.3s ease;
}

/* Hero Section */
.search-hero {
    background: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-blue) 100%);
    color: var(--white);
    padding: 80px 20px;
    text-align: center;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
}

.hero-content h1 {
    font-size: 2.8rem;
    margin-bottom: 15px;
    font-weight: 700;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.hero-content h1 i {
    margin-right: 15px;
}

.hero-content p {
    font-size: 1.3rem;
    opacity: 0.95;
    max-width: 600px;
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

/* Search Container */
.search-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 20px 80px;
}

/* Search Box */
.search-box-container {
    margin-bottom: 40px;
}

.search-form {
    max-width: 600px;
    margin: 0 auto;
}

.search-input-group {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--white);
    border: 2px solid var(--border-color);
    border-radius: 50px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.search-input-group:focus-within {
    border-color: var(--primary-blue);
    box-shadow: 0 8px 25px rgba(0, 86, 179, 0.15);
    transform: translateY(-2px);
}

.search-input {
    flex: 1;
    padding: 18px 25px;
    border: none;
    outline: none;
    font-size: 1.1rem;
    background: transparent;
}

.search-input::placeholder {
    color: #94a3b8;
}

.search-btn {
    padding: 18px 25px;
    border: none;
    background: var(--primary-blue);
    color: var(--white);
    cursor: pointer;
    transition: var(--transition);
    font-size: 1.2rem;
}

.search-btn:hover {
    background: var(--secondary-blue);
}

/* Results Section */
.results-header {
    text-align: center;
    margin-bottom: 40px;
}

.results-header h2 {
    font-size: 1.8rem;
    color: var(--dark-text);
    margin-bottom: 10px;
    font-weight: 600;
}

.keyword {
    color: var(--primary-blue);
    font-weight: 700;
}

.results-count {
    color: #64748b;
    font-size: 1.1rem;
}

/* Results Grid */
.results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.result-card {
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    transition: var(--transition);
    border: 1px solid var(--light-gray);
}

.result-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

.card-header {
    padding: 20px;
    border-bottom: 1px solid var(--light-gray);
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 15px;
}

.result-title {
    font-size: 1.3rem;
    color: var(--dark-text);
    margin: 0;
    font-weight: 600;
    flex: 1;
}

.result-title mark {
    background: linear-gradient(120deg, #fffbeb 0%, #fef3c7 100%);
    color: #92400e;
    padding: 2px 4px;
    border-radius: 4px;
    font-weight: 700;
}

.result-category {
    background: var(--light-bg);
    color: var(--primary-blue);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.card-body {
    padding: 20px;
}

.result-description {
    color: #64748b;
    line-height: 1.6;
    margin: 0;
}

.result-description mark {
    background: linear-gradient(120deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
    padding: 2px 4px;
    border-radius: 4px;
    font-weight: 500;
}

.card-footer {
    padding: 20px;
    border-top: 1px solid var(--light-gray);
}

.result-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--primary-blue);
    text-decoration: none;
    font-weight: 600;
    transition: var(--transition);
}

.result-link:hover {
    color: var(--secondary-blue);
    gap: 12px;
}

/* No Results */
.no-results {
    text-align: center;
    padding: 60px 40px;
    background: var(--white);
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
}

.no-results-icon {
    font-size: 4rem;
    color: var(--light-gray);
    margin-bottom: 20px;
}

.no-results h3 {
    font-size: 1.5rem;
    color: var(--dark-text);
    margin-bottom: 15px;
    font-weight: 600;
}

.no-results p {
    color: #64748b;
    margin-bottom: 25px;
    font-size: 1.1rem;
}

.suggestions {
    text-align: left;
    max-width: 400px;
    margin: 0 auto;
}

.suggestions p {
    font-weight: 600;
    color: var(--dark-text);
    margin-bottom: 10px;
}

.suggestions ul {
    text-align: left;
    color: #64748b;
    padding-left: 20px;
}

.suggestions li {
    margin-bottom: 8px;
}

/* Search Intro */
.search-intro {
    text-align: center;
    padding: 60px 20px;
}

.intro-content {
    max-width: 600px;
    margin: 0 auto;
}

.intro-content i {
    color: var(--light-gray);
    margin-bottom: 20px;
}

.intro-content h2 {
    font-size: 1.8rem;
    color: var(--dark-text);
    margin-bottom: 15px;
    font-weight: 600;
}

.intro-content p {
    color: #64748b;
    font-size: 1.1rem;
    margin-bottom: 40px;
}

.popular-searches {
    margin-top: 40px;
}

.popular-searches h3 {
    font-size: 1.2rem;
    color: var(--dark-text);
    margin-bottom: 20px;
    font-weight: 600;
}

.tags {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
}

.tag {
    background: var(--light-bg);
    color: var(--primary-blue);
    padding: 8px 16px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
    border: 1px solid var(--border-color);
}

.tag:hover {
    background: var(--primary-blue);
    color: var(--white);
    transform: translateY(-2px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-hero {
        padding: 60px 20px;
    }
    
    .hero-content h1 {
        font-size: 2.2rem;
    }
    
    .results-grid {
        grid-template-columns: 1fr;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .search-input-group {
        border-radius: 12px;
        flex-direction: column;
    }
    
    .search-input {
        padding: 15px 20px;
    }
    
    .search-btn {
        width: 100%;
        padding: 15px;
        border-radius: 0 0 10px 10px;
    }
    
    .tags {
        justify-content: flex-start;
    }
}

@media (max-width: 480px) {
    .search-container {
        padding: 0 15px 60px;
    }
    
    .hero-content h1 {
        font-size: 1.8rem;
    }
    
    .no-results {
        padding: 40px 20px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Focus sur le champ de recherche
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.focus();
        
        // Sauvegarder les recherches récentes
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    saveRecentSearch(searchTerm);
                }
            }
        });
    }
    
    // Animation des résultats
    const resultCards = document.querySelectorAll('.result-card');
    resultCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        
        setTimeout(() => {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 + (index * 100));
    });
});

function saveRecentSearch(term) {
    // Implémentation pour sauvegarder les recherches récentes
    // (peut utiliser localStorage ou envoyer à l'API)
    console.log('Recherche sauvegardée:', term);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>