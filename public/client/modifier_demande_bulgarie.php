<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifier si un ID de demande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: mes_demandes_bulgarie.php");
    exit;
}

$demande_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Paramètres de connexion
$host = 'localhost';
$dbname = 'babylone_service';
$username = 'root';
$password = '';

// Variables pour stocker les messages
$success_message = '';
$error_message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupérer la demande spécifique
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_etudes_bulgarie 
        WHERE id = ? 
    ");
    $stmt->execute([$demande_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si la demande existe
    if (!$demande) {
        $_SESSION['error'] = "Demande non trouvée.";
        header("Location: mes_demandes_bulgarie.php");
        exit;
    }

    // Vérifier si la demande peut être modifiée (seulement si statut "nouveau")
    if ($demande['statut'] != 'nouveau') {
        $_SESSION['error'] = "Cette demande ne peut plus être modifiée car son statut est : " . $demande['statut'];
        header("Location: mes_demandes_bulgarie.php");
        exit;
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type']) && $_POST['type'] === 'modification') {
    try {
        // Gestion de l'upload des fichiers
        $upload_dir = "../../uploads/bulgarie/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Fonction pour uploader un fichier
        function uploadFile($file, $upload_dir, $existing_file = null) {
            // Si aucun nouveau fichier n'est uploadé, conserver l'existant
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return $existing_file;
            }
            
            $filename = uniqid() . '_' . basename($file['name']);
            $target_path = $upload_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                // Supprimer l'ancien fichier s'il existe
                if ($existing_file && file_exists($upload_dir . $existing_file)) {
                    unlink($upload_dir . $existing_file);
                }
                return $filename;
            }
            return $existing_file;
        }

        // Upload des fichiers obligatoires (seulement si un nouveau fichier est fourni)
        $passeport_file = uploadFile($_FILES['passeport'], $upload_dir, $demande['passeport']);
        $justificatif_file = uploadFile($_FILES['justificatif'], $upload_dir, $demande['justificatif_financier']);
        
        // Gestion des photos
        $photos_files = [];
        if (isset($_FILES['photo']) && is_array($_FILES['photo']['tmp_name'])) {
            $existing_photos = !empty($demande['photos']) ? explode(',', $demande['photos']) : [];
            
            foreach ($_FILES['photo']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['photo']['error'][$key] === UPLOAD_ERR_OK) {
                    $photo_file = [
                        'name' => $_FILES['photo']['name'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['photo']['error'][$key]
                    ];
                    $uploaded_photo = uploadFile($photo_file, $upload_dir);
                    if ($uploaded_photo) {
                        $photos_files[] = $uploaded_photo;
                    }
                }
            }
            
            // Si aucune nouvelle photo n'a été uploadée, conserver les anciennes
            if (empty($photos_files) && !empty($existing_photos)) {
                $photos_files = $existing_photos;
            }
        } else {
            // Conserver les photos existantes
            $photos_files = !empty($demande['photos']) ? explode(',', $demande['photos']) : [];
        }
        $photos = implode(',', $photos_files);

        // Mise à jour dans la base de données
        $sql = "UPDATE demandes_etudes_bulgarie SET
                nom_complet = ?,
                telephone = ?,
                programme = ?,
                niveau_etude = ?,
                passeport = ?,
                justificatif_financier = ?,
                photos = ?,
                date_modification = NOW()
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nom'],
            $_POST['telephone'],
            $_POST['programme'],
            $_POST['niveau'],
            $passeport_file,
            $justificatif_file,
            $photos,
            $demande_id
        ]);

        $success_message = "Votre demande a été modifiée avec succès !";

    } catch (PDOException $e) {
        $error_message = "Erreur lors de la modification : " . $e->getMessage();
    }
}

