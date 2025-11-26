<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d'Équivalence de Baccalauréat</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --accent: #f72585;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --success: #4bb543;
            --danger: #dc3545;
            --border-radius: 12px;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7ff 0%, #f0f2f5 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .header h1 {
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }
        
        .header p {
            font-weight: 300;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-content {
            padding: 40px;
        }
        
        .form-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid var(--gray-light);
        }
        
        .form-section:last-of-type {
            border-bottom: none;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray-light);
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: var(--primary);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .required {
            color: var(--danger);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
            background-color: white;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.15);
        }
        
        .file-upload-area {
            border: 2px dashed var(--gray-light);
            border-radius: var(--border-radius);
            padding: 40px 20px;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            background-color: #fafbfc;
        }
        
        .file-upload-area:hover {
            border-color: var(--primary);
            background-color: #f5f7ff;
        }
        
        .file-upload-area.active {
            border-color: var(--primary);
            background-color: #f0f4ff;
        }
        
        .file-upload-icon {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 15px;
        }
        
        .file-upload-text {
            margin-bottom: 10px;
            color: var(--gray);
        }
        
        .file-upload-hint {
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .file-list {
            margin-top: 20px;
        }
        
        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background-color: var(--light);
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary);
        }
        
        .file-name {
            font-weight: 500;
        }
        
        .file-size {
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        .remove-file {
            color: var(--danger);
            cursor: pointer;
            font-weight: bold;
            padding: 5px;
            border-radius: 4px;
            transition: var(--transition);
        }
        
        .remove-file:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }
        
        .submit-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 16px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
            width: 100%;
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.3);
        }
        
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }
        
        .submit-btn:active {
            transform: translateY(0);
        }
        
        .confirmation {
            text-align: center;
            padding: 40px 30px;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            border-radius: var(--border-radius);
            margin-top: 30px;
            color: #2e7d32;
            border-left: 5px solid var(--success);
        }
        
        .confirmation h2 {
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .error-message {
            text-align: center;
            padding: 40px 30px;
            background: linear-gradient(135deg, #ffeaea, #ffd1d1);
            border-radius: var(--border-radius);
            margin-top: 30px;
            color: #dc3545;
            border-left: 5px solid var(--danger);
        }
        
        .progress-container {
            margin-top: 15px;
        }
        
        .progress-bar {
            height: 6px;
            background-color: var(--gray-light);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            width: 0%;
            transition: width 0.5s ease;
        }
        
        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .document-upload {
            border: 1.5px solid var(--gray-light);
            border-radius: var(--border-radius);
            padding: 25px;
            transition: var(--transition);
        }
        
        .document-upload:hover {
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .document-title {
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .document-title::before {
            content: "•";
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        .document-hint {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 10px;
        }
        
        .document-file-list {
            margin-top: 15px;
        }
        
        @media (max-width: 768px) {
            .form-content {
                padding: 25px;
            }
            
            .header {
                padding: 30px 20px;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .documents-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Demande d'Équivalence de Baccalauréat</h1>
            <p>Remplissez ce formulaire pour soumettre votre demande d'équivalence de diplôme</p>
        </div>
        
        <div class="form-content">
          <?php
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Configuration de la base de données
    $host = 'localhost';
    $dbname = 'babylone_service';
    $username = 'root';
    $password = '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Récupérer les données du formulaire
        $nom = $_POST['nom'] ?? '';
        $prenom = $_POST['prenom'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $universite_origine = $_POST['universite_origine'] ?? '';
        $diplome_origine = $_POST['diplome_origine'] ?? '';
        $filiere_demandee = $_POST['filiere_demandee'] ?? '';
        $notes = $_POST['notes'] ?? '';
        
        // Validation
        if (empty($nom) || empty($prenom) || empty($email) || empty($universite_origine) || empty($diplome_origine) || empty($filiere_demandee)) {
            throw new Exception('Tous les champs obligatoires doivent être remplis');
        }
        
        // Créer le dossier uploads s'il n'existe pas
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Gestion des fichiers
        $documents = [];
        $fichiers_joints = [];
        
        // Traitement du diplôme
        if (isset($_FILES['diploma']) && $_FILES['diploma']['error'] === UPLOAD_ERR_OK) {
            $diplomaFile = $_FILES['diploma'];
            $diplomaFileName = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $diplomaFile['name']);
            $diplomaFilePath = $uploadDir . $diplomaFileName;
            
            if (move_uploaded_file($diplomaFile['tmp_name'], $diplomaFilePath)) {
                $documents[] = 'Diplôme: ' . $diplomaFile['name'];
                $fichiers_joints[] = $diplomaFileName;
            }
        }
        
        // Traitement du relevé de notes
        if (isset($_FILES['transcript']) && $_FILES['transcript']['error'] === UPLOAD_ERR_OK) {
            $transcriptFile = $_FILES['transcript'];
            $transcriptFileName = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $transcriptFile['name']);
            $transcriptFilePath = $uploadDir . $transcriptFileName;
            
            if (move_uploaded_file($transcriptFile['tmp_name'], $transcriptFilePath)) {
                $documents[] = 'Relevé de notes: ' . $transcriptFile['name'];
                $fichiers_joints[] = $transcriptFileName;
            }
        }
        
        // Traitement des autres documents
        if (isset($_FILES['other_docs'])) {
            foreach ($_FILES['other_docs']['name'] as $key => $name) {
                if ($_FILES['other_docs']['error'][$key] === UPLOAD_ERR_OK) {
                    $otherFile = [
                        'name' => $name,
                        'tmp_name' => $_FILES['other_docs']['tmp_name'][$key],
                        'error' => $_FILES['other_docs']['error'][$key]
                    ];
                    
                    $otherFileName = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $otherFile['name']);
                    $otherFilePath = $uploadDir . $otherFileName;
                    
                    if (move_uploaded_file($otherFile['tmp_name'], $otherFilePath)) {
                        $documents[] = 'Autre: ' . $otherFile['name'];
                        $fichiers_joints[] = $otherFileName;
                    }
                }
            }
        }
        
        $documents_str = implode('; ', $documents);
        $fichiers_joints_str = implode(',', $fichiers_joints);
        
        // Insertion dans la base de données
        $stmt = $pdo->prepare("INSERT INTO demandes_equivalences 
                              (nom, prenom, email, telephone, universite_origine, diplome_origine, filiere_demandee, documents, fichiers_joints, notes, statut, date_demande) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())");
        
        $stmt->execute([
            $nom,
            $prenom,
            $email,
            $telephone,
            $universite_origine,
            $diplome_origine,
            $filiere_demandee,
            $documents_str,
            $fichiers_joints_str,
            $notes
        ]);
        
        $demande_id = $pdo->lastInsertId();
        
        echo '<div class="confirmation">';
        echo '<h2>Demande soumise avec succès!</h2>';
        echo '<p>Votre demande d\'équivalence de baccalauréat a été enregistrée. Votre numéro de référence est: <strong>' . $demande_id . '</strong></p>';
        echo '<p>Les fichiers ont été téléchargés avec succès.</p>';
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="error-message">';
        echo '<h2>Erreur</h2>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
}
?>
            
            <form id="equivalenceForm" method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <h2 class="section-title">Informations personnelles</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom">Nom <span class="required">*</span></label>
                            <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom <span class="required">*</span></label>
                            <input type="text" id="prenom" name="prenom" required value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2 class="section-title">Informations académiques</h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="universite_origine">Université d'origine <span class="required">*</span></label>
                            <input type="text" id="universite_origine" name="universite_origine" required value="<?php echo htmlspecialchars($_POST['universite_origine'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="diplome_origine">Diplôme d'origine <span class="required">*</span></label>
                            <input type="text" id="diplome_origine" name="diplome_origine" required value="<?php echo htmlspecialchars($_POST['diplome_origine'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="filiere_demandee">Filière demandée <span class="required">*</span></label>
                            <input type="text" id="filiere_demandee" name="filiere_demandee" required value="<?php echo htmlspecialchars($_POST['filiere_demandee'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2 class="section-title">Documents à fournir</h2>
                    
                    <div class="documents-grid">
                        <div class="document-upload">
                            <div class="document-title">Diplôme</div>
                            <div class="file-upload-area" id="diplomaUploadArea">
                                <div class="file-upload-icon">📄</div>
                                <p class="file-upload-text">Cliquez pour sélectionner votre diplôme</p>
                                <p class="file-upload-hint">Formats: PDF, JPG, PNG</p>
                            </div>
                            <input type="file" id="diplomaFile" name="diploma" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                            <div class="document-file-list" id="diplomaFileList"></div>
                        </div>
                        
                        <div class="document-upload">
                            <div class="document-title">Relevé de notes</div>
                            <div class="file-upload-area" id="transcriptUploadArea">
                                <div class="file-upload-icon">📊</div>
                                <p class="file-upload-text">Cliquez pour sélectionner votre relevé de notes</p>
                                <p class="file-upload-hint">Formats: PDF, JPG, PNG</p>
                            </div>
                            <input type="file" id="transcriptFile" name="transcript" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                            <div class="document-file-list" id="transcriptFileList"></div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 30px;">
                        <label>Autres documents (optionnel)</label>
                        <div class="file-upload-area" id="otherDocsUploadArea">
                            <div class="file-upload-icon">📎</div>
                            <p class="file-upload-text">Glissez-déposez vos fichiers ici ou cliquez pour sélectionner</p>
                            <p class="file-upload-hint">Formats acceptés: PDF, JPG, PNG</p>
                        </div>
                        <input type="file" id="otherDocsFile" name="other_docs[]" multiple accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                        <div class="file-list" id="otherDocsFileList"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes (optionnel)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Ajoutez toute information supplémentaire utile au traitement de votre demande..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">Soumettre la demande</button>
            </form>
        </div>
    </div>

    <script>
        // Gestion de la soumission du formulaire
        document.getElementById('equivalenceForm').addEventListener('submit', function(e) {
            // Validation des champs obligatoires
            const requiredFields = document.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = 'var(--danger)';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            // Vérification des fichiers obligatoires
            const diplomaFile = document.getElementById('diplomaFile');
            const transcriptFile = document.getElementById('transcriptFile');
            
            if (!diplomaFile.files.length) {
                alert('Veuillez joindre votre diplôme.');
                isValid = false;
            }
            
            if (!transcriptFile.files.length) {
                alert('Veuillez joindre votre relevé de notes.');
                isValid = false;
            }
            
            if (!isValid) {
                alert('Veuillez remplir tous les champs obligatoires.');
                e.preventDefault();
                return;
            }
            
            // Afficher l'indicateur de chargement
            const btn = document.querySelector('.submit-btn');
            const originalText = btn.textContent;
            btn.textContent = 'Envoi en cours...';
            btn.disabled = true;
            
            // Le formulaire sera soumis normalement
        });
        
        // Gestion des fichiers pour le diplôme
        const diplomaFile = document.getElementById('diplomaFile');
        const diplomaFileList = document.getElementById('diplomaFileList');
        const diplomaUploadArea = document.getElementById('diplomaUploadArea');
        
        diplomaUploadArea.addEventListener('click', function() {
            diplomaFile.click();
        });
        
        diplomaFile.addEventListener('change', function() {
            updateFileList(diplomaFile, diplomaFileList, 'diploma');
        });
        
        // Gestion des fichiers pour le relevé de notes
        const transcriptFile = document.getElementById('transcriptFile');
        const transcriptFileList = document.getElementById('transcriptFileList');
        const transcriptUploadArea = document.getElementById('transcriptUploadArea');
        
        transcriptUploadArea.addEventListener('click', function() {
            transcriptFile.click();
        });
        
        transcriptFile.addEventListener('change', function() {
            updateFileList(transcriptFile, transcriptFileList, 'transcript');
        });
        
        // Gestion des autres documents
        const otherDocsFile = document.getElementById('otherDocsFile');
        const otherDocsFileList = document.getElementById('otherDocsFileList');
        const otherDocsUploadArea = document.getElementById('otherDocsUploadArea');
        
        otherDocsUploadArea.addEventListener('click', function() {
            otherDocsFile.click();
        });
        
        otherDocsFile.addEventListener('change', function() {
            updateFileList(otherDocsFile, otherDocsFileList, 'other');
        });
        
        // Gestion du glisser-déposer pour autres documents
        otherDocsUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            otherDocsUploadArea.classList.add('active');
        });
        
        otherDocsUploadArea.addEventListener('dragleave', function() {
            otherDocsUploadArea.classList.remove('active');
        });
        
        otherDocsUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            otherDocsUploadArea.classList.remove('active');
            
            if (e.dataTransfer.files.length) {
                otherDocsFile.files = e.dataTransfer.files;
                updateFileList(otherDocsFile, otherDocsFileList, 'other');
            }
        });
        
        // Fonction pour mettre à jour la liste des fichiers
        function updateFileList(fileInput, fileListElement, type) {
            fileListElement.innerHTML = '';
            
            for (let i = 0; i < fileInput.files.length; i++) {
                const file = fileInput.files[i];
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                
                const fileInfo = document.createElement('div');
                fileInfo.innerHTML = `
                    <div class="file-name">${file.name}</div>
                    <div class="file-size">${formatFileSize(file.size)}</div>
                `;
                
                const removeBtn = document.createElement('span');
                removeBtn.className = 'remove-file';
                removeBtn.textContent = '×';
                removeBtn.addEventListener('click', function() {
                    removeFile(fileInput, i, type);
                });
                
                fileItem.appendChild(fileInfo);
                fileItem.appendChild(removeBtn);
                fileListElement.appendChild(fileItem);
            }
        }
        
        // Fonction pour formater la taille du fichier
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Fonction pour supprimer un fichier de la liste
        function removeFile(fileInput, index, type) {
            const dt = new DataTransfer();
            const files = fileInput.files;
            
            for (let i = 0; i < files.length; i++) {
                if (i !== index) {
                    dt.items.add(files[i]);
                }
            }
            
            fileInput.files = dt.files;
            
            if (type === 'diploma') {
                updateFileList(fileInput, document.getElementById('diplomaFileList'), type);
            } else if (type === 'transcript') {
                updateFileList(fileInput, document.getElementById('transcriptFileList'), type);
            } else {
                updateFileList(fileInput, document.getElementById('otherDocsFileList'), type);
            }
        }
    </script>
</body>
</html>