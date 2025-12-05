<?php
session_start();

// Connexion BDD
require_once __DIR__ . '/../../../config.php';
try {
   

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validation des champs obligatoires
        $required_fields = [
            'nom', 'email', 'telephone', 'nationalite', 'niveau'
        ];

        $errors = [];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = "Le champ $field est obligatoire.";
            }
        }

        // Vérification des documents obligatoires selon le niveau
        $niveau = $_POST['niveau'] ?? '';
        $docs_requis = getDocumentsRequises($niveau);
        
        foreach ($docs_requis as $doc) {
            if (empty($_FILES[$doc]['name'])) {
                $errors[] = "Le document $doc est obligatoire.";
            }
        }

        // Vérification du passeport et autres documents communs
        $documents_communs = ['passeport', 'photo'];
        foreach ($documents_communs as $doc) {
            if (empty($_FILES[$doc]['name'])) {
                $errors[] = "Le document $doc est obligatoire.";
            }
        }

        if (!empty($errors)) {
            echo "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> " . implode("<br>", $errors) . "</div>";
        } else {
            // Récupération des données
            $nom = $_POST['nom'] ?? '';
            $email = $_POST['email'] ?? '';
            $telephone = $_POST['telephone'] ?? '';
            $nationalite = $_POST['nationalite'] ?? '';
            $niveau = $_POST['niveau'] ?? '';
            
            $user_id = $_SESSION['user_id'] ?? 0;

            // Insertion dans la base de données
            $stmt = $pdo->prepare("INSERT INTO demandes_luxembourg 
                (user_id, nom, email, telephone, nationalite, niveau, 
                 statut, date_creation) 
                VALUES (?, ?, ?, ?, ?, ?, 'en_attente', NOW())");
            
            $stmt->execute([
                $user_id, $nom, $email, $telephone, $nationalite, $niveau
            ]);

            $demande_id = $pdo->lastInsertId();

            // Dossier uploads
            $uploadDir = __DIR__ . "/../../../uploads/luxembourg/";
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Fonction pour sauvegarder les fichiers
            function saveFile($file, $type, $demande_id, $pdo, $uploadDir) {
                if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
                    // Validation du type de fichier
                    $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                    $file_type = $file['type'];
                    
                    if (!in_array($file_type, $allowed_types)) {
                        return false;
                    }
                    
                    // Validation de la taille (5MB max)
                    if ($file['size'] > 5 * 1024 * 1024) {
                        return false;
                    }
                    
                    $filename = uniqid() . "_" . basename($file['name']);
                    $filepath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($file['tmp_name'], $filepath)) {
                        $stmt = $pdo->prepare("INSERT INTO demandes_luxembourg_fichiers 
                            (demande_id, type_fichier, chemin_fichier, date_upload) 
                            VALUES (?, ?, ?, NOW())");
                        $stmt->execute([$demande_id, $type, $filename]);
                        return true;
                    }
                }
                return false;
            }

            // Sauvegarde des tests de langue s'ils sont fournis
            if (!empty($_FILES['test_francais']['name'])) {
                saveFile($_FILES['test_francais'], 'test_francais', $demande_id, $pdo, $uploadDir);
            }
            if (!empty($_FILES['test_anglais']['name'])) {
                saveFile($_FILES['test_anglais'], 'test_anglais', $demande_id, $pdo, $uploadDir);
            }
            if (!empty($_FILES['test_allemand']['name'])) {
                saveFile($_FILES['test_allemand'], 'test_allemand', $demande_id, $pdo, $uploadDir);
            }

            // Sauvegarde des documents obligatoires
            saveFile($_FILES['passeport'], 'passeport', $demande_id, $pdo, $uploadDir);
            saveFile($_FILES['photo'], 'photo', $demande_id, $pdo, $uploadDir);

            // Sauvegarde du certificat de scolarité s'il est fourni
            if (!empty($_FILES['certificat_scolarite']['name'])) {
                saveFile($_FILES['certificat_scolarite'], 'certificat_scolarite', $demande_id, $pdo, $uploadDir);
            }

            // Sauvegarde des fichiers selon le niveau
            foreach ($docs_requis as $doc_type) {
                if (isset($_FILES[$doc_type]) && !empty($_FILES[$doc_type]['name'])) {
                    saveFile($_FILES[$doc_type], $doc_type, $demande_id, $pdo, $uploadDir);
                }
            }

            // Sauvegarde des documents supplémentaires
            if (isset($_FILES['documents_supplementaires'])) {
                foreach ($_FILES['documents_supplementaires']['name'] as $key => $name) {
                    if (!empty($name)) {
                        $file = [
                            'name' => $name,
                            'type' => $_FILES['documents_supplementaires']['type'][$key],
                            'tmp_name' => $_FILES['documents_supplementaires']['tmp_name'][$key],
                            'error' => $_FILES['documents_supplementaires']['error'][$key],
                            'size' => $_FILES['documents_supplementaires']['size'][$key]
                        ];
                        $type_doc = $_POST['types_documents_supplementaires'][$key] ?? 'document_supplementaire';
                        saveFile($file, $type_doc, $demande_id, $pdo, $uploadDir);
                    }
                }
            }

            // Redirection vers la confirmation
            header("Location: confirmation_luxembourg.php?id=" . $demande_id);
            exit;
        }
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Fonction pour déterminer les documents requis selon le niveau
function getDocumentsRequises($niveau) {
    switch($niveau) {
        case 'bachelor':
            return ['releves_lycee', 'releve_bac', 'diplome_bac'];
        case 'master':
            return ['diplome_bachelor', 'releves_bachelor'];
        case 'doctorat':
            return ['diplome_master', 'releves_master', 'projet_recherche'];
        default:
            return [];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Études au Luxembourg - Admission</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --luxembourg-blue: #00A2E0;
            --luxembourg-light-blue: #E8F4FF;
            --luxembourg-dark-blue: #0088C7;
            --luxembourg-red: #ED2939;
            --luxembourg-white: #FFFFFF;
            --luxembourg-light-gray: #F8F9FA;
            --luxembourg-gray: #6C757D;
            --luxembourg-dark: #2C3E50;
            --luxembourg-green: #28A745;
            
            --gradient-luxembourg: linear-gradient(135deg, var(--luxembourg-blue) 0%, var(--luxembourg-dark-blue) 100%);
            --gradient-light: linear-gradient(135deg, var(--luxembourg-light-blue) 0%, #F0F8FF 100%);
            
            --border-radius: 12px;
            --box-shadow: 0 8px 30px rgba(0, 162, 224, 0.15);
            --box-shadow-hover: 0 15px 40px rgba(0, 162, 224, 0.25);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f4f6fcff 0%, #fdfdfdff 100%);
            padding: 40px 20px;
            min-height: 100vh;
            line-height: 1.6;
            color: var(--luxembourg-dark);
        }
        
        .container {
            background: var(--luxembourg-white);
            padding: 50px;
            border-radius: var(--border-radius);
            max-width: 1000px;
            margin: 0 auto;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-luxembourg);
        }
        
        h1 {
            text-align: center;
            color: var(--luxembourg-dark-blue);
            margin-bottom: 40px;
            font-size: 2.8rem;
            position: relative;
            padding-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            font-weight: 700;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 150px;
            height: 4px;
            background: var(--gradient-luxembourg);
            border-radius: 2px;
        }
        
        h2 {
            color: var(--luxembourg-dark-blue);
            margin: 35px 0 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid var(--luxembourg-light-blue);
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.8rem;
            font-weight: 600;
        }
        
        h3 {
            color: var(--luxembourg-dark-blue);
            margin: 25px 0 20px;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }
        
        h4 {
            color: var(--luxembourg-blue);
            margin: 20px 0 15px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        label {
            font-weight: 600;
            display: block;
            margin-top: 25px;
            color: var(--luxembourg-dark);
            position: relative;
            font-size: 1.1rem;
        }
        
        .required::after {
            content: ' *';
            color: var(--luxembourg-red);
            font-weight: bold;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 16px 20px;
            margin-top: 12px;
            border: 2px solid #E1E8ED;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            background: var(--luxembourg-light-gray);
            font-family: inherit;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--luxembourg-blue);
            box-shadow: 0 0 0 4px rgba(0, 162, 224, 0.1);
            background: var(--luxembourg-white);
            transform: translateY(-2px);
        }
        
        .hidden {
            display: none;
        }
        
        button {
            margin-top: 40px;
            padding: 18px 40px;
            background: var(--gradient-luxembourg);
            color: var(--luxembourg-white);
            border: none;
            cursor: pointer;
            border-radius: var(--border-radius);
            font-size: 18px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            box-shadow: 0 6px 15px rgba(0, 162, 224, 0.3);
        }
        
        button:hover {
            transform: translateY(-3px);
            box-shadow: var(--box-shadow-hover);
        }
        
        .sub-section {
            padding: 30px;
            border: 2px solid #E1E8ED;
            border-radius: var(--border-radius);
            margin-top: 30px;
            background: var(--luxembourg-light-gray);
            transition: var(--transition);
        }
        
        .sub-section:hover {
            border-color: var(--luxembourg-blue);
            background: var(--luxembourg-light-blue);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }
        
        .langue-section {
            background: var(--gradient-light);
            border-left: 4px solid var(--luxembourg-blue);
        }
        
        .test-section {
            background: var(--luxembourg-light-blue);
            border-radius: var(--border-radius);
            padding: 25px;
            margin: 20px 0;
            border-left: 4px solid var(--luxembourg-blue);
        }
        
        .test-option {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 15px;
            margin: 15px 0;
            padding: 15px;
            background: var(--luxembourg-white);
            border-radius: var(--border-radius);
            border: 2px solid #E1E8ED;
            align-items: center;
        }
        
        .test-option input[type="radio"] {
            width: auto;
            margin-top: 0;
        }
        
        .test-file {
            display: none;
        }
        
        .test-file.active {
            display: block;
        }
        
        .btn-test {
            padding: 10px 20px;
            background: var(--luxembourg-blue);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        
        .btn-test:hover {
            background: var(--luxembourg-dark-blue);
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 20px;
            background: #FDEDED;
            color: #5C2B29;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border-left: 4px solid #F5C6CB;
            animation: fadeIn 0.5s ease;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
                width: 95%;
            }
            
            h1 {
                font-size: 2.2rem;
            }
            
            input, select, button, textarea {
                padding: 14px;
            }
            
            .sub-section {
                padding: 20px;
            }
            
            .test-option {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }
        
        input[type="file"] {
            padding: 14px;
            background: #F9F9F9;
            border: 2px dashed #D1D9E6;
            cursor: pointer;
            transition: var(--transition);
        }
        
        input[type="file"]:hover {
            border-color: var(--luxembourg-blue);
            background: var(--luxembourg-light-blue);
        }
        
        .niveau-section {
            margin-top: 25px;
            animation: fadeIn 0.5s ease;
        }
        
        select {
            background: url('data:image/svg+xml;utf8,<svg fill="%2300A2E0" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 20px center;
            background-size: 20px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 50px;
            cursor: pointer;
        }
        
        form {
            margin-top: 30px;
            animation: fadeIn 0.6s ease;
        }
        
        .form-section {
            margin: 30px 0;
            padding-bottom: 25px;
            border-bottom: 2px solid #EEF2F7;
        }
        
        .file-requirements {
            font-size: 0.9rem;
            color: var(--luxembourg-gray);
            margin-top: 8px;
        }
        
        .file-name {
            margin-top: 8px;
            font-size: 0.95rem;
            color: var(--luxembourg-green);
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
        }
        
        @media (max-width: 768px) {
            .grid-2, .grid-3 {
                grid-template-columns: 1fr;
            }
        }
        
        .langue-info {
            background: var(--luxembourg-light-blue);
            padding: 20px;
            border-radius: var(--border-radius);
            margin: 20px 0;
            border-left: 4px solid var(--luxembourg-blue);
        }
        
        .documents-supplementaires {
            margin-top: 30px;
        }
        
        .document-supplementaire {
            display: grid;
            grid-template-columns: 1fr 2fr auto;
            gap: 15px;
            margin-bottom: 15px;
            align-items: end;
        }
        
        .btn-add-doc {
            background: var(--luxembourg-green);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
        }
        
        .btn-add-doc:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-remove {
            background: var(--luxembourg-red);
            color: white;
            border: none;
            padding: 12px;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-remove:hover {
            background: #C82333;
        }
        
        .optional {
            color: var(--luxembourg-gray);
            font-weight: normal;
        }
    </style>
    <script>
        function toggleNiveau() {
            let niveau = document.getElementById("niveau").value;
            document.querySelectorAll(".niveau-section").forEach(div => div.classList.add("hidden"));
            if (niveau) document.getElementById("niv-" + niveau).classList.remove("hidden");
        }
        
        function validateFile(input) {
            const file = input.files[0];
            if (file) {
                // Vérification de la taille (5MB max)
                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    alert('Le fichier "' + file.name + '" est trop volumineux. Taille maximale: 5MB');
                    input.value = '';
                    return false;
                }
                
                // Vérification du type de fichier
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Type de fichier non autorisé. Formats acceptés: PDF, JPG, PNG');
                    input.value = '';
                    return false;
                }
                
                return true;
            }
            return false;
        }
        
        function toggleTestFile(langue) {
            const radioOui = document.getElementById(`test_${langue}_oui`);
            const fileInput = document.getElementById(`test_${langue}_file`);
            
            if (radioOui.checked) {
                fileInput.style.display = 'block';
                fileInput.classList.add('active');
            } else {
                fileInput.style.display = 'none';
                fileInput.classList.remove('active');
                fileInput.value = '';
            }
        }
        
        function ajouterDocumentSupplementaire() {
            const container = document.getElementById('documents-supplementaires-container');
            const index = container.children.length;
            
            const div = document.createElement('div');
            div.className = 'document-supplementaire';
            div.innerHTML = `
                <div>
                    <label>Type de document</label>
                    <input type="text" name="types_documents_supplementaires[]" placeholder="Ex: Certificat, Attestation...">
                </div>
                <div>
                    <label>Document</label>
                    <input type="file" name="documents_supplementaires[]" onchange="validateFile(this)">
                </div>
                <button type="button" class="btn-remove" onclick="supprimerDocument(this)">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(div);
        }
        
        function supprimerDocument(button) {
            button.closest('.document-supplementaire').remove();
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (validateFile(this)) {
                        const fileName = this.files[0]?.name;
                        if (fileName) {
                            let fileDisplay = this.nextElementSibling;
                            if (!fileDisplay || !fileDisplay.classList.contains('file-name')) {
                                fileDisplay = document.createElement('p');
                                fileDisplay.className = 'file-name';
                                this.parentNode.appendChild(fileDisplay);
                            }
                            fileDisplay.innerHTML = `<i class="fas fa-check-circle"></i> Fichier sélectionné: ${fileName}`;
                        }
                    }
                });
            });

            toggleNiveau();
        });

        function validateForm() {
            const niveau = document.getElementById("niveau").value;
            
            if (!niveau) {
                alert("Veuillez sélectionner votre niveau d'études.");
                return false;
            }
            
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-graduation-cap"></i> Études au Luxembourg</h1>

        <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
            <h2><i class="fas fa-user"></i> Informations personnelles</h2>
            
            <div class="grid-2">
                <div>
                    <label class="required">Nom complet :</label>
                    <input type="text" name="nom" required placeholder="Votre nom complet">
                </div>
                <div>
                    <label class="required">Email :</label>
                    <input type="email" name="email" required placeholder="votre@email.com">
                </div>
            </div>

            <div class="grid-2">
                <div>
                    <label class="required">Téléphone :</label>
                    <input type="text" name="telephone" required placeholder="+352 ...">
                </div>
                <div>
                    <label class="required">Nationalité :</label>
                    <input type="text" name="nationalite" required placeholder="Votre nationalité">
                </div>
            </div>

            <h2><i class="fas fa-language"></i> Tests de langue</h2>
            
            <div class="test-section">
                <h4><i class="fas fa-info-circle"></i> Tests de langue recommandés</h4>
                <p>Les tests de langue ne sont pas obligatoires mais fortement recommandés pour augmenter vos chances d'admission.</p>
                
                <!-- Test de français -->
                <div class="test-option">
                    <input type="radio" name="test_francais_option" id="test_fr_oui" value="oui" onchange="toggleTestFile('fr')">
                    <label for="test_fr_oui" style="margin-top: 0;">J'ai un test de français</label>
                    <div class="test-action">
                        <a href="../../test_de_langue.php?langue=francais" class="btn-test" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Demander un test
                        </a>
                    </div>
                </div>
                <div class="test-file" id="test_fr_file">
                    <input type="file" name="test_francais" onchange="validateFile(this)">
                    <p class="file-requirements">DELF, DALF, TCF - PDF, JPG, PNG - max 5MB</p>
                </div>

                <!-- Test d'anglais -->
                <div class="test-option">
                    <input type="radio" name="test_anglais_option" id="test_en_oui" value="oui" onchange="toggleTestFile('en')">
                    <label for="test_en_oui" style="margin-top: 0;">J'ai un test d'anglais</label>
                    <div class="test-action">
                        <a href="../../test_de_langue.php?langue=anglais" class="btn-test" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Demander un test
                        </a>
                    </div>
                </div>
                <div class="test-file" id="test_en_file">
                    <input type="file" name="test_anglais" onchange="validateFile(this)">
                    <p class="file-requirements">TOEFL, IELTS - PDF, JPG, PNG - max 5MB</p>
                </div>

                <!-- Test d'allemand -->
                <div class="test-option">
                    <input type="radio" name="test_allemand_option" id="test_de_oui" value="oui" onchange="toggleTestFile('de')">
                    <label for="test_de_oui" style="margin-top: 0;">J'ai un test d'allemand</label>
                    <div class="test-action">
                        <a href="../../test_de_langue.php?langue=allemand" class="btn-test" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Demander un test
                        </a>
                    </div>
                </div>
                <div class="test-file" id="test_de_file">
                    <input type="file" name="test_allemand" onchange="validateFile(this)">
                    <p class="file-requirements">TestDaF, Goethe-Zertifikat - PDF, JPG, PNG - max 5MB</p>
                </div>
            </div>

            <h2><i class="fas fa-graduation-cap"></i> Niveau d'études</h2>
            
            <label class="required">Niveau d'études :</label>
            <select id="niveau" name="niveau" onchange="toggleNiveau()" required>
                <option value="">-- Choisir votre niveau --</option>
                <option value="bachelor">Bachelor</option>
                <option value="master">Master</option>
                <option value="doctorat">Doctorat</option>
            </select>

            <!-- Bachelor -->
            <div id="niv-bachelor" class="niveau-section hidden sub-section">
                <h4><i class="fas fa-user-graduate"></i> Documents requis pour le Bachelor</h4>
                
                <label class="required">Relevés de notes Lycée :</label>
                <input type="file" name="releves_lycee" required>
                
                <label class="required">Relevé de notes du Bac :</label>
                <input type="file" name="releve_bac" required>

                <label class="required">Diplôme du Bac :</label>
                <input type="file" name="diplome_bac" required>

                <label>Certificat de scolarité de l'année en cours <span class="optional">(Optionnel)</span> :</label>
                <input type="file" name="certificat_scolarite">
            </div>

            <!-- Master -->
            <div id="niv-master" class="niveau-section hidden sub-section">
                <h4><i class="fas fa-user-graduate"></i> Documents requis pour le Master</h4>
                
                <label class="required">Diplôme de Bachelor :</label>
                <input type="file" name="diplome_bachelor" required>

                <label class="required">Relevés de notes Bachelor :</label>
                <input type="file" name="releves_bachelor" required>

                <label>Certificat de scolarité de l'année en cours <span class="optional">(Optionnel)</span> :</label>
                <input type="file" name="certificat_scolarite">
            </div>

            <!-- Doctorat -->
            <div id="niv-doctorat" class="niveau-section hidden sub-section">
                <h4><i class="fas fa-user-graduate"></i> Documents requis pour le Doctorat</h4>
                
                <label class="required">Diplôme de Master :</label>
                <input type="file" name="diplome_master" required>

                <label class="required">Relevés de notes Master :</label>
                <input type="file" name="releves_master" required>

                <label class="required">Projet de recherche :</label>
                <input type="file" name="projet_recherche" required>

                <label>Certificat de scolarité de l'année en cours <span class="optional">(Optionnel)</span> :</label>
                <input type="file" name="certificat_scolarite">
            </div>

            <h2><i class="fas fa-file-alt"></i> Documents obligatoires</h2>
            
            <div class="grid-2">
                <div>
                    <label class="required">Passeport</label>
                    <input type="file" name="passeport" required>
                </div>
                <div>
                    <label class="required">Photo d'identité :</label>
                    <input type="file" name="photo" required>
                </div>
            </div>

            <h2><i class="fas fa-plus-circle"></i> Documents supplémentaires</h2>
            
            <div class="documents-supplementaires">
                <p>Ajoutez ici tout autre document que vous souhaitez soumettre (certificats, attestations, etc.)</p>
                
                <div id="documents-supplementaires-container">
                    <!-- Les documents supplémentaires seront ajoutés ici dynamiquement -->
                </div>
                
                <button type="button" class="btn-add-doc" onclick="ajouterDocumentSupplementaire()">
                    <i class="fas fa-plus"></i> Ajouter un document
                </button>
            </div>

            <button type="submit"><i class="fas fa-paper-plane"></i> Soumettre ma demande</button>
        </form>
    </div>
</body>
</html>