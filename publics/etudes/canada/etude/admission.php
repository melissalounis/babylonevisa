<?php
// service_etudes_canada.php - Page principale de sélection des services

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service'])) {
    $service_choisi = $_POST['service'];
    
    // Redirection vers le service approprié
    switch($service_choisi) {
        case 'visa_etranger':
            header('Location:demande_visa.php');
            exit();
        case 'caq_quebec':
            header('Location: demandes_caq.php');
            exit();
        case 'attestation_province':
            header('Location: demande_attestation_province.php');
            exit();
        case 'admission':
            header('Location: etude.php');
            exit();
        default:
            $erreur = "Service non reconnu. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Services d'études au Canada</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .sous-titre {
            color: #7f8c8d;
            font-size: 1.2rem;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .service-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            padding: 20px;
            color: white;
            text-align: center;
            flex-shrink: 0;
        }
        
        .visa-etranger .card-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }
        
        .caq-quebec .card-header {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        
        .attestation-province .card-header {
            background: linear-gradient(135deg, #f39c12 0%, #d35400 100%);
        }
        
        .admission .card-header {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
        }
        
        .card-header h3 {
            font-size: 1.4rem;
            margin-bottom: 5px;
        }
        
        .card-body {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .card-body p {
            margin-bottom: 15px;
        }
        
        .card-body ul {
            list-style-type: none;
            margin-bottom: 20px;
            flex-grow: 1;
        }
        
        .card-body li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            position: relative;
            padding-left: 25px;
        }
        
        .card-body li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #2ecc71;
            font-weight: bold;
        }
        
        .card-body li:last-child {
            border-bottom: none;
        }
        
        .service-card input[type="radio"] {
            display: none;
        }
        
        .service-card label {
            display: flex;
            flex-direction: column;
            cursor: pointer;
            height: 100%;
        }
        
        .btn-continuer {
            display: block;
            width: 200px;
            margin: 30px auto;
            padding: 15px;
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            color: white;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .btn-continuer:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #8e44ad 0%, #7d3c98 100%);
        }
        
        .erreur {
            background: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
            display: none;
        }
        
        .info-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #3498db;
        }
        
        .info-box h3 {
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .process-guide {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .process-step {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .step-number {
            background: #3498db;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-weight: bold;
        }
        
        footer {
            text-align: center;
            margin-top: 40px;
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Services d'études au Canada</h1>
            <p class="sous-titre">Choisissez le service correspondant à votre besoin</p>
        </header>
        
        <?php if (isset($erreur)): ?>
            <div class="erreur" style="display: block;"><?php echo $erreur; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="" id="form-service">
            <div class="services-grid">
                <!-- Demande de visa pour l'étranger -->
                <div class="service-card visa-etranger">
                    <input type="radio" id="visa_etranger" name="service" value="visa_etranger" required>
                    <label for="visa_etranger">
                        <div class="card-header">
                            <h3>Demande de Visa</h3>
                            <p>Pour études au Canada</p>
                        </div>
                        <div class="card-body">
                            <p>Service spécialisé pour les demandes de visa d'études pour toutes les provinces canadiennes.</p>
                            <ul>
                                <li>Visa d'études pour toutes provinces</li>
                                <li>Assistance complète de dossier</li>
                                <li>Suivi personnalisé</li>
                                <li>Renouvellement de visa</li>
                            </ul>
                        </div>
                    </label>
                </div>
                
                <!-- Demande de CAQ pour Québec -->
                <div class="service-card caq-quebec">
                    <input type="radio" id="caq_quebec" name="service" value="caq_quebec">
                    <label for="caq_quebec">
                        <div class="card-header">
                            <h3>Demande de CAQ</h3>
                            <p>Pour études au Québec</p>
                        </div>
                        <div class="card-body">
                            <p>Service spécialisé pour les demandes de CAQ (Certificat d'Acceptation du Québec) requis avant le visa.</p>
                            <ul>
                                <li>CAQ pour études au Québec</li>
                                <li>Préparation du dossier SAQ</li>
                                <li>Coordination avec MIFI</li>
                                <li>Suivi jusqu'à l'obtention</li>
                            </ul>
                        </div>
                    </label>
                </div>
                
                <!-- Attestation de Province pour autres provinces -->
                <div class="service-card attestation-province">
                    <input type="radio" id="attestation_province" name="service" value="attestation_province">
                    <label for="attestation_province">
                        <div class="card-header">
                            <h3>Attestation de Province</h3>
                            <p>Pour autres provinces</p>
                        </div>
                        <div class="card-body">
                            <p>Service pour l'obtention d'attestations provinciales requises pour les études dans les autres provinces canadiennes.</p>
                            <ul>
                                <li>Attestation pour Ontario, Colombie-Britannique, etc.</li>
                                <li>Documents spécifiques à chaque province</li>
                                <li>Coordination avec autorités provinciales</li>
                                <li>Validation des critères d'admissibilité</li>
                            </ul>
                        </div>
                    </label>
                </div>
                
                <!-- Demandes d'admission -->
                <div class="service-card admission">
                    <input type="radio" id="admission" name="service" value="admission">
                    <label for="admission">
                        <div class="card-header">
                            <h3>Demandes d'Admission</h3>
                            <p>Dans les établissements canadiens</p>
                        </div>
                        <div class="card-body">
                            <p>Service d'assistance pour les demandes d'admission dans les universités et collèges canadiens.</p>
                            <ul>
                                <li>Choix d'établissements</li>
                                <li>Préparation de dossier</li>
                                <li>Lettres de motivation</li>
                                <li>Suivi des admissions</li>
                            </ul>
                        </div>
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn-continuer">Continuer vers le service</button>
        </form>
        
        <div class="info-box">
            <h3>Processus d'études au Canada</h3>
            <div class="process-guide">
                <div class="process-step">
                    <div class="step-number">1</div>
                    <h4>Admission</h4>
                    <p>Obtenir une lettre d'acceptation d'un établissement canadien</p>
                </div>
                <div class="process-step">
                    <div class="step-number">2</div>
                    <h4>Document provincial</h4>
                    <p>CAQ pour Québec ou Attestation pour autres provinces</p>
                </div>
                <div class="process-step">
                    <div class="step-number">3</div>
                    <h4>Visa d'études</h4>
                    <p>Demande de permis d'études auprès d'IRCC</p>
                </div>
            </div>
        </div>
        
        <footer>
            <p>Service d'assistance aux études au Canada &copy; <?php echo date('Y'); ?></p>
        </footer>
    </div>
    
    <script>
        // Ajouter un effet de sélection visuelle
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('click', function() {
                // Retirer la sélection de toutes les cartes
                document.querySelectorAll('.service-card').forEach(c => {
                    c.style.border = 'none';
                });
                
                // Ajouter un effet de bordure à la carte sélectionnée
                this.style.border = '3px solid #3498db';
                
                // Cocher le radio button correspondant
                const radio = this.querySelector('input[type="radio"]');
                radio.checked = true;
            });
        });
        
        // Validation du formulaire
        document.getElementById('form-service').addEventListener('submit', function(e) {
            const selected = document.querySelector('input[name="service"]:checked');
            if (!selected) {
                e.preventDefault();
                alert('Veuillez sélectionner un service avant de continuer.');
            }
        });
        
        // Sélection automatique au survol
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
            });
            
            card.addEventListener('mouseleave', function() {
                if (!this.querySelector('input[type="radio"]').checked) {
                    this.style.transform = 'translateY(0)';
                }
            });
        });
    </script>
</body>
</html>