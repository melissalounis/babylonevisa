<?php
// Méthode alternative pour trouver config.php
$configPath = __DIR__ . '/../../../config.php';
if (!file_exists($configPath)) {
    // Essayer un autre chemin
    $configPath = $_SERVER['DOCUMENT_ROOT'] . '/babylone/config.php';
}

if (!file_exists($configPath)) {
    die("Fichier config.php introuvable. Cherché à: " . $configPath);
}

require_once $configPath;
require_login();

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Démarrer une transaction
        $pdo->beginTransaction();

        // Vérifier et créer le dossier d'upload
        $uploadDir = UPLOAD_BASE . '/canada/' . $_SESSION['user_id'] . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Préparer les données pour la table demandes_canada
        $user_id = $_SESSION['user_id'];
        $nom = $_POST['nom_complet'] ?? '';
        $prenom = explode(' ', $nom)[0] ?? ''; // Extraire le prénom du nom complet
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $naissance = $_POST['date_naissance'] ?? '';
        $nationalite = $_POST['pays_origine'] ?? '';
        
        // Informations académiques
        $niveau_etude = $_POST['niveau_etude'] ?? '';
        $universite_canada = $_POST['universite_souhaitee'] ?? '';
        $programme_canada = $_POST['programme'] ?? '';
        
        // Si "autre" programme, utiliser la valeur du champ autre_programme
        if ($programme_canada === 'autre' && !empty($_POST['autre_programme'])) {
            $programme_canada = $_POST['autre_programme'];
        }

        // Validation des données requises
        $errors = [];
        if (empty($nom)) $errors[] = "Le nom complet est requis";
        if (empty($email)) $errors[] = "L'email est requis";
        if (empty($niveau_etude)) $errors[] = "Le niveau d'étude est requis";
        if (empty($programme_canada)) $errors[] = "Le domaine d'étude est requis";
        
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        // Insérer dans la table demandes_canada
        $stmt = $pdo->prepare("
            INSERT INTO demandes_canada 
            (user_id, nom, prenom, email, telephone, naissance, nationalite,
             niveau_etude, universite_canada, programme_canada, statut) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')
        ");
        
        $stmt->execute([
            $user_id,
            $nom,
            $prenom,
            $email,
            $telephone,
            $naissance,
            $nationalite,
            $niveau_etude,
            $universite_canada,
            $programme_canada
        ]);

        $demande_id = $pdo->lastInsertId();

        // Gestion des fichiers uploadés pour la table demandes_canada_fichiers
        $fileMappings = [
            'passeport' => 'passeport',
            'releves_notes' => 'releve_notes',
            'diplome' => 'diplome',
            'cv' => 'cv',
            'lettre_motivation' => 'lettre_motivation',
            'test_linguistique' => 'test_linguistique',
            'justificatifs_financiers' => 'preuve_finance'
        ];

        foreach ($fileMappings as $formField => $dbField) {
            if (isset($_FILES[$formField]) && $_FILES[$formField]['error'] === UPLOAD_ERR_OK) {
                // Vérifier la taille du fichier
                if ($_FILES[$formField]['size'] > MAX_FILE_SIZE) {
                    throw new Exception("Le fichier $formField est trop volumineux. Taille max: 5MB");
                }
                
                $fileName = uniqid() . '_' . basename($_FILES[$formField]['name']);
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($_FILES[$formField]['tmp_name'], $filePath)) {
                    // Enregistrer dans demandes_canada_fichiers
                    $stmt_file = $pdo->prepare("
                        INSERT INTO demandes_canada_fichiers 
                        (demande_id, type_fichier, nom_fichier, chemin_fichier, taille_fichier)
                        VALUES (?, ?, ?, ?, ?)
                    ");
                    
                    $stmt_file->execute([
                        $demande_id,
                        $dbField,
                        $_FILES[$formField]['name'],
                        $filePath,
                        $_FILES[$formField]['size']
                    ]);
                }
            }
        }

        // Valider la transaction
        $pdo->commit();

        $_SESSION['success'] = "Votre demande d'admission Canada a été soumise avec succès!";
        header("Location: mes_demandes_canada.php");
        exit;

    } catch (Exception $e) {
        // Annuler la transaction en cas d'erreur
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = "Erreur lors de l'enregistrement: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admission Canada - Babylone Service</title>
    <style>
        :root {
            --primary: #d32f2f;
            --secondary: #1976d2;
            --light: #f5f5f5;
            --dark: #333;
            --border: #ddd;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: var(--secondary);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .form-container {
            padding: 30px;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: bold;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-section {
            background: var(--light);
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            border-left: 4px solid var(--secondary);
        }
        
        .form-section h3 {
            color: var(--secondary);
            margin-bottom: 20px;
            font-size: 1.4em;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--secondary);
        }
        
        .file-input {
            padding: 10px;
            background: white;
            border: 2px dashed var(--border);
        }
        
        .required::after {
            content: " *";
            color: var(--primary);
        }
        
        .btn {
            background: var(--primary);
            color: white;
            padding: 15px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
            display: block;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn:hover {
            background: #b71c1c;
            transform: translateY(-2px);
        }
        
        .hidden {
            display: none;
        }
        
        .docs-requirements {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            font-size: 0.9em;
        }
        
        .file-info {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        
        .canada-badge {
            background: linear-gradient(135deg, #d32f2f, #1976d2);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            display: inline-block;
            margin-left: 10px;
        }
        
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 10px;
            }
            
            .header h1 {
                font-size: 2em;
            }
            
            .form-container {
                padding: 20px;
            }
        }
    </style>
    <script>
        function afficherDocuments() {
            const niveau = document.getElementById('niveau_etude').value;
            document.querySelectorAll('.docs-niveau').forEach(el => el.classList.add('hidden'));
            
            if (niveau) {
                document.getElementById('docs_' + niveau).classList.remove('hidden');
            }
        }
        
        function toggleAutreProgramme() {
            const programme = document.getElementById('programme').value;
            const autreContainer = document.getElementById('autre_programme_container');
            autreContainer.classList.toggle('hidden', programme !== 'autre');
            
            if (programme === 'autre') {
                document.getElementById('autre_programme').required = true;
            } else {
                document.getElementById('autre_programme').required = false;
            }
        }
        
        function validateFile(input) {
            const file = input.files[0];
            if (file) {
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    alert('Le fichier est trop volumineux. Taille maximum: 5MB');
                    input.value = '';
                }
            }
        }
        
        // Fonction pour séparer nom et prénom
        function separerNomPrenom() {
            const nomComplet = document.getElementById('nom_complet').value;
            const noms = nomComplet.split(' ');
            if (noms.length > 1) {
                // Le premier mot est le prénom, le reste le nom
                const prenom = noms[0];
                const nom = noms.slice(1).join(' ');
                console.log('Prénom:', prenom, 'Nom:', nom);
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Admission Canada <span class="canada-badge">Nouveau Système</span></h1>
            <p>Demande d'admission dans les universités et collèges canadiens</p>
            <p style="font-size: 0.9em; margin-top: 10px; opacity: 0.8;">
                ✅ Données enregistrées dans le nouveau système Canada
            </p>
        </div>
        
        <div class="form-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <!-- Informations personnelles -->
                <div class="form-section">
                    <h3>👤 Informations Personnelles</h3>
                    
                    <div class="form-group">
                        <label for="nom_complet" class="required">Nom complet</label>
                        <input type="text" id="nom_complet" name="nom_complet" required 
                               value="<?= htmlspecialchars($_POST['nom_complet'] ?? '') ?>"
                               onblur="separerNomPrenom()">
                        <small style="color: #666;">Format: Prénom suivis du nom de famille</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required">Adresse email</label>
                        <input type="email" id="email" name="email" required 
                               value="<?= htmlspecialchars($_POST['email'] ?? $_SESSION['user_email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone" class="required">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" required 
                               value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="date_naissance" class="required">Date de naissance</label>
                        <input type="date" id="date_naissance" name="date_naissance" required 
                               value="<?= htmlspecialchars($_POST['date_naissance'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="pays_origine" class="required">Pays d'origine</label>
                        <input type="text" id="pays_origine" name="pays_origine" required 
                               value="<?= htmlspecialchars($_POST['pays_origine'] ?? '') ?>">
                    </div>
                </div>
                
                <!-- Informations académiques -->
                <div class="form-section">
                    <h3>📚 Informations Académiques</h3>
                    
                    <div class="form-group">
                        <label for="niveau_etude" class="required">Niveau d'étude souhaité</label>
                        <select id="niveau_etude" name="niveau_etude" required onchange="afficherDocuments()">
                            <option value="">-- Sélectionner --</option>
                            <option value="bac" <?= ($_POST['niveau_etude'] ?? '') == 'bac' ? 'selected' : '' ?>>Baccalauréat</option>
                            <option value="l1" <?= ($_POST['niveau_etude'] ?? '') == 'l1' ? 'selected' : '' ?>>Licence 1</option>
                            <option value="l2" <?= ($_POST['niveau_etude'] ?? '') == 'l2' ? 'selected' : '' ?>>Licence 2</option>
                            <option value="l3" <?= ($_POST['niveau_etude'] ?? '') == 'l3' ? 'selected' : '' ?>>Licence 3</option>
                            <option value="master" <?= ($_POST['niveau_etude'] ?? '') == 'master' ? 'selected' : '' ?>>Master</option>
                            <option value="doctorat" <?= ($_POST['niveau_etude'] ?? '') == 'doctorat' ? 'selected' : '' ?>>Doctorat</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="universite_souhaitee">Université/College souhaité (optionnel)</label>
                        <input type="text" id="universite_souhaitee" name="universite_souhaitee" 
                               value="<?= htmlspecialchars($_POST['universite_souhaitee'] ?? '') ?>"
                               placeholder="Ex: University of Toronto, McGill University...">
                    </div>
                    
                    <div class="form-group">
                        <label for="programme" class="required">Domaine d'étude</label>
                        <select id="programme" name="programme" required onchange="toggleAutreProgramme()">
                            <option value="">-- Sélectionner --</option>
                            <option value="informatique" <?= ($_POST['programme'] ?? '') == 'informatique' ? 'selected' : '' ?>>Informatique</option>
                            <option value="gestion" <?= ($_POST['programme'] ?? '') == 'gestion' ? 'selected' : '' ?>>Gestion/Commerce</option>
                            <option value="ingenierie" <?= ($_POST['programme'] ?? '') == 'ingenierie' ? 'selected' : '' ?>>Ingénierie</option>
                            <option value="sante" <?= ($_POST['programme'] ?? '') == 'sante' ? 'selected' : '' ?>>Santé</option>
                            <option value="arts" <?= ($_POST['programme'] ?? '') == 'arts' ? 'selected' : '' ?>>Arts et Sciences</option>
                            <option value="autre" <?= ($_POST['programme'] ?? '') == 'autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>
                    
                    <div id="autre_programme_container" class="form-group hidden">
                        <label for="autre_programme">Précisez le domaine</label>
                        <input type="text" id="autre_programme" name="autre_programme" 
                               value="<?= htmlspecialchars($_POST['autre_programme'] ?? '') ?>">
                    </div>
                </div>
                
                <!-- Documents requis -->
                <div class="form-section">
                    <h3>📎 Documents à fournir</h3>
                    <p class="file-info">Formats acceptés: PDF, JPG, PNG (max. 5MB par fichier)</p>
                    
                    <div class="form-group">
                        <label for="passeport" class="required">Passeport (pages principales)</label>
                        <input type="file" id="passeport" name="passeport" class="file-input" 
                               accept=".pdf,.jpg,.png" required onchange="validateFile(this)">
                    </div>
                    
                    <!-- Documents selon le niveau -->
                    <div id="docs_bac" class="docs-niveau hidden">
                        <div class="docs-requirements">
                            <strong>Pour le Baccalauréat:</strong> Relevés de notes du lycée, diplôme du baccalauréat
                        </div>
                        <div class="form-group">
                            <label for="releves_notes">Relevés de notes lycée</label>
                            <input type="file" id="releves_notes" name="releves_notes" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                        <div class="form-group">
                            <label for="diplome">Diplôme du baccalauréat</label>
                            <input type="file" id="diplome" name="diplome" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                    </div>
                    
                    <div id="docs_l1" class="docs-niveau hidden">
                        <div class="docs-requirements">
                            <strong>Pour la Licence 1:</strong> Relevés de notes du lycée et diplôme du baccalauréat
                        </div>
                        <div class="form-group">
                            <label for="releves_notes">Relevés de notes</label>
                            <input type="file" id="releves_notes" name="releves_notes" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                        <div class="form-group">
                            <label for="diplome">Diplômes</label>
                            <input type="file" id="diplome" name="diplome" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                    </div>
                    
                    <div id="docs_l2" class="docs-niveau hidden">
                        <div class="docs-requirements">
                            <strong>Pour la Licence 2:</strong> Relevés de notes L1 et diplômes antérieurs
                        </div>
                        <div class="form-group">
                            <label for="releves_notes">Relevés de notes L1</label>
                            <input type="file" id="releves_notes" name="releves_notes" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                        <div class="form-group">
                            <label for="diplome">Diplômes</label>
                            <input type="file" id="diplome" name="diplome" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                    </div>
                    
                    <div id="docs_l3" class="docs-niveau hidden">
                        <div class="docs-requirements">
                            <strong>Pour la Licence 3:</strong> Relevés de notes L2 et diplômes antérieurs
                        </div>
                        <div class="form-group">
                            <label for="releves_notes">Relevés de notes L2</label>
                            <input type="file" id="releves_notes" name="releves_notes" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                        <div class="form-group">
                            <label for="diplome">Diplômes</label>
                            <input type="file" id="diplome" name="diplome" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                    </div>
                    
                    <div id="docs_master" class="docs-niveau hidden">
                        <div class="docs-requirements">
                            <strong>Pour le Master:</strong> Relevés de notes de licence et diplôme de licence
                        </div>
                        <div class="form-group">
                            <label for="releves_notes">Relevés de notes licence</label>
                            <input type="file" id="releves_notes" name="releves_notes" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                        <div class="form-group">
                            <label for="diplome">Diplôme de licence</label>
                            <input type="file" id="diplome" name="diplome" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                    </div>
                    
                    <div id="docs_doctorat" class="docs-niveau hidden">
                        <div class="docs-requirements">
                            <strong>Pour le Doctorat:</strong> Relevés de notes de master et diplôme de master
                        </div>
                        <div class="form-group">
                            <label for="releves_notes">Relevés de notes master</label>
                            <input type="file" id="releves_notes" name="releves_notes" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                        <div class="form-group">
                            <label for="diplome">Diplôme de master</label>
                            <input type="file" id="diplome" name="diplome" class="file-input" 
                                   accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                        </div>
                    </div>
                    
                    <!-- Documents communs -->
                    <div class="form-group">
                        <label for="cv">Curriculum Vitae (PDF)</label>
                        <input type="file" id="cv" name="cv" class="file-input" accept=".pdf" onchange="validateFile(this)">
                    </div>
                    
                    <div class="form-group">
                        <label for="lettre_motivation">Lettre de motivation (PDF)</label>
                        <input type="file" id="lettre_motivation" name="lettre_motivation" class="file-input" 
                               accept=".pdf" onchange="validateFile(this)">
                    </div>
                    
                    <div class="form-group">
                        <label for="test_linguistique">Test linguistique (IELTS/TOEFL) - Optionnel</label>
                        <input type="file" id="test_linguistique" name="test_linguistique" class="file-input" 
                               accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                    </div>
                    
                    <div class="form-group">
                        <label for="justificatifs_financiers">Justificatifs financiers</label>
                        <input type="file" id="justificatifs_financiers" name="justificatifs_financiers" class="file-input" 
                               accept=".pdf,.jpg,.png" onchange="validateFile(this)">
                    </div>
                </div>
                
                <button type="submit" class="btn">📨 Soumettre la demande d'admission Canada</button>
            </form>
        </div>
    </div>
    
    <script>
        // Afficher les documents appropriés au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            afficherDocuments();
            toggleAutreProgramme();
        });
    </script>
</body>
</html>