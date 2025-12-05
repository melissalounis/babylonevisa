<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DEBUG - Afficher les informations de session
echo "<!-- DEBUG: Session user_id = " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NON DEFINI') . " -->";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "<!-- DEBUG: Redirection vers login.php -->";
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '../../../config.php';



// Traitement du formulaire de choix
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['choix_contrat'])) {
    $choix = $_POST['choix_contrat'];
    
    echo "<!-- DEBUG: Choix reçu = " . htmlspecialchars($choix) . " -->";
    
    // Vérifier l'existence des fichiers avant redirection
    $fichier_visa = 'demande_visa_travail.php';
    $fichier_contrat = 'demande_contrat_travail.php';
    
    echo "<!-- DEBUG: Fichier visa existe = " . (file_exists($fichier_visa) ? 'OUI' : 'NON') . " -->";
    echo "<!-- DEBUG: Fichier contrat existe = " . (file_exists($fichier_contrat) ? 'OUI' : 'NON') . " -->";
    
    if ($choix === 'oui') {
        // Rediriger vers la demande de visa de travail
        if (file_exists($fichier_visa)) {
            header('Location: ' . $fichier_visa);
            exit();
        } else {
            die("Erreur: Le fichier $fichier_visa n'existe pas");
        }
    } else {
        // Rediriger vers le formulaire de demande de contrat
        if (file_exists($fichier_contrat)) {
            header('Location: ' . $fichier_contrat);
            exit();
        } else {
            die("Erreur: Le fichier $fichier_contrat n'existe pas");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choix du type de demande - Babylone Service</title>
    <style>
        :root {
            --primary-color: #1a237e;
            --primary-light: #534bae;
            --primary-dark: #000051;
            --secondary-color: #e8eaf6;
            --accent-color: #ff6d00;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --text-color: #212121;
            --text-light: #757575;
            --background: #f5f5f5;
            --white: #ffffff;
            --border-color: #ddd;
            --border-radius: 15px;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            max-width: 800px;
            width: 100%;
            margin: 0 auto;
        }
        
        .choice-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .choice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 2.5rem;
            text-align: center;
        }
        
        .card-header h1 {
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .card-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 3rem;
        }
        
        .question-section {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .question-icon {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .question-text {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 1rem;
        }
        
        .question-subtext {
            color: var(--text-light);
            font-size: 1rem;
            max-width: 500px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .choices-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .choice-option {
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: var(--white);
            position: relative;
        }
        
        .choice-option:hover {
            border-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .choice-option.selected {
            border-color: var(--primary-color);
            background: var(--secondary-color);
        }
        
        .choice-option.oui {
            border-color: var(--success-color);
        }
        
        .choice-option.oui.selected {
            background: #e8f5e9;
            border-color: var(--success-color);
        }
        
        .choice-option.non {
            border-color: var(--warning-color);
        }
        
        .choice-option.non.selected {
            background: #fff8e1;
            border-color: var(--warning-color);
        }
        
        .choice-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .choice-option.oui .choice-icon {
            color: var(--success-color);
        }
        
        .choice-option.non .choice-icon {
            color: var(--warning-color);
        }
        
        .choice-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .choice-description {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .hidden-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .submit-btn:disabled {
            background: var(--text-light);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
            opacity: 0.6;
        }
        
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
            border-left: 4px solid var(--accent-color);
        }
        
        .info-title {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-content {
            color: var(--text-light);
            font-size: 0.9rem;
            line-height: 1.5;
        }
        
        .info-content p {
            margin-bottom: 0.5rem;
        }
        
        .info-content p:last-child {
            margin-bottom: 0;
        }
        
        @media (max-width: 768px) {
            .choices-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .card-body {
                padding: 2rem;
            }
            
            .card-header {
                padding: 2rem;
            }
            
            .card-header h1 {
                font-size: 1.8rem;
            }
            
            .question-text {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .card-body {
                padding: 1.5rem;
            }
            
            .card-header {
                padding: 1.5rem;
            }
            
            .question-icon {
                font-size: 3rem;
            }
            
            .choice-option {
                padding: 1.5rem;
            }
        }

        /* Style pour les messages de débogage */
        .debug-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
            font-size: 0.8rem;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Messages de débogage -->
        <?php if (isset($_GET['debug'])): ?>
        <div class="debug-info">
            <strong>Debug Info:</strong><br>
            Session User ID: <?php echo $_SESSION['user_id'] ?? 'Non défini'; ?><br>
            Méthode: <?php echo $_SERVER['REQUEST_METHOD']; ?><br>
            Fichiers: 
            <?php 
            echo 'Visa: ' . (file_exists('demande_visa_travail.php') ? 'EXISTE' : 'MANQUANT');
            echo ' | Contrat: ' . (file_exists('demande_contrat_travail.php') ? 'EXISTE' : 'MANQUANT');
            ?>
        </div>
        <?php endif; ?>

        <div class="choice-card">
            <div class="card-header">
                <h1>Demande de Visa de Travail</h1>
                <p>Choisissez l'option qui correspond à votre situation</p>
            </div>
            
            <div class="card-body">
                <form method="POST" id="choiceForm">
                    <div class="question-section">
                        <div class="question-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h2 class="question-text">Avez-vous déjà un contrat de travail ?</h2>
                        <p class="question-subtext">
                            Votre réponse nous aidera à vous orienter vers la procédure appropriée pour votre demande de visa.
                        </p>
                    </div>
                    
                    <div class="choices-grid">
                        <label class="choice-option oui" for="choix_oui">
                            <input type="radio" name="choix_contrat" value="oui" id="choix_oui" class="hidden-radio" required>
                            <div class="choice-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="choice-title">Oui, j'ai un contrat</div>
                            <div class="choice-description">
                                Je possède déjà un contrat de travail signé avec un employeur. Je peux directement démarrer ma demande de visa de travail.
                            </div>
                        </label>
                        
                        <label class="choice-option non" for="choix_non">
                            <input type="radio" name="choix_contrat" value="non" id="choix_non" class="hidden-radio" required>
                            <div class="choice-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="choice-title">Non, je n'ai pas de contrat</div>
                            <div class="choice-description">
                                Je recherche un emploi ou je n'ai pas encore de contrat. Je souhaite obtenir un contrat de travail avant de faire ma demande de visa.
                            </div>
                        </label>
                    </div>
                    
                    <button type="submit" class="submit-btn" id="submitBtn" disabled>
                        <i class="fas fa-arrow-right"></i>Continuer
                    </button>
                    
                    <div class="info-section">
                        <div class="info-title">
                            <i class="fas fa-info-circle"></i>
                            Informations importantes
                        </div>
                        <div class="info-content">
                            <p><strong>Option "Oui" :</strong> Vous serez redirigé vers le formulaire de demande de visa de travail. Préparez vos documents (passeport, contrat, diplômes, etc.).</p>
                            <p><strong>Option "Non" :</strong> Vous accéderez à un service d'accompagnement pour trouver un emploi et obtenir un contrat de travail.</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== DÉMARRAGE DU SCRIPT ===');
            
            const choiceOptions = document.querySelectorAll('.choice-option');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('choiceForm');
            
            console.log('Éléments trouvés:', {
                options: choiceOptions.length,
                bouton: !!submitBtn,
                formulaire: !!form
            });

            // Vérifier que tous les éléments sont présents
            if (choiceOptions.length === 0 || !submitBtn || !form) {
                console.error('❌ Éléments manquants dans le DOM');
                alert('Erreur: Éléments de formulaire manquants');
                return;
            }

            console.log('✅ Tous les éléments sont présents');
            
            // Gérer la sélection des options
            choiceOptions.forEach(option => {
                const radio = option.querySelector('input[type="radio"]');
                
                if (!radio) {
                    console.error('❌ Radio introuvable dans l\'option');
                    return;
                }
                
                option.addEventListener('click', function() {
                    const type = this.classList.contains('oui') ? 'Oui' : 'Non';
                    console.log('🟢 Option cliquée:', type);
                    
                    // Désélectionner toutes les options
                    choiceOptions.forEach(opt => {
                        opt.classList.remove('selected');
                    });
                    
                    // Sélectionner l'option cliquée
                    this.classList.add('selected');
                    radio.checked = true;
                    
                    // Activer le bouton
                    submitBtn.disabled = false;
                    console.log('✅ Bouton activé');
                    
                    // Animation de confirmation
                    this.style.transform = 'scale(1.02)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                });
            });

            // Animation au chargement
            setTimeout(() => {
                const card = document.querySelector('.choice-card');
                if (card) {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                    console.log('🎬 Animation de chargement terminée');
                }
            }, 100);

            // Validation avant soumission
            form.addEventListener('submit', function(e) {
                console.log('🔄 Tentative de soumission du formulaire');
                
                const selectedOption = document.querySelector('input[name="choix_contrat"]:checked');
                
                if (!selectedOption) {
                    console.error('❌ Aucune option sélectionnée');
                    e.preventDefault();
                    alert('Veuillez sélectionner une option avant de continuer.');
                    return false;
                }

                console.log('✅ Formulaire validé - Option:', selectedOption.value);
                
                // Afficher un indicateur de chargement
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirection...';
                submitBtn.disabled = true;
                
                return true;
            });

            console.log('✅ Script initialisé avec succès');
        });

        // Gestion des erreurs globales
        window.addEventListener('error', function(e) {
            console.error('💥 Erreur JavaScript:', e.error);
        });
    </script>
</body>
</html>