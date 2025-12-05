<?php
session_start();

// Définir les messages de session s'ils existent déjà
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Traiter la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulation de traitement des données
    $nom = $_POST['nom'] ?? '';
    $email = $_POST['email'] ?? '';
    
    // Rediriger vers la même page avec un message de succès
    $_SESSION['success_message'] = 'Votre demande d\'admission a été soumise avec succès ! Nous vous contacterons bientôt.';
    
    // Rediriger pour éviter le re-soumission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Vérifier si on affiche la confirmation
$show_confirmation = isset($_SESSION['form_submitted']) && $_SESSION['form_submitted'] === true;
if ($show_confirmation) {
    unset($_SESSION['form_submitted']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Études - Belgique</title>
    <!-- Ajouter Bootstrap pour les alertes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Garder TOUT le CSS existant */
        :root {
            --belgique-black: #070988;
            --belgique-yellow: #FDDA24;
            --belgique-red: #EF3340;
            --light-gray: #f8f9fa;
            --dark-text: #333;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(16, 7, 97, 0.1);
            --transition: all 0.3s ease;
            --primary-blue: #004085;
            --secondary-blue: #007bff;
            --accent-orange: #ff6b35;
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
            background: linear-gradient(135deg, var(--belgique-black) 0%, #2c2c2c 100%);
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
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.05"><text x="50%" y="50%" font-size="20" text-anchor="middle" dominant-baseline="middle" fill="white">🇧🇪</text></svg>');
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
            color: var(--belgique-yellow);
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
            color: var(--belgique-black);
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-section h2 i {
            color: var(--belgique-yellow);
            background: var(--belgique-black);
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
            border-color: var(--belgique-black);
            box-shadow: 0 0 0 3px rgba(7, 9, 136, 0.1);
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
            border-color: var(--belgique-black);
            background: rgba(7, 9, 136, 0.05);
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
            background: var(--belgique-black);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--belgique-red);
            transform: translateY(-2px);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--belgique-black);
            border-color: var(--belgique-black);
        }
        
        .btn-outline:hover {
            background: var(--belgique-black);
            color: var(--white);
        }
        
        .btn-secondary {
            background: var(--accent-orange);
            color: var(--white);
        }
        
        .btn-secondary:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--success-color);
            color: var(--white);
        }
        
        .btn-success:hover {
            background: #0da271;
            transform: translateY(-2px);
        }
        
        .equivalence-section {
            background: rgba(7, 9, 136, 0.05);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border-left: 4px solid var(--belgique-black);
        }
        
        .hidden {
            display: none;
        }
        
        .document-item {
            background: var(--light-bg);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 15px;
            border: 1px solid var(--border-color);
            position: relative;
        }
        
        .remove-document {
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
            font-size: 0.9rem;
        }
        
        .remove-document:hover {
            background: #dc2625;
        }
        
        .add-document-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 20px;
            background: transparent;
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius);
            color: var(--belgique-black);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 10px;
        }
        
        .add-document-btn:hover {
            border-color: var(--belgique-black);
            background: rgba(7, 9, 136, 0.05);
        }
        
        /* Nouveau style pour la section des documents de licence */
        .licence-section {
            background: rgba(255, 107, 53, 0.05);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 25px;
            border-left: 4px solid var(--accent-orange);
        }
        
        /* Styles pour la confirmation */
        .confirmation-message {
            text-align: center;
            padding: 50px 30px;
        }
        
        .confirmation-icon {
            font-size: 5rem;
            color: var(--success-color);
            margin-bottom: 25px;
        }
        
        .confirmation-message h2 {
            color: var(--belgique-black);
            margin-bottom: 20px;
            font-size: 2.2rem;
        }
        
        .confirmation-message p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: #555;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.8;
        }
        
        .confirmation-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
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
            
            .confirmation-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-university"></i> Belgique - Admission Universitaire</h1>
            <p>Formulaire pour admission dans les établissements belges</p>
        </div>

        <?php if (isset($success_message)): ?>
            <!-- Section de confirmation -->
            <div class="confirmation-message">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Demande soumise avec succès !</h2>
                <p><?php echo $success_message; ?></p>
                
                <div class="confirmation-actions">
                    <a href="<?php echo $_SERVER['PHP_SELF']; ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nouvelle demande
                    </a>
                    <a href="../../index.php" class="btn btn-outline">
                        <i class="fas fa-home"></i> Retour à l'accueil
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Formulaire d'admission -->
            <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" class="form" id="belgique-form">
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
                        <label for="telephone">Numéro de téléphone <span class="required">*</span></label>
                        <input type="tel" id="telephone" name="telephone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email <span class="required">*</span></label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="adresse">Adresse complète <span class="required">*</span></label>
                        <textarea id="adresse" name="adresse" rows="3" required></textarea>
                    </div>
                </div>
                
                <!-- Section Remplissage documents -->
                <div class="form-section">
                    <h2><i class="fas fa-file-alt"></i> Remplissage des documents</h2>
                    
                    <div class="form-group">
                        <label for="niveau_etude">Niveau actuel <span class="required">*</span></label>
                        <select id="niveau_etude" name="niveau_etude" required onchange="toggleSections()">
                            <option value="">-- Choisir votre niveau --</option>
                            <option value="bac">Bac</option>
                            <option value="l1">L1</option>
                            <option value="l2">L2</option>
                            <option value="l3">L3</option>
                            <option value="master">Master</option>
                        </select>
                    </div>

                    <!-- Section Équivalence du Bac (uniquement pour L1, L2, L3) -->
                    <div id="equivalence_section" class="equivalence-section hidden">
                        <h3><i class="fas fa-balance-scale"></i> Équivalence du Bac</h3>
                        <div class="form-group">
                            <label>Avez-vous l'équivalence de votre bac pour la Belgique ?</label>
                            <div style="display: flex; gap: 15px; margin-top: 10px;">
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="equivalence_bac" value="oui" onchange="toggleEquivalenceUpload()" style="margin-right: 8px;">
                                    Oui
                                </label>
                                <label style="display: flex; align-items: center; cursor: pointer;">
                                    <input type="radio" name="equivalence_bac" value="non" onchange="toggleEquivalenceUpload()" style="margin-right: 8px;">
                                    Non
                                </label>
                            </div>
                        </div>
                        
                        <div id="equivalence_upload" class="form-group hidden">
                            <label for="document_equivalence">Document d'équivalence du Bac <span class="required">*</span></label>
                            <div class="file-input-container">
                                <input type="file" id="document_equivalence" name="document_equivalence">
                                <label for="document_equivalence" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text">Choisir un fichier</span>
                                </label>
                            </div>
                            <div class="file-hint">Document officiel d'équivalence du Bac (PDF - Max. 5MB)</div>
                        </div>
                        
                        <div id="demande_equivalence" class="form-group hidden">
                            <p style="color: var(--accent-orange); margin-bottom: 15px;">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Vous devez demander l'équivalence de votre bac pour pouvoir étudier en Belgique.
                            </p>
                            <button type="button" class="btn btn-secondary" onclick="demanderEquivalence()">
                                <i class="fas fa-file-contract"></i> Demander l'équivalence
                            </button>
                        </div>
                    </div>

                    <!-- Section Documents de Licence (uniquement pour Master) -->
                    <div id="licence_section" class="licence-section hidden">
                        <h3><i class="fas fa-graduation-cap"></i> Documents de Licence</h3>
                        <p style="margin-bottom: 20px; color: #666;">
                            Pour une admission en Master, vous devez fournir les documents suivants relatifs à votre Licence :
                        </p>
                        
                        <div class="form-group">
                            <label for="releves_licence_l1">Relevés de notes Licence 1 <span class="required">*</span></label>
                            <div class="file-input-container">
                                <input type="file" id="releves_licence_l1" name="releves_licence_l1[]" multiple>
                                <label for="releves_licence_l1" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text">Choisir un ou plusieurs fichiers</span>
                                </label>
                            </div>
                            <div class="file-hint">Tous les relevés de notes de L1 (PDF, JPG, PNG - Max 5MB par fichier)</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="releves_licence_l2">Relevés de notes Licence 2 <span class="required">*</span></label>
                            <div class="file-input-container">
                                <input type="file" id="releves_licence_l2" name="releves_licence_l2[]" multiple>
                                <label for="releves_licence_l2" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text">Choisir un ou plusieurs fichiers</span>
                                </label>
                            </div>
                            <div class="file-hint">Tous les relevés de notes de L2 (PDF, JPG, PNG - Max 5MB par fichier)</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="releves_licence_l3">Relevés de notes Licence 3 <span class="required">*</span></label>
                            <div class="file-input-container">
                                <input type="file" id="releves_licence_l3" name="releves_licence_l3[]" multiple>
                                <label for="releves_licence_l3" class="file-label">
                                    <i class="fas fa-upload"></i>
                                    <span class="file-text">Choisir un ou plusieurs fichiers</span>
                                </label>
                            </div>
                            <div class="file-hint">Tous les relevés de notes de L3 (PDF, JPG, PNG - Max 5MB par fichier)</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="diplome_licence">Diplôme de Licence <span class="required">*</span></label>
                            <div class="file-input-container">
                                <input type="file" id="diplome_licence" name="diplome_licence">
                                <label for="diplome_licence" class="file-label">
                                    <i class="fas fa-file-certificate"></i>
                                    <span class="file-text">Choisir le fichier</span>
                                </label>
                            </div>
                            <div class="file-hint">Diplôme de Licence (PDF, JPG, PNG - Max 5MB)</div>
                        </div>
                    </div>

                    <!-- Conteneur pour les documents obligatoires selon le niveau -->
                    <div id="docs_obligatoires"></div>
                </div>
                
                <!-- Documents communs -->
                <div class="form-section">
                    <h2><i class="fas fa-file-upload"></i> Documents requis pour tous</h2>
                    
                    <div class="form-group">
                        <label for="photo">Photo d'identité <span class="required">*</span></label>
                        <div class="file-input-container">
                            <input type="file" id="photo" name="photo">
                            <label for="photo" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un fichier</span>
                            </label>
                        </div>
                        <div class="file-hint">Photo d'identité récente (PNG, JPG - Max. 5MB)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="passport">Passeport <span class="required">*</span></label>
                        <div class="file-input-container">
                            <input type="file" id="passport" name="passport">
                            <label for="passport" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un fichier</span>
                            </label>
                        </div>
                        <div class="file-hint">Passeport en cours de validité (PDF, JPG - Max. 5MB)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="certificat_scolarite_actuel">Certificat de scolarité actuel <span class="required">*</span></label>
                        <div class="file-input-container">
                            <input type="file" id="certificat_scolarite_actuel" name="certificat_scolarite_actuel">
                            <label for="certificat_scolarite_actuel" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un fichier</span>
                            </label>
                        </div>
                        <div class="file-hint">Certificat de scolarité de l'année en cours (PDF, JPG - Max. 5MB)</div>
                    </div>
                </div>

                <!-- Documents supplémentaires -->
                <div class="form-section">
                    <h2><i class="fas fa-file-medical"></i> Documents supplémentaires</h2>
                    <p style="margin-bottom: 20px; color: #64748b;">
                        Vous pouvez ajouter des documents supplémentaires comme des lettres de recommandation, 
                        attestations de formation, ou autres documents pertinents.
                    </p>
                    
                    <div id="documents-supplementaires">
                        <!-- Les documents supplémentaires seront ajoutés ici dynamiquement -->
                    </div>
                    
                    <div class="add-document-btn" onclick="ajouterDocumentSupplementaire()">
                        <i class="fas fa-plus"></i>
                        Ajouter un document
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Soumettre la demande
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    let documentCounter = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // File input labels
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function() {
                const label = this.nextElementSibling;
                const fileText = label.querySelector('.file-text');
                
                if (this.files.length > 0) {
                    if (this.multiple && this.files.length > 1) {
                        fileText.textContent = this.files.length + ' fichiers sélectionnés';
                    } else {
                        fileText.textContent = this.files[0].name;
                    }
                    label.classList.add('file-selected');
                } else {
                    fileText.textContent = this.multiple ? 'Choisir un ou plusieurs fichiers' : 'Choisir un fichier';
                    label.classList.remove('file-selected');
                }
            });
        });
        
        // Form validation before submission
        document.getElementById('belgique-form')?.addEventListener('submit', function(e) {
            // Validation basique
            const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
            let valid = true;
            
            requiredFields.forEach(field => {
                if (!field.value) {
                    valid = false;
                    field.style.borderColor = 'var(--error-color)';
                } else {
                    field.style.borderColor = '';
                }
            });
            
            // Vérification spécifique pour l'équivalence du bac
            const niveau = document.getElementById('niveau_etude').value;
            const equivalenceSection = document.getElementById('equivalence_section');
            
            if (!equivalenceSection.classList.contains('hidden')) {
                const hasEquivalence = document.querySelector('input[name="equivalence_bac"]:checked');
                if (!hasEquivalence) {
                    valid = false;
                    alert('Veuillez indiquer si vous avez l\'équivalence du bac');
                    return false;
                }
            }
            
            if (!valid) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs obligatoires');
                return;
            }
            
            // Confirmation avant soumission
            if (!confirm('Êtes-vous sûr de vouloir soumettre votre demande d\'admission ?')) {
                e.preventDefault();
                return;
            }
        });
    });
    
    // Configuration des documents par niveau
    const configDocs = {
        bac: [
            { label: "Relevé de notes 1ère année", name: "releve_2nde", type: "file" },
            { label: "Relevé de notes 2ème année", name: "releve_1ere", type: "file" },
            { label: "Relevé de notes Terminale", name: "releve_terminale", type: "file" },
            { label: "Relevé de notes Bac", name: "releve_bac", type: "file" }
        ],
        l1: [
            { label: "Relevé de notes 1ère année", name: "releve_2nde", type: "file" },
            { label: "Relevé de notes 2ème année", name: "releve_1ere", type: "file" },
            { label: "Relevé de notes Terminale", name: "releve_terminale", type: "file" },
            { label: "Relevé de notes Bac", name: "releve_bac", type: "file" },
            { label: "Diplôme Bac", name: "diplome_bac", type: "file" }
        ],
        l2: [
            { label: "Relevé de notes Bac", name: "releve_bac", type: "file" },
            { label: "Diplôme Bac", name: "diplome_bac", type: "file" },
            { label: "Relevé de notes L1", name: "releve_l1", type: "file" }
        ],
        l3: [
            { label: "Relevé de notes Bac", name: "releve_bac", type: "file" },
            { label: "Diplôme Bac", name: "diplome_bac", type: "file" },
            { label: "Relevé de notes L1", name: "releve_l1", type: "file" },
            { label: "Relevé de notes L2", name: "releve_l2", type: "file" }
        ],
        master: [
            { label: "Relevé de notes Bac", name: "releve_bac", type: "file" },
            { label: "Diplôme Bac", name: "diplome_bac", type: "file" }
        ]
    };
    
    // Fonction pour afficher/masquer les sections
    function toggleSections() {
        const niveau = document.getElementById('niveau_etude').value;
        const equivalenceSection = document.getElementById('equivalence_section');
        const licenceSection = document.getElementById('licence_section');
        
        // Section Équivalence du Bac (uniquement pour L1, L2, L3)
        if (niveau === 'l1' || niveau === 'l2' || niveau === 'l3') {
            equivalenceSection.classList.remove('hidden');
        } else {
            equivalenceSection.classList.add('hidden');
            // Réinitialiser les champs d'équivalence
            document.querySelectorAll('input[name="equivalence_bac"]').forEach(radio => {
                radio.checked = false;
            });
            document.getElementById('equivalence_upload').classList.add('hidden');
            document.getElementById('demande_equivalence').classList.add('hidden');
        }
        
        // Section Documents de Licence (uniquement pour Master)
        if (niveau === 'master') {
            licenceSection.classList.remove('hidden');
        } else {
            licenceSection.classList.add('hidden');
            // Réinitialiser les champs de licence
            const licenceInputs = licenceSection.querySelectorAll('input[type="file"]');
            licenceInputs.forEach(input => {
                input.value = '';
                const label = input.nextElementSibling;
                const fileText = label.querySelector('.file-text');
                fileText.textContent = input.multiple ? 'Choisir un ou plusieurs fichiers' : 'Choisir un fichier';
                label.classList.remove('file-selected');
            });
        }
        
        // Afficher les documents obligatoires selon le niveau
        renderDocs();
    }
    
    // Fonction pour gérer l'affichage des options d'équivalence
    function toggleEquivalenceUpload() {
        const hasEquivalence = document.querySelector('input[name="equivalence_bac"]:checked');
        const uploadSection = document.getElementById('equivalence_upload');
        const demandeSection = document.getElementById('demande_equivalence');
        
        if (hasEquivalence) {
            if (hasEquivalence.value === 'oui') {
                uploadSection.classList.remove('hidden');
                demandeSection.classList.add('hidden');
            } else {
                uploadSection.classList.add('hidden');
                demandeSection.classList.remove('hidden');
            }
        }
    }
    
    // Fonction pour simuler la demande d'équivalence
    function demanderEquivalence() {
        // Redirection vers la page d'équivalence
        window.location.href = 'equivalence.php';
    }
    
    // Fonction pour afficher les champs selon le niveau choisi
    function renderDocs() {
        const niveau = document.getElementById('niveau_etude').value;
        const docsContainer = document.getElementById('docs_obligatoires');
        docsContainer.innerHTML = '';

        if (configDocs[niveau]) {
            docsContainer.innerHTML = '<h3>Documents spécifiques à votre niveau</h3>';
            
            configDocs[niveau].forEach(doc => {
                const docElement = document.createElement('div');
                docElement.className = 'form-group';
                docElement.innerHTML = `
                    <label>${doc.label} <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="${doc.type}" name="${doc.name}" required>
                        <label class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Formats acceptés: JPG, PNG, PDF (Max 5MB)</div>
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
    function ajouterDocumentSupplementaire() {
        documentCounter++;
        const container = document.getElementById('documents-supplementaires');
        
        const documentItem = document.createElement('div');
        documentItem.className = 'document-item';
        documentItem.innerHTML = `
            <button type="button" class="remove-document" onclick="supprimerDocument(this)">
                <i class="fas fa-times"></i>
            </button>
            <div class="form-group">
                <label for="document_supp_${documentCounter}">Type de document</label>
                <select name="type_document_supp[]" onchange="updateDocumentLabel(this)">
                    <option value="">-- Choisir le type --</option>
                    <option value="lettre_recommandation">Lettre de recommandation</option>
                    <option value="attestation_formation">Attestation de formation</option>
                    <option value="certificat_langue">Certificat de langue</option>
                    <option value="cv">CV</option>
                    <option value="autre">Autre document</option>
                </select>
            </div>
            <div class="form-group">
                <label for="document_supp_${documentCounter}">Document</label>
                <div class="file-input-container">
                    <input type="file" id="document_supp_${documentCounter}" name="document_supp[]">
                    <label for="document_supp_${documentCounter}" class="file-label">
                        <i class="fas fa-upload"></i>
                        <span class="file-text">Choisir un fichier</span>
                    </label>
                </div>
                <div class="file-hint">Formats acceptés: JPG, PNG, PDF (Max 5MB)</div>
            </div>
        `;
        
        container.appendChild(documentItem);
        
        // Ajouter l'event listener pour le nouveau file input
        const fileInput = documentItem.querySelector('input[type="file"]');
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
        const documentItem = button.closest('.document-item');
        documentItem.remove();
    }
    
    // Fonction pour mettre à jour le label selon le type de document
    function updateDocumentLabel(select) {
        const documentItem = select.closest('.document-item');
        const fileInput = documentItem.querySelector('input[type="file"]');
        const fileLabel = documentItem.querySelector('.file-label .file-text');
        
        if (select.value) {
            fileLabel.textContent = 'Choisir un fichier';
        }
    }
</script>
</body>
</html>