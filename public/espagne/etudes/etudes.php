<?php
// Connexion à la base de données
require_once __DIR__ . '../../../config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du formulaire
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération des données du formulaire
        $nom_complet = $_POST['nom_complet'] ?? '';
        $date_naissance = $_POST['date_naissance'] ?? '';
        $nationalite = $_POST['nationalite'] ?? '';
        $email = $_POST['email'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $adresse = $_POST['adresse'] ?? '';
        $niveau_etude = $_POST['niveau_etude'] ?? '';
        $universite_souhaitee = $_POST['universite_souhaitee'] ?? '';
        $programme_etude = $_POST['programme_etude'] ?? '';
        $type_service = $_POST['type_service'] ?? 'admission';
        $test_langue = $_POST['test_langue'] ?? '';
        $score_test = $_POST['score_test'] ?? '';
        $demander_test = $_POST['demander_test'] ?? '';
        
        // Validation des champs obligatoires
        if (empty($nom_complet) || empty($date_naissance) || empty($nationalite) || empty($email) || empty($niveau_etude) || empty($telephone) || empty($adresse)) {
            throw new Exception("Tous les champs obligatoires doivent être remplis");
        }
        
        // Gestion des fichiers uploadés
        $dossier_upload = 'uploads/espagne/';
        if (!file_exists($dossier_upload)) {
            mkdir($dossier_upload, 0777, true);
        }
        
        $fichiers_uploades = [];
        $champs_fichiers = [
            'passeport', 'lettre_admission', 'photo', 'certificat_medical', 'casier_judiciaire',
            'releve_2nde', 'releve_1ere', 'releve_terminale', 'releve_bac',
            'diplome_bac', 'releve_l1', 'releve_l2', 'releve_l3', 'diplome_licence',
            'certificat_scolarite', 'test_langue_fichier', 'annexe',
            'releve_M1', 'releve_M2', 'diplome_Master'
        ];
        
        // Gestion des documents supplémentaires
        $documents_supplementaires = [];
        if (isset($_POST['doc_supp_nom']) && is_array($_POST['doc_supp_nom'])) {
            foreach ($_POST['doc_supp_nom'] as $index => $nom_doc) {
                if (!empty($nom_doc) && isset($_FILES['doc_supp_fichier']['name'][$index]) && $_FILES['doc_supp_fichier']['error'][$index] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['doc_supp_fichier']['name'][$index],
                        'tmp_name' => $_FILES['doc_supp_fichier']['tmp_name'][$index],
                        'error' => $_FILES['doc_supp_fichier']['error'][$index]
                    ];
                    
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $nom_fichier = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file['name']);
                    $chemin_fichier = $dossier_upload . $nom_fichier;
                    
                    if (move_uploaded_file($file['tmp_name'], $chemin_fichier)) {
                        $documents_supplementaires[] = [
                            'nom' => $nom_doc,
                            'fichier' => $nom_fichier
                        ];
                    }
                }
            }
        }
        
        foreach ($champs_fichiers as $champ) {
            if (isset($_FILES[$champ]) && $_FILES[$champ]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$champ];
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $nom_fichier = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file['name']);
                $chemin_fichier = $dossier_upload . $nom_fichier;
                
                if (move_uploaded_file($file['tmp_name'], $chemin_fichier)) {
                    $fichiers_uploades[$champ] = $nom_fichier;
                }
            }
        }
        
        // Insertion dans la base de données
        $sql = "INSERT INTO demandes_etudes_espagne 
                (nom_complet, date_naissance, nationalite, email, telephone, adresse, 
                 niveau_etude, universite_souhaitee, programme_etude, type_service,
                 test_langue, score_test, demander_test, passeport, lettre_admission, photo, 
                 certificat_medical, casier_judiciaire, releve_2nde, releve_1ere, 
                 releve_terminale, releve_bac, diplome_bac, releve_l1, releve_l2, 
                 releve_l3, diplome_licence, certificat_scolarite, releve_M1, releve_M2,
                 diplome_Master, test_langue_fichier, annexe, documents_supplementaires, statut, date_soumission) 
                VALUES 
                (:nom_complet, :date_naissance, :nationalite, :email, :telephone, :adresse,
                 :niveau_etude, :universite_souhaitee, :programme_etude, :type_service,
                 :test_langue, :score_test, :demander_test, :passeport, :lettre_admission, :photo,
                 :certificat_medical, :casier_judiciaire, :releve_2nde, :releve_1ere,
                 :releve_terminale, :releve_bac, :diplome_bac, :releve_l1, :releve_l2,
                 :releve_l3, :diplome_licence, :certificat_scolarite, :releve_M1, :releve_M2,
                 :diplome_Master, :test_langue_fichier, :annexe, :documents_supplementaires, 'nouveau', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom_complet' => $nom_complet,
            ':date_naissance' => $date_naissance,
            ':nationalite' => $nationalite,
            ':email' => $email,
            ':telephone' => $telephone,
            ':adresse' => $adresse,
            ':niveau_etude' => $niveau_etude,
            ':universite_souhaitee' => $universite_souhaitee,
            ':programme_etude' => $programme_etude,
            ':type_service' => $type_service,
            ':test_langue' => $test_langue,
            ':score_test' => $score_test,
            ':demander_test' => $demander_test,
            ':passeport' => $fichiers_uploades['passeport'] ?? null,
            ':lettre_admission' => $fichiers_uploades['lettre_admission'] ?? null,
            ':photo' => $fichiers_uploades['photo'] ?? null,
            ':certificat_medical' => $fichiers_uploades['certificat_medical'] ?? null,
            ':casier_judiciaire' => $fichiers_uploades['casier_judiciaire'] ?? null,
            ':releve_2nde' => $fichiers_uploades['releve_2nde'] ?? null,
            ':releve_1ere' => $fichiers_uploades['releve_1ere'] ?? null,
            ':releve_terminale' => $fichiers_uploades['releve_terminale'] ?? null,
            ':releve_bac' => $fichiers_uploades['releve_bac'] ?? null,
            ':diplome_bac' => $fichiers_uploades['diplome_bac'] ?? null,
            ':releve_l1' => $fichiers_uploades['releve_l1'] ?? null,
            ':releve_l2' => $fichiers_uploades['releve_l2'] ?? null,
            ':releve_l3' => $fichiers_uploades['releve_l3'] ?? null,
            ':diplome_licence' => $fichiers_uploades['diplome_licence'] ?? null,
            ':certificat_scolarite' => $fichiers_uploades['certificat_scolarite'] ?? null,
            ':releve_M1' => $fichiers_uploades['releve_M1'] ?? null,
            ':releve_M2' => $fichiers_uploades['releve_M2'] ?? null,
            ':diplome_Master' => $fichiers_uploades['diplome_Master'] ?? null,
            ':test_langue_fichier' => $fichiers_uploades['test_langue_fichier'] ?? null,
            ':annexe' => $fichiers_uploades['annexe'] ?? null,
            ':documents_supplementaires' => !empty($documents_supplementaires) ? json_encode($documents_supplementaires) : null
        ]);
        
        $id_demande = $pdo->lastInsertId();
        $success_message = "Votre demande a été soumise avec succès! Numéro de référence: ES" . str_pad($id_demande, 6, '0', STR_PAD_LEFT);
        
    } catch (Exception $e) {
        $error_message = "Erreur lors de l'envoi du formulaire: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'Admission - Études en Espagne</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --spain-red: #AA151B;
            --spain-yellow: #F1BF00;
            --spain-dark: #1B1B1B;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(170, 21, 27, 0.1);
            --transition: all 0.3s ease;
            --primary-color: #AA151B;
            --secondary-color: #F1BF00;
            --accent-color: #1B1B1B;
            --light-bg: #f8fafc;
            --white: #ffffff;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --error-color: #ef4444;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            color: var(--dark-text);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
            transition: var(--transition);
        }
        
        .card:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--spain-red) 0%, #c21825 100%);
            color: var(--white);
            padding: 35px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .card-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.05"><text x="50%" y="50%" font-size="20" text-anchor="middle" dominant-baseline="middle" fill="white">🇪🇸</text></svg>');
            background-size: 100px;
            opacity: 0.1;
        }
        
        .card-header h1 {
            font-size: 2.3rem;
            margin: 0 0 12px 0;
            font-weight: 700;
        }
        
        .card-header h1 i {
            margin-right: 12px;
            color: var(--spain-yellow);
        }
        
        .card-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin: 0;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .note-info {
            background: linear-gradient(135deg, #F1BF00 0%, #f8d568 100%);
            color: var(--spain-dark);
            padding: 20px;
            margin: 20px;
            border-radius: 10px;
            border-left: 5px solid var(--spain-red);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .note-info i {
            font-size: 1.5rem;
            color: var(--spain-red);
        }
        
        .alert {
            padding: 15px;
            margin: 20px;
            border-radius: 8px;
            font-weight: 600;
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
        
        .form {
            padding: 35px;
        }
        
        .form-section {
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .form-section h2 {
            font-size: 1.5rem;
            color: var(--spain-red);
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h2 i {
            color: var(--spain-yellow);
            background: var(--spain-red);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .required {
            color: var(--error-color);
        }
        
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            font-family: inherit;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--spain-red);
            box-shadow: 0 0 0 3px rgba(170, 21, 27, 0.1);
        }
        
        .file-input-container {
            position: relative;
            margin-bottom: 8px;
        }
        
        .file-input-container input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-label {
            display: flex;
            align-items: center;
            padding: 14px 18px;
            background: var(--light-bg);
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .file-label:hover {
            border-color: var(--spain-red);
            background: rgba(170, 21, 27, 0.05);
        }
        
        .file-label.file-selected {
            border-color: var(--success-color);
            background: rgba(16, 185, 129, 0.05);
        }
        
        .file-hint {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 6px;
        }
        
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 18px;
            margin-top: 35px;
            padding-top: 25px;
            border-top: 1px solid var(--light-gray);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 14px 28px;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            border: 2px solid transparent;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn i {
            margin-right: 10px;
        }
        
        .btn-primary {
            background: var(--spain-red);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--spain-dark);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--light-bg);
            color: var(--dark-text);
            border-color: var(--border-color);
        }
        
        .btn-secondary:hover {
            background: var(--border-color);
            transform: translateY(-2px);
        }
        
        .btn-test {
            background: var(--spain-yellow);
            color: var(--spain-dark);
            border: none;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-test:hover {
            background: #e0ac00;
            transform: translateY(-2px);
        }
        
        .test-langue-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .test-options {
            margin-top: 15px;
            padding: 15px;
            background: var(--light-bg);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--spain-yellow);
        }
        
        .test-option {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .test-option input[type="radio"] {
            margin: 0;
        }
        
        .test-action {
            margin-top: 15px;
            padding: 15px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: var(--border-radius);
            border: 2px dashed var(--spain-red);
            text-align: center;
        }
        
        .doc-supp-container {
            margin-top: 20px;
        }
        
        .doc-supp-item {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
            margin-bottom: 15px;
            padding: 15px;
            background: var(--light-bg);
            border-radius: var(--border-radius);
        }
        
        .btn-remove {
            background: var(--error-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .btn-remove:hover {
            background: #dc2626;
        }
        
        .btn-add {
            background: var(--success-color);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 10px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
        }
        
        .btn-add:hover {
            background: #059669;
        }
        
        @media (max-width: 768px) {
            .test-langue-container {
                grid-template-columns: 1fr;
            }
            
            .doc-supp-item {
                grid-template-columns: 1fr;
            }
            
            .card-header h1 {
                font-size: 1.9rem;
            }
            
            .card-header p {
                font-size: 1.05rem;
            }
            
            .form {
                padding: 25px;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 12px;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .card-header {
                padding: 25px 20px;
            }
            
            .card-header h1 {
                font-size: 1.7rem;
            }
            
            .form-section h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-university"></i> Espagne - Admission Universitaire</h1>
            <p>Formulaire pour admission dans les universités espagnoles</p>
        </div>

        <div class="note-info">
            <i class="fas fa-info-circle"></i>
            <div>
                <strong>Note importante :</strong> Ce formulaire est actuellement destiné aux candidats souhaitant postuler pour un programme de Master. Pour les autres niveaux d'études, veuillez nous contacter directement.
            </div>
        </div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="#" class="form" enctype="multipart/form-data" id="espagne-form">
            <!-- Informations personnelles -->
            <div class="form-section">
                <h2><i class="fas fa-user-graduate"></i> Informations personnelles</h2>
                
                <div class="form-group">
                    <label for="nom_complet">Nom complet <span class="required">*</span></label>
                    <input type="text" id="nom_complet" name="nom_complet" required 
                           value="<?php echo htmlspecialchars($_POST['nom_complet'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="date_naissance">Date de naissance <span class="required">*</span></label>
                    <input type="date" id="date_naissance" name="date_naissance" required 
                           value="<?php echo htmlspecialchars($_POST['date_naissance'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="nationalite">Nationalité <span class="required">*</span></label>
                    <input type="text" id="nationalite" name="nationalite" required 
                           value="<?php echo htmlspecialchars($_POST['nationalite'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="telephone">Numéro de téléphone <span class="required">*</span></label>
                    <input type="tel" id="telephone" name="telephone" required 
                           value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="adresse">Adresse complète <span class="required">*</span></label>
                    <textarea id="adresse" name="adresse" required rows="3"><?php echo htmlspecialchars($_POST['adresse'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <!-- Informations académiques -->
            <div class="form-section">
                <h2><i class="fas fa-book-open"></i> Informations académiques</h2>
                
                <div class="form-group">
                    <label for="niveau_etude">Niveau d'étude actuel <span class="required">*</span></label>
                    <select id="niveau_etude" name="niveau_etude" required onchange="renderDocs()">
                        <option value="">-- Choisir votre niveau --</option>
                        <option value="bac" <?php echo ($_POST['niveau_etude'] ?? '') == 'bac' ? 'selected' : ''; ?>>Bac</option>
                        <option value="l1" <?php echo ($_POST['niveau_etude'] ?? '') == 'l1' ? 'selected' : ''; ?>>L1</option>
                        <option value="l2" <?php echo ($_POST['niveau_etude'] ?? '') == 'l2' ? 'selected' : ''; ?>>L2</option>
                        <option value="l3" <?php echo ($_POST['niveau_etude'] ?? '') == 'l3' ? 'selected' : ''; ?>>L3</option>
                        <option value="master1" <?php echo ($_POST['niveau_etude'] ?? '') == 'master1' ? 'selected' : ''; ?>>Master 1</option>
                        <option value="master2" <?php echo ($_POST['niveau_etude'] ?? '') == 'master2' ? 'selected' : ''; ?>>Master 2</option>
                        <option value="master2_termine" <?php echo ($_POST['niveau_etude'] ?? '') == 'master2_termine' ? 'selected' : ''; ?>>Master 2 terminé</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="universite_souhaitee">Université souhaitée</label>
                    <input type="text" id="universite_souhaitee" name="universite_souhaitee" 
                           value="<?php echo htmlspecialchars($_POST['universite_souhaitee'] ?? ''); ?>"
                           placeholder="Ex: Université de Barcelone, Université Complutense de Madrid...">
                </div>
                
                <div class="form-group">
                    <label for="programme_etude">Programme d'étude souhaité</label>
                    <input type="text" id="programme_etude" name="programme_etude" 
                           value="<?php echo htmlspecialchars($_POST['programme_etude'] ?? ''); ?>"
                           placeholder="Ex: Médecine, Droit, Informatique...">
                </div>
                
                <div id="docs_obligatoires" class="documents-container"></div>
            </div>
            
            <!-- Test de langue -->
            <div class="form-section">
                <h2><i class="fas fa-language"></i> Test de langue</h2>
                
                <div class="test-options">
                    <div class="test-option">
                        <input type="radio" id="test_oui" name="demander_test" value="oui" onchange="toggleTestLangue()">
                        <label for="test_oui">Oui, j'ai un test de langue</label>
                    </div>
                    <div class="test-option">
                        <input type="radio" id="test_non" name="demander_test" value="non" onchange="toggleTestLangue()">
                        <label for="test_non">Non, je n'ai pas de test de langue</label>
                    </div>
                </div>
                
                <div id="test_langue_fields" style="display: none;">
                    <div class="test-langue-container">
                        <div class="form-group">
                            <label for="test_langue">Type de test</label>
                            <select id="test_langue" name="test_langue">
                                <option value="">-- Sélectionner --</option>
                                <option value="DELE" <?php echo ($_POST['test_langue'] ?? '') == 'DELE' ? 'selected' : ''; ?>>DELE (Espagnol)</option>
                                <option value="SIELE" <?php echo ($_POST['test_langue'] ?? '') == 'SIELE' ? 'selected' : ''; ?>>SIELE (Espagnol)</option>
                                <option value="IELTS" <?php echo ($_POST['test_langue'] ?? '') == 'IELTS' ? 'selected' : ''; ?>>IELTS (Anglais)</option>
                                <option value="TOEFL" <?php echo ($_POST['test_langue'] ?? '') == 'TOEFL' ? 'selected' : ''; ?>>TOEFL (Anglais)</option>
                                <option value="autre" <?php echo ($_POST['test_langue'] ?? '') == 'autre' ? 'selected' : ''; ?>>Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="score_test">Score obtenu</label>
                            <input type="text" id="score_test" name="score_test" 
                                   value="<?php echo htmlspecialchars($_POST['score_test'] ?? ''); ?>"
                                   placeholder="Ex: B2, 6.5, 85...">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="test_langue_fichier">Certificat du test de langue</label>
                        <div class="file-input-container">
                            <input type="file" id="test_langue_fichier" name="test_langue_fichier" accept=".pdf,.jpg,.jpeg,.png">
                            <label for="test_langue_fichier" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un fichier</span>
                            </label>
                        </div>
                        <div class="file-hint">Certificat officiel du test de langue (PDF, JPG, PNG - Max. 5MB)</div>
                    </div>
                </div>
                
                <div id="test_action_container" style="display: none;">
                    <div class="test-action">
                        <p><strong>Vous n'avez pas de test de langue ?</strong></p>
                        <p>Demandez à passer un test de langue pour compléter votre dossier.</p>
                        <a href="../../test_de_langue.php" class="btn-test">
                            <i class="fas fa-clipboard-list"></i>
                            Demander un test de langue
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Documents requis -->
            <div class="form-section">
                <h2><i class="fas fa-file-upload"></i> Documents requis</h2>
                
                <div class="form-group">
                    <label for="passeport">Passeport <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="passeport" name="passeport" required accept=".pdf,.jpg,.jpeg,.png">
                        <label for="passeport" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Passeport valide (pages avec photo et informations) (PDF, JPG, PNG - Max. 5MB)</div>
                </div>
                
                <div class="form-group">
                    <label for="photo">Photo d'identité <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="photo" name="photo" required accept=".jpg,.jpeg,.png">
                        <label for="photo" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Photo format passeport (JPG, PNG - Max. 5MB)</div>
                </div>
                
                <div class="form-group">
                    <label for="annexe">Annexe <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="annexe" name="annexe" required accept=".pdf,.jpg,.jpeg,.png">
                        <label for="annexe" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Document annexe obligatoire (PDF, JPG, PNG - Max. 5MB)</div>
                </div>
                
                <!-- Documents supplémentaires -->
                <div class="doc-supp-container">
                    <h3 style="color: var(--spain-red); margin-bottom: 15px;">Documents supplémentaires</h3>
                    <div id="documents-supplementaires">
                        <!-- Les documents supplémentaires seront ajoutés ici dynamiquement -->
                    </div>
                    <button type="button" class="btn-add" onclick="ajouterDocument()">
                        <i class="fas fa-plus"></i> Ajouter un document
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" name="action" value="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Soumettre la demande
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let docSuppCounter = 0;
    
    document.addEventListener('DOMContentLoaded', function() {
        // File input labels
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.nextElementSibling;
                const fileText = label.querySelector('.file-text');
                
                if (this.files.length > 0) {
                    fileText.textContent = this.files[0].name;
                    label.classList.add('file-selected');
                } else {
                    fileText.textContent = 'Choisir un fichier';
                    label.classList.remove('file-selected');
                }
            });
        });
        
        // Initialiser les documents au chargement
        renderDocs();
        
        // Initialiser l'état du test de langue
        toggleTestLangue();
    });
    
    // Fonction pour afficher/masquer les champs de test de langue
    function toggleTestLangue() {
        const testOui = document.getElementById('test_oui');
        const testNon = document.getElementById('test_non');
        const testFields = document.getElementById('test_langue_fields');
        const testAction = document.getElementById('test_action_container');
        
        if (testOui.checked) {
            testFields.style.display = 'block';
            testAction.style.display = 'none';
        } else if (testNon.checked) {
            testFields.style.display = 'none';
            testAction.style.display = 'block';
            // Réinitialiser les champs de test
            document.getElementById('test_langue').value = '';
            document.getElementById('score_test').value = '';
            document.getElementById('test_langue_fichier').value = '';
        } else {
            testFields.style.display = 'none';
            testAction.style.display = 'none';
        }
    }
    
    // Configuration des documents par niveau
    const configDocs = {
        bac: [
            { label: "Relevé de notes 1ère année", name: "releve_2nde", required: true },
            { label: "Relevé de notes 2ème année", name: "releve_1ere", required: true },
            { label: "Relevé de notes Terminale", name: "releve_terminale", required: true },
            { label: "Relevé de notes Bac", name: "releve_bac", required: true },
            { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", required: true }
        ],
        l1: [
            { label: "Relevé de notes 1ère année", name: "releve_2nde", required: true },
            { label: "Relevé de notes 2ème année", name: "releve_1ere", required: true },
            { label: "Relevé de notes Terminale", name: "releve_terminale", required: true },
            { label: "Relevé de notes Bac", name: "releve_bac", required: true },
            { label: "Diplôme Bac", name: "diplome_bac", required: true },
            { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", required: true }
        ],
        l2: [
            { label: "Relevé de notes Bac", name: "releve_bac", required: true },
            { label: "Diplôme Bac", name: "diplome_bac", required: true },
            { label: "Relevé de notes L1", name: "releve_l1", required: true },
            { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", required: true }
        ],
        l3: [
            { label: "Relevé de notes Bac", name: "releve_bac", required: true },
            { label: "Diplôme Bac", name: "diplome_bac", required: true },
            { label: "Relevé de notes L1", name: "releve_l1", required: true },
            { label: "Relevé de notes L2", name: "releve_l2", required: true },
            { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", required: true }
        ],
        master1: [
            { label: "Relevé de notes Bac", name: "releve_bac", required: true },
            { label: "Diplôme Bac", name: "diplome_bac", required: true },
            { label: "Relevé de notes L1", name: "releve_l1", required: true },
            { label: "Relevé de notes L2", name: "releve_l2", required: true },
            { label: "Relevé de notes L3", name: "releve_l3", required: true },
            { label: "Diplôme Licence", name: "diplome_licence", required: true },
            { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", required: true }
        ],
        master2: [
            { label: "Relevé de notes Bac", name: "releve_bac", required: true },
            { label: "Diplôme Bac", name: "diplome_bac", required: true },
            { label: "Relevé de notes L1", name: "releve_l1", required: true },
            { label: "Relevé de notes L2", name: "releve_l2", required: true },
            { label: "Relevé de notes L3", name: "releve_l3", required: true },
            { label: "Diplôme Licence", name: "diplome_licence", required: true },
            { label: "Relevé de notes M1", name: "releve_M1", required: true },
            { label: "Certificat de scolarité (année en cours)", name: "certificat_scolarite", required: true }
        ],
        master2_termine: [
            { label: "Relevé de notes Bac", name: "releve_bac", required: true },
            { label: "Diplôme Bac", name: "diplome_bac", required: true },
            { label: "Relevé de notes L1", name: "releve_l1", required: true },
            { label: "Relevé de notes L2", name: "releve_l2", required: true },
            { label: "Relevé de notes L3", name: "releve_l3", required: true },
            { label: "Diplôme Licence", name: "diplome_licence", required: true },
            { label: "Relevé de notes M1", name: "releve_M1", required: true },
            { label: "Relevé de notes M2", name: "releve_M2", required: true },
            { label: "Diplôme Master", name: "diplome_Master", required: true }
        ]
    };
    
    // Fonction pour afficher les champs selon le niveau choisi
    function renderDocs() {
        const niveau = document.getElementById('niveau_etude').value;
        const docsContainer = document.getElementById('docs_obligatoires');
        docsContainer.innerHTML = '';

        if (configDocs[niveau]) {
            const title = document.createElement('h3');
            title.textContent = 'Documents académiques requis';
            title.style.color = 'var(--spain-red)';
            title.style.marginBottom = '20px';
            docsContainer.appendChild(title);
            
            configDocs[niveau].forEach(doc => {
                const docElement = document.createElement('div');
                docElement.className = 'form-group';
                docElement.innerHTML = `
                    <label>${doc.label} ${doc.required ? '<span class="required">*</span>' : ''}</label>
                    <div class="file-input-container">
                        <input type="file" name="${doc.name}" ${doc.required ? 'required' : ''} accept=".pdf,.jpg,.jpeg,.png">
                        <label class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Formats acceptés: PDF, JPG, PNG (Max 5MB)</div>
                `;
                docsContainer.appendChild(docElement);
                
                // Add event listener for file input
                const fileInput = docElement.querySelector('input[type="file"]');
                fileInput.addEventListener('change', function() {
                    const label = this.nextElementSibling;
                    const fileText = label.querySelector('.file-text');
                    
                    if (this.files.length > 0) {
                        fileText.textContent = this.files[0].name;
                        label.classList.add('file-selected');
                    } else {
                        fileText.textContent = 'Choisir un fichier';
                        label.classList.remove('file-selected');
                    }
                });
            });
        }
    }
    
    // Fonction pour ajouter un document supplémentaire
    function ajouterDocument() {
        docSuppCounter++;
        const container = document.getElementById('documents-supplementaires');
        const docElement = document.createElement('div');
        docElement.className = 'doc-supp-item';
        docElement.innerHTML = `
            <div class="form-group">
                <label>Nom du document</label>
                <input type="text" name="doc_supp_nom[]" placeholder="Ex: Lettre de motivation, CV, Portfolio...">
            </div>
            <div class="form-group">
                <label>Fichier</label>
                <div class="file-input-container">
                    <input type="file" name="doc_supp_fichier[]" accept=".pdf,.jpg,.jpeg,.png">
                    <label class="file-label">
                        <i class="fas fa-upload"></i>
                        <span class="file-text">Choisir un fichier</span>
                    </label>
                </div>
            </div>
            <button type="button" class="btn-remove" onclick="supprimerDocument(this)">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(docElement);
        
        // Add event listener for file input
        const fileInput = docElement.querySelector('input[type="file"]');
        fileInput.addEventListener('change', function() {
            const label = this.nextElementSibling;
            const fileText = label.querySelector('.file-text');
            
            if (this.files.length > 0) {
                fileText.textContent = this.files[0].name;
                label.classList.add('file-selected');
            } else {
                fileText.textContent = 'Choisir un fichier';
                label.classList.remove('file-selected');
            }
        });
    }
    
    // Fonction pour supprimer un document supplémentaire
    function supprimerDocument(button) {
        button.closest('.doc-supp-item').remove();
    }
</script>
</body>
</html>