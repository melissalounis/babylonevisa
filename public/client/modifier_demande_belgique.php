<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Vérifier si un ID de demande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID de demande manquant.";
    header("Location: mes_demandes_belgique.php");
    exit;
}

$demande_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// Paramètres de connexion

require_once __DIR__ . '../../../config.php';
// Variables pour stocker les messages
$success_message = '';
$error_message = '';

try {
    

    // Récupérer la demande spécifique de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT * FROM demandes_belgique 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$demande_id, $user_id]);
    $demande = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier si la demande existe et appartient à l'utilisateur
    if (!$demande) {
        $_SESSION['error'] = "Demande non trouvée ou vous n'avez pas l'autorisation de la modifier.";
        header("Location: mes_demandes_belgique.php");
        exit;
    }

    // Vérifier si la demande peut être modifiée (seulement si statut "en_attente")
    if ($demande['statut'] != 'en_attente') {
        $_SESSION['error'] = "Cette demande ne peut plus être modifiée car son statut est : " . $demande['statut'];
        header("Location: mes_demandes_belgique.php");
        exit;
    }

    // Récupérer les fichiers existants et les organiser par type
    $stmt_files = $pdo->prepare("
        SELECT * FROM demandes_belgique_fichiers 
        WHERE demande_id = ?
    ");
    $stmt_files->execute([$demande_id]);
    $fichiers_existants = $stmt_files->fetchAll(PDO::FETCH_ASSOC);
    
    // Organiser les fichiers par type pour un accès facile
    $fichiers_par_type = [];
    foreach ($fichiers_existants as $fichier) {
        $fichiers_par_type[$fichier['type_fichier']] = $fichier;
    }

} catch (PDOException $e) {
    die("Erreur BDD : " . $e->getMessage());
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Récupération des données du formulaire
        $nom = trim($_POST['nom']);
        $email = trim($_POST['email']);
        $nationalite = trim($_POST['nationalite']);
        $niveau_etude = $_POST['niveau_etude'];
        $naissance = !empty($_POST['naissance']) ? $_POST['naissance'] : null;

        // Validation
        $errors = [];

        if (empty($nom)) {
            $errors[] = "Le nom complet est obligatoire.";
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'adresse email est invalide.";
        }

        if (empty($nationalite)) {
            $errors[] = "La nationalité est obligatoire.";
        }

        if (empty($niveau_etude)) {
            $errors[] = "Le niveau d'étude est obligatoire.";
        }

        // Si pas d'erreurs, procéder à la mise à jour
        if (empty($errors)) {
            // Mettre à jour les informations de base
            $stmt_update = $pdo->prepare("
                UPDATE demandes_belgique 
                SET nom = ?, email = ?, nationalite = ?, niveau_etude = ?, naissance = ?, date_modification = NOW()
                WHERE id = ? AND user_id = ?
            ");
            $stmt_update->execute([$nom, $email, $nationalite, $niveau_etude, $naissance, $demande_id, $user_id]);

            // Gestion des nouveaux fichiers uploadés
            $upload_dir = '../../../uploads/demandes_belgique/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Types de fichiers autorisés
            $allowed_types = [
                'lettre_admission', 'photo', 'certificat_medical', 'casier_judiciaire',
                'releve_2nde', 'releve_1ere', 'releve_terminale', 'releve_bac', 'diplome_bac',
                'releve_l1', 'releve_l2', 'releve_l3', 'diplome_licence', 'certificat_scolarite'
            ];

            foreach ($allowed_types as $type_fichier) {
                if (isset($_FILES[$type_fichier]) && $_FILES[$type_fichier]['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES[$type_fichier];
                    
                    // Validation du type de fichier
                    $allowed_mimes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                    if (!in_array($file['type'], $allowed_mimes)) {
                        $errors[] = "Le fichier $type_fichier doit être au format PDF, JPEG ou PNG.";
                        continue;
                    }

                    // Validation de la taille (max 5MB)
                    if ($file['size'] > 5 * 1024 * 1024) {
                        $errors[] = "Le fichier $type_fichier ne doit pas dépasser 5MB.";
                        continue;
                    }

                    // Génération d'un nom de fichier unique
                    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $new_filename = 'belgique_' . $demande_id . '_' . $type_fichier . '_' . time() . '.' . $file_extension;
                    $file_path = $upload_dir . $new_filename;

                    if (move_uploaded_file($file['tmp_name'], $file_path)) {
                        // Vérifier si un fichier de ce type existe déjà
                        if (isset($fichiers_par_type[$type_fichier])) {
                            // Mettre à jour le fichier existant
                            $stmt_update_file = $pdo->prepare("
                                UPDATE demandes_belgique_fichiers 
                                SET nom_fichier = ?, chemin_fichier = ?, date_upload = NOW()
                                WHERE id = ?
                            ");
                            $stmt_update_file->execute([$file['name'], $file_path, $fichiers_par_type[$type_fichier]['id']]);

                            // Supprimer l'ancien fichier physique
                            if (file_exists($fichiers_par_type[$type_fichier]['chemin_fichier'])) {
                                unlink($fichiers_par_type[$type_fichier]['chemin_fichier']);
                            }
                            
                            // Mettre à jour le tableau local
                            $fichiers_par_type[$type_fichier]['nom_fichier'] = $file['name'];
                            $fichiers_par_type[$type_fichier]['chemin_fichier'] = $file_path;
                        } else {
                            // Insérer un nouveau fichier
                            $stmt_insert_file = $pdo->prepare("
                                INSERT INTO demandes_belgique_fichiers (demande_id, type_fichier, nom_fichier, chemin_fichier, date_upload)
                                VALUES (?, ?, ?, ?, NOW())
                            ");
                            $stmt_insert_file->execute([$demande_id, $type_fichier, $file['name'], $file_path]);
                            
                            // Mettre à jour le tableau local
                            $fichiers_par_type[$type_fichier] = [
                                'id' => $pdo->lastInsertId(),
                                'nom_fichier' => $file['name'],
                                'chemin_fichier' => $file_path,
                                'date_upload' => date('Y-m-d H:i:s')
                            ];
                        }
                    } else {
                        $errors[] = "Erreur lors de l'upload du fichier $type_fichier.";
                    }
                }
            }

            if (empty($errors)) {
                $_SESSION['success'] = "La demande a été modifiée avec succès.";
                header("Location: mes_demandes_belgique.php");
                exit;
            } else {
                $error_message = implode('<br>', $errors);
            }
        } else {
            $error_message = implode('<br>', $errors);
        }

    } catch (PDOException $e) {
        $error_message = "Erreur lors de la modification : " . $e->getMessage();
    }
}

// Si méthode GET ou POST avec erreurs, afficher le formulaire
// Les données du formulaire seront soit celles de la demande, soit celles POSTées
$form_data = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $demande;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Demande Belgique - Babylone Service</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --belgique-black: #070988;
            --belgique-yellow: #FDDA24;
            --belgique-red: #EF3340;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .header {
            background: linear-gradient(135deg, var(--belgique-black), #2c2c2c);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--belgique-black);
            border-color: var(--belgique-black);
        }
        
        .btn-primary:hover {
            background-color: var(--belgique-red);
            border-color: var(--belgique-red);
        }
        
        .file-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
        }
        
        .file-info a {
            color: var(--belgique-black);
            text-decoration: none;
        }
        
        .file-info a:hover {
            color: var(--belgique-red);
        }
        
        .required {
            color: #dc3545;
        }
        
        .section-title {
            border-bottom: 2px solid var(--belgique-yellow);
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: var(--belgique-black);
        }
        
        .no-file {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        
        .demande-info {
            background: linear-gradient(135deg, var(--belgique-yellow), #ffc107);
            color: var(--belgique-black);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-edit"></i> Modifier la Demande Belgique</h1>
                    <p class="lead mb-0">Mettez à jour les informations de votre candidature</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="mes_demandes_belgique.php" class="btn btn-light btn-lg">
                        <i class="fas fa-arrow-left"></i> Retour aux demandes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Informations de la demande -->
        <div class="demande-info">
            <div class="row">
                <div class="col-md-4">
                    <strong>Référence :</strong> #<?php echo $demande['id']; ?>
                </div>
                <div class="col-md-4">
                    <strong>Statut :</strong> 
                    <span class="badge bg-warning"><?php echo ucfirst($demande['statut']); ?></span>
                </div>
                <div class="col-md-4">
                    <strong>Date soumission :</strong> <?php echo date('d/m/Y à H:i', strtotime($demande['date_soumission'])); ?>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li class="breadcrumb-item"><a href="mes_demandes_belgique.php">Mes Demandes Belgique</a></li>
                <li class="breadcrumb-item active">Modifier #<?php echo $demande['id']; ?></li>
            </ol>
        </nav>

        <!-- Messages d'erreur -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle"></i> Erreurs :</h5>
                <ul class="mb-0">
                    <?php 
                    if (is_string($error_message)) {
                        echo "<li>$error_message</li>";
                    } else {
                        foreach ($error_message as $error) {
                            echo "<li>$error</li>";
                        }
                    }
                    ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Formulaire de modification -->
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <!-- Informations personnelles -->
                    <h4 class="section-title">
                        <i class="fas fa-user"></i> Informations Personnelles
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom" class="form-label">
                                Nom complet <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="nom" name="nom" 
                                   value="<?php echo htmlspecialchars($form_data['nom']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                Email <span class="required">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nationalite" class="form-label">
                                Nationalité <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="nationalite" name="nationalite" 
                                   value="<?php echo htmlspecialchars($form_data['nationalite']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="naissance" class="form-label">Date de naissance</label>
                            <input type="date" class="form-control" id="naissance" name="naissance" 
                                   value="<?php echo !empty($form_data['naissance']) ? $form_data['naissance'] : ''; ?>">
                        </div>
                    </div>

                    <!-- Niveau d'études -->
                    <h4 class="section-title mt-5">
                        <i class="fas fa-graduation-cap"></i> Informations Académiques
                    </h4>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="niveau_etude" class="form-label">
                                Niveau d'études <span class="required">*</span>
                            </label>
                            <select class="form-control" id="niveau_etude" name="niveau_etude" required>
                                <option value="">Sélectionnez votre niveau</option>
                                <option value="bac" <?php echo ($form_data['niveau_etude'] ?? '') == 'bac' ? 'selected' : ''; ?>>Bac</option>
                                <option value="l1" <?php echo ($form_data['niveau_etude'] ?? '') == 'l1' ? 'selected' : ''; ?>>Licence 1</option>
                                <option value="l2" <?php echo ($form_data['niveau_etude'] ?? '') == 'l2' ? 'selected' : ''; ?>>Licence 2</option>
                                <option value="l3" <?php echo ($form_data['niveau_etude'] ?? '') == 'l3' ? 'selected' : ''; ?>>Licence 3</option>
                                <option value="master" <?php echo ($form_data['niveau_etude'] ?? '') == 'master' ? 'selected' : ''; ?>>Master</option>
                            </select>
                        </div>
                    </div>

                    <!-- Documents -->
                    <h4 class="section-title mt-5">
                        <i class="fas fa-file-upload"></i> Documents
                    </h4>
                    <p class="text-muted mb-4">
                        <small>
                            <i class="fas fa-info-circle"></i> 
                            Vous pouvez remplacer les documents existants. Laissez les champs vides pour conserver les fichiers actuels.
                            Formats acceptés : PDF, JPG, PNG (max 5MB par fichier)
                        </small>
                    </p>

                    <div class="row">
                        <?php
                        $types_fichiers = [
                            'lettre_admission' => 'Lettre d\'admission',
                            'photo' => 'Photo d\'identité',
                            'certificat_medical' => 'Certificat médical',
                            'casier_judiciaire' => 'Casier judiciaire',
                            'releve_2nde' => 'Relevé de notes 2nde',
                            'releve_1ere' => 'Relevé de notes 1ère',
                            'releve_terminale' => 'Relevé de notes Terminale',
                            'releve_bac' => 'Relevé de notes Bac',
                            'diplome_bac' => 'Diplôme Bac',
                            'releve_l1' => 'Relevé de notes Licence 1',
                            'releve_l2' => 'Relevé de notes Licence 2',
                            'releve_l3' => 'Relevé de notes Licence 3',
                            'diplome_licence' => 'Diplôme Licence',
                            'certificat_scolarite' => 'Certificat de scolarité'
                        ];
                        
                        foreach ($types_fichiers as $type => $label):
                            $fichier_existant = $fichiers_par_type[$type] ?? null;
                        ?>
                            <div class="col-md-6 mb-3">
                                <label for="<?php echo $type; ?>" class="form-label">
                                    <?php echo $label; ?>
                                </label>
                                
                                <?php if ($fichier_existant && !empty($fichier_existant['nom_fichier'])): ?>
                                    <div class="file-info mb-2">
                                        <i class="fas fa-file-pdf text-danger"></i>
                                        Fichier actuel : 
                                        <a href="../../../uploads/demandes_belgique/<?php echo basename($fichier_existant['chemin_fichier']); ?>" target="_blank">
                                            <?php echo htmlspecialchars($fichier_existant['nom_fichier']); ?>
                                        </a>
                                        <br>
                                        <small class="text-muted">
                                            Uploadé le : <?php echo date('d/m/Y à H:i', strtotime($fichier_existant['date_upload'])); ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div class="file-info no-file mb-2">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Aucun fichier déposé
                                    </div>
                                <?php endif; ?>
                                
                                <input type="file" class="form-control" id="<?php echo $type; ?>" 
                                       name="<?php echo $type; ?>" accept=".pdf,.jpg,.jpeg,.png">
                                <div class="form-text">Laissez vide pour conserver le fichier actuel</div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Actions -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="mes_demandes_belgique.php" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Enregistrer les modifications
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Avertissement avant de quitter la page si des modifications ont été faites
        let formModified = false;
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input, select, textarea');
        
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                formModified = true;
            });
        });
        
        window.addEventListener('beforeunload', function(e) {
            if (formModified) {
                e.preventDefault();
                e.returnValue = 'Vous avez des modifications non enregistrées. Êtes-vous sûr de vouloir quitter ?';
            }
        });
        
        // Réinitialiser le drapeau de modification quand le formulaire est soumis
        form.addEventListener('submit', function() {
            formModified = false;
        });

        // Affichage du nom du fichier sélectionné
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const fileName = this.files[0]?.name;
                if (fileName) {
                    // Créer ou mettre à jour l'affichage du nom de fichier
                    let fileDisplay = this.nextElementSibling;
                    if (!fileDisplay || !fileDisplay.classList.contains('form-text')) {
                        fileDisplay = this.parentNode.querySelector('.file-display');
                        if (!fileDisplay) {
                            fileDisplay = document.createElement('div');
                            fileDisplay.className = 'file-display mt-2';
                            this.parentNode.appendChild(fileDisplay);
                        }
                    }
                    fileDisplay.innerHTML = `<div class="alert alert-success py-2"><i class="fas fa-check"></i> Fichier sélectionné : <strong>${fileName}</strong></div>`;
                }
            });
        });
    </script>
</body>
</html>