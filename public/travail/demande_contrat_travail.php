<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location:login.php');
    exit();
}

require_once __DIR__ . '../../../config.php';

// Initialiser les variables
$success = $error = "";
$form_data = [];

try {
    

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // Récupération et validation des données
        $user_id = $_SESSION['user_id'];
        $nom_complet = trim($_POST['nom_complet']);
        $email = trim($_POST['email']);
        $telephone = trim($_POST['telephone']);
        $domaine_competence = $_POST['domaine_competence'];
        $niveau_etude = $_POST['niveau_etude'];
        $experience = $_POST['experience'];
        $pays_recherche = $_POST['pays_recherche'];
        $type_contrat = $_POST['type_contrat'];
        $competences = trim($_POST['competences'] ?? '');
        $langues = trim($_POST['langues'] ?? '');
        $a_cv = $_POST['a_cv'] ?? 'non';

        // Sauvegarder les données pour réaffichage
        $form_data = compact('nom_complet', 'email', 'telephone', 'domaine_competence', 
                           'niveau_etude', 'experience', 'pays_recherche', 'type_contrat', 
                           'competences', 'langues', 'a_cv');

        // Validation des fichiers
        $cv = $lettre_motivation = null;
        $upload_errors = [];

        // Gestion du CV
        if ($a_cv === 'oui') {
            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
                $cv_name = $_FILES['cv']['name'];
                $cv_tmp = $_FILES['cv']['tmp_name'];
                $cv_size = $_FILES['cv']['size'];
                $cv_ext = strtolower(pathinfo($cv_name, PATHINFO_EXTENSION));
                
                $allowed_ext = ['pdf', 'doc', 'docx'];
                $max_size = 5 * 1024 * 1024; // 5MB
                
                if (!in_array($cv_ext, $allowed_ext)) {
                    $upload_errors[] = "Le CV doit être au format PDF, DOC ou DOCX";
                } elseif ($cv_size > $max_size) {
                    $upload_errors[] = "Le CV ne doit pas dépasser 5MB";
                } else {
                    $cv = uniqid() . '_' . $cv_name;
                }
            } else {
                $upload_errors[] = "Veuillez télécharger votre CV";
            }
        }

        if (isset($_FILES['lettre_motivation']) && $_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) {
            $lettre_name = $_FILES['lettre_motivation']['name'];
            $lettre_tmp = $_FILES['lettre_motivation']['tmp_name'];
            $lettre_size = $_FILES['lettre_motivation']['size'];
            $lettre_ext = strtolower(pathinfo($lettre_name, PATHINFO_EXTENSION));
            
            if (!in_array($lettre_ext, $allowed_ext)) {
                $upload_errors[] = "La lettre de motivation doit être au format PDF, DOC ou DOCX";
            } elseif ($lettre_size > $max_size) {
                $upload_errors[] = "La lettre de motivation ne doit pas dépasser 5MB";
            } else {
                $lettre_motivation = uniqid() . '_' . $lettre_name;
            }
        }

        if (empty($upload_errors)) {
            // Déplacement des fichiers
            $upload_dir = "uploads/contrats/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            if ($cv) move_uploaded_file($cv_tmp, $upload_dir . $cv);
            if ($lettre_motivation) move_uploaded_file($lettre_tmp, $upload_dir . $lettre_motivation);

            // Insertion dans la base
            $sql = "INSERT INTO demandes_contrat_travail 
                    (user_id, nom_complet, email, telephone, domaine_competence, niveau_etude, 
                     experience, pays_recherche, type_contrat, competences, 
                     langues, a_cv, cv, lettre_motivation, statut) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $user_id, $nom_complet, $email, $telephone, $domaine_competence, $niveau_etude,
                $experience, $pays_recherche, $type_contrat, $competences,
                $langues, $a_cv, $cv, $lettre_motivation
            ]);

            $success = "Votre demande de contrat a été soumise avec succès !";
            $form_data = []; // Vider les données du formulaire
        } else {
            $error = implode("<br>", $upload_errors);
        }
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
    <title>Demande de Contrat de Travail - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --primary-light: #534bae;
            --primary-dark: #000051;
            --secondary-color: #e8eaf6;
            --accent-color: #0c1e83ff;
            --success-color: #4caf50;
            --warning-color: #170663ff;
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
            background: white;
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
            background: linear-gradient(135deg, var(--warning-color), #0039f5ff);
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
        
        .progress-bar {
            background: rgba(255, 255, 255, 0.3);
            height: 6px;
            border-radius: 3px;
            margin-top: 1.5rem;
            overflow: hidden;
        }
        
        .progress {
            background: var(--white);
            height: 100%;
            width: 100%;
            border-radius: 3px;
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
        
        input[type="file"] {
            padding: 10px;
            background-color: var(--secondary-color);
            border: 2px dashed var(--primary-light);
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .file-hint {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.3rem;
        }
        
        .submit-btn {
            background: linear-gradient(135deg, var(--warning-color), #4a62ecff);
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
            background: linear-gradient(135deg, #f57c00, #e65100);
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
        
        .competences-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .tag-hint {
            font-size: 0.85rem;
            color: var(--text-light);
            font-style: italic;
        }
        
        .cv-option {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .cv-option label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .cv-option input[type="radio"] {
            width: auto;
        }
        
        .cv-option label:hover {
            border-color: var(--primary-color);
        }
        
        .cv-option input[type="radio"]:checked + label {
            background-color: var(--secondary-color);
            border-color: var(--primary-color);
        }
        
        .demande-cv-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1rem;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .demande-cv-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .hidden {
            display: none;
        }
        
        .demande-cv-info {
            background-color: var(--secondary-color);
            padding: 1rem;
            border-radius: 8px;
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
            
            .cv-option {
                flex-direction: column;
                gap: 0.5rem;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="choix_contrat.php" class="back-link">
            <i class="fas fa-arrow-left me-2"></i>Retour au choix
        </a>
        
        <div class="form-card">
            <div class="card-header">
                <h1><i class="fas fa-search me-2"></i>Demande de Contrat de Travail</h1>
                <p>Remplissez ce formulaire pour nous aider à vous trouver un emploi adapté</p>
                <div class="progress-bar">
                    <div class="progress"></div>
                </div>
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
                
                <form method="POST" enctype="multipart/form-data" id="contratForm">
                    <div class="section-title">
                        <i class="fas fa-user me-2"></i>Informations Personnelles
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom_complet" class="required">Nom Complet</label>
                            <input type="text" id="nom_complet" name="nom_complet" 
                                   value="<?php echo htmlspecialchars($form_data['nom_complet'] ?? ''); ?>" 
                                   required placeholder="Votre nom complet">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" 
                                   required placeholder="votre@email.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone" class="required">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" 
                                   value="<?php echo htmlspecialchars($form_data['telephone'] ?? ''); ?>" 
                                   required placeholder="+33 1 23 45 67 89">
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fas fa-briefcase me-2"></i>Profil Professionnel
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="domaine_competence" class="required">Domaine de compétence</label>
                            <select id="domaine_competence" name="domaine_competence" required>
                                <option value="">Sélectionnez...</option>
                                <option value="informatique" <?php echo ($form_data['domaine_competence'] ?? '') === 'informatique' ? 'selected' : ''; ?>>Informatique & Tech</option>
                                <option value="sante" <?php echo ($form_data['domaine_competence'] ?? '') === 'sante' ? 'selected' : ''; ?>>Santé & Médical</option>
                                <option value="construction" <?php echo ($form_data['domaine_competence'] ?? '') === 'construction' ? 'selected' : ''; ?>>Construction & BTP</option>
                                <option value="commerce" <?php echo ($form_data['domaine_competence'] ?? '') === 'commerce' ? 'selected' : ''; ?>>Commerce & Vente</option>
                                <option value="enseignement" <?php echo ($form_data['domaine_competence'] ?? '') === 'enseignement' ? 'selected' : ''; ?>>Enseignement & Éducation</option>
                                <option value="hotellerie" <?php echo ($form_data['domaine_competence'] ?? '') === 'hotellerie' ? 'selected' : ''; ?>>Hôtellerie & Restauration</option>
                                <option value="autre" <?php echo ($form_data['domaine_competence'] ?? '') === 'autre' ? 'selected' : ''; ?>>Autre domaine</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="niveau_etude" class="required">Niveau d'étude</label>
                            <select id="niveau_etude" name="niveau_etude" required>
                                <option value="">Sélectionnez...</option>
                                <option value="sans_diplome" <?php echo ($form_data['niveau_etude'] ?? '') === 'sans_diplome' ? 'selected' : ''; ?>>Sans diplôme</option>
                                <option value="bac" <?php echo ($form_data['niveau_etude'] ?? '') === 'bac' ? 'selected' : ''; ?>>Bac</option>
                                <option value="bac+2" <?php echo ($form_data['niveau_etude'] ?? '') === 'bac+2' ? 'selected' : ''; ?>>Bac+2 (BTS, DUT)</option>
                                <option value="bac+3" <?php echo ($form_data['niveau_etude'] ?? '') === 'bac+3' ? 'selected' : ''; ?>>Bac+3 (Licence)</option>
                                <option value="bac+5" <?php echo ($form_data['niveau_etude'] ?? '') === 'bac+5' ? 'selected' : ''; ?>>Bac+5 (Master)</option>
                                <option value="doctorat" <?php echo ($form_data['niveau_etude'] ?? '') === 'doctorat' ? 'selected' : ''; ?>>Doctorat</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="experience" class="required">Années d'expérience</label>
                            <input type="number" id="experience" name="experience" 
                                   value="<?php echo htmlspecialchars($form_data['experience'] ?? ''); ?>" 
                                   min="0" max="50" required placeholder="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="pays_recherche" class="required">Pays recherché</label>
                            <select id="pays_recherche" name="pays_recherche" required>
                                <option value="">Sélectionnez...</option>
                                <option value="france" <?php echo ($form_data['pays_recherche'] ?? '') === 'france' ? 'selected' : ''; ?>>France</option>
                                <option value="canada" <?php echo ($form_data['pays_recherche'] ?? '') === 'canada' ? 'selected' : ''; ?>>Canada</option>
                                <option value="belgique" <?php echo ($form_data['pays_recherche'] ?? '') === 'belgique' ? 'selected' : ''; ?>>Belgique</option>
                                <option value="suisse" <?php echo ($form_data['pays_recherche'] ?? '') === 'suisse' ? 'selected' : ''; ?>>Suisse</option>
                                <option value="allemagne" <?php echo ($form_data['pays_recherche'] ?? '') === 'allemagne' ? 'selected' : ''; ?>>Allemagne</option>
                                <option value="royaume_uni" <?php echo ($form_data['pays_recherche'] ?? '') === 'royaume_uni' ? 'selected' : ''; ?>>Royaume-Uni</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fas fa-file-contract me-2"></i>Préférences d'Emploi
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="type_contrat" class="required">Type de contrat souhaité</label>
                            <select id="type_contrat" name="type_contrat" required>
                                <option value="">Sélectionnez...</option>
                                <option value="cdi" <?php echo ($form_data['type_contrat'] ?? '') === 'cdi' ? 'selected' : ''; ?>>CDI</option>
                                <option value="cdd" <?php echo ($form_data['type_contrat'] ?? '') === 'cdd' ? 'selected' : ''; ?>>CDD</option>
                                <option value="stage" <?php echo ($form_data['type_contrat'] ?? '') === 'stage' ? 'selected' : ''; ?>>Stage</option>
                                <option value="interim" <?php echo ($form_data['type_contrat'] ?? '') === 'interim' ? 'selected' : ''; ?>>Intérim</option>
                                <option value="freelance" <?php echo ($form_data['type_contrat'] ?? '') === 'freelance' ? 'selected' : ''; ?>>Freelance</option>
                            </select>
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fas fa-star me-2"></i>Compétences & Langues
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="competences">Compétences spécifiques</label>
                            <textarea id="competences" name="competences" 
                                      placeholder="Décrivez vos compétences techniques, logiciels maîtrisés, certifications..."><?php echo htmlspecialchars($form_data['competences'] ?? ''); ?></textarea>
                            <div class="tag-hint">Séparez les compétences par des virgules</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="langues">Langues parlées</label>
                            <textarea id="langues" name="langues" 
                                      placeholder="Français (courant), Anglais (intermédiaire), Espagnol (débutant)..."><?php echo htmlspecialchars($form_data['langues'] ?? ''); ?></textarea>
                            <div class="tag-hint">Indiquez la langue et le niveau</div>
                        </div>
                    </div>

                    <div class="section-title">
                        <i class="fas fa-file-upload me-2"></i>Documents à Joindre
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label class="required">Avez-vous un CV ?</label>
                            <div class="cv-option">
                                <input type="radio" id="a_cv_oui" name="a_cv" value="oui" 
                                       <?php echo ($form_data['a_cv'] ?? '') === 'oui' ? 'checked' : ''; ?> required>
                                <label for="a_cv_oui">Oui, j'ai un CV</label>
                                
                                <input type="radio" id="a_cv_non" name="a_cv" value="non" 
                                       <?php echo ($form_data['a_cv'] ?? '') === 'non' ? 'checked' : ''; ?>>
                                <label for="a_cv_non">Non, je n'ai pas de CV</label>
                            </div>
                        </div>
                        
                        <div class="form-group" id="cv-upload-group">
                            <label for="cv" class="required">CV (PDF, DOC, DOCX)</label>
                            <input type="file" id="cv" name="cv" accept=".pdf,.doc,.docx">
                            <div class="file-hint">Taille maximale : 5MB - Formats acceptés : PDF, DOC, DOCX</div>
                        </div>
                        
                        <div class="form-group full-width" id="demande-cv-group" style="display: none;">
                            <div class="demande-cv-info">
                                <p><strong>Pas de CV ? Aucun problème !</strong></p>
                                <p>Notre équipe peut vous aider à créer un CV professionnel. Cliquez sur le bouton ci-dessous pour accéder au formulaire de création de CV.</p>
                            </div>
                            <a href="demande_cv.php" class="demande-cv-btn">
                                <i class="fas fa-file-alt me-2"></i>Créer mon CV professionnel
                            </a>
                            <p class="file-hint" style="margin-top: 0.5rem;">
                                Après avoir créé votre CV, revenez sur cette page pour finaliser votre demande de contrat.
                            </p>
                        </div>
                        
                        <div class="form-group">
                            <label for="lettre_motivation">Lettre de motivation (PDF, DOC, DOCX)</label>
                            <input type="file" id="lettre_motivation" name="lettre_motivation" accept=".pdf,.doc,.docx">
                            <div class="file-hint">Taille maximale : 5MB - Formats acceptés : PDF, DOC, DOCX</div>
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fas fa-paper-plane me-2"></i>Soumettre ma demande
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Gestion de l'affichage des options CV
        document.addEventListener('DOMContentLoaded', function() {
            const cvOui = document.getElementById('a_cv_oui');
            const cvNon = document.getElementById('a_cv_non');
            const cvUploadGroup = document.getElementById('cv-upload-group');
            const demandeCvGroup = document.getElementById('demande-cv-group');
            const cvInput = document.getElementById('cv');
            
            function toggleCvOptions() {
                if (cvOui.checked) {
                    cvUploadGroup.style.display = 'block';
                    demandeCvGroup.style.display = 'none';
                    cvInput.required = true;
                } else if (cvNon.checked) {
                    cvUploadGroup.style.display = 'none';
                    demandeCvGroup.style.display = 'block';
                    cvInput.required = false;
                }
            }
            
            // Initialiser l'état
            toggleCvOptions();
            
            // Écouter les changements
            cvOui.addEventListener('change', toggleCvOptions);
            cvNon.addEventListener('change', toggleCvOptions);
            
            // Validation en temps réel
            document.getElementById('contratForm').addEventListener('submit', function(e) {
                const experience = document.getElementById('experience').value;
                
                if (experience < 0) {
                    e.preventDefault();
                    alert('Le nombre d\'années d\'expérience ne peut pas être négatif');
                    return;
                }
                
                // Validation du CV si "Oui" est sélectionné
                if (cvOui.checked && (!cvInput.files || cvInput.files.length === 0)) {
                    e.preventDefault();
                    alert('Veuillez télécharger votre CV');
                    return;
                }
            });

            // Animation de chargement
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