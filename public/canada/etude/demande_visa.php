<?php
session_start();

// Afficher les messages d'erreur s'ils existent
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" style="margin: 20px;">
            <i class="fas fa-exclamation-triangle"></i> ' . $_SESSION['error_message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    echo '<div class="alert alert-success alert-dismissible fade show" style="margin: 20px;">
            <i class="fas fa-check-circle"></i> ' . $_SESSION['success_message'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Visa - Canada</title>
    <!-- Ajouter Bootstrap pour les alertes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Canada - Couleurs rouge et blanc */
        :root {
            --canada-red: #FF0000;
            --canada-white: #FFFFFF;
            --canada-dark: #1a1a1a;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(255, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --light-bg: #f8fafc;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --error-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
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
            background: var(--canada-white);
            border-radius: 16px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
            transition: var(--transition);
            border: 2px solid var(--canada-red);
        }
        
        .card:hover {
            box-shadow: 0 15px 40px rgba(255, 0, 0, 0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--canada-red) 0%, #cc0000 100%);
            color: var(--canada-white);
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
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.05"><text x="50%" y="50%" font-size="20" text-anchor="middle" dominant-baseline="middle" fill="white">🇨🇦</text></svg>');
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
            color: var(--canada-white);
        }
        
        .card-header p {
            font-size: 1.2rem;
            opacity: 0.9;
            margin: 0;
            max-width: 600px;
            margin: 0 auto;
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
            color: var(--canada-red);
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h2 i {
            color: var(--canada-white);
            background: var(--canada-red);
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
            border-color: var(--canada-red);
            box-shadow: 0 0 0 3px rgba(255, 0, 0, 0.1);
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
            border-color: var(--canada-red);
            background: rgba(255, 0, 0, 0.05);
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
            background: var(--canada-red);
            color: var(--canada-white);
        }
        
        .btn-primary:hover {
            background: #cc0000;
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--canada-red);
            border-color: var(--canada-red);
        }
        
        .btn-outline:hover {
            background: var(--canada-red);
            color: var(--canada-white);
        }
        
        .btn-success {
            background: var(--success-color);
            color: var(--canada-white);
        }
        
        .btn-success:hover {
            background: #0da271;
        }
        
        .btn-warning {
            background: var(--warning-color);
            color: var(--canada-white);
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        .btn-info {
            background: var(--info-color);
            color: var(--canada-white);
        }
        
        .btn-info:hover {
            background: #2563eb;
        }
        
        .btn-sm {
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        
        .documents-container h3 {
            font-size: 1.3rem;
            color: var(--canada-red);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--canada-red);
        }
        
        .niveau-info {
            background: #ffe6e6;
            border: 1px solid var(--canada-red);
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
            display: none;
        }
        
        .niveau-info.active {
            display: block;
        }
        
        .province-select {
            background: var(--light-bg);
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            padding: 14px 18px;
            width: 100%;
            font-size: 1rem;
        }
        
        .document-optionnel {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }
        
        .document-optionnel h4 {
            color: var(--canada-red);
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .document-supplementaire {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .garant-supplementaire {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .document-supplementaire .btn-remove,
        .garant-supplementaire .btn-remove {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--error-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .option-group {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .option-group input[type="radio"] {
            width: 18px;
            height: 18px;
        }
        
        .option-group label {
            margin-bottom: 0;
            cursor: pointer;
        }
        
        .demande-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .lettre-motivation-option {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        @media (max-width: 768px) {
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
            
            .option-group {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .demande-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-passport"></i> Demande de Visa - Canada</h1>
            <p>Formulaire pour demande de visa étudiant canadien</p>
        </div>

        <form method="post" action="save_visa.php" class="form" enctype="multipart/form-data" id="visa-form">
            <!-- Informations personnelles -->
            <div class="form-section">
                <h2><i class="fas fa-user-graduate"></i> Informations personnelles</h2>
                <div class="form-group">
                    <label for="nom">Nom complet <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" required>
                </div>
                <div class="form-group">
                    <label for="naissance">Date de naissance <span class="required">*</span></label>
                    <input type="date" id="naissance" name="naissance" required>
                </div>
                <div class="form-group">
                    <label for="nationalite">Nationalité <span class="required">*</span></label>
                    <input type="text" id="nationalite" name="nationalite" required>
                </div>
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="province">Province canadienne de destination <span class="required">*</span></label>
                    <select id="province" name="province" class="province-select" required onchange="toggleProvinceDocuments()">
                        <option value="">-- Choisir une province --</option>
                        <option value="quebec">Québec</option>
                        <option value="ontario">Ontario</option>
                        <option value="colombie_britannique">Colombie-Britannique</option>
                        <option value="alberta">Alberta</option>
                        <option value="manitoba">Manitoba</option>
                        <option value="saskatchewan">Saskatchewan</option>
                        <option value="nouvelle_ecosse">Nouvelle-Écosse</option>
                        <option value="nouveau_brunswick">Nouveau-Brunswick</option>
                        <option value="terre_neuve">Terre-Neuve-et-Labrador</option>
                        <option value="ile_du_prince_edouard">Île-du-Prince-Édouard</option>
                    </select>
                </div>
            </div>
            
            <!-- Section Informations université/établissement -->
            <div class="form-section">
                <h2><i class="fas fa-university"></i> Informations de l'établissement</h2>
                
                <div class="form-group">
                    <label for="nom_etablissement">Nom de l'établissement <span class="required">*</span></label>
                    <input type="text" id="nom_etablissement" name="nom_etablissement" required placeholder="Ex: Université de Montréal">
                </div>
                
                <div class="form-group">
                    <label for="programme_etudes">Programme d'études <span class="required">*</span></label>
                    <input type="text" id="programme_etudes" name="programme_etudes" required placeholder="Ex: Baccalauréat en informatique">
                </div>
                
                <div class="form-group">
                    <label for="duree_etudes">Durée des études <span class="required">*</span></label>
                    <select id="duree_etudes" name="duree_etudes" required>
                        <option value="">-- Choisir la durée --</option>
                        <option value="6_mois">6 mois</option>
                        <option value="1_an">1 an</option>
                        <option value="2_ans">2 ans</option>
                        <option value="3_ans">3 ans</option>
                        <option value="4_ans">4 ans</option>
                        <option value="plus_4_ans">Plus de 4 ans</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date_debut">Date de début des études <span class="required">*</span></label>
                    <input type="date" id="date_debut" name="date_debut" required>
                </div>
            </div>
            
            <!-- Section Documents université -->
            <div class="form-section">
                <h2><i class="fas fa-file-alt"></i> Documents de l'établissement</h2>
                
                <div class="documents-container">
                    <h3>📚 Documents universitaires requis</h3>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Les documents suivants sont obligatoires pour votre demande de visa étudiant.
                    </div>
                    
                    <!-- Lettre d'acceptation -->
                    <div class="form-group">
                        <label for="lettre_acceptation">Lettre d'acceptation de l'établissement <span class="required">*</span></label>
                        <div class="file-input-container">
                            <input type="file" id="lettre_acceptation" name="lettre_acceptation" required>
                            <label for="lettre_acceptation" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un fichier</span>
                            </label>
                        </div>
                        <div class="file-hint">Lettre officielle d'acceptation de l'établissement canadien (PDF - Max. 5MB)</div>
                    </div>
                </div>
            </div>
            
            <!-- Section Garant -->
            <div class="form-section">
                <h2><i class="fas fa-user-tie"></i> Informations du garant</h2>
                
                <div id="garants-container">
                    <!-- Premier garant -->
                    <div class="garant-supplementaire" id="garant-1">
                        <h4 style="color: var(--canada-red); margin-bottom: 20px;">
                            <i class="fas fa-user-tie"></i> Garant #1
                        </h4>
                        
                        <div class="form-group">
                            <label for="nom_garant_1">Nom complet du garant <span class="required">*</span></label>
                            <input type="text" id="nom_garant_1" name="nom_garant[]" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="relation_garant_1">Relation avec le garant <span class="required">*</span></label>
                            <select id="relation_garant_1" name="relation_garant[]" required>
                                <option value="">-- Choisir la relation --</option>
                                <option value="parent">Parent</option>
                                <option value="tuteur">Tuteur</option>
                                <option value="frere_soeur">Frère/Sœur</option>
                                <option value="autre_famille">Autre membre de famille</option>
                                <option value="ami">Ami</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="documents_garant_1">Documents du garant <span class="required">*</span></label>
                            <div class="file-input-container">
                                <input type="file" id="documents_garant_1" name="documents_garant_1[]" multiple required>
                                <label for="documents_garant_1" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text">Choisir les fichiers</span>
                                </label>
                            </div>
                            <div class="file-hint">Pièces d'identité, justificatifs de revenus, relevés bancaires, etc. (PDF, JPG, PNG - Max. 5MB par fichier)</div>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-outline btn-sm" onclick="ajouterGarant()">
                    <i class="fas fa-plus"></i> Ajouter un autre garant
                </button>
                
                <!-- Attestation de province (dynamique selon la province) -->
                <div id="attestation_province_container" style="display: none; margin-top: 30px;">
                    <div class="document-optionnel">
                        <h4 id="attestation_title"><i class="fas fa-file-certificate"></i> Attestation de province</h4>
                        <p style="margin-bottom: 15px; color: #64748b;" id="attestation_description">Avez-vous déjà une attestation de province ?</p>
                        
                        <div class="option-group">
                            <input type="radio" id="attestation_oui" name="attestation_province_option" value="oui" onchange="toggleAttestationProvinceUpload(true)">
                            <label for="attestation_oui" id="attestation_oui_label">Oui, j'ai déjà une attestation de province</label>
                        </div>
                        <div class="option-group">
                            <input type="radio" id="attestation_non" name="attestation_province_option" value="non" onchange="toggleAttestationProvinceUpload(false)" checked>
                            <label for="attestation_non" id="attestation_non_label">Non, je n'ai pas encore d'attestation de province</label>
                        </div>
                        
                        <div id="attestation_province_upload_container" style="display: none; margin-top: 15px;">
                            <div class="file-input-container">
                                <input type="file" id="attestation_province" name="attestation_province">
                                <label for="attestation_province" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text" id="attestation_file_text">Télécharger l'attestation de province</span>
                                </label>
                            </div>
                            <div class="file-hint" id="attestation_file_hint">Attestation de province (PDF - Max. 5MB)</div>
                        </div>
                        
                        <div id="attestation_province_demande_container" style="margin-top: 15px;">
                            <p style="margin-bottom: 10px; color: #64748b;" id="attestation_demande_text">Si vous n'avez pas encore d'attestation de province, vous pouvez en faire la demande :</p>
                            <div class="demande-buttons">
                                <a href="demande_attestation_province.php" class="btn btn-info btn-sm" id="attestation_demande_btn">
                                    <i class="fas fa-file-alt"></i> Demander une attestation de province
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Section Renseignements client -->
            <div class="form-section">
                <h2><i class="fas fa-id-card"></i> Renseignements client</h2>
                
                <div class="form-group">
                    <label for="documents_identite">Documents d'identité <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="documents_identite" name="documents_identite[]" multiple required>
                        <label for="documents_identite" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir les fichiers</span>
                        </label>
                    </div>
                    <div class="file-hint">Casier judiciaire, fiche familiale, acte de naissance, etc. (PDF, JPG, PNG - Max. 5MB par fichier)</div>
                </div>
            </div>
            
            <!-- Documents administratifs -->
            <div class="form-section">
                <h2><i class="fas fa-file-upload"></i> Documents administratifs</h2>
                
                <!-- Passeport - Obligatoire -->
                <div class="form-group">
                    <label for="passeport">Passeport <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="passeport" name="passeport" required>
                        <label for="passeport" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Pages du passeport avec photo et informations (PDF - Max. 5MB)</div>
                </div>
                
                <!-- Photo - Obligatoire -->
                <div class="form-group">
                    <label for="photo">Photo d'identité <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="photo" name="photo" required>
                        <label for="photo" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Photo format passeport (PNG, JPG - Max. 5MB)</div>
                </div>
                
                <!-- Test de langue - Optionnel avec choix -->
                <div class="document-optionnel">
                    <h4><i class="fas fa-language"></i> Test de langue (Optionnel)</h4>
                    <p style="margin-bottom: 15px; color: #64748b;">Avez-vous déjà un test de langue (IELTS, TOEFL, TEF, TCF) ?</p>
                    
                    <div class="option-group">
                        <input type="radio" id="test_oui" name="test_langue_option" value="oui" onchange="toggleTestLangueUpload(true)">
                        <label for="test_oui">Oui, j'ai déjà un test de langue</label>
                    </div>
                    <div class="option-group">
                        <input type="radio" id="test_non" name="test_langue_option" value="non" onchange="toggleTestLangueUpload(false)" checked>
                        <label for="test_non">Non, je n'ai pas encore de test de langue</label>
                    </div>
                    
                    <div id="test_langue_upload_container" style="display: none; margin-top: 15px;">
                        <div class="file-input-container">
                            <input type="file" id="test_langue" name="test_langue">
                            <label for="test_langue" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Télécharger votre test de langue</span>
                            </label>
                        </div>
                        <div class="file-hint">IELTS, TOEFL, TEF ou TCF (PDF - Max. 5MB)</div>
                    </div>
                    
                    <div id="test_langue_demande_container" style="margin-top: 15px;">
                        <p style="margin-bottom: 10px; color: #64748b;">Si vous n'avez pas encore de test de langue, vous pouvez en faire la demande :</p>
                        <div class="demande-buttons">
                            <a href="demande_test_langue.php" class="btn btn-info btn-sm">
                                <i class="fas fa-file-alt"></i> Demander un test de langue
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Preuve de fonds - Obligatoire -->
                <div class="form-group">
                    <label for="preuve_fonds">Preuve de fonds <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="preuve_fonds" name="preuve_fonds" required>
                        <label for="preuve_fonds" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Relevés bancaires, attestation de bourse, etc. (PDF - Max. 5MB)</div>
                </div>
                
                <!-- Reçu de paiement - Optionnel -->
                <div class="lettre-motivation-option">
                    <h4><i class="fas fa-receipt"></i> Reçu de paiement (Optionnel)</h4>
                    <p style="margin-bottom: 15px; color: #64748b;">Si vous avez déjà effectué des paiements (frais de visa, etc.), vous pouvez télécharger les reçus ici.</p>
                    <div class="file-input-container">
                        <input type="file" id="recu_paiement" name="recu_paiement">
                        <label for="recu_paiement" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Télécharger le reçu de paiement</span>
                        </label>
                    </div>
                    <div class="file-hint">Reçu de paiement des frais (PDF, JPG, PNG - Max. 5MB)</div>
                </div>
                
                <!-- Documents supplémentaires -->
                <div class="form-group">
                    <h4 style="margin-bottom: 20px; color: var(--canada-red);">
                        <i class="fas fa-plus-circle"></i> Documents supplémentaires
                    </h4>
                    <p style="margin-bottom: 15px; color: #64748b;">
                        Vous pouvez ajouter d'autres documents comme des lettres de recommandation, attestations de stages, etc.
                    </p>
                    
                    <div id="documents_supplementaires">
                        <!-- Les documents supplémentaires seront ajoutés ici dynamiquement -->
                    </div>
                    
                    <button type="button" class="btn btn-outline btn-sm" onclick="ajouterDocumentSupplementaire()">
                        <i class="fas fa-plus"></i> Ajouter un document
                    </button>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Soumettre la demande de visa
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let documentCounter = 0;
    let garantCounter = 1;
    
    // Fonction pour gérer l'affichage des documents selon la province
    function toggleProvinceDocuments() {
        const province = document.getElementById('province').value;
        const attestationContainer = document.getElementById('attestation_province_container');
        const title = document.getElementById('attestation_title');
        const description = document.getElementById('attestation_description');
        const fileText = document.getElementById('attestation_file_text');
        const fileHint = document.getElementById('attestation_file_hint');
        const ouiLabel = document.getElementById('attestation_oui_label');
        const nonLabel = document.getElementById('attestation_non_label');
        const demandeText = document.getElementById('attestation_demande_text');
        const demandeBtn = document.getElementById('attestation_demande_btn');
        
        if (province) {
            attestationContainer.style.display = 'block';
            
            // Adapter le texte selon la province
            if (province === 'quebec') {
                title.innerHTML = '<i class="fas fa-file-contract"></i> CAQ (Québec)';
                description.textContent = 'Avez-vous déjà un Certificat d\'Acceptation du Québec (CAQ) ?';
                ouiLabel.textContent = 'Oui, j\'ai déjà un CAQ';
                nonLabel.textContent = 'Non, je n\'ai pas encore de CAQ';
                fileText.textContent = 'Télécharger le CAQ';
                fileHint.textContent = 'Certificat d\'Acceptation du Québec (PDF - Max. 5MB)';
                demandeText.textContent = 'Si vous n\'avez pas encore de CAQ, vous pouvez en faire la demande :';
                demandeBtn.innerHTML = '<i class="fas fa-file-alt"></i> Demander un CAQ';
                demandeBtn.href = 'demande_caq.php';
            } else {
                title.innerHTML = '<i class="fas fa-file-certificate"></i> Attestation de province';
                description.textContent = 'Avez-vous déjà une attestation de province ?';
                ouiLabel.textContent = 'Oui, j\'ai déjà une attestation de province';
                nonLabel.textContent = 'Non, je n\'ai pas encore d\'attestation de province';
                fileText.textContent = 'Télécharger l\'attestation de province';
                fileHint.textContent = 'Attestation de province (PDF - Max. 5MB)';
                demandeText.textContent = 'Si vous n\'avez pas encore d\'attestation de province, vous pouvez en faire la demande :';
                demandeBtn.innerHTML = '<i class="fas fa-file-alt"></i> Demander une attestation de province';
                demandeBtn.href = 'demande_attestation_province.php';
            }
        } else {
            attestationContainer.style.display = 'none';
        }
        
        // Réinitialiser les options d'upload
        toggleAttestationProvinceUpload(false);
        document.getElementById('attestation_non').checked = true;
    }

    // Fonction pour gérer l'upload de l'attestation de province
    function toggleAttestationProvinceUpload(show) {
        const attestationContainer = document.getElementById('attestation_province_upload_container');
        const attestationDemandeContainer = document.getElementById('attestation_province_demande_container');
        const attestationInput = document.getElementById('attestation_province');
        
        if (show) {
            attestationContainer.style.display = 'block';
            attestationDemandeContainer.style.display = 'none';
            attestationInput.disabled = false;
        } else {
            attestationContainer.style.display = 'none';
            attestationDemandeContainer.style.display = 'block';
            attestationInput.disabled = true;
            attestationInput.value = '';
            
            // Réinitialiser le label du fichier
            const label = attestationInput.nextElementSibling;
            const province = document.getElementById('province').value;
            
            if (province === 'quebec') {
                label.querySelector('.file-text').textContent = 'Télécharger le CAQ';
            } else {
                label.querySelector('.file-text').textContent = 'Télécharger l\'attestation de province';
            }
            label.classList.remove('file-selected');
        }
    }

    // Fonction pour gérer l'upload du test de langue
    function toggleTestLangueUpload(show) {
        const testContainer = document.getElementById('test_langue_upload_container');
        const testDemandeContainer = document.getElementById('test_langue_demande_container');
        const testInput = document.getElementById('test_langue');
        
        if (show) {
            testContainer.style.display = 'block';
            testDemandeContainer.style.display = 'none';
            testInput.disabled = false;
        } else {
            testContainer.style.display = 'none';
            testDemandeContainer.style.display = 'block';
            testInput.disabled = true;
            testInput.value = '';
            
            // Réinitialiser le label du fichier
            const label = testInput.nextElementSibling;
            label.querySelector('.file-text').textContent = 'Télécharger votre test de langue';
            label.classList.remove('file-selected');
        }
    }

    // Fonction pour ajouter un garant supplémentaire
    function ajouterGarant() {
        garantCounter++;
        const container = document.getElementById('garants-container');
        
        const garantElement = document.createElement('div');
        garantElement.className = 'garant-supplementaire';
        garantElement.id = `garant-${garantCounter}`;
        garantElement.innerHTML = `
            <button type="button" class="btn-remove" onclick="supprimerGarant(this)">
                <i class="fas fa-times"></i>
            </button>
            <h4 style="color: var(--canada-red); margin-bottom: 20px;">
                <i class="fas fa-user-tie"></i> Garant #${garantCounter}
            </h4>
            <div class="form-group">
                <label>Nom complet du garant <span class="required">*</span></label>
                <input type="text" name="nom_garant[]" required>
            </div>
            <div class="form-group">
                <label>Relation avec le garant <span class="required">*</span></label>
                <select name="relation_garant[]" required>
                    <option value="">-- Choisir la relation --</option>
                    <option value="parent">Parent</option>
                    <option value="tuteur">Tuteur</option>
                    <option value="frere_soeur">Frère/Sœur</option>
                    <option value="autre_famille">Autre membre de famille</option>
                    <option value="ami">Ami</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="form-group">
                <label>Documents du garant <span class="required">*</span></label>
                <div class="file-input-container">
                    <input type="file" name="documents_garant_${garantCounter}[]" multiple required>
                    <label class="file-label">
                        <i class="fas fa-upload"></i>
                        <span class="file-text">Choisir les fichiers</span>
                    </label>
                </div>
                <div class="file-hint">Pièces d'identité, justificatifs de revenus, relevés bancaires, etc. (PDF, JPG, PNG - Max. 5MB par fichier)</div>
            </div>
        `;
        
        container.appendChild(garantElement);
        
        // Ajouter les écouteurs d'événements pour le nouveau fichier
        const fileInput = garantElement.querySelector('input[type="file"]');
        fileInput.addEventListener('change', function() {
            const label = this.nextElementSibling;
            const fileText = label.querySelector('.file-text');
            
            if (this.files.length > 0) {
                fileText.textContent = `${this.files.length} fichier(s) sélectionné(s)`;
                label.classList.add('file-selected');
            } else {
                fileText.textContent = 'Choisir un fichier';
                label.classList.remove('file-selected');
            }
        });
    }

    // Fonction pour supprimer un garant
    function supprimerGarant(button) {
        const garantElement = button.closest('.garant-supplementaire');
        // Ne pas supprimer le premier garant
        if (garantElement.id !== 'garant-1') {
            garantElement.remove();
        }
    }

    // Fonction pour ajouter un document supplémentaire
    function ajouterDocumentSupplementaire() {
        documentCounter++;
        const container = document.getElementById('documents_supplementaires');
        
        const docElement = document.createElement('div');
        docElement.className = 'document-supplementaire';
        docElement.innerHTML = `
            <button type="button" class="btn-remove" onclick="supprimerDocumentSupplementaire(this)">
                <i class="fas fa-times"></i>
            </button>
            <div class="form-group">
                <label>Type de document <span class="required">*</span></label>
                <select name="type_document_supp_${documentCounter}" required>
                    <option value="">-- Choisir le type --</option>
                    <option value="lettre_recommandation">Lettre de recommandation</option>
                    <option value="attestation_stage">Attestation de stage</option>
                    <option value="formation_supplementaire">Formation supplémentaire</option>
                    <option value="certificat_langue">Certificat de langue supplémentaire</option>
                    <option value="portfolio">Portfolio</option>
                    <option value="publication">Publication</option>
                    <option value="autre">Autre document</option>
                </select>
            </div>
            <div class="form-group">
                <label>Description du document</label>
                <input type="text" name="description_document_supp_${documentCounter}" placeholder="Ex: Lettre de recommandation du professeur Dupont">
            </div>
            <div class="form-group">
                <label>Fichier <span class="required">*</span></label>
                <div class="file-input-container">
                    <input type="file" name="fichier_document_supp_${documentCounter}" required>
                    <label class="file-label">
                        <i class="fas fa-upload"></i>
                        <span class="file-text">Choisir un fichier</span>
                    </label>
                </div>
                <div class="file-hint">Formats acceptés: JPG, PNG, PDF (Max 5MB)</div>
            </div>
        `;
        
        container.appendChild(docElement);
        
        // Ajouter les écouteurs d'événements pour le nouveau fichier
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
    function supprimerDocumentSupplementaire(button) {
        const docElement = button.closest('.document-supplementaire');
        docElement.remove();
    }

    document.addEventListener('DOMContentLoaded', function() {
        // File input labels pour les champs fixes
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.nextElementSibling;
                const fileText = label.querySelector('.file-text');
                
                if (this.files.length > 0) {
                    if (this.multiple) {
                        fileText.textContent = `${this.files.length} fichier(s) sélectionné(s)`;
                    } else {
                        fileText.textContent = this.files[0].name;
                    }
                    label.classList.add('file-selected');
                } else {
                    fileText.textContent = 'Choisir un fichier';
                    label.classList.remove('file-selected');
                }
            });
        });
        
        // Form validation before submission
        document.getElementById('visa-form').addEventListener('submit', function(e) {
            // Validation basique
            const requiredFields = document.querySelectorAll('input[required], select[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value) {
                    valid = false;
                    field.style.borderColor = 'var(--error-color)';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!valid) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires');
                return;
            }
        });
        
        // Date de début minimum = aujourd'hui
        const dateDebutInput = document.getElementById('date_debut');
        if (dateDebutInput) {
            const today = new Date().toISOString().split('T')[0];
            dateDebutInput.min = today;
        }
    });
</script>
</body>
</html>