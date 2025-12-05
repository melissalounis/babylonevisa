<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Connexion à la base de données

require_once __DIR__ . '../../../config.php';
try {
    
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}

// Récupérer les évaluations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT * FROM evaluations_immigration 
    WHERE email = (SELECT email FROM users WHERE id = :user_id)
    ORDER BY date_soumission DESC
");
$stmt->execute([':user_id' => $user_id]);
$evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fonction pour formater le nom du programme
function formatProgramme($programme) {
    $noms = [
        'express' => 'Entrée Express',
        'arrima' => 'Arrima Québec'
    ];
    return $noms[$programme] ?? $programme;
}

// Fonction pour formater la situation familiale
function formatSituation($situation) {
    $noms = [
        'celibataire' => 'Célibataire',
        'marie' => 'Marié(e)',
        'divorce' => 'Divorcé(e)'
    ];
    return $noms[$situation] ?? $situation;
}

// Fonction pour formater l'éducation
function formatEducation($education, $programme) {
    if ($programme === 'express') {
        $niveaux = [
            'secondary' => 'Secondaire',
            'post-secondary' => 'Post-secondaire',
            'bachelor' => 'Licence',
            'master' => 'Master',
            'phd' => 'Doctorat'
        ];
    } else {
        $niveaux = [
            'secondary' => 'Secondaire',
            'college' => 'Collégial',
            'bachelor' => 'Licence',
            'master' => 'Master',
            'phd' => 'Doctorat'
        ];
    }
    return $niveaux[$education] ?? $education;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Demandes d'Immigration</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            color: #334155;
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* En-tête */
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding: 40px 0;
        }

        .header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header h1 i {
            color: #3b82f6;
        }

        .header p {
            font-size: 1.1rem;
            color: #64748b;
        }

        /* Carte de demande */
        .demande-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .demande-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .demande-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f5f9;
        }

        .programme-badge {
            background: #1e293b;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .date-soumission {
            color: #64748b;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 8px;
        }

        .statut {
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .statut-eligible {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .statut-ineligible {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Grille d'informations */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .info-group {
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .info-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 12px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-label i {
            color: #3b82f6;
            width: 20px;
        }

        .info-value {
            color: #6b7280;
            font-size: 0.95rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-value i {
            color: #9ca3af;
            width: 16px;
        }

        /* Section score */
        .score-section {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            margin: 25px 0;
        }

        .score-value {
            font-size: 3rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .score-seuil {
            font-size: 1rem;
            opacity: 0.9;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Boutons d'action */
        .actions {
            display: flex;
            gap: 12px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-2px);
        }

        /* État vide */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            color: #9ca3af;
            margin-bottom: 20px;
        }

        .empty-state h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #374151;
        }

        .empty-state p {
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .btn-large {
            padding: 15px 30px;
            font-size: 1.1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .demande-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-file-alt"></i> Mes Demandes d'Immigration</h1>
            <p>Consultez l'historique de vos évaluations</p>
        </div>

        <?php if (empty($evaluations)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h2>Aucune demande trouvée</h2>
                <p>Vous n'avez pas encore effectué d'évaluation d'immigration.</p>
                <a href="index.php" class="btn btn-primary btn-large">
                    <i class="fas fa-plus-circle"></i> Faire une évaluation
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($evaluations as $evaluation): ?>
                <div class="demande-card">
                    <div class="demande-header">
                        <div>
                            <span class="programme-badge">
                                <i class="fas fa-passport"></i>
                                <?= formatProgramme($evaluation['programme']) ?>
                            </span>
                            <div class="date-soumission">
                                <i class="far fa-clock"></i>
                                Soumis le <?= date('d/m/Y à H:i', strtotime($evaluation['date_soumission'])) ?>
                            </div>
                        </div>
                        <span class="statut <?= $evaluation['eligible'] ? 'statut-eligible' : 'statut-ineligible' ?>">
                            <?php if ($evaluation['eligible']): ?>
                                <i class="fas fa-check-circle"></i> Éligible
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i> Non éligible
                            <?php endif; ?>
                        </span>
                    </div>

                    <div class="info-grid">
                        <div class="info-group">
                            <div class="info-label">
                                <i class="fas fa-user-circle"></i> Informations personnelles
                            </div>
                            <div class="info-value">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($evaluation['nom']) ?>
                            </div>
                            <div class="info-value">
                                <i class="fas fa-envelope"></i> <?= htmlspecialchars($evaluation['email']) ?>
                            </div>
                            <div class="info-value">
                                <i class="fas fa-birthday-cake"></i> Âge: <?= $evaluation['age'] ?> ans
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">
                                <i class="fas fa-graduation-cap"></i> Éducation & Expérience
                            </div>
                            <div class="info-value">
                                <i class="fas fa-book"></i> <?= formatEducation($evaluation['education'], $evaluation['programme']) ?>
                            </div>
                            <div class="info-value">
                                <i class="fas fa-briefcase"></i> Expérience: <?= $evaluation['experience'] ?> an(s)
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">
                                <i class="fas fa-language"></i> Langues
                            </div>
                            <div class="info-value">
                                <i class="fas fa-flag-usa"></i> Anglais: CLB <?= $evaluation['english_level'] ?>
                            </div>
                            <div class="info-value">
                                <i class="fas fa-flag"></i> Français: CLB <?= $evaluation['french_level'] ?>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">
                                <i class="fas fa-users"></i> Situation familiale
                            </div>
                            <div class="info-value">
                                <i class="fas fa-heart"></i> <?= formatSituation($evaluation['situation_familiale']) ?>
                            </div>
                            <div class="info-value">
                                <i class="fas fa-child"></i> Enfants: <?= $evaluation['enfants'] ?>
                            </div>
                            <?php if ($evaluation['province']): ?>
                                <div class="info-value">
                                    <i class="fas fa-map-marker-alt"></i> Province: <?= htmlspecialchars($evaluation['province']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="score-section">
                        <div class="info-label" style="color: white; margin-bottom: 10px; justify-content: center;">
                            <i class="fas fa-chart-bar"></i> Score d'évaluation
                        </div>
                        <div class="score-value"><?= $evaluation['score'] ?> points</div>
                        <?php if ($evaluation['programme'] === 'arrima' && isset($_SESSION['seuil'])): ?>
                            <div class="score-seuil">
                                <i class="fas fa-bullseye"></i> Seuil requis: <?= $_SESSION['seuil'] ?> points
                            </div>
                        <?php elseif ($evaluation['programme'] === 'express'): ?>
                            <div class="score-seuil">
                                <i class="fas fa-bullseye"></i> Seuil requis: 67 points
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="actions">
                        <?php if ($evaluation['eligible']): ?>
                            <a href="documents.php" class="btn btn-primary">
                                <i class="fas fa-file-pdf"></i> Voir les documents requis
                            </a>
                        <?php else: ?>
                            <a href="ineligible.php" class="btn btn-secondary">
                                <i class="fas fa-info-circle"></i> Comprendre les résultats
                            </a>
                        <?php endif; ?>
                        <button onclick="imprimerDemande(<?= $evaluation['id'] ?>)" class="btn btn-secondary">
                            <i class="fas fa-print"></i> Imprimer
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function imprimerDemande(id) {
            // Ici vous pouvez implémenter l'impression ou le PDF
            alert('Fonction d\'impression pour la demande #' + id);
            // window.open('imprimer_demande.php?id=' + id, '_blank');
        }

        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.demande-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = (index * 0.1) + 's';
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease-out';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 + (index * 100));
            });
        });
    </script>
</body>
</html>