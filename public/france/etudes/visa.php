<script>
function toggleVisaFields() {
  const select = document.querySelector("select[name='voyages_precedents']").value;
  const details = document.getElementById("details_visa");
  details.style.display = (select === "oui") ? "block" : "none";
  
  // Mettre à jour le champ hidden pour a_deja_visa
  document.getElementById("a_deja_visa").value = select;
}

function generateVisaInputs() {
  const nb = parseInt(document.getElementById("nb_visas").value) || 0;
  const container = document.getElementById("visa_inputs");
  container.innerHTML = "";

  for (let i = 1; i <= nb; i++) {
    const div = document.createElement("div");
    div.classList.add("form-group");
    div.innerHTML = `<label>Copie du visa n°${i}</label>
                     <input type="file" name="copies_visas[]" accept=".pdf,.jpg,.png">`;
    container.appendChild(div);
  }
}

// Appeler la fonction au chargement de la page pour initialiser l'état
document.addEventListener('DOMContentLoaded', function() {
  toggleVisaFields();
});
</script>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande de Visa - France</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Variables CSS */
        :root {
            --primary-blue: #0056b3;
            --secondary-blue: #0077ff;
            --accent-orange: #ff6b35;
            --light-bg: #f8fafc;
            --dark-text: #2d3748;
            --white: #ffffff;
            --light-gray: #e2e8f0;
            --border-color: #e5e7eb;
            --success-color: #10b981;
            --error-color: #ef4444;
            --transition: all 0.3s ease;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --box-shadow-hover: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        /* Styles généraux */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark-text);
            line-height: 1.6;
            padding: 20px;
        }

        /* Conteneur principal */
        .visa-container {
            max-width: 800px;
            margin: 0 auto;
        }

        /* Carte principale */
        .visa-card {
            background: var(--white);
            border-radius: 16px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }

        /* En-tête */
        .card-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: var(--white);
            padding: 30px;
            text-align: center;
        }

        .card-header h1 {
            font-size: 2.2rem;
            margin: 0 0 10px 0;
            font-weight: 700;
        }

        .card-header h1 i {
            margin-right: 10px;
        }

        .card-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin: 0;
        }

        /* Formulaire */
        .visa-form {
            padding: 30px;
        }

        /* Sections du formulaire */
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--light-gray);
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .form-section h2 {
            font-size: 1.4rem;
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-weight: 600;
        }

        /* Groupes de formulaire */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-text);
        }

        .required {
            color: var(--error-color);
        }

        /* Champs de formulaire */
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
        }

        /* Input fichier personnalisé */
        .file-input-container {
            position: relative;
            margin-bottom: 5px;
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
            padding: 12px 16px;
            background: var(--light-bg);
            border: 2px dashed var(--border-color);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: var(--transition);
        }

        .file-label:hover {
            border-color: var(--primary-blue);
            background: rgba(0, 86, 179, 0.05);
        }

        .file-label.file-selected {
            border-color: var(--success-color);
            background: rgba(16, 185, 129, 0.05);
        }

        .file-label i {
            margin-right: 10px;
            color: var(--primary-blue);
        }

        .file-hint {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 5px;
        }

        /* Info garant */
        .info-box {
            background: #e0f2fe;
            border-left: 4px solid var(--primary-blue);
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .info-box h4 {
            color: var(--primary-blue);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .info-box ul {
            margin-left: 20px;
            margin-bottom: 15px;
        }

        .info-box li {
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .info-box p {
            font-style: italic;
            color: #374151;
            font-size: 0.9rem;
        }

        /* Fieldset */
        fieldset {
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 20px;
            background: var(--light-bg);
        }

        legend {
            font-weight: 600;
            color: var(--primary-blue);
            padding: 0 10px;
            font-size: 1.1rem;
        }

        /* Actions du formulaire */
        .form-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--light-gray);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 12px 24px;
            border-radius: var(--border-radius);
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            border: 2px solid transparent;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--secondary-blue);
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-blue);
            border-color: var(--primary-blue);
        }

        .btn-outline:hover {
            background: var(--primary-blue);
            color: var(--white);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-header {
                padding: 20px;
            }
            
            .visa-form {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .card-header h1 {
                font-size: 1.8rem;
            }
            
            fieldset {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="visa-container">
        <div class="visa-card">
            <div class="card-header">
                <h1><i class="fas fa-passport"></i> Demande de Visa</h1>
                <p>Formulaire de demande de visa pour la France</p>
            </div>
            
            <form method="post" action="/babylone/public/france/utils/save.php" class="visa-form" enctype="multipart/form-data">
                <!-- Informations personnelles -->
                <div class="form-section">
                    <h2>Informations personnelles</h2>
                    <div class="form-group">
                        <label for="nom_complet">Nom et prénom complet <span class="required">*</span></label>
                        <input type="text" id="nom_complet" name="nom_complet" required placeholder="Votre nom complet">
                    </div>

                    <div class="form-group">
                        <label for="num_identite">Numéro d'identité <span class="required">*</span></label>
                        <input type="text" id="num_identite" name="num_identite" required placeholder="Votre numéro d'identité">
                    </div>

                    <div class="form-group">
                        <label for="passport">Passeport <span class="required">*</span></label>
                        <div class="file-input-container">
                            <input type="file" id="passport" name="passport" accept=".jpg,.jpeg,.png,.pdf" required>
                            <label for="passport" class="file-label">
                                <i class="fas fa-upload"></i>
                                <span class="file-text">Choisir un fichier</span>
                            </label>
                        </div>
                        <div class="file-hint">Formats acceptés: JPG, PNG, PDF (Max 5MB)</div>
                    </div>
                </div>

                <!-- Informations Visa -->
                <div class="form-section">
                    <h2>Informations sur le visa</h2>
                    
                    <!-- Info documents garant -->
                        <div id="garants_container"></div>
                    <div class="info-box">
                        <h4><i class="fas fa-info-circle"></i> Documents nécessaires pour les garants :</h4>
                        <ul>
                            <li><strong>Documents communs :</strong> pièce d'identité, justificatif de revenus, adresse complète, relation avec le demandeur.</li>
                            <li><strong>Garant fonctionnaire / salarié :</strong> fiche de paie 3 derniers mois, attestation employeur (Algérien) ou bulletin de salaire + contrat de travail (Français).</li>
                            <li><strong>Garant commerçant / artisan :</strong> registre du commerce, relevés bancaires, déclaration fiscale (Algérien) ou extrait Kbis, relevés bancaires, justificatif fiscal (Français).</li>
                            <li><strong>Garant entrepreneur :</strong> statuts société, registre du commerce, relevés bancaires (Algérien) ou statuts société, extrait Kbis, relevés bancaires (Français).</li>
                            <li><strong>Garant retraité :</strong> attestation de pension, relevés bancaires.</li>
                            <li><strong>Autre :</strong> justificatifs adaptés selon situation.</li>
                        </ul>
                        <p>Ces informations sont nécessaires pour que nous puissions remplir correctement votre demande de visa.</p>
                    </div>

                    <div class="form-group">
                        <label for="nb_garant">Nombre de garants <span class="required">*</span></label>
                        <select id="nb_garant" name="nb_garant" required>
                            <option value="">-- Choisir --</option>
                            <option value="1">1 garant</option>
                            <option value="2">2 garants</option>
                            <option value="3">3 garants</option>
                        </select>
                    </div>

                 

                    <div class="form-group">
                        <label for="hebergement_type">Type d'hébergement <span class="required">*</span></label>
                        <select id="hebergement_type" name="hebergement_type" required>
                            <option value="">-- Choisir --</option>
                            <option value="chez_un_particulier">Chez un particulier</option>
                            <option value="hotel">Réservation d'hôtel</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div id="hebergement_details" style="display:none;">
                        <div class="form-group">
                            <label for="hebergeur_nom">Nom de l'hébergeur <span class="required">*</span></label>
                            <input type="text" id="hebergeur_nom" name="hebergeur_nom" placeholder="Nom complet de l'hébergeur">
                        </div>

                        <div class="form-group">
                            <label for="hebergeur_adresse">Adresse complète <span class="required">*</span></label>
                            <input type="text" id="hebergeur_adresse" name="hebergeur_adresse" placeholder="Adresse de l'hébergeur">
                        </div>

                        <div class="form-group">
                            <label for="hebergeur_tel">Téléphone <span class="required">*</span></label>
                            <input type="text" id="hebergeur_tel" name="hebergeur_tel" placeholder="Numéro de téléphone">
                        </div>

                        <div class="form-group">
                            <label for="hebergeur_relation">Relation avec l'hébergeur <span class="required">*</span></label>
                            <input type="text" id="hebergeur_relation" name="hebergeur_relation" placeholder="Ex: Parent, Ami, Collègue">
                        </div>
                    </div>
                    <!-- Voyages précédents -->
<div class="form-section">
  <h4> Voyages précédents</h4>
  <div class="form-group">
    <label>Avez-vous déjà voyagé en France ou dans l'espace Schengen ?</label>
    <select name="voyages_precedents" onchange="toggleVisaFields()">
      <option value="non">Non</option>
      <option value="oui">Oui</option>
    </select>
    <!-- Champ caché pour faciliter le traitement -->
    <input type="hidden" name="a_deja_visa" id="a_deja_visa" value="non">
  </div>
  
  <div id="details_visa" style="display:none;">
    <div class="form-group">
      <label>Nombre de visas obtenus</label>
      <input type="number" name="nb_visas" id="nb_visas" min="1" onchange="generateVisaInputs()">
    </div>
    <div id="visa_inputs"></div>
    
   
</div>
<script>
function toggleVisaFields() {
  const select = document.querySelector("select[name='voyages_precedents']").value;
  const details = document.getElementById("details_visa");
  details.style.display = (select === "oui") ? "block" : "none";
  
  // Mettre à jour le champ hidden pour a_deja_visa
  document.getElementById("a_deja_visa").value = select;
}

function generateVisaInputs() {
  const nb = parseInt(document.getElementById("nb_visas").value) || 0;
  const container = document.getElementById("visa_inputs");
  container.innerHTML = "";

  for (let i = 1; i <= nb; i++) {
    const div = document.createElement("div");
    div.classList.add("form-group");
    div.innerHTML = `<label>Copie du visa n°${i}</label>
                     <input type="file" name="copies_visas[]" accept=".pdf,.jpg,.png">`;
    container.appendChild(div);
  }
}

// Appeler la fonction au chargement de la page pour initialiser l'état
document.addEventListener('DOMContentLoaded', function() {
  toggleVisaFields();
});
</script>

                    <div class="form-group">
                        <label for="visa_commentaire">Autres informations / remarques</label>
                        <textarea id="visa_commentaire" name="visa_commentaire" rows="4" placeholder="Informations supplémentaires..."></textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="form-actions">
                    <button type="submit" name="action" value="save" class="btn btn-outline">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <button type="submit" name="action" value="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // Gestion dynamique des garants
        const nbGarantSelect = document.getElementById('nb_garant');
        const garantsContainer = document.getElementById('garants_container');

        function renderGarants() {
            garantsContainer.innerHTML = '';
            const n = parseInt(nbGarantSelect.value) || 0;
            
            for(let i = 1; i <= n; i++) {
                const garantFieldset = document.createElement('fieldset');
                garantFieldset.innerHTML = `
                    <legend>Garant ${i}</legend>
                    <div class="form-group">
                        <label for="garant_nom_${i}">Nom complet <span class="required">*</span></label>
                        <input type="text" id="garant_nom_${i}" name="garant_nom_${i}" required placeholder="Nom complet du garant">
                    </div>

                    <div class="form-group">
                        <label for="garant_nationalite_${i}">Nationalité <span class="required">*</span></label>
                        <select id="garant_nationalite_${i}" name="garant_nationalite_${i}" required>
                            <option value="">-- Choisir --</option>
                            <option value="algerien">Algérien</option>
                            <option value="francais">Français</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="garant_type_${i}">Type de garant <span class="required">*</span></label>
                        <select id="garant_type_${i}" name="garant_type_${i}" required>
                            <option value="">-- Choisir --</option>
                            <option value="fonctionnaire">Fonctionnaire</option>
                            <option value="commercant">Commerçant / Artisan</option>
                            <option value="entrepreneur">Entrepreneur</option>
                            <option value="retraite">Retraité</option>
                            <option value="autre">Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="garant_adresse_${i}">Adresse complète <span class="required">*</span></label>
                        <input type="text" id="garant_adresse_${i}" name="garant_adresse_${i}" required placeholder="Adresse du garant">
                    </div>

                    <div class="form-group">
                        <label for="garant_email_${i}">Email</label>
                        <input type="email" id="garant_email_${i}" name="garant_email_${i}" placeholder="email@exemple.com">
                    </div>

                    <div class="form-group">
                        <label for="garant_tel_${i}">Téléphone <span class="required">*</span></label>
                        <input type="text" id="garant_tel_${i}" name="garant_tel_${i}" required placeholder="Numéro de téléphone">
                    </div>

                    <div class="form-group">
                        <label for="garant_revenus_${i}">Revenus / Profession</label>
                        <input type="text" id="garant_revenus_${i}" name="garant_revenus_${i}" placeholder="Revenus mensuels ou profession">
                    </div>

                    <div class="form-group">
                        <label for="garant_commentaire_${i}">Commentaire</label>
                        <input type="text" id="garant_commentaire_${i}" name="garant_commentaire_${i}" placeholder="Informations supplémentaires">
                    </div>
                `;
                garantsContainer.appendChild(garantFieldset);
            }
        }

        nbGarantSelect.addEventListener('change', renderGarants);

        // Gestion dynamique de l'hébergement
        const hebergementSelect = document.getElementById('hebergement_type');
        const hebergementDetails = document.getElementById('hebergement_details');

        hebergementSelect.addEventListener('change', function(){
            if(this.value === 'chez_un_particulier') {
                hebergementDetails.style.display = 'block';
            } else {
                hebergementDetails.style.display = 'none';
            }
        });

        // Gestion de l'affichage des noms de fichiers
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>