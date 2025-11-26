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

// Afficher les messages d'URL (pour compatibilité)
if (isset($_GET['error']) && $_GET['error'] == '1') {
    echo '<div class="alert alert-danger alert-dismissible fade show" style="margin: 20px;">
            <i class="fas fa-exclamation-triangle"></i> Une erreur est survenue lors de la soumission du formulaire. Veuillez vérifier tous les champs obligatoires.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

if (isset($_GET['success']) && $_GET['success'] == '1') {
    echo '<div class="alert alert-success alert-dismissible fade show" style="margin: 20px;">
            <i class="fas fa-check-circle"></i> Votre demande a été soumise avec succès !
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Études - Canada</title>
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
        
        .document-supplementaire .btn-remove {
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
            <h1><i class="fas fa-university"></i> Canada - Admission Universitaire</h1>
            <p>Formulaire pour admission dans les établissements canadiens</p>
        </div>

        <form method="post" action="save_canada.php" class="form" enctype="multipart/form-data" id="canada-form">
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
                    <label for="province">Province canadienne souhaitée <span class="required">*</span></label>
                    <select id="province" name="province" class="province-select" required onchange="toggleAttestationProvince()">
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
            
            <!-- Section Choix du niveau -->
            <div class="form-section">
                <h2><i class="fas fa-graduation-cap"></i> Choix du niveau d'études</h2>
                
                <div class="form-group">
                    <label for="niveau_etude">Niveau d'études souhaité <span class="required">*</span></label>
                    <select id="niveau_etude" name="niveau_etude" required onchange="afficherDocumentsNiveau()">
                        <option value="">-- Choisir votre niveau --</option>
                        <option value="bac">Baccalauréat (Undergraduate)</option>
                        <option value="dec">DEC (Diplôme d'études collégiales)</option>
                        <option value="dep">DEP (Diplôme d'études professionnelles)</option>
                        <option value="aec">AEC (Attestation d'études collégiales)</option>
                        <option value="maitrise">Maîtrise (Master)</option>
                        <option value="phd">Doctorat (PhD)</option>
                        <option value="technique">Formation technique/College</option>
                        <option value="langue">Programme de langue</option>
                    </select>
                </div>

                <!-- Informations spécifiques au niveau -->
                <div class="niveau-info" id="info-bac">
                    <h4>🎓 Baccalauréat (Undergraduate)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-dec">
                    <h4>📚 DEC (Diplôme d'études collégiales)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-dep">
                    <h4>⚙️ DEP (Diplôme d'études professionnelles)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-aec">
                    <h4>📝 AEC (Attestation d'études collégiales)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-maitrise">
                    <h4>🎓 Maîtrise (Master)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes du baccalauréat, diplôme de baccalauréat, relevés de notes complets, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-phd">
                    <h4>🔬 Doctorat (PhD)</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes de la maîtrise, diplôme de maîtrise, projet de recherche, CV académique, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-technique">
                    <h4>⚙️ Formation technique/College</h4>
                    <p><strong>Documents requis :</strong> Relevés de notes, diplôme de fin d'études, certificat de scolarité (optionnel)</p>
                </div>
                
                <div class="niveau-info" id="info-langue">
                    <h4>🌍 Programme de langue</h4>
                    <p><strong>Documents requis :</strong> Derniers relevés de notes, certificat de scolarité (optionnel)</p>
                </div>
            </div>
            
            <!-- Section Documents académiques dynamiques -->
            <div class="form-section">
                <h2><i class="fas fa-file-alt"></i> Documents académiques requis</h2>
                
                <div id="docs_obligatoires" class="documents-container">
                    <div class="alert alert-info">
                        Veuillez d'abord sélectionner votre niveau d'études pour voir les documents requis.
                    </div>
                </div>
            </div>
            
            <!-- Section Garant -->
            <div class="form-section">
                <h2><i class="fas fa-user-tie"></i> Informations du garant</h2>
                
                <div class="form-group">
                    <label for="nom_garant">Nom complet du garant <span class="required">*</span></label>
                    <input type="text" id="nom_garant" name="nom_garant" required>
                </div>
                
                <div class="form-group">
                    <label for="relation_garant">Relation avec le garant <span class="required">*</span></label>
                    <select id="relation_garant" name="relation_garant" required>
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
                    <label for="documents_garant">Documents du garant <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="documents_garant" name="documents_garant[]" multiple required>
                        <label for="documents_garant" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir les fichiers</span>
                        </label>
                    </div>
                    <div class="file-hint">Pièces d'identité, justificatifs de revenus, relevés bancaires, etc. (PDF, JPG, PNG - Max. 5MB par fichier)</div>
                </div>
                
                <!-- Attestation de province (Québec) -->
                <div id="attestation_province_container" style="display: none;">
                    <div class="document-optionnel">
                        <h4><i class="fas fa-file-certificate"></i> Attestation de province (Québec)</h4>
                        <p style="margin-bottom: 15px; color: #64748b;">Avez-vous une attestation de province pour le Québec ?</p>
                        
                        <div class="option-group">
                            <input type="radio" id="attestation_oui" name="attestation_province_option" value="oui" onchange="toggleAttestationProvinceUpload(true)">
                            <label for="attestation_oui">Oui, j'ai une attestation de province</label>
                        </div>
                        <div class="option-group">
                            <input type="radio" id="attestation_non" name="attestation_province_option" value="non" onchange="toggleAttestationProvinceUpload(false)" checked>
                            <label for="attestation_non">Non, je n'ai pas d'attestation de province</label>
                        </div>
                        
                        <div id="attestation_province_upload_container" style="display: none; margin-top: 15px;">
                            <div class="file-input-container">
                                <input type="file" id="attestation_province" name="attestation_province">
                                <label for="attestation_province" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text">Télécharger l'attestation de province</span>
                                </label>
                            </div>
                            <div class="file-hint">Attestation de province pour le Québec (PDF - Max. 5MB)</div>
                        </div>
                        
                        <div id="attestation_province_demande_container" style="margin-top: 15px;">
                            <p style="margin-bottom: 10px; color: #64748b;">Si vous n'avez pas d'attestation de province, vous pouvez en faire la demande :</p>
                            <div class="demande-buttons">
                                <a href="demande_attestation_province.php" class="btn btn-info btn-sm">
                                    <i class="fas fa-file-alt"></i> Demander une attestation de province
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- CAQ pour Québec -->
                    <div class="document-optionnel">
                        <h4><i class="fas fa-file-contract"></i> CAQ (Québec)</h4>
                        <p style="margin-bottom: 15px; color: #64748b;">Avez-vous un Certificat d'Acceptation du Québec (CAQ) ?</p>
                        
                        <div class="option-group">
                            <input type="radio" id="caq_oui" name="caq_option" value="oui" onchange="toggleCAQUpload(true)">
                            <label for="caq_oui">Oui, j'ai un CAQ</label>
                        </div>
                        <div class="option-group">
                            <input type="radio" id="caq_non" name="caq_option" value="non" onchange="toggleCAQUpload(false)" checked>
                            <label for="caq_non">Non, je n'ai pas de CAQ</label>
                        </div>
                        
                        <div id="caq_upload_container" style="display: none; margin-top: 15px;">
                            <div class="file-input-container">
                                <input type="file" id="caq" name="caq">
                                <label for="caq" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text">Télécharger le CAQ</span>
                                </label>
                            </div>
                            <div class="file-hint">Certificat d'Acceptation du Québec (PDF - Max. 5MB)</div>
                        </div>
                        
                        <div id="caq_demande_container" style="margin-top: 15px;">
                            <p style="margin-bottom: 10px; color: #64748b;">Si vous n'avez pas de CAQ, vous pouvez en faire la demande :</p>
                            <div class="demande-buttons">
                                <a href="demande_caq.php" class="btn btn-warning btn-sm">
                                    <i class="fas fa-file-alt"></i> Demander un CAQ
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
                        <p style="margin-bottom: 10px; color: #64748b;">Si vous n'avez pas de test de langue, vous pouvez en faire la demande :</p>
                        <div class="demande-buttons">
                            <a href="demande_test_langue.php" class="btn btn-info btn-sm">
                                <i class="fas fa-file-alt"></i> Demander un test de langue
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- CV - Optionnel avec choix -->
                <div class="document-optionnel">
                    <h4><i class="fas fa-file-alt"></i> Curriculum Vitae (Optionnel)</h4>
                    <p style="margin-bottom: 15px; color: #64748b;">Avez-vous un CV à jour ?</p>
                    
                    <div class="option-group">
                        <input type="radio" id="cv_oui" name="cv_option" value="oui" onchange="toggleCVUpload(true)">
                        <label for="cv_oui">Oui, j'ai un CV à jour</label>
                    </div>
                    <div class="option-group">
                        <input type="radio" id="cv_non" name="cv_option" value="non" onchange="toggleCVUpload(false)" checked>
                        <label for="cv_non">Non, je n'ai pas de CV</label>
                    </div>
                    
                    <div id="cv_upload_container" style="display: none; margin-top: 15px;">
                        <div class="file-input-container">
                            <input type="file" id="cv" name="cv">
                            <label for="cv" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Télécharger votre CV</span>
                            </label>
                        </div>
                        <div class="file-hint">CV détaillant votre parcours académique et professionnel (PDF - Max. 5MB)</div>
                    </div>
                    
                    <div id="cv_demande_container" style="margin-top: 15px;">
                        <p style="margin-bottom: 10px; color: #64748b;">Si vous n'avez pas de CV, vous pouvez en faire la demande :</p>
                        <div class="demande-buttons">
                            <a href="demande_cv.php" class="btn btn-warning btn-sm">
                                <i class="fas fa-file-alt"></i> Demander un CV
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Reçu de paiement - Optionnel -->
                <div class="lettre-motivation-option">
                    <h4><i class="fas fa-receipt"></i> Reçu de paiement (Optionnel)</h4>
                    <p style="margin-bottom: 15px; color: #64748b;">Si vous avez déjà effectué un paiement, vous pouvez télécharger le reçu ici.</p>
                    <div class="file-input-container">
                        <input type="file" id="recu_paiement" name="recu_paiement">
                        <label for="recu_paiement" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Télécharger le reçu de paiement</span>
                        </label>
                    </div>
                    <div class="file-hint">Reçu de paiement des frais d'inscription (PDF, JPG, PNG - Max. 5MB)</div>
                </div>
                
                <!-- Documents supplémentaires -->
                <div class="form-group">
                    <h4 style="margin-bottom: 20px; color: var(--canada-red);">
                        <i class="fas fa-plus-circle"></i> Documents supplémentaires
                    </h4>
                    <p style="margin-bottom: 15px; color: #64748b;">
                        Vous pouvez ajouter d'autres documents comme des lettres de recommandation, attestations de stages, formations supplémentaires, etc.
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
                    <i class="fas fa-paper-plane"></i> Soumettre la demande
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Configuration des documents par niveau pour le Canada
    const configDocs = {
        bac: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle"
            }
        ],
        dec: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle"
            }
        ],
        dep: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle"
            }
        ],
        aec: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle"
            }
        ],
        maitrise: [
            { 
                label: "Relevé de notes du baccalauréat", 
                name: "releve_bac", 
                type: "file",
                required: true,
                hint: "Relevés de notes complets du baccalauréat"
            },
            { 
                label: "Diplôme de baccalauréat", 
                name: "diplome_bac", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de baccalauréat"
            },
            { 
                label: "Relevés de notes universitaires complets", 
                name: "releves_universitaires", 
                type: "file",
                required: true,
                hint: "Tous les relevés de notes universitaires"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle en Maîtrise"
            }
        ],
        phd: [
            { 
                label: "Relevé de notes de la maîtrise", 
                name: "releve_maitrise", 
                type: "file",
                required: true,
                hint: "Relevés de notes complets de la maîtrise"
            },
            { 
                label: "Diplôme de maîtrise", 
                name: "diplome_maitrise", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de maîtrise"
            },
            { 
                label: "Projet de recherche", 
                name: "projet_recherche", 
                type: "file",
                required: true,
                hint: "Projet de recherche détaillé pour le doctorat"
            },
            { 
                label: "CV académique", 
                name: "cv_academique", 
                type: "file",
                required: true,
                hint: "Curriculum vitae académique détaillé"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle en Doctorat"
            }
        ],
        technique: [
            { 
                label: "Relevé de notes", 
                name: "releve_notes", 
                type: "file",
                required: true,
                hint: "Relevés de notes des études"
            },
            { 
                label: "Diplôme de fin d'études", 
                name: "diplome_fin_etudes", 
                type: "file",
                required: true,
                hint: "Copie du diplôme de fin d'études"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle en formation technique"
            }
        ],
        langue: [
            { 
                label: "Derniers relevés de notes", 
                name: "releves_notes", 
                type: "file",
                required: true,
                hint: "Derniers relevés de notes disponibles"
            },
            { 
                label: "Certificat de scolarité (Optionnel)", 
                name: "certificat_scolarite", 
                type: "file",
                required: false,
                hint: "Certificat attestant de votre scolarité actuelle en programme de langue"
            }
        ]
    };

    let documentCounter = 0;
    
    // Fonction pour afficher les documents selon le niveau choisi
    function afficherDocumentsNiveau() {
        const niveau = document.getElementById('niveau_etude').value;
        const docsContainer = document.getElementById('docs_obligatoires');
        
        // Masquer toutes les infos de niveau
        document.querySelectorAll('.niveau-info').forEach(info => {
            info.classList.remove('active');
        });
        
        // Afficher l'info du niveau sélectionné
        if (niveau) {
            document.getElementById(`info-${niveau}`).classList.add('active');
        }
        
        if (configDocs[niveau]) {
            const niveauText = document.getElementById('niveau_etude').options[document.getElementById('niveau_etude').selectedIndex].text;
            
            docsContainer.innerHTML = `
                <h3>📚 Documents académiques requis pour ${niveauText}</h3>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Les documents suivants sont obligatoires pour votre niveau d'études au Canada.
                </div>
            `;
            
            configDocs[niveau].forEach(doc => {
                const docElement = document.createElement('div');
                docElement.className = 'form-group';
                
                const requiredStar = doc.required ? '<span class="required">*</span>' : '';
                
                docElement.innerHTML = `
                    <label>${doc.label} ${requiredStar}</label>
                    <div class="file-input-container">
                        <input type="${doc.type}" name="${doc.name}" ${doc.required ? 'required' : ''}>
                        <label class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">${doc.hint} - Formats acceptés: JPG, PNG, PDF (Max 5MB)</div>
                `;
                docsContainer.appendChild(docElement);
                
                // Ajouter l'écouteur d'événement pour le fichier
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
        } else {
            docsContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Veuillez d'abord sélectionner votre niveau d'études pour voir les documents requis.
                </div>
            `;
        }
    }

    // Fonction pour gérer l'affichage des attestations de province
    function toggleAttestationProvince() {
        const province = document.getElementById('province').value;
        const attestationContainer = document.getElementById('attestation_province_container');
        
        if (province === 'quebec') {
            attestationContainer.style.display = 'block';
        } else {
            attestationContainer.style.display = 'none';
        }
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
            const fileText = label.querySelector('.file-text');
            fileText.textContent = 'Télécharger l\'attestation de province';
            label.classList.remove('file-selected');
        }
    }

    // Fonction pour gérer l'upload du CAQ
    function toggleCAQUpload(show) {
        const caqContainer = document.getElementById('caq_upload_container');
        const caqDemandeContainer = document.getElementById('caq_demande_container');
        const caqInput = document.getElementById('caq');
        
        if (show) {
            caqContainer.style.display = 'block';
            caqDemandeContainer.style.display = 'none';
            caqInput.disabled = false;
        } else {
            caqContainer.style.display = 'none';
            caqDemandeContainer.style.display = 'block';
            caqInput.disabled = true;
            caqInput.value = '';
            
            // Réinitialiser le label du fichier
            const label = caqInput.nextElementSibling;
            const fileText = label.querySelector('.file-text');
            fileText.textContent = 'Télécharger le CAQ';
            label.classList.remove('file-selected');
        }
    }

    // Fonction pour gérer l'upload du CV
    function toggleCVUpload(show) {
        const cvContainer = document.getElementById('cv_upload_container');
        const cvDemandeContainer = document.getElementById('cv_demande_container');
        const cvInput = document.getElementById('cv');
        
        if (show) {
            cvContainer.style.display = 'block';
            cvDemandeContainer.style.display = 'none';
            cvInput.disabled = false;
        } else {
            cvContainer.style.display = 'none';
            cvDemandeContainer.style.display = 'block';
            cvInput.disabled = true;
            cvInput.value = '';
            
            // Réinitialiser le label du fichier
            const label = cvInput.nextElementSibling;
            const fileText = label.querySelector('.file-text');
            fileText.textContent = 'Télécharger votre CV';
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
            const fileText = label.querySelector('.file-text');
            fileText.textContent = 'Télécharger votre test de langue';
            label.classList.remove('file-selected');
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
        document.getElementById('canada-form').addEventListener('submit', function(e) {
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
        
        // Initialiser l'affichage si un niveau est déjà sélectionné (après rechargement)
        const niveauSelect = document.getElementById('niveau_etude');
        if (niveauSelect.value) {
            afficherDocumentsNiveau();
        }
    });
</script>
</body>
</html>