<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier Campus France 2025-2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            color: white;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .month-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .month-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
        }

        .month-header {
            background: linear-gradient(135deg, #3b4656 0%, #2c3e50 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .month-header .month-name {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .month-header .year {
            font-size: 1rem;
            opacity: 0.8;
        }

        .events-container {
            padding: 20px;
        }

        .event-item {
            background: #f8f9fa;
            border-left: 4px solid #448cd6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .event-item:hover {
            background: #e3f2fd;
            transform: translateX(5px);
        }

        .event-item.dap {
            border-left-color: #4CAF50;
            background: #f1f8e9;
        }

        .event-item.dap:hover {
            background: #e8f5e9;
        }

        .event-item.hors-dap {
            border-left-color: #f44336;
            background: #ffebee;
        }

        .event-item.hors-dap:hover {
            background: #ffcdd2;
        }

        .event-item.master {
            border-left-color: #2196F3;
            background: #e3f2fd;
        }

        .event-item.master:hover {
            background: #bbdefb;
        }

        .event-item.parcoursup {
            border-left-color: #FF9800;
            background: #fff3e0;
        }

        .event-item.parcoursup:hover {
            background: #ffe0b2;
        }

        .event-icon {
            font-size: 1.2rem;
            margin-right: 10px;
            vertical-align: middle;
        }

        .event-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .event-details {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .event-date {
            display: inline-block;
            background: rgba(0,0,0,0.1);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-top: 8px;
            font-weight: 500;
        }

        .legend {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-top: 30px;
        }

        .legend h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
        }

        .legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .legend-item {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .footer {
            text-align: center;
            color: white;
            margin-top: 40px;
            opacity: 0.8;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .calendar-grid {
                grid-template-columns: 1fr;
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
            <h1>📅 Calendrier Campus France 2025-2026</h1>
            <p>Dates importantes pour votre projet d'études en France</p>
        </div>

        <div class="calendar-grid">
            <?php
            // Définition des événements avec dates précises
            $evenements_campus_france = [
                ['mois' => '2024-10', 'date' => '2024-10-01', 'icone' => '🚀', 'titre' => 'Début des inscriptions DAP', 'details' => 'Ouverture de la procédure "Demande d\'Admission Préalable"', 'classe' => 'dap'],
                ['mois' => '2024-12', 'date' => '2024-12-15', 'icone' => '⏳', 'titre' => 'Clôture des inscriptions DAP', 'details' => 'Date limite pour déposer un dossier DAP', 'classe' => 'dap'],
                ['mois' => '2024-12', 'date' => '2024-12-20', 'icone' => '🌐', 'titre' => 'Ouverture de Parcoursup', 'details' => 'Découverte des formations disponibles', 'classe' => 'parcoursup'],
                ['mois' => '2025-01', 'date' => '2025-01-15', 'icone' => '✍️', 'titre' => 'Saisie des voeux Parcoursup', 'details' => 'Période pour formuler les voeux (jusqu\'au 13 mars)', 'classe' => 'parcoursup'],
                ['mois' => '2025-02', 'date' => '2025-02-03', 'icone' => '🎓', 'titre' => 'Ouverture de Mon Master', 'details' => 'Découverte des formations de Master', 'classe' => 'master'],
                ['mois' => '2025-03', 'date' => '2025-03-24', 'icone' => '📤', 'titre' => 'Candidatures sur Mon Master', 'details' => 'Dépôt des candidatures pour les Masters', 'classe' => 'master'],
                ['mois' => '2025-04', 'date' => '2025-04-30', 'icone' => '✅', 'titre' => 'Réponses des universités (DAP)', 'details' => 'Les universités donnent leur réponse', 'classe' => 'dap'],
                ['mois' => '2025-06', 'date' => '2025-06-02', 'icone' => '🎉', 'titre' => 'Phase principale d\'admission', 'details' => 'Lancement des admissions Parcoursup', 'classe' => 'parcoursup'],
                ['mois' => '2025-10', 'date' => '2025-10-01', 'icone' => '🚀', 'titre' => 'Début campagne 2026-2027', 'details' => 'Ouverture des inscriptions pour l\'année suivante', 'classe' => 'dap'],
                ['mois' => '2025-11', 'date' => '2025-11-16', 'icone' => '⏳', 'titre' => 'Clôture DAP 2026-2027', 'details' => 'Date limite pour la procédure DAP', 'classe' => 'dap'],
                ['mois' => '2025-12', 'date' => '2025-12-07', 'icone' => '⏳', 'titre' => 'Clôture Hors-DAP 2026-2027', 'details' => 'Date limite pour la procédure Hors-DAP', 'classe' => 'hors-dap']
            ];

            // Regrouper les événements par mois
            $evenements_par_mois = [];
            foreach ($evenements_campus_france as $event) {
                $evenements_par_mois[$event['mois']][] = $event;
            }

            // Période à afficher
            $mois_debut = new DateTime('2024-10-01');
            $mois_fin = new DateTime('2025-12-01');
            $intervalle = DateInterval::createFromDateString('1 month');
            $periode = new DatePeriod($mois_debut, $intervalle, $mois_fin);

            // Afficher les cartes des mois
            foreach ($periode as $mois) {
                $cle_mois = $mois->format('Y-m');
                $annee = $mois->format('Y');
                $nom_mois = obtenirNomMois($mois->format('n'));
                
                echo "<div class='month-card'>";
                echo "<div class='month-header'>";
                echo "<div class='month-name'>$nom_mois</div>";
                echo "<div class='year'>$annee</div>";
                echo "</div>";
                echo "<div class='events-container'>";
                
                if (isset($evenements_par_mois[$cle_mois])) {
                    foreach ($evenements_par_mois[$cle_mois] as $evenement) {
                        $date_formatee = date('d/m/Y', strtotime($evenement['date']));
                        echo "<div class='event-item {$evenement['classe']}'>";
                        echo "<div class='event-title'>";
                        echo "<span class='event-icon'>{$evenement['icone']}</span>";
                        echo "{$evenement['titre']}";
                        echo "</div>";
                        echo "<div class='event-details'>{$evenement['details']}</div>";
                        echo "<div class='event-date'>$date_formatee</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<div style='text-align: center; color: #666; padding: 20px;'>";
                    echo "<i class='fas fa-coffee' style='font-size: 2rem; margin-bottom: 10px; opacity: 0.5;'></i><br>";
                    echo "Aucun événement majeur ce mois-ci";
                    echo "</div>";
                }
                echo "</div></div>";
            }

            function obtenirNomMois($numero) {
                $mois = [1 => 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                return $mois[(int)$numero];
            }
            ?>
        </div>

        <div class="legend">
            <h3>Légende des procédures</h3>
            <div class="legend-items">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #4CAF50;"></div>
                    DAP (Demande d'Admission Préalable)
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #f44336;"></div>
                    Hors-DAP
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #2196F3;"></div>
                    Master
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #FF9800;"></div>
                    Parcoursup
                </div>
            </div>
        </div>

        <div class="footer">
            <p>⚠️ Les dates peuvent varier selon votre pays de résidence. Consultez votre Espace Campus France local.</p>
        </div>
    </div>

    <script>
        // Animation simple au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.month-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>