<?php
// Démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification que l'évaluation a été effectuée et récupération sécurisée des données
if (!isset($_SESSION['evaluation_score']) || !isset($_SESSION['is_eligible'])) {
    header("Location: evaluation_express.php");
    exit;
}

$score = $_SESSION['evaluation_score'] ?? 0;
$is_eligible = $_SESSION['is_eligible'] ?? false;
$seuil = 67; // Seuil pour Entrée Express
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Non éligible - Évaluation Entrée Express</title>
    <style>
        :root {
            --primary-color: #e31837;
            --secondary-color: #ffffff;
            --warning-color: #ff9800;
            --success-color: #2e7d32;
            --dark-color: #2c2c2c;
            --light-color: #f5f5f5;
            --muted-color: #666666;
            --border-color: #dee2e6;
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            width: 100%;
            background: var(--secondary-color);
            border-radius: 12px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            border-top: 6px solid var(--warning-color);
        }

        header {
            background: linear-gradient(135deg, var(--warning-color), #e68900);
            color: var(--secondary-color);
            padding: 45px 30px;
            text-align: center;
            position: relative;
        }

        header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255, 255, 255, 0.3);
        }

        header h1 {
            margin-bottom: 12px;
            font-size: 2.4rem;
            font-weight: 700;
        }

        header p {
            font-size: 1.2rem;
            opacity: 0.95;
            font-weight: 400;
        }

        .content {
            padding: 45px;
        }

        .score-display {
            background: linear-gradient(135deg, #fff9e6, #fff3cd);
            border: 2px solid #ffeeba;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 35px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.1);
        }

        .score-display h3 {
            color: var(--warning-color);
            margin-bottom: 12px;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .score-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--warning-color);
            margin: 10px 0;
        }

        .threshold {
            font-size: 1.3rem;
            color: var(--muted-color);
            font-weight: 500;
        }

        .message {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
            border-left: 5px solid var(--primary-color);
        }

        .message h2 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 1.6rem;
        }

        .message p {
            color: var(--dark-color);
            font-size: 1.1rem;
            line-height: 1.7;
        }

        .recommendations {
            margin: 35px 0;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .recommendations h3 {
            color: var(--dark-color);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--warning-color);
            font-size: 1.4rem;
            font-weight: 600;
        }

        .recommendations ul {
            list-style: none;
            padding: 0;
        }

        .recommendations li {
            margin-bottom: 16px;
            padding: 12px 15px;
            background: white;
            border-radius: 6px;
            border-left: 4px solid var(--warning-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease;
        }

        .recommendations li:hover {
            transform: translateX(5px);
        }

        .recommendations strong {
            color: var(--primary-color);
            display: block;
            margin-bottom: 5px;
            font-size: 1.05rem;
        }

        .actions {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 45px;
        }

        .btn {
            padding: 16px 32px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 180px;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #a00);
            color: var(--secondary-color);
            box-shadow: 0 4px 15px rgba(227, 24, 55, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(227, 24, 55, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: var(--secondary-color);
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
            background: linear-gradient(135deg, #5a6268, #3d4348);
        }

        footer {
            text-align: center;
            margin-top: 40px;
            padding: 25px;
            color: var(--muted-color);
            font-size: 0.95rem;
            border-top: 1px solid var(--border-color);
            background: #f8f9fa;
        }

        footer p {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        /* Indicateur visuel */
        .eligibility-indicator {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #ffebee;
            border: 1px solid #ffcdd2;
            border-radius: 8px;
            color: #c62828;
            font-weight: 600;
            font-size: 1.1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .content {
                padding: 25px;
            }

            header {
                padding: 30px 20px;
            }

            header h1 {
                font-size: 1.8rem;
            }

            .score-value {
                font-size: 2rem;
            }

            .actions {
                flex-direction: column;
                gap: 15px;
            }

            .btn {
                min-width: 100%;
                padding: 14px 20px;
            }

            .recommendations {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            header h1 {
                font-size: 1.5rem;
            }

            .content {
                padding: 20px;
            }

            .score-display {
                padding: 20px;
            }

            .score-value {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Résultat de l'Évaluation — Entrée Express</h1>
            <p>Votre profil ne répond pas actuellement aux critères d'éligibilité</p>
        </header>

        <div class="content">
            <!-- Indicateur de non-éligibilité -->
            <div class="eligibility-indicator">
                ❌ Statut : Non éligible pour le programme Entrée Express
            </div>

            <!-- Section Score -->
            <div class="score-display">
                <h3>Votre score d'évaluation</h3>
                <div class="score-value"><?php echo htmlspecialchars($score); ?> points</div>
                <div class="threshold">Le score minimum requis est de : <strong><?php echo $seuil; ?> points</strong></div>
            </div>

            <!-- Message principal -->
            <div class="message">
                <h2>Nous sommes désolés</h2>
                <p>Votre score actuel ne vous permet pas de poursuivre le processus pour l'Entrée Express. 
                   Cependant, ce n'est pas une fin en soi — plusieurs options s'offrent à vous pour améliorer votre admissibilité et atteindre vos objectifs d'immigration.</p>
            </div>

            <!-- Recommandations détaillées -->
            <div class="recommendations">
                <h3>Recommandations pour améliorer votre admissibilité</h3>
                <ul>
                    <li>
                        <strong>Améliorez vos compétences linguistiques</strong>
                        Obtenez de meilleurs résultats aux tests de langue officiels (IELTS, CELPIP pour l'anglais ; TEF, TCF pour le français)
                    </li>
                    <li>
                        <strong>Acquérez plus d'expérience professionnelle</strong>
                        L'expérience supplémentaire, surtout dans des métiers en demande, peut significativement augmenter votre score
                    </li>
                    <li>
                        <strong>Poursuivez vos études</strong>
                        Un niveau d'éducation plus élevé (master, doctorat) améliore considérablement vos points
                    </li>
                    <li>
                        <strong>Explorez d'autres programmes d'immigration</strong>
                        Les Programmes des Candidats des Provinces (PNP) ou le volet Expérience Canadienne pourraient correspondre à votre profil
                    </li>
                    <li>
                        <strong>Consultez un conseiller en immigration agréé</strong>
                        Un professionnel peut vous aider à identifier vos meilleures options et optimiser votre dossier
                    </li>
                </ul>
            </div>

            <!-- Actions possibles -->
            <div class="actions">
                <a href="evaluation.php" class="btn btn-primary">Nouvelle évaluation</a>
                <a href="../../contact.php" class="btn btn-secondary"> Contactez un conseiller</a>
            </div>
        </div>

        <footer>
            <p>⚠ <strong>Important</strong> : Cet outil fournit une estimation informelle seulement. Pour une évaluation précise de votre admissibilité, consultez le site officiel d'Immigration, Réfugiés et Citoyenneté Canada (IRCC) ou un consultant en immigration autorisé.</p>
            <p>© 2025 - Outil d'évaluation pour l'immigration canadienne. Tous droits réservés.</p>
        </footer>
    </div>

    <script>
        // Animation simple pour améliorer l'expérience utilisateur
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.score-display, .message, .recommendations');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    element.style.transition = 'all 0.6s ease';
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 200);
            });
        });
    </script>
</body>
</html>