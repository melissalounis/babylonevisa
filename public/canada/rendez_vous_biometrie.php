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
    <title>Rendez-vous Biométrie - Canada</title>
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
        input[type="time"],
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
        
        .info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-box h4 {
            color: var(--canada-red);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }
        
        .personne-supplementaire {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .personne-supplementaire .btn-remove {
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
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-header">
            <h1><i class="fas fa-fingerprint"></i> Rendez-vous Biométrie - Canada</h1>
            <p>Formulaire de prise de rendez-vous pour la collecte des données biométriques</p>
        </div>

        <form method="post" action="save_biometrie.php" class="form" enctype="multipart/form-data" id="biometrie-form">
            <!-- Informations personnelles -->
            <div class="form-section">
                <h2><i class="fas fa-user"></i> Informations personnelles</h2>
                
                <div class="info-box">
                    <h4><i class="fas fa-info-circle"></i> Informations importantes</h4>
                    <p>La biométrie est obligatoire pour les demandes de visa canadien. Elle comprend la prise d'empreintes digitales et une photo.</p>
                </div>
                
                <div class="form-group">
                    <label for="nom_complet">Nom complet <span class="required">*</span></label>
                    <input type="text" id="nom_complet" name="nom_complet" required placeholder="Ex: Jean Dupont">
                </div>
                
                <div class="form-group">
                    <label for="date_naissance">Date de naissance <span class="required">*</span></label>
                    <input type="date" id="date_naissance" name="date_naissance" required>
                </div>
                
                <div class="form-group">
                    <label for="nationalite">Nationalité <span class="required">*</span></label>
                    <input type="text" id="nationalite" name="nationalite" required placeholder="Ex: Française">
                </div>
                
                <div class="form-group">
                    <label for="numero_passeport">Numéro de passeport <span class="required">*</span></label>
                    <input type="text" id="numero_passeport" name="numero_passeport" required placeholder="Ex: 12AB34567">
                </div>
                
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" required placeholder="exemple@email.com">
                </div>
                
                <div class="form-group">
                    <label for="telephone">Numéro de téléphone <span class="required">*</span></label>
                    <input type="tel" id="telephone" name="telephone" required placeholder="Ex: +33 1 23 45 67 89">
                </div>
            </div>
            
            <!-- Section Personnes supplémentaires -->
            <div class="form-section">
                <h2><i class="fas fa-users"></i> Personnes supplémentaires</h2>
                
                <div class="info-box">
                    <h4><i class="fas fa-users"></i> Informations groupe</h4>
                    <p>Vous pouvez prendre rendez-vous pour plusieurs personnes en même temps (famille, groupe). Toutes les personnes doivent se présenter au rendez-vous.</p>
                </div>
                
                <div id="personnes_supplementaires">
                    <!-- Les personnes supplémentaires seront ajoutées ici dynamiquement -->
                </div>
                
                <button type="button" class="btn btn-outline btn-sm" onclick="ajouterPersonneSupplementaire()" id="btn-ajouter-personne">
                    <i class="fas fa-user-plus"></i> Ajouter une personne
                </button>
            </div>
            
            <!-- Section Documents requis -->
            <div class="form-section">
                <h2><i class="fas fa-file-upload"></i> Documents requis</h2>
                
                <div class="info-box">
                    <h4><i class="fas fa-list-check"></i> Documents à apporter</h4>
                    <ul>
                        <li>Passeport valide</li>
                        <li>Lettre de biométrie</li>
                        <li>Formulaire de demande de visa complété</li>
                    </ul>
                </div>
                
                <!-- Passeport -->
                <div class="form-group">
                    <label for="passeport">Passeport <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="passeport" name="passeport" required accept=".pdf,.jpg,.jpeg,.png">
                        <label for="passeport" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Pages du passeport avec photo et informations (PDF, JPG, PNG - Max. 5MB)</div>
                </div>
                
                <!-- Lettre de biométrie -->
                <div class="form-group">
                    <label for="lettre_biometrie">Lettre de biométrie <span class="required">*</span></label>
                    <div class="file-input-container">
                        <input type="file" id="lettre_biometrie" name="lettre_biometrie" required accept=".pdf,.jpg,.jpeg,.png">
                        <label for="lettre_biometrie" class="file-label">
                            <i class="fas fa-upload"></i>
                            <span class="file-text">Choisir un fichier</span>
                        </label>
                    </div>
                    <div class="file-hint">Lettre de biométrie (PDF, JPG, PNG - Max. 5MB)</div>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="btn-submit">
                    <i class="fas fa-calendar-check"></i> Confirmer le rendez-vous
                </button>
                <button type="reset" class="btn btn-outline">
                    <i class="fas fa-undo"></i> Réinitialiser
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let personneCounter = 0;
    const MAX_PERSONNES = 6; // Maximum 6 personnes par rendez-vous
    
    // Fonction pour ajouter une personne supplémentaire
    function ajouterPersonneSupplementaire() {
        if (personneCounter >= MAX_PERSONNES) {
            alert(`Maximum ${MAX_PERSONNES} personnes par rendez-vous`);
            return;
        }
        
        personneCounter++;
        const container = document.getElementById('personnes_supplementaires');
        const btnAjouter = document.getElementById('btn-ajouter-personne');
        
        const personneElement = document.createElement('div');
        personneElement.className = 'personne-supplementaire';
        personneElement.innerHTML = `
            <button type="button" class="btn-remove" onclick="supprimerPersonneSupplementaire(this)">
                <i class="fas fa-times"></i>
            </button>
            <h4 style="margin-bottom: 15px; color: var(--canada-red);">Personne ${personneCounter}</h4>
            <div class="form-group">
                <label>Nom complet <span class="required">*</span></label>
                <input type="text" name="personne_nom_${personneCounter}" required placeholder="Ex: Marie Dupont">
            </div>
            <div class="form-group">
                <label>Date de naissance <span class="required">*</span></label>
                <input type="date" name="personne_naissance_${personneCounter}" required>
            </div>
            <div class="form-group">
                <label>Numéro de passeport <span class="required">*</span></label>
                <input type="text" name="personne_passeport_${personneCounter}" required placeholder="Ex: 12AB34567">
            </div>
            <div class="form-group">
                <label>Passeport <span class="required">*</span></label>
                <div class="file-input-container">
                    <input type="file" name="personne_passeport_file_${personneCounter}" required accept=".pdf,.jpg,.jpeg,.png">
                    <label class="file-label">
                        <i class="fas fa-upload"></i>
                        <span class="file-text">Choisir un fichier</span>
                    </label>
                </div>
                <div class="file-hint">Pages du passeport avec photo et informations (PDF, JPG, PNG - Max. 5MB)</div>
            </div>
            <div class="form-group">
                <label>Lettre de biométrie <span class="required">*</span></label>
                <div class="file-input-container">
                    <input type="file" name="personne_lettre_biometrie_${personneCounter}" required accept=".pdf,.jpg,.jpeg,.png">
                    <label class="file-label">
                        <i class="fas fa-upload"></i>
                        <span class="file-text">Choisir un fichier</span>
                    </label>
                </div>
                <div class="file-hint">Lettre de biométrie (PDF, JPG, PNG - Max. 5MB)</div>
            </div>
        `;
        
        container.appendChild(personneElement);
        
        // Initialiser les file inputs pour la nouvelle personne
        const newFileInputs = personneElement.querySelectorAll('input[type="file"]');
        newFileInputs.forEach(input => {
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
        
        // Mettre à jour le bouton d'ajout
        if (personneCounter >= MAX_PERSONNES) {
            btnAjouter.disabled = true;
            btnAjouter.style.opacity = '0.5';
            btnAjouter.title = 'Nombre maximum de personnes atteint';
        }
    }
    
    // Fonction pour supprimer une personne supplémentaire
    function supprimerPersonneSupplementaire(button) {
        const personneElement = button.closest('.personne-supplementaire');
        personneElement.remove();
        personneCounter--;
        
        // Réactiver le bouton d'ajout si nécessaire
        const btnAjouter = document.getElementById('btn-ajouter-personne');
        if (personneCounter < MAX_PERSONNES) {
            btnAjouter.disabled = false;
            btnAjouter.style.opacity = '1';
            btnAjouter.title = '';
        }
    }
    
    // Validation du formulaire
    function validateForm() {
        let isValid = true;
        
        // Réinitialiser les erreurs
        document.querySelectorAll('input, select').forEach(field => {
            field.style.borderColor = '';
        });
        
        // Validation de l'âge (au moins 14 ans pour la biométrie)
        const birthdate = new Date(document.getElementById('date_naissance').value);
        const today = new Date();
        const age = today.getFullYear() - birthdate.getFullYear();
        const monthDiff = today.getMonth() - birthdate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
            age--;
        }
        
        if (age < 14) {
            alert('La biométrie est requise pour les personnes de 14 ans et plus.');
            document.getElementById('date_naissance').style.borderColor = 'var(--error-color)';
            isValid = false;
        }
        
        // Validation des champs requis
        const requiredFields = document.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'var(--error-color)';
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // File input labels pour les champs fixes
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
        
        // Form validation before submission
        document.getElementById('biometrie-form').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return;
            }
            
            // Désactiver le bouton de soumission
            const submitBtn = document.getElementById('btn-submit');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
        });
        
        // Date de naissance maximum = aujourd'hui - 14 ans
        const dateNaissance = document.getElementById('date_naissance');
        if (dateNaissance) {
            const minBirthDate = new Date();
            minBirthDate.setFullYear(minBirthDate.getFullYear() - 120); // 120 ans maximum
            dateNaissance.max = new Date().toISOString().split('T')[0];
            dateNaissance.min = minBirthDate.toISOString().split('T')[0];
        }
    });
</script>
</body>
</html>