// Préparer les données pour l'affichage
$form_data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $demande;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Demande Bulgarie - Babylone Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #e74c3c;
            --success: #27ae60;
            --warning: #f39c12;
            --error: #c0392b;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --gray: #7f8c8d;
            --border-radius: 8px;
            --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            padding: 20px;
            min-height: 100vh;
            line-height: 1.6;
            color: #333;
        }
        
        .container {
            background: #fff;
            padding: 30px;
            border-radius: var(--border-radius);
            max-width: 900px;
            margin: 0 auto;
            box-shadow: var(--box-shadow);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--light);
        }
        
        h1 {
            color: var(--dark);
            margin-bottom: 10px;
            font-size: 2.2rem;
            font-weight: 600;
        }
        
        .demande-info {
            background: var(--light);
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        h2 {
            color: var(--dark);
            margin: 25px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light);
            font-size: 1.4rem;
        }
        
        h3 {
            color: var(--secondary);
            margin: 20px 0 15px;
            font-size: 1.2rem;
        }
        
        h4 {
            color: var(--secondary);
            margin: 15px 0 10px;
            font-size: 1.1rem;
        }
        
        label {
            font-weight: 600;
            display: block;
            margin-top: 20px;
            color: var(--dark);
        }
        
        .required::after {
            content: ' *';
            color: var(--accent);
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            margin-top: 8px;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-size: 16px;
            transition: var(--transition);
            background: #fff;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(44, 62, 80, 0.1);
        }
        
        .hidden {
            display: none;
        }
        
        button {
            margin-top: 30px;
            padding: 15px 30px;
            background: var(--primary);
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
        }
        
        button:hover {
            background: var(--secondary);
            transform: translateY(-1px);
        }
        
        .sub-section {
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: var(--border-radius);
            margin-top: 20px;
            background: #f8f9fa;
        }
        
        .success-message {
            color: var(--success);
            text-align: center;
            padding: 15px;
            background: #d4edda;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            color: var(--error);
            text-align: center;
            padding: 15px;
            background: #f8d7da;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
                width: 95%;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
        
        input[type="file"] {
            padding: 10px;
            background: #f8f9fa;
            border: 1px dashed #ccc;
        }
        
        .niveau-section {
            margin-top: 15px;
        }
        
        .file-requirements {
            font-size: 0.85rem;
            color: var(--gray);
            margin-top: 5px;
        }
        
        .info-text {
            font-size: 0.9rem;
            color: var(--gray);
            margin-top: 5px;
        }
        
        .form-section {
            margin: 20px 0;
            padding-bottom: 20px;
        }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        @media (max-width: 768px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
        
        .file-upload-wrapper {
            margin-top: 10px;
        }
        
        .file-name {
            margin-top: 5px;
            font-size: 0.9rem;
            color: var(--success);
            font-weight: 500;
        }
        
        .existing-file {
            background: var(--light);
            padding: 10px;
            border-radius: var(--border-radius);
            margin-top: 5px;
            border: 1px solid #ddd;
        }
        
        .existing-file a {
            color: var(--primary);
            text-decoration: none;
        }
        
        .existing-file a:hover {
            text-decoration: underline;
        }
        
        .btn-secondary {
            background: var(--gray);
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: #6c757d;
        }
    </style>
    <script>
        function toggleProgramme() {
            let programme = document.getElementById("programme").value;
            document.getElementById("prog-en").classList.add("hidden");
            document.getElementById("prog-prep").classList.add("hidden");

            if (programme === "anglais") document.getElementById("prog-en").classList.remove("hidden");
            if (programme === "preparatoire") document.getElementById("prog-prep").classList.remove("hidden");
        }

        function toggleNiveau() {
            let niveau = document.getElementById("niveau").value;
            document.querySelectorAll(".niveau-section").forEach(div => div.classList.add("hidden"));
            if (niveau) document.getElementById("niv-" + niveau).classList.remove("hidden");
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const fileInputs = document.querySelectorAll('input[type="file"]');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const fileName = this.files[0]?.name;
                    if (fileName) {
                        let fileDisplay = this.nextElementSibling;
                        if (!fileDisplay || !fileDisplay.classList.contains('file-name')) {
                            fileDisplay = document.createElement('p');
                            fileDisplay.className = 'file-name';
                            this.parentNode.appendChild(fileDisplay);
                        }
                        fileDisplay.innerHTML = `<i class="fas fa-check"></i> ${fileName}`;
                    }
                });
            });
            
            // Initialiser l'affichage des sections
            toggleProgramme();
            toggleNiveau();
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-edit"></i> Modifier la Demande Bulgarie</h1>
            <div class="demande-info">
                <p><strong>Référence :</strong> #<?php echo $demande['id']; ?></p>
                <p><strong>Statut :</strong> <?php echo ucfirst($demande['statut']); ?></p>
                <p><strong>Date de soumission :</strong> <?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?></p>
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de modification -->
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="type" value="modification">
            
            <h2>Informations Personnelles</h2>
            
            <div class="form-section">
                <div class="grid-2">
                    <div>
                        <label class="required">Nom complet</label>
                        <input type="text" name="nom" required value="<?php echo htmlspecialchars($form_data['nom_complet'] ?? ''); ?>">
                    </div>
                    
                    <div>
                        <label class="required">Téléphone</label>
                        <input type="text" name="telephone" required value="<?php echo htmlspecialchars($form_data['telephone'] ?? ''); ?>">
                    </div>
                </div>
                
                <div>
                    <label>Adresse email</label>
                    <input type="email" value="<?php echo htmlspecialchars($demande['email']); ?>" disabled>
                    <p class="info-text">L'email ne peut pas être modifié</p>
                </div>
            </div>

            <!-- Choix programme -->
            <div class="form-section">
                <h3>Programme d'Études</h3>
                
                <label class="required">Programme souhaité</label>
                <select id="programme" name="programme" onchange="toggleProgramme()" required>
                    <option value="">Sélectionnez un programme</option>
                    <option value="anglais" <?php echo ($form_data['programme'] ?? '') === 'anglais' ? 'selected' : ''; ?>>Programme en Anglais</option>
                    <option value="preparatoire" <?php echo ($form_data['programme'] ?? '') === 'preparatoire' ? 'selected' : ''; ?>>Année préparatoire</option>
                </select>
            </div>

            <!-- Choix du niveau -->
            <div class="form-section">
                <h3>Niveau d'Études</h3>
                
                <label class="required">Niveau actuel</label>
                <select id="niveau" name="niveau" onchange="toggleNiveau()" required>
                    <option value="">Sélectionnez votre niveau</option>
                    <option value="l1" <?php echo ($form_data['niveau_etude'] ?? '') === 'l1' ? 'selected' : ''; ?>>Licence 1 (L1)</option>
                    <option value="l2" <?php echo ($form_data['niveau_etude'] ?? '') === 'l2' ? 'selected' : ''; ?>>Licence 2 (L2)</option>
                    <option value="l3" <?php echo ($form_data['niveau_etude'] ?? '') === 'l3' ? 'selected' : ''; ?>>Licence 3 (L3)</option>
                    <option value="m1" <?php echo ($form_data['niveau_etude'] ?? '') === 'm1' ? 'selected' : ''; ?>>Master 1 (M1)</option>
                    <option value="m2" <?php echo ($form_data['niveau_etude'] ?? '') === 'm2' ? 'selected' : ''; ?>>Master 2 (M2)</option>
                </select>
            </div>

            <!-- Pièces communes -->
            <div class="form-section">
                <h3>Documents Obligatoires</h3>
                
                <div class="grid-2">
                    <div class="file-upload-wrapper">
                        <label class="required">Passeport</label>
                        
                        <?php if (!empty($demande['passeport'])): ?>
                        <div class="existing-file">
                            <i class="fas fa-file-pdf"></i> Fichier actuel : 
                            <a href="../../uploads/bulgarie/<?php echo $demande['passeport']; ?>" target="_blank">
                                Voir le document
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <input type="file" name="passeport">
                        <p class="file-requirements">Laissez vide pour conserver le fichier actuel</p>
                    </div>
                    
                    <div class="file-upload-wrapper">
                        <label class="required">Justificatif financier</label>
                        
                        <?php if (!empty($demande['justificatif_financier'])): ?>
                        <div class="existing-file">
                            <i class="fas fa-file-pdf"></i> Fichier actuel : 
                            <a href="../../uploads/bulgarie/<?php echo $demande['justificatif_financier']; ?>" target="_blank">
                                Voir le document
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <input type="file" name="justificatif">
                        <p class="file-requirements">Laissez vide pour conserver le fichier actuel</p>
                    </div>
                </div>
                
                <div class="file-upload-wrapper">
                    <label class="required">Photos d'identité</label>
                    
                    <?php if (!empty($demande['photos'])): 
                        $photos = explode(',', $demande['photos']);
                    ?>
                        <div class="existing-file">
                            <i class="fas fa-images"></i> Photos actuelles : 
                            <?php foreach ($photos as $index => $photo): ?>
                                <a href="../../uploads/bulgarie/<?php echo $photo; ?>" target="_blank" style="margin-right: 10px;">
                                    Photo <?php echo $index + 1; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" name="photo" multiple>
                    <p class="info-text">Maintenez Ctrl (Windows) ou Cmd (Mac) pour sélectionner plusieurs photos</p>
                    <p class="file-requirements">Laissez vide pour conserver les photos actuelles</p>
                </div>
            </div>

            <div class="grid-2">
                <a href="mes_demandes_bulgarie.php" class="btn btn-secondary" style="text-decoration: none; text-align: center;">
                    <i class="fas fa-arrow-left"></i> Annuler
                </a>
                <button type="submit">
                    <i class="fas fa-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</body>
</html>