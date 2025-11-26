<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$host = "localhost";
$dbname = "babylone_service";
$username = "root";
$password = "";

// Initialiser les variables
$success = $error = "";
$form_data = [];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Récupération et validation des données
        $user_id = $_SESSION['user_id'];
        $nom_complet = trim($_POST['nom_complet']);
        $email = trim($_POST['email']);
        $telephone = trim($_POST['telephone']);
        $adresse = trim($_POST['adresse']);
        $date_naissance = $_POST['date_naissance'];
        $nationalite = $_POST['nationalite'];
        $situation_familiale = $_POST['situation_familiale'];
        
        // Formation
        $formations = [];
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($_POST["formation_diplome_$i"])) {
                $formations[] = [
                    'diplome' => $_POST["formation_diplome_$i"],
                    'etablissement' => $_POST["formation_etablissement_$i"],
                    'annee_obtention' => $_POST["formation_annee_$i"],
                    'description' => $_POST["formation_description_$i"]
                ];
            }
        }
        
        // Expériences professionnelles
        $experiences = [];
        for ($i = 1; $i <= 3; $i++) {
            if (!empty($_POST["experience_poste_$i"])) {
                $experiences[] = [
                    'poste' => $_POST["experience_poste_$i"],
                    'entreprise' => $_POST["experience_entreprise_$i"],
                    'date_debut' => $_POST["experience_debut_$i"],
                    'date_fin' => $_POST["experience_fin_$i"],
                    'description' => $_POST["experience_description_$i"]
                ];
            }
        }
        
        // Compétences
        $competences_techniques = trim($_POST['competences_techniques'] ?? '');
        $competences_linguistiques = trim($_POST['competences_linguistiques'] ?? '');
        $competences_interpersonnelles = trim($_POST['competences_interpersonnelles'] ?? '');
        
        // Centres d'intérêt
        $centres_interet = trim($_POST['centres_interet'] ?? '');
        
        // Insertion dans la base
        $sql = "INSERT INTO demandes_creation_cv 
                (user_id, nom_complet, email, telephone, adresse, date_naissance, nationalite, 
                 situation_familiale, formations, experiences, competences_techniques, 
                 competences_linguistiques, competences_interpersonnelles, centres_interet, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_traitement')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $user_id, $nom_complet, $email, $telephone, $adresse, $date_naissance, $nationalite,
            $situation_familiale, json_encode($formations), json_encode($experiences),
            $competences_techniques, $competences_linguistiques, $competences_interpersonnelles,
            $centres_interet
        ]);

        $success = "Votre demande de création de CV a été soumise avec succès ! Notre équipe vous contactera dans les plus brefs délais.";
        $form_data = [];
    }
} catch (PDOException $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Création de CV - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Le même CSS que précédemment, adapté pour cette page */
        :root {
            --primary-color: #1a237e;
            --primary-light: #534bae;
            --primary-dark: #000051;
            --secondary-color: #e8eaf6;
            --accent-color: #ff6d00;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --error-color: #f44336;
            --text-color: #212121;
            --text-light: #757575;
            --background: #f5f5f5;
            --white: #ffffff;
            --border-color: #ddd;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            background: linear-gradient(135deg, #f5f6fcff 0%, #f7f2fcff 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .form-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 2rem;
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
        
        .message {
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-color);
        }
        
        .required::after {
            content: " *";
            color: var(--error-color);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
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
            margin-top: 1rem;
        }
        
        .submit-btn:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-dark));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: var(--primary-dark);
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-color);
            margin: 2rem 0 1rem 0;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--secondary-color);
            grid-column: 1 / -1;
        }
        
        .formation-item, .experience-item {
            background: var(--secondary-color);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .add-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="back-link">
            <i class="fas fa-arrow-left me-2"></i>Retour 
        </a>
        
        <div class="form-card">
            <div class="card-header">
                <h1><i class="fas fa-file-alt me-2"></i>Création de CV Professionnel</h1>
                <p>Remplissez ce formulaire pour que notre équipe puisse créer un CV adapté à votre profil</p>
            </div>
            
            <div class="card-body">
                <?php if ($success): ?>
                    <div class="message success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="message error">
                        <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="cvForm">
                    <div class="section-title">
                        <i class="fas fa-user me-2"></i>Informations Personnelles
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom_complet" class="required">Nom Complet</label>
                            <input type="text" id="nom_complet" name="nom_complet" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone" class="required">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <input type="text" id="adresse" name="adresse">
                        </div>
                        
                        <div class="form-group">
                            <label for="date_naissance">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance">
                        </div>
                        
                        <div class="form-group">
                            <label for="nationalite">Nationalité</label>
                            <input type="text" id="nationalite" name="nationalite">
                        </div>
                        
                        <div class="form-group">
                            <label for="situation_familiale">Situation familiale</label>
                            <select id="situation_familiale" name="situation_familiale">
                                <option value="">Sélectionnez...</option>
                                <option value="celibataire">Célibataire</option>
                                <option value="marie">Marié(e)</option>
                                <option value="divorce">Divorcé(e)</option>
                                <option value="veuf">Veuf/Veuve</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fas fa-graduation-cap me-2"></i>Formation
                    </div>
                    
                    <div id="formations-container">
                        <!-- Les formations seront ajoutées ici dynamiquement -->
                    </div>
                    <button type="button" class="add-btn" onclick="ajouterFormation()">
                        <i class="fas fa-plus me-2"></i>Ajouter une formation
                    </button>

                    <div class="section-title">
                        <i class="fas fa-briefcase me-2"></i>Expériences Professionnelles
                    </div>
                    
                    <div id="experiences-container">
                        <!-- Les expériences seront ajoutées ici dynamiquement -->
                    </div>
                    <button type="button" class="add-btn" onclick="ajouterExperience()">
                        <i class="fas fa-plus me-2"></i>Ajouter une expérience
                    </button>

                    <div class="section-title">
                        <i class="fas fa-star me-2"></i>Compétences
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="competences_techniques">Compétences techniques</label>
                            <textarea id="competences_techniques" name="competences_techniques" 
                                      placeholder="Langages de programmation, logiciels, outils..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="competences_linguistiques">Compétences linguistiques</label>
                            <textarea id="competences_linguistiques" name="competences_linguistiques" 
                                      placeholder="Langues parlées et niveaux..."></textarea>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="competences_interpersonnelles">Compétences interpersonnelles</label>
                            <textarea id="competences_interpersonnelles" name="competences_interpersonnelles" 
                                      placeholder="Leadership, travail d'équipe, communication..."></textarea>
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fas fa-heart me-2"></i>Centres d'Intérêt
                    </div>
                    
                    <div class="form-group">
                        <textarea id="centres_interet" name="centres_interet" 
                                  placeholder="Vos hobbies, activités extrascolaires..."></textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane me-2"></i>Soumettre ma demande de CV
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let formationCount = 0;
        let experienceCount = 0;

        function ajouterFormation() {
            formationCount++;
            const container = document.getElementById('formations-container');
            
            const formationHTML = `
                <div class="formation-item">
                    <h4>Formation ${formationCount}</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Diplôme</label>
                            <input type="text" name="formation_diplome_${formationCount}" required>
                        </div>
                        <div class="form-group">
                            <label>Établissement</label>
                            <input type="text" name="formation_etablissement_${formationCount}" required>
                        </div>
                        <div class="form-group">
                            <label>Année d'obtention</label>
                            <input type="number" name="formation_annee_${formationCount}" min="1950" max="2030">
                        </div>
                        <div class="form-group full-width">
                            <label>Description</label>
                            <textarea name="formation_description_${formationCount}"></textarea>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', formationHTML);
        }

        function ajouterExperience() {
            experienceCount++;
            const container = document.getElementById('experiences-container');
            
            const experienceHTML = `
                <div class="experience-item">
                    <h4>Expérience ${experienceCount}</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Poste occupé</label>
                            <input type="text" name="experience_poste_${experienceCount}" required>
                        </div>
                        <div class="form-group">
                            <label>Entreprise</label>
                            <input type="text" name="experience_entreprise_${experienceCount}" required>
                        </div>
                        <div class="form-group">
                            <label>Date de début</label>
                            <input type="date" name="experience_debut_${experienceCount}">
                        </div>
                        <div class="form-group">
                            <label>Date de fin</label>
                            <input type="date" name="experience_fin_${experienceCount}">
                        </div>
                        <div class="form-group full-width">
                            <label>Description des tâches</label>
                            <textarea name="experience_description_${experienceCount}"></textarea>
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', experienceHTML);
        }

        // Ajouter une formation et une expérience par défaut au chargement
        document.addEventListener('DOMContentLoaded', function() {
            ajouterFormation();
            ajouterExperience();
            
            const formCard = document.querySelector('.form-card');
            formCard.style.opacity = '0';
            formCard.style.transform = 'translateY(20px)';
            formCard.style.transition = 'all 0.5s ease';
            
            setTimeout(() => {
                formCard.style.opacity = '1';
                formCard.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>