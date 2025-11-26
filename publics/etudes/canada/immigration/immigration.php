<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évaluation Immigration Canadienne</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Évaluation pour l'Immigration Canadienne</h1>
            <p>Sélectionnez le service d'évaluation qui correspond à votre situation</p>
        </header>
        
        <div class="service-selection">
            <!-- Entrée Express -->
            <a href="evaluation.php" class="service-card express-card">
                <h2>Évaluation pour Entrée Express</h2>
                <p>Système fédéral pour travailleurs qualifiés</p>
                <ul>
                    <li>Programme fédéral des travailleurs qualifiés</li>
                    <li>Programme des métiers spécialisés</li>
                    <li>Expérience canadienne</li>
                </ul>
            </a>
            
            <!-- Arrima -->
            <a href="evaluation_arrima.php" class="service-card arrima-card">
                <h2>Évaluation pour Arrima</h2>
                <p>Système de sélection du Québec</p>
                <ul>
                    <li>Travailleurs qualifiés Québec</li>
                    <li>Critères spécifiques à la province</li>
                    <li>Français obligatoire</li>
                </ul>
            </a>
        </div>
    </div>
</body>
</html>

<style>
    :root {
        --primary-color: #e31837;
        --primary-dark: #a00;
        --secondary-color: #ffffff;
        --arrima-color: #0055a4;
        --dark-color: #2c2c2c;
        --light-color: #f5f5f5;
        --gray-light: #e9ecef;
        --gray-medium: #6c757d;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        color: var(--dark-color);
        line-height: 1.6;
        min-height: 100vh;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    header {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        color: var(--secondary-color);
        padding: 40px 30px;
        text-align: center;
        border-radius: 12px 12px 0 0;
        margin-bottom: 30px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    header h1 {
        margin-bottom: 15px;
        font-size: 2.5rem;
        font-weight: 700;
    }

    header p {
        font-size: 1.2rem;
        opacity: 0.9;
    }

    .service-selection {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 30px;
    }

    .service-card {
        background: var(--secondary-color);
        border: 3px solid var(--gray-light);
        border-radius: 12px;
        padding: 30px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }

    .service-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        border-color: var(--gray-medium);
    }

    .express-card:hover {
        border-color: var(--primary-color);
    }

    .arrima-card:hover {
        border-color: var(--arrima-color);
    }

    .service-card h2 {
        margin-bottom: 15px;
        font-size: 1.6rem;
    }

    .service-card p {
        margin-bottom: 10px;
        font-weight: 500;
    }

    .service-card ul {
        list-style: none;
        margin: 10px 0;
    }

    .service-card li {
        padding: 6px 0;
        border-bottom: 1px solid var(--gray-light);
    }

    .service-card li:last-child {
        border-bottom: none;
    }

    @media (max-width: 768px) {
        .service-selection {
            grid-template-columns: 1fr;
        }
    }
</style>
