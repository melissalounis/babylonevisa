<?php
// teste_de_langue.php
session_start();

// Configuration directe de la base de données

// Connexion à la base de données
require_once __DIR__ . '/../config.php';
// Créer la table si elle n'existe pas
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS langue_tests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL,
        telephone VARCHAR(20) NOT NULL,
        adresse TEXT NOT NULL,
        ville VARCHAR(100) NOT NULL,
        code_postal VARCHAR(10) NOT NULL,
        pays VARCHAR(50) NOT NULL,
        type_piece VARCHAR(50) NOT NULL,
        numero_piece VARCHAR(100) NOT NULL,
        date_emission_piece DATE NOT NULL,
        date_expiration_piece DATE NOT NULL,
        fichier_piece VARCHAR(255),
        fichier_passeport VARCHAR(255),
        type_test VARCHAR(50) NOT NULL,
        date_rendezvous DATE NOT NULL,
        heure_rendezvous TIME NOT NULL,
        statut VARCHAR(20) DEFAULT 'en_attente',
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_email_date (email, date_rendezvous)
    )");
} catch (PDOException $e) {
    // La table existe peut-être déjà
}

// Traitement du formulaire
$message = '';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Nettoyage des données
        $data = [
            'nom' => trim($_POST['nom']),
            'prenom' => trim($_POST['prenom']),
            'email' => trim($_POST['email']),
            'telephone' => trim($_POST['telephone']),
            'adresse' => trim($_POST['adresse']),
            'ville' => trim($_POST['ville']),
            'code_postal' => trim($_POST['code_postal']),
            'pays' => $_POST['pays'],
            'type_piece' => $_POST['type_piece'],
            'numero_piece' => trim($_POST['numero_piece']),
            'date_emission_piece' => $_POST['date_emission_piece'],
            'date_expiration_piece' => $_POST['date_expiration_piece'],
            'test_langue' => $_POST['test_langue'],
            'date_rendezvous' => $_POST['date_rendezvous'],
            'heure_rendezvous' => $_POST['heure_rendezvous']
        ];

        // Validation des champs obligatoires
        $champsObligatoires = [
            'nom', 'prenom', 'email', 'telephone', 'adresse', 'ville', 'code_postal', 'pays',
            'type_piece', 'numero_piece', 'date_emission_piece', 'date_expiration_piece',
            'test_langue', 'date_rendezvous', 'heure_rendezvous'
        ];

        foreach ($champsObligatoires as $champ) {
            if (empty($data[$champ])) {
                throw new Exception("Le champ " . str_replace('_', ' ', $champ) . " est obligatoire.");
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("L'adresse email n'est pas valide.");
        }

        // Validation des dates
        $aujourdhui = new DateTime();
        $dateEmission = DateTime::createFromFormat('Y-m-d', $data['date_emission_piece']);
        $dateExpiration = DateTime::createFromFormat('Y-m-d', $data['date_expiration_piece']);
        
        if ($dateEmission > $dateExpiration) {
            throw new Exception("La date d'émission ne peut pas être après la date d'expiration.");
        }
        
        if ($dateExpiration < $aujourdhui) {
            throw new Exception("La pièce d'identité est expirée.");
        }

        // Vérifier si le rendez-vous existe déjà
        $check = $pdo->prepare("SELECT id FROM langue_tests WHERE email = ? AND date_rendezvous = ?");
        $check->execute([$data['email'], $data['date_rendezvous']]);
        
        if ($check->fetch()) {
            throw new Exception("Un rendez-vous existe déjà pour cet email à cette date.");
        }

        // Gestion des fichiers uploadés
        $dossierUpload = __DIR__ . '/uploads/';
        if (!is_dir($dossierUpload)) {
            mkdir($dossierUpload, 0777, true);
        }

        $fichierPiece = '';
        $fichierPasseport = '';

        // Upload pièce d'identité
        if (isset($_FILES['fichier_piece']) && $_FILES['fichier_piece']['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($_FILES['fichier_piece']['name'], PATHINFO_EXTENSION);
            $fichierPiece = uniqid() . '_piece.' . $extension;
            move_uploaded_file($_FILES['fichier_piece']['tmp_name'], $dossierUpload . $fichierPiece);
        }

        // Upload passeport (optionnel)
        if (isset($_FILES['fichier_passeport']) && $_FILES['fichier_passeport']['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($_FILES['fichier_passeport']['name'], PATHINFO_EXTENSION);
            $fichierPasseport = uniqid() . '_passeport.' . $extension;
            move_uploaded_file($_FILES['fichier_passeport']['tmp_name'], $dossierUpload . $fichierPasseport);
        }

        // Insérer le rendez-vous
        $sql = "INSERT INTO langue_tests 
                (nom, prenom, email, telephone, adresse, ville, code_postal, pays,
                 type_piece, numero_piece, date_emission_piece, date_expiration_piece,
                 fichier_piece, fichier_passeport, type_test, date_rendezvous, heure_rendezvous, statut) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['telephone'],
            $data['adresse'],
            $data['ville'],
            $data['code_postal'],
            $data['pays'],
            $data['type_piece'],
            $data['numero_piece'],
            $data['date_emission_piece'],
            $data['date_expiration_piece'],
            $fichierPiece,
            $fichierPasseport,
            $data['test_langue'],
            $data['date_rendezvous'],
            $data['heure_rendezvous']
        ]);

        $message = "Votre rendez-vous pour le test de langue a été enregistré avec succès !";

    } catch (Exception $e) {
        $erreur = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendez-vous Test de Langue</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: white;
            min-height: 100vh;
            padding: 20px;
        }

        .studies-hero {
            background: linear-gradient(135deg, #0056b3 0%, #0077ff 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 15px;
        }

        .hero-content h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .hero-content h1 i {
            margin-right: 15px;
        }

        .hero-content p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border-color: #28a745;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        .rendezvous-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 30px;
            padding: 25px;
            border: 2px solid #cde2f7ff;
            border-radius: 10px;
            background: #fafafa;
        }

        .form-section h3 {
            color: #0056b3;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3748;
        }

        .required::after {
            content: " *";
            color: #dc3545;
        }

        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #0056b3;
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
        }

        .file-input {
            border: 2px dashed #e1e5e9;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input:hover {
            border-color: #0056b3;
            background: #f0f8ff;
        }

        .file-hint {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 5px;
        }

        .example-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-top: 8px;
            font-size: 0.85rem;
            color: #495057;
        }

        .example-box strong {
            color: #0056b3;
        }

        .submit-btn {
            width: 100%;
            background: #0056b3;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .submit-btn:hover {
            background: #004494;
            transform: translateY(-2px);
        }

        optgroup {
            font-weight: 600;
            color: #0056b3;
        }

        optgroup option {
            font-weight: normal;
            color: #2d3748;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .rendezvous-form {
                padding: 20px;
            }
            
            .form-section {
                padding: 20px;
            }
            
            .hero-content h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="studies-hero">
        <div class="hero-content">
            <h1><i class="fas fa-language"></i> Rendez-vous Test de Langue</h1>
            <p>Choisissez votre test et prenez rendez-vous en quelques minutes</p>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($erreur): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($erreur); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="rendezvous-form" enctype="multipart/form-data">
            <!-- Informations personnelles -->
            <div class="form-section">
                <h3><i class="fas fa-user"></i> Informations personnelles</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nom" class="required">Nom</label>
                        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prenom" class="required">Prénom</label>
                        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="required">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telephone" class="required">Téléphone</label>
                        <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group form-group-full">
                        <label for="adresse" class="required">Adresse complète</label>
                        <textarea id="adresse" name="adresse" rows="3" required><?php echo htmlspecialchars($_POST['adresse'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="ville" class="required">Ville</label>
                        <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($_POST['ville'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="code_postal" class="required">Code postal</label>
                        <input type="text" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($_POST['code_postal'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="pays" class="required">Pays</label>
                        <select id="pays" name="pays" required>
                            <option value="">Sélectionnez un pays</option>
                            <option value="france" <?php echo ($_POST['pays'] ?? '') === 'france' ? 'selected' : ''; ?>>France</option>
                            <option value="maroc" <?php echo ($_POST['pays'] ?? '') === 'maroc' ? 'selected' : ''; ?>>Maroc</option>
                            <option value="algerie" <?php echo ($_POST['pays'] ?? '') === 'algerie' ? 'selected' : ''; ?>>Algérie</option>
                            <option value="tunisie" <?php echo ($_POST['pays'] ?? '') === 'tunisie' ? 'selected' : ''; ?>>Tunisie</option>
                            <option value="senegal" <?php echo ($_POST['pays'] ?? '') === 'senegal' ? 'selected' : ''; ?>>Sénégal</option>
                            <option value="cote_ivoire" <?php echo ($_POST['pays'] ?? '') === 'cote_ivoire' ? 'selected' : ''; ?>>Côte d'Ivoire</option>
                            <option value="autre" <?php echo ($_POST['pays'] ?? '') === 'autre' ? 'selected' : ''; ?>>Autre</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Pièce d'identité -->
            <div class="form-section">
                <h3><i class="fas fa-id-card"></i> Pièce d'identité</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="type_piece" class="required">Type de pièce</label>
                        <select id="type_piece" name="type_piece" required>
                            <option value="">Sélectionnez...</option>
                            <option value="passeport" <?php echo ($_POST['type_piece'] ?? '') === 'passeport' ? 'selected' : ''; ?>>Passeport</option>
                            <option value="carte_identite" <?php echo ($_POST['type_piece'] ?? '') === 'carte_identite' ? 'selected' : ''; ?>>Carte d'identité</option>
                            <option value="permis_conduire" <?php echo ($_POST['type_piece'] ?? '') === 'permis_conduire' ? 'selected' : ''; ?>>Permis de conduire</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="numero_piece" class="required">Numéro de pièce</label>
                        <input type="text" id="numero_piece" name="numero_piece" 
                               value="<?php echo htmlspecialchars($_POST['numero_piece'] ?? ''); ?>" 
                               placeholder="Exemple: 12AB34567" 
                               required>
                        <div class="example-box">
                            <strong>Où trouver ce numéro ?</strong><br>
                            Carte d'identité : en haut à droite (ex: 12AB34567)<br>
                            Passeport : en haut à droite de la page d'identité
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="date_emission_piece" class="required">Date d'émission</label>
                        <input type="date" id="date_emission_piece" name="date_emission_piece" value="<?php echo htmlspecialchars($_POST['date_emission_piece'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="date_expiration_piece" class="required">Date d'expiration</label>
                        <input type="date" id="date_expiration_piece" name="date_expiration_piece" value="<?php echo htmlspecialchars($_POST['date_expiration_piece'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="fichier_piece" class="required">Copie de la pièce d'identité</label>
                        <input type="file" id="fichier_piece" name="fichier_piece" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="file-hint">Formats acceptés: PDF, JPG, PNG (max 5MB)</div>
                    </div>

                    <div class="form-group">
                        <label for="fichier_passeport">Copie du passeport (optionnel)</label>
                        <input type="file" id="fichier_passeport" name="fichier_passeport" class="file-input" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="file-hint">Formats acceptés: PDF, JPG, PNG (max 5MB)</div>
                    </div>
                </div>
            </div>

            <!-- Choix du test -->
            <div class="form-section">
                <h3><i class="fas fa-clipboard-list"></i> Choix du test</h3>
                <div class="form-group">
                    <label for="test_langue" class="required">Test de langue</label>
                    <select id="test_langue" name="test_langue" required>
                        <option value="">Sélectionnez un test</option>
                        
                        <optgroup label="Tests de Français">
                            <option value="tcf_tp" <?php echo ($_POST['test_langue'] ?? '') === 'tcf_tp' ? 'selected' : ''; ?>>TCF Tout Public</option>
                            <option value="tcf_dap" <?php echo ($_POST['test_langue'] ?? '') === 'tcf_dap' ? 'selected' : ''; ?>>TCF DAP</option>
                            <option value="tcf_anf" <?php echo ($_POST['test_langue'] ?? '') === 'tcf_anf' ? 'selected' : ''; ?>>TCF ANF</option>
                            <option value="tcf_canada" <?php echo ($_POST['test_langue'] ?? '') === 'tcf_canada' ? 'selected' : ''; ?>>TCF Canada</option>
                            <option value="tcf_quebec" <?php echo ($_POST['test_langue'] ?? '') === 'tcf_quebec' ? 'selected' : ''; ?>>TCF Québec</option>
                            <option value="delf_a1" <?php echo ($_POST['test_langue'] ?? '') === 'delf_a1' ? 'selected' : ''; ?>>DELF A1</option>
                            <option value="delf_a2" <?php echo ($_POST['test_langue'] ?? '') === 'delf_a2' ? 'selected' : ''; ?>>DELF A2</option>
                            <option value="delf_b1" <?php echo ($_POST['test_langue'] ?? '') === 'delf_b1' ? 'selected' : ''; ?>>DELF B1</option>
                            <option value="delf_b2" <?php echo ($_POST['test_langue'] ?? '') === 'delf_b2' ? 'selected' : ''; ?>>DELF B2</option>
                            <option value="dalf_c1" <?php echo ($_POST['test_langue'] ?? '') === 'dalf_c1' ? 'selected' : ''; ?>>DALF C1</option>
                            <option value="dalf_c2" <?php echo ($_POST['test_langue'] ?? '') === 'dalf_c2' ? 'selected' : ''; ?>>DALF C2</option>
                            <option value="tef_canada" <?php echo ($_POST['test_langue'] ?? '') === 'tef_canada' ? 'selected' : ''; ?>>TEF Canada</option>
                            <option value="tef_quebec" <?php echo ($_POST['test_langue'] ?? '') === 'tef_quebec' ? 'selected' : ''; ?>>TEF Québec</option>
                        </optgroup>
                        
                        <optgroup label="Tests d'Anglais">
                            <option value="ielts_academic" <?php echo ($_POST['test_langue'] ?? '') === 'ielts_academic' ? 'selected' : ''; ?>>IELTS Academic</option>
                            <option value="ielts_general" <?php echo ($_POST['test_langue'] ?? '') === 'ielts_general' ? 'selected' : ''; ?>>IELTS General Training</option>
                            <option value="toefl_ibt" <?php echo ($_POST['test_langue'] ?? '') === 'toefl_ibt' ? 'selected' : ''; ?>>TOEFL iBT</option>
                            <option value="toeic" <?php echo ($_POST['test_langue'] ?? '') === 'toeic' ? 'selected' : ''; ?>>TOEIC</option>
                            <option value="cambridge_b2" <?php echo ($_POST['test_langue'] ?? '') === 'cambridge_b2' ? 'selected' : ''; ?>>Cambridge B2 First</option>
                            <option value="cambridge_c1" <?php echo ($_POST['test_langue'] ?? '') === 'cambridge_c1' ? 'selected' : ''; ?>>Cambridge C1 Advanced</option>
                            <option value="celpip_general" <?php echo ($_POST['test_langue'] ?? '') === 'celpip_general' ? 'selected' : ''; ?>>CELPIP General</option>
                            <option value="pte_academic" <?php echo ($_POST['test_langue'] ?? '') === 'pte_academic' ? 'selected' : ''; ?>>PTE Academic</option>
                        </optgroup>
                    </select>
                </div>
            </div>

            <!-- Date et heure -->
            <div class="form-section">
                <h3><i class="fas fa-calendar-alt"></i> Date et heure</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="date_rendezvous" class="required">Date souhaitée</label>
                        <input type="date" id="date_rendezvous" name="date_rendezvous" value="<?php echo htmlspecialchars($_POST['date_rendezvous'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="heure_rendezvous" class="required">Heure souhaitée</label>
                        <input type="time" id="heure_rendezvous" name="heure_rendezvous" value="<?php echo htmlspecialchars($_POST['heure_rendezvous'] ?? ''); ?>" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-paper-plane"></i> Confirmer le rendez-vous
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Empêcher les dates passées
            const aujourdhui = new Date().toISOString().split('T')[0];
            document.getElementById('date_rendezvous').min = aujourdhui;
            document.getElementById('date_emission_piece').max = aujourdhui;
            
            // Validation des dates de pièce
            const dateEmission = document.getElementById('date_emission_piece');
            const dateExpiration = document.getElementById('date_expiration_piece');
            
            dateEmission.addEventListener('change', function() {
                dateExpiration.min = this.value;
            });
        });
    </script>
</body>
</html>