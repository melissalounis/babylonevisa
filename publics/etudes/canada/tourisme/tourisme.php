<?php
// demande_visa_court_sejour.php
session_start();

// Configuration
$config = [
    'database' => [
        'host' => 'localhost',
        'dbname' => 'babylone_service',
        'username' => 'root',
        'password' => ''
    ],
    'upload' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],
        'upload_dir' => 'documents/'
    ]
];

// Fonctions utilitaires
function sanitizeInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function validateRequired($fields) {
    foreach ($fields as $field => $value) {
        if (empty(trim($value))) {
            throw new Exception("Le champ $field est requis");
        }
    }
    return true;
}

function handleError($message) {
    error_log("VISA_ERROR: " . $message);
    return $message;
}

// Connexion à la base MySQL
try {
    $pdo = new PDO(
        "mysql:host={$config['database']['host']};dbname={$config['database']['dbname']};charset=utf8",
        $config['database']['username'],
        $config['database']['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Validation des champs requis
    $required_fields = [
        'nom_famille', 'prenoms', 'date_naissance', 'lieu_naissance', 'pays_naissance',
        'nationalite', 'sexe', 'statut_matrimonial', 'adresse', 'ville', 'pays_residence',
        'telephone', 'email', 'passeport_numero', 'passeport_pays', 'passeport_date_emission',
        'passeport_date_expiration', 'but_visite', 'date_arrivee', 'date_depart',
        'hebergement_type'
    ];
    
    $valid = true;
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $valid = false;
            $erreur = "Veuillez remplir tous les champs obligatoires.";
            break;
        }
    }
    
    if ($valid) {
        try {
            // Génération du numéro de dossier
            $numero_dossier = "VCS-" . date('Ymd') . "-" . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            
            // Gestion des fichiers uploadés
            $documents = [];
            $upload_dir = $config['upload']['upload_dir'];
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Liste des documents à uploader
            $documents_list = [
                'passeport' => 'Passeport',
                'photo_identite' => 'Photo d\'identité',
                'justificatif_fonds' => 'Justificatif de fonds',
                'reservation_vol' => 'Réservation de vol',
                'hebergement_preuve' => 'Preuve d\'hébergement',
                'documents_conjoint' => 'Documents conjoint',
                'documents_enfants' => 'Documents enfants',
                'autres_documents' => 'Autres documents'
            ];
            
            foreach ($documents_list as $doc_key => $doc_name) {
                if (isset($_FILES[$doc_key]) && $_FILES[$doc_key]['error'] === UPLOAD_ERR_OK) {
                    $file_extension = pathinfo($_FILES[$doc_key]['name'], PATHINFO_EXTENSION);
                    $file_name = $numero_dossier . '_' . $doc_key . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES[$doc_key]['tmp_name'], $file_path)) {
                        $documents[$doc_key] = $file_path;
                    }
                }
            }
            
            // Récupération des données des enfants
            $enfants_informations = "";
            if (isset($_POST['enfants']) && is_array($_POST['enfants'])) {
                $enfants_data = [];
                foreach ($_POST['enfants'] as $enfant) {
                    if (!empty($enfant['prenoms'])) {
                        $enfants_data[] = implode('|', [
                            $enfant['prenoms'] ?? '',
                            $enfant['nom_famille'] ?? '',
                            $enfant['date_naissance'] ?? '',
                            $enfant['lieu_naissance'] ?? '',
                            $enfant['nationalite'] ?? '',
                            $enfant['niveau_etudes'] ?? '',
                            $enfant['etablissement'] ?? ''
                        ]);
                    }
                }
                $enfants_informations = implode(';', $enfants_data);
            }
            
            // CORRECTION : Préparation de la requête avec exactement 62 paramètres
            $sql = "INSERT INTO demande_visa_court_sejour (
                nom_famille, prenoms, date_naissance, lieu_naissance, pays_naissance,
                nationalite, sexe, statut_matrimonial, adresse, ville, code_postal, pays_residence,
                telephone, email, passeport_numero, passeport_pays, passeport_date_emission,
                passeport_date_expiration, but_visite, date_arrivee, date_depart, duree_sejour,
                hebergement_type, hebergement_adresse, personne_contact_nom,
                personne_contact_telephone, personne_contact_relation, employeur_nom, employeur_adresse,
                employeur_telephone, profession, situation_professionnelle, refus_visa_precedent,
                details_refus, maladies_graves, details_maladies, condamnations_judiciaires,
                details_condamnations, service_militaire, details_service_militaire,
                documents_passeport, documents_photo, documents_fonds, documents_vol,
                documents_hebergement, documents_conjoint, documents_enfants, documents_autres,
                conjoint_nom, conjoint_prenoms, conjoint_date_naissance, conjoint_lieu_naissance,
                conjoint_nationalite, conjoint_profession, conjoint_employeur, conjoint_experience_professionnelle,
                conjoint_niveau_etudes, conjoint_etablissement_etudes, enfants_informations,
                numero_dossier, date_soumission, statut
            ) VALUES (" . str_repeat('?,', 61) . "?)";
            
            $stmt = $pdo->prepare($sql);
            
            // CORRECTION : Tableau avec exactement 62 valeurs dans le bon ordre
            $params = [
                // Informations personnelles (12)
                sanitizeInput($_POST["nom_famille"]),
                sanitizeInput($_POST["prenoms"]),
                $_POST["date_naissance"],
                sanitizeInput($_POST["lieu_naissance"]),
                sanitizeInput($_POST["pays_naissance"]),
                sanitizeInput($_POST["nationalite"]),
                $_POST["sexe"],
                $_POST["statut_matrimonial"],
                sanitizeInput($_POST["adresse"]),
                sanitizeInput($_POST["ville"]),
                sanitizeInput($_POST["code_postal"] ?? ''),
                sanitizeInput($_POST["pays_residence"]),
                
                // Coordonnées (2)
                sanitizeInput($_POST["telephone"]),
                sanitizeInput($_POST["email"]),
                
                // Passeport (4)
                sanitizeInput($_POST["passeport_numero"]),
                sanitizeInput($_POST["passeport_pays"]),
                $_POST["passeport_date_emission"],
                $_POST["passeport_date_expiration"],
                
                // Séjour au Canada (4)
                $_POST["but_visite"],
                $_POST["date_arrivee"],
                $_POST["date_depart"],
                sanitizeInput($_POST["duree_sejour"] ?? ''),
                
                // Hébergement (5)
                $_POST["hebergement_type"],
                sanitizeInput($_POST["hebergement_adresse"] ?? ''),
                sanitizeInput($_POST["personne_contact_nom"] ?? ''),
                sanitizeInput($_POST["personne_contact_telephone"] ?? ''),
                sanitizeInput($_POST["personne_contact_relation"] ?? ''),
                
                // Situation professionnelle (5)
                sanitizeInput($_POST["employeur_nom"] ?? ''),
                sanitizeInput($_POST["employeur_adresse"] ?? ''),
                sanitizeInput($_POST["employeur_telephone"] ?? ''),
                sanitizeInput($_POST["profession"] ?? ''),
                $_POST["situation_professionnelle"] ?? '',
                
                // Historique (8)
                $_POST["refus_visa_precedent"] ?? '',
                sanitizeInput($_POST["details_refus"] ?? ''),
                $_POST["maladies_graves"] ?? '',
                sanitizeInput($_POST["details_maladies"] ?? ''),
                $_POST["condamnations_judiciaires"] ?? '',
                sanitizeInput($_POST["details_condamnations"] ?? ''),
                $_POST["service_militaire"] ?? '',
                sanitizeInput($_POST["details_service_militaire"] ?? ''),
                
                // Documents (8)
                $documents['passeport'] ?? '',
                $documents['photo_identite'] ?? '',
                $documents['justificatif_fonds'] ?? '',
                $documents['reservation_vol'] ?? '',
                $documents['hebergement_preuve'] ?? '',
                $documents['documents_conjoint'] ?? '',
                $documents['documents_enfants'] ?? '',
                $documents['autres_documents'] ?? '',
                
                // Informations conjoint (10)
                sanitizeInput($_POST["conjoint_nom"] ?? ''),
                sanitizeInput($_POST["conjoint_prenoms"] ?? ''),
                $_POST["conjoint_date_naissance"] ?? '',
                sanitizeInput($_POST["conjoint_lieu_naissance"] ?? ''),
                sanitizeInput($_POST["conjoint_nationalite"] ?? ''),
                sanitizeInput($_POST["conjoint_profession"] ?? ''),
                sanitizeInput($_POST["conjoint_employeur"] ?? ''),
                sanitizeInput($_POST["conjoint_experience_professionnelle"] ?? ''),
                sanitizeInput($_POST["conjoint_niveau_etudes"] ?? ''),
                sanitizeInput($_POST["conjoint_etablissement_etudes"] ?? ''),
                
                // Informations enfants (1)
                $enfants_informations,
                
                // Métadonnées (3)
                $numero_dossier,
                date('Y-m-d H:i:s'),
                'en_attente'
            ];
            
            // Vérification finale du nombre de paramètres
            $expected_count = 62;
            $actual_count = count($params);
            
            if ($actual_count !== $expected_count) {
                throw new Exception("Nombre de paramètres incorrect: $actual_count au lieu de $expected_count. Vérifiez l'ordre des champs dans la requête SQL.");
            }
            
            // Exécution de la requête
            $stmt->execute($params);
            
            $confirmation = true;
            $id_dossier = $pdo->lastInsertId();
            
        } catch (PDOException $e) {
            $erreur = "Erreur lors de l'enregistrement dans la base de données : " . $e->getMessage();
            error_log("Erreur SQL: " . $e->getMessage());
        } catch (Exception $e) {
            $erreur = "Erreur: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Visa Court Séjour Canada</title>
    <style>
        :root {
            --primary: #dc2626;
            --primary-dark: #b91c1c;
            --secondary: #64748b;
            --success: #059669;
            --error: #dc2626;
            --warning: #d97706;
            --background: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
            --text-light: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
            --radius: 8px;
            --transition: all 0.2s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 40px;
            border-radius: var(--radius) var(--radius) 0 0;
            text-align: center;
        }

        .header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .form-card {
            background: var(--surface);
            border-radius: 0 0 var(--radius) var(--radius);
            box-shadow: var(--shadow-lg);
            padding: 0;
        }

        .form-section {
            padding: 32px;
            border-bottom: 1px solid var(--border);
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title i {
            background: var(--primary);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: 500;
            color: var(--text);
            font-size: 0.9rem;
        }

        .required::after {
            content: " *";
            color: var(--error);
        }

        input, select, textarea {
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: var(--radius);
            font-size: 1rem;
            font-family: inherit;
            transition: var(--transition);
            background: var(--surface);
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        .file-upload-container {
            border: 2px dashed var(--border);
            border-radius: var(--radius);
            padding: 24px;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            background: #f8fafc;
        }

        .file-upload-container:hover {
            border-color: var(--primary);
            background: #f0f9ff;
        }

        .file-upload-container.dragover {
            border-color: var(--primary);
            background: #dbeafe;
        }

        .file-input {
            display: none;
        }

        .file-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .file-label i {
            font-size: 2rem;
            color: var(--secondary);
        }

        .file-info {
            font-size: 0.9rem;
            color: var(--text-light);
            margin-top: 8px;
        }

        .file-preview {
            margin-top: 12px;
            padding: 12px;
            background: #f1f5f9;
            border-radius: var(--radius);
            font-size: 0.85rem;
        }

        .btn {
            padding: 14px 32px;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--border);
            color: var(--text);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9rem;
        }

        .alert {
            padding: 20px;
            border-radius: var(--radius);
            margin: 20px;
            border-left: 4px solid;
        }

        .alert-success {
            background: #f0fdf4;
            border-color: var(--success);
            color: #065f46;
        }

        .alert-error {
            background: #fef2f2;
            border-color: var(--error);
            color: #991b1b;
        }

        .dossier-info {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1px solid #bae6fd;
            border-radius: var(--radius);
            padding: 24px;
            margin: 20px;
        }

        .dossier-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .form-actions {
            display: flex;
            gap: 16px;
            justify-content: flex-end;
            padding: 32px;
            background: #f8fafc;
            border-top: 1px solid var(--border);
        }

        .question-group {
            background: #f8fafc;
            padding: 20px;
            border-radius: var(--radius);
            margin-bottom: 16px;
            border-left: 4px solid var(--primary);
        }

        .question-text {
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text);
        }

        .radio-group {
            display: flex;
            gap: 24px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .conditional-field {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
            display: none;
        }

        .family-section {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 20px;
        }

        .family-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .family-title {
            font-weight: 600;
            color: var(--primary);
        }

        .enfant-item {
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 16px;
            margin-bottom: 12px;
        }

        .enfant-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .enfant-title {
            font-weight: 600;
            color: var(--text);
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 10px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .header {
                padding: 24px;
            }

            .header h1 {
                font-size: 1.75rem;
            }

            .form-section {
                padding: 24px;
            }

            .form-actions {
                flex-direction: column;
            }

            .radio-group {
                flex-direction: column;
                gap: 12px;
            }
        }

        select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
            padding-right: 40px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-passport"></i> Demande de Visa Court Séjour Canada</h1>
            <p>Formulaire de demande de visa de visiteur temporaire</p>
        </div>

        <div class="form-card">
            <?php if (isset($confirmation) && $confirmation): ?>
                <div class="alert alert-success">
                    <h3><i class="fas fa-check-circle"></i> Demande soumise avec succès</h3>
                    <div class="dossier-info">
                        <div class="dossier-number"><?= $numero_dossier ?></div>
                        <p>Votre demande de visa court séjour a été enregistrée. Vous recevrez un email de confirmation sous peu.</p>
                    </div>
                </div>
            <?php elseif (isset($erreur)): ?>
                <div class="alert alert-error">
                    <h3><i class="fas fa-exclamation-triangle"></i> Erreur</h3>
                    <p><?= $erreur ?></p>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
                <!-- Informations personnelles -->
                <div class="form-section">
                    <h2 class="section-title"><i class="fas fa-user"></i> Informations Personnelles</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom_famille" class="required">Nom de famille</label>
                            <input type="text" id="nom_famille" name="nom_famille" required>
                        </div>
                        <div class="form-group">
                            <label for="prenoms" class="required">Prénoms</label>
                            <input type="text" id="prenoms" name="prenoms" required>
                        </div>
                        <div class="form-group">
                            <label for="date_naissance" class="required">Date de naissance</label>
                            <input type="date" id="date_naissance" name="date_naissance" required>
                        </div>
                        <div class="form-group">
                            <label for="lieu_naissance" class="required">Lieu de naissance</label>
                            <input type="text" id="lieu_naissance" name="lieu_naissance" required>
                        </div>
                        <div class="form-group">
                            <label for="pays_naissance" class="required">Pays de naissance</label>
                            <input type="text" id="pays_naissance" name="pays_naissance" required>
                        </div>
                        <div class="form-group">
                            <label for="nationalite" class="required">Nationalité</label>
                            <input type="text" id="nationalite" name="nationalite" required>
                        </div>
                        <div class="form-group">
                            <label for="sexe" class="required">Sexe</label>
                            <select id="sexe" name="sexe" required>
                                <option value="">Sélectionner</option>
                                <option value="M">Masculin</option>
                                <option value="F">Féminin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="statut_matrimonial" class="required">Statut matrimonial</label>
                            <select id="statut_matrimonial" name="statut_matrimonial" required onchange="toggleFamilySections(this.value)">
                                <option value="">Sélectionner</option>
                                <option value="Célibataire">Célibataire</option>
                                <option value="Marié(e)">Marié(e)</option>
                                <option value="Divorcé(e)">Divorcé(e)</option>
                                <option value="Veuf(ve)">Veuf(ve)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Informations du conjoint (conditionnel) -->
                <div class="form-section" id="conjoint-section" style="display: none;">
                    <h2 class="section-title"><i class="fas fa-user-friends"></i> Informations du Conjoint</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="conjoint_nom">Nom de famille</label>
                            <input type="text" id="conjoint_nom" name="conjoint_nom">
                        </div>
                        <div class="form-group">
                            <label for="conjoint_prenoms">Prénoms</label>
                            <input type="text" id="conjoint_prenoms" name="conjoint_prenoms">
                        </div>
                        <div class="form-group">
                            <label for="conjoint_date_naissance">Date de naissance</label>
                            <input type="date" id="conjoint_date_naissance" name="conjoint_date_naissance">
                        </div>
                        <div class="form-group">
                            <label for="conjoint_lieu_naissance">Lieu de naissance</label>
                            <input type="text" id="conjoint_lieu_naissance" name="conjoint_lieu_naissance">
                        </div>
                        <div class="form-group">
                            <label for="conjoint_nationalite">Nationalité</label>
                            <input type="text" id="conjoint_nationalite" name="conjoint_nationalite">
                        </div>
                        <div class="form-group">
                            <label for="conjoint_profession">Profession</label>
                            <input type="text" id="conjoint_profession" name="conjoint_profession">
                        </div>
                        <div class="form-group">
                            <label for="conjoint_employeur">Employeur</label>
                            <input type="text" id="conjoint_employeur" name="conjoint_employeur">
                        </div>
                        <div class="form-group full-width">
                            <label for="conjoint_experience_professionnelle">Expérience professionnelle</label>
                            <textarea id="conjoint_experience_professionnelle" name="conjoint_experience_professionnelle" placeholder="Décrivez l'expérience professionnelle du conjoint..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="conjoint_niveau_etudes">Niveau d'études</label>
                            <select id="conjoint_niveau_etudes" name="conjoint_niveau_etudes">
                                <option value="">Sélectionner</option>
                                <option value="Primaire">Primaire</option>
                                <option value="Secondaire">Secondaire</option>
                                <option value="Baccalauréat">Baccalauréat</option>
                                <option value="Licence">Licence</option>
                                <option value="Master">Master</option>
                                <option value="Doctorat">Doctorat</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="conjoint_etablissement_etudes">Établissement d'études</label>
                            <input type="text" id="conjoint_etablissement_etudes" name="conjoint_etablissement_etudes">
                        </div>
                    </div>
                </div>

                <!-- Informations des enfants (conditionnel) -->
                <div class="form-section" id="enfants-section" style="display: none;">
                    <h2 class="section-title"><i class="fas fa-child"></i> Informations des Enfants</h2>
                    <div class="family-section">
                        <div class="family-header">
                            <div class="family-title">Enfants à charge</div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="ajouterEnfant()">
                                <i class="fas fa-plus"></i> Ajouter un enfant
                            </button>
                        </div>
                        <div id="enfants-container">
                            <!-- Les enfants seront ajoutés dynamiquement ici -->
                        </div>
                    </div>
                </div>

                <!-- Coordonnées -->
                <div class="form-section">
                    <h2 class="section-title"><i class="fas fa-address-card"></i> Coordonnées</h2>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="adresse" class="required">Adresse complète</label>
                            <input type="text" id="adresse" name="adresse" required>
                        </div>
                        <div class="form-group">
                            <label for="ville" class="required">Ville</label>
                            <input type="text" id="ville" name="ville" required>
                        </div>
                        <div class="form-group">
                            <label for="code_postal">Code postal</label>
                            <input type="text" id="code_postal" name="code_postal">
                        </div>
                        <div class="form-group">
                            <label for="pays_residence" class="required">Pays de résidence</label>
                            <input type="text" id="pays_residence" name="pays_residence" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone" class="required">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="email" class="required">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>
                </div>

                <!-- Passeport -->
                <div class="form-section">
                    <h2 class="section-title"><i class="fas fa-passport"></i> Informations Passeport</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="passeport_numero" class="required">Numéro de passeport</label>
                            <input type="text" id="passeport_numero" name="passeport_numero" required>
                        </div>
                        <div class="form-group">
                            <label for="passeport_pays" class="required">Pays de délivrance</label>
                            <input type="text" id="passeport_pays" name="passeport_pays" required>
                        </div>
                        <div class="form-group">
                            <label for="passeport_date_emission" class="required">Date de délivrance</label>
                            <input type="date" id="passeport_date_emission" name="passeport_date_emission" required>
                        </div>
                        <div class="form-group">
                            <label for="passeport_date_expiration" class="required">Date d'expiration</label>
                            <input type="date" id="passeport_date_expiration" name="passeport_date_expiration" required>
                        </div>
                    </div>
                </div>

                <!-- Séjour au Canada -->
                <div class="form-section">
                    <h2 class="section-title"><i class="fas fa-plane"></i> Séjour au Canada</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="but_visite" class="required">But de la visite</label>
                            <select id="but_visite" name="but_visite" required>
                                <option value="">Sélectionner</option>
                                <option value="Tourisme">Tourisme</option>
                                <option value="Visite familiale">Visite familiale</option>
                                <option value="Affaires">Affaires</option>
                                <option value="Conférence">Conférence</option>
                                <option value="Transit">Transit</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_arrivee" class="required">Date d'arrivée prévue</label>
                            <input type="date" id="date_arrivee" name="date_arrivee" required>
                        </div>
                        <div class="form-group">
                            <label for="date_depart" class="required">Date de départ prévue</label>
                            <input type="date" id="date_depart" name="date_depart" required>
                        </div>
                        <div class="form-group">
                            <label for="duree_sejour" class="required">Durée du séjour</label>
                            <input type="text" id="duree_sejour" name="duree_sejour" placeholder="Ex: 2 semaines" required>
                        </div>
                    </div>
                </div>

                <!-- Hébergement -->
                <div class="form-section">
                    <h2 class="section-title"><i class="fas fa-hotel"></i> Hébergement</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="hebergement_type" class="required">Type d'hébergement</label>
                            <select id="hebergement_type" name="hebergement_type" required>
                                <option value="">Sélectionner</option>
                                <option value="Hôtel">Hôtel</option>
                                <option value="Chez des amis">Chez des amis</option>
                                <option value="Chez de la famille">Chez de la famille</option>
                                <option value="Location">Location</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="hebergement_adresse">Adresse d'hébergement au Canada</label>
                            <input type="text" id="hebergement_adresse" name="hebergement_adresse">
                        </div>
                        <div class="form-group">
                            <label for="personne_contact_nom">Nom de la personne de contact</label>
                            <input type="text" id="personne_contact_nom" name="personne_contact_nom">
                        </div>
                        <div class="form-group">
                            <label for="personne_contact_telephone">Téléphone de contact</label>
                            <input type="tel" id="personne_contact_telephone" name="personne_contact_telephone">
                        </div>
                        <div class="form-group">
                            <label for="personne_contact_relation">Relation</label>
                            <input type="text" id="personne_contact_relation" name="personne_contact_relation">
                        </div>
                    </div>
                </div>

                <!-- Situation professionnelle -->
                <div class="form-section">
                    <h2 class="section-title"><i class="fas fa-briefcase"></i> Situation Professionnelle</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="situation_professionnelle" class="required">Situation professionnelle</label>
                            <select id="situation_professionnelle" name="situation_professionnelle" required>
                                <option value="">Sélectionner</option>
                                <option value="Employé">Employé</option>
                                <option value="Indépendant">Indépendant</option>
                                <option value="Étudiant">Étudiant</option>
                                <option value="Retraité">Retraité</option>
                                <option value="Sans emploi">Sans emploi</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="profession">Profession</label>
                            <input type="text" id="profession" name="profession">
                        </div>
                        <div class="form-group">
                            <label for="employeur_nom">Nom de l'employeur</label>
                            <input type="text" id="employeur_nom" name="employeur_nom">
                        </div>
                        <div class="form-group">
                            <label for="employeur_adresse">Adresse de l'employeur</label>
                            <input type="text" id="employeur_adresse" name="employeur_adresse">
                        </div>
                        <div class="form-group">
                            <label for="employeur_telephone">Téléphone employeur</label>
                            <input type="tel" id="employeur_telephone" name="employeur_telephone">
                        </div>
                    </div>
                </div>

                <!-- Historique et antécédents -->
                <div class="form-section">
                    <h2 class="section-title"><i class="fas fa-history"></i> Historique et Antécédents</h2>
                    
                    <div class="question-group">
                        <div class="question-text">Avez-vous déjà été refusé un visa pour le Canada ou tout autre pays ?</div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="refus_visa_precedent" value="Oui" required> Oui
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="refus_visa_precedent" value="Non" required> Non
                            </label>
                        </div>
                        <div class="conditional-field" id="refus-details">
                            <label for="details_refus">Détails du refus</label>
                            <textarea id="details_refus" name="details_refus"></textarea>
                        </div>
                    </div>

                    <div class="question-group">
                        <div class="question-text">Avez-vous des maladies graves ou des problèmes de santé ?</div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="maladies_graves" value="Oui" required> Oui
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="maladies_graves" value="Non" required> Non
                            </label>
                        </div>
                        <div class="conditional-field" id="maladies-details">
                            <label for="details_maladies">Détails des problèmes de santé</label>
                            <textarea id="details_maladies" name="details_maladies"></textarea>
                        </div>
                    </div>

                    <div class="question-group">
                        <div class="question-text">Avez-vous des condamnations judiciaires ?</div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="condamnations_judiciaires" value="Oui" required> Oui
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="condamnations_judiciaires" value="Non" required> Non
                            </label>
                        </div>
                        <div class="conditional-field" id="condamnations-details">
                            <label for="details_condamnations">Détails des condamnations</label>
                            <textarea id="details_condamnations" name="details_condamnations"></textarea>
                        </div>
                    </div>

                    <div class="question-group">
                        <div class="question-text">Avez-vous servi dans l'armée ?</div>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="service_militaire" value="Oui" required> Oui
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="service_militaire" value="Non" required> Non
                            </label>
                        </div>
                        <div class="conditional-field" id="service-details">
                            <label for="details_service_militaire">Détails du service militaire</label>
                            <textarea id="details_service_militaire" name="details_service_militaire"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Documents à fournir -->
                <div class="form-section">
                    <h2 class="section-title"><i class="fas fa-file-upload"></i> Documents à Fournir</h2>
                    <div class="form-grid">
                        <!-- Passeport -->
                        <div class="form-group">
                            <label class="required">Passeport (pages principales)</label>
                            <div class="file-upload-container" onclick="document.getElementById('passeport').click()">
                                <input type="file" id="passeport" name="passeport" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                <label class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Cliquer pour uploader</span>
                                </label>
                                <div class="file-info">PDF, JPG, PNG (Max. 5MB)</div>
                                <div class="file-preview" id="passeport-preview"></div>
                            </div>
                        </div>

                        <!-- Photo d'identité -->
                        <div class="form-group">
                            <label class="required">Photo d'identité</label>
                            <div class="file-upload-container" onclick="document.getElementById('photo_identite').click()">
                                <input type="file" id="photo_identite" name="photo_identite" class="file-input" accept=".jpg,.jpeg,.png" required>
                                <label class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Cliquer pour uploader</span>
                                </label>
                                <div class="file-info">JPG, PNG (Max. 2MB)</div>
                                <div class="file-preview" id="photo_identite-preview"></div>
                            </div>
                        </div>

                        <!-- Justificatif de fonds -->
                        <div class="form-group">
                            <label class="required">Justificatif de fonds</label>
                            <div class="file-upload-container" onclick="document.getElementById('justificatif_fonds').click()">
                                <input type="file" id="justificatif_fonds" name="justificatif_fonds" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                <label class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Cliquer pour uploader</span>
                                </label>
                                <div class="file-info">Relevés bancaires, etc. (Max. 5MB)</div>
                                <div class="file-preview" id="justificatif_fonds-preview"></div>
                            </div>
                        </div>

                        <!-- Réservation de vol -->
                        <div class="form-group">
                            <label class="required">Réservation de vol</label>
                            <div class="file-upload-container" onclick="document.getElementById('reservation_vol').click()">
                                <input type="file" id="reservation_vol" name="reservation_vol" class="file-input" accept=".pdf,.jpg,.jpeg,.png" required>
                                <label class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Cliquer pour uploader</span>
                                </label>
                                <div class="file-info">PDF, JPG, PNG (Max. 5MB)</div>
                                <div class="file-preview" id="reservation_vol-preview"></div>
                            </div>
                        </div>

                        <!-- Preuve d'hébergement -->
                        <div class="form-group">
                            <label>Preuve d'hébergement</label>
                            <div class="file-upload-container" onclick="document.getElementById('hebergement_preuve').click()">
                                <input type="file" id="hebergement_preuve" name="hebergement_preuve" class="file-input" accept=".pdf,.jpg,.jpeg,.png">
                                <label class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Cliquer pour uploader</span>
                                </label>
                                <div class="file-info">Réservation hôtel, etc. (Max. 5MB)</div>
                                <div class="file-preview" id="hebergement_preuve-preview"></div>
                            </div>
                        </div>

                        <!-- Documents conjoint -->
                        <div class="form-group" id="doc-conjoint" style="display: none;">
                            <label>Documents conjoint</label>
                            <div class="file-upload-container" onclick="document.getElementById('documents_conjoint').click()">
                                <input type="file" id="documents_conjoint" name="documents_conjoint" class="file-input" accept=".pdf,.jpg,.jpeg,.png">
                                <label class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Cliquer pour uploader</span>
                                </label>
                                <div class="file-info">Passeport, acte de mariage, etc. (Max. 5MB)</div>
                                <div class="file-preview" id="documents_conjoint-preview"></div>
                            </div>
                        </div>

                        <!-- Documents enfants -->
                        <div class="form-group" id="doc-enfants" style="display: none;">
                            <label>Documents enfants</label>
                            <div class="file-upload-container" onclick="document.getElementById('documents_enfants').click()">
                                <input type="file" id="documents_enfants" name="documents_enfants" class="file-input" accept=".pdf,.jpg,.jpeg,.png">
                                <label class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Cliquer pour uploader</span>
                                </label>
                                <div class="file-info">Passeports, actes de naissance, etc. (Max. 5MB)</div>
                                <div class="file-preview" id="documents_enfants-preview"></div>
                            </div>
                        </div>

                        <!-- Autres documents -->
                        <div class="form-group">
                            <label>Autres documents</label>
                            <div class="file-upload-container" onclick="document.getElementById('autres_documents').click()">
                                <input type="file" id="autres_documents" name="autres_documents" class="file-input" accept=".pdf,.jpg,.jpeg,.png">
                                <label class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span>Cliquer pour uploader</span>
                                </label>
                                <div class="file-info">Documents supplémentaires (Max. 5MB)</div>
                                <div class="file-preview" id="autres_documents-preview"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-outline">
                        <i class="fas fa-undo"></i> Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="fas fa-paper-plane"></i> Soumettre la demande
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let enfantCount = 0;

        function toggleFamilySections(statut) {
            const conjointSection = document.getElementById('conjoint-section');
            const enfantsSection = document.getElementById('enfants-section');
            const docConjoint = document.getElementById('doc-conjoint');
            const docEnfants = document.getElementById('doc-enfants');
            
            if (statut === 'Marié(e)') {
                conjointSection.style.display = 'block';
                enfantsSection.style.display = 'block';
                docConjoint.style.display = 'block';
                docEnfants.style.display = 'block';
            } else {
                conjointSection.style.display = 'none';
                enfantsSection.style.display = 'none';
                docConjoint.style.display = 'none';
                docEnfants.style.display = 'none';
                // Réinitialiser les champs
                document.getElementById('enfants-container').innerHTML = '';
                enfantCount = 0;
            }
        }

        function ajouterEnfant() {
            enfantCount++;
            const container = document.getElementById('enfants-container');
            const enfantDiv = document.createElement('div');
            enfantDiv.className = 'enfant-item';
            enfantDiv.innerHTML = `
                <div class="enfant-header">
                    <div class="enfant-title">Enfant ${enfantCount}</div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="supprimerEnfant(this)">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="enfant_prenoms_${enfantCount}">Prénoms</label>
                        <input type="text" id="enfant_prenoms_${enfantCount}" name="enfants[${enfantCount}][prenoms]">
                    </div>
                    <div class="form-group">
                        <label for="enfant_nom_${enfantCount}">Nom de famille</label>
                        <input type="text" id="enfant_nom_${enfantCount}" name="enfants[${enfantCount}][nom_famille]">
                    </div>
                    <div class="form-group">
                        <label for="enfant_date_naissance_${enfantCount}">Date de naissance</label>
                        <input type="date" id="enfant_date_naissance_${enfantCount}" name="enfants[${enfantCount}][date_naissance]">
                    </div>
                    <div class="form-group">
                        <label for="enfant_lieu_naissance_${enfantCount}">Lieu de naissance</label>
                        <input type="text" id="enfant_lieu_naissance_${enfantCount}" name="enfants[${enfantCount}][lieu_naissance]">
                    </div>
                    <div class="form-group">
                        <label for="enfant_nationalite_${enfantCount}">Nationalité</label>
                        <input type="text" id="enfant_nationalite_${enfantCount}" name="enfants[${enfantCount}][nationalite]">
                    </div>
                    <div class="form-group">
                        <label for="enfant_niveau_etudes_${enfantCount}">Niveau d'études</label>
                        <select id="enfant_niveau_etudes_${enfantCount}" name="enfants[${enfantCount}][niveau_etudes]">
                            <option value="">Sélectionner</option>
                            <option value="Maternelle">Maternelle</option>
                            <option value="Primaire">Primaire</option>
                            <option value="Secondaire">Secondaire</option>
                            <option value="Universitaire">Universitaire</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="enfant_etablissement_${enfantCount}">Établissement</label>
                        <input type="text" id="enfant_etablissement_${enfantCount}" name="enfants[${enfantCount}][etablissement]">
                    </div>
                </div>
            `;
            container.appendChild(enfantDiv);
        }

        function supprimerEnfant(button) {
            const enfantItem = button.closest('.enfant-item');
            enfantItem.remove();
            // Recalculer le nombre d'enfants
            enfantCount = document.querySelectorAll('.enfant-item').length;
        }

        function validateForm() {
            const requiredFields = document.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = 'red';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                alert('Veuillez remplir tous les champs obligatoires.');
                return false;
            }
            
            // Empêcher la double soumission
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Soumission en cours...';
            
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Gestion des champs conditionnels
            const conditionalFields = {
                'refus_visa_precedent': 'refus-details',
                'maladies_graves': 'maladies-details',
                'condamnations_judiciaires': 'condamnations-details',
                'service_militaire': 'service-details'
            };

            Object.keys(conditionalFields).forEach(radioName => {
                const radios = document.querySelectorAll(`input[name="${radioName}"]`);
                const targetField = document.getElementById(conditionalFields[radioName]);
                
                radios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        if (this.value === 'Oui') {
                            targetField.style.display = 'block';
                        } else {
                            targetField.style.display = 'none';
                            // Effacer le contenu du champ conditionnel
                            const textarea = targetField.querySelector('textarea');
                            if (textarea) textarea.value = '';
                        }
                    });
                });
            });

            // Gestion de l'upload de fichiers avec prévisualisation
            const fileInputs = document.querySelectorAll('.file-input');
            
            fileInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const preview = document.getElementById(this.id + '-preview');
                    if (this.files.length > 0) {
                        const file = this.files[0];
                        preview.innerHTML = `
                            <i class="fas fa-file" style="color: #3b82f6; margin-right: 8px;"></i>
                            ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)
                        `;
                        
                        // Changer le style du conteneur
                        this.closest('.file-upload-container').style.borderColor = '#10b981';
                        this.closest('.file-upload-container').style.background = '#f0fdf4';
                    } else {
                        preview.innerHTML = '';
                        this.closest('.file-upload-container').style.borderColor = '';
                        this.closest('.file-upload-container').style.background = '';
                    }
                });
            });

            // Validation des dates
            const dateNaissance = document.getElementById('date_naissance');
            const dateArrivee = document.getElementById('date_arrivee');
            const dateDepart = document.getElementById('date_depart');
            const passeportExpiration = document.getElementById('passeport_date_expiration');
            
            if (dateNaissance) {
                const maxDate = new Date();
                maxDate.setFullYear(maxDate.getFullYear() - 14);
                dateNaissance.max = maxDate.toISOString().split('T')[0];
            }
            
            if (dateArrivee) {
                const minDate = new Date();
                minDate.setDate(minDate.getDate() + 1);
                dateArrivee.min = minDate.toISOString().split('T')[0];
            }
            
            if (dateDepart) {
                dateDepart.addEventListener('change', function() {
                    if (dateArrivee.value && this.value < dateArrivee.value) {
                        alert('La date de départ doit être après la date d\'arrivée');
                        this.value = '';
                    }
                });
            }
            
            if (passeportExpiration) {
                const minDate = new Date();
                minDate.setMonth(minDate.getMonth() + 6);
                passeportExpiration.min = minDate.toISOString().split('T')[0];
            }

            // Calcul automatique de la durée du séjour
            if (dateArrivee && dateDepart) {
                [dateArrivee, dateDepart].forEach(input => {
                    input.addEventListener('change', function() {
                        if (dateArrivee.value && dateDepart.value) {
                            const arrivee = new Date(dateArrivee.value);
                            const depart = new Date(dateDepart.value);
                            const diffTime = Math.abs(depart - arrivee);
                            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                            
                            const dureeInput = document.getElementById('duree_sejour');
                            if (diffDays > 0) {
                                if (diffDays === 1) {
                                    dureeInput.value = '1 jour';
                                } else if (diffDays < 7) {
                                    dureeInput.value = `${diffDays} jours`;
                                } else if (diffDays < 30) {
                                    const weeks = Math.ceil(diffDays / 7);
                                    dureeInput.value = `${weeks} semaine${weeks > 1 ? 's' : ''}`;
                                } else {
                                    const months = Math.ceil(diffDays / 30);
                                    dureeInput.value = `${months} mois`;
                                }
                            }
                        }
                    });
                });
            }

            // Drag and drop pour les fichiers
            const fileContainers = document.querySelectorAll('.file-upload-container');
            
            fileContainers.forEach(container => {
                container.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });
                
                container.addEventListener('dragleave', function() {
                    this.classList.remove('dragover');
                });
                
                container.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                    
                    const fileInput = this.querySelector('.file-input');
                    if (e.dataTransfer.files.length > 0) {
                        fileInput.files = e.dataTransfer.files;
                        fileInput.dispatchEvent(new Event('change'));
                    }
                });
            });
        });
    </script>
</body>
</html>